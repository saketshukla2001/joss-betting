#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Public assets must be readable by the web server (403 otherwise).
find "$ROOT/public" -type d -exec chmod 755 {} \;
find "$ROOT/public" -type f -exec chmod 644 {} \;

# Laravel needs to write here.
find "$ROOT/storage" -type d -exec chmod 775 {} \;
find "$ROOT/storage" -type f -exec chmod 664 {} \;
find "$ROOT/bootstrap/cache" -type d -exec chmod 775 {} \;
find "$ROOT/bootstrap/cache" -type f -exec chmod 664 {} \;

# Keep secrets private.
chmod 600 "$ROOT/.env" 2>/dev/null || true

chmod +x "$ROOT"/scripts/*.sh 2>/dev/null || true

echo "Permissions fixed for: $ROOT"
