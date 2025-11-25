# Sprint 2 - CUST-005: Add Products to Cart

**Author:** Zulfikar Hidayatullah  
**Sprint:** Sprint 2 (Week 2-3)  
**Story Points:** 5  
**Status:** ✅ Completed  
**Date:** 2024-11-25

---

## Overview

CUST-005 merupakan implementasi pertama dari Epic 2 Sprint 2: Shopping & Checkout, yaitu: menambahkan fitur add to cart yang memungkinkan customer untuk menambahkan produk ke keranjang belanja dengan session-based tracking untuk guest users, cart counter di header, dan success notification menggunakan Inertia.js.

## User Story

```
As a customer, I want to add products to cart
so I can purchase multiple items at once
```

## Acceptance Criteria

- ✅ Add to cart functionality dari product card dan detail page
- ✅ Cart counter in header yang menampilkan total items
- ✅ Success notification setelah add to cart
- ✅ Session-based cart untuk guest users
- ✅ Validation untuk product_id dan quantity

---

## Technical Implementation

### Backend Architecture

#### 1. Database Schema - Cart Tables

File: `database/migrations/2025_11_25_083034_create_carts_table.php`

Migration untuk membuat tabel carts yang menyimpan session-based cart data, yaitu:

```php
Schema::create('carts', function (Blueprint $table) {
    $table->id();
    $table->string('session_id')->unique()->index();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->timestamps();
});
```

**Key Features:**
- `session_id` unique untuk tracking guest carts
- `user_id` nullable untuk future authenticated user carts
- Index pada `session_id` untuk query optimization

File: `database/migrations/2025_11_25_083041_create_cart_items_table.php`

Migration untuk membuat tabel cart_items yang menyimpan detail items dalam cart, yaitu:

```php
Schema::create('cart_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->unsignedInteger('quantity')->default(1);
    $table->timestamps();

    // Ensure unique product per cart
    $table->unique(['cart_id', 'product_id']);
});
```

**Key Features:**
- Foreign key ke `carts` dengan cascade delete
- Foreign key ke `products` dengan cascade delete
- Unique constraint untuk mencegah duplicate product dalam cart
- Quantity sebagai unsigned integer dengan default 1

#### 2. Cart Model

File: `app/Models/Cart.php`

Model Cart untuk mengelola data keranjang belanja dengan relationships dan calculated attributes, yaitu:

```php
/**
 * Model Cart untuk menyimpan data keranjang belanja customer
 * dengan session-based tracking untuk guest users
 * serta relasi ke User dan CartItem
 */
class Cart extends Model
{
    protected $fillable = ['session_id', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
    }
}
```

**Key Features:**
- Session-based tracking dengan `session_id`
- Calculated `total_items` attribute
- Calculated `subtotal` attribute
- Relationship ke CartItem dan User

#### 3. CartItem Model

File: `app/Models/CartItem.php`

Model CartItem untuk menyimpan detail item dalam cart dengan relationships, yaitu:

```php
class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->product->price * $this->quantity;
    }
}
```

#### 4. CartService - Business Logic

File: `app/Services/CartService.php`

Service class untuk mengelola business logic cart operations, yaitu:

```php
class CartService
{
    private const SESSION_KEY = 'cart_id';

    /**
     * Mendapatkan cart untuk session saat ini
     * dengan eager loading items dan products
     */
    public function getCart(): Cart
    {
        if (! Session::isStarted()) {
            Session::start();
        }

        $sessionId = Session::getId();
        $cart = Cart::query()
            ->where('session_id', $sessionId)
            ->with(['items.product'])
            ->first();

        if (! $cart) {
            $cart = Cart::create(['session_id' => $sessionId]);
            $cart->load(['items.product']);
        }

        return $cart;
    }

    /**
     * Menambahkan produk ke cart
     * Jika produk sudah ada, quantity akan ditambahkan
     */
    public function addItem(int $productId, int $quantity = 1): CartItem
    {
        $cart = $this->getCart();
        $product = Product::findOrFail($productId);

        $existingItem = $cart->items()->where('product_id', $productId)->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
            $existingItem->refresh();
            $existingItem->load('product');
            return $existingItem;
        }

        $item = $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
        ]);
        $item->load('product');

        return $item;
    }
}
```

**Design Decisions:**
- Session-based untuk support guest users
- Auto-create cart jika belum exist
- Increment quantity jika product sudah ada
- Eager loading untuk performance

#### 5. CartController

File: `app/Http/Controllers/CartController.php`

Controller untuk handle HTTP requests dengan delegation ke CartService:

```php
class CartController extends Controller
{
    public function __construct(public CartService $cartService) {}

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->cartService->addItem(
            $validated['product_id'],
            $validated['quantity']
        );

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }
}
```

#### 6. Form Request Validation

File: `app/Http/Requests/AddToCartRequest.php`

Validation untuk add to cart dengan custom messages dalam Bahasa Indonesia:

```php
public function rules(): array
{
    return [
        'product_id' => ['required', 'integer', 'exists:products,id'],
        'quantity' => ['required', 'integer', 'min:1', 'max:99'],
    ];
}

public function messages(): array
{
    return [
        'product_id.required' => 'Produk harus dipilih.',
        'product_id.exists' => 'Produk tidak ditemukan.',
        'quantity.min' => 'Jumlah minimal adalah 1.',
        'quantity.max' => 'Jumlah maksimal adalah 99.',
    ];
}
```

#### 7. Routes Configuration

File: `routes/web.php`

Routes untuk cart operations dengan RESTful pattern:

```php
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
```

#### 8. Inertia Middleware - Shared Data

File: `app/Http/Middleware/HandleInertiaRequests.php`

Share cart data globally ke semua Inertia pages dengan lazy loading:

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'cart' => fn () => [
            'total_items' => app(CartService::class)->getTotalItems(),
        ],
    ];
}
```

### Frontend Architecture

#### 1. CartCounter Component

File: `resources/js/components/store/CartCounter.vue`

Component untuk menampilkan cart badge counter di header dengan Wayfinder integration:

```vue
<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ShoppingCart } from 'lucide-vue-next'
import { show } from '@/actions/App/Http/Controllers/CartController'

interface Props {
    count: number
}

const props = withDefaults(defineProps<Props>(), { count: 0 })

const cartUrl = computed(() => show.url())

const displayCount = computed(() => {
    if (props.count > 99) return '99+'
    return props.count.toString()
})
</script>

<template>
    <Link :href="cartUrl" class="relative">
        <ShoppingCart class="h-5 w-5" />
        <span v-if="count > 0" class="absolute -right-1 -top-1 badge">
            {{ displayCount }}
        </span>
    </Link>
</template>
```

**Key Features:**
- Display count dengan format 99+ untuk jumlah besar
- Link ke cart page menggunakan Wayfinder
- Badge styling dengan Tailwind CSS
- Conditional rendering untuk empty cart

#### 2. ProductCard Integration

File: `resources/js/components/store/ProductCard.vue`

Update ProductCard dengan add to cart button dan loading states:

```vue
<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Plus, Loader2, Check } from 'lucide-vue-next'
import { store } from '@/actions/App/Http/Controllers/CartController'

const isAdding = ref(false)
const showSuccess = ref(false)

function handleAddToCart() {
    if (!props.product.is_available || isAdding.value) return

    isAdding.value = true
    router.post(
        store.url(),
        {
            product_id: props.product.id,
            quantity: 1,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showSuccess.value = true
                setTimeout(() => {
                    showSuccess.value = false
                }, 2000)
            },
            onFinish: () => {
                isAdding.value = false
            },
        }
    )
}
</script>

<template>
    <Button @click.prevent="handleAddToCart" :disabled="!product.is_available || isAdding">
        <Loader2 v-if="isAdding" class="h-5 w-5 animate-spin" />
        <Check v-else-if="showSuccess" class="h-5 w-5" />
        <Plus v-else class="h-5 w-5" />
    </Button>
</template>
```

**Key Features:**
- Loading state dengan spinner icon
- Success state dengan checkmark
- Disabled state untuk unavailable products
- PreserveScroll untuk better UX
- 2 second success feedback

#### 3. ProductDetail Integration

File: `resources/js/pages/ProductDetail.vue`

Add to cart dengan quantity selector di product detail page:

```vue
<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { store as storeCart } from '@/actions/App/Http/Controllers/CartController'

const quantity = ref(1)
const isAdding = ref(false)

function handleAddToCart() {
    if (!product.value.is_available || isAdding.value) return

    isAdding.value = true
    router.post(
        storeCart.url(),
        {
            product_id: product.value.id,
            quantity: quantity.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                quantity.value = 1
            },
            onFinish: () => {
                isAdding.value = false
            },
        }
    )
}
</script>

<template>
    <div class="quantity-selector">
        <Button @click="quantity--" :disabled="quantity <= 1">-</Button>
        <span>{{ quantity }}</span>
        <Button @click="quantity++" :disabled="quantity >= 99">+</Button>
    </div>
    <Button @click="handleAddToCart">Tambah ke Keranjang</Button>
</template>
```

### Testing

File: `tests/Feature/CartManagementTest.php`

Comprehensive tests untuk add to cart functionality (12 tests total):

```php
public function test_can_add_product_to_cart(): void
{
    $product = Product::factory()->create(['is_active' => true]);

    $response = $this->post('/cart', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);
}

public function test_adding_existing_product_updates_quantity(): void
{
    $cartService = app(CartService::class);
    $product = Product::factory()->create();

    $cartService->addItem($product->id, 2);
    $cartService->addItem($product->id, 3);

    $this->assertDatabaseCount('cart_items', 1);
    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'quantity' => 5,
    ]);
}
```

**Test Coverage:**
- ✅ Add product to cart successfully
- ✅ Add existing product updates quantity
- ✅ Validation errors untuk invalid product
- ✅ Validation errors untuk invalid quantity
- ✅ Session-based cart creation
- ✅ Cart counter accuracy

---

## Wayfinder Integration

Generate type-safe routes dengan Wayfinder:

```bash
php artisan wayfinder:generate
```

Usage di frontend:

```typescript
import { store } from '@/actions/App/Http/Controllers/CartController'

// Get URL
const cartStoreUrl = store.url()

// Use with Inertia
router.post(store.url(), { product_id: 1, quantity: 2 })
```

---

## Performance Considerations

1. **Eager Loading**: Load items.product untuk avoid N+1 queries
2. **Session Indexing**: Index pada `session_id` untuk fast lookup
3. **Lazy Loading**: Cart data di Inertia shared data menggunakan closure
4. **Debouncing**: Frontend debounce untuk prevent double-click

---

## Future Enhancements

- [ ] Authenticated user cart persistence
- [ ] Cart expiration setelah X hari
- [ ] Cart merging saat login
- [ ] Add to cart analytics tracking
- [ ] Stock validation saat add to cart

---

## Story Completion

**Story Points:** 5  
**Actual Effort:** 5 story points  
**Acceptance Criteria:** ✅ All completed  
**Test Coverage:** 12 tests, all passing

Next Story: CUST-006 - View and Manage Cart

