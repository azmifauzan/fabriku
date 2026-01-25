# Admin Panel Guide

> **Panduan lengkap untuk Super Admin mengelola platform Fabriku**

## 📋 Daftar Isi

- [Overview](#overview)
- [Akses Admin Panel](#akses-admin-panel)
- [Dashboard](#dashboard)
- [Tenant Management](#tenant-management)
- [User Management](#user-management)
- [Role & Permission Management](#role--permission-management)
- [Audit Logs](#audit-logs)
- [Security](#security)
- [Best Practices](#best-practices)

## Overview

Admin Panel adalah interface khusus untuk Super Admin mengelola platform Fabriku secara keseluruhan. Panel ini terpisah dari tenant interface dan memiliki authentication guard sendiri untuk keamanan maksimal.

### Fitur Utama

✅ **Tenant Management** - Kelola semua tenant di platform  
✅ **User Management** - Administrasi user lintas tenant  
✅ **RBAC System** - Role-Based Access Control lengkap  
✅ **Audit Logs** - Activity tracking dengan change comparison  
✅ **Statistics** - Platform-wide metrics dan analytics

### Akses Level

- **Super Admin**: Full access ke semua fitur
- **Admin**: Terbatas pada operasi tertentu
- **Manager**: Read-only access untuk monitoring

## Akses Admin Panel

### Login

**URL:** `http://localhost:8000/admin/login`

**Credentials Default:**
```
Email: admin@fabriku.com
Password: password
```

> ⚠️ **Penting**: Segera ganti password default setelah login pertama kali!

### Tampilan Login

Admin login page menggunakan design glassmorphism dengan:
- Purple gradient background
- Backdrop blur effect
- Smooth animations
- Responsive layout

## Dashboard

Dashboard memberikan overview platform secara keseluruhan.

### Statistics Cards

1. **Total Tenants**
   - Jumlah total tenant
   - Breakdown: Active vs Inactive
   - Growth trend indicator

2. **Total Users**
   - Jumlah user di semua tenant
   - User distribution per tenant

3. **Preparation Orders**
   - Total orders di platform
   - Per kategori (Garment, Food)

4. **Sales Orders**
   - Total sales lintas tenant
   - Revenue indicators

### Recent Tenants Table

Menampilkan tenant terbaru dengan informasi:
- Tenant name
- Business category
- Subscription plan
- Status (Active/Inactive)
- Created date

## Tenant Management

Kelola semua tenant di platform dengan full CRUD operations.

### Tenant List

**Access:** Admin → Tenants

**Features:**
- Search by tenant name
- Filter by:
  - Status (Active/Inactive)
  - Subscription plan (Trial/Basic/Premium/Enterprise)
- Pagination
- Quick actions per tenant

### Create New Tenant

**Steps:**

1. Click **"Create Tenant"** button
2. Fill in tenant information:
   - **Tenant Name**: Nama bisnis (required)
   - **Business Category**: garment, food, craft, cosmetic, other
   - **Subscription Plan**: trial, basic, premium, enterprise
   - **Subscription Duration**: dalam hari (default: 30)

3. Fill in admin user details:
   - **Admin Name**: Nama admin tenant (required)
   - **Admin Email**: Email admin (required)
   - **Admin Password**: Password minimal 8 karakter (required)

4. Click **"Create Tenant"**

**Result:**
- Tenant baru dibuat
- Admin user otomatis dibuat dan di-assign
- Subscription periode mulai dari tanggal pembuatan
- Email welcome (jika configured)

### View Tenant Details

Click tenant name untuk melihat detail lengkap:

**Information Displayed:**
- Basic info (category, plan, status)
- Subscription expiry date
- Created/updated timestamps

**Statistics:**
- Total users
- Total materials
- Total patterns/recipes
- Preparation orders count
- Production orders count
- Sales orders count

**User List:**
- Semua user dalam tenant
- Role, email, status

### Edit Tenant

**Editable Fields:**
- Tenant name
- Business category
- Subscription plan
- Subscription expiry date
- Active status (checkbox)

**Actions:**
1. Click **"Edit"** on tenant detail page
2. Modify fields
3. Click **"Update Tenant"**

### Suspend/Activate Tenant

**Suspend:**
- Click **"Suspend"** button
- Confirm action
- Tenant menjadi inactive
- Users tidak bisa login
- Data tetap tersimpan

**Activate:**
- Click **"Activate"** button  
- Tenant menjadi active kembali
- Users bisa login lagi

## User Management

Kelola user dari semua tenant dalam satu interface.

### User List

**Access:** Admin → Users

**Features:**
- Search by name/email
- Filter by:
  - Tenant
  - Role (Admin/Manager/Staff)
  - Status (Active/Inactive)
- Pagination
- Cross-tenant visibility

### Create New User

**Steps:**

1. Click **"Create User"**
2. Select **Tenant** (required)
3. Fill user details:
   - Name (required)
   - Email (required)
   - Password (required, min 8 chars)
   - Phone (optional)
4. Select **Basic Role**: Admin, Manager, atau Staff
5. Assign **Additional Roles** (RBAC, optional):
   - Multiple roles dapat dipilih
   - Permissions akan digabungkan
6. Set **Active** status
7. Click **"Create User"**

### View User Details

**Information:**
- Basic info (name, email, phone)
- Tenant affiliation
- Basic role
- Status
- Email verification status
- Created date

**Assigned Roles:**
- List semua RBAC roles
- Permission count per role
- Role descriptions

### Edit User

**Editable:**
- Tenant assignment
- Name, email, phone
- Basic role
- RBAC role assignments
- Active status

**Note:** Password tidak ditampilkan, harus reset manual jika lupa.

## Role & Permission Management

Full RBAC (Role-Based Access Control) system.

### Role List

**Access:** Admin → Roles

**Display:**
- Role cards dengan info:
  - Role name & slug
  - Description
  - Permission count
  - User count
  - System role badge (jika applicable)

### Permissions Structure

Permissions dikelompokkan per **module**:

1. **Material**
   - view_materials
   - create_materials
   - update_materials
   - delete_materials

2. **Pattern**
   - view_patterns
   - create_patterns
   - update_patterns
   - delete_patterns

3. **Production**
   - view_production
   - create_production
   - update_production
   - delete_production
   - manage_contractors

4. **Sales**
   - view_sales
   - create_sales
   - update_sales
   - delete_sales
   - manage_customers

5. **Report**
   - view_reports
   - export_reports
   - view_analytics

### Create Custom Role

**Steps:**

1. Click **"Create Role"**
2. Fill basic info:
   - **Role Name**: e.g., "Warehouse Manager"
   - **Slug**: e.g., "warehouse-manager" (lowercase, dash-separated)
   - **Description**: Optional

3. Select **Permissions**:
   - Grouped by module
   - Check relevant permissions
   - Multiple selection allowed

4. Click **"Create Role"**

**Use Case Example:**
```
Role: Production Supervisor
Permissions:
- view_production ✓
- create_production ✓
- update_production ✓
- view_materials ✓
- view_patterns ✓
```

### View Role Details

**Information:**
- Description
- Total permissions
- Total users assigned
- Created date

**Permissions:**
- Grouped by module
- Visual badge display

**Users:**
- List all users with this role
- User details (name, email, tenant, status)

### Edit Role

**Editable:**
- Name & slug (kecuali system roles)
- Description
- Permission assignments

**System Role Protection:**
- System roles (Super Admin, Admin, Manager) memiliki badge khusus
- Name & slug tidak dapat diubah
- Permissions tetap dapat disesuaikan

### System Roles

Pre-defined roles yang dibuat saat seeding:

1. **Super Admin** (`super-admin`)
   - All permissions
   - Cannot delete
   - Full platform access

2. **Admin** (`admin`)
   - Most permissions
   - Limited admin operations

3. **Manager** (`manager`)
   - View + create permissions
   - No delete operations

## Audit Logs

Complete activity tracking untuk semua critical operations.

### Audit Log List

**Access:** Admin → Audit Logs

**Features:**
- Filter by:
  - Date range (from/to)
  - Event type (Created/Updated/Deleted)
  - Model type (Tenant/User/Material/Pattern)
- Pagination
- Color-coded event badges:
  - 🟢 Green: Created
  - 🔵 Blue: Updated
  - 🔴 Red: Deleted

**Displayed Info:**
- Event type
- Model affected
- User who performed action
- Tenant context
- Timestamp

### View Audit Log Details

Click **"View"** untuk melihat detail lengkap:

**Event Information:**
- Event badge with color
- Performed by (user name & email)
- Tenant name
- Model type & ID
- Timestamp
- IP address

**Old Values** (untuk UPDATE):
```json
{
  "name": "Old Tenant Name",
  "is_active": true,
  "subscription_plan": "trial"
}
```

**New Values:**
```json
{
  "name": "New Tenant Name",
  "is_active": false,
  "subscription_plan": "premium"
}
```

**Changes Comparison Table:**

| Field | Old Value | New Value |
|-------|-----------|-----------|
| name | Old Tenant Name | New Tenant Name |
| is_active | true | false |
| subscription_plan | trial | premium |

### Audit Trail Use Cases

1. **Security Monitoring**
   - Track login attempts
   - Monitor permission changes
   - Detect suspicious activities

2. **Compliance**
   - Maintain change history
   - Audit for regulations
   - Proof of changes

3. **Debugging**
   - Track when data changed
   - Find who made changes
   - Revert if needed

## Security

Admin Panel mengimplementasikan multiple security layers.

### Authentication

- **Separate Guard**: `admin` guard terpisah dari tenant guard
- **Session-based**: Secure session management
- **Last Login Tracking**: Monitor admin activities
- **Password**: Bcrypt hashing

### Authorization

- **Middleware**: `AdminMiddleware` untuk semua routes
- **Active Check**: Hanya admin aktif yang bisa login
- **Role Check**: Planned untuk granular access

### Best Security Practices

1. **Password Management**
   - ✅ Minimal 8 karakter
   - ✅ Kombinasi huruf, angka, simbol
   - ✅ Ganti password secara berkala
   - ❌ Hindari password yang mudah ditebak

2. **Access Control**
   - ✅ Assign role sesuai kebutuhan
   - ✅ Principle of least privilege
   - ✅ Review permissions secara berkala
   - ❌ Jangan share credentials

3. **Audit Trail**
   - ✅ Review audit logs secara rutin
   - ✅ Monitor anomali aktivitas
   - ✅ Set up alerts untuk critical events

4. **Session Management**
   - ✅ Logout setelah selesai bekerja
   - ✅ Timeout otomatis
   - ✅ Single session per user

## Best Practices

### Tenant Management

**DO ✅**
- Verifikasi business info sebelum activate
- Set subscription expiry yang jelas
- Monitor tenant usage secara berkala
- Backup data sebelum suspend

**DON'T ❌**
- Delete tenant tanpa backup data
- Activate tenant tanpa verifikasi
- Lupa extend subscription yang expired
- Ignore tenant complaints

### User Management

**DO ✅**
- Assign role yang sesuai dengan job function
- Deactivate user yang resign/inactive
- Verify email sebelum create
- Document role changes

**DON'T ❌**
- Give all users admin access
- Create users with weak passwords
- Forget to remove departed users
- Share user accounts

### Role & Permission

**DO ✅**
- Create role per job function
- Group permissions logically
- Review permissions quarterly
- Document custom roles

**DON'T ❌**
- Give everyone all permissions
- Create too many similar roles
- Modify system roles drastically
- Ignore permission conflicts

### Audit Logging

**DO ✅**
- Review logs weekly
- Export critical logs
- Set up alerts for anomalies
- Keep logs for compliance

**DON'T ❌**
- Ignore suspicious activities
- Delete audit logs
- Disable logging
- Overlook failed login attempts

## Troubleshooting

### Cannot Login

**Check:**
1. Email & password benar
2. Admin user is active
3. Session cookies enabled
4. CSRF token valid

**Solution:**
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Reset password via tinker
php artisan tinker
> $admin = App\Models\AdminUser::where('email', 'admin@fabriku.com')->first();
> $admin->password = Hash::make('newpassword');
> $admin->save();
```

### Tenant Not Showing

**Check:**
1. Database connection
2. Tenant actually exists
3. Filters not too restrictive

**Solution:**
```bash
# Check in database
php artisan tinker
> App\Models\Tenant::count()
> App\Models\Tenant::latest()->first()
```

### Permissions Not Working

**Check:**
1. Role assigned correctly
2. Permissions linked to role
3. Cache cleared

**Solution:**
```bash
# Clear permission cache
php artisan cache:clear

# Verify role permissions
php artisan tinker
> $role = App\Models\Role::with('permissions')->find(1);
> $role->permissions->pluck('name');
```

## FAQ

**Q: Apakah bisa ada multiple super admin?**  
A: Ya, bisa dibuat multiple admin users dengan role Super Admin.

**Q: Bagaimana cara reset password admin yang lupa?**  
A: Gunakan `php artisan tinker` atau langsung update di database.

**Q: Apakah audit log bisa dihapus?**  
A: Tidak disarankan. Untuk compliance, logs sebaiknya diarsip, bukan dihapus.

**Q: Berapa lama session admin aktif?**  
A: Default 120 menit. Bisa dikonfigurasi di config/session.php.

**Q: Apakah bisa login ke tenant dan admin bersamaan?**  
A: Ya, karena menggunakan guard terpisah. Bisa buka di tab berbeda.

**Q: Bagaimana cara backup data tenant?**  
A: Gunakan `pg_dump` untuk PostgreSQL atau export via admin UI (future feature).

## Support

Jika ada pertanyaan atau masalah:

- **Email**: support@fabriku.com
- **Documentation**: Full docs di `docs/`
- **GitHub Issues**: Report bug atau request fitur

---

Made with ❤️ for Platform Management by Fabriku Team
