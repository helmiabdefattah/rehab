import { getSettings } from './settings.js';

// Audio, vibration and voice feedback. All calls are best-effort and never throw.

let ctx = null;

/** Must be called from a user gesture at least once (iOS Safari requirement). */
export function unlockAudio() {
    try {
        const AC = window.AudioContext || window.webkitAudioContext;
        if (!AC) return;
        ctx = ctx || new AC();
        if (ctx.state === 'suspended') ctx.resume();
        // A silent blip fully unlocks audio on older iOS versions.
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        gain.gain.value = 0;
        osc.connect(gain).connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.01);
    } catch {
        // ignore
    }
}

function tone(freq, start, length, volume = 0.28, type = 'sine') {
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.type = type;
    osc.frequency.value = freq;
    gain.gain.setValueAtTime(0.0001, start);
    gain.gain.exponentialRampToValueAtTime(volume, start + 0.015);
    gain.gain.exponentialRampToValueAtTime(0.0001, start + length);
    osc.connect(gain).connect(ctx.destination);
    osc.start(start);
    osc.stop(start + length + 0.02);
}

function play(notes, force = false) {
    if (!force && !getSettings().sound) return;
    try {
        if (!ctx) unlockAudio();
        if (!ctx) return;
        if (ctx.state === 'suspended') ctx.resume();
        const t = ctx.currentTime + 0.01;
        notes.forEach(([freq, offset, length, volume, type]) => tone(freq, t + offset, length, volume, type));
    } catch {
        // ignore
    }
}

export const sound = {
    tick: () => play([[740, 0, 0.09, 0.22]]),
    go: () => play([[988, 0, 0.32, 0.3, 'triangle']]),
    switchSides: () => play([[880, 0, 0.1], [880, 0.16, 0.1]]),
    end: () => play([[660, 0, 0.14], [988, 0.16, 0.26, 0.3, 'triangle']]),
    done: () => play([[523, 0, 0.14], [659, 0.15, 0.14], [784, 0.3, 0.14], [1047, 0.45, 0.4, 0.3, 'triangle']]),
    test: () => play([[660, 0, 0.14], [988, 0.16, 0.26, 0.3, 'triangle']], true),
};

export function vibrate(pattern, force = false) {
    if (!force && !getSettings().vibration) return;
    try {
        navigator.vibrate?.(pattern);
    } catch {
        // ignore
    }
}

export function speak(text) {
    if (!getSettings().voice || !('speechSynthesis' in window)) return;
    try {
        window.speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(text);
        u.lang = 'en-US';
        u.rate = 1.02;
        window.speechSynthesis.speak(u);
    } catch {
        // ignore
    }
}

export function countdownBeep() {
    if (getSettings().countdownBeeps) sound.tick();
}
