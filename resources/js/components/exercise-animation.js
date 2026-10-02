import { $, cloneTemplate } from '../lib/dom.js';

/*
 * Looping SVG movement animations — the demonstration for every exercise
 * (they replace the old YouTube videos). A single stick-figure rig is driven
 * by SMIL <animateTransform> for standing patterns; floor patterns are small
 * bespoke scenes. Everything is inline SVG, so it works offline and in both
 * light and dark themes.
 */

const SH = '60 48'; // shoulder pivot
const HIP = '60 86'; // hip pivot

// Standing rig: a pattern supplies rotation value-lists (deg) for each part
// and an optional vertical "bob" translate. Values loop as "a;b;a".
const STANDING = {
    generic: { dur: 2.4, bob: [0, -3, 0], armL: [-8, 8, -8], armR: [8, -8, 8] },
    cardio: { dur: 0.9, bob: [0, -4, 0], armL: [-45, 35, -45], armR: [35, -45, 35], legL: [28, -32, 28], legR: [-32, 28, -32] },
    squat: { dur: 1.8, bob: [0, 26, 0], upper: [0, 12, 0], armL: [0, -60, 0], armR: [0, 60, 0] },
    hinge: { dur: 1.8, bob: [0, 6, 0], upper: [0, 62, 0] },
    lunge: { dur: 1.8, bob: [0, 18, 0], upper: [0, 8, 0], legL: [0, 34, 0], legR: [0, -30, 0] },
    calf: { dur: 1.1, bob: [0, -11, 0] },
    'leg-swing': { dur: 1.3, legR: [38, -36, 38], armL: [14, -14, 14] },
    'side-step': { dur: 1.0, bob: [0, -3, 0], legL: [20, 2, 20], legR: [2, 20, 2], armL: [-12, 12, -12], armR: [12, -12, 12] },
    twist: { dur: 1.6, upper: [-14, 14, -14], armL: [-28, 28, -28], armR: [-28, 28, -28] },
    balance: { dur: 2.4, legR: [0, -22, 0], armL: [0, -45, 0], armR: [0, 45, 0] },
    'arm-circle': { dur: 2, spin: true }, // full 360° arm rotation
    press: { dur: 1.5, armL: [0, -155, 0], armR: [0, 155, 0], bob: [0, -3, 0], load: 'press' },
    pull: { dur: 1.5, armL: [-150, -115, -150], armR: [150, 115, 150], bob: [6, -5, 6], load: 'bar' },
};

const ALIAS = { cycle: 'cardio', pushup: 'press' };

/** A rotating limb/segment with SMIL. */
function rot(id, pivot, values, dur) {
    if (!values) return null;
    return `type="rotate" values="${values.map((v) => `${v} ${pivot}`).join(';')}" dur="${dur}s"`;
}

function standing(name) {
    const p = STANDING[name] || STANDING.generic;
    const dur = p.dur;

    const anim = (inner, spec) => (spec ? `<animateTransform attributeName="transform" ${spec} repeatCount="indefinite" additive="sum"/>${inner}` : inner);

    const limb = (id, pivot, values) => {
        const line =
            id === 'legL' ? '<line x1="60" y1="86" x2="50" y2="124"/>'
            : id === 'legR' ? '<line x1="60" y1="86" x2="70" y2="124"/>'
            : id === 'armL' ? '<line x1="60" y1="50" x2="44" y2="80"/><circle cx="44" cy="80" r="2.4" class="an-dot"/>'
            : '<line x1="60" y1="50" x2="76" y2="80"/><circle cx="76" cy="80" r="2.4" class="an-dot"/>';
        const spec = p.spin && (id === 'armL' || id === 'armR')
            ? `type="rotate" values="${id === 'armL' ? '0 ' + SH + ';360 ' + SH : '0 ' + SH + ';-360 ' + SH}" dur="${dur}s" calcMode="linear"`
            : rot(id, pivot, values, dur);
        return `<g>${anim(line, spec)}</g>`;
    };

    const upperInner = `
        <line x1="60" y1="46" x2="60" y2="86" class="an-torso"/>
        <circle cx="60" cy="34" r="10" class="an-head"/>
        ${limb('armL', SH, p.armL)}
        ${limb('armR', SH, p.armR)}`;
    const upper = `<g>${anim(upperInner, rot('upper', HIP, p.upper, dur))}</g>`;

    const load = p.load === 'press'
        ? '<line x1="40" y1="80" x2="80" y2="80" class="an-load"><animateTransform attributeName="transform" type="translate" values="0 32;0 -30;0 32" dur="' + dur + 's" repeatCount="indefinite"/></line>'
        : p.load === 'bar'
        ? '<line x1="44" y1="18" x2="76" y2="18" class="an-load"><animateTransform attributeName="transform" type="translate" values="0 0;0 14;0 0" dur="' + dur + 's" repeatCount="indefinite"/></line>'
        : '';

    const figInner = `
        ${limb('legL', HIP, p.legL)}
        ${limb('legR', HIP, p.legR)}
        ${upper}
        ${load}`;
    const figSpec = p.bob ? `type="translate" values="${p.bob.map((v) => `0 ${v}`).join(';')}" dur="${dur}s"` : null;

    return `<svg viewBox="0 0 120 132" class="an-svg" role="img" aria-hidden="true">
        <line x1="18" y1="126" x2="102" y2="126" class="an-ground"/>
        <g class="an-fig">${anim(figInner, figSpec)}</g>
    </svg>`;
}

/** Floor / mat scenes — bespoke little loops. */
const FLOOR = {
    plank: (d = 2) => `
        <line x1="26" y1="92" x2="96" y2="68" class="an-torso"/>
        <circle cx="100" cy="64" r="8" class="an-head"/>
        <line x1="26" y1="92" x2="26" y2="108" class="an-torso"/>
        <line x1="26" y1="108" x2="40" y2="108" class="an-load"/>
        <line x1="96" y1="68" x2="92" y2="108" class="an-torso"/>
        <animateTransform attributeName="transform" type="translate" values="0 0;0 -3;0 0" dur="${d}s" repeatCount="indefinite"/>`,
    bridge: (d = 2) => `
        <circle cx="26" cy="96" r="8" class="an-head"/>
        <line x1="26" y1="100" x2="64" y2="100" class="an-torso an-hips"><animateTransform attributeName="transform" type="translate" values="0 0;0 -24;0 0" dur="${d}s" repeatCount="indefinite"/></line>
        <line x1="64" y1="100" x2="78" y2="110" class="an-torso an-hips"><animateTransform attributeName="transform" type="translate" values="0 0;0 -24;0 0" dur="${d}s" repeatCount="indefinite"/></line>
        <line x1="78" y1="110" x2="92" y2="108" class="an-torso"/>`,
    deadbug: (d = 1.8) => `
        <circle cx="30" cy="92" r="8" class="an-head"/>
        <line x1="30" y1="96" x2="74" y2="96" class="an-torso"/>
        <g><line x1="46" y1="96" x2="46" y2="70" class="an-torso"/><animateTransform attributeName="transform" type="rotate" values="0 46 96;40 46 96;0 46 96" dur="${d}s" repeatCount="indefinite"/></g>
        <g><line x1="62" y1="96" x2="62" y2="70" class="an-torso"/><animateTransform attributeName="transform" type="rotate" values="40 62 96;0 62 96;40 62 96" dur="${d}s" repeatCount="indefinite"/></g>`,
    crunch: (d = 1.8) => `
        <line x1="78" y1="104" x2="60" y2="88" class="an-torso an-crunch">
            <animateTransform attributeName="transform" type="rotate" values="0 78 104;-26 78 104;0 78 104" dur="${d}s" repeatCount="indefinite"/>
        </line>
        <circle cx="56" cy="84" r="8" class="an-head an-crunch"><animateTransform attributeName="transform" type="rotate" values="0 78 104;-26 78 104;0 78 104" dur="${d}s" repeatCount="indefinite"/></circle>
        <line x1="78" y1="104" x2="92" y2="104" class="an-torso"/>
        <line x1="92" y1="104" x2="86" y2="86" class="an-torso"/>
        <line x1="86" y1="86" x2="72" y2="92" class="an-torso"/>`,
    'floor-side': (d = 1.6) => `
        <circle cx="30" cy="94" r="8" class="an-head"/>
        <line x1="30" y1="98" x2="74" y2="104" class="an-torso"/>
        <line x1="74" y1="104" x2="92" y2="104" class="an-torso"/>
        <g><line x1="74" y1="100" x2="92" y2="92" class="an-torso an-leg"/><animateTransform attributeName="transform" type="rotate" values="0 74 100;-32 74 100;0 74 100" dur="${d}s" repeatCount="indefinite"/></g>`,
};

export function animationSvg(pattern) {
    const name = ALIAS[pattern] || pattern || 'generic';

    if (FLOOR[name]) {
        return `<svg viewBox="0 0 120 132" class="an-svg" role="img" aria-hidden="true">
            <line x1="14" y1="118" x2="106" y2="118" class="an-ground"/>
            <g class="an-fig">${FLOOR[name]()}</g>
        </svg>`;
    }

    return standing(name);
}

/** Inline SVG into every <span data-animation="pattern"> placeholder once. */
export function initAnimations(root = document) {
    root.querySelectorAll('[data-animation]:not([data-animated])').forEach((el) => {
        el.dataset.animated = '1';
        el.innerHTML = animationSvg(el.dataset.animation);
    });
}

let current = null;

export function openAnimation(payload, { onClose } = {}) {
    closeAnimation();
    const modal = cloneTemplate('animation-modal-template');
    if (!modal || !payload) return;

    const lastFocus = document.activeElement;
    $('[data-slot="title"]', modal).textContent = payload.exercise || 'Exercise';
    $('[data-slot="ar"]', modal).textContent = payload.name_ar || '';
    $('[data-slot="reps"]', modal).textContent = payload.reps || '';
    const stage = $('[data-slot="stage"]', modal);
    stage.innerHTML = animationSvg(payload.pattern);
    if (payload.stage) stage.dataset.stage = payload.stage;

    const close = () => {
        modal.remove();
        document.removeEventListener('keydown', onKey, true);
        current = null;
        lastFocus?.focus?.();
        onClose?.();
    };
    const onKey = (e) => {
        if (e.key === 'Escape') {
            e.stopPropagation();
            close();
        }
    };

    modal.addEventListener('click', (e) => {
        if (e.target === modal || e.target.closest('[data-close]')) close();
    });
    document.addEventListener('keydown', onKey, true);
    document.body.appendChild(modal);
    $('[data-close]', modal).focus();
    current = { close };
}

export function closeAnimation() {
    current?.close();
}

/** Clicking any element with data-animation-payload='{json}' opens the modal. */
export function initAnimationButtons() {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-animation-payload]');
        if (!trigger || trigger.closest('.workout')) return;
        e.preventDefault();
        try {
            openAnimation(JSON.parse(trigger.dataset.animationPayload));
        } catch {
            // malformed payload — ignore
        }
    });
}
