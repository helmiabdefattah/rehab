@props(['exercise'])
<button type="button" {{ $attributes->class('ex-anim') }}
    data-stage="{{ $exercise->stage->value }}"
    data-animation="{{ $exercise->animationPattern() }}"
    data-animation-payload="{{ json_encode($exercise->animationPayload()) }}"
    aria-label="View animation: {{ $exercise->name }}">
</button>
