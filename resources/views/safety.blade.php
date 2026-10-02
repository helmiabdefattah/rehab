<x-layout title="Safety & Guidelines" page="safety">
    <header class="page-head">
        <span class="eyebrow">Safety &amp; Guidelines</span>
        <h1>Train with confidence — and with care</h1>
        <p>A good warm-up and solid technique let you train hard while keeping the risk of injury low. Here’s how ReadyUp is built and how to stay safe.</p>
    </header>

    <div class="callout callout-info" role="note">
        <x-icon name="info" />
        <div>
            <h3>Disclaimer</h3>
            <p class="small">ReadyUp is an exercise, warm-up and workout guide, not a medical device or a substitute for individualised medical or physiotherapy advice. The exercises, set and rep ranges are general suggestions — adapt them to your own program, equipment and experience. If you have a current or past injury, follow the guidance of your own healthcare professional.</p>
        </div>
    </div>

    <div class="prose">
        <h2 id="warm-up-first">Why warm up for your split</h2>
        <ul>
            <li><strong>Prime the right muscles.</strong> Each warm-up targets the muscles and joints of the split you’re about to train — push, pull, legs or cardio &amp; core — so they’re ready to produce force.</li>
            <li><strong>Perform better.</strong> Raising your temperature and rehearsing the movement pattern means stronger, more controlled working sets.</li>
            <li><strong>Lower the risk.</strong> Mobile joints and switched-on stabilisers reduce the chance of tweaks and strains.</li>
            <li><strong>Build up gradually.</strong> Add load only when your technique stays clean. More weight is never worth losing form.</li>
        </ul>

        <h2 id="stop-signs">Stop the exercise if you notice</h2>
        <div class="grid grid-md-2">
            @foreach ([
                ['Sharp pain', 'Sudden or sharp pain in a joint, muscle or elsewhere.'],
                ['Joint instability', 'A joint feels like it may buckle or give way.'],
                ['Locking or catching', 'A joint locks, catches or won’t move through its range.'],
                ['Significant swelling', 'Noticeable swelling during or after activity.'],
                ['Pain spreading', 'Pain travelling down a limb, tingling or numbness.'],
                ['Anything unusual', 'Dizziness, chest pain, unusual breathlessness or new symptoms.'],
            ] as [$title, $text])
                <div class="card" style="padding:14px">
                    <strong>⛔ {{ $title }}</strong>
                    <p class="small muted" style="margin:4px 0 0">{{ $text }}</p>
                </div>
            @endforeach
        </div>

        <h2 id="structure">How every warm-up is built</h2>
        <p>General heat → mobility → activation → movement rehearsal. It is <strong>not</strong> a generic stretching routine — each stage prepares you for the split you’re about to train.</p>
        <div class="stage-flow">
            @foreach ($stages as $stage)
                <div data-stage="{{ $stage->value }}">
                    <span class="stage-pill"><span class="stage-dot"></span> Stage {{ $stage->number() }}</span>
                    <strong>{{ $stage->label() }}</strong>
                    <span class="small muted">{{ $stage->description() }}</span>
                </div>
            @endforeach
        </div>

        <h2 id="levels">Intensity &amp; progression levels</h2>
        <div class="level-list">
            @foreach ($levels as $level)
                <div class="card level-card">
                    <span class="lvl">{{ $level->value }}</span>
                    <div>
                        <strong>Level {{ $level->value }} – {{ $level->label() }}</strong>
                        <p class="small muted" style="margin:2px 0 0">{{ $level->description() }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="small">Pick the level that matches how you feel and what you’re training. You’re never moved up automatically — the choice is yours.</p>

        <h2 id="wording">How we talk about exercises</h2>
        <p>Warm-up exercises are described as <em>“commonly used to prepare / activate…”</em>. No exercise in this app is claimed to prevent or cure injuries. See <a href="{{ route('sources') }}">Sources &amp; Evidence</a> for the references used.</p>
    </div>
</x-layout>
