<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        return $request->user()->canPublishStore()
            ? redirect()->route('dashboard')
            : view('auth.verify-email');
    }

    public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = $request->user();
        abort_unless(hash_equals((string) $user->getKey(), $id), 403);
        abort_unless(hash_equals(sha1((string) $user->getEmailForVerification()), $hash), 403);

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->route('dashboard')->with('status', 'Email verified. You can now publish your Kustore.');
    }

    public function send(Request $request): RedirectResponse
    {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }

        return back()->with('status', 'A new verification link has been sent to your email.');
    }
}
