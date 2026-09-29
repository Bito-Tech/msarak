@extends('layouts.app')

@section('title', 'حسابي')

@section('content')
@php
    $nameInitial = \Illuminate\Support\Str::substr(trim((string) $user->name), 0, 1);
@endphp

<section class="profile-experience mx-auto w-full" dir="rtl" aria-labelledby="profile-title">
    <header class="profile-hero relative overflow-hidden rounded-[2rem] border">
        <div class="profile-hero-glow profile-hero-glow-one" aria-hidden="true"></div>
        <div class="profile-hero-glow profile-hero-glow-two" aria-hidden="true"></div>

        <div class="relative z-10 flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-7 lg:p-9">
            <div class="flex min-w-0 items-center gap-4 sm:gap-5">
                <span class="profile-avatar inline-flex h-20 w-20 shrink-0 items-center justify-center rounded-[1.6rem] text-3xl font-extrabold sm:h-24 sm:w-24 sm:text-4xl" aria-hidden="true">
                    {{ $nameInitial ?: 'م' }}
                </span>

                <div class="min-w-0">
                    <span class="profile-account-badge inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-extrabold">
                        <x-ui.icon name="check" class="h-4 w-4" />
                        حساب طالب
                    </span>
                    <h1 id="profile-title" class="profile-name mt-3 break-words font-extrabold">{{ $user->name }}</h1>
                    <p class="profile-email mt-2 break-all" dir="auto">{{ $user->email }}</p>
                </div>
            </div>

            <div class="profile-hero-note rounded-2xl px-4 py-3">
                <span class="block text-xs font-extrabold">مساحتك الشخصية</span>
                <strong class="mt-1 block">نتائجك وخطواتك في مكان واحد</strong>
            </div>
        </div>
    </header>

    <div class="profile-section-heading mt-5">
        <p class="profile-kicker text-sm font-extrabold">اختصارات الحساب</p>
        <h2 class="profile-section-title mt-1 font-extrabold">ماذا تريد أن تفعل الآن؟</h2>
    </div>

    <div class="profile-action-grid mt-4 grid gap-4 lg:grid-cols-2">
        <section aria-labelledby="assessment-title" class="profile-action-card profile-action-card-primary relative overflow-hidden rounded-3xl border p-5 sm:p-7">
            <div class="profile-action-glow" aria-hidden="true"></div>

            <div class="relative z-10">
                <div class="flex items-start justify-between gap-4">
                    <span class="profile-action-icon inline-flex h-14 w-14 items-center justify-center rounded-2xl" aria-hidden="true">
                        <x-ui.icon name="target" class="h-7 w-7" />
                    </span>
                    <span class="profile-action-label rounded-full px-3 py-1.5 text-xs font-extrabold">الخطوة الأساسية</span>
                </div>

                <h2 id="assessment-title" class="profile-action-title mt-5 font-extrabold">استكشاف ميولك</h2>
                <p class="profile-action-copy mt-3">
                    انتقل إلى التقييم لاستكشاف ميولك. وإذا كان لديك تقييم غير مكتمل، يمكنك متابعته من الصفحة نفسها.
                </p>

                <a href="{{ route('assessment.intro') }}"
                   class="profile-action-primary mt-6 inline-flex min-h-14 w-full items-center justify-center gap-2 rounded-2xl px-5 font-extrabold sm:w-auto">
                    الذهاب إلى التقييم
                    <x-ui.icon name="arrow-end" class="h-5 w-5" />
                </a>
            </div>
        </section>

        <section aria-labelledby="results-title" class="profile-action-card profile-action-card-results relative overflow-hidden rounded-3xl border p-5 sm:p-7">
            <div class="relative z-10">
                <div class="flex items-start justify-between gap-4">
                    <span class="profile-action-icon profile-results-icon inline-flex h-14 w-14 items-center justify-center rounded-2xl" aria-hidden="true">
                        <x-ui.icon name="activity" class="h-7 w-7" />
                    </span>
                    <span class="profile-results-label rounded-full px-3 py-1.5 text-xs font-extrabold">محفوظة لديك</span>
                </div>

                <h2 id="results-title" class="profile-action-title mt-5 font-extrabold">سجل نتائجي</h2>
                <p class="profile-action-copy mt-3">
                    راجع كل نتائج استكشاف الميول التي أتممتها سابقًا، وافتح أي نتيجة كما صدرت وقتها.
                </p>

                <a href="{{ route('profile.results.index') }}"
                   class="profile-action-secondary mt-6 inline-flex min-h-14 w-full items-center justify-center gap-2 rounded-2xl px-5 font-extrabold sm:w-auto">
                    عرض سجل نتائجي
                    <x-ui.icon name="arrow-end" class="h-5 w-5" />
                </a>
            </div>
        </section>
    </div>

    <section class="profile-guide mt-5 rounded-3xl border p-5 sm:p-7">
        <div class="flex items-start gap-3">
            <span class="profile-guide-icon inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl" aria-hidden="true">
                <x-ui.icon name="info" class="h-5 w-5" />
            </span>
            <div>
                <p class="profile-kicker text-sm font-extrabold">كيف تستخدم حسابك؟</p>
                <h2 class="profile-guide-title mt-1 font-extrabold">رحلة بسيطة من الاستكشاف إلى المقارنة</h2>
            </div>
        </div>

        <div class="profile-guide-steps mt-5 grid gap-3 md:grid-cols-3">
            <div class="profile-guide-step rounded-2xl p-4">
                <span class="profile-guide-number inline-flex h-8 w-8 items-center justify-center rounded-xl font-extrabold">1</span>
                <strong class="mt-3 block">أكمل التقييم</strong>
                <p class="mt-1.5">أجب عن المواقف بهدوء كما تصفك أنت.</p>
            </div>
            <div class="profile-guide-step rounded-2xl p-4">
                <span class="profile-guide-number inline-flex h-8 w-8 items-center justify-center rounded-xl font-extrabold">2</span>
                <strong class="mt-3 block">راجع نتيجتك</strong>
                <p class="mt-1.5">افهم المجالات الأعلى والتخصصات المقترحة للاستكشاف.</p>
            </div>
            <div class="profile-guide-step rounded-2xl p-4">
                <span class="profile-guide-number inline-flex h-8 w-8 items-center justify-center rounded-xl font-extrabold">3</span>
                <strong class="mt-3 block">قارن الخيارات</strong>
                <p class="mt-1.5">استخدم دليل التخصصات والمقارنة قبل اتخاذ قرارك.</p>
            </div>
        </div>
    </section>
</section>
@endsection
