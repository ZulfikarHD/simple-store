<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\Request;

/**
 * Policy untuk mengatur authorization CartItem
 * memastikan hanya pemilik cart yang dapat modify cart items
 * untuk mencegah IDOR (Insecure Direct Object Reference) attacks
 */
class CartItemPolicy
{
    /**
     * Constructor dengan dependency injection CartService
     */
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Determine if user can update the cart item
     * dengan verifikasi bahwa cart item belongs to current session's cart
     *
     * @param  Request  $request  HTTP request dengan session info
     * @param  CartItem  $cartItem  Item yang akan diupdate
     * @return bool True jika item belongs to current cart
     */
    public function update(Request $request, CartItem $cartItem): bool
    {
        $currentCart = $this->cartService->getCart();

        return $cartItem->cart_id === $currentCart->id;
    }

    /**
     * Determine if user can delete the cart item
     * dengan verifikasi bahwa cart item belongs to current session's cart
     *
     * @param  Request  $request  HTTP request dengan session info
     * @param  CartItem  $cartItem  Item yang akan dihapus
     * @return bool True jika item belongs to current cart
     */
    public function delete(Request $request, CartItem $cartItem): bool
    {
        $currentCart = $this->cartService->getCart();

        return $cartItem->cart_id === $currentCart->id;
    }
}
