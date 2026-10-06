@extends('layouts.base')

@section('body')
<div class="flex min-h-dvh flex-col">
    <header class="container-k flex h-16 items-center justify-between sm:h-[72px]">
        <x-wordmark />
        <div class="flex items-center gap-1">
            <x-theme-toggle />
            @auth
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-ghost btn-sm">Log out</button></form>
            @endauth
        </div>
    </header>
    <main id="main" class="flex flex-1 items-start justify-center px-5 pt-6 pb-16 sm:items-center sm:pt-0">
        <div class="w-full @yield('width', 'max-w-md')">
            @yield('content')
        </div>
    </main>
</div>
@endsection
