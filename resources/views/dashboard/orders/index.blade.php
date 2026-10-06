@extends('layouts.dashboard')
@section('page_title', 'Orders')
@section('page_subtitle', 'Mark orders as paid once you receive the money.')

@section('content')
<div class="-mx-4 mb-5 flex gap-2 overflow-x-auto px-4 pb-1 sm:mx-0 sm:px-0">
    <a href="{{ route('dashboard.orders.index') }}" class="btn btn-sm {{ $status ? 'btn-secondary' : 'btn-primary' }}">All</a>
    @foreach (\App\Enums\PaymentStatus::cases() as $case)
        <a href="{{ route('dashboard.orders.index', ['status' => $case->value]) }}" class="btn btn-sm {{ $status === $case->value ? 'btn-primary' : 'btn-secondary' }}">{{ $case->label() }}</a>
    @endforeach
</div>

<div class="card overflow-hidden">
    @forelse ($orders as $order)
        <a href="{{ route('dashboard.orders.show', $order) }}" class="flex items-center gap-4 border-line px-4 py-4 hover:bg-ice/60 sm:px-5 dark:border-white/[0.06] dark:hover:bg-white/[0.03] {{ $loop->first ? '' : 'border-t' }}">
            <div class="min-w-0 flex-1">
                <p class="truncate font-display text-sm font-medium">{{ $order->customer_name }}</p>
                <p class="truncate text-xs muted">{{ $order->number }} · {{ $order->items->first()?->product_name }}@if ($order->items->count() > 1) +{{ $order->items->count() - 1 }}@endif</p>
                <p class="text-xs muted">{{ $order->created_at->format('j M Y, H:i') }}</p>
            </div>
            <div class="flex flex-col items-end gap-1">
                <p class="font-display text-sm font-semibold">{{ $order->formattedTotal() }}</p>
                <div class="flex gap-1"><x-status-badge :status="$order->payment_status" /><x-status-badge :status="$order->fulfillment_status" class="hidden sm:inline-flex" /></div>
            </div>
        </a>
    @empty
        <div class="px-6 py-16 text-center">
            <span class="mx-auto grid size-11 place-items-center rounded-2xl bg-ice text-navy dark:bg-white/5 dark:text-white">{{ icon('receipt') }}</span>
            <p class="mt-4 font-display font-medium">No orders {{ $status ? 'with this status' : 'yet' }}</p>
            <p class="mt-1 text-sm muted">New orders appear here as soon as a customer checks out.</p>
        </div>
    @endforelse
</div>
<div class="mt-6">{{ $orders->links('partials.pager') }}</div>
@endsection
