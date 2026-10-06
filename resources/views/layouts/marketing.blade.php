@extends('layouts.base')

@section('body')
<div class="flex min-h-dvh flex-col">
    <header class="border-b border-line/70 bg-ice/95 dark:border-white/[0.06] dark:bg-navy-950/95">
        <div class="container-k flex h-16 items-center justify-between gap-4 sm:h-[72px]">
            <x-wordmark />
            <nav class="flex items-center gap-1 sm:gap-2" aria-label="Main">
                <a href="{{ route('home') }}#how-it-works" class="btn btn-ghost btn-sm hidden md:inline-flex">How it works</a>
                <a href="{{ route('home') }}#for-indonesia" class="btn btn-ghost btn-sm hidden md:inline-flex">Payments</a>
                <x-theme-toggle />
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm hidden sm:inline-flex">Create your Kustore</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-line/70 dark:border-white/[0.06]">
        <div class="container-k flex flex-col gap-6 py-10 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-2">
                <x-wordmark size="sm" />
                <p class="muted text-sm">One person. One identity. One storefront.</p>
            </div>
            <nav class="flex flex-wrap gap-x-6 gap-y-2 text-sm muted" aria-label="Footer">
                <a href="{{ route('terms') }}" class="hover:text-navy dark:hover:text-white">Terms</a>
                <a href="{{ route('privacy') }}" class="hover:text-navy dark:hover:text-white">Privacy</a>
                <a href="https://kuartal.id" class="hover:text-navy dark:hover:text-white" rel="noopener">Kuartal</a>
                <span>&copy; {{ date('Y') }} Kuartal</span>
            </nav>
        </div>
    </footer>
</div>
@endsection
