# Skema Database Simple Store

**Penulis**: Zulfikar Hidayatullah  
**Versi**: 1.0  
**Terakhir Diperbarui**: Desember 2025

---

## Overview

Dokumentasi lengkap tentang struktur database aplikasi Simple Store, yaitu: tabel-tabel utama, relasi antar tabel, indexes, dan foreign key constraints yang digunakan untuk menjaga integritas data.

## Database Configuration

| Parameter | Value |
|-----------|-------|
| **Driver** | SQLite |
| **File Location** | `database/database.sqlite` |
| **Character Set** | UTF-8 |

---

## Entity Relationship Diagram (ERD)

```
┌─────────────────┐         ┌─────────────────┐
│     users       │         │   categories    │
├─────────────────┤         ├─────────────────┤
│ id (PK)         │         │ id (PK)         │
│ name            │         │ name            │
│ email (UNIQUE)  │         │ slug (UNIQUE)   │
│ password        │         │ description     │
│ role            │         │ image           │
│ phone           │         │ is_active       │
│ address         │         │ sort_order      │
│ two_factor_*    │         │ timestamps      │
│ timestamps      │         └────────┬────────┘
└────────┬────────┘                  │
         │                           │
         │ 1:N                       │ 1:N
         │                           │
         ▼                           ▼
┌─────────────────┐         ┌─────────────────┐
│     orders      │         │    products     │
├─────────────────┤         ├─────────────────┤
│ id (PK)         │◄────┐   │ id (PK)         │
│ order_number    │     │   │ category_id(FK) │─────┘
│ user_id (FK)    │─────┘   │ name            │
│ customer_*      │         │ slug (UNIQUE)   │
│ subtotal        │         │ description     │
│ delivery_fee    │         │ price           │
│ total           │         │ image           │
│ status          │         │ stock           │
│ *_at timestamps │         │ is_active       │
│ timestamps      │         │ is_featured     │
└────────┬────────┘         │ timestamps      │
         │                  └────────┬────────┘
         │ 1:N                       │
         │                           │ 1:N
         ▼                           │
┌─────────────────┐                  │
│   order_items   │                  │
├─────────────────┤                  │
│ id (PK)         │                  │
│ order_id (FK)   │◄─────────────────┤
│ product_id (FK) │◄─────────────────┘
│ product_name    │
│ product_price   │
│ quantity        │
│ subtotal        │
│ notes           │
│ timestamps      │
└─────────────────┘

┌─────────────────┐         ┌─────────────────┐
│     carts       │         │  store_settings │
├─────────────────┤         ├─────────────────┤
│ id (PK)         │         │ id (PK)         │
│ session_id      │         │ key (UNIQUE)    │
│ user_id (FK)    │         │ value           │
│ timestamps      │         │ type            │
└────────┬────────┘         │ group           │
         │                  │ timestamps      │
         │ 1:N              └─────────────────┘
         │
         ▼
┌─────────────────┐
│   cart_items    │
├─────────────────┤
│ id (PK)         │
│ cart_id (FK)    │
│ product_id (FK) │
│ quantity        │
│ timestamps      │
└─────────────────┘
```

---

## Tables

### 1. users

Tabel untuk menyimpan data pengguna aplikasi dengan dukungan role-based access control dan two-factor authentication.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | integer | NO | AUTO_INCREMENT | Primary key |
| `name` | varchar(255) | NO | - | Nama lengkap pengguna |
| `email` | varchar(255) | NO | - | Email (unique) untuk login |
| `email_verified_at` | datetime | YES | NULL | Waktu verifikasi email |
| `password` | varchar(255) | NO | - | Password ter-hash (bcrypt) |
| `remember_token` | varchar(100) | YES | NULL | Token untuk "remember me" |
| `two_factor_secret` | text | YES | NULL | Secret key untuk 2FA |
| `two_factor_recovery_codes` | text | YES | NULL | Recovery codes untuk 2FA |
| `two_factor_confirmed_at` | datetime | YES | NULL | Waktu konfirmasi 2FA |
| `role` | varchar(255) | YES | 'customer' | Role user: admin/customer |
| `phone` | varchar(255) | YES | NULL | Nomor telepon |
| `address` | text | YES | NULL | Alamat lengkap |
| `created_at` | datetime | YES | NULL | Waktu pembuatan record |
| `updated_at` | datetime | YES | NULL | Waktu update terakhir |

**Indexes**:
- `PRIMARY KEY`: id
- `UNIQUE`: email

**Relationships**:
- `HasMany` → orders (user dapat memiliki banyak pesanan)

---

### 2. categories

Tabel untuk mengorganisir produk berdasarkan kategori dengan fitur ordering dan status aktif.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | integer | NO | AUTO_INCREMENT | Primary key |
| `name` | varchar(255) | NO | - | Nama kategori |
| `slug` | varchar(255) | NO | - | URL-friendly slug (unique) |
| `description` | text | YES | NULL | Deskripsi kategori |
| `image` | varchar(255) | YES | NULL | Path gambar kategori |
| `is_active` | tinyint | NO | 1 | Status aktif kategori |
| `sort_order` | integer | NO | 0 | Urutan tampilan |
| `created_at` | datetime | YES | NULL | Waktu pembuatan record |
| `updated_at` | datetime | YES | NULL | Waktu update terakhir |

**Indexes**:
- `PRIMARY KEY`: id
- `UNIQUE`: slug
- `INDEX`: is_active
- `INDEX`: sort_order

**Relationships**:
- `HasMany` → products (kategori memiliki banyak produk)

---

### 3. products

Tabel utama untuk menyimpan data produk dengan informasi harga, stok, dan status ketersediaan.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | integer | NO | AUTO_INCREMENT | Primary key |
| `category_id` | integer | NO | - | Foreign key ke categories |
| `name` | varchar(255) | NO | - | Nama produk |
| `slug` | varchar(255) | NO | - | URL-friendly slug (unique) |
| `description` | text | YES | NULL | Deskripsi produk |
| `price` | numeric | NO | - | Harga dalam Rupiah |
| `image` | varchar(255) | YES | NULL | Path gambar produk |
| `stock` | integer | NO | 0 | Jumlah stok tersedia |
| `is_active` | tinyint | NO | 1 | Status aktif produk |
| `is_featured` | tinyint | NO | 0 | Produk unggulan |
| `created_at` | datetime | YES | NULL | Waktu pembuatan record |
| `updated_at` | datetime | YES | NULL | Waktu update terakhir |

**Indexes**:
- `PRIMARY KEY`: id
- `UNIQUE`: slug
- `INDEX`: is_active
- `INDEX`: is_featured
- `INDEX`: category_id, is_active (composite)

**Foreign Keys**:
- `category_id` → categories(id) ON DELETE CASCADE

**Relationships**:
- `BelongsTo` → category
- `HasMany` → orderItems, cartItems

---

### 4. orders

Tabel untuk menyimpan data pesanan dengan status tracking dan informasi customer.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | integer | NO | AUTO_INCREMENT | Primary key |
| `order_number` | varchar(255) | NO | - | Nomor order (unique, auto-generated) |
| `user_id` | integer | YES | NULL | Foreign key ke users (nullable untuk guest) |
| `customer_name` | varchar(255) | NO | - | Nama customer |
| `customer_phone` | varchar(255) | NO | - | Nomor telepon customer |
| `customer_address` | text | YES | NULL | Alamat pengiriman |
| `notes` | text | YES | NULL | Catatan tambahan |
| `subtotal` | numeric | NO | - | Total sebelum ongkir |
| `delivery_fee` | numeric | NO | 0 | Biaya pengiriman |
| `total` | numeric | NO | - | Total akhir (subtotal + ongkir) |
| `status` | varchar(255) | NO | 'pending' | Status pesanan |
| `confirmed_at` | datetime | YES | NULL | Waktu dikonfirmasi |
| `preparing_at` | datetime | YES | NULL | Waktu mulai diproses |
| `ready_at` | datetime | YES | NULL | Waktu siap |
| `delivered_at` | datetime | YES | NULL | Waktu dikirim/selesai |
| `cancelled_at` | datetime | YES | NULL | Waktu dibatalkan |
| `cancellation_reason` | text | YES | NULL | Alasan pembatalan |
| `created_at` | datetime | YES | NULL | Waktu pembuatan record |
| `updated_at` | datetime | YES | NULL | Waktu update terakhir |

**Indexes**:
- `PRIMARY KEY`: id
- `UNIQUE`: order_number
- `INDEX`: status
- `INDEX`: customer_phone
- `INDEX`: status, created_at (composite)

**Foreign Keys**:
- `user_id` → users(id) ON DELETE SET NULL

**Order Status Flow**:
```
pending → confirmed → preparing → ready → delivered
    └──────────────────────────────────────→ cancelled
```

**Relationships**:
- `BelongsTo` → user
- `HasMany` → items (order_items)

---

### 5. order_items

Tabel untuk menyimpan detail item dalam pesanan dengan snapshot harga saat pembelian.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | integer | NO | AUTO_INCREMENT | Primary key |
| `order_id` | integer | NO | - | Foreign key ke orders |
| `product_id` | integer | NO | - | Foreign key ke products |
| `product_name` | varchar(255) | NO | - | Snapshot nama produk |
| `product_price` | numeric | NO | - | Snapshot harga saat beli |
| `quantity` | integer | NO | - | Jumlah item |
| `subtotal` | numeric | NO | - | Total (price × quantity) |
| `notes` | text | YES | NULL | Catatan per item |
| `created_at` | datetime | YES | NULL | Waktu pembuatan record |
| `updated_at` | datetime | YES | NULL | Waktu update terakhir |

**Indexes**:
- `PRIMARY KEY`: id
- `INDEX`: order_id, product_id (composite)

**Foreign Keys**:
- `order_id` → orders(id) ON DELETE CASCADE
- `product_id` → products(id) ON DELETE CASCADE

**Note**: `product_name` dan `product_price` disimpan sebagai snapshot untuk mempertahankan data historis meskipun produk asli diubah atau dihapus.

---

### 6. carts

Tabel untuk menyimpan keranjang belanja berbasis session.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | integer | NO | AUTO_INCREMENT | Primary key |
| `session_id` | varchar(255) | NO | - | Session ID (unique) |
| `user_id` | integer | YES | NULL | Foreign key ke users |
| `created_at` | datetime | YES | NULL | Waktu pembuatan record |
| `updated_at` | datetime | YES | NULL | Waktu update terakhir |

**Indexes**:
- `PRIMARY KEY`: id
- `UNIQUE`: session_id

**Foreign Keys**:
- `user_id` → users(id) ON DELETE SET NULL

**Relationships**:
- `BelongsTo` → user
- `HasMany` → items (cart_items)

---

### 7. cart_items

Tabel untuk menyimpan item dalam keranjang belanja.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | integer | NO | AUTO_INCREMENT | Primary key |
| `cart_id` | integer | NO | - | Foreign key ke carts |
| `product_id` | integer | NO | - | Foreign key ke products |
| `quantity` | integer | NO | 1 | Jumlah item |
| `created_at` | datetime | YES | NULL | Waktu pembuatan record |
| `updated_at` | datetime | YES | NULL | Waktu update terakhir |

**Indexes**:
- `PRIMARY KEY`: id
- `UNIQUE`: cart_id, product_id (composite - satu produk per cart)

**Foreign Keys**:
- `cart_id` → carts(id) ON DELETE CASCADE
- `product_id` → products(id) ON DELETE CASCADE

---

### 8. store_settings

Tabel untuk menyimpan konfigurasi toko dengan struktur key-value yang fleksibel.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | integer | NO | AUTO_INCREMENT | Primary key |
| `key` | varchar(255) | NO | - | Key setting (unique) |
| `value` | text | YES | NULL | Value setting |
| `type` | varchar(255) | NO | 'string' | Tipe data (string/text/integer/json/boolean) |
| `group` | varchar(255) | NO | 'general' | Group untuk pengelompokan |
| `created_at` | datetime | YES | NULL | Waktu pembuatan record |
| `updated_at` | datetime | YES | NULL | Waktu update terakhir |

**Indexes**:
- `PRIMARY KEY`: id
- `UNIQUE`: key
- `INDEX`: group

**Setting Groups**:
- `general`: Informasi umum toko (nama, alamat, dll)
- `contact`: Kontak dan WhatsApp
- `delivery`: Pengaturan pengiriman
- `operational`: Jam operasional

---

### 9. sessions

Tabel untuk menyimpan session data aplikasi.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| `id` | varchar(255) | NO | - | Session ID (primary key) |
| `user_id` | integer | YES | NULL | Foreign key ke users |
| `ip_address` | varchar(45) | YES | NULL | IP address client |
| `user_agent` | text | YES | NULL | Browser user agent |
| `payload` | text | NO | - | Session data (serialized) |
| `last_activity` | integer | NO | - | Unix timestamp aktivitas terakhir |

**Indexes**:
- `PRIMARY KEY`: id
- `INDEX`: user_id
- `INDEX`: last_activity

---

### 10. cache & cache_locks

Tabel untuk menyimpan cache data aplikasi (jika menggunakan database cache driver).

**cache**:
| Column | Type | Description |
|--------|------|-------------|
| `key` | varchar(255) | Cache key (primary key) |
| `value` | text | Cached value |
| `expiration` | integer | Unix timestamp expiration |

**cache_locks**:
| Column | Type | Description |
|--------|------|-------------|
| `key` | varchar(255) | Lock key (primary key) |
| `owner` | varchar(255) | Lock owner identifier |
| `expiration` | integer | Unix timestamp expiration |

---

### 11. jobs, job_batches, failed_jobs

Tabel untuk queue system Laravel.

**jobs**:
| Column | Type | Description |
|--------|------|-------------|
| `id` | integer | Primary key |
| `queue` | varchar(255) | Queue name |
| `payload` | text | Job payload (serialized) |
| `attempts` | integer | Attempt count |
| `reserved_at` | integer | Reserved timestamp |
| `available_at` | integer | Available timestamp |
| `created_at` | integer | Created timestamp |

**failed_jobs**:
| Column | Type | Description |
|--------|------|-------------|
| `id` | integer | Primary key |
| `uuid` | varchar(255) | Unique identifier |
| `connection` | text | Queue connection |
| `queue` | text | Queue name |
| `payload` | text | Job payload |
| `exception` | text | Exception message |
| `failed_at` | datetime | Failure timestamp |

---

## Migrations

Daftar migration files dalam urutan eksekusi:

```
database/migrations/
├── 0001_01_01_000000_create_users_table.php
├── 0001_01_01_000001_create_cache_table.php
├── 0001_01_01_000002_create_jobs_table.php
├── 2025_08_14_170933_add_two_factor_columns_to_users_table.php
├── 2025_11_25_070958_add_role_to_users_table.php
├── 2025_11_25_071000_create_categories_table.php
├── 2025_11_25_071003_create_products_table.php
├── 2025_11_25_071006_create_orders_table.php
├── 2025_11_25_071008_create_order_items_table.php
├── 2025_11_25_083034_create_carts_table.php
├── 2025_11_25_083041_create_cart_items_table.php
├── 2025_11_26_072446_create_store_settings_table.php
└── 2025_12_10_155728_make_customer_address_nullable_in_orders_table.php
```

### Running Migrations

```bash
# Run semua migrations
php artisan migrate

# Run dengan fresh database (WARNING: menghapus semua data)
php artisan migrate:fresh

# Run dengan seeding
php artisan migrate --seed

# Rollback migration terakhir
php artisan migrate:rollback

# Check status migrations
php artisan migrate:status
```

---

## Seeders

Daftar seeder yang tersedia untuk development dan production:

| Seeder | Fungsi | Penggunaan |
|--------|--------|------------|
| `DatabaseSeeder` | Main seeder yang memanggil seeder lain | Development |
| `UserSeeder` | Membuat user admin default | Production |
| `StoreSettingSeeder` | Mengisi default store settings | Production |
| `ProductionSeeder` | Kombinasi untuk production deployment | Production |

### Running Seeders

```bash
# Run DatabaseSeeder (development)
php artisan db:seed

# Run specific seeder (production)
php artisan db:seed --class=ProductionSeeder

# Fresh migrate dengan seed
php artisan migrate:fresh --seed
```

---

## Index Recommendations

### Existing Indexes (Sudah Implemented)

1. **products**: `category_id, is_active` - Untuk filter produk per kategori
2. **orders**: `status, created_at` - Untuk dashboard dan filtering
3. **orders**: `customer_phone` - Untuk pencarian customer

### Recommended Additional Indexes (Jika Scaling)

```sql
-- Untuk pencarian produk dengan full-text
CREATE INDEX products_name_idx ON products(name);
CREATE INDEX products_description_idx ON products(description);

-- Untuk reporting per periode
CREATE INDEX orders_created_at_idx ON orders(created_at);

-- Untuk analytics
CREATE INDEX order_items_product_id_created_idx 
    ON order_items(product_id, created_at);
```

---

## Data Integrity Rules

### Cascade Deletes
- **Category → Products**: Hapus kategori akan menghapus semua produk
- **Cart → CartItems**: Hapus cart akan menghapus semua items
- **Order → OrderItems**: Hapus order akan menghapus semua items
- **Product → OrderItems**: Hapus produk akan menghapus order items
- **Product → CartItems**: Hapus produk akan menghapus cart items

### Set Null on Delete
- **User → Orders**: Hapus user akan set user_id = NULL (order tetap ada)
- **User → Carts**: Hapus user akan set user_id = NULL

### Business Rules (Application Level)
- Order number auto-generated dengan format `ORD-YYYYMMDD-XXXXX`
- Product slug auto-generated dari name
- Category slug auto-generated dari name
- Order items menyimpan snapshot harga untuk data historis

---

## Backup Strategy

### Development
```bash
# Simple backup
cp database/database.sqlite database/database.sqlite.backup

# Dengan timestamp
cp database/database.sqlite database/backups/database-$(date +%Y%m%d-%H%M%S).sqlite
```

### Production Recommendations
1. **Regular Backups**: Automated daily/hourly backups
2. **Offsite Storage**: Backup ke cloud storage (S3, GCS)
3. **Point-in-Time Recovery**: Untuk MySQL/PostgreSQL deployment

---

## Related Documentation

- [01_System_Architecture.md](01_System_Architecture.md) - Arsitektur sistem
- [06_Deployment_Guide.md](06_Deployment_Guide.md) - Panduan deployment
