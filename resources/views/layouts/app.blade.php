<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title ?? 'KuStore' }}</title><meta name="description" content="Your store. Your links. Your business.">
<script>tailwind.config={darkMode:'class',theme:{extend:{fontFamily:{display:['Poppins','Arial','sans-serif'],subhead:['Poppins','Arial','sans-serif'],sans:['Arial Nova','Arial','Helvetica','sans-serif']},colors:{navy:'#1c3640','navy-deep':'#0b1319',green:'#36cc64','green-500':'#28a852','gray-bg':'#f1fbff','gray-border':'#e1e8ed','gray-muted':'#6c7a86','surface-dark':'#0f1c22','surface-dark-alt':'#152730'},borderRadius:{card:'14px',pill:'999px'}}}};</script>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-screen">
<header class="sticky top-0 z-40 border-b border-gray-border/80 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-surface-dark/90">
<div class="k-container flex h-[72px] items-center justify-between">
<a href="{{ route('home') }}" class="font-display text-lg font-semibold tracking-tight text-navy dark:text-white">Ku<span class="text-green-500">Store</span></a>
<nav class="flex items-center gap-1 sm:gap-2">
@auth<a class="rounded-pill px-4 py-2 font-subhead text-sm font-medium text-navy/80 hover:bg-gray-bg hover:text-navy dark:text-white/80 dark:hover:bg-white/5" href="{{ route('dashboard.index') }}">Dashboard</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="k-btn-outline !px-4 !py-2">Log out</button></form>
@else<a class="rounded-pill px-4 py-2 font-subhead text-sm font-medium text-navy/80 hover:bg-gray-bg hover:text-navy dark:text-white/80 dark:hover:bg-white/5" href="{{ route('login') }}">Log in</a><a class="k-btn-primary !px-4 !py-2" href="{{ route('register') }}">Create a store</a>@endauth
<button aria-label="Toggle theme" data-theme-toggle class="rounded-pill px-3 py-2 text-navy hover:bg-gray-bg dark:text-white dark:hover:bg-white/5">◐</button>
</nav></div></header>
@yield('content')
<footer class="mt-16 border-t border-gray-border bg-white/60 py-10 dark:border-white/10 dark:bg-transparent"><div class="k-container flex flex-col gap-2 text-sm text-gray-muted sm:flex-row sm:items-center sm:justify-between"><span>© {{ date('Y') }} KuStore by Kuartal.</span><span>Your store. Your links. Your business.</span></div></footer>
<script src="{{ asset('js/app.js') }}"></script>
</body></html>