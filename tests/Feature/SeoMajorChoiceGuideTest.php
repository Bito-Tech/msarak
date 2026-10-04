<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoMajorChoiceGuideTest extends TestCase
{
    public function test_major_choice_guide_is_public_indexable_and_search_focused(): void
    {
        $response = $this->get(route('major-choice.index'));

        $response
            ->assertOk()
            ->assertSee(
                '<title>كيف أختار تخصصي الجامعي؟ | دليل عملي للطلاب | مسارك</title>',
                false
            )
            ->assertSee('كيف أختار تخصصي الجامعي؟')
            ->assertSee('افهم نفسك')
            ->assertSee('افهم الخيارات')
            ->assertSee('قارن بوعي')
            ->assertSee('محتار أي تخصص أدخل؟')
            ->assertDontSee('<meta name="robots" content="noindex,follow">', false)
            ->assertSee(
                '<link rel="canonical" href="'.route('major-choice.index').'">',
                false
            )
            ->assertSee(
                '<meta property="og:image" content="'.url(config('seo.default_image')).'">',
                false
            );
    }

    public function test_major_choice_guide_links_to_interest_exploration_and_specializations(): void
    {
        $response = $this->get(route('major-choice.index'));

        $response
            ->assertOk()
            ->assertSee('href="'.route('career-interests.index').'"', false)
            ->assertSee('href="'.route('specializations.index').'"', false);
    }

    public function test_major_choice_guide_emits_webpage_image_and_two_level_breadcrumb_json_ld(): void
    {
        $response = $this->get(route('major-choice.index'));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertIsString($html);

        preg_match(
            '/<script type="application\\/ld\\+json">(.*?)<\\/script>/s',
            $html,
            $matches
        );

        $this->assertArrayHasKey(1, $matches);

        $decoded = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
        $graph = $decoded['@graph'] ?? [];

        $this->assertIsArray($graph);

        $webpage = collect($graph)->firstWhere('@type', 'WebPage');
        $image = collect($graph)->firstWhere('@type', 'ImageObject');
        $breadcrumbs = collect($graph)->firstWhere('@type', 'BreadcrumbList');

        $this->assertIsArray($webpage);
        $this->assertIsArray($image);
        $this->assertIsArray($breadcrumbs);

        $url = route('major-choice.index');

        $this->assertSame($url, $webpage['url']);
        $this->assertSame($url.'#primaryimage', $webpage['primaryImageOfPage']['@id']);
        $this->assertSame($url.'#breadcrumb', $webpage['breadcrumb']['@id']);
        $this->assertSame($url.'#primaryimage', $image['@id']);
        $this->assertSame(2, count($breadcrumbs['itemListElement']));
        $this->assertSame('الرئيسية', $breadcrumbs['itemListElement'][0]['name']);
        $this->assertSame('كيف أختار تخصصي الجامعي؟', $breadcrumbs['itemListElement'][1]['name']);
        $this->assertSame($url, $breadcrumbs['itemListElement'][1]['item']);
    }
}
