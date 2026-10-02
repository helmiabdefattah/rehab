import { $$ } from './lib/dom.js';
import { applyTheme, prefsQuery } from './lib/settings.js';
import { unlockAudio } from './lib/cues.js';
import { initAnimations, initAnimationButtons } from './components/exercise-animation.js';
import { initHome } from './pages/home.js';
import { initBuilder } from './pages/builder.js';
import { initLibrary } from './pages/library.js';
import { initProgress } from './pages/progress.js';
import { initTimer } from './pages/timer.js';
import { initSettings } from './pages/settings.js';

const pages = {
    home: initHome,
    builder: initBuilder,
    library: initLibrary,
    progress: initProgress,
    timer: initTimer,
    settings: initSettings,
};

function boot() {
    applyTheme();
    initAnimations();
    initAnimationButtons();

    // Quick warm-ups are built on the server: pass the saved level, equipment and transition time.
    $$('[data-quick-link]').forEach((a) => {
        const url = new URL(a.href, location.origin);
        url.search = prefsQuery();
        a.href = url.toString();
    });

    // Browsers only allow audio after a user gesture (iOS in particular).
    document.addEventListener('pointerdown', unlockAudio, { once: true, capture: true });

    window.matchMedia?.('(prefers-color-scheme: light)').addEventListener?.('change', () => applyTheme());

    pages[document.body.dataset.page]?.();

    if ('serviceWorker' in navigator && location.protocol === 'https:') {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
