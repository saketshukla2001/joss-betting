# Production Deployment (MySQL Connection Error)

The error shown on `joss.anekam.shop`:

```
SQLSTATE[HY000] [2002] Connection refused
mysql: Host: 127.0.0.1, Port: 3307, Database: nativefriendly_joss
```

This is **not a Laravel code error**. The application cannot reach MySQL on
`127.0.0.1:3307`. Port `3307` is the isolated local MySQL used by this
development workspace. Normal shared hosting / cPanel MySQL listens on
**3306** (or uses a socket with `DB_HOST=localhost`).

## 1. Fix `.env` on the server

Edit the server's `.env` (usually in the project root):

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://joss.anekam.shop

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nativefriendly_joss
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

SESSION_DRIVER=database
CACHE_STORE=database
```

A complete template is included at `.env.production.example`.

If cPanel created a prefixed database/user, use the full names:

```env
DB_DATABASE=cpaneluser_nativefriendly_joss
DB_USERNAME=cpaneluser_joss
```

## 2. Create the database and user

With root/admin MySQL access:

```sql
CREATE DATABASE nativefriendly_joss
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'nativefriendly_joss_user'@'127.0.0.1'
  IDENTIFIED BY 'STRONG_PASSWORD_HERE';

GRANT ALL PRIVILEGES ON nativefriendly_joss.*
  TO 'nativefriendly_joss_user'@'127.0.0.1';

FLUSH PRIVILEGES;
```

On cPanel, create the database and user in **MySQL Databases**, then add the
user to the database with **All Privileges**.

## 3. Import the dump

Use [database/nativefriendly_joss.sql](../database/nativefriendly_joss.sql) to create the database and import everything in one command. If the host already created a prefixed database, use [database/betzone-dump.sql](../database/betzone-dump.sql) instead.

SSH / terminal:

```bash
mysql -u nativefriendly_joss_user -p \
  < database/nativefriendly_joss.sql
```

phpMyAdmin:

1. Open phpMyAdmin.
2. Select the `nativefriendly_joss` database.
3. Import `database/betzone-dump.sql`.
4. Wait for "Import has been successfully finished".

The dump contains tables and demo data. It does **not** create the database,
so create the database first.

## Still 3307 after editing `.env`?

`php artisan cache:clear` itself needs the database, so it will keep failing while
the app is still pointed at port 3307. Do **not** start with `cache:clear`.

Run these in order:

```bash
cd /path/to/betzone

# 1. See what the app's .env really contains
grep -E '^DB_(HOST|PORT|DATABASE|USERNAME)=' .env

# 2. Remove Laravel's cached config (this command does not need MySQL)
php artisan config:clear
rm -f bootstrap/cache/config.php

# 3. Verify the raw .env connection (prints the actual host/port)
php scripts/check-db.php
```

If the checker still prints `Port : 3307`, the `.env` was not changed. Edit it:

```bash
sed -i 's/^DB_PORT=.*/DB_PORT=3306/' .env
sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env
php scripts/check-db.php
```

If `127.0.0.1:3306` is refused, try the socket host:

```bash
sed -i 's/^DB_HOST=.*/DB_HOST=localhost/' .env
php scripts/check-db.php
```

Only after the checker succeeds:

```bash
php artisan cache:clear
php artisan config:cache
```

If the config is still 3307 after this, check for a server-level environment
variable or a second `.env` file, and confirm with:

```bash
php artisan about
```

## 4. Clear cached configuration

If `.env` was edited but the site still uses old values, Laravel has a cached
config. Run:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

If Artisan itself is failing, delete the cached files directly:

```bash
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/packages.php
```

Then:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 5. Verify

Run the included database checker:

```bash
php scripts/check-db.php
```

Expected output:

```
OK: connected successfully.
Tables: 22
Users : 32
```

Other checks:

```bash
php artisan about
php artisan db:show
php artisan migrate:status
```

## 6. File permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Use the web-server user shown by your host (often `www-data`, `apache`, or
your cPanel account) instead of `www-data` if needed.

## CSS/JS returns 403 (unstyled site)

If the site renders as plain HTML and `/css/app.css` returns **403 Forbidden**,
the uploaded CSS/JS files are not readable by LiteSpeed/Apache. The package
now ships them as `644`, but a previous upload may still have `600`.

Run on the server:

```bash
cd /path/to/joss

chmod 755 public public/css public/js
chmod 644 public/css/*.css public/js/*.js

# full fix for all public assets and writable folders
bash scripts/fix-permissions.sh
```

Check the response:

```bash
curl -I https://joss.anekam.shop/css/app.css
```

Expected: `HTTP/2 200` and `content-type: text/css`.

If it is still 403, check ownership. The file owner must match (or be readable
by) the web-server user:

```bash
ls -l public/css/app.css
```

On cPanel, files are normally owned by the cPanel account and LiteSpeed runs
as that user. On VPS setups:

```bash
chown -R www-data:www-data public
```

Then purge LiteSpeed/cPanel cache and hard-refresh the browser
(`Cmd+Shift+R` / `Ctrl+Shift+R`).

If the CSS URL returns **404**, the file was not uploaded to `public/css/`.
Do not put `css/` at the project root; it must be inside `public/`.

## 7. APP_KEY

If the uploaded `.env` has an empty `APP_KEY`:

```bash
php artisan key:generate
```

Do not change `APP_KEY` after users/sessions exist unless you intentionally
want to invalidate encrypted data.

## Common errors

| Error | Cause | Fix |
|---|---|---|
| `Connection refused` on 3307 | MySQL is not on port 3307 | Set `DB_PORT=3306` |
| `Connection refused` on 3306 | MySQL service down / wrong host | Start MySQL or ask host for DB host |
| `Access denied for user` | Wrong username/password or no privileges | Recreate user and grant privileges |
| `Unknown database` | `DB_DATABASE` does not exist | Create it or fix the name |
| `Table 'sessions' doesn't exist` | Dump not imported | Import `betzone-dump.sql` |
| Old credentials still used | Cached config | `php artisan optimize:clear` |
| 500 with stack trace | `APP_DEBUG=true` | Set `APP_DEBUG=false` |

## If port 3307 is intentional

Only use port 3307 when the server's MySQL is configured to listen there:

- `my.cnf`: `port=3307`
- Restart MySQL
- Open the port in the firewall / security group
- Confirm with `ss -lntp | grep 3307`

Then set `DB_PORT=3307`.

## Security notes

- Set `APP_DEBUG=false` in production.
- Never commit the real `.env` or database password to Git.
- Use a database user limited to this database, not MySQL `root`.
- Keep the `vendor/` directory outside the public web root or deny direct access.
