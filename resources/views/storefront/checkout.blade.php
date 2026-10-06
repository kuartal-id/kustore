@extends('layouts.storefront')
@section('title', 'Checkout · '.$product->name.' · Kustore')
@section('noindex', true)
@section('header_left') @include('storefront.partials.back') @endsection

@section('content')
@php $unit = $product->effectivePrice(); @endphp
<div class="mx-auto max-w-5xl px-4 pt-2 sm:px-6">
    <h1 class="text-2xl font-semibold sm:text-3xl">Checkout</h1>
    <p class="mt-1 muted">You are ordering from {{ $store->display_name }}.</p>

    <form method="POST" action="{{ route('checkout.store', [$store->username, $product->slug]) }}" class="mt-6 grid gap-5 sm:mt-8 lg:grid-cols-[1fr_380px] lg:gap-8" novalidate>
        @csrf
        {{-- Summary (first on mobile) --}}
        <aside class="lg:order-2 lg:self-start lg:sticky lg:top-6">
            <div class="card card-pad">
                <h2 class="text-base font-semibold">Order summary</h2>
                <div class="mt-4 flex gap-4">
                    @if ($product->imageUrl())
                        <img src="{{ $product->imageUrl() }}" alt="" class="size-20 shrink-0 rounded-2xl object-cover">
                    @else
                        <span class="grid size-20 shrink-0 place-items-center rounded-2xl bg-ice text-navy/30 dark:bg-white/5 dark:text-white/25">{{ icon('shopping-bag', 'size-7') }}</span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="font-display font-medium leading-snug">{{ $product->name }}</p>
                        <p class="mt-1 text-sm muted">{{ $product->formattedEffectivePrice() }} × <span data-qty-label>{{ $quantity }}</span></p>
                    </div>
                </div>
                <div class="mt-5">
                    <label for="quantity" class="label">Quantity</label>
                    <input id="quantity" name="quantity" type="number" min="1" max="{{ $product->maxPurchasable() }}" value="{{ old('quantity', $quantity) }}" inputmode="numeric" class="input w-28" data-qty data-unit="{{ $unit }}">
                    <x-field-error name="quantity" />
                    @unless ($product->unlimited_stock)<p class="help">{{ $product->stock_quantity }} available</p>@endunless
                </div>
                <div class="mt-5 space-y-1.5 border-t border-line pt-4 text-sm dark:border-white/[0.07]">
                    <div class="flex justify-between muted"><span>Subtotal</span><span data-total>{{ money($unit * $quantity, $product->currency) }}</span></div>
                    @if ($product->requires_shipping)<div class="flex justify-between muted"><span>Shipping</span><span>Arranged by seller</span></div>@endif
                    <div class="flex justify-between pt-1 font-display text-lg font-semibold"><span>Total</span><span data-total>{{ money($unit * $quantity, $product->currency) }}</span></div>
                </div>
                <div class="mt-5 rounded-2xl bg-ice p-4 text-sm dark:bg-white/5">
                    <p class="flex items-center gap-2 font-display font-medium">{{ icon('credit-card', 'size-4') }} Manual payment</p>
                    <p class="mt-1 muted">After you place the order you will see how to pay {{ $store->display_name }} directly (bank transfer, e-wallet or QRIS).</p>
                </div>
            </div>
        </aside>

        {{-- Details --}}
        <div class="space-y-5 lg:order-1">
            <x-flash :fields-only="false" />
            <section class="card card-pad">
                <h2 class="text-base font-semibold">Your details</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="label">Full name</label>
                        <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="100" class="input">
                        <x-field-error name="name" />
                    </div>
                    <div>
                        <label for="email" class="label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="input">
                        <x-field-error name="email" />
                    </div>
                    <div>
                        <label for="phone" class="label">Phone / WhatsApp</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" required class="input" placeholder="08xx xxxx xxxx">
                        <x-field-error name="phone" />
                    </div>
                </div>
            </section>

            @if ($product->requires_shipping)
                <section class="card card-pad">
                    <h2 class="text-base font-semibold">Shipping address</h2>
                    <textarea id="shipping_address" name="shipping_address" rows="4" maxlength="1000" required class="input mt-4" autocomplete="street-address" placeholder="Street, number, RT/RW, kelurahan, kecamatan, city, postal code">{{ old('shipping_address') }}</textarea>
                    <x-field-error name="shipping_address" />
                </section>
            @endif

            <section class="card card-pad">
                <label for="notes" class="text-base font-semibold font-display">Notes for the seller <span class="text-sm font-normal muted">(optional)</span></label>
                <textarea id="notes" name="notes" rows="3" maxlength="1000" class="input mt-4" placeholder="Colour, size, preferred delivery time…">{{ old('notes') }}</textarea>
                <x-field-error name="notes" />
            </section>

            <button class="btn btn-primary btn-lg w-full">Place order · <span data-total>{{ money($unit * $quantity, $product->currency) }}</span></button>
            <p class="text-center text-xs muted">By placing this order you share your details with {{ $store->display_name }} so they can complete it. See our <a href="{{ route('privacy') }}" class="link">Privacy Policy</a>.</p>
        </div>
    </form>
</div>
@endsection
