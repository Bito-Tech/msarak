@extends('layouts.app')

@section('title', 'الرئيسية')

@section('content')
<div class="home-landing mx-auto w-full" dir="rtl">
    <section class="home-landing-hero relative isolate overflow-hidden rounded-[2rem] border shadow-card" aria-labelledby="home-title">
        <img
            src="https://images.unsplash.com/photo-1758270703884-e1c40c43465f?auto=format&fit=crop&fm=jpg&q=84&w=2000"
            alt=""
            class="home-landing-photo absolute inset-0 -z-30 h-full w-full object-cover"
            loading="eager"
            fetchpriority="high">
        <div class="home-landing-veil absolute inset-0 -z-20" aria-hidden="true"></div>
        <div class="home-landing-orb home-landing-orb-one absolute -z-10 rounded-full" aria-hidden="true"></div>
        <div class="home-landing-orb home-landing-orb-two absolute -z-10 rounded-full" aria-hidden="true"></div>

        <div class="home-landing-content relative flex h-full flex-col justify-center px-5 py-8 sm:px-8 sm:py-10 lg:px-14 lg:py-12 xl:px-16">
            <div class="home-landing-badges flex flex-wrap items-center gap-2">
                <span class="home-landing-badge inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold">
                    <span class="home-landing-dot h-2 w-2 rounded-full" aria-hidden="true"></span>
                    منصة توجيه لطلاب الثانوية في اليمن
                </span>
                <span class="home-landing-badge inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold">
                    اكتشف قبل أن تختار
                </span>
            </div>

            <div class="home-landing-copy mt-6 max-w-[760px]">
                <p class="home-landing-kicker font-extrabold">مسارك يبدأ من فهم أفضل لنفسك</p>

                <h1 id="home-title" class="home-landing-title mt-2 font-extrabold">
                    اكتشف ميولك، وافهم خياراتك، واختر بوعي أكبر
                </h1>

                <p class="home-landing-lead mt-5">
                    مسارك يساعدك على استكشاف ميولك والتعرّف إلى التخصصات الأكاديمية بطريقة هادئة وواضحة، لتبدأ رحلتك الجامعية بصورة أقرب لما يناسبك.
                </p>
            </div>

            <div class="home-landing-actions mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                @guest
                    <a href="{{ route('register') }}"
                       class="home-landing-btn home-landing-btn-primary inline-flex items-center justify-center gap-3 rounded-2xl px-8 text-center font-extrabold">
                        ابدأ استكشاف ميولك
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                        </svg>
                    </a>
                @else
                    @if (auth()->user()->role === 'student')
                        <a href="{{ route('assessment.intro') }}"
                           class="home-landing-btn home-landing-btn-primary inline-flex items-center justify-center gap-3 rounded-2xl px-8 text-center font-extrabold">
                            استكشف ميولك
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                            </svg>
                        </a>
                    @elseif (auth()->user()->role === 'admin')
                        <a href="{{ route('admin.assessment-versions.index') }}"
                           class="home-landing-btn home-landing-btn-primary inline-flex items-center justify-center gap-3 rounded-2xl px-8 text-center font-extrabold">
                            إدارة التقييم
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                            </svg>
                        </a>
                    @endif
                @endguest

                <a href="{{ route('specializations.index') }}"
                   class="home-landing-btn home-landing-btn-secondary inline-flex items-center justify-center gap-3 rounded-2xl px-8 text-center font-extrabold">
                    تصفح دليل التخصصات
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                    </svg>
                </a>
            </div>

            <div class="home-landing-meta mt-8 flex flex-wrap items-center gap-x-5 gap-y-3">
                <span class="inline-flex items-center gap-2 font-bold">
                    <span class="home-landing-meta-icon inline-flex h-8 w-8 items-center justify-center rounded-xl" aria-hidden="true">✓</span>
                    تقييم إرشادي
                </span>
                <span class="inline-flex items-center gap-2 font-bold">
                    <span class="home-landing-meta-icon inline-flex h-8 w-8 items-center justify-center rounded-xl" aria-hidden="true">◎</span>
                    10 تخصصات للاستكشاف
                </span>
                <span class="inline-flex items-center gap-2 font-bold">
                    <span class="home-landing-meta-icon inline-flex h-8 w-8 items-center justify-center rounded-xl" aria-hidden="true">↔</span>
                    مقارنة بين التخصصات
                </span>
            </div>

            <p class="home-landing-note mt-6 max-w-3xl">
                التقييم أداة استكشافية وإرشادية تساعدك على تكوين صورة أوضح عن ميولك، ولا يحدد تخصصًا أو مهنة واحدة مناسبة لك بشكل نهائي.
            </p>
        </div>
    </section>
</div>
@endsection
