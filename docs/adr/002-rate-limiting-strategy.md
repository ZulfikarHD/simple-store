# ADR-002: Rate Limiting Strategy & Implementation

## Status

**Accepted** - 2025-12-22

## Context

OWASP security audit menemukan bahwa aplikasi vulnerable terhadap DoS (Denial of Service) attacks karena tidak adanya rate limiting pada cart dan checkout operations. Attacker dapat:
1. Flood cart endpoints dengan spam requests
2. Create massive number of cart items
3. Perform checkout flooding
4. Degrade application performance untuk all users

Perlu implement rate limiting yang effective tapi tidak mengganggu legitimate users.

## Decision

Implement **session-based dan user-based rate limiting** dengan three-tier approach:

### Tier 1: Cart Modifications (Strict)
- **Limit**: 20 requests per minute
- **Identifier**: Session ID
- **Rationale**: Write operations lebih expensive, perlu strict limit

### Tier 2: Cart Viewing (Permissive)
- **Limit**: 60 requests per minute  
- **Identifier**: Session ID
- **Rationale**: Read operations cheaper, allow frequent viewing

### Tier 3: Checkout (Very Strict)
- **Limit**: 10 requests per hour
- **Identifier**: User ID (authenticated) atau IP (guest)
- **Rationale**: Order creation is critical operation, prevent spam orders

### Implementation Location
Rate limiters dikonfigurasi di `AppServiceProvider::boot()` instead of `bootstrap/app.php` untuk avoid facade initialization issues during bootstrap.

## Alternatives Considered

### Alternative 1: IP-based Rate Limiting Only
```php
RateLimiter::for('cart', fn (Request $request) => 
    Limit::perMinute(20)->by($request->ip())
);
```

- **Pros**: Simple implementation, works untuk all users
- **Cons**: 
  - Shared IP (NAT, corporate) unfairly limited
  - VPN/proxy bypass easily
  - Guest dan authenticated users treated same
- **Why rejected**: Session-based lebih fair dan accurate tracking

### Alternative 2: User-based Rate Limiting Only
```php
RateLimiter::for('cart', fn (Request $request) => 
    Limit::perMinute(20)->by($request->user()?->id ?: 'guest')
);
```

- **Pros**: Per-user fairness, authenticated users tracked properly
- **Cons**: 
  - All guests share same limit (unfair)
  - Guest dapat spam sebelum login
- **Why rejected**: Tidak effective untuk guest users

### Alternative 3: Fixed Global Rate Limit
```php
RateLimiter::for('api', fn (Request $request) => 
    Limit::perMinute(60)
);
```

- **Pros**: Very simple, one config untuk all
- **Cons**: 
  - One size doesn't fit all operations
  - Expensive operations (checkout) sama limit dengan cheap operations (view)
  - Tidak fair across users
- **Why rejected**: Tidak granular enough, not optimal

### Alternative 4: Redis-based Distributed Rate Limiting
- **Pros**: 
  - Works across multiple servers
  - Centralized tracking
  - More accurate
- **Cons**: 
  - Requires Redis infrastructure
  - Additional complexity
  - Overkill untuk single-server deployment
- **Why rejected**: Application currently single-server, dapat upgrade later jika needed

## Consequences

### Positive
- **DoS Protection**: Effective prevention of spam/flooding
- **Fair Usage**: Session-based tracking fair across users
- **Graduated Limits**: Different limits untuk different operation costs
- **User Experience**: Limits high enough untuk legitimate users
- **Clear Feedback**: Custom 429 responses with helpful messages

### Negative
- **Legitimate User Impact**: Heavy users might hit limits (mitigated dengan reasonable limits)
- **Session Management**: Depends on session working correctly
- **Monitoring Needed**: Must monitor untuk adjust limits jika needed

### Risks & Mitigations

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Legitimate users hit limits | Medium | Low | Limits set high based on usage patterns |
| Session hijacking bypass | Low | Low | Sessions short-lived, regenerated on login |
| Distributed attack still possible | Medium | Medium | Can add IP-based fallback jika needed |
| False positives | Low | Low | Limits generous untuk normal usage |

## Implementation Details

### Configuration Location
```php
// app/Providers/AppServiceProvider.php::boot()

RateLimiter::for('cart', function (Request $request) {
    return Limit::perMinute(20)
        ->by($request->session()->getId())
        ->response(function () {
            return response()->json([
                'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
            ], 429);
        });
});

RateLimiter::for('cart-view', function (Request $request) {
    return Limit::perMinute(60)
        ->by($request->session()->getId());
});

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

### Route Application
```php
// routes/web.php

Route::middleware(['throttle:cart-view'])->group(function () {
    Route::get('/cart', [CartController::class, 'show']);
});

Route::middleware(['throttle:cart'])->group(function () {
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);
});

Route::middleware(['auth', 'throttle:checkout'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show']);
    Route::post('/checkout', [CheckoutController::class, 'store']);
});
```

## Limit Justification

### Cart Modifications (20/minute)
- **Calculation**: Normal user adds ~5 items dalam session
- **Buffer**: 4x normal usage untuk edge cases
- **Peak**: Allows burst activity tanpa block

### Cart View (60/minute)
- **Calculation**: User might refresh/navigate multiple times
- **Buffer**: 1 request per second = very permissive
- **Peak**: Allows rapid navigation tanpa friction

### Checkout (10/hour)
- **Calculation**: Legitimate user rarely checkout >2-3x per hour
- **Buffer**: 3-5x normal untuk account multiple failed attempts
- **Peak**: Strict enough prevent spam orders

## Monitoring & Tuning

### Metrics to Track
- Rate limit hit rate per endpoint
- 429 response count
- Average requests per session
- Checkout success vs rate-limited ratio

### Tuning Triggers
**Increase limits if**:
- >5% legitimate users hitting limits
- Checkout success rate drops due to rate limiting
- Business requirements change (flash sale, etc.)

**Decrease limits if**:
- Abuse patterns detected
- Server load increases significantly
- Cost optimization needed

### Alert Thresholds
- Warning: >100 rate limit hits per hour
- Critical: >1000 rate limit hits per hour (potential attack)

## Future Enhancements

1. **Dynamic Rate Limiting**: Adjust based on server load
2. **User Tier Limits**: Premium users get higher limits
3. **Geographic Limits**: Different limits per region
4. **Redis Backend**: For distributed deployments
5. **Whitelist**: Trusted IPs bypass limits
6. **Exponential Backoff**: Increase penalty untuk repeat offenders

## Testing Strategy

### Unit Tests
- Verify rate limiter configurations
- Test custom response formats

### Integration Tests
- Simulate rapid requests
- Verify 429 responses
- Test limit reset behavior

### Load Tests
- Benchmark performance impact (<1ms overhead observed)
- Verify limits hold under load

## Rollback Plan

If rate limiting causes issues:
1. Increase limits temporarily (2x or 5x)
2. Monitor impact
3. Analyze root cause
4. Adjust permanently or rollback

Emergency disable:
```php
// Temporarily disable specific limiter
RateLimiter::for('cart', fn () => Limit::none());
```

## References

- [Laravel Rate Limiting Docs](https://laravel.com/docs/11.x/rate-limiting)
- [OWASP DoS Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Denial_of_Service_Cheat_Sheet.html)
- [API Rate Limiting Best Practices](https://cloud.google.com/architecture/rate-limiting-strategies-techniques)

## Review Schedule

- **First Review**: 2026-01-05 (2 weeks post-implementation)
- **Regular Review**: Monthly untuk first 3 months, then quarterly
- **Ad-hoc Review**: If abuse patterns detected

---

**Author**: Zulfikar Hidayatullah  
**Date**: 2025-12-22  
**Status**: Accepted and Implemented  
**Last Reviewed**: 2025-12-22

