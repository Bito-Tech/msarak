<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoStructuredDataTest extends TestCase
{
    public function test_home_page_emits_valid_site_organization_and_webpage_json_ld(): void
    {
        $graph = $this->jsonLdGraphFor(route('home'));

        $this->assertSame(
            ['Organization', 'WebSite', 'WebPage'],
            array_column($graph, '@type')
        );

        $organization = $this->nodeOfType($graph, 'Organization');
        $website = $this->nodeOfType($graph, 'WebSite');
        $webpage = $this->nodeOfType($graph, 'WebPage');

        $this->assertSame('فريق بيتو تك', $organization['name']);
        $this->assertSame(route('home'), $organization['url']);
        $this->assertContains('https://github.com/Bito-Tech', $organization['sameAs']);
        $this->assertContains('https://www.linkedin.com/company/bito-tech', $organization['sameAs']);

        $this->assertSame('مسارك', $website['name']);
        $this->assertSame(route('home'), $website['url']);
        $this->assertSame('ar', $website['inLanguage']);

        $this->assertSame(route('home'), $webpage['url']);
        $this->assertSame('ar', $webpage['inLanguage']);
        $this->assertSame(route('home').'#website', $webpage['isPartOf']['@id']);
    }

    public function test_specialization_catalog_emits_collection_page_and_two_level_breadcrumbs(): void
    {
        $url = route('specializations.index');
        $graph = $this->jsonLdGraphFor($url);

        $collection = $this->nodeOfType($graph, 'CollectionPage');
        $breadcrumbs = $this->nodeOfType($graph, 'BreadcrumbList');

        $this->assertSame($url, $collection['url']);
        $this->assertSame($url.'#breadcrumb', $collection['breadcrumb']['@id']);
        $this->assertSame(2, count($breadcrumbs['itemListElement']));
        $this->assertSame('الرئيسية', $breadcrumbs['itemListElement'][0]['name']);
        $this->assertSame(route('home'), $breadcrumbs['itemListElement'][0]['item']);
        $this->assertSame('دليل التخصصات الجامعية', $breadcrumbs['itemListElement'][1]['name']);
        $this->assertSame($url, $breadcrumbs['itemListElement'][1]['item']);
    }

    public function test_specialization_page_emits_three_level_breadcrumbs_with_current_specialization(): void
    {
        $url = route('specializations.show', 'human_medicine');
        $graph = $this->jsonLdGraphFor($url);

        $webpage = $this->nodeOfType($graph, 'WebPage');
        $breadcrumbs = $this->nodeOfType($graph, 'BreadcrumbList');

        $this->assertSame($url, $webpage['url']);
        $this->assertSame($url.'#breadcrumb', $webpage['breadcrumb']['@id']);
        $this->assertSame(3, count($breadcrumbs['itemListElement']));
        $this->assertSame('الطب البشري', $breadcrumbs['itemListElement'][2]['name']);
        $this->assertSame($url, $breadcrumbs['itemListElement'][2]['item']);
    }

    public function test_noindex_and_private_pages_do_not_emit_public_json_ld_or_default_og_image(): void
    {
        $urls = [
            route('login'),
            route('register'),
            route('password.request'),
            route('specializations.compare', [
                'first' => 'human_medicine',
                'second' => 'computer_science',
            ]),
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);

            $response->assertOk();
            $response->assertDontSee('<script type="application/ld+json">', false);
            $response->assertDontSee('<meta property="og:image"', false);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function jsonLdGraphFor(string $url): array
    {
        $response = $this->get($url);

        $response->assertOk();

        $html = $response->getContent();

        $this->assertIsString($html);
        $this->assertSame(1, substr_count($html, '<script type="application/ld+json">'));

        preg_match(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $html,
            $matches
        );

        $this->assertArrayHasKey(1, $matches);

        $decoded = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('https://schema.org', $decoded['@context'] ?? null);
        $this->assertIsArray($decoded['@graph'] ?? null);

        return $decoded['@graph'];
    }

    /**
     * @param  array<int, array<string, mixed>>  $graph
     * @return array<string, mixed>
     */
    private function nodeOfType(array $graph, string $type): array
    {
        foreach ($graph as $node) {
            if (($node['@type'] ?? null) === $type) {
                return $node;
            }
        }

        $this->fail('Missing JSON-LD node of type '.$type);
    }
}
