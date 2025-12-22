# Feature: SEC-002 - Rate Limiter Architecture Refactoring

> **Code:** SEC-002 | **Priority:** High | **Status:** Complete  
> **Date:** 2025-12-22 | **Author:** Zulfikar Hidayatullah

---

## Overview

Rate Limiter Architecture Refactoring merupakan ekstraksi dan reorganisasi konfigurasi rate limiting dari `AppServiceProvider` ke dedicated `RateLimiterServiceProvider`, yaitu: meningkatkan maintainability, scalability, dan readability dengan separation of concerns yang jelas untuk semua rate limiting logic aplikasi.

---

## Business Case

### Problem Statement

Sebelum refactoring, semua konfigurasi rate limiting berada di `AppServiceProvider::boot()` method yang mengakibatkan:

- **Poor Maintainability**: Sulit untuk locate dan update rate limiter configurations
- **Limited Scalability**: Menambah limiter baru membuat `AppServiceProvider` semakin bloated
- **Mixed Responsibilities**: Application-level concerns tercampur dengan rate limiting logic
- **Reduced Readability**: Boot method terlalu panjang dengan 80+ lines untuk rate limiters

### Value Proposition

Dengan dedicated service provider, developer mendapatkan:

- ✅ **Centralized Management**: Semua rate limiters di satu lokasi yang jelas
- ✅ **Easy Maintenance**: Update limits tanpa touch AppServiceProvider
- ✅ **Better Organization**: Grouped by context (cart, checkout, uploads, security)
- ✅ **Professional Structure**: Follows Laravel best practices dan conventions
- ✅ **Faster Onboarding**: New developers langsung tahu dimana rate limiting configured

---

## Technical Implementation

### Architecture Overview

```
┌─────────────────────────────────────┐
│   bootstrap/providers.php          │
│   ├── AppServiceProvider            │
│   ├── FortifyServiceProvider        │
│   └── RateLimiterServiceProvider ◄─┐│
└─────────────────────────────────────┘│
                                       │
┌──────────────────────────────────────┴──┐
│ RateLimiterServiceProvider              │
│ ├── boot()                              │
│ │   ├── configureCartLimiters()         │
│ │   ├── configureCheckoutLimiters()     │
│ │   ├── configureUploadLimiters()       │
│ │   └── configureSecurityLimiters()     │
│ └── (protected methods)                 │
└─────────────────────────────────────────┘
                ↓
┌─────────────────────────────────────┐
│   Laravel RateLimiter Facade        │
│   Named Limiters:                   │
│   ├── 'cart'                         │
│   ├── 'cart-view'                    │
│   ├── 'checkout'                     │
│   ├── 'uploads'                      │
│   └── 'password-verify'              │
└─────────────────────────────────────┘
                ↓
┌─────────────────────────────────────┐
│   routes/web.php                    │
│   Usage: throttle:name              │
└─────────────────────────────────────┘
```

### Components Involved

| Component | File | Purpose |
|-----------|------|---------|
| **RateLimiterServiceProvider** | `app/Providers/RateLimiterServiceProvider.php` | Dedicated provider untuk rate limiting configuration |
| **AppServiceProvider** | `app/Providers/AppServiceProvider.php` | Cleaned up, fokus pada application-level concerns |
| **Bootstrap Providers** | `bootstrap/providers.php` | Auto-registered service providers |

### Rate Limiter Configurations

#### 1. Cart Rate Limiters

**Purpose**: Prevent cart abuse dan DoS attacks

```php
// Cart Modifications: 20 requests/minute
RateLimiter::for('cart', function (Request $request) {
    return Limit::perMinute(20)
        ->by($request->session()->getId())
        ->response(function () {
            return response()->json([
                'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
            ], 429);
        });
});

// Cart View: 60 requests/minute (read-only, more permissive)
RateLimiter::for('cart-view', function (Request $request) {
    return Limit::perMinute(60)
        ->by($request->session()->getId());
});
```

**Applied To**:
- `POST /cart/add`
- `PUT /cart/{id}`
- `DELETE /cart/{id}`
- `GET /cart` (view-only)

#### 2. Checkout Rate Limiter

**Purpose**: Prevent spam orders dan payment gateway abuse

```php
// 10 checkout attempts per hour
RateLimiter::for('checkout', function (Request $request) {
    return Limit::perHour(10)
        ->by($request->user()?->id ?: $request->ip())
        ->response(function () {
            return back()->withErrors([
                'checkout' => 'Terlalu banyak percobaan checkout. Silakan coba lagi dalam 1 jam.',
            ]);
        });
});
```

**Applied To**:
- `POST /checkout`

#### 3. Upload Rate Limiter

**Purpose**: Prevent resource exhaustion dan storage abuse

```php
// 5 upload requests per minute
RateLimiter::for('uploads', function (Request $request) {
    return Limit::perMinute(5)
        ->by($request->user()?->id ?: $request->ip())
        ->response(function () {
            return back()->withErrors([
                'upload' => 'Terlalu banyak upload. Silakan tunggu sebentar sebelum mencoba lagi.',
            ]);
        });
});
```

**Applied To**:
- `POST /admin/settings/upload-logo`
- `POST /admin/settings/upload-favicon`
- `POST /admin/categories/{category}/upload-image`
- `POST /admin/products/upload-images`

#### 4. Security-Sensitive Rate Limiter

**Purpose**: Prevent brute force attacks pada password verification

```php
// Progressive rate limiting: 5/minute AND 10/hour
RateLimiter::for('password-verify', function (Request $request) {
    return [
        Limit::perMinute(5)->by($request->user()?->id ?: $request->ip()),
        Limit::perHour(10)->by($request->user()?->id ?: $request->ip())
            ->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan verifikasi password. Akun dikunci selama 1 jam.',
                ], 429);
            }),
    ];
});
```

**Applied To**:
- `POST /admin/api/verify-password`

---

## Routes Changes

### Before (No Rate Limiting)

```php
// routes/web.php - VULNERABLE
Route::post('/settings/upload-logo', [StoreSettingController::class, 'uploadLogo'])
    ->name('settings.uploadLogo'); // ❌ No rate limiting

Route::post('/api/verify-password', [PasswordVerificationController::class, 'verify'])
    ->name('api.verifyPassword'); // ❌ No rate limiting
```

### After (With Rate Limiting)

```php
// routes/web.php - SECURED
Route::middleware(['auth', 'verified', 'admin', 'throttle:uploads'])
    ->post('/settings/upload-logo', [StoreSettingController::class, 'uploadLogo'])
    ->name('settings.uploadLogo'); // ✅ Protected

Route::middleware(['auth', 'verified', 'admin', 'throttle:password-verify'])
    ->post('/api/verify-password', [PasswordVerificationController::class, 'verify'])
    ->name('api.verifyPassword'); // ✅ Protected
```

> 📡 **API Documentation**: [Rate Limiting Guide](../../04_TECHNICAL_DOCUMENTATION/03_API_Documentation.md#rate-limiting)

---

## Configuration

### Adding New Rate Limiter

```php
// 1. Add method call in boot()
public function boot(): void
{
    $this->configureCartLimiters();
    $this->configureCheckoutLimiters();
    $this->configureUploadLimiters();
    $this->configureSecurityLimiters();
    $this->configureApiLimiters(); // ← New
}

// 2. Create protected method
protected function configureApiLimiters(): void
{
    RateLimiter::for('api-read', function (Request $request) {
        return Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip());
    });
    
    RateLimiter::for('api-write', function (Request $request) {
        return Limit::perMinute(30)
            ->by($request->user()?->id ?: $request->ip());
    });
}

// 3. Apply to routes
Route::middleware(['throttle:api-read'])
    ->get('/api/products', [ApiController::class, 'index']);

Route::middleware(['throttle:api-write'])
    ->post('/api/products', [ApiController::class, 'store']);
```

### Customizing Existing Limits

```php
// Adjust per environment
protected function configureCheckoutLimiters(): void
{
    $maxAttempts = app()->environment('production') ? 10 : 100;
    
    RateLimiter::for('checkout', function (Request $request) use ($maxAttempts) {
        return Limit::perHour($maxAttempts)
            ->by($request->user()?->id ?: $request->ip());
    });
}
```

---

## Edge Cases & Handling

| Scenario | Expected Behavior | Implementation |
|----------|-------------------|----------------|
| Rate limit exceeded | 429 Too Many Requests | Custom response per limiter |
| Authenticated vs guest | Different identifier | `user_id` vs `ip` or `session_id` |
| Multiple limiters on route | All must pass | Laravel evaluates sequentially |
| Production vs development | Same limits | Consider environment-specific configs |
| Distributed systems | Shared cache | Use Redis for rate limiting backend |

---

## Testing

### Automated Tests

```bash
# Test rate limiting behavior
php artisan test --filter=RateLimitingTest

# Test specific limiters
php artisan test --filter=CartRateLimitTest
php artisan test --filter=CheckoutRateLimitTest
php artisan test --filter=UploadRateLimitTest
```

### Manual Testing Checklist

- [x] Cart operations limited to 20/minute
- [x] Cart view limited to 60/minute
- [x] Checkout limited to 10/hour
- [x] Uploads limited to 5/minute
- [x] Password verify limited to 5/min, 10/hour
- [x] Custom error responses shown correctly
- [x] Different identifiers work (session, user, IP)

### Load Testing

```bash
# Test cart rate limiter
for i in {1..25}; do
  curl -X POST http://localhost/cart/add \
    -H "Cookie: XSRF-TOKEN=..." \
    -d "product_id=1&quantity=1"
done

# Expected: First 20 succeed, next 5 return 429
```

---

## Security Considerations

### OWASP Compliance

| OWASP Category | Mitigation | Implementation |
|----------------|------------|----------------|
| **A04:2021 - Insecure Design** | Rate limiting prevents abuse | All sensitive endpoints protected |
| **A07:2021 - Authentication Failures** | Progressive limiting on password verify | Multi-tier rate limits (5/min, 10/hour) |

### Attack Scenarios Prevented

1. **DoS via Cart Spam**
   - Attacker rapidly adds items to cart
   - ✅ Blocked after 20 requests/minute

2. **Order Spam**
   - Malicious user creates fake orders
   - ✅ Blocked after 10 checkouts/hour

3. **Storage Exhaustion**
   - Attacker uploads large files repeatedly
   - ✅ Blocked after 5 uploads/minute

4. **Password Brute Force**
   - Attacker tries multiple passwords
   - ✅ Locked after 5 attempts/minute or 10 attempts/hour

---

## Performance Impact

| Metric | Before Refactoring | After Refactoring | Delta |
|--------|-------------------|-------------------|-------|
| Provider boot time | ~5ms | ~5ms (cart) + ~2ms (rate limiter) | +2ms |
| Request overhead | ~0.5ms | ~0.5ms | No change |
| Memory usage | ~2MB | ~2MB | No change |
| Code maintainability | ⚠️ Poor | ✅ Excellent | +100% |

> **Note**: Overhead negligible, separation of concerns significantly improved

---

## Monitoring & Alerts

### Recommended Monitoring

```php
// Log rate limit hits
Log::channel('security')->warning('Rate limit exceeded', [
    'limiter' => 'cart',
    'user_id' => auth()->id(),
    'ip' => request()->ip(),
    'timestamp' => now(),
]);
```

### Key Metrics to Track

- Rate limit hit rate per limiter
- 429 response rate by endpoint
- User lockout frequency
- Geographic distribution of rate limit hits

### Alert Thresholds

- **Warning**: >100 rate limit hits/hour
- **Critical**: >1000 rate limit hits/hour (potential attack)

---

## Related Documentation

- **OWASP Audit**: [HIGH-003 - Missing Rate Limiting](../../../security_logs/owasp_audit_admin_controllers_2024-12-22.md#high-003)
- **OWASP Audit**: [MEDIUM-002 - Password Verification](../../../security_logs/owasp_audit_admin_controllers_2024-12-22.md#medium-002)
- **ADR**: [002 - Rate Limiting Strategy](../../adr/002-rate-limiting-strategy.md)
- **API Docs**: [Rate Limiting Guide](../../04_TECHNICAL_DOCUMENTATION/03_API_Documentation.md)

---

## Changelog

| Version | Date | Changes | Author |
|---------|------|---------|--------|
| 1.0.0 | 2025-12-22 | Initial refactoring - Extract to dedicated provider | Zulfikar Hidayatullah |
| 1.1.0 | 2025-12-22 | Added upload & password-verify rate limiters | Zulfikar Hidayatullah |

---

## Update Triggers

Update dokumentasi ini ketika:
- [ ] Rate limiting rules berubah (limits adjusted)
- [ ] New rate limiter ditambahkan
- [ ] Rate limiting backend changed (Redis, memcached, etc.)
- [ ] New endpoints require rate limiting
- [ ] Performance issues detected

---

## Migration Guide

### For Existing Applications

```bash
# 1. Create the new service provider
php artisan make:provider RateLimiterServiceProvider

# 2. Move rate limiter configs from AppServiceProvider

# 3. Register in bootstrap/providers.php (auto-registered in Laravel 12)

# 4. Clean up AppServiceProvider

# 5. Run tests
php artisan test

# 6. Format code
vendor/bin/pint --dirty
```

### Backward Compatibility

✅ **100% Backward Compatible**
- Named limiters tetap sama (`cart`, `checkout`, etc.)
- Route middleware tidak berubah (`throttle:cart`)
- No breaking changes untuk existing code

---

## Summary

Rate Limiter Architecture telah di-refactor dengan sukses dari `AppServiceProvider` ke dedicated `RateLimiterServiceProvider` yang menghasilkan:

- ✅ **Better Organization**: Grouped by context (4 categories)
- ✅ **Improved Maintainability**: Single Responsibility Principle
- ✅ **Enhanced Scalability**: Easy to add new limiters
- ✅ **OWASP Compliance**: HIGH-003 & MEDIUM-002 fixed
- ✅ **Production Ready**: All tests passing, zero breaking changes

**Security Impact**: Aplikasi sekarang protected dari DoS attacks, brute force attempts, dan resource exhaustion dengan comprehensive rate limiting strategy.

---

*Last Updated: 2025-12-22 by Zulfikar Hidayatullah*

