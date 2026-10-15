@extends('layouts.marketing')

@section('title', 'Help and support · Kustore')
@section('description', 'Answers for sellers and buyers on Kustore, and how to contact us.')
@section('canonical', route('support'))

@php
    $email = config('kustore.support_email');
    $sellers = [
        ['How do I start?', 'Sign in with your Kuartal ID (or an email and password), pick your username and you get your page at kustore.id/yourname. Add a photo, your links and your products, then press Publish on the Overview page.'],
        ['What does it cost?', 'Kustore is free to start. You keep everything customers pay you, because payments go straight to you.'],
        ['How do customers pay me?', 'Today payments are manual: bank transfer, e-wallet or QRIS, using the instructions you write on the Payments page. You can upload your own QRIS code and add your WhatsApp number so buyers can send proof of payment. When the money arrives, open the order and mark it as paid. Automatic online payments are coming.'],
        ['How do I know when someone orders?', 'New orders appear under Orders straight away, and we email you if "Email me when someone orders" is on (Payments page). The buyer also gets an email with your payment instructions.'],
        ['Can I sell digital products and services?', 'Yes. Choose Digital or Service when you add a product. They don\'t ask the buyer for a shipping address, and you can set unlimited stock.'],
        ['How many photos can a product have?', 'Up to six: one main photo and five more. Buyers can tap through them on the product page.'],
        ['Can I change my username?', 'Yes, in Settings. Your old link stops working right away, so update it wherever you shared it.'],
        ['Where are my numbers?', 'Analytics shows page views, visitors, link clicks, product views, orders and sales for the last 7, 30 or 90 days. Customers lists everyone who has ordered, and you can export them as CSV.'],
        ['How do I download or delete my data?', 'Settings › Your data downloads everything as a file. Settings › Delete account removes your page, products, photos, orders and customers for good.'],
    ];
    $buyers = [
        ['I placed an order. What now?', 'Follow the payment instructions on your order page (also in your order email) and include your order number. The seller confirms your payment and arranges delivery.'],
        ['Who do I contact about my order?', 'The seller. Their contact details and WhatsApp (if they added it) are on their Kustore page and your order page. Kustore doesn\'t hold your money; you pay the seller directly.'],
        ['Something on a store looks wrong or unsafe', 'Tell us at the email below with the store link. We review reports and suspend stores that break the rules.'],
    ];
@endphp

@push('meta')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn ($qa) => ['@type' => 'Question', 'name' => $qa[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]]], array_merge($sellers, $buyers)),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@section('content')
<section class="container-k max-w-4xl py-14 sm:py-20">
    <p class="eyebrow">Help and support</p>
    <h1 class="mt-3 text-4xl font-semibold sm:text-5xl">How can we help?</h1>
    <p class="mt-4 max-w-2xl text-lg leading-relaxed muted">Quick answers for sellers and buyers. Can't find yours? Email us and a person will reply.</p>

    <div class="mt-10 grid gap-4 sm:grid-cols-3">
        <a href="mailto:{{ $email }}" class="card card-pad hover:border-navy/30 dark:hover:border-white/20">
            <span class="grid size-10 place-items-center rounded-xl bg-ice text-navy dark:bg-white/5 dark:text-white">{{ icon('mail', 'size-[18px]') }}</span>
            <p class="mt-4 font-display font-semibold">Email us</p>
            <p class="mt-1 text-sm break-all muted">{{ $email }}</p>
        </a>
        <a href="https://id.kuartal.id" rel="noopener" class="card card-pad hover:border-navy/30 dark:hover:border-white/20">
            <span class="grid size-10 place-items-center rounded-xl bg-ice text-navy dark:bg-white/5 dark:text-white">{{ icon('key-round', 'size-[18px]') }}</span>
            <p class="mt-4 font-display font-semibold">Sign-in help</p>
            <p class="mt-1 text-sm muted">Kuartal ID accounts are managed at id.kuartal.id.</p>
        </a>
        <a href="{{ route('password.request') }}" class="card card-pad hover:border-navy/30 dark:hover:border-white/20">
            <span class="grid size-10 place-items-center rounded-xl bg-ice text-navy dark:bg-white/5 dark:text-white">{{ icon('lock', 'size-[18px]') }}</span>
            <p class="mt-4 font-display font-semibold">Forgot your password?</p>
            <p class="mt-1 text-sm muted">For email and password accounts.</p>
        </a>
    </div>

    @foreach ([['For sellers', $sellers, 'sellers'], ['For buyers', $buyers, 'buyers']] as [$heading, $items, $id])
        <h2 id="{{ $id }}" class="mt-14 text-2xl font-semibold">{{ $heading }}</h2>
        <div class="card mt-5 overflow-hidden">
            @foreach ($items as [$q, $a])
                <details class="group border-line px-5 py-4 sm:px-6 dark:border-white/[0.06] {{ $loop->first ? '' : 'border-t' }}">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-display font-medium">
                        {{ $q }}
                        <span class="shrink-0 transition-transform group-open:rotate-90">{{ icon('chevron-right', 'size-4') }}</span>
                    </summary>
                    <p class="mt-3 leading-relaxed muted">{{ $a }}</p>
                </details>
            @endforeach
        </div>
    @endforeach

    <h2 id="delete-account" class="mt-14 text-2xl font-semibold">Deleting your account</h2>
    <div class="prose-k mt-4">
        <p>Sign in, open <strong>Settings</strong> and use <strong>Delete account</strong>. Type your username to confirm. This permanently deletes your Kustore page, links, products and photos, orders and customer list, in the app and on the web.</p>
        <p>If you sign in with Kuartal ID, this deletes your Kustore account only. To delete the Kuartal ID itself, go to <a href="https://id.kuartal.id/account/delete" class="link" rel="noopener">id.kuartal.id/account/delete</a>.</p>
        <p>Can't sign in? Email <a href="mailto:{{ $email }}" class="link">{{ $email }}</a> from the address on your account and we'll help.</p>
    </div>
</section>
@endsection
