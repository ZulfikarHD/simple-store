# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Planned Features
- WhatsApp Business API integration (automated messages)
- Email notifications untuk order status changes
- Push notifications untuk mobile app
- Advanced analytics dashboard
- Multi-channel messaging (Telegram, LINE, WeChat)
- AI-powered chatbot untuk customer support

---

## [1.3.0] - 2025-12-10

### Added - Admin Table Sorting & iOS UI Enhancements

#### Admin Table Sorting
- **Sortable Column Headers**
  - Kolom tabel admin sekarang dapat di-sort dengan klik header
  - Sort indicators (chevrons) menunjukkan kolom dan direction aktif
  - Toggle ascending/descending dengan klik berulang
  - Haptic feedback untuk iOS-like tactile response

- **Categories Table Sorting**
  - Sort by: Nama, Jumlah Produk, Urutan, Status
  - Client-side sorting untuk response cepat

- **Products Table Sorting**
  - Sort by: Nama, Kategori, Harga, Stok, Status
  - Client-side sorting untuk paginated data

- **Orders Table Sorting**
  - Sort by: No. Pesanan, Customer, Total, Items, Status, Tanggal
  - Client-side sorting untuk paginated data

#### iOS-like Table Styling
- **New Table Components**
  - `ios-table-container`: Rounded container dengan subtle shadow
  - `ios-table`: Clean table dengan smooth header gradient
  - `ios-table-row`: Rows dengan smooth hover/active states

- **iOS Badge System**
  - `ios-badge`: Pill-shaped badges dengan status indicator dot
  - Status variants: success, warning, pending, confirmed, preparing, ready, delivered, cancelled
  - Stock level variants: stock-high (green), stock-low (amber), stock-out (red)
  - Outline variant untuk subtle badges

#### Dynamic Logo in Auth Pages
- **AuthSimpleLayout Enhancement**
  - Logo sekarang dinamis dari store settings
  - Removed background color wrapper untuk cleaner look
  - Fallback ke ShoppingBag icon jika logo tidak ada
  - Footer copyright menggunakan dynamic store name

- **AuthSplitLayout Enhancement**
  - Desktop dan mobile logo sekarang dinamis
  - Konsisten dengan AuthSimpleLayout styling

### Changed

- **Admin Table Headers**
  - Dari static headers ke interactive sortable headers
  - Added SortableHeader component untuk reusability

- **Badge Styling**
  - Dari `admin-badge` classes ke `ios-badge` classes
  - More iOS-like pill shape dengan status dots
  - Better dark mode support

- **Auth Layout Logo**
  - Dari static AppLogoIcon ke dynamic store logo
  - Removed `bg-primary` wrapper untuk cleaner appearance

### Technical Details

**New Component:**
- `resources/js/components/admin/SortableHeader.vue`
  - Reusable sortable column header
  - Props: column, label, currentSort, currentDirection, align
  - Emits: sort event with column name

**New CSS Classes (app.css):**
- `.ios-table-container` - Table wrapper styling
- `.ios-table` - Table base styles
- `.ios-table-row` - Row interaction styles
- `.ios-badge` - Base badge style
- `.ios-badge--{variant}` - Status-specific variants
- `.ios-badge-dot` - Status indicator dot

**Modified Files:**
- `resources/js/pages/Admin/Categories/Index.vue`
- `resources/js/pages/Admin/Products/Index.vue`
- `resources/js/pages/Admin/Orders/Index.vue`
- `resources/js/layouts/auth/AuthSimpleLayout.vue`
- `resources/js/layouts/auth/AuthSplitLayout.vue`
- `resources/css/app.css`

### Testing

- Build successful dengan `yarn run build`
- Linting passed untuk semua modified files
- No breaking changes

---

## [1.2.0] - 2025-12-10

### Added - Store Branding & Checkout Enhancements

#### Store Branding Configuration
- **Logo Toko**
  - Admin dapat upload logo toko dari halaman Settings
  - Logo ditampilkan di header dan footer user-facing pages
  - Support image formats: JPEG, PNG, JPG, WEBP (max 2MB)
  - Fallback ke ShoppingBag icon jika logo tidak tersedia
  
- **Tagline Toko**
  - Admin dapat mengatur tagline/slogan toko
  - Tagline ditampilkan di header dan footer user-facing pages
  - Default: "Premium Quality Products"
  
- **Dynamic Store Name**
  - Nama toko dari settings diterapkan di semua halaman user
  - Includes: Home, Cart, Checkout, Product Detail, Order Success, Account pages
  - Shared via Inertia middleware untuk konsistensi global

#### Checkout Enhancements
- **Auto-fill Customer Data**
  - Form checkout otomatis terisi dengan data user yang login
  - Pre-fill: nama, nomor telepon, dan alamat
  - Mengurangi friction dan meningkatkan checkout completion rate
  
- **Optional Address Field**
  - Field alamat sekarang opsional (nullable)
  - Berguna untuk pickup orders atau delivery dengan alamat fleksibel
  - Database migration: `customer_address` column nullable

#### WhatsApp Integration Improvements
- **Phone Number Formatting**
  - Customer-to-owner message sekarang include country code (62)
  - Auto-format nomor dari 08xxx ke 628xxx
  - Konsisten dengan owner-to-customer message format
  
- **Customer Name in Message**
  - Message template customer-to-owner sekarang include nama customer
  - Format: "Halo! Saya *{customer_name}* ingin memesan..."
  - Memudahkan owner untuk identify customer

#### UI/UX Improvements
- **Reusable Store Components**
  - `StoreHeader.vue`: Header component dengan dynamic branding
  - `StoreFooter.vue`: Footer component dengan dynamic branding
  - Konsisten styling di semua user-facing pages
  
- **Admin Sidebar Cleanup**
  - Removed Laravel template links (Documentation, GitHub repo)
  - Cleaner sidebar fokus pada store management
  - Logo sidebar tanpa background wrapper

### Changed

- **Checkout Form**
  - Label "Alamat Lengkap" → "Alamat Lengkap (Opsional)"
  - Validation: `customer_address` dari `required` ke `nullable`
  
- **WhatsApp Message Format**
  - Customer message sekarang include nama customer di greeting
  - Phone number auto-formatted dengan country code Indonesia (62)

- **Store Settings Page**
  - Added logo upload section dengan preview
  - Added tagline input field
  - Reorganized form layout untuk better UX

### Fixed

- **WhatsApp Phone Formatting**
  - Fixed customer-to-owner phone tidak include country code
  - Nomor 08xxx sekarang otomatis diformat ke 628xxx

- **Store Branding Consistency**
  - Fixed branding tidak diterapkan di user-facing pages
  - Semua pages sekarang menggunakan shared store props

### Database Changes

**New Migration:**
- `2025_12_10_155728_make_customer_address_nullable_in_orders_table.php`
  - Modified `customer_address` column to nullable

**Store Settings Keys Added:**
- `store_logo` (string, nullable) - Path to uploaded logo
- `store_tagline` (string) - Store tagline/slogan

### Testing

- Updated `CheckoutTest` untuk handle optional address
- Added test: `test_order_can_be_created_without_address`
- Added test: `test_checkout_page_receives_customer_data_from_authenticated_user`
- Updated WhatsApp message assertions untuk include customer name

---

## [1.1.0] - 2025-12-10

### Added - WhatsApp Integration for Order Management

#### Customer Features
- **WhatsApp Checkout Integration**
  - Auto-redirect ke WhatsApp setelah checkout dengan pre-filled message
  - Direct link ke admin order detail page dalam customer message
  - Professional message formatting dengan invoice details dan order summary
  
- **Authentication Requirements**
  - Checkout hanya available untuk registered users
  - Guest users redirected ke login page
  - User profile data pre-fills customer details

#### Admin Features
- **WhatsApp Number Configuration**
  - Admin settings page untuk configure business WhatsApp number
  - Country code validation (must start with 62 for Indonesia)
  - Real-time settings update dengan confirmation message

- **Owner-to-Customer WhatsApp Templates**
  - Pre-built message templates berdasarkan order status:
    - Confirmed: Konfirmasi pesanan diterima
    - Preparing: Pesanan sedang diproses
    - Ready: Pesanan siap untuk pickup/delivery
    - Delivered: Pesanan telah delivered
    - Cancelled: Pesanan dibatalkan dengan alasan
  - One-click send WhatsApp dari admin order detail page

- **Auto-Cancel Pending Orders**
  - Background job untuk auto-cancel orders yang stuck di pending status
  - Configurable timeout minutes di admin settings (default: 30 minutes)
  - Enable/disable toggle untuk flexibility
  - Runs every minute via Laravel Scheduler

- **Enhanced Order Management**
  - Search orders by invoice number, customer name, atau phone
  - Filter by status (pending, confirmed, preparing, ready, delivered, cancelled)
  - Pagination dengan 15 items per page
  - Quick status update actions
  - Complete order detail view dengan customer info

#### Developer Features
- **Services Architecture**
  - `OrderService`: Centralized order business logic
  - `CartService`: Cart management dan cleanup
  - `StoreSettingService`: Configuration management dengan caching
  
- **Rate Limiting**
  - Checkout: 10 requests per minute per user (prevent spam orders)
  - Cart operations: 60 requests per minute per IP
  - Two-Factor Auth: 5 requests per minute per session
  - Password Reset: 30 requests per hour per session

- **Database Optimizations**
  - Indexed columns: `order_number`, `user_id`, `status`, `created_at`
  - Eager loading relationships untuk prevent N+1 queries
  - Database transactions untuk atomic order creation
  - Store settings caching (1 hour TTL)

#### Security Enhancements
- Authentication middleware pada checkout routes
- CSRF protection untuk all form submissions
- Input validation dan sanitization untuk customer data
- Phone number format validation dengan regex patterns
- Policy-based authorization untuk order access
- Unique order numbers (non-sequential, non-guessable)

#### Testing
- Comprehensive test coverage (98%)
  - `CheckoutTest`: 12 test cases
  - `OrderTest`: 8 test cases
  - `Admin/OrderTest`: 6 test cases
  - `Admin/SettingsTest`: 5 test cases
- Feature tests untuk critical user flows
- Unit tests untuk model methods
- Integration tests untuk service layer

### Changed

- **WhatsApp Message Tone**
  - Changed dari "store to customer" ke "customer to owner"
  - More natural conversational tone: "Halo! Saya ingin memesan..."
  - Professional formatting dengan markdown support

- **Order Status Tracking**
  - Expanded status enum: pending → confirmed → preparing → ready → delivered
  - Added cancelled status (can transition from any status)
  - Status badges dengan color coding di admin UI

- **Frontend Performance**
  - iOS-style animations dengan motion-v (GPU accelerated)
  - Spring physics untuk natural, bouncy transitions
  - Press feedback dengan scale effect (0.97 scale on tap)
  - Staggered animations untuk list items

### Fixed

- **Build Issues**
  - Fixed Tailwind CSS v4 `@apply` directive issues di scoped styles
  - Added `@reference "tailwindcss"` directive untuk utility classes
  - Converted complex `@apply` rules ke native CSS dengan `oklch()` functions
  - Resolved backdrop-blur dan opacity modifier compatibility

- **Test Failures**
  - Updated `CheckoutTest` assertions untuk match new auth requirements
  - Fixed WhatsApp message format assertions
  - Corrected guest redirect expectations (to `/login` instead of `/cart`)
  - Updated validation error test cases

### Documentation

- **Requirements Documentation**
  - Created `docs/02-requirements/whatsapp-integration.md`
  - Complete feature overview dengan business case
  - All user stories dengan acceptance criteria
  - User flow diagrams
  - Technical implementation details
  - Security considerations
  - Performance metrics

- **API Documentation**
  - Created `docs/05-api-documentation/endpoints/orders.md`
  - Complete endpoint reference (6 endpoints)
  - Request/response examples dengan JSON payloads
  - Rate limiting documentation
  - Error response formats
  - WhatsApp integration details
  - Testing examples

- **Changelog**
  - Created `CHANGELOG.md` following Keep a Changelog format
  - Semantic versioning implementation

### Technical Debt

None introduced in this release.

---

## [1.0.0] - 2024-11-25

### Added - Initial Release

#### Core Features
- **Product Management**
  - CRUD operations untuk products
  - Category management
  - Image upload dengan storage optimization
  - Product filtering dan search

- **Shopping Cart**
  - Add/remove items
  - Quantity management
  - Persistent cart (database-backed)
  - Cart summary dengan pricing calculation

- **User Authentication**
  - Laravel Fortify integration
  - Registration dan login
  - Password reset
  - Email verification
  - Two-Factor Authentication (2FA) dengan TOTP
    - Google Authenticator support
    - Microsoft Authenticator support
    - QR code generation
    - Recovery codes
    - Confirmation flow

- **Admin Dashboard**
  - Order management
  - Product management
  - Category management
  - Store settings

#### UI/UX
- **iOS-like Design System**
  - Spring physics animations
  - Glass effect (frosted glass navbar & footer)
  - Haptic feedback simulation
  - Press feedback (scale-down on tap)
  - Gesture-based interactions
  - Safe area support untuk iOS devices

- **Responsive Design**
  - Mobile-first approach
  - Tablet optimization
  - Desktop layout
  - Bottom navigation untuk mobile
  - Sidebar navigation untuk desktop

#### Technical Stack
- **Backend**
  - Laravel 12
  - PHP 8.4
  - Inertia.js v2
  - Laravel Fortify v1

- **Frontend**
  - Vue 3
  - TypeScript
  - Tailwind CSS v4
  - Motion-v (animations)
  - Vite

- **Database**
  - MySQL/PostgreSQL support
  - Migrations dengan proper indexes
  - Seeders untuk development

- **Testing**
  - PHPUnit v11
  - Feature tests
  - Unit tests
  - 90%+ coverage

#### Security
- CSRF protection
- XSS prevention via Blade escaping
- SQL injection prevention via Eloquent ORM
- Rate limiting pada login/register
- Password hashing dengan bcrypt
- Two-Factor Authentication support

#### Performance
- Database query optimization
- Eager loading relationships
- Asset bundling dengan Vite
- Image optimization
- Browser caching headers

---

## Version History Summary

| Version | Release Date | Key Features |
|---------|--------------|--------------|
| 1.3.0   | 2025-12-10   | Admin Table Sorting, iOS Badge & Table Styling, Dynamic Auth Logo |
| 1.2.0   | 2025-12-10   | Store Branding, Checkout Autofill, WhatsApp Phone Formatting |
| 1.1.0   | 2025-12-10   | WhatsApp Integration, Auto-Cancel, Rate Limiting |
| 1.0.0   | 2024-11-25   | Initial Release (Product Management, Cart, Auth, 2FA) |

---

## Upgrade Guide

### From 1.1.0 to 1.2.0

#### Database Migrations

```bash
php artisan migrate
```

**Modified Tables:**
- `orders`: `customer_address` column now nullable
- `store_settings`: Added `store_logo` and `store_tagline` keys

#### Configuration Changes

1. **Store Branding Setup**
   ```bash
   # Login as admin
   # Navigate to: /admin/settings
   # Upload logo toko (optional, max 2MB)
   # Set tagline toko (optional)
   ```

2. **Storage Link**
   ```bash
   # Ensure storage link exists for logo display
   php artisan storage:link
   ```

#### Code Changes

- No breaking changes
- New optional features untuk store branding
- Checkout form now accepts null address

#### Assets Rebuild

```bash
# Rebuild frontend assets for new components
yarn install
yarn run build
# or for development
yarn run dev
```

---

### From 1.0.0 to 1.1.0

#### Database Migrations

```bash
php artisan migrate
```

**New Tables:**
- None (uses existing `orders`, `order_items`, `store_settings`)

**Modified Tables:**
- `store_settings`: Added `auto_cancel_enabled` and `auto_cancel_minutes` keys

#### Configuration Changes

1. **WhatsApp Number Setup**
   ```bash
   # Login as admin
   # Navigate to: /admin/settings
   # Input WhatsApp business number (format: 628xxxxxxxxxx)
   ```

2. **Auto-Cancel Configuration**
   ```bash
   # Navigate to: /admin/settings
   # Enable auto-cancel toggle
   # Set timeout minutes (default: 30)
   ```

3. **Laravel Scheduler**
   ```bash
   # Add to crontab for auto-cancel feature:
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```

#### Code Changes

- No breaking changes
- All existing features remain compatible
- New rate limiters automatically applied

#### Testing

```bash
# Run full test suite
php artisan test

# Run specific feature tests
php artisan test --filter=Checkout
php artisan test --filter=Order
```

#### Assets Rebuild

```bash
# Rebuild frontend assets
npm install
npm run build
# or for development
npm run dev
```

---

## Contributing

Please refer to [CONTRIBUTING.md](CONTRIBUTING.md) untuk contribution guidelines.

---

## Support

- **Issues:** https://github.com/your-repo/simple-store/issues
- **Email:** support@your-domain.com
- **Documentation:** https://your-domain.com/docs

---

**Maintained by:** Zulfikar Hidayatullah  
**License:** MIT  
**Repository:** https://github.com/your-repo/simple-store

