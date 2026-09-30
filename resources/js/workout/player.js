import { Countdown } from '../lib/clock.js';
import { sound, vibrate, speak, countdownBeep, unlockAudio } from '../lib/cues.js';
import { keepAwake, releaseAwake } from '../lib/wakelock.js';
import { getSettings, saveSettings } from '../lib/settings.js';
import { clock, duration } from '../lib/format.js';
import { $, esc, cloneTemplate, setIcon, icon } from '../lib/dom.js';
import { write, remove } from '../lib/store.js';
import { openVideo, closeVideo } from '../components/video-modal.js';

const STAGE_ICONS = { heat: 'flame', mobility: 'rotate', activation: 'target', dynamic: 'activity' };

/**
 * Full-screen Workout Mode.
 * Phases per exercise: "ready" (get-ready countdown) → "work".
 * Per-side exercises cue "switch sides" at half time.
 */
export class WorkoutPlayer {
    constructor(routine, { onFinish, resume = null } = {}) {
        this.routine = routine;
        this.items = routine.items;
        this.onFinish = onFinish;
        this.index = resume?.index ?? 0;
        this.completed = new Set(resume?.completed ?? []);
        this.skipped = new Set(resume?.skipped ?? []);
        this.startedAt = resume?.startedAt ?? Date.now();
        this.phase = 'ready';
        this.paused = false;
        this.countdown = null;
        this.lastWhole = null;
        this.switched = false;
        this.el = null;
        this.onKey = this.onKey.bind(this);
        this.onPop = this.onPop.bind(this);
        this.onBeforeUnload = (e) => {
            e.preventDefault();
            e.returnValue = '';
        };
    }

    open() {
        unlockAudio();
        this.el = cloneTemplate('workout-template');
        document.body.appendChild(this.el);
        document.body.style.overflow = 'hidden';

        this.el.addEventListener('click', (e) => {
            const action = e.target.closest('[data-action]')?.dataset.action;
            if (action) this.handle(action);
        });
        document.addEventListener('keydown', this.onKey);
        window.addEventListener('beforeunload', this.onBeforeUnload);
        history.pushState({ workout: true }, '');
        window.addEventListener('popstate', this.onPop);

        this.renderSoundButton();
        keepAwake();
        this.startItem(this.index);
    }

    /* ------------------------------------------------------------ flow */

    startItem(index, { skipReady = false } = {}) {
        this.countdown?.stop();
        this.index = index;
        this.switched = false;
        this.lastWhole = null;
        this.persist();

        const item = this.items[index];
        const getReady = getSettings().getReady;

        this.renderItem(item);

        if (!skipReady && getReady > 0) {
            this.phase = 'ready';
            speak(`${index === 0 ? 'First' : 'Next'}: ${item.name}. ${duration(item.seconds)}.`);
            this.runCountdown(getReady, () => this.startWork());
        } else {
            this.startWork();
        }
    }

    startWork() {
        const item = this.items[this.index];
        this.phase = 'work';
        this.switched = false;
        this.lastWhole = null;
        sound.go();
        vibrate(200);
        if (getSettings().getReady === 0) speak(item.name);
        this.runCountdown(item.seconds, () => this.completeItem());
    }

    completeItem() {
        this.completed.add(this.index);
        this.skipped.delete(this.index);

        if (this.index >= this.items.length - 1) {
            sound.done();
            vibrate([300, 120, 300]);
            speak('Warm-up complete. Well done.');
            this.finish(false);
            return;
        }

        sound.end();
        vibrate([120, 80, 120]);
        this.startItem(this.index + 1);
    }

    runCountdown(seconds, onDone) {
        this.countdown?.stop();
        this.countdown = new Countdown({
            seconds,
            onTick: (c) => this.tick(c),
            onDone,
        });
        this.render();
        if (!this.paused) this.countdown.start();
        else this.tick(this.countdown);
    }

    tick(c) {
        const remaining = c.remainingMs / 1000;
        const whole = Math.ceil(remaining);
        const item = this.items[this.index];

        if (c.running && whole !== this.lastWhole) {
            if (whole <= 3 && whole >= 1 && this.lastWhole !== null) countdownBeep();

            if (this.phase === 'work' && item.per_side && !this.switched && c.elapsedMs >= c.durationMs / 2) {
                this.switched = true;
                sound.switchSides();
                vibrate([100, 60, 100]);
                speak('Switch sides');
            }
            this.lastWhole = whole;
        }

        this.renderClock(c);
    }

    /* --------------------------------------------------------- actions */

    handle(action) {
        switch (action) {
            case 'toggle':
                return this.togglePause();
            case 'next':
                return this.skip();
            case 'prev':
                return this.previous();
            case 'restart':
                this.paused = false;
                this.renderToggle();
                return this.startItem(this.index, { skipReady: true });
            case 'video':
                return this.showVideo();
            case 'sound':
                saveSettings({ sound: !getSettings().sound });
                return this.renderSoundButton();
            case 'fullscreen':
                return this.toggleFullscreen();
            case 'end':
                return this.confirmEnd();
        }
    }

    togglePause(force) {
        this.paused = force ?? !this.paused;
        if (this.paused) {
            this.countdown?.pause();
            window.speechSynthesis?.cancel?.();
        } else {
            unlockAudio();
            this.countdown?.start();
        }
        this.el.classList.toggle('is-paused', this.paused);
        this.renderToggle();
    }

    skip() {
        if (!this.completed.has(this.index)) this.skipped.add(this.index);
        if (this.index >= this.items.length - 1) {
            this.finish(false);
            return;
        }
        this.startItem(this.index + 1);
    }

    previous() {
        this.startItem(Math.max(0, this.index - 1));
    }

    showVideo() {
        const video = this.items[this.index]?.exercise?.video;
        if (!video) return;
        const wasRunning = !this.paused;
        if (getSettings().pauseOnVideo && wasRunning) this.togglePause(true);
        openVideo(video, {
            onClose: () => {
                if (getSettings().pauseOnVideo && wasRunning) this.togglePause(false);
            },
        });
    }

    toggleFullscreen() {
        try {
            if (document.fullscreenElement) document.exitFullscreen();
            else this.el.requestFullscreen?.();
        } catch {
            // not supported (e.g. iPhone) — ignore
        }
    }

    confirmEnd() {
        const wasRunning = !this.paused;
        this.togglePause(true);
        if (window.confirm('End this warm-up now? You can still record how you felt.')) {
            this.finish(true);
        } else if (wasRunning) {
            this.togglePause(false);
        }
    }

    onKey(e) {
        if (document.querySelector('.modal') || e.target.closest?.('input, textarea, select')) return;
        const map = { ' ': 'toggle', ArrowRight: 'next', ArrowLeft: 'prev', r: 'restart', R: 'restart' };
        if (map[e.key]) {
            e.preventDefault();
            this.handle(map[e.key]);
        } else if (e.key === 'Escape') {
            this.confirmEnd();
        }
    }

    onPop() {
        // Android back button / browser back while training.
        const wasRunning = !this.paused;
        this.togglePause(true);
        if (window.confirm('Leave Workout Mode? Your progress in this warm-up will end.')) {
            this.finish(true, { fromBack: true });
        } else {
            history.pushState({ workout: true }, '');
            if (wasRunning) this.togglePause(false);
        }
    }

    finish(early, { fromBack = false } = {}) {
        this.countdown?.stop();
        closeVideo();
        releaseAwake();
        remove('activeWorkout');
        document.removeEventListener('keydown', this.onKey);
        window.removeEventListener('beforeunload', this.onBeforeUnload);
        window.removeEventListener('popstate', this.onPop);
        if (!fromBack && history.state?.workout) history.back();
        try {
            if (document.fullscreenElement) document.exitFullscreen();
        } catch {
            // ignore
        }
        this.el.remove();
        document.body.style.overflow = '';

        this.onFinish?.({
            routine: this.routine,
            early,
            completed: [...this.completed],
            skipped: [...this.skipped],
            elapsedSec: Math.round((Date.now() - this.startedAt) / 1000),
        });
    }

    persist() {
        write('activeWorkout', {
            routine: this.routine,
            index: this.index,
            completed: [...this.completed],
            skipped: [...this.skipped],
            startedAt: this.startedAt,
            savedAt: Date.now(),
        });
    }

    /* -------------------------------------------------------- rendering */

    renderItem(item) {
        const el = this.el;
        const next = this.items[this.index + 1];

        el.dataset.stage = item.stage;
        $('[data-slot="count"]', el).textContent = `${this.index + 1} / ${this.items.length}`;
        $('[data-slot="name"]', el).textContent = item.name;
        $('[data-slot="ar"]', el).textContent = item.name_ar;
        $('[data-slot="cue"]', el).textContent = item.cue || item.reps_label || '';
        $('[data-slot="steps"]', el).innerHTML = (item.exercise.instructions || []).map((s) => `<li>${esc(s)}</li>`).join('');

        const video = item.exercise.video;
        const demo = $('[data-slot="demo"]', el);
        demo.innerHTML = `
            <button type="button" class="ex-thumb" data-action="video" data-stage="${esc(item.stage)}" aria-label="Watch video: ${esc(item.name)}">
                <span class="ex-thumb-fallback">${icon(STAGE_ICONS[item.stage] || 'target')}<span>${esc(item.stage_label)}</span></span>
                ${video.thumbnail ? `<img src="${esc(video.thumbnail)}" alt="" referrerpolicy="no-referrer" onerror="this.remove()"><span class="play-badge">${icon('play', true)}</span>` : ''}
            </button>`;
        const videoBtn = $('[data-action="video"].btn-video', el);
        $('[data-slot="video-label"]', el).textContent = video.embed ? 'Watch Video' : 'Find a Video';
        setIcon(videoBtn.querySelector('svg'), video.embed ? 'play' : 'search');
        videoBtn.querySelector('svg').classList.toggle('icon-fill', !!video.embed);
        videoBtn.classList.toggle('is-search', !video.embed);

        $('[data-slot="next"]', el).textContent = next ? next.name : 'Finish 🎉';
        $('[data-slot="next-dur"]', el).textContent = next ? clock(next.seconds) : '';
        $('[data-action="prev"]', el).disabled = this.index === 0;
    }

    render() {
        const item = this.items[this.index];
        this.el.dataset.phase = this.phase;
        $('[data-slot="label"]', this.el).textContent = this.phase === 'ready' ? 'Get ready' : 'Current exercise';
        $('[data-slot="of"]', this.el).hidden = this.phase === 'ready';
        $('[data-slot="of"]', this.el).textContent = `of ${clock(item.seconds)}${item.per_side ? ` · ${clock(item.side_seconds)} each side` : ''}`;
        this.renderToggle();
    }

    renderClock(c) {
        const el = this.el;
        const item = this.items[this.index];
        const remaining = c.remainingMs / 1000;

        $('[data-slot="clock"]', el).textContent = clock(remaining);
        $('[data-slot="bar"]', el).style.width = `${(c.progress * 100).toFixed(1)}%`;

        let side = '';
        if (this.phase === 'work' && item.per_side) {
            side = c.elapsedMs >= c.durationMs / 2 ? 'Side 2 — switch sides' : 'Side 1';
        } else if (this.phase === 'ready') {
            side = item.per_side ? `${clock(item.side_seconds)} each side` : clock(item.seconds);
        }
        $('[data-slot="side"]', el).textContent = side;

        // Whole-routine progress and time left.
        const transition = this.routine.transition_seconds ?? 0;
        const done = this.items.slice(0, this.index).reduce((s, it) => s + it.seconds + transition, 0);
        const current = this.phase === 'ready' ? c.elapsedMs / 1000 : transition + c.elapsedMs / 1000;
        const total = this.routine.total_seconds || 1;
        $('[data-slot="overall"]', el).style.width = `${Math.min(100, ((done + current) / total) * 100).toFixed(1)}%`;
        $('[data-slot="total"]', el).textContent = `${clock(Math.max(0, total - done - current))} left`;
    }

    renderToggle() {
        $('[data-slot="toggle-label"]', this.el).textContent = this.paused ? 'Resume' : 'Pause';
        setIcon($('[data-slot="toggle-icon"]', this.el), this.paused ? 'play' : 'pause');
    }

    renderSoundButton() {
        const btn = $('[data-action="sound"]', this.el);
        const on = getSettings().sound;
        setIcon(btn.querySelector('svg'), on ? 'volume' : 'volume-x');
        btn.setAttribute('aria-pressed', String(on));
        btn.title = on ? 'Sound on' : 'Sound off';
    }
}
