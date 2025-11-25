<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk model Product
 * memverifikasi relationship, scope, dan helper methods
 */
class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test pembuatan product dengan factory
     */
    public function test_can_create_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Nasi Goreng Test',
            'price' => 25000,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Nasi Goreng Test',
            'price' => 25000,
        ]);
    }

    /**
     * Test auto-generate slug saat pembuatan product
     */
    public function test_auto_generates_slug_on_creation(): void
    {
        $category = Category::factory()->create();
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Ayam Geprek Spesial',
            'price' => 20000,
        ]);

        $this->assertEquals('ayam-geprek-spesial', $product->slug);
    }

    /**
     * Test relationship product belongs to category
     */
    public function test_product_belongs_to_category(): void
    {
        $category = Category::factory()->create(['name' => 'Makanan']);
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals('Makanan', $product->category->name);
    }

    /**
     * Test scope active pada product
     */
    public function test_scope_active_filters_only_active_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id, 'is_active' => true]);
        Product::factory()->count(2)->create(['category_id' => $category->id, 'is_active' => false]);

        $activeProducts = Product::active()->get();

        $this->assertCount(3, $activeProducts);
    }

    /**
     * Test scope inStock pada product
     */
    public function test_scope_in_stock_filters_products_with_stock(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id, 'stock' => 10]);
        Product::factory()->count(2)->create(['category_id' => $category->id, 'stock' => 0]);

        $inStockProducts = Product::inStock()->get();

        $this->assertCount(3, $inStockProducts);
    }

    /**
     * Test method isAvailable
     */
    public function test_is_available_returns_correct_value(): void
    {
        $category = Category::factory()->create();

        $availableProduct = Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
            'stock' => 10,
        ]);

        $outOfStockProduct = Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
            'stock' => 0,
        ]);

        $inactiveProduct = Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => false,
            'stock' => 10,
        ]);

        $this->assertTrue($availableProduct->isAvailable());
        $this->assertFalse($outOfStockProduct->isAvailable());
        $this->assertFalse($inactiveProduct->isAvailable());
    }

    /**
     * Test formatted price attribute
     */
    public function test_formatted_price_attribute(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 25000,
        ]);

        $this->assertEquals('Rp 25.000', $product->formatted_price);
    }
}
