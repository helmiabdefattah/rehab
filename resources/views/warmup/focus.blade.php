<x-layout :title="$config['title']" page="focus">
    <header class="page-head">
        <span class="eyebrow">{{ $config['emoji'] }} Focused preparation</span>
        <h1>{{ $config['title'] }}</h1>

        @if ($area === 'knee')
            <p>Low-impact preparation for the knee and the muscles that support it: ankle mobility, calf activation, controlled knee bending and straightening, sit-to-stand and mini squats, step-up progressions and balance. Use it on its own or before any session.</p>
        @else
            <p>Glute activation, hip external and internal rotation, hip stability and controlled hip mobility. The goal is <strong>controlled mobility and activation — not forcing the piriformis or glutes into a painful stretch.</strong></p>
        @endif
    </header>

    <div class="grid grid-md-2">
        @foreach ($config['durations'] as $minutes)
            <a class="hero-quick" href="{{ route('warmup.focus.start', [$area, $minutes]) }}" data-quick-link>
                <div>
                    <strong>▶ {{ $minutes }}-MIN {{ strtoupper($area === 'knee' ? 'Knee Prep' : 'Hip & Glute Prep') }}</strong>
                    <span>Starts Workout Mode immediately</span>
                </div>
                <span class="play"><x-icon name="play" fill /></span>
            </a>
        @endforeach
    </div>

    <section class="section grid grid-md-2">
        @if ($area === 'knee')
            <div class="callout callout-ok">
                <x-icon name="check" />
                <div>
                    <h3>What this section prioritises</h3>
                    <ul class="small">
                        <li>Ankle mobility and calf raises</li>
                        <li>Controlled knee flexion / extension (sit-to-stand, mini squat, TKE)</li>
                        <li>Step-up progression on a low step</li>
                        <li>Balance and single-leg stability</li>
                        <li>Glute support for knee alignment</li>
                    </ul>
                </div>
            </div>
            <div class="callout callout-warn">
                <x-icon name="alert" />
                <div>
                    <h3>Not automatically prescribed</h3>
                    <ul class="small">
                        <li>Deep loaded squats</li>
                        <li>High-volume jumping</li>
                        <li>Aggressive twisting / pivoting</li>
                        <li>High-impact plyometrics</li>
                    </ul>
                    <p class="small">For running and football, impact is introduced gradually and only at the appropriate level. Respect any restrictions from your surgeon or physiotherapist — the app makes no assumptions about the type of cartilage surgery you had.</p>
                </div>
            </div>
        @else
            <div class="callout callout-ok">
                <x-icon name="check" />
                <div>
                    <h3>What this section prioritises</h3>
                    <ul class="small">
                        <li>Glute activation (bridge, clamshell, side-lying abduction)</li>
                        <li>Hip external &amp; internal rotation (90/90, hip CARs)</li>
                        <li>Hip stability (band walks, side plank, single-leg work)</li>
                        <li>Gentle, rhythmic figure-4 mobility</li>
                    </ul>
                </div>
            </div>
            <div class="callout callout-warn">
                <x-icon name="alert" />
                <div>
                    <h3>Avoid forcing the stretch</h3>
                    <ul class="small">
                        <li>No aggressive or long, strong piriformis stretches before activity.</li>
                        <li>Aim for a mild sensation — never pain.</li>
                        <li>Stop if pain spreads down the leg, or you feel tingling or numbness.</li>
                        <li>Skip positions that reproduce your previous buttock pain today.</li>
                    </ul>
                </div>
            </div>
        @endif
    </section>

    <section class="section">
        <div class="section-head">
            <h2>Exercises in this section</h2>
            <span class="muted small">{{ $exercises->count() }} exercises</span>
        </div>
        <div class="card-grid">
            @foreach ($exercises as $exercise)
                <x-exercise-card :exercise="$exercise" />
            @endforeach
        </div>
    </section>
</x-layout>
