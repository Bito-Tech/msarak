/**
 * Assessment Journey Engine (Issue F-03)
 * Professional Software Engineering Implementation.
 * Adheres strictly to contracts H-03 and C-03.
 * Zero RIASEC leakage, defensive state management, debounced autosave, and accessible UI.
 */

const RATING_LEVELS = [
    {
        value: -2,
        label: 'لا يشبهني إطلاقًا',
        icon: 'emoji-strongly-dislike.svg',
        activeClass: 'bg-rose-600 text-white border-rose-600 shadow-xs',
        idleClass: 'bg-rose-50/60 text-rose-800 border-rose-200 hover:bg-rose-100',
    },
    {
        value: -1,
        label: 'لا يشبهني',
        icon: 'emoji-dislike.svg',
        activeClass: 'bg-amber-600 text-white border-amber-600 shadow-xs',
        idleClass: 'bg-amber-50/60 text-amber-800 border-amber-200 hover:bg-amber-100',
    },
    {
        value: 0,
        label: 'محايد / غير متأكد',
        icon: 'emoji-neutral.svg',
        activeClass: 'bg-slate-700 text-white border-slate-700 shadow-xs',
        idleClass: 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200',
    },
    {
        value: 1,
        label: 'يشبهني',
        icon: 'emoji-like.svg',
        activeClass: 'bg-teal-600 text-white border-teal-600 shadow-xs',
        idleClass: 'bg-teal-50/60 text-teal-800 border-teal-200 hover:bg-teal-100',
    },
    {
        value: 2,
        label: 'يشبهني جدًا',
        icon: 'emoji-strongly-like.svg',
        activeClass: 'bg-emerald-600 text-white border-emerald-600 shadow-xs',
        idleClass: 'bg-emerald-50/60 text-emerald-800 border-emerald-200 hover:bg-emerald-100',
    },
];

const ARABIC_OPTION_LETTERS = ['أ', 'ب', 'ج', 'د'];

class AssessmentJourney {
    constructor(appElement) {
        this.app = appElement;
        this.sessionId = this.app.dataset.sessionId;
        this.completeUrl = this.app.dataset.completeUrl;
        this.saveBaseUrl = this.app.dataset.saveBaseUrl;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        // Domain State
        this.questions = [];
        this.answers = {}; // question_id -> { primary_option_id, none_selected, unable_to_judge, ratings: {} }
        this.currentIndex = 0;
        this.isCompleted = false;

        // Save State & Synchronization
        this.saveStatus = 'idle'; // idle | saving | saved | error
        this.debounceSaveTimer = null;
        this.pendingConflict = null;
        this.abortController = null;

        this.cacheElements();
        this.bindEvents();
        this.initData();
    }

    cacheElements() {
        this.currentPositionNum = document.getElementById('current-position-num');
        this.totalQuestionsNum = document.getElementById('total-questions-num');
        this.processedCountNum = document.getElementById('processed-count-num');
        this.progressBarFill = document.getElementById('progress-bar-fill');
        this.progressBarContainer = document.getElementById('progress-bar-container');

        this.scenarioBadge = document.getElementById('scenario-badge');
        this.badgeNum = document.getElementById('badge-num');
        this.scenarioText = document.getElementById('scenario-text');
        this.optionsContainer = document.getElementById('options-container');

        this.noneFitRadio = document.getElementById('none-fit-radio');
        this.noneFitCard = document.getElementById('none-fit-card');
        this.cannotJudgeRadio = document.getElementById('cannot-judge-radio');
        this.cannotJudgeCard = document.getElementById('cannot-judge-card');

        this.saveStatusIcon = document.getElementById('save-status-icon');
        this.saveStatusText = document.getElementById('save-status-text');

        this.errorAlert = document.getElementById('error-alert');
        this.errorAlertMessage = document.getElementById('error-alert-message');
        this.retrySaveBtn = document.getElementById('retry-save-btn');

        this.prevBtn = document.getElementById('prev-btn');
        this.nextBtn = document.getElementById('next-btn');
        this.completeBtn = document.getElementById('complete-btn');
        this.questionsNavGrid = document.getElementById('questions-nav-grid');

        // Conflict Modal
        this.conflictModal = document.getElementById('conflict-modal');
        this.conflictKeepBtn = document.getElementById('conflict-keep-btn');
        this.conflictReviewBtn = document.getElementById('conflict-review-btn');

        // Completion Modal
        this.completionModal = document.getElementById('completion-modal');
        this.completionConfirmBtn = document.getElementById('completion-confirm-btn');
        this.completionCancelBtn = document.getElementById('completion-cancel-btn');
        this.completionModalError = document.getElementById('completion-modal-error');
    }

    bindEvents() {
        this.prevBtn.addEventListener('click', () => this.goToPrevious());
        this.nextBtn.addEventListener('click', () => this.goToNext());
        this.completeBtn.addEventListener('click', () => this.openCompletionModal());

        this.noneFitRadio.addEventListener('change', () => this.handleSpecialChoice('none_selected'));
        this.cannotJudgeRadio.addEventListener('change', () => this.handleSpecialChoice('unable_to_judge'));

        this.retrySaveBtn.addEventListener('click', () => {
            if (this.questions.length === 0) {
                this.fetchSessionData();
            } else {
                this.flushSave();
            }
        });

        // Conflict modal handlers
        this.conflictKeepBtn.addEventListener('click', () => this.resolveConflict(true));
        this.conflictReviewBtn.addEventListener('click', () => this.resolveConflict(false));

        // Completion modal handlers
        this.completionConfirmBtn.addEventListener('click', () => this.submitCompletion());
        this.completionCancelBtn.addEventListener('click', () => this.closeCompletionModal());

        // Keyboard accessibility
        document.addEventListener('keydown', (e) => this.handleKeyboardNav(e));

        // Offline / Online resilience
        window.addEventListener('offline', () => {
            this.setSaveStatus('error', 'انقطع الاتصال بالإنترنت.');
        });
        window.addEventListener('online', () => {
            this.flushSave();
        });
    }

    handleKeyboardNav(e) {
        if (e.key === 'Escape') {
            if (!this.conflictModal.classList.contains('hidden')) {
                this.resolveConflict(false);
            }
            if (!this.completionModal.classList.contains('hidden')) {
                this.closeCompletionModal();
            }
        }
    }

    initData() {
        const initialScript = document.getElementById('assessment-initial-data');
        if (initialScript && initialScript.textContent.trim()) {
            try {
                const initialData = JSON.parse(initialScript.textContent);
                if (Array.isArray(initialData?.questions)) {
                    this.loadPayload(initialData);
                    return;
                }
            } catch (err) {
                console.error('Failed to parse embedded initial assessment data', err);
            }
        }

        this.fetchSessionData();
    }

    async fetchSessionData() {
        this.setSaveStatus('loading', 'جارٍ تحميل جلسة التقييم…');
        this.hideError();
        try {
            const response = await fetch(this.app.dataset.sessionUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
            });

            if (!response.ok) {
                throw new Error('Failed to load session');
            }

            const json = await response.json();
            if (!Array.isArray(json.data?.questions)) {
                throw new Error('Invalid session payload');
            }
            this.loadPayload(json.data);
            this.setSaveStatus('idle', 'جاهز');
        } catch (err) {
            this.setSaveStatus('error', 'تعذر تحميل التقييم.');
            this.showError('تعذر تحميل التقييم. تحقق من اتصالك ثم حاول مرة أخرى.');
        }
    }

    loadPayload(data) {
        if (!data || !data.questions) return;

        this.questions = data.questions.sort((a, b) => a.position - b.position);
        this.isCompleted = data.session?.status === 'completed';

        // Preload saved answers into memory
        this.answers = {};
        if (Array.isArray(data.saved_answers)) {
            data.saved_answers.forEach((ans) => {
                const ratingsMap = {};
                if (Array.isArray(ans.ratings)) {
                    ans.ratings.forEach((r) => {
                        ratingsMap[r.option_id] = r.rating;
                    });
                }

                this.answers[ans.question_id] = {
                    primary_option_id: ans.primary_option_id,
                    none_selected: Boolean(ans.none_selected),
                    unable_to_judge: Boolean(ans.unable_to_judge),
                    ratings: ratingsMap,
                };
            });
        }

        // Resume at first unanswered position
        const startingPosition = data.progress?.current_position || 1;
        const targetIndex = this.questions.findIndex((q) => q.position === startingPosition);
        this.currentIndex = targetIndex >= 0 ? targetIndex : 0;

        this.renderNavGrid();
        this.renderCurrentQuestion();
        this.updateProgress();

        if (this.isCompleted) {
            this.disableInputsForCompletedSession();
        }
    }

    getCurrentQuestion() {
        return this.questions[this.currentIndex] || null;
    }

    getCurrentAnswer() {
        const question = this.getCurrentQuestion();
        if (!question) return null;

        if (!this.answers[question.question_id]) {
            this.answers[question.question_id] = {
                primary_option_id: null,
                none_selected: false,
                unable_to_judge: false,
                ratings: {},
            };
        }

        return this.answers[question.question_id];
    }

    renderCurrentQuestion() {
        const question = this.getCurrentQuestion();
        if (!question) return;

        const focused = document.activeElement;
        const focusedOption = focused?.closest?.('.option-card')?.dataset.optionId;
        const focusedRating = focused?.dataset.rating;
        const focusedRadio = focused?.name === 'primary_option';
        const currentAnswer = this.getCurrentAnswer();
        this.hideError();

        // Update header & badges
        this.badgeNum.textContent = question.position;
        this.currentPositionNum.textContent = question.position;
        this.totalQuestionsNum.textContent = this.questions.length;
        this.scenarioText.textContent = question.scenario;

        // Reset radio states
        this.noneFitRadio.checked = currentAnswer.none_selected;
        this.cannotJudgeRadio.checked = currentAnswer.unable_to_judge;
        this.updateSpecialCardStyles();

        // Render the 4 options
        this.optionsContainer.innerHTML = '';
        const hasPrimary = currentAnswer.primary_option_id !== null;

        question.options.forEach((opt, idx) => {
            const isPrimary = currentAnswer.primary_option_id === opt.option_id;
            const currentRating = currentAnswer.ratings[opt.option_id];
            const letter = ARABIC_OPTION_LETTERS[idx] || (idx + 1);

            const card = document.createElement('div');
            card.className = `option-card group relative rounded-xl border px-3 py-2.5 sm:px-4 sm:py-3 transition-all ${
                isPrimary
                    ? 'border-brand-600 bg-brand-50/40 ring-1 ring-brand-600 shadow-xs'
                    : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60'
            }`;
            card.dataset.optionId = opt.option_id;

            // Option selection row
            const topRow = document.createElement('div');
            topRow.className = 'flex items-center gap-2.5 cursor-pointer';

            // Letter Avatar Pill
            const letterBadge = document.createElement('span');
            letterBadge.className = `flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-xs font-bold transition-colors ${
                isPrimary
                    ? 'bg-brand-600 text-white shadow-xs'
                    : 'bg-slate-100 text-slate-600 group-hover:bg-slate-200'
            }`;
            letterBadge.textContent = letter;

            // Hidden Radio for form semantics
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'primary_option';
            radio.value = opt.option_id;
            radio.id = `opt-radio-${opt.option_id}`;
            radio.checked = isPrimary;
            radio.className = 'sr-only';

            const label = document.createElement('label');
            label.htmlFor = radio.id;
            label.className = 'flex-1 text-sm font-semibold text-slate-900 cursor-pointer leading-snug sm:text-base';
            label.textContent = opt.option_text;

            topRow.appendChild(letterBadge);
            topRow.appendChild(radio);
            topRow.appendChild(label);
            card.appendChild(topRow);

            topRow.addEventListener('click', (e) => {
                e.preventDefault();
                this.selectPrimaryOption(opt.option_id);
            });
            radio.addEventListener('change', () => this.selectPrimaryOption(opt.option_id));

            // Optional 5-point rating scale drawer
            // Appears when a primary option has been selected
            if (hasPrimary) {
                const ratingDrawer = document.createElement('div');
                ratingDrawer.className = 'mt-2 border-t border-slate-100 pt-2';

                const ratingTitle = document.createElement('div');
                ratingTitle.className = 'mb-1 text-xs text-slate-600';
                ratingTitle.innerHTML = `
                    <span class="font-medium">إلى أي مدى يشبهك؟ (اختياري)</span>
                `;
                ratingDrawer.appendChild(ratingTitle);

                const scaleGrid = document.createElement('div');
                scaleGrid.className = 'grid grid-cols-2 gap-1.5 min-[390px]:grid-cols-3 md:grid-cols-5';

                RATING_LEVELS.forEach((level) => {
                    const isSelectedRating = currentRating === level.value;
                    const ratingBtn = document.createElement('button');
                    ratingBtn.type = 'button';
                    ratingBtn.className = `rating-btn flex min-h-11 min-w-0 items-center justify-center gap-1 rounded-lg border px-1.5 py-1 text-xs font-bold leading-tight transition-colors ${
                        isSelectedRating ? level.activeClass : level.idleClass
                    }`;
                    ratingBtn.dataset.rating = String(level.value);
                    ratingBtn.setAttribute('aria-label', `تقييم ${letter}: ${level.label}`);
                    ratingBtn.setAttribute('aria-pressed', String(isSelectedRating));
                    const icon = document.createElement('img');
                    icon.src = `/assets/assessment/emoji/${level.icon}`;
                    icon.alt = '';
                    icon.width = 20;
                    icon.height = 20;
                    icon.className = 'h-5 w-5 shrink-0';
                    const text = document.createElement('span');
                    text.className = 'text-center';
                    text.textContent = level.label;
                    ratingBtn.append(icon, text);

                    ratingBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        this.handleRatingClick(opt.option_id, level.value, isSelectedRating);
                    });

                    scaleGrid.appendChild(ratingBtn);
                });
                ratingDrawer.appendChild(scaleGrid);
                card.appendChild(ratingDrawer);
            }

            this.optionsContainer.appendChild(card);
        });

        if (focusedOption) {
            const card = [...this.optionsContainer.children].find((element) => element.dataset.optionId === focusedOption);
            const target = focusedRadio
                ? card?.querySelector('input[name="primary_option"]')
                : [...(card?.querySelectorAll('.rating-btn') || [])].find((button) => button.dataset.rating === focusedRating);
            target?.focus({ preventScroll: true });
        }

        this.updateNavButtons();
        this.renderNavGrid();
    }

    updateSpecialCardStyles() {
        const currentAnswer = this.getCurrentAnswer();

        this.noneFitCard.className = `relative flex cursor-pointer items-start gap-2 rounded-xl border p-3 transition-colors focus-within:ring-2 focus-within:ring-brand-600 ${
            currentAnswer.none_selected
                ? 'border-brand-600 bg-brand-50/50 ring-1 ring-brand-600'
                : 'border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300'
        }`;

        this.cannotJudgeCard.className = `relative flex cursor-pointer items-start gap-2 rounded-xl border p-3 transition-colors focus-within:ring-2 focus-within:ring-brand-600 ${
            currentAnswer.unable_to_judge
                ? 'border-brand-600 bg-brand-50/50 ring-1 ring-brand-600'
                : 'border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300'
        }`;
    }

    selectPrimaryOption(optionId) {
        const answer = this.getCurrentAnswer();
        answer.primary_option_id = optionId;
        answer.none_selected = false;
        answer.unable_to_judge = false;

        this.noneFitRadio.checked = false;
        this.cannotJudgeRadio.checked = false;

        this.renderCurrentQuestion();
        this.scheduleSave();
    }

    handleSpecialChoice(choiceType) {
        const answer = this.getCurrentAnswer();
        answer.primary_option_id = null;
        answer.ratings = {}; // Clear ratings for special choices

        if (choiceType === 'none_selected') {
            answer.none_selected = true;
            answer.unable_to_judge = false;
            this.cannotJudgeRadio.checked = false;
        } else {
            answer.unable_to_judge = true;
            answer.none_selected = false;
            this.noneFitRadio.checked = false;
        }

        this.renderCurrentQuestion();
        this.scheduleSave();
    }

    handleRatingClick(optionId, ratingValue, isAlreadySelected) {
        const answer = this.getCurrentAnswer();

        // Clicking the active rating again unrates (clears rating)
        if (isAlreadySelected) {
            delete answer.ratings[optionId];
            this.renderCurrentQuestion();
            this.scheduleSave();
            return;
        }

        // Conflict check: if student chose this option as Primary AND gives strong negative rating (-1 or -2)
        if (answer.primary_option_id === optionId && (ratingValue === -1 || ratingValue === -2)) {
            this.pendingConflict = { optionId, ratingValue };
            this.openConflictModal();
            return;
        }

        answer.ratings[optionId] = ratingValue;
        this.renderCurrentQuestion();
        this.scheduleSave();
    }

    openConflictModal() {
        this.conflictModal.classList.remove('hidden');
    }

    closeConflictModal() {
        this.conflictModal.classList.add('hidden');
        this.pendingConflict = null;
    }

    resolveConflict(keepRating) {
        if (keepRating && this.pendingConflict) {
            const answer = this.getCurrentAnswer();
            answer.ratings[this.pendingConflict.optionId] = this.pendingConflict.ratingValue;
            this.renderCurrentQuestion();
            this.scheduleSave();
        }
        this.closeConflictModal();
    }

    scheduleSave() {
        clearTimeout(this.debounceSaveTimer);
        this.setSaveStatus('saving', 'جارٍ الحفظ…');
        this.debounceSaveTimer = setTimeout(() => {
            this.flushSave();
        }, 350);
    }

    async flushSave() {
        clearTimeout(this.debounceSaveTimer);

        const question = this.getCurrentQuestion();
        const answer = this.getCurrentAnswer();
        if (!question || !answer) return;

        const hasPrimary = answer.primary_option_id !== null;
        if (!hasPrimary && !answer.none_selected && !answer.unable_to_judge) {
            return;
        }

        const ratingsArray = [];
        if (!answer.unable_to_judge && answer.ratings) {
            for (const [optId, ratingVal] of Object.entries(answer.ratings)) {
                ratingsArray.push({
                    option_id: Number(optId),
                    rating: Number(ratingVal),
                });
            }
        }

        const payload = {
            primary_option_id: answer.primary_option_id,
            none_selected: answer.none_selected,
            unable_to_judge: answer.unable_to_judge,
            ratings: ratingsArray,
        };

        this.setSaveStatus('saving', 'جارٍ الحفظ…');
        this.hideError();

        try {
            if (this.abortController) {
                this.abortController.abort();
            }
            this.abortController = new AbortController();

            const url = `${this.saveBaseUrl}/${question.question_id}`;
            const response = await fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify(payload),
                signal: this.abortController.signal,
            });

            if (!response.ok) {
                const errData = await response.json().catch(() => ({}));
                const errMsg = errData.message || 'تعذر حفظ الإجابة. حاول مرة أخرى.';
                throw new Error(errMsg);
            }

            const result = await response.json();
            this.setSaveStatus('saved', 'حُفظت الإجابة.');
            this.updateProgress();
            this.renderNavGrid();
            this.updateNavButtons();
        } catch (err) {
            if (err.name === 'AbortError') return;
            this.setSaveStatus('error', 'تعذر حفظ الإجابة.');
            this.showError(err.message || 'تعذر حفظ الإجابة. حاول مرة أخرى.');
        }
    }

    setSaveStatus(status, message) {
        this.saveStatus = status;
        this.saveStatusText.textContent = message;

        if (status === 'saving') {
            this.saveStatusText.className = 'text-brand-600 font-semibold';
            this.saveStatusIcon.innerHTML = `
                <svg class="h-3.5 w-3.5 animate-spin text-brand-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            `;
        } else if (status === 'saved') {
            this.saveStatusText.className = 'text-emerald-600 font-semibold';
            this.saveStatusIcon.innerHTML = `
                <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            `;
        } else if (status === 'error') {
            this.saveStatusText.className = 'text-red-600 font-semibold';
            this.saveStatusIcon.innerHTML = `
                <svg class="h-3.5 w-3.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
            `;
        } else {
            this.saveStatusText.className = 'text-slate-400 font-medium';
            this.saveStatusIcon.innerHTML = '';
        }
    }

    showError(message) {
        this.errorAlertMessage.textContent = message;
        this.errorAlert.classList.remove('hidden');
    }

    hideError() {
        this.errorAlert.classList.add('hidden');
    }

    updateProgress() {
        const total = this.questions.length || 18;
        let answeredCount = 0;

        this.questions.forEach((q) => {
            const ans = this.answers[q.question_id];
            if (ans && (ans.primary_option_id !== null || ans.none_selected || ans.unable_to_judge)) {
                answeredCount++;
            }
        });

        this.processedCountNum.textContent = answeredCount;
        const percentage = Math.round((answeredCount / total) * 100);
        this.progressBarFill.style.width = `${percentage}%`;
        this.progressBarContainer.setAttribute('aria-valuenow', percentage);
    }

    renderNavGrid() {
        this.questionsNavGrid.innerHTML = '';

        this.questions.forEach((q, idx) => {
            const ans = this.answers[q.question_id];
            const isAnswered = ans && (ans.primary_option_id !== null || ans.none_selected || ans.unable_to_judge);
            const isCurrent = idx === this.currentIndex;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.setAttribute('aria-label', `الموقف رقم ${q.position}`);
            btn.className = `flex h-9 w-full items-center justify-center rounded-xl text-xs font-bold transition-all ${
                isCurrent
                    ? 'bg-brand-600 text-white ring-2 ring-brand-600 ring-offset-2 shadow-xs'
                    : isAnswered
                    ? 'bg-emerald-50 text-emerald-800 border border-emerald-300 hover:bg-emerald-100'
                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
            }`;

            btn.textContent = q.position;
            btn.addEventListener('click', () => {
                this.flushSave();
                this.goToIndex(idx);
            });

            this.questionsNavGrid.appendChild(btn);
        });
    }

    updateNavButtons() {
        this.prevBtn.disabled = this.currentIndex <= 0;

        const isLastQuestion = this.currentIndex >= this.questions.length - 1;
        const allProcessed = this.checkAllProcessed();

        if (allProcessed) {
            this.completeBtn.classList.remove('hidden');
        } else {
            this.completeBtn.classList.add('hidden');
        }

        if (isLastQuestion) {
            this.nextBtn.classList.add('hidden');
        } else {
            this.nextBtn.classList.remove('hidden');
        }
    }

    checkAllProcessed() {
        if (this.questions.length === 0) return false;
        return this.questions.every((q) => {
            const ans = this.answers[q.question_id];
            return ans && (ans.primary_option_id !== null || ans.none_selected || ans.unable_to_judge);
        });
    }

    goToIndex(newIndex) {
        if (newIndex < 0 || newIndex >= this.questions.length) return;
        this.currentIndex = newIndex;
        this.renderCurrentQuestion();
    }

    goToNext() {
        this.flushSave();
        if (this.currentIndex < this.questions.length - 1) {
            this.goToIndex(this.currentIndex + 1);
        }
    }

    goToPrevious() {
        this.flushSave();
        if (this.currentIndex > 0) {
            this.goToIndex(this.currentIndex - 1);
        }
    }

    openCompletionModal() {
        this.completionModalError.classList.add('hidden');
        this.completionModal.classList.remove('hidden');
    }

    closeCompletionModal() {
        this.completionModal.classList.add('hidden');
    }

    async submitCompletion() {
        this.completionConfirmBtn.disabled = true;
        this.completionConfirmBtn.textContent = 'جارٍ الحفظ…';
        this.completionModalError.classList.add('hidden');

        try {
            const response = await fetch(this.completeUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
            });

            if (!response.ok) {
                const errData = await response.json().catch(() => ({}));
                const errMsg = errData.message || 'لم تكتمل المواقف المطلوبة بعد. راجع المواقف التي تحتاج معالجة، ثم حاول إكمال التقييم مرة أخرى.';
                this.completionModalError.textContent = errMsg;
                this.completionModalError.classList.remove('hidden');
                this.completionConfirmBtn.disabled = false;
                this.completionConfirmBtn.textContent = 'إكمال التقييم';
                return;
            }

            const data = await response.json();
            const resultUrl = data.data?.result_url || '/results';
            window.location.href = resultUrl;
        } catch (err) {
            this.completionModalError.textContent = 'تعذر الاتصال. تأكد من اتصالك بالإنترنت ثم حاول مرة أخرى.';
            this.completionModalError.classList.remove('hidden');
            this.completionConfirmBtn.disabled = false;
            this.completionConfirmBtn.textContent = 'إكمال التقييم';
        }
    }

    disableInputsForCompletedSession() {
        const inputs = this.app.querySelectorAll('input, button');
        inputs.forEach((el) => {
            if (el.id !== 'prev-btn' && el.id !== 'next-btn') {
                el.disabled = true;
            }
        });
        this.setSaveStatus('saved', 'اكتمل هذا التقييم، ولا يمكن تعديل إجاباته.');
    }
}

// Auto-initialize when #assessment-app is mounted
document.addEventListener('DOMContentLoaded', () => {
    const appEl = document.getElementById('assessment-app');
    if (appEl) {
        new AssessmentJourney(appEl);
    }
});

export default AssessmentJourney;
