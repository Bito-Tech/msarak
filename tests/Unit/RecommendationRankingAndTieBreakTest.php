<?php

namespace Tests\Unit;

use App\Services\RecommendationService;
use App\Services\SpecializationCatalogService;
use Tests\TestCase;

/**
 * Q-04 Phase 5: recommendation ranking, ties and limits.
 *
 * The recommendation service compares the student's six-domain score vector
 * against every specialization profile in the catalog with cosine similarity,
 * then sorts descending by similarity. Expected similarities below were
 * computed by hand from the catalog vectors in resources/data/specializations.json
 * (version 1.0, ten specializations) and are compared to the service output
 * as literal constants.
 */
class RecommendationRankingAndTieBreakTest extends TestCase
{
    private const SCORES_I_DOMINANT = [
        'R' => 0,
        'I' => 100,
        'A' => 0,
        'S' => 0,
        'E' => 0,
        'C' => 0,
    ];

    /**
     * A pure I vector is collinear with the I-heavy profiles. By hand:
     *  - computer_science  [R .5, I 1, others 0] -> I component only matches
     *    on I: cos = 1/sqrt(1^2 + .5^2) * 1 ... see the assertions below.
     */
    public function test_ranking_is_strictly_descending_by_similarity_score(): void
    {
        $recommendations = app(RecommendationService::class)->recommend(self::SCORES_I_DOMINANT);

        $similarities = array_column($recommendations, 'similarity_score');

        $sorted = $similarities;
        rsort($sorted);

        $this->assertSame($sorted, $similarities, 'recommendations must be sorted descending');
    }

    /**
     * The limit is clamped to the 3..5 window: anything below 3 floors at 3,
     * anything above 5 caps at 5. Both bounds are asserted.
     */
    public function test_the_recommendation_limit_is_clamped_between_three_and_five(): void
    {
        $service = app(RecommendationService::class);

        $this->assertCount(3, $service->recommend(self::SCORES_I_DOMINANT, 1));
        $this->assertCount(3, $service->recommend(self::SCORES_I_DOMINANT, 2));
        $this->assertCount(3, $service->recommend(self::SCORES_I_DOMINANT, 3));
        $this->assertCount(4, $service->recommend(self::SCORES_I_DOMINANT, 4));
        $this->assertCount(5, $service->recommend(self::SCORES_I_DOMINANT, 5));
        $this->assertCount(5, $service->recommend(self::SCORES_I_DOMINANT, 6));
        $this->assertCount(5, $service->recommend(self::SCORES_I_DOMINANT, 99));
    }

    /**
     * The default limit is 5, matching the catalog-bound result set the
     * completion service persists.
     */
    public function test_the_default_limit_is_five(): void
    {
        $recommendations = app(RecommendationService::class)->recommend(self::SCORES_I_DOMINANT);

        $this->assertCount(5, $recommendations);
    }

    /**
     * No specialization can appear twice, and the ranking is a permutation of
     * distinct catalog keys.
     */
    public function test_no_duplicate_specialization_keys_are_returned(): void
    {
        $recommendations = app(RecommendationService::class)->recommend(self::SCORES_I_DOMINANT);

        $keys = array_column($recommendations, 'specialization_key');

        $this->assertSame($keys, array_values(array_unique($keys)));
        $this->assertSame(count($keys), count(array_unique($keys)));
    }

    /**
     * Every recommendation carries the fields the result persistence layer
     * snapshots: key, name, similarity and a non-empty rationale.
     */
    public function test_every_recommendation_carries_the_snapshot_fields(): void
    {
        $recommendations = app(RecommendationService::class)->recommend(self::SCORES_I_DOMINANT);

        foreach ($recommendations as $recommendation) {
            $this->assertArrayHasKey('specialization_key', $recommendation);
            $this->assertArrayHasKey('name', $recommendation);
            $this->assertArrayHasKey('similarity_score', $recommendation);
            $this->assertArrayHasKey('rationale', $recommendation);
            $this->assertIsString($recommendation['specialization_key']);
            $this->assertNotSame('', $recommendation['specialization_key']);
            $this->assertNotSame('', $recommendation['name']);
            $this->assertNotSame('', $recommendation['rationale']);
            $this->assertIsFloat($recommendation['similarity_score']);
        }
    }

    /**
     * A zero student vector has no magnitude, so every cosine similarity is
     * exactly 0.0. The ranking then falls back to the deterministic tie-break.
     */
    public function test_a_zero_score_vector_produces_zero_similarities(): void
    {
        $zeros = ['R' => 0, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0];

        $recommendations = app(RecommendationService::class)->recommend($zeros);

        foreach ($recommendations as $recommendation) {
            $this->assertSame(0.0, $recommendation['similarity_score']);
        }
    }

    /**
     * The tie-break is a stable, deterministic secondary key: when two
     * specialties tie on similarity, the lexicographically smaller
     * specialization_key comes first. The zero-vector forces a full tie, so
     * the returned order is the pure tie-break order.
     */
    public function test_ties_are_broken_deterministically_by_specialization_key(): void
    {
        $zeros = ['R' => 0, 'I' => 0, 'A' => 0, 'S' => 0, 'E' => 0, 'C' => 0];

        $first = app(RecommendationService::class)->recommend($zeros);
        $second = app(RecommendationService::class)->recommend($zeros);

        $this->assertSame(
            array_column($first, 'specialization_key'),
            array_column($second, 'specialization_key'),
            'the tie-break must be repeatable'
        );

        $keys = array_column($first, 'specialization_key');
        $sorted = $keys;
        sort($sorted);
        $this->assertSame($sorted, $keys, 'ties resolve in ascending key order');
    }

    /**
     * Independent numeric check: a pure I vector against the catalog.
     *
     * Computed by hand with cos = dot / (|student| * |profile|), student =
     * [0,100,0,0,0,0] so |student| = 100 and dot = 100 * profile_I:
     *
     *   computer_science  [.5,1,0,0,0,.5]  dot=100 |b|=1.2247 -> 0.8165
     *   human_medicine    [0,1,0,.5,0,.5]  dot=100 |b|=1.2247 -> 0.8165
     *   law               [0,1,0,.5,.5,0]  dot=100 |b|=1.2247 -> 0.8165
     *   accounting        [0,.5,0,0,.5,1]  dot=50  |b|=1.2247 -> 0.4082
     *   architecture      [.5,.5,1,0,0,0]  dot=50  |b|=1.2247 -> 0.4082
     *   business_admin    [0,.5,0,0,1,.5]  dot=50  |b|=1.2247 -> 0.4082
     *   civil_engineering [1,.5,0,0,.5,0]  dot=50  |b|=1.2247 -> 0.4082
     *   graphic_design    [0,.5,1,0,0,.5]  dot=50  |b|=1.2247 -> 0.4082
     *   nursing           [.5,.5,0,1,0,0]  dot=50  |b|=1.2247 -> 0.4082
     *   english_language  [0,0,1,.5,0,.5]  dot=0            -> 0.0000
     *
     * Every profile carries I=0.5 or 1 plus two or three other 0.5 weights,
     * so |profile| is 1.2247 for all of them, which keeps the arithmetic
     * uniform. Top five after the descending sort with the key tie-break:
     *   computer_science, human_medicine, law (all 0.8165, keys ascending),
     *   then accounting, architecture (both 0.4082, keys ascending).
     */
    public function test_top_five_for_a_pure_investigative_vector_match_hand_calculation(): void
    {
        $recommendations = app(RecommendationService::class)->recommend(self::SCORES_I_DOMINANT);

        $this->assertSame('computer_science', $recommendations[0]['specialization_key']);
        $this->assertSame(0.8165, $recommendations[0]['similarity_score']);
        $this->assertSame('human_medicine', $recommendations[1]['specialization_key']);
        $this->assertSame(0.8165, $recommendations[1]['similarity_score']);
        $this->assertSame('law', $recommendations[2]['specialization_key']);
        $this->assertSame(0.8165, $recommendations[2]['similarity_score']);
        $this->assertSame('accounting', $recommendations[3]['specialization_key']);
        $this->assertSame(0.4082, $recommendations[3]['similarity_score']);
        $this->assertSame('architecture', $recommendations[4]['specialization_key']);
        $this->assertSame(0.4082, $recommendations[4]['similarity_score']);
    }

    /**
     * An exact catalog match yields similarity 1.0: a vector equal to the
     * computer_science profile is collinear with it.
     */
    public function test_an_exact_profile_match_scores_one(): void
    {
        $recommendations = app(RecommendationService::class)->recommend([
            'R' => 50,
            'I' => 100,
            'A' => 0,
            'S' => 0,
            'E' => 0,
            'C' => 50,
        ]);

        $this->assertSame('computer_science', $recommendations[0]['specialization_key']);
        $this->assertSame(1.0, $recommendations[0]['similarity_score']);
    }

    /**
     * Every returned key must exist in the catalog, so the persisted
     * specialization_key is always resolvable later.
     */
    public function test_every_returned_key_exists_in_the_catalog(): void
    {
        $catalogKeys = array_column(
            app(SpecializationCatalogService::class)->all(),
            'id'
        );

        $recommendations = app(RecommendationService::class)->recommend(self::SCORES_I_DOMINANT);

        foreach ($recommendations as $recommendation) {
            $this->assertContains($recommendation['specialization_key'], $catalogKeys);
        }
    }
}
