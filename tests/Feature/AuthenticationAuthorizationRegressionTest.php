<?php

namespace Tests\Feature;

use App\Models\AssessmentSession;
use App\Models\AssessmentVersion;
use App\Models\Result;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Q-04 Phase 11: authentication and authorization regression.
 *
 * Q-04 adds no route and changes no middleware, so this file proves the
 * existing security matrix is intact around the result surface: the role
 * gate still separates student and admin, guests are still unauthenticated,
 * and the routes registered for the assessment and result flows still carry
 * the auth and role middleware the contract requires.
 */
class AuthenticationAuthorizationRegressionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every student-facing assessment and result route is protected by both
     * the auth and the student-role middleware.
     */
    public function test_student_routes_keep_their_auth_and_role_middleware(): void
    {
        $protected = [
            'assessment.intro',
            'assessment.sessions.store',
            'assessment.show',
            'assessment.answers.update',
            'assessment.sessions.complete',
            'profile.show',
            'profile.results.index',
            'results.show',
        ];

        foreach ($protected as $name) {
            $this->assertTrue(Route::has($name), "route {$name} must exist");

            $middleware = collect(Route::getRoutes()->getByName($name)->gatherMiddleware())
                ->implode(',');

            $this->assertStringContainsString('auth', $middleware, "route {$name} lost its auth middleware");
            $this->assertStringContainsString('role:student', $middleware, "route {$name} lost its role middleware");
        }
    }

    /**
     * Admin routes keep the admin role gate and never expose the student
     * assessment surface.
     */
    public function test_admin_routes_keep_the_admin_role_gate(): void
    {
        $adminRoutes = [
            'admin.assessment-versions.store',
            'admin.assessment-versions.publish',
            'admin.statistics.index',
        ];

        foreach ($adminRoutes as $name) {
            $this->assertTrue(Route::has($name), "route {$name} must exist");

            $middleware = collect(Route::getRoutes()->getByName($name)->gatherMiddleware())
                ->implode(',');

            $this->assertStringContainsString('auth', $middleware);
            $this->assertStringContainsString('role:admin', $middleware);
        }
    }

    /**
     * A student cannot reach the admin surface; the role gate refuses with
     * 403 and no admin payload is produced.
     */
    public function test_a_student_is_forbidden_from_the_admin_surface(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->getJson(route('admin.statistics.index'))
            ->assertStatus(403);
    }

    /**
     * An admin cannot start or resume an assessment: the student role is the
     * only one admitted to the journey.
     */
    public function test_an_admin_is_forbidden_from_starting_an_assessment(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->postJson(route('assessment.sessions.store'))
            ->assertStatus(403);
    }

    /**
     * A guest hitting the student routes gets 401, never the payload.
     */
    public function test_guests_are_unauthenticated_on_student_routes(): void
    {
        $this->getJson(route('assessment.intro'))->assertStatus(401);
        $this->postJson(route('assessment.sessions.store'))->assertStatus(401);
        $this->getJson(route('profile.show'))->assertStatus(401);
        $this->getJson(route('profile.results.index'))->assertStatus(401);
        $this->getJson(route('results.show', 1))->assertStatus(401);
    }

    /**
     * The public catalog stays public: no auth and no role required, so the
     * boundary is not over-broad.
     */
    public function test_the_specialization_catalog_stays_public(): void
    {
        $this->get(route('specializations.index'))->assertOk();
        $this->get(route('specializations.compare', ['first' => 'computer_science', 'second' => 'law']))
            ->assertOk();
        $this->get(route('specializations.show', 'computer_science'))->assertOk();
    }

    /**
     * Ownership enforcement survives an authenticated-but-wrong-user attempt
     * on the completion endpoint: no result is created for the intruder.
     */
    public function test_authorization_failure_creates_no_side_effects(): void
    {
        $owner = User::factory()->create(['role' => 'student']);
        $intruder = User::factory()->create(['role' => 'student']);

        $version = AssessmentVersion::create([
            'version_number' => 1,
            'status' => 'active',
            'published_at' => now(),
        ]);

        $session = AssessmentSession::create([
            'user_id' => $owner->id,
            'assessment_version_id' => $version->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $this->actingAs($intruder)
            ->postJson(route('assessment.sessions.complete', $session))
            ->assertStatus(404);

        $this->assertSame(0, Result::count());
        $this->assertSame(0, $session->result()->count());
    }
}
