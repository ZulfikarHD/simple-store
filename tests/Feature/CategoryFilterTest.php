<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Feature test untuk filter kategori produk (CUST-002)
 * Memastikan kategori navigation menu dan filter produk berfungsi dengan benar
 *
 * @author Zulfikar Hidayatullah
 */
class CategoryFilterTest extends TestCase
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
     * Test halaman home menampilkan semua kategori aktif
     */
    public function test_home_page_displays_active_categories(): void
    {
        Category::factory()->count(3)->create(['is_active' => true]);
        Category::factory()->count(2)->create(['is_active' => false]);

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('categories.data', 3)
        );
    }

    /**
     * Test kategori tidak aktif tidak ditampilkan
     */
    public function test_inactive_categories_are_not_displayed(): void
    {
        Category::factory()->create(['is_active' => true, 'name' => 'Makanan']);
        Category::factory()->create(['is_active' => false, 'name' => 'Hidden']);

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('categories.data', 1)
            ->where('categories.data.0.name', 'Makanan')
        );
    }

    /**
     * Test data kategori memiliki struktur yang benar dari CategoryResource
     */
    public function test_category_data_has_correct_structure(): void
    {
        $category = Category::factory()->create([
            'name' => 'Makanan Berat',
            'description' => 'Kategori makanan berat',
            'is_active' => true,
        ]);
        Product::factory()->count(3)->for($category)->create(['is_active' => true]);

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('categories.data.0', fn (Assert $cat) => $cat
                ->has('id')
                ->has('name')
                ->has('slug')
                ->has('description')
                ->has('products_count')
            )
        );
    }

    /**
     * Test kategori menampilkan jumlah produk aktif yang benar
     */
    public function test_category_shows_correct_active_products_count(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->count(5)->for($category)->create(['is_active' => true]);
        Product::factory()->count(2)->for($category)->create(['is_active' => false]);

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('categories.data.0.products_count', 5)
        );
    }

    /**
     * Test filter produk berdasarkan kategori via query parameter
     */
    public function test_products_can_be_filtered_by_category(): void
    {
        $makanan = Category::factory()->create(['name' => 'Makanan', 'is_active' => true]);
        $minuman = Category::factory()->create(['name' => 'Minuman', 'is_active' => true]);

        Product::factory()->count(3)->for($makanan)->create(['is_active' => true]);
        Product::factory()->count(2)->for($minuman)->create(['is_active' => true]);

        $response = $this->get('/?category='.$makanan->id);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 3)
            ->where('selectedCategory', $makanan->id)
        );
    }

    /**
     * Test semua produk ditampilkan ketika tidak ada filter kategori
     */
    public function test_all_products_displayed_without_category_filter(): void
    {
        $makanan = Category::factory()->create(['name' => 'Makanan', 'is_active' => true]);
        $minuman = Category::factory()->create(['name' => 'Minuman', 'is_active' => true]);

        Product::factory()->count(3)->for($makanan)->create(['is_active' => true]);
        Product::factory()->count(2)->for($minuman)->create(['is_active' => true]);

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 5)
            ->where('selectedCategory', null)
        );
    }

    /**
     * Test selectedCategory bernilai null ketika tidak ada filter
     */
    public function test_selected_category_is_null_without_filter(): void
    {
        Category::factory()->create(['is_active' => true]);

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('selectedCategory', null)
        );
    }

    /**
     * Test selectedCategory berisi ID kategori yang dipilih
     */
    public function test_selected_category_contains_correct_id(): void
    {
        $category = Category::factory()->create(['is_active' => true]);

        $response = $this->get('/?category='.$category->id);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('selectedCategory', $category->id)
        );
    }

    /**
     * Test kategori diurutkan berdasarkan sort_order dan name
     */
    public function test_categories_are_ordered_correctly(): void
    {
        Category::factory()->create(['name' => 'Zebra', 'sort_order' => 2, 'is_active' => true]);
        Category::factory()->create(['name' => 'Apple', 'sort_order' => 1, 'is_active' => true]);
        Category::factory()->create(['name' => 'Banana', 'sort_order' => 1, 'is_active' => true]);

        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('categories.data.0.name', 'Apple')
            ->where('categories.data.1.name', 'Banana')
            ->where('categories.data.2.name', 'Zebra')
        );
    }

    /**
     * Test filter dengan kategori yang tidak ada menampilkan produk kosong
     */
    public function test_filter_with_nonexistent_category_shows_empty_products(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->count(3)->for($category)->create(['is_active' => true]);

        $response = $this->get('/?category=99999');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('products.data', 0)
            ->where('selectedCategory', 99999)
        );
    }

    /**
     * Test halaman home menampilkan empty state ketika tidak ada kategori
     */
    public function test_home_page_works_without_categories(): void
    {
        $response = $this->get('/');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('categories.data', 0)
            ->has('products.data', 0)
        );
    }
}
