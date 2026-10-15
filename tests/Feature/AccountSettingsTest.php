<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['name' => 'Sekar Ayu']);
        $this->store = Store::factory()->for($this->user)->published()->create(['username' => 'sekar']);
    }

    public function test_settings_page_renders(): void
    {
        $this->actingAs($this->user)->get('/dashboard/settings')->assertOk()
            ->assertSee('kustore.id/')->assertSee('Download my data')->assertSee('Delete my account')->assertSee('Change password');
    }

    public function test_kuartal_id_users_have_no_password_form_and_see_kuartal_id_deletion_link(): void
    {
        $sso = User::factory()->kuartalId()->create();
        Store::factory()->for($sso)->create();

        $this->actingAs($sso)->get('/dashboard/settings')->assertOk()
            ->assertDontSee('Change password')->assertSee('id.kuartal.id/account/delete');
        $this->actingAs($sso)->put('/dashboard/settings/password', ['current_password' => 'x', 'password' => 'abcdefgh12', 'password_confirmation' => 'abcdefgh12'])->assertNotFound();
    }

    public function test_name_can_be_changed(): void
    {
        $this->actingAs($this->user)->put('/dashboard/settings/profile', ['name' => 'Sekar A.'])->assertRedirect();
        $this->assertSame('Sekar A.', $this->user->fresh()->name);
    }

    public function test_username_change_moves_the_page(): void
    {
        $this->actingAs($this->user)->put('/dashboard/settings/username', ['username' => 'Sekar-Shop'])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('sekar-shop', $this->store->fresh()->username);
        $this->get('/sekar-shop')->assertOk();
        $this->get('/sekar')->assertNotFound();
    }

    public function test_username_change_rejects_taken_and_reserved_names(): void
    {
        Store::factory()->create(['username' => 'taken']);

        $this->actingAs($this->user)->put('/dashboard/settings/username', ['username' => 'taken'])->assertSessionHasErrors('username');
        $this->actingAs($this->user)->put('/dashboard/settings/username', ['username' => 'support'])->assertSessionHasErrors('username');
        $this->actingAs($this->user)->put('/dashboard/settings/username', ['username' => 'forgot-password'])->assertSessionHasErrors('username');
        $this->assertSame('sekar', $this->store->fresh()->username);
    }

    public function test_password_change_requires_current_password(): void
    {
        $this->actingAs($this->user)->put('/dashboard/settings/password', [
            'current_password' => 'wrong', 'password' => 'newpassword12', 'password_confirmation' => 'newpassword12',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($this->user)->put('/dashboard/settings/password', [
            'current_password' => 'password', 'password' => 'newpassword12', 'password_confirmation' => 'newpassword12',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('newpassword12', $this->user->fresh()->password));
    }

    public function test_export_contains_the_accounts_data_only(): void
    {
        StoreLink::factory()->for($this->store)->create(['title' => 'Instagram Sekar']);
        Product::factory()->for($this->store)->create(['name' => 'Batik Tote']);
        $customer = Customer::create(['store_id' => $this->store->id, 'name' => 'Dewi', 'email' => 'dewi@example.com']);
        Order::create(['store_id' => $this->store->id, 'customer_id' => $customer->id, 'number' => 'KS-1', 'customer_name' => 'Dewi',
            'customer_email' => 'dewi@example.com', 'currency' => 'IDR', 'subtotal' => 1, 'total' => 1, 'payment_provider' => 'manual']);
        $other = Store::factory()->create();
        Product::factory()->for($other)->create(['name' => 'Not Mine']);

        $res = $this->actingAs($this->user)->get('/dashboard/settings/export')->assertOk();
        $this->assertStringContainsString('attachment; filename="kustore-export-sekar-', $res->headers->get('Content-Disposition'));
        $json = $res->json();
        $this->assertSame('sekar', $json['store']['username']);
        $this->assertSame('Instagram Sekar', $json['links'][0]['title']);
        $this->assertSame('Batik Tote', $json['products'][0]['name']);
        $this->assertSame('KS-1', $json['orders'][0]['number']);
        $this->assertStringNotContainsString('Not Mine', $res->getContent());
        $this->assertArrayNotHasKey('password', $json['account']);
    }

    public function test_account_deletion_requires_typed_username_and_removes_everything(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/a.webp', 'x');
        Storage::disk('public')->put('avatars/me.webp', 'x');
        $this->store->forceFill(['avatar_path' => 'avatars/me.webp'])->save();
        $product = Product::factory()->for($this->store)->create();
        $product->images()->create(['path' => 'products/a.webp', 'is_main' => true]);

        $this->actingAs($this->user)->delete('/dashboard/settings/account', ['confirm' => 'wrong'])->assertSessionHasErrors('confirm');
        $this->assertNotNull($this->user->fresh());

        $this->actingAs($this->user)->delete('/dashboard/settings/account', ['confirm' => 'sekar'])->assertRedirect('/');

        $this->assertNull(User::find($this->user->id));
        $this->assertNull(Store::find($this->store->id));
        $this->assertNull(Product::find($product->id));
        Storage::disk('public')->assertMissing('products/a.webp');
        Storage::disk('public')->assertMissing('avatars/me.webp');
        $this->assertGuest();
        $this->get('/sekar')->assertNotFound();
    }
}
