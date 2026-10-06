@extends('layouts.dashboard')
@section('page_title', 'Admin')
@section('page_subtitle', 'Stores and users on Kustore.')

@section('content')
<section class="card overflow-hidden">
    <h2 class="p-5 pb-3 text-base font-semibold">Stores · {{ $stores->total() }}</h2>
    @foreach ($stores as $s)
        <div class="flex flex-wrap items-center gap-3 border-t border-line px-5 py-3.5 dark:border-white/[0.06]">
            <x-avatar :store="$s" size="size-9" text="text-xs" />
            <div class="min-w-0 flex-1">
                <p class="truncate font-display text-sm font-medium">{{ $s->display_name }} <span class="muted font-normal">{{ '@'.$s->username }}</span></p>
                <p class="truncate text-xs muted">{{ $s->user?->email ?? 'no email' }} · {{ ucfirst($s->account_type) }} · {{ $s->created_at->format('j M Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if ($s->is_suspended)<span class="badge badge-red">Suspended</span>@elseif ($s->is_published)<span class="badge badge-green">Live</span>@else<span class="badge badge-neutral">Hidden</span>@endif
                <form method="POST" action="{{ route('admin.stores.suspend', $s) }}" data-confirm="{{ $s->is_suspended ? 'Restore' : 'Suspend' }} {{ '@'.$s->username }}?">
                    @csrf
                    <button class="btn btn-sm {{ $s->is_suspended ? 'btn-secondary' : 'btn-danger' }}">{{ $s->is_suspended ? 'Restore' : 'Suspend' }}</button>
                </form>
            </div>
        </div>
    @endforeach
    <div class="border-t border-line p-4 dark:border-white/[0.06]">{{ $stores->links('partials.pager') }}</div>
</section>

<section class="card mt-6 overflow-hidden">
    <h2 class="p-5 pb-3 text-base font-semibold">Users · {{ $users->total() }}</h2>
    @foreach ($users as $u)
        <div class="flex items-center gap-3 border-t border-line px-5 py-3 text-sm dark:border-white/[0.06]">
            <div class="min-w-0 flex-1">
                <p class="truncate font-medium">{{ $u->name }} @if ($u->is_admin)<span class="badge badge-navy">Admin</span>@endif</p>
                <p class="truncate text-xs muted">{{ $u->email ?? '—' }} · {{ $u->isKuartalIdUser() ? 'Kuartal ID' : 'Email' }}{{ $u->email_verified_at ? ' · verified' : '' }}</p>
            </div>
            <span class="text-xs muted">{{ $u->store ? '@'.$u->store->username : 'No store' }}</span>
        </div>
    @endforeach
    <div class="border-t border-line p-4 dark:border-white/[0.06]">{{ $users->links('partials.pager') }}</div>
</section>
@endsection
