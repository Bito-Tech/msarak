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
 * Q-04 Phase 4: the ValidQuestions boundary of assessment completion.
 *
 * The approved specification (question_bank_specification.md section 15.1 and
 * system contracts section 9) defines:
 *
 *      ValidQuestions = answers whose response_type is not cannot_judge
 *      option         = valid
 *      none           = valid
 *      cannot_judge   = excluded
 *
 * and refuses a final result when ValidQuestions < 15, while the completion
 * service requires all 18 questions to be answered first. The boundary this
 * file pins down is therefore 14 valid -> reject, 15..18 valid -> accept.
 */
class ValidQuestionsBoundaryTest extends TestCase
{
    use RefreshDatabase;

    private const CODES = ['R', 'I', 'A', 'S', 'E', 'C'];

    /**
     * @dataProvider validQuestionBoundaries
     */
    public function test_the_valid_questions_boundary_governs_result_creation(int $valid, bool $allowed): void
    {
        [$student, $session] = $this->makeSessionWith($valid);

        $response = $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session));

        if ($allowed) {
            $response->assertOk()
                ->assertJsonPath('data.status', 'completed')
                ->assertJsonPath('data.session_id', $session->id);

            $this->assertSame(1, Result::count());
            $this->assertDatabaseHas('assessment_sessions', [
                'id' => $session->id,
                'status' => 'completed',
            ]);
        } else {
            $response->assertStatus(409)
                ->assertJsonPath('code', 'SESSION_NOT_READY');

            $this->assertSame(0, Result::count());
            $this->assertDatabaseHas('assessment_sessions', [
                'id' => $session->id,
                'status' => 'in_progress',
            ]);
        }
    }

    /**
     * @return array<string, array{int, bool}>
     */
    public static function validQuestionBoundaries(): array
    {
        return [
            '14 valid answers are rejected' => [14, false],
            '15 valid answers are accepted' => [15, true],
            '16 valid answers are accepted' => [16, true],
            '17 valid answers are accepted' => [17, true],
            '18 valid answers are accepted' => [18, true],
        ];
    }

    /**
     * `cannot_judge` is the exact reason a question leaves ValidQuestions:
     * 18 answered questions with 4 of them cannot_judge leaves only 14 valid.
     */
    public function test_cannot_judge_answers_do_not_count_as_valid_questions(): void
    {
        [$student, $session] = $this->makeSessionWith(14);

        $answers = $session->answers;
        $this->assertSame(18, $answers->count());

        $cannotJudge = $answers->where('response_type', 'cannot_judge')->count();
        $valid = $answers->where('response_type', '!=', 'cannot_judge')->count();

        $this->assertSame(4, $cannotJudge);
        $this->assertSame(14, $valid);

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertStatus(409)
            ->assertJsonPath('code', 'SESSION_NOT_READY');

        $this->assertSame(0, Result::count());
    }

    /**
     * `none` is a valid question even though it picks no primary option: an
     * all-`none` session of 18 questions has 18 valid questions and must be
     * accepted, producing the six zero scores.
     */
    public function test_none_answers_count_as_valid_questions(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $version = $this->createPublishedVersion();
        $session = $this->createSession($student, $version);

        foreach ($version->questions as $question) {
            Answer::create([
                'assessment_session_id' => $session->id,
                'question_id' => $question->id,
                'primary_option_id' => null,
                'response_type' => 'none',
            ]);
        }

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $result = Result::firstOrFail();
        $this->assertCount(6, $result->resultScores);
        $result->resultScores->each(
            fn ($score) => $this->assertSame('0.00', (string) $score->score)
        );
    }

    /**
     * A boundary of exactly 15 valid with 3 cannot_judge must succeed, which
     * pins the >= 15 rule (not > 15) at its exact threshold.
     */
    public function test_exactly_fifteen_valid_questions_is_the_acceptance_threshold(): void
    {
        [$student, $session] = $this->makeSessionWith(15);

        $valid = $session->answers->where('response_type', '!=', 'cannot_judge')->count();
        $this->assertSame(15, $valid);

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $this->assertSame(1, Result::count());
    }

    /**
     * @return array{User, AssessmentSession}
     */
    private function makeSessionWith(int $valid): array
    {
        $student = User::factory()->create(['role' => 'student']);
        $version = $this->createPublishedVersion();
        $session = $this->createSession($student, $version);

        foreach ($version->questions as $position => $question) {
            $isValid = ($position + 1) <= $valid;

            Answer::create([
                'assessment_session_id' => $session->id,
                'question_id' => $question->id,
                'primary_option_id' => $isValid ? $question->questionOptions->first()->id : null,
                'response_type' => $isValid ? 'option' : 'cannot_judge',
            ]);
        }

        return [$student, $session];
    }

    private function createPublishedVersion(): AssessmentVersion
    {
        $version = AssessmentVersion::create([
            'version_number' => AssessmentVersion::max('version_number') + 1,
            'status' => 'active',
            'published_at' => now(),
        ]);

        for ($position = 1; $position <= 18; $position++) {
            $question = Question::create([
                'assessment_version_id' => $version->id,
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

        return $version->refresh();
    }

    private function createSession(User $student, AssessmentVersion $version): AssessmentSession
    {
        return AssessmentSession::create([
            'user_id' => $student->id,
            'assessment_version_id' => $version->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }
}
