<?php

namespace App\Http\Controllers;

use App\Services\SpecializationCatalogService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(SpecializationCatalogService $catalog): Response
    {
        $urls = [
            route('home'),
            route('career-interests.index'),
            route('major-choice.index'),
            route('scientific-foundation.index'),
            route('specializations.index'),
        ];

        foreach ($catalog->all() as $specialization) {
            $urls[] = route('specializations.show', [
                'specialization' => $specialization['id'],
            ]);
        }

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
