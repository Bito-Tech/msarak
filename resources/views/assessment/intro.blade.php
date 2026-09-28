@extends('layouts.app')

@section('title', 'استكشاف ميولك')

@section('content')
<div class="assessment-intro assessment-intro-screen mx-auto w-full" dir="rtl">
    <section class="assessment-intro-shell grid gap-4 lg:grid-cols-[0.98fr_1.02fr]" aria-labelledby="intro-title">
        {{-- البطاقة اليمنى: التعريف والبدء --}}
        <article class="assessment-intro-hero-card relative isolate flex min-h-0 flex-col justify-between overflow-hidden rounded-3xl border p-5 shadow-card sm:p-7 lg:p-8">
            <img
                src="https://images.unsplash.com/photo-1758270703884-e1c40c43465f?auto=format&fit=crop&fm=jpg&q=82&w=1800"
                alt=""
                class="assessment-intro-hero-image absolute inset-0 -z-20 h-full w-full object-cover"
                loading="eager"
                fetchpriority="high">
            <div class="assessment-intro-hero-wash absolute inset-0 -z-10" aria-hidden="true"></div>

            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="assessment-intro-pill assessment-intro-pill-light inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        نحو 10–15 دقيقة
                    </span>
                    <span class="assessment-intro-pill assessment-intro-pill-light inline-flex items-center rounded-full px-3 py-1.5 text-xs font-bold">
                        18 موقفًا
                    </span>
                </div>

                <p class="assessment-intro-eyebrow assessment-intro-kicker mt-5 font-bold">ابدأ من نفسك، لا من توقعات الآخرين</p>

                <h1 id="intro-title" class="assessment-intro-title mt-2 max-w-2xl font-extrabold">
                    استكشاف ميولك
                </h1>

                <p class="assessment-intro-lead mt-4 max-w-2xl">
                    مواقف قصيرة تساعدك على ملاحظة الأنشطة والتصرفات التي تميل إليها، لتكوّن صورة أوضح عن المجالات التي تستحق منك الاستكشاف.
                </p>

                <div class="assessment-intro-note assessment-intro-note-light mt-5 max-w-2xl rounded-2xl border p-4">
                    <p class="assessment-intro-note-text">
                        هذا التقييم <strong>استكشافي وإرشادي</strong>، وليس اختبار قدرات أو تشخيص شخصية، ولا يحدد تخصصًا أو مهنة واحدة مناسبة لك بشكل نهائي.
                    </p>
                </div>

                <div class="assessment-intro-action mt-6">
                    @if ($activeSession)
                        <div class="assessment-intro-resume-text mb-3 flex items-center gap-2 font-bold">
                            <span class="assessment-action-status-dot inline-flex h-2.5 w-2.5 rounded-full" aria-hidden="true"></span>
                            لديك تقييم غير مكتمل — تم حفظ تقدمك ويمكنك المتابعة مباشرة
                        </div>
                        <form method="POST" action="{{ route('assessment.sessions.store') }}">
                            @csrf
                            <button type="submit" class="assessment-intro-cta assessment-intro-cta-primary inline-flex min-h-16 w-full max-w-md items-center justify-center gap-3 rounded-2xl px-10 py-4 text-xl font-extrabold sm:w-auto sm:min-w-[340px]">
                                متابعة التقييم
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                                </svg>
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('assessment.sessions.store') }}">
                            @csrf
                            <button type="submit" class="assessment-intro-cta assessment-intro-cta-primary inline-flex min-h-16 w-full max-w-md items-center justify-center gap-3 rounded-2xl px-10 py-4 text-xl font-extrabold sm:w-auto sm:min-w-[340px]">
                                بدء التقييم
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                                </svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="assessment-intro-bottom-hint mt-5 rounded-2xl border p-4">
                <p class="assessment-intro-photo-label font-bold">أثناء الإجابة</p>
                <p class="assessment-intro-photo-text mt-1 font-extrabold">
                    اختر ما يشبهك فعلًا، ثم استخدم التقييم لتوضيح درجة انطباق بقية التصرفات عليك.
                </p>
            </div>
        </article>

        {{-- البطاقة اليسرى: طريقة الإجابة --}}
        <article class="assessment-intro-guide-card flex min-h-0 flex-col rounded-3xl border p-5 shadow-card sm:p-6 lg:p-7" aria-labelledby="guide-heading">
            <header class="flex items-end justify-between gap-3">
                <div>
                    <p class="assessment-intro-eyebrow assessment-guide-kicker font-bold">ثلاث خطوات فقط</p>
                    <h2 id="guide-heading" class="assessment-guide-title mt-1 font-extrabold text-slate-900">طريقة الإجابة</h2>
                </div>
                @if ($activeSession)
                    <span class="assessment-resume-chip rounded-full px-3 py-1.5 text-xs font-bold">تم حفظ تقدمك</span>
                @endif
            </header>

            <div class="assessment-intro-steps mt-4 grid min-h-0 flex-1 gap-3">
                <section class="assessment-intro-step rounded-2xl border p-4">
                    <div class="flex items-start gap-3">
                        <span class="assessment-step-number inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold">1</span>
                        <div>
                            <h3 class="assessment-step-title font-extrabold text-slate-900">اختر التصرف الأقرب لك</h3>
                            <p class="assessment-step-text mt-1.5 text-slate-600">
                                اختر تصرفًا واحدًا يمثل طريقتك المعتادة، لا التصرف الذي يبدو أفضل أو أكثر قبولًا.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="assessment-intro-step rounded-2xl border p-4">
                    <div class="flex items-start gap-3">
                        <span class="assessment-step-number inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold">2</span>
                        <div class="min-w-0 flex-1">
                            <h3 class="assessment-step-title font-extrabold text-slate-900">قيّم الخيارات إن رغبت</h3>
                            <p class="assessment-step-text mt-1.5 text-slate-600">
                                التقييمات مستقلة واختيارية، ويمكنك تقييم أي عدد من التصرفات وترك البقية دون تقييم.
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-5 gap-2" aria-label="مستويات التقييم الاختياري">
                        @php
                            $ratingPreview = [
                                ['icon' => 'emoji-strongly-dislike.png', 'label' => 'لا يشبهني'],
                                ['icon' => 'emoji-dislike.png', 'label' => 'قليلًا'],
                                ['icon' => 'emoji-neutral.png', 'label' => 'محايد'],
                                ['icon' => 'emoji-like.png', 'label' => 'يشبهني'],
                                ['icon' => 'emoji-strongly-like.png', 'label' => 'جداً'],
                            ];
                        @endphp
                        @foreach ($ratingPreview as $rating)
                            <div class="assessment-intro-emoji flex min-w-0 flex-col items-center gap-1 rounded-xl px-1.5 py-2 text-center">
                                <img src="/assets/assessment/emoji/{{ $rating['icon'] }}" alt="" class="h-9 w-9 object-contain">
                                <span class="assessment-intro-emoji-label font-semibold text-slate-600">{{ $rating['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="assessment-intro-step rounded-2xl border p-4">
                    <div class="flex items-start gap-3">
                        <span class="assessment-step-number inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold">3</span>
                        <div>
                            <h3 class="assessment-step-title font-extrabold text-slate-900">استخدم البديل عند الحاجة</h3>
                            <div class="assessment-step-text mt-1.5 space-y-1.5 text-slate-600">
                                <p><strong class="text-slate-900">لا يشبهني أي من هذه التصرفات:</strong> فهمت الموقف، لكن لا ينطبق عليك أي خيار.</p>
                                <p><strong class="text-slate-900">لا أستطيع الحكم على هذا الموقف:</strong> لا تملك معلومات كافية لإجابة موثوقة.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="assessment-intro-footer mt-4 rounded-2xl border p-4">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="flex items-center gap-2.5">
                        <span class="assessment-mini-icon inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" aria-hidden="true">✓</span>
                        <p class="assessment-intro-footer-text font-semibold text-slate-700">حفظ تلقائي</p>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="assessment-mini-icon inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" aria-hidden="true">↔</span>
                        <p class="assessment-intro-footer-text font-semibold text-slate-700">مراجعة أي موقف</p>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="assessment-mini-icon inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" aria-hidden="true">◎</span>
                        <p class="assessment-intro-footer-text font-semibold text-slate-700">أجب وفق ما يشبهك</p>
                    </div>
                </div>
            </div>
        </article>
    </section>
</div>
@endsection
