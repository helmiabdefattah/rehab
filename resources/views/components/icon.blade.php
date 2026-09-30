@props(['name', 'fill' => false])
<svg {{ $attributes->class(['icon', 'icon-fill' => $fill]) }} aria-hidden="true" focusable="false"><use href="#i-{{ $name }}"></use></svg>
