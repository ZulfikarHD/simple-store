# Development Environment Setup Guide

Panduan lengkap untuk mengatur environment development F&B Web App.

---

## Prerequisites

Pastikan sistem Anda memiliki komponen berikut:

| Software | Version | Required |
|----------|---------|----------|
| PHP | 8.4+ | ✅ Yes |
| Composer | 2.x | ✅ Yes |
| Node.js | 18+ | ✅ Yes |
| Yarn | 1.22+ | ✅ Yes |
| Git | 2.x+ | ✅ Yes |
| SQLite | 3.x | ✅ Yes |
| MySQL/PostgreSQL | 8.x / 15+ | ⚪ Optional |

---

## Installation Steps

### 1. Clone Repository

```bash
git clone https://github.com/yourcompany/fnb-web-app.git
cd fnb-web-app
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
# Menggunakan Yarn (recommended)
yarn install

# JANGAN gunakan npm install
```

### 4. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit file `.env`:

```env
APP_NAME="F&B Web App"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database (SQLite - Default)
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

# Atau MySQL
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=fnb_app
# DB_USERNAME=root
# DB_PASSWORD=

# WhatsApp Configuration
WHATSAPP_NUMBER=6281234567890
WHATSAPP_MESSAGE_TEMPLATE="*Pesanan Baru*\n{order_details}"
```

### 5. Database Setup

```bash
# Untuk SQLite, buat file database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed database dengan data testing
php artisan db:seed
```

### 6. Storage Link

```bash
php artisan storage:link
```

### 7. Build Frontend Assets

```bash
# Development build (dengan hot reload)
yarn dev

# Production build
yarn build
```

### 8. Start Development Server

```bash
# Opsi 1: Menggunakan artisan serve
php artisan serve

# Opsi 2: Menggunakan Composer script
composer run dev

# Opsi 3: Full stack dengan Vite
# Terminal 1:
php artisan serve

# Terminal 2:
yarn dev
```

Akses aplikasi di: `http://localhost:8000`

---

## Default Login Credentials

### Admin Account
- **Email:** admin@fnbstore.com
- **Password:** password

### Test Accounts
| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@fnbstore.com | password |
| Staff | staff@fnbstore.com | password |

---

## Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ProductTest.php

# Run with filter
php artisan test --filter=testCanCreateProduct

# Run with coverage report
php artisan test --coverage
```

---

## Code Quality Tools

### Pint (Code Formatter)

```bash
# Check code style
vendor/bin/pint --test

# Fix code style
vendor/bin/pint --dirty
```

### ESLint (JavaScript/Vue)

```bash
# Check linting issues
yarn lint

# Fix linting issues
yarn lint:fix
```

### Prettier (Code Formatting)

```bash
# Format code
yarn format
```

---

## Wayfinder (Route Generation)

```bash
# Generate TypeScript route definitions
php artisan wayfinder:generate

# Atau otomatis via Vite plugin (dalam vite.config.ts)
```

---

## Troubleshooting

### Issue: Permission Denied on Storage

```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache

# Jika masih error
sudo chown -R $USER:www-data storage bootstrap/cache
```

### Issue: Database Connection Failed

1. Pastikan file SQLite ada:
   ```bash
   touch database/database.sqlite
   ```

2. Untuk MySQL, pastikan service berjalan:
   ```bash
   sudo service mysql start
   ```

3. Verifikasi credentials di `.env`

### Issue: Assets Not Loading / Vite Manifest Error

```bash
# Build ulang assets
yarn build

# Clear cache
php artisan optimize:clear
php artisan config:clear
php artisan view:clear
```

### Issue: Class Not Found

```bash
# Regenerate autoload
composer dump-autoload

# Clear cache
php artisan optimize:clear
```

### Issue: Node Modules Issues

```bash
# Hapus node_modules dan reinstall
rm -rf node_modules
yarn install
```

---

## Useful Commands

### Artisan Commands

```bash
# List all routes
php artisan route:list

# Create new controller
php artisan make:controller ProductController

# Create new model with migration & factory
php artisan make:model Product -mf

# Create form request
php artisan make:request StoreProductRequest

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

### Development Shortcuts

```bash
# Start development (PHP + Vite)
composer run dev

# Build for production
yarn build

# Generate types
php artisan wayfinder:generate
```

---

## IDE Setup Recommendations

### VS Code Extensions
- Volar (Vue Language Features)
- Laravel Blade Snippets
- PHP Intelephense
- Tailwind CSS IntelliSense
- ESLint
- Prettier

### PHPStorm Plugins
- Laravel Plugin
- Vue.js
- Tailwind CSS

---

## Environment Variables Reference

| Variable | Description | Default |
|----------|-------------|---------|
| APP_NAME | Application name | F&B Web App |
| APP_ENV | Environment (local/staging/production) | local |
| APP_DEBUG | Enable debug mode | true |
| APP_URL | Application URL | http://localhost |
| DB_CONNECTION | Database driver | sqlite |
| WHATSAPP_NUMBER | Store WhatsApp number | - |

---

*Document version: 1.0*  
*Last updated: November 2024*

