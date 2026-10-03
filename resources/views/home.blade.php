@extends('layouts.app')

@section('title', 'اكتشف ميولك واختر تخصصك الجامعي بوعي')
@section('seo_description', 'اكتشف ميولك المهنية مع مسارك، واستكشف التخصصات الجامعية وطبيعة الدراسة والمهارات والمسارات المهنية لتبدأ قرارك بعد الثانوية بوعي أكبر.')

@section('content')
<div class="home-landing mx-auto w-full" dir="rtl">
    <section class="home-landing-hero home-landing-hero-v3 relative isolate overflow-hidden rounded-[2rem] border shadow-card" aria-labelledby="home-title">
        <div class="home-landing-scene absolute inset-0 -z-30" aria-hidden="true">
            <div class="home-landing-scene-glow home-landing-scene-glow-one"></div>
            <div class="home-landing-scene-glow home-landing-scene-glow-two"></div>
        </div>

        <div class="home-landing-visual absolute inset-y-0 left-0 -z-20 hidden w-[48%] overflow-hidden lg:block" aria-hidden="true">
            <img
                src="/assets/home/masarak-home-student-hero.jpg"
                alt=""
                class="home-landing-visual-image h-full w-full object-cover"
                loading="eager"
                fetchpriority="high">
            <div class="home-landing-visual-fade absolute inset-0"></div>
        </div>

        <div class="home-landing-wave absolute inset-x-0 bottom-0 -z-10 h-24 sm:h-28 lg:h-32" aria-hidden="true"></div>

        <div class="home-landing-content home-landing-content-v3 relative flex min-h-full flex-col justify-center px-5 py-8 sm:px-8 sm:py-10 lg:w-[58%] lg:px-12 lg:py-10 xl:px-16">
            <div class="home-landing-badges flex flex-wrap items-center gap-2">
                <span class="home-landing-badge inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold">
                    <span class="home-landing-dot h-2 w-2 rounded-full" aria-hidden="true"></span>
                    منصة توجيه أكاديمي ومهني لطلاب الثانوية في اليمن
                </span>
            </div>

            <div class="home-landing-copy mt-5 max-w-[780px]">
                <p class="home-landing-kicker font-extrabold">مسارك يبدأ من فهم أفضل لنفسك</p>

                <h1 id="home-title" class="home-landing-title home-landing-title-v3 mt-2 font-extrabold">
                    اكتشف ميولك، وافهم خياراتك،
                    <span class="home-landing-title-accent block">واختر بوعي أكبر</span>
                </h1>

                <p class="home-landing-lead home-landing-lead-v3 mt-4">
                    مسارك يساعدك على استكشاف ميولك والتعرّف إلى التخصصات الأكاديمية بطريقة هادئة وواضحة، لتبدأ رحلتك الجامعية بصورة أقرب لما يناسبك.
                </p>
            </div>

            <div class="home-landing-actions home-landing-actions-v3 mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                @guest
                    <a href="{{ route('register') }}"
                       class="home-landing-btn home-landing-btn-primary-v3 inline-flex items-center justify-center gap-3 rounded-2xl px-8 text-center font-extrabold">
                        استكشف ميولك
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                        </svg>
                    </a>
                @else
                    @if (auth()->user()->role === 'student')
                        <a href="{{ route('assessment.intro') }}"
                           class="home-landing-btn home-landing-btn-primary-v3 inline-flex items-center justify-center gap-3 rounded-2xl px-8 text-center font-extrabold">
                            استكشف ميولك
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                            </svg>
                        </a>
                    @elseif (auth()->user()->role === 'admin')
                        <a href="{{ route('admin.assessment-versions.index') }}"
                           class="home-landing-btn home-landing-btn-primary-v3 inline-flex items-center justify-center gap-3 rounded-2xl px-8 text-center font-extrabold">
                            إدارة التقييم
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                            </svg>
                        </a>
                    @endif
                @endguest

                <a href="{{ route('specializations.index') }}"
                   class="home-landing-btn home-landing-btn-secondary-v3 inline-flex items-center justify-center gap-3 rounded-2xl px-8 text-center font-extrabold">
                    تصفح دليل التخصصات
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                    </svg>
                </a>
            </div>

            <div class="home-landing-meta home-landing-meta-v3 mt-7 grid gap-3 sm:grid-cols-3">
                <div class="home-landing-meta-item flex items-center gap-3">
                    <span class="home-landing-meta-icon inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">✓</span>
                    <div>
                        <strong class="block">تقييم إرشادي</strong>
                        <span class="block">لاستكشاف ميولك</span>
                    </div>
                </div>

                <div class="home-landing-meta-item flex items-center gap-3">
                    <span class="home-landing-meta-icon inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">◎</span>
                    <div>
                        <strong class="block">10 تخصصات</strong>
                        <span class="block">موضحة بالتفصيل</span>
                    </div>
                </div>

                <div class="home-landing-meta-item flex items-center gap-3">
                    <span class="home-landing-meta-icon inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">↔</span>
                    <div>
                        <strong class="block">مقارنة بين التخصصات</strong>
                        <span class="block">لتسهيل قرارك</span>
                    </div>
                </div>
            </div>

            <p class="home-landing-note home-landing-note-v3 mt-5 max-w-3xl">
                التقييم أداة استكشافية وإرشادية تساعدك على تكوين صورة أوضح عن ميولك، ولا يحدد تخصصًا أو مهنة واحدة مناسبة لك بشكل نهائي.
            </p>
        </div>

        <div class="home-landing-mobile-visual relative z-10 mx-4 mb-4 overflow-hidden rounded-[1.4rem] lg:hidden" aria-hidden="true">
            <img
                src="/assets/home/masarak-home-student-hero.jpg"
                alt=""
                class="h-full w-full object-cover"
                loading="eager">
            <div class="absolute inset-0 bg-gradient-to-t from-[#edf7f3]/60 via-transparent to-transparent"></div>
        </div>
    </section>
</div>
@endsection
