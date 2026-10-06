@extends('layouts.narrow')
@section('title', 'Choose your username · Kustore')
@section('noindex', true)

@section('content')
<p class="eyebrow">Step 1 of 2</p>
<h1 class="mt-3 text-3xl font-semibold">Choose your username</h1>
<p class="mt-2 muted">This is your address on Kustore. You can share it anywhere.</p>

<form method="POST" action="{{ route('onboarding.username') }}" class="card card-pad mt-8 space-y-5 sm:p-8">
    @csrf
    <div>
        <label for="username" class="label">Username</label>
        <div class="input-group">
            <span>kustore.id/</span>
            <input id="username" name="username" value="{{ $username }}" required minlength="3" maxlength="30" autocomplete="off" autocapitalize="none" spellcheck="false" pattern="[a-z0-9][a-z0-9_\-]*[a-z0-9]" data-username autofocus>
        </div>
        <x-field-error name="username" />
        <p class="help">3 to 30 characters. Lowercase letters, numbers, "-" and "_".</p>
    </div>
    <div class="rounded-2xl bg-ice px-4 py-3 text-sm dark:bg-white/5">
        <span class="muted">Your page:</span> <span class="font-display font-medium">kustore.id/<span data-username-preview>{{ $username ?: 'yourname' }}</span></span>
    </div>
    <button class="btn btn-primary btn-lg w-full">Continue</button>
</form>
@endsection
