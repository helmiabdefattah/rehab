<x-layout :title="$exercise->name" page="exercise">
    <nav class="small" style="margin-bottom:12px"><a href="{{ route('exercises.index') }}">← Exercise Library</a></nav>

    <div class="detail-layout">
        <div class="stack">
            <div data-stage="{{ $exercise->stage->value }}">
                <span class="stage-pill"><span class="stage-dot"></span> Stage {{ $exercise->stage->number() }} · {{ $exercise->stage->label() }}</span>
                <h1 style="margin-top:8px;text-transform:uppercase">{{ $exercise->name }}</h1>
                <p class="muted" dir="rtl" lang="ar" style="text-align:left;font-size:1.1rem">{{ $exercise->name_ar }}</p>
            </div>

            @if ($exercise->hasVideo())
                <div class="video-frame">
                    <iframe src="{{ $exercise->embedUrl() }}" title="{{ $exercise->video_title }}" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
                <p class="video-source">
                    Video: “{{ $exercise->video_title }}”@if ($exercise->video_channel) · {{ $exercise->video_channel }}@endif
                    · <a href="{{ $exercise->video_url }}" target="_blank" rel="noopener">Open on YouTube</a>
                    @if ($exercise->video_verification === 'single')
                        · <span class="badge badge-warn">Please review</span>
                    @endif
                </p>
                @if ($exercise->video_note)
                    <p class="small muted">{{ $exercise->video_note }}</p>
                @endif
            @else
                <x-exercise-thumb :exercise="$exercise" style="border-radius:16px" data-stage="{{ $exercise->stage->value }}" />
                <div class="callout callout-info">
                    <x-icon name="info" />
                    <div>
                        <p class="small" style="margin:0">A verified demonstration video hasn’t been added for this exercise yet — no link has been invented. Follow the written technique, or <a href="{{ $exercise->videoSearchUrl() }}" target="_blank" rel="noopener">search YouTube</a> (prefer physiotherapy or sports-medicine channels).</p>
                    </div>
                </div>
            @endif

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
                    <div><dt>Duration</dt><dd>{{ $exercise->duration_label }}</dd></div>
                    <div><dt>Repetitions</dt><dd>{{ $exercise->reps_label }}</dd></div>
                    <div><dt>Difficulty</dt><dd>{{ $exercise->difficulty }}</dd></div>
                    <div><dt>Impact</dt><dd>{{ $exercise->impact->value }}</dd></div>
                    <div class="full"><dt>Target muscles</dt><dd>{{ implode(' / ', $exercise->target_muscles) }}</dd></div>
                    <div class="full"><dt>Target joints</dt><dd>{{ implode(' / ', $exercise->target_joints) }}</dd></div>
                    <div class="full"><dt>Category</dt><dd>{{ $exercise->category }}</dd></div>
                    <div class="full"><dt>Suitable activities</dt><dd>{{ collect($exercise->activities)->map(fn ($a) => \App\Enums\Activity::from($a)->label())->implode(', ') }}</dd></div>
                    <div class="full"><dt>Minimum level</dt><dd>Level {{ $exercise->min_level }} · {{ \App\Enums\Level::from($exercise->min_level)->label() }}</dd></div>
                    @if ($exercise->equipment)
                        <div class="full"><dt>Equipment</dt><dd>{{ collect($exercise->equipment)->map(fn ($e) => config("warmup.equipment.$e"))->implode(', ') }}</dd></div>
                    @endif
                </dl>
                <div class="tag-list">
                    @foreach ($exercise->tags as $tag)
                        <a class="tag" href="{{ route('exercises.index', ['tag' => $tag->slug]) }}">{{ $tag->name }}</a>
                    @endforeach
                </div>
                <x-video-button :exercise="$exercise" class="btn-block" />
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
