<x-layout :title="$activity->shortLabel().' Day'" page="train">
    <header class="page-head">
        <span class="eyebrow">{{ $activity->emoji() }} {{ $activity->shortLabel() }} day</span>
        <h1>{{ $activity->shortLabel() }} — warm-up &amp; workout</h1>
        <p>Today you’re training your {{ $activity->muscles() }}. Start with the targeted warm-up, then move into the workout.</p>
    </header>

    {{-- PART 1 — Warm-up --}}
    <section class="section">
        <div class="train-part">
            <span class="train-step">1</span>
            <div class="train-part-body">
                <h2>Warm-up</h2>
                <p class="muted small">A {{ $activity->shortLabel() }}-specific warm-up that raises your temperature, mobilises the joints and switches on the muscles you’re about to train — so you’re stronger and better protected.</p>
                <div class="row">
                    <a class="btn btn-primary" href="{{ route('warmup.builder', ['activity' => $split]) }}"><x-icon name="play" fill /> Start {{ $activity->shortLabel() }} warm-up</a>
                    <a class="btn btn-ghost" href="{{ route('warmup.quick', 10) }}" data-quick-link>⚡ Quick 10-min</a>
                </div>
            </div>
        </div>
    </section>

    {{-- PART 2 — Workout --}}
    <section class="section">
        <div class="train-part">
            <span class="train-step">2</span>
            <div class="train-part-body">
                <h2>Workout</h2>
                <p class="muted small">{{ $exercises->count() }} exercises for your {{ $activity->shortLabel() }} session. Start the guided workout to step through each exercise set by set, with a rest timer between sets. Warm up first; add weight only with clean technique.</p>
                <div class="row">
                    <button type="button" class="btn btn-primary" data-start-workout><x-icon name="play" fill /> Start {{ $activity->shortLabel() }} workout</button>
                    <a class="btn btn-ghost" href="{{ route('timer') }}"><x-icon name="timer" /> Rest timer</a>
                </div>
            </div>
        </div>

        <script type="application/json" id="workout-data" data-split="{{ $split }}" data-label="{{ $activity->shortLabel() }}">@json($exercises->map->toClientArray())</script>

        <div class="card-grid" style="margin-top:16px">
            @foreach ($exercises as $exercise)
                <x-exercise-card :exercise="$exercise" />
            @endforeach
        </div>

        <div class="row section">
            <a class="btn btn-ghost" href="{{ route('progress') }}"><x-icon name="chart" /> My progress</a>
        </div>
    </section>

    @include('warmup.partials.workout-templates')

    <section class="section">
        <div class="callout callout-info">
            <x-icon name="info" />
            <div>
                <p class="small" style="margin:0">These are suggested exercises and set/rep ranges — adjust the load, volume and selection to your own program and experience. Stop if you feel sharp pain.</p>
            </div>
        </div>
    </section>
</x-layout>
