@extends('layouts.narrow')
@section('title', 'Reset your password · Kustore')
@section('noindex', true)

@section('content')
<div class="card card-pad sm:p-8">
    <h1 class="text-2xl font-semibold">Reset your password</h1>
    <p class="mt-2 muted">For accounts that sign in with email and password. Enter your email and we'll send you a link to choose a new password.</p>
    <x-flash class="mt-6" />
    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4" novalidate>
        @csrf
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="input">
            <x-field-error name="email" />
        </div>
        <button class="btn btn-primary w-full">Send reset link</button>
    </form>
    <p class="mt-6 text-sm muted">Signed up with Kuartal ID? Reset your password at <a href="https://id.kuartal.id" class="link" rel="noopener">id.kuartal.id</a> instead.</p>
</div>
<p class="mt-6 text-center text-sm"><a href="{{ route('login') }}" class="link">Back to log in</a></p>
@endsection
