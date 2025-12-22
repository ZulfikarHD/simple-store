<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Feature test untuk halaman detail produk (CUST-004)
 * Memastikan halaman detail produk menampilkan informasi lengkap
 * termasuk gambar, deskripsi, harga, dan produk terkait
 *
 * @author Zulfikar Hidayatullah
 */
class ProductDetailTest extends TestCase
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
     * Test dapat mengakses halaman detail produk dengan slug
     */
    public function test_can_view_product_detail_page(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create([
            'name' => 'Nasi Goreng Spesial',
            'slug' => 'nasi-goreng-spesial',
            'is_active' => true,
        ]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('ProductDetail')
            ->has('product.data')
        );
    }

    /**
     * Test halaman detail menampilkan informasi produk yang benar
     */
    public function test_product_detail_shows_correct_information(): void
    {
        $category = Category::factory()->create([
            'name' => 'Makanan',
            'is_active' => true,
        ]);
        $product = Product::factory()->for($category)->create([
            'name' => 'Nasi Goreng Spesial',
            'slug' => 'nasi-goreng-spesial',
            'description' => 'Nasi goreng dengan bumbu rahasia',
            'price' => 25000,
            'is_active' => true,
        ]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ProductDetail')
            ->where('product.data.name', 'Nasi Goreng Spesial')
            ->where('product.data.slug', 'nasi-goreng-spesial')
            ->where('product.data.description', 'Nasi goreng dengan bumbu rahasia')
            ->where('product.data.price', 25000)
            ->has('product.data.category')
            ->where('product.data.category.name', 'Makanan')
        );
    }

    /**
     * Test 404 untuk produk yang tidak ada
     */
    public function test_returns_404_for_nonexistent_product(): void
    {
        $response = $this->get('/products/produk-tidak-ada');

        $response->assertStatus(404);
    }

    /**
     * Test 404 untuk produk yang tidak aktif
     */
    public function test_returns_404_for_inactive_product(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create([
            'slug' => 'produk-nonaktif',
            'is_active' => false,
        ]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertStatus(404);
    }

    /**
     * Test halaman detail menampilkan produk terkait
     */
    public function test_product_detail_shows_related_products(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $mainProduct = Product::factory()->for($category)->create([
            'slug' => 'produk-utama',
            'is_active' => true,
        ]);
        Product::factory()->count(3)->for($category)->create(['is_active' => true]);

        $response = $this->get('/products/'.$mainProduct->slug);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ProductDetail')
            ->has('relatedProducts.data', 3)
        );
    }

    /**
     * Test produk terkait tidak termasuk produk yang sedang dilihat
     */
    public function test_related_products_exclude_current_product(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $mainProduct = Product::factory()->for($category)->create([
            'name' => 'Produk Utama',
            'slug' => 'produk-utama',
            'is_active' => true,
        ]);
        $relatedProduct = Product::factory()->for($category)->create([
            'name' => 'Produk Terkait',
            'is_active' => true,
        ]);

        $response = $this->get('/products/'.$mainProduct->slug);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ProductDetail')
            ->has('relatedProducts.data', 1)
            ->where('relatedProducts.data.0.name', 'Produk Terkait')
        );
    }

    /**
     * Test produk terkait hanya dari kategori yang sama
     */
    public function test_related_products_only_from_same_category(): void
    {
        $makanan = Category::factory()->create(['name' => 'Makanan', 'is_active' => true]);
        $minuman = Category::factory()->create(['name' => 'Minuman', 'is_active' => true]);

        $mainProduct = Product::factory()->for($makanan)->create([
            'slug' => 'produk-makanan',
            'is_active' => true,
        ]);
        Product::factory()->count(2)->for($makanan)->create(['is_active' => true]);
        Product::factory()->count(3)->for($minuman)->create(['is_active' => true]);

        $response = $this->get('/products/'.$mainProduct->slug);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ProductDetail')
            ->has('relatedProducts.data', 2)
        );
    }

    /**
     * Test produk terkait maksimal 4 item
     */
    public function test_related_products_limited_to_four(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $mainProduct = Product::factory()->for($category)->create([
            'slug' => 'produk-utama',
            'is_active' => true,
        ]);
        Product::factory()->count(10)->for($category)->create(['is_active' => true]);

        $response = $this->get('/products/'.$mainProduct->slug);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ProductDetail')
            ->has('relatedProducts.data', 4)
        );
    }

    /**
     * Test produk terkait hanya produk aktif
     */
    public function test_related_products_only_active(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $mainProduct = Product::factory()->for($category)->create([
            'slug' => 'produk-utama',
            'is_active' => true,
        ]);
        Product::factory()->count(2)->for($category)->create(['is_active' => true]);
        Product::factory()->count(3)->for($category)->create(['is_active' => false]);

        $response = $this->get('/products/'.$mainProduct->slug);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ProductDetail')
            ->has('relatedProducts.data', 2)
        );
    }

    /**
     * Test halaman detail tanpa produk terkait
     */
    public function test_product_detail_works_without_related_products(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create([
            'slug' => 'produk-satu-satunya',
            'is_active' => true,
        ]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ProductDetail')
            ->has('relatedProducts.data', 0)
        );
    }

    /**
     * Test data produk memiliki struktur yang benar
     */
    public function test_product_data_has_correct_structure(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create(['is_active' => true]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('ProductDetail')
            ->has('product.data', fn (Assert $prod) => $prod
                ->has('id')
                ->has('name')
                ->has('slug')
                ->has('description')
                ->has('price')
                ->has('image')
                ->has('stock')
                ->has('is_available')
                ->has('category')
                ->has('stock_status')
            )
        );
    }

    /**
     * Test status ketersediaan produk ditampilkan dengan benar
     */
    public function test_product_availability_status_is_correct(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $availableProduct = Product::factory()->for($category)->create([
            'slug' => 'produk-tersedia',
            'stock' => 10,
            'is_active' => true,
        ]);
        $unavailableProduct = Product::factory()->for($category)->create([
            'slug' => 'produk-habis',
            'stock' => 0,
            'is_active' => true,
        ]);

        $response1 = $this->get('/products/'.$availableProduct->slug);
        $response1->assertInertia(fn (Assert $page) => $page
            ->where('product.data.is_available', true)
        );

        $response2 = $this->get('/products/'.$unavailableProduct->slug);
        $response2->assertInertia(fn (Assert $page) => $page
            ->where('product.data.is_available', false)
        );
    }
}
