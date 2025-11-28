<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk memastikan user yang mengakses route adalah admin
 * Middleware ini melakukan pengecekan role user untuk proteksi akses
 * ke halaman-halaman admin seperti dashboard, products, orders, dll
 *
 * @author Zulfikar Hidayatullah
 */
class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request dengan validasi role admin
     * Jika user bukan admin, akan di-redirect ke halaman home
     * dengan pesan error unauthorized access
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Pastikan user sudah login dan memiliki role admin
        if (! $user || ! $user->isAdmin()) {
            // Untuk request AJAX/API, kembalikan response 403
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.',
                ], 403);
            }

            // Untuk request biasa, redirect ke home dengan flash message
            return redirect()
                ->route('home')
                ->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman admin.');
        }

        return $next($request);
    }
}
