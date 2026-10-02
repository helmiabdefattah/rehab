@props(['exercise', 'open' => false, 'thumb' => true])
<article {{ $attributes->class('ex-card') }} data-stage="{{ $exercise->stage->value }}" id="ex-{{ $exercise->slug }}">
    @if ($thumb)
        <x-exercise-thumb :exercise="$exercise" />
    @endif

    <div class="ex-body">
        <div class="ex-title">
            <span class="stage-dot" style="margin-top:7px"></span>
            <div>
                <h3><a href="{{ route('exercises.show', $exercise) }}">{{ $exercise->name }}</a></h3>
                <span class="ar" dir="rtl" lang="ar">{{ $exercise->name_ar }}</span>
            </div>
        </div>

        <dl class="ex-meta">
            <div class="full"><dt>Purpose</dt><dd>{{ $exercise->purpose }}</dd></div>
            @if ($exercise->isWorkout() && $exercise->sets)
                <div><dt>Sets</dt><dd>{{ $exercise->sets['sets'] }} × {{ $exercise->sets['reps'] }}</dd></div>
                <div><dt>Rest</dt><dd>{{ $exercise->sets['rest'] }}</dd></div>
            @else
                <div><dt>Duration</dt><dd>{{ $exercise->duration_label }}</dd></div>
                <div><dt>Reps</dt><dd>{{ $exercise->reps_label }}</dd></div>
            @endif
            <div class="full"><dt>Target</dt><dd>{{ implode(' / ', $exercise->target_muscles) }}</dd></div>
            <div><dt>Difficulty</dt><dd>{{ $exercise->difficulty }}</dd></div>
            <div><dt>Impact</dt><dd>{{ $exercise->impact->value }}</dd></div>
        </dl>

        <div class="tag-list">
            @foreach ($exercise->tags as $tag)
                <a class="tag" href="{{ route('exercises.index', ['tag' => $tag->slug]) }}">{{ $tag->name }}</a>
            @endforeach
        </div>

        <details @if ($open) open @endif>
            <summary>Why it’s included &amp; how to perform</summary>
            <div class="stack" style="--stack-gap:14px">
                <div class="ex-section">
                    <h4>Why it is included</h4>
                    <p>{{ $exercise->why }}</p>
                </div>
                <div class="ex-section">
                    <h4>How to perform</h4>
                    <ol>
                        @foreach ($exercise->instructions as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ol>
                </div>
                <div class="ex-section">
                    <h4>Common mistakes</h4>
                    <ul>
                        @foreach ($exercise->mistakes as $mistake)
                            <li>{{ $mistake }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="ex-section">
                    <h4>Safety notes</h4>
                    <ul>
                        @foreach ($exercise->safety as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="ex-section">
                    <h4>Progression / Regression</h4>
                    <p class="small"><strong>↑</strong> {{ $exercise->progression }}<br><strong>↓</strong> {{ $exercise->regression }}</p>
                </div>
            </div>
        </details>

        <div class="ex-actions">
            <button type="button" class="btn btn-ghost btn-sm" data-animation-payload="{{ json_encode($exercise->animationPayload()) }}"><x-icon name="activity" /> View animation</button>
        </div>
    </div>
</article>
