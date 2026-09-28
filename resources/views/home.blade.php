@extends('layouts.app')

@section('title', 'الرئيسية')

@section('content')
<div class="home-screen mx-auto w-full" dir="rtl">
    <section class="home-grid grid gap-4 lg:grid-cols-[0.9fr_1.1fr]" aria-labelledby="home-title">
        {{-- البطاقة اليمنى: الهوية والرسالة الرئيسية --}}
        <article class="home-hero relative isolate flex min-h-0 flex-col justify-between overflow-hidden rounded-3xl border p-5 shadow-card sm:p-7 lg:p-9">
            <img
                src="https://images.unsplash.com/photo-1758270703884-e1c40c43465f?auto=format&fit=crop&fm=jpg&q=82&w=1800"
                alt=""
                class="home-hero-image absolute inset-0 -z-20 h-full w-full object-cover"
                loading="eager"
                fetchpriority="high">
            <div class="home-hero-wash absolute inset-0 -z-10" aria-hidden="true"></div>

            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="home-pill inline-flex items-center rounded-full px-3 py-1.5 text-xs font-bold">لطلاب الثانوية في اليمن</span>
                    <span class="home-pill inline-flex items-center rounded-full px-3 py-1.5 text-xs font-bold">استكشاف قبل الاختيار</span>
                </div>

                <p class="home-kicker mt-5 font-bold">قرارك يبدأ بصورة أوضح عن نفسك</p>

                <h1 id="home-title" class="home-title mt-2 max-w-3xl font-extrabold">
                    استكشف ميولك وتعرّف إلى التخصصات
                </h1>

                <p class="home-lead mt-4 max-w-2xl">
                    تعرّف إلى طبيعة التخصصات ومهاراتها، واستخدم تقييم الميول لتبدأ استكشاف الخيارات التي تستحق اهتمامك.
                </p>

                <div class="home-insight mt-5 max-w-2xl rounded-2xl border p-4">
                    <p class="font-semibold">
                        لا نختار عنك تخصصك؛ نساعدك على جمع إشارات أوضح عن ميولك، ثم نضع أمامك معلومات منظمة تساعدك على المقارنة والاستكشاف.
                    </p>
                </div>
            </div>

            <div class="home-hero-footer mt-5 flex flex-wrap items-center gap-3">
                <span class="home-mini-stat inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold">
                    <span aria-hidden="true">✓</span>
                    تقييم ميول إرشادي
                </span>
                <span class="home-mini-stat inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold">
                    <span aria-hidden="true">↔</span>
                    مقارنة بين التخصصات
                </span>
            </div>
        </article>

        {{-- البطاقة اليسرى: نقاط الدخول الرئيسية --}}
        <article class="home-actions-card flex min-h-0 flex-col rounded-3xl border p-5 shadow-card sm:p-6 lg:p-7">
            <header>
                <p class="home-kicker text-sm font-bold">ابدأ من المسار المناسب لك</p>
                <h2 class="home-actions-title mt-1 font-extrabold text-slate-900">ماذا تريد أن تستكشف الآن؟</h2>
                <p class="home-actions-copy mt-2 text-slate-600">
                    اختر أحد المسارين؛ يمكنك العودة للآخر في أي وقت.
                </p>
            </header>

            <div class="home-action-list mt-5 grid min-h-0 flex-1 gap-4">
                <section class="home-action-block flex min-h-0 flex-col justify-between rounded-2xl border p-5">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="home-action-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                                <x-ui.icon name="target" class="h-6 w-6" />
                            </span>
                            <div>
                                <p class="home-action-eyebrow text-xs font-bold">ابدأ بنفسك</p>
                                <h3 class="home-action-title font-extrabold text-slate-900">استكشاف ميولك</h3>
                            </div>
                        </div>

                        <p class="home-action-copy mt-3 text-slate-600">
                            يساعدك التقييم على استكشاف الأنشطة والمجالات التي قد تستمتع بها أو ترغب في التعرف إليها أكثر.
                        </p>

                        <div class="mt-3 grid grid-cols-5 gap-2" aria-label="مقياس تقييم الميول">
                            @php
                                $homeEmoji = [
                                    'emoji-strongly-dislike.png',
                                    'emoji-dislike.png',
                                    'emoji-neutral.png',
                                    'emoji-like.png',
                                    'emoji-strongly-like.png',
                                ];
                            @endphp
                            @foreach ($homeEmoji as $emoji)
                                <span class="home-emoji inline-flex items-center justify-center rounded-xl">
                                    <img src="/assets/assessment/emoji/{{ $emoji }}" alt="" class="h-8 w-8 object-contain">
                                </span>
                            @endforeach
                        </div>
                    </div>

                    @guest
                        <a href="{{ route('register') }}" class="home-big-btn home-big-btn-primary mt-4 inline-flex w-full items-center justify-center gap-3 rounded-2xl text-center font-extrabold">
                            إنشاء حساب لبدء التقييم
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                            </svg>
                        </a>
                    @else
                        @if (auth()->user()->role === 'student')
                            <a href="{{ route('assessment.intro') }}" class="home-big-btn home-big-btn-primary mt-4 inline-flex w-full items-center justify-center gap-3 rounded-2xl text-center font-extrabold">
                                استكشاف ميولك
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                                </svg>
                            </a>
                        @elseif (auth()->user()->role === 'admin')
                            <a href="{{ route('admin.assessment-versions.index') }}" class="home-big-btn home-big-btn-primary mt-4 inline-flex w-full items-center justify-center gap-3 rounded-2xl text-center font-extrabold">
                                إدارة التقييم
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                                </svg>
                            </a>
                        @endif
                    @endguest
                </section>

                <section class="home-action-block flex min-h-0 flex-col justify-between rounded-2xl border p-5">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="home-action-icon inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                                <x-ui.icon name="info" class="h-6 w-6" />
                            </span>
                            <div>
                                <p class="home-action-eyebrow text-xs font-bold">استكشف الخيارات</p>
                                <h3 class="home-action-title font-extrabold text-slate-900">دليل التخصصات</h3>
                            </div>
                        </div>

                        <p class="home-action-copy mt-3 text-slate-600">
                            تصفح التخصصات الأكاديمية المتاحة، واطّلع على تفاصيل كل تخصص، ثم قارن بين الخيارات التي تهمك.
                        </p>
                    </div>

                    <a href="{{ route('specializations.index') }}" class="home-big-btn home-big-btn-secondary mt-4 inline-flex w-full items-center justify-center gap-3 rounded-2xl text-center font-extrabold">
                        تصفح التخصصات
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                        </svg>
                    </a>
                </section>
            </div>

            <p class="home-disclaimer mt-4 text-slate-500">
                التقييم أداة استكشافية وإرشادية، وليس اختبارًا للقدرات أو تشخيصًا للشخصية، ولا يحدد تخصصًا أو مهنة واحدة مناسبة لك بشكل نهائي.
            </p>
        </article>
    </section>
</div>
@endsection
