# Error Codes & Messages

## Overview
Daftar lengkap error codes dan pesan error yang mungkin muncul di aplikasi Simple Store, beserta penjelasan dan cara mengatasinya.

---

## HTTP Status Codes

### 2xx - Success
| Code | Meaning | Keterangan |
|------|---------|------------|
| 200 | OK | Request berhasil |
| 201 | Created | Resource berhasil dibuat |
| 204 | No Content | Request berhasil, tidak ada konten dikembalikan |

### 4xx - Client Errors
| Code | Meaning | Penyebab Umum | Solusi |
|------|---------|---------------|--------|
| 400 | Bad Request | Data request tidak valid | Periksa format data yang dikirim |
| 401 | Unauthorized | User belum login | Login ulang |
| 403 | Forbidden | User tidak punya akses | Periksa role/permission user |
| 404 | Not Found | Resource tidak ditemukan | Periksa URL/ID resource |
| 419 | Page Expired | CSRF token expired | Refresh halaman, submit ulang |
| 422 | Unprocessable Entity | Validation error | Periksa pesan validation |
| 429 | Too Many Requests | Rate limit exceeded | Tunggu beberapa saat |

### 5xx - Server Errors
| Code | Meaning | Penyebab Umum | Solusi |
|------|---------|---------------|--------|
| 500 | Internal Server Error | Server error | Cek Laravel logs |
| 502 | Bad Gateway | PHP-FPM down | Restart PHP-FPM |
| 503 | Service Unavailable | Maintenance mode | `php artisan up` |
| 504 | Gateway Timeout | Request timeout | Optimize query/process |

---

## Laravel Specific Errors

### Authentication Errors

#### `These credentials do not match our records.`
**Penyebab:** Email atau password salah
**Solusi:**
1. Pastikan email dan password benar
2. Gunakan fitur "Lupa Password" jika perlu
3. Cek apakah user sudah terdaftar

#### `Your email address is not verified.`
**Penyebab:** Email belum diverifikasi
**Solusi:**
1. Cek inbox email untuk link verifikasi
2. Request ulang email verifikasi
3. Admin dapat manual verify:
```bash
php artisan tinker
>>> $user = \App\Models\User::where('email', 'user@email.com')->first();
>>> $user->email_verified_at = now();
>>> $user->save();
```

#### `Unauthenticated.`
**Penyebab:** Session expired atau token invalid
**Solusi:**
1. Login ulang
2. Clear browser cookies
3. Cek session configuration

### Authorization Errors

#### `This action is unauthorized.`
**Penyebab:** User tidak punya permission untuk aksi tersebut
**Solusi:**
1. Cek role user
2. Pastikan user adalah admin untuk admin actions
```bash
php artisan tinker
>>> $user = \App\Models\User::find(1);
>>> $user->role  # should be 'admin' for admin access
```

### Validation Errors

#### Format Error Messages
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message here"]
  }
}
```

#### Common Validation Messages

| Field | Error Message | Penyebab |
|-------|---------------|----------|
| email | "The email has already been taken." | Email sudah terdaftar |
| email | "The email must be a valid email address." | Format email salah |
| password | "The password must be at least 8 characters." | Password terlalu pendek |
| name | "The name field is required." | Field name kosong |
| phone | "The phone format is invalid." | Format nomor telepon salah |
| quantity | "The quantity must be at least 1." | Quantity harus > 0 |
| stock | "The selected product is out of stock." | Stok habis |

### Database Errors

#### `SQLSTATE[HY000]: General error: 1 no such table`
**Penyebab:** Table belum ada di database
**Solusi:**
```bash
php artisan migrate
```

#### `SQLSTATE[HY000]: General error: unable to open database file`
**Penyebab:** SQLite file tidak ditemukan atau permission denied
**Solusi:**
```bash
touch database/database.sqlite
chmod 664 database/database.sqlite
php artisan migrate
```

#### `SQLSTATE[23000]: Integrity constraint violation`
**Penyebab:** Foreign key constraint atau unique constraint violation
**Solusi:**
1. Cek apakah data reference exists
2. Pastikan tidak ada duplikat untuk unique fields

#### `SQLSTATE[HY000] [2002] Connection refused`
**Penyebab:** Database server tidak berjalan
**Solusi:**
```bash
# Untuk MySQL
sudo systemctl start mysql

# Untuk SQLite - pastikan file ada
ls database/database.sqlite
```

### Session Errors

#### `419 - Page Expired`
**Penyebab:** CSRF token expired
**Solusi:**
1. Refresh halaman
2. Submit form ulang
3. Clear browser cache

#### `Session store not set on request.`
**Penyebab:** Session middleware tidak aktif
**Solusi:**
1. Pastikan middleware 'web' aktif di route
2. Cek `bootstrap/app.php` untuk middleware configuration

---

## Order-Related Errors

### `Order tidak dapat dibuat`

#### Possible Causes:
1. Cart kosong
2. Produk out of stock
3. Validation error pada customer data

**Debugging:**
```bash
# Check cart
php artisan tinker
>>> $cart = \App\Models\Cart::with('items.product')->find(1);
>>> $cart->items->each(fn($item) => dump($item->product->name, $item->product->stock));

# Check validation
# Lihat network tab di browser untuk detail error
```

### `Stok tidak mencukupi`

**Penyebab:** Quantity melebihi available stock
**Solusi:**
1. Kurangi quantity di cart
2. Admin update stock produk:
```bash
php artisan tinker
>>> $product = \App\Models\Product::find(1);
>>> $product->stock = 100;
>>> $product->save();
```

### `Order tidak ditemukan`

**Penyebab:** Order ID tidak valid atau sudah dihapus
**Solusi:**
1. Cek order number dengan benar
2. Verify di database:
```bash
php artisan tinker
>>> \App\Models\Order::where('order_number', 'ORD-XXXXX')->exists()
```

### `Status order tidak dapat diubah`

**Penyebab:** Invalid status transition
**Valid Transitions:**
```
pending → confirmed → preparing → ready → delivered
pending → cancelled
confirmed → cancelled
```

---

## Product & Category Errors

### `Produk tidak ditemukan`
**Penyebab:** Product ID invalid atau produk sudah dihapus
**Solusi:**
```bash
php artisan tinker
>>> \App\Models\Product::find($productId)
>>> \App\Models\Product::withTrashed()->find($productId)  # jika pakai soft delete
```

### `Kategori tidak dapat dihapus`
**Penyebab:** Kategori masih memiliki produk
**Solusi:**
1. Pindahkan produk ke kategori lain
2. Hapus semua produk di kategori tersebut
```bash
php artisan tinker
>>> \App\Models\Product::where('category_id', $categoryId)->count()
```

### `Gambar gagal diupload`
**Penyebab:**
1. File terlalu besar
2. Format tidak didukung
3. Permission storage

**Solusi:**
```bash
# Check upload limit di php.ini
php -i | grep upload_max_filesize
php -i | grep post_max_size

# Check storage permissions
ls -la storage/app/public/
chmod -R 775 storage/app/public/
```

---

## Cart Errors

### `Item tidak dapat ditambahkan ke cart`

**Possible Causes:**
1. Produk tidak aktif
2. Produk out of stock
3. Session issues

**Debugging:**
```bash
php artisan tinker
>>> $product = \App\Models\Product::find($productId);
>>> dump($product->is_active, $product->stock);
```

### `Cart tidak ditemukan`

**Penyebab:** Session expired atau cart sudah dihapus
**Solusi:**
1. Refresh halaman
2. Add item baru ke cart

---

## File & Storage Errors

### `Storage link tidak ada`
**Error:** Gambar tidak muncul, 404 pada `/storage/*`
**Solusi:**
```bash
php artisan storage:link
```

### `Permission denied saat upload`
**Error:** `file_put_contents(): Failed to open stream: Permission denied`
**Solusi:**
```bash
sudo chown -R www-data:www-data storage
chmod -R 775 storage
```

### `Disk full`
**Error:** `No space left on device`
**Solusi:**
```bash
# Check disk usage
df -h

# Clear old files
php artisan cache:clear
rm -rf storage/logs/*.log
# Backup dulu sebelum hapus files lain
```

---

## Queue & Job Errors

### `Job failed`
**Debugging:**
```bash
# Check failed jobs
php artisan queue:failed

# Retry specific job
php artisan queue:retry {job_id}

# Retry all failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

### `Queue worker not running`
**Solusi:**
```bash
# Start queue worker
php artisan queue:work

# For production (via supervisor)
sudo supervisorctl restart simple-store-worker:*
```

---

## Environment & Configuration Errors

### `No application encryption key has been specified.`
**Solusi:**
```bash
php artisan key:generate
```

### `Configuration caching error`
**Error:** `Your configuration files are not serializable.`
**Solusi:**
```bash
# Clear config cache
php artisan config:clear

# Remove closures from config files
# Don't use env() in config files
```

### `.env file missing`
**Solusi:**
```bash
cp .env.example .env
php artisan key:generate
# Edit .env dengan values yang benar
```

---

## Error Logging

### Log Locations
```bash
# Application logs
storage/logs/laravel.log

# Nginx logs
/var/log/nginx/access.log
/var/log/nginx/error.log

# PHP-FPM logs
/var/log/php8.4-fpm.log
```

### View Recent Errors
```bash
# Last 50 lines
tail -50 storage/logs/laravel.log

# Follow log in real-time
tail -f storage/logs/laravel.log

# Search for specific errors
grep -i "error" storage/logs/laravel.log | tail -20
```

### Clear Old Logs
```bash
# Remove old log files
rm storage/logs/laravel-*.log

# Keep only recent logs
find storage/logs -name "*.log" -mtime +7 -delete
```

---

## Quick Reference Card

### Emergency Commands
```bash
# Application won't start
php artisan optimize:clear
php artisan key:generate

# Database issues
php artisan migrate:status
php artisan migrate

# Permission issues
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Frontend issues
yarn build
php artisan storage:link

# Check what's wrong
php artisan about
tail -50 storage/logs/laravel.log
```

---

## Contact

**Developer**: Zulfikar Hidayatullah  
**WhatsApp**: +62 857-1583-8733

---

## Document Info

**Last Updated**: [Date]  
**Maintained By**: Zulfikar Hidayatullah

