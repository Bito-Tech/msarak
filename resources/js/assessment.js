/**
 * Assessment Journey Engine (Issue F-03)
 * Professional Software Engineering Implementation.
 * Adheres strictly to contracts H-03 and C-03.
 * Zero RIASEC leakage, defensive state management, debounced autosave, and accessible UI.
 */

const RATING_LEVELS = [
    { value: -2, label: 'لا يشبهني إطلاقًا', displayLabel: 'لا يشبهني إطلاقًا', icon: 'emoji-strongly-dislike.svg' },
    { value: -1, label: 'لا يشبهني', displayLabel: 'لا يشبهني', icon: 'emoji-dislike.svg' },
    { value: 0, label: 'محايد / غير متأكد', displayLabel: 'محايد', icon: 'emoji-neutral.svg' },
    { value: 1, label: 'يشبهني', displayLabel: 'يشبهني', icon: 'emoji-like.svg' },
    { value: 2, label: 'يشبهني جدًا', displayLabel: 'يشبهني جدًا', icon: 'emoji-strongly-like.svg' },
];

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
        this.lastRatingInteraction = null;
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
        this.topPrevBtn = document.getElementById('top-prev-btn');
        this.topNextBtn = document.getElementById('top-next-btn');
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
        this.topPrevBtn.addEventListener('click', () => this.goToPrevious());
        this.topNextBtn.addEventListener('click', () => this.goToNext());
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

        this.badgeNum.textContent = question.position;
        this.currentPositionNum.textContent = question.position;
        this.totalQuestionsNum.textContent = this.questions.length;
        this.scenarioText.textContent = question.scenario;

        this.noneFitRadio.checked = currentAnswer.none_selected;
        this.cannotJudgeRadio.checked = currentAnswer.unable_to_judge;
        this.updateSpecialCardStyles();

        this.optionsContainer.innerHTML = '';

        question.options.forEach((opt, idx) => {
            const isPrimary = currentAnswer.primary_option_id === opt.option_id;
            const currentRating = currentAnswer.ratings[opt.option_id];

            const card = document.createElement('div');
            card.dir = 'rtl';
            card.className = `option-card assessment-option-card flex min-w-0 flex-col gap-1.5 rounded-lg border px-2 py-2 transition sm:gap-2 sm:rounded-xl sm:px-4 sm:py-2 lg:flex-row lg:items-center lg:justify-center lg:gap-2.5 lg:px-5 lg:py-2 ${isPrimary ? 'is-primary shadow-sm' : ''}`;
            card.dataset.optionId = String(opt.option_id);

            const choiceWrap = document.createElement('div');
            choiceWrap.className = 'flex min-w-0 flex-1 items-start gap-2 sm:gap-2.5 lg:flex-none lg:w-[44%] xl:w-[46%]';

            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'primary_option';
            radio.value = opt.option_id;
            radio.id = `opt-radio-${opt.option_id}`;
            radio.checked = isPrimary;
            radio.className = 'mt-0.5 h-4 w-4 shrink-0 cursor-pointer border-slate-300 text-brand-600 focus:ring-brand-600 sm:mt-1 sm:h-5 sm:w-5';
            radio.setAttribute('aria-label', `اختيار التصرف ${idx + 1} بوصفه الأقرب لك`);

            const label = document.createElement('label');
            label.htmlFor = radio.id;
            label.className = 'min-w-0 flex-1 cursor-pointer text-right text-[10px] font-semibold leading-[1.15rem] text-slate-800 min-[380px]:text-[10.5px] sm:text-[0.9rem] sm:leading-6';
            label.textContent = opt.option_text;

            choiceWrap.append(radio, label);
            card.appendChild(choiceWrap);

            radio.addEventListener('change', () => this.selectPrimaryOption(opt.option_id));
            label.addEventListener('click', (event) => {
                event.preventDefault();
                this.selectPrimaryOption(opt.option_id);
            });

            const ratingArea = document.createElement('div');
            ratingArea.className = 'min-w-0 border-t border-slate-200/70 pt-1 sm:pt-1.5 lg:w-[315px] lg:shrink-0 lg:border-0 lg:pt-0 xl:w-[335px]';

            const scale = document.createElement('div');
            scale.dir = 'ltr';
            scale.className = 'grid w-full min-w-0 grid-cols-5 items-center gap-0 sm:gap-1.5';

            RATING_LEVELS.forEach((level) => {
                const isSelectedRating = currentRating === level.value;
                const wasJustPressed = this.lastRatingInteraction
                    && this.lastRatingInteraction.optionId === opt.option_id
                    && this.lastRatingInteraction.ratingValue === level.value;

                const ratingItem = document.createElement('div');
                ratingItem.className = 'assessment-rating-item flex min-w-0 flex-col items-center gap-0.5';

                const ratingBtn = document.createElement('button');
                ratingBtn.type = 'button';
                ratingBtn.className = `rating-btn assessment-rating-btn mx-auto inline-flex h-7 w-7 items-center justify-center rounded-lg bg-transparent min-[380px]:h-7.5 min-[380px]:w-7.5 sm:h-9 sm:w-9 ${isSelectedRating ? 'is-selected' : ''} ${wasJustPressed ? 'rating-pop' : ''}`;
                ratingBtn.dataset.rating = String(level.value);
                ratingBtn.setAttribute('aria-label', `تقييم التصرف ${idx + 1}: ${level.label}`);
                ratingBtn.setAttribute('aria-pressed', String(isSelectedRating));
                ratingBtn.title = level.label;

                const icon = document.createElement('img');
                icon.src = `/assets/assessment/emoji/${level.icon}`;
                icon.alt = '';
                icon.width = 34;
                icon.height = 34;
                icon.className = 'assessment-emoji-image h-6 w-6 select-none object-contain min-[380px]:h-6.5 min-[380px]:w-6.5 sm:h-8 sm:w-8';
                icon.draggable = false;
                ratingBtn.appendChild(icon);

                const ratingLabel = document.createElement('span');
                ratingLabel.className = 'assessment-rating-label w-full whitespace-nowrap text-center text-[6.5px] font-medium leading-[0.62rem] text-slate-500 sm:text-[8px] sm:leading-[0.7rem]';
                ratingLabel.textContent = level.displayLabel;

                ratingBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.handleRatingClick(opt.option_id, level.value, isSelectedRating);
                });

                ratingItem.append(ratingBtn, ratingLabel);
                scale.appendChild(ratingItem);
            });

            ratingArea.append(scale);
            card.appendChild(ratingArea);
            this.optionsContainer.appendChild(card);
        });

        if (focusedOption) {
            const card = [...this.optionsContainer.children].find((element) => element.dataset.optionId === focusedOption);
            const target = focusedRadio
                ? card?.querySelector('input[name="primary_option"]')
                : [...(card?.querySelectorAll('.rating-btn') || [])].find((button) => button.dataset.rating === focusedRating);
            target?.focus({ preventScroll: true });
        }

        this.lastRatingInteraction = null;
        this.updateNavButtons();
        this.renderNavGrid();
    }

    updateSpecialCardStyles() {
        const currentAnswer = this.getCurrentAnswer();
        const base = 'assessment-special-card relative flex min-h-9 cursor-pointer items-center gap-1.5 rounded-lg border px-2 py-1.5 transition sm:min-h-10 sm:gap-2 sm:rounded-xl sm:px-3 sm:py-2';
        this.noneFitCard.className = `${base} ${currentAnswer.none_selected ? 'is-selected' : ''}`;
        this.cannotJudgeCard.className = `${base} ${currentAnswer.unable_to_judge ? 'is-selected' : ''}`;
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

        // A rating belongs to the normal option-answer mode. If a special response
        // was active, return to the normal mode without inventing a primary choice.
        if (answer.none_selected || answer.unable_to_judge) {
            answer.none_selected = false;
            answer.unable_to_judge = false;
            this.noneFitRadio.checked = false;
            this.cannotJudgeRadio.checked = false;
        }

        // Clicking the active rating again unrates (clears rating).
        if (isAlreadySelected) {
            this.lastRatingInteraction = { optionId, ratingValue };
            delete answer.ratings[optionId];
            this.renderCurrentQuestion();

            if (answer.primary_option_id !== null) {
                this.scheduleSave();
            } else {
                this.setSaveStatus('idle', 'اختر التصرف الأساسي للحفظ.');
            }
            return;
        }

        // Conflict check: if student chose this option as Primary AND gives strong negative rating (-1 or -2).
        if (answer.primary_option_id === optionId && (ratingValue === -1 || ratingValue === -2)) {
            this.pendingConflict = { optionId, ratingValue };
            this.openConflictModal();
            return;
        }

        this.lastRatingInteraction = { optionId, ratingValue };
        answer.ratings[optionId] = ratingValue;
        this.renderCurrentQuestion();

        if (answer.primary_option_id !== null) {
            this.scheduleSave();
        } else {
            this.setSaveStatus('idle', 'اختر التصرف الأساسي للحفظ.');
        }
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
            this.lastRatingInteraction = {
                optionId: this.pendingConflict.optionId,
                ratingValue: this.pendingConflict.ratingValue,
            };
            answer.ratings[this.pendingConflict.optionId] = this.pendingConflict.ratingValue;
            this.renderCurrentQuestion();
            this.scheduleSave();
        } else {
            this.lastRatingInteraction = null;
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
            this.saveStatusText.className = 'assessment-status-saving font-semibold';
            this.saveStatusIcon.innerHTML = `
                <svg class="assessment-status-saving h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
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
            btn.className = `assessment-question-dot flex h-9 w-full items-center justify-center rounded-xl text-xs font-bold transition-all ${
                isCurrent ? 'is-current' : isAnswered ? 'is-answered' : 'is-idle'
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
        const isFirst = this.currentIndex <= 0;
        const isLast = this.currentIndex >= this.questions.length - 1;
        const allProcessed = this.checkAllProcessed();

        this.prevBtn.disabled = isFirst;
        this.topPrevBtn.disabled = isFirst;
        this.topNextBtn.disabled = isLast;

        if (allProcessed) {
            this.completeBtn.classList.remove('hidden');
        } else {
            this.completeBtn.classList.add('hidden');
        }

        if (isLast) {
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
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.app.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'start' });
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
            if (!['prev-btn', 'next-btn', 'top-prev-btn', 'top-next-btn'].includes(el.id)) {
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
