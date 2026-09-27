<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\AnswerOptionRating;
use App\Models\AssessmentSession;
use App\Models\AssessmentVersion;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\ScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Q-04 Phase 3: numeric fidelity of Scoring v1.2.
 *
 * Every expected value in this file was computed independently of the
 * ScoringService implementation, by hand from the approved specification
 * (docs/04-assessment/question_bank_specification.md sections 15-21):
 *
 *      N_d     = valid questions where domain d appears
 *      K_d     = times the student picked d as primary
 *      M_d     = optional ratings actually given for d
 *      P_d     = K_d / N_d
 *      Rraw_d  = sum_r_d / (M_d + 2)          (lambda = 2)
 *      R_d     = (Rraw_d + 2) / 4
 *      C_d     = min(1, M_d / N_d)
 *      W_d     = 0.30 * C_d
 *      Score_d = round(clamp(100*((1-W_d)*P_d + W_d*R_d), 0, 100), 2)
 *
 * The service output is compared against those literal constants, never
 * against another call to the service itself.
 */
class ScoringV12NumericFidelityTest extends TestCase
{
    use RefreshDatabase;

    private const QUESTION_CODES = ['R', 'I', 'A', 'S'];

    /**
     * Case 1 — no ratings at all: the score collapses to 100 * P_d.
     *
     * Ten option answers: four pick I, six pick R, no ratings anywhere.
     * Expected (by hand): P_I = 4/10, W = 0 → I = 40.00; R = 6/10 → 60.00.
     */
    public function test_case_1_no_ratings_collapses_to_primary_percentage(): void
    {
        $answers = collect();
        for ($i = 0; $i < 10; $i++) {
            [$answer] = $this->makeAnswer('option', $i < 4 ? 'I' : 'R');
            $answers->push($answer);
        }

        $scores = app(ScoringService::class)->calculate($answers->values()->all());

        $this->assertSame(40.00, $scores['I']);
        $this->assertSame(60.00, $scores['R']);
        $this->assertSame(0.00, $scores['A']);
        $this->assertSame(0.00, $scores['S']);
    }

    /**
     * Case 2 — an explicit 0 rating is NOT a missing rating.
     *
     * One option answer picking I, with I rated 0 and R rated -2.
     * By hand: M_I = 1, Rraw_I = 0/(1+2) = 0, R_I = 0.5, C = 1, W = 0.30.
     * Score_I = 100*(0.70*1 + 0.30*0.5) = 85.00.
     * For R: P_R = 0, M_R = 1, Rraw_R = -2/3, R_R = (2-0.6667)/4 = 0.3333.
     * Score_R = 100*(0 + 0.30*0.3333) = 10.00.
     */
    public function test_case_2_explicit_zero_rating_differs_from_a_missing_one(): void
    {
        [$answer, $options] = $this->makeAnswer('option', 'I');

        $this->rate($answer, $options['I'], 0);
        $this->rate($answer, $options['R'], -2);

        $scores = app(ScoringService::class)->calculate([$answer]);

        $this->assertSame(85.00, $scores['I']);
        $this->assertSame(10.00, $scores['R']);
    }

    /**
     * Case 2b — the missing-rating counterpart of Case 2.
     *
     * Same answer with no rating rows at all: M = 0, so W = 0 and the score
     * is pure primary → 100.00 for I. This is the NULL != 0 rule: a missing
     * row never reaches the rating term.
     */
    public function test_case_2b_missing_rating_keeps_the_score_purely_primary(): void
    {
        [$answer] = $this->makeAnswer('option', 'I');

        $scores = app(ScoringService::class)->calculate([$answer]);

        $this->assertSame(100.00, $scores['I']);
        $this->assertSame(0.00, $scores['R']);
    }

    /**
     * Case 3 — positive ratings at full coverage.
     *
     * Two option answers picking I, each rated +2.
     * Rraw_I = 4/(2+2) = 1, R_I = 0.75, C = 1, W = 0.30.
     * Score_I = 100*(0.70*1 + 0.30*0.75) = 92.50.
     */
    public function test_case_3_positive_ratings_at_full_coverage(): void
    {
        $answers = collect();
        for ($i = 0; $i < 2; $i++) {
            [$answer, $options] = $this->makeAnswer('option', 'I');
            $this->rate($answer, $options['I'], 2);
            $answers->push($answer);
        }

        $scores = app(ScoringService::class)->calculate($answers);

        $this->assertSame(92.50, $scores['I']);
    }

    /**
     * Case 4 — negative ratings at full coverage.
     *
     * Two option answers picking I, each rated -2.
     * Rraw_I = -4/4 = -1, R_I = 0.25, W = 0.30.
     * Score_I = 100*(0.70*1 + 0.30*0.25) = 77.50.
     */
    public function test_case_4_negative_ratings_at_full_coverage(): void
    {
        $answers = collect();
        for ($i = 0; $i < 2; $i++) {
            [$answer, $options] = $this->makeAnswer('option', 'I');
            $this->rate($answer, $options['I'], -2);
            $answers->push($answer);
        }

        $scores = app(ScoringService::class)->calculate($answers);

        $this->assertSame(77.50, $scores['I']);
    }

    /**
     * Case 5 — partial ratings: the specification's worked example.
     *
     * Ten answers: first four pick I, last six pick R; the first five carry
     * I-ratings 2,2,1,1,0. By hand from sections 17-21:
     *   N_I=10, K_I=4, M_I=5, sum_r=6, Rraw=6/7=0.857143, R=0.714286,
     *   C=0.5, W=0.15 → 100*(0.85*0.4 + 0.15*0.714286) = 44.71.
     */
    public function test_case_5_partial_ratings_match_the_specification_example(): void
    {
        $answers = collect();
        $ratings = [2, 2, 1, 1, 0];

        for ($i = 0; $i < 10; $i++) {
            [$answer, $options] = $this->makeAnswer('option', $i < 4 ? 'I' : 'R');

            if (array_key_exists($i, $ratings)) {
                $this->rate($answer, $options['I'], $ratings[$i]);
            }

            $answers->push($answer);
        }

        $scores = app(ScoringService::class)->calculate($answers);

        $this->assertSame(44.71, $scores['I']);
        $this->assertSame(60.00, $scores['R']);
    }

    /**
     * Case 6 — full ratings with lambda shrinkage visible.
     *
     * Three answers picking I, each rated +2.
     * Rraw_I = 6/(3+2) = 1.2, R_I = 0.8, W = 0.30.
     * Score_I = 100*(0.70 + 0.30*0.8) = 94.00.
     */
    public function test_case_6_full_ratings_apply_lambda_shrinkage(): void
    {
        $answers = collect();
        for ($i = 0; $i < 3; $i++) {
            [$answer, $options] = $this->makeAnswer('option', 'I');
            $this->rate($answer, $options['I'], 2);
            $answers->push($answer);
        }

        $scores = app(ScoringService::class)->calculate($answers);

        $this->assertSame(94.00, $scores['I']);
    }

    /**
     * Case 7 — `none` counts as a valid question but adds no primary pick.
     *
     * One `none` answer: N rises for all four offered domains, K stays 0, no
     * ratings → every score is 0.00. `none` is valid, it simply never picks.
     */
    public function test_case_7_none_is_valid_but_never_picks_a_domain(): void
    {
        [$answer] = $this->makeAnswer('none');

        $scores = app(ScoringService::class)->calculate([$answer]);

        $this->assertSame(0.00, $scores['R']);
        $this->assertSame(0.00, $scores['I']);
        $this->assertSame(0.00, $scores['A']);
        $this->assertSame(0.00, $scores['S']);
    }

    /**
     * Case 8 — `cannot_judge` is excluded from the whole calculation.
     *
     * A `cannot_judge` answer contributes nothing: N_d never increments, so
     * every domain reports 0.00. This is the ValidQuestions exclusion rule.
     * The answer is placed in a separate session from the option answers so
     * the score vector stays attributable to the cannot_judge row alone.
     */
    public function test_case_8_cannot_judge_is_excluded_entirely(): void
    {
        [$optionAnswer] = $this->makeAnswer('option', 'I');
        [$cannotJudge] = $this->makeAnswer('cannot_judge');

        $withCj = app(ScoringService::class)->calculate([$optionAnswer, $cannotJudge]);
        $withoutCj = app(ScoringService::class)->calculate([$optionAnswer]);

        // cannot_judge must not perturb a single domain score.
        foreach (ScoringService::RIASEC_CODES as $code) {
            $this->assertSame($withoutCj[$code], $withCj[$code]);
        }

        // A session of cannot_judge answers only yields the zero vector.
        $scores = app(ScoringService::class)->calculate([$cannotJudge]);
        foreach (ScoringService::RIASEC_CODES as $code) {
            $this->assertSame(0.00, $scores[$code]);
        }
    }

    /**
     * Case 9 — a realistic combination of option, none and cannot_judge.
     *
     * Four option answers picking I (each rated +1), one `none`, one
     * `cannot_judge`. cannot_judge drops out, leaving five valid questions.
     * For I: N=5, K=4, M=4, sum=4, Rraw=4/6=0.666667, R=0.666667,
     * C=0.8, W=0.24 → 100*(0.76*0.8 + 0.24*0.666667) = 76.80.
     */
    public function test_case_9_combination_of_option_none_and_cannot_judge(): void
    {
        $answers = collect();
        for ($i = 0; $i < 4; $i++) {
            [$answer, $options] = $this->makeAnswer('option', 'I');
            $this->rate($answer, $options['I'], 1);
            $answers->push($answer);
        }

        $answers->push($this->makeAnswer('none')[0]);
        $answers->push($this->makeAnswer('cannot_judge')[0]);

        $scores = app(ScoringService::class)->calculate($answers);

        $this->assertSame(76.80, $scores['I']);
        $this->assertSame(0.00, $scores['R']);
    }

    /**
     * Case 10a — upper boundary: full positive ratings cannot exceed 100.
     *
     * Six answers picking I, each rated +2: Rraw = 12/8 = 1.5, R = 0.875,
     * W = 0.30 → 100*(0.70 + 0.30*0.875) = 96.25, still inside the clamp.
     */
    public function test_case_10a_upper_boundary_stays_within_one_hundred(): void
    {
        $answers = collect();
        for ($i = 0; $i < 6; $i++) {
            [$answer, $options] = $this->makeAnswer('option', 'I');
            $this->rate($answer, $options['I'], 2);
            $answers->push($answer);
        }

        $scores = app(ScoringService::class)->calculate($answers);

        $this->assertSame(96.25, $scores['I']);
        $this->assertLessThanOrEqual(100.00, $scores['I']);
    }

    /**
     * Case 10b — lower boundary: full negative ratings cannot go below 0.
     *
     * Six answers picking I, each rated -2: Rraw = -12/8 = -1.5, R = 0.125,
     * W = 0.30 → 100*(0.70 + 0.30*0.125) = 73.75.
     */
    public function test_case_10b_lower_boundary_never_drops_below_zero(): void
    {
        $answers = collect();
        for ($i = 0; $i < 6; $i++) {
            [$answer, $options] = $this->makeAnswer('option', 'I');
            $this->rate($answer, $options['I'], -2);
            $answers->push($answer);
        }

        $scores = app(ScoringService::class)->calculate($answers);

        $this->assertSame(73.75, $scores['I']);
        $this->assertGreaterThanOrEqual(0.00, $scores['I']);
    }

    /**
     * Case 10c — zero primary picks with negative ratings stays above zero.
     *
     * Four `none` answers that each rate R as -2: P_R = 0, M_R = 4,
     * Rraw_R = -8/6 = -1.333333, R_R = 0.166667, W = 0.30.
     * Score_R = 100*(0 + 0.30*0.166667) = 5.00 — the clamp is never needed
     * because the 30% cap keeps the rating term strictly bounded.
     */
    public function test_case_10c_zero_primary_with_negative_ratings(): void
    {
        $answers = collect();
        for ($i = 0; $i < 4; $i++) {
            [$answer, $options] = $this->makeAnswer('none');
            $this->rate($answer, $options['R'], -2);
            $answers->push($answer);
        }

        $scores = app(ScoringService::class)->calculate($answers);

        $this->assertSame(5.00, $scores['R']);
    }

    /**
     * Case 10d — the lambda boundary with a single rating.
     *
     * One answer picking I rated +1: Rraw = 1/(1+2) = 0.333333,
     * R = 0.583333, W = 0.30 → 100*(0.70 + 0.30*0.583333) = 87.50.
     */
    public function test_case_10d_single_rating_uses_lambda_shrinkage(): void
    {
        [$answer, $options] = $this->makeAnswer('option', 'I');
        $this->rate($answer, $options['I'], 1);

        $scores = app(ScoringService::class)->calculate([$answer]);

        $this->assertSame(87.50, $scores['I']);
    }

    /**
     * The six-domain vector is always complete and in the fixed RIASEC order,
     * even for domains that never appeared in any question.
     */
    public function test_the_six_domain_vector_is_always_complete_and_ordered(): void
    {
        [$answer] = $this->makeAnswer('option', 'I');

        $scores = app(ScoringService::class)->calculate([$answer]);

        $this->assertSame(['R', 'I', 'A', 'S', 'E', 'C'], array_keys($scores));
        $this->assertSame(0.00, $scores['E']);
        $this->assertSame(0.00, $scores['C']);
    }

    /**
     * A domain that appears in a question but is never picked and never rated
     * scores exactly 0.00 — P_d = 0 and W_d = 0.
     */
    public function test_an_offered_but_unpicked_domain_scores_zero(): void
    {
        [$answer] = $this->makeAnswer('option', 'I');

        $scores = app(ScoringService::class)->calculate([$answer]);

        $this->assertSame(0.00, $scores['A']);
        $this->assertSame(0.00, $scores['S']);
    }

    /**
     * Rounding is to exactly two decimal places, verified on a repeating
     * fraction: 0.714286 * 0.15 + 0.85 * 0.4 = 44.7142857... → 44.71.
     */
    public function test_scores_are_rounded_to_two_decimal_places(): void
    {
        $answers = collect();
        $ratings = [2, 2, 1, 1, 0];

        for ($i = 0; $i < 10; $i++) {
            [$answer, $options] = $this->makeAnswer('option', $i < 4 ? 'I' : 'R');

            if (array_key_exists($i, $ratings)) {
                $this->rate($answer, $options['I'], $ratings[$i]);
            }

            $answers->push($answer);
        }

        $scores = app(ScoringService::class)->calculate($answers);

        $this->assertSame(44.71, $scores['I']);
        $this->assertSame(2, strlen(substr(strstr((string) $scores['I'], '.'), 1)));
    }

    /**
     * Lambda is exactly 2 and the rating weight is capped at 30%, per the
     * approved coefficients of Scoring v1.2.
     */
    public function test_lambda_and_the_rating_weight_cap_are_the_approved_constants(): void
    {
        $this->assertSame(2.0, ScoringService::LAMBDA);
        $this->assertSame(0.30, ScoringService::MAX_RATING_WEIGHT);
        $this->assertSame('1.2', ScoringService::VERSION);
    }

    private function rate(Answer $answer, QuestionOption $option, int $value): AnswerOptionRating
    {
        return AnswerOptionRating::create([
            'answer_id' => $answer->id,
            'question_option_id' => $option->id,
            'rating' => $value,
        ]);
    }

    /** @return array{Answer, array<string, QuestionOption>} */
    private function makeAnswer(string $responseType, ?string $primaryCode = null): array
    {
        $user = User::factory()->create(['role' => 'student']);
        $version = AssessmentVersion::create([
            'version_number' => AssessmentVersion::max('version_number') + 1,
            'status' => 'draft',
        ]);
        $question = Question::create([
            'assessment_version_id' => $version->id,
            'position' => 1,
            'scenario' => 'موقف اختباري',
        ]);
        $options = [];
        foreach (self::QUESTION_CODES as $position => $code) {
            $options[$code] = QuestionOption::create([
                'question_id' => $question->id,
                'position' => $position + 1,
                'option_text' => "خيار {$code}",
                'riasec_code' => $code,
            ]);
        }
        $session = AssessmentSession::create([
            'user_id' => $user->id,
            'assessment_version_id' => $version->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        $answer = Answer::create([
            'assessment_session_id' => $session->id,
            'question_id' => $question->id,
            'primary_option_id' => $primaryCode ? $options[$primaryCode]->id : null,
            'response_type' => $responseType,
        ]);

        return [$answer, $options];
    }
}
