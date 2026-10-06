@extends('layouts.base')
@section('title', trim($__env->yieldContent('code')).' · Kustore')
@section('noindex', true)
@section('body')
<main id="main" class="grid min-h-dvh place-items-center px-5">
    <div class="max-w-md text-center">
        <p class="font-display text-sm font-semibold muted">@yield('code')</p>
        <h1 class="mt-3 text-3xl font-semibold">@yield('heading')</h1>
        <p class="mt-3 muted">@yield('message')</p>
        <div class="mt-8 flex justify-center gap-3">
            <a href="{{ url('/') }}" class="btn btn-primary">Go to Kustore</a>
        </div>
        <div class="mt-14"><x-wordmark size="sm" /></div>
    </div>
</main>
@endsection
