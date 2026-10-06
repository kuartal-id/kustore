<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-default-theme="@yield('default_theme', 'system')">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'Kustore by Kuartal')</title>
    <meta name="description" content="@yield('description', 'Kustore by Kuartal. One person. One identity. One storefront. Your link-in-bio, personal site and shop in one place.')">
    @hasSection('canonical')<link rel="canonical" href="@yield('canonical')">@endif
    @hasSection('noindex')<meta name="robots" content="noindex, nofollow">@endif
    @stack('meta')
    <meta name="theme-color" content="#F1FBFF" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0A161B" media="(prefers-color-scheme: dark)">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preload" href="{{ asset('fonts/poppins-latin-600-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset_v('css/app.css') }}">
    <script src="{{ asset_v('js/theme.js') }}"></script>
    <script src="{{ asset_v('js/app.js') }}" defer></script>
</head>
<body class="@yield('body_class', 'min-h-dvh')">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 btn btn-primary btn-sm">Skip to content</a>
    @yield('body')
</body>
</html>
