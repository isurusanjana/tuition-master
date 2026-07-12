# Tuition Master — Developer Documentation

This document explains how the application is built, so you can confidently extend it. It assumes you've already read the Quick Start in `README.md`.

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Request Lifecycle](#2-request-lifecycle)
3. [Multi-Tenancy & Data Isolation](#3-multi-tenancy--data-isolation)
4. [Authentication](#4-authentication)
5. [The Permission System](#5-the-permission-system)
6. [The User Hierarchy](#6-the-user-hierarchy)
7. [Database Schema Reference](#7-database-schema-reference)
8. [Core Classes Reference](#8-core-classes-reference)
9. [Adding a New Module (Step-by-Step)](#9-adding-a-new-module-step-by-step)
10. [Frontend / Theming](#10-frontend--theming)
11. [File Uploads](#11-file-uploads)
12. [Testing](#12-testing)
13. [Deploying to a Production Server](#13-deploying-to-a-production-server)
14. [Coding Conventions](#14-coding-conventions)

---

## 1. Architecture Overview

Tuition Master is a **plain-PHP MVC application** — there is no framework dependency. This is deliberate: the whole request pipeline (`public/index.php` → `Router` → `Controller` → `Model` → `View`) is under 300 lines total and can be read in one sitting, which matters for a project meant to be extended by many different developers over time.

```
Browser
   │
   ▼
public/index.php   ← front controller: loads config, autoloader, session, route table
   │
   ▼
core/Router.php    ← matches METHOD + path against registered routes, extracts {params}
   │
   ▼
app/controllers/*  ← extends core/Controller.php; calls Permission::authorize(), then
   │                  talks to one or more Models, then renders a View
   ▼
app/models/*       ← extends core/Model.php; talks to core/Database.php (PDO)
   │
   ▼
app/views/*        ← plain PHP templates; layouts/main.php is the shared shell
```

Nothing is "magic": there's no dependency injection container, no ORM query builder beyond what `core/Model.php` provides, and no template compiler. Views are plain `.php` files using `<?= e($value) ?>` for escaping.

---

## 2. Request Lifecycle

1. Apache (via `.htaccess`) or the PHP built-in server routes every request to `public/index.php`.
2. `config/config.php` loads `.env` and defines constants (`APP_URL`, `DB_HOST`, etc.).
3. A `spl_autoload_register` callback resolves class names to files under `core/` and `app/models/` (controllers are `require_once`'d directly by the Router when a matching route is found).
4. `Session::start()` starts (or resumes) the PHP session.
5. The full route table is registered against a `Router` instance (see `public/index.php`).
6. `$router->dispatch($method, $uri)` matches the request, instantiates the target controller, and calls the target method with any `{param}` values extracted from the URL.
7. `core/Controller.php`'s constructor enforces `Auth::check()` for any controller with `$requiresAuth = true` (the default — only `AuthController` opts out).
8. The controller method typically:
   - calls `$this->authorize('module_key', 'action')` (wraps `Permission::authorize()`),
   - validates input with `core/Validator.php`,
   - talks to one or more models,
   - and either redirects (`Response::redirect()`) or renders a view (`View::render()`).
9. `View::render()` captures the view's output into `$content`, then includes `layouts/main.php`, which prints the sidebar/topbar/menu (built from `Permission::menus()`) and injects `$content`.

---

## 3. Multi-Tenancy & Data Isolation

Every tuition center is a row in `tuition_centers`. Every other table that holds tenant data has a `tuition_center_id` column (nullable only for the Super Admin's own account and for system-wide roles/menus/permissions, which are shared across tenants).

**Isolation is enforced at the model layer**, not just in controllers, specifically in `core/Model.php`:

```php
protected bool $tenantScoped = true; // default for all models

public function find(int $id) {
    // automatically adds "AND tuition_center_id = :cid" using Auth::centerId(),
    // UNLESS the logged-in user is the Super Admin (Auth::centerId() === null for super admin)
}
```

`Auth::centerId()` returns:
- `null` if the logged-in user is the Super Admin (no restriction — sees everything),
- the user's own `tuition_center_id` otherwise.

This means: **even if a controller forgets to filter by tenant, the model layer still won't leak another tenant's rows**, because every `find()`, `all()`, `create()`, `update()`, and `delete()` call in `core/Model.php` applies the tenant filter automatically for any model with `$tenantScoped = true` (the default).

A few models intentionally set `$tenantScoped = false` because their table has no `tuition_center_id` column of its own (e.g. `Mark`, which is scoped indirectly through its parent `Exam`) — in those cases the *controller* is responsible for checking that the parent record belongs to the current tenant before acting (see `MarksController` for the pattern).

---

## 4. Authentication

`core/Auth.php` is a thin wrapper around the session plus a couple of DB lookups:

- `Auth::attempt($username, $password)` — looks up the user by username or email, verifies the password with `password_verify()`, and on success stores the full user row (joined with role info) in the session.
- `Auth::user()` / `Auth::id()` / `Auth::centerId()` / `Auth::roleSlug()` / `Auth::roleLevel()` — cheap accessors reading from the session.
- `Auth::isSuperAdmin()` — true only when the logged-in user's role slug is `super_admin`.

Passwords are hashed with `password_hash(..., PASSWORD_BCRYPT)`. There is no password-reset email flow wired up out of the box (`AuthController::forgot()` just shows a confirmation message) — wire up your own mailer if you need that in production.

---

## 5. The Permission System

There are **two layers**, resolved in this order (first match wins):

1. **User-level override** (`user_menu_permission` / `user_permission` tables) — set individually per user from *Users → Permissions*.
2. **Role-level default** (`role_menu` / `role_permission` tables) — set per role from *Roles & Permissions → Access*.

Both layers cover two independent concerns:

| Concern | Tables | Checked via |
|---|---|---|
| **Which menu items appear in the sidebar** | `menu_items`, `role_menu`, `user_menu_permission` | `Permission::menus()` |
| **Which CRUD actions are allowed on a page** | `permissions`, `role_permission`, `user_permission` | `Permission::can($moduleKey, $action)` |

`core/Permission.php`:

```php
Permission::can('exams', 'add');       // true/false
Permission::authorize('exams', 'add'); // renders a 403 page and exit()s if false
```

Every controller action that mutates or exposes data calls `$this->authorize($module, $action)` (a small wrapper in `core/Controller.php`) as its very first line. Views also call `Permission::can(...)` directly to decide whether to render an "Edit"/"Delete" button at all.

**Super Admin bypasses all permission checks** (`Auth::isSuperAdmin()` short-circuits both `can()` and `authorize()`).

### Caching

`Permission::can()` and `Permission::menus()` cache their results in static arrays for the lifetime of the request (this matters nothing in production, since each HTTP request is a fresh PHP process — but matters for tests / long-running CLI scripts that check permissions, then modify permissions, then check again). Call `Permission::clearCache()` after writing to the permission tables if you need fresh results within the same PHP process (already done in `UserController::savePermissions()` and `RoleController::saveAccess()`).

---

## 6. The User Hierarchy

Every user row has a `parent_user_id` pointing at the user who created them:

```
Super Admin
 └─ Center Admin (created by Super Admin when the tuition center was onboarded)
     ├─ Admin Staff (created by Center Admin)
     ├─ Teacher       (created by Center Admin or Admin Staff)
     │   └─ (assigned to Classes, not "owned" via parent_user_id)
     ├─ Student       (created by Center Admin, Admin Staff, or a Teacher)
     └─ Parent        (created similarly; linked to Student(s) via student_parent table)
```

`Auth::canManageUser(array $targetUserRow): bool` walks up the `parent_user_id` chain to decide whether the logged-in user is allowed to view/edit/delete a given target user. The rule:

1. Super Admin can manage anyone.
2. The target must belong to the same tuition center.
3. The target's role must have a **strictly lower authority level** (`roles.level`, where 0 = highest authority) than the logged-in user's own role — you cannot manage a peer or a superior.
4. The target must be a descendant of the logged-in user in the `parent_user_id` chain (`Auth::isDescendantOf()`).

`Auth::subordinateIds($userId)` returns the full flattened list of user IDs under a given user — used to scope "which students can I assign this lesson/exam to" dropdowns.

**This hierarchy check is separate from tenant isolation** — tenant isolation says "you can only ever see rows in your own tuition center"; the hierarchy check further restricts *which users within that tenant* you can manage. A Center Admin can manage everyone in their center (their role level is high enough and everyone else in the center is, transitively, under them); a Teacher can only manage the students/parents they (or someone under them) directly created.

---

## 7. Database Schema Reference

Full DDL lives in `database/schema.sql`, seed data (system roles, permissions, menu items, default Super Admin, sample help articles) in `database/seed.sql`. Key tables at a glance:

| Table | Purpose |
|---|---|
| `tuition_centers` | One row per tenant |
| `roles` | System roles (`tuition_center_id IS NULL`) + custom per-tenant roles |
| `users` | All user types; `role_id`, `tuition_center_id`, `parent_user_id` drive isolation/hierarchy |
| `student_parent` | Many-to-many link between student and parent users |
| `staff_profiles` | Employment info (salary, designation) used by Payroll |
| `menu_items`, `permissions` | Catalogues of assignable menu items / CRUD actions |
| `role_menu`, `role_permission` | Role-level defaults |
| `user_menu_permission`, `user_permission` | Per-user overrides |
| `classes`, `class_teacher`, `class_student` | Classes and their teacher/student assignments |
| `attendance`, `staff_attendance` | Per-class-per-student and per-staff attendance records |
| `exams`, `exam_assignments`, `marks` | Exams, optional per-student assignment, and recorded marks |
| `notes` | Special notes (general/behavioral/academic/medical) |
| `lessons`, `lesson_assignments` | Lesson resources and their per-user assignment |
| `payroll` | Monthly payroll entries per staff member |
| `inventory_items`, `inventory_transactions` | Stock items and stock-in/out history |
| `notifications`, `notification_reads` | Header notification messages + per-user read state |
| `settings` | Key/value store for theme/branding, scoped per tenant (or global when `tuition_center_id IS NULL`) |
| `help_articles` | Contextual help content per module |
| `activity_logs` | Lightweight audit trail (`log_activity()` helper) |

All foreign keys use `ON DELETE CASCADE` where a child record has no meaning without its parent (e.g. deleting a tuition center cascades to its users, classes, etc.) — be careful with delete operations in production; consider a "soft delete"/archive flag instead of hard deletes if you need to retain historical data.

---

## 8. Core Classes Reference

| Class | Responsibility |
|---|---|
| `core/Database.php` | PDO singleton + `query()`/`fetchAll()`/`fetchOne()` helpers. Always use named parameters; PDO emulation is disabled, so **placeholder names must be unique per query** even when the same value is used twice (see note below). |
| `core/Model.php` | Base CRUD (`find`, `all`, `count`, `create`, `update`, `delete`) with automatic tenant scoping and a `fillable` allow-list per model. |
| `core/Router.php` | Minimal route matcher (`{param}` segments), plus `url($name, $params)` for reverse-routing (exposed to views as the `route()` helper). |
| `core/Controller.php` | Base class: enforces auth, exposes `$this->authorize()`, `$this->validateCsrf()`, `$this->guardUser()`. |
| `core/Auth.php` | Login/logout, session-backed current-user accessors, tenant + hierarchy authorization checks. |
| `core/Permission.php` | Resolves effective menu list and CRUD permissions (role default + user override). |
| `core/Session.php` | Thin `$_SESSION` wrapper + CSRF token generation/verification + flash messages. |
| `core/Validator.php` | Fluent validation (`required`, `email`, `min`, `numeric`, `unique`) collecting field-keyed error arrays. |
| `core/FileUpload.php` | Extension/size-validated upload handling with randomized filenames. |
| `core/View.php` | Renders a view file, capturing output into `$content` for the layout. |
| `core/Helpers.php` | Global helper functions: `e()`, `url()`, `route()`, `asset()`, `old()`, `csrf_field()`, `flash_message()`, `format_date()`, `setting()`, `log_activity()`, `redirect_with_success()/error()`. |

> **PDO placeholder gotcha:** because `PDO::ATTR_EMULATE_PREPARES` is `false`, you **cannot reuse the same named placeholder twice in one query** (e.g. `WHERE username = :u OR email = :u` will throw `SQLSTATE[HY093]`). Always use distinct names (`:u1`, `:u2`) and pass both in the params array, even when the value is identical. This is covered by `tests/AuthTest.php` and was caught during development — see `core/Auth.php::attempt()` and `app/models/User.php` for the corrected pattern.

---

## 9. Adding a New Module (Step-by-Step)

Say you want to add a **"Fee Payments"** module. Here's the full checklist, following the existing pattern (e.g. copy `InventoryController`/`InventoryItem` as a template):

1. **Schema**: add a `fee_payments` table to `database/schema.sql` with a `tuition_center_id` column and appropriate foreign keys.
2. **Seed data**: add rows to `permissions` (`fee_payments` / `view`, `add`, `edit`, `delete`) and `menu_items` in `database/seed.sql`, then grant them to whichever roles should have access by default (`role_menu` / `role_permission`).
3. **Model**: `app/models/FeePayment.php` extending `core/Model.php`:
   ```php
   class FeePayment extends Model {
       protected string $table = 'fee_payments';
       protected array $fillable = ['tuition_center_id','student_id','amount','paid_at','method'];
   }
   ```
4. **Controller**: `app/controllers/FeePaymentController.php` extending `core/Controller.php`. Every action should start with `$this->authorize('fee_payments', '<action>')`. Follow the CRUD pattern in `PayrollController.php`.
5. **Routes**: register `GET/POST /fee-payments...` routes in `public/index.php`.
6. **Views**: `app/views/fee_payments/{index,create,edit}.php` — copy the structure from `app/views/payroll/*.php` (Bootstrap card + `datatable` class for the list table).
7. **Tests**: add a `tests/FeePaymentTest.php` extending `DatabaseTestCase` following the pattern in `tests/ModelTest.php`.

That's the entire surface area — there's no service container registration, no route caching to bust, no ORM migration DSL to learn.

---

## 10. Frontend / Theming

- Bootstrap 5 + Bootstrap Icons + DataTables are loaded from CDN in `app/views/layouts/main.php` (swap for local copies if you need to run fully offline).
- All themeable colors are CSS custom properties (`--tm-primary`, `--tm-sidebar`, etc.) defined inline in `layouts/main.php` from the `settings` table (see `setting()` helper) and consumed throughout `public/assets/css/custom.css`.
- **Menu orientation** (`vertical` sidebar vs `horizontal` top menu) is a single setting (`menu_orientation`) read once in `layouts/main.php`, which conditionally renders `app/views/layouts/menu_items.php` inside either `<aside class="tm-sidebar">` or `<nav class="tm-menu-horizontal">`.
- Settings are stored per-tenant in the `settings` table (`tuition_center_id` = the tenant, or `NULL` for the Super Admin's global defaults, which act as a fallback when a tenant hasn't customized a given key — see `setting()` in `core/Helpers.php`).
- `public/assets/js/app.js` wires up: mobile sidebar toggle, DataTables auto-init for any `<table class="datatable">`, `data-confirm="..."` delete-form confirmations, and a live color-preview hook (`data-preview-var`) used on the Theme Settings page.

---

## 11. File Uploads

`core/FileUpload.php::upload($file, $subDir, $allowedExtensions, $maxMb)`:
- Validates the extension against an allow-list (different per resource type — see `LessonController`),
- validates size,
- writes to `public/assets/uploads/<subDir>/<random-hex-filename>.<ext>` (never the original filename, to avoid path traversal / overwrite issues),
- returns the relative path to store in the DB (e.g. `lessons/ab12cd34....pdf`), or `null` on failure.

`FileUpload::delete($relativePath)` removes a previously-uploaded file (called when replacing/deleting a lesson resource or a tuition center/user logo).

---

## 12. Testing

The suite lives in `tests/` and uses PHPUnit. Install it via Composer:

```bash
composer install
composer test
```

### What's covered

| File | Focus |
|---|---|
| `tests/ValidatorTest.php` | `core/Validator.php` rules (`required`, `email`, `min`, `numeric`, chaining) — pure logic, no DB. |
| `tests/HelpersTest.php` | `e()`, `url()`, `asset()`, `format_date()` — pure logic, no DB. |
| `tests/RouterTest.php` | Named-route URL generation, multi-param routes — pure logic, no DB. |
| `tests/AuthTest.php` | `Auth::attempt()` success/failure, and the user-hierarchy authorization logic (`Auth::canManageUser`, `Auth::subordinateIds`) — **requires DB**. |
| `tests/PermissionTest.php` | Role-default vs. user-override resolution order in `Permission::can()` — **requires DB**. |
| `tests/ModelTest.php` | Base `Model` CRUD + the `InventoryTransaction` stock-adjustment logic — **requires DB**. |

### Database-backed tests

`tests/DatabaseTestCase.php` is the base class for anything that needs MySQL. It attempts a connection in `setUpBeforeClass()`; if that fails, **every test in the class is automatically marked skipped** rather than failing the whole suite — so `composer test` is safe to run even without a configured database (useful in a plain CI job that only lints/checks pure logic).

To actually exercise the DB-backed tests:
1. Make sure `.env` points at a real, reachable database with `database/schema.sql` and `database/seed.sql` imported.
2. Run `composer test`. Each DB test creates its own isolated fixture rows (a throwaway tuition center + user(s) with a unique code, in `setUp()`) and deletes them again in `tearDown()`, so it's safe to run against a shared dev database — it will not touch your existing data, only rows it created itself.

### Writing new tests

- Pure logic (no DB) → extend `PHPUnit\Framework\TestCase` directly, like `ValidatorTest`.
- Anything touching `Database::`, `Auth::`, or a `Model` subclass → extend `DatabaseTestCase`, create your own fixture data in `setUp()`, and delete it in `tearDown()`. Use a unique identifier (`substr(uniqid(), -8)`) in any `UNIQUE`-constrained column (emails, usernames, tuition center codes) so parallel/rerun test runs never collide.
- If your test checks a `Permission::can()` result before *and* after modifying `role_permission`/`user_permission` rows, call `Permission::clearCache()` in between — see `PermissionTest::testUserLevelOverrideGrantsExtraPermission()`.

---

## 13. Deploying to a Production Server

1. Point your web server's document root at `public/` (not the project root) — everything outside `public/` is deliberately unreachable from the web for security.
2. Set `APP_DEBUG=false` and `APP_ENV=production` in `.env` on the server (this suppresses PHP error display and generic-izes the DB-connection-failure message).
3. Set a real, random `APP_KEY` in `.env` (reserved for future use — e.g. if you add encrypted cookie values).
4. Make sure `public/assets/uploads/` is writable by the web server user.
5. Import `database/schema.sql` then `database/seed.sql` against your production database, and immediately change the Super Admin password (see README Quick Start step 4).
6. Enable HTTPS and set `Session` cookies to `Secure` (edit `core/Session.php`'s `session_start()` options) once you're behind TLS.
7. Consider putting a process supervisor / cron in front of `activity_logs` growth (archive or prune periodically) if you expect heavy usage.

For local Windows/Apache/XAMPP setup instructions, see **INSTALL_WINDOWS.md**.

---

## 14. Coding Conventions

- **Controllers** never write raw SQL directly for data that belongs to a model — put queries in the model, keep controllers focused on validation + authorization + orchestration.
- **Every** state-changing controller method must call `$this->validateCsrf()` and `$this->authorize($module, $action)`.
- **Every** SQL query uses named parameters via `Database::query()`/`fetchAll()`/`fetchOne()` — never interpolate user input into SQL strings.
- **Views** never query the database directly except for small, view-only read helpers already established in existing views (e.g. `Permission::can()` for button visibility) — fetch everything the view needs in the controller and pass it as view data.
- Escape all output with `e()` unless you explicitly intend to output raw HTML (e.g. `nl2br(e($content))`).
