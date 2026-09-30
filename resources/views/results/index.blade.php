@extends('layouts.app')

@section('title', 'سجل نتائجي')

@section('content')
@php
    $resultsCount = $results->count();
@endphp

<section class="results-history results-history-v3 mx-auto w-full" dir="rtl" aria-labelledby="results-history-title">
    <header class="results-history-header results-history-header-v3">
        <div class="results-history-heading-wrap">
            <span class="results-history-heading-icon" aria-hidden="true">
                <x-ui.icon name="activity" class="h-5 w-5" />
            </span>

            <div class="min-w-0">
                <p class="results-history-eyebrow">سجل التقييمات</p>
                <h1 id="results-history-title">سجل نتائجي</h1>
                <p>نتائجك المكتملة محفوظة هنا، من الأحدث إلى الأقدم.</p>
            </div>
        </div>

        @if (! $results->isEmpty())
            <span class="results-history-total">
                <strong>{{ $resultsCount }}</strong>
                <span>{{ $resultsCount === 1 ? 'نتيجة' : 'نتائج' }}</span>
            </span>
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
        <div class="results-history-list-head" aria-hidden="true">
            <span>النتائج السابقة</span>
            <span>اضغط على أي نتيجة لعرض التفاصيل</span>
        </div>

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
                            <span class="results-history-row-icon" aria-hidden="true">
                                <x-ui.icon name="target" class="h-5 w-5" />
                            </span>

                            <div class="results-history-row-content min-w-0">
                                <div class="results-history-row-title-line">
                                    <h2>نتيجة استكشاف الميول</h2>
                                    <span class="results-history-row-index">#{{ $loop->iteration }}</span>
                                </div>

                                <div class="results-history-row-meta">
                                    <span class="results-history-meta-item">
                                        <x-ui.icon name="clock" class="h-4 w-4" />
                                        {{ $dateLabel }}
                                    </span>

                                    <span class="results-history-meta-separator" aria-hidden="true"></span>

                                    <span class="results-history-version">
                                        الإصدار {{ $item->scoring_version }}
                                    </span>
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
                <span class="results-history-footer-icon" aria-hidden="true">
                    <x-ui.icon name="plus" class="h-4 w-4" />
                </span>
                تقييم جديد أو متابعة التقييم
                <x-ui.icon name="arrow-end" class="h-4 w-4" />
            </a>
        </div>
    @endif
</section>
@endsection
