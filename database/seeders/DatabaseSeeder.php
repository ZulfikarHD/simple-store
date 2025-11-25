<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * DatabaseSeeder untuk mengisi data awal aplikasi F&B
 * termasuk admin user, categories, products, dan sample orders
 *
 * @author Zulfikar Hidayatullah
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedUsers();
        $this->seedCategories();
        $this->seedProducts();
        $this->seedOrders();
    }

    /**
     * Membuat user admin dan beberapa customer untuk testing
     */
    private function seedUsers(): void
    {
        // Admin user
        User::factory()->admin()->create([
            'name' => 'Admin Store',
            'email' => 'admin@store.test',
        ]);

        // Customer users
        User::factory()->customer()->create([
            'name' => 'Customer Demo',
            'email' => 'customer@store.test',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 123, Jakarta',
        ]);

        // Additional customers
        User::factory()->customer()->count(5)->create();
    }

    /**
     * Membuat kategori produk F&B
     */
    private function seedCategories(): void
    {
        $categories = [
            [
                'name' => 'Makanan Berat',
                'description' => 'Menu utama seperti nasi, mie, dan lauk pauk lengkap',
                'sort_order' => 1,
            ],
            [
                'name' => 'Makanan Ringan',
                'description' => 'Snack dan camilan ringan untuk menemani waktu santai',
                'sort_order' => 2,
            ],
            [
                'name' => 'Minuman Dingin',
                'description' => 'Berbagai minuman segar dan dingin',
                'sort_order' => 3,
            ],
            [
                'name' => 'Minuman Panas',
                'description' => 'Kopi, teh, dan minuman hangat lainnya',
                'sort_order' => 4,
            ],
            [
                'name' => 'Dessert',
                'description' => 'Hidangan penutup manis yang menggugah selera',
                'sort_order' => 5,
            ],
            [
                'name' => 'Paket Hemat',
                'description' => 'Paket kombinasi makanan dan minuman dengan harga spesial',
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                ...$category,
                'slug' => Str::slug($category['name']),
                'is_active' => true,
            ]);
        }
    }

    /**
     * Membuat produk untuk setiap kategori
     */
    private function seedProducts(): void
    {
        $products = [
            'Makanan Berat' => [
                ['name' => 'Nasi Goreng Spesial', 'price' => 25000, 'description' => 'Nasi goreng dengan telur, ayam, dan sayuran segar'],
                ['name' => 'Mie Goreng Seafood', 'price' => 28000, 'description' => 'Mie goreng dengan udang, cumi, dan bakso ikan'],
                ['name' => 'Ayam Geprek', 'price' => 22000, 'description' => 'Ayam crispy dengan sambal geprek level pilihan'],
                ['name' => 'Ayam Bakar Madu', 'price' => 30000, 'description' => 'Ayam bakar dengan bumbu madu special'],
                ['name' => 'Nasi Campur', 'price' => 27000, 'description' => 'Nasi dengan berbagai lauk pauk pilihan'],
                ['name' => 'Soto Ayam', 'price' => 20000, 'description' => 'Soto ayam dengan kuah bening dan suwiran ayam'],
            ],
            'Makanan Ringan' => [
                ['name' => 'Kentang Goreng', 'price' => 15000, 'description' => 'Kentang goreng crispy dengan saus pilihan'],
                ['name' => 'Chicken Wings', 'price' => 22000, 'description' => 'Sayap ayam crispy dengan berbagai pilihan saus'],
                ['name' => 'Roti Bakar', 'price' => 12000, 'description' => 'Roti bakar dengan selai coklat atau keju'],
                ['name' => 'Pisang Goreng', 'price' => 10000, 'description' => 'Pisang goreng crispy dengan topping pilihan'],
            ],
            'Minuman Dingin' => [
                ['name' => 'Es Teh Manis', 'price' => 5000, 'description' => 'Teh manis dingin yang menyegarkan'],
                ['name' => 'Es Jeruk', 'price' => 8000, 'description' => 'Jus jeruk segar dengan es'],
                ['name' => 'Es Campur', 'price' => 15000, 'description' => 'Campuran buah-buahan dengan es serut dan sirup'],
                ['name' => 'Es Cendol', 'price' => 12000, 'description' => 'Cendol dengan santan dan gula merah'],
                ['name' => 'Jus Alpukat', 'price' => 15000, 'description' => 'Jus alpukat creamy dengan susu'],
                ['name' => 'Jus Mangga', 'price' => 12000, 'description' => 'Jus mangga segar'],
            ],
            'Minuman Panas' => [
                ['name' => 'Kopi Susu', 'price' => 15000, 'description' => 'Kopi dengan susu creamy'],
                ['name' => 'Cappuccino', 'price' => 18000, 'description' => 'Espresso dengan susu foam'],
                ['name' => 'Latte', 'price' => 18000, 'description' => 'Espresso dengan steamed milk'],
                ['name' => 'Teh Hangat', 'price' => 5000, 'description' => 'Teh hangat dengan aroma melati'],
                ['name' => 'Coklat Panas', 'price' => 15000, 'description' => 'Coklat hangat yang nikmat'],
            ],
            'Dessert' => [
                ['name' => 'Brownies', 'price' => 18000, 'description' => 'Brownies coklat lembut'],
                ['name' => 'Pudding Coklat', 'price' => 12000, 'description' => 'Pudding coklat dengan vla'],
                ['name' => 'Es Krim', 'price' => 15000, 'description' => 'Es krim dengan berbagai pilihan rasa'],
            ],
            'Paket Hemat' => [
                ['name' => 'Paket Nasi Goreng', 'price' => 28000, 'description' => 'Nasi goreng + Es teh', 'is_featured' => true],
                ['name' => 'Paket Ayam Geprek', 'price' => 25000, 'description' => 'Ayam geprek + Nasi + Es teh', 'is_featured' => true],
                ['name' => 'Paket Hemat Mie', 'price' => 30000, 'description' => 'Mie goreng + Kentang + Es jeruk', 'is_featured' => true],
            ],
        ];

        foreach ($products as $categoryName => $items) {
            $category = Category::where('name', $categoryName)->first();

            if (! $category) {
                continue;
            }

            foreach ($items as $item) {
                Product::create([
                    'category_id' => $category->id,
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']),
                    'description' => $item['description'],
                    'price' => $item['price'],
                    'stock' => rand(20, 100),
                    'is_active' => true,
                    'is_featured' => $item['is_featured'] ?? false,
                ]);
            }
        }
    }

    /**
     * Membuat sample orders untuk testing
     */
    private function seedOrders(): void
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            return;
        }

        // Order dengan status berbeda-beda
        $statuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'];

        foreach ($statuses as $status) {
            $factory = Order::factory();

            if ($status !== 'pending') {
                $factory = $factory->{$status}();
            }

            $order = $factory->create();

            // Tambahkan 2-4 items ke order
            $orderProducts = $products->random(rand(2, 4));
            $subtotal = 0;

            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                $itemSubtotal = $product->price * $quantity;
                $subtotal += $itemSubtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal,
                ]);
            }

            // Update order totals
            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal + $order->delivery_fee,
            ]);
        }
    }
}
