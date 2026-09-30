@props(['exercise', 'size' => null])
@if ($exercise->hasVideo())
    <button type="button" {{ $attributes->class(['btn btn-video', 'btn-sm' => $size === 'sm']) }} data-video="{{ json_encode($exercise->videoPayload()) }}">
        <x-icon name="play" fill /> Watch Video
    </button>
@else
    <button type="button" {{ $attributes->class(['btn btn-video is-search', 'btn-sm' => $size === 'sm']) }} data-video="{{ json_encode($exercise->videoPayload()) }}">
        <x-icon name="search" /> Find a Video
    </button>
@endif
