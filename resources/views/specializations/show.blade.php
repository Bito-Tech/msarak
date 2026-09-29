@extends('layouts.app')

@section('title', $specialization['name'] ?? 'تخصص')

@section('content')
@php
    $specId = $specialization['id'] ?? '';
    $specName = $specialization['name'] ?? 'بدون اسم';
    $imageUrl = is_array($specializationVisual ?? null) && !empty($specializationVisual['production_ready'])
        ? ($specializationVisual['image_url'] ?? null)
        : null;
@endphp

<article class="specialization-detail mx-auto w-full" data-specialization="{{ $specId }}" dir="rtl">
    <div class="specialization-detail-toolbar mb-3 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('specializations.index') }}"
           class="specialization-detail-back inline-flex min-h-11 items-center gap-2 rounded-xl px-3 text-sm font-bold">
            <x-ui.icon name="chevron-start" class="h-4 w-4" />
            رجوع لدليل التخصصات
        </a>

        @if (!empty($specId))
            <button type="button"
                    class="compare-toggle specialization-detail-compare inline-flex min-h-11 items-center justify-center gap-2 rounded-xl px-4 text-sm font-extrabold"
                    data-id="{{ $specId }}"
                    data-name="{{ $specName }}"
                    data-redirect="{{ route('specializations.index') }}">
                <x-ui.icon name="plus" class="h-4 w-4" />
                قارن هذا التخصص
            </button>
        @endif
    </div>

    <header class="specialization-detail-hero relative overflow-hidden rounded-3xl border">
        <div class="specialization-detail-accent absolute inset-x-0 top-0 z-10 h-1" aria-hidden="true"></div>

        <div class="grid min-h-0 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="specialization-detail-hero-copy order-2 flex flex-col justify-center p-5 sm:p-7 lg:order-1 lg:p-9">
                <div class="flex items-center gap-3">
                    <span class="specialization-detail-icon inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl">
                        <x-ui.spec-icon :id="$specId" :name="$specName" />
                    </span>
                    <span class="specialization-detail-label rounded-full px-3 py-1.5 text-xs font-extrabold">
                        دليل التخصصات
                    </span>
                </div>

                <h1 class="specialization-detail-title mt-5 font-extrabold">
                    {{ $specName }}
                </h1>

                <p class="specialization-detail-description mt-4 max-w-3xl">
                    {{ $specialization['description'] ?? '' }}
                </p>

                <div class="specialization-detail-quick mt-6 flex flex-wrap gap-2.5">
                    @if (!empty($specialization['key_activities']))
                        <span class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold">
                            <x-ui.icon name="check" class="h-4 w-4" />
                            {{ count($specialization['key_activities']) }} أنشطة رئيسية
                        </span>
                    @endif
                    @if (!empty($specialization['required_skills']))
                        <span class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold">
                            <x-ui.icon name="check" class="h-4 w-4" />
                            {{ count($specialization['required_skills']) }} مهارات مهمة
                        </span>
                    @endif
                    @if (!empty($specialization['career_paths']))
                        <span class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold">
                            <x-ui.icon name="arrow-end" class="h-4 w-4" />
                            {{ count($specialization['career_paths']) }} مسارات وظيفية
                        </span>
                    @endif
                </div>
            </div>

            <div class="specialization-detail-media order-1 relative min-h-64 overflow-hidden lg:order-2 lg:min-h-[26rem]">
                @if ($imageUrl)
                    <img
                        src="{{ $imageUrl }}"
                        alt="صورة تعبيرية عن تخصص {{ $specName }}"
                        class="specialization-detail-image absolute inset-0 h-full w-full object-cover"
                        loading="eager"
                        fetchpriority="high">
                @else
                    <div class="specialization-detail-fallback absolute inset-0 flex items-center justify-center">
                        <x-ui.spec-icon :id="$specId" :name="$specName" size="lg" />
                    </div>
                @endif
                <div class="specialization-detail-image-wash absolute inset-0" aria-hidden="true"></div>
            </div>
        </div>
    </header>

    <div class="specialization-detail-content mt-4 grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="grid gap-4">
            @if (!empty($specialization['study_nature']))
                <section class="specialization-detail-section specialization-detail-study rounded-3xl border p-5 sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="specialization-detail-section-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                            <x-ui.icon name="info" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="specialization-detail-eyebrow text-xs font-bold">كيف تبدو الدراسة؟</p>
                            <h2 class="specialization-detail-section-title mt-1 font-extrabold">طبيعة الدراسة</h2>
                        </div>
                    </div>
                    <p class="specialization-detail-body mt-4">{{ $specialization['study_nature'] }}</p>
                </section>
            @endif

            @if (!empty($specialization['required_skills']))
                <section class="specialization-detail-section rounded-3xl border p-5 sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="specialization-detail-section-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                            <x-ui.icon name="check" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="specialization-detail-eyebrow text-xs font-bold">ما الذي يساعدك على النجاح؟</p>
                            <h2 class="specialization-detail-section-title mt-1 font-extrabold">المهارات المطلوبة</h2>
                        </div>
                    </div>

                    <div class="specialization-detail-list mt-4 grid gap-2.5">
                        @foreach ($specialization['required_skills'] as $skill)
                            <div class="specialization-detail-list-item flex items-start gap-3 rounded-2xl px-3.5 py-3">
                                <span class="specialization-detail-bullet mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-extrabold">{{ $loop->iteration }}</span>
                                <span>{{ $skill }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <div class="grid gap-4">
            @if (!empty($specialization['key_activities']))
                <section class="specialization-detail-section rounded-3xl border p-5 sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="specialization-detail-section-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                            <x-ui.icon name="check" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="specialization-detail-eyebrow text-xs font-bold">ماذا ستفعل غالبًا؟</p>
                            <h2 class="specialization-detail-section-title mt-1 font-extrabold">أبرز الأنشطة</h2>
                        </div>
                    </div>

                    <div class="specialization-detail-list mt-4 grid gap-2.5">
                        @foreach ($specialization['key_activities'] as $activity)
                            <div class="specialization-detail-list-item flex items-start gap-3 rounded-2xl px-3.5 py-3">
                                <span class="specialization-detail-bullet mt-1 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-extrabold">{{ $loop->iteration }}</span>
                                <span>{{ $activity }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if (!empty($specialization['career_paths']))
                <section class="specialization-detail-section specialization-detail-careers rounded-3xl border p-5 sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="specialization-detail-section-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                            <x-ui.icon name="arrow-end" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="specialization-detail-eyebrow text-xs font-bold">إلى أين يمكن أن يقودك؟</p>
                            <h2 class="specialization-detail-section-title mt-1 font-extrabold">المسارات الوظيفية</h2>
                        </div>
                    </div>

                    <div class="specialization-detail-career-grid mt-4 grid gap-2.5 sm:grid-cols-2">
                        @foreach ($specialization['career_paths'] as $path)
                            <div class="specialization-detail-career-item rounded-2xl px-3.5 py-3 font-bold">
                                {{ $path }}
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>

    @if (!empty($specialization['sources']))
        <section class="specialization-detail-sources mt-4 rounded-3xl border p-5 sm:p-6">
            <div class="flex items-start gap-3">
                <span class="specialization-detail-section-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                    <x-ui.icon name="info" class="h-5 w-5" />
                </span>
                <div>
                    <p class="specialization-detail-eyebrow text-xs font-bold">للمراجعة والتحقق</p>
                    <h2 class="specialization-detail-section-title mt-1 font-extrabold">مصادر المعلومات</h2>
                </div>
            </div>

            <ul class="specialization-detail-source-list mt-4 grid gap-2.5">
                @foreach ($specialization['sources'] as $source)
                    <li class="rounded-2xl px-3.5 py-3 text-sm leading-relaxed">{{ $source }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    @if (!empty($specId))
        <section class="specialization-detail-cta mt-4 flex flex-col gap-3 rounded-3xl border p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <p class="specialization-detail-eyebrow text-xs font-bold">هل تريد وضعه بجانب خيار آخر؟</p>
                <h2 class="specialization-detail-cta-title mt-1 font-extrabold">قارن {{ $specName }} بتخصص آخر</h2>
                <p class="specialization-detail-body mt-1">المقارنة تساعدك على رؤية طبيعة الدراسة والمهارات والمسارات بشكل أوضح.</p>
            </div>

            <button type="button"
                    class="compare-toggle specialization-detail-compare specialization-detail-compare-large inline-flex min-h-14 shrink-0 items-center justify-center gap-2 rounded-2xl px-6 font-extrabold"
                    data-id="{{ $specId }}"
                    data-name="{{ $specName }}"
                    data-redirect="{{ route('specializations.index') }}">
                <x-ui.icon name="plus" class="h-5 w-5" />
                أضف للمقارنة
            </button>
        </section>
    @endif
</article>
@endsection
