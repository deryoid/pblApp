<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and attach recommended security headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // 1. Mencegah Clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 2. Mencegah MIME-type Sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 3. XSS Filter perlindungan browser lama
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // 4. Batasi pengiriman informasi referrer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 5. Batasi akses fitur sensor perangkat keras yang tidak relevan
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
