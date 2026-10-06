@php $productUrl = route('storefront.product', [$store->username, $product->slug]); $available = $product->inStock(); @endphp
<article class="group flex flex-col rounded-[var(--radius-card)] border border-line bg-white p-2.5 transition-shadow hover:shadow-card sm:p-3 dark:border-white/[0.07] dark:bg-navy-900">
    <a href="{{ $productUrl }}" class="relative block overflow-hidden rounded-[14px] bg-ice dark:bg-white/5">
        @if ($product->imageUrl())
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy" class="aspect-square w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]">
        @else
            <div class="grid aspect-square place-items-center text-navy/25 dark:text-white/20">{{ icon('shopping-bag', 'size-8') }}</div>
        @endif
        <span class="badge absolute top-2 left-2 bg-white/95 text-navy shadow-sm dark:bg-navy-950/90 dark:text-white">{{ $product->type->label() }}</span>
        @if ($product->isOnSale())<span class="badge badge-navy absolute top-2 right-2">Sale</span>@endif
    </a>
    <div class="flex flex-1 flex-col px-1 pt-3 pb-1">
        <a href="{{ $productUrl }}" class="line-clamp-2 font-display text-sm leading-snug font-medium hover:underline sm:text-[15px]">{{ $product->name }}</a>
        <div class="mt-1.5 flex flex-wrap items-baseline gap-x-2">
            <span class="font-display text-sm font-semibold sm:text-[15px]">{{ $product->formattedEffectivePrice() }}</span>
            @if ($product->isOnSale())<span class="text-xs muted line-through">{{ $product->formattedPrice() }}</span>@endif
        </div>
        <p class="mt-0.5 text-xs {{ $available ? 'muted' : 'text-danger dark:text-[#FF8A75]' }}">{{ $product->availabilityLabel() }}</p>
        <div class="mt-auto pt-3">
            @if ($available)
                <a href="{{ route('checkout.show', [$store->username, $product->slug]) }}" class="btn btn-primary btn-sm w-full">Buy</a>
            @else
                <span class="btn btn-secondary btn-sm w-full opacity-60" aria-disabled="true">Sold out</span>
            @endif
        </div>
    </div>
</article>
