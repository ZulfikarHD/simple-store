# Alur Pengguna (User Flows)

Dokumentasi ini menjelaskan alur penggunaan aplikasi Simple Store secara visual dan detail, mencakup flow untuk pelanggan (customer) dan administrator (admin).

---

## Flow 1: Registrasi dan Login

### 1.1 Flow Registrasi

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Halaman   │───>│    Klik     │───>│  Isi Form   │───>│   Submit    │
│    Utama    │    │   Daftar    │    │ Registrasi  │    │   Daftar    │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
                                                                │
       ┌────────────────────────────────────────────────────────┘
       ▼
┌─────────────┐    ┌─────────────┐
│   Validasi  │───>│  Redirect   │
│    Data     │    │  ke Home    │
└─────────────┘    └─────────────┘
```

**Detail Langkah**:
1. User mengakses halaman utama (`/`)
2. User klik tombol "Daftar" di header atau menu mobile
3. System menampilkan halaman registrasi (`/register`)
4. User mengisi form:
   - Nama Lengkap
   - Email
   - Password
   - Konfirmasi Password
5. User klik tombol "Daftar"
6. System memvalidasi data:
   - Email belum terdaftar
   - Password minimal 8 karakter
   - Password dan konfirmasi cocok
7. Jika valid: Account dibuat, user auto-login, redirect ke halaman utama
8. Jika invalid: Tampilkan error di form

---

### 1.2 Flow Login

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Halaman   │───>│    Klik     │───>│  Isi Form   │───>│   Submit    │
│    Utama    │    │    Masuk    │    │    Login    │    │    Login    │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
                                                                │
       ┌────────────────────────────────────────────────────────┘
       ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Validasi  │───>│  2FA Check  │───>│  Redirect   │
│ Credentials │    │  (if enabled)│   │  ke Target  │
└─────────────┘    └─────────────┘    └─────────────┘
```

**Detail Langkah**:
1. User mengakses halaman utama atau protected page
2. User klik tombol "Masuk"
3. System menampilkan halaman login (`/login`)
4. User mengisi Email dan Password
5. User klik tombol "Masuk"
6. System memvalidasi credentials
7. Jika 2FA enabled: Tampilkan halaman verifikasi 2FA
8. Jika valid: Redirect ke halaman sebelumnya atau dashboard
9. Jika invalid: Tampilkan pesan error

---

## Flow 2: Proses Belanja (Shopping Flow)

### 2.1 Flow Lengkap Customer Journey

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           CUSTOMER JOURNEY                               │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Browse    │───>│   Detail    │───>│  Add to     │───>│    Cart     │
│   Katalog   │    │   Produk    │    │   Cart      │    │    Page     │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
      │                                                         │
      │  ┌──────── Loop: Tambah produk lain ────────────────────┤
      │  │                                                      │
      │  ▼                                                      ▼
      └──┘                                              ┌─────────────┐
                                                        │  Checkout   │
                                                        │    Form     │
                                                        └─────────────┘
                                                               │
       ┌───────────────────────────────────────────────────────┘
       ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Submit    │───>│   Success   │───>│  WhatsApp   │
│   Order     │    │    Page     │    │  Confirm    │
└─────────────┘    └─────────────┘    └─────────────┘
```

### 2.2 Detail Flow Add to Cart

```
┌─────────────┐
│   Katalog   │
│    Home     │
└──────┬──────┘
       │ Tap produk
       ▼
┌─────────────┐
│   Detail    │
│   Produk    │
└──────┬──────┘
       │ Set quantity
       │ Tap "Tambah ke Keranjang"
       ▼
┌─────────────┐
│  Validasi   │
│    Stok     │
└──────┬──────┘
       │
       ├──── Stok tersedia ────┐
       │                       │
       │                       ▼
       │               ┌─────────────┐
       │               │  Add Item   │
       │               │  to Cart    │
       │               └──────┬──────┘
       │                      │
       │                      ▼
       │               ┌─────────────┐
       │               │   Success   │
       │               │  Animation  │
       │               │  ✓ Added!   │
       │               └─────────────┘
       │
       └──── Stok habis ───────┐
                               │
                               ▼
                       ┌─────────────┐
                       │   Button    │
                       │  Disabled   │
                       │ "Stok Habis"│
                       └─────────────┘
```

### 2.3 Detail Flow Checkout

```
┌─────────────┐
│    Cart     │
│    Page     │
└──────┬──────┘
       │ Tap "Lanjut ke Checkout"
       ▼
┌─────────────┐    ┌─────────────┐
│   Check     │───>│  Redirect   │ Cart kosong
│ Cart Empty? │ Ya │   ke Home   │
└──────┬──────┘    └─────────────┘
       │ Tidak
       ▼
┌─────────────┐
│  Checkout   │
│    Form     │
└──────┬──────┘
       │
       │ Isi form:
       │ - Nama Lengkap*
       │ - No. Telepon*
       │ - Alamat
       │ - Catatan
       │
       ▼
┌─────────────┐
│   Submit    │
│   Form      │
└──────┬──────┘
       │
       ├──── Validation Error ────┐
       │                          │
       │                          ▼
       │                  ┌─────────────┐
       │                  │   Shake     │
       │                  │  Animation  │
       │                  │ Show Errors │
       │                  └──────┬──────┘
       │                         │ Fix errors
       │                         └─────────────┐
       │                                       │
       │◄──────────────────────────────────────┘
       │
       ▼ Validation Success
┌─────────────┐
│   Create    │
│   Order     │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Clear     │
│   Cart      │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  Redirect   │
│  to Success │
└─────────────┘
```

### 2.4 Detail Flow WhatsApp Confirmation

```
┌─────────────┐
│   Success   │
│    Page     │
└──────┬──────┘
       │
       │ Display:
       │ - Order Number
       │ - Customer Info
       │ - Order Details
       │ - Total
       │
       ▼
┌─────────────┐
│ Tap "Konfirmasi│
│ via WhatsApp"│
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Generate  │
│  WhatsApp   │
│    URL      │
└──────┬──────┘
       │
       │ URL includes:
       │ - Store phone number
       │ - Pre-filled message:
       │   • Order number
       │   • Customer name
       │   • Items list
       │   • Total amount
       │
       ▼
┌─────────────┐
│ Open WhatsApp│
│   App/Web   │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│   Send      │
│   Message   │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────┐
│     ✓ Order Confirmed!          │
│   Toko akan memproses pesanan   │
└─────────────────────────────────┘
```

---

## Flow 3: Manajemen Pesanan (Admin)

### 3.1 Order Management Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         ADMIN ORDER MANAGEMENT                           │
└─────────────────────────────────────────────────────────────────────────┘

                    ┌─────────────┐
                    │   New Order │
                    │  (Pending)  │
                    └──────┬──────┘
                           │
        ┌──────────────────┴──────────────────┐
        │                                      │
        ▼                                      ▼
┌─────────────┐                        ┌─────────────┐
│   Confirm   │                        │   Cancel    │
│    Order    │                        │    Order    │
└──────┬──────┘                        └──────┬──────┘
       │                                      │
       ▼                                      ▼
┌─────────────┐                        ┌─────────────┐
│  Confirmed  │                        │  Cancelled  │
└──────┬──────┘                        └─────────────┘
       │
       ▼
┌─────────────┐
│  Preparing  │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│    Ready    │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  Delivered  │
│     ✓       │
└─────────────┘
```

### 3.2 Detail Admin Dashboard Flow

```
┌─────────────┐
│   Admin     │
│   Login     │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  Dashboard  │
│   Page      │
└──────┬──────┘
       │
       │ Auto-load:
       │ - Today's orders count
       │ - Pending orders count
       │ - Total sales
       │ - Active products
       │ - Recent orders (5)
       │ - Status breakdown
       │
       ├──────────────────────────────────────┐
       │                                      │
       │ Watch pending orders                 │
       │ (Poll every 30 seconds)              │
       │                                      │
       │          ┌─────────────┐             │
       │          │  New Order  │             │
       │          │  Detected!  │             │
       │          └──────┬──────┘             │
       │                 │                    │
       │                 ▼                    │
       │         ┌─────────────┐              │
       │         │  Browser    │              │
       │         │Notification │              │
       │         └─────────────┘              │
       │                                      │
       └──────────────────────────────────────┘
       │
       │ Tap on order
       ▼
┌─────────────┐
│   Order     │
│   Detail    │
└──────┬──────┘
       │
       │ Actions:
       │ - View customer info
       │ - View items
       │ - Update status
       │
       ▼
┌─────────────┐
│   Update    │
│   Status    │
└─────────────┘
```

---

## Flow 4: Manajemen Produk (Admin)

### 4.1 CRUD Product Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         PRODUCT MANAGEMENT                               │
└─────────────────────────────────────────────────────────────────────────┘

                    ┌─────────────┐
                    │   Product   │
                    │    List     │
                    └──────┬──────┘
                           │
        ┌──────────┬───────┴───────┬──────────┐
        │          │               │          │
        ▼          ▼               ▼          ▼
┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐
│   CREATE    │ │    READ     │ │   UPDATE    │ │   DELETE    │
│ Add Product │ │View Product │ │Edit Product │ │Remove Product│
└──────┬──────┘ └─────────────┘ └──────┬──────┘ └──────┬──────┘
       │                               │               │
       ▼                               ▼               ▼
┌─────────────┐                ┌─────────────┐ ┌─────────────┐
│  Fill Form  │                │  Fill Form  │ │  Confirm    │
│ - Name      │                │ - Edit name │ │  Delete?    │
│ - Category  │                │ - Edit price│ └──────┬──────┘
│ - Price     │                │ - Edit stock│        │
│ - Stock     │                │ - etc...    │        │ Yes
│ - Description│               └──────┬──────┘        │
│ - Image     │                       │               ▼
│ - Active    │                       │        ┌─────────────┐
│ - Featured  │                       │        │   Remove    │
└──────┬──────┘                       │        │   Product   │
       │                              │        └─────────────┘
       ▼                              ▼
┌─────────────┐                ┌─────────────┐
│   Validate  │                │   Validate  │
│    Data     │                │    Data     │
└──────┬──────┘                └──────┬──────┘
       │                              │
       ▼                              ▼
┌─────────────┐                ┌─────────────┐
│    Save     │                │    Save     │
│   Product   │                │   Changes   │
└──────┬──────┘                └──────┬──────┘
       │                              │
       ▼                              ▼
┌─────────────┐                ┌─────────────┐
│  Redirect   │                │  Redirect   │
│   to List   │                │   to List   │
└─────────────┘                └─────────────┘
```

---

## Flow 5: Guest vs Authenticated User

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      GUEST vs AUTHENTICATED FLOW                         │
└─────────────────────────────────────────────────────────────────────────┘

                         ┌─────────────┐
                         │   User      │
                         │  Arrives    │
                         └──────┬──────┘
                                │
            ┌───────────────────┴───────────────────┐
            │                                       │
            ▼                                       ▼
    ┌─────────────┐                         ┌─────────────┐
    │    GUEST    │                         │ AUTHENTICATED│
    │    USER     │                         │     USER     │
    └──────┬──────┘                         └──────┬──────┘
           │                                       │
           │ ✓ Browse products                     │ ✓ All guest features
           │ ✓ View details                        │ ✓ Order history
           │ ✓ Add to cart (session)               │ ✓ Account settings
           │ ✓ Checkout                            │ ✓ 2FA security
           │ ✗ Order history                       │ ✓ Profile management
           │ ✗ Account settings                    │
           │                                       │
           │                                       │
           ▼                                       ▼
    ┌─────────────┐                         ┌─────────────┐
    │ Cart stored │                         │ Cart stored │
    │ in Session  │                         │  by User ID │
    └─────────────┘                         └─────────────┘
           │                                       │
           │ On browser close:                     │ Persists across:
           │ Cart may be lost                      │ - Sessions
           │                                       │ - Devices (via login)
           │                                       │
           └───────────────────┬───────────────────┘
                               │
                               ▼
                       ┌─────────────┐
                       │   CHECKOUT  │
                       │  (Same Flow)│
                       └─────────────┘
```

---

## Diagram Visual

Untuk diagram visual dalam format gambar, lihat folder:
- `04_TECHNICAL_DOCUMENTATION/diagrams/user_flow_diagram.png`
- `04_TECHNICAL_DOCUMENTATION/diagrams/order_state_diagram.png`
- `04_TECHNICAL_DOCUMENTATION/diagrams/system_architecture.png`

---

## Quick Reference: Status Transitions

### Order Status State Machine

```
                    ┌──────────────────────────────────────┐
                    │                                      │
                    ▼                                      │
┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐  │  ┌─────────┐
│ Pending │───>│Confirmed│───>│Preparing│───>│  Ready  │──┴─>│Delivered│
└────┬────┘    └─────────┘    └─────────┘    └─────────┘     └─────────┘
     │
     └────────────────────────────────────────────────────────┐
                                                              │
                                                              ▼
                                                        ┌─────────┐
                                                        │Cancelled│
                                                        └─────────┘
```

### Stock Status Logic

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        STOCK STATUS LOGIC                                │
└─────────────────────────────────────────────────────────────────────────┘

                         ┌─────────────┐
                         │  Get Stock  │
                         │    Count    │
                         └──────┬──────┘
                                │
        ┌───────────────────────┼───────────────────────┐
        │                       │                       │
        ▼                       ▼                       ▼
┌─────────────┐         ┌─────────────┐         ┌─────────────┐
│  Stock > 5  │         │ Stock 1-5   │         │  Stock = 0  │
│             │         │             │         │             │
│  IN_STOCK   │         │  LOW_STOCK  │         │ OUT_OF_STOCK│
│   (Green)   │         │   (Amber)   │         │    (Red)    │
└─────────────┘         └─────────────┘         └─────────────┘
       │                       │                       │
       │ Can add to cart       │ Can add to cart       │ Cannot add
       │                       │ Shows warning         │ Button disabled
       ▼                       ▼                       ▼
┌─────────────┐         ┌─────────────┐         ┌─────────────┐
│  "Tersedia" │         │"Stok Terbatas"│       │ "Stok Habis"│
└─────────────┘         └─────────────┘         └─────────────┘
```
