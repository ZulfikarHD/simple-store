# Panduan Deployment Awal

**Penulis**: Zulfikar Hidayatullah  
**Terakhir Diperbarui**: Desember 2024

## Jenis Deployment

Dokumen ini mencakup deployment untuk **VPS/Cloud Server** dengan akses root. Untuk jenis hosting lainnya, lihat panduan spesifik:

| Hosting Type | Panduan |
|--------------|---------|
| VPS/Cloud (root access) | Dokumen ini |
| Hostinger Shared Hosting | [05_Hostinger_Shared_Hosting_Guide.md](./05_Hostinger_Shared_Hosting_Guide.md) |

## Pre-Deployment Checklist
- [ ] Server memenuhi minimum requirements
- [ ] Domain dan SSL sudah dikonfigurasi
- [ ] Database sudah dibuat
- [ ] Environment variables sudah disiapkan
- [ ] Backup strategy sudah disetup
- [ ] Monitoring tools sudah disetup (jika ada)

## Deployment Steps

### 1. Server Preparation
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y php8.4 php8.4-cli php8.4-fpm php8.4-mysql \
    php8.4-mbstring php8.4-xml php8.4-bcmath php8.4-curl \
    composer nginx mysql-server git

# Install Node.js & Yarn
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
npm install -g yarn
```

### 2. Create Database
```bash
# Login ke MySQL
sudo mysql -u root -p

# Create database dan user
CREATE DATABASE simple_store;
CREATE USER 'simple_store_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON simple_store.* TO 'simple_store_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Clone & Setup Application
```bash
# Navigate to web directory
cd /var/www

# Clone repository
sudo git clone [repository-url] simple-store
cd simple-store

# Set ownership
sudo chown -R www-data:www-data /var/www/simple-store

# Copy environment file
sudo -u www-data cp .env.example .env

# Edit .env dengan kredensial yang benar
sudo -u www-data nano .env
```

### 4. Install Dependencies
```bash
# Install PHP dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader

# Install Node dependencies
sudo -u www-data yarn install

# Generate app key
sudo -u www-data php artisan key:generate
```

### 5. Database Migration
```bash
# Run migrations
sudo -u www-data php artisan migrate --force

# Run seeders (jika diperlukan)
sudo -u www-data php artisan db:seed --force
```

### 6. Build Assets
```bash
# Build production assets
sudo -u www-data yarn run build
```

### 7. Optimize Application
```bash
# Cache configuration
sudo -u www-data php artisan config:cache

# Cache routes
sudo -u www-data php artisan route:cache

# Cache views
sudo -u www-data php artisan view:cache

# Create storage link
sudo -u www-data php artisan storage:link
```

### 8. Set Permissions
```bash
# Set directory permissions
sudo chmod -R 755 /var/www/simple-store
sudo chmod -R 775 /var/www/simple-store/storage
sudo chmod -R 775 /var/www/simple-store/bootstrap/cache
```

### 9. Configure Web Server
Lihat: `05_DEPLOYMENT_SETUP/01_Hosting_Configuration.md` untuk konfigurasi Nginx/Apache

### 10. Setup Cron & Queue Worker
```bash
# Edit crontab
sudo crontab -e -u www-data

# Add Laravel scheduler
* * * * * cd /var/www/simple-store && php artisan schedule:run >> /dev/null 2>&1
```

### 11. Restart Services
```bash
# Restart PHP-FPM
sudo systemctl restart php8.4-fpm

# Restart Nginx
sudo systemctl restart nginx

# Restart MySQL (jika diperlukan)
sudo systemctl restart mysql
```

## Post-Deployment Verification

### 1. Check Website Access
- [ ] Website accessible via HTTPS
- [ ] No SSL warnings
- [ ] Homepage loads correctly
- [ ] Assets (CSS/JS) loads properly

### 2. Test Core Features
- [ ] User registration works
- [ ] User login works
- [ ] Database operations work
- [ ] File uploads work (jika ada)
- [ ] Email sending works (jika ada)

### 3. Check Error Logs
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Nginx error logs
sudo tail -f /var/log/nginx/error.log

# PHP-FPM logs
sudo tail -f /var/log/php8.4-fpm.log
```

### 4. Performance Check
- [ ] Page load time < 3 seconds
- [ ] No N+1 query issues
- [ ] Assets properly minified

## Rollback Plan
Jika deployment gagal:

```bash
# 1. Restore previous version (jika ada)
git checkout [previous-commit-hash]

# 2. Restore database backup
mysql -u root -p simple_store < backup.sql

# 3. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Rebuild assets
yarn run build
```

## Initial Admin Account
```bash
# Create admin user via tinker
php artisan tinker

# Di dalam tinker:
$user = new App\Models\User();
$user->name = 'Administrator';
$user->email = 'admin@your-domain.com';
$user->password = Hash::make('secure_password');
$user->save();
```

## Security Hardening

### Disable Directory Listing
Di nginx config, pastikan `autoindex off;`

### Hide Server Information
```nginx
# Di nginx.conf
server_tokens off;
```

### Setup Firewall
```bash
# UFW setup
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### Secure .env File
```bash
chmod 600 .env
```

## Monitoring Setup
[Setup monitoring tools seperti Uptime Robot, etc]

## Backup Schedule
Lihat: `06_DATABASE/02_Backup_Procedures.md`

## Support Contacts

- **Developer**: Zulfikar Hidayatullah (+62 857-1583-8733)
- Lihat juga: `09_TROUBLESHOOTING/03_Emergency_Contacts.md`

## Panduan Terkait

- [05_Hostinger_Shared_Hosting_Guide.md](./05_Hostinger_Shared_Hosting_Guide.md) - Untuk deployment ke Hostinger shared hosting
- [01_Hosting_Configuration.md](./01_Hosting_Configuration.md) - Konfigurasi web server
- [02_Environment_Setup.md](./02_Environment_Setup.md) - Setup environment variables
- [03_Domain_SSL_Configuration.md](./03_Domain_SSL_Configuration.md) - Konfigurasi domain dan SSL

## Deployment Completed!
- **Deployment Date**: [Date]
- **Deployed By**: [Name]
- **Version**: [Version/Commit]
- **Status**: [Success/Issues]



