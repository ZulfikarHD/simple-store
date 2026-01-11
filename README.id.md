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

Teman saya butuh website e-commerce untuk tokonya, jadi saya buatin ini. Sistem toko online yang straightforward dengan product management, shopping cart, dan checkout via WhatsApp. Tidak terlalu kompleks, fokus ke fitur-fitur essential yang memang dibutuhkan.

Dibangun dengan Laravel dan Vue karena stack yang saya familiar. Ada beberapa sentuhan seperti iOS-style animations dan Google OAuth untuk memberikan user experience yang lebih modern. Admin panel-nya cukup lengkap untuk manage products, orders, dan customize WhatsApp message templates.

Sudah production-ready dengan security yang cukup solid (OWASP compliant), tapi tetap saya sarankan untuk review code-nya sendiri sebelum deploy untuk bisnis yang serius.

---

## 🎯 Features

### ✨ Customer Features

- 🛍️ Product catalog dengan category filtering dan search
- 🛒 Shopping cart dengan session persistence
- 💳 Checkout dengan WhatsApp integration
- 🔐 Google OAuth authentication
- 📧 Email/password authentication
- 🔒 Two-Factor Authentication (2FA/TOTP)
- 📦 Order tracking dan history
- 👤 User account management

### 👨‍💼 Admin Features

- 📊 Dashboard dengan real-time statistics
- 📦 Product management (CRUD)
- 🏷️ Category management (CRUD)
- 📋 Order management dengan status workflow
- 🎨 Store branding (logo, tagline, favicon)
- 💬 Customizable WhatsApp message templates
- 🎯 Customizable timeline icons
- 🌍 Multi-region phone formatting (8 negara)
- 🔔 Browser notifications untuk order baru

### 🔒 Security Features (OWASP Compliant)

- ⏱️ Rate limiting (cart, checkout, upload, password)
- 🛡️ CSRF protection
- 🚫 XSS prevention
- 💉 SQL injection prevention
- 📝 Template injection prevention
- 🔐 Password hashing (bcrypt)
- ✉️ Email verification
- 🔑 2FA dengan recovery codes

### 🎨 UI/UX Features

- 📱 iOS-like design system
- 🎭 Spring physics animations
- 🌓 Dark mode support
- 📲 Mobile-first responsive design
- ⚡ Haptic feedback simulation
- 🎯 Press feedback effects

---

## 🚀 Tech Stack

### Backend

| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 8.4.14 | Server-side language |
| **Laravel** | 12.43.1 | Web application framework |
| **MySQL** | 8.0+ | Database management system |
| **Inertia Laravel** | 2.0.16 | SPA bridge |
| **Laravel Fortify** | 1.33.0 | Authentication scaffolding |
| **Laravel Socialite** | 5.24.0 | OAuth authentication |
| **Laravel Wayfinder** | 0.1.12 | Type-safe routing |

### Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| **Vue.js** | 3.5.22 | Progressive JavaScript framework |
| **Inertia Vue** | 2.2.7 | Vue adapter untuk Inertia.js |
| **TypeScript** | 5.2.2 | Typed JavaScript |
| **TailwindCSS** | 4.1.14 | Utility-first CSS framework |
| **Vite** | 7.0.4 | Build tool |
| **Reka UI** | 2.4.1 | Headless UI components |
| **Lucide Vue Next** | 0.468.0 | Icon library |
| **Motion V** | 1.7.4 | Animation library |
| **VueUse Core** | 12.8.2 | Vue composition utilities |

### Development Tools

| Tool | Version | Purpose |
|------|---------|---------|
| **PHPUnit** | 11.5.46 | PHP testing framework |
| **Laravel Pint** | 1.26.0 | PHP code formatter |
| **ESLint** | 9.37.0 | JavaScript linter |
| **Prettier** | 3.6.2 | Code formatter |

---

## 📦 Prerequisites

Sebelum memulai, pastikan sudah terinstall:

- **PHP** 8.2 atau lebih tinggi
- **Composer** (versi terbaru)
- **Node.js** 18+ dan **Yarn**
- **MySQL** 8.0+ atau **PostgreSQL** 13+
- **Git**

---

## 🔧 Installation

### 1. Clone repository

```bash
git clone https://github.com/ZulfikarHD/simple-store.git
cd simple-store
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install JavaScript dependencies

```bash
yarn install
```

### 4. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure file `.env`

```env
APP_NAME="Simple Store"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simple_store
DB_USERNAME=root
DB_PASSWORD=

# Google OAuth (optional)
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

### 6. Database setup

```bash
php artisan migrate --seed
```

### 7. Storage setup

```bash
php artisan storage:link
```

### 8. Run development server

```bash
# Menggunakan composer script (recommended - runs all services)
composer dev

# Atau manual
php artisan serve
yarn dev
```

Buka `http://localhost:8000` di browser.

---

## ⚙️ Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_NAME` | Application name | Simple Store |
| `APP_URL` | Application URL | http://localhost |
| `DB_CONNECTION` | Database driver | mysql |
| `GOOGLE_CLIENT_ID` | Google OAuth client ID | - |
| `GOOGLE_CLIENT_SECRET` | Google OAuth secret | - |

### Google OAuth Setup

Untuk setup Google OAuth yang lengkap, lihat [GOOGLE_OAUTH_SETUP.md](docs/GOOGLE_OAUTH_SETUP.md)

---

## 🎮 Usage

### Default Credentials

Setelah seeding, bisa login dengan:

**Admin Account:**
- Email: `admin@example.com`
- Password: `password`

**User Account:**
- Email: `user@example.com`
- Password: `password`

### Admin Panel

Akses admin panel di `/admin` setelah login sebagai admin.

---

## 🏗️ Development

### Project Structure

```
simple-store/
├── app/                    # Core application
│   ├── Http/Controllers/  # Request handlers
│   ├── Models/            # Eloquent models
│   ├── Services/          # Business logic
│   └── Policies/          # Authorization
├── resources/
│   └── js/
│       ├── pages/         # Inertia pages
│       ├── components/    # Vue components
│       └── composables/   # Composition functions
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
└── tests/                 # PHPUnit tests
```

### Coding Standards

**PHP (Laravel Pint):**
```bash
./vendor/bin/pint
```

**JavaScript/Vue (ESLint & Prettier):**
```bash
yarn lint
yarn format
```

### Running Tests

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

Untuk deployment guide yang lebih detail, lihat [Deployment Guide](docs/04_TECHNICAL_DOCUMENTATION/06_Deployment_Guide.md)

---

## 📖 Documentation

Documentation lengkap tersedia di directory `/docs`:

- [User Manual](docs/02_USER_DOCUMENTATION/01_User_Manual.md)
- [Admin Guide](docs/03_ADMIN_DOCUMENTATION/01_Admin_Guide.md)
- [Technical Documentation](docs/04_TECHNICAL_DOCUMENTATION/)
- [API Documentation](docs/04_TECHNICAL_DOCUMENTATION/03_API_Documentation.md)
- [Security Documentation](docs/04_TECHNICAL_DOCUMENTATION/05_Security_Documentation.md)

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run dengan coverage
php artisan test --coverage
```

**Test Coverage:** 98%+

---

### Reporting Vulnerabilities

Jika menemukan security vulnerability, silakan email ke:
**zulfikar.h@diasection.org**

---

## 🤝 Contributing

Contributions sangat diterima! Silakan ikuti langkah berikut:

1. Fork repository ini
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buka Pull Request

---

## 📄 License

Project ini menggunakan MIT License - lihat file [LICENSE](LICENSE) untuk detail.

---

### ⚠️ Catatan Penting Soal Security

**Mohon dibaca dengan seksama:**

- 🔐 **Jangan share informasi sensitif** - API keys, database credentials, atau data penting lainnya jangan pernah di-commit ke repository
- 🔍 **Jangan langsung percaya sama open-source project manapun** - termasuk yang ini. Always review the code yourself
- ✅ **Review security implementation sendiri** sebelum deploy ke production
- 🐛 **Saya juga masih terus belajar** - jadi kalau menemukan bug atau security issue, feedback-nya sangat saya hargai

Project ini saya share dengan niat baik, tapi security adalah tanggung jawab bersama. Selalu lakukan due diligence sendiri sebelum menggunakan code orang lain di production.

---

## 👨‍💻 Author

**Zulfikar Hidayatullah**

- 📧 Email: zulfikar.h@diasection.org
- 📱 Phone: +62 857-1583-8733
- 🐙 GitHub: [@ZulfikarHD](https://github.com/ZulfikarHD)

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [Vue.js](https://vuejs.org) - The Progressive JavaScript Framework
- [Inertia.js](https://inertiajs.com) - Modern monolith approach
- [TailwindCSS](https://tailwindcss.com) - Utility-first CSS framework
- [Reka UI](https://reka-ui.com) - Vue component library
- All contributors yang membantu improve project ini

---

## 📞 Support

- 📧 Email: zulfikar.h@diasection.org
- 💬 Issues: [GitHub Issues](https://github.com/ZulfikarHD/simple-store/issues)
- 📖 Documentation: [docs/](docs/)

---

Version 1.9.0 | Production Ready
