<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'KuStore' }}</title>
    <meta name="description" content="Your store. Your links. Your business.">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        display: ['Poppins', 'Arial', 'sans-serif'],
                        subhead: ['Poppins', 'Arial', 'sans-serif'],
                        sans: ['Arial Nova', 'Arial', 'Helvetica', 'sans-serif'],
                    },
                    colors: {
                        navy: '#18333d',
                        deep: '#0b1319',
                        green: '#36cc64',
                        'green-dark': '#1f8642',
                        ice: '#eef3f4',
                        muted: '#6c7a86',
                        border: '#dfe9ed',
                        'surface-dark': '#0f1c22',
                        'surface-dark-alt': '#152730',
                    },
                },
            },
        };
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Arial+Nova:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-screen bg-ice text-deep antialiased">
    <header class="sticky top-0 z-40 border-b border-border/80 bg-white/80 backdrop-blur-xl">
        <div class="k-container flex h-[74px] items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="kuartal-brand" aria-label="Kuartal home">
                <img
                    src="https://raw.githubusercontent.com/kuartal-id/terminal/main/web/public/kuartal-icon-mark.png"
                    alt="Kuartal"
                    class="brand-mark"
                >
                <span class="brand-wordmark">
                    <span>Kuartal</span>
                    <span class="brand-product">Store</span>
                </span>
            </a>

            <nav class="flex items-center gap-1.5 sm:gap-2">
                @auth
                    <a class="k-nav-link" href="{{ route('dashboard.index') }}">Dashboard</a>
                    <a class="k-nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log out</a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                @else
                    <a class="k-nav-link" href="{{ route('login') }}">Log in</a>
                    <a class="k-btn-primary" href="{{ route('register') }}">Create account</a>
                @endauth
            </nav>
        </div>
    </header>

    @yield('content')

    <footer class="mt-16 border-t border-border bg-white/60 py-10">
        <div class="k-container flex flex-col gap-2 text-sm text-muted sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <img src="https://raw.githubusercontent.com/kuartal-id/terminal/main/web/public/kuartal-icon-mark.png" alt="Kuartal" class="w-5 h-5 rounded-md object-cover">
                <span class="font-display font-semibold text-navy">Kuartal</span>
            </div>
            <p class="m-0">Built for your store, your links, your business.</p>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
