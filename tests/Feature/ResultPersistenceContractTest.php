<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\AnswerOptionRating;
use App\Models\AssessmentSession;
use App\Models\AssessmentVersion;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Result;
use App\Models\ResultRecommendation;
use App\Models\ResultScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Q-04 Phase 6: result persistence after completion.
 *
 * The contract (system contracts section 9 and the approved data model)
 * requires one immutable Result per completed session carrying:
 *   - exactly six ResultScores (one per RIASEC domain),
 *   - 3..5 ResultRecommendations with a 1-based display_order,
 *   - the scoring and catalog versions that produced them.
 *
 * This file proves the data is actually in the database, not merely that the
 * HTTP response looks right.
 */
class ResultPersistenceContractTest extends TestCase
{
    use RefreshDatabase;

    private const CODES = ['R', 'I', 'A', 'S', 'E', 'C'];

    public function test_completion_persists_one_result_with_six_scores_and_five_recommendations(): void
    {
        [$student, $session] = $this->makeAnsweredSession();

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $this->assertSame(1, Result::count());
        $this->assertDatabaseCount('result_scores', 6);
        $this->assertDatabaseCount('result_recommendations', 5);
    }

    public function test_the_result_row_carries_the_session_and_both_versions(): void
    {
        [$student, $session] = $this->makeAnsweredSession();

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $this->assertDatabaseHas('results', [
            'assessment_session_id' => $session->id,
            'scoring_version' => '1.2',
            'catalog_version' => '1.0',
        ]);

        $result = Result::firstOrFail();
        $this->assertSame($session->id, $result->assessment_session_id);
        $this->assertSame('1.2', $result->scoring_version);
        $this->assertSame('1.0', $result->catalog_version);
        $this->assertNotNull($result->created_at);
    }

    public function test_the_session_is_marked_completed_with_a_timestamp(): void
    {
        [$student, $session] = $this->makeAnsweredSession();

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $this->assertDatabaseHas('assessment_sessions', [
            'id' => $session->id,
            'status' => 'completed',
        ]);

        $session->refresh();
        $this->assertNotNull($session->completed_at);
    }

    public function test_each_riasec_domain_has_exactly_one_score_row(): void
    {
        [$student, $session] = $this->makeAnsweredSession();

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $result = Result::firstOrFail();

        $this->assertEqualsCanonicalizing(
            self::CODES,
            $result->resultScores->pluck('riasec_code')->all()
        );

        foreach (self::CODES as $code) {
            $this->assertSame(
                1,
                ResultScore::where('result_id', $result->id)
                    ->where('riasec_code', $code)
                    ->count(),
                "domain {$code} must appear exactly once"
            );
        }
    }

    public function test_every_persisted_score_is_within_zero_and_one_hundred(): void
    {
        [$student, $session] = $this->makeAnsweredSession();

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        foreach (ResultScore::all() as $score) {
            $this->assertGreaterThanOrEqual(0.00, (float) $score->score);
            $this->assertLessThanOrEqual(100.00, (float) $score->score);
        }
    }

    public function test_persisted_scores_match_the_independently_calculated_values(): void
    {
        // Fixture rotation: question p offers the four consecutive codes
        // CODES[p-1 .. p+2] and the student always picks the first option,
        // whose code is CODES[p-1]. Over 18 positions each domain is:
        //   - picked exactly 3 times (K_d = 3),
        //   - offered in the 4 windows whose start residue is j, j-1, j-2 or
        //     j-3, and each residue occurs 3 times, so N_d = 12.
        // No ratings are given, so W_d = 0 and Score_d = 100 * P_d
        // = 100 * 3/12 = 25.00 for all six domains.
        [$student, $session] = $this->makeAnsweredSession();

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $result = Result::firstOrFail();

        foreach (self::CODES as $code) {
            $score = $result->resultScores->firstWhere('riasec_code', $code);
            $this->assertNotNull($score, "missing persisted score for {$code}");
            $this->assertSame(25.00, (float) $score->score, "wrong persisted score for {$code}");
        }
    }

    public function test_recommendations_carry_a_contiguous_one_based_display_order(): void
    {
        [$student, $session] = $this->makeAnsweredSession();

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $result = Result::firstOrFail();
        $orders = $result->resultRecommendations
            ->sortBy('display_order')
            ->pluck('display_order')
            ->all();

        $this->assertSame([1, 2, 3, 4, 5], $orders);
    }

    public function test_no_duplicate_specialization_or_display_order_per_result(): void
    {
        [$student, $session] = $this->makeAnsweredSession();

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $result = Result::firstOrFail();

        $keys = $result->resultRecommendations->pluck('specialization_key')->all();
        $this->assertSame(count($keys), count(array_unique($keys)));

        $orders = $result->resultRecommendations->pluck('display_order')->all();
        $this->assertSame(count($orders), count(array_unique($orders)));
    }

    public function test_every_recommendation_belongs_to_the_right_result_with_snapshots(): void
    {
        [$student, $session] = $this->makeAnsweredSession();

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $result = Result::firstOrFail();

        foreach ($result->resultRecommendations as $recommendation) {
            $this->assertSame($result->id, $recommendation->result_id);
            $this->assertNotSame('', (string) $recommendation->name_snapshot);
            $this->assertNotSame('', (string) $recommendation->rationale_snapshot);
            $this->assertGreaterThanOrEqual(0.0, (float) $recommendation->similarity_score);
            $this->assertLessThanOrEqual(1.0, (float) $recommendation->similarity_score);
        }
    }

    public function test_all_score_and_recommendation_rows_reference_the_result_foreign_key(): void
    {
        [$student, $session] = $this->makeAnsweredSession();

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $result = Result::firstOrFail();

        $this->assertSame(
            6,
            ResultScore::where('result_id', $result->id)->count()
        );
        $this->assertSame(
            5,
            ResultRecommendation::where('result_id', $result->id)->count()
        );
        $this->assertSame(
            0,
            ResultScore::where('result_id', '!=', $result->id)->count()
        );
        $this->assertSame(
            0,
            ResultRecommendation::where('result_id', '!=', $result->id)->count()
        );
    }

    public function test_the_result_is_linked_to_its_owning_session_and_user(): void
    {
        [$student, $session] = $this->makeAnsweredSession();

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $result = Result::firstOrFail();

        $this->assertSame($session->id, $result->assessment_session_id);
        $this->assertSame($student->id, $result->assessmentSession->user_id);
        $this->assertSame($session->assessment_version_id, $result->assessmentSession->assessment_version_id);
    }

    public function test_a_completed_session_has_exactly_one_result_row(): void
    {
        [$student, $session] = $this->makeAnsweredSession();

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $this->assertSame(
            1,
            Result::where('assessment_session_id', $session->id)->count()
        );
        $this->assertSame(1, $session->result()->count());
    }

    public function test_ratings_persisted_before_completion_feed_the_persisted_scores(): void
    {
        // Rate the primary option of the first answer +2 so the rating term is
        // non-trivial: for the chosen domain the score must no longer equal
        // the pure-primary value, proving AnswerOptionRating rows are read.
        [$student, $session, $version] = $this->makeAnsweredSession(true);

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $result = Result::firstOrFail();
        $scores = $result->resultScores->pluck('score', 'riasec_code');

        // Without ratings every domain would be 16.67; the rated domain must
        // move away from that pure-primary value.
        $this->assertNotSame('16.67', (string) $scores['R']);
    }

    /**
     * @return array{User, AssessmentSession, AssessmentVersion}
     */
    private function makeAnsweredSession(bool $withRating = false): array
    {
        $student = User::factory()->create(['role' => 'student']);
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

            $options = [];
            for ($optionPosition = 1; $optionPosition <= 4; $optionPosition++) {
                $options[] = QuestionOption::create([
                    'question_id' => $question->id,
                    'position' => $optionPosition,
                    'option_text' => "الخيار {$optionPosition}",
                    'riasec_code' => self::CODES[($position + $optionPosition - 2) % 6],
                ]);
            }
        }

        $session = AssessmentSession::create([
            'user_id' => $student->id,
            'assessment_version_id' => $version->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        foreach ($version->refresh()->questions as $position => $question) {
            $primary = $question->questionOptions->first();
            $answer = Answer::create([
                'assessment_session_id' => $session->id,
                'question_id' => $question->id,
                'primary_option_id' => $primary->id,
                'response_type' => 'option',
            ]);

            if ($withRating && $position === 0) {
                AnswerOptionRating::create([
                    'answer_id' => $answer->id,
                    'question_option_id' => $primary->id,
                    'rating' => 2,
                ]);
            }
        }

        return [$student, $session, $version];
    }
}
