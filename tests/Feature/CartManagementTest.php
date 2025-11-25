<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Feature test untuk manajemen keranjang belanja (CUST-005 & CUST-006)
 * Memastikan fitur add to cart, view cart, update quantity, dan remove item
 * berfungsi dengan benar dengan session-based cart
 *
 * @author Zulfikar Hidayatullah
 */
class CartManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup untuk setiap test dengan menonaktifkan Vite
     * karena manifest tidak tersedia saat testing
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /**
     * Test menambahkan produk ke cart dengan sukses
     */
    public function test_can_add_product_to_cart(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create([
            'is_active' => true,
            'stock' => 10,
        ]);

        $response = $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Produk berhasil ditambahkan ke keranjang.');

        $this->assertDatabaseHas('carts', [
            'session_id' => session()->getId(),
        ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    /**
     * Test menambahkan produk yang sudah ada di cart akan update quantity
     * Menggunakan direct CartService untuk avoid session issues di testing
     */
    public function test_adding_existing_product_updates_quantity(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create([
            'is_active' => true,
            'stock' => 10,
        ]);

        $cartService = app(\App\Services\CartService::class);

        // Add product pertama kali
        $cartService->addItem($product->id, 2);

        // Add product yang sama lagi
        $cartService->addItem($product->id, 3);

        // Harus ada 1 cart item dengan quantity 5 (2+3)
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }

    /**
     * Test validasi gagal ketika product_id tidak valid
     */
    public function test_cannot_add_invalid_product_to_cart(): void
    {
        $response = $this->post('/cart', [
            'product_id' => 99999,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    /**
     * Test validasi gagal ketika quantity adalah 0 atau negatif
     */
    public function test_cannot_add_product_with_zero_or_negative_quantity(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create(['is_active' => true]);

        $response = $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

        $response->assertSessionHasErrors('quantity');

        $response = $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => -1,
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    /**
     * Test dapat melihat halaman cart
     */
    public function test_can_view_cart_page(): void
    {
        $response = $this->get('/cart');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Cart')
            ->has('cart')
            ->has('cart.items')
            ->where('cart.total_items', 0)
        );
    }

    /**
     * Test cart data calculation dengan items
     * Menggunakan CartService untuk verify business logic
     */
    public function test_cart_calculates_totals_correctly(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product1 = Product::factory()->for($category)->create([
            'name' => 'Product 1',
            'price' => 10000,
            'is_active' => true,
        ]);
        $product2 = Product::factory()->for($category)->create([
            'name' => 'Product 2',
            'price' => 20000,
            'is_active' => true,
        ]);

        $cartService = app(\App\Services\CartService::class);

        // Add products ke cart menggunakan service
        $cartService->addItem($product1->id, 2);
        $cartService->addItem($product2->id, 1);

        // Verify cart data
        $cartData = $cartService->getCartData();

        $this->assertCount(2, $cartData['items']);
        $this->assertEquals(3, $cartData['total_items']);
        $this->assertEquals(40000, $cartData['subtotal']);
    }

    /**
     * Test dapat mengupdate quantity item di cart
     * Menggunakan direct CartService untuk avoid session issues di testing
     */
    public function test_can_update_cart_item_quantity(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create(['is_active' => true]);

        $cartService = app(\App\Services\CartService::class);

        // Add product ke cart menggunakan service
        $cartService->addItem($product->id, 2);

        $cartItem = CartItem::first();

        // Update quantity menggunakan service
        $cartService->updateQuantity($cartItem->id, 5);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    /**
     * Test validasi gagal ketika update quantity dengan nilai invalid
     */
    public function test_cannot_update_quantity_with_invalid_value(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create(['is_active' => true]);

        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 2]);
        $cartItem = CartItem::first();

        $response = $this->patch("/cart/{$cartItem->id}", [
            'quantity' => 0,
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    /**
     * Test dapat menghapus item dari cart
     * Menggunakan direct CartService untuk avoid session issues di testing
     */
    public function test_can_remove_item_from_cart(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create(['is_active' => true]);

        $cartService = app(\App\Services\CartService::class);

        // Add product ke cart menggunakan service
        $cartService->addItem($product->id, 2);

        $cartItem = CartItem::first();

        // Remove item menggunakan service
        $cartService->removeItem($cartItem->id);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    /**
     * Test menghapus item yang tidak ada mengembalikan error
     */
    public function test_cannot_remove_non_existent_item(): void
    {
        $response = $this->delete('/cart/99999');

        // Item tidak ditemukan karena route model binding
        $response->assertNotFound();
    }

    /**
     * Test cart counter accuracy setelah berbagai operasi
     * Menggunakan CartService untuk verify item count logic
     */
    public function test_cart_counter_reflects_accurate_item_count(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product1 = Product::factory()->for($category)->create(['is_active' => true]);
        $product2 = Product::factory()->for($category)->create(['is_active' => true]);

        $cartService = app(\App\Services\CartService::class);

        // Add 2 products menggunakan service
        $cartService->addItem($product1->id, 2);
        $cartService->addItem($product2->id, 3);

        // Verify total items
        $this->assertEquals(5, $cartService->getTotalItems());

        // Remove one product menggunakan service
        $cartItem = CartItem::first();
        $cartService->removeItem($cartItem->id);

        // Verify updated total items
        $this->assertEquals(3, $cartService->getTotalItems());
    }

    /**
     * Test empty cart menampilkan empty state
     */
    public function test_empty_cart_displays_correctly(): void
    {
        $response = $this->get('/cart');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Cart')
            ->has('cart.items', 0)
            ->where('cart.total_items', 0)
            ->where('cart.subtotal', 0)
        );
    }
}
