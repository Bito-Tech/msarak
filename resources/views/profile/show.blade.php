@extends('layouts.app')

@section('title', 'حسابي')

@section('content')
<div class="mx-auto max-w-3xl space-y-5">
    <header class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="text-sm font-bold text-brand-700">حسابي</p>
        <h1 class="mt-2 break-words text-2xl font-bold text-slate-900 sm:text-3xl">{{ $user->name }}</h1>
        <p class="mt-2 break-all text-sm text-slate-600" dir="auto">{{ $user->email }}</p>
    </header>

    <div class="grid gap-4 sm:grid-cols-2">
        <section aria-labelledby="assessment-title" class="rounded-2xl border border-brand-200 bg-brand-50 p-5 sm:p-6">
            <h2 id="assessment-title" class="text-xl font-bold text-slate-900">استكشاف ميولك</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-700">
                انتقل إلى التقييم. إذا كان لديك تقييم غير مكتمل، يمكنك متابعته من هناك.
            </p>
            <a href="{{ route('assessment.intro') }}" class="btn btn-primary mt-5 w-full text-sm">الذهاب إلى التقييم</a>
        </section>

        <section aria-labelledby="results-title" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 id="results-title" class="text-xl font-bold text-slate-900">نتائجي</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-700">
                كل نتائج استكشاف الميول التي أتممتها محفوظة لديك، وتفتح كل نتيجة كما صدرت وقتها.
            </p>
            <a href="{{ route('profile.results.index') }}" class="btn btn-secondary mt-5 w-full text-sm">عرض سجل نتائجي</a>
        </section>
    </div>
</div>
@endsection
