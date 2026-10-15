<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_user_can_reset_password_with_emailed_link(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'sekar@example.com']);

        $this->get('/forgot-password')->assertOk();
        $this->post('/forgot-password', ['email' => 'Sekar@Example.com'])->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($n) use (&$token) {
            $token = $n->token;

            return true;
        });

        $this->get('/reset-password/'.$token.'?email=sekar@example.com')->assertOk()->assertSee('sekar@example.com');
        $this->post('/reset-password', [
            'token' => $token, 'email' => 'sekar@example.com', 'password' => 'brandnew123', 'password_confirmation' => 'brandnew123',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('brandnew123', $user->fresh()->password));

        // The token is single use.
        $this->post('/reset-password', [
            'token' => $token, 'email' => 'sekar@example.com', 'password' => 'another123', 'password_confirmation' => 'another123',
        ])->assertSessionHasErrors('email');
    }

    public function test_kuartal_id_accounts_never_get_reset_links_even_with_same_email(): void
    {
        Notification::fake();
        $sso = User::factory()->kuartalId()->create(['email' => 'shared@example.com']);

        $this->post('/forgot-password', ['email' => 'shared@example.com'])->assertSessionHas('status');

        Notification::assertNothingSent();
        $this->assertNull($sso->fresh()->password);
    }

    public function test_unknown_email_gets_the_same_answer(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'nobody@example.com'])
            ->assertSessionHas('status', fn ($s) => str_contains($s, 'If that email belongs'));
        Notification::assertNothingSent();
    }

    public function test_bad_token_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'sekar@example.com']);

        $this->post('/reset-password', [
            'token' => 'nope', 'email' => 'sekar@example.com', 'password' => 'brandnew123', 'password_confirmation' => 'brandnew123',
        ])->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_login_page_links_to_reset(): void
    {
        $this->get('/login')->assertOk()->assertSee(route('password.request'));
    }
}
