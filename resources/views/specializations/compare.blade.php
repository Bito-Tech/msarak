@extends('layouts.app')

@section('title', 'مقارنة التخصصات')

@section('content')
@php
    $firstId = $first['id'] ?? '';
    $secondId = $second['id'] ?? '';

    $firstImage = is_array($firstVisual ?? null) && !empty($firstVisual['production_ready'])
        ? ($firstVisual['image_url'] ?? null)
        : null;
    $secondImage = is_array($secondVisual ?? null) && !empty($secondVisual['production_ready'])
        ? ($secondVisual['image_url'] ?? null)
        : null;

    $sections = [
        [
            'label' => 'طبيعة الدراسة',
            'hint' => 'كيف تبدو التجربة الأكاديمية في كل تخصص؟',
            'field' => 'study_nature',
            'icon' => 'info',
        ],
        [
            'label' => 'أبرز الأنشطة',
            'hint' => 'ما نوع الأعمال والمهام التي ستتعامل معها غالبًا؟',
            'field' => 'key_activities',
            'icon' => 'check',
        ],
        [
            'label' => 'المهارات المطلوبة',
            'hint' => 'ما القدرات التي تساعدك على التقدم في كل مسار؟',
            'field' => 'required_skills',
            'icon' => 'activity',
        ],
        [
            'label' => 'المسارات الوظيفية',
            'hint' => 'إلى أين قد يقودك كل تخصص بعد الدراسة؟',
            'field' => 'career_paths',
            'icon' => 'arrow-end',
        ],
    ];
@endphp

<section class="specialization-compare mx-auto w-full" aria-labelledby="compare-title" dir="rtl">
    <div class="specialization-compare-toolbar mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('specializations.index') }}"
           class="specialization-compare-back inline-flex min-h-12 items-center gap-2 rounded-xl px-4 text-base font-bold">
            <x-ui.icon name="chevron-start" class="h-5 w-5" />
            رجوع لدليل التخصصات
        </a>

        <span class="specialization-compare-note inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold">
            <x-ui.icon name="info" class="h-4 w-4" />
            قارن الجوانب نفسها بين التخصصين
        </span>
    </div>

    <header class="specialization-compare-heading rounded-3xl border px-5 py-5 sm:px-7 sm:py-6">
        <p class="specialization-compare-kicker text-sm font-extrabold">قرار أوضح يبدأ بمقارنة أوضح</p>
        <div class="mt-1 flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 id="compare-title" class="specialization-compare-title font-extrabold">مقارنة التخصصات</h1>
                <p class="specialization-compare-lead mt-2 max-w-3xl">
                    راجع طبيعة الدراسة والمهارات والأنشطة والمسارات الوظيفية جنبًا إلى جنب، ثم افتح تفاصيل أي تخصص عندما تحتاج صورة أعمق.
                </p>
            </div>

            <div class="specialization-compare-axis-count shrink-0 rounded-2xl px-4 py-3 text-center">
                <strong class="block">4</strong>
                <span class="block text-xs font-bold">محاور للمقارنة</span>
            </div>
        </div>
    </header>

    <div class="specialization-compare-pair mt-5 grid items-stretch gap-3 lg:grid-cols-[1fr_auto_1fr] lg:gap-4">
        <article class="compare-major-card compare-major-card-first relative overflow-hidden rounded-3xl border" data-specialization="{{ $firstId }}">
            <div class="compare-major-accent absolute inset-x-0 top-0 z-20 h-1" aria-hidden="true"></div>

            <div class="compare-major-media relative min-h-52 overflow-hidden sm:min-h-60">
                @if ($firstImage)
                    <img src="{{ $firstImage }}"
                         alt="صورة تعبيرية عن تخصص {{ $first['name'] ?? '' }}"
                         class="compare-major-image absolute inset-0 h-full w-full object-cover"
                         loading="eager">
                @else
                    <div class="compare-major-fallback absolute inset-0 flex items-center justify-center">
                        <x-ui.spec-icon :id="$firstId" :name="$first['name'] ?? ''" size="lg" />
                    </div>
                @endif
                <div class="compare-major-wash absolute inset-0" aria-hidden="true"></div>

                <span class="compare-major-position absolute top-4 start-4 z-10 rounded-full px-3 py-1.5 text-xs font-extrabold">التخصص الأول</span>
            </div>

            <div class="compare-major-body p-5 sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="compare-major-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl">
                        <x-ui.spec-icon :id="$firstId" :name="$first['name'] ?? ''" />
                    </span>
                    <h2 class="compare-major-title font-extrabold">{{ $first['name'] ?? 'بدون اسم' }}</h2>
                </div>

                <p class="compare-major-description mt-4">{{ $first['description'] ?? '' }}</p>

                <a href="{{ route('specializations.show', $firstId) }}"
                   class="compare-major-details mt-5 inline-flex min-h-11 items-center gap-2 rounded-xl px-4 text-sm font-extrabold">
                    عرض تفاصيل التخصص
                    <x-ui.icon name="arrow-end" class="h-4 w-4" />
                </a>
            </div>
        </article>

        <div class="specialization-compare-vs flex items-center justify-center" aria-hidden="true">
            <span class="inline-flex h-14 w-14 items-center justify-center rounded-full font-extrabold">VS</span>
        </div>

        <article class="compare-major-card compare-major-card-second relative overflow-hidden rounded-3xl border" data-specialization="{{ $secondId }}">
            <div class="compare-major-accent absolute inset-x-0 top-0 z-20 h-1" aria-hidden="true"></div>

            <div class="compare-major-media relative min-h-52 overflow-hidden sm:min-h-60">
                @if ($secondImage)
                    <img src="{{ $secondImage }}"
                         alt="صورة تعبيرية عن تخصص {{ $second['name'] ?? '' }}"
                         class="compare-major-image absolute inset-0 h-full w-full object-cover"
                         loading="eager">
                @else
                    <div class="compare-major-fallback absolute inset-0 flex items-center justify-center">
                        <x-ui.spec-icon :id="$secondId" :name="$second['name'] ?? ''" size="lg" />
                    </div>
                @endif
                <div class="compare-major-wash absolute inset-0" aria-hidden="true"></div>

                <span class="compare-major-position absolute top-4 start-4 z-10 rounded-full px-3 py-1.5 text-xs font-extrabold">التخصص الثاني</span>
            </div>

            <div class="compare-major-body p-5 sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="compare-major-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl">
                        <x-ui.spec-icon :id="$secondId" :name="$second['name'] ?? ''" />
                    </span>
                    <h2 class="compare-major-title font-extrabold">{{ $second['name'] ?? 'بدون اسم' }}</h2>
                </div>

                <p class="compare-major-description mt-4">{{ $second['description'] ?? '' }}</p>

                <a href="{{ route('specializations.show', $secondId) }}"
                   class="compare-major-details mt-5 inline-flex min-h-11 items-center gap-2 rounded-xl px-4 text-sm font-extrabold">
                    عرض تفاصيل التخصص
                    <x-ui.icon name="arrow-end" class="h-4 w-4" />
                </a>
            </div>
        </article>
    </div>

    <div class="specialization-compare-sections mt-5 grid gap-4">
        @foreach ($sections as $section)
            <section class="compare-axis-card overflow-hidden rounded-3xl border">
                <header class="compare-axis-head flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="compare-axis-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                            <x-ui.icon :name="$section['icon']" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="compare-axis-eyebrow text-xs font-bold">محور المقارنة</p>
                            <h2 class="compare-axis-title mt-1 font-extrabold">{{ $section['label'] }}</h2>
                        </div>
                    </div>
                    <p class="compare-axis-hint max-w-xl text-sm">{{ $section['hint'] }}</p>
                </header>

                <div class="compare-axis-grid grid lg:grid-cols-2">
                    @foreach ([$first, $second] as $column => $specialization)
                        @php
                            $value = $specialization[$section['field']] ?? null;
                            $currentId = $specialization['id'] ?? '';
                        @endphp

                        <div class="compare-axis-column {{ $column === 0 ? 'compare-axis-column-first' : 'compare-axis-column-second' }}"
                             data-specialization="{{ $currentId }}">
                            <div class="compare-axis-column-label flex items-center gap-2">
                                <x-ui.spec-icon :id="$currentId" :name="$specialization['name'] ?? ''" part="dot" />
                                <span class="font-extrabold">{{ $specialization['name'] ?? 'بدون اسم' }}</span>
                            </div>

                            @if (empty($value))
                                <p class="compare-axis-empty mt-4">لا تتوفر بيانات لهذا المحور.</p>
                            @elseif (is_array($value))
                                <div class="compare-axis-items mt-4 grid gap-2.5">
                                    @foreach ($value as $item)
                                        <div class="compare-axis-item flex items-start gap-3 rounded-2xl px-3.5 py-3">
                                            <span class="compare-axis-bullet mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-extrabold">{{ $loop->iteration }}</span>
                                            <span>{{ $item }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="compare-axis-copy mt-4">{{ $value }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <section class="specialization-compare-footer mt-5 flex flex-col gap-4 rounded-3xl border p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div>
            <p class="specialization-compare-kicker text-sm font-extrabold">ما زلت تريد استكشاف خيارات أخرى؟</p>
            <h2 class="specialization-compare-footer-title mt-1 font-extrabold">ارجع للدليل واختر مقارنة مختلفة</h2>
            <p class="specialization-compare-footer-copy mt-2">يمكنك تبديل أي تخصص وإعادة المقارنة دون فقدان وضوح المحاور.</p>
        </div>

        <a href="{{ route('specializations.index') }}"
           class="specialization-compare-footer-cta inline-flex min-h-14 shrink-0 items-center justify-center gap-2 rounded-2xl px-6 font-extrabold">
            العودة لدليل التخصصات
            <x-ui.icon name="chevron-start" class="h-5 w-5" />
        </a>
    </section>
</section>
@endsection
