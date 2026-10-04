@extends('layouts.app')
@section('content')
<div class="min-h-screen {{ $store->theme==='dark' ? 'bg-[#0f1c22] text-white' : 'bg-[#f7f9f9] text-[#0b1319]' }}">
<div class="store-shell">
<div class="text-center"><div class="mx-auto h-24 w-24 overflow-hidden rounded-full shadow-md">@if($store->avatar_url)<img class="h-full w-full object-cover" src="{{ $store->avatar_url }}" alt="">@else<div class="flex h-full w-full items-center justify-center bg-[#18333d] text-3xl font-semibold text-white">{{ strtoupper(substr($store->display_name,0,1)) }}</div>@endif</div><h1 class="mt-5 text-3xl">{{ $store->display_name }}</h1>@if($store->bio)<p class="mx-auto mt-2 max-w-lg text-sm opacity-70">{{ $store->bio }}</p>@endif<p class="mt-2 text-xs opacity-50">kustore.id/{{ $store->username }}</p></div>
<div class="mt-8 space-y-3">@foreach($store->links as $link)<a class="store-button" href="{{ $link->url }}" target="_blank" rel="noopener">{{ $link->title }}</a>@endforeach</div>
@if($store->products->count())<section class="mt-10"><h2 class="text-xl">Shop</h2><div class="mt-4 grid gap-4 sm:grid-cols-2">@foreach($store->products as $product)<a href="{{ route('store.product',[$store->username,$product->slug]) }}" class="overflow-hidden rounded-2xl border border-[#e1e8ed] bg-white dark:border-white/10 dark:bg-[#152730]">@if($product->image_url)<img class="aspect-[4/3] w-full object-cover" src="{{ $product->image_url }}" alt="">@endif<div class="p-4"><h3>{{ $product->name }}</h3><p class="mt-1 text-sm opacity-60">{{ $product->currency }} {{ number_format($product->price,0,',','.') }}</p></div></a>@endforeach</div></section>@endif
<div class="mt-12 text-center text-xs opacity-50">Powered by KuStore · Kuartal</div>
</div></div>
@endsection