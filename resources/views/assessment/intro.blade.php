@extends('layouts.app')

@section('title', 'استكشاف ميولك')

@section('content')
<div class="assessment-intro-v2 mx-auto w-full" dir="rtl">
    <section class="assessment-intro-v2-hero overflow-hidden rounded-[2rem] border" aria-labelledby="intro-title">
        <div class="assessment-intro-v2-layout">
            <div class="assessment-intro-v2-content">
                <div class="assessment-intro-v2-meta flex flex-wrap items-center gap-2">
                    <span class="assessment-intro-v2-chip inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold">
                        <x-ui.icon name="clock" class="h-4 w-4" />
                        نحو 10–15 دقيقة
                    </span>
                    <span class="assessment-intro-v2-chip inline-flex items-center rounded-full px-3 py-1.5 text-xs font-bold">
                        18 موقفًا
                    </span>
                </div>

                <p class="assessment-intro-v2-kicker mt-5 font-extrabold">
                    ابدأ من نفسك، لا من توقعات الآخرين
                </p>

                <h1 id="intro-title" class="assessment-intro-v2-title mt-2 font-extrabold">
                    استكشاف ميولك
                </h1>

                <p class="assessment-intro-v2-lead mt-4">
                    مواقف قصيرة تساعدك على ملاحظة الأنشطة والتصرفات التي تميل إليها، لتكوّن صورة أوضح عن المجالات التي تستحق منك الاستكشاف.
                </p>

                @if ($activeSession)
                    <div class="assessment-intro-v2-resume mt-5 flex items-start gap-2.5 rounded-xl px-3.5 py-3" role="status">
                        <span class="assessment-intro-v2-status-dot mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full" aria-hidden="true"></span>
                        <p class="font-bold">
                            لديك تقييم غير مكتمل — تم حفظ تقدمك ويمكنك المتابعة مباشرة.
                        </p>
                    </div>
                @endif

                <form method="POST" action="{{ route('assessment.sessions.store') }}" class="mt-6">
                    @csrf
                    <button type="submit"
                            class="assessment-intro-v2-cta inline-flex min-h-14 w-full items-center justify-center gap-3 rounded-xl px-7 font-extrabold sm:w-auto sm:min-w-[280px]">
                        <span>{{ $activeSession ? 'متابعة التقييم' : 'بدء التقييم' }}</span>
                        <x-ui.icon name="arrow-end" class="h-5 w-5" />
                    </button>
                </form>

                <p class="assessment-intro-v2-disclaimer mt-4">
                    هذا التقييم <strong>استكشافي وإرشادي</strong>، وليس اختبار قدرات أو تشخيص شخصية، ولا يحدد تخصصًا أو مهنة واحدة مناسبة لك بشكل نهائي.
                </p>
            </div>

            <div class="assessment-intro-v2-visual" aria-hidden="true">
                <img
                    src="/assets/assessment/question-card-student.jpg"
                    alt=""
                    class="assessment-intro-v2-image"
                    loading="eager"
                    fetchpriority="high">
                <div class="assessment-intro-v2-image-shade"></div>
                <div class="assessment-intro-v2-image-caption">
                    <span>أثناء الإجابة</span>
                    <strong>اختر ما يشبهك فعلًا</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="assessment-intro-v2-guide mt-4 rounded-2xl border" aria-labelledby="guide-heading">
        <div class="assessment-intro-v2-guide-head">
            <div>
                <p class="assessment-intro-v2-guide-kicker font-bold">ثلاث خطوات فقط</p>
                <h2 id="guide-heading" class="assessment-intro-v2-guide-title mt-1 font-extrabold">طريقة الإجابة</h2>
            </div>
            <p class="assessment-intro-v2-guide-note">لا تحتاج إلى التفكير في الإجابة المثالية؛ اختر الأقرب لك.</p>
        </div>

        <div class="assessment-intro-v2-steps">
            <article class="assessment-intro-v2-step">
                <span class="assessment-intro-v2-number" aria-hidden="true">1</span>
                <div>
                    <h3 class="font-extrabold">اختر التصرف الأقرب لك</h3>
                    <p>اختر تصرفًا واحدًا يمثل طريقتك المعتادة، لا التصرف الذي يبدو أفضل أو أكثر قبولًا.</p>
                </div>
            </article>

            <article class="assessment-intro-v2-step">
                <span class="assessment-intro-v2-number" aria-hidden="true">2</span>
                <div class="min-w-0">
                    <h3 class="font-extrabold">قيّم الخيارات إن رغبت</h3>
                    <p>التقييمات مستقلة واختيارية. يمكنك تحديد مدى انطباق بقية التصرفات عليك أو تركها دون تقييم.</p>

                    <div class="assessment-intro-v2-rating mt-3" aria-label="مقياس التقييم الاختياري">
                        @php
                            $ratingPreview = [
                                ['icon' => 'emoji-strongly-dislike.png', 'label' => 'لا يشبهني'],
                                ['icon' => 'emoji-dislike.png', 'label' => 'قليلًا'],
                                ['icon' => 'emoji-neutral.png', 'label' => 'محايد'],
                                ['icon' => 'emoji-like.png', 'label' => 'يشبهني'],
                                ['icon' => 'emoji-strongly-like.png', 'label' => 'يشبهني جدًا'],
                            ];
                        @endphp
                        @foreach ($ratingPreview as $rating)
                            <span class="assessment-intro-v2-rating-item">
                                <img src="/assets/assessment/emoji/{{ $rating['icon'] }}" alt="">
                                <span>{{ $rating['label'] }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="assessment-intro-v2-step">
                <span class="assessment-intro-v2-number" aria-hidden="true">3</span>
                <div>
                    <h3 class="font-extrabold">استخدم البديل عند الحاجة</h3>
                    <p>
                        <strong>لا يشبهني أي من هذه التصرفات:</strong> فهمت الموقف، لكن لا ينطبق عليك أي خيار.
                        <br>
                        <strong>لا أستطيع الحكم على هذا الموقف:</strong> لا تملك معلومات كافية لإجابة موثوقة.
                    </p>
                </div>
            </article>
        </div>

        <div class="assessment-intro-v2-foot">
            <span><x-ui.icon name="check" class="h-4 w-4" /> حفظ تلقائي</span>
            <span><x-ui.icon name="clock" class="h-4 w-4" /> يمكنك مراجعة أي موقف</span>
            <span><x-ui.icon name="info" class="h-4 w-4" /> الإجابة وفق ما يشبهك أنت</span>
        </div>
    </section>
</div>
@endsection
