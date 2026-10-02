import { $, $$, cloneTemplate, pressOne, uid } from '../lib/dom.js';
import { addSession, ACTIVITIES } from '../lib/sessions.js';
import { duration } from '../lib/format.js';

/** Completion screen with the post-session feedback form. */
export function showFinish({ routine, early, completed, skipped, elapsedSec }, { onClose } = {}) {
    const el = cloneTemplate('finish-template');
    document.body.appendChild(el);
    document.body.style.overflow = 'hidden';

    const total = routine.items.length;
    const doneCount = completed.length;
    $('[data-slot="emoji"]', el).textContent = early ? '👏' : '🎉';
    $('[data-slot="heading"]', el).textContent = early ? 'Warm-up ended' : 'Warm-up complete';
    $('[data-slot="summary"]', el).textContent =
        `${doneCount} of ${total} exercises · ${duration(elapsedSec)}${skipped.length ? ` · ${skipped.length} skipped` : ''}`;

    const state = { feel: null, pain: null, areas: new Set() };
    const form = $('[data-feedback]', el);
    const save = $('[data-save]', el);
    const areasBox = $('[data-pain-areas]', el);
    const advice = $('[data-feedback-advice]', el);

    const update = () => {
        const needsArea = state.pain === true && state.areas.size === 0;
        save.disabled = !state.feel || state.pain === null || needsArea;
        areasBox.hidden = state.pain !== true;
        advice.hidden = !(state.pain === true || state.feel === 'uncomfortable');
    };

    $('[data-feel]', el).addEventListener('click', (e) => {
        const b = e.target.closest('button[data-value]');
        if (!b) return;
        state.feel = b.dataset.value;
        pressOne(b.parentElement, state.feel);
        update();
    });

    $('[data-pain]', el).addEventListener('click', (e) => {
        const b = e.target.closest('button[data-value]');
        if (!b) return;
        state.pain = b.dataset.value === '1';
        pressOne(b.parentElement, b.dataset.value);
        if (!state.pain) {
            state.areas.clear();
            $$('.chip', areasBox).forEach((c) => c.setAttribute('aria-pressed', 'false'));
        }
        update();
    });

    areasBox.addEventListener('click', (e) => {
        const b = e.target.closest('button[data-value]');
        if (!b) return;
        const on = b.getAttribute('aria-pressed') !== 'true';
        b.setAttribute('aria-pressed', String(on));
        on ? state.areas.add(b.dataset.value) : state.areas.delete(b.dataset.value);
        update();
    });

    const close = () => {
        el.remove();
        document.body.style.overflow = '';
        onClose?.();
    };

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        if (save.disabled) return;

        const r = routine.readiness || {};
        addSession({
            id: uid(),
            date: new Date().toISOString(),
            title: routine.title,
            name: routine.name,
            program: routine.program,
            activity: routine.activity,
            source: routine.source,
            minutes: routine.minutes,
            intensity: routine.intensity,
            level: routine.level,
            readiness: { status: r.status, score: r.answers?.checked ? r.score : null, checked: !!r.answers?.checked, reported: r.reported || [] },
            exercises_total: total,
            exercises_completed: doneCount,
            exercises: completed.map((i) => routine.items[i]?.slug).filter(Boolean),
            completed: !early,
            duration_sec: elapsedSec,
            feel: state.feel,
            pain: state.pain,
            pain_areas: [...state.areas],
            notes: $('[data-notes]', el).value.trim().slice(0, 500),
        });

        // Offer to move straight into the matching split's workout.
        const split = routine.activity;
        const link = $('[data-workout-link]', el);
        if (link && ACTIVITIES[split]) {
            link.href = `/train/${split}`;
            $('[data-workout-label]', el).textContent = `Start ${ACTIVITIES[split].label} workout`;
            link.hidden = false;
        }

        form.hidden = true;
        $('[data-saved]', el).hidden = false;
        $('[data-saved] a:not([hidden])', el)?.focus();
    });

    $('[data-finish-close]', el).addEventListener('click', close);

    $('[data-discard]', el).addEventListener('click', () => {
        if (window.confirm('Close without saving this session?')) close();
    });

    update();
    return { close };
}
