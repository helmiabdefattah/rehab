export const $ = (sel, root = document) => root.querySelector(sel);
export const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

export function esc(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

export function icon(name, fill = false) {
    return `<svg class="icon${fill ? ' icon-fill' : ''}" aria-hidden="true" focusable="false"><use href="#i-${name}"></use></svg>`;
}

export function setIcon(svg, name) {
    svg?.querySelector('use')?.setAttribute('href', `#i-${name}`);
}

export function cloneTemplate(id) {
    const tpl = document.getElementById(id);
    return tpl ? tpl.content.firstElementChild.cloneNode(true) : null;
}

/** Toggle aria-pressed within a group so exactly one button is pressed. */
export function pressOne(group, value) {
    $$('button[data-value]', group).forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.value === String(value))));
}

export function uid() {
    if (window.crypto?.randomUUID) return window.crypto.randomUUID();
    return `s-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
}
