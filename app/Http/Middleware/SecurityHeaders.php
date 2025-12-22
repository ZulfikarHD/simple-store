<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk menambahkan security headers pada response
 * termasuk X-Frame-Options, CSP, X-Content-Type-Options, dll
 * untuk mencegah clickjacking, XSS, dan security attacks lainnya
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request dan set security headers
     * dengan konfigurasi yang sesuai untuk aplikasi Laravel + Inertia + Vue
     *
     * @param  Request  $request  HTTP request
     * @param  Closure  $next  Next middleware
     * @return Response Response dengan security headers
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // X-Frame-Options: Mencegah clickjacking attacks
        // SAMEORIGIN allows framing dari same origin saja
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // X-Content-Type-Options: Mencegah MIME-type sniffing
        // nosniff memaksa browser untuk respect Content-Type header
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-XSS-Protection: Legacy XSS filter untuk older browsers
        // mode=block akan block page jika XSS terdeteksi
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy: Kontrol informasi referrer yang dikirim
        // strict-origin-when-cross-origin untuk balance security dan functionality
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy: Kontrol akses ke browser features
        // Disable geolocation, microphone, dan camera untuk security
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Content-Security-Policy: Kontrol resource loading
        // Konfigurasi untuk Laravel + Vite + Inertia + Vue
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://rsms.me", // unsafe-eval untuk Vue dev
            "style-src 'self' 'unsafe-inline' https://rsms.me", // unsafe-inline untuk inline styles
            "img-src 'self' data: https:", // Allow images dari HTTPS dan data URIs
            "font-src 'self' https://rsms.me data:", // Allow fonts dari rsms.me (Inter font)
            "connect-src 'self' https://wa.me", // Allow WhatsApp API
            "frame-ancestors 'self'", // Sama dengan X-Frame-Options
            "base-uri 'self'", // Restrict base tag
            "form-action 'self'", // Restrict form submissions
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
