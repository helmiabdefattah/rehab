<x-layout page="home">
    <section class="page-head">
        <span class="eyebrow" data-greeting>Ready to move</span>
        <h1>Prepare your body before you train.</h1>
        <p>Personalised warm-ups that build from general heat to mobility, activation and sport-specific preparation — with extra care for the knee and hip &amp; glute.</p>
        <div class="row">
            <span class="badge badge-ok" data-level-badge>Level 1 · Re-entry</span>
            <span class="badge" data-last-session hidden></span>
        </div>
    </section>

    <section aria-labelledby="quick-title">
        <h2 id="quick-title" class="sr-only">Quick warm-ups</h2>
        <a class="hero-quick" href="{{ route('warmup.quick', 10) }}" data-quick-link>
            <div>
                <strong>⚡ QUICK 10-MIN WARM-UP</strong>
                <span>Balanced routine · starts immediately</span>
            </div>
            <span class="play"><x-icon name="play" fill /></span>
        </a>
        <div class="quick-row">
            @foreach ($quick as $minutes => $q)
                <a class="quick-chip" href="{{ route('warmup.quick', $minutes) }}" data-quick-link>
                    <span>⚡ {{ $minutes }}-MIN</span>
                    <small>{{ \Illuminate\Support\Str::after($q['title'], ' ') }}</small>
                </a>
            @endforeach
        </div>
    </section>

    <section class="section" aria-labelledby="build-title">
        <div class="section-head">
            <h2 id="build-title">Build My Warm-Up</h2>
            <a href="{{ route('warmup.builder') }}" class="small">Customise →</a>
        </div>
        <p class="muted small">What are you about to do? We’ll ask how you feel today and adjust the routine.</p>
        <div class="activity-grid">
            @foreach ($activities as $activity)
                <a class="activity-tile" href="{{ route('warmup.builder', ['activity' => $activity->value]) }}">
                    <span class="emoji" aria-hidden="true">{{ $activity->emoji() }}</span>
                    <strong>{{ $activity->label() }}</strong>
                    <small>{{ $activity->description() }}</small>
                </a>
            @endforeach
        </div>
    </section>

    <section class="section" aria-labelledby="focus-title">
        <div class="section-head">
            <h2 id="focus-title">Focused preparation</h2>
        </div>
        <div class="grid grid-md-2">
            <a class="card card-link" href="{{ route('warmup.focus', 'knee') }}">
                <span class="eyebrow">{{ $focus['knee']['emoji'] }} Knee</span>
                <h3>Knee Preparation</h3>
                <p class="muted small">Ankle mobility, calf raises, controlled squat patterns, step-ups and balance — low impact, progressive.</p>
            </a>
            <a class="card card-link" href="{{ route('warmup.focus', 'hip') }}">
                <span class="eyebrow">{{ $focus['hip']['emoji'] }} Hip &amp; Glute</span>
                <h3>Hip &amp; Glute Preparation</h3>
                <p class="muted small">Glute activation, hip rotation and stability. Controlled mobility — never forcing a painful piriformis stretch.</p>
            </a>
        </div>
    </section>

    <section class="section grid grid-md-2">
        <a class="card card-link" href="{{ route('timer') }}">
            <span class="eyebrow">⏱ During training</span>
            <h3>Training Timer</h3>
            <p class="muted small">Rest timer between sets, interval (work/rest) timer, stopwatch with laps and countdown — big display, sound &amp; vibration.</p>
        </a>
        <a class="card card-link" href="{{ route('progress') }}">
            <span class="eyebrow">📈 My Progress</span>
            <h3 data-progress-headline>No sessions yet</h3>
            <p class="muted small" data-progress-sub>Complete a warm-up to start tracking sessions, readiness and how you felt.</p>
        </a>
    </section>

    <section class="section">
        <div class="callout callout-warn">
            <x-icon name="shield" />
            <div>
                <h3>Previous injury considerations</h3>
                <p class="small">Completing rehabilitation doesn’t mean every movement should immediately be done at maximum intensity. Keep exercises pain-free or within an acceptable, non-worsening range, and stop if you feel sharp pain, instability, locking or significant swelling.</p>
                <a href="{{ route('safety') }}" class="small">Read Safety &amp; Guidelines →</a>
            </div>
        </div>
    </section>
</x-layout>
