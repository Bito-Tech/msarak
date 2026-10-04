@php
    $seoHomeUrl = route('home');
    $seoOrganizationId = $seoHomeUrl.'#organization';
    $seoWebsiteId = $seoHomeUrl.'#website';
    $seoWebPageId = $seoCanonical.'#webpage';
    $seoImageObjectId = $seoCanonical.'#primaryimage';

    $seoGraph = [
        [
            '@type' => 'Organization',
            '@id' => $seoOrganizationId,
            'name' => (string) config('seo.organization_name', 'فريق بيتو تك'),
            'url' => $seoHomeUrl,
            'sameAs' => array_values((array) config('seo.organization_same_as', [])),
        ],
        [
            '@type' => 'WebSite',
            '@id' => $seoWebsiteId,
            'url' => $seoHomeUrl,
            'name' => $seoSiteName,
            'description' => (string) config('seo.default_description', ''),
            'inLanguage' => (string) config('seo.language', 'ar'),
            'publisher' => [
                '@id' => $seoOrganizationId,
            ],
        ],
        [
            '@type' => request()->routeIs('specializations.index') ? 'CollectionPage' : 'WebPage',
            '@id' => $seoWebPageId,
            'url' => $seoCanonical,
            'name' => $seoTitle,
            'description' => $seoDescription,
            'inLanguage' => (string) config('seo.language', 'ar'),
            'isPartOf' => [
                '@id' => $seoWebsiteId,
            ],
            'publisher' => [
                '@id' => $seoOrganizationId,
            ],
            'primaryImageOfPage' => [
                '@id' => $seoImageObjectId,
            ],
        ],
        [
            '@type' => 'ImageObject',
            '@id' => $seoImageObjectId,
            'url' => $seoImage,
            'contentUrl' => $seoImage,
            'width' => (int) $seoImageWidth,
            'height' => (int) $seoImageHeight,
            'encodingFormat' => $seoImageType,
            'caption' => $seoImageAlt,
        ],
    ];

    if (request()->routeIs('career-interests.index')) {
        $seoBreadcrumbId = $seoCanonical.'#breadcrumb';
        $seoGraph[2]['breadcrumb'] = [
            '@id' => $seoBreadcrumbId,
        ];

        $seoGraph[] = [
            '@type' => 'BreadcrumbList',
            '@id' => $seoBreadcrumbId,
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'الرئيسية',
                    'item' => $seoHomeUrl,
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'اختبار الميول المهنية',
                    'item' => $seoCanonical,
                ],
            ],
        ];
    }

    if (request()->routeIs('specializations.index')) {
        $seoBreadcrumbId = $seoCanonical.'#breadcrumb';
        $seoGraph[2]['breadcrumb'] = [
            '@id' => $seoBreadcrumbId,
        ];

        $seoGraph[] = [
            '@type' => 'BreadcrumbList',
            '@id' => $seoBreadcrumbId,
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'الرئيسية',
                    'item' => $seoHomeUrl,
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'دليل التخصصات الجامعية',
                    'item' => $seoCanonical,
                ],
            ],
        ];
    }

    if (request()->routeIs('specializations.show')) {
        $seoSpecializationName = (string) ($specialization['name'] ?? 'تخصص جامعي');
        $seoCatalogUrl = route('specializations.index');
        $seoBreadcrumbId = $seoCanonical.'#breadcrumb';

        $seoGraph[2]['breadcrumb'] = [
            '@id' => $seoBreadcrumbId,
        ];

        $seoGraph[] = [
            '@type' => 'BreadcrumbList',
            '@id' => $seoBreadcrumbId,
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'الرئيسية',
                    'item' => $seoHomeUrl,
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'دليل التخصصات الجامعية',
                    'item' => $seoCatalogUrl,
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $seoSpecializationName,
                    'item' => $seoCanonical,
                ],
            ],
        ];
    }

    $seoJsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => $seoGraph,
    ];
@endphp
<script type="application/ld+json">{!! json_encode(
    $seoJsonLd,
    JSON_UNESCAPED_UNICODE
    | JSON_UNESCAPED_SLASHES
    | JSON_HEX_TAG
    | JSON_HEX_AMP
    | JSON_HEX_APOS
    | JSON_HEX_QUOT
) !!}</script>
