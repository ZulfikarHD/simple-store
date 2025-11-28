<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test untuk Admin ProductController
 * mencakup semua operasi CRUD produk dengan validasi
 */
class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa guest tidak dapat akses halaman produk
     */
    public function test_guest_cannot_access_products_index(): void
    {
        $response = $this->get(route('admin.products.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test authenticated user dapat melihat daftar produk
     */
    public function test_authenticated_user_can_view_products_list(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id]);

        $response = $this->actingAs($user)->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Products/Index')
            ->has('products.data', 5)
            ->has('categories'));
    }

    /**
     * Test pencarian produk berdasarkan nama
     */
    public function test_can_search_products_by_name(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Product::factory()->create(['name' => 'Nasi Goreng Spesial', 'category_id' => $category->id]);
        Product::factory()->create(['name' => 'Mie Goreng', 'category_id' => $category->id]);

        $response = $this->actingAs($user)->get(route('admin.products.index', ['search' => 'Nasi']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Products/Index')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Nasi Goreng Spesial'));
    }

    /**
     * Test filter produk berdasarkan kategori
     */
    public function test_can_filter_products_by_category(): void
    {
        $user = User::factory()->admin()->create();
        $category1 = Category::factory()->create(['name' => 'Makanan']);
        $category2 = Category::factory()->create(['name' => 'Minuman']);
        Product::factory()->count(3)->create(['category_id' => $category1->id]);
        Product::factory()->count(2)->create(['category_id' => $category2->id]);

        $response = $this->actingAs($user)->get(route('admin.products.index', ['category_id' => $category1->id]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('products.data', 3));
    }

    /**
     * Test filter produk berdasarkan status aktif
     */
    public function test_can_filter_products_by_active_status(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id, 'is_active' => true]);
        Product::factory()->count(2)->create(['category_id' => $category->id, 'is_active' => false]);

        $response = $this->actingAs($user)->get(route('admin.products.index', ['is_active' => '1']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('products.data', 3));
    }

    /**
     * Test dapat mengakses halaman create produk
     */
    public function test_can_access_create_product_page(): void
    {
        $user = User::factory()->admin()->create();
        Category::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('admin.products.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Products/Create')
            ->has('categories', 3));
    }

    /**
     * Test dapat membuat produk baru tanpa gambar
     */
    public function test_can_create_product_without_image(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $productData = [
            'name' => 'Produk Baru',
            'description' => 'Deskripsi produk baru',
            'price' => 25000,
            'stock' => 50,
            'category_id' => $category->id,
            'is_active' => true,
            'is_featured' => false,
        ];

        $response = $this->actingAs($user)->post(route('admin.products.store'), $productData);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('products', ['name' => 'Produk Baru']);
    }

    /**
     * Test dapat membuat produk baru dengan gambar
     */
    public function test_can_create_product_with_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $productData = [
            'name' => 'Produk Dengan Gambar',
            'description' => 'Deskripsi produk',
            'price' => 30000,
            'stock' => 25,
            'category_id' => $category->id,
            'is_active' => true,
            'is_featured' => true,
            'image' => UploadedFile::fake()->image('product.jpg', 400, 400),
        ];

        $response = $this->actingAs($user)->post(route('admin.products.store'), $productData);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['name' => 'Produk Dengan Gambar']);

        $product = Product::where('name', 'Produk Dengan Gambar')->first();
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    /**
     * Test validasi saat membuat produk
     */
    public function test_create_product_validation(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post(route('admin.products.store'), []);

        $response->assertSessionHasErrors(['name', 'price', 'stock', 'category_id']);
    }

    /**
     * Test dapat mengakses halaman edit produk
     */
    public function test_can_access_edit_product_page(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($user)->get(route('admin.products.edit', $product));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Products/Edit')
            ->has('product')
            ->has('categories'));
    }

    /**
     * Test dapat mengupdate produk
     */
    public function test_can_update_product(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $updateData = [
            'name' => 'Produk Diupdate',
            'description' => 'Deskripsi baru',
            'price' => 35000,
            'stock' => 100,
            'category_id' => $category->id,
            'is_active' => true,
            'is_featured' => true,
        ];

        $response = $this->actingAs($user)->put(route('admin.products.update', $product), $updateData);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Produk Diupdate']);
    }

    /**
     * Test dapat menghapus produk yang tidak ada di order aktif
     */
    public function test_can_delete_product_without_active_orders(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($user)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * Test tidak dapat menghapus produk yang ada di order aktif
     */
    public function test_cannot_delete_product_with_active_orders(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        // Buat order dengan status pending yang berisi produk
        $order = Order::factory()->create(['status' => 'pending']);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->delete(route('admin.products.destroy', $product));

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    /**
     * Test pagination berfungsi dengan benar
     */
    public function test_products_pagination_works(): void
    {
        $user = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Product::factory()->count(15)->create(['category_id' => $category->id]);

        $response = $this->actingAs($user)->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('products.data', 10)
            ->where('products.total', 15)
            ->where('products.last_page', 2));
    }
}
