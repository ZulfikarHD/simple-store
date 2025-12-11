# Security Best Practices

## Overview
Panduan security best practices untuk aplikasi Simple Store, yaitu: preventive measures, security configurations, dan incident response procedures.

## Application Security

### 1. Authentication & Authorization

#### Strong Password Policy
```php
// config/fortify.php atau validation rules
'password' => [
    'required',
    'string',
    'min:8',              // Minimum 8 characters
    'regex:/[a-z]/',      // At least satu lowercase
    'regex:/[A-Z]/',      // At least satu uppercase
    'regex:/[0-9]/',      // At least satu angka
    'regex:/[@$!%*#?&]/', // At least satu special char
],
```

#### Session Security
```env
# .env
SESSION_LIFETIME=120        # 2 hours
SESSION_ENCRYPT=true        # Encrypt session data
SESSION_SECURE_COOKIE=true  # HTTPS only
SESSION_HTTP_ONLY=true      # Not accessible via JavaScript
SESSION_SAME_SITE=strict    # CSRF protection
```

#### Two-Factor Authentication (jika implemented)
- Encourage atau require 2FA untuk admin users
- Use TOTP (Time-based One-Time Password)
- Provide backup codes

### 2. Data Protection

#### Sensitive Data Encryption
```php
// Encrypt sensitive data sebelum storing
use Illuminate\Support\Facades\Crypt;

// Encrypt
$encrypted = Crypt::encryptString('sensitive data');

// Decrypt
$decrypted = Crypt::decryptString($encrypted);
```

#### Database Security
```php
// Always use parameter binding (Eloquent does this automatically)
// NEVER do this:
DB::select("SELECT * FROM users WHERE email = '{$email}'"); // VULNERABLE!

// Always do this:
DB::select("SELECT * FROM users WHERE email = ?", [$email]); // SAFE
// atau
User::where('email', $email)->first(); // SAFE (Eloquent)
```

### 3. Input Validation & Sanitization

#### Validate All Input
```php
// Always use Form Requests
php artisan make:request StoreUserRequest

// StoreUserRequest.php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'phone' => ['required', 'regex:/^08[0-9]{8,11}$/'],
    ];
}
```

#### XSS Prevention
```php
// Vue/Blade automatically escapes output
// Vue template
<p>{{ userInput }}</p>  <!-- Safe, auto-escaped -->

// Only use v-html dengan trusted content
<div v-html="trustedHtml"></div>  <!-- Only jika absolutely necessary -->
```

#### CSRF Protection
```php
// Laravel automatically provides CSRF protection
// Ensure CSRF token included dalam forms

// Blade
<form method="POST">
    @csrf
    <!-- form fields -->
</form>

// Inertia automatically includes CSRF token
```

### 4. API Security

#### Rate Limiting
```php
// routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    // 60 requests per minute
});

// Custom rate limiter
// app/Providers/RouteServiceProvider.php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

#### API Authentication
```php
// Use Sanctum untuk API authentication
// config/sanctum.php
'expiration' => 60, // Token expiry dalam minutes

// Implement token rotation
// Revoke old tokens regularly
```

## Server Security

### 1. File Permissions
```bash
# Application directory
chmod -R 755 /var/www/simple-store
chown -R www-data:www-data /var/www/simple-store

# Storage dan bootstrap/cache
chmod -R 775 /var/www/simple-store/storage
chmod -R 775 /var/www/simple-store/bootstrap/cache

# .env file (sensitive)
chmod 600 /var/www/simple-store/.env
chown www-data:www-data /var/www/simple-store/.env
```

### 2. Web Server Configuration

#### Nginx Security Headers
```nginx
# /etc/nginx/sites-available/simple-store

server {
    # ... other config ...
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Content-Security-Policy "default-src 'self' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' data: https:; connect-src 'self' https:; frame-ancestors 'self';" always;
    
    # Hide Nginx version
    server_tokens off;
    
    # Disable unnecessary HTTP methods
    if ($request_method !~ ^(GET|HEAD|POST|PUT|DELETE|OPTIONS)$ ) {
        return 405;
    }
    
    # Prevent access ke sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    location ~ /\.env {
        deny all;
    }
    
    location ~ /\.git {
        deny all;
    }
}
```

### 3. Firewall Configuration
```bash
# Setup UFW (Uncomplicated Firewall)
sudo ufw status

# Allow only necessary ports
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status verbose
```

### 4. SSH Security
```bash
# Edit SSH config
sudo nano /etc/ssh/sshd_config

# Recommended settings:
PermitRootLogin no              # Disable root login
PasswordAuthentication no       # Use SSH keys only
PubkeyAuthentication yes        # Enable key auth
Port 2222                       # Change dari default port 22 (optional)
MaxAuthTries 3                  # Limit login attempts
ClientAliveInterval 300         # Timeout idle sessions
ClientAliveCountMax 2

# Restart SSH
sudo systemctl restart sshd
```

## Database Security

### 1. User Permissions
```sql
-- Create dedicated user dengan minimal permissions
CREATE USER 'simple_store_user'@'localhost' IDENTIFIED BY 'strong_password';

-- Grant only necessary permissions
GRANT SELECT, INSERT, UPDATE, DELETE ON simple_store.* TO 'simple_store_user'@'localhost';

-- NO DROP, CREATE, ALTER permissions untuk app user!
-- Use separate admin user untuk schema changes

FLUSH PRIVILEGES;
```

### 2. Connection Security
```env
# .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1    # Use localhost atau private IP only
DB_PORT=3306
DB_DATABASE=simple_store
DB_USERNAME=simple_store_user
DB_PASSWORD=strong_secure_password

# Never expose database ke public internet!
```

### 3. Backup Encryption
```bash
# Encrypt database backups
mysqldump -u user -p simple_store | gzip | openssl enc -aes-256-cbc -salt -out backup.sql.gz.enc -k YOUR_ENCRYPTION_KEY

# Decrypt when needed
openssl enc -d -aes-256-cbc -in backup.sql.gz.enc -k YOUR_ENCRYPTION_KEY | gunzip | mysql -u user -p simple_store
```

## SSL/TLS Configuration

### 1. Strong SSL Configuration
```nginx
# Modern SSL configuration
ssl_protocols TLSv1.2 TLSv1.3;
ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
ssl_prefer_server_ciphers off;

# HSTS (HTTP Strict Transport Security)
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

# OCSP Stapling
ssl_stapling on;
ssl_stapling_verify on;
ssl_trusted_certificate /path/to/chain.pem;
```

### 2. Certificate Management
```bash
# Auto-renewal dengan certbot (Let's Encrypt)
sudo certbot renew --dry-run

# Check expiry
echo | openssl s_client -servername your-domain.com -connect your-domain.com:443 2>/dev/null | openssl x509 -noout -dates
```

## Environment Variables Security

### 1. Secure .env File
```bash
# Proper permissions
chmod 600 .env
chown www-data:www-data .env

# Never commit .env ke git
# Ensure .gitignore includes .env
echo ".env" >> .gitignore
```

### 2. Sensitive Data Handling
```env
# Use strong, unique values
APP_KEY=base64:... # Generated by php artisan key:generate
DB_PASSWORD=... # Strong password

# Production settings
APP_ENV=production
APP_DEBUG=false  # NEVER true in production!
```

## Monitoring & Logging

### 1. Security Event Logging
```php
// Log security events
use Illuminate\Support\Facades\Log;

// Failed login
Log::warning('Failed login attempt', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);

// Successful login
Log::info('User logged in', [
    'user_id' => $user->id,
    'ip' => $request->ip(),
]);

// Suspicious activity
Log::alert('Suspicious activity detected', [
    'user_id' => $user->id,
    'action' => $action,
    'ip' => $request->ip(),
]);
```

### 2. Monitor Failed Login Attempts
```bash
# Check failed login attempts
grep "Failed login" /var/www/simple-store/storage/logs/laravel.log | tail -50

# Check for unusual patterns
tail -1000 /var/log/nginx/access.log | awk '{print $1}' | sort | uniq -c | sort -rn | head -20
```

### 3. Intrusion Detection
```bash
# Install fail2ban
sudo apt install fail2ban

# Configure untuk protect SSH, HTTP
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo nano /etc/fail2ban/jail.local

# Enable dan start
sudo systemctl enable fail2ban
sudo systemctl start fail2ban

# Check status
sudo fail2ban-client status
```

## Dependency Security

### 1. Regular Security Audits
```bash
# Check PHP dependencies
composer audit

# Check Node dependencies
yarn audit

# Fix vulnerabilities
composer update --with-dependencies
yarn upgrade
```

### 2. Keep Dependencies Updated
```bash
# Check outdated packages
composer outdated
yarn outdated

# Update regularly (monthly recommended)
composer update
yarn upgrade
```

## Incident Response

### 1. Security Incident Checklist

#### Immediate Actions
- [ ] Assess scope of breach
- [ ] Enable maintenance mode jika necessary
- [ ] Change all passwords dan API keys
- [ ] Review access logs
- [ ] Identify vulnerability
- [ ] Document everything

#### Investigation
- [ ] Check for unauthorized access
- [ ] Review database untuk data tampering
- [ ] Check file modifications
- [ ] Review server logs
- [ ] Identify point of entry

#### Remediation
- [ ] Patch vulnerability
- [ ] Remove malicious code/backdoors
- [ ] Restore from clean backup jika necessary
- [ ] Notify affected users (jika applicable)
- [ ] Report to authorities (jika required)

#### Post-Incident
- [ ] Conduct post-mortem
- [ ] Update security procedures
- [ ] Implement additional safeguards
- [ ] Train team on lessons learned

### 2. Data Breach Response
```bash
# 1. Isolate affected systems
sudo ufw deny from <suspicious_ip>

# 2. Preserve evidence
tar -czf evidence_$(date +%Y%m%d_%H%M%S).tar.gz /var/log /var/www/simple-store/storage/logs

# 3. Change all credentials immediately
# Database passwords
# API keys
# User passwords (force reset)

# 4. Scan for malware
sudo apt install clamav
sudo clamscan -r /var/www/simple-store
```

## Security Checklist

### Daily
- [ ] Monitor error logs untuk suspicious activity
- [ ] Check failed login attempts
- [ ] Verify backups successful

### Weekly
- [ ] Review access logs
- [ ] Check for security updates
- [ ] Audit admin actions

### Monthly
- [ ] Run security audit (composer audit, yarn audit)
- [ ] Update dependencies
- [ ] Review user permissions
- [ ] Check SSL certificate expiry
- [ ] Review security policies

### Quarterly
- [ ] Full security audit
- [ ] Penetration testing
- [ ] Review incident response procedures
- [ ] Security training untuk team

## Security Resources

### Tools
- **SSL Test**: https://www.ssllabs.com/ssltest/
- **Security Headers**: https://securityheaders.com/
- **Vulnerability Scanner**: OWASP ZAP, Nessus

### References
- OWASP Top 10: https://owasp.org/www-project-top-ten/
- Laravel Security: https://laravel.com/docs/security
- PHP Security: https://www.php.net/manual/en/security.php

## Contact

### Security Issues
**Report To**: Zulfikar Hidayatullah (+62 857-1583-8733)
**Email**: [security@your-domain.com]

### Emergency Response
**Primary Contact**: Zulfikar Hidayatullah
**Escalation**: [Manager/CTO]

## Compliance

### Data Privacy (GDPR-like considerations)
- [ ] User data collection documented
- [ ] Privacy policy up to date
- [ ] User consent obtained
- [ ] Right to deletion implemented
- [ ] Data retention policy enforced

### PCI DSS (jika handle payment data)
- [ ] Never store CVV
- [ ] Tokenize payment data
- [ ] Use certified payment gateway
- [ ] Regular security audits
- [ ] Access control implemented

## Document Updates
**Last Updated**: [Date]
**Next Review**: [Date]
**Maintained By**: Zulfikar Hidayatullah


