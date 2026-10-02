import { Countdown } from '../lib/clock.js';
import { sound, vibrate, speak, countdownBeep, unlockAudio } from '../lib/cues.js';
import { keepAwake, releaseAwake } from '../lib/wakelock.js';
import { getSettings } from '../lib/settings.js';
import { clock, duration } from '../lib/format.js';
import { $, esc, cloneTemplate, setIcon } from '../lib/dom.js';
import { animationSvg, openAnimation, closeAnimation } from '../components/exercise-animation.js';

/** Turn a rest label ("2–3 min", "90 sec", "walk between rounds") into seconds. */
export function restSeconds(label) {
    if (!label) return 90;
    const s = String(label).toLowerCase();
    const m = s.match(/(\d+)/);
    if (!m) return 90;
    let n = Number(m[1]);
    if (s.includes('min')) n *= 60;
    return Math.min(600, Math.max(15, n));
}

/**
 * Guided workout player for a training split.
 * Steps through each exercise set by set, with a rest-timer countdown between
 * sets. Mirrors the warm-up Workout Mode, but set/rep based.
 */
export class WorkoutSession {
    constructor(exercises, { split = '', label = '', onClose } = {}) {
        this.items = (exercises || []).filter((e) => e && e.sets);
        this.split = split;
        this.label = label;
        this.onClose = onClose;
        this.index = 0;
        this.set = 1;
        this.phase = 'work'; // 'work' | 'rest' | 'done'
        this.countdown = null;
        this.lastWhole = null;
        this.el = null;
        this.onKey = this.onKey.bind(this);
        this.onPop = this.onPop.bind(this);
    }

    open() {
        if (!this.items.length) return;
        unlockAudio();
        this.el = cloneTemplate('workout-session-template');
        document.body.appendChild(this.el);
        document.body.style.overflow = 'hidden';

        this.el.addEventListener('click', (e) => {
            const action = e.target.closest('[data-action]')?.dataset.action;
            if (action) this.handle(action);
        });
        document.addEventListener('keydown', this.onKey);
        history.pushState({ session: true }, '');
        window.addEventListener('popstate', this.onPop);

        this.renderSoundButton();
        keepAwake();
        this.startExercise(0);
    }

    /* ------------------------------------------------------------ flow */

    get item() {
        return this.items[this.index];
    }

    totalSets() {
        return this.item?.sets?.sets || 1;
    }

    startExercise(index) {
        this.countdown?.stop();
        this.index = index;
        this.set = 1;
        this.phase = 'work';
        this.renderItem();
        this.render();
        speak(`${index === 0 ? 'First' : 'Next'}: ${this.item.name}.`);
    }

    /** User finished the current set. */
    completeSet() {
        if (this.phase === 'rest') return this.skipRest();

        if (this.set >= this.totalSets()) {
            // Exercise finished.
            if (this.index >= this.items.length - 1) {
                return this.finishWorkout();
            }
            sound.end();
            vibrate([120, 80, 120]);
            return this.startExercise(this.index + 1);
        }

        // More sets to go — rest, then the next set.
        this.startRest();
    }

    startRest() {
        this.phase = 'rest';
        const secs = restSeconds(this.item.sets.rest);
        sound.end();
        vibrate(150);
        speak(`Rest. ${duration(secs)}.`);
        this.render();
        this.lastWhole = null;
        this.countdown?.stop();
        this.countdown = new Countdown({
            seconds: secs,
            onTick: (c) => this.tickRest(c),
            onDone: () => this.restDone(),
        });
        this.countdown.start();
    }

    tickRest(c) {
        const whole = Math.ceil(c.remainingMs / 1000);
        if (c.running && whole !== this.lastWhole) {
            if (whole <= 3 && whole >= 1 && this.lastWhole !== null) countdownBeep();
            this.lastWhole = whole;
        }
        $('[data-slot="clock"]', this.el).textContent = clock(c.remainingMs / 1000);
        $('[data-slot="bar"]', this.el).style.width = `${(c.progress * 100).toFixed(1)}%`;
    }

    restDone() {
        sound.go();
        vibrate(200);
        this.set += 1;
        this.phase = 'work';
        speak(`Set ${this.set}. ${this.item.name}.`);
        this.render();
    }

    skipRest() {
        this.countdown?.stop();
        this.restDone();
    }

    addRest(seconds) {
        if (this.phase === 'rest') this.countdown?.add(seconds);
    }

    finishWorkout() {
        this.countdown?.stop();
        sound.done();
        vibrate([300, 120, 300]);
        speak('Workout complete. Well done.');
        this.phase = 'done';
        this.render();
    }

    /* --------------------------------------------------------- actions */

    handle(action) {
        switch (action) {
            case 'done-set':
                return this.completeSet();
            case 'skip-rest':
                return this.skipRest();
            case 'add-rest':
                return this.addRest(15);
            case 'next':
                return this.index >= this.items.length - 1 ? this.finishWorkout() : this.startExercise(this.index + 1);
            case 'prev':
                return this.startExercise(Math.max(0, this.index - 1));
            case 'view':
                return openAnimation(this.item.animation);
            case 'sound': {
                const s = getSettings();
                s.sound = !s.sound;
                try {
                    localStorage.setItem('readyup.settings', JSON.stringify(s));
                } catch {
                    // ignore
                }
                return this.renderSoundButton();
            }
            case 'fullscreen':
                return this.toggleFullscreen();
            case 'end':
                return this.confirmEnd();
            case 'close':
                return this.close();
        }
    }

    toggleFullscreen() {
        try {
            if (document.fullscreenElement) document.exitFullscreen();
            else this.el.requestFullscreen?.();
        } catch {
            // not supported — ignore
        }
    }

    confirmEnd() {
        if (this.phase === 'done' || window.confirm('End this workout now?')) this.close();
    }

    onKey(e) {
        if (document.querySelector('.modal') || e.target.closest?.('input, textarea, select')) return;
        if (e.key === ' ' || e.key === 'Enter') {
            e.preventDefault();
            this.completeSet();
        } else if (e.key === 'ArrowRight') {
            this.handle('next');
        } else if (e.key === 'ArrowLeft') {
            this.handle('prev');
        } else if (e.key === 'Escape') {
            this.confirmEnd();
        }
    }

    onPop() {
        if (window.confirm('Leave the workout?')) {
            this.close({ fromBack: true });
        } else {
            history.pushState({ session: true }, '');
        }
    }

    close({ fromBack = false } = {}) {
        this.countdown?.stop();
        closeAnimation();
        releaseAwake();
        document.removeEventListener('keydown', this.onKey);
        window.removeEventListener('popstate', this.onPop);
        if (!fromBack && history.state?.session) history.back();
        try {
            if (document.fullscreenElement) document.exitFullscreen();
        } catch {
            // ignore
        }
        this.el?.remove();
        document.body.style.overflow = '';
        this.onClose?.();
    }

    /* -------------------------------------------------------- rendering */

    renderItem() {
        const el = this.el;
        const item = this.item;
        const next = this.items[this.index + 1];

        $('[data-slot="count"]', el).textContent = `${this.index + 1} / ${this.items.length}`;
        $('[data-slot="name"]', el).textContent = item.name;
        $('[data-slot="ar"]', el).textContent = item.name_ar || '';
        $('[data-slot="demo"]', el).innerHTML = `
            <button type="button" class="ex-anim" data-action="view" aria-label="View animation: ${esc(item.name)}">
                ${animationSvg(item.animation.pattern)}
            </button>`;
        $('[data-slot="steps"]', el).innerHTML = (item.instructions || []).map((s) => `<li>${esc(s)}</li>`).join('');
        $('[data-slot="next"]', el).textContent = next ? next.name : 'Finish 🎉';
        $('[data-action="prev"]', el).disabled = this.index === 0;
    }

    render() {
        const el = this.el;
        const item = this.item;
        el.dataset.phase = this.phase;

        if (this.phase === 'done') {
            $('[data-slot="done-summary"]', el).textContent = `${this.items.length} exercises · ${this.label} workout`;
            return;
        }

        const total = this.totalSets();
        $('[data-slot="label"]', el).textContent = this.phase === 'rest' ? 'Rest' : 'Work';
        $('[data-slot="setinfo"]', el).textContent = `Set ${this.set} of ${total}`;
        $('[data-slot="reps"]', el).textContent = this.phase === 'rest'
            ? `Next: set ${Math.min(total, this.set + 1)} of ${total}`
            : `${item.sets.reps}`;

        // Overall progress across all sets of all exercises.
        const doneBefore = this.items.slice(0, this.index).reduce((s, it) => s + (it.sets?.sets || 1), 0);
        const totalAll = this.items.reduce((s, it) => s + (it.sets?.sets || 1), 0);
        const done = doneBefore + (this.set - 1) + (this.phase === 'rest' ? 0.5 : 0);
        $('[data-slot="overall"]', el).style.width = `${Math.min(100, (done / totalAll) * 100).toFixed(1)}%`;

        // Primary button label.
        const btn = $('[data-slot="primary-label"]', el);
        if (this.phase === 'rest') {
            btn.textContent = 'Skip rest';
        } else if (this.set >= total) {
            btn.textContent = this.index >= this.items.length - 1 ? 'Finish workout' : 'Done — next exercise';
        } else {
            btn.textContent = 'Set done';
        }

        if (this.phase !== 'rest') {
            $('[data-slot="clock"]', el).textContent = `${item.sets.reps}`;
            $('[data-slot="bar"]', el).style.width = '0%';
        }
    }

    renderSoundButton() {
        const btn = $('[data-action="sound"]', this.el);
        const on = getSettings().sound;
        setIcon(btn.querySelector('svg'), on ? 'volume' : 'volume-x');
        btn.setAttribute('aria-pressed', String(on));
    }
}
