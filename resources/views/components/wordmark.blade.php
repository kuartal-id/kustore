@props(['href' => route('home'), 'size' => 'md', 'tagline' => true])
@php
    // Official Kustore logo (public/images/brand, 320x64 = 2x the largest display height).
    // Light logo in light mode, dark logo in dark mode, switched by the `.dark` class on <html> (public/js/theme.js).
    [$w, $h] = match ($size) { 'sm' => [110, 22], 'lg' => [160, 32], default => [140, 28] };
    $imgClass = match ($size) { 'sm' => 'h-[22px] w-[110px]', 'lg' => 'h-8 w-40', default => 'h-7 w-[140px]' };
@endphp
<a href="{{ $href }}" {{ $attributes->class(['group inline-flex shrink-0 items-end gap-2']) }} aria-label="Kustore by Kuartal, home">
    <img src="{{ asset_v('images/brand/kustore-logo-light.png') }}" alt="Kustore" width="{{ $w }}" height="{{ $h }}" class="{{ $imgClass }} max-w-none dark:hidden">
    <img src="{{ asset_v('images/brand/kustore-logo-dark.png') }}" alt="Kustore" width="{{ $w }}" height="{{ $h }}" class="{{ $imgClass }} hidden max-w-none dark:block">
    @if ($tagline)
        <span @class(['pb-px font-display font-medium leading-none text-muted dark:text-muted-dark', 'text-[11px]' => $size !== 'lg', 'text-xs' => $size === 'lg'])>by Kuartal</span>
    @endif
</a>
