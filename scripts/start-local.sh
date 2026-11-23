#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

MYSQL_BASE="${MYSQL_BASE:-/opt/homebrew/opt/mysql}"
MYSQLD="$MYSQL_BASE/bin/mysqld"
MYSQLADMIN="$MYSQL_BASE/bin/mysqladmin"
MYSQL="$MYSQL_BASE/bin/mysql"
SOCKET="$ROOT/run/mysql.sock"
PORT="${APP_PORT:-8090}"

mkdir -p run storage/logs storage/app

if ! "$MYSQLADMIN" --socket="$SOCKET" -u root ping >/dev/null 2>&1; then
    echo "Starting isolated MySQL on port 3307…"

    if [ ! -d "$ROOT/mysql-data/mysql" ]; then
        "$MYSQLD" --initialize-insecure \
            --datadir="$ROOT/mysql-data" \
            --basedir="$MYSQL_BASE" \
            --log-error="$ROOT/run/mysql-init.log"
    fi

    nohup "$MYSQLD" \
        --datadir="$ROOT/mysql-data" \
        --port=3307 \
        --socket="$SOCKET" \
        --pid-file="$ROOT/run/mysql.pid" \
        --mysqlx=OFF \
        --bind-address=127.0.0.1 \
        --log-error="$ROOT/run/mysql-error.log" \
        >"$ROOT/run/mysql.out" 2>&1 &

    for _ in $(seq 1 30); do
        if "$MYSQLADMIN" --socket="$SOCKET" -u root ping >/dev/null 2>&1; then
            break
        fi
        sleep 1
    done
fi

if ! "$MYSQLADMIN" --socket="$SOCKET" -u root ping >/dev/null 2>&1; then
    echo "MySQL did not start. Check run/mysql-error.log." >&2
    exit 1
fi

"$MYSQL" --socket="$SOCKET" -u root <<'SQL'
CREATE DATABASE IF NOT EXISTS betzone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'betzone'@'127.0.0.1' IDENTIFIED BY 'betzone_secret';
CREATE USER IF NOT EXISTS 'betzone'@'localhost' IDENTIFIED BY 'betzone_secret';
GRANT ALL PRIVILEGES ON betzone.* TO 'betzone'@'127.0.0.1';
GRANT ALL PRIVILEGES ON betzone.* TO 'betzone'@'localhost';
FLUSH PRIVILEGES;
SQL

USER_COUNT="$(php -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo \App\Models\User::count();' 2>/dev/null | tr -d "[:space:]" || true)"

if [ -z "$USER_COUNT" ] || [ "$USER_COUNT" = "0" ]; then
    echo "Running migrations and demo seed…"
    php artisan migrate --seed --force
fi

if ! curl -s -o /dev/null "http://127.0.0.1:$PORT/up"; then
    nohup php artisan serve --host=127.0.0.1 --port="$PORT" \
        >"$ROOT/storage/logs/server.log" 2>&1 &
    echo $! > "$ROOT/storage/app/server.pid"

    for _ in $(seq 1 30); do
        if curl -s -o /dev/null "http://127.0.0.1:$PORT/up"; then
            break
        fi
        sleep 1
    done
fi

if ! pgrep -f "artisan odds:tick --loop" >/dev/null 2>&1; then
    nohup php artisan odds:tick --loop --sleep=15 \
        >"$ROOT/storage/logs/odds.log" 2>&1 &
    echo $! > "$ROOT/storage/app/odds.pid"
fi

echo ""
echo "  BetZone is running"
echo "  ------------------"
echo "  Storefront : http://127.0.0.1:$PORT"
echo "  Admin      : http://127.0.0.1:$PORT/admin"
echo ""
echo "  Admin  : admin / Admin@12345"
echo "  Client : demo / Client@12345"
echo ""
echo "  Stop with: ./scripts/stop-local.sh"
