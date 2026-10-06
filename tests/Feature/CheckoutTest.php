<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Payments\ManualPaymentProvider;
use App\Payments\PaymentProviderInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->store = Store::factory()->published()->create([
            'username' => 'sekar',
            'payment_instructions' => 'Transfer to BCA 0123456789 a.n. Sekar',
        ]);
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'name' => 'Dewi Lestari',
            'email' => 'Dewi@Example.com',
            'phone' => '0812 3456 7890',
            'shipping_address' => 'Jl. Malioboro 1, Yogyakarta',
            'quantity' => 2,
        ], $extra);
    }

    public function test_checkout_creates_pending_order_decrements_stock_and_shows_instructions(): void
    {
        $product = Product::factory()->for($this->store)->create(['slug' => 'tote', 'price' => 289000, 'sale_price' => 249000, 'stock_quantity' => 5]);

        $this->get('/sekar/product/tote/checkout')->assertOk()->assertSee('Shipping address');

        $response = $this->post('/sekar/product/tote/checkout', $this->payload());

        $order = Order::with('items', 'payments')->sole();
        $response->assertRedirect();
        $this->assertStringContainsString('/sekar/order/'.$order->number, $response->headers->get('Location'));

        $this->assertSame('pending', $order->payment_status->value);
        $this->assertSame('unfulfilled', $order->fulfillment_status->value);
        $this->assertSame('manual', $order->payment_provider);
        $this->assertSame(498000, $order->total);
        $this->assertSame('dewi@example.com', $order->customer_email);
        $this->assertSame(2, $order->items->first()->quantity);
        $this->assertSame(249000, $order->items->first()->unit_price);
        $this->assertSame('pending', $order->payments->first()->status);
        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('customers', ['store_id' => $this->store->id, 'email' => 'dewi@example.com']);

        $this->get($response->headers->get('Location'))->assertOk()
            ->assertSee($order->number)->assertSee('Transfer to BCA 0123456789 a.n. Sekar');
    }

    public function test_confirmation_requires_a_valid_signature(): void
    {
        $product = Product::factory()->for($this->store)->create(['slug' => 'tote']);
        $this->post('/sekar/product/tote/checkout', $this->payload());
        $order = Order::sole();

        $this->get('/sekar/order/'.$order->number)->assertForbidden();
    }

    public function test_checkout_is_blocked_when_out_of_stock_or_quantity_exceeds_stock(): void
    {
        Product::factory()->for($this->store)->create(['slug' => 'soldout', 'stock_quantity' => 0]);
        $few = Product::factory()->for($this->store)->create(['slug' => 'few', 'stock_quantity' => 1]);

        $this->post('/sekar/product/soldout/checkout', $this->payload(['quantity' => 1]))->assertSessionHasErrors('quantity');
        $this->post('/sekar/product/few/checkout', $this->payload(['quantity' => 2]))->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $few->fresh()->stock_quantity);
    }

    public function test_unlimited_stock_is_not_decremented_and_digital_needs_no_address(): void
    {
        $product = Product::factory()->for($this->store)->create([
            'slug' => 'ebook', 'type' => 'digital', 'requires_shipping' => false,
            'unlimited_stock' => true, 'stock_quantity' => null,
        ]);

        $this->get('/sekar/product/ebook/checkout')->assertOk()->assertDontSee('Shipping address');
        $this->post('/sekar/product/ebook/checkout', $this->payload(['shipping_address' => null, 'quantity' => 3]))
            ->assertSessionHasNoErrors();

        $this->assertNull($product->fresh()->stock_quantity);
        $this->assertNull(Order::sole()->shipping_address);
    }

    public function test_physical_products_require_a_shipping_address(): void
    {
        Product::factory()->for($this->store)->create(['slug' => 'tote', 'requires_shipping' => true]);
        $this->post('/sekar/product/tote/checkout', $this->payload(['shipping_address' => '']))
            ->assertSessionHasErrors('shipping_address');
    }

    public function test_cannot_checkout_from_unpublished_store_or_inactive_product(): void
    {
        $draft = Store::factory()->create(['username' => 'draft']);
        Product::factory()->for($draft)->create(['slug' => 'x']);
        Product::factory()->for($this->store)->create(['slug' => 'off', 'is_active' => false]);

        $this->post('/draft/product/x/checkout', $this->payload())->assertNotFound();
        $this->post('/sekar/product/off/checkout', $this->payload())->assertNotFound();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_is_rate_limited(): void
    {
        Product::factory()->for($this->store)->create(['slug' => 'tote', 'unlimited_stock' => true]);
        for ($i = 0; $i < 6; $i++) {
            $this->post('/sekar/product/tote/checkout', $this->payload(['quantity' => 1]));
        }
        $this->post('/sekar/product/tote/checkout', $this->payload(['quantity' => 1]))->assertStatus(429);
    }

    public function test_payment_provider_is_resolved_from_config(): void
    {
        $this->assertInstanceOf(ManualPaymentProvider::class, app(PaymentProviderInterface::class));

        config(['kustore.payment_provider' => 'does-not-exist']);
        $this->expectException(\InvalidArgumentException::class);
        app(PaymentProviderInterface::class);
    }
}
