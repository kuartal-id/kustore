@extends('layouts.dashboard')
@section('page_title', 'Order '.$order->number)
@section('page_subtitle', 'Placed '.$order->created_at->format('j M Y, H:i'))
@section('back', route('dashboard.orders.index'))

@section('content')
<div class="grid gap-4 sm:gap-6 lg:grid-cols-[1fr_320px]">
    <div class="space-y-4 sm:space-y-6">
        <section class="card">
            <h2 class="p-5 pb-3 text-base font-semibold sm:px-6">Items</h2>
            @foreach ($order->items as $item)
                <div class="flex items-center justify-between gap-4 border-t border-line px-5 py-3.5 sm:px-6 dark:border-white/[0.06]">
                    <div class="min-w-0">
                        <p class="truncate font-display text-sm font-medium">{{ $item->product_name }}</p>
                        <p class="text-xs muted">{{ ucfirst($item->product_type) }} · {{ $item->quantity }} × {{ money($item->unit_price, $order->currency) }}</p>
                    </div>
                    <p class="font-display text-sm font-semibold">{{ money($item->line_total, $order->currency) }}</p>
                </div>
            @endforeach
            <div class="space-y-1.5 border-t border-line px-5 py-4 text-sm sm:px-6 dark:border-white/[0.06]">
                <div class="flex justify-between muted"><span>Subtotal</span><span>{{ money($order->subtotal, $order->currency) }}</span></div>
                <div class="flex justify-between muted"><span>Shipping</span><span>{{ $order->shipping_total ? money($order->shipping_total, $order->currency) : 'Arranged with customer' }}</span></div>
                <div class="flex justify-between pt-1 font-display text-base font-semibold"><span>Total</span><span>{{ $order->formattedTotal() }}</span></div>
            </div>
        </section>

        <section class="card card-pad">
            <h2 class="text-base font-semibold">Customer</h2>
            <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                <div><dt class="muted">Name</dt><dd class="mt-0.5 font-medium">{{ $order->customer_name }}</dd></div>
                <div><dt class="muted">Email</dt><dd class="mt-0.5 font-medium break-all"><a href="mailto:{{ $order->customer_email }}" class="link">{{ $order->customer_email }}</a></dd></div>
                <div><dt class="muted">Phone</dt><dd class="mt-0.5 font-medium">
                    @if ($order->customer_phone)
                        {{ $order->customer_phone }}
                        @php $wa = preg_replace('/\D/', '', preg_replace('/^0/', '62', $order->customer_phone)); @endphp
                        <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="link ml-1">WhatsApp</a>
                    @else — @endif
                </dd></div>
                @if ($order->shipping_address)
                    <div class="sm:col-span-2"><dt class="muted">Shipping address</dt><dd class="mt-0.5 font-medium whitespace-pre-line">{{ $order->shipping_address }}</dd></div>
                @endif
                @if ($order->notes)
                    <div class="sm:col-span-2"><dt class="muted">Notes</dt><dd class="mt-0.5 whitespace-pre-line">{{ $order->notes }}</dd></div>
                @endif
            </dl>
        </section>
    </div>

    <form method="POST" action="{{ route('dashboard.orders.update', $order) }}" class="card card-pad space-y-5 lg:self-start">
        @csrf @method('PATCH')
        <h2 class="text-base font-semibold">Status</h2>
        <div>
            <label for="payment_status" class="label">Payment</label>
            <select id="payment_status" name="payment_status" class="input">
                @foreach (\App\Enums\PaymentStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($order->payment_status === $case)>{{ $case->label() }}</option>
                @endforeach
            </select>
            <p class="help">Via {{ $order->payment_provider === 'manual' ? 'manual payment' : $order->payment_provider }}@if ($order->paid_at) · paid {{ $order->paid_at->format('j M Y') }}@endif</p>
        </div>
        <div>
            <label for="fulfillment_status" class="label">Fulfillment</label>
            <select id="fulfillment_status" name="fulfillment_status" class="input">
                @foreach (\App\Enums\FulfillmentStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected($order->fulfillment_status === $case)>{{ $case->label() }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary w-full">Update order</button>
    </form>
</div>
@endsection
