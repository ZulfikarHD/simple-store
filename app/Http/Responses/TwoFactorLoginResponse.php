<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * Custom Two Factor Login Response untuk redirect berdasarkan role user
 * setelah berhasil verifikasi 2FA, yaitu:
 * - Admin diarahkan ke /admin/dashboard
 * - Customer diarahkan ke halaman home (/)
 *
 * @author Zulfikar Hidayatullah
 */
class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    /**
     * Create an HTTP response yang merepresentasikan redirect setelah verifikasi 2FA
     * dengan logika role-based routing berdasarkan tipe user
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        // Untuk request JSON/API, return response JSON standar
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => true]);
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
