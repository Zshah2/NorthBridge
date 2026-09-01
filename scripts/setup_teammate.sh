#!/usr/bin/env bash
# First-time local setup for teammates (no secrets in repo).
# Usage: bash scripts/setup_teammate.sh
# See docs/COLLAB.md and CONTRIBUTING.md.

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "== CollegeWeb teammate setup =="
echo "Project root: $ROOT"
echo

# --- PHP ---
if ! command -v php >/dev/null 2>&1; then
  echo "ERROR: php not found. Install PHP 8+ and try again."
  exit 1
fi
PHP_VER="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
echo "OK  PHP $PHP_VER"

# --- DB config hint ---
HAS_ENV=0
if [[ -n "${DB_HOST:-}" && -n "${DB_NAME:-}" ]]; then
  HAS_ENV=1
  echo "OK  DB_* env vars detected"
fi
if [[ -f "$ROOT/app/config/database.local.php" ]]; then
  echo "OK  app/config/database.local.php exists"
  HAS_ENV=1
fi
if [[ "$HAS_ENV" -eq 0 ]]; then
  echo
  echo "WARN: No database config found."
  echo "  Option A: export DB_HOST=127.0.0.1 DB_PORT=3306 DB_NAME=collegeweb DB_USER=root DB_PASS='...'"
  echo "  Option B: cp app/config/database.local.php.example app/config/database.local.php && edit it"
  echo
  read -r -p "Continue anyway? [y/N] " ans
  if [[ ! "$ans" =~ ^[Yy]$ ]]; then
    exit 1
  fi
fi

# --- Login credentials template ---
if [[ ! -f "$ROOT/docs/LOGIN_CREDENTIALS.txt" ]]; then
  if [[ -f "$ROOT/docs/LOGIN_CREDENTIALS.txt.example" ]]; then
    cp "$ROOT/docs/LOGIN_CREDENTIALS.txt.example" "$ROOT/docs/LOGIN_CREDENTIALS.txt"
    echo "OK  Created docs/LOGIN_CREDENTIALS.txt from .example (gitignored)"
  fi
fi

# --- Composer (optional, for 2FA email later) ---
if [[ ! -f "$ROOT/vendor/autoload.php" ]] && command -v composer >/dev/null 2>&1; then
  echo "→ composer install --no-interaction"
  composer install --no-interaction
fi

run_php() {
  echo "→ php $*"
  php "$@"
}

echo
echo "-- Database --"
run_php scripts/migrate.php
run_php scripts/import_all.php
run_php scripts/seed_demo_registration.php

echo
echo "== Setup complete =="
echo
echo "Next steps:"
echo "  1. Create an admin user (use your own email/password):"
echo "       php scripts/seed_superadmin.php your@email.com YourPassword"
echo "  2. Start the server:"
echo "       php -S 127.0.0.1:8000 -t public public/router.php"
echo "  3. Open http://127.0.0.1:8000/login.php"
echo
echo "Before coding: git checkout main && git pull && git checkout -b feature/your-name-topic"
echo "See CONTRIBUTING.md — never push directly to main."
