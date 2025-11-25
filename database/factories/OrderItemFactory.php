<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk model OrderItem
 * dengan data item pesanan yang realistis
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomElement([15000, 20000, 25000, 30000, 35000, 40000]);
        $quantity = fake()->numberBetween(1, 3);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->randomElement([
                'Nasi Goreng',
                'Mie Goreng',
                'Ayam Geprek',
                'Es Teh',
                'Kopi Susu',
            ]),
            'product_price' => $price,
            'quantity' => $quantity,
            'subtotal' => $price * $quantity,
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }

    /**
     * State untuk menggunakan data dari produk yang sudah ada
     */
    public function forProduct(Product $product): static
    {
        $quantity = fake()->numberBetween(1, 3);

        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => $quantity,
            'subtotal' => $product->price * $quantity,
        ]);
    }
}
