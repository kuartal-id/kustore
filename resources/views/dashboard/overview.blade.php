@extends('layouts.dashboard')
@section('page_title', 'Overview')
@section('page_subtitle', 'Hi '.str(auth()->user()->name)->before(' ').', here is how your Kustore is doing.')

@section('content')
@php $url = $store->url(); @endphp

{{-- Store status --}}
<section class="card card-pad">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                @if ($store->is_suspended)
                    <span class="badge badge-red">Suspended</span>
                @elseif ($store->is_published)
                    <span class="badge badge-green"><span class="dot bg-green"></span>Live</span>
                @else
                    <span class="badge badge-neutral">Hidden</span>
                @endif
                <span class="text-sm muted">{{ $store->is_published ? 'Anyone with the link can see your store.' : 'Only you can see your store.' }}</span>
            </div>
            <div class="mt-3 flex min-w-0 items-center gap-2">
                <a href="{{ $url }}" target="_blank" rel="noopener" class="truncate font-display text-lg font-semibold hover:underline sm:text-xl">kustore.id/{{ $store->username }}</a>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="btn btn-secondary btn-sm" data-copy="{{ $url }}">{{ icon('copy', 'size-4') }}<span data-copy-label>Copy link</span></button>
            <form method="POST" action="{{ route('dashboard.publish') }}">
                @csrf
                @if ($store->is_published)
                    <button class="btn btn-secondary btn-sm">{{ icon('eye-off', 'size-4') }} Unpublish</button>
                @else
                    <button class="btn btn-accent btn-sm" @disabled($store->is_suspended)>{{ icon('eye', 'size-4') }} Publish</button>
                @endif
            </form>
        </div>
    </div>
    @unless (auth()->user()->canPublishStore())
        <div class="mt-5 flex flex-col gap-3 rounded-2xl bg-ice p-4 text-sm sm:flex-row sm:items-center sm:justify-between dark:bg-white/5">
            <span>Confirm your email to publish your store.</span>
            <a href="{{ route('verification.notice') }}" class="link">Verify email</a>
        </div>
    @endunless
</section>

{{-- Numbers --}}
<section class="mt-4 grid grid-cols-2 gap-3 sm:mt-6 sm:gap-4 lg:grid-cols-4" aria-label="Summary">
    @foreach ([
        ['Store views', number_format($views, 0, ',', '.'), 'Last 30 days', 'eye'],
        ['Link clicks', number_format($clicks, 0, ',', '.'), 'Last 30 days', 'link'],
        ['Sales', money($revenue, $store->currency), 'From paid orders', 'receipt'],
        ['Active products', $activeProducts, $pendingOrders.' order'.($pendingOrders === 1 ? '' : 's').' awaiting payment', 'package'],
    ] as [$label, $value, $hint, $icon])
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between text-muted dark:text-muted-dark"><span class="text-[13px] font-medium">{{ $label }}</span>{{ icon($icon, 'size-4') }}</div>
            <p class="mt-3 truncate font-display text-xl font-semibold sm:text-2xl">{{ $value }}</p>
            <p class="mt-1 truncate text-xs muted">{{ $hint }}</p>
        </div>
    @endforeach
</section>

<div class="mt-4 grid gap-4 sm:mt-6 sm:gap-6 lg:grid-cols-[1fr_320px]">
    {{-- Recent orders --}}
    <section class="card">
        <div class="flex items-center justify-between p-5 sm:px-6">
            <h2 class="text-base font-semibold">Recent orders</h2>
            <a href="{{ route('dashboard.orders.index') }}" class="text-sm font-medium muted hover:text-navy dark:hover:text-white">See all</a>
        </div>
        @forelse ($recentOrders as $order)
            <a href="{{ route('dashboard.orders.show', $order) }}" class="flex items-center gap-4 border-t border-line px-5 py-3.5 hover:bg-ice/60 sm:px-6 dark:border-white/[0.06] dark:hover:bg-white/[0.03]">
                <div class="min-w-0 flex-1">
                    <p class="truncate font-display text-sm font-medium">{{ $order->customer_name }}</p>
                    <p class="truncate text-xs muted">{{ $order->number }} · {{ $order->created_at->diffForHumans() }}</p>
                </div>
                <div class="text-right">
                    <p class="font-display text-sm font-semibold">{{ $order->formattedTotal() }}</p>
                    <x-status-badge :status="$order->payment_status" class="mt-1" />
                </div>
            </a>
        @empty
            <div class="border-t border-line px-6 py-10 text-center dark:border-white/[0.06]">
                <p class="font-display font-medium">No orders yet</p>
                <p class="mt-1 text-sm muted">Share your store link to get your first sale.</p>
            </div>
        @endforelse
    </section>

    {{-- Quick actions --}}
    <section class="card card-pad">
        <h2 class="text-base font-semibold">Quick actions</h2>
        <div class="mt-4 grid grid-cols-2 gap-2.5 lg:grid-cols-1">
            <a href="{{ route('dashboard.products.create') }}" class="btn btn-secondary justify-start">{{ icon('plus', 'size-4') }} Add product</a>
            <a href="{{ route('dashboard.links.index') }}" class="btn btn-secondary justify-start">{{ icon('link', 'size-4') }} Add link</a>
            <a href="{{ route('dashboard.store.edit') }}" class="btn btn-secondary justify-start">{{ icon('pencil', 'size-4') }} Edit store</a>
            <a href="https://wa.me/?text={{ urlencode('Visit my Kustore: '.$url) }}" target="_blank" rel="noopener" class="btn btn-secondary justify-start">{{ icon('whatsapp', 'size-4') }} Share</a>
        </div>
        @php
            $steps = [
                ['Add a photo or logo', (bool) $store->avatar_path],
                ['Add your first link', $linksCount > 0],
                ['Add your first product', $activeProducts > 0],
                ['Publish your store', $store->is_published],
            ];
            $done = collect($steps)->where(1, true)->count();
        @endphp
        @if ($done < count($steps))
            <div class="divider mt-6 pt-5">
                <p class="text-sm font-medium">Getting started · {{ $done }}/{{ count($steps) }}</p>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($steps as [$label, $ok])
                        <li class="flex items-center gap-2.5 {{ $ok ? 'muted line-through' : '' }}">
                            <span @class(['grid size-5 place-items-center rounded-full', 'bg-green text-ink' => $ok, 'border border-line-strong dark:border-white/20' => ! $ok])>@if ($ok){{ icon('check', 'size-3') }}@endif</span>{{ $label }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>
</div>
@endsection
