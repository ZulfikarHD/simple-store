# Technology Stack

## Overview

Simple Store dibangun menggunakan modern tech stack yang mengutamakan developer experience, performance, dan maintainability. Arsitektur aplikasi mengadopsi pola monolithic dengan Single Page Application (SPA) behavior melalui Inertia.js.

## Backend Stack

### Core Framework

| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 8.4.1 | Server-side programming language |
| **Laravel** | 12.41.1 | PHP web application framework |
| **MySQL** | 8.0+ | Relational database management system |

### Laravel Ecosystem Packages

| Package | Version | Purpose |
|---------|---------|---------|
| **Inertia.js Laravel** | 2.0.11 | Bridge antara Laravel dan Vue.js SPA |
| **Laravel Fortify** | 1.32.1 | Authentication scaffolding tanpa UI |
| **Laravel Wayfinder** | 0.1.12 | Type-safe routing untuk TypeScript |
| **Laravel Prompts** | 0.3.8 | Beautiful CLI prompts |
| **Laravel Tinker** | 2.x | REPL untuk Laravel |

### Development Tools (Backend)

| Tool | Version | Purpose |
|------|---------|---------|
| **Laravel Pint** | 1.26.0 | PHP code formatter (PSR-12 compliant) |
| **Laravel Sail** | 1.49.0 | Docker development environment |
| **Laravel Pail** | 1.2.2 | Real-time log viewer |
| **PHPUnit** | 11.5.45 | PHP testing framework |
| **Faker** | 1.23+ | Fake data generator untuk testing |
| **Mockery** | 1.6+ | Mock object framework |

## Frontend Stack

### Core Technologies

| Technology | Version | Purpose |
|------------|---------|---------|
| **Vue.js** | 3.5.22 | Progressive JavaScript framework |
| **Inertia.js Vue** | 2.2.7 | Vue adapter untuk Inertia.js |
| **TypeScript** | 5.2.2 | Typed JavaScript superset |
| **Tailwind CSS** | 4.1.14 | Utility-first CSS framework |

### UI Libraries

| Library | Version | Purpose |
|---------|---------|---------|
| **Reka UI** | 2.4.1 | Headless UI components (Vue 3) |
| **Lucide Vue Next** | 0.468.0 | Icon library |
| **Motion V** | 1.7.4 | Animation library untuk Vue |
| **VueUse Motion** | 3.0.3 | Motion directives dan composables |
| **tw-animate-css** | 1.2.5 | Tailwind CSS animations |

### Utility Libraries

| Library | Version | Purpose |
|---------|---------|---------|
| **VueUse Core** | 12.8.2 | Collection of Vue composition utilities |
| **Class Variance Authority** | 0.7.1 | Utility untuk managing component variants |
| **clsx** | 2.1.1 | Utility untuk constructing className strings |
| **Tailwind Merge** | 3.2.0 | Merge Tailwind CSS classes tanpa conflicts |

### Build Tools

| Tool | Version | Purpose |
|------|---------|---------|
| **Vite** | 7.0.4 | Next-generation frontend build tool |
| **Laravel Vite Plugin** | 2.0.0 | Laravel integration untuk Vite |
| **Wayfinder Vite Plugin** | 0.1.7 | Type generation untuk routes |
| **Vue TSC** | 2.2.4 | TypeScript compiler untuk Vue |

### Code Quality Tools

| Tool | Version | Purpose |
|------|---------|---------|
| **ESLint** | 9.37.0 | JavaScript/TypeScript linter |
| **Prettier** | 3.6.2 | Code formatter |
| **Prettier Plugin Tailwind** | 0.6.11 | Tailwind class sorting |
| **Prettier Plugin Organize Imports** | 4.1.0 | Auto-organize imports |

## Architecture Pattern

### Application Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Browser                               │
│  ┌───────────────────────────────────────────────────────┐  │
│  │                   Vue 3 SPA                            │  │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │  │
│  │  │   Pages     │  │ Components  │  │  Composables│    │  │
│  │  └─────────────┘  └─────────────┘  └─────────────┘    │  │
│  └───────────────────────────────────────────────────────┘  │
│                            │                                 │
│                    Inertia.js Protocol                       │
│                            │                                 │
└────────────────────────────┼────────────────────────────────┘
                             │
┌────────────────────────────┼────────────────────────────────┐
│                        Laravel                               │
│                            │                                 │
│  ┌─────────────────────────┼─────────────────────────────┐  │
│  │                    Controllers                         │  │
│  └─────────────────────────┼─────────────────────────────┘  │
│                            │                                 │
│  ┌───────────────┐  ┌─────┴─────┐  ┌───────────────────┐   │
│  │   Services    │  │   Models  │  │   Form Requests   │   │
│  └───────────────┘  └─────┬─────┘  └───────────────────┘   │
│                            │                                 │
│  ┌─────────────────────────┼─────────────────────────────┐  │
│  │                   Eloquent ORM                         │  │
│  └─────────────────────────┼─────────────────────────────┘  │
│                            │                                 │
│  ┌─────────────────────────┼─────────────────────────────┐  │
│  │                  SQLite Database                       │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

### Design Patterns Used

| Pattern | Implementation |
|---------|----------------|
| **MVC** | Laravel Controllers, Models, Vue Views |
| **Repository Pattern** | Indirect via Eloquent ORM |
| **Service Layer** | Business logic separation |
| **Form Request** | Request validation classes |
| **Factory Pattern** | Database seeders dan testing |
| **Observer Pattern** | Eloquent model events |

## Database Design

### Engine
- **SQLite** - Dipilih untuk kesederhanaan deployment dan zero-configuration
- Dapat di-upgrade ke **MySQL/PostgreSQL** untuk production scale

### Schema Overview

| Table | Purpose | Relations |
|-------|---------|-----------|
| `users` | User accounts | Has many orders, carts |
| `categories` | Product categories | Has many products |
| `products` | Product catalog | Belongs to category |
| `carts` | Shopping cart sessions | Belongs to user, has many items |
| `cart_items` | Cart contents | Belongs to cart, product |
| `orders` | Customer orders | Belongs to user, has many items |
| `order_items` | Order details | Belongs to order, product |
| `store_settings` | Store configuration | Key-value storage |
| `sessions` | Laravel sessions | - |
| `cache` | Laravel cache | - |
| `jobs` | Laravel queue | - |

## Infrastructure

### Runtime Configuration

| Setting | Value |
|---------|-------|
| **Timezone** | Asia/Jakarta (WIB) |
| **Locale** | id (Indonesian) |
| **Currency** | Rupiah (Rp) |
| **Language** | Indonesian |

### Package Managers

| Manager | Purpose |
|---------|---------|
| **Composer** | PHP dependencies |
| **Yarn** | Node.js dependencies (preferred) |
| **npm** | Alternative package manager |

### Development Scripts

```bash
# Development server dengan hot reload
composer run dev

# Build untuk production
yarn build

# Run tests
php artisan test

# Format PHP code
vendor/bin/pint

# Lint JavaScript/TypeScript
yarn lint

# Format frontend code
yarn format
```

## Security Features

| Feature | Implementation |
|---------|----------------|
| **Authentication** | Laravel Fortify |
| **Two-Factor Auth** | TOTP via Fortify |
| **CSRF Protection** | Laravel built-in |
| **XSS Prevention** | Vue auto-escaping |
| **SQL Injection Prevention** | Eloquent ORM parameterized queries |
| **Password Hashing** | Bcrypt via Laravel |

## Performance Optimizations

| Optimization | Implementation |
|--------------|----------------|
| **SPA Navigation** | Inertia.js client-side routing |
| **Asset Bundling** | Vite dengan tree-shaking |
| **Lazy Loading** | Vue async components |
| **Prefetching** | Inertia.js link prefetching |
| **Image Optimization** | Storage system dengan public disk |
| **Database Caching** | Laravel cache driver |

## Compatibility

### Browser Support
- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)

### PHP Requirements
- PHP ≥ 8.2
- Extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

### Node.js Requirements
- Node.js ≥ 18.x
- Yarn ≥ 1.22.x atau npm ≥ 9.x
