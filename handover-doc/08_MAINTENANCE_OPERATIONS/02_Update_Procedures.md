# Prosedur Update

## Overview
Panduan lengkap untuk melakukan update aplikasi Simple Store, yaitu: Laravel updates, dependency updates, dan system updates dengan aman.

## Types of Updates

### 1. Security Updates (Priority: CRITICAL)
- Apply as soon as possible
- Minimal testing required
- Can be done during business hours dengan proper backup

### 2. Bug Fix Updates (Priority: HIGH)
- Apply after testing di staging
- Schedule during low-traffic hours
- Full backup required

### 3. Feature Updates (Priority: MEDIUM)
- Thorough testing required
- Schedule maintenance window
- User notification needed

### 4. Minor Updates (Priority: LOW)
- Can wait untuk scheduled maintenance
- Bundle multiple updates
- Full testing cycle

## Pre-Update Checklist

### Essential Steps
- [ ] **Backup database** - See `06_DATABASE/02_Backup_Procedures.md`
- [ ] **Backup application files**
- [ ] **Document current version**
- [ ] **Review update changelog**
- [ ] **Check for breaking changes**
- [ ] **Prepare rollback plan**
- [ ] **Schedule maintenance window**
- [ ] **Notify stakeholders**

### Testing Environment
- [ ] Staging environment ready
- [ ] Staging data up-to-date
- [ ] Test credentials prepared

## Laravel Framework Updates

### Check Current Version
```bash
cd /var/www/simple-store
php artisan --version
```

### Minor Version Updates (e.g., 12.1 → 12.2)
**Risk Level**: Low
**Downtime**: Minimal

```bash
# 1. Backup
php artisan down
cp .env .env.backup
tar -czf simple-store-backup-$(date +%Y%m%d).tar.gz /var/www/simple-store

# 2. Update composer.json untuk allow minor updates
# "laravel/framework": "^12.1" allows updates to 12.x

# 3. Update dependencies
composer update laravel/framework --with-dependencies

# 4. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 5. Run migrations (jika ada)
php artisan migrate --force

# 6. Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Test application
php artisan test

# 8. Bring application back up
php artisan up
```

### Major Version Updates (e.g., 11.x → 12.x)
**Risk Level**: HIGH
**Downtime**: Significant
**Testing Required**: Extensive

**⚠️ WARNING**: Major updates require careful planning!

```bash
# 1. Review upgrade guide
# https://laravel.com/docs/12.x/upgrade

# 2. Test in development environment FIRST
git checkout -b upgrade-laravel-12
composer require laravel/framework:^12.0

# 3. Fix breaking changes
# Review upgrade guide dan fix all breaking changes

# 4. Run tests extensively
php artisan test
yarn test

# 5. Test in staging environment
# Deploy ke staging dan test thoroughly

# 6. Schedule maintenance window (2-4 hours)
# Notify users well in advance

# 7. Production deployment
# Follow standard deployment procedure dengan extra caution
```

## Dependency Updates

### Check Outdated Packages
```bash
# PHP packages
composer outdated

# Node packages
yarn outdated
```

### Update PHP Dependencies

#### Minor Updates (Patches)
```bash
# Update all packages untuk patch versions
composer update --with-dependencies

# Atau update specific package
composer update vendor/package

# Clear caches
php artisan config:clear
php artisan cache:clear

# Run tests
php artisan test
```

#### Major Updates
```bash
# Review package changelog first
# composer show vendor/package

# Update composer.json dengan new version
# vim composer.json

# Install new version
composer update vendor/package --with-dependencies

# Fix breaking changes (jika ada)
# Run tests
php artisan test

# Test thoroughly
```

### Update Node Dependencies

#### Minor Updates
```bash
# Update packages
yarn upgrade

# Rebuild assets
yarn run build

# Test frontend
# Check browser console untuk errors
```

#### Major Updates
```bash
# Check for breaking changes
yarn outdated

# Update specific package
yarn upgrade package-name@latest

# Rebuild
yarn run build

# Test thoroughly
# Especially test all JavaScript functionality
```

## Database Updates

### Run New Migrations
```bash
# Check pending migrations
php artisan migrate:status

# Backup database first
mysqldump -u user -p simple_store > backup_before_migration.sql

# Run migrations
php artisan migrate --force

# If something goes wrong, rollback
php artisan migrate:rollback
```

### Schema Changes
```bash
# For complex schema changes:
# 1. Test in staging first
# 2. Estimate migration time
# 3. Schedule maintenance window
# 4. Backup database
# 5. Run migration
# 6. Verify data integrity
```

## System Updates

### PHP Updates

#### Minor Version (e.g., 8.4.1 → 8.4.2)
**Risk Level**: Low

```bash
# Update PHP
sudo apt update
sudo apt upgrade php8.4-*

# Restart PHP-FPM
sudo systemctl restart php8.4-fpm

# Test application
curl -I https://your-domain.com
```

#### Major Version (e.g., 8.3 → 8.4)
**Risk Level**: HIGH
**Testing Required**: Extensive

```bash
# 1. Review PHP upgrade guide
# Check deprecations dan breaking changes

# 2. Test in development first
# Install new PHP version di development
# Test application thoroughly

# 3. Update production
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.4 php8.4-fpm php8.4-mysql php8.4-mbstring php8.4-xml php8.4-bcmath php8.4-curl

# 4. Update nginx/apache configuration untuk use new PHP version

# 5. Restart services
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx

# 6. Test thoroughly
php -v
php artisan --version
```

### MySQL Updates
**Risk Level**: MEDIUM-HIGH
**Backup Required**: YES

```bash
# 1. Backup database completely
mysqldump --all-databases > all_databases_backup.sql

# 2. Update MySQL
sudo apt update
sudo apt upgrade mysql-server

# 3. Run mysql_upgrade (jika diperlukan untuk major versions)
sudo mysql_upgrade -u root -p

# 4. Restart MySQL
sudo systemctl restart mysql

# 5. Verify
mysql --version
mysql -u root -p -e "SELECT version();"

# 6. Test application database connectivity
```

### Web Server Updates (Nginx/Apache)
**Risk Level**: MEDIUM

```bash
# 1. Backup configuration
sudo cp /etc/nginx/sites-available/simple-store /etc/nginx/sites-available/simple-store.backup

# 2. Update
sudo apt update
sudo apt upgrade nginx

# 3. Test configuration
sudo nginx -t

# 4. Reload/Restart
sudo systemctl reload nginx
# atau jika major changes:
sudo systemctl restart nginx

# 5. Verify website accessible
curl -I https://your-domain.com
```

## Update Procedures by Environment

### Development Environment
```bash
# Pull latest code
git pull origin main

# Update dependencies
composer install
yarn install

# Run migrations
php artisan migrate

# Clear caches
php artisan config:clear
php artisan cache:clear

# Rebuild assets
yarn run dev

# Run tests
php artisan test
```

### Staging Environment
```bash
# 1. Enable maintenance mode
php artisan down

# 2. Pull latest code
git pull origin staging

# 3. Backup database
mysqldump -u user -p simple_store > backup_$(date +%Y%m%d).sql

# 4. Update dependencies
composer install --no-dev --optimize-autoloader
yarn install --production

# 5. Run migrations
php artisan migrate --force

# 6. Clear dan rebuild caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Rebuild assets
yarn run build

# 8. Run tests
php artisan test

# 9. Disable maintenance mode
php artisan up

# 10. Test thoroughly
# Manual testing of critical features
```

### Production Environment
```bash
# 1. Notify users (jika diperlukan)
# Send email/notification about maintenance

# 2. Enable maintenance mode
php artisan down --message="System update in progress"

# 3. Backup database
mysqldump -u user -p simple_store > /var/backups/simple-store/production_backup_$(date +%Y%m%d_%H%M%S).sql
gzip /var/backups/simple-store/production_backup_*.sql

# 4. Backup application files
tar -czf /var/backups/simple-store/app_backup_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/simple-store

# 5. Pull latest code
cd /var/www/simple-store
git pull origin main

# 6. Update dependencies
composer install --no-dev --optimize-autoloader --no-interaction
yarn install --production --non-interactive

# 7. Run migrations
php artisan migrate --force

# 8. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 9. Rebuild assets
yarn run build

# 10. Optimize
composer dump-autoload --optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 11. Restart queue workers (jika ada)
php artisan queue:restart

# 12. Disable maintenance mode
php artisan up

# 13. Verify functionality
curl -I https://your-domain.com
# Test critical features manually

# 14. Monitor logs
tail -f storage/logs/laravel.log

# 15. Notify users completion (jika applicable)
```

## Rollback Procedures

### Quick Rollback (Code Issues)
```bash
# 1. Enable maintenance mode
php artisan down

# 2. Revert code
git log --oneline -5  # Find previous commit
git revert HEAD  # atau git reset --hard [commit-hash]

# 3. Reinstall dependencies (jika necessary)
composer install --no-dev
yarn install --production

# 4. Rollback migrations (jika necessary)
php artisan migrate:rollback

# 5. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 6. Rebuild
yarn run build
php artisan config:cache
php artisan route:cache

# 7. Resume
php artisan up
```

### Full Rollback (Database + Code)
```bash
# 1. Enable maintenance mode
php artisan down

# 2. Restore database
gunzip -c /var/backups/simple-store/backup_file.sql.gz | mysql -u user -p simple_store

# 3. Restore application files
cd /var/www
rm -rf simple-store
tar -xzf /var/backups/simple-store/app_backup_file.tar.gz

# 4. Clear caches
cd /var/www/simple-store
php artisan config:clear
php artisan cache:clear

# 5. Resume
php artisan up

# 6. Investigate what went wrong
```

## Post-Update Verification

### Automated Checks
```bash
# Run test suite
php artisan test

# Check for errors
tail -100 storage/logs/laravel.log | grep ERROR

# Verify services
systemctl status nginx
systemctl status php8.4-fpm
systemctl status mysql
```

### Manual Checks
- [ ] Homepage loads correctly
- [ ] User login works
- [ ] Critical features functional
- [ ] No JavaScript errors di console
- [ ] Database queries working
- [ ] Email sending works (jika applicable)
- [ ] File uploads work (jika applicable)
- [ ] API endpoints functional (jika applicable)

## Update Log Template

```markdown
# Update Log: [Date]

## Update Type
[Security/Bug Fix/Feature/Minor]

## Affected Components
- Laravel: [old version] → [new version]
- PHP: [old version] → [new version]
- Packages: [list]

## Changes Made
1. [Change description]
2. [Change description]

## Testing Performed
- [ ] Unit tests
- [ ] Feature tests
- [ ] Manual testing
- [ ] Staging environment
- [ ] Load testing (jika applicable)

## Issues Encountered
[Any issues dan how they were resolved]

## Rollback Plan
[Describe rollback procedure used/prepared]

## Downtime
Start: [Time]
End: [Time]
Duration: [Minutes]

## Verification
- [ ] All tests passing
- [ ] Critical features working
- [ ] No errors in logs
- [ ] Performance acceptable

## Notes
[Additional notes]

## Updated By
Zulfikar Hidayatullah
```

## Best Practices

### Before Updates
1. **Always backup** - Database dan files
2. **Test in staging** - Mirror production
3. **Review changelogs** - Know what's changing
4. **Schedule appropriately** - Low-traffic times
5. **Notify users** - Set expectations

### During Updates
1. **Follow procedures** - Don't skip steps
2. **Document actions** - Keep log of what you do
3. **Monitor closely** - Watch for issues
4. **Have rollback ready** - Be prepared to revert

### After Updates
1. **Verify thoroughly** - Test everything
2. **Monitor closely** - Watch for issues (24-48 hours)
3. **Document completion** - Update logs
4. **Learn dari issues** - Improve process

## Emergency Updates

For critical security updates:

```bash
# 1. Assess risk: Update now vs exploit risk
# 2. Quick backup
# 3. Apply update
# 4. Minimal testing
# 5. Monitor closely
# 6. Full testing when possible
```

## Resources
- Laravel Upgrade Guide: https://laravel.com/docs/upgrades
- PHP Migration Guide: https://www.php.net/manual/en/appendices.php
- Composer Documentation: https://getcomposer.org/doc/

## Contact
**Developer**: Zulfikar Hidayatullah (+62 857-1583-8733)

## Document Updates
**Last Updated**: [Date]
**Maintained By**: Zulfikar Hidayatullah


