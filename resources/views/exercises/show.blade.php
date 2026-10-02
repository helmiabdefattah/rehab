<x-layout :title="$exercise->name" page="exercise">
    <nav class="small" style="margin-bottom:12px"><a href="{{ route('exercises.index') }}">← Exercise Library</a></nav>

    <div class="detail-layout">
        <div class="stack">
            <div data-stage="{{ $exercise->stage->value }}">
                <span class="stage-pill"><span class="stage-dot"></span> Stage {{ $exercise->stage->number() }} · {{ $exercise->stage->label() }}</span>
                <h1 style="margin-top:8px;text-transform:uppercase">{{ $exercise->name }}</h1>
                <p class="muted" dir="rtl" lang="ar" style="text-align:left;font-size:1.1rem">{{ $exercise->name_ar }}</p>
            </div>

            <div class="ex-anim ex-anim-lg" data-stage="{{ $exercise->stage->value }}" data-animation="{{ $exercise->animationPattern() }}" role="img" aria-label="Animated demonstration of {{ $exercise->name }}"></div>
            <p class="small muted">Looping animated demonstration — follow the written technique below.</p>

            <div class="card">
                <h2>How to perform</h2>
                <ol>
                    @foreach ($exercise->instructions as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
            </div>

            <div class="grid grid-md-2">
                <div class="card">
                    <h3>Common mistakes</h3>
                    <ul class="small">
                        @foreach ($exercise->mistakes as $mistake)
                            <li>{{ $mistake }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="card">
                    <h3>Safety notes</h3>
                    <ul class="small">
                        @foreach ($exercise->safety as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <aside class="stack">
            <div class="card stack" style="--stack-gap:14px">
                <dl class="ex-meta">
                    <div class="full"><dt>Purpose</dt><dd>{{ $exercise->purpose }}</dd></div>
                    @if ($exercise->isWorkout() && $exercise->sets)
                        <div><dt>Sets</dt><dd>{{ $exercise->sets['sets'] }} sets</dd></div>
                        <div><dt>Reps</dt><dd>{{ $exercise->sets['reps'] }}</dd></div>
                        <div class="full"><dt>Rest between sets</dt><dd>{{ $exercise->sets['rest'] }}</dd></div>
                    @else
                        <div><dt>Duration</dt><dd>{{ $exercise->duration_label }}</dd></div>
                        <div><dt>Repetitions</dt><dd>{{ $exercise->reps_label }}</dd></div>
                    @endif
                    <div><dt>Difficulty</dt><dd>{{ $exercise->difficulty }}</dd></div>
                    <div><dt>Impact</dt><dd>{{ $exercise->impact->value }}</dd></div>
                    <div class="full"><dt>Target muscles</dt><dd>{{ implode(' / ', $exercise->target_muscles) }}</dd></div>
                    @if ($exercise->target_joints)
                        <div class="full"><dt>Target joints</dt><dd>{{ implode(' / ', $exercise->target_joints) }}</dd></div>
                    @endif
                    <div class="full"><dt>Section</dt><dd>{{ $exercise->isWorkout() ? 'Workout' : 'Warm-up' }}</dd></div>
                    <div class="full"><dt>Training split</dt><dd>{{ collect($exercise->activities)->map(fn ($a) => \App\Enums\Activity::from($a)->shortLabel())->implode(', ') }}</dd></div>
                    @if ($exercise->equipment)
                        <div class="full"><dt>Equipment</dt><dd>{{ collect($exercise->equipment)->map(fn ($e) => config("warmup.equipment.$e"))->implode(', ') }}</dd></div>
                    @endif
                </dl>
                <div class="tag-list">
                    @foreach ($exercise->tags as $tag)
                        <a class="tag" href="{{ route('exercises.index', ['tag' => $tag->slug]) }}">{{ $tag->name }}</a>
                    @endforeach
                </div>
                <button type="button" class="btn btn-outline btn-block" data-animation-payload="{{ json_encode($exercise->animationPayload()) }}"><x-icon name="activity" /> View animation</button>
            </div>

            <div class="card">
                <h3>Why it is included</h3>
                <p class="small">{{ $exercise->why }}</p>
                <h3 style="margin-top:14px">Progression</h3>
                <p class="small">{{ $exercise->progression }}</p>
                <h3 style="margin-top:14px">Regression</h3>
                <p class="small">{{ $exercise->regression }}</p>
            </div>

            @if ($related->isNotEmpty())
                <div class="card">
                    <h3>More in {{ $exercise->category }}</h3>
                    <ul class="small">
                        @foreach ($related as $item)
                            <li><a href="{{ route('exercises.show', $item) }}">{{ $item->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>
    </div>

    <nav class="pager" aria-label="Exercise navigation">
        @if ($previous)
            <a class="btn btn-outline btn-sm" href="{{ route('exercises.show', $previous) }}"><x-icon name="chevron-left" /> {{ $previous->name }}</a>
        @else
            <span></span>
        @endif
        @if ($next)
            <a class="btn btn-outline btn-sm" href="{{ route('exercises.show', $next) }}">{{ $next->name }} <x-icon name="chevron-right" /></a>
        @endif
    </nav>
</x-layout>
