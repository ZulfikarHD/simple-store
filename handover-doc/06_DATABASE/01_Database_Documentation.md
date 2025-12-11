# Dokumentasi Database

## Overview
Dokumentasi lengkap mengenai database aplikasi Simple Store, yaitu: struktur, koneksi, dan management procedures.

## Database Information
- **Database Name**: simple_store
- **Engine**: [MySQL/PostgreSQL]
- **Version**: [Version]
- **Character Set**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Timezone**: Asia/Jakarta (WIB)

## Connection Details
Lihat: `10_CREDENTIALS_ACCESS/03_Database_Credentials.md` untuk kredensial lengkap.

## Database Structure
Lihat: `04_TECHNICAL_DOCUMENTATION/02_Database_Schema.md` untuk detail schema.

## Database Size & Statistics
```sql
-- Check database size
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'simple_store'
GROUP BY table_schema;

-- Check table sizes
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)',
    table_rows AS 'Row Count'
FROM information_schema.tables
WHERE table_schema = 'simple_store'
ORDER BY (data_length + index_length) DESC;
```

## Maintenance Tasks

### Optimize Tables
```sql
-- Optimize all tables
OPTIMIZE TABLE users, [table2], [table3];

-- Analyze tables
ANALYZE TABLE users, [table2], [table3];
```

### Check Table Integrity
```sql
-- Check tables
CHECK TABLE users, [table2], [table3];

-- Repair tables (jika diperlukan)
REPAIR TABLE users;
```

## Query Performance

### Slow Query Log
Location: [Path ke slow query log]

### Common Queries to Monitor
```sql
-- Find slow queries
SELECT * FROM mysql.slow_log 
ORDER BY query_time DESC 
LIMIT 10;

-- Check for missing indexes
-- [Queries untuk check index usage]
```

## Index Optimization
Lihat: `04_TECHNICAL_DOCUMENTATION/02_Database_Schema.md` untuk daftar indexes.

## Database Users & Permissions

### Application User
- **Username**: simple_store_user
- **Permissions**: SELECT, INSERT, UPDATE, DELETE
- **Host**: localhost

### Admin User
- **Username**: root atau admin user
- **Permissions**: ALL PRIVILEGES
- **Usage**: Maintenance dan backup only

## Backup & Restore
- **Backup Procedures**: Lihat `02_Backup_Procedures.md`
- **Restore Procedures**: Lihat `03_Restore_Procedures.md`
- **Backup Schedule**: [Daily/Weekly]
- **Retention Policy**: [Policy]
- **Backup Location**: [Location]

## Migration History
```bash
# Check migration status
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback

# Reset all migrations (DANGEROUS)
php artisan migrate:reset
```

## Seeding Data

### Development Data
```bash
php artisan db:seed
```

### Production Data
```bash
php artisan db:seed --class=ProductionSeeder
```

## Database Monitoring

### Key Metrics to Monitor
- Database size growth
- Query response time
- Connection pool usage
- Lock wait time
- Replication lag (jika ada)

### Monitoring Tools
[Tools yang digunakan untuk monitoring]

## Troubleshooting

### Common Issues

#### Connection Refused
```bash
# Check MySQL service
sudo systemctl status mysql

# Restart MySQL
sudo systemctl restart mysql
```

#### Too Many Connections
```sql
-- Check current connections
SHOW PROCESSLIST;

-- Increase max_connections (in my.cnf)
max_connections = 200
```

#### Disk Space Full
```bash
# Check disk space
df -h

# Clear old logs
# [Commands to clear logs]
```

## Best Practices
1. Always backup sebelum melakukan perubahan struktur
2. Test migrations di staging environment terlebih dahulu
3. Monitor database performance secara regular
4. Implement proper indexing strategy
5. Regular maintenance (optimize, analyze tables)
6. Keep database credentials secure
7. Use prepared statements untuk prevent SQL injection
8. Implement connection pooling untuk performance

## Security Considerations
- Database user hanya memiliki permissions yang diperlukan
- Password strong dan unique
- Connection melalui localhost atau private network
- Regular security updates
- Audit log enabled (jika diperlukan)
- Encrypted connections (SSL/TLS)

## Contact & Support
Lihat: `09_TROUBLESHOOTING/03_Emergency_Contacts.md`


