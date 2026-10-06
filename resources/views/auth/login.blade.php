@extends('layouts.narrow')
@section('title', 'Log in · Kustore')
@section('canonical', route('login'))

@section('content')
<div class="card card-pad sm:p-8">
    <h1 class="text-2xl font-semibold">Welcome to Kustore</h1>
    <p class="mt-2 muted">Sign in or create your Kustore with one account.</p>

    @error('kuartal')<div class="flash flash-error mt-6" role="alert">{{ icon('circle-alert', 'size-4 mt-0.5 shrink-0') }}<span>{{ $message }}</span></div>@enderror

    <x-kuartal-button class="mt-7 w-full" />
    <p class="mt-3 text-center text-xs muted">Kuartal ID is the single account for every Kuartal product.</p>

    <details class="group mt-8 border-t border-line pt-6 dark:border-white/[0.07]" @if ($errors->hasAny(['email', 'password'])) open @endif>
        <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-medium muted hover:text-navy dark:hover:text-white">
            Use email and password instead
            <span class="transition-transform group-open:rotate-90">{{ icon('chevron-right', 'size-4') }}</span>
        </summary>
        <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-4" novalidate>
            @csrf
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="input">
                <x-field-error name="email" />
            </div>
            <div>
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required class="input">
                <x-field-error name="password" />
            </div>
            <label class="flex items-center gap-2 text-sm muted"><input type="checkbox" name="remember" value="1" class="checkbox"> Keep me signed in</label>
            <button class="btn btn-secondary w-full">Log in with email</button>
            <p class="text-center text-sm muted">No account? <a href="{{ route('register') }}" class="link">Create one with email</a></p>
        </form>
    </details>
</div>
<p class="mt-6 text-center text-xs muted">By continuing you agree to the <a href="{{ route('terms') }}" class="link">Terms</a> and <a href="{{ route('privacy') }}" class="link">Privacy Policy</a>.</p>
@endsection
