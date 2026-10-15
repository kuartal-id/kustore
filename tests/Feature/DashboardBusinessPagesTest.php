<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Customers, Analytics and Payments: the pages that used to say "Coming soon". */
class DashboardBusinessPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create();
        $this->store = Store::factory()->for($this->owner)->published()->create(['username' => 'warung']);
    }

    private function order(Customer $customer, int $total, string $status = 'pending', array $extra = []): Order
    {
        return Order::create($extra + [
            'store_id' => $customer->store_id, 'customer_id' => $customer->id, 'number' => Order::generateNumber(),
            'customer_name' => $customer->name, 'customer_email' => $customer->email, 'customer_phone' => $customer->phone,
            'currency' => 'IDR', 'subtotal' => $total, 'total' => $total, 'payment_provider' => 'manual',
            'payment_status' => $status, 'paid_at' => $status === 'paid' ? now() : null,
        ]);
    }

    public function test_navigation_no_longer_shows_coming_soon(): void
    {
        $this->actingAs($this->owner)->get('/dashboard')->assertOk()
            ->assertDontSee('Coming soon')
            ->assertSee(route('dashboard.customers.index'))
            ->assertSee(route('dashboard.analytics'))
            ->assertSee(route('dashboard.payments.edit'))
            ->assertSee(route('dashboard.settings'));
    }

    public function test_customers_list_search_detail_and_csv(): void
    {
        $dewi = Customer::create(['store_id' => $this->store->id, 'name' => 'Dewi Lestari', 'email' => 'dewi@example.com', 'phone' => '0812 1111 2222']);
        $andi = Customer::create(['store_id' => $this->store->id, 'name' => 'Andi Wijaya', 'email' => 'andi@example.com']);
        $this->order($dewi, 150000, 'paid');
        $this->order($dewi, 50000);
        $this->order($andi, 20000, 'paid');

        $this->actingAs($this->owner)->get('/dashboard/customers')->assertOk()
            ->assertSee('Dewi Lestari')->assertSee('Andi Wijaya')->assertSee('Rp 150.000');
        $this->actingAs($this->owner)->get('/dashboard/customers?q=andi')->assertOk()
            ->assertSee('Andi Wijaya')->assertDontSee('Dewi Lestari');
        $this->actingAs($this->owner)->get("/dashboard/customers/{$dewi->id}")->assertOk()
            ->assertSee('Rp 150.000')->assertSee('wa.me/6281211112222', false);

        $csv = $this->actingAs($this->owner)->get('/dashboard/customers/export')->assertOk()->streamedContent();
        $this->assertStringContainsString('"Dewi Lestari",dewi@example.com', $csv);
    }

    public function test_csv_neutralises_spreadsheet_formulas(): void
    {
        Customer::create(['store_id' => $this->store->id, 'name' => '=HYPERLINK("http://evil")', 'email' => 'x@example.com']);

        $csv = $this->actingAs($this->owner)->get('/dashboard/customers/export')->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_customers_of_another_store_are_hidden(): void
    {
        $other = Store::factory()->create();
        $stranger = Customer::create(['store_id' => $other->id, 'name' => 'Secret Buyer', 'email' => 's@example.com']);

        $this->actingAs($this->owner)->get("/dashboard/customers/{$stranger->id}")->assertNotFound();
        $this->actingAs($this->owner)->get('/dashboard/customers')->assertDontSee('Secret Buyer');
    }

    public function test_analytics_counts_views_clicks_products_and_referrers(): void
    {
        $link = StoreLink::factory()->for($this->store)->create(['title' => 'My Instagram']);
        $product = Product::factory()->for($this->store)->create(['name' => 'Kopi Lombok']);
        foreach ([['a', 'instagram.com'], ['a', 'instagram.com'], ['b', null]] as [$v, $ref]) {
            AnalyticsEvent::create(['store_id' => $this->store->id, 'type' => AnalyticsEvent::STORE_VIEW, 'visitor_hash' => str_repeat($v, 64), 'referrer_host' => $ref]);
        }
        AnalyticsEvent::create(['store_id' => $this->store->id, 'type' => AnalyticsEvent::LINK_CLICK, 'store_link_id' => $link->id]);
        AnalyticsEvent::create(['store_id' => $this->store->id, 'type' => AnalyticsEvent::PRODUCT_VIEW, 'product_id' => $product->id]);
        // Old event outside the 7-day range
        $old = AnalyticsEvent::create(['store_id' => $this->store->id, 'type' => AnalyticsEvent::STORE_VIEW]);
        $old->forceFill(['created_at' => now()->subDays(20)])->save();

        $res = $this->actingAs($this->owner)->get('/dashboard/analytics?days=7')->assertOk();
        $res->assertViewHas('totals', fn ($t) => $t['views'] === 3 && $t['visitors'] === 2 && $t['clicks'] === 1 && $t['product_views'] === 1);
        $res->assertSee('My Instagram')->assertSee('Kopi Lombok')->assertSee('instagram.com')->assertSee('Direct or app');

        $this->actingAs($this->owner)->get('/dashboard/analytics?days=30')->assertViewHas('totals', fn ($t) => $t['views'] === 4);
        $this->actingAs($this->owner)->get('/dashboard/analytics?days=999')->assertViewHas('days', 30);
    }

    public function test_analytics_empty_state(): void
    {
        $this->actingAs($this->owner)->get('/dashboard/analytics')->assertOk()->assertSee('No visits yet');
    }

    public function test_payment_settings_save_instructions_qris_whatsapp_and_notifications(): void
    {
        Storage::fake('public');

        $this->actingAs($this->owner)->put('/dashboard/payments', [
            'payment_instructions' => 'Transfer ke Mandiri 123 a.n. Warung',
            'whatsapp_number' => '0812-3456-7890',
            'notify_new_orders' => '0',
            'qris' => UploadedFile::fake()->image('qris.png', 600, 600),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $store = $this->store->fresh();
        $this->assertSame('Transfer ke Mandiri 123 a.n. Warung', $store->payment_instructions);
        $this->assertSame('6281234567890', $store->whatsapp_number);
        $this->assertFalse($store->notify_new_orders);
        $this->assertNotNull($store->payment_qris_path);
        Storage::disk('public')->assertExists($store->payment_qris_path);

        $this->actingAs($this->owner)->get('/dashboard/payments')->assertOk()->assertSee('Replace QRIS')->assertSee('+6281234567890');

        $this->actingAs($this->owner)->put('/dashboard/payments', ['remove_qris' => '1', 'notify_new_orders' => '1'])->assertRedirect();
        Storage::disk('public')->assertMissing($store->payment_qris_path);
        $this->assertNull($this->store->fresh()->payment_qris_path);
    }

    public function test_payment_settings_reject_bad_whatsapp_and_non_images(): void
    {
        $this->actingAs($this->owner)->put('/dashboard/payments', ['whatsapp_number' => 'call me'])->assertSessionHasErrors('whatsapp_number');
        $this->actingAs($this->owner)->put('/dashboard/payments', ['qris' => UploadedFile::fake()->create('qris.php', 5, 'application/x-php')])->assertSessionHasErrors('qris');
    }

    public function test_business_pages_require_login(): void
    {
        foreach (['/dashboard/customers', '/dashboard/analytics', '/dashboard/payments', '/dashboard/settings'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }
}
