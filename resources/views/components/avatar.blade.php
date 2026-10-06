@props(['store', 'size' => 'size-12', 'text' => 'text-base'])
@if ($store->avatarUrl())
    <img src="{{ $store->avatarUrl() }}" alt="{{ $store->display_name }}" {{ $attributes->class([$size, 'shrink-0 rounded-full object-cover ring-1 ring-line dark:ring-white/10']) }}>
@else
    <span {{ $attributes->class([$size, $text, 'grid shrink-0 place-items-center rounded-full bg-navy font-display font-semibold text-white dark:bg-white dark:text-navy']) }} aria-hidden="true">{{ $store->initials() }}</span>
@endif
