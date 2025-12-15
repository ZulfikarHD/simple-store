# Konfigurasi Hosting

**Penulis**: Zulfikar Hidayatullah  
**Terakhir Diperbarui**: Desember 2024

## Hosting Provider

| Item | Value |
|------|-------|
| **Provider** | Hostinger |
| **Plan** | [Plan name - Business/Premium/etc] |
| **Account** | Lihat `10_CREDENTIALS_ACCESS/02_Hostinger_Account_Details.md` |
| **Deployment Guide** | [05_Hostinger_Shared_Hosting_Guide.md](./05_Hostinger_Shared_Hosting_Guide.md) |

## Hostinger Shared Hosting Specifics

Hostinger shared hosting memiliki karakteristik khusus yang perlu diperhatikan, antara lain:

### Keterbatasan
- **Tidak ada npm/yarn** - Build assets harus dilakukan di lokal
- **Tidak ada sudo access** - Tidak bisa install package tambahan
- **Tidak ada supervisor** - Queue harus menggunakan sync driver
- **SSH access terbatas** - Basic commands only

### Struktur Folder Hostinger
```
/home/u123456789/
├── domains/
│   ├── yourdomain.com/
│   │   └── public_html/          # Document root
│   └── subdomain.yourdomain.com/
│       └── public_html/          # Subdomain document root
├── logs/
└── ssl/
```

### Symlink Setup untuk Laravel
```bash
# Di folder subdomain
cd domains/subdomain.yourdomain.com/
git clone [repository] simple-store
mv public_html public_html_backup
ln -s simple-store/public public_html
```

## Server Specifications

### Minimum Requirements
- **PHP**: 8.4 atau lebih tinggi
- **Database**: MySQL 8.0+ atau PostgreSQL 13+
- **Web Server**: Apache/Nginx
- **RAM**: 2GB minimum
- **Storage**: 20GB minimum
- **SSL**: Required

### Current Server
- **OS**: [Operating System]
- **PHP Version**: 8.4.1
- **Database**: [MySQL/PostgreSQL version]
- **Web Server**: [Apache/Nginx version]
- **RAM**: [Actual RAM]
- **Storage**: [Actual storage]

## PHP Configuration

### Required Extensions
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- cURL

### PHP Settings
```ini
memory_limit = 256M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
```

## Web Server Configuration

### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### Nginx (nginx.conf)
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/simple-store/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Directory Permissions
```bash
chmod -R 755 /path/to/simple-store
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data /path/to/simple-store
```

## Cron Jobs
Setup cron untuk Laravel scheduler:
```bash
* * * * * cd /path/to/simple-store && php artisan schedule:run >> /dev/null 2>&1
```

## Queue Worker
Setup supervisor atau systemd untuk queue worker:
```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

## Storage Link
Buat symlink untuk storage:
```bash
php artisan storage:link
```

## Security Headers
[Konfigurasi security headers di web server]

## Monitoring
- **Uptime Monitoring**: [Service yang digunakan]
- **Error Tracking**: [Sentry/Bugsnag/etc jika digunakan]
- **Performance Monitoring**: [Service yang digunakan]

## Panduan Terkait

- [05_Hostinger_Shared_Hosting_Guide.md](./05_Hostinger_Shared_Hosting_Guide.md) - Panduan lengkap deployment Hostinger
- [02_Environment_Setup.md](./02_Environment_Setup.md) - Konfigurasi environment variables
- [03_Domain_SSL_Configuration.md](./03_Domain_SSL_Configuration.md) - Setup domain dan SSL
- [04_Initial_Deployment_Guide.md](./04_Initial_Deployment_Guide.md) - Panduan deployment VPS/Cloud



