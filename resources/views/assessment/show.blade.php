@extends('layouts.app')

@section('title', 'استكشاف ميولك')

@section('content')
<div id="assessment-app"
     dir="rtl"
     class="assessment-stage mx-auto w-full max-w-[1440px]"
     data-session-id="{{ $session->id }}"
     data-complete-url="{{ route('assessment.sessions.complete', $session->id) }}"
     data-save-base-url="/assessment/sessions/{{ $session->id }}/answers"
     data-session-url="{{ route('assessment.show', $session->id) }}">

    <script id="assessment-initial-data" type="application/json">
        {!! json_encode($initialData, JSON_UNESCAPED_UNICODE) !!}
    </script>

    <section class="assessment-panel overflow-hidden rounded-2xl border shadow-card" aria-labelledby="assessment-question-title">
        {{-- شريط علوي خفيف فقط للتقدم والحفظ والتنقل السريع. --}}
        <div class="assessment-toolbar border-b px-2.5 py-2 sm:px-5 sm:py-3 lg:px-6">
            <div class="flex min-w-0 items-center gap-2.5 sm:gap-4">
                <button type="button" id="top-prev-btn"
                        class="assessment-top-button hidden h-8 w-8 shrink-0 items-center justify-center rounded-full border transition disabled:pointer-events-none disabled:opacity-35 sm:inline-flex sm:h-9 sm:w-9"
                        aria-label="الموقف السابق">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.1" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

                <div class="assessment-options-pane min-w-0 flex-1 rounded-xl border p-2.5 sm:rounded-2xl sm:p-4 lg:p-5">
                    <div class="mb-1 flex min-w-0 items-center justify-between gap-2 sm:mb-1.5 sm:gap-3">
                        <span id="progress-position-text" class="shrink-0 text-[10px] font-bold text-slate-700 sm:text-sm">
                            الموقف <span id="current-position-num">1</span> من <span id="total-questions-num">18</span>
                        </span>
                        <div id="save-status-indicator" class="min-w-0 truncate text-[9px] font-medium sm:text-xs" aria-live="polite">
                            <span id="save-status-icon" class="inline-flex align-middle"></span>
                            <span id="save-status-text" class="text-slate-400">جاهز</span>
                        </div>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100 sm:h-2"
                         role="progressbar" id="progress-bar-container"
                         aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                        <div id="progress-bar-fill"
                             class="assessment-progress-fill h-full rounded-full transition-[width] duration-300 ease-out"
                             style="width:0%"></div>
                    </div>
                </div>

                <button type="button" id="top-next-btn"
                        class="assessment-top-button hidden h-8 w-8 shrink-0 items-center justify-center rounded-full border transition disabled:pointer-events-none disabled:opacity-35 sm:inline-flex sm:h-9 sm:w-9"
                        aria-label="الموقف التالي">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.1" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-2 sm:p-5 lg:p-6">
            <div class="flex min-w-0 flex-col gap-2.5 sm:gap-4 lg:flex-row lg:items-start lg:gap-6">
                {{-- اليمين: السؤال + خريطة المواقف. --}}
                <aside class="min-w-0 lg:sticky lg:top-24 lg:w-[430px] lg:shrink-0 xl:w-[480px]">
                    <div class="assessment-question-card rounded-xl border p-2.5 shadow-sm sm:rounded-2xl sm:p-5">
                        <div class="mb-1.5 flex items-center justify-between gap-2 sm:mb-4 sm:gap-3">
                            <span id="scenario-badge" class="assessment-question-badge hidden items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold sm:inline-flex sm:gap-2 sm:px-3 sm:py-1.5 sm:text-xs">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h4.5M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                                </svg>
                                الموقف <span id="badge-num" class="ms-0.5">1</span>
                            </span>
                            <span class="assessment-question-kicker hidden text-xs font-medium sm:inline">استكشاف ميولك</span>
                        </div>

                        <h1 id="assessment-question-title" class="sr-only">استكشاف ميولك</h1>
                        <h2 id="scenario-text" class="assessment-question-text text-[0.8rem] font-extrabold leading-[1.45rem] sm:text-xl sm:leading-[1.9]">
                            جارٍ تحميل الموقف…
                        </h2>
                        <p class="assessment-question-hint mt-0.5 hidden text-[9px] leading-4 sm:mt-2 sm:block sm:text-sm sm:leading-7">
                            اختر التصرف الأقرب لك، ثم قيّم الخيارات اختياريًا.
                        </p>

                        <div class="assessment-map mt-5 hidden rounded-xl border p-3.5 lg:block">
                            <div class="mb-2 flex items-center justify-between gap-2 text-xs">
                                <span class="font-bold">خريطة المواقف</span>
                                <span id="processed-summary-text" class="assessment-map-muted">
                                    تمت معالجة <span id="processed-count-num">0</span> من 18
                                </span>
                            </div>
                            <div id="questions-nav-grid" class="grid grid-cols-6 gap-1.5 sm:grid-cols-9 lg:grid-cols-6"></div>
                        </div>
                    </div>
                </aside>

                {{-- اليسار: الخيارات والتقييم والتنقل. --}}
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

                    <div class="mb-1.5 hidden items-end justify-between gap-3 sm:mb-3 sm:flex">
                        <div>
                            <p class="text-[11px] font-extrabold text-slate-900 sm:text-lg">اختر تصرفًا واحدًا</p>
                            <p class="mt-0.5 text-[8.5px] leading-3.5 text-slate-500 sm:text-sm sm:leading-relaxed">
                                اختر التصرف الأساسي، وقيّم كل خيار مباشرة.
                            </p>
                        </div>
                    </div>

                    <fieldset id="options-fieldset" class="min-w-0">
                        <legend class="sr-only">اختر التصرف الأساسي ثم قيّم الخيارات اختياريًا</legend>
                        <p class="sr-only">مقياس التقييم من لا يشبهني إطلاقًا إلى يشبهني جدًا.</p>
                        <div id="options-container" class="min-w-0 space-y-1.5 sm:space-y-3"></div>
                    </fieldset>

                    <div class="mt-2 border-t border-slate-200/70 pt-2 sm:mt-4 sm:pt-3.5">
                        <p class="assessment-special-label mb-1.5 hidden text-[8.5px] font-bold sm:mb-2.5 sm:block sm:text-sm">إذا لم يناسبك أي تصرف أو لم تستطع الحكم:</p>
                        <div class="grid min-w-0 grid-cols-2 gap-1.5 sm:gap-2">
                            <label id="none-fit-card" class="relative flex min-h-9 min-w-0 cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1.5 transition hover:border-brand-200 hover:bg-brand-50/40 sm:min-h-11 sm:gap-2.5 sm:rounded-xl sm:px-3 sm:py-2.5">
                                <input type="radio" name="response_state" value="none_selected" id="none-fit-radio"
                                       class="h-4 w-4 shrink-0 border-slate-300 text-brand-600 focus:ring-brand-600 sm:h-5 sm:w-5">
                                <span class="min-w-0 text-[8.5px] font-semibold leading-4 text-slate-800 sm:text-sm sm:leading-6">لا يشبهني أي من هذه التصرفات</span>
                            </label>
                            <label id="cannot-judge-card" class="relative flex min-h-9 min-w-0 cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2 py-1.5 transition hover:border-brand-200 hover:bg-brand-50/40 sm:min-h-11 sm:gap-2.5 sm:rounded-xl sm:px-3 sm:py-2.5">
                                <input type="radio" name="response_state" value="unable_to_judge" id="cannot-judge-radio"
                                       class="h-5 w-5 shrink-0 border-slate-300 text-brand-600 focus:ring-brand-600">
                                <span class="min-w-0 text-[11px] font-semibold leading-5 text-slate-800 sm:text-sm sm:leading-6">لا أستطيع الحكم على هذا الموقف</span>
                            </label>
                        </div>
                    </div>

                    <div class="assessment-nav-dock sticky bottom-1 z-20 -mx-0.5 mt-2 flex min-w-0 items-center justify-between gap-1.5 rounded-xl border p-1.5 shadow-pop backdrop-blur sm:static sm:mx-0 sm:mt-4 sm:rounded-none sm:border-x-0 sm:border-b-0 sm:bg-transparent sm:p-0 sm:pt-3.5 sm:shadow-none">
                        <button type="button" id="prev-btn" class="btn btn-secondary min-h-10 min-w-0 flex-1 px-2.5 py-1.5 text-xs disabled:pointer-events-none disabled:opacity-40 sm:min-h-11 sm:max-w-40 sm:px-3 sm:py-2 sm:text-sm">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            <span>السابق</span>
                        </button>

                        <button type="button" id="next-btn" class="btn btn-primary min-h-10 min-w-0 flex-1 px-2.5 py-1.5 text-xs sm:min-h-11 sm:max-w-40 sm:px-3 sm:py-2 sm:text-sm">
                            <span>التالي</span>
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 19.5-7.5-7.5 7.5-7.5" />
                            </svg>
                        </button>

                        <button type="button" id="complete-btn" class="btn btn-success hidden min-h-10 min-w-0 flex-1 px-2.5 py-1.5 text-xs sm:min-h-11 sm:max-w-44 sm:px-3 sm:py-2 sm:text-sm">
                            <span>إكمال التقييم</span>
                        </button>
                    </div>
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
