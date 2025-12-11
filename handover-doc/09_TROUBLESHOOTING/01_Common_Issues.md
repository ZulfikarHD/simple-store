# Troubleshooting - Common Issues

## Overview
Dokumen ini berisi daftar masalah umum yang mungkin terjadi pada aplikasi Simple Store beserta solusinya.

## Quick Reference

| Masalah | Penyebab Umum | Solusi Cepat |
|---------|---------------|--------------|
| Halaman blank/putih | Build frontend belum jalan | `yarn build` atau `yarn dev` |
| Error 500 | Config cache outdated | `php artisan optimize:clear` |
| Login gagal | Session expired | Clear browser cookies |
| Gambar tidak muncul | Storage link missing | `php artisan storage:link` |
| Query lambat | Missing indexes | Check database indexes |

---

## 1. Frontend Issues

### 1.1 Halaman Blank / White Screen

**Gejala:**
- Halaman kosong tanpa konten
- Console browser menunjukkan error JavaScript

**Penyebab:**
- Frontend assets belum di-build
- Vite development server tidak berjalan
- JavaScript error

**Solusi:**
```bash
# Development mode
yarn dev

# Production build
yarn build

# Check Vite manifest
cat public/build/manifest.json
```

**Jika masih error:**
```bash
# Clear cache dan rebuild
rm -rf public/build
rm -rf node_modules/.vite
yarn build
```

### 1.2 Vite Manifest Error

**Gejala:**
```
Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest
```

**Solusi:**
```bash
# Option 1: Build assets
yarn build

# Option 2: Run development server
yarn dev

# Option 3: Clear and rebuild
php artisan optimize:clear
yarn build
```

### 1.3 CSS/Styling Tidak Muncul

**Gejala:**
- Layout berantakan
- Tailwind classes tidak bekerja

**Solusi:**
```bash
# Rebuild assets
yarn build

# Check tailwind config
cat resources/css/app.css
```

### 1.4 Vue Component Error

**Gejala:**
- Error di console: "Failed to resolve component"
- Komponen tidak render

**Solusi:**
```bash
# Check component path
ls resources/js/Pages/

# Pastikan nama file sesuai (case-sensitive!)
# ✅ ProductList.vue
# ❌ productlist.vue

# Clear cache
php artisan optimize:clear
yarn build
```

---

## 2. Backend Issues

### 2.1 Error 500 - Internal Server Error

**Gejala:**
- Halaman error 500
- Tidak ada detail error

**Langkah Debugging:**
```bash
# 1. Check Laravel logs
tail -50 storage/logs/laravel.log

# 2. Enable debug mode (development only!)
# .env
APP_DEBUG=true

# 3. Clear all caches
php artisan optimize:clear

# 4. Check permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**Penyebab Umum:**
1. Config cache outdated
2. Permission issues
3. Database connection error
4. Missing .env file

### 2.2 Database Connection Error

**Gejala:**
```
SQLSTATE[HY000]: General error: unable to open database file
```

**Solusi:**
```bash
# Check database file exists
ls -la database/database.sqlite

# Create if missing
touch database/database.sqlite

# Fix permissions
chmod 664 database/database.sqlite
chmod 775 database/

# Run migrations
php artisan migrate
```

### 2.3 Session/Authentication Issues

**Gejala:**
- User ter-logout secara random
- Session expired terus-menerus

**Solusi:**
```bash
# Clear session data
php artisan session:table  # jika pakai database session
php artisan migrate

# Clear cache
php artisan cache:clear

# Check session config
php artisan tinker
>>> config('session.driver')
>>> config('session.lifetime')
```

**Di Browser:**
1. Clear cookies untuk domain aplikasi
2. Refresh halaman
3. Login ulang

### 2.4 Permission Denied Errors

**Gejala:**
```
file_put_contents(): Failed to open stream: Permission denied
```

**Solusi:**
```bash
# Fix storage permissions
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache

# Jika menggunakan Nginx
sudo chown -R www-data:www-data storage bootstrap/cache
```

### 2.5 Class Not Found Error

**Gejala:**
```
Class 'App\Models\Product' not found
```

**Solusi:**
```bash
# Regenerate autoload
composer dump-autoload

# Clear cache
php artisan optimize:clear

# Check class exists
ls app/Models/
```

---

## 3. Order & Cart Issues

### 3.1 Cart Tidak Tersimpan

**Gejala:**
- Item di cart hilang setelah refresh
- Cart kosong padahal sudah add item

**Penyebab & Solusi:**
```bash
# Check session
php artisan tinker
>>> session()->all()

# Check cart table
php artisan tinker
>>> \App\Models\Cart::with('items')->get()

# Clear old carts
php artisan tinker
>>> \App\Models\Cart::where('updated_at', '<', now()->subDays(7))->delete()
```

### 3.2 Order Gagal Dibuat

**Gejala:**
- Error saat checkout
- Order tidak tersimpan ke database

**Debugging:**
```bash
# Check validation errors
# Lihat network tab di browser untuk response error

# Check order creation
php artisan tinker
>>> \App\Models\Order::latest()->first()

# Check logs
tail -20 storage/logs/laravel.log | grep -i order
```

### 3.3 Status Order Tidak Update

**Gejala:**
- Status order tidak berubah
- Timeline order tidak update

**Solusi:**
```bash
# Manual update via tinker
php artisan tinker
>>> $order = \App\Models\Order::find(1);
>>> $order->status = 'confirmed';
>>> $order->confirmed_at = now();
>>> $order->save();
```

---

## 4. Product & Category Issues

### 4.1 Gambar Produk Tidak Muncul

**Gejala:**
- Gambar broken/404
- Placeholder muncul terus

**Solusi:**
```bash
# 1. Check storage link
ls -la public/storage

# Jika tidak ada:
php artisan storage:link

# 2. Check image exists
ls storage/app/public/products/

# 3. Check permissions
chmod -R 755 storage/app/public

# 4. Verify URL
php artisan tinker
>>> \App\Models\Product::first()->image
>>> Storage::url(\App\Models\Product::first()->image)
```

### 4.2 Produk Tidak Muncul di Homepage

**Gejala:**
- Halaman kosong
- Tidak ada produk ditampilkan

**Check:**
```bash
php artisan tinker
>>> \App\Models\Product::where('is_active', true)->count()
>>> \App\Models\Category::where('is_active', true)->count()
```

**Jika count = 0:**
```bash
# Seed data
php artisan db:seed

# Atau manual activate
php artisan tinker
>>> \App\Models\Product::query()->update(['is_active' => true]);
>>> \App\Models\Category::query()->update(['is_active' => true]);
```

### 4.3 Stock Tidak Update

**Gejala:**
- Stock tidak berkurang setelah order
- Stock menunjukkan nilai salah

**Solusi:**
```bash
php artisan tinker
>>> $product = \App\Models\Product::find(1);
>>> $product->stock = 100;
>>> $product->save();
```

---

## 5. Admin Panel Issues

### 5.1 Tidak Bisa Login Admin

**Gejala:**
- Login gagal dengan credentials benar
- Redirect loop

**Solusi:**
```bash
# Check user role
php artisan tinker
>>> \App\Models\User::where('email', 'admin@example.com')->first()->role

# Update role jika perlu
>>> $user = \App\Models\User::where('email', 'admin@example.com')->first();
>>> $user->role = 'admin';
>>> $user->save();

# Reset password jika lupa
>>> $user->password = bcrypt('password_baru');
>>> $user->save();
```

### 5.2 Dashboard Statistics Tidak Akurat

**Gejala:**
- Angka statistik tidak sesuai
- Data tidak update

**Solusi:**
```bash
# Clear cache
php artisan cache:clear

# Verify data manually
php artisan tinker
>>> \App\Models\Order::count()
>>> \App\Models\Order::sum('total')
>>> \App\Models\User::count()
```

---

## 6. Performance Issues

### 6.1 Aplikasi Lambat

**Gejala:**
- Response time > 2 detik
- Loading lama

**Checklist:**
```bash
# 1. Enable caching (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Check slow queries
# Enable query log di AppServiceProvider atau gunakan Laravel Debugbar

# 3. Check server resources
top
free -h
df -h

# 4. Optimize database
php artisan tinker
>>> DB::statement('VACUUM')  # untuk SQLite
```

### 6.2 Memory Limit Exceeded

**Gejala:**
```
Allowed memory size of X bytes exhausted
```

**Solusi:**
```php
// Temporary increase (di code)
ini_set('memory_limit', '512M');

// Permanent (di php.ini)
memory_limit = 512M

// Untuk artisan commands
php -d memory_limit=512M artisan your:command
```

---

## 7. Deployment Issues

### 7.1 composer install Gagal

**Gejala:**
- Memory exhausted
- Package conflicts

**Solusi:**
```bash
# Increase memory
COMPOSER_MEMORY_LIMIT=-1 composer install

# Without dev dependencies
composer install --no-dev --optimize-autoloader

# Clear composer cache
composer clear-cache
```

### 7.2 yarn/npm install Gagal

**Gejala:**
- Node version mismatch
- Package not found

**Solusi:**
```bash
# Check node version
node -v

# Use correct version (via nvm)
nvm use 20

# Clear cache dan reinstall
rm -rf node_modules
rm yarn.lock  # atau package-lock.json
yarn install
```

### 7.3 Environment Issues

**Gejala:**
- Config tidak sesuai
- Database berbeda dari expected

**Solusi:**
```bash
# Check current environment
php artisan env

# Verify .env exists
ls -la .env

# Copy from example jika tidak ada
cp .env.example .env
php artisan key:generate
```

---

## 8. Quick Recovery Commands

### Full Reset (Development Only!)
```bash
# ⚠️ HATI-HATI: Ini akan menghapus semua data!
php artisan migrate:fresh --seed
php artisan optimize:clear
yarn build
```

### Soft Reset
```bash
# Clear semua cache tanpa hapus data
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Check Application Health
```bash
# Quick health check
php artisan about
php artisan route:list --compact
php artisan migrate:status
```

---

## Contact & Escalation

Jika masalah tidak dapat diselesaikan dengan panduan di atas:

**Developer**: Zulfikar Hidayatullah  
**WhatsApp**: +62 857-1583-8733

### Informasi yang Diperlukan Saat Eskalasi:
1. Screenshot/copy error message
2. Langkah-langkah reproduksi masalah
3. Output dari `php artisan about`
4. Isi log file terkait (`storage/logs/laravel.log`)
5. Browser & OS yang digunakan

---

## Document Info

**Last Updated**: [Date]  
**Maintained By**: Zulfikar Hidayatullah

