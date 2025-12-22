# 🏪 Production Staff Management Guide
## Simple Store - Zulfikar Hidayatullah

---

## 📋 Overview

Sistem staff management ini dirancang untuk **production use** dimana owner/admin dapat mengelola staff accounts melalui Admin Panel, bukan via seeder atau command setiap kali.

### Flow:
1. **Initial Setup** (One-Time): Create owner pertama via Artisan command
2. **Ongoing Operations**: Owner manage staff via Admin Panel
3. **Scalable**: Support multiple admins/owners

---

## 🚀 Phase 1: Initial Setup (First Time Only)

### Method 1: Via Artisan Command (RECOMMENDED) ✅

Setelah deploy ke production, jalankan command ini **SEKALI SAJA**:

```bash
php artisan store:create-owner
```

**Interactive Mode**:
```bash
$ php artisan store:create-owner

🏪 Simple Store - Create Owner Account

Nama lengkap owner: Zulfikar Hidayatullah
Email owner: owner@simple-store.com
Password owner (min 8 karakter): ********
Nomor telepon (optional, enter untuk skip): +6285715838733

✅ Owner account berhasil dibuat!

┌───────┬────────────────────────────┐
│ Field │ Value                      │
├───────┼────────────────────────────┤
│ ID    │ 1                          │
│ Nama  │ Zulfikar Hidayatullah      │
│ Email │ owner@simple-store.com     │
│ Role  │ admin                      │
│ Phone │ +6285715838733             │
└───────┴────────────────────────────┘

🔐 Sekarang Anda bisa login dengan email dan password yang telah dibuat.
📱 Setelah login, Anda bisa menambahkan staff lain via Admin Panel.
```

**Non-Interactive Mode** (untuk automation):
```bash
php artisan store:create-owner \
  --name="Zulfikar Hidayatullah" \
  --email="owner@simple-store.com" \
  --password="SecurePassword123!" \
  --phone="+6285715838733"
```

### Method 2: Via Seeder (Alternative)

```bash
# Create seeder
php artisan make:seeder OwnerSeeder

# Edit database/seeders/OwnerSeeder.php
```

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::create([
            'name' => 'Zulfikar Hidayatullah',
            'email' => 'owner@simple-store.com',
            'password' => Hash::make('SecurePassword123!'),
            'phone' => '+6285715838733',
            'email_verified_at' => now(),
        ]);

        // ✅ SECURE: Set role explicitly
        $owner->role = 'admin';
        $owner->save();
    }
}
```

```bash
# Run seeder
php artisan db:seed --class=OwnerSeeder
```

---

## 👥 Phase 2: Managing Staff (Ongoing Operations)

### Setelah owner pertama dibuat, semua staff management dilakukan via **Admin Panel**.

### 1. Login sebagai Owner

```
URL: https://your-domain.com/login
Email: owner@simple-store.com
Password: (password yang dibuat)
```

### 2. Akses Staff Management

```
Navigation: Admin Panel → Staff Management
URL: https://your-domain.com/admin/staff
```

### 3. Create New Staff

**Via UI**:
1. Klik "Tambah Staff Baru"
2. Isi form:
   - Nama: `John Doe`
   - Email: `john@simple-store.com`
   - Password: `SecurePass123!`
   - Role: `admin` atau `customer`
   - Phone: `+6281234567890` (optional)
3. Klik "Simpan"

**Audit Trail**:
```sql
-- Setiap staff creation tercatat di audit_logs
SELECT * FROM audit_logs 
WHERE action = 'staff.create' 
ORDER BY created_at DESC;
```

### 4. Update Staff

**Change Role**:
- Owner bisa promote customer → admin
- Owner bisa demote admin → customer
- **PROTECTION**: Tidak bisa demote diri sendiri

**Reset Password**:
- Owner bisa reset password staff lain
- **PROTECTION**: Tidak bisa reset password sendiri (use Profile menu)

### 5. Delete Staff

**Protections**:
- ❌ Tidak bisa delete diri sendiri
- ❌ Tidak bisa delete admin terakhir (min 1 admin)
- ❌ Tidak bisa delete staff yang punya order history

---

## 🔒 Security Features

### 1. Role Assignment Protection

```php
// ✅ SAFE: Explicit role assignment
$user = User::create([...]);
$user->role = $validated['role']; // From admin input, with authorization
$user->save();

// ❌ BLOCKED: Mass assignment
User::create($request->all()); // Role tidak di $fillable
```

### 2. Authorization Checks

- Only admin can access `/admin/staff/*`
- Cannot edit/delete self
- Cannot delete last admin
- All actions logged in audit_logs

### 3. Audit Logging

Tracked Actions:
- `staff.create` - Staff account created
- `staff.update` - Staff info updated
- `staff.password_reset` - Password changed
- `staff.delete` - Staff account deleted

```php
// Query audit logs
AuditLog::where('action', 'LIKE', 'staff.%')
    ->with('user')
    ->latest()
    ->paginate(50);
```

---

## 📊 Multiple Owners/Admins

### YES, You Can Have Multiple Owners/Admins!

**Scenario**: Anda dan partner bisnis sama-sama sebagai owner

**Setup**:
1. Create owner pertama via command (Anda)
2. Login sebagai owner pertama
3. Via Admin Panel → Staff → Create New Staff:
   - Name: Partner Name
   - Email: partner@simple-store.com
   - Role: **admin** ← Set as admin!
4. Done! Sekarang ada 2 owners

**Recommended Structure**:
```
Owner/Admin Hierarchy:
├── Primary Owner (You) - admin role
├── Business Partner - admin role
├── Store Manager - admin role
└── Staff/Cashier - customer role (optional, atau bisa create role baru)
```

### Role Expansion (Optional)

Jika perlu lebih banyak roles, edit migration:

```php
// database/migrations/xxxx_create_users_table.php
$table->enum('role', ['customer', 'staff', 'manager', 'admin', 'owner'])
    ->default('customer');
```

Tapi untuk simple store, **2 roles (customer, admin) sudah cukup**.

---

## 🎯 Production Deployment Checklist

### Step 1: Deploy Application

```bash
# Clone/upload code to server
git clone https://your-repo.git
cd simple-store

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Configure environment
cp .env.example .env
nano .env  # Edit dengan production settings

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Link storage
php artisan storage:link

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 2: Create Initial Owner

```bash
php artisan store:create-owner
```

### Step 3: Login & Setup

1. Login ke admin panel
2. Upload logo dan favicon
3. Configure store settings
4. Create staff accounts (if needed)
5. Create categories dan products

### Step 4: Monitoring

```bash
# Check audit logs
tail -f storage/logs/laravel.log

# Database audit logs
mysql> SELECT * FROM audit_logs WHERE action LIKE 'staff.%' ORDER BY created_at DESC LIMIT 10;
```

---

## 📱 API Endpoints (For Reference)

### Staff Management Routes

| Method | URL | Action |
|--------|-----|--------|
| GET | `/admin/staff` | List all staff |
| GET | `/admin/staff/create` | Show create form |
| POST | `/admin/staff` | Store new staff |
| GET | `/admin/staff/{id}/edit` | Show edit form |
| PATCH | `/admin/staff/{id}` | Update staff |
| PATCH | `/admin/staff/{id}/password` | Reset password |
| DELETE | `/admin/staff/{id}` | Delete staff |

### Example API Calls (for testing)

```bash
# Create staff via API (requires auth token)
curl -X POST https://your-domain.com/admin/staff \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!",
    "role": "admin",
    "phone": "+6281234567890"
  }'
```

---

## 🔧 Troubleshooting

### Issue 1: "Email already exists"

**Cause**: Trying to create staff with existing email  
**Solution**: Use different email or reset password of existing user

### Issue 2: "Cannot delete last admin"

**Cause**: Trying to delete the only admin in system  
**Solution**: Create another admin first, then delete

### Issue 3: "Unauthorized"

**Cause**: Not logged in as admin  
**Solution**: Login with admin account

### Issue 4: Command not found

**Cause**: Artisan command not registered  
**Solution**: Run `composer dump-autoload` and try again

---

## 📈 Monitoring & Maintenance

### Daily Checks

```sql
-- Check new staff created today
SELECT * FROM users 
WHERE created_at >= CURDATE() 
AND role = 'admin';

-- Check recent role changes
SELECT * FROM audit_logs 
WHERE action IN ('staff.create', 'staff.update', 'staff.delete')
AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);
```

### Weekly Reports

```php
// Generate weekly staff activity report
$weeklyStats = [
    'new_staff' => User::where('created_at', '>=', now()->subWeek())->count(),
    'role_changes' => AuditLog::where('action', 'staff.update')
        ->where('created_at', '>=', now()->subWeek())
        ->count(),
    'total_admins' => User::where('role', 'admin')->count(),
];
```

---

## 🎓 Best Practices

### 1. Password Management
- ✅ Use strong passwords (min 8 chars, mixed case, numbers)
- ✅ Change default passwords immediately
- ✅ Don't share admin credentials

### 2. Role Assignment
- ✅ Give minimal privileges needed
- ✅ Regular audit of admin accounts
- ✅ Remove ex-employee accounts promptly

### 3. Audit Logging
- ✅ Review logs regularly
- ✅ Alert on suspicious activities
- ✅ Keep logs for compliance

### 4. Backup
- ✅ Regular database backups
- ✅ Test restore procedures
- ✅ Store backups securely

---

## 🆘 Emergency Procedures

### Lost Admin Access

**Option 1: Via Database (Direct Access)**
```sql
-- Promote customer to admin
UPDATE users 
SET role = 'admin' 
WHERE email = 'your-backup-email@example.com';
```

**Option 2: Via Tinker**
```bash
php artisan tinker

# Promote user to admin
$user = User::where('email', 'your-email@example.com')->first();
$user->role = 'admin';
$user->save();
```

**Option 3: Create Emergency Admin**
```bash
php artisan store:create-owner --email="emergency@example.com"
```

---

## 📞 Support

**Developer**: Zulfikar Hidayatullah  
**Phone**: +62 857-1583-8733  
**Security Issues**: Report immediately via audit logs

---

## ✅ Quick Reference

### Initial Setup (Once)
```bash
php artisan store:create-owner
```

### Daily Operations
- Use Admin Panel → Staff Management
- All changes auto-logged in audit_logs

### Multiple Owners
- ✅ YES, supported
- Create via Admin Panel, set role = admin

### Security
- ✅ Role assignment explicit, not from user input
- ✅ Authorization checks on all actions
- ✅ Audit logging for all changes
- ✅ Cannot self-delete or self-demote

---

**Last Updated**: December 22, 2025  
**Version**: 1.0  
**Production Ready**: ✅ YES

