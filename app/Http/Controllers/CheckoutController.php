<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request, string $username, string $slug): View
    {
        $store = StorefrontController::visibleStore($username);
        $product = StorefrontController::purchasableProduct($store, $slug);

        return view('storefront.checkout', [
            'store' => $store,
            'product' => $product,
            'quantity' => max(1, min((int) $request->query('quantity', 1), max(1, $product->maxPurchasable()))),
        ]);
    }

    public function store(Request $request, CheckoutService $checkout, string $username, string $slug): RedirectResponse
    {
        $store = StorefrontController::visibleStore($username);
        $product = StorefrontController::purchasableProduct($store, $slug);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9+()\s-]{6,40}$/'],
            'shipping_address' => [$product->requires_shipping ? 'required' : 'nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        [$order] = $checkout->placeOrder($product, $data);

        return redirect()->to(URL::signedRoute('checkout.confirmation', [$store->username, $order->number]));
    }

    public function confirmation(string $username, string $number): View
    {
        $store = StorefrontController::visibleStore($username);
        $order = Order::where('store_id', $store->id)->where('number', $number)->with('items', 'payments')->firstOrFail();

        return view('storefront.confirmation', [
            'store' => $store,
            'order' => $order,
            'instructions' => $order->payments->last()?->payload['instructions'] ?? $store->payment_instructions,
        ]);
    }
}
