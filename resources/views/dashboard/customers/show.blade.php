@extends('layouts.dashboard')
@section('page_title', $customer->name)
@section('page_subtitle', 'Customer since '.$customer->created_at->format('j M Y'))
@section('back', route('dashboard.customers.index'))

@section('content')
<div class="grid gap-4 sm:gap-6 lg:grid-cols-[1fr_320px]">
    <section class="card overflow-hidden lg:order-1">
        <h2 class="p-5 pb-3 text-base font-semibold sm:px-6">Orders</h2>
        @foreach ($orders as $order)
            <a href="{{ route('dashboard.orders.show', $order) }}" class="flex items-center gap-4 border-t border-line px-5 py-3.5 hover:bg-ice/60 sm:px-6 dark:border-white/[0.06] dark:hover:bg-white/[0.03]">
                <div class="min-w-0 flex-1">
                    <p class="truncate font-display text-sm font-medium">{{ $order->items->first()?->product_name }}@if ($order->items->count() > 1) +{{ $order->items->count() - 1 }}@endif</p>
                    <p class="truncate text-xs muted">{{ $order->number }} · {{ $order->created_at->format('j M Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="font-display text-sm font-semibold">{{ $order->formattedTotal() }}</p>
                    <x-status-badge :status="$order->payment_status" class="mt-1" />
                </div>
            </a>
        @endforeach
    </section>

    <aside class="card card-pad space-y-5 lg:order-2 lg:self-start">
        <div>
            <p class="text-[13px] muted">Paid in total</p>
            <p class="mt-1 font-display text-2xl font-semibold">{{ money($paidTotal, $store->currency) }}</p>
            <p class="text-xs muted">{{ $orders->count() }} {{ $orders->count() === 1 ? 'order' : 'orders' }}</p>
        </div>
        <dl class="divider space-y-3 pt-5 text-sm">
            <div><dt class="muted">Email</dt><dd class="mt-0.5 font-medium break-all"><a href="mailto:{{ $customer->email }}" class="link">{{ $customer->email }}</a></dd></div>
            <div><dt class="muted">Phone</dt><dd class="mt-0.5 font-medium">{{ $customer->phone ?: '—' }}</dd></div>
        </dl>
        @php $wa = \App\Models\Store::normaliseWhatsapp($customer->phone); @endphp
        @if ($wa)
            <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="btn btn-secondary w-full">{{ icon('whatsapp', 'size-4') }} Message on WhatsApp</a>
        @endif
    </aside>
</div>
@endsection
