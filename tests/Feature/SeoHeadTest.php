<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoHeadTest extends TestCase
{
    public function test_home_page_has_one_complete_central_seo_head(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertIsString($html);
        $this->assertSame(1, substr_count($html, '<title>'));
        $this->assertSame(1, substr_count($html, '<meta name="description"'));
        $this->assertSame(1, substr_count($html, '<link rel="canonical"'));
        $this->assertSame(1, substr_count($html, '<meta property="og:title"'));
        $this->assertSame(1, substr_count($html, '<meta property="og:description"'));
        $this->assertSame(1, substr_count($html, '<meta property="og:url"'));
        $this->assertSame(1, substr_count($html, '<meta property="og:type"'));
        $this->assertSame(1, substr_count($html, '<meta name="twitter:card"'));
        $this->assertSame(1, substr_count($html, '<meta name="twitter:title"'));
        $this->assertSame(1, substr_count($html, '<meta name="twitter:description"'));

        $response->assertSee('<title>الرئيسية | مسارك</title>', false);
        $response->assertSee(
            '<meta name="description" content="'.config('seo.default_description').'">',
            false
        );
        $response->assertSee(
            '<link rel="canonical" href="'.route('home').'">',
            false
        );
        $response->assertSee(
            '<meta property="og:url" content="'.route('home').'">',
            false
        );
        $response->assertSee(
            '<meta property="og:site_name" content="مسارك">',
            false
        );
        $response->assertSee(
            '<meta property="og:locale" content="ar_YE">',
            false
        );
        $response->assertSee(
            '<meta name="twitter:card" content="summary">',
            false
        );
    }

    public function test_public_page_metadata_tracks_the_existing_page_title_and_absolute_url(): void
    {
        $url = route('specializations.show', 'human_medicine');
        $response = $this->get($url);

        $response->assertOk();

        $html = $response->getContent();

        $this->assertIsString($html);
        $this->assertStringContainsString('<title>الطب البشري | مسارك</title>', $html);

        preg_match('/<link rel="canonical" href="([^"]+)">/', $html, $canonicalMatch);
        preg_match('/<meta property="og:url" content="([^"]+)">/', $html, $ogUrlMatch);

        $canonical = html_entity_decode($canonicalMatch[1] ?? '', ENT_QUOTES | ENT_HTML5);
        $ogUrl = html_entity_decode($ogUrlMatch[1] ?? '', ENT_QUOTES | ENT_HTML5);

        $this->assertSame($url, $canonical);
        $this->assertSame($url, $ogUrl);
        $this->assertNotFalse(filter_var($canonical, FILTER_VALIDATE_URL));
        $this->assertNotFalse(filter_var($ogUrl, FILTER_VALIDATE_URL));
    }



    public function test_comparison_page_is_noindex_and_keeps_a_valid_parameterized_canonical(): void
    {
        $url = route('specializations.compare', [
            'first' => 'human_medicine',
            'second' => 'computer_science',
        ]);

        $response = $this->get($url);

        $response->assertOk();

        $html = $response->getContent();

        $this->assertIsString($html);
        $this->assertStringContainsString(
            '<meta name="robots" content="noindex,follow">',
            $html
        );

        preg_match('/<link rel="canonical" href="([^"]+)">/', $html, $canonicalMatch);
        preg_match('/<meta property="og:url" content="([^"]+)">/', $html, $ogUrlMatch);

        $canonical = html_entity_decode($canonicalMatch[1] ?? '', ENT_QUOTES | ENT_HTML5);
        $ogUrl = html_entity_decode($ogUrlMatch[1] ?? '', ENT_QUOTES | ENT_HTML5);

        $this->assertSame($url, $canonical);
        $this->assertSame($url, $ogUrl);
        $this->assertStringContainsString('first=human_medicine', $canonical);
        $this->assertStringContainsString('second=computer_science', $canonical);
    }

    public function test_default_social_image_tags_are_not_emitted_before_the_og_image_stage(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertDontSee('<meta property="og:image"', false)
            ->assertDontSee('<meta name="twitter:image"', false);
    }
}
