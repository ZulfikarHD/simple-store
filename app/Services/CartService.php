<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Session;

/**
 * CartService untuk mengelola operasi keranjang belanja
 * dengan session-based tracking untuk guest users
 * mencakup add, update, remove, dan clear cart operations
 */
class CartService
{
    /**
     * Session key untuk menyimpan cart_id
     */
    private const SESSION_KEY = 'cart_id';

    /**
     * Mendapatkan cart untuk session saat ini
     * dengan eager loading items dan products
     * serta support untuk authenticated user carts
     */
    public function getCart(): Cart
    {
        // Ensure session has started
        if (! Session::isStarted()) {
            Session::start();
        }

        $sessionId = Session::getId();
        $userId = auth()->id();

        // Jika user authenticated, prioritas cart berdasarkan user_id
        // Jika tidak, gunakan session_id
        $cart = Cart::query()
            ->when($userId, function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }, function ($query) use ($sessionId) {
                $query->where('session_id', $sessionId)
                    ->whereNull('user_id');
            })
            ->with(['items.product'])
            ->first();

        if (! $cart) {
            $cart = Cart::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
            ]);
            $cart->load(['items.product']);
        }

        return $cart;
    }

    /**
     * Merge guest cart dengan user cart setelah login
     * untuk mencegah session fixation dan maintain cart items
     * Call this method in LoginController after successful authentication
     *
     * @param  string  $guestSessionId  Session ID sebelum login
     */
    public function mergeGuestCart(string $guestSessionId): void
    {
        $userId = auth()->id();

        if (! $userId) {
            return;
        }

        // Find guest cart
        $guestCart = Cart::where('session_id', $guestSessionId)
            ->whereNull('user_id')
            ->first();

        if (! $guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        // Find or create user cart
        $userCart = Cart::firstOrCreate([
            'user_id' => $userId,
        ], [
            'session_id' => Session::getId(),
        ]);

        // Merge items dengan database transaction untuk atomic operation
        \DB::transaction(function () use ($guestCart, $userCart) {
            foreach ($guestCart->items as $guestItem) {
                $existingItem = $userCart->items()
                    ->where('product_id', $guestItem->product_id)
                    ->first();

                if ($existingItem) {
                    // Jika item sudah ada, tambahkan quantity
                    $existingItem->increment('quantity', $guestItem->quantity);
                } else {
                    // Jika item belum ada, buat baru
                    $userCart->items()->create([
                        'product_id' => $guestItem->product_id,
                        'quantity' => $guestItem->quantity,
                    ]);
                }
            }

            // Delete guest cart setelah merge
            $guestCart->items()->delete();
            $guestCart->delete();
        });
    }

    /**
     * Menambahkan produk ke cart
     * Jika produk sudah ada, quantity akan ditambahkan
     *
     * @param  int  $productId  ID produk yang akan ditambahkan
     * @param  int  $quantity  Jumlah yang akan ditambahkan
     * @return CartItem Item yang ditambahkan atau diupdate
     */
    public function addItem(int $productId, int $quantity = 1): CartItem
    {
        $cart = $this->getCart();
        $product = Product::findOrFail($productId);

        // Cek apakah produk sudah ada di cart
        $existingItem = $cart->items()->where('product_id', $productId)->first();

        if ($existingItem) {
            // Update quantity jika produk sudah ada
            $existingItem->increment('quantity', $quantity);
            $existingItem->refresh();
            $existingItem->load('product');

            return $existingItem;
        }

        // Buat item baru jika produk belum ada di cart
        $item = $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
        ]);
        $item->load('product');

        return $item;
    }

    /**
     * Mengupdate quantity item di cart
     *
     * @param  int  $itemId  ID cart item
     * @param  int  $quantity  Quantity baru
     * @return CartItem Item yang diupdate
     */
    public function updateQuantity(int $itemId, int $quantity): CartItem
    {
        $cart = $this->getCart();
        $item = $cart->items()->findOrFail($itemId);

        $item->update(['quantity' => $quantity]);
        $item->load('product');

        return $item;
    }

    /**
     * Menghapus item dari cart
     *
     * @param  int  $itemId  ID cart item yang akan dihapus
     */
    public function removeItem(int $itemId): bool
    {
        $cart = $this->getCart();
        $item = $cart->items()->find($itemId);

        if (! $item) {
            return false;
        }

        return $item->delete();
    }

    /**
     * Mengosongkan semua item di cart
     */
    public function clearCart(): bool
    {
        $cart = $this->getCart();

        return $cart->items()->delete() >= 0;
    }

    /**
     * Mendapatkan total jumlah item di cart
     */
    public function getTotalItems(): int
    {
        return $this->getCart()->total_items;
    }

    /**
     * Mendapatkan subtotal cart
     */
    public function getSubtotal(): float
    {
        return $this->getCart()->subtotal;
    }

    /**
     * Mendapatkan data cart untuk response
     * dengan format yang siap digunakan di frontend
     *
     * @return array<string, mixed>
     */
    public function getCartData(): array
    {
        $cart = $this->getCart();

        return [
            'id' => $cart->id,
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'slug' => $item->product->slug,
                        'price' => $item->product->price,
                        'image' => $item->product->image,
                        'is_available' => $item->product->isAvailable(),
                    ],
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ];
            })->values()->all(), // Konversi ke array untuk konsistensi
            'total_items' => $cart->total_items,
            'subtotal' => $cart->subtotal,
            'formatted_subtotal' => $cart->formatted_subtotal,
        ];
    }
}
