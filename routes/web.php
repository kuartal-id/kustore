<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\KuartalIdController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Dashboard\LinkController;
use App\Http\Controllers\Dashboard\OrderController;
use App\Http\Controllers\Dashboard\OverviewController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\StoreController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

// Marketing & legal
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');

// Kuartal ID (primary sign-in)
Route::middleware('throttle:oidc')->group(function () {
    Route::get('/auth/kuartal/redirect', [KuartalIdController::class, 'redirect'])->name('auth.kuartal.redirect');
    Route::get('/auth/kuartal/callback', [KuartalIdController::class, 'callback'])->name('auth.kuartal.callback');
});

// Email + password (secondary)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:register');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:verification')->name('verification.send');

    Route::get('/onboarding', [OnboardingController::class, 'username'])->name('onboarding.username');
    Route::post('/onboarding', [OnboardingController::class, 'storeUsername']);
    Route::get('/onboarding/account-type', [OnboardingController::class, 'type'])->name('onboarding.type');
    Route::post('/onboarding/account-type', [OnboardingController::class, 'storeType']);

    Route::middleware('has.store')->prefix('dashboard')->group(function () {
        Route::get('/', [OverviewController::class, 'index'])->name('dashboard');
        Route::post('/publish', [OverviewController::class, 'togglePublish'])->name('dashboard.publish');

        Route::get('/store', [StoreController::class, 'edit'])->name('dashboard.store.edit');
        Route::put('/store', [StoreController::class, 'update'])->name('dashboard.store.update');

        Route::get('/links', [LinkController::class, 'index'])->name('dashboard.links.index');
        Route::post('/links', [LinkController::class, 'store'])->name('dashboard.links.store');
        Route::get('/links/{link}/edit', [LinkController::class, 'edit'])->name('dashboard.links.edit');
        Route::put('/links/{link}', [LinkController::class, 'update'])->name('dashboard.links.update');
        Route::delete('/links/{link}', [LinkController::class, 'destroy'])->name('dashboard.links.destroy');
        Route::post('/links/{link}/move/{direction}', [LinkController::class, 'move'])
            ->whereIn('direction', ['up', 'down'])->name('dashboard.links.move');

        Route::get('/products', [ProductController::class, 'index'])->name('dashboard.products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('dashboard.products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('dashboard.products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('dashboard.products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('dashboard.products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('dashboard.products.destroy');

        Route::get('/orders', [OrderController::class, 'index'])->name('dashboard.orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('dashboard.orders.show');
        Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('dashboard.orders.update');
    });

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        Route::post('/stores/{store:id}/suspend', [AdminController::class, 'toggleSuspend'])->name('admin.stores.suspend');
    });
});

// Link click counter + redirect
Route::get('/go/{link}', [StorefrontController::class, 'go'])->whereNumber('link')->name('links.go');

// Public storefronts (keep last: catch-all usernames)
Route::pattern('username', '[a-z0-9][a-z0-9_-]{1,28}[a-z0-9]');
Route::get('/{username}', [StorefrontController::class, 'show'])->name('storefront.show');
Route::get('/{username}/product/{slug}', [StorefrontController::class, 'product'])->name('storefront.product');
Route::get('/{username}/product/{slug}/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/{username}/product/{slug}/checkout', [CheckoutController::class, 'store'])
    ->middleware('throttle:checkout')->name('checkout.store');
Route::get('/{username}/order/{number}', [CheckoutController::class, 'confirmation'])
    ->middleware('signed')->name('checkout.confirmation');
