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
        <div class="section-head">
            <h2>Exercise demonstration videos</h2>
            <span class="muted small">{{ $exercises->filter->hasVideo()->count() }} of {{ $exercises->count() }} curated</span>
        </div>
        <p class="small muted">Links were taken from search results (never constructed) and cross-checked where possible. “Please review” marks links seen in a single search only; “Not curated yet” means no verified video exists yet and the app offers a YouTube search instead. Verify all links from a connected machine with <code>php artisan videos:verify</code>.</p>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Exercise</th><th>Video</th><th>Source</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($exercises as $exercise)
                        <tr>
                            <td><a href="{{ route('exercises.show', $exercise) }}">{{ $exercise->name }}</a></td>
                            <td>
                                @if ($exercise->hasVideo())
                                    <a href="{{ $exercise->video_url }}" target="_blank" rel="noopener">{{ $exercise->video_title }}</a>
                                @else
                                    <a href="{{ $exercise->videoSearchUrl() }}" target="_blank" rel="noopener" class="muted">YouTube search</a>
                                @endif
                            </td>
                            <td>{{ $exercise->video_channel ?? '—' }}</td>
                            <td>
                                @if (! $exercise->hasVideo())
                                    <span class="badge">Not curated yet</span>
                                @elseif ($exercise->video_verification === 'confirmed')
                                    <span class="badge badge-ok">Cross-checked</span>
                                @else
                                    <span class="badge badge-warn">Please review</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-layout>
