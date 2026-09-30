@extends('layouts.app')

@section('title', 'سجل نتائجي')

@section('content')
@php
    $resultsCount = $results->count();
@endphp

<section class="results-history results-history-v2 mx-auto w-full" dir="rtl" aria-labelledby="results-history-title">
    <header class="results-history-header">
        <div>
            <p class="results-history-eyebrow">سجل التقييمات</p>
            <h1 id="results-history-title">سجل نتائجي</h1>
            <p>كل نتيجة مكتملة محفوظة هنا، والأحدث تظهر أولًا.</p>
        </div>

        @if (! $results->isEmpty())
            <span class="results-history-total">{{ $resultsCount }} نتيجة</span>
        @endif
    </header>

    @if ($results->isEmpty())
        <section class="results-history-empty-v2">
            <span class="results-history-empty-mark" aria-hidden="true">
                <x-ui.icon name="activity" class="h-6 w-6" />
            </span>
            <h2>لا توجد نتائج بعد</h2>
            <p>أكمل تقييمك الأول، وستظهر النتيجة هنا مباشرة.</p>
            <a href="{{ route('assessment.intro') }}" class="results-history-main-action">
                ابدأ استكشاف ميولك
                <x-ui.icon name="arrow-end" class="h-5 w-5" />
            </a>
        </section>
    @else
        <ol class="results-history-list" aria-label="النتائج السابقة">
            @foreach ($results as $item)
                @php
                    $dateLabel = $item->created_at
                        ? $item->created_at->locale('ar')->translatedFormat('j F Y')
                        : 'تاريخ غير متاح';
                @endphp

                <li class="results-history-row">
                    <a href="{{ route('results.show', $item) }}" class="results-history-row-link">
                        <div class="results-history-row-main">
                            <span class="results-history-row-number" aria-hidden="true">{{ $loop->iteration }}</span>
                            <div class="min-w-0">
                                <h2>نتيجة استكشاف الميول</h2>
                                <div class="results-history-row-meta">
                                    <span>{{ $dateLabel }}</span>
                                    <span aria-hidden="true">•</span>
                                    <span>الإصدار {{ $item->scoring_version }}</span>
                                </div>
                            </div>
                        </div>

                        <span class="results-history-row-arrow" aria-hidden="true">
                            <x-ui.icon name="chevron-start" class="h-5 w-5" />
                        </span>
                    </a>
                </li>
            @endforeach
        </ol>

        <div class="results-history-footer-action">
            <a href="{{ route('assessment.intro') }}">
                تقييم جديد أو متابعة التقييم
                <x-ui.icon name="arrow-end" class="h-4 w-4" />
            </a>
        </div>
    @endif
</section>
@endsection
