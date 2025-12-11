# Konfigurasi Domain & SSL

## Domain Information
- **Primary Domain**: [your-domain.com]
- **Subdomain**: [jika ada]
- **Registrar**: [Domain registrar]
- **DNS Provider**: [Cloudflare/etc]

## DNS Configuration

### A Records
```
@ (root)    →   [Server IP Address]
www         →   [Server IP Address]
```

### CNAME Records (jika ada)
```
[subdomain] →   [target]
```

### MX Records (untuk email)
```
[Email MX records jika ada]
```

### TXT Records (untuk verification)
```
[SPF, DKIM, verification records]
```

## SSL Certificate

### Certificate Provider
- **Provider**: [Let's Encrypt/Cloudflare/Commercial]
- **Type**: [Single/Wildcard/Multi-domain]
- **Validity**: [Expiry date]
- **Auto-Renewal**: [Yes/No]

### Let's Encrypt dengan Certbot
```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-apache
# atau untuk Nginx
sudo apt-get install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --apache -d your-domain.com -d www.your-domain.com
# atau untuk Nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### Manual Certificate Installation

#### Apache
```apache
<VirtualHost *:443>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    
    DocumentRoot /path/to/simple-store/public
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/chain.crt
    
    <Directory /path/to/simple-store/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    Redirect permanent / https://your-domain.com/
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    
    root /path/to/simple-store/public;
    index index.php;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # [Rest of nginx config...]
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

## SSL Testing
Setelah instalasi, test SSL configuration:
- **SSL Labs**: https://www.ssllabs.com/ssltest/
- **SSL Checker**: https://www.sslshopper.com/ssl-checker.html

## Cloudflare Setup (jika digunakan)

### DNS Settings
1. Point domain ke Cloudflare nameservers
2. Set DNS records di Cloudflare
3. Enable proxy (orange cloud) untuk CDN

### SSL/TLS Settings
- **SSL/TLS encryption mode**: Full (strict)
- **Always Use HTTPS**: On
- **HTTP Strict Transport Security (HSTS)**: Enable
- **Minimum TLS Version**: TLS 1.2

### Page Rules
```
URL: http://*your-domain.com/*
Setting: Always Use HTTPS
```

## WWW Redirect
Pilih salah satu (www atau non-www) sebagai canonical:

### Prefer non-www
```nginx
server {
    server_name www.your-domain.com;
    return 301 https://your-domain.com$request_uri;
}
```

### Prefer www
```nginx
server {
    server_name your-domain.com;
    return 301 https://www.your-domain.com$request_uri;
}
```

## Verification Checklist
- [ ] Domain pointing ke server IP
- [ ] SSL certificate terinstall
- [ ] HTTPS redirect berfungsi
- [ ] WWW redirect berfungsi
- [ ] Mixed content warnings tidak ada
- [ ] SSL Labs test: A rating
- [ ] Auto-renewal configured

## Renewal Reminders
- **Certificate Expiry**: [Date]
- **Domain Renewal**: [Date]
- **Renewal Procedure**: [Steps atau automation]


