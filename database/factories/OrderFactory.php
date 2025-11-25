<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk model Order
 * dengan data pesanan yang realistis
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomElement([25000, 35000, 50000, 75000, 100000, 150000]);
        $deliveryFee = fake()->randomElement([0, 5000, 10000, 15000]);

        return [
            'order_number' => Order::generateOrderNumber(),
            'user_id' => null,
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_address' => fake()->address(),
            'notes' => fake()->optional(0.3)->sentence(),
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $subtotal + $deliveryFee,
            'status' => 'pending',
            'confirmed_at' => null,
            'preparing_at' => null,
            'ready_at' => null,
            'delivered_at' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ];
    }

    /**
     * State untuk order dengan user terdaftar
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_phone' => $user->phone ?? fake()->phoneNumber(),
            'customer_address' => $user->address ?? fake()->address(),
        ]);
    }

    /**
     * State untuk order dengan status confirmed
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    /**
     * State untuk order dengan status preparing
     */
    public function preparing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'preparing',
            'confirmed_at' => now()->subMinutes(30),
            'preparing_at' => now(),
        ]);
    }

    /**
     * State untuk order dengan status ready
     */
    public function ready(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ready',
            'confirmed_at' => now()->subHour(),
            'preparing_at' => now()->subMinutes(30),
            'ready_at' => now(),
        ]);
    }

    /**
     * State untuk order dengan status delivered
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'confirmed_at' => now()->subHours(2),
            'preparing_at' => now()->subHours(1)->subMinutes(30),
            'ready_at' => now()->subHour(),
            'delivered_at' => now(),
        ]);
    }

    /**
     * State untuk order dengan status cancelled
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => fake()->sentence(),
        ]);
    }
}
