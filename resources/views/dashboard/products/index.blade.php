@extends('layouts.dashboard')
@section('page_title', 'Products')
@section('page_subtitle', 'What you sell on your page.')
@section('page_actions')
    <a href="{{ route('dashboard.products.create') }}" class="btn btn-primary btn-sm">{{ icon('plus', 'size-4') }} Add product</a>
@endsection

@section('content')
@if ($products->isEmpty())
    <div class="card px-6 py-16 text-center">
        <span class="mx-auto grid size-11 place-items-center rounded-2xl bg-ice text-navy dark:bg-white/5 dark:text-white">{{ icon('package') }}</span>
        <p class="mt-4 font-display font-medium">No products yet</p>
        <p class="mt-1 text-sm muted">Physical goods, digital downloads or services. Prices in Rupiah.</p>
        <a href="{{ route('dashboard.products.create') }}" class="btn btn-primary mt-6">{{ icon('plus', 'size-4') }} Add your first product</a>
    </div>
@else
    <div class="card overflow-hidden">
        <div class="hidden grid-cols-[1fr_120px_110px_100px] gap-4 border-b border-line px-5 py-3 text-xs font-medium muted md:grid dark:border-white/[0.06]">
            <span>Product</span><span>Price</span><span>Stock</span><span class="text-right">Status</span>
        </div>
        @foreach ($products as $product)
            <a href="{{ route('dashboard.products.edit', $product) }}" class="grid grid-cols-[1fr_auto] items-center gap-x-4 gap-y-1 border-line px-4 py-3.5 hover:bg-ice/60 md:grid-cols-[1fr_120px_110px_100px] md:px-5 dark:border-white/[0.06] dark:hover:bg-white/[0.03] {{ $loop->first ? '' : 'border-t' }}">
                <div class="flex min-w-0 items-center gap-3">
                    @if ($product->imageUrl())
                        <img src="{{ $product->imageUrl() }}" alt="" class="size-12 shrink-0 rounded-xl object-cover">
                    @else
                        <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-ice text-navy/40 dark:bg-white/5 dark:text-white/30">{{ icon('image', 'size-5') }}</span>
                    @endif
                    <div class="min-w-0">
                        <p class="flex items-center gap-1.5 truncate font-display text-sm font-medium">{{ $product->name }}@if ($product->is_featured)<span class="badge badge-navy">Featured</span>@endif</p>
                        <p class="truncate text-xs muted">{{ $product->type->label() }}@if ($product->sku) · {{ $product->sku }}@endif<span class="md:hidden"> · {{ $product->unlimited_stock ? 'Unlimited' : $product->stock_quantity.' in stock' }}</span></p>
                    </div>
                </div>
                <div class="text-right md:text-left">
                    <p class="font-display text-sm font-semibold">{{ $product->formattedEffectivePrice() }}</p>
                    @if ($product->isOnSale())<p class="text-xs muted line-through">{{ $product->formattedPrice() }}</p>@endif
                </div>
                <p class="hidden text-sm md:block">{{ $product->unlimited_stock ? 'Unlimited' : $product->stock_quantity }}</p>
                <div class="col-span-2 md:col-span-1 md:text-right">
                    @if (! $product->is_active)<span class="badge badge-neutral">Draft</span>
                    @elseif (! $product->inStock())<span class="badge badge-amber">Sold out</span>
                    @else<span class="badge badge-green">Active</span>@endif
                </div>
            </a>
        @endforeach
    </div>
    <div class="mt-6">{{ $products->links('partials.pager') }}</div>
@endif
@endsection
