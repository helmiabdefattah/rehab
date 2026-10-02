<x-layout page="home">
    <section class="page-head">
        <span class="eyebrow" data-greeting>Ready to move</span>
        <h1>Train smarter — warm up right, then work out.</h1>
        <p>Pick what you’re training today. You get a warm-up built for those exact muscles — so you perform at your best and cut the risk of injury — then the workout itself. Every move has a looping animated demo.</p>
        <div class="row">
            <span class="badge" data-last-session hidden></span>
        </div>
    </section>

    <section class="section" aria-labelledby="split-title">
        <div class="section-head">
            <h2 id="split-title">What are you training today?</h2>
        </div>
        <p class="muted small">Choose your split. Each one opens its targeted warm-up and the workout exercises.</p>
        <div class="activity-grid">
            @foreach ($activities as $activity)
                <a class="activity-tile" href="{{ route('train', $activity->value) }}">
                    <span class="emoji" aria-hidden="true">{{ $activity->emoji() }}</span>
                    <strong>{{ $activity->shortLabel() }}</strong>
                    <small>{{ $activity->description() }}</small>
                </a>
            @endforeach
        </div>
    </section>

    <section aria-labelledby="quick-title">
        <h2 id="quick-title" class="sr-only">Quick warm-ups</h2>
        <a class="hero-quick" href="{{ route('warmup.quick', 10) }}" data-quick-link>
            <div>
                <strong>⚡ QUICK 10-MIN WARM-UP</strong>
                <span>Balanced full-body routine · starts immediately</span>
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

    <section class="section grid grid-md-2">
        <a class="card card-link" href="{{ route('warmup.builder') }}">
            <span class="eyebrow">🔥 Warm-up</span>
            <h3>Build My Warm-Up</h3>
            <p class="muted small">Choose a split, time and intensity. The routine progresses from general heat → mobility → activation → movement rehearsal for the muscles you’re about to train.</p>
        </a>
        <a class="card card-link" href="{{ route('exercises.index') }}">
            <span class="eyebrow">📚 Library</span>
            <h3>Exercise Library</h3>
            <p class="muted small">Every warm-up and workout exercise with technique, mistakes, progressions and an animated demonstration.</p>
        </a>
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
            <p class="muted small" data-progress-sub>Complete a warm-up to start tracking your sessions and how you felt.</p>
        </a>
    </section>

    <section class="section">
        <div class="callout callout-info">
            <x-icon name="shield" />
            <div>
                <h3>Warm up before you lift</h3>
                <p class="small">A targeted warm-up raises your temperature, primes the joints and switches on the right muscles so you’re stronger and better protected in your working sets. Use good technique and stop if you feel sharp pain.</p>
                <a href="{{ route('safety') }}" class="small">Read Safety &amp; Guidelines →</a>
            </div>
        </div>
    </section>
</x-layout>
