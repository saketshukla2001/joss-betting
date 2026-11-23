# Database Files

## 1. `nativefriendly_joss.sql` — easiest production import

This file creates and selects the production database:

```sql
CREATE DATABASE IF NOT EXISTS nativefriendly_joss
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nativefriendly_joss;
```

It contains all **22 tables**, **32 demo users**, hierarchy, bets, matches,
wallet transactions, statements, commission, audit and login logs.

Import:

```bash
mysql -u DB_USER -p < database/nativefriendly_joss.sql
```

Requirements:

- The MySQL user needs `CREATE` and `INSERT` privileges.
- Existing tables with the same names are replaced. Back up first.
- On cPanel/phpMyAdmin, create the database first, then import
  `betzone-dump.sql` instead (because the cPanel DB name has a prefix).

## 2. `betzone-dump.sql` — generic import

No `CREATE DATABASE`/`USE` statements. Use it when the hosting panel already
created the database:

```bash
mysql -u cpaneluser_dbuser -p cpaneluser_nativefriendly_joss \
  < database/betzone-dump.sql
```

Or phpMyAdmin → select database → Import → choose this file.

## After import

Set the same credentials in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nativefriendly_joss
DB_USERNAME=...
DB_PASSWORD=...
```

Then:

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php scripts/check-db.php
```

Expected checker output:

```
OK: connected successfully.
Tables: 22
Users : 32
```

## Database user example

Run as a MySQL administrator (replace the password):

```sql
CREATE USER 'nfj_user'@'127.0.0.1' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON nativefriendly_joss.* TO 'nfj_user'@'127.0.0.1';
FLUSH PRIVILEGES;
```

For cPanel, create the database and user from **MySQL Databases** instead.
