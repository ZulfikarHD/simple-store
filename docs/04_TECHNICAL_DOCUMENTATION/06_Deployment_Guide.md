# Panduan Deployment Simple Store

**Penulis**: Zulfikar Hidayatullah  
**Versi**: 1.0  
**Terakhir Diperbarui**: Desember 2025

---

## Overview

Panduan lengkap untuk deployment aplikasi Simple Store ke environment production, yaitu: persiapan server, konfigurasi, langkah-langkah deployment, dan maintenance procedures.

---

## System Requirements

### Minimum Server Requirements

| Component | Requirement |
|-----------|-------------|
| **OS** | Ubuntu 22.04 LTS / Debian 11+ |
| **PHP** | 8.4+ dengan extensions |
| **Web Server** | Nginx 1.18+ atau Apache 2.4+ |
| **Node.js** | 18+ (untuk build assets) |
| **RAM** | Minimal 1GB (recommended 2GB+) |
| **Storage** | 10GB+ (tergantung jumlah produk/images) |

### PHP Extensions Required

```bash
php -m | grep -E "bcmath|ctype|fileinfo|json|mbstring|openssl|pdo|tokenizer|xml|curl|gd|zip|sqlite3"
```

Extensions yang diperlukan:
- `bcmath`
- `ctype`
- `curl`
- `dom`
- `fileinfo`
- `gd` (untuk image processing)
- `json`
- `mbstring`
- `openssl`
- `pdo_sqlite` (atau `pdo_mysql` jika menggunakan MySQL)
- `tokenizer`
- `xml`
- `zip`

---

## Pre-Deployment Checklist

### Code Preparation

- [ ] Semua tests passing (`php artisan test`)
- [ ] Code sudah di-lint (`vendor/bin/pint`)
- [ ] Assets sudah di-build (`yarn build`)
- [ ] `.env.example` updated dengan semua required variables
- [ ] Migrations sudah di-review dan tested
- [ ] Tidak ada debug code atau hardcoded credentials

### Server Preparation

- [ ] SSH access configured
- [ ] Firewall configured (UFW)
- [ ] SSL certificate ready (Let's Encrypt)
- [ ] Domain DNS configured
- [ ] PHP dan extensions installed
- [ ] Nginx/Apache installed
- [ ] Composer installed
- [ ] Node.js installed (untuk build)

---

## Deployment Methods

### Method 1: Manual Deployment

#### Step 1: Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx php8.4-fpm php8.4-cli php8.4-common \
    php8.4-mysql php8.4-sqlite3 php8.4-xml php8.4-curl php8.4-gd \
    php8.4-mbstring php8.4-zip php8.4-bcmath php8.4-intl \
    git unzip curl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js (via NodeSource)
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Yarn
npm install -g yarn
```

#### Step 2: Clone Repository

```bash
# Navigate to web directory
cd /var/www

# Clone repository
git clone https://github.com/your-username/simple-store.git
cd simple-store

# Set ownership
sudo chown -R www-data:www-data /var/www/simple-store
sudo chmod -R 755 /var/www/simple-store

# Make storage writable
sudo chmod -R 775 storage bootstrap/cache
```

#### Step 3: Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies dan build assets
yarn install
yarn build
```

#### Step 4: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Edit environment variables
nano .env
```

**Production `.env` Configuration**:

```env
APP_NAME="Simple Store"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/simple-store/database/database.sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=file
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### Step 5: Database Setup

```bash
# Create SQLite database file
touch database/database.sqlite

# Run migrations
php artisan migrate --force

# Seed with production data
php artisan db:seed --class=ProductionSeeder --force

# Create storage symlink
php artisan storage:link
```

#### Step 6: Optimize Application

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

#### Step 7: Configure Nginx

```nginx
# /etc/nginx/sites-available/simple-store
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    root /var/www/simple-store/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript 
               application/rss+xml application/atom+xml image/svg+xml;

    # Logging
    access_log /var/log/nginx/simple-store-access.log;
    error_log /var/log/nginx/simple-store-error.log;

    # Max upload size
    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static files caching
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ /\.env {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/simple-store /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

#### Step 8: Setup SSL with Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal (already configured by Certbot)
sudo certbot renew --dry-run
```

#### Step 9: Configure Queue Worker (Optional)

```bash
# Create systemd service
sudo nano /etc/systemd/system/simple-store-worker.service
```

```ini
[Unit]
Description=Simple Store Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
WorkingDirectory=/var/www/simple-store
ExecStart=/usr/bin/php /var/www/simple-store/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start service
sudo systemctl enable simple-store-worker
sudo systemctl start simple-store-worker
```

---

### Method 2: Docker Deployment

#### Docker Compose Configuration

```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: simple-store-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
      - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    networks:
      - simple-store

  nginx:
    image: nginx:alpine
    container_name: simple-store-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
      - ./docker/nginx/ssl:/etc/nginx/ssl
    networks:
      - simple-store

networks:
  simple-store:
    driver: bridge
```

#### Dockerfile

```dockerfile
FROM php:8.4-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_sqlite mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application
COPY . /var/www

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Change ownership
RUN chown -R www-data:www-data /var/www

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
```

#### Deploy with Docker

```bash
# Build and start containers
docker-compose up -d --build

# Run migrations
docker-compose exec app php artisan migrate --force

# Seed database
docker-compose exec app php artisan db:seed --class=ProductionSeeder --force
```

---

## Post-Deployment Tasks

### 1. Create Admin User

```bash
# Via tinker
php artisan tinker

>>> User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('secure-password'),
    'role' => 'admin',
    'email_verified_at' => now()
]);
```

Atau gunakan seeder:

```bash
php artisan db:seed --class=UserSeeder --force
```

### 2. Setup Store Settings

Login sebagai admin dan konfigurasi di `/admin/settings`:
- Nama toko
- Alamat
- Nomor WhatsApp
- Biaya pengiriman
- Logo toko

### 3. Verify Application

```bash
# Test routes
curl -I https://yourdomain.com
curl -I https://yourdomain.com/login

# Check logs
tail -f storage/logs/laravel.log

# Check PHP-FPM status
systemctl status php8.4-fpm
```

---

## Update/Deployment Workflow

### Git-based Updates

```bash
# Navigate to project
cd /var/www/simple-store

# Enable maintenance mode
php artisan down

# Pull latest changes
git pull origin main

# Install dependencies (jika ada perubahan)
composer install --optimize-autoloader --no-dev
yarn install
yarn build

# Run migrations
php artisan migrate --force

# Clear and rebuild cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart queue worker (jika ada)
sudo systemctl restart simple-store-worker

# Disable maintenance mode
php artisan up
```

### Zero-Downtime Deployment Script

```bash
#!/bin/bash
# deploy.sh

set -e

DEPLOY_PATH="/var/www/simple-store"
RELEASES_PATH="${DEPLOY_PATH}/releases"
CURRENT_PATH="${DEPLOY_PATH}/current"
SHARED_PATH="${DEPLOY_PATH}/shared"
RELEASE=$(date +%Y%m%d%H%M%S)
NEW_RELEASE_PATH="${RELEASES_PATH}/${RELEASE}"

# Create new release directory
mkdir -p ${NEW_RELEASE_PATH}

# Clone repository
git clone --depth 1 git@github.com:your-username/simple-store.git ${NEW_RELEASE_PATH}

# Install dependencies
cd ${NEW_RELEASE_PATH}
composer install --optimize-autoloader --no-dev
yarn install
yarn build

# Link shared resources
ln -nfs ${SHARED_PATH}/.env ${NEW_RELEASE_PATH}/.env
ln -nfs ${SHARED_PATH}/storage ${NEW_RELEASE_PATH}/storage
ln -nfs ${SHARED_PATH}/database/database.sqlite ${NEW_RELEASE_PATH}/database/database.sqlite

# Run migrations
php artisan migrate --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Update symlink
ln -nfs ${NEW_RELEASE_PATH} ${CURRENT_PATH}

# Restart services
sudo systemctl reload php8.4-fpm
sudo systemctl restart simple-store-worker

# Cleanup old releases (keep last 5)
cd ${RELEASES_PATH}
ls -1t | tail -n +6 | xargs -d '\n' rm -rf --

echo "Deployed ${RELEASE}"
```

---

## Backup Procedures

### Database Backup

```bash
# SQLite backup
cp /var/www/simple-store/database/database.sqlite \
   /var/backups/simple-store/database-$(date +%Y%m%d).sqlite

# Automated backup with cron
0 2 * * * cp /var/www/simple-store/database/database.sqlite /var/backups/simple-store/database-$(date +\%Y\%m\%d).sqlite
```

### Storage Backup

```bash
# Backup uploaded files
tar -czf /var/backups/simple-store/storage-$(date +%Y%m%d).tar.gz \
    /var/www/simple-store/storage/app/public
```

### Full Backup Script

```bash
#!/bin/bash
# backup.sh

BACKUP_DIR="/var/backups/simple-store"
DATE=$(date +%Y%m%d-%H%M%S)
PROJECT_DIR="/var/www/simple-store"

mkdir -p ${BACKUP_DIR}

# Database
cp ${PROJECT_DIR}/database/database.sqlite ${BACKUP_DIR}/db-${DATE}.sqlite

# Storage
tar -czf ${BACKUP_DIR}/storage-${DATE}.tar.gz ${PROJECT_DIR}/storage/app/public

# Environment (encrypted)
gpg --symmetric --cipher-algo AES256 -o ${BACKUP_DIR}/env-${DATE}.gpg ${PROJECT_DIR}/.env

# Upload to remote (optional)
# aws s3 cp ${BACKUP_DIR}/ s3://your-bucket/backups/ --recursive

# Cleanup old backups (keep 7 days)
find ${BACKUP_DIR} -type f -mtime +7 -delete

echo "Backup completed: ${DATE}"
```

---

## Monitoring

### Application Health Check

```bash
# Create health check endpoint
# routes/web.php sudah include /up endpoint

# Monitor with cron
*/5 * * * * curl -sf https://yourdomain.com/up || echo "Site down!" | mail -s "Alert" admin@example.com
```

### Log Rotation

```bash
# /etc/logrotate.d/simple-store
/var/www/simple-store/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}
```

### Performance Monitoring (Optional)

- **Laravel Telescope**: Development/staging debugging
- **Laravel Horizon**: Queue monitoring
- **External**: UptimeRobot, Pingdom untuk uptime monitoring

---

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| 500 Error | Check `storage/logs/laravel.log` |
| 403 Forbidden | Check file permissions (`chmod -R 755`) |
| Assets not loading | Run `yarn build` dan `php artisan storage:link` |
| Session not persisting | Check session driver dan permissions |
| Slow performance | Enable caching (`php artisan config:cache`) |

### Debug Mode

```bash
# Temporarily enable debug mode (JANGAN di production lama-lama)
# Edit .env
APP_DEBUG=true

# Setelah debug selesai
APP_DEBUG=false
php artisan config:cache
```

### Clear All Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload
```

---

## Security Hardening

### File Permissions

```bash
# Set correct permissions
find /var/www/simple-store -type f -exec chmod 644 {} \;
find /var/www/simple-store -type d -exec chmod 755 {} \;

# Writable directories
chmod -R 775 storage bootstrap/cache

# Ownership
chown -R www-data:www-data /var/www/simple-store
```

### Firewall (UFW)

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### Fail2Ban (SSH Protection)

```bash
sudo apt install fail2ban
sudo systemctl enable fail2ban
```

---

## Environment-Specific Configuration

### Development

```env
APP_ENV=local
APP_DEBUG=true
```

### Staging

```env
APP_ENV=staging
APP_DEBUG=true
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

---

## Related Documentation

- [01_System_Architecture.md](01_System_Architecture.md) - Arsitektur sistem
- [05_Security_Documentation.md](05_Security_Documentation.md) - Security details
- [07_Testing_Documentation.md](07_Testing_Documentation.md) - Testing strategy

