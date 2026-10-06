@extends('layouts.dashboard')
@section('page_title', $product->exists ? 'Edit product' : 'New product')
@section('back', route('dashboard.products.index'))
@section('page_actions')
    @if ($product->exists && $product->is_active && $store->is_published)
        <a href="{{ route('storefront.product', [$store->username, $product->slug]) }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">{{ icon('external-link', 'size-4') }} View</a>
    @endif
@endsection

@section('content')
@php
    $type = old('type', $product->type?->value ?? 'physical');
    $currencySymbol = config('kustore.currencies.'.$store->currency.'.symbol', $store->currency);
@endphp
<form method="POST" enctype="multipart/form-data" novalidate
      action="{{ $product->exists ? route('dashboard.products.update', $product) : route('dashboard.products.store') }}"
      class="grid gap-4 sm:gap-6 lg:grid-cols-[1fr_320px]">
    @csrf
    @if ($product->exists) @method('PUT') @endif
    <input type="hidden" name="requires_shipping_present" value="1">

    <div class="space-y-4 sm:space-y-6">
        <section class="card card-pad">
            <h2 class="text-base font-semibold">Details</h2>
            <div class="mt-5 space-y-5">
                <div>
                    <label for="name" class="label">Name</label>
                    <input id="name" name="name" value="{{ old('name', $product->name) }}" maxlength="120" required class="input" placeholder="Batik tote bag">
                    <x-field-error name="name" />
                </div>
                <div>
                    <label for="slug" class="label">Link</label>
                    <div class="input-group">
                        <span class="whitespace-nowrap">/product/</span>
                        <input id="slug" name="slug" value="{{ old('slug', $product->slug) }}" maxlength="90" placeholder="created from the name">
                    </div>
                    <x-field-error name="slug" />
                </div>
                <div>
                    <label for="description" class="label">Description</label>
                    <textarea id="description" name="description" rows="6" maxlength="5000" class="input" placeholder="Materials, size, what's included, delivery time.">{{ old('description', $product->description) }}</textarea>
                    <x-field-error name="description" />
                </div>
            </div>
        </section>

        <section class="card card-pad">
            <h2 class="text-base font-semibold">Price</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="price" class="label">Price</label>
                    <div class="input-group"><span>{{ $currencySymbol }}</span><input id="price" name="price" inputmode="numeric" value="{{ old('price', \App\Support\Money::toInput($product->price, $store->currency)) }}" required placeholder="150000"></div>
                    <x-field-error name="price" />
                </div>
                <div>
                    <label for="sale_price" class="label">Sale price <span class="font-normal muted">(optional)</span></label>
                    <div class="input-group"><span>{{ $currencySymbol }}</span><input id="sale_price" name="sale_price" inputmode="numeric" value="{{ old('sale_price', \App\Support\Money::toInput($product->sale_price, $store->currency)) }}" placeholder="125000"></div>
                    <x-field-error name="sale_price" />
                </div>
            </div>
            <p class="help">Currency: {{ $store->currency }}. Whole Rupiah, no decimals.</p>
        </section>

        <section class="card card-pad">
            <h2 class="text-base font-semibold">Type and delivery</h2>
            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                @foreach (['physical' => ['Physical', 'truck', 'Shipped to the customer'], 'digital' => ['Digital', 'download', 'File, link or code'], 'service' => ['Service', 'calendar', 'Booking, class or work']] as $value => [$label, $ic, $text])
                    <label class="choice">
                        <input type="radio" name="type" value="{{ $value }}" @checked($type === $value)>
                        <span class="flex items-center gap-2 font-display font-semibold">{{ icon($ic, 'size-4') }}{{ $label }}</span>
                        <span class="text-xs muted">{{ $text }}</span>
                    </label>
                @endforeach
            </div>
            <x-field-error name="type" />
            <label class="mt-5 flex items-center gap-2 text-sm"><input type="checkbox" name="requires_shipping" value="1" class="checkbox" data-shipping @checked(old('requires_shipping', $product->exists ? $product->requires_shipping : $type === 'physical'))> Ask customers for a shipping address</label>
        </section>

        <section class="card card-pad">
            <h2 class="text-base font-semibold">Inventory</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="stock_quantity" class="label">Stock</label>
                    <input id="stock_quantity" name="stock_quantity" type="number" min="0" inputmode="numeric" value="{{ old('stock_quantity', $product->stock_quantity) }}" class="input disabled:opacity-50" data-stock>
                    <x-field-error name="stock_quantity" />
                </div>
                <div>
                    <label for="sku" class="label">SKU <span class="font-normal muted">(optional)</span></label>
                    <input id="sku" name="sku" value="{{ old('sku', $product->sku) }}" maxlength="64" class="input">
                    <x-field-error name="sku" />
                </div>
            </div>
            <label class="mt-4 flex items-center gap-2 text-sm"><input type="checkbox" name="unlimited_stock" value="1" class="checkbox" data-unlimited @checked(old('unlimited_stock', $product->unlimited_stock))> Unlimited stock (good for digital products and services)</label>
        </section>
    </div>

    <div class="space-y-4 sm:space-y-6 lg:sticky lg:top-10 lg:self-start">
        <section class="card card-pad">
            <h2 class="text-base font-semibold">Photo</h2>
            <div class="mt-4 overflow-hidden rounded-2xl bg-ice dark:bg-white/5">
                @if ($product->exists && $product->imageUrl())
                    <img id="product-image" src="{{ $product->imageUrl() }}" alt="" class="aspect-square w-full object-cover">
                @else
                    <img id="product-image" src="data:," alt="" class="aspect-square w-full object-cover" hidden>
                    <div id="product-image-placeholder" class="grid aspect-square place-items-center text-navy/30 dark:text-white/25">{{ icon('image', 'size-10') }}</div>
                @endif
            </div>
            <label class="btn btn-secondary btn-sm mt-4 w-full">{{ icon('upload', 'size-4') }} {{ $product->exists && $product->imageUrl() ? 'Replace photo' : 'Upload photo' }}
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="sr-only" data-preview="product-image">
            </label>
            <p class="help text-center">JPG, PNG or WebP, up to 4 MB. Square looks best.</p>
            <x-field-error name="image" />
            @if ($product->exists && $product->imageUrl())
                <label class="mt-3 flex items-center gap-2 text-sm muted"><input type="checkbox" name="remove_image" value="1" class="checkbox"> Remove photo</label>
            @endif
        </section>

        <section class="card card-pad space-y-4">
            <h2 class="text-base font-semibold">Visibility</h2>
            <label class="flex items-start gap-3 text-sm"><input type="checkbox" name="is_active" value="1" class="checkbox mt-0.5" @checked(old('is_active', $product->is_active))><span><span class="font-medium">Active</span><br><span class="muted">Show on your page and allow orders.</span></span></label>
            <label class="flex items-start gap-3 text-sm"><input type="checkbox" name="is_featured" value="1" class="checkbox mt-0.5" @checked(old('is_featured', $product->is_featured))><span><span class="font-medium">Featured</span><br><span class="muted">Show first on your page.</span></span></label>
        </section>

        <button class="btn btn-primary btn-lg w-full">{{ $product->exists ? 'Save product' : 'Create product' }}</button>
    </div>
</form>

@if ($product->exists)
    <form method="POST" action="{{ route('dashboard.products.destroy', $product) }}" data-confirm="Delete this product? Past orders keep their details." class="mt-6 flex justify-end lg:justify-start">
        @csrf @method('DELETE')
        <button class="btn btn-danger btn-sm">{{ icon('trash-2', 'size-4') }} Delete product</button>
    </form>
@endif
@endsection
