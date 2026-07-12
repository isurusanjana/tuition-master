# Installing & Running Tuition Master on Windows (Local Apache / XAMPP)

This guide walks through running Tuition Master on a local Windows machine using **XAMPP** (Apache + MySQL/MariaDB + PHP bundled together). Every command below is exact — copy/paste as-is.

---

## 1. Install XAMPP

1. Download XAMPP for Windows (PHP 8.1 or newer) from **https://www.apachefriends.org/**.
2. Run the installer. Accept the defaults (install to `C:\xampp`).
3. Make sure the **Apache** and **MySQL** components are selected during install.
4. Launch **XAMPP Control Panel** (Start Menu → XAMPP → XAMPP Control Panel).
5. Click **Start** next to both **Apache** and **MySQL**. Both rows should turn green.

If Apache fails to start because port 80 is in use (common if Skype or IIS is running), change Apache's port:
- In XAMPP Control Panel, click **Config → Apache (httpd.conf)**, change `Listen 80` to `Listen 8080`, and also update `ServerName localhost:80` to `ServerName localhost:8080`.
- You'll then access the app at `http://localhost:8080/...` instead of `http://localhost/...` everywhere below.

---

## 2. Get the project into XAMPP's web folder

Copy (or `git clone`) the entire `tuition-master` project folder into XAMPP's `htdocs` directory, so you end up with:

```
C:\xampp\htdocs\tuition-master\
    app\
    config\
    core\
    database\
    public\
    tests\
    ...
```

If you're using Git, open **Command Prompt** and run:

```bat
cd C:\xampp\htdocs
git clone <your-repo-url> tuition-master
```

Or if you were given a ZIP file, extract it directly into `C:\xampp\htdocs\` and rename the extracted folder to `tuition-master` if needed.

---

## 3. Create the database

1. Open **phpMyAdmin**: with Apache/MySQL running, go to `http://localhost/phpmyadmin` in your browser.
2. Click **New** in the left sidebar, name the database `tuition_master`, choose collation `utf8mb4_unicode_ci`, click **Create**. *(The schema script also creates it if it doesn't exist, so this step is optional — but it confirms MySQL is reachable.)*
3. Click the **Import** tab (with `tuition_master` selected on the left), choose **Choose File**, select:
   ```
   C:\xampp\htdocs\tuition-master\database\schema.sql
   ```
   Click **Go**. Wait for "Import has been successfully finished".
4. Repeat **Import** for the seed data file:
   ```
   C:\xampp\htdocs\tuition-master\database\seed.sql
   ```
   Click **Go**.

### Or via the command line instead of phpMyAdmin

Open **Command Prompt** and run (default XAMPP MySQL has no root password):

```bat
cd C:\xampp\htdocs\tuition-master
C:\xampp\mysql\bin\mysql -u root < database\schema.sql
C:\xampp\mysql\bin\mysql -u root < database\seed.sql
```

---

## 4. Generate a real password hash for the Super Admin account

The seed file ships a placeholder password hash. Generate a real one and update it:

```bat
cd C:\xampp\htdocs\tuition-master
C:\xampp\php\php.exe -r "echo password_hash('Admin@123', PASSWORD_BCRYPT), PHP_EOL;"
```

This prints something like:
```
$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOPQRSTUVWXYZ0123
```

Copy that full string, then open phpMyAdmin → `tuition_master` database → **SQL** tab, and run (replacing `<paste-hash-here>` with the copied string, keeping the quotes):

```sql
UPDATE users SET password = '<paste-hash-here>' WHERE username = 'superadmin';
```

Click **Go**. You can now log in with username `superadmin` and password `Admin@123` (change it immediately after logging in, from **My Profile**).

---

## 5. Configure the `.env` file

In `C:\xampp\htdocs\tuition-master\`, copy `.env.example` to `.env`:

```bat
cd C:\xampp\htdocs\tuition-master
copy .env.example .env
```

Open `.env` in Notepad and confirm/edit these values for a default XAMPP setup:

```
APP_NAME="Tuition Master"
APP_URL=http://localhost/tuition-master/public
APP_ENV=local
APP_DEBUG=true
APP_KEY=change-this-to-a-random-32-char-string

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=tuition_master
DB_USER=root
DB_PASS=
```

> Default XAMPP MySQL has an empty root password, so leave `DB_PASS=` blank. If you've set a MySQL root password, put it after `DB_PASS=`.

---

## 6. Open the app

With Apache and MySQL both running in XAMPP Control Panel, open your browser to:

```
http://localhost/tuition-master/public
```

You should see the Tuition Master login page. Log in with:

- **Username:** `superadmin`
- **Password:** `Admin@123`

---

## 7. (Optional) Clean URL without `/public`

By default you access the app at `http://localhost/tuition-master/public/...`. If you'd rather it be reachable at `http://localhost/tuition-master/...` (no `/public`), set up an Apache Virtual Host pointing directly at the `public` folder:

1. Open `C:\xampp\apache\conf\extra\httpd-vhosts.conf` in Notepad (as Administrator) and add:

   ```apache
   <VirtualHost *:80>
       ServerName tuitionmaster.local
       DocumentRoot "C:/xampp/htdocs/tuition-master/public"
       <Directory "C:/xampp/htdocs/tuition-master/public">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

2. Make sure `Include conf/extra/httpd-vhosts.conf` is uncommented in `C:\xampp\apache\conf\httpd.conf`.
3. Edit your hosts file: open **Notepad as Administrator**, open `C:\Windows\System32\drivers\etc\hosts`, add this line:
   ```
   127.0.0.1   tuitionmaster.local
   ```
4. Update `.env`: `APP_URL=http://tuitionmaster.local`
5. Restart Apache in XAMPP Control Panel (Stop, then Start).
6. Visit `http://tuitionmaster.local` in your browser.

---

## 8. Enable `mod_rewrite` (needed for clean URLs)

XAMPP enables `mod_rewrite` by default, but if routes 404 unexpectedly, confirm it's on:

1. Open `C:\xampp\apache\conf\httpd.conf` in Notepad.
2. Find the line `#LoadModule rewrite_module modules/mod_rewrite.so` and remove the leading `#` if present.
3. Also confirm your `<Directory>` block for `htdocs` (or your vhost, if you set one up in step 7) has `AllowOverride All` — this lets the project's `public/.htaccess` take effect.
4. Restart Apache.

---

## 9. Running the test suite on Windows

Install [Composer for Windows](https://getcomposer.org/Composer-Setup.exe) first, then:

```bat
cd C:\xampp\htdocs\tuition-master
composer install
composer test
```

This installs PHPUnit into `vendor\` and runs the full suite. Database-backed tests will run automatically since `.env` is already configured against your local MySQL from step 5.

---

## 10. Common Windows/XAMPP Troubleshooting

| Symptom | Fix |
|---|---|
| Apache won't start, port 80 in use | Change Apache's port as described in Step 1, or stop the conflicting service (Skype, IIS, `World Wide Web Publishing Service` in Windows Services). |
| "Database connection failed" on the login page | Confirm MySQL is running (green in XAMPP Control Panel) and `.env`'s `DB_HOST`/`DB_USER`/`DB_PASS`/`DB_NAME` are correct. |
| Blank white page | Set `APP_DEBUG=true` in `.env` temporarily to see the underlying PHP error, and check `C:\xampp\apache\logs\error.log`. |
| File upload fails for lessons/logos | Check that `C:\xampp\htdocs\tuition-master\public\assets\uploads\` exists and isn't blocked by antivirus/Windows permissions; also check PHP's `upload_max_filesize` and `post_max_size` in `C:\xampp\php\php.ini` (defaults are usually fine for documents, but raise them for large videos, then restart Apache). |
| 404 on every page except the homepage | `mod_rewrite` isn't active or `AllowOverride All` is missing — see Step 8. |
| Login works but styling looks broken | You're likely offline — the UI loads Bootstrap/DataTables from a CDN by default; check your internet connection, or swap the `<link>`/`<script>` tags in `app/views/layouts/main.php` for local copies if you need a fully offline environment. |

---

You're set up! Head back to `README.md` → *"First login workflow"* to create your first tuition center.
