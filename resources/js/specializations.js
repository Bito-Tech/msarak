const STORAGE_KEY = 'specializations-compare';

function getSelection() {
    try {
        return JSON.parse(sessionStorage.getItem(STORAGE_KEY)) ?? [];
    } catch {
        return [];
    }
}

function setSelection(selection) {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(selection));
    renderBar();
}

function renderBar() {
    const selection = getSelection();
    const bar = document.getElementById('compare-bar');

    document.querySelectorAll('.compare-toggle').forEach((button) => {
        const selected = selection.some((item) => item.id === button.dataset.id);
        button.setAttribute('aria-pressed', String(selected));
        button.classList.toggle('border-brand-600', selected);
        button.classList.toggle('bg-brand-50', selected);
        button.setAttribute('aria-label', `${selected ? 'إزالة' : 'إضافة'} ${button.dataset.name} ${selected ? 'من' : 'إلى'} المقارنة`);
    });

    if (!bar) return;

    if (selection.length === 0) {
        bar.classList.add('hidden');
        return;
    }

    bar.classList.remove('hidden');
    document.getElementById('compare-bar-names').textContent =
        selection.map((item) => item.name).join(' مقابل ');

    const link = document.getElementById('compare-bar-link');
    if (selection.length === 2) {
        link.href = `/specializations/compare?first=${selection[0].id}&second=${selection[1].id}`;
        link.removeAttribute('aria-disabled');
        link.removeAttribute('tabindex');
        link.classList.remove('opacity-50');
    } else {
        link.removeAttribute('href');
        link.setAttribute('aria-disabled', 'true');
        link.setAttribute('tabindex', '-1');
        link.classList.add('opacity-50');
    }
}

document.addEventListener('click', (event) => {
    const button = event.target.closest('.compare-toggle');
    if (button) {
        let selection = getSelection();
        const { id, name, redirect } = button.dataset;
        const exists = selection.some((item) => item.id === id);

        if (exists) {
            selection = selection.filter((item) => item.id !== id);
        } else if (selection.length < 2) {
            selection.push({ id, name });
        } else {
            const replace = window.confirm(
                'لديك بالفعل تخصصان مختاران. هل تريد استبدال أولهما بهذا التخصص؟'
            );
            if (!replace) return;
            selection = [selection[1], { id, name }];
        }

        setSelection(selection);

        if (redirect && !exists) {
            window.location.href = redirect;
        }
        return;
    }

    if (event.target.id === 'compare-bar-clear') {
        setSelection([]);
    }
});

renderBar();
