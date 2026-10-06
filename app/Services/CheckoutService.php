<?php

namespace App\Services;

use App\Enums\FulfillmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Payments\PaymentManager;
use App\Payments\PaymentResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private PaymentManager $payments) {}

    /**
     * @param  array{name:string,email:string,phone?:?string,shipping_address?:?string,notes?:?string,quantity:int}  $data
     * @return array{0: Order, 1: PaymentResult}
     */
    public function placeOrder(Product $product, array $data): array
    {
        $provider = $this->payments->provider();

        return DB::transaction(function () use ($product, $data, $provider) {
            /** @var Product $locked */
            $locked = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            $store = $product->store;
            $qty = (int) $data['quantity'];

            if (! $locked->is_active) {
                throw ValidationException::withMessages(['quantity' => 'This product is no longer available.']);
            }
            if (! $locked->inStock($qty)) {
                throw ValidationException::withMessages(['quantity' => $locked->unlimited_stock || $locked->stock_quantity > 0
                    ? "Only {$locked->stock_quantity} left in stock."
                    : 'Sorry, this product is sold out.']);
            }

            $customer = Customer::updateOrCreate(
                ['store_id' => $store->id, 'email' => strtolower($data['email'])],
                ['name' => $data['name'], 'phone' => $data['phone'] ?? null],
            );

            $unit = $locked->effectivePrice();
            $subtotal = $unit * $qty;

            $order = Order::create([
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'number' => Order::generateNumber(),
                'customer_name' => $data['name'],
                'customer_email' => strtolower($data['email']),
                'customer_phone' => $data['phone'] ?? null,
                'shipping_address' => $locked->requires_shipping ? ($data['shipping_address'] ?? null) : null,
                'notes' => $data['notes'] ?? null,
                'currency' => $locked->currency,
                'subtotal' => $subtotal,
                'shipping_total' => 0,
                'total' => $subtotal,
                'payment_provider' => $provider->key(),
                'payment_status' => PaymentStatus::Pending->value,
                'fulfillment_status' => FulfillmentStatus::Unfulfilled->value,
            ]);

            $order->items()->create([
                'product_id' => $locked->id,
                'product_name' => $locked->name,
                'product_type' => $locked->type->value,
                'unit_price' => $unit,
                'quantity' => $qty,
                'line_total' => $subtotal,
            ]);

            if (! $locked->unlimited_stock) {
                $locked->decrement('stock_quantity', $qty);
            }

            $order->setRelation('store', $store);

            return [$order, $provider->initiate($order)];
        });
    }
}
