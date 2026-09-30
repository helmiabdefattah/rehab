<x-layout title="Settings" page="settings">
    <header class="page-head">
        <span class="eyebrow">Settings</span>
        <h1>Settings</h1>
        <p class="muted">Saved on this device only.</p>
    </header>

    <form class="stack" data-settings-form style="--stack-gap:18px" onsubmit="return false">
        <section class="card">
            <h2>Progression level</h2>
            <p class="small muted">Change your level when movement feels controlled and symptom-free — see the progression check in <a href="{{ route('progress') }}">My Progress</a>.</p>
            <div class="level-list" data-setting-level>
                @foreach (\App\Enums\Level::cases() as $level)
                    <label class="card level-card" style="cursor:pointer;padding:14px">
                        <span class="lvl">{{ $level->value }}</span>
                        <span>
                            <input type="radio" name="level" value="{{ $level->value }}" class="sr-only">
                            <strong>Level {{ $level->value }} · {{ $level->label() }}</strong>
                            <span class="small muted" style="display:block">{{ $level->description() }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="card">
            <h2>Default equipment</h2>
            <p class="small muted">Pre-selected when you build a warm-up and used for quick warm-ups.</p>
            <div class="chips" data-setting-equipment>
                @foreach (config('warmup.equipment') as $key => $label)
                    <button type="button" class="chip" data-value="{{ $key }}" aria-pressed="false">{{ $label }}</button>
                @endforeach
            </div>
        </section>

        <section class="card">
            <h2>Workout Mode &amp; timers</h2>
            <div class="switch-row">
                <div><strong>“Get ready” countdown</strong><span class="small muted">Seconds between exercises</span></div>
                <select class="input" style="width:auto" data-setting="getReady">
                    @foreach ([0, 3, 5, 10] as $s)
                        <option value="{{ $s }}">{{ $s }} s</option>
                    @endforeach
                </select>
            </div>
            @foreach ([
                'sound' => ['Sound', 'Beeps at the start and end of each exercise'],
                'countdownBeeps' => ['3-2-1 countdown beeps', 'Short beeps in the last 3 seconds'],
                'voice' => ['Voice cues', 'Announces exercises and “switch sides” (text-to-speech)'],
                'vibration' => ['Vibration', 'Where supported (most Android phones)'],
                'keepAwake' => ['Keep screen awake', 'While Workout Mode or a timer is running'],
                'pauseOnVideo' => ['Pause when watching a video', 'In Workout Mode'],
            ] as $key => [$label, $hint])
                <div class="switch-row">
                    <div><strong>{{ $label }}</strong><span class="small muted">{{ $hint }}</span></div>
                    <label class="switch"><input type="checkbox" data-setting="{{ $key }}" aria-label="{{ $label }}"><span></span></label>
                </div>
            @endforeach
            <div class="row" style="margin-top:12px">
                <button type="button" class="btn btn-outline btn-sm" data-test-sound><x-icon name="volume" /> Test sound &amp; vibration</button>
            </div>
        </section>

        <section class="card">
            <h2>Appearance</h2>
            <div class="segmented" role="group" aria-label="Theme" data-setting-theme>
                <button type="button" data-value="dark" aria-pressed="false">Dark</button>
                <button type="button" data-value="light" aria-pressed="false">Light</button>
                <button type="button" data-value="system" aria-pressed="false">System</button>
            </div>
        </section>

        <section class="card">
            <h2>Your data</h2>
            <p class="small muted">Sessions and settings stay in this browser. Export a backup before clearing browser data or switching phones.</p>
            <div class="row">
                <button type="button" class="btn btn-outline btn-sm" data-export><x-icon name="download" /> Export backup</button>
                <label class="btn btn-outline btn-sm" style="cursor:pointer"><x-icon name="upload" /> Import backup<input type="file" accept="application/json,.json" class="sr-only" data-import></label>
                <button type="button" class="btn btn-danger btn-sm" data-clear><x-icon name="trash" /> Delete all data</button>
            </div>
        </section>

        <section class="card">
            <h2>About</h2>
            <p class="small">ReadyUp is an exercise and warm-up guide, not a medical device or substitute for individualised medical or physiotherapy advice. See <a href="{{ route('safety') }}">Safety &amp; Guidelines</a> and <a href="{{ route('sources') }}">Sources &amp; Evidence</a>.</p>
        </section>
    </form>
</x-layout>
