<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_store_is_public_with_seo_tags(): void
    {
        $store = Store::factory()->published()->create(['username' => 'sekar', 'display_name' => 'Sekar Studio']);
        StoreLink::factory()->for($store)->create(['title' => 'My Instagram']);
        StoreLink::factory()->for($store)->create(['title' => 'Hidden link', 'is_visible' => false]);
        Product::factory()->for($store)->create(['name' => 'Tote bag', 'slug' => 'tote-bag']);

        $this->get('/sekar')->assertOk()
            ->assertSee('Sekar Studio')->assertSee('My Instagram')->assertDontSee('Hidden link')->assertSee('Tote bag')
            ->assertSee('<link rel="canonical" href="http://localhost/sekar">', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('name="twitter:card"', false);

        $this->assertDatabaseHas('analytics_events', ['store_id' => $store->id, 'type' => AnalyticsEvent::STORE_VIEW]);
    }

    public function test_unpublished_store_is_404(): void
    {
        Store::factory()->create(['username' => 'draftstore']);
        $this->get('/draftstore')->assertNotFound();
    }

    public function test_unpublished_store_is_404_even_for_its_owner(): void
    {
        $store = Store::factory()->create(['username' => 'mine']);
        $this->actingAs($store->user)->get('/mine')->assertNotFound();
    }

    public function test_suspended_store_and_its_products_are_404(): void
    {
        $store = Store::factory()->published()->suspended()->create(['username' => 'bad']);
        Product::factory()->for($store)->create(['slug' => 'thing']);

        $this->get('/bad')->assertNotFound();
        $this->get('/bad/product/thing')->assertNotFound();
        $this->get('/bad/product/thing/checkout')->assertNotFound();
    }

    public function test_product_page_has_json_ld_and_inactive_products_are_404(): void
    {
        $store = Store::factory()->published()->create(['username' => 'sekar']);
        Product::factory()->for($store)->create(['name' => 'Tote', 'slug' => 'tote', 'price' => 289000]);
        Product::factory()->for($store)->create(['slug' => 'hidden', 'is_active' => false]);

        $this->get('/sekar/product/tote')->assertOk()
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"priceCurrency":"IDR"', false)
            ->assertSee('Rp 289.000');
        $this->get('/sekar/product/hidden')->assertNotFound();
    }

    public function test_link_redirect_counts_clicks_without_storing_ip(): void
    {
        $store = Store::factory()->published()->create();
        $link = StoreLink::factory()->for($store)->create(['url' => 'https://instagram.com/sekar']);

        $this->get('/go/'.$link->id)->assertRedirect('https://instagram.com/sekar');

        $this->assertSame(1, $link->fresh()->clicks_count);
        $event = AnalyticsEvent::firstWhere('type', AnalyticsEvent::LINK_CLICK);
        $this->assertNotNull($event);
        $this->assertSame(64, strlen($event->visitor_hash));
        $this->assertStringNotContainsString('127.0.0.1', json_encode($event->getAttributes()));
    }

    public function test_hidden_link_and_links_of_unpublished_stores_do_not_redirect(): void
    {
        $hidden = StoreLink::factory()->for(Store::factory()->published())->create(['is_visible' => false]);
        $draft = StoreLink::factory()->for(Store::factory())->create();

        $this->get('/go/'.$hidden->id)->assertNotFound();
        $this->get('/go/'.$draft->id)->assertNotFound();
    }

    public function test_landing_and_legal_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Continue with Kuartal ID')->assertSee('Kustore')->assertDontSee('KuStore');
        $this->get('/terms')->assertOk()->assertSee('Draft');
        $this->get('/privacy')->assertOk()->assertSee('Draft');
        $this->get('/login')->assertOk()->assertSee('Continue with Kuartal ID');
    }

    public function test_admin_can_suspend_store_and_non_admin_cannot_reach_admin(): void
    {
        $admin = User::factory()->admin()->create();
        Store::factory()->for($admin)->create();
        $target = Store::factory()->published()->create(['username' => 'target']);

        $user = User::factory()->create();
        Store::factory()->for($user)->create();
        $this->actingAs($user)->get('/admin')->assertNotFound();

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('target');
        $this->actingAs($admin)->post('/admin/stores/'.$target->id.'/suspend')->assertRedirect();
        $this->assertTrue($target->fresh()->is_suspended);
        auth()->logout();
        $this->get('/target')->assertNotFound();
    }
}
