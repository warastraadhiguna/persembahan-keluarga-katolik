@props(['active' => false])

@php
$classes = $active
    ? 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium bg-white/20 text-white'
    : 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-primary-100 hover:bg-white/10 hover:text-white transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @isset($icon)
        <span class="shrink-0">{{ $icon }}</span>
    @endisset
    <span>{{ $slot }}</span>
</a>
