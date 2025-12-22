<?php

namespace App\Http\Responses;

use App\Services\CartService;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

/**
 * Custom Login Response untuk redirect berdasarkan role user
 * setelah berhasil login, yaitu:
 * - Admin diarahkan ke /admin/dashboard
 * - Customer diarahkan ke halaman home (/)
 * Dengan session regeneration dan cart merge untuk security
 *
 * @author Zulfikar Hidayatullah
 */
class LoginResponse implements LoginResponseContract
{
    /**
     * Constructor dengan dependency injection CartService
     */
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Create an HTTP response yang merepresentasikan redirect setelah login
     * dengan logika role-based routing berdasarkan tipe user
     * serta session regeneration dan cart merge untuk mencegah session fixation
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        // Untuk request JSON/API, return response JSON standar
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        // Store old session ID sebelum regenerate
        $oldSessionId = $request->session()->getId();

        // Regenerate session untuk mencegah session fixation attacks
        $request->session()->regenerate();

        // Merge guest cart dengan user cart setelah login
        // untuk maintain cart items dan prevent data loss
        try {
            $this->cartService->mergeGuestCart($oldSessionId);
        } catch (\Exception $e) {
            // Log error tapi jangan block login process
            \Log::warning('Failed to merge guest cart after login', [
                'user_id' => auth()->id(),
                'old_session_id' => $oldSessionId,
                'error' => $e->getMessage(),
            ]);
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
