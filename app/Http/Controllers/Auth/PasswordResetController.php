<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Password reset for local (email + password) accounts only.
 *
 * Laravel's default broker looks users up by email alone, which could match a Kuartal ID account
 * that happens to share the address. Kustore never links accounts by email, so the user lookup is
 * done here (local accounts only) and only the broker's token storage is reused.
 */
class PasswordResetController extends Controller
{
    public function request(): View
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $user = self::localUser($data['email']);

        if ($user) {
            $broker = Password::broker();
            // The broker's own throttle (config auth.passwords.users.throttle) stops repeated emails.
            if (! $broker->getRepository()->recentlyCreatedToken($user)) {
                $user->notify(new ResetPasswordNotification($broker->createToken($user)));
            }
        }

        // Same answer whether or not the account exists, so emails can't be probed.
        return back()->with('status', 'If that email belongs to a Kustore account with a password, we have sent a reset link. Check your inbox.');
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => (string) $request->query('email', '')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $user = self::localUser($data['email']);
        $repository = Password::broker()->getRepository();

        if (! $user || ! $repository->exists($user, $data['token'])) {
            throw ValidationException::withMessages(['email' => 'This reset link is invalid or has expired. Please request a new one.']);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ])->save();
        $repository->delete($user);

        return redirect()->route('login')->with('status', 'Your password has been reset. You can log in now.');
    }

    private static function localUser(string $email): ?User
    {
        return User::whereNull('kuartal_id_sub')
            ->whereNotNull('password')
            ->where('email', strtolower($email))
            ->first();
    }
}
