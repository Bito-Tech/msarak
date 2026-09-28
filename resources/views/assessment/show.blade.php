@extends('layouts.app')

@section('title', 'استكشاف ميولك')

@section('content')
<div id="assessment-app"
     dir="rtl"
     class="mx-auto w-full max-w-7xl overflow-x-hidden"
     data-session-id="{{ $session->id }}"
     data-complete-url="{{ route('assessment.sessions.complete', $session->id) }}"
     data-save-base-url="/assessment/sessions/{{ $session->id }}/answers"
     data-session-url="{{ route('assessment.show', $session->id) }}">

    <script id="assessment-initial-data" type="application/json">
        {!! json_encode($initialData, JSON_UNESCAPED_UNICODE) !!}
    </script>

    <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-card" aria-labelledby="assessment-question-title">
        {{-- شريط التقدم ثابت بصريًا أعلى الصفحة على جميع المقاسات. --}}
        <div class="border-b border-slate-100 px-3 py-3 sm:px-5 sm:py-4 lg:px-7">
            <div class="flex min-w-0 items-center gap-2.5 sm:gap-4">
                <button type="button" id="top-prev-btn"
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-xs transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 disabled:pointer-events-none disabled:opacity-35 sm:h-10 sm:w-10"
                        aria-label="الموقف السابق">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

                <div class="min-w-0 flex-1">
                    <div class="mb-1.5 flex min-w-0 items-center justify-between gap-2">
                        <span id="progress-position-text" class="shrink-0 text-xs font-bold text-slate-700 sm:text-sm">
                            الموقف <span id="current-position-num">1</span> من <span id="total-questions-num">18</span>
                        </span>
                        <div id="save-status-indicator" class="min-w-0 truncate text-[11px] font-medium sm:text-xs" aria-live="polite">
                            <span id="save-status-icon" class="inline-flex align-middle"></span>
                            <span id="save-status-text" class="text-slate-400">جاهز</span>
                        </div>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100 sm:h-2"
                         role="progressbar" id="progress-bar-container"
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <div id="progress-bar-fill"
                             class="h-full rounded-full bg-gradient-to-l from-brand-500 to-brand-700 transition-[width] duration-300 ease-out"
                             style="width: 0%;"></div>
                    </div>
                </div>

                <button type="button" id="top-next-btn"
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-xs transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 disabled:pointer-events-none disabled:opacity-35 sm:h-10 sm:w-10"
                        aria-label="الموقف التالي">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="px-3 py-4 sm:px-5 sm:py-5 lg:px-7 lg:py-7">
            {{-- على سطح المكتب: السؤال يمينًا والخيارات يسارًا. على الهاتف: تكديس عمودي كامل بلا تمرير أفقي. --}}
            <div class="flex min-w-0 flex-col gap-4 lg:flex-row lg:items-start lg:gap-7">
                <aside class="min-w-0 lg:sticky lg:top-24 lg:w-[34%] lg:shrink-0">
                    <div class="rounded-2xl bg-slate-50/80 p-4 ring-1 ring-inset ring-slate-100 sm:p-5 lg:p-6">
                        <div class="mb-3 flex items-center justify-between gap-3 lg:mb-5">
                            <span id="scenario-badge" class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-700 ring-1 ring-inset ring-brand-100">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h4.5M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                                </svg>
                                الموقف <span id="badge-num" class="ms-0.5">1</span>
                            </span>
                            <span class="hidden text-xs font-medium text-slate-400 lg:inline">استكشاف ميولك</span>
                        </div>

                        <h1 id="assessment-question-title" class="sr-only">استكشاف ميولك</h1>
                        <h2 id="scenario-text" class="text-lg font-extrabold leading-[1.75] text-slate-900 sm:text-xl lg:text-[1.55rem] lg:leading-[1.8]">
                            جارٍ تحميل الموقف…
                        </h2>
                        <p class="mt-2 text-sm leading-7 text-slate-500">
                            اختر التصرف الأقرب لك، ثم قيّم مدى انطباق كل خيار عليك. التقييمات الإضافية اختيارية.
                        </p>
                    </div>
                </aside>

                <div class="min-w-0 flex-1">
                    <div id="error-alert" class="mb-4 hidden rounded-xl border border-danger-300 bg-danger-50 p-3.5 text-danger-700" role="alert">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-2.5">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-danger-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                                <p id="error-alert-message" class="min-w-0 text-sm font-medium">تعذر حفظ الإجابة. حاول مرة أخرى.</p>
                            </div>
                            <button type="button" id="retry-save-btn" class="btn btn-danger shrink-0 px-3 text-xs sm:text-sm">إعادة المحاولة</button>
                        </div>
                    </div>

                    <div class="mb-3 flex items-end justify-between gap-3">
                        <div>
                            <p class="text-sm font-extrabold text-slate-900 sm:text-base">اختر تصرفًا واحدًا</p>
                            <p class="mt-0.5 text-xs leading-relaxed text-slate-500">التقييم أسفل كل خيار مستقل واختياري.</p>
                        </div>
                    </div>

                    <fieldset id="options-fieldset" class="min-w-0">
                        <legend class="sr-only">اختر التصرف الأساسي ثم قيّم الخيارات اختياريًا</legend>
                        <p class="sr-only">مقياس التقييم من لا يشبهني إطلاقًا إلى يشبهني جدًا.</p>
                        <div id="options-container" class="min-w-0 space-y-2.5 sm:space-y-3"></div>
                    </fieldset>

                    <div class="mt-4 border-t border-slate-100 pt-3.5">
                        <p class="mb-2.5 text-xs font-bold text-brand-700 sm:text-sm">إذا لم يناسبك أي تصرف أو لم تستطع الحكم:</p>
                        <div class="grid min-w-0 grid-cols-1 gap-2 sm:grid-cols-2">
                            <label id="none-fit-card" class="relative flex min-h-11 min-w-0 cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3 py-2.5 transition hover:border-brand-200 hover:bg-brand-50/40">
                                <input type="radio" name="response_state" value="none_selected" id="none-fit-radio"
                                       class="h-4.5 w-4.5 shrink-0 border-slate-300 text-brand-600 focus:ring-brand-600 sm:h-5 sm:w-5">
                                <span class="min-w-0 text-sm font-semibold leading-6 text-slate-800">لا يشبهني أي من هذه التصرفات</span>
                            </label>
                            <label id="cannot-judge-card" class="relative flex min-h-11 min-w-0 cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3 py-2.5 transition hover:border-brand-200 hover:bg-brand-50/40">
                                <input type="radio" name="response_state" value="unable_to_judge" id="cannot-judge-radio"
                                       class="h-4.5 w-4.5 shrink-0 border-slate-300 text-brand-600 focus:ring-brand-600 sm:h-5 sm:w-5">
                                <span class="min-w-0 text-sm font-semibold leading-6 text-slate-800">لا أستطيع الحكم على هذا الموقف</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 flex min-w-0 items-center justify-between gap-2 border-t border-slate-100 pt-3.5">
                        <button type="button" id="prev-btn" class="btn btn-secondary min-w-0 flex-1 px-3 text-sm disabled:pointer-events-none disabled:opacity-40 sm:max-w-36">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            <span>السابق</span>
                        </button>

                        <button type="button" id="next-btn" class="btn btn-primary min-w-0 flex-1 px-3 text-sm sm:max-w-36">
                            <span>التالي</span>
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                            </svg>
                        </button>

                        <button type="button" id="complete-btn" class="btn btn-success hidden min-w-0 flex-1 px-3 text-sm sm:max-w-40">
                            <span>إكمال التقييم</span>
                        </button>
                    </div>

                    <nav aria-label="التنقل بين مواقف التقييم" class="mt-4 border-t border-slate-100 pt-3.5">
                        <div class="mb-2 flex min-w-0 items-center justify-between gap-2 text-[11px] text-slate-500 sm:text-xs">
                            <span class="shrink-0 font-semibold">الانتقال إلى موقف</span>
                            <span id="processed-summary-text" class="min-w-0 truncate">تمت معالجة <span id="processed-count-num">0</span> من 18 موقفًا</span>
                        </div>
                        <div id="questions-nav-grid" class="grid min-w-0 grid-cols-6 gap-1 sm:grid-cols-9 lg:grid-cols-9 xl:grid-cols-18"></div>
                    </nav>
                </div>
            </div>
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
