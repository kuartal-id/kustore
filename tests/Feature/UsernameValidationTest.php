<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use App\Rules\Username;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UsernameValidationTest extends TestCase
{
    use RefreshDatabase;

    public static function invalidUsernames(): array
    {
        return [
            'too short' => ['ab'],
            'too long' => [str_repeat('a', 31)],
            'uppercase' => ['Sekar'],
            'space' => ['sekar studio'],
            'dot' => ['sekar.studio'],
            'leading dash' => ['-sekar'],
            'trailing underscore' => ['sekar_'],
            'unicode' => ['sékar'],
            'slash' => ['sekar/x'],
        ];
    }

    #[DataProvider('invalidUsernames')]
    public function test_invalid_formats_are_rejected(string $username): void
    {
        $this->assertTrue(Validator::make(['u' => $username], ['u' => [new Username]])->fails());
    }

    public static function reservedUsernames(): array
    {
        return array_map(fn ($n) => [$n], [
            'admin', 'login', 'logout', 'register', 'dashboard', 'api', 'support', 'help', 'kuartal', 'kustore',
            'official', 'shop', 'store', 'auth', 'settings', 'onboarding', 'checkout', 'product', 'assets',
            'storage', 'public', 'terms', 'privacy', 'about', 'pricing', 'www', 'mail', 'static', 'email', 'verify',
        ]);
    }

    #[DataProvider('reservedUsernames')]
    public function test_reserved_names_are_rejected(string $username): void
    {
        $v = Validator::make(['u' => $username], ['u' => [new Username]]);
        $this->assertTrue($v->fails());
        $this->assertStringContainsString('reserved', $v->errors()->first('u'));
    }

    public function test_valid_usernames_pass(): void
    {
        foreach (['abc', 'sekar-studio', 'kopi_nusantara', 'toko123', str_repeat('a', 30)] as $u) {
            $this->assertTrue(Validator::make(['u' => $u], ['u' => [new Username]])->passes(), $u);
        }
    }

    public function test_onboarding_rejects_reserved_and_taken_names_and_creates_store(): void
    {
        Store::factory()->create(['username' => 'taken']);
        $user = User::factory()->kuartalId()->create();

        $this->actingAs($user)->post('/onboarding', ['username' => 'admin'])->assertSessionHasErrors('username');
        $this->actingAs($user)->post('/onboarding', ['username' => 'taken'])->assertSessionHasErrors('username');

        // Input is normalised to lowercase
        $this->actingAs($user)->post('/onboarding', ['username' => 'Sekar-Studio'])->assertRedirect(route('onboarding.type'));
        $this->actingAs($user)->post('/onboarding/account-type', ['account_type' => 'business', 'display_name' => 'Sekar Studio'])
            ->assertRedirect(route('dashboard'));

        $store = $user->fresh()->store;
        $this->assertSame('sekar-studio', $store->username);
        $this->assertSame('business', $store->account_type);
        $this->assertFalse($store->is_published);
        $this->assertSame('kuartal_id', $store->verification_level->value);
    }

    public function test_user_without_store_is_sent_to_onboarding(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertRedirect(route('onboarding.username'));
    }
}
