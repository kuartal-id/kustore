<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KuartalIdOidcTest extends TestCase
{
    use RefreshDatabase;

    private const ISSUER = 'https://id.kuartal.test';
    private const CLIENT = 'kustore-test-client';

    private string $privateKey = '';
    private array $jwk;

    protected function setUp(): void
    {
        parent::setUp();

        $res = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($res, $this->privateKey);
        $d = openssl_pkey_get_details($res)['rsa'];
        $b64 = fn ($v) => rtrim(strtr(base64_encode($v), '+/', '-_'), '=');
        $this->jwk = ['kty' => 'RSA', 'use' => 'sig', 'alg' => 'RS256', 'kid' => 'test-1', 'n' => $b64($d['n']), 'e' => $b64($d['e'])];
    }

    private array $claimOverrides = [];
    private ?string $signKey = null;

    /** Configure what the fake IdP will return for the next token request. */
    private function fakeIdp(array $claims = [], array $tokenExtra = [], ?string $signWith = null): void
    {
        $this->claimOverrides = $claims;
        $this->signKey = $signWith;

        if ($this->faked ?? false) {
            return;
        }
        $this->faked = true;

        Http::fake(function ($request) {
            $url = $request->url();
            if (str_ends_with($url, '/.well-known/openid-configuration')) {
                return Http::response([
                    'issuer' => self::ISSUER,
                    'authorization_endpoint' => self::ISSUER.'/oauth/authorize',
                    'token_endpoint' => self::ISSUER.'/oauth/token',
                    'userinfo_endpoint' => self::ISSUER.'/oauth/userinfo',
                    'jwks_uri' => self::ISSUER.'/oauth/jwks',
                ]);
            }
            if (str_ends_with($url, '/oauth/jwks')) {
                return Http::response(['keys' => [$this->jwk]]);
            }
            $claims = $this->claims();
            if (str_ends_with($url, '/oauth/token')) {
                return Http::response(['access_token' => 'at', 'token_type' => 'Bearer',
                    'id_token' => JWT::encode($claims, $this->signKey ?? $this->privateKey, 'RS256', 'test-1')]);
            }
            if (str_ends_with($url, '/oauth/userinfo')) {
                return Http::response(['sub' => $claims['sub'], 'name' => $claims['name'], 'email' => $claims['email'], 'email_verified' => true]);
            }

            return Http::response('not found', 404);
        });
    }

    private bool $faked = false;

    private function claims(): array
    {
        return array_merge([
            'iss' => self::ISSUER,
            'aud' => self::CLIENT,
            'sub' => 'kid-123',
            'iat' => time(),
            'exp' => time() + 300,
            'nonce' => session('kuartal_id.oidc.nonce') ?? $this->lastNonce,
            'name' => 'Sekar Ayu',
            'email' => 'sekar@example.com',
            'email_verified' => true,
        ], $this->claimOverrides);
    }

    private ?string $lastNonce = null;

    /** Start a login so the session holds state/nonce/verifier. */
    private function startLogin(): array
    {
        $this->fakeIdp();
        $response = $this->get('/auth/kuartal/redirect');
        $response->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $q);
        $this->lastNonce = $q['nonce'];

        return $q;
    }

    public function test_redirect_uses_pkce_s256_state_nonce_and_only_basic_scopes(): void
    {
        $q = $this->startLogin();

        $this->assertSame('code', $q['response_type']);
        $this->assertSame(self::CLIENT, $q['client_id']);
        $this->assertSame('openid profile email', $q['scope']);
        $this->assertStringNotContainsString('entitlements', $q['scope']);
        $this->assertSame('S256', $q['code_challenge_method']);
        $this->assertNotEmpty($q['state']);
        $this->assertNotEmpty($q['nonce']);
        $pending = session('kuartal_id.oidc');
        $this->assertSame($pending['state'], $q['state']);
        $expected = rtrim(strtr(base64_encode(hash('sha256', $pending['verifier'], true)), '+/', '-_'), '=');
        $this->assertSame($expected, $q['code_challenge']);
        $this->assertArrayNotHasKey('client_secret', $q);
    }

    public function test_callback_with_wrong_state_is_rejected(): void
    {
        $this->startLogin();

        $this->get('/auth/kuartal/callback?code=abc&state=forged')
            ->assertRedirect(route('login'))->assertSessionHasErrors('kuartal');

        $this->assertGuest();
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '/oauth/token'));
    }

    public function test_callback_without_pending_session_is_rejected(): void
    {
        $this->get('/auth/kuartal/callback?code=abc&state=anything')
            ->assertRedirect(route('login'))->assertSessionHasErrors('kuartal');
        $this->assertGuest();
    }

    public function test_state_cannot_be_replayed(): void
    {
        $q = $this->startLogin();
        $this->fakeIdp();
        $this->get('/auth/kuartal/callback?code=abc&state='.$q['state'])->assertRedirect(route('onboarding.username'));
        auth()->logout();

        $this->get('/auth/kuartal/callback?code=abc&state='.$q['state'])->assertSessionHasErrors('kuartal');
    }

    public function test_callback_with_wrong_nonce_is_rejected(): void
    {
        $q = $this->startLogin();
        $this->fakeIdp(['nonce' => 'not-the-nonce']);

        $this->get('/auth/kuartal/callback?code=abc&state='.$q['state'])
            ->assertRedirect(route('login'))->assertSessionHasErrors('kuartal');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_callback_with_bad_signature_wrong_audience_issuer_or_expired_is_rejected(): void
    {
        $other = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($other, $otherKey);

        $cases = [
            'signature' => [[], $otherKey],
            'audience' => [['aud' => 'someone-else'], null],
            'issuer' => [['iss' => 'https://evil.test'], null],
            'expired' => [['exp' => time() - 3600, 'iat' => time() - 7200], null],
        ];

        foreach ($cases as $name => [$claims, $key]) {
            $q = $this->startLogin();
            $this->fakeIdp($claims, [], $key);
            $this->get('/auth/kuartal/callback?code=abc&state='.$q['state'])
                ->assertSessionHasErrors('kuartal');
            $this->assertGuest();
        }
        $this->assertDatabaseCount('users', 0);
    }

    public function test_valid_callback_creates_user_by_sub_and_sends_to_onboarding(): void
    {
        $q = $this->startLogin();
        $this->fakeIdp();

        $this->get('/auth/kuartal/callback?code=abc&state='.$q['state'])->assertRedirect(route('onboarding.username'));

        $user = User::firstWhere('kuartal_id_sub', 'kid-123');
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);

        Http::assertSent(fn ($r) => str_contains($r->url(), '/oauth/token')
            && $r['grant_type'] === 'authorization_code'
            && ! empty($r['code_verifier'])
            && $r['client_secret'] === 'test-secret');
    }

    public function test_returning_user_with_store_goes_to_dashboard(): void
    {
        $user = User::factory()->create(['kuartal_id_sub' => 'kid-123', 'password' => null]);
        Store::factory()->for($user)->create();

        $q = $this->startLogin();
        $this->fakeIdp();
        $this->get('/auth/kuartal/callback?code=abc&state='.$q['state'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_accounts_are_never_linked_by_email(): void
    {
        $local = User::factory()->create(['email' => 'sekar@example.com']);

        $q = $this->startLogin();
        $this->fakeIdp();
        $this->get('/auth/kuartal/callback?code=abc&state='.$q['state']);

        $sso = User::firstWhere('kuartal_id_sub', 'kid-123');
        $this->assertNotNull($sso);
        $this->assertNotSame($local->id, $sso->id);
        $this->assertNull($local->fresh()->kuartal_id_sub);
        $this->assertAuthenticatedAs($sso);
    }

    public function test_provider_error_is_handled(): void
    {
        $this->startLogin();
        $this->get('/auth/kuartal/callback?error=access_denied&state=x')
            ->assertRedirect(route('login'))->assertSessionHasErrors('kuartal');
    }
}
