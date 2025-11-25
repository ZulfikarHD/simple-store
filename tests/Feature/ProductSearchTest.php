<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Feature test untuk pencarian produk (CUST-003)
 * Memastikan fitur search berfungsi dengan benar termasuk
 * pencarian berdasarkan nama, deskripsi, dan kombinasi dengan filter kategori
 *
 * @author Zulfikar Hidayatullah
 */
class ProductSearchTest extends TestCase
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
     * Test pencarian produk berdasarkan nama
     */
    public function test_can_search_products_by_name(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->for($category)->create(['name' => 'Nasi Goreng Spesial', 'is_active' => true]);
        Product::factory()->for($category)->create(['name' => 'Mie Goreng', 'is_active' => true]);
        Product::factory()->for($category)->create(['name' => 'Es Teh Manis', 'is_active' => true]);

        $response = $this->get('/?search=nasi');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Nasi Goreng Spesial')
            ->where('searchQuery', 'nasi')
        );
    }

    /**
     * Test pencarian produk berdasarkan deskripsi
     */
    public function test_can_search_products_by_description(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->for($category)->create([
            'name' => 'Produk A',
            'description' => 'Makanan pedas dengan bumbu rempah',
            'is_active' => true,
        ]);
        Product::factory()->for($category)->create([
            'name' => 'Produk B',
            'description' => 'Minuman segar dingin',
            'is_active' => true,
        ]);

        $response = $this->get('/?search=pedas');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Produk A')
        );
    }

    /**
     * Test pencarian case-insensitive (tidak sensitif huruf besar/kecil)
     */
    public function test_search_is_case_insensitive(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->for($category)->create(['name' => 'NASI GORENG', 'is_active' => true]);
        Product::factory()->for($category)->create(['name' => 'Mie Ayam', 'is_active' => true]);

        $response = $this->get('/?search=nasi');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'NASI GORENG')
        );
    }

    /**
     * Test pencarian dengan kombinasi filter kategori
     */
    public function test_search_combined_with_category_filter(): void
    {
        $makanan = Category::factory()->create(['name' => 'Makanan', 'is_active' => true]);
        $minuman = Category::factory()->create(['name' => 'Minuman', 'is_active' => true]);

        Product::factory()->for($makanan)->create(['name' => 'Nasi Goreng', 'is_active' => true]);
        Product::factory()->for($makanan)->create(['name' => 'Mie Goreng', 'is_active' => true]);
        Product::factory()->for($minuman)->create(['name' => 'Es Goreng', 'is_active' => true]);

        $response = $this->get('/?search=goreng&category='.$makanan->id);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 2)
            ->where('searchQuery', 'goreng')
            ->where('selectedCategory', $makanan->id)
        );
    }

    /**
     * Test pencarian kosong mengembalikan semua produk
     */
    public function test_empty_search_returns_all_products(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->count(5)->for($category)->create(['is_active' => true]);

        $response = $this->get('/?search=');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 5)
        );
    }

    /**
     * Test pencarian tanpa hasil mengembalikan array kosong
     */
    public function test_search_with_no_results_returns_empty_array(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->for($category)->create(['name' => 'Nasi Goreng', 'is_active' => true]);

        $response = $this->get('/?search=xyz123notfound');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 0)
            ->where('searchQuery', 'xyz123notfound')
        );
    }

    /**
     * Test searchQuery prop dikirim ke frontend
     */
    public function test_search_query_is_passed_to_frontend(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->for($category)->create(['is_active' => true]);

        $response = $this->get('/?search=test');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('searchQuery', 'test')
        );
    }

    /**
     * Test searchQuery null ketika tidak ada pencarian
     */
    public function test_search_query_is_null_without_search(): void
    {
        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('searchQuery', null)
        );
    }

    /**
     * Test pencarian hanya mengembalikan produk aktif
     */
    public function test_search_only_returns_active_products(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->for($category)->create(['name' => 'Nasi Goreng Aktif', 'is_active' => true]);
        Product::factory()->for($category)->create(['name' => 'Nasi Goreng Nonaktif', 'is_active' => false]);

        $response = $this->get('/?search=nasi');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Nasi Goreng Aktif')
        );
    }

    /**
     * Test pencarian dengan kata kunci parsial
     */
    public function test_search_with_partial_keyword(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->for($category)->create(['name' => 'Ayam Bakar', 'is_active' => true]);
        Product::factory()->for($category)->create(['name' => 'Ikan Bakar', 'is_active' => true]);
        Product::factory()->for($category)->create(['name' => 'Nasi Goreng', 'is_active' => true]);

        $response = $this->get('/?search=bakar');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 2)
        );
    }

    /**
     * Test pencarian di nama dan deskripsi sekaligus
     */
    public function test_search_matches_both_name_and_description(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->for($category)->create([
            'name' => 'Produk Spesial',
            'description' => 'Deskripsi biasa',
            'is_active' => true,
        ]);
        Product::factory()->for($category)->create([
            'name' => 'Produk Biasa',
            'description' => 'Ini adalah menu spesial',
            'is_active' => true,
        ]);

        $response = $this->get('/?search=spesial');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 2)
        );
    }
}
