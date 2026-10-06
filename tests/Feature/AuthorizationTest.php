<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $alice;
    private User $bob;
    private Store $aliceStore;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alice = User::factory()->create();
        $this->aliceStore = Store::factory()->for($this->alice)->create(['display_name' => 'Alice Shop']);
        $this->bob = User::factory()->create();
        Store::factory()->for($this->bob)->create(['display_name' => 'Bob Shop']);
    }

    public function test_policy_prevents_editing_another_users_store(): void
    {
        $this->assertTrue($this->alice->can('update', $this->aliceStore));
        $this->assertFalse($this->bob->can('update', $this->aliceStore));
        $this->assertFalse($this->bob->can('publish', $this->aliceStore));
    }

    public function test_store_editor_only_changes_the_signed_in_users_store(): void
    {
        $this->actingAs($this->bob)->put('/dashboard/store', [
            'display_name' => 'Hacked', 'account_type' => 'individual', 'layout' => 'minimal', 'color_mode' => 'light',
            'user_id' => $this->alice->id, 'username' => 'stolen',
        ])->assertRedirect();

        $this->assertSame('Alice Shop', $this->aliceStore->fresh()->display_name);
        $this->assertSame('Hacked', $this->bob->store->fresh()->display_name);
        $this->assertNotSame('stolen', $this->bob->store->fresh()->username);
    }

    public function test_cannot_edit_or_delete_another_users_product(): void
    {
        $product = Product::factory()->for($this->aliceStore)->create(['name' => 'Original']);

        $this->actingAs($this->bob)->get("/dashboard/products/{$product->id}/edit")->assertForbidden();
        $this->actingAs($this->bob)->put("/dashboard/products/{$product->id}", ['name' => 'Hacked', 'price' => 1, 'type' => 'physical'])->assertForbidden();
        $this->actingAs($this->bob)->delete("/dashboard/products/{$product->id}")->assertForbidden();

        $this->assertSame('Original', $product->fresh()->name);
    }

    public function test_cannot_touch_another_users_links_or_orders(): void
    {
        $link = StoreLink::factory()->for($this->aliceStore)->create();
        $order = Order::create([
            'store_id' => $this->aliceStore->id, 'number' => 'KS-TEST-1', 'customer_name' => 'X', 'customer_email' => 'x@example.com',
            'currency' => 'IDR', 'subtotal' => 1000, 'total' => 1000, 'payment_provider' => 'manual',
        ]);

        $this->actingAs($this->bob)->put("/dashboard/links/{$link->id}", ['title' => 'x', 'url' => 'https://x.test', 'icon' => 'custom'])->assertForbidden();
        $this->actingAs($this->bob)->delete("/dashboard/links/{$link->id}")->assertForbidden();
        $this->actingAs($this->bob)->post("/dashboard/links/{$link->id}/move/up")->assertForbidden();
        $this->actingAs($this->bob)->get("/dashboard/orders/{$order->id}")->assertForbidden();
        $this->actingAs($this->bob)->patch("/dashboard/orders/{$order->id}", ['payment_status' => 'paid', 'fulfillment_status' => 'completed'])->assertForbidden();

        $this->assertSame('pending', $order->fresh()->payment_status->value);
    }

    public function test_owner_can_manage_products_links_and_orders(): void
    {
        $this->actingAs($this->alice)->post('/dashboard/products', [
            'name' => 'Batik Tote', 'price' => '289.000', 'type' => 'digital', 'stock_quantity' => 4, 'is_active' => 1,
        ])->assertRedirect(route('dashboard.products.index'));
        $product = $this->aliceStore->products()->sole();
        $this->assertSame('batik-tote', $product->slug);
        $this->assertSame(289000, $product->price);
        $this->assertFalse($product->requires_shipping, 'digital products default to no shipping');

        $this->actingAs($this->alice)->post('/dashboard/links', ['title' => 'IG', 'url' => 'https://instagram.com/a', 'icon' => 'instagram', 'is_visible' => 1]);
        $this->actingAs($this->alice)->post('/dashboard/links', ['title' => 'WA', 'url' => 'https://wa.me/62', 'icon' => 'whatsapp', 'is_visible' => 1]);
        $wa = $this->aliceStore->links()->where('title', 'WA')->sole();
        $this->actingAs($this->alice)->post("/dashboard/links/{$wa->id}/move/up");
        $this->assertSame(['WA', 'IG'], $this->aliceStore->links()->pluck('title')->all());

        $this->actingAs($this->alice)->post('/dashboard/links', ['title' => 'Bad', 'url' => 'javascript:alert(1)', 'icon' => 'custom'])
            ->assertSessionHasErrors('url');
    }

    public function test_unverified_local_user_cannot_publish_but_kuartal_id_user_can(): void
    {
        $local = User::factory()->unverified()->create();
        $store = Store::factory()->for($local)->create();
        $this->actingAs($local)->post('/dashboard/publish')->assertRedirect(route('verification.notice'));
        $this->assertFalse($store->fresh()->is_published);

        $sso = User::factory()->kuartalId()->create(['email_verified_at' => null]);
        $ssoStore = Store::factory()->for($sso)->create();
        $this->actingAs($sso)->post('/dashboard/publish')->assertRedirect();
        $this->assertTrue($ssoStore->fresh()->is_published);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->post('/dashboard/publish')->assertRedirect(route('login'));
    }
}
