<x-layout title="Safety & Guidelines" page="safety">
    <header class="page-head">
        <span class="eyebrow">Safety &amp; Guidelines</span>
        <h1>Train with confidence — and with care</h1>
        <p>This app supports an adult returning to regular activity after completing rehabilitation for a previous knee cartilage surgery and a previous piriformis / glute muscle injury.</p>
    </header>

    <div class="callout callout-danger" role="note">
        <x-icon name="alert" />
        <div>
            <h3>Medical disclaimer</h3>
            <p class="small">This application is an exercise and warm-up guide, not a medical device or substitute for individualized medical or physiotherapy advice. Because the user has a history of knee cartilage surgery and a previous Piriformis/Glute injury, exercise selection and progression should respect any restrictions previously provided by their healthcare professional. Stop if you experience significant pain, instability, locking, swelling, or other concerning symptoms and seek professional assessment.</p>
        </div>
    </div>

    <div class="prose">
        <h2 id="previous-injury">Previous injury considerations</h2>
        <ul>
            <li><strong>Rehab complete ≠ maximum intensity.</strong> Completing rehabilitation does not necessarily mean every movement should immediately be performed at maximum intensity. Build up gradually.</li>
            <li><strong>Pain-free or acceptable.</strong> Exercises should be pain-free or within an acceptable, non-worsening range — mild discomfort that doesn’t build during the session and settles quickly afterwards, <em>if that is what your clinician has agreed</em>. Follow any pain thresholds your own physiotherapist gave you.</li>
            <li><strong>No assumptions about your surgery.</strong> The app does not know the type of cartilage procedure, technique, affected area of the knee, or any current restrictions — it makes no surgery-specific claims. Your surgeon’s and physiotherapist’s guidance always comes first.</li>
            <li><strong>No diagnosis, no treatment.</strong> The readiness check and post-session feedback only adapt and record your warm-up. They don’t diagnose injuries or prescribe medical treatment.</li>
            <li><strong>Get assessed if symptoms return.</strong> If symptoms return, persist or worsen, seek a professional assessment.</li>
        </ul>

        <h2 id="stop-signs">Stop the exercise if you notice</h2>
        <div class="grid grid-md-2">
            @foreach ([
                ['Sharp pain', 'Sudden or sharp pain in the knee, hip, buttock or elsewhere.'],
                ['Instability / giving way', 'The knee feels like it may buckle or give way.'],
                ['Locking or catching', 'The knee locks, catches or won’t fully straighten or bend.'],
                ['Significant swelling', 'Noticeable swelling during or after activity.'],
                ['Pain spreading down the leg', 'Pain travelling down the leg, tingling or numbness.'],
                ['Anything unusual', 'Dizziness, chest pain, unusual breathlessness or symptoms you haven’t had before.'],
            ] as [$title, $text])
                <div class="card" style="padding:14px">
                    <strong>⛔ {{ $title }}</strong>
                    <p class="small muted" style="margin:4px 0 0">{{ $text }}</p>
                </div>
            @endforeach
        </div>

        <h2 id="structure">How every warm-up is built</h2>
        <p>General body temperature → mobility → activation → stability → dynamic movement → sport-specific preparation. It is <strong>not</strong> a generic stretching routine.</p>
        <div class="stage-flow">
            @foreach ($stages as $stage)
                <div data-stage="{{ $stage->value }}">
                    <span class="stage-pill"><span class="stage-dot"></span> Stage {{ $stage->number() }}</span>
                    <strong>{{ $stage->label() }}</strong>
                    <span class="small muted">{{ $stage->description() }}</span>
                </div>
            @endforeach
        </div>

        <h2 id="knee">Knee preparation</h2>
        <p>Low-impact preparation first: ankle mobility, calf raises, controlled knee flexion/extension, sit-to-stand, mini squats, step-up progressions and balance work. <strong>Not automatically prescribed:</strong> deep loaded squats, high-volume jumping, aggressive twisting or high-impact plyometrics. For running and football, impact is introduced gradually (marching → skipping → easy jogging → build-ups) and only at levels where it is appropriate.</p>
        <p><a href="{{ route('warmup.focus', 'knee') }}" class="btn btn-outline btn-sm">Open Knee Preparation →</a></p>

        <h2 id="hip">Hip &amp; glute preparation</h2>
        <p>Glute activation, hip external and internal rotation, hip stability and controlled hip mobility. The goal is <strong>controlled mobility and activation, not forcing the piriformis into a painful stretch.</strong> Figure-4 work is done as gentle rhythmic rocking with a mild sensation only; stop if pain spreads down the leg or you notice tingling or numbness.</p>
        <p><a href="{{ route('warmup.focus', 'hip') }}" class="btn btn-outline btn-sm">Open Hip &amp; Glute Preparation →</a></p>

        <h2 id="readiness">The daily readiness check</h2>
        <ul>
            <li><strong>Good readiness</strong> (no symptoms, 7–10/10) → your normal routine.</li>
            <li><strong>Take it steady</strong> (stiffness only, or 4–6/10) → more controlled mobility and activation, intensity capped at moderate, impact capped at moderate, level capped at 2.</li>
            <li><strong>Caution</strong> (pain, swelling, instability, pain on walking/stairs, or 1–3/10) → a caution message, light intensity, low-impact exercises only, and knee- or hip-loading drills removed depending on what you reported. You are never labelled as injured.</li>
        </ul>

        <h2 id="levels">Progression levels</h2>
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
        <p class="small">You are never moved up automatically based on time. The progression check in <a href="{{ route('progress') }}">My Progress</a> looks for consistent, symptom-free sessions at your current level; the decision is yours (ideally together with your physiotherapist).</p>

        <h2 id="wording">How we talk about exercises</h2>
        <p>Exercises are described as <em>“commonly used to prepare / activate…”</em>. No exercise in this app is claimed to prevent or cure injuries. See <a href="{{ route('sources') }}">Sources &amp; Evidence</a> for the references used.</p>
    </div>
</x-layout>
