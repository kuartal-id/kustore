<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreLink;
use App\Services\Analytics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function __construct(private Analytics $analytics) {}

    public function show(Request $request, string $username): View
    {
        $store = self::visibleStore($username);
        $this->analytics->record($request, $store, AnalyticsEvent::STORE_VIEW);

        return view('storefront.show', [
            'store' => $store,
            'links' => $store->links()->where('is_visible', true)->get(),
            'products' => $store->products()->active()->with('mainImage')->ordered()->get(),
        ]);
    }

    public function product(Request $request, string $username, string $slug): View
    {
        $store = self::visibleStore($username);
        $product = $store->products()->active()->where('slug', $slug)->with('mainImage')->firstOrFail();
        $product->setRelation('store', $store);
        $this->analytics->record($request, $store, AnalyticsEvent::PRODUCT_VIEW, null, $product->id);

        return view('storefront.product', [
            'store' => $store,
            'product' => $product,
            'others' => $store->products()->active()->whereKeyNot($product->id)->with('mainImage')->ordered()->limit(4)->get(),
        ]);
    }

    public function go(Request $request, StoreLink $link): RedirectResponse
    {
        $store = $link->store;
        abort_unless($link->is_visible && $store && $store->isPubliclyVisible(), 404);

        $link->increment('clicks_count');
        $this->analytics->record($request, $store, AnalyticsEvent::LINK_CLICK, $link->id);

        return redirect()->away($link->url);
    }

    /** Unpublished or suspended stores are a plain 404 for everyone. */
    public static function visibleStore(string $username): Store
    {
        return Store::visible()->where('username', strtolower($username))->firstOrFail();
    }

    public static function purchasableProduct(Store $store, string $slug): Product
    {
        $product = $store->products()->active()->where('slug', $slug)->with('mainImage')->firstOrFail();
        $product->setRelation('store', $store);

        return $product;
    }
}
