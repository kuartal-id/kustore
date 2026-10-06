@extends('layouts.narrow')
@section('title', 'Create your Kustore')
@section('canonical', route('register'))

@section('content')
<div class="card card-pad sm:p-8">
    <h1 class="text-2xl font-semibold">Create your Kustore</h1>
    <p class="mt-2 muted">The fastest way is with your Kuartal ID.</p>
    <x-kuartal-button class="mt-7 w-full" />

    <div class="my-8 flex items-center gap-3 text-xs muted"><span class="h-px flex-1 bg-line dark:bg-white/10"></span>or sign up with email<span class="h-px flex-1 bg-line dark:bg-white/10"></span></div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4" novalidate>
        @csrf
        <div>
            <label for="name" class="label">Your name</label>
            <input id="name" name="name" autocomplete="name" required value="{{ old('name') }}" class="input">
            <x-field-error name="name" />
        </div>
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="input">
            <x-field-error name="email" />
            <p class="help">We will send a link to confirm it before your store can go live.</p>
        </div>
        <div>
            <label for="password" class="label">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required class="input">
            <x-field-error name="password" />
            <p class="help">At least 10 characters, with letters and numbers.</p>
        </div>
        <div>
            <label for="password_confirmation" class="label">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="input">
        </div>
        <button class="btn btn-secondary w-full">Create account</button>
    </form>
</div>
<p class="mt-6 text-center text-sm muted">Already have an account? <a href="{{ route('login') }}" class="link">Log in</a></p>
@endsection
