<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * Custom Login Response untuk redirect berdasarkan role user
 * setelah berhasil login, yaitu:
 * - Admin diarahkan ke /admin/dashboard
 * - Customer diarahkan ke halaman home (/)
 *
 * @author Zulfikar Hidayatullah
 */
class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response yang merepresentasikan redirect setelah login
     * dengan logika role-based routing berdasarkan tipe user
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        // Untuk request JSON/API, return response JSON standar
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        $user = $request->user();

        // Redirect berdasarkan role user
        if ($user && $user->isAdmin()) {
            return redirect()->intended('/admin/dashboard');
        }

        // Default redirect untuk customer ke halaman home
        return redirect()->intended('/');
    }
}
