# Prosedur Restore Database

## Overview
Panduan lengkap untuk restore database Simple Store dari backup, yaitu: full restore, partial restore, dan disaster recovery procedures.

## ⚠️ PERINGATAN
- **SELALU** backup database saat ini sebelum restore
- **TEST** restore di staging environment terlebih dahulu jika memungkinkan
- **INFORM** users bahwa system akan down selama restore
- **VERIFY** backup file sebelum restore
- **ENABLE** maintenance mode sebelum restore

## Pre-Restore Checklist
- [ ] Identify backup file yang akan direstore
- [ ] Verify backup file integrity
- [ ] Backup current database state
- [ ] Enable maintenance mode
- [ ] Inform stakeholders
- [ ] Stop application services (queue workers, dll)
- [ ] Ensure sufficient disk space

## Full Database Restore

### Step 1: Enable Maintenance Mode
```bash
cd /var/www/simple-store
php artisan down --message="Database maintenance in progress"
```

### Step 2: Stop Related Services
```bash
# Stop queue workers
supervisorctl stop simple-store-worker:*
# atau kill queue processes
pkill -f "artisan queue:work"

# Stop cron jobs temporarily (optional)
# Comment out cron entry temporarily
```

### Step 3: Backup Current Database
```bash
# Backup database saat ini
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u simple_store_user -p simple_store > /var/backups/simple-store/before_restore_${DATE}.sql
gzip /var/backups/simple-store/before_restore_${DATE}.sql

echo "Current database backed up as: before_restore_${DATE}.sql.gz"
```

### Step 4: Verify Backup File
```bash
# Locate backup file
BACKUP_FILE="/var/backups/simple-store/simple_store_20240101_020000.sql.gz"

# Verify file exists
ls -lh $BACKUP_FILE

# Test gunzip
gunzip -t $BACKUP_FILE

# Check first few lines
gunzip -c $BACKUP_FILE | head -20
```

### Step 5: Drop Current Database (Optional)
```bash
# Login to MySQL
mysql -u root -p

# Drop dan recreate database untuk clean restore
DROP DATABASE IF EXISTS simple_store;
CREATE DATABASE simple_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 6: Restore Database
```bash
# Decompress dan restore
gunzip -c $BACKUP_FILE | mysql -u simple_store_user -p simple_store

# Atau jika backup tidak compressed
mysql -u simple_store_user -p simple_store < backup_file.sql
```

### Step 7: Verify Restore
```bash
# Login to MySQL
mysql -u simple_store_user -p

# Select database
USE simple_store;

# Check tables
SHOW TABLES;

# Check row counts
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM [other_important_table];

# Verify recent data
SELECT * FROM users ORDER BY created_at DESC LIMIT 5;

EXIT;
```

### Step 8: Clear Application Cache
```bash
cd /var/www/simple-store

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 9: Restart Services
```bash
# Restart queue workers
supervisorctl start simple-store-worker:*
# atau
php artisan queue:restart

# Restart web server (jika diperlukan)
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
```

### Step 10: Disable Maintenance Mode
```bash
php artisan up
```

### Step 11: Test Application
- [ ] Homepage loads
- [ ] User login works
- [ ] Database queries work
- [ ] Check critical features
- [ ] Review application logs

## Partial Restore (Single Table)

### Restore Single Table
```bash
# Extract single table from backup
mysqldump -u simple_store_user -p simple_store users > users_backup.sql

# Restore single table
mysql -u simple_store_user -p simple_store < users_backup.sql
```

### Restore Specific Rows
```bash
# Extract specific data using SQL
mysql -u simple_store_user -p simple_store << EOF
# Your SQL queries to restore specific data
EOF
```

## Point-in-Time Recovery

### Using Binary Logs (jika enabled)
```bash
# List binary logs
mysql -u root -p -e "SHOW BINARY LOGS;"

# Restore to specific point
mysqlbinlog --start-datetime="2024-01-01 00:00:00" \
    --stop-datetime="2024-01-01 12:00:00" \
    /var/log/mysql/mysql-bin.000001 | mysql -u root -p simple_store
```

## Disaster Recovery Scenarios

### Scenario 1: Accidental Data Deletion
```bash
# 1. Stop application immediately
php artisan down

# 2. Identify when deletion occurred
# Check application logs

# 3. Restore from backup sebelum deletion
# Follow full restore procedure dengan backup sebelum incident

# 4. Verify data restored
# 5. Resume application
```

### Scenario 2: Database Corruption
```bash
# 1. Enable maintenance mode
php artisan down

# 2. Attempt repair
mysql -u root -p
CHECK TABLE table_name;
REPAIR TABLE table_name;

# 3. If repair fails, restore from backup
# Follow full restore procedure

# 4. Resume application
```

### Scenario 3: Complete Server Failure
```bash
# 1. Setup new server
# Follow: 05_DEPLOYMENT_SETUP/04_Initial_Deployment_Guide.md

# 2. Restore database dari remote backup
# Download backup dari cloud/remote server

# 3. Restore database
# Follow full restore procedure

# 4. Test thoroughly sebelum pointing domain
```

## Automated Restore Script

### Create Restore Script
```bash
#!/bin/bash
# Database Restore Script
# Author: Zulfikar Hidayatullah

# Configuration
DB_NAME="simple_store"
DB_USER="simple_store_user"
DB_PASS="your_password"
BACKUP_FILE=$1
LOG_FILE="/var/log/simple-store-restore.log"

# Function: Log message
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a $LOG_FILE
}

# Check if backup file provided
if [ -z "$BACKUP_FILE" ]; then
    echo "Usage: $0 <backup_file.sql.gz>"
    exit 1
fi

# Check if file exists
if [ ! -f "$BACKUP_FILE" ]; then
    log_message "ERROR: Backup file not found: $BACKUP_FILE"
    exit 1
fi

log_message "Starting database restore from: $BACKUP_FILE"

# Verify backup file
log_message "Verifying backup file integrity..."
gunzip -t $BACKUP_FILE
if [ $? -ne 0 ]; then
    log_message "ERROR: Backup file is corrupted"
    exit 1
fi
log_message "Backup file verified successfully"

# Backup current database
log_message "Backing up current database..."
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > /tmp/before_restore_${DATE}.sql
gzip /tmp/before_restore_${DATE}.sql
log_message "Current database backed up to: /tmp/before_restore_${DATE}.sql.gz"

# Enable maintenance mode
log_message "Enabling maintenance mode..."
cd /var/www/simple-store
php artisan down

# Restore database
log_message "Restoring database..."
gunzip -c $BACKUP_FILE | mysql -u $DB_USER -p$DB_PASS $DB_NAME
if [ $? -eq 0 ]; then
    log_message "Database restored successfully"
else
    log_message "ERROR: Database restore failed"
    log_message "Rolling back..."
    gunzip -c /tmp/before_restore_${DATE}.sql.gz | mysql -u $DB_USER -p$DB_PASS $DB_NAME
    php artisan up
    exit 1
fi

# Clear caches
log_message "Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Disable maintenance mode
log_message "Disabling maintenance mode..."
php artisan up

log_message "Database restore completed successfully"
log_message "----------------------------------------"
```

## Rollback Procedure

Jika restore gagal atau cause issues:

```bash
# 1. Enable maintenance mode lagi
php artisan down

# 2. Restore dari before_restore backup
gunzip -c /tmp/before_restore_TIMESTAMP.sql.gz | mysql -u simple_store_user -p simple_store

# 3. Clear caches
php artisan cache:clear

# 4. Resume application
php artisan up

# 5. Investigate issue
tail -100 /var/log/simple-store-restore.log
```

## Testing Restore Procedures

### Regular Restore Testing (Recommended Monthly)
```bash
# 1. Create test database
mysql -u root -p
CREATE DATABASE simple_store_test;
EXIT;

# 2. Restore latest backup to test database
gunzip -c latest_backup.sql.gz | mysql -u root -p simple_store_test

# 3. Verify data
mysql -u root -p simple_store_test
SHOW TABLES;
SELECT COUNT(*) FROM users;
# etc...

# 4. Drop test database
DROP DATABASE simple_store_test;
```

## Recovery Time Objective (RTO)
- **Target RTO**: [Berapa lama downtime yang acceptable]
- **Typical Restore Time**: [Waktu yang dibutuhkan untuk restore]
- **Factors**: Database size, server performance, backup location

## Recovery Point Objective (RPO)
- **Target RPO**: [Berapa banyak data loss yang acceptable]
- **Current RPO**: [Based on backup frequency]

## Post-Restore Verification Checklist
- [ ] All tables present
- [ ] Row counts reasonable
- [ ] Recent data present
- [ ] Foreign keys intact
- [ ] Indexes present
- [ ] User authentication works
- [ ] Application logs show no errors
- [ ] Critical features tested
- [ ] Queue jobs processing
- [ ] Scheduled tasks running

## Troubleshooting

### Restore Fails
```bash
# Check MySQL error log
tail -100 /var/log/mysql/error.log

# Check disk space
df -h

# Check MySQL service
sudo systemctl status mysql

# Verify credentials
mysql -u simple_store_user -p -e "SHOW DATABASES;"
```

### Incomplete Restore
```bash
# Check backup file completeness
tail -20 backup_file.sql

# Should end with:
# -- Dump completed on YYYY-MM-DD HH:MM:SS
```

### Performance Issues After Restore
```bash
# Analyze dan optimize tables
mysql -u root -p simple_store << EOF
ANALYZE TABLE users, [other_tables];
OPTIMIZE TABLE users, [other_tables];
EOF
```

## Best Practices
1. **Practice restore procedures** regularly
2. **Document every restore** - What, when, why, results
3. **Test backups** before relying on them
4. **Keep multiple restore points**
5. **Monitor restore performance**
6. **Train team members** on restore procedures
7. **Have rollback plan** ready
8. **Communicate** dengan stakeholders during restore

## Emergency Contacts
Lihat: `09_TROUBLESHOOTING/03_Emergency_Contacts.md`

## Support
Untuk bantuan dengan restore:
**Developer**: Zulfikar Hidayatullah (+62 857-1583-8733)


