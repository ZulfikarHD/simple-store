<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk mengelola operasi keranjang belanja
 * termasuk add, view, update, dan remove cart items
 * dengan integrasi CartService untuk business logic
 */
class CartController extends Controller
{
    /**
     * Constructor dengan dependency injection CartService
     */
    public function __construct(public CartService $cartService) {}

    /**
     * Menampilkan halaman keranjang belanja
     * dengan daftar items dan total harga
     */
    public function show(): Response
    {
        return Inertia::render('Cart', [
            'cart' => $this->cartService->getCartData(),
        ]);
    }

    /**
     * Menambahkan produk ke keranjang belanja
     * dengan validasi product_id dan quantity
     */
    public function store(AddToCartRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->cartService->addItem(
            $validated['product_id'],
            $validated['quantity']
        );

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    /**
     * Mengupdate quantity item di keranjang
     * dengan validasi quantity minimal 1 dan ownership verification
     * untuk mencegah IDOR attacks
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        // Verifikasi bahwa cart item belongs to current session's cart
        $this->authorize('update', $cartItem);

        $validated = $request->validated();

        $this->cartService->updateQuantity(
            $cartItem->id,
            $validated['quantity']
        );

        return back()->with('success', 'Keranjang berhasil diperbarui.');
    }

    /**
     * Menghapus item dari keranjang belanja
     * dengan ownership verification untuk mencegah IDOR attacks
     */
    public function destroy(CartItem $cartItem): RedirectResponse
    {
        // Verifikasi bahwa cart item belongs to current session's cart
        $this->authorize('delete', $cartItem);

        $deleted = $this->cartService->removeItem($cartItem->id);

        if (! $deleted) {
            return back()->with('error', 'Item tidak ditemukan di keranjang.');
        }

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }
}
