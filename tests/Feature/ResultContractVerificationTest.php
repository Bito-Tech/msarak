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
 * Q-04 Phase 13: contract verification between backend, controller, JSON,
 * Blade and documentation for the result and history surface.
 *
 * Each check is classified PASS / FAIL / AMBIGUOUS against the documented
 * contract in docs/02-system-design/04-system-contracts.md, sections 9, 10
 * and 16. Nothing is modified to make a check pass: deviations are recorded
 * as findings instead.
 */
class ResultContractVerificationTest extends TestCase
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
     * PASS — the completion envelope matches section 9 exactly.
     *
     * It returns data.session_id, data.status, data.result_id and
     * data.result_url, and the contract's "لا تعاد الدرجات من هذا الطلب" rule:
     * scores are not part of the completion payload.
     */
    public function test_contract_completion_envelope_carries_no_scores(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($student);

        $response = $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $response->assertJsonStructure([
            'data' => ['session_id', 'status', 'result_id', 'result_url'],
            'message',
        ]);

        $this->assertArrayNotHasKey('scores', $response->json('data'));
        $this->assertArrayNotHasKey('recommendations', $response->json('data'));

        $result = Result::firstOrFail();
        $this->assertSame(
            "/results/{$result->id}",
            $response->json('data.result_url')
        );
    }

    /**
     * AMBIGUOUS — section 9 documents "201 أول مرة أو 200 مكرر" for completion.
     *
     * The implementation returns 200 in both cases. This is a documented-vs-
     * actual deviation, recorded as a finding rather than silently aligned.
     */
    public function test_contract_completion_status_code_is_documented_as_201_first_time(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $session = $this->createAnsweredSession($student);

        $first = $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        $second = $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        // Actual: both 200. Documented: 201 then 200 (deviation, see Q4-C2).
        $this->assertSame(200, $first->status());
        $this->assertSame(200, $second->status());

        // The retry contract still holds regardless of the status code.
        $this->assertSame(
            $first->json('data.result_id'),
            $second->json('data.result_id')
        );
    }

    /**
     * PASS — the history list is newest-first and exposes the documented
     * result identity fields.
     */
    public function test_contract_history_is_newest_first_with_identity_fields(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $first = $this->completeFreshSession($student);
        $this->travel(1)->hour();
        $second = $this->completeFreshSession($student);

        $history = $this->actingAs($student)
            ->getJson(route('profile.results.index'))
            ->assertOk()
            ->json('data');

        $this->assertSame([$second->id, $first->id], collect($history)->pluck('result_id')->all());

        foreach ($history as $item) {
            $this->assertArrayHasKey('result_id', $item);
            $this->assertArrayHasKey('assessment_session_id', $item);
            $this->assertArrayHasKey('scoring_version', $item);
            $this->assertArrayHasKey('catalog_version', $item);
            $this->assertArrayHasKey('created_at', $item);
        }
    }

    /**
     * FAIL (documented gap) — section 10 requires the history rows to carry
     * `top_domains` and `recommendation_names`, but neither the JSON payload
     * nor the Blade view renders them. Recorded as a finding (Q4-C3), not
     * fixed, because adding computed summary fields is a product decision.
     */
    public function test_contract_history_summary_fields_are_absent_from_the_payload(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->completeFreshSession($student);

        $history = $this->actingAs($student)
            ->getJson(route('profile.results.index'))
            ->assertOk()
            ->json('data');

        $this->assertNotEmpty($history);

        foreach ($history as $item) {
            $this->assertArrayNotHasKey('top_domains', $item);
            $this->assertArrayNotHasKey('recommendation_names', $item);
        }

        // The Blade page does not render them either.
        $html = $this->actingAs($student)
            ->get(route('profile.results.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('top_domains', $html);
        $this->assertStringNotContainsString('recommendation_names', $html);
    }

    /**
     * FAIL (documented gap) — section 10 requires the result page to present
     * `top_domains` as "أعلى ثلاثة مع الحفاظ على التعادل". The view derives a
     * top set visually (badge + reading) but never emits a top_domains field
     * in the JSON payload. Recorded as a finding (Q4-C4).
     */
    public function test_contract_top_domains_is_absent_from_the_result_payload(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $result = $this->completeFreshSession($student);

        $payload = $this->actingAs($student)
            ->getJson(route('results.show', $result))
            ->assertOk()
            ->json('data');

        $this->assertArrayNotHasKey('top_domains', $payload);
        $this->assertArrayHasKey('scores', $payload);
        $this->assertArrayHasKey('recommendations', $payload);
    }

    /**
     * FAIL (documented gap) — "لا ترسل similarity_score إلى الواجهة" (§10) but
     * the JSON result payload exposes similarity_score on every recommendation
     * (ResultController::show, line ~111). The Blade page correctly omits it.
     * Recorded as a finding (Q4-C1).
     */
    public function test_contract_similarity_score_is_exposed_by_the_json_payload(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $result = $this->completeFreshSession($student);

        $payload = $this->actingAs($student)
            ->getJson(route('results.show', $result))
            ->assertOk()
            ->json('data');

        $this->assertNotEmpty($payload['recommendations']);

        foreach ($payload['recommendations'] as $recommendation) {
            $this->assertArrayHasKey('similarity_score', $recommendation);
        }

        // The browser surface is compliant: the rendered page has no similarity.
        $html = $this->actingAs($student)
            ->get(route('results.show', $result))
            ->assertOk()
            ->getContent();

        foreach ($payload['recommendations'] as $recommendation) {
            $this->assertStringNotContainsString(
                'similarity',
                strtolower($html)
            );
            break;
        }
    }

    /**
     * PASS — the result payload carries exactly the snapshot fields the
     * persistence layer wrote, with no server-side recomputation.
     */
    public function test_contract_result_payload_matches_the_persisted_rows(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $result = $this->completeFreshSession($student)->load(['resultScores', 'resultRecommendations']);

        $payload = $this->actingAs($student)
            ->getJson(route('results.show', $result))
            ->assertOk()
            ->json('data');

        $persistedScores = $result->resultScores->pluck('score', 'riasec_code')->all();
        foreach ($payload['scores'] as $score) {
            $this->assertSame((float) $persistedScores[$score['riasec_code']], (float) $score['score']);
        }

        $persistedRecs = $result->resultRecommendations->keyBy('specialization_key')->all();
        foreach ($payload['recommendations'] as $recommendation) {
            $persisted = $persistedRecs[$recommendation['specialization_key']];
            $this->assertSame($persisted->name_snapshot, $recommendation['name_snapshot']);
            $this->assertSame($persisted->display_order, $recommendation['display_order']);
            $this->assertSame((float) $persisted->similarity_score, (float) $recommendation['similarity_score']);
            $this->assertSame($persisted->rationale_snapshot, $recommendation['rationale_snapshot']);
        }
    }

    /**
     * PASS — display_order on the wire is 1..5 and matches the stored rows,
     * honoring the "3–5 تخصصات" band of section 10.
     */
    public function test_contract_recommendation_count_and_order_are_within_the_documented_band(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $result = $this->completeFreshSession($student);

        $payload = $this->actingAs($student)
            ->getJson(route('results.show', $result))
            ->assertOk()
            ->json('data.recommendations');

        $count = count($payload);
        $this->assertGreaterThanOrEqual(3, $count);
        $this->assertLessThanOrEqual(5, $count);

        $orders = array_column($payload, 'display_order');
        $this->assertSame(range(1, $count), $orders);
    }

    /**
     * PASS — versions travel with the result: scoring_version 1.2 and the
     * catalog version are present on both the JSON payload and the page.
     */
    public function test_contract_version_fields_are_present_on_both_surfaces(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $result = $this->completeFreshSession($student);

        $payload = $this->actingAs($student)
            ->getJson(route('results.show', $result))
            ->assertOk()
            ->json('data');

        $this->assertSame('1.2', $payload['scoring_version']);
        $this->assertNotEmpty($payload['catalog_version']);

        $this->actingAs($student)
            ->get(route('results.show', $result))
            ->assertOk()
            ->assertSee('1.2', false)
            ->assertSee($payload['catalog_version'], false);
    }

    private function createAnsweredSession(User $student, ?AssessmentVersion $version = null): AssessmentSession
    {
        $version = $version ?? $this->version;

        $session = AssessmentSession::create([
            'user_id' => $student->id,
            'assessment_version_id' => $version->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        foreach ($version->questions as $question) {
            Answer::create([
                'assessment_session_id' => $session->id,
                'question_id' => $question->id,
                'primary_option_id' => $question->questionOptions->first()->id,
                'response_type' => 'option',
            ]);
        }

        return $session;
    }

    private function completeFreshSession(User $student): Result
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

        $session = $this->createAnsweredSession($student, $version);

        $this->actingAs($student)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertOk();

        return Result::latest('id')->firstOrFail();
    }
}
