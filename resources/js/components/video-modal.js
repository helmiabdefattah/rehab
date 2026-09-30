import { $, cloneTemplate } from '../lib/dom.js';

// Opens a video modal for any element with data-video='{json}' (exercise video payload).

let current = null;

export function openVideo(video, { onClose } = {}) {
    closeVideo();
    const modal = cloneTemplate('video-modal-template');
    if (!modal || !video) return;

    const lastFocus = document.activeElement;
    $('[data-slot="title"]', modal).textContent = video.exercise || 'Exercise video';

    const frame = $('[data-slot="frame"]', modal);
    const open = $('[data-slot="open"]', modal);
    const search = $('[data-slot="search"]', modal);
    const meta = $('[data-slot="meta"]', modal);
    const note = $('[data-slot="note"]', modal);

    search.href = video.search;

    if (video.embed) {
        const iframe = document.createElement('iframe');
        iframe.src = `${video.embed}&autoplay=1`;
        iframe.title = video.title || video.exercise;
        iframe.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen';
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';
        iframe.allowFullscreen = true;
        frame.appendChild(iframe);
        open.href = video.url;
        meta.textContent = [`“${video.title}”`, video.channel].filter(Boolean).join(' · ') +
            (video.verification === 'single' ? ' · link seen in one search only — please review' : '');
        search.hidden = true;
    } else {
        frame.remove();
        open.remove();
        meta.remove();
        $('[data-slot="empty"]', modal).hidden = false;
        search.classList.remove('btn-ghost');
        search.classList.add('btn-primary');
    }

    if (video.note) {
        note.textContent = video.note;
        note.hidden = false;
    }

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

export function closeVideo() {
    current?.close();
}

export function initVideoButtons() {
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-video]');
        if (!trigger || trigger.closest('.workout')) return;
        e.preventDefault();
        try {
            openVideo(JSON.parse(trigger.dataset.video));
        } catch {
            // malformed payload — ignore
        }
    });
}
