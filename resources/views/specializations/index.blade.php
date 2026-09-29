@extends('layouts.app')

@section('title', 'دليل التخصصات')

@section('content')
<div class="specializations-page mx-auto w-full" dir="rtl">
    <section class="specializations-hero specializations-title-card rounded-2xl border shadow-card" aria-labelledby="specializations-title">
        <div class="specializations-title-row flex items-center gap-3">
            <span class="specializations-title-icon inline-flex shrink-0 items-center justify-center rounded-xl" aria-hidden="true">
                <x-ui.icon name="info" class="h-5 w-5" />
            </span>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                    <h1 id="specializations-title" class="specializations-title font-extrabold">
                        دليل التخصصات
                    </h1>
                    <p class="specializations-kicker font-bold">اختر ما تريد استكشافه</p>
                </div>

                <p class="specializations-lead mt-1">
                    تصفح التخصصات، واقرأ وصفًا مختصرًا، ثم افتح التفاصيل لما يلفت اهتمامك.
                </p>
            </div>

            <div class="specializations-count inline-flex shrink-0 items-center gap-2 rounded-xl border">
                <span class="specializations-count-number font-extrabold">{{ count($specializations) }}</span>
                <span class="specializations-count-label font-bold">تخصصات</span>
            </div>
        </div>
    </section>

    @if (!empty($specializations) && count($specializations) > 0)
        <section class="specializations-grid mt-5 grid gap-4 md:grid-cols-2" aria-label="قائمة التخصصات">
            @foreach ($specializations as $specialization)
                @php
                    $specId = $specialization['id'] ?? '';
                    $specName = $specialization['name'] ?? 'بدون اسم';
                    $specDescription = $specialization['description'] ?? 'لا يوجد وصف متاح.';
                    $visual = $specializationVisuals[$specId] ?? null;
                    $imageUrl = is_array($visual) && !empty($visual['production_ready']) ? ($visual['image_url'] ?? null) : null;
                @endphp

                <article data-specialization="{{ $specId }}" class="specialization-card group flex min-h-0 flex-col overflow-hidden rounded-3xl border sm:flex-row">
                    <div class="specialization-card-accent absolute inset-x-0 top-0 z-10" aria-hidden="true"></div>

                    {{-- المحتوى في اليمين على الشاشات الواسعة. --}}
                    <div class="order-2 flex min-w-0 flex-1 flex-col p-5 sm:order-1 sm:w-[62%] sm:p-5 lg:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="specialization-icon-shell inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl">
                                    <x-ui.spec-icon :id="$specId" :name="$specName" />
                                </span>

                                <div class="min-w-0">
                                    <p class="specialization-index text-xs font-bold">التخصص {{ $loop->iteration }}</p>
                                    <h2 class="specialization-card-title mt-1 font-extrabold text-slate-900">
                                        {{ $specName }}
                                    </h2>
                                </div>
                            </div>

                            <span class="specialization-number inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold" aria-hidden="true">
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>

                        <p class="specialization-card-description mt-4 flex-1 text-slate-600">
                            {{ $specDescription }}
                        </p>

                        <div class="specialization-card-actions mt-5 flex flex-col gap-2.5 border-t pt-4 xl:flex-row">
                            <a href="{{ route('specializations.show', $specId) }}"
                               class="specialization-details-btn inline-flex min-h-12 flex-1 items-center justify-center gap-2 rounded-xl px-5 text-center font-extrabold">
                                عرض التفاصيل
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                                </svg>
                            </a>

                            <button type="button"
                                    class="compare-toggle specialization-compare-btn inline-flex min-h-12 items-center justify-center gap-2 rounded-xl px-4 font-bold"
                                    data-id="{{ $specId }}"
                                    data-name="{{ $specName }}">
                                <x-ui.icon name="plus" class="h-4 w-4 pointer-events-none" />
                                أضف للمقارنة
                            </button>
                        </div>
                    </div>

                    {{-- الصورة في الجانب الأيسر على الكمبيوتر، وفي أعلى البطاقة على الهاتف. --}}
                    <div class="specialization-card-media order-1 relative min-h-44 overflow-hidden sm:order-2 sm:min-h-full sm:w-[38%]">
                        @if ($imageUrl)
                            <img
                                src="{{ $imageUrl }}"
                                alt="صورة تعبيرية عن تخصص {{ $specName }}"
                                class="specialization-card-image absolute inset-0 h-full w-full object-cover"
                                loading="{{ $loop->iteration <= 2 ? 'eager' : 'lazy' }}"
                                decoding="async">
                        @else
                            <div class="specialization-image-fallback absolute inset-0 flex items-center justify-center">
                                <x-ui.spec-icon :id="$specId" :name="$specName" size="lg" />
                            </div>
                        @endif
                        <div class="specialization-image-wash absolute inset-0" aria-hidden="true"></div>
                        <span class="specialization-image-label absolute bottom-3 left-3 rounded-full px-3 py-1 text-[11px] font-bold">
                            {{ $specName }}
                        </span>
                    </div>
                </article>
            @endforeach
        </section>
    @else
        <x-ui.empty-state
            title="لا توجد تخصصات معروضة حاليًا"
            description="سنضيف التخصصات قريبًا. تابعنا."
            icon="info"
            class="mt-8" />
    @endif
</div>

{{-- عقد specializations.js محفوظ كما هو: ids + hidden/flex + data attributes --}}
<div id="compare-bar"
     class="specializations-compare-bar fixed inset-x-3 bottom-3 z-40 mx-auto flex hidden max-w-3xl flex-wrap items-center justify-between gap-3 rounded-2xl border p-3 text-sm shadow-pop sm:inset-x-6 sm:p-4">
    <p class="min-w-0 flex-1" role="status" aria-live="polite">
        تم اختيار:
        <span id="compare-bar-names" class="font-extrabold"></span>
    </p>

    <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto">
        <a id="compare-bar-link" class="specialization-details-btn inline-flex min-h-11 items-center justify-center rounded-xl px-5 font-extrabold">
            قارن الآن
        </a>
        <button type="button"
                id="compare-bar-clear"
                class="specialization-compare-btn inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-4 font-bold">
            <x-ui.icon name="x-mark" class="h-4 w-4 pointer-events-none" />
            إفراغ الاختيار
        </button>
    </div>
</div>
@endsection
