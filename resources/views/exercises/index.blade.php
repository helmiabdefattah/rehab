<x-layout title="Exercise Library" page="library">
    <header class="page-head">
        <span class="eyebrow">Exercise Library</span>
        <h1>{{ $exercises->count() }} warm-up exercises</h1>
        <p>Every exercise includes purpose, technique, common mistakes, safety notes, progressions and a demonstration video.</p>
    </header>

    <form method="get" action="{{ route('exercises.index') }}" class="stack" data-library-filters style="--stack-gap:12px">
        <div class="search-input">
            <x-icon name="search" />
            <label for="q" class="sr-only">Search exercises</label>
            <input id="q" name="q" type="search" class="input" placeholder="Search exercises, muscles…" value="{{ $filters['q'] }}" autocomplete="off">
        </div>
        <div class="filters-grid">
            <div class="field">
                <label for="tag" class="small">Tag</label>
                <select id="tag" name="tag" class="input">
                    <option value="">All tags</option>
                    @foreach ($tags as $tag)
                        <option value="{{ $tag->slug }}" @selected($filters['tag'] === $tag->slug)>{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="stage" class="small">Stage</label>
                <select id="stage" name="stage" class="input">
                    <option value="">All stages</option>
                    @foreach ($stages as $stage)
                        <option value="{{ $stage->value }}" @selected($filters['stage'] === $stage->value)>{{ $stage->number() }}. {{ $stage->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="activity" class="small">Suitable for</label>
                <select id="activity" name="activity" class="input">
                    <option value="">Any activity</option>
                    @foreach ($activities as $activity)
                        <option value="{{ $activity->value }}" @selected($filters['activity'] === $activity->value)>{{ $activity->emoji() }} {{ $activity->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <noscript><button class="btn btn-outline btn-sm" type="submit">Apply filters</button></noscript>
        <div class="row">
            <span class="muted small" data-library-count>{{ $visible->count() }} of {{ $exercises->count() }} shown</span>
            <span class="spacer"></span>
            <a href="{{ route('exercises.index') }}" class="btn btn-ghost btn-sm" data-library-reset>Reset</a>
        </div>
    </form>

    <div class="card-grid section" data-library-grid>
        @foreach ($exercises as $exercise)
            <x-exercise-card
                :exercise="$exercise"
                :hidden="! $visible->has($exercise->id)"
                data-search="{{ mb_strtolower(implode(' ', [$exercise->name, $exercise->name_ar, $exercise->category, $exercise->purpose, implode(' ', $exercise->target_muscles), $exercise->tags->pluck('name')->implode(' ')])) }}"
                data-tags="{{ $exercise->tags->pluck('slug')->implode(' ') }}"
                data-stage-key="{{ $exercise->stage->value }}"
                data-activities="{{ implode(' ', $exercise->activities) }}"
            />
        @endforeach
    </div>

    <div class="empty" data-library-empty @if ($visible->isNotEmpty()) hidden @endif>
        <div class="big">🔍</div>
        <p>No exercises match these filters.</p>
    </div>
</x-layout>
