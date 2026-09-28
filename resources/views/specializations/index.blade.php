@extends('layouts.app')

@section('title', 'دليل التخصصات')

@section('content')
    <section aria-labelledby="specializations-title" class="mx-auto max-w-5xl pb-28">
        <header class="mb-7">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="max-w-xl">
                    <h1 id="specializations-title" class="text-3xl font-extrabold leading-tight text-slate-900 sm:text-4xl">
                        دليل التخصصات
                    </h1>
                    <p class="mt-3 text-base leading-relaxed text-slate-700 sm:text-lg">
                        تصفح التخصصات الأكاديمية المتاحة، واطّلع على تفاصيل كل تخصص، أو قارن بين تخصصين.
                    </p>
                </div>
            </div>
        </header>

        {{-- شبكة التخصصات: بطاقة معرض بأيقونة دلالية وشريط علوي موحد الهوية --}}
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($specializations as $specialization)
                @php
                    $specName = $specialization['name'] ?? 'بدون اسم';
                @endphp
                <article class="card-surface flex flex-col p-0">
                    <div class="flex flex-1 flex-col p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <x-ui.spec-icon :id="$specialization['id'] ?? null" :name="$specName" />
                            <span class="text-xs font-bold text-slate-400" aria-hidden="true">{{ $loop->iteration }}</span>
                        </div>
                        <h2 class="mt-4 text-xl font-bold text-slate-900">{{ $specName }}</h2>
                        <p class="mt-2 flex-1 leading-relaxed text-slate-600 line-clamp-3">
                            {{ $specialization['description']??'لا يوجد تفاصيل' }}
                        </p>
                        <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4">
                            <a href="{{ route('specializations.show', $specialization['id'] ?? '') }}"
                               class="btn btn-primary btn-pill text-sm">
                                التفاصيل
                            </a>
                            <button type="button"
                                    class="compare-toggle btn btn-secondary text-sm"
                                    data-id="{{ $specialization['id'] ?? '' }}"
                                    data-name="{{ $specialization['name'] ?? '' }}">
                                <x-ui.icon name="plus" class="h-4 w-4 pointer-events-none" />
                                أضف للمقارنة
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- حالة الفراغ — تظهر فقط إن لم توجد تخصصات (نص موقت بانتظار اعتماد ملاطف) --}}
        @if (empty($specializations) || count($specializations) === 0)
            <x-ui.empty-state title="لا توجد تخصصات معروضة حاليًا" description="سنضيف التخصصات قريبًا. تابعنا." icon="info" class="mt-8" />
        @endif
    </section>

    {{-- صينية المقارنة: لاصقة أسفل الشاشة تبقى ظاهرة أثناء التصفح (عقد specializations.js: ids + flex|hidden) --}}
    <div id="compare-bar" class="fixed inset-x-4 bottom-4 z-40 mx-auto flex hidden max-w-2xl flex-wrap items-center justify-between gap-3 rounded-card border border-brand-200 bg-white p-3 text-sm shadow-pop sm:p-4">
        <p class="min-w-0 flex-1 text-slate-700" role="status" aria-live="polite">
            تم اختيار: <span id="compare-bar-names" class="font-semibold text-brand-800"></span>
        </p>
        <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto">
            <a id="compare-bar-link" class="btn btn-primary text-sm">
                قارن الآن
            </a>
            <button type="button" id="compare-bar-clear"
                    class="btn btn-secondary text-sm hover:border-danger-300 hover:bg-danger-50 hover:text-danger-700">
                <x-ui.icon name="x-mark" class="h-4 w-4 pointer-events-none" />
                إفراغ الاختيار
            </button>
        </div>
    </div>
@endsection
