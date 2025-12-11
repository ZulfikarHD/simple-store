# Arsitektur Sistem Simple Store

**Penulis**: Zulfikar Hidayatullah  
**Versi**: 1.0  
**Terakhir Diperbarui**: Desember 2025

---

## Overview

Simple Store merupakan aplikasi e-commerce berbasis web yang dibangun menggunakan arsitektur modern berbasis Laravel dan Vue.js dengan Inertia.js sebagai penghubung, yaitu: menghilangkan kebutuhan API terpisah untuk komunikasi frontend-backend dengan tetap mempertahankan pengalaman SPA (Single Page Application).

## Tech Stack

### Backend
| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| Framework | Laravel | v12.41.1 |
| PHP Runtime | PHP | v8.4.1 |
| Authentication | Laravel Fortify | v1.32.1 |
| Database | SQLite | - |
| ORM | Eloquent | Built-in |
| Queue | Database Driver | Built-in |

### Frontend
| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| JavaScript Framework | Vue.js | v3.5.22 |
| SPA Bridge | Inertia.js | v2.2.7 |
| CSS Framework | Tailwind CSS | v4.1.14 |
| Build Tool | Vite | - |
| Type-safe Routes | Laravel Wayfinder | v0.1.12 |
| Animation | Motion-V | - |

### Development Tools
| Tool | Versi | Fungsi |
|------|-------|--------|
| PHPUnit | v11.5.45 | Testing |
| Laravel Pint | v1.26.0 | Code Formatter |
| ESLint | v9.37.0 | JavaScript Linter |
| Prettier | v3.6.2 | Code Formatter |

---

## Diagram Arsitektur

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              CLIENT LAYER                                     │
│  ┌─────────────────────────────────────────────────────────────────────────┐ │
│  │                        Vue.js 3 + Inertia.js v2                         │ │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐    │ │
│  │  │   Pages/    │  │ Components/ │  │ Composables │  │   Layouts/  │    │ │
│  │  │  - Home     │  │  - store/   │  │  - useCart  │  │  - Store    │    │ │
│  │  │  - Cart     │  │  - admin/   │  │  - useToast │  │  - Admin    │    │ │
│  │  │  - Checkout │  │  - ui/      │  │             │  │  - Auth     │    │ │
│  │  │  - Admin/*  │  │  - mobile/  │  │             │  │             │    │ │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘    │ │
│  └─────────────────────────────────────────────────────────────────────────┘ │
│                                    ↕                                          │
│  ┌─────────────────────────────────────────────────────────────────────────┐ │
│  │                    Tailwind CSS v4 + Motion-V                           │ │
│  │         (iOS-like Design System dengan Spring Physics Animation)         │ │
│  └─────────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘
                                      ↕
                            HTTP/Inertia Protocol
                                      ↕
┌─────────────────────────────────────────────────────────────────────────────┐
│                           APPLICATION LAYER                                   │
│  ┌─────────────────────────────────────────────────────────────────────────┐ │
│  │                      Laravel v12 Framework                              │ │
│  │                                                                          │ │
│  │  ┌──────────────────────────────────────────────────────────────────┐   │ │
│  │  │                        MIDDLEWARE STACK                          │   │ │
│  │  │  HandleAppearance → HandleInertiaRequests → AddLinkHeaders      │   │ │
│  │  │  EnsureUserIsAdmin (alias: 'admin')                              │   │ │
│  │  └──────────────────────────────────────────────────────────────────┘   │ │
│  │                                  ↓                                       │ │
│  │  ┌─────────────────────────────────────────────────────────────────┐    │ │
│  │  │                        CONTROLLERS                               │    │ │
│  │  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐           │    │ │
│  │  │  │ Product      │  │ Cart         │  │ Checkout     │           │    │ │
│  │  │  │ Controller   │  │ Controller   │  │ Controller   │           │    │ │
│  │  │  └──────────────┘  └──────────────┘  └──────────────┘           │    │ │
│  │  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐           │    │ │
│  │  │  │ Account      │  │ Admin\*      │  │ Settings\*   │           │    │ │
│  │  │  │ Controller   │  │ Controllers  │  │ Controllers  │           │    │ │
│  │  │  └──────────────┘  └──────────────┘  └──────────────┘           │    │ │
│  │  └─────────────────────────────────────────────────────────────────┘    │ │
│  │                                  ↓                                       │ │
│  │  ┌─────────────────────────────────────────────────────────────────┐    │ │
│  │  │                      SERVICE LAYER                               │    │ │
│  │  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐           │    │ │
│  │  │  │ CartService  │  │ OrderService │  │ ProductSvc   │           │    │ │
│  │  │  └──────────────┘  └──────────────┘  └──────────────┘           │    │ │
│  │  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐           │    │ │
│  │  │  │ CategorySvc  │  │ DashboardSvc │  │ StoreSetting │           │    │ │
│  │  │  │              │  │              │  │ Service      │           │    │ │
│  │  │  └──────────────┘  └──────────────┘  └──────────────┘           │    │ │
│  │  │  ┌──────────────┐                                               │    │ │
│  │  │  │ ImageService │                                               │    │ │
│  │  │  └──────────────┘                                               │    │ │
│  │  └─────────────────────────────────────────────────────────────────┘    │ │
│  │                                  ↓                                       │ │
│  │  ┌─────────────────────────────────────────────────────────────────┐    │ │
│  │  │                       MODEL LAYER                                │    │ │
│  │  │  ┌────────┐ ┌────────┐ ┌─────────┐ ┌─────────┐ ┌──────────────┐ │    │ │
│  │  │  │ User   │ │Category│ │ Product │ │  Order  │ │ StoreSetting │ │    │ │
│  │  │  └────────┘ └────────┘ └─────────┘ └─────────┘ └──────────────┘ │    │ │
│  │  │  ┌────────┐ ┌─────────┐ ┌─────────────┐                         │    │ │
│  │  │  │  Cart  │ │CartItem │ │ OrderItem   │                         │    │ │
│  │  │  └────────┘ └─────────┘ └─────────────┘                         │    │ │
│  │  └─────────────────────────────────────────────────────────────────┘    │ │
│  └─────────────────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────────────────┘
                                      ↕
┌─────────────────────────────────────────────────────────────────────────────┐
│                              DATA LAYER                                       │
│  ┌────────────────────┐  ┌────────────────────┐  ┌────────────────────┐     │
│  │      SQLite        │  │    File Storage    │  │    Cache (File)    │     │
│  │   (Primary DB)     │  │  storage/app/public│  │   storage/cache    │     │
│  │                    │  │   - product images │  │   - query cache    │     │
│  │   - users          │  │   - category imgs  │  │   - route cache    │     │
│  │   - categories     │  │   - store logo     │  │                    │     │
│  │   - products       │  │                    │  │                    │     │
│  │   - orders         │  │                    │  │                    │     │
│  │   - carts          │  │                    │  │                    │     │
│  │   - store_settings │  │                    │  │                    │     │
│  └────────────────────┘  └────────────────────┘  └────────────────────┘     │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Layer Aplikasi

### 1. Presentation Layer (Frontend)

Frontend dibangun menggunakan Vue.js 3 dengan Composition API, yang terintegrasi dengan Laravel melalui Inertia.js v2. Dengan demikian, aplikasi dapat berjalan sebagai SPA tanpa memerlukan API terpisah.

**Komponen Utama**:
- **Pages**: Halaman-halaman utama aplikasi (Home, Cart, Checkout, Admin/*)
- **Components**: Reusable UI components termasuk store/, admin/, ui/, mobile/
- **Layouts**: Template layout untuk Store, Admin, dan Auth
- **Composables**: Reusable logic hooks (useCart, useToast, dll)

**Styling & Animation**:
- Tailwind CSS v4 dengan konfigurasi tema custom
- Motion-V untuk animasi dengan iOS-like spring physics
- Dark mode support dengan `dark:` prefix

### 2. Application Layer (Backend)

Laravel v12 menangani semua business logic dengan struktur yang terorganisir:

**Controllers**: Menangani HTTP requests dan mengembalikan Inertia responses
- `ProductController`: Katalog produk dengan caching
- `CartController`: Manajemen keranjang belanja
- `CheckoutController`: Proses checkout dan integrasi WhatsApp
- `AccountController`: Manajemen akun customer
- `Admin\*`: Controllers untuk admin panel

**Services**: Business logic yang kompleks dipisahkan ke service classes
- `CartService`: Operasi keranjang (add, update, remove, clear)
- `OrderService`: Checkout flow dan order management
- `ProductService`: CRUD produk dengan cache invalidation
- `CategoryService`: Manajemen kategori
- `DashboardService`: Statistik dan analytics
- `StoreSettingService`: Konfigurasi toko
- `ImageService`: Upload dan resize gambar

**Form Requests**: Validasi input terpisah dari controller logic

### 3. Data Layer

Data persistence menggunakan SQLite sebagai database utama dengan Eloquent ORM:

**Models**:
- `User`: Pengguna dengan role-based access (admin/customer)
- `Category`: Kategori produk dengan hierarchical ordering
- `Product`: Produk dengan pricing, stock, dan status
- `Cart` & `CartItem`: Session-based shopping cart
- `Order` & `OrderItem`: Pesanan dengan status tracking
- `StoreSetting`: Key-value configuration store

**Caching Strategy**:
- File-based cache untuk query results
- Cache TTL 5 menit untuk produk dan kategori
- Automatic cache invalidation saat data berubah

---

## Design Patterns

### Backend Patterns

1. **Service Pattern**: Business logic dipisahkan dari controller ke service classes
   ```php
   // Controller hanya handle HTTP, delegate ke service
   public function store(CheckoutRequest $request)
   {
       $order = $this->orderService->createOrder($request->validated());
       return redirect()->route('checkout.success', $order);
   }
   ```

2. **Repository Pattern (Implicit)**: Eloquent ORM sebagai data access layer
   ```php
   // Model dengan scopes dan relationships
   Product::query()->active()->inStock()->with('category')->get();
   ```

3. **Observer Pattern**: Model events untuk auto-generation
   ```php
   // Auto-generate slug saat creating
   static::creating(function (Product $product) {
       if (empty($product->slug)) {
           $product->slug = Str::slug($product->name);
       }
   });
   ```

4. **Factory Pattern**: Model factories untuk testing dan seeding

### Frontend Patterns

1. **Component-based Architecture**: Vue Single File Components (SFC)
2. **Composition API**: Vue 3 composables untuk reusable logic
3. **Props/Events Pattern**: Parent-child communication
4. **Provide/Inject**: Dependency injection untuk deep components

---

## Request Flow

### Customer Order Flow

```
┌──────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Browse  │────→│  Add to     │────→│  Checkout   │────→│  WhatsApp   │
│ Products │     │   Cart      │     │   Form      │     │ Confirmation│
└──────────┘     └─────────────┘     └─────────────┘     └─────────────┘
     │                 │                   │                    │
     ▼                 ▼                   ▼                    ▼
┌──────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│ Product  │     │ Cart        │     │ Order       │     │ External    │
│Controller│     │ Service     │     │ Service     │     │ WhatsApp    │
└──────────┘     └─────────────┘     └─────────────┘     └─────────────┘
```

### Admin Order Management Flow

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Dashboard  │────→│  View Order  │────→│ Update Status│
│  (Statistics)│     │   Details    │     │   + Notify   │
└──────────────┘     └──────────────┘     └──────────────┘
       │                   │                     │
       ▼                   ▼                     ▼
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  Dashboard   │     │  Order       │     │  WhatsApp    │
│  Service     │     │  Service     │     │  to Customer │
└──────────────┘     └──────────────┘     └──────────────┘
```

---

## Security Architecture

### Authentication
- **Laravel Fortify**: Built-in authentication scaffolding
- **Two-Factor Authentication**: TOTP-based 2FA dengan QR code
- **Password Confirmation**: Re-authentication untuk sensitive actions
- **Session Management**: Database-stored sessions

### Authorization
- **Role-based Access Control**: Admin dan Customer roles
- **Middleware Protection**: `EnsureUserIsAdmin` untuk admin routes
- **Route Groups**: Grouped protection untuk admin panel

### Security Measures
- **CSRF Protection**: Laravel built-in dengan per-request tokens
- **XSS Prevention**: Vue.js automatic escaping + server-side validation
- **SQL Injection Prevention**: Eloquent ORM dengan parameterized queries
- **Rate Limiting**: Laravel throttle middleware (configurable)
- **Password Hashing**: Bcrypt dengan automatic rehashing

---

## Performance Optimization

### Query Optimization
- **Eager Loading**: Prevent N+1 queries dengan `with()`
- **Database Indexing**: Index pada columns yang sering di-query
- **Query Caching**: Cache expensive queries dengan TTL

### Asset Optimization
- **Vite Bundling**: Code splitting dan tree shaking
- **CSS Purging**: Tailwind unused CSS removal
- **Lazy Loading**: Component lazy loading dengan Inertia

### Image Optimization
- **Auto Resize**: ImageService untuk resize uploads
- **WebP Support**: Modern image format support
- **Storage Symlink**: Public storage untuk efficient serving

---

## Scalability Considerations

### Horizontal Scaling
- **Stateless Application**: Session di database, memungkinkan load balancing
- **Queue System**: Database queue driver, dapat upgrade ke Redis
- **File Storage**: Local storage, dapat upgrade ke S3/cloud storage

### Vertical Scaling
- **Database**: SQLite untuk small-medium, dapat migrate ke MySQL/PostgreSQL
- **Cache**: File cache, dapat upgrade ke Redis untuk high-traffic
- **Queue Workers**: Multiple workers untuk parallel processing

### Upgrade Path
```
Current State              →  Production Scale
─────────────────────────────────────────────────
SQLite                     →  MySQL/PostgreSQL
File Cache                 →  Redis
Local Storage              →  AWS S3/Cloud Storage
Database Queue             →  Redis Queue
Single Server              →  Load Balanced Cluster
```

---

## Third-Party Integrations

| Service | Fungsi | Implementasi |
|---------|--------|--------------|
| WhatsApp | Order notification | URL scheme `wa.me` dengan pre-filled message |
| Laravel Wayfinder | Type-safe routes | Auto-generated TypeScript dari Laravel routes |
| Motion-V | Animation | Vue.js animation library |

---

## Environment Configuration

Aplikasi menggunakan environment variables untuk konfigurasi:

```env
# Application
APP_NAME="Simple Store"
APP_ENV=production
APP_DEBUG=false

# Database
DB_CONNECTION=mysql
DB_DATABASE=/path/to/database.mysql

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=file

# Queue
QUEUE_CONNECTION=database
```

---

## Monitoring & Logging

### Application Logs
- **Location**: `storage/logs/laravel.log`
- **Format**: PSR-3 compliant
- **Rotation**: Daily log rotation

### Error Handling
- **Exception Handler**: Centralized di `bootstrap/app.php`
- **Production Errors**: User-friendly error pages
- **Debug Mode**: Detailed errors di development

---

## Related Documentation

- [02_Database_Schema.md](02_Database_Schema.md) - Detail skema database
- [03_API_Documentation.md](03_API_Documentation.md) - Endpoint dan API
- [04_File_Structure.md](04_File_Structure.md) - Struktur direktori
- [05_Security_Documentation.md](05_Security_Documentation.md) - Security details
- [06_Deployment_Guide.md](06_Deployment_Guide.md) - Panduan deployment
- [07_Testing_Documentation.md](07_Testing_Documentation.md) - Testing strategy
