import { $, $$, pressOne } from '../lib/dom.js';
import { getSettings, saveSettings, applyTheme, DEFAULTS } from '../lib/settings.js';
import { listSessions, replaceSessions, clearSessions } from '../lib/sessions.js';
import { sound, vibrate, unlockAudio, speak } from '../lib/cues.js';
import { remove, storageAvailable } from '../lib/store.js';
import { toast } from '../components/toast.js';

export function initSettings() {
    const form = $('[data-settings-form]');
    if (!form) return;

    if (!storageAvailable()) {
        toast('Browser storage is unavailable — settings and history cannot be saved in this mode.', 6000);
    }

    const sync = () => {
        const s = getSettings();
        $$('input[name="level"]', form).forEach((r) => {
            r.checked = Number(r.value) === s.level;
            r.closest('label').style.borderColor = r.checked ? 'var(--accent)' : '';
        });
        $$('[data-setting-equipment] button').forEach((b) => b.setAttribute('aria-pressed', String(s.equipment.includes(b.dataset.value))));
        $$('[data-setting]', form).forEach((input) => {
            const key = input.dataset.setting;
            if (input.type === 'checkbox') input.checked = !!s[key];
            else input.value = String(s[key]);
        });
        pressOne($('[data-setting-theme]'), s.theme);
    };

    $$('input[name="level"]', form).forEach((r) =>
        r.addEventListener('change', () => {
            saveSettings({ level: Number(r.value) });
            sync();
            toast(`Level ${r.value} selected`);
        }),
    );

    $('[data-setting-equipment]').addEventListener('click', (e) => {
        const b = e.target.closest('button[data-value]');
        if (!b) return;
        const set = new Set(getSettings().equipment);
        set.has(b.dataset.value) ? set.delete(b.dataset.value) : set.add(b.dataset.value);
        saveSettings({ equipment: [...set] });
        sync();
    });

    $$('[data-setting]', form).forEach((input) =>
        input.addEventListener('change', () => {
            const key = input.dataset.setting;
            saveSettings({ [key]: input.type === 'checkbox' ? input.checked : Number(input.value) });
            if (key === 'voice' && input.checked) speak('Voice cues are on.');
            toast('Saved');
        }),
    );

    $('[data-setting-theme]').addEventListener('click', (e) => {
        const b = e.target.closest('button[data-value]');
        if (!b) return;
        saveSettings({ theme: b.dataset.value });
        applyTheme(b.dataset.value);
        sync();
    });

    $('[data-test-sound]').addEventListener('click', () => {
        unlockAudio();
        sound.test();
        vibrate([150, 80, 150], true);
        speak('This is a voice cue.');
        if (!('vibrate' in navigator)) toast('Vibration is not supported in this browser.');
    });

    $('[data-export]').addEventListener('click', () => {
        const data = { app: 'readyup', version: 1, exported_at: new Date().toISOString(), settings: getSettings(), sessions: listSessions() };
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `readyup-backup-${new Date().toISOString().slice(0, 10)}.json`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(() => URL.revokeObjectURL(a.href), 2000);
        toast('Backup downloaded');
    });

    $('[data-import]').addEventListener('change', async (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        try {
            const data = JSON.parse(await file.text());
            if (data?.app !== 'readyup' || !Array.isArray(data.sessions)) throw new Error('Not a ReadyUp backup');
            if (!window.confirm(`Import ${data.sessions.length} sessions? This replaces the history on this device.`)) return;
            replaceSessions(data.sessions);
            if (data.settings && typeof data.settings === 'object') saveSettings({ ...DEFAULTS, ...data.settings });
            applyTheme();
            sync();
            toast('Backup imported');
        } catch (err) {
            toast(`Import failed: ${err.message}`);
        } finally {
            e.target.value = '';
        }
    });

    $('[data-clear]').addEventListener('click', () => {
        if (!window.confirm('Delete all sessions and settings on this device? This cannot be undone.')) return;
        clearSessions();
        remove('settings');
        remove('activeWorkout');
        remove('builder');
        remove('timer');
        applyTheme();
        sync();
        toast('All data deleted');
    });

    sync();
}
