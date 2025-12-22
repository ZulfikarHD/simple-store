# Dokumentasi Keamanan Simple Store

**Penulis**: Zulfikar Hidayatullah  
**Versi**: 1.0  
**Terakhir Diperbarui**: Desember 2025

---

## Overview

Dokumentasi keamanan aplikasi Simple Store yang mencakup implementasi autentikasi, otorisasi, proteksi data, dan best practices keamanan yang diterapkan, yaitu: memastikan data pengguna dan transaksi terlindungi dengan standar keamanan modern.

---

## Authentication

### Laravel Fortify

Sistem autentikasi menggunakan Laravel Fortify v1.32.1 dengan fitur-fitur berikut:

| Fitur | Status | Keterangan |
|-------|--------|------------|
| Login | ✅ Aktif | Email + Password |
| Registration | ✅ Aktif | Dengan validasi email |
| Password Reset | ✅ Aktif | Via email link |
| Email Verification | ✅ Aktif | Verifikasi email wajib |
| Two-Factor Auth | ✅ Aktif | TOTP-based (Google Authenticator) |
| Password Confirmation | ✅ Aktif | Untuk sensitive actions |

### Password Policy

```php
// app/Actions/Fortify/PasswordValidationRules.php
protected function passwordRules(): array
{
    return [
        'required',
        'string',
        Password::min(8)
            ->mixedCase()      // Minimal 1 uppercase + 1 lowercase
            ->numbers()        // Minimal 1 angka
            ->symbols()        // Minimal 1 simbol
            ->uncompromised(), // Cek di database breach
    ];
}
```

**Kriteria Password**:
- Minimal 8 karakter
- Kombinasi huruf besar dan kecil
- Minimal 1 angka
- Minimal 1 karakter spesial
- Tidak termasuk dalam database password yang pernah bocor

### Password Hashing

```php
// Laravel secara otomatis menggunakan bcrypt
// dengan cost factor 12 (configurable)
'password' => 'hashed', // Automatic casting di model
```

### Two-Factor Authentication (2FA)

Implementasi 2FA menggunakan TOTP (Time-based One-Time Password):

```
┌─────────────────────────────────────────────────────────────┐
│                    2FA SETUP FLOW                            │
├─────────────────────────────────────────────────────────────┤
│  1. User mengaktifkan 2FA                                   │
│  2. System generate secret key                              │
│  3. QR Code ditampilkan untuk scanning                      │
│  4. User konfirmasi dengan memasukkan OTP                   │
│  5. Recovery codes di-generate dan ditampilkan              │
│  6. 2FA aktif untuk login selanjutnya                       │
└─────────────────────────────────────────────────────────────┘
```

**Recovery Codes**:
- 8 recovery codes di-generate
- Setiap code hanya bisa digunakan 1x
- User dapat regenerate codes kapan saja

### Session Management

```php
// config/session.php
'driver' => 'database',     // Sessions disimpan di database
'lifetime' => 120,          // 2 jam idle timeout
'expire_on_close' => false, // Tidak expire saat browser ditutup
'encrypt' => true,          // Session data encrypted
'same_site' => 'lax',       // CSRF protection
'http_only' => true,        // Tidak accessible via JavaScript
'secure' => env('APP_ENV') === 'production', // HTTPS only di production
```

---

## Authorization

### Role-Based Access Control (RBAC)

Sistem menggunakan simple role-based access dengan 2 roles:

| Role | Akses |
|------|-------|
| `admin` | Full access ke admin panel, CRUD produk, kategori, order management |
| `customer` | Browse produk, cart, checkout, view order history |

### Admin Middleware

```php
// app/Http/Middleware/EnsureUserIsAdmin.php
public function handle(Request $request, Closure $next): Response
{
    if (! auth()->check() || ! auth()->user()->isAdmin()) {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        abort(403);
    }

    return $next($request);
}
```

### Route Protection

```php
// routes/web.php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Semua admin routes dilindungi
    Route::resource('products', Admin\ProductController::class);
    Route::resource('categories', Admin\CategoryController::class);
    Route::resource('orders', Admin\OrderController::class);
});
```

### Password Confirmation untuk Sensitive Actions

```php
// Fitur-fitur yang memerlukan password confirmation:
// - Delete account
// - Update sensitive settings
// - Export data

Route::middleware(['auth', 'password.confirm'])->group(function () {
    Route::delete('/settings/profile', [ProfileController::class, 'destroy']);
});
```

---

## CSRF Protection

### Implementation

Laravel secara otomatis meng-handle CSRF protection untuk semua POST/PUT/PATCH/DELETE requests.

```php
// CSRF token otomatis di-include oleh Inertia.js
// Tidak perlu manual handling di frontend
```

**Excluded Cookies** (tidak dienkripsi):

```php
// bootstrap/app.php
$middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
```

### Form Protection

```vue
<!-- Inertia Form component otomatis include CSRF token -->
<Form action="/checkout" method="post">
  <!-- Form fields -->
</Form>
```

---

## XSS Prevention

### Vue.js Auto-Escaping

Vue.js secara otomatis escape semua data yang di-render:

```vue
<!-- Safe: Auto-escaped -->
<p>{{ product.name }}</p>

<!-- Dangerous: Raw HTML (hindari kecuali diperlukan) -->
<p v-html="product.description"></p>
```

### Server-Side Validation

```php
// Semua input divalidasi di server
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        // Input di-sanitize oleh Laravel
    ];
}
```

### Content Security Policy (Recommended)

```php
// Tambahkan di middleware atau nginx config
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';
```

---

## Template Injection Prevention (OWASP A03:2021)

### WhatsApp Template Validation

Template pesan WhatsApp divalidasi untuk mencegah injection attacks:

```php
// app/Http/Requests/Admin/UpdateStoreSettingsRequest.php
private function templateSafetyRule(): \Closure
{
    return function (string $attribute, mixed $value, \Closure $fail): void {
        // Pola berbahaya yang tidak diperbolehkan
        $dangerousPatterns = [
            '/<script/i',           // Script tags
            '/javascript:/i',       // JavaScript protocol
            '/on\w+\s*=/i',         // Event handlers
            '/<iframe/i',           // Iframe injection
            // ... more patterns
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $fail('Template mengandung konten yang tidak diperbolehkan.');
                return;
            }
        }
    };
}
```

### Variable Validation

Hanya variabel yang diizinkan yang dapat digunakan dalam template:

```php
$allowedVariables = [
    '{customer_name}',
    '{order_number}',
    '{total}',
    '{store_name}',
    '{cancellation_reason}',
];

// Validasi semua variabel dalam template
preg_match_all('/\{[^}]+\}/', $value, $matches);
foreach ($matches[0] as $variable) {
    if (!in_array($variable, $allowedVariables, true)) {
        $fail("Variabel '{$variable}' tidak valid.");
    }
}
```

### Data Sanitization

User data yang dimasukkan ke template di-sanitize:

```php
// app/Services/StoreSettingService.php
private function sanitizeForTemplate(?string $value): string
{
    if ($value === null) return '';

    // Remove null bytes dan control characters
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

    // Limit length untuk mencegah buffer overflow
    $value = mb_substr($value, 0, 500);

    // Escape special characters
    $value = str_replace(['\\', '{', '}'], ['\\\\', '\\{', '\\}'], $value);

    return $value;
}
```

### Icon Whitelist Validation

Timeline icons divalidasi dengan whitelist untuk mencegah component injection:

```php
private function allowedIconsRule(): \Closure
{
    return function (string $attribute, mixed $value, \Closure $fail): void {
        $allowedIcons = [
            'Clock', 'Timer', 'Hourglass', 'CalendarClock',
            'CheckCircle2', 'CircleCheck', 'Check', // ...
        ];

        if (!in_array($value, $allowedIcons, true)) {
            $fail('Icon yang dipilih tidak valid.');
        }
    };
}
```

---

## SQL Injection Prevention

### Eloquent ORM

Semua queries menggunakan Eloquent ORM dengan parameterized queries:

```php
// Safe: Parameterized query
Product::where('name', 'like', "%{$search}%")->get();

// Safe: Query builder
DB::table('products')->where('id', $id)->first();

// HINDARI: Raw query dengan string concatenation
// DB::raw("SELECT * FROM products WHERE name = '{$input}'");
```

### Query Binding

```php
// Laravel otomatis bind parameters
Product::whereRaw('LOWER(name) LIKE ?', ["%".strtolower($search)."%"]);
```

---

## File Upload Security

### Image Validation

```php
// app/Http/Requests/Admin/StoreProductRequest.php
public function rules(): array
{
    return [
        'image' => [
            'nullable',
            'image',                    // Harus file gambar
            'mimes:jpeg,png,jpg,webp',  // Format yang diizinkan
            'max:2048',                 // Maksimal 2MB
        ],
    ];
}
```

### File Storage

```php
// app/Services/ImageService.php
public function store(UploadedFile $file, string $path): string
{
    // Generate unique filename untuk mencegah path traversal
    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
    
    // Store di storage/app/public
    return $file->storeAs($path, $filename, 'public');
}
```

### Storage Configuration

```
storage/
└── app/
    └── public/           # Publicly accessible
        ├── products/     # Product images
        ├── categories/   # Category images
        └── logos/        # Store logos

public/
└── storage/             # Symlink ke storage/app/public
```

---

## Rate Limiting

### Login Throttling

```php
// Laravel Fortify default: 5 attempts per minute
// Setelah 5 failed attempts, user di-lock selama 1 menit
```

### API Rate Limiting

```php
// Configurable di RouteServiceProvider atau bootstrap/app.php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

---

## Data Protection

### Sensitive Data Handling

```php
// Model User - hidden attributes
protected $hidden = [
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
];
```

### Order Data Snapshot

```php
// Order items menyimpan snapshot harga untuk integritas data
OrderItem::create([
    'product_name' => $product->name,     // Snapshot, bukan FK
    'product_price' => $product->price,   // Harga saat pembelian
]);
```

### Environment Variables

```bash
# .env (NEVER commit to version control)
APP_KEY=base64:xxxxx...    # Encryption key
DB_PASSWORD=xxxxx          # Database password
MAIL_PASSWORD=xxxxx        # Mail password
```

---

## Encryption

### Application Key

```bash
# Generate secure application key
php artisan key:generate

# Key digunakan untuk:
# - Session encryption
# - Cookie encryption
# - Password reset tokens
```

### Data Encryption

```php
// Untuk data sensitif yang perlu di-encrypt
use Illuminate\Support\Facades\Crypt;

$encrypted = Crypt::encryptString($sensitiveData);
$decrypted = Crypt::decryptString($encrypted);
```

---

## HTTP Security Headers

### Recommended Headers (nginx/Apache)

```nginx
# nginx configuration
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;
```

### HTTPS Enforcement

```php
// Di production, force HTTPS
// config/app.php atau middleware
if (app()->environment('production')) {
    URL::forceScheme('https');
}
```

---

## Logging & Monitoring

### Security Logs

```php
// config/logging.php - Log authentication events
'channels' => [
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'info',
        'days' => 30,
    ],
],
```

### What to Log

| Event | Level | Channel |
|-------|-------|---------|
| Failed login attempts | WARNING | security |
| Password changes | INFO | security |
| 2FA enabled/disabled | INFO | security |
| Admin actions | INFO | security |
| Suspicious activity | ALERT | security |

---

## Security Checklist

### Development

- [ ] `.env` tidak di-commit ke version control
- [ ] `APP_DEBUG=false` di production
- [ ] Database credentials tidak hardcoded
- [ ] Dependencies di-update secara berkala
- [ ] Code review untuk security issues

### Deployment

- [ ] HTTPS enabled
- [ ] Security headers configured
- [ ] File permissions correct (755 folders, 644 files)
- [ ] `storage/` dan `bootstrap/cache/` writable
- [ ] Database backup configured
- [ ] Error logging enabled (tanpa expose sensitive data)

### Monitoring

- [ ] Failed login monitoring
- [ ] Error rate monitoring
- [ ] Performance monitoring
- [ ] Uptime monitoring

---

## Vulnerability Reporting

Jika menemukan vulnerability, laporkan ke:
- **Email**: security@example.com
- **Response Time**: 24-48 jam
- **Responsible Disclosure**: Ya

---

## Security Update Policy

1. **Critical**: Patch dalam 24 jam
2. **High**: Patch dalam 72 jam
3. **Medium**: Patch dalam 1 minggu
4. **Low**: Patch di release berikutnya

### Keeping Dependencies Updated

```bash
# Check for security advisories
composer audit

# Update dependencies
composer update

# Check npm dependencies
yarn audit
yarn upgrade-interactive
```

---

## Related Documentation

- [01_System_Architecture.md](01_System_Architecture.md) - Arsitektur sistem
- [06_Deployment_Guide.md](06_Deployment_Guide.md) - Panduan deployment
- [07_Testing_Documentation.md](07_Testing_Documentation.md) - Testing strategy

