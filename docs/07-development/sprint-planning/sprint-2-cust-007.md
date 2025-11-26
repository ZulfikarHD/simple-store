# Sprint 2 - CUST-007: Checkout Form Implementation

**Author:** Zulfikar Hidayatullah  
**Sprint:** Sprint 2 (Week 2-3)  
**Story Points:** 5  
**Status:** ✅ Completed  
**Date:** 2024-11-26

---

## Overview

CUST-007 merupakan implementasi checkout form yang bertujuan untuk memungkinkan customer menyelesaikan pesanan, yaitu: mengisi data pengiriman (nama, telepon, alamat), melihat ringkasan pesanan, dan melakukan validasi input secara real-time sebelum melanjutkan ke proses pembayaran via WhatsApp.

## User Story

```
As a customer, I want to fill checkout form with my delivery details
so I can complete my order with correct shipping information
```

## Acceptance Criteria

- ✅ Form checkout dengan fields: nama lengkap, nomor telepon, alamat, catatan (opsional)
- ✅ Validasi form real-time dengan pesan error dalam bahasa Indonesia
- ✅ Tampilan order summary dari cart items
- ✅ Redirect ke cart jika cart kosong
- ✅ Submit button dengan loading state

---

## Technical Implementation

### Backend Architecture

#### 1. CheckoutController

File: `app/Http/Controllers/CheckoutController.php`

Controller untuk mengelola proses checkout dengan dependency injection CartService dan OrderService, yaitu:

```php
/**
 * Controller untuk mengelola proses checkout
 * termasuk menampilkan form, proses order, dan halaman sukses
 * dengan integrasi WhatsApp untuk notifikasi pesanan
 */
class CheckoutController extends Controller
{
    public function __construct(
        public CartService $cartService,
        public OrderService $orderService
    ) {}

    public function show(): Response|RedirectResponse
    {
        $cartData = $this->cartService->getCartData();

        // Redirect ke cart jika kosong
        if (count($cartData['items']) === 0) {
            return redirect()->route('cart.show')
                ->with('error', 'Keranjang belanja kosong.');
        }

        return Inertia::render('Checkout', [
            'cart' => $cartData,
        ]);
    }
}
```

**Key Features:**
- Dependency injection untuk CartService dan OrderService
- Validasi cart tidak kosong sebelum menampilkan form
- Redirect dengan flash message jika cart kosong

#### 2. CheckoutRequest - Form Validation

File: `app/Http/Requests/CheckoutRequest.php`

Form Request untuk validasi input checkout dengan rules dan messages dalam bahasa Indonesia, yaitu:

```php
public function rules(): array
{
    return [
        'customer_name' => ['required', 'string', 'min:3', 'max:100'],
        'customer_phone' => ['required', 'string', 'min:10', 'max:15', 'regex:/^[0-9+\-\s]+$/'],
        'customer_address' => ['required', 'string', 'min:10', 'max:500'],
        'notes' => ['nullable', 'string', 'max:500'],
    ];
}

public function messages(): array
{
    return [
        'customer_name.required' => 'Nama lengkap harus diisi.',
        'customer_phone.required' => 'Nomor telepon harus diisi.',
        'customer_phone.regex' => 'Format nomor telepon tidak valid.',
        'customer_address.required' => 'Alamat pengiriman harus diisi.',
        // ... custom messages lainnya
    ];
}
```

**Key Features:**
- Validasi format nomor telepon dengan regex
- Custom error messages dalam bahasa Indonesia
- Validasi panjang minimum dan maximum untuk semua fields

#### 3. Routes Configuration

File: `routes/web.php`

Routes untuk checkout flow dengan naming convention yang konsisten:

```php
// Checkout routes untuk proses pemesanan
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
```

### Frontend Implementation

#### 1. Checkout.vue Page

File: `resources/js/pages/Checkout.vue`

Halaman checkout dengan form Inertia dan order summary, yaitu:

```vue
<script setup lang="ts">
import { Form } from '@inertiajs/vue3'
import { store as checkoutStore } from '@/actions/App/Http/Controllers/CheckoutController'
// ... imports

interface Props {
    cart: CartData
}

const props = defineProps<Props>()
</script>

<template>
    <Form v-bind="checkoutStore.form()" #default="{ errors, processing, hasErrors }">
        <!-- Data Penerima Section -->
        <div class="rounded-xl border border-border bg-card p-6">
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="customer_name">Nama Lengkap *</Label>
                    <Input id="customer_name" name="customer_name" />
                    <p v-if="errors.customer_name" class="text-sm text-destructive">
                        {{ errors.customer_name }}
                    </p>
                </div>
                <!-- ... fields lainnya -->
            </div>
        </div>

        <!-- Order Summary -->
        <div class="sticky top-24 rounded-xl border border-border bg-card p-6">
            <!-- Items list dan totals -->
            <Button type="submit" :disabled="processing">
                <Loader2 v-if="processing" class="h-4 w-4 animate-spin" />
                Pesan via WhatsApp
            </Button>
        </div>
    </Form>
</template>
```

**Key Features:**
- Inertia Form component untuk seamless form handling
- Real-time error display per field
- Loading state dengan spinner icon
- Responsive layout dengan sticky order summary

---

## Testing

### Feature Tests

File: `tests/Feature/CheckoutTest.php`

Tests yang mencakup berbagai scenarios checkout flow, yaitu:

```php
// Test redirect ke cart jika kosong
public function test_redirects_to_cart_when_checkout_with_empty_cart(): void
{
    $response = $this->get('/checkout');
    $response->assertRedirect('/cart');
    $response->assertSessionHas('error');
}

// Test validasi customer_name
public function test_validation_fails_when_customer_name_is_empty(): void
{
    $response = $this->post('/checkout', [
        'customer_name' => '',
        'customer_phone' => '081234567890',
        'customer_address' => 'Jl. Test No. 123',
    ]);
    $response->assertSessionHasErrors('customer_name');
}
```

### Test Results

```
✓ redirects to cart when checkout with empty cart
✓ validation fails when customer name is empty
✓ validation fails when phone format invalid
✓ validation fails when address too short
```

---

## Results

### Files Created/Modified

**New Files:**
- `app/Http/Controllers/CheckoutController.php`
- `app/Http/Requests/CheckoutRequest.php`
- `resources/js/pages/Checkout.vue`

**Modified Files:**
- `routes/web.php` - Added checkout routes
- `resources/js/pages/Cart.vue` - Updated checkout button link

### Screenshot

Halaman checkout menampilkan:
1. Form data penerima dengan validasi real-time
2. Order summary dengan daftar items
3. Button submit dengan loading state

---

## Related Documentation

- [CUST-005: Add Products to Cart](./sprint-2-cust-005.md)
- [CUST-006: View and Manage Cart](./sprint-2-cust-006.md)
- [CUST-008: WhatsApp Integration](./sprint-2-cust-008.md)
- [CUST-009: Order Confirmation](./sprint-2-cust-009.md)

