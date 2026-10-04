<?php

namespace Tests\Feature;

use App\Models\AssessmentSession;
use App\Models\AssessmentVersion;
use App\Models\Result;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoNoIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_account_and_password_pages_are_noindex_follow(): void
    {
        $urls = [
            route('login'),
            route('register'),
            route('password.request'),
            route('password.reset', ['token' => 'test-token']),
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertIsString($html);
            $this->assertSame(
                1,
                substr_count($html, '<meta name="robots" content="noindex,follow">'),
                $url
            );
        }
    }

    public function test_student_private_pages_are_noindex_follow(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $version = AssessmentVersion::create([
            'version_number' => 1,
            'status' => 'active',
            'published_at' => now(),
        ]);

        $session = AssessmentSession::create([
            'user_id' => $student->id,
            'assessment_version_id' => $version->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $result = Result::create([
            'assessment_session_id' => $session->id,
            'catalog_version' => 'v1',
            'scoring_version' => 'v1.2',
        ]);

        $urls = [
            route('profile.show'),
            route('profile.results.index'),
            route('assessment.intro'),
            route('assessment.show', $session),
            route('results.show', $result),
        ];

        foreach ($urls as $url) {
            $response = $this->actingAs($student)->get($url);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertIsString($html);
            $this->assertSame(
                1,
                substr_count($html, '<meta name="robots" content="noindex,follow">'),
                $url
            );
        }
    }

    public function test_admin_pages_are_noindex_follow(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $urls = [
            route('admin.assessment-versions.index'),
            route('admin.statistics.index'),
        ];

        foreach ($urls as $url) {
            $response = $this->actingAs($admin)->get($url);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertIsString($html);
            $this->assertSame(
                1,
                substr_count($html, '<meta name="robots" content="noindex,follow">'),
                $url
            );
        }
    }

    public function test_public_indexable_pages_do_not_inherit_private_noindex_policy(): void
    {
        $urls = [
            route('home'),
            route('career-interests.index'),
            route('major-choice.index'),
            route('specializations.index'),
            route('specializations.show', 'human_medicine'),
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);

            $response->assertOk();
            $response->assertDontSee(
                '<meta name="robots" content="noindex,follow">',
                false
            );
        }
    }

    public function test_comparison_page_keeps_its_explicit_noindex_policy(): void
    {
        $response = $this->get(route('specializations.compare', [
            'first' => 'human_medicine',
            'second' => 'computer_science',
        ]));

        $response
            ->assertOk()
            ->assertSee(
                '<meta name="robots" content="noindex,follow">',
                false
            );
    }
}
