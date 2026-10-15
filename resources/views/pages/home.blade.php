@extends('layouts.marketing')

@section('title', 'Kustore by Kuartal · One person. One identity. One storefront.')
@section('canonical', route('home'))
@push('meta')
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Kustore">
    <meta property="og:title" content="Kustore by Kuartal">
    <meta property="og:description" content="Your link-in-bio, personal site and shop in one place. Built for Indonesia.">
    <meta property="og:url" content="{{ route('home') }}">
    <meta property="og:image" content="{{ asset('images/brand/kustore-og.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Kustore">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ asset('images/brand/kustore-og.png') }}">
@endpush

@section('content')
{{-- Hero --}}
<section class="container-k grid items-center gap-12 pt-12 pb-16 sm:pt-20 lg:grid-cols-[1.1fr_0.9fr] lg:gap-16 lg:pt-24 lg:pb-24">
    <div>
        <p class="eyebrow">Kustore by Kuartal</p>
        <h1 class="mt-5 text-[40px] leading-[1.05] font-semibold text-balance sm:text-6xl lg:text-[64px]">
            One person.<br>One identity.<br>One storefront.
        </h1>
        <p class="mt-6 max-w-xl text-lg leading-relaxed muted">
            Kustore puts your links, your profile and the things you sell on one clean page,
            at an address that is yours: <span class="font-semibold text-navy dark:text-white">kustore.id/yourname</span>.
        </p>
        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
            <x-kuartal-button />
            <a href="{{ route('register') }}" class="btn btn-secondary btn-lg">Create your Kustore</a>
        </div>
        <p class="mt-4 text-sm muted">Free to start. Sign in with your Kuartal ID, the same account you use across Kuartal.</p>
    </div>

    {{-- Storefront preview: Diemas Hawkins, founder of Kuartal. Links to the live page once @diemas is published. --}}
    <div class="relative mx-auto w-full max-w-sm lg:max-w-none">
        @if ($featured)<a href="{{ $featured->url() }}" class="block rounded-[28px] focus-visible:outline-2 focus-visible:outline-offset-4" aria-label="See Diemas Hawkins on Kustore">@endif
        <div class="card mx-auto max-w-[360px] p-6 shadow-lift" @unless ($featured) aria-hidden="true" @endunless>
            <div class="flex flex-col items-center text-center">
                <picture>
                    <source srcset="{{ asset_v('images/home/diemas-hawkins.webp') }}" type="image/webp">
                    <img src="{{ asset_v('images/home/diemas-hawkins.jpg') }}" alt="" width="72" height="72" class="size-[72px] rounded-full object-cover ring-1 ring-line dark:ring-white/10">
                </picture>
                <p class="mt-4 flex items-center gap-1.5 font-display text-lg font-semibold">Diemas Hawkins <span class="text-green">{{ icon('badge-check', 'size-[18px]') }}</span></p>
                <p class="text-sm muted">@diemas · Lombok</p>
                <p class="mt-3 text-sm leading-relaxed text-navy/80 dark:text-white/75">Founder and CEO of Kuartal. Private markets, venture building and impact investing.</p>
            </div>
            <div class="mt-6 space-y-2.5">
                @foreach ([['globe', 'Kuartal'], ['instagram', 'Instagram'], ['whatsapp', 'Chat on WhatsApp']] as [$i, $t])
                    <div class="flex h-12 items-center gap-3 rounded-full border border-line px-4 text-sm font-medium dark:border-white/10">
                        <span class="text-navy dark:text-white">{{ icon($i, 'size-[18px]') }}</span><span class="flex-1 text-center font-display">{{ $t }}</span><span class="w-[18px]"></span>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 grid grid-cols-2 gap-3">
                @foreach ([['Handbooks', 'Kuartal', 'tag'], ['Team kits', 'Argapura Sport', 'shopping-bag']] as [$n, $p, $i])
                    <div class="rounded-2xl border border-line p-2.5 dark:border-white/10">
                        <div class="grid aspect-square place-items-center rounded-xl bg-ice text-navy/30 dark:bg-white/5 dark:text-white/25">{{ icon($i, 'size-7') }}</div>
                        <p class="mt-2.5 truncate font-display text-[13px] font-medium">{{ $n }}</p>
                        <p class="text-[13px] muted">{{ $p }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        @if ($featured)</a>@endif
        <div class="card absolute -bottom-5 left-0 hidden items-center gap-3 px-4 py-3 shadow-lift sm:flex lg:-left-6" aria-hidden="true">
            <span class="dot bg-green"></span>
            <span class="font-display text-sm font-medium">New order received</span>
        </div>
    </div>
</section>

{{-- Claim bar --}}
<section class="container-k pb-16 sm:pb-24">
    <a href="{{ route('login') }}" class="card flex flex-col items-start gap-4 p-5 transition-colors hover:border-navy/40 sm:flex-row sm:items-center sm:justify-between sm:p-6 dark:hover:border-white/20">
        <div class="flex min-w-0 items-center gap-2 font-display text-xl font-medium sm:text-2xl">
            <span class="muted">kustore.id/</span><span class="truncate">yourname</span>
        </div>
        <span class="btn btn-accent">Claim your username {{ icon('arrow-right', 'size-4') }}</span>
    </a>
</section>

{{-- Three in one --}}
<section class="border-y border-line/70 bg-white dark:border-white/[0.06] dark:bg-navy-900/40">
    <div class="container-k py-16 sm:py-24">
        <div class="max-w-2xl">
            <p class="eyebrow">Three things, one page</p>
            <h2 class="mt-4 text-3xl font-semibold sm:text-4xl">Everything people need to find you, trust you and buy from you.</h2>
        </div>
        <div class="mt-12 grid gap-10 sm:grid-cols-3 sm:gap-8">
            @foreach ([
                ['link', 'Link in bio', 'All your channels in one tidy list. Instagram, TikTok, WhatsApp, YouTube and more, in the order you choose.'],
                ['globe', 'Personal site', 'A real profile with your story, location and website. Clear typography, fast on any phone.'],
                ['shopping-bag', 'Storefront', 'Sell products, digital goods or services. Customers order in a minute, you get paid your way.'],
            ] as [$icon, $title, $text])
                <div>
                    <span class="grid size-11 place-items-center rounded-2xl bg-ice text-navy dark:bg-white/5 dark:text-white">{{ icon($icon, 'size-5') }}</span>
                    <h3 class="mt-5 text-lg font-semibold">{{ $title }}</h3>
                    <p class="mt-2 leading-relaxed muted">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- How it works --}}
<section id="how-it-works" class="container-k py-16 sm:py-24">
    <div class="grid gap-12 lg:grid-cols-[0.8fr_1.2fr]">
        <div>
            <p class="eyebrow">How it works</p>
            <h2 class="mt-4 text-3xl font-semibold sm:text-4xl">Live in five minutes.</h2>
            <p class="mt-4 max-w-md leading-relaxed muted">No design skills needed. Pick a username, add what matters, and share one link everywhere.</p>
        </div>
        <ol class="grid gap-4">
            @foreach ([
                ['Sign in with Kuartal ID', 'One secure account for every Kuartal product. No new password to remember.'],
                ['Choose your username', 'Your page lives at kustore.id/yourname. Short, memorable, yours.'],
                ['Add links and products', 'Set prices in Rupiah, upload a photo, write a short description.'],
                ['Publish and share', 'Put the link in your bio, WhatsApp status or business card.'],
            ] as $i => [$title, $text])
                <li class="card flex gap-5 p-5 sm:p-6">
                    <span class="font-display text-sm font-semibold text-muted dark:text-muted-dark">0{{ $i + 1 }}</span>
                    <div>
                        <h3 class="font-semibold">{{ $title }}</h3>
                        <p class="mt-1 text-[15px] leading-relaxed muted">{{ $text }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- Indonesia --}}
<section id="for-indonesia" class="container-k pb-16 sm:pb-24">
    <div class="card grid gap-10 p-6 sm:p-10 lg:grid-cols-2 lg:p-12">
        <div>
            <p class="eyebrow">Built for Indonesia</p>
            <h2 class="mt-4 text-3xl font-semibold">Prices in Rupiah. Payments the way your customers already pay.</h2>
            <p class="mt-4 leading-relaxed muted">Start with manual payments: bank transfer, e-wallet or your own QRIS code, with your own instructions. Buyers get an email with how to pay, you get an email for every order, and you mark orders as paid from your phone. Automatic online payments are coming.</p>
        </div>
        <ul class="grid gap-4 self-center">
            @foreach ([
                ['credit-card', 'Manual payments today', 'Bank transfer, e-wallet, QRIS. Your instructions, shown after every order.'],
                ['whatsapp', 'Share where people are', 'One tap to share on WhatsApp, Instagram, X, Facebook, LinkedIn and Telegram.'],
                ['shield', 'Private by default', 'No third-party trackers on your page. Simple, honest numbers.'],
            ] as [$icon, $title, $text])
                <li class="flex gap-4">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-ice text-navy dark:bg-white/5 dark:text-white">{{ icon($icon, 'size-[18px]') }}</span>
                    <div><p class="font-display font-semibold">{{ $title }}</p><p class="mt-0.5 text-[15px] muted">{{ $text }}</p></div>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- Apps --}}
@php $play = config('kustore.apps.play_store_url'); $appStore = config('kustore.apps.app_store_url'); @endphp
<section id="app" class="container-k pb-16 sm:pb-24">
    <div class="grid items-center gap-10 lg:grid-cols-2">
        <div>
            <p class="eyebrow">Kustore app</p>
            <h2 class="mt-4 text-3xl font-semibold sm:text-4xl">Run your store from your phone.</h2>
            <p class="mt-4 max-w-lg leading-relaxed muted">The Kustore app for Android and iPhone has everything the website has: add products with your camera, check new orders, mark them paid and share your link in one tap.</p>
            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                @if ($play)
                    <a href="{{ $play }}" class="btn btn-primary btn-lg" rel="noopener">{{ icon('smartphone', 'size-5') }} Get it on Google Play</a>
                @else
                    <span class="btn btn-secondary btn-lg cursor-default" aria-disabled="true">{{ icon('smartphone', 'size-5') }} Google Play · coming soon</span>
                @endif
                @if ($appStore)
                    <a href="{{ $appStore }}" class="btn btn-primary btn-lg" rel="noopener">{{ icon('smartphone', 'size-5') }} Download on the App Store</a>
                @else
                    <span class="btn btn-secondary btn-lg cursor-default" aria-disabled="true">{{ icon('smartphone', 'size-5') }} App Store · coming soon</span>
                @endif
            </div>
            @unless ($play && $appStore)
                <p class="mt-3 text-sm muted">Until then, add kustore.id to your home screen from your phone's browser.</p>
            @endunless
        </div>
        <ul class="grid gap-3 sm:grid-cols-2">
            @foreach ([
                ['image', 'Photos from your camera', 'Snap a product and it\'s on your page.'],
                ['receipt', 'Orders on the go', 'See who ordered and mark payments as received.'],
                ['share-2', 'Share in one tap', 'Send your link to WhatsApp, Instagram and more.'],
                ['chart-column', 'Your numbers', 'Views, clicks and sales, wherever you are.'],
            ] as [$i, $t, $d])
                <li class="card card-pad">
                    <span class="grid size-10 place-items-center rounded-xl bg-ice text-navy dark:bg-white/5 dark:text-white">{{ icon($i, 'size-[18px]') }}</span>
                    <p class="mt-4 font-display font-semibold">{{ $t }}</p>
                    <p class="mt-1 text-[15px] muted">{{ $d }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- Questions --}}
<section class="border-y border-line/70 bg-white dark:border-white/[0.06] dark:bg-navy-900/40">
    <div class="container-k grid gap-10 py-16 sm:py-24 lg:grid-cols-[0.8fr_1.2fr]">
        <div>
            <p class="eyebrow">Questions</p>
            <h2 class="mt-4 text-3xl font-semibold sm:text-4xl">Good to know.</h2>
            <p class="mt-4 max-w-md leading-relaxed muted">More answers on the <a href="{{ route('support') }}" class="link">help page</a>.</p>
        </div>
        <div class="card overflow-hidden">
            @foreach ([
                ['What does it cost?', 'Kustore is free to start, and payments go straight to you.'],
                ['Do I need a Kuartal ID?', 'It\'s the easiest way in, and the same account works across Kuartal. You can also sign up with an email and password.'],
                ['How do customers pay?', 'By bank transfer, e-wallet or your own QRIS code, following your instructions. You mark orders as paid. Automatic online payments are coming.'],
                ['Can I sell services or digital products?', 'Yes. Services and digital products skip the shipping address and can have unlimited stock.'],
            ] as [$q, $a])
                <details class="group border-line px-5 py-4 sm:px-6 dark:border-white/[0.06] {{ $loop->first ? '' : 'border-t' }}">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-display font-medium">{{ $q }}<span class="shrink-0 transition-transform group-open:rotate-90">{{ icon('chevron-right', 'size-4') }}</span></summary>
                    <p class="mt-3 leading-relaxed muted">{{ $a }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- Final CTA --}}
<section class="container-k py-20 sm:py-28">
    <div class="rounded-[28px] bg-navy px-6 py-12 text-center text-white sm:px-12 sm:py-16 dark:bg-navy-900 dark:ring-1 dark:ring-white/[0.07]">
        <h2 class="mx-auto max-w-2xl text-3xl font-semibold text-balance sm:text-4xl">Your name. Your page. Your store.</h2>
        <p class="mx-auto mt-4 max-w-lg text-white/70">Create your Kustore today and share one link that does it all.</p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('auth.kuartal.redirect') }}" class="btn btn-accent btn-lg">Continue with Kuartal ID</a>
            @if ($demo)
                <a href="{{ $demo->url() }}" class="btn btn-lg border border-white/20 text-white hover:border-white/50">See an example</a>
            @endif
        </div>
    </div>
</section>
@endsection
