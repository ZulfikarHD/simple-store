# Google OAuth Integration

## Overview

Google OAuth Integration merupakan sistem autentikasi third-party yang bertujuan untuk menyediakan metode login alternatif menggunakan Google account, yaitu: one-click authentication, auto-registration untuk user baru, dan profile synchronization yang terintegrasi dengan Laravel Socialite package untuk handling OAuth 2.0 flow secara secure dan efficient.

## Prerequisites

Komponen yang diperlukan untuk mengimplementasikan Google OAuth authentication, antara lain:

- **Google Account** - Untuk mengakses Google Cloud Console dan membuat OAuth application
- **Google Cloud Console Access** - Platform untuk konfigurasi OAuth credentials di [console.cloud.google.com](https://console.cloud.google.com/)
- **Laravel Socialite** - Package Laravel untuk handling OAuth authentication (v5.24.0 atau lebih tinggi)
- **Database Migration** - Tabel users harus memiliki kolom `google_id`, `avatar`, dan `password` nullable

## Quick Start

### 1. Installation

Package Laravel Socialite sudah ter-install di project ini. Jika Anda melakukan fresh installation, package akan otomatis ter-install melalui composer.

```bash
# Verifikasi package sudah ter-install
composer show laravel/socialite
```

### 2. Database Setup

#### Untuk Fresh Installation

Jalankan migration untuk menambahkan kolom yang diperlukan:

```bash
php artisan migrate
```

Migration `2025_12_22_015036_add_google_id_to_users_table.php` akan menambahkan:
- `google_id` (string, nullable, unique) - Google user ID
- `avatar` (string, nullable) - URL avatar dari Google
- `password` (nullable) - Memungkinkan user login hanya dengan Google

#### Untuk Existing Installation (Sudah Migrate Sebelumnya)

Jika Anda sudah menjalankan migration sebelum Google OAuth di-implement, jalankan migration yang spesifik:

```bash
# Cek status migration
php artisan migrate:status

# Jika migration add_google_id_to_users_table belum dijalankan
php artisan migrate --path=/database/migrations/2025_12_22_015036_add_google_id_to_users_table.php

# Atau jalankan semua pending migrations
php artisan migrate
```

**Verification:**
```bash
# Verifikasi kolom sudah ditambahkan
php artisan tinker
>>> Schema::hasColumn('users', 'google_id')
=> true
>>> Schema::hasColumn('users', 'avatar')
=> true
```

### 3. Google Cloud Console Setup

#### Step 1: Buat Project Baru

1. Akses [Google Cloud Console](https://console.cloud.google.com/)
2. Klik **Select a project** > **New Project**
3. Masukkan nama project (contoh: "Simple Store OAuth")
4. Klik **Create**

#### Step 2: Aktifkan Google+ API

1. Navigate ke **APIs & Services** > **Library**
2. Search "Google+ API"
3. Klik **Enable**

#### Step 3: Konfigurasi OAuth Consent Screen

1. Navigate ke **APIs & Services** > **OAuth consent screen**
2. Pilih **External** sebagai User Type
3. Isi informasi aplikasi:
   - **App name**: Nama toko Anda
   - **User support email**: Email support Anda
   - **Developer contact information**: Email developer
4. Klik **Save and Continue**
5. Di section **Scopes**, tambahkan:
   - `userinfo.email`
   - `userinfo.profile`
   - `openid`
6. Klik **Update** > **Save and Continue**
7. (Optional) Tambahkan test users untuk testing
8. Klik **Save and Continue**

#### Step 4: Buat OAuth 2.0 Credentials

1. Navigate ke **APIs & Services** > **Credentials**
2. Klik **Create Credentials** > **OAuth client ID**
3. Pilih **Web application**
4. Isi informasi:
   - **Name**: "Simple Store Web Client"
   - **Authorized JavaScript origins**:
     - Development: `http://localhost:8000`
     - Production: `https://yourdomain.com`
   - **Authorized redirect URIs**:
     - Development: `http://localhost:8000/auth/google/callback`
     - Production: `https://yourdomain.com/auth/google/callback`
5. Klik **Create**
6. **Copy Client ID dan Client Secret** (simpan dengan aman)

### 4. Environment Configuration

Tambahkan Google OAuth credentials ke file `.env`:

```env
# Google OAuth Configuration
GOOGLE_CLIENT_ID=your-client-id-from-google-console
GOOGLE_CLIENT_SECRET=your-client-secret-from-google-console
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

**Important Notes:**
- Ganti `your-client-id-from-google-console` dengan Client ID aktual
- Ganti `your-client-secret-from-google-console` dengan Client Secret aktual
- Untuk production, ganti URL dengan domain aktual dan gunakan HTTPS

### 5. Testing

```bash
# Start Laravel server
php artisan serve

# Akses halaman login
# URL: http://localhost:8000/login
# Klik tombol "Masuk dengan Google"
```

## Architecture

### OAuth Flow

Sistem Google OAuth mengimplementasikan OAuth 2.0 authorization code flow yang mencakup beberapa komponen, antara lain:

```
User → Click "Login with Google"
  ↓
Application → Redirect to Google OAuth
  ↓
Google → User Authorization
  ↓
Google → Redirect to Callback URL with Code
  ↓
Application → Exchange Code for Access Token
  ↓
Application → Fetch User Profile from Google
  ↓
Application → Create/Update User & Login
  ↓
User → Redirected to Home (Authenticated)
```

### Components

#### Backend Components

- **GoogleAuthController** (`app/Http/Controllers/Auth/GoogleAuthController.php`)
  - `redirect()`: Redirect user ke Google OAuth consent screen
  - `callback()`: Handle callback dari Google dan process authentication

- **User Model** (`app/Models/User.php`)
  - Updated `$fillable` untuk include `google_id` dan `avatar`
  - Support untuk user tanpa password (Google-only login)

- **Routes** (`routes/web.php`)
  - `GET /auth/google`: Initiate OAuth flow
  - `GET /auth/google/callback`: Handle OAuth callback

- **Configuration** (`config/services.php`)
  - Google OAuth credentials configuration
  - Redirect URI configuration

#### Frontend Components

- **Login Page** (`resources/js/pages/auth/Login.vue`)
  - Tombol "Masuk dengan Google" dengan official Google logo
  - iOS-style design dengan smooth animations

- **Register Page** (`resources/js/pages/auth/Register.vue`)
  - Tombol "Daftar dengan Google"
  - Konsisten styling dengan login page

### Database Schema

```sql
-- Kolom yang ditambahkan ke tabel users
ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE;
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL;
ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL;
```

## Features

### One-Click Authentication

User dapat melakukan login menggunakan Google account tanpa perlu membuat password baru, dimana proses ini hanya memerlukan satu klik pada tombol "Masuk dengan Google" yang kemudian redirect ke Google consent screen untuk authorization, serta mengurangi friction pada proses authentication dan meningkatkan conversion rate.

### Auto-Registration

Sistem secara otomatis membuat user account baru ketika user melakukan login dengan Google untuk pertama kalinya, yang mencakup:
- Extraction data profile dari Google (nama, email, avatar)
- Assignment default role sebagai `customer`
- Email otomatis terverifikasi (`email_verified_at` di-set)
- Password tidak diperlukan (nullable)

### Account Linking

User yang sudah memiliki account dengan email tertentu dapat link Google account mereka ke existing account berdasarkan email matching, dimana pada login berikutnya user memiliki flexibility untuk login menggunakan Google OAuth atau traditional email-password authentication sesuai preferensi mereka.

### Profile Synchronization

Avatar dan profile information dari Google account secara otomatis disinkronisasi ke database, yang memungkinkan aplikasi untuk menampilkan user avatar tanpa memerlukan manual upload, serta memastikan data profile selalu up-to-date dengan Google account.

## Security

### OAuth 2.0 Standard

Implementasi menggunakan OAuth 2.0 authorization code flow yang merupakan industry standard untuk secure authentication, dimana access token tidak pernah exposed ke browser dan semua token exchange dilakukan di server-side untuk mencegah token theft.

### CSRF Protection

Semua OAuth routes dilindungi oleh Laravel CSRF middleware yang memvalidasi token pada setiap request untuk mencegah cross-site request forgery attacks, serta memastikan bahwa callback request benar-benar berasal dari Google OAuth flow yang legitimate.

### Data Privacy

Aplikasi hanya meminta minimal scopes yang diperlukan (`userinfo.email`, `userinfo.profile`, `openid`) tanpa meminta akses ke sensitive data lainnya, serta tidak menyimpan Google access token di database untuk menjaga privacy dan security user data.

### HTTPS Requirement

Production environment wajib menggunakan HTTPS untuk mengenkripsi data transmission antara aplikasi dan Google servers, dimana Google OAuth akan reject requests dari non-HTTPS URLs pada production untuk memastikan secure communication.

## Configuration

### Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `GOOGLE_CLIENT_ID` | OAuth Client ID dari Google Console | `123456789-abc.apps.googleusercontent.com` |
| `GOOGLE_CLIENT_SECRET` | OAuth Client Secret (keep secret!) | `GOCSPX-abc123def456` |
| `GOOGLE_REDIRECT_URI` | Callback URL untuk OAuth | `http://localhost:8000/auth/google/callback` |

### Development vs Production

| Environment | Base URL | Protocol | Redirect URI |
|-------------|----------|----------|--------------|
| Development | `localhost:8000` | HTTP | `http://localhost:8000/auth/google/callback` |
| Production | `yourdomain.com` | HTTPS | `https://yourdomain.com/auth/google/callback` |

**Important:**
- URL di Google Cloud Console harus sama persis dengan URL di `.env`
- Production harus menggunakan HTTPS (mandatory)
- Gunakan credentials yang berbeda untuk dev dan production

## Troubleshooting

### Error: redirect_uri_mismatch

**Cause:** Redirect URI yang dikirim aplikasi tidak match dengan yang terdaftar di Google Console.

**Solution:**
1. Verifikasi authorized redirect URIs di Google Console > Credentials
2. Pastikan URL format exact match (case-sensitive):
   - Dev: `http://localhost:8000/auth/google/callback`
   - Prod: `https://yourdomain.com/auth/google/callback`
3. Periksa `GOOGLE_REDIRECT_URI` di `.env`
4. Clear config cache: `php artisan config:clear`
5. Restart Laravel server

### Error: Access blocked: This app's request is invalid

**Cause:** OAuth application belum dikonfigurasi dengan benar atau API belum enabled.

**Solution:**
1. Pastikan Google+ API sudah enabled di Google Console
2. Verifikasi OAuth consent screen sudah complete
3. Periksa scopes sudah ditambahkan (`userinfo.email`, `userinfo.profile`, `openid`)
4. Jika test mode, pastikan email sudah di test users list

### User Tidak Dapat Login

**Diagnostic Steps:**

1. **Check Credentials:**
   ```bash
   # Verifikasi .env
   cat .env | grep GOOGLE_
   ```

2. **Check Database:**
   ```bash
   php artisan tinker
   >>> Schema::hasColumn('users', 'google_id')
   => true
   ```

3. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Clear Cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

### Migration Already Run Issue

**Problem:** Migration sudah dijalankan sebelum Google OAuth di-implement.

**Solution:**

```bash
# Option 1: Rollback dan re-run (HATI-HATI: akan drop data)
php artisan migrate:rollback --step=1
php artisan migrate

# Option 2: Run specific migration (RECOMMENDED)
php artisan migrate --path=/database/migrations/2025_12_22_015036_add_google_id_to_users_table.php

# Option 3: Manual database update (jika migration file tidak ada)
php artisan tinker
>>> DB::statement('ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE');
>>> DB::statement('ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL');
>>> DB::statement('ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL');
```

## Testing

### Manual Testing Checklist

- [ ] User baru dapat register via Google
- [ ] User existing dapat link Google account (by email)
- [ ] Avatar dari Google ditampilkan di aplikasi
- [ ] Email otomatis terverifikasi
- [ ] Default role customer di-assign
- [ ] User dapat logout dan login kembali via Google
- [ ] Error handling untuk OAuth failure
- [ ] Redirect ke intended page setelah login

### Test Scenarios

#### Scenario 1: New User Registration

1. User belum punya account di Simple Store
2. Klik "Masuk dengan Google"
3. Login dengan Google account
4. **Expected:** User account baru dibuat, logged in, redirect ke home

#### Scenario 2: Existing User (Email Match)

1. User sudah punya account dengan email `user@example.com`
2. Klik "Masuk dengan Google"
3. Login dengan Google account `user@example.com`
4. **Expected:** Google ID di-link ke existing account, logged in

#### Scenario 3: OAuth Cancellation

1. User klik "Masuk dengan Google"
2. Di Google consent screen, user klik "Cancel"
3. **Expected:** Redirect ke login page dengan error message

## Maintenance

### Credentials Rotation

Jika perlu rotate OAuth credentials untuk security purposes:

1. Generate new credentials di Google Console
2. Update `.env` dengan new credentials
3. Clear config cache: `php artisan config:clear`
4. Restart application
5. Test OAuth flow
6. Delete old credentials di Google Console

### Monitoring

Monitor OAuth authentication success rate dan error logs untuk detect issues:

```bash
# Check recent OAuth errors
tail -100 storage/logs/laravel.log | grep "GoogleAuth"

# Monitor user creation via Google
php artisan tinker
>>> User::whereNotNull('google_id')->count()
```

## Related Documentation

- **User Manual:** [User Authentication Guide](../02_USER_DOCUMENTATION/01_User_Manual.md)
- **API Keys Reference:** [API Keys Management](./02_API_Keys_Reference.md)
- **Troubleshooting:** [Common Issues](../09_TROUBLESHOOTING/01_Common_Issues_Solutions.md)

## Support

Jika mengalami masalah dengan Google OAuth integration:

- **Developer:** Zulfikar Hidayatullah
- **Phone:** +62 857-1583-8733
- **Email:** [Your Email]
- **Documentation:** `/docs/07_THIRD_PARTY_SERVICES/04_Google_OAuth_Integration.md`

---

**Last Updated:** 2025-12-22  
**Version:** 1.8.0  
**Author:** Zulfikar Hidayatullah

