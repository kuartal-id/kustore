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
            Made with <span class="font-display font-semibold text-navy dark:text-white">Kustore</span> · Create yours
        </a>
    </footer>
</div>
@endsection
