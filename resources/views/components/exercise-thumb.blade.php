@props(['exercise'])
@php
    $stageIcon = ['heat' => 'flame', 'mobility' => 'rotate', 'activation' => 'target', 'dynamic' => 'activity'][$exercise->stage->value];
@endphp
<button type="button" {{ $attributes->class('ex-thumb') }} data-video="{{ json_encode($exercise->videoPayload()) }}" aria-label="{{ $exercise->hasVideo() ? 'Watch video' : 'Find a video' }}: {{ $exercise->name }}">
    <span class="ex-thumb-fallback">
        <x-icon :name="$stageIcon" />
        <span>{{ $exercise->category }}</span>
    </span>
    @if ($exercise->thumbnailUrl())
        <img src="{{ $exercise->thumbnailUrl() }}" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer" onerror="this.remove()">
        <span class="play-badge"><x-icon name="play" fill /></span>
    @endif
</button>
