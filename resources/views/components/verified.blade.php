@props(['store', 'class' => 'size-5'])
@if ($store->verification_level?->isVerified())
    <span class="inline-flex text-green" title="{{ $store->verification_level->label() }}">{!! icon('badge-check', $class) !!}<span class="sr-only">{{ $store->verification_level->label() }}</span></span>
@endif
