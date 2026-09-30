export function clock(totalSeconds) {
    const s = Math.max(0, Math.ceil(totalSeconds));
    const m = Math.floor(s / 60);
    return `${m}:${String(s % 60).padStart(2, '0')}`;
}

export function clockMs(ms) {
    return clock(ms / 1000);
}

/** Stopwatch format with tenths: m:ss.t (or h:mm:ss.t) */
export function stopwatch(ms) {
    const total = Math.max(0, Math.floor(ms / 100));
    const tenths = total % 10;
    const secs = Math.floor(total / 10);
    const h = Math.floor(secs / 3600);
    const m = Math.floor((secs % 3600) / 60);
    const s = secs % 60;
    const core = h > 0 ? `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}` : `${m}:${String(s).padStart(2, '0')}`;
    return `${core}.${tenths}`;
}

export function duration(totalSeconds) {
    const s = Math.round(totalSeconds);
    if (s < 60) return `${s} sec`;
    const m = Math.floor(s / 60);
    const r = s % 60;
    return r ? `${m} min ${r} s` : `${m} min`;
}

export function shortDate(iso) {
    try {
        return new Date(iso).toLocaleDateString(undefined, { weekday: 'short', day: 'numeric', month: 'short' });
    } catch {
        return iso;
    }
}

export function timeOfDay(iso) {
    try {
        return new Date(iso).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
    } catch {
        return '';
    }
}
