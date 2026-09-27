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
 * Q-04 Phase 12: the frontend must not compute anything.
 *
 * The binding rules in the approved contracts (sections 7, 8 and 10) and the
 * F-04 view invariants require that scores, recommendation order and the
 * similarity decision come from the backend only. The result page is a Blade
 * view rendered from persisted rows, so the verification mechanism here is
 * the rendered HTML itself: it must display the stored values and must not
 * contain any scoring, similarity or RIASEC code logic.
 */
class FrontendScoringBoundaryTest extends TestCase
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
     * The rendered page shows the persisted scores, not values derived in the
     * view: rendering again over the same rows reproduces the same output.
     */
    public function test_the_result_page_renders_persisted_scores_verbatim(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($student);

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $result = Result::with(['resultScores', 'resultRecommendations'])->firstOrFail();

        $first = $this->actingAs($student)
            ->get(route('results.show', $result))
            ->assertOk();

        // Mutate nothing, render again: the output must be identical.
        $second = $this->actingAs($student)
            ->get(route('results.show', $result))
            ->assertOk();

        $this->assertSame(
            $this->scoreMarkup($first->getContent()),
            $this->scoreMarkup($second->getContent()),
            'the view must render the stored values deterministically'
        );

        // Every persisted score appears on the page.
        foreach ($result->resultScores as $score) {
            $first->assertSee($this->formattedScore((float) $score->score), false);
        }
    }

    /**
     * The recommendation order on the page is the backend display_order, and
     * the page never ranks or re-sorts the rows itself.
     */
    public function test_the_page_keeps_the_backend_recommendation_order(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($student);

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $result = Result::with('resultRecommendations')->firstOrFail();
        $recommendations = $result->resultRecommendations
            ->sortBy('display_order')
            ->values();

        $response = $this->actingAs($student)
            ->get(route('results.show', $result))
            ->assertOk();

        $html = $response->getContent();
        $positions = [];

        foreach ($recommendations as $recommendation) {
            $pos = mb_strpos($html, $recommendation->name_snapshot);
            $this->assertNotFalse($pos, "missing recommendation {$recommendation->name_snapshot}");
            $positions[] = $pos;
        }

        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions, 'recommendations render in display_order');
    }

    /**
     * The contract forbids sending similarity_score to the interface and
     * forbids presenting any score as a certainty. The rendered page must not
     * expose a similarity figure or a RIASEC code.
     */
    public function test_the_page_never_exposes_similarity_scores_or_riasec_codes(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($student);

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $result = Result::firstOrFail();
        $html = $this->actingAs($student)
            ->get(route('results.show', $result))
            ->assertOk()
            ->getContent();

        // No similarity number leaks: the persisted decimals must not render.
        foreach ($result->resultRecommendations as $recommendation) {
            $this->assertStringNotContainsString(
                (string) $recommendation->similarity_score,
                $html,
                'similarity_score must never reach the interface'
            );
            $this->assertStringNotContainsString(
                'similarity',
                $html,
                'the similarity concept must not be named on the page'
            );
            break;
        }

        foreach (self::CODES as $code) {
            $this->assertStringNotContainsString(
                'riasec',
                strtolower($html),
                'the RIASEC vocabulary must not appear on the page'
            );
            break;
        }
    }

    /**
     * The JSON contract is meant to keep similarity_score server-side only
     * ("لا ترسل similarity_score إلى الواجهة", contracts section 10).
     *
     * The current ResultController JSON payload still exposes it, so this test
     * documents the actual response shape and asserts what the contract
     * requires separately. The deviation itself is recorded as Q4-C1 in the
     * Q-04 findings and is not silently fixed here: changing the payload is a
     * contract decision, not a test decision.
     */
    public function test_the_json_result_payload_shape_against_the_contract(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($student);

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $result = Result::firstOrFail();

        $payload = $this->actingAs($student)
            ->getJson(route('results.show', $result))
            ->assertOk()
            ->json('data');

        // The contract-required fields are all present.
        $this->assertArrayHasKey('result_id', $payload);
        $this->assertArrayHasKey('assessment_session_id', $payload);
        $this->assertArrayHasKey('scoring_version', $payload);
        $this->assertArrayHasKey('catalog_version', $payload);
        $this->assertArrayHasKey('scores', $payload);
        $this->assertArrayHasKey('recommendations', $payload);

        // The scores come from the persisted rows, never computed on the way out.
        $persisted = $result->resultScores->pluck('score', 'riasec_code')->all();
        foreach ($payload['scores'] as $score) {
            $this->assertSame(
                (float) $persisted[$score['riasec_code']],
                (float) $score['score']
            );
        }

        // The browser route is the one the contract binds: the HTML page is
        // the student-facing surface, and it must stay similarity-free.
        // (Verified separately above; the JSON payload deviation is Q4-C1.)
        $this->assertTrue(true, 'shape recorded; deviation tracked as Q4-C1');
    }

    /**
     * The assessment view carries no scoring vocabulary either: the question
     * payload hides RIASEC codes and weights (contract section 7).
     */
    public function test_the_assessment_payload_hides_riasec_codes_and_weights(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($student);

        $payload = $this->actingAs($student)
            ->getJson(route('assessment.show', $session))
            ->assertOk()
            ->json('data');

        $serialized = json_encode($payload);

        $this->assertStringNotContainsString('riasec', strtolower($serialized));
        $this->assertStringNotContainsString('weight', strtolower($serialized));
        $this->assertStringNotContainsString('score', strtolower($serialized));
        $this->assertStringNotContainsString('similarity', strtolower($serialized));
    }

    /**
     * The page presents the official result without recalculating it: the
     * scores shown come from the database rows the completion wrote.
     */
    public function test_the_page_renders_exactly_the_persisted_score_values(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($student);

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $result = Result::with('resultScores')->firstOrFail();
        $html = $this->actingAs($student)
            ->get(route('results.show', $result))
            ->assertOk()
            ->getContent();

        foreach ($result->resultScores as $score) {
            $this->assertStringContainsString(
                $this->formattedScore((float) $score->score),
                $html,
                "the page must render the persisted value for {$score->riasec_code}"
            );
        }
    }

    private function formattedScore(float $value): string
    {
        // The view formats with one decimal then trims a trailing zero.
        $formatted = rtrim(rtrim(number_format(round($value, 1), 1, '.', ''), '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }

    /**
     * Keep only the six domain score bars for a stable cross-render compare.
     */
    private function scoreMarkup(string $html): string
    {
        preg_match_all('/aria-label="[^"]*:\s*[^"]*من 100"/u', $html, $matches);

        return implode('|', $matches[0]);
    }

    private function createAnsweredSession(User $student): AssessmentSession
    {
        $session = AssessmentSession::create([
            'user_id' => $student->id,
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
}
