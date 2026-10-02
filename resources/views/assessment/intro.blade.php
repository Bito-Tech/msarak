@extends('layouts.app')

@section('title', 'استكشاف ميولك')

@section('content')
<div class="assessment-onboarding" dir="rtl">
    <section class="assessment-onboarding-shell" aria-labelledby="assessment-onboarding-title">
        <div class="assessment-onboarding-hero">
            <div class="assessment-onboarding-copy">
                <p class="assessment-onboarding-kicker">استكشاف ميولك — ابدأ من نفسك، لا من توقعات الآخرين</p>

                <h1 id="assessment-onboarding-title" class="assessment-onboarding-title">
                    اكتشف المجالات الأقرب لك
                </h1>

                <p class="assessment-onboarding-lead">
                    أجب عن مواقف قصيرة تساعدك على فهم ميولك، وتمنحك صورة أوضح عن المجالات التي تستحق منك الاستكشاف أكثر.
                </p>

                <div class="assessment-onboarding-meta" aria-label="معلومات التقييم">
                    <span>18 موقفًا</span>
                    <span aria-hidden="true">•</span>
                    <span>حوالي 10 دقائق</span>
                    <span aria-hidden="true">•</span>
                    <span>حفظ تلقائي للتقدم</span>
                </div>

                @if ($activeSession)
                    <div class="assessment-onboarding-status" aria-live="polite">
                        <span class="assessment-onboarding-status-dot" aria-hidden="true"></span>
                        لديك تقييم غير مكتمل — يمكنك المتابعة من حيث توقفت
                    </div>
                @endif

                <form method="POST" action="{{ route('assessment.sessions.store') }}" class="assessment-onboarding-actions">
                    @csrf
                    <button type="submit" class="assessment-onboarding-cta">
                        {{ $activeSession ? 'متابعة التقييم' : 'بدء التقييم' }}
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                        </svg>
                    </button>
                </form>

                <p class="assessment-onboarding-disclaimer">
                    التقييم أداة <strong>استكشافية وإرشادية</strong>، وليس اختبار قدرات أو حكمًا نهائيًا على تخصصك.
                </p>
            </div>

            <div class="assessment-onboarding-image-wrap">
                <img
                    src="{{ asset('assets/assessment/assessment-intro-student.webp') }}"
                    alt="طالب ثانوي يفكر في مستقبله الدراسي ويخطط لاختياراته"
                    class="assessment-onboarding-image"
                    loading="eager"
                    fetchpriority="high">
            </div>
        </div>

        <section class="assessment-onboarding-guide" aria-labelledby="assessment-guide-title">
            <header class="assessment-onboarding-guide-head">
                <p>قبل أن تبدأ</p>
                <h2 id="assessment-guide-title">طريقة الإجابة</h2>
            </header>

            <div class="assessment-onboarding-steps">
                <article class="assessment-onboarding-step">
                    <span class="assessment-onboarding-step-num">01</span>
                    <div>
                        <h3>اختر التصرف الأقرب لك</h3>
                        <p>اختر التصرف الذي يشبهك فعلًا، لا التصرف الذي يبدو أفضل أو أكثر قبولًا.</p>
                    </div>
                </article>

                <article class="assessment-onboarding-step">
                    <span class="assessment-onboarding-step-num">02</span>
                    <div class="min-w-0">
                        <h3>قيّم بقية الخيارات إن رغبت</h3>
                        <p>يمكنك استخدام التقييم لتوضيح مدى شبه كل تصرف بك، وترك أي خيار دون تقييم.</p>

                        @php
                            $ratingPreview = [
                                ['icon' => 'emoji-strongly-dislike.svg', 'label' => 'لا يشبهني'],
                                ['icon' => 'emoji-dislike.svg', 'label' => 'قليلًا'],
                                ['icon' => 'emoji-neutral.svg', 'label' => 'محايد'],
                                ['icon' => 'emoji-like.svg', 'label' => 'يشبهني'],
                                ['icon' => 'emoji-strongly-like.svg', 'label' => 'جداً'],
                            ];
                        @endphp

                        <div class="assessment-onboarding-emoji-row" aria-label="درجات التقييم الاختياري">
                            @foreach ($ratingPreview as $rating)
                                <div class="assessment-onboarding-emoji-item">
                                    <img src="/assets/assessment/emoji/{{ $rating['icon'] }}" alt="" class="assessment-onboarding-emoji-image">
                                    <span>{{ $rating['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>

                <article class="assessment-onboarding-step">
                    <span class="assessment-onboarding-step-num">03</span>
                    <div>
                        <h3>استخدم البديل عند الحاجة</h3>
                        <p><strong>لا يشبهني أي من هذه التصرفات:</strong> إذا فهمت الموقف لكن لا ينطبق عليك أي خيار.</p>
                        <p><strong>لا أستطيع الحكم على هذا الموقف:</strong> إذا لم تكن لديك معلومات كافية لإجابة موثوقة.</p>
                    </div>
                </article>
            </div>
        </section>

        <div class="assessment-onboarding-reminders" aria-label="معلومات مفيدة">
            <div><strong>حفظ تلقائي</strong><span>يمكنك التوقف والمتابعة لاحقًا</span></div>
            <div><strong>لا توجد إجابة مثالية</strong><span>اختر ما يصفك أنت</span></div>
            <div><strong>النتيجة للاستكشاف</strong><span>لتوجيهك نحو مجالات أقرب لميولك</span></div>
        </div>
    </section>
</div>
@endsection
