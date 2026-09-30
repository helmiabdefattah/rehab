import { $, $$, pressOne, setIcon, esc } from '../lib/dom.js';
import { Countdown, Stopwatch } from '../lib/clock.js';
import { sound, vibrate, speak, countdownBeep, unlockAudio } from '../lib/cues.js';
import { keepAwake, releaseAwake } from '../lib/wakelock.js';
import { read, write } from '../lib/store.js';
import { clock, clockMs, stopwatch } from '../lib/format.js';

// Training timer: Rest (between sets), Interval (work/rest rounds), Stopwatch (laps), Countdown.

const DEFAULTS = {
    mode: 'rest',
    rest: { seconds: 90, set: 1 },
    interval: { work: 40, rest: 20, rounds: 8 },
    countdown: { min: 5, sec: 0 },
};

export function initTimer() {
    const face = $('[data-timer-face]');
    if (!face) return;

    const saved = read('timer', {}) || {};
    const state = {
        mode: saved.mode || DEFAULTS.mode,
        rest: { ...DEFAULTS.rest, ...saved.rest },
        interval: { ...DEFAULTS.interval, ...saved.interval },
        countdown: { ...DEFAULTS.countdown, ...saved.countdown },
    };
    const persist = () => write('timer', state);

    const ui = {
        phase: $('[data-timer-phase]'),
        clock: $('[data-timer-clock]'),
        sub: $('[data-timer-sub]'),
        bar: $('[data-timer-bar]'),
        toggle: $('[data-timer-toggle]'),
        toggleLabel: $('[data-timer-toggle-label]'),
        toggleIcon: $('[data-timer-toggle-icon]'),
        reset: $('[data-timer-reset]'),
        extra: $('[data-timer-extra]'),
        extraLabel: $('[data-timer-extra-label]'),
    };

    let engine = null;

    const setFace = ({ phase = 'work', label = '', time = '', sub = '', progress = 0 }) => {
        face.dataset.phase = phase;
        ui.phase.textContent = label;
        ui.clock.textContent = time;
        ui.sub.textContent = sub;
        ui.bar.style.width = `${Math.min(100, progress * 100).toFixed(1)}%`;
    };

    const setToggle = (running, started) => {
        ui.toggleLabel.textContent = running ? 'Pause' : started ? 'Resume' : 'Start';
        setIcon(ui.toggleIcon, running ? 'pause' : 'play');
    };

    const beepLastSeconds = (c, memo) => {
        const whole = Math.ceil(c.remainingMs / 1000);
        if (c.running && whole !== memo.last) {
            if (whole <= 3 && whole >= 1 && memo.last !== null) countdownBeep();
            memo.last = whole;
        }
    };

    /* -------------------------------------------------------- engines */

    const engines = {
        rest() {
            const memo = { last: null };
            let finished = false;
            const c = new Countdown({
                seconds: state.rest.seconds,
                onTick: (cd) => {
                    beepLastSeconds(cd, memo);
                    render();
                },
                onDone: () => {
                    finished = true;
                    state.rest.set += 1;
                    persist();
                    sound.end();
                    vibrate([300, 120, 300]);
                    speak(`Rest over. Set ${state.rest.set}.`);
                    releaseAwake();
                    c.reset(state.rest.seconds);
                    memo.last = null;
                    renderSet();
                    render();
                },
            });
            const render = () => {
                if (finished && !c.running) {
                    setFace({ phase: 'done', label: 'Rest over', time: 'GO!', sub: `Set ${state.rest.set}`, progress: 1 });
                } else {
                    setFace({
                        phase: c.running || c.elapsedMs ? 'rest' : 'work',
                        label: c.running ? 'Resting' : c.elapsedMs ? 'Paused' : 'Rest timer',
                        time: clockMs(c.remainingMs),
                        sub: `Next: set ${state.rest.set}`,
                        progress: c.progress,
                    });
                }
                setToggle(c.running, c.elapsedMs > 0);
            };
            return {
                extraLabel: '15s',
                start: () => {
                    finished = false;
                    c.start();
                    render();
                },
                pause: () => c.pause(),
                running: () => c.running,
                reset: () => {
                    finished = false;
                    memo.last = null;
                    c.reset(state.rest.seconds);
                },
                extra: () => c.add(15),
                configure: () => {
                    finished = false;
                    memo.last = null;
                    c.reset(state.rest.seconds);
                },
                render,
            };
        },

        interval() {
            const memo = { last: null };
            let phases = [];
            let i = 0;
            let c = null;
            let done = false;

            const build = () => {
                const { work, rest, rounds } = state.interval;
                phases = [{ type: 'prep', s: 10, round: 1 }];
                for (let r = 1; r <= rounds; r++) {
                    phases.push({ type: 'work', s: work, round: r });
                    if (rest > 0 && r < rounds) phases.push({ type: 'rest', s: rest, round: r });
                }
            };

            const startPhase = (index, autostart) => {
                c?.stop();
                i = index;
                memo.last = null;
                const p = phases[i];
                c = new Countdown({
                    seconds: p.s,
                    onTick: (cd) => {
                        beepLastSeconds(cd, memo);
                        render();
                    },
                    onDone: () => next(),
                });
                if (autostart) {
                    if (p.type === 'work') {
                        sound.go();
                        vibrate(250);
                        speak(`Work. Round ${p.round}.`);
                    } else if (p.type === 'rest') {
                        sound.end();
                        vibrate([100, 60, 100]);
                        speak('Rest.');
                    }
                    c.start();
                }
                render();
            };

            const next = () => {
                if (i >= phases.length - 1) {
                    done = true;
                    c.stop();
                    sound.done();
                    vibrate([300, 120, 300, 120, 300]);
                    speak('Intervals complete.');
                    releaseAwake();
                    render();
                    return;
                }
                startPhase(i + 1, true);
            };

            const render = () => {
                const p = phases[i];
                const remainingTotal = phases.slice(i + 1).reduce((s, x) => s + x.s, 0) + c.remainingMs / 1000;
                if (done) {
                    setFace({ phase: 'done', label: 'Complete', time: 'DONE', sub: `${state.interval.rounds} rounds`, progress: 1 });
                } else {
                    setFace({
                        phase: p.type,
                        label: { prep: 'Get ready', work: 'Work', rest: 'Rest' }[p.type] + (c.running || c.elapsedMs ? '' : ' · tap Start'),
                        time: clockMs(c.remainingMs),
                        sub: `Round ${p.round}/${state.interval.rounds} · ${clock(remainingTotal)} left`,
                        progress: c.progress,
                    });
                }
                setToggle(!done && c.running, c.elapsedMs > 0 || i > 0);
            };

            build();
            startPhase(0, false);

            return {
                extraLabel: 'Skip',
                start: () => {
                    if (done) {
                        done = false;
                        build();
                        startPhase(0, true);
                        return;
                    }
                    c.start();
                    render();
                },
                pause: () => c.pause(),
                running: () => !done && c.running,
                reset: () => {
                    done = false;
                    build();
                    startPhase(0, false);
                },
                extra: () => {
                    if (!done && (c.running || c.elapsedMs > 0 || i > 0)) next();
                },
                configure: () => {
                    done = false;
                    build();
                    startPhase(0, false);
                },
                render,
            };
        },

        stopwatch() {
            const laps = [];
            const list = $('[data-laps]');
            const emptyNote = $('[data-laps-empty]');
            const lapCount = $('[data-lap-count]');
            const sw = new Stopwatch({ onTick: () => render() });

            const renderLaps = () => {
                list.innerHTML = laps
                    .map((l, n) => `<li><span>Lap ${laps.length - n}</span><span>${esc(stopwatch(l.lap))}</span><span class="muted">${esc(stopwatch(l.total))}</span></li>`)
                    .join('');
                emptyNote.hidden = laps.length > 0;
                lapCount.textContent = laps.length ? `${laps.length} lap${laps.length === 1 ? '' : 's'}` : '';
            };

            const render = () => {
                setFace({
                    phase: 'work',
                    label: sw.running ? 'Running' : sw.elapsedMs ? 'Paused' : 'Stopwatch',
                    time: stopwatch(sw.elapsedMs),
                    sub: laps.length ? `Last lap ${stopwatch(laps[0].lap)}` : '',
                    progress: (sw.elapsedMs % 60000) / 60000,
                });
                setToggle(sw.running, sw.elapsedMs > 0);
            };

            renderLaps();
            return {
                extraLabel: 'Lap',
                start: () => {
                    sw.start();
                    render();
                },
                pause: () => sw.pause(),
                running: () => sw.running,
                reset: () => {
                    sw.reset();
                    laps.length = 0;
                    renderLaps();
                },
                extra: () => {
                    if (!sw.running) return;
                    const total = sw.elapsedMs;
                    const prev = laps[0]?.total ?? 0;
                    laps.unshift({ lap: total - prev, total });
                    sound.tick();
                    vibrate(60);
                    renderLaps();
                    render();
                },
                configure: () => {},
                render,
            };
        },

        countdown() {
            const memo = { last: null };
            let finished = false;
            const seconds = () => Math.max(1, state.countdown.min * 60 + state.countdown.sec);
            const c = new Countdown({
                seconds: seconds(),
                onTick: (cd) => {
                    beepLastSeconds(cd, memo);
                    render();
                },
                onDone: () => {
                    finished = true;
                    sound.done();
                    vibrate([300, 120, 300, 120, 300]);
                    speak('Time.');
                    releaseAwake();
                    render();
                },
            });
            const render = () => {
                setFace({
                    phase: finished ? 'done' : 'work',
                    label: finished ? 'Time!' : c.running ? 'Counting down' : c.elapsedMs ? 'Paused' : 'Countdown',
                    time: finished ? '0:00' : clockMs(c.remainingMs),
                    sub: `of ${clock(c.durationMs / 1000)}`,
                    progress: c.progress,
                });
                setToggle(c.running, c.elapsedMs > 0 && !finished);
            };
            return {
                extraLabel: '30s',
                start: () => {
                    if (finished) {
                        finished = false;
                        memo.last = null;
                        c.reset(seconds());
                    }
                    c.start();
                    render();
                },
                pause: () => c.pause(),
                running: () => c.running,
                reset: () => {
                    finished = false;
                    memo.last = null;
                    c.reset(seconds());
                },
                extra: () => {
                    finished = false;
                    c.add(30);
                },
                configure: () => {
                    finished = false;
                    memo.last = null;
                    c.reset(seconds());
                },
                render,
            };
        },
    };

    /* --------------------------------------------------------- wiring */

    const switchMode = (mode) => {
        if (engine?.running() && !window.confirm('Stop the running timer and switch mode?')) {
            pressOne($('[data-timer-tabs]'), state.mode);
            return;
        }
        engine?.pause();
        engine?.reset();
        releaseAwake();
        state.mode = mode;
        persist();
        pressOne($('[data-timer-tabs]'), mode);
        $$('[data-panel]').forEach((p) => (p.hidden = p.dataset.panel !== mode));
        engine = engines[mode]();
        ui.extraLabel.textContent = engine.extraLabel;
        setIcon(ui.extra.querySelector('svg'), mode === 'stopwatch' ? 'flag' : mode === 'interval' ? 'skip-forward' : 'plus');
        engine.render();
    };

    $$('[data-timer-tabs] button').forEach((b) => {
        b.dataset.value = b.dataset.mode;
        b.addEventListener('click', () => switchMode(b.dataset.mode));
    });

    ui.toggle.addEventListener('click', () => {
        unlockAudio();
        if (engine.running()) {
            engine.pause();
            releaseAwake();
        } else {
            engine.start();
            keepAwake();
        }
        engine.render();
    });

    ui.reset.addEventListener('click', () => {
        engine.pause();
        engine.reset();
        releaseAwake();
        engine.render();
    });

    ui.extra.addEventListener('click', () => {
        unlockAudio();
        engine.extra();
        engine.render();
    });

    // Rest presets & set counter
    const renderSet = () => ($('[data-set-count]').textContent = state.rest.set);
    $('[data-rest-presets]').addEventListener('click', (e) => {
        const b = e.target.closest('button[data-seconds]');
        if (!b) return;
        state.rest.seconds = Number(b.dataset.seconds);
        $$('[data-rest-presets] button').forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
        persist();
        if (state.mode === 'rest') {
            engine.configure();
            releaseAwake();
            engine.render();
        }
    });
    $$('[data-rest-presets] button').forEach((x) => x.setAttribute('aria-pressed', String(Number(x.dataset.seconds) === state.rest.seconds)));
    $('[data-set-plus]').addEventListener('click', () => {
        state.rest.set += 1;
        persist();
        renderSet();
        if (state.mode === 'rest') engine.render();
    });
    $('[data-set-minus]').addEventListener('click', () => {
        state.rest.set = Math.max(1, state.rest.set - 1);
        persist();
        renderSet();
        if (state.mode === 'rest') engine.render();
    });
    renderSet();

    // Steppers (interval + countdown settings)
    const stepperTarget = {
        work: [state.interval, 'work', 'interval'],
        rest: [state.interval, 'rest', 'interval'],
        rounds: [state.interval, 'rounds', 'interval'],
        cmin: [state.countdown, 'min', 'countdown'],
        csec: [state.countdown, 'sec', 'countdown'],
    };

    $$('[data-stepper]').forEach((box) => {
        const [obj, key, mode] = stepperTarget[box.dataset.stepper];
        const input = $('input', box);
        const min = Number(box.dataset.min);
        const max = Number(box.dataset.max);
        const step = Number(box.dataset.step);
        const set = (v) => {
            const value = Math.max(min, Math.min(max, Math.round(Number(v) || 0)));
            obj[key] = value;
            input.value = value;
            persist();
            if (state.mode === mode && !engine.running()) {
                engine.configure();
                engine.render();
            }
            $$('[data-interval-presets] .chip').forEach((c) => c.setAttribute('aria-pressed', 'false'));
        };
        input.value = obj[key];
        $('[data-dec]', box).addEventListener('click', () => set(obj[key] - step));
        $('[data-inc]', box).addEventListener('click', () => set(obj[key] + step));
        input.addEventListener('change', () => set(input.value));
    });

    $('[data-interval-presets]').addEventListener('click', (e) => {
        const b = e.target.closest('.chip');
        if (!b) return;
        if (engine?.running() && !window.confirm('Stop the running intervals and load this preset?')) return;
        Object.assign(state.interval, { work: Number(b.dataset.work), rest: Number(b.dataset.rest), rounds: Number(b.dataset.rounds) });
        persist();
        $$('[data-stepper]').forEach((box) => {
            const [obj, key] = stepperTarget[box.dataset.stepper];
            $('input', box).value = obj[key];
        });
        $$('[data-interval-presets] .chip').forEach((c) => c.setAttribute('aria-pressed', String(c === b)));
        if (state.mode === 'interval') {
            engine.pause();
            engine.configure();
            releaseAwake();
            engine.render();
        }
    });

    // Space bar toggles the timer on desktop.
    document.addEventListener('keydown', (e) => {
        if (e.key === ' ' && !e.target.closest('input, textarea, select, button')) {
            e.preventDefault();
            ui.toggle.click();
        }
    });

    switchMode(state.mode);
}
