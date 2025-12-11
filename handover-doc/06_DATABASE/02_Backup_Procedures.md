# Prosedur Backup Database

## Overview
Dokumentasi lengkap mengenai backup procedures untuk database Simple Store, yaitu: metode backup, schedule, dan best practices.

## Backup Strategy

### Backup Types
1. **Full Backup**: Complete database backup (Daily)
2. **Incremental Backup**: Changes only (setiap 6 jam, optional)
3. **Before Deployment**: Sebelum setiap deployment
4. **On-Demand**: Manual backup ketika diperlukan

### Retention Policy
- **Daily Backups**: Keep 7 hari terakhir
- **Weekly Backups**: Keep 4 minggu terakhir
- **Monthly Backups**: Keep 12 bulan terakhir
- **Before Deployment**: Keep permanently atau sampai deployment stable

## Backup Location
- **Primary**: `/var/backups/simple-store/`
- **Remote**: [Cloud storage/Remote server]
- **Archive**: [Long-term storage location]

## Manual Backup

### Using mysqldump (Recommended)
```bash
#!/bin/bash
# Backup database lengkap

# Variables
DB_NAME="simple_store"
DB_USER="simple_store_user"
DB_PASS="your_password"
BACKUP_DIR="/var/backups/simple-store"
DATE=$(date +%Y%m%d_%H%M%S)
FILENAME="simple_store_${DATE}.sql"

# Create backup directory jika belum ada
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    $DB_NAME > $BACKUP_DIR/$FILENAME

# Compress backup
gzip $BACKUP_DIR/$FILENAME

# Verify backup
if [ -f "$BACKUP_DIR/${FILENAME}.gz" ]; then
    echo "Backup berhasil: ${FILENAME}.gz"
    echo "Size: $(du -h $BACKUP_DIR/${FILENAME}.gz | cut -f1)"
else
    echo "Backup gagal!"
    exit 1
fi
```

### Using Laravel Backup Package (jika terinstall)
```bash
# Run backup
php artisan backup:run

# Backup only database
php artisan backup:run --only-db
```

## Automated Backup

### Backup Script
Save script berikut sebagai `/home/sirinedev/Templates/simple-store/handover-doc/06_DATABASE/backup_scripts/database_backup.sh`:

```bash
#!/bin/bash
# Automated Database Backup Script
# Author: Zulfikar Hidayatullah

# Configuration
DB_NAME="simple_store"
DB_USER="simple_store_user"
DB_PASS="your_password"
BACKUP_DIR="/var/backups/simple-store"
RETENTION_DAYS=7
DATE=$(date +%Y%m%d_%H%M%S)
FILENAME="simple_store_${DATE}.sql"
LOG_FILE="/var/log/simple-store-backup.log"

# Function: Log message
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a $LOG_FILE
}

# Create backup directory
mkdir -p $BACKUP_DIR

log_message "Starting database backup..."

# Backup database
mysqldump -u $DB_USER -p$DB_PASS \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --add-drop-table \
    --complete-insert \
    $DB_NAME > $BACKUP_DIR/$FILENAME 2>> $LOG_FILE

# Check backup success
if [ $? -eq 0 ]; then
    log_message "Database dump successful"
    
    # Compress backup
    gzip $BACKUP_DIR/$FILENAME
    
    if [ $? -eq 0 ]; then
        log_message "Backup compressed: ${FILENAME}.gz"
        log_message "Size: $(du -h $BACKUP_DIR/${FILENAME}.gz | cut -f1)"
    else
        log_message "ERROR: Compression failed"
        exit 1
    fi
else
    log_message "ERROR: Database dump failed"
    exit 1
fi

# Remove old backups
log_message "Removing backups older than ${RETENTION_DAYS} days..."
find $BACKUP_DIR -name "simple_store_*.sql.gz" -mtime +$RETENTION_DAYS -delete
log_message "Old backups removed"

# Verify backup integrity
log_message "Verifying backup integrity..."
gunzip -t $BACKUP_DIR/${FILENAME}.gz
if [ $? -eq 0 ]; then
    log_message "Backup verification successful"
else
    log_message "ERROR: Backup verification failed"
    exit 1
fi

# Optional: Upload to remote storage
# rsync -avz $BACKUP_DIR/${FILENAME}.gz user@remote:/path/to/backup/
# OR use cloud storage CLI

log_message "Backup completed successfully"
log_message "----------------------------------------"
```

### Make Script Executable
```bash
chmod +x /home/sirinedev/Templates/simple-store/handover-doc/06_DATABASE/backup_scripts/database_backup.sh
```

### Setup Cron Job
```bash
# Edit crontab
crontab -e

# Add backup schedule
# Daily backup at 2 AM
0 2 * * * /home/sirinedev/Templates/simple-store/handover-doc/06_DATABASE/backup_scripts/database_backup.sh

# Weekly backup at Sunday 3 AM (dengan retention lebih lama)
0 3 * * 0 /home/sirinedev/Templates/simple-store/handover-doc/06_DATABASE/backup_scripts/database_backup.sh
```

## Backup Sebelum Deployment
```bash
#!/bin/bash
# Pre-deployment backup

DATE=$(date +%Y%m%d_%H%M%S)
DEPLOYMENT_BACKUP_DIR="/var/backups/simple-store/deployments"

mkdir -p $DEPLOYMENT_BACKUP_DIR

mysqldump -u simple_store_user -p \
    --single-transaction \
    simple_store > $DEPLOYMENT_BACKUP_DIR/pre_deployment_${DATE}.sql

gzip $DEPLOYMENT_BACKUP_DIR/pre_deployment_${DATE}.sql

echo "Pre-deployment backup created: pre_deployment_${DATE}.sql.gz"
```

## Remote Backup

### Using rsync
```bash
# Sync backups ke remote server
rsync -avz --delete \
    /var/backups/simple-store/ \
    user@remote-server:/backup/simple-store/
```

### Using Cloud Storage (AWS S3 example)
```bash
# Install AWS CLI
# Upload to S3
aws s3 cp /var/backups/simple-store/ \
    s3://your-bucket/simple-store-backups/ \
    --recursive
```

### Using Google Drive (rclone)
```bash
# Install and configure rclone
rclone copy /var/backups/simple-store/ gdrive:simple-store-backups/
```

## Backup Verification

### Verify Backup File
```bash
# Check file exists dan not empty
ls -lh /var/backups/simple-store/

# Test gunzip
gunzip -t backup_file.sql.gz

# Check SQL syntax (extract first untuk testing)
gunzip -c backup_file.sql.gz | head -n 100
```

### Test Restore (Recommended)
Periodic test restore di staging environment:
```bash
# Restore to test database
gunzip -c backup_file.sql.gz | mysql -u root -p test_database
```

## Monitoring & Alerts

### Check Backup Status
```bash
# Check last backup
ls -lt /var/backups/simple-store/ | head -5

# Check backup log
tail -50 /var/log/simple-store-backup.log
```

### Setup Alerts
[Setup alert system untuk notify jika backup gagal]
- Email notification
- Slack/Discord webhook
- Monitoring dashboard

## Backup Checklist
- [ ] Backup script configured dan tested
- [ ] Cron job scheduled
- [ ] Backup directory has sufficient space
- [ ] Remote backup configured
- [ ] Retention policy implemented
- [ ] Verification process in place
- [ ] Alert system configured
- [ ] Restore procedure documented dan tested
- [ ] Team members know backup location
- [ ] Backup access permissions properly set

## Troubleshooting

### Backup Fails
```bash
# Check disk space
df -h

# Check MySQL access
mysql -u simple_store_user -p -e "SHOW DATABASES;"

# Check permissions
ls -la /var/backups/simple-store/

# Check logs
tail -100 /var/log/simple-store-backup.log
```

### Large Database Backup
Untuk database yang sangat besar:
```bash
# Use compression on-the-fly
mysqldump -u user -p database | gzip > backup.sql.gz

# Split large backups
mysqldump -u user -p database | split -b 1G - backup.sql.
```

## Best Practices
1. **Test restores regularly** - Backup tidak berguna jika tidak bisa direstore
2. **Multiple backup locations** - Local + remote
3. **Encrypt sensitive backups**
4. **Monitor backup size** - Unusual size changes indicate issues
5. **Document backup procedures** - Team harus tahu cara backup/restore
6. **Automate backup verification**
7. **Keep pre-deployment backups** longer
8. **Test backup scripts** sebelum production

## Security
- Store backup credentials securely
- Limit access ke backup files
- Encrypt backups yang contain sensitive data
- Use secure transfer methods (rsync over SSH, HTTPS)
- Regular audit backup access logs

## Contact
Jika ada masalah dengan backup, hubungi:
Zulfikar Hidayatullah (+62 857-1583-8733)


