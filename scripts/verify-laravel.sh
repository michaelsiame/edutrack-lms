#!/usr/bin/env bash
# verify-laravel.sh — gate Laravel changes before declaring work done.
# Runs the cheap, high-signal checks that catch the ways an edit breaks the app:
#   1. php -l on every changed/new .php file (syntax errors)
#   2. blade compile (catches @directive / Blade syntax errors in views)
#   3. route:list (catches broken route/controller references)
#   4. optional: vendor/bin/pint --test (style) if present
#
# Usage:  scripts/verify-laravel.sh
# Exit:   0 = all gates pass, 1 = something failed (details printed).
set -uo pipefail
cd "$(dirname "$0")/.." || exit 2

fail=0
section() { printf "\n=== %s ===\n" "$1"; }

# --- 1. PHP lint changed files ----------------------------------------------
section "php -l (changed .php files)"
phpfiles=$( { git diff --name-only; git diff --cached --name-only; \
  git ls-files --others --exclude-standard; } 2>/dev/null | sort -u | grep '\.php$' || true)
php_count=0
if [ -z "$phpfiles" ]; then
  echo "  (no changed .php files)"
else
  while IFS= read -r f; do
    [ -f "$f" ] || continue
    php_count=$((php_count+1))
    if ! out=$(php -l "$f" 2>&1); then echo "  ✗ $f"; echo "$out" | sed 's/^/      /'; fail=1
    else echo "  ✓ $f"; fi
  done <<< "$phpfiles"
fi

# --- 2. Blade compile -------------------------------------------------------
section "blade compile (view:cache)"
if out=$(php artisan view:cache 2>&1); then echo "  ✓ all Blade views compile"; php artisan view:clear >/dev/null 2>&1
else echo "  ✗ Blade compile failed"; echo "$out" | tail -15 | sed 's/^/      /'; fail=1; fi

# --- 3. Routes resolve ------------------------------------------------------
section "routes (route:list)"
if out=$(php artisan route:list 2>&1); then echo "  ✓ routes resolve ($(echo "$out" | grep -c GET) GET routes)"
else echo "  ✗ route:list failed"; echo "$out" | tail -15 | sed 's/^/      /'; fail=1; fi

# --- 4. Style (optional) ----------------------------------------------------
if [ -x vendor/bin/pint ] && [ "$php_count" -gt 0 ]; then
  section "pint --test (style, advisory)"
  echo "$phpfiles" | tr '\n' ' ' | xargs vendor/bin/pint --test 2>&1 | tail -8 | sed 's/^/  /' || echo "  (style issues — advisory)"
fi

section "RESULT"
if [ "$fail" -eq 0 ]; then echo "  ✓ PASS — Laravel change verification clean"; exit 0
else echo "  ✗ FAIL — fix the errors above before finishing"; exit 1; fi
