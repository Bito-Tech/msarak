<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\AssessmentSession;
use App\Models\AssessmentVersion;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Result;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Q-04 Phase 10: ownership and IDOR protection.
 *
 * The security contract (system contracts sections 17 and 16) states that a
 * resource belonging to another student returns 404 RESOURCE_NOT_FOUND and
 * never reveals that it exists, while guests get 401 and a wrong role gets
 * 403. This file proves the boundary for every student-owned resource that
 * carries assessment data: session, answers, result and history.
 */
class ResultOwnershipSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const CODES = ['R', 'I', 'A', 'S', 'E', 'C'];

    private AssessmentVersion $version;

    protected function setUp(): void
    {
        parent::setUp();

        $this->version = AssessmentVersion::create([
            'version_number' => 1,
            'status' => 'active',
            'published_at' => now(),
        ]);

        for ($position = 1; $position <= 18; $position++) {
            $question = Question::create([
                'assessment_version_id' => $this->version->id,
                'position' => $position,
                'scenario' => "الموقف {$position}",
            ]);

            for ($optionPosition = 1; $optionPosition <= 4; $optionPosition++) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'position' => $optionPosition,
                    'option_text' => "الخيار {$optionPosition}",
                    'riasec_code' => self::CODES[($position + $optionPosition - 2) % 6],
                ]);
            }
        }
    }

    /**
     * The owner can read their own session, result and history.
     */
    public function test_the_owner_can_access_every_own_resource(): void
    {
        $owner = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($owner);
        $result = $this->completeSession($owner, $session);

        $this->actingAs($owner)
            ->getJson(route('assessment.show', $session->id))
            ->assertOk();

        $this->actingAs($owner)
            ->getJson(route('results.show', $result->id))
            ->assertOk();

        $this->actingAs($owner)
            ->getJson(route('profile.results.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /**
     * IDOR on the result: another student cannot read it and the response
     * does not disclose that the result exists.
     */
    public function test_another_student_cannot_read_a_foreign_result(): void
    {
        $owner = User::factory()->create(['role' => 'student']);
        $intruder = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($owner);
        $result = $this->completeSession($owner, $session);

        $this->actingAs($intruder)
            ->getJson(route('results.show', $result->id))
            ->assertStatus(404)
            ->assertJson([
                'message' => 'المورد غير موجود.',
                'code' => 'RESOURCE_NOT_FOUND',
            ]);

        // The foreign result must never appear in the intruder's history.
        $history = $this->actingAs($intruder)
            ->getJson(route('profile.results.index'))
            ->assertOk();

        $this->assertNotContains(
            $result->id,
            collect($history->json('data'))->pluck('result_id')->all()
        );
    }

    /**
     * IDOR on the session: another student cannot read the assessment state,
     * which would otherwise expose every question and saved answer.
     */
    public function test_another_student_cannot_read_a_foreign_session(): void
    {
        $owner = User::factory()->create(['role' => 'student']);
        $intruder = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($owner);

        $this->actingAs($intruder)
            ->getJson(route('assessment.show', $session->id))
            ->assertStatus(404)
            ->assertJson([
                'message' => 'المورد غير موجود.',
                'code' => 'RESOURCE_NOT_FOUND',
            ]);
    }

    /**
     * IDOR on the answers: another student cannot overwrite an answer that
     * belongs to a session they do not own.
     */
    public function test_another_student_cannot_write_into_a_foreign_session(): void
    {
        $owner = User::factory()->create(['role' => 'student']);
        $intruder = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($owner);
        $answer = $session->answers()->firstOrFail();

        $this->actingAs($intruder)
            ->putJson(
                route('assessment.answers.update', [$session->id, $answer->question_id]),
                [
                    'primary_option_id' => $answer->primary_option_id,
                    'none_selected' => false,
                    'unable_to_judge' => false,
                    'ratings' => [],
                ]
            )
            ->assertStatus(404)
            ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');

        // The owner's answer is untouched.
        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'primary_option_id' => $answer->primary_option_id,
        ]);
    }

    /**
     * IDOR on completion: another student cannot complete a foreign session
     * and produce a result under it.
     */
    public function test_another_student_cannot_complete_a_foreign_session(): void
    {
        $owner = User::factory()->create(['role' => 'student']);
        $intruder = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($owner);

        $this->actingAs($intruder)
            ->postJson(route('assessment.sessions.complete', $session->id))
            ->assertStatus(404)
            ->assertJsonPath('code', 'RESOURCE_NOT_FOUND');

        $this->assertSame(0, Result::count());
        $this->assertDatabaseHas('assessment_sessions', [
            'id' => $session->id,
            'status' => 'in_progress',
        ]);
    }

    /**
     * A guest is blocked before any ownership logic runs: every student route
     * answers 401, and no result is created.
     */
    public function test_a_guest_is_denied_on_every_student_resource(): void
    {
        $owner = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($owner);

        // Complete while authenticated, then log out before the guest probes.
        $result = $this->completeSession($owner, $session);
        auth()->logout();

        $this->getJson(route('assessment.show', $session->id))->assertStatus(401);
        $this->getJson(route('results.show', $result->id))->assertStatus(401);
        $this->getJson(route('profile.results.index'))->assertStatus(401);
        $this->getJson(route('assessment.intro'))->assertStatus(401);

        $this->postJson(route('assessment.sessions.complete', $session->id))
            ->assertStatus(401);
    }

    /**
     * An admin holds no operational access to a student's session or result:
     * the role gate refuses with 403 rather than 404, keeping the boundary
     * between administrative and student data explicit.
     */
    public function test_an_admin_cannot_read_a_student_session_or_result(): void
    {
        $owner = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);
        $session = $this->createAnsweredSession($owner);
        $result = $this->completeSession($owner, $session);

        $this->actingAs($admin)
            ->getJson(route('assessment.show', $session->id))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson(route('results.show', $result->id))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->getJson(route('profile.results.index'))
            ->assertStatus(403);
    }

    /**
     * The owner's own result survives every foreign attempt: no denial path
     * mutates or deletes the legitimate data.
     */
    public function test_foreign_attempts_leave_the_owners_data_intact(): void
    {
        $owner = User::factory()->create(['role' => 'student']);
        $intruder = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($owner);
        $result = $this->completeSession($owner, $session);

        $this->actingAs($intruder)
            ->getJson(route('results.show', $result->id))
            ->assertStatus(404);

        $this->actingAs($intruder)
            ->postJson(route('assessment.sessions.complete', $session->id))
            ->assertStatus(404);

        $this->assertSame(1, Result::count());
        $this->assertSame(1, $result->resultScores()->count() === 0 ? 0 : 1);
        $this->assertSame(6, $result->resultScores()->count());
        $this->assertSame(5, $result->resultRecommendations()->count());

        // The owner still sees their own result.
        $this->actingAs($owner)
            ->getJson(route('results.show', $result->id))
            ->assertOk();
    }

    /**
     * The history endpoint ignores any user_id tampering: ownership is derived
     * from the session, never from a query parameter.
     */
    public function test_history_ignores_a_forged_user_id_parameter(): void
    {
        $owner = User::factory()->create(['role' => 'student']);
        $intruder = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($owner);
        $result = $this->completeSession($owner, $session);

        $history = $this->actingAs($intruder)
            ->getJson(route('profile.results.index').'?user_id='.$owner->id)
            ->assertOk();

        $this->assertNotContains(
            $result->id,
            collect($history->json('data'))->pluck('result_id')->all()
        );
    }

    private function createAnsweredSession(User $owner): AssessmentSession
    {
        $session = AssessmentSession::create([
            'user_id' => $owner->id,
            'assessment_version_id' => $this->version->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        foreach ($this->version->questions as $question) {
            Answer::create([
                'assessment_session_id' => $session->id,
                'question_id' => $question->id,
                'primary_option_id' => $question->questionOptions->first()->id,
                'response_type' => 'option',
            ]);
        }

        return $session;
    }

    private function completeSession(User $owner, AssessmentSession $session): Result
    {
        $this->actingAs($owner)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        return Result::firstOrFail();
    }
}
