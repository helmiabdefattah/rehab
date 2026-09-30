// Timestamp-based timers: accurate even when the tab is throttled in the background.

export class Countdown {
    constructor({ seconds, onTick, onDone }) {
        this.durationMs = seconds * 1000;
        this.onTick = onTick;
        this.onDone = onDone;
        this.carried = 0;
        this.startedAt = 0;
        this.running = false;
        this.timer = null;
    }

    get elapsedMs() {
        return this.carried + (this.running ? performance.now() - this.startedAt : 0);
    }

    get remainingMs() {
        return Math.max(0, this.durationMs - this.elapsedMs);
    }

    get progress() {
        return this.durationMs ? Math.min(1, this.elapsedMs / this.durationMs) : 1;
    }

    start() {
        if (this.running || this.remainingMs <= 0) return;
        this.running = true;
        this.startedAt = performance.now();
        this.timer = setInterval(() => this.tick(), 100);
        this.tick();
    }

    pause() {
        if (!this.running) return;
        this.carried = this.elapsedMs;
        this.running = false;
        clearInterval(this.timer);
        this.onTick?.(this);
    }

    stop() {
        this.running = false;
        clearInterval(this.timer);
    }

    reset(seconds = this.durationMs / 1000) {
        this.stop();
        this.durationMs = seconds * 1000;
        this.carried = 0;
        this.onTick?.(this);
    }

    add(seconds) {
        this.durationMs = Math.max(1000, this.durationMs + seconds * 1000);
        this.onTick?.(this);
    }

    tick() {
        this.onTick?.(this);
        if (this.running && this.remainingMs <= 0) {
            this.stop();
            this.onDone?.(this);
        }
    }
}

export class Stopwatch {
    constructor({ onTick }) {
        this.onTick = onTick;
        this.carried = 0;
        this.startedAt = 0;
        this.running = false;
        this.timer = null;
    }

    get elapsedMs() {
        return this.carried + (this.running ? performance.now() - this.startedAt : 0);
    }

    start() {
        if (this.running) return;
        this.running = true;
        this.startedAt = performance.now();
        this.timer = setInterval(() => this.onTick?.(this), 50);
    }

    pause() {
        if (!this.running) return;
        this.carried = this.elapsedMs;
        this.running = false;
        clearInterval(this.timer);
        this.onTick?.(this);
    }

    reset() {
        this.running = false;
        clearInterval(this.timer);
        this.carried = 0;
        this.onTick?.(this);
    }
}
