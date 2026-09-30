<x-layout title="My Progress" page="progress">
    <header class="page-head">
        <span class="eyebrow">My Progress</span>
        <h1>Your warm-up history</h1>
        <p>Stored privately on this device. Export a backup any time in <a href="{{ route('settings') }}">Settings</a>.</p>
    </header>

    <div data-progress-empty class="card empty" hidden>
        <div class="big">📭</div>
        <h2>No sessions yet</h2>
        <p>Complete a warm-up and record how you felt — your dashboard will appear here.</p>
        <a href="{{ route('warmup.builder') }}" class="btn btn-primary">Start a warm-up</a>
    </div>

    <div data-progress-content hidden>
        <section class="stat-grid" aria-label="Summary">
            <div class="stat is-hero"><div class="label">Warm-ups completed</div><div class="value tabular" data-stat="completed">0</div></div>
            <div class="stat"><div class="label">Total sessions</div><div class="value tabular" data-stat="total">0</div></div>
            <div class="stat"><div class="label">Average readiness</div><div class="value tabular" data-stat="readiness">–</div></div>
            <div class="stat"><div class="label">Exercises completed</div><div class="value tabular" data-stat="exercises">0</div></div>
            <div class="stat"><div class="label">This week</div><div class="value tabular" data-stat="week">0</div></div>
            <div class="stat"><div class="label">Current level</div><div class="value" data-stat="level">1</div></div>
        </section>

        <section class="section grid grid-md-2">
            <div class="card">
                <h2>Sessions per week</h2>
                <p class="muted small">Last 8 weeks</p>
                <div class="col-chart" data-weekly style="--cols:8" role="img" aria-label="Sessions per week for the last 8 weeks"></div>
                <div class="col-labels" data-weekly-labels style="--cols:8"></div>
            </div>
            <div class="card">
                <h2>Activity type</h2>
                <p class="muted small">All sessions</p>
                <div class="bar-chart" data-activity-chart></div>
            </div>
        </section>

        <section class="section grid grid-md-2">
            <div class="card">
                <h2>How you felt</h2>
                <p class="muted small">Feedback after each session</p>
                <div class="bar-chart" data-feel-chart></div>
            </div>
            <div class="card">
                <h2>Pain reports</h2>
                <p class="muted small">Where discomfort was recorded (no diagnosis)</p>
                <div class="bar-chart" data-pain-chart></div>
                <div data-pain-advice class="callout callout-warn" hidden style="margin-top:14px">
                    <x-icon name="info" />
                    <div><p class="small" style="margin:0" data-pain-advice-text></p></div>
                </div>
            </div>
        </section>

        <section class="section card" aria-labelledby="level-check-title">
            <div class="section-head">
                <h2 id="level-check-title">Progression check</h2>
                <span class="badge" data-level-current>Level 1</span>
            </div>
            <p class="small muted">You are never moved up automatically. These criteria focus on symptom-free movement quality and gradual exposure — not time alone. If in doubt, check with your physiotherapist.</p>
            <ul class="checklist" data-level-criteria></ul>
            <div class="row" style="margin-top:12px">
                <button type="button" class="btn btn-primary btn-sm" data-level-up hidden></button>
                <button type="button" class="btn btn-ghost btn-sm" data-level-down hidden></button>
            </div>
        </section>

        <section class="section card">
            <div class="section-head">
                <h2>Session history</h2>
                <span class="muted small" data-history-count></span>
            </div>
            <ul class="history" data-history></ul>
        </section>
    </div>
</x-layout>
