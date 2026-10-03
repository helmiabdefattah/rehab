import { DATA } from '../data/warmup-data.js';

/*
 * Client-side warm-up routine engine — a JavaScript port of the PHP
 * App\Services\Warmup\RoutineBuilder, minus the (removed) readiness logic.
 * Because the exercise data and templates are baked into the bundle
 * (warmup-data.js), the builder can generate any routine fully offline.
 */

const STEP = 5;
const STAGES = ['heat', 'mobility', 'activation', 'dynamic'];
const RANK = { light: 1, moderate: 2, high: 3 };

const ex = (slug) => DATA.exercises[slug] || null;
const defaultSeconds = (e) => (e.per_side ? e.seconds * 2 : e.seconds);

function minSeconds(e) {
    if (e.elastic) return 45;
    const side = Math.max(15, Math.round((e.seconds * 0.6) / STEP) * STEP);
    return e.per_side ? side * 2 : Math.max(20, side);
}

function maxSeconds(e) {
    if (e.elastic) return 480;
    return Math.round((defaultSeconds(e) * 1.5) / 10) * 10;
}

function roundSeconds(e, seconds) {
    const step = e.per_side ? STEP * 2 : STEP;
    const value = Math.round(seconds / step) * step;
    return Math.max(minSeconds(e), Math.min(maxSeconds(e), value));
}

function suitable(e, ctx) {
    if (e.min_level > ctx.level) return 'level';
    if (RANK[e.min_intensity] > RANK[ctx.intensity]) return 'level';
    // Equipment required must be available today.
    if ((e.equipment || []).some((k) => !ctx.equipment.includes(k))) return 'equipment';
    return null;
}

const equipmentList = (keys) => keys.map((k) => (DATA.config.equipment[k] || k).toLowerCase()).join(' and ');

function pick(candidates, used, ctx, adjustments) {
    for (let index = 0; index < candidates.length; index++) {
        const slug = candidates[index];
        const e = ex(slug);
        if (!e || used[slug]) continue;
        const reason = suitable(e, ctx);
        if (reason === null) return e;
        if (index === 0 && reason === 'equipment') {
            adjustments.push(`${e.name} skipped — needs ${equipmentList(e.equipment)}.`);
        }
    }
    return null;
}

function budgets(template, minutes, intensity) {
    const table = template.budgets || DATA.config.stage_budgets;
    const keys = Object.keys(table).map(Number);
    const key = keys.sort((a, b) => Math.abs(a - minutes) - Math.abs(b - minutes))[0];
    let b = { ...table[key] };

    if (key !== minutes) {
        const factor = minutes / key;
        b = Object.fromEntries(Object.entries(b).map(([k, v]) => [k, Math.round(v * factor)]));
    }

    const shift = DATA.config.budget_shift.intensity[intensity] || 0; // readiness always 0
    if (shift > 0) {
        const moved = Math.round(b.dynamic * Math.min(shift, 0.8));
        b.dynamic -= moved;
        b.mobility += Math.floor(moved / 2);
        b.activation += moved - Math.floor(moved / 2);
    } else if (shift < 0) {
        const moved = Math.round(b.mobility * Math.min(Math.abs(shift), 0.4));
        b.mobility -= moved;
        b.dynamic += moved;
    }
    return b;
}

function scale(items, target) {
    for (let pass = 0; pass < 4 && items.length; pass++) {
        const total = items.reduce((s, it) => s + it.seconds, 0);
        if (Math.abs(target - total) < STEP) break;

        const diff0 = target - total;
        const groups = diff0 > 0 ? [(e) => e.elastic, () => true] : [(e) => !e.elastic, () => true];

        for (const filter of groups) {
            const diff = target - items.reduce((s, it) => s + it.seconds, 0);
            if (Math.abs(diff) < STEP) break;

            const keys = items.map((it, i) => (filter(it.exercise) ? i : -1)).filter((i) => i >= 0);
            let room = 0;
            for (const k of keys) {
                room += diff > 0
                    ? maxSeconds(items[k].exercise) - items[k].seconds
                    : items[k].seconds - minSeconds(items[k].exercise);
            }
            if (room <= 0) continue;

            const ratio = Math.min(1, Math.abs(diff) / room);
            for (const k of keys) {
                const e = items[k].exercise;
                const delta = diff > 0
                    ? (maxSeconds(e) - items[k].seconds) * ratio
                    : -(items[k].seconds - minSeconds(e)) * ratio;
                items[k].seconds = roundSeconds(e, items[k].seconds + delta);
            }
        }
    }

    // Still too long even at minimum durations: drop least essential items.
    while (items.length > 1 && items.reduce((s, it) => s + it.seconds, 0) > target + 30) {
        let drop = -1;
        let worst = null;
        items.forEach((it, i) => {
            const rankKey = [it.p, it.slot];
            if (!worst || rankKey[0] > worst[0] || (rankKey[0] === worst[0] && rankKey[1] > worst[1])) {
                worst = rankKey;
                drop = i;
            }
        });
        if (drop < 0 || items[drop].p === 1) break;
        items.splice(drop, 1);
    }
    return items;
}

function fitStage(chosen, budget, transition, used) {
    let admitted = [];
    let spent = 0;

    for (const entry of chosen) {
        const e = entry.exercise;
        const seconds = e.elastic ? minSeconds(e) : defaultSeconds(e);
        const cost = seconds + transition;
        if (entry.p === 1 || spent + cost <= budget + 10) {
            admitted.push({ ...entry, seconds });
            spent += cost;
        } else {
            delete used[e.slug];
        }
    }

    admitted = scale(admitted, budget - admitted.length * transition);
    admitted.sort((a, b) => a.slot - b.slot);
    return admitted;
}

function total(items, transition) {
    return items.reduce((s, it) => s + it.seconds, 0) + items.length * transition;
}

function slack(items) {
    return items.reduce((s, it) => s + (it.seconds - minSeconds(it.exercise)), 0);
}

function balance(stages, target, transition) {
    let flat = [];
    for (const stage of STAGES) {
        for (const it of stages[stage] || []) flat.push({ ...it, stage });
    }
    if (!flat.length) return [];

    flat = scale(flat, target - flat.length * transition);

    while (total(flat, transition) - target > slack(flat) && flat.length > 4) {
        const perStage = {};
        flat.forEach((it) => (perStage[it.stage] = (perStage[it.stage] || 0) + 1));

        let drop = -1;
        let worst = null;
        flat.forEach((it, i) => {
            if (perStage[it.stage] <= 1 || it.keep) return;
            const key = [it.p, perStage[it.stage], it.slot];
            if (!worst || key[0] > worst[0] || (key[0] === worst[0] && key[1] > worst[1]) ||
                (key[0] === worst[0] && key[1] === worst[1] && key[2] > worst[2])) {
                worst = key;
                drop = i;
            }
        });
        if (drop < 0) break;
        flat.splice(drop, 1);
        flat = scale(flat, target - flat.length * transition);
    }

    // Absorb the remaining rounding difference, elastic items first.
    const order = flat
        .map((it, i) => [i, (it.exercise.elastic ? 100000 : 0) + (it.exercise.per_side ? 0 : 10000) + it.seconds])
        .sort((a, b) => b[1] - a[1])
        .map((x) => x[0]);

    for (const k of order) {
        const diff = target - total(flat, transition);
        if (diff === 0) break;
        const e = flat[k].exercise;
        const step = e.per_side ? 10 : STEP;
        const wanted = flat[k].seconds + Math.trunc(diff / step) * step;
        flat[k].seconds = Math.max(minSeconds(e), Math.min(maxSeconds(e), wanted));
    }

    return flat;
}

function present(req, level, intensity, transition, items, adjustments) {
    const flat = [];
    const stages = [];

    for (const meta of DATA.stages) {
        const stageItems = items.filter((it) => it.stage === meta.key);
        if (!stageItems.length) continue;

        const presented = stageItems.map((it) => {
            const e = it.exercise;
            return {
                slug: e.slug,
                name: e.name,
                name_ar: e.name_ar,
                stage: meta.key,
                stage_label: meta.label,
                stage_number: meta.number,
                seconds: it.seconds,
                per_side: e.per_side,
                side_seconds: e.per_side ? Math.floor(it.seconds / 2) : null,
                cue: (e.cues && e.cues[intensity]) || null,
                reps_label: e.reps_label,
                exercise: e,
            };
        });

        stages.push({
            key: meta.key,
            number: meta.number,
            label: meta.label,
            description: meta.description,
            seconds: presented.reduce((s, p) => s + p.seconds, 0) + presented.length * transition,
            items: presented,
        });
        flat.push(...presented);
    }

    const split = DATA.splits[req.activity] || { short: req.activity, emoji: '' };
    const name = req.title || split.short;
    const totalSeconds = flat.reduce((s, p) => s + p.seconds, 0) + flat.length * transition;

    return {
        title: [name, `${req.minutes} min`, intensity.charAt(0).toUpperCase() + intensity.slice(1)].join(' | '),
        name,
        program: req.program,
        activity: req.activity,
        emoji: split.emoji,
        minutes: req.minutes,
        intensity,
        requested_intensity: req.intensity,
        level,
        level_label: DATA.levels[level],
        requested_level: req.level,
        equipment: req.equipment,
        source: req.source || 'builder',
        transition_seconds: transition,
        total_seconds: totalSeconds,
        adjustments: [...new Set(adjustments)],
        stages,
        items: flat,
        generated_at: new Date().toISOString(),
    };
}

/**
 * Build a warm-up routine entirely in the browser.
 * @param {{program:string, activity:string, minutes:number, intensity:string,
 *          level:number, equipment:string[], transition?:number,
 *          source?:string, title?:string}} req
 */
export function buildRoutine(req) {
    const template = DATA.config.templates[req.program];
    if (!template) throw new Error(`Unknown warm-up program [${req.program}].`);

    const level = req.level;
    const intensity = req.intensity;
    const transition = Math.max(0, req.transition ?? DATA.config.transition_seconds);
    const ctx = { level, intensity, equipment: req.equipment || [] };
    const adjustments = [];

    const b = budgets(template, req.minutes, intensity);
    const used = {};
    const stages = {};

    for (const stage of STAGES) {
        const slots = (template[stage] || []).map((slot, i) => ({ ...slot, i }));
        slots.sort((a, bb) => a.p - bb.p || a.i - bb.i);

        const chosen = [];
        for (const slot of slots) {
            const candidates = (slot.levels && slot.levels[level]) || slot.pick;
            const e = pick(candidates, used, ctx, adjustments);
            if (!e) continue;
            chosen.push({ slot: slot.i, p: slot.p, keep: slot.keep || false, exercise: e });
            used[e.slug] = true;
        }
        stages[stage] = fitStage(chosen, b[stage], transition, used);
    }

    const items = balance(stages, req.minutes * 60, transition);
    return present(req, level, intensity, transition, items, adjustments);
}

/** Resolve a ⚡ quick mode (5/10/15) to its full-body routine request. */
export function quickRoutine(minutes, prefs = {}) {
    const q = DATA.config.quick[minutes];
    if (!q) return null;
    return buildRoutine({
        program: q.program,
        activity: q.activity,
        minutes,
        intensity: q.intensity,
        level: prefs.level ?? 1,
        equipment: prefs.equipment ?? DATA.config.default_equipment,
        transition: prefs.transition ?? DATA.config.transition_seconds,
        source: 'quick',
        title: q.title,
    });
}
