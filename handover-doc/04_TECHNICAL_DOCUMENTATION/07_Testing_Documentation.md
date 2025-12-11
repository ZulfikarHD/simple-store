# Dokumentasi Testing Simple Store

**Penulis**: Zulfikar Hidayatullah  
**Versi**: 1.0  
**Terakhir Diperbarui**: Desember 2025

---

## Overview

Dokumentasi strategi testing aplikasi Simple Store menggunakan PHPUnit sebagai framework utama, yaitu: mencakup struktur tests, cara menjalankan tests, dan best practices yang diterapkan untuk menjamin kualitas kode.

---

## Testing Stack

| Component | Technology | Version |
|-----------|------------|---------|
| **Framework** | PHPUnit | v11.5.45 |
| **Assertions** | Laravel Testing Helpers | Built-in |
| **Database** | SQLite In-Memory | - |
| **Factories** | Laravel Model Factories | Built-in |
| **Mocking** | PHPUnit/Mockery | Built-in |

---

## Test Structure

```
tests/
├── TestCase.php                    # Base test case class
│
├── Feature/                        # Feature/Integration tests
│   ├── ExampleTest.php
│   │
│   ├── Auth/                       # Authentication tests
│   │   ├── LoginTest.php
│   │   ├── RegisterTest.php
│   │   └── PasswordResetTest.php
│   │
│   ├── Admin/                      # Admin panel tests
│   │   ├── AdminMiddlewareTest.php
│   │   ├── DashboardTest.php
│   │   ├── CategoryControllerTest.php
│   │   ├── ProductControllerTest.php
│   │   ├── OrderControllerTest.php
│   │   ├── StoreSettingControllerTest.php
│   │   └── PasswordVerificationTest.php
│   │
│   ├── Models/                     # Model-specific tests
│   │   └── ...
│   │
│   ├── Settings/                   # User settings tests
│   │   └── ...
│   │
│   ├── DashboardTest.php           # Customer dashboard
│   ├── ProductCatalogTest.php      # Product listing
│   ├── ProductDetailTest.php       # Product detail page
│   ├── ProductSearchTest.php       # Search functionality
│   ├── CategoryFilterTest.php      # Category filtering
│   ├── CartManagementTest.php      # Shopping cart
│   └── CheckoutTest.php            # Checkout process
│
└── Unit/                           # Unit tests
    └── ExampleTest.php
```

---

## Running Tests

### All Tests

```bash
# Run all tests
php artisan test

# With coverage report
php artisan test --coverage

# With parallel execution
php artisan test --parallel
```

### Specific Tests

```bash
# Run single test file
php artisan test tests/Feature/ProductCatalogTest.php

# Run specific test method
php artisan test --filter=testCanViewProductCatalog

# Run tests in directory
php artisan test tests/Feature/Admin/

# Run with verbose output
php artisan test -v
```

### Test Groups

```bash
# Run only feature tests
php artisan test tests/Feature

# Run only unit tests
php artisan test tests/Unit

# Run admin tests only
php artisan test tests/Feature/Admin
```

### PHPUnit Options

```bash
# Using vendor PHPUnit directly
./vendor/bin/phpunit

# Stop on failure
./vendor/bin/phpunit --stop-on-failure

# With testdox output
./vendor/bin/phpunit --testdox
```

---

## Test Categories

### 1. Feature Tests (Integration)

Feature tests menguji alur lengkap dari HTTP request hingga response.

#### Product Catalog Tests

```php
// tests/Feature/ProductCatalogTest.php

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_product_catalog(): void
    {
        // Arrange
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        // Act
        $response = $this->get('/');

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('products', 1)
            ->has('categories', 1)
        );
    }

    public function test_can_filter_products_by_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $otherProduct = Product::factory()->create();

        $response = $this->get('/?category=' . $category->id);

        $response->assertInertia(fn ($page) => $page
            ->has('products', 1)
            ->where('selectedCategory', $category->id)
        );
    }

    public function test_can_search_products(): void
    {
        $product = Product::factory()->create(['name' => 'Nasi Goreng Spesial']);
        $other = Product::factory()->create(['name' => 'Es Teh']);

        $response = $this->get('/?search=nasi');

        $response->assertInertia(fn ($page) => $page
            ->has('products', 1)
            ->where('searchQuery', 'nasi')
        );
    }
}
```

#### Cart Management Tests

```php
// tests/Feature/CartManagementTest.php

class CartManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_can_update_cart_item_quantity(): void
    {
        $product = Product::factory()->create();
        $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cartItem = CartItem::first();

        $response = $this->patch("/cart/{$cartItem->id}", [
            'quantity' => 5,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    public function test_can_remove_item_from_cart(): void
    {
        $product = Product::factory()->create();
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);
        
        $cartItem = CartItem::first();

        $response = $this->delete("/cart/{$cartItem->id}");

        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }
}
```

#### Checkout Tests

```php
// tests/Feature/CheckoutTest.php

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_checkout_with_valid_data(): void
    {
        $product = Product::factory()->create(['price' => 25000, 'stock' => 10]);
        
        // Add to cart first
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 2]);

        $response = $this->post('/checkout', [
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Test No. 123',
            'notes' => 'Tidak pedas',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'status' => 'pending',
        ]);
    }

    public function test_cannot_checkout_with_empty_cart(): void
    {
        $response = $this->post('/checkout', [
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_checkout_requires_customer_name_and_phone(): void
    {
        $product = Product::factory()->create();
        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 1]);

        $response = $this->post('/checkout', []);

        $response->assertSessionHasErrors(['customer_name', 'customer_phone']);
    }
}
```

### 2. Admin Tests

#### Admin Middleware Tests

```php
// tests/Feature/Admin/AdminMiddlewareTest.php

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_customer_cannot_access_admin(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
    }
}
```

#### Product Management Tests

```php
// tests/Feature/Admin/ProductControllerTest.php

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_products_list(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/products');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Products/Index')
            ->has('products.data', 5)
        );
    }

    public function test_admin_can_create_product(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post('/admin/products', [
                'category_id' => $category->id,
                'name' => 'New Product',
                'description' => 'Test description',
                'price' => 50000,
                'stock' => 100,
                'is_active' => true,
                'is_featured' => false,
            ]);

        $response->assertRedirect('/admin/products');
        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price' => 50000,
        ]);
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put("/admin/products/{$product->id}", [
                'category_id' => $product->category_id,
                'name' => 'Updated Name',
                'description' => $product->description,
                'price' => 75000,
                'stock' => 50,
                'is_active' => true,
                'is_featured' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 75000,
        ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/products/{$product->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_product_creation_requires_valid_data(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/products', [
                'name' => '', // Invalid: empty
                'price' => -100, // Invalid: negative
            ]);

        $response->assertSessionHasErrors(['name', 'price', 'category_id']);
    }
}
```

#### Order Management Tests

```php
// tests/Feature/Admin/OrderControllerTest.php

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_orders(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/orders');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Orders/Index')
            ->has('orders.data', 3)
        );
    }

    public function test_admin_can_update_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->id}/status", [
                'status' => 'confirmed',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);
        $this->assertNotNull($order->fresh()->confirmed_at);
    }

    public function test_admin_can_cancel_order_with_reason(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/orders/{$order->id}/status", [
                'status' => 'cancelled',
                'cancellation_reason' => 'Stok habis',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
            'cancellation_reason' => 'Stok habis',
        ]);
    }

    public function test_can_filter_orders_by_status(): void
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'confirmed']);
        Order::factory()->create(['status' => 'delivered']);

        $response = $this->actingAs($this->admin)
            ->get('/admin/orders?status=pending');

        $response->assertInertia(fn ($page) => $page
            ->has('orders.data', 1)
        );
    }
}
```

### 3. Unit Tests

Unit tests fokus pada testing individual methods/functions tanpa HTTP layer.

```php
// tests/Unit/Services/CartServiceTest.php

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);
    }

    public function test_can_calculate_cart_subtotal(): void
    {
        $product1 = Product::factory()->create(['price' => 10000]);
        $product2 = Product::factory()->create(['price' => 15000]);

        $this->cartService->addItem($product1->id, 2);
        $this->cartService->addItem($product2->id, 1);

        $subtotal = $this->cartService->getSubtotal();

        $this->assertEquals(35000, $subtotal); // (10000 * 2) + (15000 * 1)
    }

    public function test_adding_same_product_increases_quantity(): void
    {
        $product = Product::factory()->create();

        $this->cartService->addItem($product->id, 1);
        $this->cartService->addItem($product->id, 2);

        $cart = $this->cartService->getCart();
        $item = $cart->items->first();

        $this->assertEquals(3, $item->quantity);
    }
}
```

---

## Test Helpers & Traits

### RefreshDatabase

Setiap test menggunakan `RefreshDatabase` trait untuk memastikan database bersih:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    
    // Tests will have fresh database
}
```

### Custom Test Helpers

```php
// tests/TestCase.php

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Create admin user untuk testing
     */
    protected function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /**
     * Create customer user untuk testing
     */
    protected function createCustomer(): User
    {
        return User::factory()->create(['role' => 'customer']);
    }

    /**
     * Setup cart dengan products
     */
    protected function setupCartWithProducts(int $count = 3): Cart
    {
        $products = Product::factory()->count($count)->create();
        $cartService = app(CartService::class);

        foreach ($products as $product) {
            $cartService->addItem($product->id, 1);
        }

        return $cartService->getCart();
    }
}
```

---

## Factories

### User Factory

```php
// database/factories/UserFactory.php

public function definition(): array
{
    return [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'email_verified_at' => now(),
        'password' => static::$password ??= Hash::make('password'),
        'remember_token' => Str::random(10),
        'role' => 'customer',
        'phone' => fake()->phoneNumber(),
        'address' => fake()->address(),
    ];
}

public function admin(): static
{
    return $this->state(['role' => 'admin']);
}

public function customer(): static
{
    return $this->state(['role' => 'customer']);
}

public function unverified(): static
{
    return $this->state(['email_verified_at' => null]);
}
```

### Product Factory

```php
// database/factories/ProductFactory.php

public function definition(): array
{
    return [
        'category_id' => Category::factory(),
        'name' => fake()->words(3, true),
        'slug' => fn (array $attributes) => Str::slug($attributes['name']),
        'description' => fake()->paragraph(),
        'price' => fake()->numberBetween(10000, 500000),
        'stock' => fake()->numberBetween(0, 100),
        'image' => null,
        'is_active' => true,
        'is_featured' => false,
    ];
}

public function inactive(): static
{
    return $this->state(['is_active' => false]);
}

public function featured(): static
{
    return $this->state(['is_featured' => true]);
}

public function outOfStock(): static
{
    return $this->state(['stock' => 0]);
}
```

### Order Factory

```php
// database/factories/OrderFactory.php

public function definition(): array
{
    return [
        'order_number' => Order::generateOrderNumber(),
        'user_id' => User::factory(),
        'customer_name' => fake()->name(),
        'customer_phone' => fake()->phoneNumber(),
        'customer_address' => fake()->address(),
        'notes' => fake()->optional()->sentence(),
        'subtotal' => fake()->numberBetween(50000, 500000),
        'delivery_fee' => 10000,
        'total' => fn (array $attributes) => $attributes['subtotal'] + $attributes['delivery_fee'],
        'status' => 'pending',
    ];
}

public function confirmed(): static
{
    return $this->state([
        'status' => 'confirmed',
        'confirmed_at' => now(),
    ]);
}

public function delivered(): static
{
    return $this->state([
        'status' => 'delivered',
        'delivered_at' => now(),
    ]);
}

public function cancelled(): static
{
    return $this->state([
        'status' => 'cancelled',
        'cancelled_at' => now(),
        'cancellation_reason' => 'Test cancellation',
    ]);
}

public function withItems(int $count = 3): static
{
    return $this->has(
        OrderItem::factory()->count($count),
        'items'
    );
}
```

---

## Testing Best Practices

### 1. Naming Convention

```php
// Format: test_<action>_<condition>_<expected_result>
public function test_can_view_product_catalog(): void {}
public function test_admin_can_create_product(): void {}
public function test_guest_cannot_access_admin_panel(): void {}
public function test_checkout_fails_with_empty_cart(): void {}
```

### 2. Arrange-Act-Assert Pattern

```php
public function test_can_add_product_to_cart(): void
{
    // Arrange: Setup test data
    $product = Product::factory()->create(['stock' => 10]);

    // Act: Execute the action
    $response = $this->post('/cart', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    // Assert: Verify results
    $response->assertRedirect();
    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);
}
```

### 3. Test One Thing at a Time

```php
// ✅ Good: Focused test
public function test_product_name_is_required(): void
{
    $response = $this->actingAs($this->admin)
        ->post('/admin/products', ['name' => '']);

    $response->assertSessionHasErrors('name');
}

// ❌ Bad: Testing multiple things
public function test_product_validation(): void
{
    // Tests too many things at once
}
```

### 4. Use Factories, Not Hardcoded Data

```php
// ✅ Good
$product = Product::factory()->create(['price' => 50000]);

// ❌ Bad
$product = new Product(['id' => 1, 'name' => 'Test', ...]);
```

### 5. Test Edge Cases

```php
public function test_cannot_add_out_of_stock_product(): void {}
public function test_cannot_checkout_with_empty_cart(): void {}
public function test_handles_concurrent_stock_updates(): void {}
```

---

## Coverage Report

### Generate Coverage

```bash
# HTML report
php artisan test --coverage-html coverage/

# Text summary
php artisan test --coverage

# Minimum coverage threshold
php artisan test --coverage --min=80
```

### Coverage Targets

| Area | Target | Priority |
|------|--------|----------|
| Controllers | 80%+ | High |
| Services | 90%+ | High |
| Models | 70%+ | Medium |
| Middleware | 90%+ | High |
| Requests | 80%+ | Medium |

---

## Continuous Integration

### GitHub Actions Example

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: sqlite3, pdo_sqlite
          coverage: xdebug

      - name: Install Dependencies
        run: composer install --no-progress --prefer-dist

      - name: Run Tests
        run: php artisan test --coverage --min=80

      - name: Upload Coverage
        uses: codecov/codecov-action@v3
```

---

## Related Documentation

- [01_System_Architecture.md](01_System_Architecture.md) - Arsitektur sistem
- [04_File_Structure.md](04_File_Structure.md) - Struktur file
- [06_Deployment_Guide.md](06_Deployment_Guide.md) - Panduan deployment

