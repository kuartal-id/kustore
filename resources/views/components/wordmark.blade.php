@props(['href' => route('home'), 'size' => 'md'])
{{-- Placeholder wordmark: no official Kustore logo file exists yet. --}}
<a href="{{ $href }}" {{ $attributes->class(['group inline-flex items-baseline gap-1.5 text-navy dark:text-white']) }} aria-label="Kustore by Kuartal, home">
    <span @class(['font-display font-semibold tracking-[-0.03em]', 'text-[22px]' => $size === 'md', 'text-lg' => $size === 'sm', 'text-3xl' => $size === 'lg'])>Kustore<span class="text-green">.</span></span>
    <span @class(['font-display font-medium text-muted dark:text-muted-dark', 'text-[11px]' => $size !== 'lg', 'text-xs' => $size === 'lg'])>by Kuartal</span>
</a>
