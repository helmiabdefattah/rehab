// Safe localStorage wrapper: storage can be unavailable (private mode, blocked site data).
const PREFIX = 'readyup.';

export function read(key, fallback = null) {
    try {
        const raw = localStorage.getItem(PREFIX + key);
        return raw === null ? fallback : JSON.parse(raw);
    } catch {
        return fallback;
    }
}

export function write(key, value) {
    try {
        localStorage.setItem(PREFIX + key, JSON.stringify(value));
        return true;
    } catch {
        return false;
    }
}

export function remove(key) {
    try {
        localStorage.removeItem(PREFIX + key);
    } catch {
        // ignore
    }
}

export function storageAvailable() {
    try {
        const k = PREFIX + '__test';
        localStorage.setItem(k, '1');
        localStorage.removeItem(k);
        return true;
    } catch {
        return false;
    }
}
