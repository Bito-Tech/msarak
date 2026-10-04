<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoCareerInterestsPageTest extends TestCase
{
    public function test_career_interests_page_is_public_indexable_and_search_focused(): void
    {
        $response = $this->get(route('career-interests.index'));

        $response
            ->assertOk()
            ->assertSee(
                '<title>اختبار الميول المهنية بالعربي | اكتشف ميولك | مسارك</title>',
                false
            )
            ->assertSee('اختبار الميول المهنية: ابدأ بفهم ما يجذبك')
            ->assertSee('نموذج هولاند RIASEC')
            ->assertSee('عملي/تطبيقي')
            ->assertSee('بحثي/تحليلي')
            ->assertSee('فني/إبداعي')
            ->assertSee('اجتماعي/مساند')
            ->assertSee('مبادر/تأثيري')
            ->assertSee('تنظيمي/إجرائي')
            ->assertSee('ليس لاختبار الشخصية أو القدرات')
            ->assertDontSee('المغامر')
            ->assertDontSee('التقليدي')
            ->assertDontSee('<meta name="robots" content="noindex,follow">', false)
            ->assertSee(
                '<link rel="canonical" href="'.route('career-interests.index').'">',
                false
            )
            ->assertSee(
                '<meta property="og:image" content="'.url(config('seo.default_image')).'">',
                false
            );
    }

    public function test_guest_can_move_from_the_public_page_to_registration_or_specializations(): void
    {
        $response = $this->get(route('career-interests.index'));

        $response
            ->assertOk()
            ->assertSee('href="'.route('register').'"', false)
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('href="'.route('specializations.index').'"', false);
    }

    public function test_career_interests_page_emits_webpage_image_and_two_level_breadcrumb_json_ld(): void
    {
        $response = $this->get(route('career-interests.index'));

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

        $url = route('career-interests.index');

        $this->assertSame($url, $webpage['url']);
        $this->assertSame($url.'#primaryimage', $webpage['primaryImageOfPage']['@id']);
        $this->assertSame($url.'#breadcrumb', $webpage['breadcrumb']['@id']);
        $this->assertSame($url.'#primaryimage', $image['@id']);
        $this->assertSame(2, count($breadcrumbs['itemListElement']));
        $this->assertSame('الرئيسية', $breadcrumbs['itemListElement'][0]['name']);
        $this->assertSame('اختبار الميول المهنية', $breadcrumbs['itemListElement'][1]['name']);
        $this->assertSame($url, $breadcrumbs['itemListElement'][1]['item']);
    }
}
