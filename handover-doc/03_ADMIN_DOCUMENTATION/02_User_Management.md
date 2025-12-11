# Manajemen Pengguna

## Informasi Dokumen

| Atribut | Detail |
|---------|--------|
| **Nama Dokumen** | Panduan Manajemen Pengguna |
| **Developer** | Zulfikar Hidayatullah (+62 857-1583-8733) |
| **Versi** | 1.0.0 |

---

## Overview

Manajemen Pengguna pada Simple Store merupakan modul yang bertujuan untuk mengelola akun pengguna aplikasi, yaitu: pembuatan akun, autentikasi, role management, dan keamanan akun. Sistem ini menggunakan Laravel Fortify untuk authentication dan mendukung Two-Factor Authentication (2FA).

---

## Struktur Database Users

### Tabel `users`

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | integer | Primary key |
| `name` | varchar | Nama lengkap pengguna |
| `email` | varchar | Email (unique) untuk login |
| `email_verified_at` | datetime | Timestamp verifikasi email |
| `password` | varchar | Password ter-hash |
| `role` | varchar | Role pengguna (admin/user) |
| `phone` | varchar | Nomor telepon |
| `address` | text | Alamat pengguna |
| `two_factor_secret` | text | Secret key untuk 2FA |
| `two_factor_recovery_codes` | text | Recovery codes untuk 2FA |
| `two_factor_confirmed_at` | datetime | Timestamp konfirmasi 2FA |
| `remember_token` | varchar | Token untuk remember me |
| `created_at` | datetime | Timestamp pembuatan akun |
| `updated_at` | datetime | Timestamp update terakhir |

---

## Role dan Permission

### Role yang Tersedia

Simple Store menggunakan sistem role sederhana dengan dua tipe pengguna:

#### 1. Admin
**Deskripsi**: Pengelola toko dengan akses penuh ke admin panel.

**Akses yang dimiliki:**
- ✅ Dashboard overview dan statistik
- ✅ Manajemen Produk (CRUD lengkap)
- ✅ Manajemen Kategori (CRUD lengkap)
- ✅ Manajemen Pesanan (view, update status)
- ✅ Pengaturan Toko
- ✅ Akses storefront sebagai customer

#### 2. User (Customer)
**Deskripsi**: Pelanggan yang dapat berbelanja di toko.

**Akses yang dimiliki:**
- ✅ Browsing produk dan katalog
- ✅ Keranjang belanja (cart)
- ✅ Checkout dan pembuatan pesanan
- ✅ Riwayat pesanan
- ✅ Profil dan pengaturan akun
- ❌ Tidak dapat mengakses admin panel

### Permission Matrix

| Fitur | Admin | User |
|-------|:-----:|:----:|
| Dashboard Admin | ✅ | ❌ |
| Manajemen Produk | ✅ | ❌ |
| Manajemen Kategori | ✅ | ❌ |
| Manajemen Pesanan | ✅ | ❌ |
| Pengaturan Toko | ✅ | ❌ |
| Browsing Produk | ✅ | ✅ |
| Keranjang & Checkout | ✅ | ✅ |
| Riwayat Pesanan | ✅ | ✅ |
| Profil & Settings | ✅ | ✅ |
| Two-Factor Auth | ✅ | ✅ |

---

## Autentikasi

### Registrasi Pengguna Baru

Pengguna dapat mendaftar melalui form registrasi dengan data berikut:

| Field | Required | Validasi |
|-------|:--------:|----------|
| Nama Lengkap | ✅ | Minimal 3 karakter |
| Email | ✅ | Format email valid, unique |
| Password | ✅ | Minimal 8 karakter |
| Konfirmasi Password | ✅ | Harus sama dengan password |

**Flow Registrasi:**
1. User mengisi form registrasi di `/register`
2. Sistem memvalidasi input
3. Password di-hash dengan bcrypt
4. Akun dibuat dengan role `user` (default)
5. User otomatis login dan redirect ke dashboard

### Login

**URL**: `/login`

**Data yang Diperlukan:**
- Email
- Password
- Remember Me (opsional)

**Flow Login:**
1. User memasukkan kredensial
2. Sistem memverifikasi email dan password
3. Jika 2FA aktif, user diminta kode verifikasi
4. Session dibuat dan user redirect sesuai role:
   - Admin → `/admin/dashboard`
   - User → `/` (homepage)

### Logout

**URL**: `POST /logout`

**Flow Logout:**
1. Session dihapus
2. Remember token di-invalidate
3. User redirect ke homepage

---

## Two-Factor Authentication (2FA)

### Overview

Two-Factor Authentication merupakan fitur keamanan tambahan yang menggunakan TOTP (Time-based One-Time Password) untuk verifikasi identitas pengguna. Fitur ini menggunakan Laravel Fortify.

### Mengaktifkan 2FA

**Lokasi**: Profil → Keamanan → Two Factor Authentication

**Steps:**
1. Klik "Enable Two-Factor Authentication"
2. Scan QR code dengan authenticator app:
   - Google Authenticator
   - Authy
   - Microsoft Authenticator
3. Masukkan kode 6 digit untuk konfirmasi
4. Simpan recovery codes di tempat aman

### Recovery Codes

Recovery codes adalah kode backup yang dapat digunakan jika:
- Device authenticator hilang
- App authenticator tidak bisa diakses
- Kode OTP tidak bisa di-generate

**Best Practices:**
- Simpan recovery codes di tempat aman (password manager, printed copy)
- Setiap code hanya bisa digunakan sekali
- Generate ulang codes jika sudah banyak terpakai

### Menonaktifkan 2FA

**Steps:**
1. Masuk ke Profil → Keamanan
2. Klik "Disable Two-Factor Authentication"
3. Konfirmasi dengan password
4. 2FA dinonaktifkan

---

## Manajemen Profil

### Update Profil

**URL**: `/settings/profile`

User dapat mengupdate:
- Nama lengkap
- Email address
- Nomor telepon
- Alamat

### Update Password

**URL**: `/settings/password`

**Requirements:**
- Password saat ini (untuk verifikasi)
- Password baru (minimal 8 karakter)
- Konfirmasi password baru

### Delete Account

**URL**: `/settings/profile` → Delete Account

**Flow:**
1. User klik "Delete Account"
2. Masukkan password untuk konfirmasi
3. Akun dihapus permanen
4. Data terkait (orders, cart) tetap tersimpan untuk referensi

---

## Session Management

### Browser Sessions

**URL**: `/settings/sessions`

User dapat melihat dan manage browser sessions:
- Lihat semua device yang sedang login
- Informasi browser dan IP address
- Logout dari device lain

### Session Timeout

| Setting | Value |
|---------|-------|
| Session Lifetime | 120 menit |
| Remember Me | 5 hari |

---

## Password Reset

### Forgot Password Flow

1. User klik "Lupa Password" di halaman login
2. Masukkan email yang terdaftar
3. Sistem mengirim email reset password
4. User klik link di email
5. Masukkan password baru
6. Password diupdate dan user bisa login

### Reset Password via Admin

Saat ini, reset password hanya bisa dilakukan oleh user melalui email. Untuk kasus khusus, admin dapat:
1. Akses database langsung (development)
2. Hubungi developer untuk reset manual

---

## Membuat Admin Baru

### Via Database Seeder

```bash
php artisan db:seed --class=AdminSeeder
```

### Via Tinker

```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name' => 'Admin Name',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
]);
```

### Via Database Query

```sql
INSERT INTO users (name, email, password, role, created_at, updated_at)
VALUES (
    'Admin Name',
    'admin@example.com',
    '$2y$10$[hashed_password]',
    'admin',
    NOW(),
    NOW()
);
```

---

## Best Practices

### Untuk Administrator

1. **Gunakan Password Kuat**
   - Minimal 12 karakter untuk admin
   - Kombinasi huruf besar, kecil, angka, simbol
   - Jangan gunakan password yang sama di platform lain

2. **Aktifkan 2FA**
   - Wajib untuk semua admin
   - Simpan recovery codes dengan aman

3. **Review Sessions Berkala**
   - Cek device yang aktif login
   - Logout device yang tidak dikenal

4. **Jangan Share Kredensial**
   - Satu akun untuk satu orang
   - Buat akun terpisah jika ada admin baru

### Untuk User

1. **Verifikasi Email**
   - Pastikan email valid untuk menerima notifikasi
   - Aktifkan notifikasi order status

2. **Jaga Keamanan Akun**
   - Jangan login di device publik
   - Logout setelah selesai di komputer bersama

---

## Troubleshooting

| Problem | Solusi |
|---------|--------|
| Tidak bisa login | Reset password via email |
| Email reset tidak masuk | Cek folder spam, tunggu 5 menit |
| 2FA code tidak valid | Cek waktu device sudah sinkron, gunakan recovery code |
| Session expired terus | Clear cookies, check koneksi internet |
| Tidak bisa akses admin | Verifikasi role di database |

---

## API Endpoints

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/register` | POST | Registrasi user baru |
| `/login` | POST | Login user |
| `/logout` | POST | Logout user |
| `/forgot-password` | POST | Request reset password |
| `/reset-password` | POST | Reset password dengan token |
| `/two-factor-challenge` | POST | Verifikasi 2FA |
| `/user/confirmed-password-status` | GET | Cek status password confirmed |
| `/user/confirm-password` | POST | Konfirmasi password |
