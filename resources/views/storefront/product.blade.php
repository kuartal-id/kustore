@extends('layouts.storefront')
@php
    $title = $product->name.' · '.$store->display_name.' · Kustore';
    $description = $product->description ? str($product->description)->squish()->limit(155)->toString() : $product->name.' by '.$store->display_name.', '.$product->formattedEffectivePrice().'.';
    $canonical = route('storefront.product', [$store->username, $product->slug]);
    $available = $product->inStock();
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => $description,
        'url' => $canonical,
        'brand' => ['@type' => 'Brand', 'name' => $store->display_name],
        'offers' => [
            '@type' => 'Offer',
            'url' => $canonical,
            'priceCurrency' => $product->currency,
            'price' => (string) ($product->effectivePrice() / (10 ** config('kustore.currencies.'.$product->currency.'.exponent', 0))),
            'availability' => $available ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'seller' => ['@type' => 'Organization', 'name' => $store->display_name],
        ],
    ];
    if ($product->imageUrl()) { $ld['image'] = [$product->imageUrl()]; }
    if ($product->sku) { $ld['sku'] = $product->sku; }
@endphp
@section('title', $title)
@section('description', $description)
@section('canonical', $canonical)
@section('header_left') @include('storefront.partials.back') @endsection
@push('meta')
    @include('storefront.partials.seo', ['title' => $title, 'description' => $description, 'canonical' => $canonical, 'image' => $product->imageUrl(), 'type' => 'product'])
    <meta property="product:price:amount" content="{{ $ld['offers']['price'] }}">
    <meta property="product:price:currency" content="{{ $product->currency }}">
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@section('content')
<div class="mx-auto max-w-5xl px-4 pt-2 sm:px-6">
    <div class="grid gap-6 md:grid-cols-2 md:gap-10 lg:gap-14">
        <div class="self-start overflow-hidden rounded-[24px] border md:sticky md:top-6 border-line bg-white dark:border-white/[0.07] dark:bg-navy-900">
            @if ($product->imageUrl())
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
            @else
                <div class="grid aspect-square place-items-center bg-ice text-navy/25 dark:bg-white/5 dark:text-white/20">{{ icon('shopping-bag', 'size-14') }}</div>
            @endif
        </div>

        <div class="md:pt-2">
            <div class="flex flex-wrap gap-2">
                <span class="badge badge-neutral">{{ $product->type->label() }}</span>
                @if ($product->isOnSale())<span class="badge badge-navy">Sale</span>@endif
            </div>
            <h1 class="mt-4 text-[28px] leading-tight font-semibold text-balance sm:text-4xl">{{ $product->name }}</h1>
            <div class="mt-4 flex flex-wrap items-baseline gap-x-3">
                <span class="font-display text-2xl font-semibold">{{ $product->formattedEffectivePrice() }}</span>
                @if ($product->isOnSale())<span class="text-lg muted line-through">{{ $product->formattedPrice() }}</span>@endif
            </div>
            <p class="mt-2 flex items-center gap-2 text-sm {{ $available ? '' : 'text-danger dark:text-[#FF8A75]' }}">
                <span @class(['dot', 'bg-green' => $available, 'bg-danger' => ! $available])></span>{{ $product->availabilityLabel() }}
            </p>

            @if ($available)
                <form method="GET" action="{{ route('checkout.show', [$store->username, $product->slug]) }}" class="mt-7 flex items-end gap-3">
                    <div class="w-24">
                        <label for="quantity" class="label">Quantity</label>
                        <input id="quantity" name="quantity" type="number" min="1" max="{{ $product->maxPurchasable() }}" value="1" inputmode="numeric" class="input text-center">
                    </div>
                    <button class="btn btn-accent btn-lg flex-1">Buy now</button>
                </form>
            @else
                <span class="btn btn-secondary btn-lg mt-7 w-full opacity-60" aria-disabled="true">Sold out</span>
            @endif

            <ul class="mt-6 space-y-2 text-sm muted">
                @if ($product->requires_shipping)
                    <li class="flex items-center gap-2">{{ icon('truck', 'size-4') }} Shipped by the seller. Delivery is arranged after payment.</li>
                @elseif ($product->type->value === 'digital')
                    <li class="flex items-center gap-2">{{ icon('download', 'size-4') }} Delivered digitally by the seller after payment.</li>
                @else
                    <li class="flex items-center gap-2">{{ icon('calendar', 'size-4') }} The seller will contact you to schedule.</li>
                @endif
                <li class="flex items-center gap-2">{{ icon('credit-card', 'size-4') }} Pay the seller directly. Instructions after ordering.</li>
            </ul>

            @if ($product->description)
                <div class="mt-8 border-t border-line pt-6 dark:border-white/[0.07]">
                    <h2 class="text-base font-semibold">Description</h2>
                    <div class="mt-3 text-[15px] leading-relaxed whitespace-pre-line text-navy/85 dark:text-white/80">{{ $product->description }}</div>
                </div>
            @endif

            <a href="{{ $store->url() }}" class="card mt-8 flex items-center gap-4 p-4 hover:border-navy/30 dark:hover:border-white/20">
                <x-avatar :store="$store" size="size-12" text="text-sm" />
                <div class="min-w-0 flex-1">
                    <p class="text-xs muted">Sold by</p>
                    <p class="flex items-center gap-1 truncate font-display font-semibold">{{ $store->display_name }} <x-verified :store="$store" class="size-4" /></p>
                    <p class="truncate text-sm muted">{{ '@'.$store->username }}@if ($store->location) · {{ $store->location }}@endif</p>
                </div>
                {{ icon('chevron-right', 'size-5 text-muted') }}
            </a>
        </div>
    </div>

    @if ($others->isNotEmpty())
        <section class="mt-16" aria-labelledby="more-heading">
            <h2 id="more-heading" class="mb-5 text-xl font-semibold">More from {{ $store->display_name }}</h2>
            <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4">
                @foreach ($others as $product) @include('storefront.partials.product-card') @endforeach
            </div>
        </section>
    @endif

    <div class="mt-12">@include('storefront.partials.share', ['url' => $canonical, 'text' => $title])</div>
</div>
@endsection
