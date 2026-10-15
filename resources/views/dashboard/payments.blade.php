@extends('layouts.dashboard')
@section('page_title', 'Payments')
@section('page_subtitle', 'How customers pay you, and what has come in.')

@section('content')
<section class="grid grid-cols-2 gap-3 sm:gap-4" aria-label="Summary">
    <div class="card p-4 sm:p-5">
        <p class="text-[13px] font-medium muted">Paid this month</p>
        <p class="mt-2 truncate font-display text-xl font-semibold sm:text-2xl">{{ money($paidThisMonth, $store->currency) }}</p>
    </div>
    <a href="{{ route('dashboard.orders.index', ['status' => 'pending']) }}" class="card p-4 hover:border-navy/30 sm:p-5 dark:hover:border-white/20">
        <p class="text-[13px] font-medium muted">Waiting for payment</p>
        <p class="mt-2 truncate font-display text-xl font-semibold sm:text-2xl">{{ money($awaiting, $store->currency) }}</p>
        <p class="mt-1 text-xs muted">{{ $awaitingCount }} {{ $awaitingCount === 1 ? 'order' : 'orders' }}</p>
    </a>
</section>

<div class="mt-4 grid gap-4 sm:mt-6 sm:gap-6 lg:grid-cols-[1fr_320px]">
    <form method="POST" action="{{ route('dashboard.payments.update') }}" enctype="multipart/form-data" class="space-y-4 sm:space-y-6">
        @csrf @method('PUT')

        <section class="card card-pad">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold">Manual payments</h2>
                    <p class="mt-1 text-sm muted">Customers pay you directly by bank transfer, e-wallet or QRIS. You mark the order as paid when the money arrives.</p>
                </div>
                <span class="badge badge-green shrink-0"><span class="dot bg-green"></span>On</span>
            </div>
            <label for="payment_instructions" class="label mt-5">Payment instructions</label>
            <textarea id="payment_instructions" name="payment_instructions" rows="5" maxlength="2000" class="input" placeholder="Transfer to BCA 1234567890 a.n. Your Name, then send proof via WhatsApp with your order number.">{{ old('payment_instructions', $store->payment_instructions) }}</textarea>
            <p class="help">Shown to customers right after they order, and in their order email.</p>
            <x-field-error name="payment_instructions" />
        </section>

        <section class="card card-pad">
            <h2 class="flex items-center gap-2 text-base font-semibold">{{ icon('qr-code', 'size-[18px]') }} QRIS</h2>
            <p class="mt-1 text-sm muted">Upload your own QRIS code from your bank or e-wallet app. Customers see it after they order and can scan it to pay.</p>
            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start">
                <div class="w-40 shrink-0 overflow-hidden rounded-2xl border border-line bg-white dark:border-white/10">
                    @if ($store->qrisUrl())
                        <img id="qris-image" src="{{ $store->qrisUrl() }}" alt="Your QRIS code" class="aspect-square w-full object-contain">
                    @else
                        <img id="qris-image" src="data:," alt="" class="aspect-square w-full object-contain" hidden>
                        <div id="qris-image-placeholder" class="grid aspect-square place-items-center text-navy/30 dark:text-white/25">{{ icon('qr-code', 'size-10') }}</div>
                    @endif
                </div>
                <div class="space-y-3">
                    <label class="btn btn-secondary btn-sm">{{ icon('upload', 'size-4') }} {{ $store->qrisUrl() ? 'Replace QRIS' : 'Upload QRIS' }}
                        <input type="file" name="qris" accept="image/jpeg,image/png,image/webp" class="sr-only" data-preview="qris-image">
                    </label>
                    <p class="help">JPG, PNG or WebP, up to 4 MB. Use the code image only, not a screenshot of your whole screen.</p>
                    <x-field-error name="qris" />
                    @if ($store->qrisUrl())
                        <label class="flex items-center gap-2 text-sm muted"><input type="checkbox" name="remove_qris" value="1" class="checkbox"> Remove QRIS</label>
                    @endif
                </div>
            </div>
        </section>

        <section class="card card-pad">
            <h2 class="flex items-center gap-2 text-base font-semibold">{{ icon('whatsapp', 'size-[18px]') }} WhatsApp for payment proof</h2>
            <p class="mt-1 text-sm muted">Adds a "Send payment proof on WhatsApp" button to the order page, with the order number filled in.</p>
            <label for="whatsapp_number" class="label mt-4">WhatsApp number</label>
            <input id="whatsapp_number" name="whatsapp_number" inputmode="tel" autocomplete="tel" class="input" value="{{ old('whatsapp_number', $store->whatsapp_number ? '+'.$store->whatsapp_number : '') }}" placeholder="0812 3456 7890">
            <x-field-error name="whatsapp_number" />
        </section>

        <section class="card card-pad">
            <h2 class="flex items-center gap-2 text-base font-semibold">{{ icon('bell', 'size-[18px]') }} Notifications</h2>
            <label class="mt-4 flex items-start gap-3 text-sm">
                <input type="hidden" name="notify_new_orders" value="0">
                <input type="checkbox" name="notify_new_orders" value="1" class="checkbox mt-0.5" @checked(old('notify_new_orders', $store->notify_new_orders ?? true))>
                <span><span class="font-medium">Email me when someone orders</span><br><span class="muted">Sent to {{ auth()->user()->email ?: 'your account email' }}.</span></span>
            </label>
        </section>

        <div class="sticky bottom-20 z-10 flex justify-end lg:bottom-6">
            <button class="btn btn-primary btn-lg shadow-lift">Save payment settings</button>
        </div>
    </form>

    <aside class="space-y-4 sm:space-y-6 lg:self-start">
        <section class="card card-pad">
            <h2 class="text-base font-semibold">Online payments</h2>
            <p class="mt-2 text-sm muted">Automatic payments (QRIS, virtual accounts, e-wallets and cards) are coming through Kuartal's payment gateway. Your manual setup keeps working alongside it.</p>
            <span class="badge badge-neutral mt-3">Coming soon</span>
        </section>

        <section class="card">
            <h2 class="p-5 pb-3 text-base font-semibold">Recent payments</h2>
            @forelse ($recent as $payment)
                <a href="{{ $payment->order ? route('dashboard.orders.show', $payment->order) : '#' }}" class="flex items-center justify-between gap-3 border-t border-line px-5 py-3 text-sm hover:bg-ice/60 dark:border-white/[0.06] dark:hover:bg-white/[0.03]">
                    <span class="min-w-0">
                        <span class="block truncate font-display font-medium">{{ $payment->order?->customer_name }}</span>
                        <span class="block truncate text-xs muted">{{ $payment->order?->number }} · {{ $payment->created_at->format('j M') }}</span>
                    </span>
                    <span class="text-right">
                        <span class="block font-display font-semibold">{{ money($payment->amount, $payment->currency) }}</span>
                        @if ($payment->order)<x-status-badge :status="$payment->order->payment_status" />@endif
                    </span>
                </a>
            @empty
                <p class="border-t border-line px-5 py-8 text-center text-sm muted dark:border-white/[0.06]">No payments yet.</p>
            @endforelse
        </section>
    </aside>
</div>
@endsection
