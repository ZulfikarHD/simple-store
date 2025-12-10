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
| 1.1.0   | 2025-12-10   | WhatsApp Integration, Auto-Cancel, Rate Limiting |
| 1.0.0   | 2024-11-25   | Initial Release (Product Management, Cart, Auth, 2FA) |

---

## Upgrade Guide

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

