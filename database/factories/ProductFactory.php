<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory untuk model Product
 * dengan data produk F&B Indonesia yang realistis
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $products = [
            'Nasi Goreng Spesial',
            'Mie Goreng Seafood',
            'Ayam Geprek',
            'Ayam Bakar Madu',
            'Sate Ayam',
            'Bakso Urat',
            'Soto Ayam',
            'Gado-gado',
            'Es Teh Manis',
            'Es Jeruk',
            'Kopi Susu',
            'Cappuccino',
            'Latte',
            'Jus Alpukat',
            'Jus Mangga',
            'Es Campur',
            'Es Cendol',
            'Roti Bakar',
            'Kentang Goreng',
            'Chicken Wings',
        ];

        $name = fake()->unique()->randomElement($products);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(2),
            'price' => fake()->randomElement([15000, 18000, 20000, 25000, 30000, 35000, 40000, 45000, 50000]),
            'image' => null,
            'stock' => fake()->numberBetween(10, 100),
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
        ];
    }

    /**
     * State untuk produk yang tidak aktif
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * State untuk produk yang habis (out of stock)
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * State untuk produk featured
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
