# Orders API Documentation

**Author:** Zulfikar Hidayatullah  
**Last Updated:** 2025-12-10  
**Version:** 1.1.0

---

## Overview

Orders API merupakan endpoint untuk mengelola order management dengan WhatsApp integration yang bertujuan untuk menyediakan seamless ordering experience, yaitu: checkout flow, invoice generation, WhatsApp messaging, dan order tracking.

---

## Base URL

```
Production: https://your-domain.com
Development: http://localhost:8000
```

---

## Authentication

Semua endpoint memerlukan authentication kecuali disebutkan lain:

```http
Cookie: laravel_session={SESSION_TOKEN}
X-XSRF-TOKEN: {CSRF_TOKEN}
```

---

## Endpoints

### 1. View Checkout Page

Menampilkan checkout form dengan cart summary untuk authenticated user.

#### Request

```http
GET /checkout
Accept: application/json
X-Inertia: true
Cookie: laravel_session={SESSION_TOKEN}
```

#### Response (Success)

```http
HTTP/1.1 200 OK
Content-Type: application/json
X-Inertia: true
```

```json
{
  "component": "Checkout",
  "props": {
    "cart": {
      "items": [
        {
          "id": 1,
          "product": {
            "id": 1,
            "name": "Nasi Goreng Spesial",
            "price": 25000,
            "image_url": "/storage/products/nasi-goreng.jpg"
          },
          "quantity": 2,
          "subtotal": 50000
        }
      ],
      "subtotal": 50000,
      "deliveryFee": 10000,
      "total": 60000
    },
    "customer": {
      "name": "John Doe",
      "phone": "081234567890",
      "address": "Jl. Sudirman No. 123"
    }
  }
}
```

**Note:** `customer` prop berisi data user yang login untuk auto-fill form checkout. Jika user tidak memiliki phone atau address, field tersebut akan null.

#### Response (Unauthorized)

```http
HTTP/1.1 302 Found
Location: /login
```

#### Response (Empty Cart)

```http
HTTP/1.1 302 Found
Location: /cart
```

#### Rate Limiting

- **Limit:** 60 requests per minute per IP
- **Headers:**
  - `X-RateLimit-Limit: 60`
  - `X-RateLimit-Remaining: 59`

---

### 2. Process Checkout

Membuat order baru dari cart items dan generate invoice dengan WhatsApp integration.

#### Request

```http
POST /checkout
Accept: application/json
Content-Type: application/json
X-Inertia: true
Cookie: laravel_session={SESSION_TOKEN}
X-XSRF-TOKEN: {CSRF_TOKEN}
```

```json
{
  "customer_name": "John Doe",
  "customer_phone": "081234567890",
  "customer_address": "Jl. Sudirman No. 123, Jakarta Pusat",
  "notes": "Tolong tambahkan sambal extra"
}
```

#### Request Body Parameters

| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| `customer_name` | string | Yes | min:3, max:100 | Nama customer untuk order |
| `customer_phone` | string | Yes | min:10, max:15, regex:/^[0-9+\-\s]+$/ | Nomor telepon customer (WhatsApp) |
| `customer_address` | string | **No** | max:500 | Alamat lengkap untuk delivery (opsional) |
| `notes` | string | No | max:500 | Catatan tambahan untuk order |

**Note:** `customer_address` sekarang opsional untuk mendukung pickup orders atau delivery dengan alamat fleksibel.

#### Response (Success)

```http
HTTP/1.1 302 Found
Location: /checkout/success/{order_id}
```

#### Response (Validation Error)

```http
HTTP/1.1 302 Found
Location: /checkout
Set-Cookie: laravel_session={SESSION_TOKEN}
```

```json
{
  "errors": {
    "customer_name": ["The customer name field is required."],
    "customer_phone": ["The customer phone field is required."]
  }
}
```

#### Response (Empty Cart)

```http
HTTP/1.1 302 Found
Location: /cart
```

```json
{
  "errors": {
    "cart": ["Your cart is empty. Please add items before checkout."]
  }
}
```

#### Rate Limiting

- **Limit:** 10 requests per minute per user
- **Headers:**
  - `X-RateLimit-Limit: 10`
  - `X-RateLimit-Remaining: 9`

#### Business Logic

1. **Validation:**
   - Cart tidak boleh empty
   - All required fields validated
   - User harus authenticated

2. **Order Creation (DB Transaction):**
   ```
   START TRANSACTION
   ├── Create order record
   ├── Copy cart items to order_items
   ├── Clear user's cart
   └── COMMIT
   ```

3. **Invoice Generation:**
   - Format: `ORD-YYYYMMDD-{5 random alphanumeric}`
   - Example: `ORD-20251210-A7B9C`
   - Uniqueness guaranteed via database check

4. **Order Data:**
   - User information attached
   - Pricing calculated (subtotal + delivery_fee)
   - Status set to 'pending'
   - Timestamps recorded

---

### 3. View Order Success

Menampilkan order success page dengan detail order dan WhatsApp integration button.

#### Request

```http
GET /checkout/success/{order}
Accept: application/json
X-Inertia: true
Cookie: laravel_session={SESSION_TOKEN}
```

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `order` | integer | Yes | Order ID |

#### Response (Success)

```http
HTTP/1.1 200 OK
Content-Type: application/json
X-Inertia: true
```

```json
{
  "component": "OrderSuccess",
  "props": {
    "order": {
      "id": 123,
      "order_number": "ORD-20251210-A7B9C",
      "customer_name": "John Doe",
      "customer_phone": "081234567890",
      "customer_address": "Jl. Sudirman No. 123, Jakarta Pusat",
      "notes": "Tolong tambahkan sambal extra",
      "subtotal": 50000,
      "delivery_fee": 10000,
      "total": 60000,
      "status": "pending",
      "created_at": "2025-12-10T14:30:00.000000Z",
      "items": [
        {
          "id": 1,
          "product_name": "Nasi Goreng Spesial",
          "quantity": 2,
          "price": 25000,
          "subtotal": 50000
        }
      ]
    },
    "whatsappUrl": "https://wa.me/6281234567890?text=Halo%21%20Saya%20ingin%20memesan..."
  }
}
```

#### Response (Not Found)

```http
HTTP/1.1 404 Not Found
Content-Type: application/json
```

```json
{
  "message": "Order not found"
}
```

#### Response (Unauthorized - Not Owner)

```http
HTTP/1.1 403 Forbidden
Content-Type: application/json
```

```json
{
  "message": "This action is unauthorized."
}
```

#### WhatsApp URL Structure

```
https://wa.me/{OWNER_PHONE}?text={ENCODED_MESSAGE}
```

**Phone Number Formatting:**
- Sistem auto-format nomor dari `08xxx` ke `628xxx`
- Country code Indonesia (62) ditambahkan otomatis
- Non-numeric characters dihapus

**Message Format:**
```
Halo! Saya *John Doe* ingin memesan.

*Invoice:* #ORD-20251210-A7B9C

*Ringkasan Pesanan:*
- Nasi Goreng Spesial × 2

*Total:* Rp 60.000

*Alamat:* Jl. Sudirman No. 123, Jakarta Pusat

*Catatan:* Tolong tambahkan sambal extra

*Link Detail Pesanan:*
https://your-domain.com/admin/orders/123

Mohon konfirmasi pesanan saya. Terima kasih!
```

**Note:** Alamat dan Catatan hanya ditampilkan jika diisi oleh customer.

---

## Admin Endpoints

### 4. List Orders (Admin)

Menampilkan list semua orders dengan pagination, search, dan filter untuk admin.

#### Request

```http
GET /admin/orders
Accept: application/json
X-Inertia: true
Cookie: laravel_session={SESSION_TOKEN}
```

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `search` | string | No | Search by order number, customer name, atau phone |
| `status` | string | No | Filter by status (pending, confirmed, preparing, ready, delivered, cancelled) |
| `page` | integer | No | Page number (default: 1) |
| `per_page` | integer | No | Items per page (default: 15) |

#### Example Request

```http
GET /admin/orders?search=john&status=pending&page=1
```

#### Response (Success)

```http
HTTP/1.1 200 OK
Content-Type: application/json
X-Inertia: true
```

```json
{
  "component": "Admin/Orders/Index",
  "props": {
    "orders": {
      "data": [
        {
          "id": 123,
          "order_number": "ORD-20251210-A7B9C",
          "customer_name": "John Doe",
          "customer_phone": "081234567890",
          "total": 60000,
          "status": "pending",
          "created_at": "2025-12-10T14:30:00.000000Z",
          "items_count": 2
        }
      ],
      "current_page": 1,
      "last_page": 5,
      "per_page": 15,
      "total": 73
    },
    "filters": {
      "search": "john",
      "status": "pending"
    }
  }
}
```

#### Authorization

- Requires `admin` role
- Middleware: `auth`, `admin`

---

### 5. View Order Detail (Admin)

Menampilkan detail lengkap order dengan WhatsApp template options untuk admin.

#### Request

```http
GET /admin/orders/{order}
Accept: application/json
X-Inertia: true
Cookie: laravel_session={SESSION_TOKEN}
```

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `order` | integer | Yes | Order ID |

#### Response (Success)

```http
HTTP/1.1 200 OK
Content-Type: application/json
X-Inertia: true
```

```json
{
  "component": "Admin/Orders/Show",
  "props": {
    "order": {
      "id": 123,
      "order_number": "ORD-20251210-A7B9C",
      "user_id": 456,
      "customer_name": "John Doe",
      "customer_phone": "081234567890",
      "customer_address": "Jl. Sudirman No. 123, Jakarta Pusat",
      "notes": "Tolong tambahkan sambal extra",
      "subtotal": 50000,
      "delivery_fee": 10000,
      "total": 60000,
      "status": "pending",
      "created_at": "2025-12-10T14:30:00.000000Z",
      "updated_at": "2025-12-10T14:30:00.000000Z",
      "user": {
        "id": 456,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "items": [
        {
          "id": 1,
          "order_id": 123,
          "product_id": 10,
          "product_name": "Nasi Goreng Spesial",
          "quantity": 2,
          "price": 25000,
          "subtotal": 50000,
          "product": {
            "id": 10,
            "name": "Nasi Goreng Spesial",
            "image_url": "/storage/products/nasi-goreng.jpg"
          }
        }
      ]
    },
    "whatsappToCustomerUrls": {
      "confirmed": "https://wa.me/6281234567890?text=Halo%20John%20Doe...",
      "preparing": "https://wa.me/6281234567890?text=Halo%20John%20Doe...",
      "ready": "https://wa.me/6281234567890?text=Halo%20John%20Doe...",
      "delivered": "https://wa.me/6281234567890?text=Halo%20John%20Doe...",
      "cancelled": "https://wa.me/6281234567890?text=Halo%20John%20Doe..."
    }
  }
}
```

#### WhatsApp Template Messages

**Confirmed:**
```
Halo John Doe! ✅

Pesanan Anda telah kami terima dan dikonfirmasi.

*Invoice:* #ORD-20251210-A7B9C
*Total:* Rp 60.000

Kami akan segera memproses pesanan Anda.
Terima kasih! 🙏
```

**Preparing:**
```
Halo John Doe! 👨‍🍳

Pesanan Anda sedang diproses.

*Invoice:* #ORD-20251210-A7B9C
*Status:* Sedang Diproses

Mohon tunggu sebentar ya!
```

**Ready:**
```
Halo John Doe! 🎉

Pesanan Anda sudah siap!

*Invoice:* #ORD-20251210-A7B9C
*Status:* Siap untuk Diambil/Dikirim

Silakan ambil pesanan Anda atau kami akan segera kirim.
Terima kasih! 🙏
```

**Delivered:**
```
Halo John Doe! ✅

Pesanan Anda telah dikirim/diserahkan.

*Invoice:* #ORD-20251210-A7B9C
*Total:* Rp 60.000

Terima kasih telah berbelanja! 
Semoga puas dengan pesanan Anda 😊
```

**Cancelled:**
```
Halo John Doe,

Mohon maaf, pesanan Anda telah dibatalkan.

*Invoice:* #ORD-20251210-A7B9C
*Alasan:* [Alasan pembatalan]

Jika ada pertanyaan, silakan hubungi kami.
Terima kasih atas pengertiannya.
```

#### Authorization

- Requires `admin` role
- Middleware: `auth`, `admin`

---

### 6. Update Order Status (Admin)

Mengupdate status order untuk tracking order lifecycle.

#### Request

```http
PATCH /admin/orders/{order}
Accept: application/json
Content-Type: application/json
X-Inertia: true
Cookie: laravel_session={SESSION_TOKEN}
X-XSRF-TOKEN: {CSRF_TOKEN}
```

```json
{
  "status": "confirmed"
}
```

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `order` | integer | Yes | Order ID |

#### Request Body Parameters

| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| `status` | string | Yes | in:pending,confirmed,preparing,ready,delivered,cancelled | Order status baru |

#### Valid Status Transitions

```
pending → confirmed
confirmed → preparing
preparing → ready
ready → delivered
any → cancelled
```

#### Response (Success)

```http
HTTP/1.1 302 Found
Location: /admin/orders/{order}
```

```json
{
  "message": "Order status updated successfully"
}
```

#### Response (Validation Error)

```http
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/json
```

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "status": ["The selected status is invalid."]
  }
}
```

#### Authorization

- Requires `admin` role
- Middleware: `auth`, `admin`

---

## Order Status Lifecycle

```
┌──────────┐
│ pending  │ ◄── Initial status after checkout
└────┬─────┘
     │
     ▼
┌──────────┐
│confirmed │ ◄── Admin confirms order
└────┬─────┘
     │
     ▼
┌──────────┐
│preparing │ ◄── Kitchen preparing food
└────┬─────┘
     │
     ▼
┌──────────┐
│  ready   │ ◄── Order ready for pickup/delivery
└────┬─────┘
     │
     ▼
┌──────────┐
│delivered │ ◄── Order completed
└──────────┘

     │ (from any status)
     ▼
┌──────────┐
│cancelled │ ◄── Order cancelled
└──────────┘
```

---

## Auto-Cancel Feature

### Background Job

**Command:** `php artisan app:auto-cancel-pending-orders`

**Schedule:** Every minute via Laravel Scheduler

**Logic:**
```php
// Get auto_cancel_minutes from settings
$minutes = StoreSettingService::getAutoCancelMinutes();

// Find pending orders older than X minutes
Order::where('status', 'pending')
     ->where('created_at', '<', now()->subMinutes($minutes))
     ->update(['status' => 'cancelled']);
```

**Configuration:**
- Enable/disable via Admin Settings
- Configurable minutes (default: 30)
- Affects only 'pending' status orders

---

## Error Responses

### 401 Unauthorized

```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden

```json
{
  "message": "This action is unauthorized."
}
```

### 404 Not Found

```json
{
  "message": "Order not found"
}
```

### 422 Unprocessable Entity

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "customer_name": ["The customer name field is required."],
    "customer_phone": ["The customer phone format is invalid."]
  }
}
```

### 429 Too Many Requests

```json
{
  "message": "Too many requests. Please try again later.",
  "retry_after": 60
}
```

**Headers:**
```
X-RateLimit-Limit: 10
X-RateLimit-Remaining: 0
Retry-After: 60
```

---

## Rate Limiting Summary

| Endpoint | Limit | Scope | Key |
|----------|-------|-------|-----|
| `GET /checkout` | 60/min | IP | Request IP |
| `POST /checkout` | 10/min | User | User ID |
| `GET /checkout/success/{order}` | 60/min | IP | Request IP |
| `GET /admin/orders` | None | - | - |
| `GET /admin/orders/{order}` | None | - | - |
| `PATCH /admin/orders/{order}` | None | - | - |

---

## Best Practices

### Frontend Integration

**1. Handle Redirects:**
```javascript
// Inertia.js handles redirects automatically
router.post('/checkout', formData, {
  onSuccess: () => {
    // Redirect to success page handled by backend
  },
  onError: (errors) => {
    // Display validation errors
  }
});
```

**2. WhatsApp Button Click:**
```javascript
// Open WhatsApp in new window
const openWhatsApp = (url) => {
  window.open(url, '_blank');
};
```

**3. Real-time Updates:**
```javascript
// Poll for order status changes
const checkOrderStatus = async (orderId) => {
  const response = await fetch(`/api/orders/${orderId}/status`);
  const { status } = await response.json();
  // Update UI
};
```

### Backend Integration

**1. Service Layer:**
```php
// Use OrderService for business logic
$order = app(OrderService::class)->createOrder($request->user(), $data);
```

**2. Database Transactions:**
```php
// Wrap critical operations
DB::transaction(function () use ($user, $data) {
    // Create order
    // Copy cart items
    // Clear cart
});
```

**3. Error Handling:**
```php
try {
    $order = OrderService::createOrder($user, $data);
} catch (EmptyCartException $e) {
    return redirect()->route('cart')->withErrors(['cart' => $e->getMessage()]);
}
```

---

## Testing Examples

### Feature Test: Checkout

```php
public function test_can_complete_checkout_with_valid_data(): void
{
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 25000]);
    
    // Add to cart
    Cart::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);
    
    // Checkout
    $response = $this->actingAs($user)->post('/checkout', [
        'customer_name' => 'John Doe',
        'customer_phone' => '081234567890',
        'customer_address' => 'Jl. Test No. 123',
    ]);
    
    // Assertions
    $response->assertRedirect();
    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'customer_name' => 'John Doe',
        'total' => 50000,
        'status' => 'pending',
    ]);
    $this->assertDatabaseCount('carts', 0); // Cart cleared
}
```

### Unit Test: Order Model

```php
public function test_can_generate_whatsapp_message(): void
{
    $order = Order::factory()
        ->has(OrderItem::factory()->count(2))
        ->create();
    
    $message = $order->generateWhatsAppMessage();
    
    $this->assertStringContainsString($order->order_number, $message);
    $this->assertStringContainsString($order->customer_name, $message);
    $this->assertStringContainsString('Mohon konfirmasi', $message);
}
```

---

## Performance Metrics

### Expected Response Times

| Endpoint | P50 | P95 | P99 |
|----------|-----|-----|-----|
| `GET /checkout` | 50ms | 100ms | 200ms |
| `POST /checkout` | 150ms | 300ms | 500ms |
| `GET /checkout/success` | 50ms | 100ms | 200ms |
| `GET /admin/orders` | 100ms | 200ms | 400ms |
| `GET /admin/orders/{id}` | 75ms | 150ms | 300ms |

### Database Query Optimization

- Eager load relationships: `with(['items.product', 'user'])`
- Index frequently queried columns
- Use pagination untuk large datasets
- Cache store settings (1 hour TTL)

---

## Security Considerations

### Input Sanitization

```php
// Automatically handled by Laravel validation
$validated = $request->validate([
    'customer_name' => ['required', 'string', 'max:255'],
    'customer_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
    'customer_address' => ['required', 'string', 'max:500'],
    'notes' => ['nullable', 'string', 'max:1000'],
]);
```

### CSRF Protection

```html
<!-- Automatically included in Inertia forms -->
<Form method="post" action="/checkout">
  <!-- CSRF token auto-injected -->
</Form>
```

### Authorization Checks

```php
// Policy-based authorization
Gate::authorize('view', $order);
```

---

## User Endpoints

### 7. View User Order Detail

Menampilkan detail pesanan untuk user yang memiliki order tersebut.

#### Request

```http
GET /account/orders/{order}
Accept: application/json
X-Inertia: true
Cookie: laravel_session={SESSION_TOKEN}
```

#### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `order` | integer | Yes | Order ID |

#### Response (Success)

```http
HTTP/1.1 200 OK
Content-Type: application/json
X-Inertia: true
```

```json
{
  "component": "Account/OrderDetail",
  "props": {
    "order": {
      "id": 123,
      "order_number": "ORD-20251210-A7B9C",
      "customer_name": "John Doe",
      "customer_phone": "081234567890",
      "customer_address": "Jl. Sudirman No. 123",
      "notes": "Tidak pedas",
      "subtotal": 50000,
      "delivery_fee": 5000,
      "total": 55000,
      "status": "pending",
      "status_label": "Menunggu Konfirmasi",
      "items": [
        {
          "id": 1,
          "product_name": "Nasi Goreng Spesial",
          "product_price": 25000,
          "quantity": 2,
          "subtotal": 50000,
          "notes": null
        }
      ],
      "items_count": 1,
      "cancellation_reason": null,
      "timestamps": {
        "created_at": "2025-12-10 14:30:00",
        "created_at_human": "5 minutes ago",
        "confirmed_at": null,
        "preparing_at": null,
        "ready_at": null,
        "delivered_at": null,
        "cancelled_at": null
      },
      "whatsapp_url": "https://wa.me/628xxx?text=..."
    }
  }
}
```

**Note:** `whatsapp_url` hanya disertakan untuk order dengan status `pending` agar user dapat mengirim ulang konfirmasi WhatsApp.

#### Response (Forbidden - Not Owner)

```http
HTTP/1.1 403 Forbidden
Content-Type: application/json
```

```json
{
  "message": "This action is unauthorized."
}
```

#### Authorization

- Requires authentication
- User must be the owner of the order (`order.user_id === auth.user.id`)

---

## Changelog

### Version 1.2.0 (2025-12-10)

**Added:**
- ✅ User order detail endpoint (`GET /account/orders/{order}`)
- ✅ `whatsapp_url` field in order detail for pending orders
- ✅ Order creation now saves `user_id` for authenticated users
- ✅ Status timeline with timestamps in order detail

**Changed:**
- `getOrderDetail()` now conditionally includes `whatsapp_url` for pending orders

**Fixed:**
- Orders were created with `user_id: null` for authenticated users - now correctly saves user ID

### Version 1.1.0 (2025-12-10)

**Added:**
- ✅ Customer data auto-fill dari authenticated user
- ✅ Phone number auto-formatting dengan country code (62)
- ✅ Customer name di WhatsApp message greeting

**Changed:**
- `customer_address` sekarang opsional (nullable)
- WhatsApp message format lebih concise
- Conditional display untuk alamat dan catatan

**Fixed:**
- Phone number formatting untuk WhatsApp customer-to-owner

### Version 1.0.0 (2025-12-10)

**Added:**
- ✅ Complete order management endpoints
- ✅ WhatsApp integration URLs
- ✅ Admin order listing dengan filters
- ✅ Order status lifecycle management
- ✅ Auto-cancel background job
- ✅ Rate limiting pada critical endpoints

**Security:**
- ✅ Authentication requirements
- ✅ Authorization policies
- ✅ Input validation dan sanitization
- ✅ CSRF protection

**Performance:**
- ✅ Database query optimization
- ✅ Eager loading relationships
- ✅ Response time targets documented

---

## Support

Untuk pertanyaan atau issue terkait Orders API:
- **Email:** support@your-domain.com
- **Documentation:** https://your-domain.com/docs
- **API Status:** https://status.your-domain.com

---

**Last Updated:** 2025-12-10  
**API Version:** 1.1.0  
**Author:** Zulfikar Hidayatullah

