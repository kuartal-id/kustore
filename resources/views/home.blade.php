@extends('layouts.app')
@section('content')
<section class="relative overflow-hidden">
<div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_78%_20%,rgba(54,204,100,.14),transparent_25%),radial-gradient(circle_at_15%_28%,rgba(56,182,255,.10),transparent_28%)]"></div>
<div class="k-container py-24 sm:py-32 lg:py-36">
<div class="grid items-center gap-16 lg:grid-cols-[1.1fr_.9fr]">
<div>
<div class="k-eyebrow">Kustore by Kuartal</div>
<h1 class="mt-6 max-w-4xl text-5xl leading-[1.02] sm:text-6xl lg:text-[72px]">Everything you sell.<br><span class="text-green">One place.</span></h1>
<p class="mt-7 max-w-xl text-lg leading-8 text-muted sm:text-xl">A simple, beautiful storefront for your links, products and business. Built for creators, founders and people building something of their own.</p>
<div class="mt-9 flex flex-wrap gap-3"><a class="k-btn-primary" href="{{ route('register') }}">Create your Kustore</a><a class="k-btn-outline" href="{{ route('login') }}">Log in</a></div>
<div class="mt-7 flex items-center gap-3 text-xs font-medium text-muted"><span class="h-2 w-2 rounded-full bg-green"></span> One account. One storefront. Part of Kuartal.</div>
</div>
<div class="relative">
<div class="k-card overflow-hidden p-0">
<div class="border-b border-border bg-white px-6 py-5"><div class="flex items-center justify-between"><div class="kustore-brand"><span class="kustore-mark">re</span><span class="kustore-word !text-base">Kustore</span></div><span class="text-xs text-muted">Preview</span></div></div>
<div class="bg-ice p-7 sm:p-9"><div class="mx-auto max-w-sm text-center"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-navy font-display text-2xl text-white">K</div><h2 class="mt-4 text-2xl">Your storefront</h2><p class="mt-2 text-sm text-muted">Links, products and everything your audience needs.</p><div class="mt-6 space-y-3"><div class="k-link">Your latest product ↗</div><div class="k-link">Website ↗</div><div class="k-link">Contact ↗</div></div></div></div>
</div>
</div>
</div>
<div class="mt-24 border-t border-border pt-10">
<div class="grid gap-8 sm:grid-cols-3"><div><div class="k-eyebrow">01</div><h2 class="mt-4 text-xl">One home</h2><p class="mt-2 text-sm leading-6 text-muted">Put your profile, links, products and services together.</p></div><div><div class="k-eyebrow">02</div><h2 class="mt-4 text-xl">Built to sell</h2><p class="mt-2 text-sm leading-6 text-muted">Turn your audience into customers without juggling multiple tools.</p></div><div><div class="k-eyebrow">03</div><h2 class="mt-4 text-xl">Kuartal ecosystem</h2><p class="mt-2 text-sm leading-6 text-muted">Kustore is part of a growing family of Kuartal products and services.</p></div></div>
</div>
</div>
</section>
@endsection