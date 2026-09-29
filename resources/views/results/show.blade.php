@extends('layouts.app')

@section('title', 'نتيجة استكشاف الميول')

@section('content')
@php
    /** @var \App\Models\Result $result */
    // F-04: لا Scoring ولا Ranking هنا — المجالات تُعرض بترتيب RIASEC الثابت
    // من config، والتوصيات بترتيب Backend (display_order) كما وصلت.
    // إبراز الأعلى = عرض بصري فقط (شارة + نص معتمد من C-04 §5).
    $domains = config('riasec.domains');
    $scoreByCode = $result->resultScores->pluck('score', 'riasec_code');

    $present = [];
    foreach ($domains as $code => $label) {
        if ($scoreByCode->has($code)) {
            $present[$code] = (float) $scoreByCode[$code];
        }
    }

    $max = $present === [] ? null : max($present);
    $topCodes = $max === null
        ? []
        : array_keys(array_filter($present, static fn ($s): bool => abs($s - $max) < 0.005));

    $fmt = static function ($v): string {
        $v = round((float) $v, 1);
        return rtrim(rtrim(number_format($v, 1, '.', ''), '0'), '.');
    };

    $recommendations = $result->resultRecommendations;
@endphp

<div class="result-experience mx-auto w-full" dir="rtl">
    <header class="result-hero relative overflow-hidden rounded-[2rem] border">
        <div class="result-hero-glow result-hero-glow-one" aria-hidden="true"></div>
        <div class="result-hero-glow result-hero-glow-two" aria-hidden="true"></div>

        <div class="relative z-10 grid gap-6 p-5 sm:p-7 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:p-10">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="result-status-pill inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-extrabold">
                        <x-ui.icon name="check" class="h-4 w-4" />
                        اكتمل التقييم
                    </span>
                    <span class="result-date-pill rounded-full px-3 py-1.5 text-xs font-bold">
                        {{ $result->created_at?->format('Y-m-d') }}
                    </span>
                </div>

                <p class="result-hero-kicker mt-5 text-sm font-extrabold">صورة إرشادية عن ميولك الحالية</p>
                <h1 class="result-hero-title mt-2 font-extrabold">نتيجة استكشاف الميول</h1>

                <p class="result-hero-copy mt-4 max-w-3xl">
                    هذه النتيجة تساعدك على استكشاف المجالات والتخصصات التي قد تستحق مزيدًا من التعرف. وهي تعكس ميولك الحالية في هذا التقييم، وليست حكمًا نهائيًا على قدراتك أو مستقبلك.
                </p>

                <div class="result-meta mt-5 flex flex-wrap gap-2">
                    <span class="rounded-xl px-3 py-2 text-sm font-bold">إصدار الأداة {{ $result->scoring_version }}</span>
                    <span class="rounded-xl px-3 py-2 text-sm font-bold">إصدار الدليل {{ $result->catalog_version }}</span>
                </div>

                <a href="#recs-title" class="result-primary-cta mt-6 inline-flex min-h-14 items-center justify-center gap-2 rounded-2xl px-6 font-extrabold">
                    اطّلع على التخصصات المقترحة
                    <x-ui.icon name="arrow-end" class="h-5 w-5" />
                </a>
            </div>

            <div class="result-hero-summary rounded-3xl border p-5 sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="result-summary-kicker text-xs font-extrabold">قراءة سريعة</p>
                        <h2 class="result-summary-title mt-1 font-extrabold">المجالات الأبرز في هذه النتيجة</h2>
                    </div>
                    <span class="result-summary-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                        <x-ui.icon name="target" class="h-6 w-6" />
                    </span>
                </div>

                @if ($present === [])
                    <p class="result-summary-copy mt-4">
                        لا يظهر في نتيجتك مجال أعلى بوضوح من بقية المجالات. هذا أمر ممكن، ولا يعني غياب الميول أو عدم صلاحية النتيجة.
                    </p>
                @else
                    <div class="result-top-domains mt-5 grid gap-3">
                        @foreach ($topCodes as $code)
                            <div class="result-top-domain flex items-center justify-between gap-3 rounded-2xl px-4 py-3" data-domain="{{ $code }}">
                                <span class="flex items-center gap-3">
                                    <span class="result-domain-dot h-3 w-3 shrink-0 rounded-full"></span>
                                    <span class="font-extrabold">{{ $domains[$code] }}</span>
                                </span>
                                <span class="result-top-score font-extrabold tabular-nums">{{ $fmt($present[$code]) }}%</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <p class="result-summary-note mt-4">
                    النتيجة بداية للاستكشاف والمقارنة، وليست قرارًا نهائيًا عن تخصصك.
                </p>
            </div>
        </div>
    </header>

    <section aria-labelledby="domains-title" class="result-domains-section mt-5 rounded-[2rem] border p-5 sm:p-7 lg:p-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="result-section-kicker text-sm font-extrabold">خريطة ميولك</p>
                <h2 id="domains-title" class="result-section-title mt-1 font-extrabold">مجالات الميول الست</h2>
            </div>
            <p class="result-section-hint max-w-xl text-sm">المجالات معروضة بالترتيب الثابت نفسه، مع إبراز الأعلى بصريًا فقط.</p>
        </div>

        @if ($present === [])
            <div class="result-neutral-message mt-5 rounded-2xl p-5">
                <p>
                    لا تظهر في نتيجتك مجال أعلى بوضوح من بقية المجالات. هذا أمر ممكن، ولا يعني غياب الميول أو عدم صلاحية النتيجة. قد يساعدك التعرف إلى تجارب وأنشطة مختلفة ثم إعادة التفكير في الخيارات بعد اكتساب خبرة جديدة.
                </p>
            </div>
        @else
            <ul class="result-domain-grid mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($domains as $code => $label)
                    @continue(! $scoreByCode->has($code))
                    @php
                        $score = (float) $scoreByCode[$code];
                        $isTop = in_array($code, $topCodes, true);
                    @endphp
                    <li class="result-domain-card rounded-3xl border p-4 sm:p-5 {{ $isTop ? 'is-top' : '' }}" data-domain="{{ $code }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="result-domain-dot h-3 w-3 shrink-0 rounded-full"></span>
                                    <h3 class="result-domain-name font-extrabold">{{ $label }}</h3>
                                </div>
                                @if ($isTop)
                                    <span class="result-top-badge mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-extrabold">الأعلى في نتيجتك</span>
                                @endif
                            </div>
                            <span class="result-domain-score font-extrabold tabular-nums">{{ $fmt($score) }}%</span>
                        </div>

                        <div class="result-domain-track mt-5 h-2.5 w-full overflow-hidden rounded-full"
                             role="img" aria-label="{{ $label }}: {{ $fmt($score) }} من 100">
                            <div class="result-domain-fill h-full rounded-full"
                                 style="width: {{ min(100, max(0, $score)) }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="result-reading mt-6 rounded-3xl border p-5 sm:p-6">
                <div class="flex items-start gap-3">
                    <span class="result-reading-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                        <x-ui.icon name="info" class="h-5 w-5" />
                    </span>
                    <div>
                        <h3 class="result-reading-title font-extrabold">قراءة سريعة</h3>
                        @if (count($topCodes) === 1)
                            <p class="result-reading-copy mt-2">
                                يظهر في نتيجتك مجال أعلى من بقية المجالات في هذا التقييم. هذا يعني أن أنشطة
                                <strong>{{ $domains[$topCodes[0]] }}</strong>
                                تستحق أن تبدأ باستكشافها، ولا يعني أنها الخيار الوحيد المتاح لك أو أنها تحدد تخصصك النهائي.
                            </p>
                        @elseif (count($topCodes) === 2)
                            <p class="result-reading-copy mt-2">
                                تظهر في نتيجتك ميول متقاربة إلى مجالي
                                <strong>{{ $domains[$topCodes[0]] }}</strong> و<strong>{{ $domains[$topCodes[1]] }}</strong>.
                                قد يكون من المفيد استكشاف خيارات تجمع بين طبيعة المجالين، ثم مقارنة الدراسة والمهارات المطلوبة في كل خيار.
                            </p>
                        @elseif (count($topCodes) === 3)
                            <p class="result-reading-copy mt-2">
                                تظهر في نتيجتك مجموعة متقاربة من المجالات:
                                <strong>{{ $domains[$topCodes[0]] }}</strong> و<strong>{{ $domains[$topCodes[1]] }}</strong> و<strong>{{ $domains[$topCodes[2]] }}</strong>.
                                لا تحتاج إلى اختيار مجال واحد الآن؛ ابدأ بمقارنة الأنشطة والمواد الدراسية والتجارب المرتبطة بكل مجال.
                            </p>
                        @else
                            <p class="result-reading-copy mt-2">
                                لا يظهر في نتيجتك مجال أعلى بوضوح من بقية المجالات. هذا أمر ممكن، ولا يعني غياب الميول أو عدم صلاحية النتيجة. قد يساعدك التعرف إلى تجارب وأنشطة مختلفة ثم إعادة التفكير في الخيارات بعد اكتساب خبرة جديدة.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </section>

    <section aria-labelledby="recs-title" class="result-recommendations mt-7 scroll-mt-5">
        <div class="result-recommendations-heading rounded-[2rem] border p-5 sm:p-7">
            <p class="result-section-kicker text-sm font-extrabold">خيارات تستحق الاستكشاف</p>
            <h2 id="recs-title" class="result-section-title mt-1 font-extrabold">تخصصات مقترحة للاستكشاف</h2>
            <p class="result-recommendations-intro mt-3 max-w-4xl">
                بناءً على المجالات الأعلى في نتيجتك، تظهر التخصصات التالية كخيارات تستحق الاستكشاف. اقرأ عن طبيعة كل تخصص ومهاراته ومتطلباته، ثم قارن بينها قبل اتخاذ قرارك.
            </p>
        </div>

        @if ($recommendations->isEmpty())
            <x-ui.empty-state class="mt-4" icon="info"
                title="لا تتوفر توصيات كافية لعرضها"
                description="لا تتوفر حاليًا توصيات كافية يمكن عرضها بثقة استكشافية. يمكنك استخدام المجالات الأعلى لاستكشاف التخصصات في الدليل مباشرة، ثم مقارنة طبيعة الدراسة والمهارات ومتطلبات القبول.">
                <a href="{{ route('specializations.index') }}" class="btn btn-primary btn-pill text-sm mt-2">تصفّح دليل التخصصات</a>
            </x-ui.empty-state>
        @else
            <ol class="result-recommendation-grid mt-4 grid gap-4 lg:grid-cols-2">
                @foreach ($recommendations as $recommendation)
                    @php
                        $key = (string) $recommendation->specialization_key;
                        $inCatalog = in_array($key, $catalogKeys, true);
                        $visual = $specializationVisuals[$key] ?? null;
                        $visualUrl = is_array($visual) && !empty($visual['production_ready'])
                            ? ($visual['image_url'] ?? null)
                            : null;
                    @endphp

                    <li class="result-recommendation-card relative overflow-hidden rounded-3xl border" data-specialization="{{ $key }}">
                        <div class="result-recommendation-accent absolute inset-x-0 top-0 z-20 h-1" aria-hidden="true"></div>

                        @if ($visualUrl)
                            <div class="result-recommendation-media relative min-h-44 overflow-hidden">
                                <img src="{{ $visualUrl }}"
                                     alt="صورة تعبيرية عن {{ $recommendation->name_snapshot }}"
                                     class="absolute inset-0 h-full w-full object-cover"
                                     loading="lazy">
                                <div class="result-recommendation-media-wash absolute inset-0" aria-hidden="true"></div>
                                <span class="result-recommendation-order absolute top-3 start-3 z-10 inline-flex h-10 w-10 items-center justify-center rounded-full font-extrabold">
                                    {{ $recommendation->display_order }}
                                </span>
                            </div>
                        @else
                            <div class="result-recommendation-no-media flex items-center gap-3 p-5 pb-0">
                                <span class="result-recommendation-order inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full font-extrabold">
                                    {{ $recommendation->display_order }}
                                </span>
                                <span class="text-sm font-bold">الخيار {{ $recommendation->display_order }}</span>
                            </div>
                        @endif

                        <div class="p-5 sm:p-6">
                            <div class="flex items-start gap-3">
                                @if ($inCatalog)
                                    <span class="result-recommendation-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl">
                                        <x-ui.spec-icon :id="$key" :name="$recommendation->name_snapshot" />
                                    </span>
                                @endif
                                <div class="min-w-0">
                                    <p class="result-recommendation-label text-xs font-extrabold">تخصص مقترح للاستكشاف</p>
                                    <h3 class="result-recommendation-title mt-1 font-extrabold">{{ $recommendation->name_snapshot }}</h3>
                                </div>
                            </div>

                            @if ($recommendation->rationale_snapshot)
                                <p class="result-recommendation-rationale mt-4">{{ $recommendation->rationale_snapshot }}</p>
                            @endif

                            @if (! $inCatalog)
                                <p class="result-recommendation-limited mt-4 rounded-2xl p-3.5">
                                    تتوفر معلومات محدودة عن هذا التخصص في الدليل الحالي. يمكن استكشافه مبدئيًا، لكن ينبغي الرجوع إلى مصدر رسمي للبرنامج وخطته الدراسية وشروط القبول قبل استخدامه في قرار فعلي.
                                </p>
                            @endif

                            @if ($inCatalog)
                                <a href="{{ route('specializations.show', $key) }}"
                                   class="result-recommendation-cta mt-5 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl px-5 font-extrabold">
                                    استكشف التخصص
                                    <x-ui.icon name="arrow-end" class="h-4 w-4" />
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>

            <div class="result-recommendations-note mt-4 rounded-2xl border px-4 py-4">
                لا توجد نتيجة تحدد تخصصًا واحدًا مناسبًا لك بشكل نهائي. استخدم المجالات الأعلى كبداية للاستكشاف، ثم قارن طبيعة الدراسة والمهارات المطلوبة وشروط القبول والظروف المتاحة لك.
            </div>
        @endif
    </section>

    <section aria-labelledby="steps-title" class="result-next-steps mt-7 rounded-[2rem] border p-5 sm:p-7">
        <div class="flex items-start gap-3">
            <span class="result-section-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                <x-ui.icon name="arrow-end" class="h-6 w-6" />
            </span>
            <div>
                <p class="result-section-kicker text-sm font-extrabold">ماذا تفعل الآن؟</p>
                <h2 id="steps-title" class="result-section-title mt-1 font-extrabold">إرشادات الطالب بعد ظهور النتيجة</h2>
            </div>
        </div>

        <ol class="result-steps-grid mt-6 grid gap-3 md:grid-cols-2">
            @foreach ([
                'راجع المجالات الأعلى، واقرأ وصف الأنشطة المرتبطة بها.',
                'استكشف أكثر من تخصص مقترح، ولا تكتفِ بخيار واحد.',
                'قارن طبيعة الدراسة والمواد والمهارات المطلوبة.',
                'راجع شروط القبول والمعلومات الرسمية للبرنامج.',
                'فكّر في تجربة صغيرة أو نشاط تعريفي يساعدك على اختبار اهتمامك عمليًا.',
                'ناقش الخيارات مع الأسرة أو المرشد أو شخص موثوق لديه معرفة مناسبة.',
                'استخدم النتيجة بوصفها أحد مصادر القرار، لا المصدر الوحيد.',
            ] as $step)
                <li class="result-step flex items-start gap-3 rounded-2xl p-4">
                    <span class="result-step-number inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl font-extrabold">{{ $loop->iteration }}</span>
                    <span>{{ $step }}</span>
                </li>
            @endforeach
        </ol>
    </section>

    <div class="result-guidance-grid mt-5 grid gap-4 lg:grid-cols-[1fr_0.9fr]">
        <section aria-labelledby="family-title" class="result-family rounded-3xl border p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="result-section-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                    <x-ui.icon name="info" class="h-5 w-5" />
                </span>
                <h2 id="family-title" class="result-small-title font-extrabold">إرشادات الأسرة</h2>
            </div>
            <p class="result-guidance-copy mt-4">
                هذه النتيجة نقطة بداية للحوار وليست حكمًا على مستقبل الطالب. استمعوا إلى اهتماماته، وناقشوا معه أكثر من خيار، وساعدوه على مقارنة طبيعة الدراسة وشروطها وظروفها. لا تفرضوا تخصصًا اعتمادًا على النتيجة وحدها.
            </p>
        </section>

        <section aria-labelledby="limits-title" class="result-limits rounded-3xl border p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="result-limits-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                    <x-ui.icon name="warning" class="h-5 w-5" />
                </span>
                <h2 id="limits-title" class="result-small-title font-extrabold">حدود استخدام النتيجة</h2>
            </div>
            <p class="result-guidance-copy mt-4">
                هذه النتيجة إرشادية واستكشافية. لا تقيس الذكاء أو التحصيل أو الأهلية الرسمية للقبول، ولا تضمن النجاح أو الوظيفة أو الدخل. كما أنها لا تحدد تخصصًا واحدًا صحيحًا. استخدمها مع المعلومات الرسمية وتجارب الاستكشاف والحوار مع أشخاص موثوقين.
            </p>
        </section>
    </div>

    <div class="result-actions mt-6 flex flex-col gap-3 sm:flex-row">
        <a href="{{ route('specializations.index') }}" class="result-primary-cta inline-flex min-h-14 items-center justify-center gap-2 rounded-2xl px-6 font-extrabold">
            تصفّح دليل التخصصات
            <x-ui.icon name="arrow-end" class="h-5 w-5" />
        </a>
        <a href="{{ route('profile.results.index') }}" class="result-secondary-cta inline-flex min-h-14 items-center justify-center gap-2 rounded-2xl px-6 font-extrabold">
            سجل نتائجي
            <x-ui.icon name="clock" class="h-5 w-5" />
        </a>
    </div>
</div>
@endsection
