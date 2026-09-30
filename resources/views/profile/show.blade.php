@extends('layouts.app')

@section('title', 'حسابي')

@section('content')
@php
    $nameInitial = \Illuminate\Support\Str::substr(trim((string) $user->name), 0, 1);
@endphp

<section class="profile-experience profile-experience-v2 mx-auto w-full" dir="rtl" aria-labelledby="profile-title">
    <header class="profile-mobile-header">
        <span class="profile-mobile-avatar" aria-hidden="true">{{ $nameInitial ?: 'م' }}</span>
        <div class="min-w-0">
            <p class="profile-mobile-eyebrow">حسابي</p>
            <h1 id="profile-title">{{ $user->name }}</h1>
            <p class="profile-mobile-email" dir="auto">{{ $user->email }}</p>
        </div>
    </header>

    <section class="profile-mobile-section" aria-labelledby="profile-actions-title">
        <h2 id="profile-actions-title">الخدمات</h2>

        <div class="profile-mobile-list">
            <a href="{{ route('assessment.intro') }}" class="profile-mobile-row">
                <span class="profile-mobile-row-icon" aria-hidden="true">
                    <x-ui.icon name="target" class="h-5 w-5" />
                </span>
                <span class="profile-mobile-row-copy">
                    <strong>استكشاف ميولك</strong>
                    <small>ابدأ تقييمًا جديدًا أو تابع تقييمك الحالي</small>
                </span>
                <x-ui.icon name="chevron-start" class="profile-mobile-chevron h-5 w-5" />
            </a>

            <a href="{{ route('profile.results.index') }}" class="profile-mobile-row">
                <span class="profile-mobile-row-icon" aria-hidden="true">
                    <x-ui.icon name="activity" class="h-5 w-5" />
                </span>
                <span class="profile-mobile-row-copy">
                    <strong>سجل نتائجي</strong>
                    <small>راجع النتائج السابقة وافتح أي نتيجة محفوظة</small>
                </span>
                <x-ui.icon name="chevron-start" class="profile-mobile-chevron h-5 w-5" />
            </a>

            <a href="{{ route('specializations.index') }}" class="profile-mobile-row">
                <span class="profile-mobile-row-icon" aria-hidden="true">
                    <x-ui.icon name="book-open" class="h-5 w-5" />
                </span>
                <span class="profile-mobile-row-copy">
                    <strong>دليل التخصصات</strong>
                    <small>استكشف التخصصات وقارن بينها بهدوء</small>
                </span>
                <x-ui.icon name="chevron-start" class="profile-mobile-chevron h-5 w-5" />
            </a>
        </div>
    </section>

    <section class="profile-mobile-section profile-mobile-note" aria-labelledby="profile-guide-title">
        <h2 id="profile-guide-title">كيف تستخدم مسارك؟</h2>

        <div class="profile-steps-simple">
            <div><span>1</span><p>أكمل التقييم</p></div>
            <div><span>2</span><p>راجع نتيجتك</p></div>
            <div><span>3</span><p>قارن التخصصات</p></div>
        </div>
    </section>
</section>
@endsection
