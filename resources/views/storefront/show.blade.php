@extends('layouts.storefront')
@section('title', $store->metaTitle())
@section('description', $store->metaDescription())
@section('canonical', $store->url())
@section('header_width', $store->renderedLayout() === 'minimal' ? 'max-w-xl' : 'max-w-5xl')
@push('meta')
    @include('storefront.partials.seo', ['title' => $store->metaTitle(), 'description' => $store->metaDescription(), 'canonical' => $store->url(), 'image' => $store->avatarUrl(), 'type' => 'profile'])
@endpush

@section('content')
@if ($store->renderedLayout() === 'minimal')
    {{-- Minimal: centered link-in-bio --}}
    <div class="mx-auto max-w-xl px-5 pt-4 sm:pt-8">
        <section class="text-center">
            <x-avatar :store="$store" size="size-24" text="text-2xl" class="mx-auto" />
            <div class="mt-5">@include('storefront.partials.identity', ['center' => true])</div>
        </section>

        @if ($links->isNotEmpty())
            <section class="mt-8 space-y-3" aria-label="Links">
                @foreach ($links as $link) @include('storefront.partials.link') @endforeach
            </section>
        @endif

        @if ($products->isNotEmpty())
            <section class="mt-12" aria-labelledby="shop-heading">
                <div class="mb-4 flex items-baseline justify-between">
                    <h2 id="shop-heading" class="text-lg font-semibold">Shop</h2>
                    <span class="text-sm muted">{{ $products->count() }} {{ Str::plural('item', $products->count()) }}</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    @foreach ($products as $product) @include('storefront.partials.product-card') @endforeach
                </div>
            </section>
        @endif

        <div class="mt-12 flex justify-center text-center">@include('storefront.partials.share', ['url' => $store->url()])</div>
    </div>
@else
    {{-- Commerce: profile header + wide product grid --}}
    <div class="mx-auto max-w-5xl px-4 pt-2 sm:px-6">
        <section class="card p-5 sm:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                <x-avatar :store="$store" size="size-20 sm:size-24" text="text-2xl" />
                <div class="min-w-0 flex-1">@include('storefront.partials.identity')</div>
            </div>
            @if ($links->isNotEmpty())
                <div class="mt-6 flex flex-wrap gap-2 border-t border-line pt-5 dark:border-white/[0.07]" aria-label="Links">
                    @foreach ($links as $link)
                        <a href="{{ route('links.go', $link) }}" target="_blank" rel="noopener nofollow" class="btn btn-secondary btn-sm">{{ icon($link->icon, 'size-4') }}{{ $link->title }}</a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="mt-10" aria-labelledby="shop-heading">
            <div class="mb-5 flex items-baseline justify-between">
                <h2 id="shop-heading" class="text-xl font-semibold">Products</h2>
                <span class="text-sm muted">{{ $products->count() }} {{ Str::plural('item', $products->count()) }}</span>
            </div>
            @if ($products->isNotEmpty())
                <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($products as $product) @include('storefront.partials.product-card') @endforeach
                </div>
            @else
                <p class="card px-6 py-12 text-center muted">No products yet. Check back soon.</p>
            @endif
        </section>

        <div class="mt-12">@include('storefront.partials.share', ['url' => $store->url()])</div>
    </div>
@endif
@endsection
