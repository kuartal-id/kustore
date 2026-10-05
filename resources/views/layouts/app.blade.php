<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title ?? 'Kustore' }}</title>
<meta name="description" content="Kustore — your store, links and business in one place.">
<script>
tailwind.config={darkMode:'class',theme:{extend:{fontFamily:{display:['Poppins','Arial','sans-serif'],subhead:['Poppins','Arial','sans-serif'],sans:['Arial Nova','Arial','Helvetica','sans-serif']},colors:{navy:'#1c3640','deep':'#0b1319','green':'#36cc64','green-dark':'#28a852','ice':'#f1fbff','muted':'#6c7a86','border':'#e1e8ed','surface-dark':'#0f1c22','surface-dark-alt':'#152730'}}}};
</script>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v=20261005-2">
</head>
<body class="min-h-screen bg-ice text-deep antialiased">
<header class="sticky top-0 z-50 border-b border-border/80 bg-white/90 backdrop-blur-xl">
<div class="k-container flex h-[74px] items-center justify-between gap-4">
<a href="{{ route('home') }}" class="kustore-brand" aria-label="Kustore home">
<span class="kustore-mark" aria-hidden="true">re</span>
<span class="kustore-word">Kustore</span>
</a>
<nav class="flex items-center gap-1 sm:gap-2">
@auth
<a class="k-nav-link" href="{{ route('dashboard.index') }}">Dashboard</a>
<form method="POST" action="{{ route('logout') }}">@csrf<button class="k-nav-link">Log out</button></form>
@else
<a class="k-nav-link" href="{{ route('login') }}">Log in</a>
<a class="k-btn-primary" href="{{ route('register') }}">Create account</a>
@endauth
</nav>
</div>
</header>
@yield('content')
<footer class="mt-20 border-t border-border bg-white py-12">
<div class="k-container flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
<div><div class="kustore-brand"><span class="kustore-mark kustore-mark-small">re</span><span class="kustore-word">Kustore</span></div><p class="mt-3 text-sm text-muted">Your store. Your links. Your business.</p></div>
<p class="m-0 text-sm text-muted">Part of the Kuartal ecosystem.</p>
</div>
</footer>
<script src="{{ asset('js/app.js') }}?v=20261005-2"></script>
</body>
</html>