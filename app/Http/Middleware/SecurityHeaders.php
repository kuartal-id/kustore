<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
        }
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        $issuer = rtrim((string) config('services.kuartal_id.issuer'), '/');

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self'",
            "img-src 'self' data: https:",   // https: for Kuartal ID avatars
            "font-src 'self'",
            "connect-src 'self'",
            "form-action 'self' ".$issuer,
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "object-src 'none'",
            'upgrade-insecure-requests',
        ]);

        $headers = [
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Content-Security-Policy' => $csp,
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        // Local http dev: don't force https upgrades or HSTS on 127.0.0.1.
        if (app()->environment('local') && ! $request->isSecure()) {
            $headers['Content-Security-Policy'] = str_replace('; upgrade-insecure-requests', '', $csp);
            unset($headers['Strict-Transport-Security']);
        }

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}
