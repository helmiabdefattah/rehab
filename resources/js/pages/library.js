import { $, $$ } from '../lib/dom.js';

// Instant client-side filtering; mirrors ExerciseController::matches() for no-JS use.
export function initLibrary() {
    const form = $('[data-library-filters]');
    if (!form) return;

    const cards = $$('[data-library-grid] .ex-card');
    const count = $('[data-library-count]');
    const empty = $('[data-library-empty]');

    const apply = () => {
        const q = form.q.value.trim().toLowerCase();
        const tag = form.tag.value;
        const section = form.section.value;
        const activity = form.activity.value;
        let shown = 0;

        cards.forEach((card) => {
            const ok =
                (!q || card.dataset.search.includes(q)) &&
                (!tag || card.dataset.tags.split(' ').includes(tag)) &&
                (!section || card.dataset.section === section) &&
                (!activity || card.dataset.activities.split(' ').includes(activity));
            card.hidden = !ok;
            if (ok) shown++;
        });

        count.textContent = `${shown} of ${cards.length} shown`;
        empty.hidden = shown > 0;

        const params = new URLSearchParams();
        [['q', q], ['tag', tag], ['section', section], ['activity', activity]].forEach(([k, v]) => v && params.set(k, v));
        history.replaceState(null, '', `${location.pathname}${params.toString() ? `?${params}` : ''}`);
    };

    form.addEventListener('input', apply);
    form.addEventListener('change', apply);
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        apply();
    });
    $('[data-library-reset]').addEventListener('click', (e) => {
        e.preventDefault();
        form.reset();
        form.q.value = '';
        ['tag', 'section', 'activity'].forEach((k) => (form[k].value = ''));
        apply();
    });
}
