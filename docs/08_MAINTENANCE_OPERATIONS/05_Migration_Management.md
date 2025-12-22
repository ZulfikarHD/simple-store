# Migration Management Guide

## Overview

Dokumentasi ini merupakan panduan lengkap untuk mengelola database migrations di Simple Store yang bertujuan untuk memastikan database schema selalu up-to-date dan konsisten, yaitu: running migrations, rollback procedures, dan troubleshooting common migration issues yang mungkin terjadi selama development atau deployment process.

## Prerequisites

Komponen yang diperlukan untuk mengelola migrations, antara lain:

- **Database Access** - Credentials dan koneksi ke database (SQLite/MySQL/PostgreSQL)
- **Artisan CLI** - Laravel command-line tool untuk running migrations
- **Backup** - Selalu backup database sebelum running migrations di production

## Migration Commands

### Basic Commands

```bash
# Melihat status semua migrations
php artisan migrate:status

# Menjalankan semua pending migrations
php artisan migrate

# Menjalankan migrations dengan verbose output
php artisan migrate -v

# Menjalankan migrations di production (dengan confirmation)
php artisan migrate --force

# Rollback migration terakhir
php artisan migrate:rollback

# Rollback beberapa batch
php artisan migrate:rollback --step=3

# Reset semua migrations (HATI-HATI: akan drop semua tables)
php artisan migrate:reset

# Refresh migrations (reset + migrate)
php artisan migrate:refresh

# Fresh migrations (drop all tables + migrate)
php artisan migrate:fresh
```

### Specific Migration Commands

```bash
# Menjalankan migration file tertentu
php artisan migrate --path=/database/migrations/2025_12_22_015036_add_google_id_to_users_table.php

# Rollback migration tertentu (by batch)
php artisan migrate:rollback --batch=3

# Melihat SQL yang akan dijalankan tanpa execute
php artisan migrate --pretend
```

## Google OAuth Migration

### Migration File

**File**: `database/migrations/2025_12_22_015036_add_google_id_to_users_table.php`

**Changes**:
- Adds `google_id` column (string, nullable, unique)
- Adds `avatar` column (string, nullable)
- Modifies `password` column to nullable

### Scenario 1: Fresh Installation

Jika Anda melakukan fresh installation setelah Google OAuth di-implement:

```bash
# Semua migrations akan otomatis termasuk Google OAuth
php artisan migrate
```

**Expected Output**:
```
Migration table created successfully.
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table (XX.XXms)
...
Migrating: 2025_12_22_015036_add_google_id_to_users_table
Migrated:  2025_12_22_015036_add_google_id_to_users_table (XX.XXms)
```

### Scenario 2: Already Migrated Before Google OAuth

Jika Anda sudah menjalankan `php artisan migrate` sebelum Google OAuth di-implement, migration file baru tidak akan otomatis terdeteksi. Berikut adalah solusinya:

#### Option 1: Run Pending Migrations (RECOMMENDED)

```bash
# Check status migrations
php artisan migrate:status

# Jika ada pending migrations, jalankan
php artisan migrate

# Verify kolom sudah ditambahkan
php artisan tinker
>>> Schema::hasColumn('users', 'google_id')
=> true
>>> Schema::hasColumn('users', 'avatar')
=> true
```

#### Option 2: Run Specific Migration

Jika migration tidak terdeteksi sebagai pending:

```bash
# Run migration file tertentu
php artisan migrate --path=/database/migrations/2025_12_22_015036_add_google_id_to_users_table.php
```

**Expected Output**:
```
Migrating: 2025_12_22_015036_add_google_id_to_users_table
Migrated:  2025_12_22_015036_add_google_id_to_users_table (XX.XXms)
```

#### Option 3: Manual Database Update (Last Resort)

Jika migration file tidak ada atau tidak bisa dijalankan:

```bash
# Via Tinker
php artisan tinker

# Jalankan SQL commands
>>> DB::statement('ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE');
=> true
>>> DB::statement('ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL');
=> true
>>> DB::statement('ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL');
=> true

# Verify
>>> Schema::hasColumn('users', 'google_id')
=> true
```

**MySQL/PostgreSQL Alternative**:
```sql
-- Connect ke database
mysql -u username -p database_name

-- Run SQL commands
ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE;
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL;
ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL;

-- Verify
DESCRIBE users;
```

### Scenario 3: Migration Already Run (Duplicate Error)

Jika Anda mencoba run migration yang sudah pernah dijalankan:

**Error**:
```
SQLSTATE[42S21]: Column already exists: 1060 Duplicate column name 'google_id'
```

**Solution**:
```bash
# Check migration status
php artisan migrate:status

# Jika migration sudah run, tidak perlu action
# Verify kolom exists
php artisan tinker
>>> Schema::hasColumn('users', 'google_id')
=> true
```

## Rollback Google OAuth Migration

Jika Anda perlu rollback Google OAuth migration:

### Option 1: Rollback Last Batch

```bash
# Rollback migration terakhir
php artisan migrate:rollback

# Atau rollback specific step
php artisan migrate:rollback --step=1
```

**Expected Output**:
```
Rolling back: 2025_12_22_015036_add_google_id_to_users_table
Rolled back:  2025_12_22_015036_add_google_id_to_users_table (XX.XXms)
```

**Changes**:
- Drops `google_id` column
- Drops `avatar` column
- `password` column remains nullable (cannot revert to NOT NULL if data exists)

### Option 2: Manual Rollback

```bash
php artisan tinker

# Drop kolom
>>> Schema::table('users', function($table) {
...     $table->dropColumn(['google_id', 'avatar']);
... });
=> null
```

## Troubleshooting

### Error: Migration table not found

**Cause**: Migration table belum dibuat.

**Solution**:
```bash
# Create migration table
php artisan migrate:install

# Then run migrations
php artisan migrate
```

### Error: Column already exists

**Cause**: Migration sudah pernah dijalankan atau kolom sudah ada.

**Solution**:
```bash
# Check migration status
php artisan migrate:status

# Check if column exists
php artisan tinker
>>> Schema::hasColumns('users', ['google_id', 'avatar'])
```

### Error: Cannot modify password column

**Cause**: Ada data existing dengan password NULL.

**Solution**:
```bash
# Check for NULL passwords
php artisan tinker
>>> User::whereNull('password')->count()

# Jika ada, migration akan tetap berjalan
# Password column akan menjadi nullable
```

### Error: SQLSTATE[HY000]: General error: 1 no such table

**Cause**: Table users belum dibuat.

**Solution**:
```bash
# Run semua migrations dari awal
php artisan migrate

# Atau jika perlu fresh start
php artisan migrate:fresh
```

## Best Practices

### Before Running Migrations

1. **Backup Database**
   ```bash
   # SQLite
   cp database/database.sqlite database/database.sqlite.backup
   
   # MySQL
   mysqldump -u username -p database_name > backup.sql
   ```

2. **Check Migration Status**
   ```bash
   php artisan migrate:status
   ```

3. **Review Migration Files**
   - Read migration file untuk understand changes
   - Check for breaking changes
   - Verify rollback logic

### During Migrations

1. **Use Transactions** (automatic in Laravel)
2. **Monitor Output** untuk detect errors
3. **Verify Changes** setelah migration
4. **Test Application** untuk ensure functionality

### After Migrations

1. **Verify Schema**
   ```bash
   php artisan tinker
   >>> Schema::getColumnListing('users')
   ```

2. **Test Critical Features**
   - User registration
   - Login (email & Google)
   - Profile updates

3. **Check Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Update Documentation** jika ada schema changes

## Production Migrations

### Pre-Deployment Checklist

- [ ] Backup production database
- [ ] Test migrations di staging environment
- [ ] Review migration files untuk breaking changes
- [ ] Notify team tentang maintenance window
- [ ] Prepare rollback plan

### Deployment Steps

```bash
# 1. Put application in maintenance mode
php artisan down

# 2. Pull latest code
git pull origin main

# 3. Install dependencies
composer install --no-dev --optimize-autoloader

# 4. Run migrations
php artisan migrate --force

# 5. Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Bring application back online
php artisan up
```

### Post-Deployment Verification

```bash
# Check migration status
php artisan migrate:status

# Verify schema
php artisan tinker
>>> Schema::hasColumn('users', 'google_id')

# Test application
# - Login dengan email
# - Login dengan Google
# - Create test order
```

### Rollback Plan

Jika terjadi issue setelah deployment:

```bash
# 1. Put in maintenance mode
php artisan down

# 2. Rollback migrations
php artisan migrate:rollback --step=1

# 3. Rollback code
git reset --hard previous_commit

# 4. Clear caches
php artisan config:clear
php artisan cache:clear

# 5. Bring back online
php artisan up
```

## Migration History

### Recent Migrations

| Date | Migration | Description | Rollback Safe |
|------|-----------|-------------|---------------|
| 2025-12-22 | add_google_id_to_users_table | Google OAuth support | ⚠️ Partial* |
| 2025-12-10 | make_customer_address_nullable | Optional address | ✅ Yes |
| ... | ... | ... | ... |

*Rollback akan drop `google_id` dan `avatar` columns, tetapi `password` akan tetap nullable

## Related Documentation

- **Google OAuth Integration:** [Google OAuth Setup](../07_THIRD_PARTY_SERVICES/04_Google_OAuth_Integration.md)
- **Database Schema:** [Database Documentation](../04_TECHNICAL_DOCUMENTATION/02_Database_Schema.md)
- **Backup Procedures:** [Database Backup](../06_DATABASE/02_Backup_Procedures.md)

## Support

Jika mengalami masalah dengan migrations:

- **Developer:** Zulfikar Hidayatullah
- **Phone:** +62 857-1583-8733
- **Emergency:** Check `09_TROUBLESHOOTING/` untuk common issues

---

**Last Updated:** 2025-12-22  
**Version:** 1.8.0  
**Author:** Zulfikar Hidayatullah

