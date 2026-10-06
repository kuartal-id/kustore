@extends('layouts.dashboard')
@section('page_title', 'Links')
@section('page_subtitle', 'The buttons on your page, in this order.')

@section('content')
<div class="grid gap-4 sm:gap-6 lg:grid-cols-[1fr_340px]">
    <section class="card overflow-hidden lg:order-1">
        @forelse ($links as $i => $link)
            <div class="flex items-center gap-3 border-line px-4 py-3.5 sm:px-5 dark:border-white/[0.06] {{ $i ? 'border-t' : '' }}">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-ice text-navy dark:bg-white/5 dark:text-white">{{ icon($link->icon, 'size-[18px]') }}</span>
                <div class="min-w-0 flex-1">
                    <p class="flex items-center gap-2 truncate font-display text-sm font-medium">{{ $link->title }} @unless ($link->is_visible)<span class="badge badge-neutral">Hidden</span>@endunless</p>
                    <p class="truncate text-xs muted">{{ parse_url($link->url, PHP_URL_HOST) ?: $link->url }} · {{ $link->clicks_count }} {{ Str::plural('click', $link->clicks_count) }}</p>
                </div>
                <div class="flex items-center">
                    <form method="POST" action="{{ route('dashboard.links.move', [$link, 'up']) }}">@csrf<button class="btn btn-ghost btn-icon size-9" aria-label="Move up" @disabled($loop->first)>{{ icon('arrow-up', 'size-4') }}</button></form>
                    <form method="POST" action="{{ route('dashboard.links.move', [$link, 'down']) }}">@csrf<button class="btn btn-ghost btn-icon size-9" aria-label="Move down" @disabled($loop->last)>{{ icon('arrow-down', 'size-4') }}</button></form>
                    <a href="{{ route('dashboard.links.edit', $link) }}" class="btn btn-ghost btn-icon size-9" aria-label="Edit">{{ icon('pencil', 'size-4') }}</a>
                </div>
            </div>
        @empty
            <div class="px-6 py-14 text-center">
                <span class="mx-auto grid size-11 place-items-center rounded-2xl bg-ice text-navy dark:bg-white/5 dark:text-white">{{ icon('link') }}</span>
                <p class="mt-4 font-display font-medium">No links yet</p>
                <p class="mt-1 text-sm muted">Add your Instagram, WhatsApp or website.</p>
            </div>
        @endforelse
    </section>

    <section class="card card-pad lg:order-2 lg:self-start">
        <h2 class="text-base font-semibold">Add a link</h2>
        <form method="POST" action="{{ route('dashboard.links.store') }}" class="mt-4" novalidate>
            @csrf
            @include('dashboard.links._fields', ['link' => new \App\Models\StoreLink()])
            <button class="btn btn-primary mt-5 w-full">{{ icon('plus', 'size-4') }} Add link</button>
        </form>
    </section>
</div>
@endsection
