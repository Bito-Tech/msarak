@extends('layouts.app')

@section('title', 'استكشاف ميولك')

@section('content')
<div class="assessment-intro mx-auto w-full max-w-6xl space-y-5" dir="rtl">
    <section class="assessment-intro-hero overflow-hidden rounded-3xl border shadow-card" aria-labelledby="intro-title">
        <div class="grid min-h-[430px] lg:grid-cols-[1.08fr_0.92fr]">
            <div class="assessment-intro-copy flex flex-col justify-center p-5 sm:p-8 lg:p-10">
                <div class="mb-4 flex flex-wrap items-center gap-2 text-xs font-bold">
                    <span class="assessment-intro-pill inline-flex items-center gap-2 rounded-full px-3 py-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        نحو 10–15 دقيقة
                    </span>
                    <span class="assessment-intro-pill inline-flex items-center gap-2 rounded-full px-3 py-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h4.5M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                        </svg>
                        18 موقفًا
                    </span>
                </div>

                <p class="assessment-intro-eyebrow text-sm font-bold">ابدأ من نفسك، لا من توقعات الآخرين</p>
                <h1 id="intro-title" class="mt-2 text-3xl font-extrabold leading-tight text-white sm:text-4xl lg:text-[2.7rem]">
                    استكشاف ميولك
                </h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-white/85 sm:text-base">
                    مواقف قصيرة تساعدك على ملاحظة الأنشطة والتصرفات التي تميل إليها، لتكوّن صورة أوضح عن المجالات التي تستحق منك الاستكشاف.
                </p>

                <div class="assessment-intro-note mt-5 rounded-2xl border p-4">
                    <div class="flex items-start gap-3">
                        <span class="assessment-intro-note-icon inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M9.75 3.75h4.5A3.75 3.75 0 0 1 18 7.5c0 1.62-.81 2.64-2.12 3.52-.97.65-1.63 1.23-1.63 2.48v.25h-4.5v-.5c0-2.24 1.28-3.48 2.8-4.45.76-.48.95-.83.95-1.3a.75.75 0 0 0-.75-.75h-1.5a.75.75 0 0 0-.75.75v.25h-4.5V7.5a3.75 3.75 0 0 1 3.75-3.75Z" />
                            </svg>
                        </span>
                        <p class="text-xs leading-6 text-white/80 sm:text-sm">
                            هذا التقييم <strong class="text-white">استكشافي وإرشادي</strong>؛ ليس اختبار قدرات أو تشخيص شخصية، ولا يقرر تخصصًا أو مهنة واحدة نيابةً عنك.
                        </p>
                    </div>
                </div>

                @if ($activeSession)
                    <div class="assessment-resume mt-5 rounded-2xl border p-4 sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-base font-extrabold text-white sm:text-lg">لديك تقييم غير مكتمل</h2>
                                <p class="mt-1 text-xs leading-6 text-white/75 sm:text-sm">تم حفظ تقدمك، ويمكنك المتابعة من حيث توقفت.</p>
                            </div>
                            <form method="POST" action="{{ route('assessment.sessions.store') }}" class="shrink-0">
                                @csrf
                                <button type="submit" class="assessment-intro-cta inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold sm:w-auto">
                                    متابعة التقييم
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('assessment.sessions.store') }}" class="mt-6">
                        @csrf
                        <button type="submit" class="assessment-intro-cta inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl px-6 py-3 text-base font-extrabold sm:w-auto">
                            بدء التقييم
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                            </svg>
                        </button>
                    </form>
                @endif
            </div>

            <figure class="assessment-intro-visual relative min-h-[300px] overflow-hidden lg:min-h-full">
                <img
                    src="https://images.unsplash.com/photo-1758270703884-e1c40c43465f?auto=format&fit=crop&fm=jpg&q=82&w=1600"
                    alt="طلاب داخل قاعة جامعية أثناء التعلم"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="eager"
                    fetchpriority="high">
                <div class="assessment-intro-photo-overlay absolute inset-0" aria-hidden="true"></div>
                <div class="absolute inset-x-4 bottom-4 sm:inset-x-6 sm:bottom-6">
                    <div class="assessment-intro-photo-card rounded-2xl border p-4 backdrop-blur-md">
                        <p class="text-xs font-bold text-white/70">فكرتك الأساسية أثناء الإجابة</p>
                        <p class="mt-1 text-base font-extrabold leading-7 text-white sm:text-lg">
                            اختر ما يشبهك فعلًا، ثم استخدم التقييم لتوضيح درجة انطباق بقية التصرفات عليك.
                        </p>
                    </div>
                </div>
            </figure>
        </div>
    </section>

    <section class="assessment-intro-guide rounded-3xl border p-4 shadow-card sm:p-6 lg:p-7" aria-labelledby="guide-heading">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="assessment-intro-eyebrow text-xs font-bold">ثلاث خطوات فقط</p>
                <h2 id="guide-heading" class="mt-1 text-xl font-extrabold text-slate-900 sm:text-2xl">طريقة الإجابة</h2>
            </div>
            <p class="max-w-xl text-xs leading-6 text-slate-500 sm:text-sm">
                لا توجد إجابة صحيحة أو خاطئة؛ المهم أن تعبّر الإجابة عنك أنت.
            </p>
        </div>

        <div class="mt-5 grid gap-3 lg:grid-cols-3">
            <article class="assessment-intro-step rounded-2xl border p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <span class="assessment-step-number inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold">1</span>
                    <h3 class="text-base font-extrabold text-slate-900">اختر التصرف الأقرب لك</h3>
                </div>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    من بين التصرفات الأربعة اختر تصرفًا واحدًا يمثل طريقتك المعتادة، لا التصرف الذي يبدو أفضل اجتماعيًا.
                </p>
            </article>

            <article class="assessment-intro-step rounded-2xl border p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <span class="assessment-step-number inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold">2</span>
                    <h3 class="text-base font-extrabold text-slate-900">قيّم الخيارات إن رغبت</h3>
                </div>
                <p class="mt-3 text-sm leading-7 text-slate-600">
                    التقييمات الخمسة مستقلة واختيارية؛ يمكنك تقييم أي عدد من التصرفات وترك البقية دون تقييم.
                </p>

                <div class="mt-4 grid grid-cols-5 gap-1.5" aria-label="مستويات التقييم الاختياري">
                    @php
                        $ratingPreview = [
                            ['icon' => 'emoji-strongly-dislike.png', 'label' => 'لا يشبهني إطلاقًا'],
                            ['icon' => 'emoji-dislike.png', 'label' => 'لا يشبهني'],
                            ['icon' => 'emoji-neutral.png', 'label' => 'محايد'],
                            ['icon' => 'emoji-like.png', 'label' => 'يشبهني'],
                            ['icon' => 'emoji-strongly-like.png', 'label' => 'يشبهني جدًا'],
                        ];
                    @endphp
                    @foreach ($ratingPreview as $rating)
                        <div class="assessment-intro-emoji flex min-w-0 flex-col items-center gap-1 rounded-xl px-1.5 py-2 text-center">
                            <img src="/assets/assessment/emoji/{{ $rating['icon'] }}" alt="" class="h-7 w-7 object-contain sm:h-8 sm:w-8">
                            <span class="text-[8px] font-semibold leading-3 text-slate-600 sm:text-[9px]">{{ $rating['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="assessment-intro-step rounded-2xl border p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <span class="assessment-step-number inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold">3</span>
                    <h3 class="text-base font-extrabold text-slate-900">استخدم البديل عند الحاجة فقط</h3>
                </div>
                <div class="mt-3 space-y-2.5 text-sm leading-6 text-slate-600">
                    <p><strong class="text-slate-900">لا يشبهني أي من هذه التصرفات:</strong> فهمت الموقف، لكن لا ينطبق عليك أي خيار.</p>
                    <p><strong class="text-slate-900">لا أستطيع الحكم على هذا الموقف:</strong> لا تملك معلومات كافية لتكوين إجابة موثوقة.</p>
                </div>
            </article>
        </div>
    </section>

    <section class="assessment-intro-footer rounded-2xl border px-4 py-4 sm:px-5" aria-label="ملاحظات قبل البدء">
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="flex items-center gap-2.5">
                <span class="assessment-mini-icon inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" aria-hidden="true">✓</span>
                <p class="text-xs font-semibold leading-5 text-slate-700">تُحفظ إجاباتك تلقائيًا أثناء التقييم.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="assessment-mini-icon inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" aria-hidden="true">↔</span>
                <p class="text-xs font-semibold leading-5 text-slate-700">يمكنك العودة ومراجعة أي موقف.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="assessment-mini-icon inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" aria-hidden="true">◎</span>
                <p class="text-xs font-semibold leading-5 text-slate-700">أجب بهدوء وفق ما يشبهك أنت.</p>
            </div>
        </div>
    </section>
</div>
@endsection
