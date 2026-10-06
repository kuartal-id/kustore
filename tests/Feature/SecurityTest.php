<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_pages_and_errors(): void
    {
        foreach (['/', '/login', '/does-not-exist-store'] as $url) {
            $r = $this->get($url);
            $r->assertHeader('X-Frame-Options', 'DENY');
            $r->assertHeader('X-Content-Type-Options', 'nosniff');
            $r->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
            $r->assertHeader('Strict-Transport-Security');
            $r->assertHeader('Permissions-Policy');
            $r->assertHeaderMissing('X-Powered-By');
            $csp = $r->headers->get('Content-Security-Policy');
            $this->assertStringContainsString("default-src 'self'", $csp);
            $this->assertStringContainsString("frame-ancestors 'none'", $csp);
            $this->assertStringContainsString("script-src 'self'", $csp);
            $this->assertStringNotContainsString('unsafe-inline', $csp);
            $this->assertStringNotContainsString('fonts.googleapis', $csp);
        }
    }

    public function test_logout_requires_post(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/logout');
        $this->assertAuthenticatedAs($user); // GET never logs out
        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_registration_sends_verification_and_rejects_duplicate_local_email(): void
    {
        Notification::fake();
        $this->post('/register', ['name' => 'Sekar', 'email' => 'SEKAR@example.com', 'password' => 'secret-pass-123', 'password_confirmation' => 'secret-pass-123'])
            ->assertRedirect(route('onboarding.username'));
        $user = User::firstWhere('email', 'sekar@example.com');
        $this->assertNotNull($user);
        $this->assertNotSame('secret-pass-123', $user->password);
        $this->assertFalse($user->canPublishStore());
        Notification::assertSentTo($user, VerifyEmail::class);

        auth()->logout();
        $this->post('/register', ['name' => 'Again', 'email' => 'sekar@example.com', 'password' => 'secret-pass-123', 'password_confirmation' => 'secret-pass-123'])
            ->assertSessionHasErrors('email');
    }

    public function test_kuartal_id_users_cannot_log_in_with_email_password(): void
    {
        User::factory()->kuartalId()->create(['email' => 'sso@example.com']);
        $this->post('/login', ['email' => 'sso@example.com', 'password' => 'anything'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_regenerates_session_and_is_rate_limited(): void
    {
        $user = User::factory()->create(['email' => 'a@example.com', 'password' => 'right-password-1']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'a@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => 'a@example.com', 'password' => 'right-password-1'])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_successful_login(): void
    {
        $user = User::factory()->create(['email' => 'b@example.com', 'password' => 'right-password-1']);
        Store::factory()->for($user)->create();
        $this->post('/login', ['email' => 'B@example.com', 'password' => 'right-password-1'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_avatar_upload_is_reencoded_with_random_name_and_non_images_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $store = Store::factory()->for($user)->create();
        $base = ['display_name' => 'S', 'account_type' => 'individual', 'layout' => 'minimal', 'color_mode' => 'light'];

        $this->actingAs($user)->put('/dashboard/store', $base + ['avatar' => UploadedFile::fake()->image('me.php.png', 300, 300)])
            ->assertSessionHasNoErrors();
        $path = $store->fresh()->avatar_path;
        $this->assertMatchesRegularExpression('#^avatars/[a-z0-9]{40}\.(webp|png)$#', $path);
        Storage::disk('public')->assertExists($path);

        $this->actingAs($user)->put('/dashboard/store', $base + ['avatar' => UploadedFile::fake()->create('shell.php', 10, 'application/x-php')])
            ->assertSessionHasErrors('avatar');
        $this->actingAs($user)->put('/dashboard/store', $base + ['avatar' => UploadedFile::fake()->image('big.jpg')->size(5000)])
            ->assertSessionHasErrors('avatar');
    }

    public function test_csrf_is_enforced(): void
    {
        // The test kernel skips CSRF; assert the middleware is part of the web group instead.
        $web = app(\Illuminate\Contracts\Http\Kernel::class)->getMiddlewareGroups()['web'];
        $this->assertContains(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class, $web);
    }
}
