@extends('layouts.app')

@section('title', 'سجل نتائجي')

@section('content')
@php
    $resultsCount = $results->count();
@endphp

<section class="results-history mx-auto w-full" dir="rtl" aria-labelledby="results-history-title">
    <header class="results-history-hero relative overflow-hidden rounded-[2rem] border">
        <div class="results-history-glow" aria-hidden="true"></div>

        <div class="relative z-10 grid gap-4 p-4 sm:p-5 lg:grid-cols-[1fr_auto] lg:items-center lg:p-6">
            <div>
                <span class="results-history-kicker inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-extrabold">
                    <x-ui.icon name="clock" class="h-4 w-4" />
                    حسابي / النتائج
                </span>

                <h1 id="results-history-title" class="results-history-title mt-4 font-extrabold">سجل نتائجي</h1>
                <p class="results-history-lead mt-3 max-w-3xl">
                    نتائج استكشاف الميول التي أتممتها، الأحدث أولًا. كل نتيجة محفوظة كما صدرت وقتها ويمكنك الرجوع إليها في أي وقت.
                </p>
            </div>

            <div class="results-history-count rounded-3xl px-5 py-4 text-center">
                <strong class="block font-extrabold">{{ $resultsCount }}</strong>
                <span class="mt-1 block text-sm font-bold">نتيجة محفوظة</span>
            </div>
        </div>
    </header>

    @if ($results->isEmpty())
        <section class="results-history-empty mt-5 rounded-[2rem] border p-6 text-center sm:p-9">
            <span class="results-history-empty-icon mx-auto inline-flex h-16 w-16 items-center justify-center rounded-3xl" aria-hidden="true">
                <x-ui.icon name="activity" class="h-8 w-8" />
            </span>
            <h2 class="results-history-empty-title mt-5 font-extrabold">لا توجد نتائج محفوظة بعد</h2>
            <p class="results-history-empty-copy mx-auto mt-3 max-w-xl">
                لم تُكمل أي تقييم حتى الآن. ابدأ رحلة استكشاف الميول، وعند إكمالها ستظهر نتيجتك هنا مباشرة.
            </p>
            <a href="{{ route('assessment.intro') }}"
               class="results-history-primary mt-6 inline-flex min-h-14 items-center justify-center gap-2 rounded-2xl px-6 font-extrabold">
                ابدأ استكشاف ميولك
                <x-ui.icon name="arrow-end" class="h-5 w-5" />
            </a>
        </section>
    @else
        <div class="results-history-toolbar mt-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="results-history-section-kicker text-sm font-extrabold">نتائجك السابقة</p>
                <h2 class="results-history-section-title mt-1 font-extrabold">افتح أي نتيجة لمراجعتها</h2>
            </div>
            <span class="results-history-order text-sm font-bold">مرتبة من الأحدث إلى الأقدم</span>
        </div>

        <ol class="results-history-grid mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @foreach ($results as $item)
                @php
                    $dateLabel = $item->created_at
                        ? $item->created_at->locale('ar')->translatedFormat('j F Y')
                        : 'تاريخ غير متاح';
                @endphp

                <li>
                    <a href="{{ route('results.show', $item) }}"
                       class="results-history-card group flex h-full flex-col rounded-2xl border p-3.5 sm:p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="results-history-card-icon inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" aria-hidden="true">
                                    <x-ui.icon name="check" class="h-6 w-6" />
                                </span>
                                <div class="min-w-0">
                                    <span class="results-history-status inline-flex rounded-full px-2.5 py-1 text-xs font-extrabold">محفوظة</span>
                                    <h3 class="results-history-card-title mt-2 font-extrabold">نتيجة استكشاف الميول</h3>
                                </div>
                            </div>

                            <span class="results-history-card-index inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold">
                                {{ $loop->iteration }}
                            </span>
                        </div>

                        <div class="results-history-card-date mt-3 flex items-center gap-2">
                            <x-ui.icon name="clock" class="h-5 w-5 shrink-0" />
                            <span class="font-bold">{{ $dateLabel }}</span>
                        </div>

                        <div class="results-history-card-meta mt-2.5 grid grid-cols-2 gap-1.5">
                            <div class="rounded-xl px-2.5 py-2">
                                <span class="block text-xs font-bold">إصدار الأداة</span>
                                <strong class="mt-1 block">{{ $item->scoring_version }}</strong>
                            </div>
                            <div class="rounded-xl px-2.5 py-2">
                                <span class="block text-xs font-bold">إصدار الدليل</span>
                                <strong class="mt-1 block">{{ $item->catalog_version }}</strong>
                            </div>
                        </div>

                        <div class="results-history-card-action mt-3 flex items-center justify-between gap-3 border-t pt-2.5">
                            <span class="font-extrabold">عرض النتيجة كاملة</span>
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl">
                                <x-ui.icon name="chevron-start" class="h-5 w-5 transition-transform group-hover:-translate-x-1" />
                            </span>
                        </div>
                    </a>
                </li>
            @endforeach
        </ol>

        <section class="results-history-cta mt-5 flex flex-col gap-4 rounded-3xl border p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <p class="results-history-section-kicker text-sm font-extrabold">هل تريد نتيجة أحدث؟</p>
                <h2 class="results-history-cta-title mt-1 font-extrabold">يمكنك العودة إلى التقييم من هنا</h2>
                <p class="results-history-cta-copy mt-2">ابدأ أو تابع استكشاف ميولك، وستُحفظ أي نتيجة مكتملة في هذا السجل.</p>
            </div>

            <a href="{{ route('assessment.intro') }}"
               class="results-history-primary inline-flex min-h-14 shrink-0 items-center justify-center gap-2 rounded-2xl px-6 font-extrabold">
                الذهاب إلى التقييم
                <x-ui.icon name="arrow-end" class="h-5 w-5" />
            </a>
        </section>
    @endif
</section>
@endsection
