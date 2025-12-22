# ADR-001: Security Middleware & Headers Implementation

## Status

**Accepted** - 2025-12-22

## Context

Aplikasi Simple Store memerlukan security hardening untuk mencegah common web vulnerabilities berdasarkan OWASP Top 10 2021. Audit security menemukan 12 vulnerabilities yang perlu segera ditangani, termasuk kurangnya security headers, IDOR vulnerabilities, dan potential XSS/injection attacks.

Beberapa pendekatan yang dapat diambil untuk implement security measures:
1. Third-party security packages (e.g., Laravel Security)
2. Custom middleware dengan manual configuration
3. Mix of Laravel built-in features + custom implementation
4. Web server level (Nginx/Apache) configuration

## Decision

Menggunakan **hybrid approach** dengan kombinasi:
- Laravel built-in features (policies, validation, middleware)
- Custom security middleware untuk HTTP headers
- Application-level rate limiting menggunakan Laravel RateLimiter
- Model-level input sanitization

### Specific Implementations:

#### 1. Security Headers via Middleware
Create custom `SecurityHeaders` middleware yang set comprehensive HTTP security headers:
- X-Frame-Options
- X-Content-Type-Options
- Content-Security-Policy
- Referrer-Policy
- Permissions-Policy

#### 2. Rate Limiting via AppServiceProvider
Configure custom rate limiters di `AppServiceProvider::boot()` instead of `bootstrap/app.php` untuk avoid facade initialization issues:
- Per-session rate limiting untuk cart operations
- Per-user/IP rate limiting untuk checkout

#### 3. Authorization via Policies
Use Laravel's authorization policies untuk IDOR prevention:
- `CartItemPolicy` untuk cart item access control
- `OrderPolicy` untuk order access control

#### 4. Input Sanitization via Model Mutators
Implement sanitization at model level menggunakan attribute mutators:
- Product names/descriptions XSS prevention
- Phone number normalization

## Alternatives Considered

### Alternative 1: Third-party Security Package
- **Pros**: 
  - Battle-tested implementation
  - Regular updates
  - Comprehensive coverage
- **Cons**: 
  - Additional dependency
  - Less control over implementation
  - Potential over-engineering untuk simple store
  - Learning curve untuk maintenance
- **Why rejected**: Project memiliki specific requirements yang simple, custom implementation lebih maintainable dan lightweight

### Alternative 2: Web Server Level Configuration
- **Pros**: 
  - Offload dari application
  - Better performance
  - Centralized config
- **Cons**: 
  - Tidak portable across environments
  - Requires server access
  - Harder to test locally
  - Deployment complexity
- **Why rejected**: Application-level implementation lebih portable dan easier to test/maintain

### Alternative 3: Pure Laravel Packages Only
- **Pros**: 
  - Laravel ecosystem integration
  - Well-documented
  - Community support
- **Cons**: 
  - May not cover all specific needs
  - Less customization flexibility
- **Why rejected**: Beberapa requirements (CSP config, specific rate limits) memerlukan custom logic

### Alternative 4: Facade-based Config di bootstrap/app.php
- **Pros**: 
  - Centralized configuration
  - Follows some Laravel patterns
- **Cons**: 
  - Facade initialization issues
  - Runtime errors di bootstrap phase
- **Why rejected**: Caused "facade root not set" error, moved to AppServiceProvider instead

## Consequences

### Positive
- **Control**: Full control atas security implementation
- **Maintainability**: Clear, documented, custom code
- **Performance**: Minimal overhead dengan targeted implementation
- **Testability**: Easy to test dengan Laravel testing tools
- **Portability**: Works across all environments tanpa server config
- **Flexibility**: Mudah adjust security rules sesuai kebutuhan

### Negative
- **Maintenance Burden**: Custom code requires own maintenance
- **Security Updates**: Must monitor OWASP updates manually
- **Documentation**: Requires thorough documentation (mitigated dengan docs ini)

### Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Custom implementation bugs | High | Comprehensive testing (16 tests), code review |
| Missing security updates | Medium | Regular OWASP Top 10 review, scheduled audits |
| Configuration drift | Low | Documented defaults, version control |
| Performance overhead | Low | Benchmarked (<5ms), minimal middleware |

## Implementation Details

### Files Created:
1. `app/Http/Middleware/SecurityHeaders.php` - Security headers middleware
2. `app/Policies/CartItemPolicy.php` - Cart authorization
3. `app/Rules/IndonesianPhoneNumber.php` - Custom validation

### Files Modified:
1. `app/Providers/AppServiceProvider.php` - Rate limiter config
2. `bootstrap/app.php` - Register SecurityHeaders middleware
3. `app/Models/Product.php` - XSS sanitization mutators
4. `app/Models/Order.php` - Mass assignment protection
5. `app/Services/CartService.php` - User-based cart logic
6. `app/Http/Responses/LoginResponse.php` - Session regeneration

### Configuration Points:
- Rate limits: `AppServiceProvider::boot()`
- Security headers: `SecurityHeaders::handle()`
- CSP policy: `SecurityHeaders` (customizable per environment)

## Testing Strategy

- Unit tests untuk validation rules
- Feature tests untuk authorization policies
- Integration tests untuk rate limiting
- Manual testing untuk security headers

## Future Considerations

1. Monitor rate limit effectiveness → adjust jika needed
2. Review CSP policy regularly → tighten as app evolves
3. Consider Laravel Sanctum jika API grows
4. Implement audit logging untuk security events
5. Add WAF (Web Application Firewall) di production jika needed

## References

- [OWASP Top 10 2021](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/11.x/security)
- [Mozilla CSP Guidelines](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
- [OWASP Secure Headers Project](https://owasp.org/www-project-secure-headers/)

## Review Schedule

- **Next Review**: 2026-01-22 (30 days post-implementation)
- **Regular Review**: Quarterly atau when OWASP Top 10 updates

---

**Author**: Zulfikar Hidayatullah  
**Date**: 2025-12-22  
**Status**: Accepted and Implemented

