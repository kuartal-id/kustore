@extends('layouts.dashboard')
@section('page_title', 'Customers')
@section('page_subtitle', 'Everyone who has ordered from your Kustore.')
@section('page_actions')
    @if ($totalCustomers)
        <a href="{{ route('dashboard.customers.export') }}" class="btn btn-secondary btn-sm">{{ icon('download', 'size-4') }} Export CSV</a>
    @endif
@endsection

@section('content')
<section class="grid grid-cols-2 gap-3 sm:gap-4" aria-label="Summary">
    <div class="card p-4 sm:p-5">
        <p class="text-[13px] font-medium muted">Customers</p>
        <p class="mt-2 font-display text-2xl font-semibold">{{ number_format($totalCustomers, 0, ',', '.') }}</p>
    </div>
    <div class="card p-4 sm:p-5">
        <p class="text-[13px] font-medium muted">Ordered more than once</p>
        <p class="mt-2 font-display text-2xl font-semibold">{{ number_format($repeatCustomers, 0, ',', '.') }}</p>
    </div>
</section>

<form method="GET" action="{{ route('dashboard.customers.index') }}" class="mt-5 flex gap-2" role="search">
    <label for="q" class="sr-only">Search customers</label>
    <div class="input-group flex-1">
        <span>{{ icon('search', 'size-4') }}</span>
        <input id="q" name="q" value="{{ $search }}" placeholder="Name, email or phone" autocomplete="off">
    </div>
    <button class="btn btn-secondary">Search</button>
</form>

<div class="card mt-4 overflow-hidden">
    @forelse ($customers as $customer)
        <a href="{{ route('dashboard.customers.show', $customer) }}" class="flex items-center gap-4 border-line px-4 py-4 hover:bg-ice/60 sm:px-5 dark:border-white/[0.06] dark:hover:bg-white/[0.03] {{ $loop->first ? '' : 'border-t' }}">
            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-ice font-display text-sm font-semibold text-navy dark:bg-white/10 dark:text-white">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
            <div class="min-w-0 flex-1">
                <p class="truncate font-display text-sm font-medium">{{ $customer->name }}</p>
                <p class="truncate text-xs muted">{{ $customer->email }}@if ($customer->phone) · {{ $customer->phone }}@endif</p>
            </div>
            <div class="text-right">
                <p class="font-display text-sm font-semibold">{{ money((int) $customer->paid_total, $store->currency) }}</p>
                <p class="text-xs muted">{{ $customer->orders_count }} {{ $customer->orders_count === 1 ? 'order' : 'orders' }}</p>
            </div>
        </a>
    @empty
        <div class="px-6 py-16 text-center">
            <span class="mx-auto grid size-11 place-items-center rounded-2xl bg-ice text-navy dark:bg-white/5 dark:text-white">{{ icon('users') }}</span>
            <p class="mt-4 font-display font-medium">{{ $search !== '' ? 'No customers match "'.$search.'"' : 'No customers yet' }}</p>
            <p class="mt-1 text-sm muted">Customers are added automatically when someone places an order.</p>
        </div>
    @endforelse
</div>
<div class="mt-6">{{ $customers->links('partials.pager') }}</div>
@endsection
