# Tuition Master

**Tuition Master** is a multi-tenant SAAS web application for managing tuition centers — built with plain PHP (no framework), MySQL, and Bootstrap 5.

One deployment can serve many independent tuition centers ("tenants"). A **Super Admin** onboards each tuition center along with its own admin account; from there, every tuition center manages its own staff, classes, students, and data completely independently of every other center.

---

## ✨ Features

- **Multi-tenant SAAS architecture** — every tuition center's data (users, classes, attendance, exams, marks, notes, lessons, payroll, inventory, notifications) is fully isolated from every other tuition center.
- **User types**: Super Admin, Tuition Center Admin, Admin Staff, Teachers, Students, Parents — plus support for custom roles per tuition center.
- **Hierarchical user management** — every user is created "under" another user (`parent_user_id`), and non-admins can only view/manage the users, classes, and records that fall under their own hierarchy.
- **Two-layer permission system**:
  - **Role-level** default menu visibility + CRUD (view/add/edit/delete) permissions, configurable from *Roles & Permissions → Access*.
  - **User-level overrides**, set individually per user from *Users → Permissions*, which take precedence over the role defaults.
- **Classes** — create classes/subjects, assign teachers and enroll students.
- **Attendance** — mark attendance by class (per student) and by staff, with history.
- **Exams & Marks** — full CRUD for exams, optional assignment of an exam to specific students only, and marks entry/editing per exam.
- **Special Notes** — general/behavioral/academic/medical notes about a student or class, with visibility control (private / staff / staff+parents).
- **Lesson Tools** — upload documents, PDFs, or videos (or link external URLs), then assign them to specific students under your hierarchy.
- **Student Summary Reports** — attendance, marks, notes, classes and lessons in one view, tailored to the viewer's role (student sees their own; parent sees their children; teacher sees their class students; admins see everyone in their center).
- **Payroll** — monthly payroll entries per staff member with basic salary, allowances, deductions, and paid/pending status.
- **Inventory** — stock items with stock-in/stock-out transaction history and reorder-level flags.
- **System Notifications** — post messages to the header notification area, scoped to a tuition center, a specific role, or (super admin only) broadcast to the whole system.
- **Theme customization** — primary/secondary/header/sidebar/button/text colors, logo, footer text, and site name — all configurable in-app, per tuition center.
- **Menu orientation** — switch between a vertical sidebar or a horizontal top menu, per tuition center.
- **Help & Tutorials** — built-in contextual help articles per module.
- **DataTables-powered lists**, CSRF protection, prepared statements throughout, password hashing (bcrypt), and role/tenant-aware authorization checks on every controller action.
- **PHPUnit test suite** covering validation, routing, the permission-resolution engine, tenant-aware model CRUD, and the user-hierarchy authorization logic.

---

## 🧱 Tech Stack

| Layer       | Technology                                   |
|-------------|-----------------------------------------------|
| Language    | PHP 8.1+ (no framework — a small custom MVC core in `/core`) |
| Database    | MySQL 5.7+ / MariaDB 10.3+                    |
| Frontend    | Bootstrap 5, Bootstrap Icons, DataTables, vanilla JS |
| Testing     | PHPUnit 10                                    |
| Dependency management | Composer (dev-only; the app itself has zero runtime PHP dependencies) |

There is intentionally **no PHP framework dependency** (no Laravel/Symfony) — this keeps the codebase easy to read end-to-end, easy to deploy on a shared/basic Apache+MySQL host, and easy to extend module-by-module. See `DOCUMENTATION.md` for the architecture in depth.

---

## 📁 Project Structure

```
tuition-master/
├── app/
│   ├── controllers/     # One controller per module (UserController, ExamController, ...)
│   ├── models/           # One model per table, extending core/Model.php
│   └── views/            # Plain PHP views, grouped by module; layouts/main.php is the shell
├── config/
│   └── config.php        # Loads .env and defines app-wide constants
├── core/                 # The framework: Router, Controller, Model, Auth, Permission, Database, ...
├── database/
│   ├── schema.sql         # Full database schema
│   └── seed.sql           # System roles, permissions, menu items, default Super Admin, help articles
├── public/                # Web root — point Apache/Nginx here
│   ├── index.php          # Front controller / route table
│   └── assets/            # css/js/img + user uploads
├── tests/                 # PHPUnit test suite
├── composer.json
├── phpunit.xml
├── .env.example
├── DOCUMENTATION.md        # Full developer documentation
└── INSTALL_WINDOWS.md      # Step-by-step local Apache/XAMPP setup for Windows
```

---

## 🚀 Quick Start

### 1. Requirements
- PHP 8.1+ with `pdo_mysql`, `mbstring`, `fileinfo` extensions
- MySQL 5.7+ or MariaDB 10.3+
- Apache (with `mod_rewrite`) or PHP's built-in server
- Composer (only needed to install PHPUnit for running tests — the app itself needs no vendor packages to run)

### 2. Clone & configure
```bash
git clone <your-repo-url> tuition-master
cd tuition-master
cp .env.example .env
```
Edit `.env` and set your database credentials and `APP_URL`.

### 3. Create the database
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```
This creates the `tuition_master` database and a default **Super Admin** account:

| Username     | Password    |
|--------------|-------------|
| `superadmin` | `Admin@123` |

> ⚠️ The password hash shipped in `seed.sql` is a placeholder. After importing, generate a real hash and update the row (see step 4), or simply log in and change the password immediately from **My Profile**.

### 4. Generate the real Super Admin password hash
```bash
php -r "echo password_hash('Admin@123', PASSWORD_BCRYPT), PHP_EOL;"
```
Copy the output and run:
```sql
UPDATE users SET password = '<paste-hash-here>' WHERE username = 'superadmin';
```

### 5. Run it
Using PHP's built-in server (fastest way to try it locally):
```bash
php -S localhost:8000 -t public public/index.php
```
Then open **http://localhost:8000/login**.

For a full Apache/XAMPP setup on Windows (recommended for anything beyond a quick test), see **[INSTALL_WINDOWS.md](INSTALL_WINDOWS.md)**.

### 6. First login workflow
1. Log in as `superadmin`.
2. Go to **Tuition Centers → Add Tuition Center**. Fill in the center's details *and* its first Tuition Center Admin account in the same form.
3. Log out, log back in as that Tuition Center Admin.
4. From there, add Admin Staff, Teachers, Students, Parents, Classes, etc. — everything created is scoped to that tuition center only.

---

## 🧪 Running Tests

```bash
composer install
composer test
# or directly:
vendor/bin/phpunit --testdox
```

Tests that touch the database (`AuthTest`, `PermissionTest`, `ModelTest`) automatically **skip themselves** if no database connection is available, so `composer test` still runs the pure-logic tests (`ValidatorTest`, `HelpersTest`, `RouterTest`) in any environment. For the DB-backed tests to run, make sure `.env` points at a real database with the schema imported (a disposable/dev database is fine — the tests create and clean up their own fixture rows).

See **DOCUMENTATION.md → Testing** for details on what's covered and how to add new tests.

---

## 🔒 Security Notes

- All queries use PDO prepared statements.
- Passwords are hashed with `password_hash()` (bcrypt).
- Every state-changing form includes a CSRF token, verified server-side.
- Every controller action calls `Permission::authorize()` before touching data.
- Tenant isolation is enforced at the model layer (`core/Model.php`), not just in controllers, so a bug in one controller can't leak cross-tenant data.
- File uploads are validated by extension and size, renamed to random filenames, and stored outside of directly-guessable paths.

---

## 📄 License

MIT — do whatever you like with it, no warranty provided. See `DOCUMENTATION.md` for architectural details before making major changes.

---

## 📚 Further Reading

- **[DOCUMENTATION.md](DOCUMENTATION.md)** — architecture, module-by-module reference, how to add a new module, database schema reference, permission system deep-dive, testing guide.
- **[INSTALL_WINDOWS.md](INSTALL_WINDOWS.md)** — installing and running on a local Apache server on Windows (XAMPP), step by step, with exact commands.
