# Dokumentasi API & Routes Simple Store

**Penulis**: Zulfikar Hidayatullah  
**Versi**: 1.0  
**Terakhir Diperbarui**: Desember 2025

---

## Overview

Simple Store menggunakan Inertia.js sebagai penghubung antara frontend Vue.js dan backend Laravel, yaitu: menghilangkan kebutuhan API REST terpisah dengan mengirimkan data langsung sebagai props ke Vue components. Dengan demikian, dokumentasi ini mencakup web routes dan internal API endpoints untuk admin panel.

## Architecture Pattern

```
┌─────────────────────────────────────────────────────────────────┐
│                    INERTIA.JS PROTOCOL                          │
│  ┌─────────────────┐    HTTP Request    ┌─────────────────────┐ │
│  │  Vue Component  │ ──────────────────→│  Laravel Controller │ │
│  │                 │                    │                     │ │
│  │  (uses router   │ ←────────────────  │  Inertia::render()  │ │
│  │   or Form)      │    Inertia Response│  returns props      │ │
│  └─────────────────┘                    └─────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## Public Routes (Customer-Facing)

### Product Catalog

#### GET `/` (Home)
Menampilkan halaman utama dengan katalog produk.

**Controller**: `ProductController@index`  
**Route Name**: `home`

**Query Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `category` | integer | No | Filter berdasarkan category_id |
| `search` | string | No | Pencarian berdasarkan nama/deskripsi |

**Inertia Props**:
```javascript
{
  products: ProductResource[],      // Daftar produk aktif
  categories: CategoryResource[],   // Daftar kategori dengan product_count
  selectedCategory: number | null,  // Category ID yang dipilih
  searchQuery: string | null        // Query pencarian
}
```

**Example URL**:
```
GET /?category=1&search=nasi
```

---

#### GET `/products/{product:slug}`
Menampilkan detail produk.

**Controller**: `ProductController@show`  
**Route Name**: `products.show`

**URL Parameters**:
| Parameter | Type | Description |
|-----------|------|-------------|
| `product` | string | Product slug (URL-friendly) |

**Inertia Props**:
```javascript
{
  product: ProductResource,         // Detail produk
  relatedProducts: ProductResource[] // Produk terkait (max 4)
}
```

**Example URL**:
```
GET /products/nasi-goreng-spesial
```

---

### Shopping Cart

#### GET `/cart`
Menampilkan halaman keranjang belanja.

**Controller**: `CartController@show`  
**Route Name**: `cart.show`

**Inertia Props**:
```javascript
{
  cart: {
    id: number,
    items: CartItemResource[],
    total_items: number,
    subtotal: number,
    formatted_subtotal: string
  }
}
```

---

#### POST `/cart`
Menambahkan produk ke keranjang.

**Controller**: `CartController@store`  
**Route Name**: `cart.store`

**Request Body**:
```json
{
  "product_id": 1,
  "quantity": 2
}
```

**Validation**:
| Field | Rules |
|-------|-------|
| `product_id` | required, exists:products,id |
| `quantity` | required, integer, min:1 |

**Response**: Redirect back dengan flash message

---

#### PATCH `/cart/{cartItem}`
Mengupdate quantity item di keranjang.

**Controller**: `CartController@update`  
**Route Name**: `cart.update`

**Request Body**:
```json
{
  "quantity": 3
}
```

**Validation**:
| Field | Rules |
|-------|-------|
| `quantity` | required, integer, min:1 |

---

#### DELETE `/cart/{cartItem}`
Menghapus item dari keranjang.

**Controller**: `CartController@destroy`  
**Route Name**: `cart.destroy`

---

### Checkout

#### GET `/checkout`
Menampilkan halaman checkout.

**Controller**: `CheckoutController@show`  
**Route Name**: `checkout.show`

**Inertia Props**:
```javascript
{
  cart: CartData,
  delivery_fee: number,
  store_settings: {
    store_name: string,
    whatsapp_number: string
  },
  user: UserData | null  // Data user jika authenticated
}
```

---

#### POST `/checkout`
Memproses checkout dan membuat order.

**Controller**: `CheckoutController@store`  
**Route Name**: `checkout.store`

**Request Body**:
```json
{
  "customer_name": "John Doe",
  "customer_phone": "081234567890",
  "customer_address": "Jl. Contoh No. 123",
  "notes": "Tidak pedas"
}
```

**Validation**:
| Field | Rules |
|-------|-------|
| `customer_name` | required, string, max:255 |
| `customer_phone` | required, string, max:20 |
| `customer_address` | nullable, string |
| `notes` | nullable, string |

**Response**: Redirect ke checkout success dengan WhatsApp URL

---

#### GET `/checkout/success/{order}`
Menampilkan halaman sukses setelah checkout.

**Controller**: `CheckoutController@success`  
**Route Name**: `checkout.success`

**Inertia Props**:
```javascript
{
  order: OrderData,
  whatsapp_url: string  // URL untuk konfirmasi via WhatsApp
}
```

---

### Account (Authenticated Users)

#### GET `/account`
Menampilkan halaman akun pengguna.

**Controller**: `AccountController@index`  
**Route Name**: `account.index`  
**Middleware**: `auth`

---

#### GET `/account/orders`
Menampilkan daftar pesanan pengguna.

**Controller**: `AccountController@orders`  
**Route Name**: `account.orders`  
**Middleware**: `auth`

**Inertia Props**:
```javascript
{
  orders: OrderResource[]  // Daftar order user
}
```

---

#### GET `/account/orders/{order}`
Menampilkan detail pesanan.

**Controller**: `AccountController@orderShow`  
**Route Name**: `account.orders.show`  
**Middleware**: `auth`

---

## Admin Routes

Semua admin routes memerlukan autentikasi dan role admin.

**Prefix**: `/admin`  
**Middleware**: `auth`, `admin`

### Dashboard

#### GET `/admin/dashboard`
Menampilkan dashboard admin dengan statistik.

**Controller**: `Admin\DashboardController@index`  
**Route Name**: `admin.dashboard`

**Inertia Props**:
```javascript
{
  statistics: {
    total_orders: number,
    pending_orders: number,
    total_revenue: number,
    total_products: number
  },
  recent_orders: OrderResource[],
  revenue_chart: ChartData
}
```

---

### Category Management

#### GET `/admin/categories`
Daftar semua kategori.

**Controller**: `Admin\CategoryController@index`  
**Route Name**: `admin.categories.index`

---

#### GET `/admin/categories/create`
Form tambah kategori baru.

**Controller**: `Admin\CategoryController@create`  
**Route Name**: `admin.categories.create`

---

#### POST `/admin/categories`
Menyimpan kategori baru.

**Controller**: `Admin\CategoryController@store`  
**Route Name**: `admin.categories.store`

**Request Body (multipart/form-data)**:
```json
{
  "name": "Minuman",
  "description": "Berbagai minuman segar",
  "image": File,
  "is_active": true,
  "sort_order": 1
}
```

---

#### GET `/admin/categories/{category}`
Detail kategori.

**Controller**: `Admin\CategoryController@show`  
**Route Name**: `admin.categories.show`

---

#### GET `/admin/categories/{category}/edit`
Form edit kategori.

**Controller**: `Admin\CategoryController@edit`  
**Route Name**: `admin.categories.edit`

---

#### PUT|PATCH `/admin/categories/{category}`
Update kategori.

**Controller**: `Admin\CategoryController@update`  
**Route Name**: `admin.categories.update`

---

#### DELETE `/admin/categories/{category}`
Hapus kategori.

**Controller**: `Admin\CategoryController@destroy`  
**Route Name**: `admin.categories.destroy`

---

### Product Management

#### GET `/admin/products`
Daftar semua produk dengan pagination dan filter.

**Controller**: `Admin\ProductController@index`  
**Route Name**: `admin.products.index`

**Query Parameters**:
| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Pencarian nama produk |
| `category` | integer | Filter by category_id |
| `status` | string | Filter: active/inactive |
| `per_page` | integer | Items per page (default: 10) |

---

#### GET `/admin/products/create`
Form tambah produk baru.

**Controller**: `Admin\ProductController@create`  
**Route Name**: `admin.products.create`

---

#### POST `/admin/products`
Menyimpan produk baru.

**Controller**: `Admin\ProductController@store`  
**Route Name**: `admin.products.store`

**Request Body (multipart/form-data)**:
```json
{
  "category_id": 1,
  "name": "Nasi Goreng Spesial",
  "description": "Nasi goreng dengan telur dan ayam",
  "price": 25000,
  "stock": 100,
  "image": File,
  "is_active": true,
  "is_featured": false
}
```

**Validation**:
| Field | Rules |
|-------|-------|
| `category_id` | required, exists:categories,id |
| `name` | required, string, max:255 |
| `description` | nullable, string |
| `price` | required, numeric, min:0 |
| `stock` | required, integer, min:0 |
| `image` | nullable, image, max:2048 |
| `is_active` | boolean |
| `is_featured` | boolean |

---

#### GET `/admin/products/{product}`
Detail produk.

**Controller**: `Admin\ProductController@show`  
**Route Name**: `admin.products.show`

---

#### GET `/admin/products/{product}/edit`
Form edit produk.

**Controller**: `Admin\ProductController@edit`  
**Route Name**: `admin.products.edit`

---

#### PUT|PATCH `/admin/products/{product}`
Update produk.

**Controller**: `Admin\ProductController@update`  
**Route Name**: `admin.products.update`

---

#### DELETE `/admin/products/{product}`
Hapus produk.

**Controller**: `Admin\ProductController@destroy`  
**Route Name**: `admin.products.destroy`

---

### Order Management

#### GET `/admin/orders`
Daftar semua pesanan dengan filter.

**Controller**: `Admin\OrderController@index`  
**Route Name**: `admin.orders.index`

**Query Parameters**:
| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Cari order_number/customer_name/phone |
| `status` | string | Filter by status |
| `start_date` | date | Filter dari tanggal |
| `end_date` | date | Filter sampai tanggal |
| `per_page` | integer | Items per page |

**Inertia Props**:
```javascript
{
  orders: PaginatedOrders,
  statuses: {
    pending: "Menunggu",
    confirmed: "Dikonfirmasi",
    preparing: "Diproses",
    ready: "Siap",
    delivered: "Dikirim",
    cancelled: "Dibatalkan"
  },
  filters: AppliedFilters,
  status_counts: StatusCounts[]
}
```

---

#### GET `/admin/orders/{order}`
Detail pesanan.

**Controller**: `Admin\OrderController@show`  
**Route Name**: `admin.orders.show`

**Inertia Props**:
```javascript
{
  order: {
    id: number,
    order_number: string,
    customer_name: string,
    customer_phone: string,
    customer_address: string | null,
    notes: string | null,
    items: OrderItemResource[],
    subtotal: number,
    delivery_fee: number,
    total: number,
    status: string,
    status_label: string,
    timestamps: {
      created_at: string,
      confirmed_at: string | null,
      preparing_at: string | null,
      ready_at: string | null,
      delivered_at: string | null,
      cancelled_at: string | null
    },
    whatsapp_url: string | null  // URL untuk notifikasi customer
  },
  statuses: StatusLabels
}
```

---

#### PATCH `/admin/orders/{order}/status`
Update status pesanan.

**Controller**: `Admin\OrderController@updateStatus`  
**Route Name**: `admin.orders.updateStatus`

**Request Body**:
```json
{
  "status": "confirmed",
  "cancellation_reason": null  // Required jika status = cancelled
}
```

**Response**: Redirect back dengan flash message dan WhatsApp URL untuk notifikasi

---

### Store Settings

#### GET `/admin/settings`
Halaman pengaturan toko.

**Controller**: `Admin\StoreSettingController@index`  
**Route Name**: `admin.settings.index`

**Inertia Props**:
```javascript
{
  settings: {
    store_name: string,
    store_address: string,
    store_description: string,
    whatsapp_number: string,
    delivery_fee: number,
    logo_url: string | null,
    operational_hours: object
  }
}
```

---

#### PATCH `/admin/settings`
Update pengaturan toko.

**Controller**: `Admin\StoreSettingController@update`  
**Route Name**: `admin.settings.update`

**Request Body**:
```json
{
  "store_name": "Simple Store",
  "store_address": "Jl. Contoh No. 123",
  "store_description": "Toko online terpercaya",
  "whatsapp_number": "6281234567890",
  "delivery_fee": 10000,
  "operational_hours": {
    "monday": { "open": "08:00", "close": "21:00" },
    "tuesday": { "open": "08:00", "close": "21:00" }
  }
}
```

---

#### POST `/admin/settings/upload-logo`
Upload logo toko.

**Controller**: `Admin\StoreSettingController@uploadLogo`  
**Route Name**: `admin.settings.uploadLogo`

**Request Body (multipart/form-data)**:
| Field | Type | Rules |
|-------|------|-------|
| `logo` | File | required, image, max:2048 |

---

## Internal API Endpoints

API endpoints untuk operasi AJAX di admin panel.

### Order API

#### GET `/admin/api/orders/pending`
Mendapatkan daftar order pending untuk real-time notification.

**Controller**: `Api\OrderApiController@pending`  
**Route Name**: `admin.api.orders.pending`

**Response**:
```json
{
  "orders": [
    {
      "id": 1,
      "order_number": "ORD-20251211-ABCDE",
      "customer_name": "John Doe",
      "total": 75000,
      "status": "pending",
      "created_at": "2025-12-11T10:30:00.000000Z",
      "waiting_minutes": 15
    }
  ],
  "count": 1
}
```

---

#### PATCH `/admin/api/orders/{order}/quick-status`
Quick update status order tanpa reload halaman.

**Controller**: `Api\OrderApiController@quickStatus`  
**Route Name**: `admin.api.orders.quickStatus`

**Request Body**:
```json
{
  "status": "confirmed"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Status pesanan berhasil diperbarui",
  "order": { ... },
  "whatsapp_url": "https://wa.me/..."
}
```

---

### Password Verification

#### POST `/admin/api/verify-password`
Verifikasi password untuk sensitive actions.

**Controller**: `Admin\PasswordVerificationController@verify`  
**Route Name**: `admin.api.verifyPassword`

**Request Body**:
```json
{
  "password": "current_password"
}
```

**Response**:
```json
{
  "verified": true
}
```

---

## Authentication Routes (Laravel Fortify)

### Login/Logout

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/login` | `login` | Halaman login |
| POST | `/login` | `login.store` | Proses login |
| POST | `/logout` | `logout` | Proses logout |

### Registration

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/register` | `register` | Halaman registrasi |
| POST | `/register` | `register.store` | Proses registrasi |

### Password Reset

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/forgot-password` | `password.request` | Form forgot password |
| POST | `/forgot-password` | `password.email` | Kirim reset email |
| GET | `/reset-password/{token}` | `password.reset` | Form reset password |
| POST | `/reset-password` | `password.update` | Update password |

### Two-Factor Authentication

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/two-factor-challenge` | `two-factor.login` | Form 2FA challenge |
| POST | `/two-factor-challenge` | `two-factor.login.store` | Verify 2FA code |
| POST | `/user/two-factor-authentication` | `two-factor.enable` | Enable 2FA |
| DELETE | `/user/two-factor-authentication` | `two-factor.disable` | Disable 2FA |
| GET | `/user/two-factor-qr-code` | `two-factor.qr-code` | Get QR code |
| GET | `/user/two-factor-recovery-codes` | `two-factor.recovery-codes` | Get recovery codes |

### Email Verification

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/email/verify` | `verification.notice` | Notice page |
| GET | `/email/verify/{id}/{hash}` | `verification.verify` | Verify email |
| POST | `/email/verification-notification` | `verification.send` | Resend email |

---

## User Settings Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/settings/profile` | `profile.edit` | Edit profile |
| PATCH | `/settings/profile` | `profile.update` | Update profile |
| DELETE | `/settings/profile` | `profile.destroy` | Delete account |
| GET | `/settings/password` | `user-password.edit` | Edit password |
| PUT | `/settings/password` | `user-password.update` | Update password |
| GET | `/settings/two-factor` | `two-factor.show` | 2FA settings |
| GET | `/settings/appearance` | `appearance.edit` | Appearance settings |

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "customer_name": ["Nama customer wajib diisi."],
    "customer_phone": ["Nomor telepon tidak valid."]
  }
}
```

### Not Found (404)
```json
{
  "message": "Product not found."
}
```

### Unauthorized (401)
Redirect ke halaman login.

### Forbidden (403)
```json
{
  "message": "You do not have permission to access this resource."
}
```

### Server Error (500)
```json
{
  "message": "Internal server error."
}
```

---

## Wayfinder Integration

Aplikasi menggunakan Laravel Wayfinder untuk type-safe routes di frontend.

### Usage Example (Vue.js)

```typescript
// Import generated route functions
import { show } from '@/actions/App/Http/Controllers/ProductController'
import { store } from '@/actions/App/Http/Controllers/CartController'

// Get URL
const productUrl = show.url('nasi-goreng-spesial')
// Result: "/products/nasi-goreng-spesial"

// Use with Inertia router
router.visit(show.url(product.slug))

// Use with Form component
<Form v-bind="store.form()">
  <input name="product_id" :value="product.id" />
  <input name="quantity" value="1" />
  <button type="submit">Add to Cart</button>
</Form>
```

---

## Rate Limiting

Rate limiting dikonfigurasi di `bootstrap/app.php`:

| Route Group | Limit | Window |
|-------------|-------|--------|
| API | 60 requests | 1 minute |
| Auth (login) | 5 attempts | 1 minute |

---

## Related Documentation

- [01_System_Architecture.md](01_System_Architecture.md) - Arsitektur sistem
- [02_Database_Schema.md](02_Database_Schema.md) - Skema database
- [04_File_Structure.md](04_File_Structure.md) - Struktur file
