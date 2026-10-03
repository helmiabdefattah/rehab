<x-layout title="Build My Warm-Up" page="builder">
    <div data-builder data-preselected="{{ $preselected }}" data-quick="{{ $quick ?? '' }}" data-autostart="{{ $autostart ? '1' : '0' }}">
        @if ($routine)
            <script type="application/json" id="routine-data">@json($routine)</script>
        @endif

        <div class="callout callout-info" data-resume hidden style="margin-bottom:16px">
            <x-icon name="clock" />
            <div class="stack" style="--stack-gap:8px">
                <div>
                    <h3>Resume your warm-up?</h3>
                    <p class="small" data-resume-text></p>
                </div>
                <div class="row">
                    <button type="button" class="btn btn-primary btn-sm" data-resume-yes>Resume</button>
                    <button type="button" class="btn btn-ghost btn-sm" data-resume-no>Discard</button>
                </div>
            </div>
        </div>

        <header class="page-head">
            <span class="eyebrow">Build My Warm-Up</span>
            <h1 data-step-title>What are you training today?</h1>
            <p class="muted" data-step-sub>Choose your split. The warm-up primes exactly those muscles — heat → mobility → activation → movement rehearsal.</p>
        </header>

        <ol class="wizard-steps" aria-label="Steps">
            <li data-step-dot="1" class="is-current"><span></span><small>Split</small></li>
            <li data-step-dot="2"><span></span><small>Session</small></li>
            <li data-step-dot="3"><span></span><small>Routine</small></li>
        </ol>

        <form data-builder-form novalidate>
            {{-- STEP 1 — Activity --}}
            <section class="wizard-panel" data-step="1">
                <div class="activity-grid" role="group" aria-label="Activity">
                    @foreach ($activities as $activity)
                        <button type="button" class="activity-tile" data-activity="{{ $activity->value }}" aria-pressed="false">
                            <span class="emoji" aria-hidden="true">{{ $activity->emoji() }}</span>
                            <strong>{{ $activity->shortLabel() }}</strong>
                            <small>{{ $activity->description() }}</small>
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- STEP 2 — Session settings --}}
            <section class="wizard-panel stack" data-step="2" hidden style="--stack-gap:22px">
                <div class="field">
                    <span class="field-label" id="intensity-label">Workout intensity</span>
                    <div class="segmented" role="group" aria-labelledby="intensity-label" data-choice="intensity">
                        @foreach ($intensities as $intensity)
                            <button type="button" data-value="{{ $intensity->value }}" aria-pressed="{{ $intensity->value === 'moderate' ? 'true' : 'false' }}">{{ $intensity->label() }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="field">
                    <span class="field-label" id="time-label">Available time</span>
                    <div class="segmented" role="group" aria-labelledby="time-label" data-choice="minutes">
                        @foreach ($durations as $minutes)
                            <button type="button" data-value="{{ $minutes }}" aria-pressed="{{ $minutes === 10 ? 'true' : 'false' }}">{{ $minutes }}<small>min</small></button>
                        @endforeach
                    </div>
                </div>

                <div class="field">
                    <span class="field-label" id="level-label">Progression level</span>
                    <div class="segmented" role="group" aria-labelledby="level-label" data-choice="level">
                        @foreach ($levels as $level)
                            <button type="button" data-value="{{ $level->value }}" aria-pressed="{{ $level->value === 1 ? 'true' : 'false' }}">Level {{ $level->value }}<small>{{ $level->label() }}</small></button>
                        @endforeach
                    </div>
                    <p class="field-hint" data-level-hint>{{ $levels[0]->description() }}</p>
                </div>

                <div class="field">
                    <span class="field-label" id="equipment-label">Equipment available today</span>
                    <div class="chips" role="group" aria-labelledby="equipment-label" data-equipment>
                        @foreach ($equipment as $key => $label)
                            <button type="button" class="chip" data-value="{{ $key }}" aria-pressed="false">{{ $label }}</button>
                        @endforeach
                    </div>
                    <p class="field-hint">Exercises that need missing equipment are swapped for alternatives.</p>
                </div>
            </section>

            {{-- STEP 3 — Generated routine --}}
            <section class="wizard-panel" data-step="3" hidden>
                <div data-routine-loading class="empty"><div class="big">⏳</div><p>Building your warm-up…</p></div>
                <div data-routine-error hidden class="callout callout-danger">
                    <x-icon name="alert" />
                    <div>
                        <h3>Couldn’t build the routine</h3>
                        <p class="small" data-routine-error-text>Please check your connection and try again.</p>
                        <button type="button" class="btn btn-sm btn-outline" data-retry>Try again</button>
                    </div>
                </div>
                <div data-routine-preview hidden></div>
            </section>

            <div class="wizard-nav">
                <button type="button" class="btn btn-outline" data-back hidden><x-icon name="chevron-left" /> Back</button>
                <button type="button" class="btn btn-primary" data-next disabled>Next <x-icon name="chevron-right" /></button>
            </div>
        </form>
    </div>

    @include('warmup.partials.workout-templates')
</x-layout>
