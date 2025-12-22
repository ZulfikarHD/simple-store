# Setup Google OAuth Authentication

## Overview

Dokumentasi ini merupakan panduan lengkap untuk mengkonfigurasi Google OAuth authentication di Simple Store yang bertujuan untuk mengintegrasikan sistem autentikasi menggunakan Google account, yaitu: setup Google Cloud Console, konfigurasi OAuth credentials, dan implementasi environment variables yang diperlukan untuk menjalankan fitur login dengan Google secara optimal.

## Prerequisites

Komponen yang diperlukan untuk melakukan setup Google OAuth authentication, antara lain:

- **Akun Google** - Untuk mengakses Google Cloud Console dan membuat OAuth credentials
- **Google Cloud Console Access** - Platform untuk konfigurasi OAuth application di [console.cloud.google.com](https://console.cloud.google.com/)
- **Simple Store Application** - Aplikasi Laravel yang sudah ter-install dengan Laravel Socialite package

## Quick Start

### Installation

Proses setup dilakukan melalui beberapa tahapan konfigurasi di Google Cloud Console yang mencakup pembuatan project, aktivasi API, dan generation OAuth credentials, dimana setiap tahapan memiliki fungsi spesifik untuk memastikan integrasi berjalan dengan baik.

### 1. Pembuatan Project di Google Cloud Console

Langkah pertama adalah membuat project baru di Google Cloud Console yang akan digunakan sebagai container untuk OAuth application, yaitu:

1. Akses [Google Cloud Console](https://console.cloud.google.com/) menggunakan akun Google Anda
2. Klik **Select a project** di navigation bar atas, kemudian pilih **New Project**
3. Masukkan nama project yang deskriptif (contoh: "Simple Store OAuth") untuk memudahkan identifikasi
4. Klik **Create** dan tunggu hingga project creation process selesai

### 2. Aktivasi Google+ API

Google+ API diperlukan untuk mengakses user profile information dari Google account, dengan langkah aktivasi sebagai berikut:

1. Di sidebar navigation, pilih **APIs & Services** > **Library** untuk membuka API library
2. Gunakan search box untuk mencari "Google+ API"
3. Klik pada hasil pencarian untuk membuka detail page
4. Klik tombol **Enable** untuk mengaktifkan API tersebut pada project Anda

### 3. Konfigurasi OAuth Consent Screen

OAuth consent screen merupakan halaman yang ditampilkan kepada user saat mereka melakukan authorization untuk aplikasi Anda, dimana konfigurasi ini mencakup app information, scopes, dan test users yang diizinkan menggunakan OAuth application:

1. Di sidebar navigation, pilih **APIs & Services** > **OAuth consent screen**
2. Pilih **External** sebagai User Type untuk mengizinkan semua user dengan Google account, kemudian klik **Create**
3. Isi informasi aplikasi yang diperlukan dengan data sebagai berikut:
   - **App name**: Nama toko Anda (contoh: "Simple Store") yang akan ditampilkan pada consent screen
   - **User support email**: Email yang dapat dihubungi user untuk support
   - **Developer contact information**: Email developer untuk komunikasi dari Google
4. Klik **Save and Continue** untuk melanjutkan ke konfigurasi scopes
5. Di section **Scopes**, klik **Add or Remove Scopes** untuk menentukan data yang akan diakses
6. Pilih scope minimal yang diperlukan untuk authentication, antara lain:
   - `userinfo.email` - Untuk mengakses email address user
   - `userinfo.profile` - Untuk mengakses nama dan avatar user
   - `openid` - Untuk OpenID Connect authentication
7. Klik **Update** untuk menyimpan scope selection, lalu klik **Save and Continue**
8. Di section **Test users**, tambahkan email account untuk testing sebelum publish (opsional namun direkomendasikan)
9. Klik **Save and Continue** untuk menyelesaikan consent screen configuration

### 4. Pembuatan OAuth 2.0 Credentials

OAuth 2.0 credentials terdiri dari Client ID dan Client Secret yang digunakan untuk mengidentifikasi aplikasi Anda saat melakukan authentication request ke Google, dengan konfigurasi authorized origins dan redirect URIs untuk security purposes:

1. Di sidebar navigation, pilih **APIs & Services** > **Credentials**
2. Klik **Create Credentials** kemudian pilih **OAuth client ID** dari dropdown menu
3. Pilih **Web application** sebagai Application type karena Simple Store adalah web-based application
4. Isi informasi credentials dengan detail sebagai berikut:
   - **Name**: Nama deskriptif untuk credentials (contoh: "Simple Store Web Client")
   - **Authorized JavaScript origins**: URL base aplikasi yang diizinkan melakukan OAuth request, yaitu:
     - `http://localhost:8000` untuk development environment
     - `https://yourdomain.com` untuk production environment (ganti dengan domain aktual)
   - **Authorized redirect URIs**: URL callback yang akan menerima response dari Google setelah authentication, yaitu:
     - `http://localhost:8000/auth/google/callback` untuk development
     - `https://yourdomain.com/auth/google/callback` untuk production
5. Klik **Create** untuk generate credentials
6. Copy dan simpan **Client ID** dan **Client Secret** yang muncul di modal dialog dengan aman, dimana credentials ini akan digunakan pada environment configuration

### 5. Konfigurasi Environment Variables

Environment variables digunakan untuk menyimpan OAuth credentials secara aman tanpa hardcode di source code, dimana konfigurasi ini memungkinkan aplikasi untuk melakukan authentication request ke Google dengan credentials yang valid:

1. Buka file `.env` yang terletak di root directory project Simple Store
2. Tambahkan konfigurasi Google OAuth dengan format sebagai berikut:

```env
GOOGLE_CLIENT_ID=your-client-id-here
GOOGLE_CLIENT_SECRET=your-client-secret-here
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

3. Ganti placeholder `your-client-id-here` dan `your-client-secret-here` dengan credentials aktual yang Anda dapatkan dari Google Cloud Console pada step sebelumnya
4. Pastikan `GOOGLE_REDIRECT_URI` sesuai dengan URL yang didaftarkan di authorized redirect URIs pada Google Cloud Console

### 6. Testing dan Verification

Proses testing dilakukan untuk memverifikasi bahwa OAuth integration berfungsi dengan baik dan user dapat melakukan authentication menggunakan Google account, yaitu:

1. Jalankan aplikasi Laravel menggunakan command:
```bash
php artisan serve
```

2. Buka web browser dan akses halaman login di URL: `http://localhost:8000/login`
3. Klik tombol **Masuk dengan Google** yang terletak di bawah form login
4. Sistem akan redirect ke Google consent screen dimana Anda dapat login dengan akun Google
5. Setelah memberikan permission, Google akan redirect kembali ke aplikasi dengan authentication token
6. Jika berhasil, Anda akan diarahkan ke halaman home dalam keadaan sudah logged in dengan data profile dari Google account

## Configuration Notes

### Development vs Production Environment

Konfigurasi OAuth credentials berbeda untuk development dan production environment yang bertujuan untuk memisahkan testing environment dari live application, dimana setiap environment memiliki authorized URLs yang spesifik:

- **Development Environment**: Menggunakan `http://localhost:8000` sebagai base URL dengan HTTP protocol untuk local testing
- **Production Environment**: Menggunakan domain aktual (contoh: `https://yourdomain.com`) dengan HTTPS protocol yang mandatory untuk security
- **URL Consistency**: Pastikan URL yang didaftarkan di Google Cloud Console authorized URIs sama persis dengan URL yang dikonfigurasi di file `.env` untuk menghindari redirect_uri_mismatch error

### Security Best Practices

Implementasi OAuth authentication memerlukan perhatian khusus terhadap security untuk melindungi credentials dan user data, dengan guidelines sebagai berikut:

- **Environment File Protection**: Jangan commit file `.env` ke version control system (Git) karena berisi sensitive credentials yang dapat disalahgunakan
- **Client Secret Confidentiality**: Jangan share Client Secret ke siapa pun termasuk di public forums, chat groups, atau documentation yang accessible secara public
- **Separate Credentials**: Gunakan OAuth credentials yang berbeda untuk development dan production environment untuk isolasi dan security yang lebih baik
- **HTTPS Requirement**: Production environment harus menggunakan HTTPS untuk mengenkripsi data transmission antara aplikasi dan Google servers

## Troubleshooting

### Error: redirect_uri_mismatch

Error ini terjadi ketika redirect URI yang dikirim oleh aplikasi tidak match dengan authorized redirect URIs yang terdaftar di Google Cloud Console, dimana solusinya adalah memastikan URL callback sama persis (case-sensitive dan termasuk protocol) dengan yang didaftarkan:

**Verification Steps:**
1. Periksa authorized redirect URIs di Google Cloud Console > Credentials > OAuth 2.0 Client IDs
2. Pastikan URL format sesuai dengan environment:
   - Development: `http://localhost:8000/auth/google/callback`
   - Production: `https://yourdomain.com/auth/google/callback`
3. Periksa `GOOGLE_REDIRECT_URI` di file `.env` apakah sama dengan yang terdaftar
4. Restart Laravel server setelah mengubah environment variables

### Error: Access blocked: This app's request is invalid

Error ini mengindikasikan bahwa OAuth application belum dikonfigurasi dengan benar atau API yang diperlukan belum diaktifkan, dengan solusi verification sebagai berikut:

**Verification Steps:**
1. Pastikan Google+ API sudah enabled di Google Cloud Console > APIs & Services > Library
2. Verifikasi OAuth consent screen sudah dikonfigurasi dengan lengkap termasuk app name dan support email
3. Periksa apakah scopes yang diperlukan (`userinfo.email`, `userinfo.profile`, `openid`) sudah ditambahkan
4. Jika menggunakan test mode, pastikan email account yang digunakan untuk login sudah ditambahkan ke test users list

### User Tidak Dapat Login

Jika user mengalami kesulitan login setelah redirect dari Google, lakukan troubleshooting dengan checking beberapa komponen sistem, yaitu:

**Diagnostic Steps:**
1. **Credentials Validation**: Verifikasi bahwa `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET` di file `.env` sudah correct dan tidak ada typo atau extra spaces
2. **Database Migration**: Pastikan migration untuk Google OAuth sudah dijalankan dengan command `php artisan migrate` yang menambahkan kolom `google_id` dan `avatar` ke users table
3. **Error Logs**: Cek Laravel error logs di `storage/logs/laravel.log` untuk melihat detailed error message dan stack trace yang dapat membantu identify root cause
4. **Cache Clear**: Jalankan `php artisan config:clear` untuk clear cached configuration jika baru mengubah environment variables
5. **Session Storage**: Pastikan session driver berfungsi dengan baik dan tidak ada issue dengan session storage (database/file/redis)

## Features

Implementasi Google OAuth authentication di Simple Store menyediakan beberapa fitur yang bertujuan untuk meningkatkan user experience dan mengurangi friction pada proses authentication, antara lain:

### One-Click Login dengan Google

User dapat melakukan authentication menggunakan Google account mereka tanpa perlu membuat password baru atau mengingat credentials tambahan, dimana proses ini hanya memerlukan satu klik pada tombol "Masuk dengan Google" yang kemudian redirect ke Google consent screen untuk authorization.

### Auto-Registration untuk User Baru

Sistem secara otomatis membuat user account baru ketika user melakukan login dengan Google untuk pertama kalinya, yang mencakup extraction data profile dari Google (nama, email, avatar) dan assignment default role sebagai customer, sehingga user dapat langsung menggunakan aplikasi tanpa melalui proses registrasi manual yang panjang.

### Account Linking untuk Existing Users

User yang sudah memiliki account dengan email tertentu dapat link Google account mereka ke existing account berdasarkan email matching, dimana pada login berikutnya user memiliki flexibility untuk login menggunakan Google OAuth atau traditional email-password authentication sesuai preferensi mereka.

### Profile Synchronization

Avatar dan profile information dari Google account secara otomatis disinkronisasi ke Simple Store database, yang memungkinkan aplikasi untuk menampilkan user avatar tanpa memerlukan manual upload, serta email address otomatis terverifikasi karena sudah divalidasi oleh Google authentication system.

## Support

Jika mengalami masalah, hubungi developer:
- **Developer**: Zulfikar Hidayatullah
- **Phone**: +62 857-1583-8733

