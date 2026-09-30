<x-layout title="Training Timer" page="timer">
    <header class="page-head">
        <span class="eyebrow">Training Timer</span>
        <h1>Timer for your training</h1>
        <p class="muted">Rest between sets, intervals, stopwatch or a simple countdown. Keeps the screen awake while running.</p>
    </header>

    <div class="segmented timer-tabs" role="tablist" aria-label="Timer mode" data-timer-tabs>
        <button type="button" role="tab" data-mode="rest" aria-pressed="true">Rest</button>
        <button type="button" role="tab" data-mode="interval" aria-pressed="false">Interval</button>
        <button type="button" role="tab" data-mode="stopwatch" aria-pressed="false">Stopwatch</button>
        <button type="button" role="tab" data-mode="countdown" aria-pressed="false">Countdown</button>
    </div>

    <div class="timer-face" data-timer-face data-phase="work">
        <div class="timer-phase" data-timer-phase>Rest timer</div>
        <div class="timer-clock" data-timer-clock role="timer" aria-live="off">1:30</div>
        <div class="timer-sub" data-timer-sub></div>
        <div class="timer-bar" aria-hidden="true"><span data-timer-bar></span></div>
    </div>

    <div class="timer-controls">
        <button type="button" class="btn" data-timer-reset><x-icon name="restart" /> Reset</button>
        <button type="button" class="btn btn-primary btn-lg" data-timer-toggle><x-icon name="play" fill data-timer-toggle-icon /> <span data-timer-toggle-label>Start</span></button>
        <button type="button" class="btn" data-timer-extra><x-icon name="plus" /> <span data-timer-extra-label>15s</span></button>
    </div>

    {{-- REST --}}
    <section class="section stack" data-panel="rest">
        <div class="field">
            <span class="field-label">Rest duration</span>
            <div class="preset-grid" data-rest-presets>
                @foreach ([30, 45, 60, 90, 120, 180] as $s)
                    <button type="button" data-seconds="{{ $s }}" aria-pressed="{{ $s === 90 ? 'true' : 'false' }}">{{ intdiv($s, 60) }}:{{ str_pad($s % 60, 2, '0', STR_PAD_LEFT) }}</button>
                @endforeach
            </div>
        </div>
        <div class="card">
            <div class="row" style="justify-content:space-between">
                <div>
                    <strong>Set counter</strong>
                    <p class="small muted" style="margin:0">Counts up automatically each time a rest finishes.</p>
                </div>
                <div class="set-counter">
                    <button type="button" class="icon-btn" data-set-minus aria-label="Previous set"><x-icon name="minus" /></button>
                    <output data-set-count>1</output>
                    <button type="button" class="icon-btn" data-set-plus aria-label="Next set"><x-icon name="plus" /></button>
                </div>
            </div>
        </div>
    </section>

    {{-- INTERVAL --}}
    <section class="section stack" data-panel="interval" hidden>
        <div class="field">
            <span class="field-label">Presets</span>
            <div class="chips" data-interval-presets>
                <button type="button" class="chip" data-work="20" data-rest="10" data-rounds="8">20/10 × 8</button>
                <button type="button" class="chip" data-work="30" data-rest="30" data-rounds="10">30/30 × 10</button>
                <button type="button" class="chip" data-work="40" data-rest="20" data-rounds="8">40/20 × 8</button>
                <button type="button" class="chip" data-work="45" data-rest="15" data-rounds="6">45/15 × 6</button>
                <button type="button" class="chip" data-work="60" data-rest="0" data-rounds="10">EMOM 60 × 10</button>
            </div>
        </div>
        <div class="grid grid-md-3">
            @foreach (['work' => ['Work (sec)', 5, 600, 5], 'rest' => ['Rest (sec)', 0, 600, 5], 'rounds' => ['Rounds', 1, 50, 1]] as $key => [$label, $min, $max, $step])
                <div class="field card">
                    <label for="interval-{{ $key }}" class="field-label">{{ $label }}</label>
                    <div class="stepper" data-stepper="{{ $key }}" data-min="{{ $min }}" data-max="{{ $max }}" data-step="{{ $step }}">
                        <button type="button" data-dec aria-label="Decrease {{ $label }}">−</button>
                        <input id="interval-{{ $key }}" class="input" type="number" inputmode="numeric" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}">
                        <button type="button" data-inc aria-label="Increase {{ $label }}">+</button>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="small muted">A 10-second “get ready” phase runs before the first round.</p>
    </section>

    {{-- STOPWATCH --}}
    <section class="section stack" data-panel="stopwatch" hidden>
        <div class="card">
            <div class="section-head"><h2>Laps</h2><span class="muted small" data-lap-count></span></div>
            <ol class="laps" data-laps></ol>
            <p class="small muted" data-laps-empty>Tap <strong>Lap</strong> while running to record splits.</p>
        </div>
    </section>

    {{-- COUNTDOWN --}}
    <section class="section stack" data-panel="countdown" hidden>
        <div class="grid grid-2">
            <div class="field card">
                <label for="countdown-min" class="field-label">Minutes</label>
                <div class="stepper" data-stepper="cmin" data-min="0" data-max="120" data-step="1">
                    <button type="button" data-dec aria-label="Decrease minutes">−</button>
                    <input id="countdown-min" class="input" type="number" inputmode="numeric" min="0" max="120">
                    <button type="button" data-inc aria-label="Increase minutes">+</button>
                </div>
            </div>
            <div class="field card">
                <label for="countdown-sec" class="field-label">Seconds</label>
                <div class="stepper" data-stepper="csec" data-min="0" data-max="59" data-step="5">
                    <button type="button" data-dec aria-label="Decrease seconds">−</button>
                    <input id="countdown-sec" class="input" type="number" inputmode="numeric" min="0" max="59" step="5">
                    <button type="button" data-inc aria-label="Increase seconds">+</button>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="callout callout-info">
            <x-icon name="volume" />
            <div>
                <p class="small" style="margin:0">Sound, voice cues and vibration follow your <a href="{{ route('settings') }}">settings</a>. Vibration isn’t supported on iPhone browsers; keep the volume up for beeps. Timers stay accurate if you switch apps, but phones may delay sounds while the screen is locked.</p>
            </div>
        </div>
    </section>
</x-layout>
