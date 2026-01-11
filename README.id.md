# 🛒 Simple Store

🇮🇩 **Bahasa Indonesia** | [🇬🇧 English](README.md)

> Platform e-commerce modern dan aman yang dibangun dengan Laravel 12 dan Vue 3

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel](https://img.shields.io/badge/Laravel-12.43-red.svg)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue-3.5-green.svg)](https://vuejs.org)
[![PHP](https://img.shields.io/badge/PHP-8.4-blue.svg)](https://php.net)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.2-blue.svg)](https://www.typescriptlang.org)

---

## 📋 Tentang

Simple Store adalah platform e-commerce lengkap yang dirancang untuk bisnis kecil hingga menengah. Dibangun dengan teknologi modern dan praktik keamanan terbaik, aplikasi ini menyediakan pengalaman belanja yang mulus untuk pelanggan dan alat manajemen yang powerful untuk administrator.

### ✨ Keunggulan Utama

- 🎨 **Desain ala iOS** - UI modern dan indah dengan animasi spring physics
- 🔒 **Keamanan Utama** - Compliant dengan OWASP dengan langkah keamanan komprehensif
- 📱 **Optimasi Mobile** - Desain responsif mobile-first
- 🚀 **Stack Modern** - Laravel 12, Vue 3, TypeScript, Tailwind CSS 4
- 💬 **Integrasi WhatsApp** - Komunikasi langsung dengan pelanggan
- 🌐 **Dukungan Multi-region** - Format telepon untuk 8 negara
- 🔐 **Google OAuth** - Autentikasi one-click

---

## 🎯 Fitur

### ✨ Fitur Pelanggan

- 🛍️ Katalog produk dengan filter kategori dan pencarian
- 🛒 Keranjang belanja dengan persistensi sesi
- 💳 Checkout dengan integrasi WhatsApp
- 🔐 Autentikasi Google OAuth
- 📧 Autentikasi email/password
- 🔒 Two-Factor Authentication (2FA/TOTP)
- 📦 Pelacakan dan riwayat pesanan
- 👤 Manajemen akun pengguna

### 👨‍💼 Fitur Admin

- 📊 Dashboard dengan statistik real-time
- 📦 Manajemen produk (CRUD)
- 🏷️ Manajemen kategori (CRUD)
- 📋 Manajemen pesanan dengan alur status
- 🎨 Branding toko (logo, tagline, favicon)
- 💬 Kustomisasi template pesan WhatsApp
- 🎯 Kustomisasi ikon timeline
- 🌍 Format telepon multi-region (8 negara)
- 🔔 Notifikasi browser untuk pesanan baru

### 🔒 Fitur Keamanan (Compliant OWASP)

- ⏱️ Rate limiting (cart, checkout, upload, password)
- 🛡️ Proteksi CSRF
- 🚫 Pencegahan XSS
- 💉 Pencegahan SQL injection
- 📝 Pencegahan template injection
- 🔐 Password hashing (bcrypt)
- ✉️ Verifikasi email
- 🔑 2FA dengan recovery codes

### 🎨 Fitur UI/UX

- 📱 Sistem desain ala iOS
- 🎭 Animasi spring physics
- 🌓 Dukungan dark mode
- 📲 Desain responsif mobile-first
- ⚡ Simulasi haptic feedback
- 🎯 Efek press feedback

---

## 🚀 Tech Stack

### Backend

| Teknologi | Versi | Kegunaan |
|-----------|-------|----------|
| **PHP** | 8.4.14 | Bahasa server-side |
| **Laravel** | 12.43.1 | Framework aplikasi web |
| **MySQL** | 8.0+ | Sistem manajemen database |
| **Inertia Laravel** | 2.0.16 | Bridge SPA |
| **Laravel Fortify** | 1.33.0 | Scaffolding autentikasi |
| **Laravel Socialite** | 5.24.0 | Autentikasi OAuth |
| **Laravel Wayfinder** | 0.1.12 | Routing type-safe |

### Frontend

| Teknologi | Versi | Kegunaan |
|-----------|-------|----------|
| **Vue.js** | 3.5.22 | Framework JavaScript progresif |
| **Inertia Vue** | 2.2.7 | Adapter Vue untuk Inertia.js |
| **TypeScript** | 5.2.2 | JavaScript dengan tipe |
| **TailwindCSS** | 4.1.14 | Framework CSS utility-first |
| **Vite** | 7.0.4 | Build tool |
| **Reka UI** | 2.4.1 | Komponen UI headless |
| **Lucide Vue Next** | 0.468.0 | Library ikon |
| **Motion V** | 1.7.4 | Library animasi |
| **VueUse Core** | 12.8.2 | Utilitas komposisi Vue |

### Development Tools

| Tool | Versi | Kegunaan |
|------|-------|----------|
| **PHPUnit** | 11.5.46 | Framework testing PHP |
| **Laravel Pint** | 1.26.0 | Formatter kode PHP |
| **ESLint** | 9.37.0 | Linter JavaScript |
| **Prettier** | 3.6.2 | Formatter kode |

---

## 📦 Prasyarat

Sebelum memulai, pastikan Anda memiliki:

- **PHP** 8.2 atau lebih tinggi
- **Composer** (versi terbaru)
- **Node.js** 18+ dan **Yarn**
- **MySQL** 8.0+ atau **PostgreSQL** 13+
- **Git**

---

## 🔧 Instalasi

### 1. Clone repository

```bash
git clone https://github.com/ZulfikarHD/simple-store.git
cd simple-store
```

### 2. Install dependensi PHP

```bash
composer install
```

### 3. Install dependensi JavaScript

```bash
yarn install
```

### 4. Setup environment

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Konfigurasi file `.env` Anda

```env
APP_NAME="Simple Store"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simple_store
DB_USERNAME=root
DB_PASSWORD=

# Google OAuth (opsional)
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

### 6. Setup database

```bash
php artisan migrate --seed
```

### 7. Setup storage

```bash
php artisan storage:link
```

### 8. Jalankan development server

```bash
# Menggunakan composer script (direkomendasikan - menjalankan semua service)
composer dev

# Atau manual
php artisan serve
yarn dev
```

Kunjungi `http://localhost:8000` di browser Anda.

---

## ⚙️ Konfigurasi

### Environment Variables

| Variable | Deskripsi | Default |
|----------|-----------|---------|
| `APP_NAME` | Nama aplikasi | Simple Store |
| `APP_URL` | URL aplikasi | http://localhost |
| `DB_CONNECTION` | Driver database | mysql |
| `GOOGLE_CLIENT_ID` | Client ID Google OAuth | - |
| `GOOGLE_CLIENT_SECRET` | Secret Google OAuth | - |

### Setup Google OAuth

Untuk konfigurasi Google OAuth yang detail, lihat [GOOGLE_OAUTH_SETUP.md](docs/GOOGLE_OAUTH_SETUP.md)

---

## 🎮 Penggunaan

### Kredensial Default

Setelah seeding, Anda dapat login dengan:

**Akun Admin:**
- Email: `admin@example.com`
- Password: `password`

**Akun User:**
- Email: `user@example.com`
- Password: `password`

### Panel Admin

Akses panel admin di `/admin` setelah login sebagai admin.

---

## 🏗️ Development

### Struktur Proyek

```
simple-store/
├── app/                    # Aplikasi inti
│   ├── Http/Controllers/  # Request handlers
│   ├── Models/            # Model Eloquent
│   ├── Services/          # Business logic
│   └── Policies/          # Otorisasi
├── resources/
│   └── js/
│       ├── pages/         # Halaman Inertia
│       ├── components/    # Komponen Vue
│       └── composables/   # Fungsi komposisi
├── database/
│   ├── migrations/        # Migrasi database
│   └── seeders/           # Seeder database
└── tests/                 # Testing PHPUnit
```

### Standar Coding

**PHP (Laravel Pint):**
```bash
./vendor/bin/pint
```

**JavaScript/Vue (ESLint & Prettier):**
```bash
yarn lint
yarn format
```

### Menjalankan Tests

```bash
# PHP tests
php artisan test

# Dengan coverage
php artisan test --coverage
```

---

## 🚀 Deployment

### Production Build

```bash
composer install --optimize-autoloader --no-dev
yarn install --production
yarn build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Untuk instruksi deployment yang detail, lihat [Panduan Deployment](docs/04_TECHNICAL_DOCUMENTATION/06_Deployment_Guide.md)

---

## 📖 Dokumentasi

Dokumentasi komprehensif tersedia di direktori `/docs`:

- [Manual Pengguna](docs/02_USER_DOCUMENTATION/01_User_Manual.md)
- [Panduan Admin](docs/03_ADMIN_DOCUMENTATION/01_Admin_Guide.md)
- [Dokumentasi Teknis](docs/04_TECHNICAL_DOCUMENTATION/)
- [Dokumentasi API](docs/04_TECHNICAL_DOCUMENTATION/03_API_Documentation.md)
- [Dokumentasi Keamanan](docs/04_TECHNICAL_DOCUMENTATION/05_Security_Documentation.md)

---

## 🧪 Testing

```bash
# Jalankan semua tests
php artisan test

# Jalankan test suite spesifik
php artisan test --testsuite=Feature

# Jalankan dengan coverage
php artisan test --coverage
```

**Test Coverage:** 98%+

---

## 🔒 Keamanan

Keamanan adalah prioritas utama. Proyek ini mengimplementasikan:

- Praktik keamanan terbaik OWASP
- Rate limiting pada endpoint sensitif
- Proteksi CSRF
- Pencegahan XSS
- Pencegahan SQL injection
- Password hashing dengan bcrypt
- Two-Factor Authentication
- Verifikasi email

### Melaporkan Kerentanan

Jika Anda menemukan kerentanan keamanan, silakan email ke:
**zulfikar.h@diasection.org**

---

## 🗺️ Roadmap

- [ ] Dukungan multi-bahasa
- [ ] Integrasi payment gateway (Midtrans, Xendit)
- [ ] Notifikasi email
- [ ] Review dan rating produk
- [ ] Fitur wishlist
- [ ] Pencarian dan filter lanjutan
- [ ] Export pesanan ke PDF/Excel
- [ ] Manajemen inventori
- [ ] Sistem diskon/kupon

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan ikuti langkah berikut:

1. Fork repository
2. Buat feature branch (`git checkout -b feature/FiturKeren`)
3. Commit perubahan Anda (`git commit -m 'Tambah fitur keren'`)
4. Push ke branch (`git push origin feature/FiturKeren`)
5. Buka Pull Request

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah MIT License - lihat file [LICENSE](LICENSE) untuk detail.

---

## 👨‍💻 Pembuat

**Zulfikar Hidayatullah**

- 📧 Email: zulfikar.h@diasection.org
- 📱 Telepon: +62 857-1583-8733
- 🐙 GitHub: [@ZulfikarHD](https://github.com/ZulfikarHD)

---

## 🙏 Penghargaan

- [Laravel](https://laravel.com) - Framework PHP
- [Vue.js](https://vuejs.org) - Framework JavaScript Progresif
- [Inertia.js](https://inertiajs.com) - Pendekatan monolith modern
- [TailwindCSS](https://tailwindcss.com) - Framework CSS utility-first
- [Reka UI](https://reka-ui.com) - Library komponen Vue
- Semua kontributor yang membantu meningkatkan proyek ini

---

## 📞 Dukungan

- 📧 Email: zulfikar.h@diasection.org
- 💬 Issues: [GitHub Issues](https://github.com/ZulfikarHD/simple-store/issues)
- 📖 Dokumentasi: [docs/](docs/)

---

**Dibuat dengan ❤️ di Indonesia**

Versi 1.9.0 | Production Ready
