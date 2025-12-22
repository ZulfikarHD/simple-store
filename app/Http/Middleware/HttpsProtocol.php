<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk enforce HTTPS connections pada production environment
 * dengan redirect otomatis dari HTTP ke HTTPS untuk keamanan data transmission
 */
class HttpsProtocol
{
    /**
     * Handle an incoming request dan enforce HTTPS di production
     * dimana semua HTTP requests akan di-redirect ke HTTPS dengan 301 status
     *
     * @param  Request  $request  HTTP request
     * @param  Closure  $next  Next middleware
     * @return Response Response atau redirect ke HTTPS
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Enforce HTTPS hanya di production environment
        // Development environment tetap bisa menggunakan HTTP
        if (! $request->secure() && app()->environment('production')) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
