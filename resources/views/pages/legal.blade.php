@extends('layouts.marketing')

@section('content')
<article class="container-k max-w-3xl py-14 sm:py-20">
    <div class="flash flash-error mb-8" role="note">{{ icon('info', 'size-4 mt-0.5 shrink-0') }}<span><strong>Draft.</strong> This page is a placeholder and is not yet legally reviewed. It will be replaced before general launch.</span></div>
    <p class="eyebrow">Kustore by Kuartal</p>
    <h1 class="mt-3 text-4xl font-semibold">@yield('heading')</h1>
    <p class="mt-2 text-sm muted">Last updated: {{ now()->format('j F Y') }} (draft)</p>
    <div class="prose-k mt-8">@yield('legal')</div>
</article>
@endsection
