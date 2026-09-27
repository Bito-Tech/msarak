<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\AssessmentSession;
use App\Models\AssessmentVersion;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Result;
use App\Models\ResultRecommendation;
use App\Models\ResultScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

/**
 * Q-04 Phase 7: atomicity, idempotency and concurrency of completion.
 *
 * The completion contract requires the calculation, matching, persistence
 * and session-closing to happen inside a single transaction, so a failure
 * anywhere leaves no partial result, and a repeated completion returns the
 * existing result instead of duplicating it.
 */
class CompletionAtomicityAndIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private const CODES = ['R', 'I', 'A', 'S', 'E', 'C'];

    /**
     * Atomicity: a failure after the Result and its scores are written but
     * before the recommendations land must roll the whole unit of work back.
     */
    public function test_failure_during_recommendation_persistence_rolls_back_everything(): void
    {
        [$student, $session] = $this->makeAnsweredSession();
        $eventName = 'eloquent.creating: '.ResultRecommendation::class;
        Event::listen($eventName, static function (): never {
            throw new RuntimeException('forced failure after result and scores');
        });

        try {
            $this->actingAs($student)
                ->postJson(route('assessment.sessions.complete', $session))
                ->assertStatus(500);
        } finally {
            Event::forget($eventName);
        }

        $this->assertSame(0, Result::count());
        $this->assertSame(0, ResultScore::count());
        $this->assertSame(0, ResultRecommendation::count());
        $this->assertDatabaseHas('assessment_sessions', [
            'id' => $session->id,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Atomicity: a failure while writing the score rows leaves no orphan
     * Result either — the parent must not survive its children.
     */
    public function test_failure_during_score_persistence_leaves_no_result(): void
    {
        [$student, $session] = $this->makeAnsweredSession();
        $eventName = 'eloquent.creating: '.ResultScore::class;
        Event::listen($eventName, static function (): never {
            throw new RuntimeException('forced failure while writing scores');
        });

        try {
            $this->actingAs($student)
                ->postJson(route('assessment.sessions.complete', $session))
                ->assertStatus(500);
        } finally {
            Event::forget($eventName);
        }

        $this->assertSame(0, Result::count());
        $this->assertSame(0, ResultScore::count());
        $this->assertSame(0, ResultRecommendation::count());
        $this->assertDatabaseHas('assessment_sessions', [
            'id' => $session->id,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Atomicity: a session is only marked completed when the whole unit of
     * work succeeds. A failure before the session update keeps it in progress.
     */
    public function test_a_session_stays_in_progress_when_completion_fails(): void
    {
        [$student, $session] = $this->makeAnsweredSession();
        $eventName = 'eloquent.creating: '.Result::class;
        Event::listen($eventName, static function (): never {
            throw new RuntimeException('forced failure at result creation');
        });

        try {
            $this->actingAs($student)
                ->postJson(route('assessment.sessions.complete', $session))
                ->assertStatus(500);
        } finally {
            Event::forget($eventName);
        }

        $this->assertSame(0, Result::count());
        $session->refresh();
        $this->assertSame('in_progress', $session->status);
        $this->assertNull($session->completed_at);
    }

    /**
     * Idempotency: completing the same session twice returns the same result
     * and creates exactly one of everything.
     */
    public function test_repeated_completion_returns_the_same_result_without_duplicates(): void
    {
        [$student, $session] = $this->makeAnsweredSession();

        $first = $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session));
        $second = $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session));

        $first->assertOk();
        $second->assertOk();

        $this->assertSame($first->json('data.result_id'), $second->json('data.result_id'));
        $this->assertSame(1, Result::count());
        $this->assertSame(6, ResultScore::count());
        $this->assertSame(5, ResultRecommendation::count());
    }

    /**
     * Idempotency at scale: many repeated completions still converge on one
     * result, six scores and five recommendations.
     */
    public function test_many_repeated_completions_still_produce_one_result(): void
    {
        [$student, $session] = $this->makeAnsweredSession();
        $resultId = null;

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $response = $this->actingAs($student)
                ->postJson(route('assessment.sessions.complete', $session))
                ->assertOk();

            if ($resultId === null) {
                $resultId = $response->json('data.result_id');
            } else {
                $this->assertSame($resultId, $response->json('data.result_id'));
            }
        }

        $this->assertSame(1, Result::count());
        $this->assertSame(6, ResultScore::count());
        $this->assertSame(5, ResultRecommendation::count());
    }

    /**
     * Idempotency after an intervening failure: the first attempt explodes,
     * the retry succeeds exactly once.
     */
    public function test_completion_after_a_failed_attempt_succeeds_exactly_once(): void
    {
        [$student, $session] = $this->makeAnsweredSession();
        $eventName = 'eloquent.creating: '.ResultRecommendation::class;

        Event::listen($eventName, static function (): never {
            throw new RuntimeException('one-shot failure');
        });

        try {
            $this->actingAs($student)
                ->postJson(route('assessment.sessions.complete', $session))
                ->assertStatus(500);
        } finally {
            Event::forget($eventName);
        }

        $this->assertSame(0, Result::count());

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $this->assertSame(1, Result::count());
        $this->assertSame(6, ResultScore::count());
        $this->assertSame(5, ResultRecommendation::count());
    }

    /**
     * Concurrency safety is deliberately NOT claimed here: this test issues
     * sequential duplicate requests, which proves idempotency, not locking.
     * The service guards a concurrent double-completion with lockForUpdate on
     * the session row, but PHP test processes are single-threaded and cannot
     * exercise two transactions at once. This is recorded as NOT VERIFIED
     * rather than simulated.
     */
    public function test_concurrent_completion_is_not_simulated_as_idempotency(): void
    {
        [$student, $session] = $this->makeAnsweredSession();

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $this->assertSame(1, Result::count());

        $this->assertTrue(
            AssessmentSession::whereKey($session->id)->where('status', 'completed')->exists(),
            'the session row is the completion anchor'
        );
    }

    /**
     * @return array{User, AssessmentSession}
     */
    private function makeAnsweredSession(): array
    {
        $student = User::factory()->create(['role' => 'student']);
        $version = AssessmentVersion::create([
            'version_number' => AssessmentVersion::max('version_number') + 1,
            'status' => 'active',
            'published_at' => now(),
        ]);

        $session = AssessmentSession::create([
            'user_id' => $student->id,
            'assessment_version_id' => $version->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        for ($position = 1; $position <= 18; $position++) {
            $question = Question::create([
                'assessment_version_id' => $version->id,
                'position' => $position,
                'scenario' => "الموقف {$position}",
            ]);

            $options = [];
            for ($optionPosition = 1; $optionPosition <= 4; $optionPosition++) {
                $options[] = QuestionOption::create([
                    'question_id' => $question->id,
                    'position' => $optionPosition,
                    'option_text' => "الخيار {$optionPosition}",
                    'riasec_code' => self::CODES[($position + $optionPosition - 2) % 6],
                ]);
            }

            Answer::create([
                'assessment_session_id' => $session->id,
                'question_id' => $question->id,
                'primary_option_id' => $options[0]->id,
                'response_type' => 'option',
            ]);
        }

        return [$student, $session->refresh()];
    }
}
