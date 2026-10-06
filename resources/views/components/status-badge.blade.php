@props(['status'])
@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $tone = match ($value) {
        'paid', 'completed', 'shipped' => 'badge-green',
        'pending', 'processing' => 'badge-amber',
        'failed', 'cancelled', 'refunded' => 'badge-red',
        default => 'badge-neutral',
    };
@endphp
<span {{ $attributes->class(['badge', $tone]) }}>{{ ucfirst($value) }}</span>
