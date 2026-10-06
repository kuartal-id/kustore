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

    {{-- Storefront preview (static illustration) --}}
    <div class="relative mx-auto w-full max-w-sm lg:max-w-none" aria-hidden="true">
        <div class="card mx-auto max-w-[360px] p-6 shadow-lift">
            <div class="flex flex-col items-center text-center">
                <span class="grid size-[72px] place-items-center rounded-full bg-navy font-display text-xl font-semibold text-white dark:bg-white dark:text-navy">RA</span>
                <p class="mt-4 flex items-center gap-1.5 font-display text-lg font-semibold">Rani Atelier <span class="text-green">{{ icon('badge-check', 'size-[18px]') }}</span></p>
                <p class="text-sm muted">@raniatelier · Bandung</p>
                <p class="mt-3 text-sm leading-relaxed text-navy/80 dark:text-white/75">Handmade batik bags and scarves. Made to order in small batches.</p>
            </div>
            <div class="mt-6 space-y-2.5">
                @foreach ([['instagram', 'Instagram'], ['whatsapp', 'Order via WhatsApp'], ['tiktok', 'Watch how it is made']] as [$i, $t])
                    <div class="flex h-12 items-center gap-3 rounded-full border border-line px-4 text-sm font-medium dark:border-white/10">
                        <span class="text-navy dark:text-white">{{ icon($i, 'size-[18px]') }}</span><span class="flex-1 text-center font-display">{{ $t }}</span><span class="w-[18px]"></span>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 grid grid-cols-2 gap-3">
                @foreach ([['Tote Parang', 'Rp 289.000'], ['Silk scarf', 'Rp 175.000']] as [$n, $p])
                    <div class="rounded-2xl border border-line p-2.5 dark:border-white/10">
                        <div class="grid aspect-square place-items-center rounded-xl bg-ice text-navy/30 dark:bg-white/5 dark:text-white/25">{{ icon('shopping-bag', 'size-7') }}</div>
                        <p class="mt-2.5 truncate font-display text-[13px] font-medium">{{ $n }}</p>
                        <p class="text-[13px] muted">{{ $p }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card absolute -bottom-5 left-0 hidden items-center gap-3 px-4 py-3 shadow-lift sm:flex lg:-left-6">
            <span class="dot bg-green"></span>
            <span class="font-display text-sm font-medium">New order · Rp 289.000</span>
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
            <p class="mt-4 leading-relaxed muted">Start with manual payments: bank transfer, e-wallet or QRIS, using your own instructions. You mark orders as paid from your phone. Payment gateways are on the roadmap.</p>
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

{{-- Final CTA --}}
<section class="container-k pb-20 sm:pb-28">
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
