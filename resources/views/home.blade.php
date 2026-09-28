@extends('layouts.app')

@section('title', 'الرئيسية')

@section('content')
    <section aria-labelledby="home-title" class="rounded-card border border-slate-200 bg-white px-5 py-8 sm:px-10 sm:py-12 lg:py-14">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold text-brand-700">توجيه أكاديمي ومهني لطلاب الثانوية في اليمن</p>
            <h1 id="home-title" class="mt-3 text-3xl font-bold leading-tight text-slate-900 sm:text-4xl lg:text-5xl">
                استكشف ميولك وتعرّف إلى التخصصات
            </h1>
            <p class="mt-4 max-w-xl text-base leading-relaxed text-slate-700 sm:text-lg">
                تعرّف إلى طبيعة التخصصات ومهاراتها، واستخدم تقييم الميول لتبدأ استكشاف الخيارات التي تستحق اهتمامك.
            </p>

            <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                @guest
                    <a href="{{ route('specializations.index') }}" class="btn btn-primary text-center">تصفح التخصصات</a>
                    <a href="{{ route('register') }}" class="btn btn-secondary text-center">إنشاء حساب لبدء التقييم</a>
                @else
                    @if (auth()->user()->role === 'student')
                        <a href="{{ route('assessment.intro') }}" class="btn btn-primary text-center">استكشاف ميولك</a>
                        <a href="{{ route('specializations.index') }}" class="btn btn-secondary text-center">تصفح التخصصات</a>
                    @elseif (auth()->user()->role === 'admin')
                        <a href="{{ route('admin.assessment-versions.index') }}" class="btn btn-primary text-center">إدارة التقييم</a>
                        <a href="{{ route('specializations.index') }}" class="btn btn-secondary text-center">تصفح التخصصات</a>
                    @else
                        <a href="{{ route('specializations.index') }}" class="btn btn-primary text-center">تصفح التخصصات</a>
                    @endif
                @endguest
            </div>
        </div>
    </section>

    <section aria-label="طرق الاستكشاف" class="mt-6 grid gap-4 md:grid-cols-2">
        <a href="{{ route('specializations.index') }}" class="card-surface card-lift group flex flex-col p-5 sm:p-6">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 text-brand-700" aria-hidden="true">
                <x-ui.icon name="info" class="h-5 w-5" />
            </span>
            <h2 class="mt-4 text-xl font-bold text-slate-900">دليل التخصصات</h2>
            <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600 sm:text-base">
                تصفح التخصصات الأكاديمية المتاحة، واطّلع على تفاصيل كل تخصص، أو قارن بين تخصصين.
            </p>
            <span class="mt-4 text-sm font-semibold text-brand-700 group-hover:underline">تصفح الدليل</span>
        </a>

        @php
            $assessmentHref = auth()->check() && auth()->user()->role === 'student'
                ? route('assessment.intro')
                : (auth()->guest() ? route('register') : null);
        @endphp
        @if ($assessmentHref)
            <a href="{{ $assessmentHref }}" class="card-surface card-lift group flex flex-col p-5 sm:p-6">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-brand-50 text-brand-700" aria-hidden="true">
                    <x-ui.icon name="target" class="h-5 w-5" />
                </span>
                <h2 class="mt-4 text-xl font-bold text-slate-900">استكشاف ميولك</h2>
                <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600 sm:text-base">
                    يساعدك هذا التقييم على استكشاف الأنشطة والمجالات التي قد تستمتع بها أو ترغب في التعرف إليها أكثر.
                </p>
                <span class="mt-4 text-sm font-semibold text-brand-700 group-hover:underline">{{ auth()->guest() ? 'أنشئ حسابًا للبدء' : 'ابدأ الاستكشاف' }}</span>
            </a>
        @endif
    </section>

    <p class="mx-auto mt-8 max-w-2xl text-sm leading-relaxed text-slate-600">
        التقييم أداة استكشافية وإرشادية، وليس اختبارًا للقدرات أو تشخيصًا للشخصية، ولا يحدد تخصصًا أو مهنة واحدة مناسبة لك بشكل نهائي.
    </p>
@endsection
