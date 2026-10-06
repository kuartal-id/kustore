<?php

use App\Support\Icons;
use App\Support\Money;

if (! function_exists('asset_v')) {
    /** Public asset URL with a cache-busting version from the file's mtime. */
    function asset_v(string $path): string
    {
        $file = public_path($path);

        return asset($path).(is_file($file) ? '?v='.filemtime($file) : '');
    }
}

if (! function_exists('icon')) {
    function icon(string $name, string $class = 'size-5'): \Illuminate\Support\HtmlString
    {
        return new \Illuminate\Support\HtmlString(Icons::svg($name, $class));
    }
}

if (! function_exists('money')) {
    function money(?int $amount, ?string $currency = null): string
    {
        return Money::format($amount, $currency);
    }
}
