# Skema Database

## Overview
Dokumentasi lengkap tentang struktur database aplikasi Simple Store.

## ERD (Entity Relationship Diagram)
Lihat: `diagrams/database_ERD.png`

## Database Connection
- **Driver**: [mysql/pgsql]
- **Charset**: utf8mb4
- **Collation**: utf8mb4_unicode_ci

## Tables

### users
Tabel untuk menyimpan data pengguna.

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|
| id | bigint unsigned | NO | AUTO_INCREMENT | Primary key |
| name | varchar(255) | NO | - | Nama lengkap user |
| email | varchar(255) | NO | - | Email (unique) |
| email_verified_at | timestamp | YES | NULL | Waktu verifikasi email |
| password | varchar(255) | NO | - | Hashed password |
| remember_token | varchar(100) | YES | NULL | Remember me token |
| created_at | timestamp | YES | NULL | Waktu dibuat |
| updated_at | timestamp | YES | NULL | Waktu diupdate |

**Indexes**:
- PRIMARY: id
- UNIQUE: email

**Relationships**:
- [Jelaskan relasi dengan tabel lain]

---

### [Tabel 2]
[Dokumentasi serupa untuk setiap tabel]

---

## Migrations
Daftar migration files dalam urutan eksekusi:
1. `2024_01_01_000000_create_users_table.php`
2. [List semua migration files]

## Seeders
Daftar seeder yang tersedia:
- `DatabaseSeeder.php`: [Deskripsi]
- [Seeder lainnya]

## Indexes & Performance

### Existing Indexes
[Daftar index yang ada dan tujuannya]

### Recommendations
[Rekomendasi index tambahan untuk optimasi]

## Foreign Key Constraints
[Daftar foreign key dan cascade behavior]

## Database Views
[Jika ada database views]

## Stored Procedures
[Jika ada stored procedures]

## Backup Strategy
Lihat: `06_DATABASE/02_Backup_Procedures.md`


