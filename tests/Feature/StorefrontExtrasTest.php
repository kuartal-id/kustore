<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Product photos, order emails, QRIS/WhatsApp on the order page, home page, help, sitemap and app link files. */
class StorefrontExtrasTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_have_several_photos_and_choose_the_main_one(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $store = Store::factory()->for($user)->published()->create(['username' => 'galeri']);

        $this->actingAs($user)->post('/dashboard/products', [
            'name' => 'Kain Tenun', 'price' => '350.000', 'type' => 'physical', 'stock_quantity' => 3, 'is_active' => '1',
            'image' => UploadedFile::fake()->image('main.jpg', 800, 800),
            'images' => [UploadedFile::fake()->image('b.jpg', 800, 800), UploadedFile::fake()->image('c.jpg', 800, 800)],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $product = Product::sole();
        $this->assertCount(3, $product->images);
        $this->assertSame(1, $product->images->where('is_main', true)->count());

        $extra = $product->images->where('is_main', false)->first();
        $this->actingAs($user)->put("/dashboard/products/{$product->id}", [
            'name' => 'Kain Tenun', 'price' => '350000', 'type' => 'physical', 'stock_quantity' => 3, 'is_active' => '1',
            'main_image_id' => $extra->id,
        ])->assertSessionHasNoErrors();
        $this->assertTrue($extra->fresh()->is_main);
        $this->assertSame(1, $product->images()->where('is_main', true)->count());

        $this->get('/galeri/product/'.$product->slug)->assertOk()->assertSee('data-gallery-thumb', false)->assertSee('Show photo 3');

        $toRemove = $product->images()->where('is_main', false)->pluck('id')->all();
        $this->actingAs($user)->put("/dashboard/products/{$product->id}", [
            'name' => 'Kain Tenun', 'price' => '350000', 'type' => 'physical', 'stock_quantity' => 3, 'is_active' => '1',
            'remove_images' => $toRemove,
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, $product->images()->count());
    }

    public function test_photo_limit_is_enforced(): void
    {
        $user = User::factory()->create();
        Store::factory()->for($user)->create();
        $files = array_map(fn ($i) => UploadedFile::fake()->image("p{$i}.jpg", 50, 50), range(1, 6));

        $this->actingAs($user)->post('/dashboard/products', [
            'name' => 'Too many', 'price' => '1000', 'type' => 'physical', 'images' => $files,
        ])->assertSessionHasErrors('images');
    }

    public function test_cannot_remove_or_promote_another_products_photos(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->for($user)->create();
        $mine = Product::factory()->for($store)->create();
        $theirs = Product::factory()->for(Store::factory()->create())->create();
        $foreign = $theirs->images()->create(['path' => 'products/x.webp', 'is_main' => true]);

        $this->actingAs($user)->put("/dashboard/products/{$mine->id}", [
            'name' => $mine->name, 'price' => '1000', 'type' => 'physical', 'remove_images' => [$foreign->id], 'main_image_id' => $foreign->id,
        ])->assertSessionHasNoErrors();

        $this->assertNotNull($foreign->fresh());
        $this->assertSame($theirs->id, $foreign->fresh()->product_id);
    }

    public function test_checkout_emails_seller_and_buyer_and_order_page_shows_qris_and_whatsapp(): void
    {
        Notification::fake();
        Storage::fake('public');
        Storage::disk('public')->put('qris/code.webp', 'x');
        $seller = User::factory()->create(['email' => 'seller@example.com']);
        $store = Store::factory()->for($seller)->published()->create(['username' => 'kopi']);
        $store->forceFill(['payment_qris_path' => 'qris/code.webp', 'whatsapp_number' => '6281234567890'])->save();
        Product::factory()->for($store)->create(['slug' => 'arabika', 'price' => 85000, 'stock_quantity' => 9]);

        $res = $this->post('/kopi/product/arabika/checkout', [
            'name' => 'Dewi', 'email' => 'dewi@example.com', 'phone' => '0812 0000 1111', 'shipping_address' => 'Mataram', 'quantity' => 1,
        ]);
        $this->followRedirects($res)->assertOk()
            ->assertSee('Scan to pay with QRIS')
            ->assertSee('wa.me/6281234567890?text=', false)
            ->assertSee('dewi@example.com');

        Notification::assertSentTo($seller, NewOrderNotification::class);
        Notification::assertSentTo(new AnonymousNotifiable, OrderReceivedNotification::class, fn ($n, $c, $notifiable) => $notifiable->routes['mail'] === 'dewi@example.com');
    }

    public function test_seller_can_turn_off_order_emails(): void
    {
        Notification::fake();
        $seller = User::factory()->create();
        $store = Store::factory()->for($seller)->published()->create(['username' => 'diam']);
        $store->forceFill(['notify_new_orders' => false])->save();
        Product::factory()->for($store)->create(['slug' => 'x', 'price' => 1000, 'stock_quantity' => 9, 'requires_shipping' => false]);

        $this->post('/diam/product/x/checkout', ['name' => 'D', 'email' => 'd@example.com', 'phone' => '081200001111', 'quantity' => 1])->assertRedirect();

        Notification::assertNotSentTo($seller, NewOrderNotification::class);
    }

    public function test_home_shows_diemas_and_links_to_his_store_once_live(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Diemas Hawkins')->assertSee('@diemas')
            ->assertSee('images/home/diemas-hawkins.webp')
            ->assertDontSee('Rani Atelier')
            ->assertDontSee('href="'.url('/diemas').'"', false);

        Store::factory()->published()->create(['username' => 'diemas']);
        $this->get('/')->assertOk()->assertSee('href="'.url('/diemas').'"', false);
    }

    public function test_support_page_and_sitemap(): void
    {
        $this->get('/support')->assertOk()->assertSee('How can we help?')->assertSee('id.kuartal.id/account/delete')->assertSee(config('kustore.support_email'));

        $live = Store::factory()->published()->create(['username' => 'live-store']);
        Product::factory()->for($live)->create(['slug' => 'visible-item']);
        Product::factory()->for($live)->create(['slug' => 'hidden-item', 'is_active' => false]);
        Store::factory()->create(['username' => 'draft-store']);
        Store::factory()->published()->suspended()->create(['username' => 'banned-store']);

        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();
        $this->assertStringContainsString(url('/live-store'), $xml);
        $this->assertStringContainsString('/live-store/product/visible-item', $xml);
        $this->assertStringNotContainsString('hidden-item', $xml);
        $this->assertStringNotContainsString('draft-store', $xml);
        $this->assertStringNotContainsString('banned-store', $xml);
    }

    public function test_app_link_files_are_served_as_json(): void
    {
        $aasa = $this->get('/.well-known/apple-app-site-association')->assertOk();
        $this->assertStringStartsWith('application/json', $aasa->headers->get('Content-Type'));
        $json = json_decode(file_get_contents($aasa->baseResponse->getFile()->getPathname()), true);
        $this->assertStringEndsWith('.id.kustore.app', $json['applinks']['details'][0]['appIDs'][0]);

        $links = json_decode(file_get_contents(public_path('.well-known/assetlinks.json')), true);
        $this->assertSame(config('kustore.apps.android_package'), $links[0]['target']['package_name']);
    }

    public function test_root_htaccess_blocks_the_mobile_source_folder(): void
    {
        $this->assertMatchesRegularExpression('/\|mobile\|/', file_get_contents(base_path('.htaccess')));
    }
}
