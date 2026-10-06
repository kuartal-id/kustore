{{-- expects: $store, $title, $description, $canonical, $image (nullable), $type --}}
<meta property="og:site_name" content="Kustore">
<meta property="og:type" content="{{ $type ?? 'profile' }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:locale" content="id_ID">
@if (! empty($image))
<meta property="og:image" content="{{ $image }}">
@else
{{-- Fallback: Kustore logo on ice (1200x630) --}}
<meta property="og:image" content="{{ asset('images/brand/kustore-og.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Kustore">
@endif
<meta name="twitter:card" content="{{ ! empty($image) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
@if (! empty($image))<meta name="twitter:image" content="{{ $image }}">@endif
