<x-layout title="Sources & Evidence" page="sources">
    <header class="page-head">
        <span class="eyebrow">Sources &amp; Evidence</span>
        <h1>Sources &amp; exercise references</h1>
        <p>References used for exercise technique, warm-up principles, sports preparation and injury-aware exercise considerations. Study findings describe the populations studied — they are not guarantees for any individual.</p>
    </header>

    @foreach ($topics as $key => $label)
        @continue(! isset($sources[$key]))
        <section class="section card">
            <h2>{{ $label }}</h2>
            <ul class="source-list">
                @foreach ($sources[$key] as $source)
                    <li>
                        <div class="cite">{{ $source->citation }}</div>
                        <p class="small muted" style="margin:4px 0">{{ $source->relevance }}</p>
                        @if ($source->url)
                            <a href="{{ $source->url }}" target="_blank" rel="noopener" class="small">{{ $source->doi ? 'doi:'.$source->doi : $source->url }} ↗</a>
                        @else
                            <span class="small muted">Print / subscription reference (no public link)</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach

    <section class="section">
        <div class="callout callout-info">
            <x-icon name="info" />
            <div>
                <p class="small" style="margin:0">Every exercise now ships with a built-in looping animated demonstration instead of an external video, so the app works offline and no third-party links are needed. Open any <a href="{{ route('exercises.index') }}">exercise in the library</a> to see its animation and full technique.</p>
            </div>
        </div>
    </section>
</x-layout>
