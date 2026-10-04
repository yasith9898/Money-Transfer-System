# Money Transfer System - Admin Only

## 🎯 System Overview
This is an **ADMIN-ONLY** money transfer system. There are no regular user accounts or public-facing interfaces. Only administrators can access the system.

---

## 🔐 Admin Login Credentials

### Login URL
**http://127.0.0.1:8000/admin/login**

### Super Admin Account
- **Email:** admin@admin.com
- **Password:** admin123
- **Role:** Super Admin
- **Permissions:** Full system access + Can manage other admins

### Regular Admin Account
- **Email:** admin1@admin.com
- **Password:** admin123
- **Role:** Admin
- **Permissions:** Can manage accounts, transactions, currencies (Cannot manage other admins)

---

## 📊 Admin Dashboard Features

### Main Dashboard (http://127.0.0.1:8000/admin)
- **Account Statistics**
  - Total Accounts
  - Active Accounts
  
- **Transaction Statistics**
  - Today's Transactions
  - Recent Transaction History
  
- **Currency Management**
  - Active Currencies
  
- **Quick Actions**
  - New Transfer
  - Create Account
  - Balance Adjustment
  - Daily Report
  - Manage Admins (Super Admin Only)

### Accessible Modules
1. **Accounts** - Create and manage customer accounts
2. **Transactions** - Process transfers and adjustments
3. **Currencies** - Manage supported currencies
4. **Reports** - View daily reports and commission reports
5. **Admin Users** - Manage admin accounts (Super Admin only)

---

## 🛡️ Security Features

### Authentication & Authorization
- All routes protected with `auth` and `admin` middleware
- Role-based access control (admin vs super_admin)
- Session management with CSRF protection
- Secure password hashing (bcrypt)

### Access Restrictions
- ❌ No public registration
- ❌ No user-side login
- ❌ No regular user accounts
- ✅ Admin login only
- ✅ Admin-managed accounts for customers

---

## 🚀 Getting Started

1. **Start the server:**
   ```bash
   php artisan serve
   ```

2. **Access admin panel:**
   - Navigate to: http://127.0.0.1:8000
   - You'll be redirected to admin login

3. **Login with credentials above**

4. **Start managing:**
   - Create customer accounts
   - Process transactions
   - Manage currencies
   - View reports

---

## 👥 User Roles

### Super Admin
- Full system access
- Can create/edit/delete other admin users
- Can manage all accounts, transactions, and currencies
- Access to all reports and system settings

### Admin
- Can manage accounts, transactions, and currencies
- Cannot create or manage other admin users
- Access to all reports

---

## 📝 Important Notes

1. **No Regular Users:** This system does NOT have regular user registration or login. Accounts are created by admins for customers.

2. **Admin Only Access:** All features require admin authentication.

3. **Protected Routes:** All functional routes (accounts, transactions, currencies) are protected with admin middleware.

4. **Password Security:** Change default passwords in production!

5. **Database Seeder:** Running `php artisan db:seed` will create the default admin accounts above.

---

## 🔄 Re-seeding Database

To recreate admin accounts:
```bash
php artisan db:seed
```

This will create the super admin and regular admin accounts with the credentials listed above.
