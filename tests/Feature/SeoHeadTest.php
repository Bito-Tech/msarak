<?php

namespace Tests\Feature;

use App\Services\SpecializationCatalogService;
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

        $response->assertSee(
            '<title>اكتشف ميولك واختر تخصصك الجامعي بوعي | مسارك</title>',
            false
        );
        $response->assertSee(
            '<meta name="description" content="اكتشف ميولك المهنية مع مسارك، واستكشف التخصصات الجامعية وطبيعة الدراسة والمهارات والمسارات المهنية لتبدأ قرارك بعد الثانوية بوعي أكبر.">',
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
            '<meta name="twitter:card" content="summary_large_image">',
            false
        );
        $response->assertSee(
            '<meta property="og:image" content="'.url(config('seo.default_image')).'">',
            false
        );
        $response->assertSee(
            '<meta property="og:image:alt" content="مسارك — التوجيه الأكاديمي والمهني">',
            false
        );
        $response->assertSee(
            '<meta name="twitter:image" content="'.url(config('seo.default_image')).'">',
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
        $this->assertStringContainsString(
            '<title>تخصص الطب البشري | الدراسة والمهارات والمسارات المهنية | مسارك</title>',
            $html
        );

        preg_match('/<link rel="canonical" href="([^"]+)">/', $html, $canonicalMatch);
        preg_match('/<meta property="og:url" content="([^"]+)">/', $html, $ogUrlMatch);

        $canonical = html_entity_decode($canonicalMatch[1] ?? '', ENT_QUOTES | ENT_HTML5);
        $ogUrl = html_entity_decode($ogUrlMatch[1] ?? '', ENT_QUOTES | ENT_HTML5);

        $this->assertSame($url, $canonical);
        $this->assertSame($url, $ogUrl);
        $this->assertNotFalse(filter_var($canonical, FILTER_VALIDATE_URL));
        $this->assertNotFalse(filter_var($ogUrl, FILTER_VALIDATE_URL));
    }




    public function test_specialization_catalog_has_search_focused_unique_metadata(): void
    {
        $response = $this->get(route('specializations.index'));

        $response
            ->assertOk()
            ->assertSee('<title>دليل التخصصات الجامعية | مسارك</title>', false)
            ->assertSee(
                '<meta name="description" content="تصفح دليل التخصصات الجامعية في مسارك، وتعرّف إلى طبيعة الدراسة والمهارات والأنشطة والمسارات المهنية لكل تخصص قبل اتخاذ قرارك.">',
                false
            )
            ->assertDontSee(config('seo.default_description'), false);
    }

    public function test_every_specialization_page_has_specific_title_and_bounded_description(): void
    {
        $catalog = app(SpecializationCatalogService::class);

        foreach ($catalog->all() as $specialization) {
            $url = route('specializations.show', $specialization['id']);
            $response = $this->get($url);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertIsString($html);
            $this->assertStringContainsString(
                '<title>تخصص '.$specialization['name'].' | الدراسة والمهارات والمسارات المهنية | مسارك</title>',
                $html
            );

            preg_match('/<meta name="description" content="([^"]*)">/', $html, $descriptionMatch);
            $description = html_entity_decode($descriptionMatch[1] ?? '', ENT_QUOTES | ENT_HTML5);

            $this->assertNotSame('', $description);
            $this->assertNotSame(config('seo.default_description'), $description);
            $this->assertStringStartsWith('تعرّف إلى تخصص '.$specialization['name'].':', $description);
            $this->assertLessThanOrEqual(155, mb_strlen($description));

            preg_match('/<link rel="canonical" href="([^"]+)">/', $html, $canonicalMatch);
            $canonical = html_entity_decode($canonicalMatch[1] ?? '', ENT_QUOTES | ENT_HTML5);

            $this->assertSame($url, $canonical);
        }
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
        $this->assertStringNotContainsString('&amp;amp;', $html);
        $this->assertStringContainsString('first=human_medicine', $canonical);
        $this->assertStringContainsString('second=computer_science', $canonical);
    }

    public function test_public_pages_emit_one_absolute_social_image_contract(): void
    {
        $urls = [
            route('home'),
            route('specializations.index'),
            route('specializations.show', 'human_medicine'),
        ];

        $expectedImage = url(config('seo.default_image'));

        foreach ($urls as $url) {
            $response = $this->get($url);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertIsString($html);
            $this->assertSame(1, substr_count($html, '<meta property="og:image"'));
            $this->assertSame(1, substr_count($html, '<meta property="og:image:alt"'));
            $this->assertSame(1, substr_count($html, '<meta name="twitter:image"'));
            $this->assertSame(1, substr_count($html, '<meta name="twitter:image:alt"'));
            $this->assertStringContainsString(
                '<meta property="og:image" content="'.$expectedImage.'">',
                $html
            );
            $this->assertNotFalse(filter_var($expectedImage, FILTER_VALIDATE_URL));
        }
    }
}
