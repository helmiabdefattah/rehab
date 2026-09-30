import { getSettings } from './settings.js';

// Keeps the screen on while a workout or timer runs (Screen Wake Lock API, where supported).
let sentinel = null;
let wanted = false;

export async function keepAwake() {
    wanted = true;
    if (!getSettings().keepAwake || sentinel || !('wakeLock' in navigator)) return;
    try {
        sentinel = await navigator.wakeLock.request('screen');
        sentinel.addEventListener('release', () => {
            sentinel = null;
        });
    } catch {
        sentinel = null;
    }
}

export async function releaseAwake() {
    wanted = false;
    try {
        await sentinel?.release();
    } catch {
        // ignore
    }
    sentinel = null;
}

document.addEventListener('visibilitychange', () => {
    if (wanted && document.visibilityState === 'visible') keepAwake();
});
