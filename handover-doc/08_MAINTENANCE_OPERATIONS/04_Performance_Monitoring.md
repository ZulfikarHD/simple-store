# Performance Monitoring

## Overview
Panduan untuk monitoring dan optimasi performance aplikasi Simple Store, yaitu: key metrics, monitoring tools, dan optimization strategies.

## Key Performance Metrics

### 1. Application Metrics

#### Response Time
- **Target**: < 200ms (average)
- **Acceptable**: < 500ms
- **Poor**: > 1000ms

#### Throughput
- **Measure**: Requests per second (RPS)
- **Monitor**: Peak vs average traffic

#### Error Rate
- **Target**: < 0.1%
- **Alert**: > 1%
- **Critical**: > 5%

#### Availability
- **Target**: 99.9% uptime (< 43 minutes downtime/month)
- **Monitor**: 24/7

### 2. Server Metrics

#### CPU Usage
- **Normal**: < 70%
- **Warning**: 70-85%
- **Critical**: > 85%

```bash
# Check CPU usage
top
htop
# atau
mpstat 1 10  # 10 samples dengan 1 second interval
```

#### Memory Usage
- **Normal**: < 75%
- **Warning**: 75-90%
- **Critical**: > 90%

```bash
# Check memory
free -h
# atau detailed
vmstat 1 10
```

#### Disk I/O
- **Monitor**: Read/write speed
- **Alert**: High I/O wait time

```bash
# Check disk I/O
iostat -x 1 10
# atau
iotop
```

#### Disk Space
- **Warning**: > 80% full
- **Critical**: > 90% full

```bash
# Check disk space
df -h
# Check directory sizes
du -sh /var/www/simple-store/*
```

### 3. Database Metrics

#### Query Performance
- **Target**: < 100ms per query
- **Slow**: > 1000ms

```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;  -- Log queries > 1 second

-- Check slow queries
SELECT * FROM mysql.slow_log 
ORDER BY query_time DESC 
LIMIT 20;
```

#### Connection Pool
```sql
-- Check current connections
SHOW PROCESSLIST;

-- Check connection statistics
SHOW STATUS LIKE 'Threads%';
SHOW STATUS LIKE 'Connections%';
```

#### Cache Hit Rate
```sql
-- Check query cache (jika enabled)
SHOW STATUS LIKE 'Qcache%';
```

### 4. Frontend Metrics

#### Page Load Time
- **Target**: < 2 seconds
- **Acceptable**: < 3 seconds
- **Poor**: > 5 seconds

#### First Contentful Paint (FCP)
- **Good**: < 1.8s
- **Needs Improvement**: 1.8s - 3s
- **Poor**: > 3s

#### Largest Contentful Paint (LCP)
- **Good**: < 2.5s
- **Needs Improvement**: 2.5s - 4s
- **Poor**: > 4s

#### Cumulative Layout Shift (CLS)
- **Good**: < 0.1
- **Needs Improvement**: 0.1 - 0.25
- **Poor**: > 0.25

## Monitoring Tools

### 1. Built-in Laravel Tools

#### Laravel Telescope (Development)
```bash
# Install telescope (development only!)
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate

# Access: https://your-domain.com/telescope
```

#### Laravel Debugbar (Development)
```bash
# Install debugbar
composer require barryvdh/laravel-debugbar --dev

# Access: Bottom of page during development
```

#### Query Logging
```php
// config/database.php or .env
'connections' => [
    'mysql' => [
        // ...
        'options' => [
            PDO::ATTR_EMULATE_PREPARES => true
        ],
    ],
],

// Enable query log
DB::enableQueryLog();

// Get queries
$queries = DB::getQueryLog();
dd($queries);
```

### 2. Server Monitoring

#### Basic Monitoring Script
```bash
#!/bin/bash
# Simple monitoring script

echo "=== Server Health Check ==="
echo "Date: $(date)"
echo ""

echo "CPU Usage:"
top -bn1 | grep "Cpu(s)" | awk '{print $2}' | awk -F'%' '{print $1"%"}'
echo ""

echo "Memory Usage:"
free -h | grep Mem | awk '{print $3 "/" $2}'
echo ""

echo "Disk Usage:"
df -h / | tail -1 | awk '{print $3 "/" $2 " (" $5 " used)"}'
echo ""

echo "PHP-FPM Status:"
systemctl is-active php8.4-fpm
echo ""

echo "Nginx Status:"
systemctl is-active nginx
echo ""

echo "MySQL Status:"
systemctl is-active mysql
echo ""

echo "Recent Errors (last 10):"
tail -10 /var/www/simple-store/storage/logs/laravel.log | grep ERROR
```

### 3. Application Performance Monitoring (APM)

Recommended APM tools (pilih salah satu):

#### New Relic
- Full-stack monitoring
- Real-time analytics
- Error tracking

#### Datadog
- Infrastructure monitoring
- APM
- Log management

#### Scout APM (Laravel-specific)
```bash
# Install Scout APM
composer require scoutapp/scout-apm-laravel
php artisan vendor:publish --provider="Scoutapp\ScoutApmAgent\Providers\ScoutApmServiceProvider"

# Configure
# .env
SCOUT_MONITOR=true
SCOUT_KEY=your_key
SCOUT_NAME="Simple Store"
```

## Performance Optimization

### 1. Database Optimization

#### Add Missing Indexes
```sql
-- Identify slow queries first
EXPLAIN SELECT * FROM orders WHERE user_id = 1 AND status = 'pending';

-- Add indexes pada frequently queried columns
ALTER TABLE orders ADD INDEX idx_user_status (user_id, status);
ALTER TABLE products ADD INDEX idx_category_active (category_id, is_active);
```

#### Optimize Eloquent Queries
```php
// Bad: N+1 Query Problem
$orders = Order::all();
foreach ($orders as $order) {
    echo $order->user->name;  // Query untuk setiap order!
}

// Good: Eager Loading
$orders = Order::with('user')->get();
foreach ($orders as $order) {
    echo $order->user->name;  // No additional queries
}

// Better: Select only needed columns
$orders = Order::with('user:id,name')->select('id', 'user_id', 'total')->get();
```

#### Use Query Caching
```php
// Cache query results
$popularProducts = Cache::remember('popular_products', 3600, function () {
    return Product::where('is_active', true)
        ->orderBy('views', 'desc')
        ->limit(10)
        ->get();
});
```

### 2. Application Caching

#### Config Caching
```bash
# Cache configuration (production)
php artisan config:cache

# Clear when needed
php artisan config:clear
```

#### Route Caching
```bash
# Cache routes (production)
php artisan route:cache

# Clear when needed
php artisan route:clear
```

#### View Caching
```bash
# Cache views (production)
php artisan view:cache

# Clear when needed
php artisan view:clear
```

#### Application Caching
```php
// Use cache untuk expensive operations
use Illuminate\Support\Facades\Cache;

// Cache for 1 hour
$stats = Cache::remember('dashboard_stats', 3600, function () {
    return [
        'total_orders' => Order::count(),
        'total_revenue' => Order::sum('total'),
        'active_users' => User::where('last_login_at', '>', now()->subDays(30))->count(),
    ];
});

// Cache tags untuk granular clearing
Cache::tags(['users', 'admin'])->put('stats', $stats, 3600);
Cache::tags(['users'])->flush();
```

### 3. Asset Optimization

#### Vite Build Optimization
```javascript
// vite.config.js
export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['vue', '@inertiajs/vue3'],
                },
            },
        },
        chunkSizeWarningLimit: 1000,
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,  // Remove console.log di production
            },
        },
    },
});
```

#### Image Optimization
```bash
# Install image optimization tools
sudo apt install jpegoptim optipng pngquant gifsicle

# Optimize images
find /var/www/simple-store/public/images -name "*.jpg" -exec jpegoptim --strip-all --max=85 {} \;
find /var/www/simple-store/public/images -name "*.png" -exec optipng -o5 {} \;
```

#### Enable Compression (Nginx)
```nginx
# Enable gzip compression
gzip on;
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;
gzip_disable "msie6";
```

### 4. CDN Integration

#### Cloudflare Setup
1. Point DNS ke Cloudflare
2. Enable proxy (orange cloud)
3. Configure caching rules
4. Enable minification (JS, CSS, HTML)
5. Enable Brotli compression
6. Configure page rules

### 5. PHP-FPM Optimization

```ini
; /etc/php/8.4/fpm/pool.d/www.conf

; Process manager settings
pm = dynamic
pm.max_children = 50          ; Maximum processes
pm.start_servers = 10         ; Start with these many
pm.min_spare_servers = 5      ; Minimum idle
pm.max_spare_servers = 15     ; Maximum idle
pm.max_requests = 500         ; Recycle after this many requests

; Performance tuning
request_terminate_timeout = 30
rlimit_files = 65536
```

## Performance Testing

### 1. Load Testing

#### Using Apache Bench (ab)
```bash
# Install
sudo apt install apache2-utils

# Test dengan 1000 requests, 10 concurrent
ab -n 1000 -c 10 https://your-domain.com/

# With authentication
ab -n 1000 -c 10 -C "session=cookie_value" https://your-domain.com/dashboard
```

#### Using wrk
```bash
# Install
sudo apt install wrk

# Test dengan 10 connections selama 30 seconds
wrk -t10 -c10 -d30s https://your-domain.com/

# More aggressive test
wrk -t12 -c400 -d30s https://your-domain.com/
```

### 2. Database Performance Testing

#### Analyze Query Performance
```sql
-- Enable profiling
SET profiling = 1;

-- Run query
SELECT * FROM orders WHERE user_id = 1;

-- Show profile
SHOW PROFILES;
SHOW PROFILE FOR QUERY 1;
```

#### Check Index Usage
```sql
-- Check if indexes being used
EXPLAIN SELECT * FROM orders WHERE user_id = 1 AND status = 'pending';

-- Look for:
-- type: Should be 'ref' atau better, NOT 'ALL'
-- key: Should show index name, NOT NULL
-- rows: Should be low number
```

## Performance Monitoring Dashboard

### Key Metrics to Display
1. **Response Time** (average, p95, p99)
2. **Error Rate** (4xx, 5xx)
3. **Request Rate** (requests/second)
4. **Database Performance** (query time, slow queries)
5. **Server Resources** (CPU, Memory, Disk)
6. **Cache Hit Rate**
7. **Queue Status** (pending, failed jobs)

### Sample Monitoring Dashboard Script
```bash
#!/bin/bash
# Performance Dashboard

echo "╔════════════════════════════════════════════╗"
echo "║   Simple Store Performance Dashboard        ║"
echo "╚════════════════════════════════════════════╝"
echo ""

# Server Resources
echo "📊 Server Resources"
echo "CPU: $(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | awk -F'%' '{print $1}')%"
echo "Memory: $(free | grep Mem | awk '{printf "%.1f%%", $3/$2 * 100.0}')"
echo "Disk: $(df -h / | tail -1 | awk '{print $5}')"
echo ""

# Application Health
echo "🏥 Application Health"
echo "Nginx: $(systemctl is-active nginx)"
echo "PHP-FPM: $(systemctl is-active php8.4-fpm)"
echo "MySQL: $(systemctl is-active mysql)"
echo ""

# Recent Errors
echo "⚠️  Recent Errors (last hour)"
ERROR_COUNT=$(grep -c "ERROR" /var/www/simple-store/storage/logs/laravel-$(date +%Y-%m-%d).log 2>/dev/null || echo "0")
echo "Error Count: $ERROR_COUNT"
echo ""

# Response Time (last 100 requests dari access log)
echo "⚡ Response Time"
tail -100 /var/log/nginx/access.log | awk '{print $10}' | awk '{sum+=$1; count++} END {if(count>0) printf "Average: %.2f seconds\n", sum/count}'
echo ""

# Database
echo "💾 Database"
CONN_COUNT=$(mysql -u root -p -se "SHOW STATUS LIKE 'Threads_connected';" | awk '{print $2}')
echo "Active Connections: $CONN_COUNT"
```

## Alert Configuration

### Critical Alerts (Immediate Response)
- Server down
- Database connection failure
- Error rate > 5%
- Disk space > 95%
- Memory usage > 95%

### Warning Alerts (Monitor Closely)
- Error rate > 1%
- Response time > 1000ms
- Disk space > 85%
- Memory usage > 85%
- CPU usage > 85%

### Informational Alerts
- Deployment completed
- Backup completed
- Certificate expiry < 30 days

## Regular Performance Review

### Weekly Review
- [ ] Check average response times
- [ ] Review error logs
- [ ] Identify slow queries
- [ ] Monitor resource trends

### Monthly Review
- [ ] Full performance audit
- [ ] Load testing
- [ ] Optimization opportunities
- [ ] Capacity planning

## Contact
**Developer**: Zulfikar Hidayatullah (+62 857-1583-8733)

## Document Updates
**Last Updated**: [Date]
**Next Review**: [Date]
**Maintained By**: Zulfikar Hidayatullah


