@extends('layouts.base')
@section('default_theme', $store->color_mode === 'dark' ? 'dark' : 'light')

@section('body')
<div class="flex min-h-dvh flex-col">
    <header class="@yield('header_width', 'max-w-5xl') mx-auto flex h-14 w-full items-center justify-between px-4 sm:h-16 sm:px-6">
        @hasSection('header_left')
            @yield('header_left')
        @else
            <span></span>
        @endif
        <div class="flex items-center">
            <a href="#share" class="btn btn-ghost btn-icon" aria-label="Share">{{ icon('share-2', 'size-[18px]') }}</a>
            <x-theme-toggle />
        </div>
    </header>
    <main id="main" class="flex-1">
        @yield('content')
    </main>
    <footer class="mt-16 pb-10 text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded-full border border-line px-4 py-2 text-xs font-medium muted hover:border-navy/30 hover:text-navy dark:border-white/10 dark:hover:text-white">
            Made with
            <img src="{{ asset_v('images/brand/kustore-logo-light.png') }}" alt="Kustore" width="70" height="14" class="h-3.5 w-[70px] max-w-none dark:hidden">
            <img src="{{ asset_v('images/brand/kustore-logo-dark.png') }}" alt="Kustore" width="70" height="14" class="hidden h-3.5 w-[70px] max-w-none dark:block">
            · Create yours
        </a>
    </footer>
</div>
@endsection
