<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk model Category
 * memverifikasi relationship, scope, dan auto-slug generation
 */
class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test pembuatan category dengan factory
     */
    public function test_can_create_category(): void
    {
        $category = Category::factory()->create([
            'name' => 'Makanan Test',
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Makanan Test',
        ]);
    }

    /**
     * Test auto-generate slug saat pembuatan category
     */
    public function test_auto_generates_slug_on_creation(): void
    {
        $category = Category::create([
            'name' => 'Makanan Berat Spesial',
            'description' => 'Test description',
        ]);

        $this->assertEquals('makanan-berat-spesial', $category->slug);
    }

    /**
     * Test relationship category has many products
     */
    public function test_category_has_many_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->products);
        $this->assertInstanceOf(Product::class, $category->products->first());
    }

    /**
     * Test scope active pada category
     */
    public function test_scope_active_filters_only_active_categories(): void
    {
        Category::factory()->count(3)->create(['is_active' => true]);
        Category::factory()->count(2)->create(['is_active' => false]);

        $activeCategories = Category::active()->get();

        $this->assertCount(3, $activeCategories);
    }

    /**
     * Test scope ordered pada category
     */
    public function test_scope_ordered_sorts_by_sort_order(): void
    {
        Category::factory()->create(['name' => 'Third', 'sort_order' => 3]);
        Category::factory()->create(['name' => 'First', 'sort_order' => 1]);
        Category::factory()->create(['name' => 'Second', 'sort_order' => 2]);

        $orderedCategories = Category::ordered()->get();

        $this->assertEquals('First', $orderedCategories->first()->name);
        $this->assertEquals('Third', $orderedCategories->last()->name);
    }
}
