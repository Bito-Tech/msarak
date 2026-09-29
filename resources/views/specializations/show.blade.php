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
    <div class="specialization-detail-toolbar mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('specializations.index') }}"
           class="specialization-detail-back inline-flex min-h-12 items-center gap-2 rounded-xl px-4 text-base font-bold">
            <x-ui.icon name="chevron-start" class="h-5 w-5" />
            رجوع لدليل التخصصات
        </a>

        @if (!empty($specId))
            <button type="button"
                    class="compare-toggle specialization-detail-compare inline-flex min-h-12 items-center justify-center gap-2 rounded-xl px-5 text-base font-extrabold"
                    data-id="{{ $specId }}"
                    data-name="{{ $specName }}"
                    data-redirect="{{ route('specializations.index') }}">
                <x-ui.icon name="plus" class="h-5 w-5" />
                قارن هذا التخصص
            </button>
        @endif
    </div>

    <header class="specialization-detail-hero relative overflow-hidden rounded-3xl border">
        <div class="specialization-detail-accent absolute inset-x-0 top-0 z-10 h-1" aria-hidden="true"></div>

        <div class="grid min-h-0 lg:grid-cols-[1.12fr_0.88fr]">
            <div class="specialization-detail-hero-copy order-2 flex flex-col justify-center p-5 sm:p-8 lg:order-1 lg:p-10 xl:p-12">
                <div class="flex items-center gap-3">
                    <span class="specialization-detail-icon inline-flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl">
                        <x-ui.spec-icon :id="$specId" :name="$specName" />
                    </span>
                    <span class="specialization-detail-label rounded-full px-4 py-2 text-sm font-extrabold">
                        دليل التخصصات
                    </span>
                </div>

                <h1 class="specialization-detail-title mt-6 font-extrabold">
                    {{ $specName }}
                </h1>

                <p class="specialization-detail-description mt-5 max-w-3xl">
                    {{ $specialization['description'] ?? '' }}
                </p>

                <div class="specialization-detail-quick mt-7 grid gap-3 sm:grid-cols-3">
                    @if (!empty($specialization['key_activities']))
                        <div class="specialization-detail-quick-item rounded-2xl p-3.5">
                            <span class="specialization-detail-quick-number block font-extrabold">{{ count($specialization['key_activities']) }}</span>
                            <span class="specialization-detail-quick-label block font-bold">أنشطة رئيسية</span>
                        </div>
                    @endif
                    @if (!empty($specialization['required_skills']))
                        <div class="specialization-detail-quick-item rounded-2xl p-3.5">
                            <span class="specialization-detail-quick-number block font-extrabold">{{ count($specialization['required_skills']) }}</span>
                            <span class="specialization-detail-quick-label block font-bold">مهارات مهمة</span>
                        </div>
                    @endif
                    @if (!empty($specialization['career_paths']))
                        <div class="specialization-detail-quick-item rounded-2xl p-3.5">
                            <span class="specialization-detail-quick-number block font-extrabold">{{ count($specialization['career_paths']) }}</span>
                            <span class="specialization-detail-quick-label block font-bold">مسارات وظيفية</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="specialization-detail-media order-1 relative min-h-72 overflow-hidden lg:order-2 lg:min-h-[30rem]">
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

    @if (!empty($specialization['study_nature']))
        <section class="specialization-detail-section specialization-detail-study mt-5 rounded-3xl border p-6 sm:p-8">
            <div class="flex items-start gap-4">
                <span class="specialization-detail-section-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                    <x-ui.icon name="info" class="h-6 w-6" />
                </span>
                <div>
                    <p class="specialization-detail-eyebrow text-sm font-bold">كيف تبدو الدراسة؟</p>
                    <h2 class="specialization-detail-section-title mt-1 font-extrabold">طبيعة الدراسة</h2>
                </div>
            </div>
            <p class="specialization-detail-body specialization-detail-study-body mt-5">{{ $specialization['study_nature'] }}</p>
        </section>
    @endif

    <div class="specialization-detail-dual mt-5 grid gap-5 lg:grid-cols-2">
        @if (!empty($specialization['key_activities']))
            <section class="specialization-detail-section rounded-3xl border p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <span class="specialization-detail-section-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                        <x-ui.icon name="check" class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="specialization-detail-eyebrow text-sm font-bold">ماذا ستفعل غالبًا؟</p>
                        <h2 class="specialization-detail-section-title mt-1 font-extrabold">أبرز الأنشطة</h2>
                    </div>
                </div>

                <div class="specialization-detail-list mt-5 grid gap-3">
                    @foreach ($specialization['key_activities'] as $activity)
                        <div class="specialization-detail-list-item flex items-start gap-3 rounded-2xl px-4 py-3.5">
                            <span class="specialization-detail-bullet mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-sm font-extrabold">{{ $loop->iteration }}</span>
                            <span>{{ $activity }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if (!empty($specialization['required_skills']))
            <section class="specialization-detail-section rounded-3xl border p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <span class="specialization-detail-section-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                        <x-ui.icon name="check" class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="specialization-detail-eyebrow text-sm font-bold">ما الذي يساعدك على النجاح؟</p>
                        <h2 class="specialization-detail-section-title mt-1 font-extrabold">المهارات المطلوبة</h2>
                    </div>
                </div>

                <div class="specialization-detail-list mt-5 grid gap-3">
                    @foreach ($specialization['required_skills'] as $skill)
                        <div class="specialization-detail-list-item flex items-start gap-3 rounded-2xl px-4 py-3.5">
                            <span class="specialization-detail-bullet mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-sm font-extrabold">{{ $loop->iteration }}</span>
                            <span>{{ $skill }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    @if (!empty($specialization['career_paths']))
        <section class="specialization-detail-section specialization-detail-careers mt-5 rounded-3xl border p-6 sm:p-8">
            <div class="flex items-start gap-4">
                <span class="specialization-detail-section-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                    <x-ui.icon name="arrow-end" class="h-6 w-6" />
                </span>
                <div>
                    <p class="specialization-detail-eyebrow text-sm font-bold">إلى أين يمكن أن يقودك؟</p>
                    <h2 class="specialization-detail-section-title mt-1 font-extrabold">المسارات الوظيفية</h2>
                </div>
            </div>

            <div class="specialization-detail-career-grid mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($specialization['career_paths'] as $path)
                    <div class="specialization-detail-career-item rounded-2xl px-4 py-4 font-bold">
                        {{ $path }}
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if (!empty($specialization['sources']))
        <section class="specialization-detail-sources mt-5 rounded-3xl border p-6 sm:p-8">
            <div class="flex items-start gap-4">
                <span class="specialization-detail-section-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                    <x-ui.icon name="info" class="h-6 w-6" />
                </span>
                <div>
                    <p class="specialization-detail-eyebrow text-sm font-bold">للمراجعة والتحقق</p>
                    <h2 class="specialization-detail-section-title mt-1 font-extrabold">مصادر المعلومات</h2>
                    <p class="specialization-detail-source-intro mt-2">يمكنك فتح أي مصدر في تبويب جديد للاطلاع على المرجع الأصلي.</p>
                </div>
            </div>

            <div class="specialization-detail-source-list mt-5 grid gap-3">
                @foreach ($specialization['sources'] as $source)
                    @php
                        $sourceUrl = null;
                        $sourceLabel = $source;

                        if (preg_match('/https?:\/\/[^\s]+/u', $source, $matches)) {
                            $sourceUrl = rtrim($matches[0], '.,،؛;');
                            $sourceLabel = trim(str_replace($matches[0], '', $source));
                            $sourceLabel = trim($sourceLabel, " \t\n\r\0\x0B—–-");
                        }
                    @endphp

                    @if ($sourceUrl)
                        <a href="{{ $sourceUrl }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="specialization-detail-source-link flex items-center justify-between gap-4 rounded-2xl px-4 py-4">
                            <span class="min-w-0">
                                <strong class="specialization-detail-source-title block">{{ $sourceLabel ?: 'فتح المصدر الأصلي' }}</strong>
                                <span class="specialization-detail-source-url mt-1 block truncate" dir="ltr">{{ $sourceUrl }}</span>
                            </span>
                            <span class="specialization-detail-source-arrow inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" aria-hidden="true">↗</span>
                        </a>
                    @else
                        <div class="specialization-detail-source-static rounded-2xl px-4 py-4">
                            {{ $source }}
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    @if (!empty($specId))
        <section class="specialization-detail-cta mt-5 flex flex-col gap-4 rounded-3xl border p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8">
            <div>
                <p class="specialization-detail-eyebrow text-sm font-bold">هل تريد وضعه بجانب خيار آخر؟</p>
                <h2 class="specialization-detail-cta-title mt-1 font-extrabold">قارن {{ $specName }} بتخصص آخر</h2>
                <p class="specialization-detail-body mt-2">المقارنة تساعدك على رؤية طبيعة الدراسة والمهارات والمسارات بشكل أوضح.</p>
            </div>

            <button type="button"
                    class="compare-toggle specialization-detail-compare specialization-detail-compare-large inline-flex min-h-16 shrink-0 items-center justify-center gap-2 rounded-2xl px-7 text-base font-extrabold"
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
