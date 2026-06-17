---
name: laravel-verify
description: Verify Laravel code changes before declaring work done. Use after editing ANY .php (controllers, models, seeders, commands) or .blade.php view. Catches syntax errors, broken Blade directives, and broken route/controller references that would 500 the app.
---

# Verify Laravel changes before finishing

You edit PHP/Blade without running the app, so a typo can break a page silently.
After making changes, ALWAYS run the gate and fix everything it reports before
you consider the task done.

## Run the gate
```
scripts/verify-laravel.sh
```
It runs, on your changed files and the app:
1. `php -l` on every changed/new `.php` file — fix any syntax error it prints.
2. Blade compile (`view:cache`) — fix any `@directive`/Blade syntax error.
3. `route:list` — fix any broken route or controller/method reference.
4. `pint --test` (advisory) — style; run `vendor/bin/pint <file>` to auto-fix.

## Rules
- The task is NOT done until the gate prints `✓ PASS`.
- If a check fails, read the error, open the offending file, fix it, re-run.
- Never commit or report success with a failing gate.
- For DB-affecting work, also run the relevant `php artisan db:seed --class=...`
  and confirm it completes without error. NEVER run migrate:fresh/refresh/reset
  or any command that drops data.
