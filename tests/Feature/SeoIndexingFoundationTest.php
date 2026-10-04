<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoIndexingFoundationTest extends TestCase
{
    public function test_robots_file_allows_public_crawling_and_declares_the_sitemap(): void
    {
        $path = public_path('robots.txt');

        $this->assertFileExists($path);

        $robots = (string) file_get_contents($path);

        $this->assertStringContainsString("User-agent: *\nAllow: /", $robots);
        $this->assertStringContainsString("User-agent: OAI-SearchBot\nAllow: /", $robots);
        $this->assertStringContainsString(
            'Sitemap: https://masarak.42web.io/sitemap.xml',
            $robots
        );
    }

    public function test_sitemap_contains_only_the_public_indexable_surface(): void
    {
        $response = $this->get(route('sitemap'));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $xml = $response->getContent();

        $this->assertIsString($xml);
        $this->assertStringContainsString(
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
            $xml
        );

        preg_match_all('/<loc>(.*?)<\/loc>/', $xml, $matches);
        $locations = $matches[1] ?? [];

        $this->assertCount(14, $locations);
        $this->assertCount(14, array_unique($locations));

        $this->assertContains(route('home'), $locations);
        $this->assertContains(route('career-interests.index'), $locations);
        $this->assertContains(route('major-choice.index'), $locations);
        $this->assertContains(route('specializations.index'), $locations);
        $this->assertContains(route('specializations.show', 'computer_science'), $locations);
        $this->assertContains(route('specializations.show', 'human_medicine'), $locations);

        foreach ($locations as $location) {
            $this->assertStringNotContainsString('/admin', $location);
            $this->assertStringNotContainsString('/profile', $location);
            $this->assertStringNotContainsString('/results/', $location);
            $this->assertStringNotContainsString('/assessment', $location);
            $this->assertStringNotContainsString('/login', $location);
            $this->assertStringNotContainsString('/register', $location);
        }
    }
}
