import { $, $$, esc, icon, pressOne } from '../lib/dom.js';
import { getSettings, LEVELS } from '../lib/settings.js';
import { read, write, remove } from '../lib/store.js';
import { clock } from '../lib/format.js';
import { animationSvg } from '../components/exercise-animation.js';
import { buildRoutine, quickRoutine } from '../warmup/engine.js';
import { WorkoutPlayer } from '../workout/player.js';
import { showFinish } from '../workout/finish.js';
import { toast } from '../components/toast.js';

const STEP_COPY = {
    1: ['What are you training today?', 'Choose your split. The warm-up primes exactly those muscles — heat → mobility → activation → movement rehearsal.'],
    2: ['Set up your session', 'Pick intensity, available time and your level. The routine adjusts automatically.'],
    3: ['Your warm-up', 'Review the routine, then start Workout Mode.'],
};

export function initBuilder() {
    const root = $('[data-builder]');
    if (!root) return;

    const settings = getSettings();
    const remembered = read('builder', {}) || {};

    const state = {
        step: 1,
        activity: null,
        intensity: remembered.intensity || 'moderate',
        minutes: remembered.minutes || 10,
        level: settings.level,
        equipment: new Set(settings.equipment),
        routine: null,
    };

    const nextBtn = $('[data-next]', root);
    const backBtn = $('[data-back]', root);
    const preview = $('[data-routine-preview]', root);

    /* ------------------------------------------------ controls → state */

    $$('[data-activity]', root).forEach((btn) =>
        btn.addEventListener('click', () => {
            selectActivity(btn.dataset.activity);
            goTo(2);
        }),
    );

    $$('[data-choice]', root).forEach((group) => {
        const key = group.dataset.choice;
        group.addEventListener('click', (e) => {
            const b = e.target.closest('button[data-value]');
            if (!b) return;
            state[key] = key === 'intensity' ? b.dataset.value : Number(b.dataset.value);
            pressOne(group, b.dataset.value);
            if (key === 'level') $('[data-level-hint]', root).textContent = LEVELS[state.level].description;
        });
    });

    $('[data-equipment]', root).addEventListener('click', (e) => {
        const b = e.target.closest('button[data-value]');
        if (!b) return;
        state.equipment.has(b.dataset.value) ? state.equipment.delete(b.dataset.value) : state.equipment.add(b.dataset.value);
        renderEquipment();
    });

    nextBtn.addEventListener('click', () => {
        if (state.step === 3) return startWorkout();
        goTo(state.step + 1);
    });

    backBtn.addEventListener('click', () => goTo(Math.max(1, state.step - 1)));
    $('[data-retry]', root).addEventListener('click', () => generate());

    /* --------------------------------------------------------- helpers */

    function selectActivity(activity) {
        state.activity = activity;
        $$('[data-activity]', root).forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.activity === activity)));
        updateNav();
    }

    function renderEquipment() {
        $$('[data-equipment] button', root).forEach((b) => b.setAttribute('aria-pressed', String(state.equipment.has(b.dataset.value))));
    }

    function stepValid(step) {
        if (step === 1) return !!state.activity;
        if (step === 3) return !!state.routine;
        return true;
    }

    function updateNav() {
        backBtn.hidden = state.step === 1;
        nextBtn.disabled = !stepValid(state.step);
        const labels = { 1: 'Next', 2: 'Build my warm-up', 3: 'Start Workout Mode' };
        nextBtn.innerHTML = state.step === 3 ? `${icon('play', true)} ${labels[3]}` : `${labels[state.step]} ${icon('chevron-right')}`;
    }

    function goTo(step) {
        state.step = step;
        $$('[data-step]', root).forEach((s) => (s.hidden = Number(s.dataset.step) !== step));
        $$('[data-step-dot]', root).forEach((d) => {
            const n = Number(d.dataset.stepDot);
            d.classList.toggle('is-current', n === step);
            d.classList.toggle('is-done', n < step);
        });
        $('[data-step-title]', root).textContent = STEP_COPY[step][0];
        $('[data-step-sub]', root).textContent = STEP_COPY[step][1];
        if (step === 3) generate();
        updateNav();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function generate() {
        state.routine = null;
        $('[data-routine-loading]', root).hidden = false;
        $('[data-routine-error]', root).hidden = true;
        preview.hidden = true;
        updateNav();

        write('builder', { intensity: state.intensity, minutes: state.minutes });

        // Built entirely in the browser — works offline, no server round-trip.
        try {
            const data = buildRoutine({
                program: state.activity,
                activity: state.activity,
                minutes: state.minutes,
                intensity: state.intensity,
                level: state.level,
                equipment: [...state.equipment],
                transition: getSettings().getReady,
                source: 'builder',
            });
            showRoutine(data);
        } catch (err) {
            $('[data-routine-loading]', root).hidden = true;
            $('[data-routine-error]', root).hidden = false;
            $('[data-routine-error-text]', root).textContent = `Couldn’t build the routine. (${err.message})`;
        }
    }

    function showRoutine(routine) {
        state.routine = routine;
        $('[data-routine-loading]', root).hidden = true;
        preview.innerHTML = renderRoutine(routine);
        preview.hidden = false;
        updateNav();
    }

    function startWorkout(resume = null) {
        const routine = resume?.routine ?? state.routine;
        if (!routine?.items?.length) return;
        new WorkoutPlayer(routine, {
            resume,
            onFinish: (result) => showFinish(result),
        }).open();
    }

    /* ------------------------------------------------------- bootstrap */

    renderEquipment();
    pressOne($('[data-choice="intensity"]', root), state.intensity);
    pressOne($('[data-choice="minutes"]', root), state.minutes);
    pressOne($('[data-choice="level"]', root), state.level);
    $('[data-level-hint]', root).textContent = LEVELS[state.level].description;

    const quickMins = Number(root.dataset.quick || 0);
    if (quickMins) {
        // ⚡ Quick warm-up: built in the browser from your saved prefs, then launched.
        const s = getSettings();
        const routine = quickRoutine(quickMins, { level: s.level, equipment: s.equipment, transition: s.getReady });
        state.step = 3;
        $$('[data-step]', root).forEach((st) => (st.hidden = st.dataset.step !== '3'));
        $('.wizard-steps', root).hidden = true;
        $('[data-step-title]', root).textContent = `${routine.emoji} ${routine.name}`;
        $('[data-step-sub]', root).textContent = 'A balanced routine built from your saved level and equipment. Stop if anything feels sharp or unstable.';
        showRoutine(routine);
        backBtn.remove();
        if (root.dataset.autostart !== '0') startWorkout();
    } else if (root.dataset.preselected) {
        selectActivity(root.dataset.preselected);
        goTo(2);
    } else {
        goTo(1);
    }

    // Offer to resume an interrupted workout (e.g. the page was reloaded).
    const active = read('activeWorkout');
    if (active?.routine && Date.now() - active.savedAt < 2 * 60 * 60 * 1000 && !quickMins) {
        const banner = $('[data-resume]', root);
        $('[data-resume-text]', root).textContent =
            `${active.routine.title} — stopped at exercise ${active.index + 1} of ${active.routine.items.length}.`;
        banner.hidden = false;
        $('[data-resume-yes]', root).addEventListener('click', () => {
            banner.hidden = true;
            state.routine = active.routine;
            startWorkout(active);
        });
        $('[data-resume-no]', root).addEventListener('click', () => {
            remove('activeWorkout');
            banner.hidden = true;
            toast('Discarded');
        });
    }
}

/* ------------------------------------------------------------ templates */

function renderRoutine(r) {
    let n = 0;
    const stages = r.stages
        .map(
            (stage) => `
        <section class="routine-stage" data-stage="${esc(stage.key)}">
            <div class="routine-stage-head">
                <span class="stage-dot"></span>
                <h3>Stage ${stage.number} · ${esc(stage.label)}</h3>
                <span class="muted">${clock(stage.seconds)}</span>
            </div>
            <p class="small muted" style="margin:0 0 8px">${esc(stage.description)}</p>
            ${stage.items.map((item) => renderItem(item, n++)).join('')}
        </section>`,
        )
        .join('');

    const list = (items) => (items?.length ? `<ul class="small">${items.map((m) => `<li>${esc(m)}</li>`).join('')}</ul>` : '');

    return `
        <div class="section">
            <h2 style="margin:0">${esc(r.emoji)} ${esc(r.title)}</h2>
            <p class="small muted" style="margin:6px 0 0">A warm-up built to prepare you for your ${esc(r.name)} session.</p>
            <div class="routine-summary">
                <span class="badge">${icon('clock')} ${clock(r.total_seconds)}</span>
                <span class="badge">${r.items.length} exercises</span>
                <span class="badge">Intensity: ${esc(r.intensity)}</span>
                <span class="badge badge-ok">Level ${r.level} · ${esc(r.level_label)}</span>
            </div>
        </div>

        ${
            r.adjustments?.length
                ? `<details class="card" style="margin-top:12px"><summary class="small" style="cursor:pointer;font-weight:700">Adjustments made (${r.adjustments.length})</summary>${list(r.adjustments)}</details>`
                : ''
        }

        ${stages}

        <p class="small muted section">Includes ${r.transition_seconds}-second “get ready” transitions. Stop if you feel sharp pain or anything unusual.</p>`;
}

function renderItem(item, index) {
    const ex = item.exercise;
    const payload = esc(JSON.stringify(ex.animation));
    return `
        <div class="routine-item" data-stage="${esc(item.stage)}">
            <span class="num">${index + 1}</span>
            <div>
                <strong>${esc(item.name)}</strong>
                <span class="sub">${esc(item.cue || item.reps_label)}</span>
            </div>
            <div class="dur">${clock(item.seconds)}${item.per_side ? `<small>${clock(item.side_seconds)} / side</small>` : ''}</div>
            <div class="routine-item-actions">
                <span class="ex-anim ex-anim-sm" data-animation="${esc(ex.animation.pattern)}">${animationSvg(ex.animation.pattern)}</span>
                <button type="button" class="btn btn-ghost btn-sm" data-animation-payload="${payload}">${icon('maximize')} View</button>
                <details style="flex-basis:100%">
                    <summary class="small muted" style="cursor:pointer;font-weight:700">How to · ${esc(ex.purpose)}</summary>
                    <ol class="small" style="margin-top:6px">${ex.instructions.map((s) => `<li>${esc(s)}</li>`).join('')}</ol>
                    <p class="small"><strong>Common mistakes:</strong> ${esc(ex.mistakes.join(' · '))}</p>
                    <p class="small"><strong>Safety:</strong> ${esc(ex.safety.join(' '))}</p>
                    <a class="small" href="${esc(ex.url)}" target="_blank" rel="noopener">Full exercise card ↗</a>
                </details>
            </div>
        </div>`;
}
