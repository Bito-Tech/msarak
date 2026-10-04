<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoInternalLinkingTest extends TestCase
{
    public function test_home_links_directly_to_the_public_guidance_hubs(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertIsString($html);

        $context = $this->extractNav($html, 'مسارات إرشادية مرتبطة');

        $this->assertStringContainsString('href="'.route('career-interests.index').'"', $context);
        $this->assertStringContainsString('href="'.route('major-choice.index').'"', $context);
        $this->assertStringContainsString('href="'.route('scientific-foundation.index').'"', $context);
    }

    public function test_specialization_catalog_has_visible_breadcrumb_and_contextual_guidance_links(): void
    {
        $response = $this->get(route('specializations.index'));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertIsString($html);

        $breadcrumb = $this->extractNav($html, 'مسار الصفحة');
        $context = $this->extractNav($html, 'مسارات إرشادية مرتبطة');

        $this->assertStringContainsString('href="'.route('home').'"', $breadcrumb);
        $this->assertStringContainsString('دليل التخصصات الجامعية', $breadcrumb);

        $this->assertStringContainsString('href="'.route('major-choice.index').'"', $context);
        $this->assertStringContainsString('href="'.route('career-interests.index').'"', $context);
        $this->assertStringContainsString('href="'.route('scientific-foundation.index').'"', $context);
    }

    public function test_specialization_detail_visible_breadcrumb_matches_the_public_hierarchy(): void
    {
        $response = $this->get(route('specializations.show', 'human_medicine'));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertIsString($html);

        $breadcrumb = $this->extractNav($html, 'مسار الصفحة');
        $context = $this->extractNav($html, 'مسارات إرشادية مرتبطة');

        $this->assertStringContainsString('href="'.route('home').'"', $breadcrumb);
        $this->assertStringContainsString('href="'.route('specializations.index').'"', $breadcrumb);
        $this->assertStringContainsString('الطب البشري', $breadcrumb);

        $this->assertStringContainsString('href="'.route('major-choice.index').'"', $context);
        $this->assertStringContainsString('href="'.route('career-interests.index').'"', $context);
    }

    public function test_all_public_guides_and_catalog_pages_expose_a_visible_breadcrumb(): void
    {
        $urls = [
            route('career-interests.index'),
            route('major-choice.index'),
            route('scientific-foundation.index'),
            route('specializations.index'),
            route('specializations.show', 'human_medicine'),
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);

            $response->assertOk();

            $html = $response->getContent();

            $this->assertIsString($html);
            $this->assertSame(
                1,
                substr_count($html, 'aria-label="مسار الصفحة"'),
                $url
            );
        }
    }

    public function test_footer_lists_each_public_guidance_destination_once(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertIsString($html);

        $footer = $this->extractNav($html, 'روابط التذييل');

        foreach ([
            route('home'),
            route('career-interests.index'),
            route('major-choice.index'),
            route('scientific-foundation.index'),
            route('specializations.index'),
        ] as $url) {
            $this->assertSame(
                1,
                substr_count($footer, 'href="'.$url.'"'),
                $url
            );
        }
    }

    private function extractNav(string $html, string $ariaLabel): string
    {
        preg_match(
            '/<nav[^>]*aria-label="'.preg_quote($ariaLabel, '/').'"[^>]*>(.*?)<\/nav>/s',
            $html,
            $matches
        );

        $this->assertArrayHasKey(1, $matches, 'Expected navigation: '.$ariaLabel);

        return $matches[1];
    }
}
