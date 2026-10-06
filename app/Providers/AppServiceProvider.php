<?php

namespace App\Providers;

use App\Payments\PaymentManager;
use App\Payments\PaymentProviderInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentManager::class);
        $this->app->bind(PaymentProviderInterface::class, fn ($app) => $app->make(PaymentManager::class)->provider());
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Password::defaults(fn () => Password::min(10)->letters()->numbers());

        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by('login:'.$email.'|'.$request->ip()),
                Limit::perMinute(20)->by('login-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('register', fn (Request $request) => Limit::perHour(10)->by('register:'.$request->ip()));

        RateLimiter::for('oidc', fn (Request $request) => Limit::perMinute(20)->by('oidc:'.$request->ip()));

        RateLimiter::for('checkout', fn (Request $request) => [
            Limit::perMinute(6)->by('checkout:'.$request->ip()),
            Limit::perHour(40)->by('checkout-h:'.$request->ip()),
        ]);

        RateLimiter::for('verification', fn (Request $request) => Limit::perMinute(3)->by('verify:'.($request->user()?->id ?: $request->ip())));
    }
}
