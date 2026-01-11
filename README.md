# 🛒 Simple Store

[🇮🇩 Bahasa Indonesia](README.id.md) | 🇬🇧 **English**

> A modern, secure e-commerce platform built with Laravel 12 and Vue 3

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![Laravel](https://img.shields.io/badge/Laravel-12.43-red.svg)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue-3.5-green.svg)](https://vuejs.org)
[![PHP](https://img.shields.io/badge/PHP-8.4-blue.svg)](https://php.net)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.2-blue.svg)](https://www.typescriptlang.org)

---

## 📋 About

A friend needed an e-commerce site for their shop, so I built this. It's a straightforward online store system with product management, shopping cart, and WhatsApp-based checkout. Nothing fancy, just the essentials that work.

Built with Laravel and Vue because that's what I'm comfortable with. Added some nice touches like iOS-style animations and Google login to make it feel modern. The admin panel lets you manage products, orders, and customize WhatsApp messages for customers.

It's production-ready and has decent security (OWASP compliant), but always review the code yourself before using it for real business.

---

## 🎯 Features

### ✨ Customer Features

- 🛍️ Product catalog with category filtering and search
- 🛒 Shopping cart with session persistence
- 💳 Checkout with WhatsApp integration
- 🔐 Google OAuth authentication
- 📧 Email/password authentication
- 🔒 Two-Factor Authentication (2FA/TOTP)
- 📦 Order tracking and history
- 👤 User account management

### 👨‍💼 Admin Features

- 📊 Dashboard with real-time statistics
- 📦 Product management (CRUD)
- 🏷️ Category management (CRUD)
- 📋 Order management with status workflow
- 🎨 Store branding (logo, tagline, favicon)
- 💬 WhatsApp message templates customization
- 🎯 Timeline icons customization
- 🌍 Multi-region phone formatting (8 countries)
- 🔔 Browser notifications for new orders

### 🔒 Security Features (OWASP Compliant)

- ⏱️ Rate limiting (cart, checkout, uploads, password)
- 🛡️ CSRF protection
- 🚫 XSS prevention
- 💉 SQL injection prevention
- 📝 Template injection prevention
- 🔐 Password hashing (bcrypt)
- ✉️ Email verification
- 🔑 2FA with recovery codes

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
| **Inertia Vue** | 2.2.7 | Vue adapter for Inertia.js |
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

Before you begin, ensure you have:

- **PHP** 8.2 or higher
- **Composer** (latest version)
- **Node.js** 18+ and **Yarn**
- **MySQL** 8.0+ or **PostgreSQL** 13+
- **Git**

---

## 🔧 Installation

### 1. Clone the repository

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

### 5. Configure your `.env` file

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

### 8. Run the development server

```bash
# Using composer script (recommended - runs all services)
composer dev

# Or manually
php artisan serve
yarn dev
```

Visit `http://localhost:8000` in your browser.

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

For detailed Google OAuth configuration, see [GOOGLE_OAUTH_SETUP.md](docs/GOOGLE_OAUTH_SETUP.md)

---

## 🎮 Usage

### Default Credentials

After seeding, you can login with:

**Admin Account:**
- Email: `admin@example.com`
- Password: `password`

**User Account:**
- Email: `user@example.com`
- Password: `password`

### Admin Panel

Access the admin panel at `/admin` after logging in as admin.

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

# With coverage
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

For detailed deployment instructions, see [Deployment Guide](docs/04_TECHNICAL_DOCUMENTATION/06_Deployment_Guide.md)

---

## 📖 Documentation

Comprehensive documentation is available in the `/docs` directory:

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

# Run with coverage
php artisan test --coverage
```

**Test Coverage:** 98%+

---

### Reporting Vulnerabilities

If you discover a security vulnerability, please email:
**zulfikar.h@diasection.org**

---


## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Author

**Zulfikar Hidayatullah**

- 📧 Email: zulfikar.h@diasection.org
- 📱 Phone: +62 857-1583-8733
- 🐙 GitHub: [@ZulfikarHD](https://github.com/ZulfikarHD)

---


### ⚠️ Important Security Reminder

**Please read this carefully:**

- 🔐 **Never share your private information** like API keys, database credentials, or any sensitive data
- 🔍 **Don't blindly trust any open-source project** - including this one
- ✅ **Always review the security yourself** before using in production
- 🐛 **I'm continuously learning** - your feedback on security issues and bugs is highly appreciated

This project is shared with good intentions, but security is a shared responsibility. Always do your due diligence.

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [Vue.js](https://vuejs.org) - The Progressive JavaScript Framework
- [Inertia.js](https://inertiajs.com) - Modern monolith approach
- [TailwindCSS](https://tailwindcss.com) - Utility-first CSS framework
- [Reka UI](https://reka-ui.com) - Vue component library
- All contributors who help improve this project

---

## 📞 Support

- 📧 Email: zulfikar.h@diasection.org
- 💬 Issues: [GitHub Issues](https://github.com/ZulfikarHD/simple-store/issues)
- 📖 Documentation: [docs/](docs/)

---

Version 1.9.0 | Production Ready
