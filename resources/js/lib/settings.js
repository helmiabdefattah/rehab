import { read, write } from './store.js';

export const DEFAULTS = Object.freeze({
    level: 1,
    // The equipment that actually gates warm-up exercises — selected by default
    // so nothing is filtered out. Users toggle the rest in Settings.
    equipment: ['mini-band', 'long-band', 'step', 'bike'],
    getReady: 5,
    sound: true,
    countdownBeeps: true,
    voice: false,
    vibration: true,
    keepAwake: true,
    pauseOnVideo: true,
    theme: 'dark',
});

export const LEVELS = {
    1: { label: 'Re-entry', description: 'Low impact and controlled movements.' },
    2: { label: 'Conditioning', description: 'Moderate dynamic movements and greater range of motion.' },
    3: { label: 'Performance Preparation', description: 'More dynamic movements, controlled acceleration, lateral movement and activity-specific preparation.' },
};

export function getSettings() {
    const saved = read('settings', {}) || {};
    const merged = { ...DEFAULTS, ...saved };
    merged.level = [1, 2, 3].includes(Number(merged.level)) ? Number(merged.level) : 1;
    merged.equipment = Array.isArray(merged.equipment) ? merged.equipment : [...DEFAULTS.equipment];
    merged.getReady = Number(merged.getReady);
    return merged;
}

export function saveSettings(patch) {
    const next = { ...getSettings(), ...patch };
    write('settings', next);
    document.dispatchEvent(new CustomEvent('settings:change', { detail: next }));
    return next;
}

export function applyTheme(theme = getSettings().theme) {
    let resolved = theme;
    if (theme === 'system') {
        resolved = window.matchMedia?.('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
    }
    document.documentElement.setAttribute('data-theme', resolved);
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', resolved === 'light' ? '#f2f5f8' : '#0a0e13');
}

/** Query string used by server-built routines (quick & focus warm-ups). */
export function prefsQuery(settings = getSettings()) {
    return new URLSearchParams({
        level: String(settings.level),
        equipment: settings.equipment.join(','),
        transition: String(settings.getReady),
    }).toString();
}

export function levelLabel(level) {
    return `Level ${level} · ${LEVELS[level]?.label ?? ''}`;
}
