@extends('layouts.narrow')
@section('title', 'Verify your email · Kustore')
@section('noindex', true)

@section('content')
<div class="card card-pad sm:p-8">
    <span class="grid size-11 place-items-center rounded-2xl bg-ice text-navy dark:bg-white/5 dark:text-white">{{ icon('mail') }}</span>
    <h1 class="mt-5 text-2xl font-semibold">Check your inbox</h1>
    <p class="mt-2 leading-relaxed muted">We sent a confirmation link to <strong class="text-navy dark:text-white">{{ auth()->user()->email }}</strong>. Confirm your email to publish your Kustore. You can keep setting it up in the meantime.</p>
    <x-flash class="mt-5" />
    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
        <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn btn-secondary w-full sm:w-auto">Send the link again</button></form>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to dashboard</a>
    </div>
</div>
@endsection
