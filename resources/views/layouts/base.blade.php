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
    <link rel="icon" href="{{ asset_v('favicon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="icon" href="{{ asset_v('favicon-32x32.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset_v('apple-touch-icon.png') }}" sizes="180x180">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
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
