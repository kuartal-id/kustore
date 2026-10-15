@extends('layouts.dashboard')
@section('page_title', 'Analytics')
@section('page_subtitle', 'Simple, private numbers from your own page. No third-party trackers.')
@section('page_actions')
    <nav class="flex gap-1.5" aria-label="Date range">
        @foreach ($ranges as $r)
            <a href="{{ route('dashboard.analytics', ['days' => $r]) }}" class="btn btn-sm {{ $days === $r ? 'btn-primary' : 'btn-secondary' }}" @if ($days === $r) aria-current="page" @endif>{{ $r }} days</a>
        @endforeach
    </nav>
@endsection

@section('content')
@php
    $nf = fn ($n) => number_format($n, 0, ',', '.');
    $cards = [
        ['Page views', $nf($totals['views']), 'eye'],
        ['Visitors', $nf($totals['visitors']), 'users'],
        ['Link clicks', $nf($totals['clicks']), 'link'],
        ['Product views', $nf($totals['product_views']), 'package'],
        ['Orders', $nf($totals['orders']), 'receipt'],
        ['Sales (paid)', money($totals['sales'], $store->currency), 'trending-up'],
    ];
    // Chart geometry (SVG attributes only: the CSP forbids inline styles).
    $n = count($series);
    $w = 720; $h = 200; $pad = 28;
    $slot = ($w - $pad) / max(1, $n);
    $bar = max(2, min(18, $slot - 2));          // 2px gap between bars
    $max = max(1, max(array_column($series, 'views')));
    $nice = (int) (ceil($max / max(1, 10 ** floor(log10($max)))) * 10 ** floor(log10($max)));
    $scale = ($h - 24) / max(1, $nice);
    $base = $h - 20;
@endphp

<section class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3" aria-label="Totals for the last {{ $days }} days">
    @foreach ($cards as [$label, $value, $icon])
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between muted"><span class="text-[13px] font-medium">{{ $label }}</span>{{ icon($icon, 'size-4') }}</div>
            <p class="mt-3 truncate font-display text-xl font-semibold sm:text-2xl">{{ $value }}</p>
        </div>
    @endforeach
</section>

<section class="card card-pad mt-4 sm:mt-6">
    <div class="flex items-baseline justify-between gap-4">
        <h2 class="text-base font-semibold">Page views per day</h2>
        <p class="text-xs muted">Last {{ $days }} days</p>
    </div>
    @if ($totals['views'] === 0)
        <p class="mt-6 rounded-2xl bg-ice p-6 text-center text-sm muted dark:bg-white/5">No visits yet. Share kustore.id/{{ $store->username }} to see numbers here.</p>
    @else
        <svg viewBox="0 0 {{ $w }} {{ $h }}" class="mt-4 h-auto w-full text-navy dark:text-green" role="img" aria-label="Bar chart of daily page views. The table below lists the same numbers.">
            {{-- Recessive grid: baseline, half and top --}}
            @foreach ([0, 0.5, 1] as $f)
                @php $gy = $base - $nice * $f * $scale; @endphp
                <line x1="{{ $pad }}" x2="{{ $w }}" y1="{{ $gy }}" y2="{{ $gy }}" class="stroke-line dark:stroke-white/10" stroke-width="1" />
                <text x="{{ $pad - 6 }}" y="{{ $gy + 4 }}" text-anchor="end" class="fill-muted dark:fill-muted-dark" font-size="10">{{ $nf($nice * $f) }}</text>
            @endforeach
            @foreach ($series as $i => $day)
                @php
                    $x = $pad + $i * $slot + ($slot - $bar) / 2;
                    $bh = $day['views'] * $scale;
                    $y = $base - $bh;
                    $r = min(4, $bar / 2, $bh);
                @endphp
                <g>
                    <title>{{ $day['date']->format('D j M') }}: {{ $nf($day['views']) }} views, {{ $nf($day['visitors']) }} visitors, {{ $nf($day['clicks']) }} link clicks</title>
                    {{-- Hit target bigger than the mark --}}
                    <rect x="{{ $pad + $i * $slot }}" y="0" width="{{ $slot }}" height="{{ $base }}" fill="transparent" />
                    @if ($bh > 0)
                        <path d="M{{ $x }},{{ $base }} V{{ $y + $r }} Q{{ $x }},{{ $y }} {{ $x + $r }},{{ $y }} H{{ $x + $bar - $r }} Q{{ $x + $bar }},{{ $y }} {{ $x + $bar }},{{ $y + $r }} V{{ $base }} Z" fill="currentColor" />
                    @endif
                </g>
                @if ($i === 0 || $i === $n - 1 || ($n > 14 && $i % 7 === 0 && $i < $n - 4) || ($n <= 14 && $n > 7 && $i % 3 === 0 && $i < $n - 2))
                    <text x="{{ $x + $bar / 2 }}" y="{{ $h - 4 }}" text-anchor="middle" class="fill-muted dark:fill-muted-dark" font-size="10">{{ $day['date']->format('j M') }}</text>
                @endif
            @endforeach
        </svg>
        <details class="mt-4 text-sm">
            <summary class="cursor-pointer font-medium muted">Show as a table</summary>
            <div class="mt-3 max-h-72 overflow-auto">
                <table class="w-full text-left">
                    <thead class="muted"><tr><th class="py-1.5 font-medium">Day</th><th class="py-1.5 text-right font-medium">Views</th><th class="py-1.5 text-right font-medium">Visitors</th><th class="py-1.5 text-right font-medium">Link clicks</th></tr></thead>
                    <tbody>
                        @foreach (array_reverse($series) as $day)
                            <tr class="border-t border-line dark:border-white/[0.06]"><td class="py-1.5">{{ $day['date']->format('D j M') }}</td><td class="py-1.5 text-right">{{ $nf($day['views']) }}</td><td class="py-1.5 text-right">{{ $nf($day['visitors']) }}</td><td class="py-1.5 text-right">{{ $nf($day['clicks']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    @endif
</section>

<div class="mt-4 grid gap-4 sm:mt-6 sm:gap-6 lg:grid-cols-3">
    @foreach ([
        ['Top links', $topLinks, fn ($row) => $row->link?->title ?? 'Deleted link', 'clicks'],
        ['Top products', $topProducts, fn ($row) => $row->product?->name ?? 'Deleted product', 'views'],
        ['Where visitors come from', $referrers, fn ($row) => $row->host !== '' ? $row->host : 'Direct or app', 'views'],
    ] as [$title, $rows, $label, $unit])
        <section class="card">
            <h2 class="p-5 pb-3 text-base font-semibold">{{ $title }}</h2>
            @php $top = max(1, (int) ($rows->max('c') ?? 1)); @endphp
            @forelse ($rows as $row)
                <div class="border-t border-line px-5 py-3 dark:border-white/[0.06]">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="min-w-0 truncate">{{ $label($row) }}</span>
                        <span class="shrink-0 font-display font-medium">{{ $nf($row->c) }} <span class="text-xs font-normal muted">{{ $unit }}</span></span>
                    </div>
                    <svg viewBox="0 0 100 4" preserveAspectRatio="none" class="mt-2 h-1 w-full text-navy dark:text-green" aria-hidden="true">
                        <rect width="100" height="4" rx="2" class="fill-ice dark:fill-white/10" />
                        <rect width="{{ round($row->c / $top * 100, 1) }}" height="4" rx="2" fill="currentColor" />
                    </svg>
                </div>
            @empty
                <p class="border-t border-line px-5 py-8 text-center text-sm muted dark:border-white/[0.06]">Nothing yet for this period.</p>
            @endforelse
        </section>
    @endforeach
</div>

<p class="mt-6 text-xs muted">Visits from you while signed in and from search-engine bots aren't counted. "Visitors" counts distinct visitors per day; Kustore never stores IP addresses.</p>
@endsection
