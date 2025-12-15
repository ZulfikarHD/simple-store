# Panduan Deployment Hostinger Shared Hosting

## Overview

Dokumen ini merupakan panduan deployment khusus untuk Hostinger shared hosting yang bertujuan untuk memberikan langkah-langkah detail deployment aplikasi Laravel ke subdomain, yaitu: konfigurasi tanpa akses npm/yarn di server, build assets secara lokal, dan setup symlink yang sesuai dengan struktur Hostinger.

**Penulis**: Zulfikar Hidayatullah  
**Terakhir Diperbarui**: Desember 2024

## Karakteristik Hostinger Shared Hosting

Hostinger shared hosting memiliki beberapa keterbatasan yang perlu diperhatikan, antara lain:

- **Tidak ada akses npm/yarn** - Node.js tidak tersedia di SSH
- **Tidak ada sudo/root access** - Tidak bisa install package tambahan
- **Tidak ada supervisor** - Queue worker harus menggunakan sync driver
- **SSH access terbatas** - Hanya basic commands yang tersedia
- **PHP version configurable** - Bisa dipilih via hPanel

## Pre-Deployment Checklist

Sebelum memulai deployment, pastikan item berikut sudah disiapkan:

- [ ] Akun Hostinger dengan SSH access enabled
- [ ] Subdomain sudah dibuat di hPanel
- [ ] Database MySQL sudah dibuat di hPanel
- [ ] Repository Git sudah siap (GitHub/GitLab/Bitbucket)
- [ ] PHP version di hPanel sudah diset ke 8.4+
- [ ] **Assets sudah di-build secara lokal** (lihat section Build Assets Lokal)

## Step 1: Build Assets di Lokal

Karena Hostinger tidak memiliki npm/yarn, build assets harus dilakukan di komputer lokal terlebih dahulu.

### 1.1 Build di Komputer Lokal

```bash
# Di komputer lokal (development machine)
cd /path/to/simple-store

# Install dependencies
yarn install

# Build untuk production
yarn run build

# Pastikan folder public/build sudah tergenerate
ls -la public/build
```

### 1.2 Commit Build Assets ke Repository

```bash
# Hapus public/build dari .gitignore (jika ada)
# Edit .gitignore dan comment/hapus baris:
# /public/build

# Commit build assets
git add public/build
git commit -m "chore: add production build assets for shared hosting deployment"
git push origin main
```

**Catatan Penting**: Untuk shared hosting tanpa npm/yarn, build assets **HARUS** di-commit ke repository. Ini berbeda dengan best practice normal dimana build assets tidak di-commit.

## Step 2: Setup Database di hPanel

### 2.1 Buat Database

1. Login ke hPanel Hostinger
2. Navigasi ke **Databases** → **MySQL Databases**
3. Buat database baru:
   - **Database name**: `u123456789_simplestore` (prefix otomatis dari Hostinger)
   - **Username**: `u123456789_storeuser`
   - **Password**: [Gunakan password yang kuat]
4. Catat kredensial untuk digunakan di `.env`

### 2.2 Catat Informasi Database

```
DB_HOST=localhost
DB_DATABASE=u123456789_simplestore
DB_USERNAME=u123456789_storeuser
DB_PASSWORD=your_secure_password
```

## Step 3: Setup Subdomain di hPanel

### 3.1 Buat Subdomain

1. Login ke hPanel Hostinger
2. Navigasi ke **Domains** → **Subdomains**
3. Buat subdomain baru: `store.yourdomain.com`
4. Hostinger akan membuat folder: `domains/store.yourdomain.com/public_html`

### 3.2 Enable SSH Access

1. Navigasi ke **Advanced** → **SSH Access**
2. Enable SSH access
3. Catat SSH credentials:
   - **Host**: `ssh.yourdomain.com` atau IP address
   - **Port**: `22`
   - **Username**: `u123456789`

## Step 4: Deployment via SSH

### 4.1 Connect ke Server

```bash
# Connect via SSH
ssh u123456789@ssh.yourdomain.com -p 22

# Atau menggunakan IP
ssh u123456789@123.456.789.000 -p 22
```

### 4.2 Clone Repository

```bash
# Navigate ke folder domain
cd domains/store.yourdomain.com/

# Clone repository
git clone https://github.com/yourusername/simple-store.git

# Verifikasi clone berhasil
ls -la simple-store
```

### 4.3 Setup Symlink public_html

```bash
# Backup public_html original (opsional)
mv public_html public_html_backup

# Buat symlink dari public_html ke folder public aplikasi
ln -s simple-store/public public_html

# Verifikasi symlink
ls -la
# Output harus menunjukkan: public_html -> simple-store/public
```

### 4.4 Setup Environment

```bash
# Masuk ke folder aplikasi
cd simple-store

# Copy environment file
cp .env.example .env

# Edit environment file
nano .env
```

### 4.5 Konfigurasi .env

Edit file `.env` dengan konfigurasi berikut:

```env
APP_NAME="Simple Store"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://store.yourdomain.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_simplestore
DB_USERNAME=u123456789_storeuser
DB_PASSWORD=your_secure_password

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_STORE=file

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Catatan**: `QUEUE_CONNECTION=sync` wajib digunakan karena shared hosting tidak support supervisor untuk queue worker.

### 4.6 Install PHP Dependencies

```bash
# Install composer dependencies (production mode)
composer install --no-dev --optimize-autoloader

# Jika composer tidak tersedia global, gunakan:
php composer.phar install --no-dev --optimize-autoloader
```

### 4.7 Generate Application Key

```bash
php artisan key:generate
```

### 4.8 Generate Wayfinder Routes

```bash
php artisan wayfinder:generate
```

### 4.9 Run Database Migration

```bash
# Run migrations
php artisan migrate --force

# Run seeders jika diperlukan
php artisan db:seed --force
```

### 4.10 Setup Storage Symlink

```bash
# Coba via artisan dulu
php artisan storage:link

# Jika gagal, buat manual:
cd public
rm -rf storage 2>/dev/null
ln -s ../storage/app/public storage
cd ..

# Verifikasi symlink
ls -la public/storage
# Output: storage -> ../storage/app/public
```

### 4.11 Set File Permissions

```bash
# Set permissions untuk storage dan cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Pastikan .env tidak bisa diakses publik
chmod 644 .env
```

### 4.12 Optimize Laravel

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize composer autoload
composer dump-autoload --optimize
```

## Step 5: Post-Deployment Verification

### 5.1 Test Website Access

```bash
# Test via curl (di SSH)
curl -I https://store.yourdomain.com
```

Atau buka browser dan akses `https://store.yourdomain.com`

### 5.2 Checklist Verifikasi

- [ ] Website accessible via HTTPS
- [ ] Homepage loads correctly
- [ ] CSS dan JavaScript berfungsi (assets loaded)
- [ ] Login/Register berfungsi
- [ ] Database operations berfungsi
- [ ] File upload berfungsi (jika ada)
- [ ] Tidak ada error di `storage/logs/laravel.log`

### 5.3 Check Error Logs

```bash
# Lihat Laravel logs
tail -50 storage/logs/laravel.log

# Atau untuk monitoring real-time
tail -f storage/logs/laravel.log
```

## Step 6: Setup SSL (Jika Belum Otomatis)

Hostinger biasanya menyediakan SSL gratis via Let's Encrypt:

1. Login ke hPanel
2. Navigasi ke **Security** → **SSL**
3. Pilih subdomain `store.yourdomain.com`
4. Install SSL certificate
5. Enable **Force HTTPS**

## Troubleshooting

### Error: "composer: command not found"

```bash
# Download composer.phar
curl -sS https://getcomposer.org/installer | php

# Gunakan dengan php prefix
php composer.phar install --no-dev --optimize-autoloader
```

### Error: "PHP version mismatch"

1. Login ke hPanel
2. Navigasi ke **Advanced** → **PHP Configuration**
3. Ubah PHP version ke 8.4
4. Tunggu beberapa menit untuk propagasi

### Error: "Permission denied" pada storage

```bash
# Reset permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Jika masih error, coba 777 (kurang aman tapi untuk troubleshooting)
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### Error: "Assets not loading" (CSS/JS broken)

1. Pastikan `public/build` folder ada dan berisi assets
2. Jika tidak ada, build ulang di lokal dan push ke repository:

```bash
# Di komputer lokal
yarn run build
git add public/build
git commit -m "fix: rebuild assets"
git push origin main

# Di server
cd ~/domains/store.yourdomain.com/simple-store
git pull origin main
php artisan cache:clear
```

### Error: "Class not found" setelah deployment

```bash
# Regenerate autoload
composer dump-autoload --optimize

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Error: "SQLSTATE connection refused"

1. Verifikasi kredensial database di `.env`
2. Pastikan `DB_HOST=localhost` (bukan 127.0.0.1 untuk Hostinger)
3. Pastikan database dan user sudah dibuat di hPanel

## Update Deployment

Untuk update aplikasi setelah ada perubahan:

### Update dengan Perubahan Code Saja

```bash
# Connect SSH
ssh u123456789@ssh.yourdomain.com

# Navigate ke folder aplikasi
cd domains/store.yourdomain.com/simple-store

# Pull latest changes
git pull origin main

# Clear caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run new migrations jika ada
php artisan migrate --force
```

### Update dengan Perubahan Assets

```bash
# Di komputer lokal DULU
yarn run build
git add public/build
git commit -m "chore: rebuild assets"
git push origin main

# Kemudian di server
ssh u123456789@ssh.yourdomain.com
cd domains/store.yourdomain.com/simple-store
git pull origin main
php artisan cache:clear
```

## Quick Reference Commands

```bash
# === DEPLOYMENT COMMANDS ===
cd domains/store.yourdomain.com/
git clone https://github.com/yourusername/simple-store.git
mv public_html public_html_backup
ln -s simple-store/public public_html
cd simple-store
cp .env.example .env
nano .env
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan wayfinder:generate
php artisan migrate --force
php artisan storage:link
chmod -R 775 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# === UPDATE COMMANDS ===
cd domains/store.yourdomain.com/simple-store
git pull origin main
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# === TROUBLESHOOTING COMMANDS ===
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload --optimize
tail -50 storage/logs/laravel.log
```

## Kontak Support

Jika mengalami kendala dalam proses deployment:

- **Developer**: Zulfikar Hidayatullah (+62 857-1583-8733)
- **Hostinger Support**: Via hPanel Live Chat atau support@hostinger.com

## Catatan Penting

1. **Backup sebelum update** - Selalu backup database sebelum menjalankan migration baru
2. **Test di staging dulu** - Jika memungkinkan, test perubahan di subdomain staging sebelum production
3. **Monitor logs** - Pantau `storage/logs/laravel.log` setelah deployment
4. **Build assets lokal** - Selalu ingat untuk build assets di lokal sebelum push ke repository

