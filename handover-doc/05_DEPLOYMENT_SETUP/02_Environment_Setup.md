# Setup Environment

## Environment Variables (.env)

### Application
```env
APP_NAME="Simple Store"
APP_ENV=production
APP_KEY=base64:GENERATED_KEY
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://your-domain.com
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
```

### Database
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simple_store
DB_USERNAME=root
DB_PASSWORD=
```
**Note**: Lihat `10_CREDENTIALS_ACCESS/03_Database_Credentials.md` untuk kredensial aktual.

### Cache & Session
```env
CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### Queue
```env
QUEUE_CONNECTION=sync
```

### Mail
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Third-Party Services
```env
# Lihat 07_THIRD_PARTY_SERVICES untuk detail
```

## Setup Steps

### 1. Clone Repository
```bash
git clone [repository-url] simple-store
cd simple-store
```

### 2. Install Dependencies
```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# Node dependencies
yarn install
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```bash
# Run migrations
php artisan migrate --force

# Run seeders (jika diperlukan)
php artisan db:seed --force
```

### 5. Build Frontend Assets
```bash
yarn run build
```

### 6. Optimize Application
```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoload
composer dump-autoload --optimize
```

### 7. Storage Link
```bash
php artisan storage:link
```

### 8. File Permissions
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## Development Environment

### Local Development dengan Sail
```bash
# Start Sail
./vendor/bin/sail up -d

# Stop Sail
./vendor/bin/sail down
```

### Local Development tanpa Sail
```bash
# Start dev server
composer run dev
# atau
php artisan serve & yarn run dev
```

## Environment-Specific Settings

### Development
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

### Staging
```env
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.your-domain.com
```

### Production
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

## Troubleshooting
Lihat: `09_TROUBLESHOOTING/01_Common_Issues_Solutions.md`


