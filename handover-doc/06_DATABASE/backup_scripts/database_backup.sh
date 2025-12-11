#!/bin/bash
# Automated Database Backup Script
# Author: Zulfikar Hidayatullah
# Description: Script untuk backup database Simple Store secara otomatis dengan
# compression dan retention policy yang telah ditentukan

# Configuration
DB_NAME="simple_store"
DB_USER="simple_store_user"
DB_PASS="your_password"
BACKUP_DIR="/var/backups/simple-store"
RETENTION_DAYS=7
DATE=$(date +%Y%m%d_%H%M%S)
FILENAME="simple_store_${DATE}.sql"
LOG_FILE="/var/log/simple-store-backup.log"

# Function: Log message dengan timestamp
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a $LOG_FILE
}

# Create backup directory jika belum ada
mkdir -p $BACKUP_DIR

log_message "Starting database backup..."

# Backup database dengan single-transaction untuk consistency
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

    # Compress backup untuk save space
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

# Remove old backups sesuai retention policy
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
# Uncomment dan configure sesuai kebutuhan
# rsync -avz $BACKUP_DIR/${FILENAME}.gz user@remote:/path/to/backup/
# OR use cloud storage CLI (aws s3 cp, gsutil cp, rclone copy, etc)

log_message "Backup completed successfully"
log_message "----------------------------------------"

exit 0


