<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HeaderKeamanan
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=(self), microphone=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $vite = '';
        $hotFile = public_path('hot');
        if (file_exists($hotFile)) {
            $viteUrl = trim((string) file_get_contents($hotFile));
            $parsed = parse_url($viteUrl);

            if (isset($parsed['host'], $parsed['port'])) {
                // parse_url returns IPv6 literals WITH their brackets (e.g. "[::1]").
                // Browsers reject bracketed IPv6 literals inside a CSP source list, so
                // normalise any loopback address to "127.0.0.1" to match the Vite
                // dev server (configured to bind to 127.0.0.1 in vite.config.js).
                // This keeps the @vite() asset URLs and the CSP in sync.
                $host = $parsed['host'];
                if ($host === '[::1]' || $host === '::1' || $host === 'localhost') {
                    $host = '127.0.0.1';
                }

                $schemeHost = $parsed['scheme'] . '://' . $host . ':' . $parsed['port'];
                $vite = " {$schemeHost} ws://{$host}:{$parsed['port']}";
            }
        }

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'{$vite}",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net{$vite}",
            "img-src 'self' data:",
            "font-src 'self' https://fonts.bunny.net",
            "connect-src 'self'{$vite}",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
