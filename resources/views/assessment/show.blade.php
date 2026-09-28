@extends('layouts.app')

@section('title', 'استكشاف ميولك')

@section('content')
<div id="assessment-app"
     class="mx-auto w-full max-w-6xl"
     data-session-id="{{ $session->id }}"
     data-complete-url="{{ route('assessment.sessions.complete', $session->id) }}"
     data-save-base-url="/assessment/sessions/{{ $session->id }}/answers"
     data-session-url="{{ route('assessment.show', $session->id) }}">

    <script id="assessment-initial-data" type="application/json">
        {!! json_encode($initialData, JSON_UNESCAPED_UNICODE) !!}
    </script>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-card" aria-labelledby="assessment-question-title">
        {{-- Progress bar + duplicate navigation controls, matching the approved visual direction. --}}
        <div class="border-b border-slate-100 px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3 sm:gap-5">
                <button type="button" id="top-prev-btn"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-xs transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 disabled:pointer-events-none disabled:opacity-35"
                        aria-label="الموقف السابق">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                    </svg>
                </button>

                <div class="min-w-0 flex-1">
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                        <span id="progress-position-text" class="text-sm font-bold text-slate-700">
                            الموقف <span id="current-position-num">1</span> من <span id="total-questions-num">18</span>
                        </span>
                        <div id="save-status-indicator" class="flex items-center gap-1.5 text-xs font-medium" aria-live="polite">
                            <span id="save-status-icon" class="inline-flex items-center"></span>
                            <span id="save-status-text" class="text-slate-400">جاهز</span>
                        </div>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100"
                         role="progressbar" id="progress-bar-container"
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <div id="progress-bar-fill"
                             class="h-full rounded-full bg-gradient-to-l from-brand-500 to-brand-700 transition-[width] duration-300 ease-out"
                             style="width: 0%;"></div>
                    </div>
                </div>

                <button type="button" id="top-next-btn"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-xs transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 disabled:pointer-events-none disabled:opacity-35"
                        aria-label="الموقف التالي">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-7">
            {{-- The data contract exposes no category/domain label, so the supported question position is shown instead. --}}
            <div class="mb-5 text-end">
                <span id="scenario-badge" class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3.5 py-1.5 text-xs font-bold text-brand-700 ring-1 ring-inset ring-brand-100">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h4.5M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                    </svg>
                    الموقف <span id="badge-num" class="ms-0.5">1</span>
                </span>
            </div>

            <header class="mb-5 text-end">
                <h1 id="assessment-question-title" class="sr-only">استكشاف ميولك</h1>
                <h2 id="scenario-text" class="text-xl font-extrabold leading-[1.65] text-slate-900 sm:text-2xl lg:text-[1.75rem]">
                    جارٍ تحميل الموقف…
                </h2>
                <p class="mt-1 text-sm leading-relaxed text-slate-500 sm:text-base">
                    اختر الإجابة الأقرب لك، ثم قيّم مدى انطباق كل تصرف عليك. التقييمات الإضافية اختيارية.
                </p>
            </header>

            <div id="error-alert" class="mb-5 hidden rounded-xl border border-danger-300 bg-danger-50 p-4 text-danger-700" role="alert">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-2.5">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-danger-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <p id="error-alert-message" class="text-sm font-medium">تعذر حفظ الإجابة. حاول مرة أخرى.</p>
                    </div>
                    <button type="button" id="retry-save-btn" class="btn btn-danger px-3 text-sm">إعادة المحاولة</button>
                </div>
            </div>

            <fieldset id="options-fieldset">
                <legend class="sr-only">اختر التصرف الأساسي ثم قيّم الخيارات اختياريًا</legend>
                <p class="sr-only">مقياس التقييم من لا يشبهني إطلاقًا إلى يشبهني جدًا.</p>
                <div id="options-container" class="space-y-3"></div>
            </fieldset>

            {{-- Existing answer states are preserved as two distinct choices. --}}
            <div class="mt-5 border-t border-slate-100 pt-4">
                <p class="mb-3 text-sm font-bold text-brand-700">إذا لم يناسبك أي تصرف أو لم تستطع الحكم:</p>
                <div class="grid gap-2.5 sm:grid-cols-2">
                    <label id="none-fit-card" class="relative flex min-h-12 cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 transition hover:border-brand-200 hover:bg-brand-50/40">
                        <input type="radio" name="response_state" value="none_selected" id="none-fit-radio"
                               class="h-5 w-5 shrink-0 border-slate-300 text-brand-600 focus:ring-brand-600">
                        <span class="text-sm font-semibold leading-relaxed text-slate-800">لا يشبهني أي من هذه التصرفات</span>
                    </label>
                    <label id="cannot-judge-card" class="relative flex min-h-12 cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 transition hover:border-brand-200 hover:bg-brand-50/40">
                        <input type="radio" name="response_state" value="unable_to_judge" id="cannot-judge-radio"
                               class="h-5 w-5 shrink-0 border-slate-300 text-brand-600 focus:ring-brand-600">
                        <span class="text-sm font-semibold leading-relaxed text-slate-800">لا أستطيع الحكم على هذا الموقف</span>
                    </label>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
                <button type="button" id="prev-btn" class="btn btn-secondary min-w-28 text-sm disabled:pointer-events-none disabled:opacity-40">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                    </svg>
                    <span>السابق</span>
                </button>

                <div class="flex items-center gap-2 sm:gap-3">
                    <button type="button" id="next-btn" class="btn btn-primary min-w-28 text-sm">
                        <span>التالي</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                    <button type="button" id="complete-btn" class="btn btn-success hidden min-w-32 text-sm">
                        <span>إكمال التقييم</span>
                    </button>
                </div>
            </div>

            {{-- Keep the existing quick navigation, but visually integrate it into the same surface. --}}
            <nav aria-label="التنقل بين مواقف التقييم" class="mt-5 border-t border-slate-100 pt-4">
                <div class="mb-2 flex items-center justify-between gap-3 text-xs text-slate-500">
                    <span class="font-semibold">الانتقال إلى موقف</span>
                    <span id="processed-summary-text">تمت معالجة <span id="processed-count-num">0</span> من 18 موقفًا</span>
                </div>
                <div id="questions-nav-grid" class="grid grid-cols-6 gap-1.5 sm:grid-cols-9 lg:grid-cols-18"></div>
            </nav>
        </div>
    </section>

    <div id="conflict-modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur-xs" role="dialog" aria-modal="true" aria-labelledby="conflict-modal-title">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl sm:p-7">
            <h3 id="conflict-modal-title" class="text-lg font-bold text-slate-900">تأكيد تقييم التصرف</h3>
            <p class="mt-3 text-sm leading-relaxed text-slate-700">
                اخترت هذا التصرف بوصفه ما يشبهك أكثر، لكنك قيمته بأنه لا يشبهك. هل تريد الاحتفاظ بهذه الإجابة؟
            </p>
            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" id="conflict-review-btn" class="btn btn-secondary text-sm">مراجعة الاختيار</button>
                <button type="button" id="conflict-keep-btn" class="btn btn-primary text-sm">الاحتفاظ بالإجابة</button>
            </div>
        </div>
    </div>

    <div id="completion-modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur-xs" role="dialog" aria-modal="true" aria-labelledby="completion-modal-title">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl sm:p-7">
            <h3 id="completion-modal-title" class="text-lg font-bold text-slate-900">تأكيد إكمال التقييم</h3>
            <p class="mt-3 text-sm leading-relaxed text-slate-700">
                تأكد من مراجعة إجاباتك. بعد إكمال التقييم لن تتمكن من تعديل هذه الجلسة.
            </p>
            <div id="completion-modal-error" class="mt-3 hidden text-xs font-semibold text-danger-600">
                لم تكتمل المواقف المطلوبة بعد. راجع المواقف التي تحتاج معالجة، ثم حاول إكمال التقييم مرة أخرى.
            </div>
            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" id="completion-cancel-btn" class="btn btn-secondary text-sm">متابعة المراجعة</button>
                <button type="button" id="completion-confirm-btn" class="btn btn-success text-sm">إكمال التقييم</button>
            </div>
        </div>
    </div>
</div>
@endsection
