<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title ?? 'KuStore' }}</title>
<meta name="description" content="Your store. Your links. Your business.">
<script>
tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        display: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        subhead: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        sans: ['Arial Nova', 'Arial', 'Helvetica', 'ui-sans-serif', 'system-ui', 'sans-serif']
      },
      colors: {
        navy: '#18333d',
        'navy-deep': '#0b1319',
        green: '#36cc64',
        'green-500': '#28a852',
        'gray-bg': '#f7f9f9',
        'gray-border': '#e1e8ed',
        'gray-muted': '#6c7a86',
        'surface-dark': '#0f1c22',
        'surface-dark-alt': '#152730'
      },
      borderRadius: {
        card: '14px',
        pill: '999px'
      }
    }
  }
};
</script>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-screen">
<header class="border-b border-gray-border bg-white/90 dark:border-white/10 dark:bg-surface-dark/90">
<div class="k-container flex h-16 items-center justify-between">
<a href="{{ route('home') }}" class="font-display text-xl font-semibold text-navy dark:text-white">Ku<span class="text-green-500">Store</span></a>
<nav class="flex items-center gap-2">
@auth
<a class="text-sm" href="{{ route('dashboard.index') }}">Dashboard</a>
<form method="POST" action="{{ route('logout') }}">@csrf<button class="k-btn-outline !px-4 !py-2">Log out</button></form>
@else
<a class="text-sm" href="{{ route('login') }}">Log in</a>
<a class="k-btn-primary !px-4 !py-2" href="{{ route('register') }}">Create a store</a>
@endauth
<button data-theme-toggle class="px-3">◐</button>
</nav>
</div>
</header>
@yield('content')
<footer class="border-t border-gray-border py-10 dark:border-white/10">
<div class="k-container text-sm text-gray-muted">© {{ date('Y') }} KuStore by Kuartal. · Your store. Your links. Your business.</div>
</footer>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>