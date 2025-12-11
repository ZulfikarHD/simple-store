# Struktur File Simple Store

**Penulis**: Zulfikar Hidayatullah  
**Versi**: 1.0  
**Terakhir Diperbarui**: Desember 2025

---

## Overview

Dokumentasi struktur direktori dan file aplikasi Simple Store, yaitu: penjelasan fungsi setiap folder, naming conventions, dan panduan organisasi kode untuk memudahkan navigasi dan maintenance.

---

## Root Directory

```
simple-store/
├── app/                        # Core aplikasi PHP
├── bootstrap/                  # Bootstrap files Laravel
├── config/                     # Configuration files
├── database/                   # Database migrations, seeders, factories
├── handover-doc/               # Dokumentasi handover
├── public/                     # Public assets & entry point
├── resources/                  # Views, JS, CSS
├── routes/                     # Route definitions
├── storage/                    # Logs, cache, uploads
├── tests/                      # Test files
├── vendor/                     # Composer dependencies (gitignored)
├── node_modules/               # Node dependencies (gitignored)
├── .env                        # Environment configuration (gitignored)
├── .env.example                # Environment template
├── .gitignore                  # Git ignore rules
├── artisan                     # Laravel CLI
├── composer.json               # PHP dependencies
├── composer.lock               # PHP dependency lock
├── package.json                # Node dependencies
├── yarn.lock                   # Yarn dependency lock
├── vite.config.ts              # Vite configuration
├── tsconfig.json               # TypeScript configuration
├── tailwind.config.js          # Tailwind CSS configuration
├── eslint.config.js            # ESLint configuration
├── phpunit.xml                 # PHPUnit configuration
└── pint.json                   # Laravel Pint configuration
```

---

## App Directory (`app/`)

Core aplikasi Laravel dengan struktur MVC dan service layer.

```
app/
├── Actions/                    # Single-purpose action classes
│   └── Fortify/               # Fortify authentication actions
│       ├── CreateNewUser.php
│       ├── PasswordValidationRules.php
│       ├── ResetUserPassword.php
│       └── UpdateUserPassword.php
│
├── Console/                    # Artisan commands
│   └── Commands/              # Custom commands (auto-registered)
│
├── Http/
│   ├── Controllers/           # Request handlers
│   │   ├── Controller.php     # Base controller
│   │   ├── ProductController.php    # Product catalog
│   │   ├── CartController.php       # Shopping cart
│   │   ├── CheckoutController.php   # Checkout process
│   │   ├── AccountController.php    # User account
│   │   ├── Admin/                   # Admin panel controllers
│   │   │   ├── DashboardController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ProductController.php
│   │   │   ├── OrderController.php
│   │   │   ├── StoreSettingController.php
│   │   │   └── PasswordVerificationController.php
│   │   ├── Api/                     # API controllers
│   │   │   └── OrderApiController.php
│   │   └── Settings/                # User settings controllers
│   │       ├── PasswordController.php
│   │       ├── ProfileController.php
│   │       └── TwoFactorAuthController.php
│   │
│   ├── Middleware/            # HTTP middleware
│   │   ├── EnsureUserIsAdmin.php    # Admin role check
│   │   ├── HandleAppearance.php     # Theme handling
│   │   └── HandleInertiaRequests.php # Inertia shared data
│   │
│   ├── Requests/              # Form request validation
│   │   ├── AddToCartRequest.php
│   │   ├── CheckoutRequest.php
│   │   ├── UpdateCartItemRequest.php
│   │   ├── Admin/                   # Admin form requests
│   │   │   ├── StoreCategoryRequest.php
│   │   │   ├── UpdateCategoryRequest.php
│   │   │   ├── StoreProductRequest.php
│   │   │   ├── UpdateProductRequest.php
│   │   │   └── UpdateStoreSettingRequest.php
│   │   └── Settings/                # Settings form requests
│   │       └── ProfileUpdateRequest.php
│   │
│   └── Resources/             # API Resources (Eloquent transformers)
│       ├── CategoryResource.php
│       └── ProductResource.php
│
├── Models/                    # Eloquent models
│   ├── User.php              # User model dengan 2FA support
│   ├── Category.php          # Product category
│   ├── Product.php           # Product dengan stock management
│   ├── Cart.php              # Shopping cart
│   ├── CartItem.php          # Cart item
│   ├── Order.php             # Order dengan status tracking
│   ├── OrderItem.php         # Order item dengan snapshot harga
│   └── StoreSetting.php      # Key-value store settings
│
├── Providers/                 # Service providers
│   ├── AppServiceProvider.php
│   └── FortifyServiceProvider.php
│
└── Services/                  # Business logic services
    ├── CartService.php        # Cart operations
    ├── OrderService.php       # Order management
    ├── ProductService.php     # Product CRUD
    ├── CategoryService.php    # Category management
    ├── DashboardService.php   # Dashboard statistics
    ├── StoreSettingService.php # Store configuration
    └── ImageService.php       # Image upload & resize
```

### Penjelasan Folder App

| Folder | Fungsi |
|--------|--------|
| `Actions/` | Single-responsibility action classes untuk operasi spesifik |
| `Console/Commands/` | Custom Artisan commands |
| `Http/Controllers/` | HTTP request handlers yang return Inertia responses |
| `Http/Middleware/` | Request/response filtering |
| `Http/Requests/` | Form validation terpisah dari controller |
| `Http/Resources/` | JSON transformation untuk API responses |
| `Models/` | Eloquent models dengan relationships dan scopes |
| `Providers/` | Service container bindings dan boot logic |
| `Services/` | Business logic terpisah dari controller |

---

## Resources Directory (`resources/`)

Frontend assets dan views.

```
resources/
├── css/
│   └── app.css                # Main stylesheet (Tailwind imports)
│
├── js/
│   ├── app.ts                 # Main JS entry point
│   ├── ssr.ts                 # Server-side rendering entry
│   │
│   ├── pages/                 # Inertia page components
│   │   ├── Home.vue           # Product catalog page
│   │   ├── Cart.vue           # Shopping cart page
│   │   ├── Checkout.vue       # Checkout form page
│   │   ├── OrderSuccess.vue   # Order confirmation page
│   │   ├── ProductDetail.vue  # Product detail page
│   │   ├── Dashboard.vue      # User dashboard
│   │   ├── Welcome.vue        # Welcome/landing page
│   │   │
│   │   ├── Account/           # User account pages
│   │   │   ├── Index.vue
│   │   │   ├── Orders.vue
│   │   │   └── OrderDetail.vue
│   │   │
│   │   ├── Admin/             # Admin panel pages
│   │   │   ├── Dashboard.vue
│   │   │   ├── Categories/
│   │   │   │   ├── Index.vue
│   │   │   │   ├── Create.vue
│   │   │   │   ├── Edit.vue
│   │   │   │   └── Show.vue
│   │   │   ├── Products/
│   │   │   │   ├── Index.vue
│   │   │   │   ├── Create.vue
│   │   │   │   ├── Edit.vue
│   │   │   │   └── Show.vue
│   │   │   ├── Orders/
│   │   │   │   ├── Index.vue
│   │   │   │   └── Show.vue
│   │   │   └── Settings/
│   │   │       └── Index.vue
│   │   │
│   │   ├── auth/              # Authentication pages
│   │   │   ├── Login.vue
│   │   │   ├── Register.vue
│   │   │   ├── ForgotPassword.vue
│   │   │   ├── ResetPassword.vue
│   │   │   ├── VerifyEmail.vue
│   │   │   ├── ConfirmPassword.vue
│   │   │   └── TwoFactorChallenge.vue
│   │   │
│   │   └── settings/          # User settings pages
│   │       ├── Profile.vue
│   │       ├── Password.vue
│   │       ├── TwoFactor.vue
│   │       └── Appearance.vue
│   │
│   ├── components/            # Reusable Vue components
│   │   ├── ui/               # Base UI components (shadcn/ui)
│   │   │   ├── Button.vue
│   │   │   ├── Input.vue
│   │   │   ├── Card.vue
│   │   │   ├── Dialog.vue
│   │   │   ├── Select.vue
│   │   │   ├── Badge.vue
│   │   │   ├── Avatar.vue
│   │   │   ├── Tabs.vue
│   │   │   ├── Sheet.vue
│   │   │   ├── Skeleton.vue
│   │   │   ├── Separator.vue
│   │   │   ├── DropdownMenu.vue
│   │   │   ├── Pagination.vue
│   │   │   └── ...
│   │   │
│   │   ├── store/            # Store-specific components
│   │   │   ├── ProductCard.vue       # Product display card
│   │   │   ├── CartItem.vue          # Cart item row
│   │   │   ├── CartCounter.vue       # Cart badge counter
│   │   │   ├── CategoryFilter.vue    # Category tabs
│   │   │   ├── SearchBar.vue         # Product search
│   │   │   ├── PriceDisplay.vue      # Formatted price
│   │   │   ├── OrderStatusBadge.vue  # Status indicator
│   │   │   ├── EmptyState.vue        # Empty state display
│   │   │   ├── StoreHeader.vue       # Store header
│   │   │   ├── StoreFooter.vue       # Store footer
│   │   │   └── index.ts              # Barrel export
│   │   │
│   │   ├── admin/            # Admin-specific components
│   │   │   ├── AdminBottomNav.vue    # Mobile bottom navigation
│   │   │   ├── NewOrderAlert.vue     # New order notification
│   │   │   ├── OrderCard.vue         # Order card display
│   │   │   ├── PasswordConfirmDialog.vue
│   │   │   └── SortableHeader.vue    # Table sorting header
│   │   │
│   │   ├── mobile/           # Mobile-specific components
│   │   │   └── ...
│   │   │
│   │   ├── AppContent.vue     # Main content wrapper
│   │   ├── AppHeader.vue      # Application header
│   │   ├── AppShell.vue       # Application shell
│   │   ├── AppSidebar.vue     # Sidebar navigation
│   │   ├── AppLogo.vue        # Logo component
│   │   ├── Breadcrumbs.vue    # Breadcrumb navigation
│   │   ├── NavMain.vue        # Main navigation
│   │   ├── NavUser.vue        # User menu
│   │   ├── UserInfo.vue       # User info display
│   │   ├── PageTransition.vue # Page transition animation
│   │   ├── Heading.vue        # Page heading
│   │   ├── InputError.vue     # Form error display
│   │   ├── TextLink.vue       # Styled link
│   │   ├── TwoFactorSetupModal.vue
│   │   ├── TwoFactorRecoveryCodes.vue
│   │   └── ...
│   │
│   ├── layouts/              # Layout components
│   │   ├── AppLayout.vue     # Main app layout (store)
│   │   ├── AuthLayout.vue    # Authentication layout
│   │   ├── app/              # App layout components
│   │   │   ├── AppHeaderLayout.vue
│   │   │   └── AppSidebarLayout.vue
│   │   ├── auth/             # Auth layout components
│   │   └── settings/         # Settings layout
│   │       └── Layout.vue
│   │
│   ├── composables/          # Vue composition functions
│   │   ├── useAppearance.ts        # Theme/appearance management
│   │   ├── useHapticFeedback.ts    # Mobile haptic feedback
│   │   ├── useInitials.ts          # Name initials helper
│   │   ├── useMobileDetect.ts      # Mobile device detection
│   │   ├── useMotionV.ts           # Motion library integration
│   │   ├── useOrderNotifications.ts # Real-time order alerts
│   │   ├── useSpringAnimation.ts   # Spring physics animation
│   │   └── useTwoFactorAuth.ts     # 2FA helper functions
│   │
│   ├── lib/                  # Utility libraries
│   │   └── utils.ts          # General utilities
│   │
│   ├── types/                # TypeScript type definitions
│   │   └── index.d.ts        # Global type declarations
│   │
│   ├── actions/              # Wayfinder generated (gitignored)
│   ├── routes/               # Wayfinder generated (gitignored)
│   └── wayfinder/            # Wayfinder configuration
│
└── views/
    └── app.blade.php          # Root Blade template untuk Inertia
```

### Penjelasan Folder Resources

| Folder | Fungsi |
|--------|--------|
| `css/` | Stylesheets dengan Tailwind CSS |
| `js/pages/` | Inertia page components (route endpoints) |
| `js/components/` | Reusable Vue components |
| `js/layouts/` | Page layout wrappers |
| `js/composables/` | Vue 3 composition API hooks |
| `js/lib/` | Utility functions dan helpers |
| `js/types/` | TypeScript type definitions |
| `views/` | Blade templates (minimal, hanya untuk Inertia root) |

---

## Database Directory (`database/`)

Database-related files untuk migrations, seeders, dan factories.

```
database/
├── database.sqlite            # SQLite database file (gitignored)
│
├── factories/                 # Model factories untuk testing
│   ├── UserFactory.php
│   ├── CategoryFactory.php
│   ├── ProductFactory.php
│   ├── OrderFactory.php
│   └── OrderItemFactory.php
│
├── migrations/                # Database schema migrations
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2025_08_14_170933_add_two_factor_columns_to_users_table.php
│   ├── 2025_11_25_070958_add_role_to_users_table.php
│   ├── 2025_11_25_071000_create_categories_table.php
│   ├── 2025_11_25_071003_create_products_table.php
│   ├── 2025_11_25_071006_create_orders_table.php
│   ├── 2025_11_25_071008_create_order_items_table.php
│   ├── 2025_11_25_083034_create_carts_table.php
│   ├── 2025_11_25_083041_create_cart_items_table.php
│   ├── 2025_11_26_072446_create_store_settings_table.php
│   └── 2025_12_10_155728_make_customer_address_nullable_in_orders_table.php
│
└── seeders/                   # Database seeders
    ├── DatabaseSeeder.php     # Main seeder (development)
    ├── ProductionSeeder.php   # Production deployment seeder
    ├── UserSeeder.php         # Default admin user
    ├── StoreSettingSeeder.php # Default store settings
    └── README.md              # Seeder documentation
```

---

## Config Directory (`config/`)

Laravel configuration files.

```
config/
├── app.php                    # Application settings
├── auth.php                   # Authentication configuration
├── cache.php                  # Cache driver settings
├── database.php               # Database connections
├── filesystems.php            # Storage disk configuration
├── fortify.php                # Laravel Fortify settings
├── inertia.php                # Inertia.js configuration
├── logging.php                # Log channel configuration
├── mail.php                   # Mail driver settings
├── queue.php                  # Queue driver settings
├── services.php               # Third-party services
└── session.php                # Session configuration
```

---

## Routes Directory (`routes/`)

Route definition files.

```
routes/
├── web.php                    # Web routes (main application)
├── console.php                # Console commands routes
└── api.php                    # API routes (jika diperlukan)
```

---

## Tests Directory (`tests/`)

PHPUnit test files.

```
tests/
├── TestCase.php               # Base test case
│
├── Feature/                   # Feature/integration tests
│   ├── ExampleTest.php
│   ├── DashboardTest.php
│   ├── ProductCatalogTest.php
│   ├── ProductDetailTest.php
│   ├── ProductSearchTest.php
│   ├── CategoryFilterTest.php
│   ├── CartManagementTest.php
│   ├── CheckoutTest.php
│   │
│   ├── Admin/                 # Admin feature tests
│   │   ├── CategoryManagementTest.php
│   │   ├── ProductManagementTest.php
│   │   ├── OrderManagementTest.php
│   │   └── StoreSettingsTest.php
│   │
│   ├── Auth/                  # Authentication tests
│   │   ├── LoginTest.php
│   │   ├── RegisterTest.php
│   │   └── PasswordResetTest.php
│   │
│   ├── Models/                # Model-specific tests
│   │   └── ...
│   │
│   └── Settings/              # Settings tests
│       └── ...
│
└── Unit/                      # Unit tests
    └── ExampleTest.php
```

---

## Storage Directory (`storage/`)

Application storage untuk logs, cache, dan uploads.

```
storage/
├── app/
│   ├── public/                # User uploads (symlinked to public/storage)
│   │   ├── products/          # Product images
│   │   ├── categories/        # Category images
│   │   └── logos/             # Store logos
│   └── private/               # Private files
│
├── framework/
│   ├── cache/                 # Framework cache files
│   │   └── data/
│   ├── sessions/              # Session files
│   ├── testing/               # Testing cache
│   └── views/                 # Compiled Blade views
│
└── logs/
    └── laravel.log            # Application logs
```

---

## Public Directory (`public/`)

Publicly accessible files.

```
public/
├── build/                     # Vite compiled assets
│   ├── assets/
│   │   ├── app-[hash].js
│   │   ├── app-[hash].css
│   │   └── ...
│   └── manifest.json
│
├── storage/                   # Symlink ke storage/app/public
├── favicon.ico                # Site favicon
├── robots.txt                 # Search engine robots
└── index.php                  # Application entry point
```

---

## Naming Conventions

### PHP Files

| Type | Convention | Example |
|------|------------|---------|
| Controllers | `{Resource}Controller.php` | `ProductController.php` |
| Admin Controllers | `Admin\{Resource}Controller.php` | `Admin\OrderController.php` |
| Models | Singular, PascalCase | `Product.php`, `OrderItem.php` |
| Services | `{Resource}Service.php` | `CartService.php` |
| Requests | `{Action}{Resource}Request.php` | `StoreProductRequest.php` |
| Resources | `{Resource}Resource.php` | `ProductResource.php` |
| Migrations | `yyyy_mm_dd_hhmmss_description.php` | `2025_11_25_071003_create_products_table.php` |
| Factories | `{Model}Factory.php` | `ProductFactory.php` |
| Seeders | `{Model}Seeder.php` | `UserSeeder.php` |

### Vue/TypeScript Files

| Type | Convention | Example |
|------|------------|---------|
| Pages | PascalCase | `ProductDetail.vue` |
| Components | PascalCase | `ProductCard.vue` |
| Composables | `use{Name}.ts` | `useCart.ts` |
| Types | PascalCase | `Product.d.ts` |

### CSS Classes

- Tailwind utility classes
- BEM-like naming untuk custom classes jika diperlukan

---

## Key Files Explained

### Configuration Files

| File | Fungsi |
|------|--------|
| `.env` | Environment variables (tidak di-commit) |
| `.env.example` | Template environment variables |
| `composer.json` | PHP dependencies dan autoloading |
| `package.json` | Node dependencies dan npm scripts |
| `vite.config.ts` | Frontend build configuration |
| `tailwind.config.js` | Tailwind CSS theme customization |
| `tsconfig.json` | TypeScript compiler options |
| `phpunit.xml` | PHPUnit test configuration |
| `pint.json` | PHP code style configuration |

### Laravel Core Files

| File | Fungsi |
|------|--------|
| `artisan` | Laravel CLI entry point |
| `bootstrap/app.php` | Application bootstrap, middleware, exceptions |
| `bootstrap/providers.php` | Service provider registration |
| `public/index.php` | Web entry point |

### Important Scripts

```bash
# Development
composer run dev     # Start all development services
yarn dev            # Start Vite dev server
php artisan serve   # Start PHP development server

# Build
yarn build          # Build production assets

# Testing
php artisan test    # Run PHPUnit tests
vendor/bin/pint     # Format PHP code

# Database
php artisan migrate         # Run migrations
php artisan db:seed         # Run seeders
php artisan migrate:fresh --seed  # Fresh database with seeds

# Utilities
php artisan wayfinder:generate  # Generate Wayfinder routes
php artisan storage:link        # Create storage symlink
```

---

## Related Documentation

- [01_System_Architecture.md](01_System_Architecture.md) - Arsitektur sistem
- [02_Database_Schema.md](02_Database_Schema.md) - Skema database
- [03_API_Documentation.md](03_API_Documentation.md) - Dokumentasi API
