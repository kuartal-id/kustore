<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /** Public pages plus every live store and its active products. Unpublished/suspended stores never appear. */
    public function index(): Response
    {
        $urls = [
            ['loc' => route('home'), 'lastmod' => null],
            ['loc' => route('support'), 'lastmod' => null],
            ['loc' => route('terms'), 'lastmod' => null],
            ['loc' => route('privacy'), 'lastmod' => null],
        ];

        Store::visible()
            ->with(['products' => fn ($q) => $q->active()->select('id', 'store_id', 'slug', 'updated_at')])
            ->select('id', 'username', 'updated_at')
            ->orderBy('id')
            ->chunk(500, function ($stores) use (&$urls) {
                foreach ($stores as $store) {
                    $urls[] = ['loc' => $store->url(), 'lastmod' => $store->updated_at];
                    foreach ($store->products as $product) {
                        $urls[] = ['loc' => route('storefront.product', [$store->username, $product->slug]), 'lastmod' => $product->updated_at];
                    }
                }
            });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
