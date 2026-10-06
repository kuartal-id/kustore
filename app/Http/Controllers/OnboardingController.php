<?php

namespace App\Http\Controllers;

use App\Enums\VerificationLevel;
use App\Models\Store;
use App\Rules\Username;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function username(Request $request): View|RedirectResponse
    {
        if ($request->user()->store) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.username', [
            'username' => old('username', $request->session()->get('onboarding.username')),
        ]);
    }

    public function storeUsername(Request $request): RedirectResponse
    {
        if ($request->user()->store) {
            return redirect()->route('dashboard');
        }

        $request->merge(['username' => strtolower(trim((string) $request->input('username')))]);
        $data = $request->validate(self::usernameRules(), [
            'username.unique' => 'That username is already taken.',
        ]);

        $request->session()->put('onboarding.username', $data['username']);

        return redirect()->route('onboarding.type');
    }

    public function type(Request $request): View|RedirectResponse
    {
        if ($request->user()->store) {
            return redirect()->route('dashboard');
        }
        if (! $request->session()->has('onboarding.username')) {
            return redirect()->route('onboarding.username');
        }

        return view('onboarding.type', [
            'username' => $request->session()->get('onboarding.username'),
            'name' => $request->user()->name,
        ]);
    }

    public function storeType(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->store) {
            return redirect()->route('dashboard');
        }

        $username = (string) $request->session()->get('onboarding.username');
        $request->merge(['username' => $username]);

        $data = $request->validate([
            'username' => self::usernameRules(),
            'account_type' => ['required', Rule::in(array_keys(config('kustore.account_types')))],
            'display_name' => ['required', 'string', 'max:80'],
        ]);

        DB::transaction(function () use ($user, $data) {
            $store = new Store([
                'username' => $data['username'],
                'display_name' => $data['display_name'],
                'account_type' => $data['account_type'],
                'layout' => $data['account_type'] === 'individual' ? 'minimal' : 'commerce',
                'color_mode' => 'light',
                'currency' => config('kustore.currency', 'IDR'),
            ]);
            $store->user()->associate($user);
            $store->verification_level = $user->isKuartalIdUser() ? VerificationLevel::KuartalId : VerificationLevel::Unverified;
            $store->save();
        });

        $request->session()->forget('onboarding.username');

        return redirect()->route('dashboard')->with('status', 'Your Kustore is ready. Add links and products, then publish when you are happy.');
    }

    public static function usernameRules(): array
    {
        return ['required', 'string', new Username, Rule::unique('stores', 'username')];
    }
}
