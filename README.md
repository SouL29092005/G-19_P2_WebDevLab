# Laboratory Computer Reservation System

A PHP application served by Apache, with PostgreSQL for data storage. XAMPP's bundled MariaDB/MySQL is not used. These instructions walk through getting the source from GitHub and running it locally on Windows with XAMPP and PostgreSQL.

The configured local URL is **http://lab-reservation.local:8080/**. Apache must serve the project's `public` directory as its document root. Do not point the web root at the repository root.

## Requirements

- Windows 10 or 11.
- Git, or a browser to download the repository as a ZIP file.
- XAMPP with Apache and PHP 8.1 or newer.
- PostgreSQL 14 or newer, installed separately from XAMPP. PostgreSQL's installer can also install pgAdmin.
- The XAMPP PHP `pdo_pgsql` and `pgsql` extensions enabled.

## 1. Download the project

Install XAMPP using its default directory, `C:\xampp`. If you choose a different directory, use that actual directory in the Apache configuration later.

The recommended project location is:

```text
C:\xampp\htdocs\G-19_P2_WebDevLab
```

### Option A: Clone with Git

Open PowerShell and run the following from the XAMPP `htdocs` directory. Replace the example GitHub URL with the repository's URL from its GitHub page.

```powershell
cd C:\xampp\htdocs
git clone https://github.com/OWNER/REPOSITORY.git G-19_P2_WebDevLab
```

### Option B: Download a ZIP

1. Open the repository on GitHub.
2. Select **Code**, then **Download ZIP**.
3. Extract the ZIP into `C:\xampp\htdocs`.
4. If the extracted folder has a generated name, rename it to `G-19_P2_WebDevLab`.

Check that this file exists:

```text
C:\xampp\htdocs\G-19_P2_WebDevLab\README.md
```

Avoid an extra nested directory such as `C:\xampp\htdocs\G-19_P2_WebDevLab\REPOSITORY-main\README.md`; if the files are nested, move the repository contents up one directory.

## 2. Install and start PostgreSQL

XAMPP does not provide PostgreSQL. Install PostgreSQL separately using the Windows installer from the PostgreSQL website. During setup, remember the password for the PostgreSQL user (often `postgres`) and keep the default port `5432` unless another PostgreSQL server already uses it. You can manage the server with pgAdmin or use `psql` from the PostgreSQL installation's `bin` directory.

Start the PostgreSQL Windows service from the installer, Windows Services, or pgAdmin. Do not start XAMPP's MySQL module for this application; it is not used.

## 3. Create the database and load the schema

Open PowerShell and go to the repository root:

```powershell
cd C:\xampp\htdocs\G-19_P2_WebDevLab
```

Create a database named `lab_reservation`. If PostgreSQL's `bin` directory is in your `PATH`, run:

```powershell
createdb -U postgres lab_reservation
```

Otherwise, use the installed PostgreSQL version's full executable path. For example, change `16` if your installed PostgreSQL version differs:

```powershell
& "C:\Program Files\PostgreSQL\16\bin\createdb.exe" -U postgres lab_reservation
```

Enter the PostgreSQL password if prompted. Then load the schema and sample data:

```powershell
psql -U postgres -d lab_reservation -v ON_ERROR_STOP=1 -f database/schema.sql
psql -U postgres -d lab_reservation -v ON_ERROR_STOP=1 -f database/seed.sql
```

If `psql` is not in `PATH`, use its full path in the same way, for example:

```powershell
& "C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -d lab_reservation -v ON_ERROR_STOP=1 -f database/schema.sql
& "C:\Program Files\PostgreSQL\16\bin\psql.exe" -U postgres -d lab_reservation -v ON_ERROR_STOP=1 -f database/seed.sql
```

The schema creates the application tables and booking indexes. The seed adds computers and all 28 half-hour slots, and enables PostgreSQL's `pgcrypto` extension. The database account running the seed must be allowed to create that extension.

For a fresh database, use `schema.sql` and `seed.sql` as shown. Do not apply the upgrade migration to a fresh database. If you already have an older copy of this database and its `bookings` table lacks the booking-group fields, apply the migration instead:

```powershell
psql -U postgres -d lab_reservation -v ON_ERROR_STOP=1 -f database/migrations/20261027_add_booking_group.sql
```

### Create an administrator account

Create an administrator account with an email address and password you control before signing in to the admin area. The password is stored as a bcrypt hash, which is compatible with the application's login system. Do not reuse the example values below; replace all three values with your own name, email, and a long, unique password before running the query.

In **pgAdmin 4**:

1. Connect to your PostgreSQL server.
2. In the Browser tree, expand **Databases** and select `lab_reservation`.
3. Right-click `lab_reservation` and choose **Query Tool**. Make sure the Query Tool is connected to `lab_reservation`, not the default `postgres` database.
4. Paste the SQL below, replace the example values, and click **Execute/Refresh** (the lightning icon) or press **F5**.
5. Confirm the result says `INSERT 0 1`.

```sql
CREATE EXTENSION IF NOT EXISTS pgcrypto;

INSERT INTO users (full_name, email, password_hash, role)
VALUES (
    'Your Admin Name',
    'your-admin-email@example.com',
    crypt('ReplaceWithYourOwnLongUniquePassword', gen_salt('bf')),
    'admin'
);
```

Run this insert once and use an email address that is not already registered. Keep the password private; do not put it in this README or commit it to Git. If PostgreSQL reports that you do not have permission to create the extension, connect to pgAdmin as the database owner (or ask the database administrator to enable `pgcrypto`) and run the query again.

You can run the same SQL from the PostgreSQL command line instead: connect to the target database with `psql -U postgres -d lab_reservation`, paste the SQL, press Enter, then type `\q` to exit. If `psql` is not in `PATH`, run the `psql.exe` from your PostgreSQL installation's `bin` folder.

## 4. Configure the database credentials

Create a file named `.env` inside the repository's `config` folder:

```text
C:\xampp\htdocs\G-19_P2_WebDevLab\config\.env
```

Put your PostgreSQL connection settings in that file. Replace the password with the one you chose when installing PostgreSQL:

```dotenv
DB_HOST=localhost
DB_PORT=5432
DB_NAME=lab_reservation
DB_USER=postgres
DB_PASSWORD="your_postgres_password"
```

Save the file as `config/.env`, not `.env.txt`. In Windows Notepad, choose **Save as type: All files** if needed. This file contains a password and is excluded from Git; do not commit or share it.

## 5. Enable PostgreSQL in XAMPP PHP

Apache's PHP must have the PostgreSQL PDO driver enabled. Edit:

```text
C:\xampp\php\php.ini
```

The easiest way to open the correct file is XAMPP Control Panel > **Apache** > **Config** > **PHP (php.ini)**. Find the PostgreSQL extension lines and remove the leading semicolon if present:

```ini
extension=pdo_pgsql
extension=pgsql
```

Save `php.ini`. Confirm the matching DLL files, typically `php_pdo_pgsql.dll` and `php_pgsql.dll`, exist in `C:\xampp\php\ext`. In XAMPP Control Panel, stop and restart Apache after changing PHP configuration.

To check the XAMPP PHP CLI, run:

```powershell
C:\xampp\php\php.exe -m
```

The module list should include `PDO`, `pdo_pgsql`, and `pgsql`. The browser uses Apache's PHP configuration, so the XAMPP Apache `php.ini` must be the one you edited.

## 6. Configure Apache and the local URL

The application has `APP_BASE_URL` set to `http://lab-reservation.local:8080` in `config/app.php`. Keep this address unless you also change that constant and update the Apache/hosts configuration to match.

### Enable Apache modules and the virtual-host file

In XAMPP Control Panel, open Apache **Config** > **Apache (httpd.conf)**. In `C:\xampp\apache\conf\httpd.conf`, verify these lines are enabled (not preceded by `#`):

```apache
LoadModule rewrite_module modules/mod_rewrite.so
Include conf/extra/httpd-vhosts.conf
```

Apache must listen on port `8080`. If `Listen 8080` is not already present, add it near the existing `Listen 80` line. Do not add a duplicate listener. If another program uses port `8080`, stop that program or choose another port and update both the Apache and `APP_BASE_URL` settings consistently.

Open `C:\xampp\apache\conf\extra\httpd-vhosts.conf` and add this virtual host at the end. The paths below assume the recommended project location:

```apache
<VirtualHost *:8080>
    ServerName lab-reservation.local
    DocumentRoot "C:/xampp/htdocs/G-19_P2_WebDevLab/public"

    <Directory "C:/xampp/htdocs/G-19_P2_WebDevLab/public">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

If you put the repository somewhere else, replace both directory paths with the actual path to its `public` folder. The document root must end in `G-19_P2_WebDevLab/public`, not just `G-19_P2_WebDevLab`.

### Add the local host name

Open Notepad as Administrator. Open this file (you may need to change the file type selector to **All files**):

```text
C:\Windows\System32\drivers\etc\hosts
```

Add this line and save:

```text
127.0.0.1 lab-reservation.local
```

Restart Apache from the XAMPP Control Panel after saving the Apache configuration. If Apache fails to start, check the XAMPP log and confirm port `8080` is not already in use and `Listen 8080` is not duplicated.

## 7. Start the website

1. Start the PostgreSQL service.
2. Start Apache in the XAMPP Control Panel. The MySQL module can remain stopped.
3. Open this URL in a browser:

   **http://lab-reservation.local:8080/**

4. Register a student account to use student booking features. To manage the system, sign in with the administrator account you created in step 3.

## Main pages

- `/` - home page
- `/register.php` - student registration
- `/login.php` - sign in
- `/dashboard.php` - student dashboard
- `/new-booking.php` - select a date, multiple time slots, and an available computer
- `/confirm-booking.php` - review and confirm a temporary two-minute hold
- `/my-bookings.php` - view or cancel bookings
- `/availability` - public computer availability
- `/admin/` - administrator dashboard

The PHP files in `public` are served directly by Apache. `public/.htaccess` forwards clean routes such as `/availability` and `/admin/` to `public/index.php`; `mod_rewrite` and `AllowOverride All` are required for those clean routes. Existing PHP files such as `/new-booking.php` are served directly.

## Troubleshooting

### The browser says the site cannot be reached

Confirm Apache is running, visit the exact URL including `:8080`, check the hosts-file entry, and make sure no other process is occupying port `8080`.

### Apache will not start

Open the Apache error log from XAMPP Control Panel. Check for duplicate `Listen` lines, a port conflict, a misspelled virtual-host path, or a missing Apache module.

### The application reports a database connection error

Confirm PostgreSQL is running on port `5432`, the database `lab_reservation` exists, and the values in `config/.env` are correct. Verify the file is named `.env` and is in `config`, not the repository root.

### PHP reports that the PostgreSQL driver is missing

Enable `pdo_pgsql` (and `pgsql`) in the `php.ini` used by XAMPP Apache, confirm the extension DLLs exist in `C:\xampp\php\ext`, then restart Apache.

### A route returns 404

Check that Apache's `DocumentRoot` points to the repository's `public` directory. For clean routes, verify `mod_rewrite` is enabled and the `public` directory allows `.htaccess` overrides.

### Booking setup says a database update is needed

This normally means you are using an existing database created from an older schema. Apply `database/migrations/20261027_add_booking_group.sql` to that database, then retry. Do not re-create the database if it contains data you need to keep.

## Security notes

- Keep `config/.env` private and never commit database credentials.
- Create an administrator with a unique, strong password and keep it private.
- The configuration described here is for local development. Do not expose this setup directly to the public internet without production-grade HTTPS, secrets management, account hardening, and server configuration.
