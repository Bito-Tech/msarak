<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoScientificFoundationTest extends TestCase
{
    public function test_scientific_foundation_is_public_indexable_and_clear_about_scope(): void
    {
        $response = $this->get(route('scientific-foundation.index'));

        $response
            ->assertOk()
            ->assertSee(
                '<title>الأساس العلمي لاختبار الميول المهنية | مسارك</title>',
                false
            )
            ->assertSee('الأساس العلمي لاختبار الميول المهنية في مسارك')
            ->assertSee('نموذج هولاند RIASEC')
            ->assertSee('SCCT')
            ->assertSee('SEVT')
            ->assertSee('لا يقيس الذكاء')
            ->assertSee('لا يشخّص الصحة النفسية أو الشخصية الشاملة')
            ->assertSee('لا تُقدَّم الدرجات بوصفها مقارنة بمعيار وطني يمني غير متاح للمشروع')
            ->assertDontSee('<meta name="robots" content="noindex,follow">', false)
            ->assertSee(
                '<link rel="canonical" href="'.route('scientific-foundation.index').'">',
                false
            )
            ->assertSee(
                '<meta property="og:image" content="'.url(config('seo.default_image')).'">',
                false
            );
    }

    public function test_scientific_foundation_uses_approved_riasec_labels_and_links_to_user_journey(): void
    {
        $response = $this->get(route('scientific-foundation.index'));

        $response
            ->assertOk()
            ->assertSee('عملي/تطبيقي')
            ->assertSee('بحثي/تحليلي')
            ->assertSee('فني/إبداعي')
            ->assertSee('اجتماعي/مساند')
            ->assertSee('مبادر/تأثيري')
            ->assertSee('تنظيمي/إجرائي')
            ->assertSee('href="'.route('career-interests.index').'"', false)
            ->assertSee('href="'.route('major-choice.index').'"', false)
            ->assertSee('https://www.onetcenter.org/reports/IP_Manual.html', false)
            ->assertSee('https://doi.org/10.1006/jvbe.1994.1027', false)
            ->assertDontSee('https://doi.org/10.1016/0001-8791(94)90026-4', false);
    }

    public function test_existing_public_guides_link_to_scientific_foundation(): void
    {
        $url = route('scientific-foundation.index');

        $this->get(route('career-interests.index'))
            ->assertOk()
            ->assertSee('href="'.$url.'"', false);

        $this->get(route('major-choice.index'))
            ->assertOk()
            ->assertSee('href="'.$url.'"', false);
    }

    public function test_scientific_foundation_emits_webpage_image_and_two_level_breadcrumb_json_ld(): void
    {
        $response = $this->get(route('scientific-foundation.index'));

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

        $url = route('scientific-foundation.index');

        $this->assertSame($url, $webpage['url']);
        $this->assertSame($url.'#primaryimage', $webpage['primaryImageOfPage']['@id']);
        $this->assertSame($url.'#breadcrumb', $webpage['breadcrumb']['@id']);
        $this->assertSame($url.'#primaryimage', $image['@id']);
        $this->assertSame(2, count($breadcrumbs['itemListElement']));
        $this->assertSame('الرئيسية', $breadcrumbs['itemListElement'][0]['name']);
        $this->assertSame('الأساس العلمي لمسارك', $breadcrumbs['itemListElement'][1]['name']);
        $this->assertSame($url, $breadcrumbs['itemListElement'][1]['item']);
    }
}
