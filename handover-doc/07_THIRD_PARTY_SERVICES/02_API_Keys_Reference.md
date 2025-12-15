# Referensi API Keys & Services

## Overview
Dokumen ini berisi daftar semua third-party services yang digunakan dalam aplikasi Simple Store, yaitu: service providers, purpose, dan referensi ke credentials.

⚠️ **PERINGATAN**: Dokumen ini TIDAK berisi API keys aktual. Lihat `10_CREDENTIALS_ACCESS/` untuk credentials lengkap.

## Third-Party Services List

### 1. WhatsApp Business API
**Provider**: [Provider Name]
**Purpose**: Customer notifications, order updates, customer support
**Documentation**: Lihat `01_WhatsApp_Integration.md`

**Required Keys**:
- Access Token
- Phone Number ID
- Business Account ID
- Webhook Verify Token

**Renewal**: [Date]
**Cost**: [Monthly/Per message]

---

### 2. Payment Gateway
**Provider**: [Midtrans/Xendit/etc]
**Purpose**: Payment processing

**Required Keys**:
- Client Key
- Server Key
- Merchant ID

**Environment**:
- Sandbox: [Sandbox URL]
- Production: [Production URL]

**Renewal**: [Contract period]
**Cost**: [Transaction fee]

---

### 3. Email Service (SMTP)
**Provider**: [Gmail/SendGrid/Mailgun/etc]
**Purpose**: Transactional emails, notifications

**Required Configuration**:
- SMTP Host
- SMTP Port
- Username
- Password/API Key

**Monthly Limit**: [Email limit]
**Cost**: [Cost]

---

### 4. Cloud Storage (Optional)
**Provider**: [AWS S3/Google Cloud/DigitalOcean Spaces]
**Purpose**: File storage, backups

**Required Keys**:
- Access Key ID
- Secret Access Key
- Bucket Name
- Region

**Storage Limit**: [Limit]
**Cost**: [Cost per GB]

---

### 5. Google Services (Optional)
**Purpose**: Analytics, Maps, reCAPTCHA, etc.

#### Google Analytics
- Tracking ID
- Measurement ID

#### Google Maps API
- API Key
- Enabled APIs: Maps JavaScript API, Geocoding API

#### Google reCAPTCHA
- Site Key
- Secret Key
- Version: v2/v3

**Cost**: Free tier/[Paid tier]

---

### 6. Social Media Integration (Optional)
**Purpose**: Social login, sharing

#### Facebook
- App ID
- App Secret
- OAuth Redirect URI

#### Google OAuth
- Client ID
- Client Secret
- OAuth Redirect URI

---

### 7. SMS Gateway (Optional)
**Provider**: [Twilio/Nexmo/local provider]
**Purpose**: OTP, SMS notifications

**Required Keys**:
- Account SID
- Auth Token
- Phone Number

**Cost**: [Per SMS cost]

---

### 8. Push Notifications (Optional)
**Provider**: [Firebase Cloud Messaging/OneSignal]
**Purpose**: Browser/mobile push notifications

**Required Keys**:
- Server Key
- Sender ID
- Project ID

---

### 9. CDN Service (Optional)
**Provider**: [Cloudflare/AWS CloudFront]
**Purpose**: Content delivery, DDoS protection

**Configuration**:
- Zone ID
- API Token
- DNS Settings

**Cost**: [Cost]

---

### 10. Monitoring & Error Tracking
**Provider**: [Sentry/Bugsnag/Raygun]
**Purpose**: Error tracking, performance monitoring

**Required Keys**:
- DSN
- Project ID
- API Token

**Monthly Events**: [Limit]

---

### 11. Shipping Integration (Optional)
**Provider**: [JNE/SiCepat/J&T/etc]
**Purpose**: Shipping rate calculation, tracking

**Required Keys**:
- API Key
- Origin Code

**Documentation**: [Provider docs]

---

## Environment Variable Summary

### Production (.env)
```env
# WhatsApp
WHATSAPP_API_URL=
WHATSAPP_ACCESS_TOKEN=
WHATSAPP_PHONE_NUMBER_ID=
WHATSAPP_BUSINESS_ACCOUNT_ID=
WHATSAPP_WEBHOOK_VERIFY_TOKEN=

# Payment Gateway
PAYMENT_CLIENT_KEY=
PAYMENT_SERVER_KEY=
PAYMENT_MERCHANT_ID=

# Email
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=

# Cloud Storage (if used)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=

# Google Services (if used)
GOOGLE_ANALYTICS_ID=
GOOGLE_MAPS_API_KEY=
RECAPTCHA_SITE_KEY=
RECAPTCHA_SECRET_KEY=

# Social Login (if used)
FACEBOOK_APP_ID=
FACEBOOK_APP_SECRET=
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=

# SMS Gateway (if used)
SMS_API_KEY=
SMS_SENDER_ID=

# Push Notifications (if used)
FCM_SERVER_KEY=
FCM_SENDER_ID=

# Error Tracking (if used)
SENTRY_DSN=

# Shipping (if used)
SHIPPING_API_KEY=
SHIPPING_ORIGIN_CODE=
```

## API Keys Management

### Security Best Practices
1. **Never commit** API keys ke repository
2. **Use .env file** untuk store credentials
3. **Different keys** untuk different environments
4. **Rotate keys** regularly
5. **Limit permissions** - only grant necessary access
6. **Monitor usage** untuk detect unauthorized access
7. **Revoke immediately** jika compromised

### Key Rotation Schedule
- **Critical Keys** (Payment, Database): Quarterly
- **Medium Risk** (Email, SMS): Semi-annually
- **Low Risk** (Analytics): Annually

### Access Control
**Who has access to production keys**:
1. [Name] - [Role] - [Access level]
2. [Name] - [Role] - [Access level]

### Where Keys Are Stored
- **Production Server**: `/var/www/simple-store/.env`
- **Backup**: [Secure location - see 10_CREDENTIALS_ACCESS]
- **Team Access**: [Password manager/vault]

## Testing Credentials

### Sandbox/Test Keys
Untuk development dan testing, gunakan sandbox credentials:

```env
# Test Environment
PAYMENT_SANDBOX_MODE=true
PAYMENT_SANDBOX_CLIENT_KEY=sandbox_key
PAYMENT_SANDBOX_SERVER_KEY=sandbox_key

WHATSAPP_TEST_NUMBER=test_number
WHATSAPP_SANDBOX_API=sandbox_url
```

## API Rate Limits

| Service | Rate Limit | Action When Exceeded |
|---------|------------|---------------------|
| WhatsApp | [X messages/min] | Queue messages |
| Payment | [X requests/min] | Retry with backoff |
| Email | [X emails/hour] | Queue emails |
| SMS | [X SMS/min] | Queue messages |
| Google Maps | [X requests/day] | Cache results |

## Cost Monitoring

### Monthly Budget
| Service | Estimated Cost | Actual Cost | Status |
|---------|---------------|-------------|---------|
| WhatsApp | Rp XXX | Rp XXX | ✓ |
| Payment | Rp XXX | Rp XXX | ✓ |
| Email | Rp XXX | Rp XXX | ✓ |
| Cloud Storage | Rp XXX | Rp XXX | ✓ |
| **Total** | **Rp XXX** | **Rp XXX** | - |

### Cost Alerts
Setup alerts untuk:
- Usage approaching limit
- Unexpected cost spikes
- Service failures

## Service Health Monitoring

### Status Pages
- WhatsApp: [Status page URL]
- Payment Gateway: [Status page URL]
- Email Service: [Status page URL]

### Monitoring Tools
[Tools yang digunakan untuk monitor service health]

## Troubleshooting

### Invalid API Key
```bash
# Verify environment variables loaded
php artisan tinker
config('services.whatsapp.token')

# Clear config cache
php artisan config:clear
php artisan config:cache
```

### Rate Limit Exceeded
```bash
# Check logs
tail -f storage/logs/laravel.log | grep "rate limit"

# Implement exponential backoff
# Review usage patterns
```

### Service Unavailable
```bash
# Check service status page
# Check network connectivity
ping api.service.com

# Check DNS resolution
nslookup api.service.com

# Review error logs
```

## Documentation Links

### Official Documentation
- WhatsApp Business API: https://developers.facebook.com/docs/whatsapp
- [Payment Gateway]: [URL]
- [Email Service]: [URL]
- [Other services]: [URL]

## Support Contacts

### Service Providers
- **WhatsApp Support**: [Contact]
- **Payment Gateway Support**: [Contact]
- **Email Service Support**: [Contact]

### Internal Contact
- **Developer**: Zulfikar Hidayatullah (+62 857-1583-8733)
- **Technical Lead**: [Name & Contact]

## Renewal Reminders

### Upcoming Renewals
- [Service Name]: [Renewal Date]
- [Service Name]: [Renewal Date]

### Renewal Process
1. Review usage dan requirements
2. Check for better pricing/alternatives
3. Renew atau migrate 30 days sebelum expiry
4. Update credentials jika changed
5. Test thoroughly after renewal

## Credentials Backup
⚠️ **CRITICAL**: Always maintain secure backup of all credentials

**Backup Location**: Lihat `10_CREDENTIALS_ACCESS/`

## Emergency Procedures
Jika API keys compromised:
1. **Immediately revoke** compromised keys
2. **Generate new keys**
3. **Update .env** files
4. **Clear config cache**: `php artisan config:clear && php artisan config:cache`
5. **Restart services**
6. **Monitor for unauthorized usage**
7. **Document incident**
8. **Review security procedures**



