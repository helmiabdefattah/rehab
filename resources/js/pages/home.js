import { $ } from '../lib/dom.js';
import { getSettings, levelLabel } from '../lib/settings.js';
import { listSessions, computeStats, FEELINGS, ACTIVITIES } from '../lib/sessions.js';
import { shortDate } from '../lib/format.js';

export function initHome() {
    const settings = getSettings();
    const badge = $('[data-level-badge]');
    if (badge) badge.textContent = levelLabel(settings.level);

    const hour = new Date().getHours();
    const greeting = $('[data-greeting]');
    if (greeting) greeting.textContent = hour < 12 ? 'Good morning' : hour < 18 ? 'Good afternoon' : 'Good evening';

    const sessions = listSessions();
    if (!sessions.length) return;

    const last = sessions[0];
    const stats = computeStats(sessions);
    const lastBadge = $('[data-last-session]');
    if (lastBadge) {
        lastBadge.hidden = false;
        lastBadge.textContent = `Last: ${ACTIVITIES[last.activity]?.emoji ?? ''} ${shortDate(last.date)} ${FEELINGS[last.feel]?.emoji ?? ''}`;
    }

    const headline = $('[data-progress-headline]');
    const sub = $('[data-progress-sub]');
    if (headline) headline.textContent = `${stats.completed} warm-up${stats.completed === 1 ? '' : 's'} completed`;
    if (sub) {
        sub.textContent = `${stats.thisWeek} this week · ${stats.total} total sessions` +
            (stats.avgReadiness ? ` · average readiness ${stats.avgReadiness.toFixed(1)}/10` : '');
    }
}
