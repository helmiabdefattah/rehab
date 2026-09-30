import { $, esc } from '../lib/dom.js';
import { getSettings, saveSettings, LEVELS } from '../lib/settings.js';
import { listSessions, computeStats, levelCheck, deleteSession, FEELINGS, PAIN_AREAS, ACTIVITIES } from '../lib/sessions.js';
import { shortDate, timeOfDay, duration } from '../lib/format.js';
import { toast } from '../components/toast.js';

export function initProgress() {
    render();
}

function render() {
    const sessions = listSessions();
    const empty = $('[data-progress-empty]');
    const content = $('[data-progress-content]');

    empty.hidden = sessions.length > 0;
    content.hidden = sessions.length === 0;
    if (!sessions.length) return;

    const stats = computeStats(sessions);
    const level = getSettings().level;

    setStat('completed', stats.completed);
    setStat('total', stats.total);
    setStat('readiness', stats.avgReadiness ? `${stats.avgReadiness.toFixed(1)}<small>/10</small>` : '–', true);
    setStat('exercises', stats.exercises);
    setStat('week', stats.thisWeek);
    setStat('level', `${level} <small>${esc(LEVELS[level].label)}</small>`, true);

    renderWeekly(stats.weeks);
    renderBars($('[data-activity-chart]'), Object.entries(stats.byActivity).map(([k, v]) => [`${ACTIVITIES[k].emoji} ${ACTIVITIES[k].label}`, v]));
    renderBars($('[data-feel-chart]'), Object.entries(stats.byFeel).map(([k, v]) => [`${FEELINGS[k].emoji} ${FEELINGS[k].label}`, v]));

    const painEntries = Object.entries(stats.byPain).filter(([, v]) => v > 0);
    const painChart = $('[data-pain-chart]');
    if (painEntries.length) {
        renderBars(painChart, painEntries.map(([k, v]) => [PAIN_AREAS[k], v]));
    } else {
        painChart.innerHTML = '<p class="small muted">No pain reported so far. 👍</p>';
    }

    const advice = $('[data-pain-advice]');
    advice.hidden = stats.painPattern.length === 0;
    if (stats.painPattern.length) {
        const parts = stats.painPattern.map((p) => `${p.label.toLowerCase()} (${p.count} of your last ${p.of} sessions)`);
        $('[data-pain-advice-text]').textContent =
            `You’ve recorded discomfort around the ${parts.join(' and ')}. Consider reducing intensity or stepping back a level, and if symptoms persist or worsen, seek a professional assessment. This is a record of what you reported, not a diagnosis.`;
    }

    renderLevel(sessions, level);
    renderHistory(sessions);
}

function setStat(key, value, html = false) {
    const el = document.querySelector(`[data-stat="${key}"]`);
    if (!el) return;
    html ? (el.innerHTML = value) : (el.textContent = value);
}

function renderWeekly(weeks) {
    const max = Math.max(1, ...weeks.map((w) => w.count));
    $('[data-weekly]').innerHTML = weeks
        .map((w) => {
            const label = `Week of ${w.start.toLocaleDateString(undefined, { day: 'numeric', month: 'short' })}: ${w.count} session${w.count === 1 ? '' : 's'}`;
            return `<div class="col" tabindex="0" aria-label="${esc(label)}"><span style="height:${(w.count / max) * 100}%"></span><span class="tip">${esc(label)}</span></div>`;
        })
        .join('');
    $('[data-weekly-labels]').innerHTML = weeks
        .map((w, i) => `<span>${i === weeks.length - 1 ? 'This wk' : w.start.toLocaleDateString(undefined, { day: 'numeric', month: 'numeric' })}</span>`)
        .join('');
}

function renderBars(el, entries) {
    const max = Math.max(1, ...entries.map(([, v]) => v));
    el.innerHTML = entries
        .map(
            ([label, v]) => `
        <div class="hbar" title="${esc(label)}: ${v}">
            <span>${esc(label)}</span>
            <span class="track"><span class="fill" style="width:${(v / max) * 100}%"></span></span>
            <span class="val">${v}</span>
        </div>`,
        )
        .join('');
}

function renderLevel(sessions, level) {
    const check = levelCheck(sessions, level);
    $('[data-level-current]').textContent = `Level ${level} · ${LEVELS[level].label}`;
    $('[data-level-criteria]').innerHTML =
        level >= 3
            ? '<li class="is-met"><span class="mark">✓</span><span>You are at the highest level. Keep prioritising quality and symptom-free movement.</span></li>'
            : check.criteria
                  .map((c) => `<li class="${c.met ? 'is-met' : ''}"><span class="mark">${c.met ? '✓' : '·'}</span><span>${esc(c.text)}</span></li>`)
                  .join('') +
              '<li><span class="mark">?</span><span>Movements at this level feel controlled, and your physiotherapist hasn’t advised otherwise</span></li>';

    const up = $('[data-level-up]');
    const down = $('[data-level-down]');

    up.hidden = level >= 3;
    up.textContent = check.ready ? `Move to Level ${level + 1}` : `Level ${level + 1}: not yet suggested`;
    up.disabled = !check.ready;
    up.onclick = () => {
        if (!window.confirm(`Move to Level ${level + 1} (${LEVELS[level + 1].label})? You can go back at any time.`)) return;
        saveSettings({ level: level + 1 });
        toast(`Level ${level + 1} selected`);
        render();
    };

    down.hidden = level <= 1;
    down.textContent = check.stepBack ? `Suggested: step back to Level ${level - 1}` : `Go back to Level ${level - 1}`;
    down.classList.toggle('btn-danger', check.stepBack);
    down.onclick = () => {
        saveSettings({ level: level - 1 });
        toast(`Level ${level - 1} selected`);
        render();
    };
}

function renderHistory(sessions) {
    $('[data-history-count]').textContent = `${sessions.length} session${sessions.length === 1 ? '' : 's'}`;
    const list = $('[data-history]');
    list.innerHTML = sessions
        .slice(0, 100)
        .map((s) => {
            const pain = s.pain
                ? `<span class="badge badge-warn">Pain: ${esc((s.pain_areas || []).map((a) => PAIN_AREAS[a] || a).join(', '))}</span>`
                : '<span class="badge badge-ok">No pain</span>';
            const status = s.completed ? '' : '<span class="badge">Ended early</span>';
            return `
            <li>
                <span class="emoji" aria-hidden="true">${ACTIVITIES[s.activity]?.emoji ?? '•'}</span>
                <div>
                    <strong>${esc(s.title || s.name)}</strong>
                    <span class="small muted">${esc(shortDate(s.date))} ${esc(timeOfDay(s.date))} · ${s.exercises_completed}/${s.exercises_total} exercises · ${esc(duration(s.duration_sec || 0))}</span>
                    <div class="row" style="gap:6px;margin-top:4px">
                        <span class="badge">${FEELINGS[s.feel]?.emoji ?? ''} ${FEELINGS[s.feel]?.label ?? '–'}</span>
                        ${pain} ${status}
                        ${s.readiness?.checked ? `<span class="badge badge-info">Readiness ${s.readiness.score}/10</span>` : ''}
                    </div>
                    ${s.notes ? `<p class="small muted" style="margin:6px 0 0">“${esc(s.notes)}”</p>` : ''}
                </div>
                <button type="button" class="icon-btn" data-delete="${esc(s.id)}" aria-label="Delete session"><svg class="icon" aria-hidden="true"><use href="#i-trash"></use></svg></button>
            </li>`;
        })
        .join('');

    list.onclick = (e) => {
        const btn = e.target.closest('[data-delete]');
        if (!btn || !window.confirm('Delete this session?')) return;
        deleteSession(btn.dataset.delete);
        toast('Session deleted');
        render();
    };
}
