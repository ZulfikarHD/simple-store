# Tugas Maintenance Rutin

## Overview
Dokumen ini berisi daftar maintenance tasks yang harus dilakukan secara regular untuk memastikan aplikasi Simple Store berjalan optimal dan aman.

## Daily Tasks

### 1. Monitor Application Health
**Time**: Setiap pagi (09:00 WIB)
**Duration**: 10 menit

**Checklist**:
- [ ] Check website accessible
- [ ] Verify HTTPS working
- [ ] Check response time
- [ ] Review error logs

**Commands**:
```bash
# Check website status
curl -I https://your-domain.com

# Check recent errors
tail -50 /var/www/simple-store/storage/logs/laravel.log | grep ERROR

# Check disk space
df -h

# Check server load
uptime
```

**What to Look For**:
- Error spikes
- Unusual response times
- Disk space warnings
- Memory issues

---

### 2. Backup Verification
**Time**: Setiap pagi (09:30 WIB)
**Duration**: 5 menit

**Checklist**:
- [ ] Verify last backup successful
- [ ] Check backup file size reasonable
- [ ] Verify backup log untuk errors

**Commands**:
```bash
# Check last backup
ls -lth /var/backups/simple-store/ | head -5

# Check backup log
tail -20 /var/log/simple-store-backup.log

# Verify backup integrity
gunzip -t /var/backups/simple-store/latest_backup.sql.gz
```

---

### 3. Monitor Queue Jobs (jika applicable)
**Time**: Beberapa kali sehari
**Duration**: 2 menit

**Checklist**:
- [ ] Check failed jobs count
- [ ] Verify queue workers running
- [ ] Review job processing time

**Commands**:
```bash
# Check queue status
php artisan queue:failed

# Check worker processes
ps aux | grep "queue:work"

# View queue size (Redis example)
redis-cli llen "queues:default"
```

## Weekly Tasks

### 1. Database Maintenance
**Time**: Setiap Minggu pagi (Minggu 02:00 WIB)
**Duration**: 30 menit

**Checklist**:
- [ ] Optimize database tables
- [ ] Check database size growth
- [ ] Review slow query log
- [ ] Update statistics

**Commands**:
```bash
# Login to MySQL
mysql -u root -p

# Check database size
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'simple_store'
GROUP BY table_schema;

# Optimize tables
OPTIMIZE TABLE users, products, orders;

# Analyze tables untuk update statistics
ANALYZE TABLE users, products, orders;

EXIT;
```

---

### 2. Log Cleanup
**Time**: Setiap Minggu (Minggu 03:00 WIB)
**Duration**: 10 menit

**Checklist**:
- [ ] Archive old logs
- [ ] Clear old logs > 30 days
- [ ] Compress archived logs

**Commands**:
```bash
# Archive Laravel logs older than 30 days
find /var/www/simple-store/storage/logs -name "*.log" -mtime +30 -exec gzip {} \;

# Move archived logs
mkdir -p /var/backups/logs/$(date +%Y%m)
mv /var/www/simple-store/storage/logs/*.gz /var/backups/logs/$(date +%Y%m)/

# Clear web server logs older than 60 days
find /var/log/nginx -name "*.log" -mtime +60 -delete

# Clear old backup logs
find /var/log -name "*backup*.log" -mtime +90 -delete
```

---

### 3. Security Check
**Time**: Setiap Minggu (Minggu pagi)
**Duration**: 20 menit

**Checklist**:
- [ ] Review failed login attempts
- [ ] Check for suspicious activity
- [ ] Review access logs
- [ ] Verify SSL certificate expiry

**Commands**:
```bash
# Check SSL certificate expiry
echo | openssl s_client -servername your-domain.com -connect your-domain.com:443 2>/dev/null | openssl x509 -noout -dates

# Review failed login attempts (check application logs)
grep "Failed login" /var/www/simple-store/storage/logs/laravel.log | tail -50

# Check for unusual access patterns
tail -100 /var/log/nginx/access.log | awk '{print $1}' | sort | uniq -c | sort -rn | head -20
```

---

### 4. Update Check
**Time**: Setiap Minggu (Senin pagi)
**Duration**: 15 menit

**Checklist**:
- [ ] Check for Laravel updates
- [ ] Check for PHP updates
- [ ] Check for npm package updates
- [ ] Review security advisories

**Commands**:
```bash
cd /var/www/simple-store

# Check outdated composer packages
composer outdated

# Check outdated npm packages
yarn outdated

# Check PHP version
php -v

# Check for security vulnerabilities
composer audit
yarn audit
```

## Monthly Tasks

### 1. Performance Review
**Time**: Awal setiap bulan
**Duration**: 1-2 jam

**Checklist**:
- [ ] Review application performance metrics
- [ ] Analyze database query performance
- [ ] Check page load times
- [ ] Review server resources usage
- [ ] Identify bottlenecks

**Tools & Commands**:
```bash
# Database slow query analysis
mysql -u root -p
SELECT * FROM mysql.slow_log ORDER BY query_time DESC LIMIT 20;

# Check server resources
htop
free -h
iostat

# Laravel query debugging
# Enable query log di config/database.php
# Review generated queries
```

**Actions**:
- Optimize slow queries
- Add missing indexes
- Implement caching strategies
- Scale resources jika diperlukan

---

### 2. Backup Testing
**Time**: Setiap bulan (tanggal 15)
**Duration**: 1 jam

**Checklist**:
- [ ] Restore backup ke test environment
- [ ] Verify data integrity
- [ ] Test application functionality
- [ ] Document any issues

**Procedure**: Lihat `06_DATABASE/03_Restore_Procedures.md`

---

### 3. Security Updates
**Time**: Awal setiap bulan (setelah testing)
**Duration**: 2-3 jam

**Checklist**:
- [ ] Review security patches available
- [ ] Test updates di staging
- [ ] Schedule maintenance window
- [ ] Apply updates ke production
- [ ] Verify functionality post-update

**Commands**:
```bash
# Update system packages
sudo apt update
sudo apt list --upgradable

# In staging environment first
sudo apt upgrade -y

# Update composer dependencies (untuk security patches)
composer update --with-dependencies

# Update npm packages
yarn upgrade

# Test thoroughly
php artisan test
```

---

### 4. User Management Review
**Time**: Setiap bulan
**Duration**: 30 menit

**Checklist**:
- [ ] Review user accounts
- [ ] Disable inactive accounts
- [ ] Review admin access
- [ ] Audit user permissions

**Commands**:
```bash
# Find inactive users (example: > 90 days)
php artisan tinker
User::where('last_login_at', '<', now()->subDays(90))->get();
```

---

### 5. Storage Cleanup
**Time**: Setiap bulan
**Duration**: 30 menit

**Checklist**:
- [ ] Remove old temporary files
- [ ] Clean up old uploaded files (jika applicable)
- [ ] Remove old cache files
- [ ] Check storage quota

**Commands**:
```bash
# Check storage usage
du -sh /var/www/simple-store/storage/*

# Clear old cache files
php artisan cache:clear

# Remove old session files (older than 7 days)
find /var/www/simple-store/storage/framework/sessions -mtime +7 -delete

# Clean temporary uploads (older than 30 days)
find /var/www/simple-store/storage/app/temp -mtime +30 -delete
```

## Quarterly Tasks

### 1. Full System Audit
**Time**: Setiap 3 bulan
**Duration**: Full day

**Areas to Review**:
- [ ] Security posture
- [ ] Performance trends
- [ ] Cost optimization
- [ ] Capacity planning
- [ ] Backup strategy effectiveness
- [ ] Disaster recovery plan validity

**Deliverables**:
- Audit report
- Recommendations
- Action items

---

### 2. Dependency Updates
**Time**: Setiap 3 bulan
**Duration**: 4-8 jam

**Checklist**:
- [ ] Review all dependencies
- [ ] Plan update strategy
- [ ] Test updates di development
- [ ] Test updates di staging
- [ ] Deploy ke production
- [ ] Monitor post-deployment

**Important**: Major version updates require thorough testing!

---

### 3. Documentation Review
**Time**: Setiap 3 bulan
**Duration**: 2-3 jam

**Checklist**:
- [ ] Update outdated documentation
- [ ] Add new features documentation
- [ ] Remove obsolete information
- [ ] Verify procedures masih accurate
- [ ] Update screenshots jika diperlukan

## Annual Tasks

### 1. Infrastructure Review
**Time**: Awal tahun
**Duration**: 1-2 days

**Topics**:
- [ ] Server infrastructure adequacy
- [ ] Technology stack review
- [ ] Hosting provider evaluation
- [ ] Service provider evaluation
- [ ] Cost vs benefit analysis
- [ ] Migration opportunities

---

### 2. Security Audit
**Time**: Once per year
**Duration**: 2-3 days

**Activities**:
- [ ] Penetration testing
- [ ] Vulnerability scanning
- [ ] Code security review
- [ ] Access control audit
- [ ] Compliance review
- [ ] Security policy update

**Consider**: Hire external security auditor

---

### 3. Disaster Recovery Drill
**Time**: Once per year
**Duration**: Full day

**Scenario Testing**:
- [ ] Complete server failure
- [ ] Database corruption
- [ ] Data breach response
- [ ] DDoS attack
- [ ] Backup restoration

**Goal**: Ensure recovery procedures work dan team knows how to execute

## Maintenance Automation

### Automated Daily Tasks
Setup cron jobs untuk automated tasks:

```bash
# Edit crontab
crontab -e

# Daily backup (2 AM)
0 2 * * * /path/to/backup_script.sh

# Daily log rotation (3 AM)
0 3 * * * /path/to/log_rotation.sh

# Daily health check (every hour)
0 * * * * /path/to/health_check.sh
```

### Monitoring Alerts
Setup monitoring alerts untuk:
- High error rate
- Slow response time
- Disk space < 20%
- Memory usage > 80%
- Failed backup
- SSL expiry < 30 days
- Service downtime

## Maintenance Calendar Template

```markdown
# Monthly Maintenance Calendar

## Week 1
- [ ] Security updates review
- [ ] Performance metrics review
- [ ] Cost review

## Week 2
- [ ] Backup restore testing
- [ ] Documentation updates
- [ ] Dependency review

## Week 3
- [ ] User management review
- [ ] Storage cleanup
- [ ] Database optimization

## Week 4
- [ ] Monthly report preparation
- [ ] Plan next month's tasks
- [ ] Review incidents/issues
```

## Maintenance Best Practices

### Before Any Maintenance
1. **Backup first** - Always
2. **Test in staging** - If possible
3. **Schedule appropriately** - Low traffic times
4. **Notify users** - If downtime expected
5. **Have rollback plan** - Always

### During Maintenance
1. **Enable maintenance mode** - If needed
2. **Follow procedures** - Don't skip steps
3. **Document actions** - Keep logs
4. **Monitor closely** - Watch for issues

### After Maintenance
1. **Verify functionality** - Test thoroughly
2. **Check logs** - Look for errors
3. **Monitor performance** - Ensure normal
4. **Disable maintenance mode**
5. **Document completion** - Update logs

## Emergency Maintenance

When urgent maintenance required:

1. **Assess urgency** - Is it truly urgent?
2. **Backup immediately** - Before any changes
3. **Enable maintenance mode**
4. **Execute fix**
5. **Test thoroughly**
6. **Resume normal operation**
7. **Post-mortem** - Why did this happen? How to prevent?

## Maintenance Log

Keep a maintenance log:

```markdown
# Maintenance Log

## 2024-01-15 - Database Optimization
**Type**: Scheduled Monthly
**Duration**: 30 minutes
**Actions**: 
- Optimized all tables
- Rebuilt indexes
**Issues**: None
**Notes**: Performance improved 15%

## 2024-01-10 - Security Update
**Type**: Emergency
**Duration**: 1 hour
**Actions**: 
- Applied PHP security patch
- Updated dependencies
**Issues**: Minor compatibility issue resolved
**Notes**: Downtime 15 minutes
```

## Contact for Maintenance Issues
**Developer**: Zulfikar Hidayatullah (+62 857-1583-8733)
**Escalation**: [Name & Contact]

## Resources
- **Maintenance Scripts**: `/var/www/simple-store/maintenance/`
- **Backup Location**: `/var/backups/simple-store/`
- **Logs**: `/var/www/simple-store/storage/logs/`


