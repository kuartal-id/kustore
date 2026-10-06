<?php

namespace App\Auth\KuartalId;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;
use UnexpectedValueException;

/**
 * Minimal OpenID Connect client for Kuartal ID (Authorization Code + PKCE S256).
 * Discovery and JWKS are cached; ID tokens are verified locally (RS256 signature,
 * iss, aud/azp, exp, iat, nonce).
 */
class KuartalIdClient
{
    public function config(string $key, mixed $default = null): mixed
    {
        return config("services.kuartal_id.$key", $default);
    }

    public function isConfigured(): bool
    {
        return filled($this->config('client_id')) && filled($this->config('issuer')) && filled($this->config('redirect'));
    }

    public function issuer(): string
    {
        return rtrim((string) $this->config('issuer'), '/');
    }

    public function discovery(): array
    {
        $ttl = (int) $this->config('cache_ttl', 3600);

        return Cache::remember('kuartal_id.discovery.'.md5($this->issuer()), $ttl, function () {
            try {
                $doc = Http::timeout(10)->acceptJson()
                    ->get($this->issuer().'/.well-known/openid-configuration')->throw()->json();
            } catch (Throwable $e) {
                throw new KuartalIdException('Could not load Kuartal ID discovery document.', 0, $e);
            }

            if (! is_array($doc) || rtrim((string) ($doc['issuer'] ?? ''), '/') !== $this->issuer()) {
                throw new KuartalIdException('Kuartal ID discovery issuer mismatch.');
            }
            foreach (['authorization_endpoint', 'token_endpoint', 'jwks_uri'] as $key) {
                if (empty($doc[$key])) {
                    throw new KuartalIdException("Kuartal ID discovery is missing [$key].");
                }
            }

            return $doc;
        });
    }

    public function jwks(bool $refresh = false): array
    {
        $key = 'kuartal_id.jwks.'.md5($this->issuer());
        if ($refresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, (int) $this->config('cache_ttl', 3600), function () {
            try {
                $jwks = Http::timeout(10)->acceptJson()->get($this->discovery()['jwks_uri'])->throw()->json();
            } catch (Throwable $e) {
                throw new KuartalIdException('Could not load Kuartal ID signing keys.', 0, $e);
            }
            if (empty($jwks['keys']) || ! is_array($jwks['keys'])) {
                throw new KuartalIdException('Kuartal ID JWKS has no keys.');
            }

            return $jwks;
        });
    }

    public static function newCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(64)), '+/', '-_'), '=');
    }

    public static function codeChallenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    public static function randomToken(): string
    {
        return Str::random(48);
    }

    public function authorizationUrl(string $state, string $nonce, string $codeVerifier): string
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->config('client_id'),
            'redirect_uri' => $this->config('redirect'),
            'scope' => implode(' ', $this->config('scopes', ['openid', 'profile', 'email'])),
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => self::codeChallenge($codeVerifier),
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);

        return $this->discovery()['authorization_endpoint'].'?'.$query;
    }

    /** @return array{access_token?:string,id_token?:string,token_type?:string,expires_in?:int} */
    public function exchangeCode(string $code, string $codeVerifier): array
    {
        $params = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->config('redirect'),
            'client_id' => $this->config('client_id'),
            'code_verifier' => $codeVerifier,
        ];
        if (filled($this->config('client_secret'))) {
            $params['client_secret'] = $this->config('client_secret');
        }

        try {
            $response = Http::timeout(15)->asForm()->acceptJson()
                ->post($this->discovery()['token_endpoint'], $params);
        } catch (Throwable $e) {
            throw new KuartalIdException('Kuartal ID token endpoint unreachable.', 0, $e);
        }

        if (! $response->successful()) {
            throw new KuartalIdException('Kuartal ID token exchange failed (HTTP '.$response->status().').');
        }

        $tokens = $response->json();
        if (! is_array($tokens) || empty($tokens['id_token'])) {
            throw new KuartalIdException('Kuartal ID did not return an ID token.');
        }

        return $tokens;
    }

    /** Verify an ID token and return its claims. */
    public function verifyIdToken(string $idToken, string $expectedNonce): array
    {
        $claims = $this->decode($idToken, false);

        if (rtrim((string) ($claims['iss'] ?? ''), '/') !== $this->issuer()) {
            throw new KuartalIdException('ID token issuer mismatch.');
        }

        $clientId = (string) $this->config('client_id');
        $aud = (array) ($claims['aud'] ?? []);
        if (! in_array($clientId, $aud, true)) {
            throw new KuartalIdException('ID token audience mismatch.');
        }
        if (count($aud) > 1 && ($claims['azp'] ?? null) !== $clientId) {
            throw new KuartalIdException('ID token authorized party mismatch.');
        }
        if (empty($claims['exp']) || empty($claims['iat'])) {
            throw new KuartalIdException('ID token missing exp/iat.');
        }
        if (! is_string($claims['nonce'] ?? null) || $expectedNonce === '' || ! hash_equals($expectedNonce, $claims['nonce'])) {
            throw new KuartalIdException('ID token nonce mismatch.');
        }
        if (! is_string($claims['sub'] ?? null) || $claims['sub'] === '') {
            throw new KuartalIdException('ID token has no subject.');
        }

        return $claims;
    }

    public function userinfo(string $accessToken): array
    {
        $endpoint = $this->discovery()['userinfo_endpoint'] ?? null;
        if (! $endpoint) {
            return [];
        }
        try {
            $response = Http::timeout(10)->acceptJson()->withToken($accessToken)->get($endpoint);
        } catch (Throwable) {
            return [];
        }

        return $response->successful() && is_array($response->json()) ? $response->json() : [];
    }

    private function decode(string $jwt, bool $refreshed): array
    {
        try {
            $header = json_decode(JWT::urlsafeB64Decode(explode('.', $jwt)[0] ?? ''), true);
            if (($header['alg'] ?? null) !== 'RS256') {
                throw new KuartalIdException('ID token must be signed with RS256.');
            }

            JWT::$leeway = (int) $this->config('leeway', 60);
            $keys = JWK::parseKeySet($this->jwks($refreshed), 'RS256');

            if (isset($header['kid']) && ! isset($keys[$header['kid']]) && ! $refreshed) {
                return $this->decode($jwt, true); // key rotation
            }

            return json_decode(json_encode(JWT::decode($jwt, $keys)), true);
        } catch (KuartalIdException $e) {
            throw $e;
        } catch (UnexpectedValueException|\DomainException|\InvalidArgumentException $e) {
            throw new KuartalIdException('ID token verification failed: '.$e->getMessage(), 0, $e);
        }
    }
}
