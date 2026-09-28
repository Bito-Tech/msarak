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

<div class="mx-auto max-w-4xl">

    {{-- رأس النتيجة --}}
    <header class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="auth-head">
            <span class="auth-head-icon">
                <x-ui.icon name="check" class="h-5 w-5" />
            </span>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">نتيجة استكشاف الميول</h1>
                <p class="mt-2 text-xs leading-relaxed text-slate-600 sm:text-sm">
                    {{ $result->created_at?->format('Y-m-d') }}
                    · إصدار الأداة {{ $result->scoring_version }}
                    · إصدار الدليل {{ $result->catalog_version }}
                </p>
            </div>
        </div>
        {{-- C-04 §3.1 --}}
        <p class="mt-5 max-w-2xl border-s-4 border-brand-500 ps-4 text-sm leading-relaxed text-slate-700 sm:text-base">
            هذه النتيجة تساعدك على استكشاف المجالات والتخصصات التي قد تستحق مزيدًا من التعرف. وهي تعكس ميولك الحالية في هذا التقييم، وليست حكمًا نهائيًا على قدراتك أو مستقبلك.
        </p>
        <a href="#recs-title" class="btn btn-primary mt-5 w-full text-sm sm:w-auto">اطّلع على التخصصات المقترحة</a>
    </header>

    {{-- المجالات الستة --}}
    <section aria-labelledby="domains-title" class="card-surface mt-5 p-5 sm:p-8">
        <h2 id="domains-title" class="text-xl font-bold text-slate-900">مجالات الميول الست</h2>

        @if ($present === [])
            {{-- حالة دفاعية: نتيجة بلا درجات محفوظة --}}
            <p class="mt-3 text-sm leading-relaxed text-slate-600">
                لا تظهر في نتيجتك مجال أعلى بوضوح من بقية المجالات. هذا أمر ممكن، ولا يعني غياب الميول أو عدم صلاحية النتيجة. قد يساعدك التعرف إلى تجارب وأنشطة مختلفة ثم إعادة التفكير في الخيارات بعد اكتساب خبرة جديدة.
            </p>
        @else
            <ul class="mt-6 grid gap-5 sm:grid-cols-2 sm:gap-x-8 sm:gap-y-7">
                @foreach ($domains as $code => $label)
                    @continue(! $scoreByCode->has($code))
                    @php $score = (float) $scoreByCode[$code]; @endphp
                    <li class="min-w-0">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="flex min-w-0 flex-wrap items-center gap-2 text-sm font-bold text-slate-900">
                                {{ $label }}
                                @if ($max !== null && in_array($code, $topCodes, true))
                                    <x-ui.badge variant="brand">الأعلى في نتيجتك</x-ui.badge>
                                @endif
                            </span>
                            <span class="text-sm font-semibold text-slate-600 tabular-nums">{{ $fmt($score) }}%</span>
                        </div>
                        <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-slate-100"
                             role="img" aria-label="{{ $label }}: {{ $fmt($score) }} من 100">
                            <div class="h-full rounded-full {{ in_array($code, $topCodes, true) ? 'bg-brand-600' : 'bg-brand-300' }}"
                                 style="width: {{ min(100, max(0, $score)) }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>

            {{-- تفسير الأعلى — نصوص C-04 §5 المعتمدة --}}
            <div class="mt-8 rounded-xl border border-brand-200 bg-brand-50 p-4 sm:p-5">
                <h3 class="text-sm font-bold text-brand-800">قراءة سريعة</h3>
                @if (count($topCodes) === 1)
                    {{-- C-04 §5.1 --}}
                    <p class="mt-2 text-sm leading-relaxed text-slate-700">
                        يظهر في نتيجتك مجال أعلى من بقية المجالات في هذا التقييم. هذا يعني أن أنشطة
                        <strong>{{ $domains[$topCodes[0]] }}</strong>
                        تستحق أن تبدأ باستكشافها، ولا يعني أنها الخيار الوحيد المتاح لك أو أنها تحدد تخصصك النهائي.
                    </p>
                @elseif (count($topCodes) === 2)
                    {{-- C-04 §5.2 --}}
                    <p class="mt-2 text-sm leading-relaxed text-slate-700">
                        تظهر في نتيجتك ميول متقاربة إلى مجالي
                        <strong>{{ $domains[$topCodes[0]] }}</strong> و<strong>{{ $domains[$topCodes[1]] }}</strong>.
                        قد يكون من المفيد استكشاف خيارات تجمع بين طبيعة المجالين، ثم مقارنة الدراسة والمهارات المطلوبة في كل خيار.
                    </p>
                @elseif (count($topCodes) === 3)
                    {{-- C-04 §5.3 --}}
                    <p class="mt-2 text-sm leading-relaxed text-slate-700">
                        تظهر في نتيجتك مجموعة متقاربة من المجالات:
                        <strong>{{ $domains[$topCodes[0]] }}</strong> و<strong>{{ $domains[$topCodes[1]] }}</strong> و<strong>{{ $domains[$topCodes[2]] }}</strong>.
                        لا تحتاج إلى اختيار مجال واحد الآن؛ ابدأ بمقارنة الأنشطة والمواد الدراسية والتجارب المرتبطة بكل مجال.
                    </p>
                @else
                    {{-- C-04 §5.4 — تقارب/انفلاع واضح --}}
                    <p class="mt-2 text-sm leading-relaxed text-slate-700">
                        لا يظهر في نتيجتك مجال أعلى بوضوح من بقية المجالات. هذا أمر ممكن، ولا يعني غياب الميول أو عدم صلاحية النتيجة. قد يساعدك التعرف إلى تجارب وأنشطة مختلفة ثم إعادة التفكير في الخيارات بعد اكتساب خبرة جديدة.
                    </p>
                @endif
            </div>
        @endif
    </section>

    {{-- التوصيات — بترتيب Backend الحرفي --}}
    <section aria-labelledby="recs-title" class="mt-8">
        <h2 id="recs-title" class="scroll-mt-6 text-xl font-bold text-slate-900 sm:text-2xl">تخصصات مقترحة للاستكشاف</h2>

        @if ($recommendations->isEmpty())
            {{-- C-04 §6.5 --}}
            <x-ui.empty-state class="mt-4" icon="info"
                title="لا تتوفر توصيات كافية لعرضها"
                description="لا تتوفر حاليًا توصيات كافية يمكن عرضها بثقة استكشافية. يمكنك استخدام المجالات الأعلى لاستكشاف التخصصات في الدليل مباشرة، ثم مقارنة طبيعة الدراسة والمهارات ومتطلبات القبول.">
                <a href="{{ route('specializations.index') }}" class="btn btn-primary btn-pill text-sm mt-2">تصفّح دليل التخصصات</a>
            </x-ui.empty-state>
        @else
            <p class="mt-2 text-sm leading-relaxed text-slate-700">
                بناءً على المجالات الأعلى في نتيجتك، تظهر التخصصات التالية كخيارات تستحق الاستكشاف. اقرأ عن طبيعة كل تخصص ومهاراته ومتطلباته، ثم قارن بينها قبل اتخاذ قرارك.
            </p>

            <ul class="mt-5 space-y-3">
                @foreach ($recommendations as $recommendation)
                    @php
                        $key = (string) $recommendation->specialization_key;
                        $inCatalog = in_array($key, $catalogKeys, true);
                    @endphp
                    <li class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-colors hover:border-brand-200 sm:p-6">
                        <div class="flex flex-wrap items-start gap-x-4 gap-y-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-extrabold text-brand-800"
                                  aria-hidden="true">{{ $recommendation->display_order }}</span>
                            <div class="min-w-0 flex-1 basis-52">
                                <h3 class="text-base font-bold text-slate-900">{{ $recommendation->name_snapshot }}</h3>

                                @if ($recommendation->rationale_snapshot)
                                    <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $recommendation->rationale_snapshot }}</p>
                                @endif

                                @if (! $inCatalog)
                                    {{-- C-04 §6.4 --}}
                                    <p class="mt-2 text-xs leading-relaxed text-slate-500">
                                        تتوفر معلومات محدودة عن هذا التخصص في الدليل الحالي. يمكن استكشافه مبدئيًا، لكن ينبغي الرجوع إلى مصدر رسمي للبرنامج وخطته الدراسية وشروط القبول قبل استخدامه في قرار فعلي.
                                    </p>
                                @endif
                            </div>
                            @if ($inCatalog)
                                {{-- الجوال: سطر مستقل؛ المكتب: بجانب النص --}}
                                <a href="{{ route('specializations.show', $key) }}"
                                   class="btn btn-soft text-sm basis-full sm:basis-auto sm:self-center">استكشف التخصص</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>

            {{-- C-04 §3.2 --}}
            <p class="mt-4 text-sm leading-relaxed text-slate-600">
                لا توجد نتيجة تحدد تخصصًا واحدًا مناسبًا لك بشكل نهائي. استخدم المجالات الأعلى كبداية للاستكشاف، ثم قارن طبيعة الدراسة والمهارات المطلوبة وشروط القبول والظروف المتاحة لك.
            </p>
        @endif
    </section>

    {{-- إرشادات الطالب — C-04 §7 --}}
    <section aria-labelledby="steps-title" class="card-surface mt-8 p-5 sm:p-8">
        <h2 id="steps-title" class="text-xl font-bold text-slate-900">إرشادات الطالب بعد ظهور النتيجة</h2>
        <ol class="mt-5 grid list-decimal gap-x-8 gap-y-3 ps-5 text-sm leading-relaxed text-slate-700 marker:font-bold marker:text-brand-600 sm:grid-cols-2">
            <li>راجع المجالات الأعلى، واقرأ وصف الأنشطة المرتبطة بها.</li>
            <li>استكشف أكثر من تخصص مقترح، ولا تكتفِ بخيار واحد.</li>
            <li>قارن طبيعة الدراسة والمواد والمهارات المطلوبة.</li>
            <li>راجع شروط القبول والمعلومات الرسمية للبرنامج.</li>
            <li>فكّر في تجربة صغيرة أو نشاط تعريفي يساعدك على اختبار اهتمامك عمليًا.</li>
            <li>ناقش الخيارات مع الأسرة أو المرشد أو شخص موثوق لديه معرفة مناسبة.</li>
            <li>استخدم النتيجة بوصفها أحد مصادر القرار، لا المصدر الوحيد.</li>
        </ol>
    </section>

    {{-- إرشادات الأسرة — C-04 §8.1 --}}
    <section aria-labelledby="family-title" class="card-surface mt-5 p-5 sm:p-6">
        <h2 id="family-title" class="text-lg font-bold text-slate-900">إرشادات الأسرة</h2>
        <p class="mt-3 text-sm leading-relaxed text-slate-700">
            هذه النتيجة نقطة بداية للحوار وليست حكمًا على مستقبل الطالب. استمعوا إلى اهتماماته، وناقشوا معه أكثر من خيار، وساعدوه على مقارنة طبيعة الدراسة وشروطها وظروفها. لا تفرضوا تخصصًا اعتمادًا على النتيجة وحدها.
        </p>
    </section>

    {{-- حدود الاستخدام — C-04 §9 --}}
    <section aria-labelledby="limits-title" class="mt-5 rounded-card border border-slate-200 bg-slate-100/60 p-5">
        <h2 id="limits-title" class="flex items-center gap-2 text-sm font-bold text-slate-700">
            <x-ui.icon name="info" class="h-4 w-4 shrink-0 text-slate-500" />
            حدود استخدام النتيجة
        </h2>
        <p class="mt-2 text-xs leading-relaxed text-slate-600">
            هذه النتيجة إرشادية واستكشافية. لا تقيس الذكاء أو التحصيل أو الأهلية الرسمية للقبول، ولا تضمن النجاح أو الوظيفة أو الدخل. كما أنها لا تحدد تخصصًا واحدًا صحيحًا. استخدمها مع المعلومات الرسمية وتجارب الاستكشاف والحوار مع أشخاص موثوقين.
        </p>
    </section>

    {{-- أزرار التنقل --}}
    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
        <a href="{{ route('specializations.index') }}" class="btn btn-primary">تصفّح دليل التخصصات</a>
        <a href="{{ route('profile.results.index') }}" class="btn btn-secondary">سجل نتائجي</a>
    </div>
</div>
@endsection
