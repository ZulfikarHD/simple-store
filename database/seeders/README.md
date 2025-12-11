# Database Seeders

Penjelasan mengenai seeders yang tersedia di aplikasi Simple Store.

---

## 📁 Available Seeders

### 1. DatabaseSeeder (Development)
**File:** `DatabaseSeeder.php`

**Purpose:** Untuk development dan testing

**Data yang dibuat:**
- ✅ Admin user (`admin@test.com` / `admin123`)
- ✅ Customer demo (`customer@test.com` / `customer123`)  
- ✅ 5 Customer tambahan (random)
- ✅ 6 Kategori produk F&B
- ✅ 30+ Sample products
- ✅ Sample orders dengan berbagai status
- ✅ Store settings default

**Cara penggunaan:**
```bash
# Fresh migration + seed
php artisan migrate:fresh --seed

# Atau hanya seed
php artisan db:seed
```

**Kapan digunakan:**
- Local development
- Testing
- Demo purposes

---

### 2. ProductionSeeder (Production)
**File:** `ProductionSeeder.php`

**Purpose:** Untuk production/deployment pertama kali

**Data yang dibuat:**
- ✅ 1 Admin user (dengan credentials yang Anda input)
- ✅ Store settings default (opsional)

**Cara penggunaan:**
```bash
php artisan db:seed --class=ProductionSeeder
```

**Features:**
- 🔒 **Interactive & Secure:** Input credentials via command line
- ✅ **Validasi Input:** Email, password, dan nama tervalidasi
- 🛡️ **Safe:** Cek duplicate admin sebelum membuat user baru
- 📋 **User Friendly:** Menggunakan Laravel Prompts untuk UX yang baik

**Kapan digunakan:**
- Setup production pertama kali
- Deployment ke server
- Membuat admin baru

**Dokumentasi Lengkap:** 
Lihat [PRODUCTION_SETUP.md](../../PRODUCTION_SETUP.md) untuk panduan lengkap.

---

### 3. StoreSettingSeeder
**File:** `StoreSettingSeeder.php`

**Purpose:** Mengisi store settings default

**Data yang dibuat:**
- General settings (nama toko, alamat, telepon)
- WhatsApp settings
- Operating hours
- Delivery settings

**Cara penggunaan:**
```bash
php artisan db:seed --class=StoreSettingSeeder
```

**Note:** Seeder ini otomatis dipanggil oleh `DatabaseSeeder` dan `ProductionSeeder`.

---

### 4. UserSeeder (Legacy)
**File:** `UserSeeder.php`

**Purpose:** Membuat users (admin & customers)

**Note:** Seeder ini sudah terintegrasi ke dalam `DatabaseSeeder`, tidak perlu dijalankan terpisah.

---

## 🔄 Reset Database

### Development
```bash
# Reset semua + seed sample data
php artisan migrate:fresh --seed
```

### Production (⚠️ DANGER)
```bash
# Reset semua + seed production
php artisan migrate:fresh --seed --class=ProductionSeeder

# Atau step by step
php artisan migrate:fresh --force
php artisan db:seed --class=ProductionSeeder
```

> ⚠️ **Warning:** `migrate:fresh` akan menghapus SEMUA data!

---

## 🎯 Quick Reference

| Seeder | Environment | Admin Created | Sample Data | Interactive |
|--------|-------------|---------------|-------------|-------------|
| **DatabaseSeeder** | Development | ✅ (`admin@test.com`) | ✅ Banyak | ❌ |
| **ProductionSeeder** | Production | ✅ (Your input) | ❌ Minimal | ✅ |
| **StoreSettingSeeder** | Both | ❌ | ✅ Settings only | ❌ |

---

## 💡 Tips

### Development Workflow
```bash
# Fresh start dengan sample data
php artisan migrate:fresh --seed

# Login sebagai admin
Email: admin@test.com
Password: admin123
```

### Production Workflow
```bash
# Setup awal
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder

# Input credentials Anda sendiri
# Login ke /admin dengan credentials tersebut
```

---

## 📞 Support

**Developer:** Zulfikar Hidayatullah  
**Phone:** +62 857-1583-8733

