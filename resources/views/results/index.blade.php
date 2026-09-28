@extends('layouts.app')

@section('title', 'سجل نتائجي')

@section('content')
<div class="mx-auto max-w-3xl">

    <header class="mb-6">
        <p class="text-sm font-bold text-brand-700">حسابي / النتائج</p>
        <h1 class="mt-2 text-3xl font-extrabold leading-tight text-slate-900">سجل نتائجي</h1>
        <p class="mt-2 text-base leading-relaxed text-slate-700">
            نتائج استكشاف الميول التي أتممتها، الأحدث أولًا. كل نتيجة محفوظة كما صدرت وقتها.
        </p>
    </header>

    @if ($results->isEmpty())
        {{-- حالة No Result (F-04) --}}
        <x-ui.empty-state icon="clock"
            title="لا توجد نتائج محفوظة بعد"
            description="لم تُكمل أي تقييم حتى الآن. ابدأ رحلة استكشاف الميول وستظهر نتيجتك هنا مباشرة.">
            <a href="{{ route('assessment.intro') }}" class="btn btn-primary mt-2">ابدأ استكشاف ميولك</a>
        </x-ui.empty-state>
    @else
        <ul class="space-y-3">
            @foreach ($results as $item)
                <li>
                    <a href="{{ route('results.show', $item) }}"
                       class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-colors hover:border-brand-200 hover:bg-brand-50/30 sm:p-5">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-100 text-brand-700"
                              aria-hidden="true">
                            <x-ui.icon name="check" class="h-5 w-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-base font-bold text-slate-900 group-hover:text-brand-700">
                                نتيجة التقييم — {{ $item->created_at?->format('Y-m-d') }}
                            </span>
                            <span class="mt-1 block break-words text-xs leading-relaxed text-slate-600 sm:text-sm">
                                إصدار الأداة {{ $item->scoring_version }} · إصدار الدليل {{ $item->catalog_version }}
                            </span>
                        </span>
                        <x-ui.icon name="chevron-start" class="h-5 w-5 shrink-0 text-slate-400 transition-transform group-hover:-translate-x-0.5 group-hover:text-brand-600" />
                    </a>
                </li>
            @endforeach
        </ul>
    @endif

    <div class="mt-7">
        <a href="{{ route('assessment.intro') }}" class="btn btn-secondary w-full sm:w-auto">الذهاب إلى التقييم</a>
    </div>
</div>
@endsection
