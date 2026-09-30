import { read, write, remove } from './store.js';

// Workout session history, kept on this device.

const KEY = 'sessions';
export const FEELINGS = {
    good: { emoji: '😊', label: 'Good' },
    okay: { emoji: '😐', label: 'Okay' },
    uncomfortable: { emoji: '😟', label: 'Uncomfortable' },
};
export const PAIN_AREAS = {
    knee: 'Knee',
    hip: 'Hip',
    glute: 'Glute',
    piriformis: 'Piriformis area',
    ankle: 'Ankle',
    other: 'Other',
};
export const ACTIVITIES = {
    gym: { emoji: '🏋️', label: 'Gym / Strength' },
    running: { emoji: '🏃', label: 'Running' },
    football: { emoji: '⚽', label: 'Football' },
    general: { emoji: '🚶', label: 'General' },
};

function isValid(s) {
    return s && typeof s === 'object' && typeof s.id === 'string' && typeof s.date === 'string';
}

export function listSessions() {
    const all = read(KEY, []);
    return (Array.isArray(all) ? all : []).filter(isValid).sort((a, b) => b.date.localeCompare(a.date));
}

export function addSession(session) {
    const all = listSessions();
    all.unshift(session);
    return write(KEY, all.slice(0, 1000));
}

export function deleteSession(id) {
    write(KEY, listSessions().filter((s) => s.id !== id));
}

export function replaceSessions(sessions) {
    return write(KEY, (Array.isArray(sessions) ? sessions : []).filter(isValid));
}

export function clearSessions() {
    remove(KEY);
}

function startOfWeek(date) {
    const d = new Date(date);
    d.setHours(0, 0, 0, 0);
    const day = (d.getDay() + 6) % 7; // Monday = 0
    d.setDate(d.getDate() - day);
    return d;
}

export function computeStats(sessions) {
    const checked = sessions.filter((s) => s.readiness?.checked && Number.isFinite(s.readiness?.score));
    const weekStart = startOfWeek(new Date());

    const weeks = [];
    for (let i = 7; i >= 0; i--) {
        const start = new Date(weekStart);
        start.setDate(start.getDate() - i * 7);
        const end = new Date(start);
        end.setDate(end.getDate() + 7);
        weeks.push({
            start,
            count: sessions.filter((s) => {
                const d = new Date(s.date);
                return d >= start && d < end;
            }).length,
        });
    }

    const byActivity = Object.fromEntries(Object.keys(ACTIVITIES).map((k) => [k, 0]));
    const byFeel = Object.fromEntries(Object.keys(FEELINGS).map((k) => [k, 0]));
    const byPain = Object.fromEntries(Object.keys(PAIN_AREAS).map((k) => [k, 0]));

    sessions.forEach((s) => {
        if (s.activity in byActivity) byActivity[s.activity]++;
        if (s.feel in byFeel) byFeel[s.feel]++;
        if (s.pain) (s.pain_areas || []).forEach((a) => a in byPain && byPain[a]++);
    });

    return {
        total: sessions.length,
        completed: sessions.filter((s) => s.completed).length,
        avgReadiness: checked.length ? checked.reduce((sum, s) => sum + s.readiness.score, 0) / checked.length : null,
        exercises: sessions.reduce((sum, s) => sum + (s.exercises_completed || 0), 0),
        thisWeek: weeks[weeks.length - 1].count,
        weeks,
        byActivity,
        byFeel,
        byPain,
        painPattern: painPattern(sessions),
    };
}

/** Areas reported in at least 2 of the last 5 sessions (used for a gentle, non-diagnostic nudge). */
export function painPattern(sessions) {
    const recent = sessions.slice(0, 5);
    const counts = {};
    recent.forEach((s) => s.pain && (s.pain_areas || []).forEach((a) => (counts[a] = (counts[a] || 0) + 1)));
    return Object.entries(counts)
        .filter(([, n]) => n >= 2)
        .map(([area, n]) => ({ area, label: PAIN_AREAS[area] || area, count: n, of: recent.length }));
}

/**
 * Progression is never automatic. These criteria emphasise symptom-free movement
 * quality and gradual exposure at the current level — not time alone.
 */
export function levelCheck(sessions, level) {
    const atLevel = sessions.filter((s) => Number(s.level) === Number(level));
    const completedAtLevel = atLevel.filter((s) => s.completed);
    const last5 = atLevel.slice(0, 5);
    const checked = last5.filter((s) => s.readiness?.checked && Number.isFinite(s.readiness?.score));
    const avg = checked.length ? checked.reduce((sum, s) => sum + s.readiness.score, 0) / checked.length : null;

    const criteria = [
        {
            met: completedAtLevel.length >= 6,
            text: `At least 6 completed warm-ups at Level ${level} (${completedAtLevel.length}/6)`,
        },
        {
            met: last5.length >= 5 && last5.every((s) => !s.pain),
            text: 'No pain reported in your last 5 sessions at this level',
        },
        {
            met: last5.length >= 5 && last5.every((s) => s.feel === 'good' || s.feel === 'okay'),
            text: 'Your last 5 sessions felt Good or Okay',
        },
        {
            met: avg !== null && checked.length >= 3 && avg >= 7,
            text: `Average readiness of 7/10 or more in recent checks (${avg === null ? '–' : avg.toFixed(1)})`,
        },
    ];

    const recent3 = atLevel.slice(0, 3);
    const stepBack = recent3.length >= 3 && recent3.filter((s) => s.pain || s.feel === 'uncomfortable').length >= 2;

    return { criteria, ready: level < 3 && criteria.every((c) => c.met), stepBack };
}
