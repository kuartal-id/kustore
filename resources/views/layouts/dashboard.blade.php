@extends('layouts.base')
@section('noindex', true)
@section('title', trim($__env->yieldContent('page_title', 'Dashboard')).' · Kustore')

@php
    $store = auth()->user()->store;
    $nav = [
        ['dashboard', 'Overview', 'layout-dashboard', 'dashboard'],
        ['dashboard.store.edit', 'Store', 'store', 'dashboard.store.*'],
        ['dashboard.links.index', 'Links', 'link', 'dashboard.links.*'],
        ['dashboard.products.index', 'Products', 'package', 'dashboard.products.*'],
        ['dashboard.orders.index', 'Orders', 'receipt', 'dashboard.orders.*'],
    ];
    $soon = [['Customers', 'users'], ['Analytics', 'chart-column'], ['Payments', 'credit-card']];
@endphp

@section('body')
<div class="min-h-dvh lg:flex">
    {{-- Desktop sidebar --}}
    <aside class="hidden w-[264px] shrink-0 border-r border-line bg-white lg:fixed lg:inset-y-0 lg:flex lg:flex-col dark:border-white/[0.06] dark:bg-navy-900/60">
        <div class="flex h-[72px] items-center px-6"><x-wordmark :href="route('dashboard')" /></div>
        <div class="px-4">
            <a href="{{ $store->url() }}" class="flex items-center gap-3 rounded-2xl border border-line p-3 hover:border-navy/30 dark:border-white/10 dark:hover:border-white/25" target="_blank" rel="noopener">
                <x-avatar :store="$store" size="size-9" text="text-xs" />
                <span class="min-w-0 flex-1">
                    <span class="block truncate font-display text-sm font-semibold">{{ $store->display_name }}</span>
                    <span class="flex items-center gap-1.5 text-xs muted"><span @class(['dot', 'bg-green' => $store->is_published, 'bg-line-strong dark:bg-white/25' => ! $store->is_published])></span>{{ $store->is_published ? 'Live' : 'Hidden' }} · {{ '@'.$store->username }}</span>
                </span>
            </a>
        </div>
        <nav class="mt-5 flex-1 space-y-1 px-4" aria-label="Dashboard">
            @foreach ($nav as [$route, $label, $icon, $pattern])
                <a href="{{ route($route) }}" class="nav-item" @if (request()->routeIs($pattern)) aria-current="page" @endif>{{ icon($icon, 'size-[18px]') }}{{ $label }}</a>
            @endforeach
            <p class="px-3 pt-6 pb-2 eyebrow">Coming soon</p>
            @foreach ($soon as [$label, $icon])
                <span class="nav-item-disabled" aria-disabled="true">{{ icon($icon, 'size-[18px]') }}{{ $label }}<span class="badge badge-neutral ml-auto">Soon</span></span>
            @endforeach
            @if (auth()->user()->is_admin)
                <a href="{{ route('admin.index') }}" class="nav-item mt-4" @if (request()->routeIs('admin.*')) aria-current="page" @endif>{{ icon('shield', 'size-[18px]') }}Admin</a>
            @endif
        </nav>
        <div class="flex items-center gap-1 border-t border-line p-4 dark:border-white/[0.06]">
            <a href="{{ $store->url() }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm flex-1 justify-start">{{ icon('external-link', 'size-4') }} View store</a>
            <x-theme-toggle />
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-ghost btn-icon" aria-label="Log out" title="Log out">{{ icon('log-out', 'size-[18px]') }}</button></form>
        </div>
    </aside>

    {{-- Mobile top bar --}}
    <header class="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-line bg-ice/95 px-4 lg:hidden dark:border-white/[0.06] dark:bg-navy-950/95">
        <x-wordmark :href="route('dashboard')" size="sm" />
        <div class="flex items-center">
            <a href="{{ $store->url() }}" target="_blank" rel="noopener" class="btn btn-ghost btn-icon" aria-label="View store">{{ icon('external-link', 'size-[18px]') }}</a>
            <x-theme-toggle />
            <button type="button" class="btn btn-ghost btn-icon" data-toggle="mobile-menu" aria-expanded="false" aria-controls="mobile-menu" aria-label="Menu">{{ icon('menu', 'size-5') }}</button>
        </div>
    </header>

    {{-- Mobile menu sheet --}}
    <div id="mobile-menu" class="fixed inset-0 z-40 lg:hidden" data-modal hidden>
        <button type="button" class="absolute inset-0 h-full w-full bg-navy-950/40" data-toggle="mobile-menu" aria-label="Close menu"></button>
        <div class="absolute inset-x-0 bottom-0 rounded-t-[28px] bg-white px-5 pt-3 pb-8 pb-safe dark:bg-navy-900">
            <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-line-strong dark:bg-white/20"></div>
            <div class="flex items-center gap-3 pb-4">
                <x-avatar :store="$store" size="size-10" text="text-sm" />
                <div class="min-w-0"><p class="truncate font-display font-semibold">{{ $store->display_name }}</p><p class="text-sm muted">kustore.id/{{ $store->username }}</p></div>
            </div>
            <nav class="space-y-1 border-t border-line pt-3 dark:border-white/10">
                @foreach ($soon as [$label, $icon])
                    <span class="nav-item-disabled">{{ icon($icon, 'size-[18px]') }}{{ $label }}<span class="badge badge-neutral ml-auto">Soon</span></span>
                @endforeach
                @if (auth()->user()->is_admin)
                    <a href="{{ route('admin.index') }}" class="nav-item">{{ icon('shield', 'size-[18px]') }}Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-item w-full">{{ icon('log-out', 'size-[18px]') }}Log out</button></form>
            </nav>
        </div>
    </div>

    {{-- Content --}}
    <main id="main" class="min-w-0 flex-1 pb-28 lg:ml-[264px] lg:pb-16">
        <div class="mx-auto w-full max-w-5xl px-4 pt-6 sm:px-6 lg:px-10 lg:pt-10">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-4 lg:mb-8">
                <div class="min-w-0">
                    @hasSection('back')<a href="@yield('back')" class="mb-2 inline-flex items-center gap-1 text-sm muted hover:text-navy dark:hover:text-white">{{ icon('arrow-left', 'size-4') }} Back</a>@endif
                    <h1 class="text-2xl font-semibold sm:text-[28px]">@yield('page_title')</h1>
                    @hasSection('page_subtitle')<p class="mt-1 muted">@yield('page_subtitle')</p>@endif
                </div>
                @yield('page_actions')
            </div>
            <x-flash class="mb-6" />
            @yield('content')
        </div>
    </main>

    {{-- Mobile bottom navigation --}}
    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-line bg-white/95 pb-safe lg:hidden dark:border-white/[0.07] dark:bg-navy-900/95" aria-label="Dashboard">
        <div class="mx-auto flex max-w-lg">
            @foreach ($nav as [$route, $label, $icon, $pattern])
                <a href="{{ route($route) }}" class="tab-item" @if (request()->routeIs($pattern)) aria-current="page" @endif>
                    <span class="tab-pip h-[3px] w-5 rounded-full bg-transparent"></span>
                    {{ icon($icon, 'size-[21px]') }}
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </div>
    </nav>
</div>
@endsection
