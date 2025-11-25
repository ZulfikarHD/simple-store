<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Feature test untuk halaman katalog produk (CUST-001)
 * Memastikan produk aktif ditampilkan dengan benar pada halaman home
 *
 * @author Zulfikar Hidayatullah
 */
class ProductCatalogTest extends TestCase
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
     * Test halaman home dapat diakses dan mengembalikan status 200
     */
    public function test_home_page_returns_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test halaman home merender komponen Home dengan Inertia
     */
    public function test_home_page_renders_home_component(): void
    {
        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
        );
    }

    /**
     * Test produk aktif ditampilkan pada halaman home
     */
    public function test_active_products_are_displayed_on_home_page(): void
    {
        $category = Category::factory()->create();
        $activeProducts = Product::factory()
            ->count(3)
            ->for($category)
            ->create(['is_active' => true]);

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 3)
        );
    }

    /**
     * Test produk tidak aktif tidak ditampilkan pada halaman home
     */
    public function test_inactive_products_are_not_displayed(): void
    {
        $category = Category::factory()->create();

        Product::factory()
            ->for($category)
            ->create(['is_active' => true]);

        Product::factory()
            ->for($category)
            ->inactive()
            ->create();

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 1)
        );
    }

    /**
     * Test data produk memiliki struktur yang benar dari ProductResource
     */
    public function test_product_data_has_correct_structure(): void
    {
        $category = Category::factory()->create(['name' => 'Makanan Berat']);
        Product::factory()
            ->for($category)
            ->create([
                'name' => 'Nasi Goreng Spesial',
                'price' => 25000,
                'is_active' => true,
                'stock' => 10,
            ]);

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 1)
            ->has('products.data.0', fn (Assert $product) => $product
                ->has('id')
                ->has('name')
                ->has('slug')
                ->has('description')
                ->has('price')
                ->has('image')
                ->has('category')
                ->has('is_available')
            )
        );
    }

    /**
     * Test kategori produk di-eager load dengan benar
     */
    public function test_product_category_is_eager_loaded(): void
    {
        $category = Category::factory()->create(['name' => 'Minuman Dingin']);
        Product::factory()
            ->for($category)
            ->create(['is_active' => true]);

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data.0.category', fn (Assert $cat) => $cat
                ->has('id')
                ->has('name')
            )
        );
    }

    /**
     * Test halaman home menampilkan empty state ketika tidak ada produk
     */
    public function test_home_page_shows_empty_state_when_no_products(): void
    {
        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 0)
        );
    }

    /**
     * Test produk dengan is_available false ketika stock habis
     */
    public function test_product_is_not_available_when_out_of_stock(): void
    {
        $category = Category::factory()->create();
        Product::factory()
            ->for($category)
            ->outOfStock()
            ->create(['is_active' => true]);

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 1)
            ->where('products.data.0.is_available', false)
        );
    }
}
