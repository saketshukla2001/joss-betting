#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if [ -f storage/app/server.pid ]; then
    PID="$(cat storage/app/server.pid)"
    if kill -0 "$PID" 2>/dev/null; then
        kill "$PID" 2>/dev/null || true
        echo "Stopped Laravel server (pid $PID)."
    fi
    rm -f storage/app/server.pid
fi

if [ -f storage/app/odds.pid ]; then
    PID="$(cat storage/app/odds.pid)"
    if kill -0 "$PID" 2>/dev/null; then
        kill "$PID" 2>/dev/null || true
        echo "Stopped odds ticker (pid $PID)."
    fi
    rm -f storage/app/odds.pid
fi

if [ "${1:-}" = "--all" ]; then
    if [ -f run/mysql.pid ]; then
        PID="$(cat run/mysql.pid)"
        if kill -0 "$PID" 2>/dev/null; then
            kill "$PID" 2>/dev/null || true
            echo "Stopped isolated MySQL (pid $PID)."
        fi
        rm -f run/mysql.pid
    fi
fi

echo "Demo stopped."
