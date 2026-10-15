@extends('layouts.storefront')
@section('title', 'Order '.$order->number.' · Kustore')
@section('noindex', true)
@section('header_left') @include('storefront.partials.back') @endsection

@section('content')
<div class="mx-auto max-w-xl px-4 pt-4 sm:px-6">
    <div class="text-center">
        <span class="mx-auto grid size-14 place-items-center rounded-full bg-green text-ink">{{ icon('check', 'size-7') }}</span>
        <h1 class="mt-5 text-3xl font-semibold">Order placed</h1>
        <p class="mt-2 muted">Thank you, {{ str($order->customer_name)->before(' ') }}. {{ $store->display_name }} has received your order.</p>
    </div>

    <div class="card card-pad mt-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs muted">Order number</p>
                <p class="font-display text-xl font-semibold tracking-wide">{{ $order->number }}</p>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" data-copy="{{ $order->number }}">{{ icon('copy', 'size-4') }}<span data-copy-label>Copy</span></button>
        </div>
        <div class="mt-4 flex items-center gap-2 text-sm"><x-status-badge :status="$order->payment_status" /> <span class="muted">Waiting for your payment</span></div>
    </div>

    <div class="card card-pad mt-4">
        <h2 class="flex items-center gap-2 text-base font-semibold">{{ icon('credit-card', 'size-[18px]') }} How to pay</h2>
        <div class="mt-3 rounded-2xl bg-ice p-4 text-[15px] leading-relaxed whitespace-pre-line dark:bg-white/5">{{ $instructions ?: 'The seller will contact you with payment details.' }}</div>
        @if ($store->qrisUrl())
            <div class="mt-4 rounded-2xl border border-line p-4 text-center dark:border-white/[0.07]">
                <p class="flex items-center justify-center gap-2 font-display text-sm font-semibold">{{ icon('qr-code', 'size-4') }} Scan to pay with QRIS</p>
                <img src="{{ $store->qrisUrl() }}" alt="QRIS code for {{ $store->display_name }}" class="mx-auto mt-3 aspect-square w-full max-w-[260px] rounded-xl bg-white object-contain">
                <p class="mt-2 text-xs muted">Pay exactly {{ $order->formattedTotal() }}.</p>
            </div>
        @endif
        <p class="mt-3 text-sm muted">Include your order number <strong class="text-navy dark:text-white">{{ $order->number }}</strong> with your payment. The seller confirms payments manually.</p>
        @if ($wa = $store->whatsappUrl('Hi '.$store->display_name.', here is my payment proof for order '.$order->number.' ('.$order->formattedTotal().').'))
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn btn-accent mt-4 w-full">{{ icon('whatsapp', 'size-4') }} Send payment proof on WhatsApp</a>
        @endif
        <p class="mt-3 text-xs muted">We've also emailed these details to {{ $order->customer_email }}.</p>
    </div>

    <div class="card mt-4">
        @foreach ($order->items as $item)
            <div class="flex justify-between gap-4 p-5 text-sm">
                <span><span class="font-display font-medium">{{ $item->product_name }}</span> <span class="muted">× {{ $item->quantity }}</span></span>
                <span class="font-display font-medium">{{ money($item->line_total, $order->currency) }}</span>
            </div>
        @endforeach
        <div class="flex justify-between border-t border-line p-5 font-display font-semibold dark:border-white/[0.07]"><span>Total</span><span>{{ $order->formattedTotal() }}</span></div>
    </div>

    <div class="mt-8 flex justify-center"><a href="{{ $store->url() }}" class="btn btn-secondary">{{ icon('arrow-left', 'size-4') }} Back to {{ $store->display_name }}</a></div>
</div>
@endsection
