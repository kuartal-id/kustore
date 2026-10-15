<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderReceivedNotification;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
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
        $this->sendOrderEmails($order->load('items', 'store.user'));

        return redirect()->to(URL::signedRoute('checkout.confirmation', [$store->username, $order->number]));
    }

    /** Emails never block a sale: if mail is down, the order still stands and the seller sees it in the dashboard. */
    private function sendOrderEmails(Order $order): void
    {
        try {
            $seller = $order->store->user;
            if ($order->store->notify_new_orders && $seller?->email) {
                $seller->notify(new NewOrderNotification($order));
            }
            Notification::route('mail', $order->customer_email)->notify(new OrderReceivedNotification($order));
        } catch (\Throwable $e) {
            Log::warning('checkout.order_email_failed', ['order' => $order->number, 'message' => $e->getMessage()]);
        }
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
