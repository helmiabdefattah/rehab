{{-- Workout Mode: cloned and driven by resources/js/workout/player.js --}}
<template id="workout-template">
    <div class="workout" data-phase="ready" role="dialog" aria-modal="true" aria-label="Workout Mode">
        <div class="workout-top">
            <button type="button" class="icon-btn" data-action="end" aria-label="End workout" title="End workout"><x-icon name="x" /></button>
            <span class="count" data-slot="count"></span>
            <span class="total-left" data-slot="total"></span>
            <button type="button" class="icon-btn" data-action="sound" aria-label="Toggle sound" title="Sound"><x-icon name="volume" /></button>
            <button type="button" class="icon-btn" data-action="fullscreen" aria-label="Full screen" title="Full screen"><x-icon name="maximize" /></button>
        </div>
        <div class="workout-overall" aria-hidden="true"><span data-slot="overall"></span></div>

        <div class="workout-body">
            <div class="workout-label" data-slot="label">Get ready</div>
            <h2 class="workout-name" data-slot="name"></h2>
            <div class="workout-ar" dir="rtl" lang="ar" data-slot="ar"></div>
            <div class="workout-side" data-slot="side"></div>
            <div class="workout-clock" data-slot="clock" role="timer" aria-live="off"></div>
            <div class="workout-of" data-slot="of"></div>
            <div class="workout-bar" aria-hidden="true"><span data-slot="bar"></span></div>
            <p class="workout-cue" data-slot="cue"></p>
            <div class="workout-demo" data-slot="demo"></div>
            <details class="workout-howto">
                <summary>How to perform</summary>
                <ol data-slot="steps"></ol>
            </details>
        </div>

        <div class="workout-controls">
            <button type="button" class="btn" data-action="prev"><x-icon name="skip-back" /> Previous</button>
            <button type="button" class="btn btn-primary" data-action="toggle"><x-icon name="pause" fill data-slot="toggle-icon" /> <span data-slot="toggle-label">Pause</span></button>
            <button type="button" class="btn" data-action="next"><x-icon name="skip-forward" /> Skip</button>
        </div>
        <div class="workout-secondary">
            <button type="button" class="btn btn-ghost btn-sm" data-action="restart"><x-icon name="restart" /> Restart</button>
            <button type="button" class="btn btn-ghost btn-sm" data-action="video"><x-icon name="maximize" /> <span data-slot="video-label">View animation</span></button>
        </div>
        <div class="workout-next">
            <div>
                <small>Next</small>
                <strong data-slot="next"></strong>
            </div>
            <span class="dur" data-slot="next-dur"></span>
        </div>
    </div>
</template>

<template id="finish-template">
    <div class="workout" data-phase="done" role="dialog" aria-modal="true" aria-label="Warm-up complete">
        <div class="workout-body" style="justify-content:flex-start">
            <div class="done-hero">
                <div class="big" data-slot="emoji">🎉</div>
                <h2 data-slot="heading">Warm-up complete</h2>
                <p class="muted" data-slot="summary"></p>
            </div>

            <form class="stack" data-feedback style="max-width:560px;width:100%;text-align:left;--stack-gap:18px">
                <fieldset style="border:0;padding:0;margin:0">
                    <legend class="field-label" style="margin-bottom:8px">How did you feel?</legend>
                    <div class="feel-options" data-feel>
                        <button type="button" data-value="good" aria-pressed="false"><span>😊</span>Good</button>
                        <button type="button" data-value="okay" aria-pressed="false"><span>😐</span>Okay</button>
                        <button type="button" data-value="uncomfortable" aria-pressed="false"><span>😟</span>Uncomfortable</button>
                    </div>
                </fieldset>

                <fieldset style="border:0;padding:0;margin:0">
                    <legend class="field-label" style="margin-bottom:8px">Did you experience any pain during the warm-up?</legend>
                    <div class="yes-no" data-pain style="width:100%">
                        <button type="button" data-value="0" aria-pressed="false">No</button>
                        <button type="button" data-value="1" aria-pressed="false">Yes</button>
                    </div>
                </fieldset>

                <fieldset data-pain-areas hidden style="border:0;padding:0;margin:0">
                    <legend class="field-label" style="margin-bottom:8px">Where?</legend>
                    <div class="chips">
                        @foreach (['knee' => 'Knee', 'hip' => 'Hip', 'glute' => 'Glute', 'piriformis' => 'Piriformis area', 'ankle' => 'Ankle', 'other' => 'Other'] as $value => $label)
                            <button type="button" class="chip" data-value="{{ $value }}" aria-pressed="false">{{ $label }}</button>
                        @endforeach
                    </div>
                </fieldset>

                <div class="callout callout-warn" data-feedback-advice hidden>
                    <x-icon name="info" />
                    <div>
                        <p class="small" style="margin:0">Thanks — this will be recorded. Consider reducing the intensity or level next time. If symptoms persist or worsen, seek advice from a physiotherapist or doctor. This app does not diagnose problems.</p>
                    </div>
                </div>

                <div class="field">
                    <label for="session-notes">Notes <span class="muted small">(optional)</span></label>
                    <textarea id="session-notes" class="input" data-notes maxlength="500" placeholder="e.g. knee felt stiff during step-ups, eased after a few minutes"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block" data-save disabled>Save session</button>
                <button type="button" class="btn btn-ghost btn-block" data-discard>Don’t save</button>
            </form>

            <div class="stack" data-saved hidden style="max-width:560px;width:100%">
                <div class="callout callout-ok">
                    <x-icon name="check" />
                    <div><h3>Session saved</h3><p class="small">Your progress dashboard has been updated.</p></div>
                </div>
                <a href="{{ route('timer') }}" class="btn btn-primary btn-lg btn-block"><x-icon name="timer" /> Open Training Timer</a>
                <a href="{{ route('progress') }}" class="btn btn-outline btn-block"><x-icon name="chart" /> View My Progress</a>
                <button type="button" class="btn btn-ghost btn-block" data-finish-close><x-icon name="list" /> Back to routine</button>
            </div>
        </div>
    </div>
</template>
