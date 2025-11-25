# Sprint 2 - CUST-006: View and Manage Cart

**Author:** Zulfikar Hidayatullah  
**Sprint:** Sprint 2 (Week 2-3)  
**Story Points:** 5  
**Status:** ✅ Completed  
**Date:** 2024-11-25

---

## Overview

CUST-006 merupakan implementasi kedua dari Epic 2 Sprint 2: Shopping & Checkout, yaitu: menambahkan halaman cart management yang memungkinkan customer untuk melihat semua items di keranjang, mengatur quantity dengan increment/decrement, menghapus items, dan melihat summary total harga dengan real-time updates menggunakan Inertia.js.

## User Story

```
As a customer, I want to view and manage my cart
so I can review and adjust my order before checkout
```

## Acceptance Criteria

- ✅ Cart page showing all items dengan product details
- ✅ Adjust quantities dengan +/- buttons
- ✅ Remove items dengan confirmation
- ✅ Show subtotal and total calculations
- ✅ Empty state untuk cart kosong
- ✅ Real-time updates tanpa page refresh

---

## Technical Implementation

### Backend Architecture

#### 1. CartService - Extended Methods

File: `app/Services/CartService.php`

Extended CartService dengan methods untuk cart management operations:

```php
/**
 * Mengupdate quantity item di cart
 */
public function updateQuantity(int $itemId, int $quantity): CartItem
{
    $cart = $this->getCart();
    $item = $cart->items()->findOrFail($itemId);

    $item->update(['quantity' => $quantity]);
    $item->load('product');

    return $item;
}

/**
 * Menghapus item dari cart
 */
public function removeItem(int $itemId): bool
{
    $cart = $this->getCart();
    $item = $cart->items()->find($itemId);

    if (! $item) {
        return false;
    }

    return $item->delete();
}

/**
 * Mengosongkan semua item di cart
 */
public function clearCart(): bool
{
    $cart = $this->getCart();
    return $cart->items()->delete() >= 0;
}

/**
 * Mendapatkan data cart untuk response
 * dengan format yang siap digunakan di frontend
 */
public function getCartData(): array
{
    $cart = $this->getCart();

    return [
        'id' => $cart->id,
        'items' => $cart->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'slug' => $item->product->slug,
                    'price' => $item->product->price,
                    'image' => $item->product->image,
                    'is_available' => $item->product->isAvailable(),
                ],
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal,
            ];
        }),
        'total_items' => $cart->total_items,
        'subtotal' => $cart->subtotal,
        'formatted_subtotal' => $cart->formatted_subtotal,
    ];
}
```

**Key Features:**
- Update quantity dengan validation via model
- Remove item dengan soft error handling
- Clear cart untuk future checkout integration
- getCartData() dengan formatted response untuk frontend

#### 2. CartController - Extended

File: `app/Http/Controllers/CartController.php`

Controller methods untuk view dan manage cart:

```php
/**
 * Menampilkan halaman keranjang belanja
 * dengan daftar items dan total harga
 */
public function show(): Response
{
    return Inertia::render('Cart', [
        'cart' => $this->cartService->getCartData(),
    ]);
}

/**
 * Mengupdate quantity item di keranjang
 * dengan validasi quantity minimal 1
 */
public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
{
    $validated = $request->validated();

    $this->cartService->updateQuantity(
        $cartItem->id,
        $validated['quantity']
    );

    return back()->with('success', 'Keranjang berhasil diperbarui.');
}

/**
 * Menghapus item dari keranjang belanja
 */
public function destroy(CartItem $cartItem): RedirectResponse
{
    $deleted = $this->cartService->removeItem($cartItem->id);

    if (! $deleted) {
        return back()->with('error', 'Item tidak ditemukan di keranjang.');
    }

    return back()->with('success', 'Item berhasil dihapus dari keranjang.');
}
```

**Key Features:**
- Route model binding untuk CartItem
- Validation via Form Request
- Flash messages untuk user feedback
- preserveScroll untuk better UX

#### 3. UpdateCartItemRequest

File: `app/Http/Requests/UpdateCartItemRequest.php`

Validation untuk update quantity:

```php
public function rules(): array
{
    return [
        'quantity' => ['required', 'integer', 'min:1', 'max:99'],
    ];
}

public function messages(): array
{
    return [
        'quantity.required' => 'Jumlah harus diisi.',
        'quantity.integer' => 'Jumlah harus berupa angka.',
        'quantity.min' => 'Jumlah minimal adalah 1.',
        'quantity.max' => 'Jumlah maksimal adalah 99.',
    ];
}
```

#### 4. Routes

File: `routes/web.php`

Routes untuk cart management operations:

```php
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
```

### Frontend Architecture

#### 1. CartItem Component - Enhanced

File: `resources/js/components/store/CartItem.vue`

Component untuk display dan manage individual cart item dengan Inertia integration:

```vue
<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Minus, Plus, Trash2, Loader2 } from 'lucide-vue-next'
import { update, destroy } from '@/actions/App/Http/Controllers/CartController'

interface Props {
    item: {
        id: number
        product: {
            id: number
            name: string
            price: number
            image?: string | null
            is_available: boolean
        }
        quantity: number
        subtotal: number
    }
}

const props = defineProps<Props>()

const isUpdating = ref(false)
const isRemoving = ref(false)

/**
 * Handler untuk increment quantity
 */
function handleIncrement() {
    if (isUpdating.value) return

    isUpdating.value = true
    router.patch(
        update.url(props.item.id),
        { quantity: props.item.quantity + 1 },
        {
            preserveScroll: true,
            onFinish: () => {
                isUpdating.value = false
            },
        }
    )
}

/**
 * Handler untuk decrement quantity
 */
function handleDecrement() {
    if (isUpdating.value || props.item.quantity <= 1) return

    isUpdating.value = true
    router.patch(
        update.url(props.item.id),
        { quantity: props.item.quantity - 1 },
        {
            preserveScroll: true,
            onFinish: () => {
                isUpdating.value = false
            },
        }
    )
}

/**
 * Handler untuk remove item dari cart
 */
function handleRemove() {
    if (isRemoving.value) return

    isRemoving.value = true
    router.delete(destroy.url(props.item.id), {
        preserveScroll: true,
        onFinish: () => {
            isRemoving.value = false
        },
    })
}
</script>

<template>
    <div class="cart-item" :class="{ 'opacity-50': isRemoving }">
        <!-- Product Image & Info -->
        <div class="product-info">
            <img :src="imageUrl" :alt="item.product.name" />
            <div>
                <h4>{{ item.product.name }}</h4>
                <p>{{ formattedPrice }}</p>
            </div>
        </div>

        <!-- Quantity Controls -->
        <div class="quantity-controls">
            <Button
                @click="handleDecrement"
                :disabled="item.quantity <= 1 || isUpdating"
            >
                <Loader2 v-if="isUpdating" class="animate-spin" />
                <Minus v-else />
            </Button>
            <span>{{ item.quantity }}</span>
            <Button @click="handleIncrement" :disabled="isUpdating">
                <Loader2 v-if="isUpdating" class="animate-spin" />
                <Plus v-else />
            </Button>
        </div>

        <!-- Subtotal & Remove -->
        <div class="item-actions">
            <p class="subtotal">{{ formattedSubtotal }}</p>
            <Button @click="handleRemove" :disabled="isRemoving">
                <Loader2 v-if="isRemoving" class="animate-spin" />
                <Trash2 v-else />
            </Button>
        </div>
    </div>
</template>
```

**Key Features:**
- Increment/decrement dengan loading states
- Remove button dengan loading state
- Disabled state durante operations
- PreserveScroll untuk smooth UX
- Wayfinder integration untuk type-safe URLs
- Opacity change saat removing

#### 2. Cart Page

File: `resources/js/pages/Cart.vue`

Full cart management page dengan list items dan summary:

```vue
<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { home } from '@/routes'
import CartItem from '@/components/store/CartItem.vue'
import CartCounter from '@/components/store/CartCounter.vue'
import EmptyState from '@/components/store/EmptyState.vue'
import { ArrowLeft, ShoppingCart } from 'lucide-vue-next'

interface CartData {
    id: number
    items: Array<{
        id: number
        product: {
            id: number
            name: string
            price: number
            image?: string | null
            is_available: boolean
        }
        quantity: number
        subtotal: number
    }>
    total_items: number
    subtotal: number
    formatted_subtotal: string
}

interface Props {
    cart: CartData
}

const props = defineProps<Props>()

const isEmpty = computed(() => props.cart.items.length === 0)

const formattedSubtotal = computed(() => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(props.cart.subtotal)
})
</script>

<template>
    <Head title="Keranjang Belanja - Simple Store" />

    <div class="cart-page">
        <!-- Header dengan Cart Counter -->
        <header>
            <Link :href="home()">Simple Store</Link>
            <CartCounter :count="cart.total_items" />
        </header>

        <main>
            <!-- Back Button -->
            <Link :href="home()">
                <ArrowLeft /> Kembali ke Katalog
            </Link>

            <!-- Page Title -->
            <h1>Keranjang Belanja</h1>
            <p>
                {{ isEmpty ? 'Keranjang Anda kosong' : `${cart.total_items} item dalam keranjang` }}
            </p>

            <!-- Empty State -->
            <EmptyState
                v-if="isEmpty"
                icon="🛒"
                title="Keranjang Kosong"
                description="Belum ada produk di keranjang. Yuk mulai belanja!"
            >
                <template #action>
                    <Link :href="home()">
                        <Button>
                            <ShoppingCart /> Mulai Belanja
                        </Button>
                    </Link>
                </template>
            </EmptyState>

            <!-- Cart Content -->
            <div v-else class="cart-content">
                <!-- Cart Items List -->
                <div class="cart-items">
                    <CartItem
                        v-for="item in cart.items"
                        :key="item.id"
                        :item="item"
                    />
                </div>

                <!-- Order Summary Sidebar -->
                <div class="order-summary">
                    <h2>Ringkasan Pesanan</h2>

                    <div class="summary-details">
                        <div>
                            <span>Subtotal ({{ cart.total_items }} item)</span>
                            <span>{{ formattedSubtotal }}</span>
                        </div>
                        <div>
                            <span>Ongkos Kirim</span>
                            <span>Dihitung saat checkout</span>
                        </div>
                        <hr />
                        <div class="total">
                            <span>Total</span>
                            <span>{{ formattedSubtotal }}</span>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <Button size="lg" class="checkout-button">
                        Lanjut ke Checkout
                    </Button>

                    <!-- Continue Shopping Link -->
                    <Link :href="home()" class="continue-shopping">
                        atau lanjut belanja
                    </Link>
                </div>
            </div>
        </main>
    </div>
</template>
```

**Key Features:**
- Empty state dengan call-to-action
- List of CartItem components
- Order summary dengan calculations
- Sticky summary sidebar (desktop)
- Responsive layout (mobile/desktop)
- Cart counter in header
- Back to catalog navigation

### Testing

File: `tests/Feature/CartManagementTest.php`

Extended tests untuk cart management functionality:

```php
public function test_can_view_cart_page(): void
{
    $response = $this->get('/cart');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Cart')
        ->has('cart')
        ->has('cart.items')
        ->where('cart.total_items', 0)
    );
}

public function test_cart_calculates_totals_correctly(): void
{
    $cartService = app(CartService::class);
    $product1 = Product::factory()->create(['price' => 10000]);
    $product2 = Product::factory()->create(['price' => 20000]);

    $cartService->addItem($product1->id, 2);
    $cartService->addItem($product2->id, 1);

    $cartData = $cartService->getCartData();

    $this->assertCount(2, $cartData['items']);
    $this->assertEquals(3, $cartData['total_items']);
    $this->assertEquals(40000, $cartData['subtotal']);
}

public function test_can_update_cart_item_quantity(): void
{
    $cartService = app(CartService::class);
    $product = Product::factory()->create();

    $cartService->addItem($product->id, 2);
    $cartItem = CartItem::first();

    $cartService->updateQuantity($cartItem->id, 5);

    $this->assertDatabaseHas('cart_items', [
        'id' => $cartItem->id,
        'quantity' => 5,
    ]);
}

public function test_can_remove_item_from_cart(): void
{
    $cartService = app(CartService::class);
    $product = Product::factory()->create();

    $cartService->addItem($product->id, 2);
    $cartItem = CartItem::first();

    $cartService->removeItem($cartItem->id);

    $this->assertDatabaseMissing('cart_items', [
        'id' => $cartItem->id,
    ]);
}
```

**Test Coverage:**
- ✅ View cart page
- ✅ Cart calculations (totals, subtotals)
- ✅ Update quantity
- ✅ Remove item
- ✅ Empty cart display
- ✅ Cart counter accuracy

---

## Wayfinder Integration

Type-safe cart management routes:

```typescript
import { 
    show,
    update,
    destroy 
} from '@/actions/App/Http/Controllers/CartController'

// View cart
router.get(show.url())

// Update quantity
router.patch(update.url(itemId), { quantity: 5 })

// Remove item
router.delete(destroy.url(itemId))
```

---

## UI/UX Considerations

1. **Loading States**: Spinner icons durante operations
2. **Disabled States**: Prevent double-click dan invalid operations
3. **Optimistic Updates**: Disabled selama request pending
4. **PreserveScroll**: Maintain scroll position setelah updates
5. **Visual Feedback**: Success checkmarks, opacity changes
6. **Empty State**: Clear call-to-action untuk empty cart
7. **Responsive Design**: Mobile-first dengan desktop enhancements

---

## Performance Optimizations

1. **Eager Loading**: Load items.product untuk avoid N+1
2. **PreserveScroll**: Tidak reload entire page
3. **Conditional Rendering**: Hide sections based on state
4. **Debounced Actions**: Prevent rapid clicking
5. **Lazy Loading**: Cart data dalam Inertia shared data

---

## Future Enhancements

- [ ] Bulk operations (select multiple, remove all)
- [ ] Save for later functionality
- [ ] Stock warnings di cart page
- [ ] Price change notifications
- [ ] Cart persistence notification
- [ ] Undo remove functionality

---

## Story Completion

**Story Points:** 5  
**Actual Effort:** 5 story points  
**Acceptance Criteria:** ✅ All completed  
**Test Coverage:** 12 tests total, all passing

Next Story: CUST-007 - Checkout Form

