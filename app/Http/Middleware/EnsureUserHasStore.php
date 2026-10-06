<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasStore
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->store) {
            return redirect()->route('onboarding.username');
        }

        return $next($request);
    }
}
