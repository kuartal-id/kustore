<?php

return [

    /*
    | Payment provider key. Resolved through the "payment_providers" map below,
    | so no provider class is hard-coded anywhere else in the app.
    */
    'payment_provider' => env('KUSTORE_PAYMENT_PROVIDER', 'manual'),

    'payment_providers' => [
        'manual' => App\Payments\ManualPaymentProvider::class,
        // 'xendit'  => App\Payments\XenditPaymentProvider::class,   (phase 2)
        // 'midtrans' => App\Payments\MidtransPaymentProvider::class, (phase 2)
    ],

    'currency' => env('KUSTORE_CURRENCY', 'IDR'),

    /*
    | Money is stored as integers in each currency's minor unit. IDR is used
    | without decimals in practice, so its exponent is 0 (Rp 150.000 => 150000).
    */
    'currencies' => [
        'IDR' => ['symbol' => 'Rp', 'exponent' => 0, 'thousands' => '.', 'decimal' => ','],
        'USD' => ['symbol' => '$', 'exponent' => 2, 'thousands' => ',', 'decimal' => '.'],
    ],

    'username' => [
        'min' => 3,
        'max' => 30,
        // lowercase letters, numbers, "-" and "_"; must start and end with a letter or number
        'pattern' => '/^[a-z0-9](?:[a-z0-9_-]*[a-z0-9])?$/',
    ],

    'reserved_usernames' => [
        // required list
        'admin', 'login', 'logout', 'register', 'dashboard', 'api', 'support', 'help',
        'kuartal', 'kustore', 'official', 'shop', 'store', 'auth', 'settings', 'onboarding',
        'checkout', 'product', 'assets', 'storage', 'public', 'terms', 'privacy', 'about',
        'pricing', 'www', 'mail', 'static',
        // routes and files used by the app itself
        'go', 'up', 'email', 'verify', 'password', 'css', 'js', 'fonts', 'images', 'img',
        'favicon', 'robots', 'sitemap', 'account', 'orders', 'products', 'links', 'me',
        'new', 'root', 'system', 'billing', 'payments', 'customers', 'analytics', 'security',
        'kuartalid', 'kuartal-id', 'kuartal_id', 'kustoreid', 'team', 'blog', 'docs', 'status',
    ],

    'account_types' => [
        'individual' => 'Individual',
        'business' => 'Business',
        'organisation' => 'Organisation',
    ],

    'categories' => [
        'Creator', 'Fashion', 'Food & Drink', 'Beauty', 'Art & Design', 'Music', 'Education',
        'Technology', 'Health & Fitness', 'Home & Living', 'Services', 'Community', 'Other',
    ],

    'layouts' => [
        'minimal' => 'Minimal',
        'commerce' => 'Commerce',
        'creator' => 'Creator',
        'professional' => 'Professional',
        'editorial' => 'Editorial',
    ],

    // Presets not yet designed individually render with the closest implemented one.
    'layout_map' => [
        'minimal' => 'minimal',
        'creator' => 'minimal',
        'commerce' => 'commerce',
        'professional' => 'commerce',
        'editorial' => 'commerce',
    ],

    'link_icons' => [
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'x' => 'X',
        'facebook' => 'Facebook',
        'linkedin' => 'LinkedIn',
        'threads' => 'Threads',
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
        'github' => 'GitHub',
        'discord' => 'Discord',
        'spotify' => 'Spotify',
        'website' => 'Website',
        'custom' => 'Custom',
    ],

    'uploads' => [
        'max_kb' => 4096,
        'max_dimension' => 1600,
    ],
];
