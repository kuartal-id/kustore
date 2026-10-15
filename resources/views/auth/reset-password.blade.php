@extends('layouts.narrow')
@section('title', 'Choose a new password · Kustore')
@section('noindex', true)

@section('content')
<div class="card card-pad sm:p-8">
    <h1 class="text-2xl font-semibold">Choose a new password</h1>
    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email', $email) }}" class="input">
            <x-field-error name="email" />
        </div>
        <div>
            <label for="password" class="label">New password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required minlength="10" class="input">
            <p class="help">At least 10 characters, with letters and numbers.</p>
            <x-field-error name="password" />
        </div>
        <div>
            <label for="password_confirmation" class="label">Repeat new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="10" class="input">
        </div>
        <button class="btn btn-primary w-full">Save new password</button>
    </form>
</div>
@endsection
