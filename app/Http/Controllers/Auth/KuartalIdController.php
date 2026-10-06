<?php

namespace App\Http\Controllers\Auth;

use App\Auth\KuartalId\KuartalIdClient;
use App\Auth\KuartalId\KuartalIdException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KuartalIdController extends Controller
{
    private const SESSION_KEY = 'kuartal_id.oidc';

    public function __construct(private KuartalIdClient $client) {}

    public function redirect(Request $request): RedirectResponse
    {
        if (! $this->client->isConfigured()) {
            return redirect()->route('login')->withErrors(['kuartal' => 'Kuartal ID sign-in is not configured yet.']);
        }

        $state = KuartalIdClient::randomToken();
        $nonce = KuartalIdClient::randomToken();
        $verifier = KuartalIdClient::newCodeVerifier();

        $request->session()->put(self::SESSION_KEY, [
            'state' => $state,
            'nonce' => $nonce,
            'verifier' => $verifier,
            'created_at' => now()->timestamp,
        ]);

        try {
            return redirect()->away($this->client->authorizationUrl($state, $nonce, $verifier));
        } catch (KuartalIdException $e) {
            Log::warning('kuartal_id.redirect_failed', ['message' => $e->getMessage()]);

            return redirect()->route('login')->withErrors(['kuartal' => 'Kuartal ID is unavailable right now. Please try again shortly.']);
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        // One-time use: always remove the pending login state.
        $pending = $request->session()->pull(self::SESSION_KEY);

        if ($request->filled('error')) {
            return $this->fail('Sign-in with Kuartal ID was cancelled.', 'provider_error:'.substr((string) $request->query('error'), 0, 50));
        }

        $state = (string) $request->query('state', '');
        if (! is_array($pending) || $state === '' || ! hash_equals((string) $pending['state'], $state)) {
            return $this->fail('Your sign-in session expired or was invalid. Please try again.', 'state_mismatch');
        }
        if (now()->timestamp - (int) ($pending['created_at'] ?? 0) > 600) {
            return $this->fail('Your sign-in session expired. Please try again.', 'state_expired');
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            return $this->fail('Kuartal ID did not return an authorization code.', 'missing_code');
        }

        try {
            $tokens = $this->client->exchangeCode($code, (string) $pending['verifier']);
            $claims = $this->client->verifyIdToken((string) $tokens['id_token'], (string) $pending['nonce']);
            $profile = ! empty($tokens['access_token']) ? $this->client->userinfo((string) $tokens['access_token']) : [];
        } catch (KuartalIdException $e) {
            return $this->fail('We could not verify your Kuartal ID sign-in. Please try again.', $e->getMessage());
        }

        // userinfo must describe the same subject, otherwise ignore it.
        if (($profile['sub'] ?? null) !== $claims['sub']) {
            $profile = [];
        }
        $info = array_merge($claims, $profile);

        // Identity is the "sub" claim only. Never match/link accounts by email.
        $user = User::firstOrNew(['kuartal_id_sub' => $claims['sub']]);
        $user->name = (string) ($info['name'] ?? $info['preferred_username'] ?? $user->name ?? 'Kuartal user');
        if (! empty($info['email'])) {
            $user->email = strtolower((string) $info['email']);
            $user->email_verified_at = ! empty($info['email_verified']) ? ($user->email_verified_at ?? now()) : null;
        }
        if (! empty($info['picture']) && str_starts_with((string) $info['picture'], 'https://')) {
            $user->avatar_url = (string) $info['picture'];
        }
        $user->password = null;
        $user->last_login_at = now();
        $user->save();

        Auth::login($user, remember: false);
        $request->session()->regenerate();

        return $user->store
            ? redirect()->intended(route('dashboard'))
            : redirect()->route('onboarding.username');
    }

    private function fail(string $message, string $reason): RedirectResponse
    {
        Log::notice('kuartal_id.callback_rejected', ['reason' => $reason]);

        return redirect()->route('login')->withErrors(['kuartal' => $message]);
    }
}
