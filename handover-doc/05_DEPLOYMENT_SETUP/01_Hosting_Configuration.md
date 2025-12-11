# Konfigurasi Hosting

## Hosting Provider
**Provider**: [Hostinger/VPS/Cloud]
**Plan**: [Plan name]
**Account**: Lihat `10_CREDENTIALS_ACCESS/02_Hostinger_Account_Details.md`

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


