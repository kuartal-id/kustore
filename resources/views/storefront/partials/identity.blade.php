{{-- Name, handle, meta row --}}
<h1 class="flex items-center gap-1.5 {{ $center ?? false ? 'justify-center' : '' }} text-2xl font-semibold sm:text-[28px]">
    <span>{{ $store->display_name }}</span>
    <x-verified :store="$store" class="size-[22px]" />
</h1>
<p class="mt-1 text-[15px] muted">{{ '@'.$store->username }}</p>
@php
    $meta = array_filter([
        $store->category ? ['tag', $store->category, null] : null,
        $store->location ? ['map-pin', $store->location, null] : null,
        $store->website ? ['globe', preg_replace('#^https?://(www\.)?#', '', rtrim($store->website, '/')), $store->website] : null,
    ]);
@endphp
@if ($meta)
    <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5 text-sm muted {{ $center ?? false ? 'justify-center' : '' }}">
        @foreach ($meta as [$ic, $label, $href])
            <li class="flex items-center gap-1.5">{{ icon($ic, 'size-4') }}
                @if ($href)<a href="{{ $href }}" rel="noopener nofollow" target="_blank" class="hover:text-navy hover:underline dark:hover:text-white">{{ $label }}</a>@else{{ $label }}@endif
            </li>
        @endforeach
    </ul>
@endif
@if ($store->bio)
    <p class="mt-4 text-[15px] leading-relaxed whitespace-pre-line text-navy/85 dark:text-white/80 {{ $center ?? false ? 'mx-auto max-w-md' : 'max-w-2xl' }}">{{ $store->bio }}</p>
@endif
