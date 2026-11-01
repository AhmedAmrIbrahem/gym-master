# Gym Dashboard — pre-deployment audit (2026-10-24)

## Scope

Static review of the latest available project snapshot plus performance patches (phases 1–4), the phase-4 test hotfix and Android/admin UI patch. This is **not** a clean end-to-end production certification: the currently deployed project may differ. Laravel integration tests, a fresh MySQL migration and real-device/browser tests must still pass.

## Verified fixes included with this patch

- Restore linked-data deletion protection for member account deletion (self/admin) and exercises, previously lost while merging patches.
- Preserve already-installed program/day/food deletion protection.
- Change default seeding so it never creates a privileged account automatically, and make the legacy UserSeeder delegate to the existing explicitly configured SuperAdminSeeder instead of containing fixed credentials.
- Make the shared exercise seeder idempotent on repeated runs.
- Fix a malformed existing exercise ownership migration for **new installations** and a reversible `down()`.
- Add regression tests for deletion guards. No existing meals, user records or workouts are changed by this patch.

## Before the first public deployment

1. **Do not upload the old full project ZIP as-is.** The uploaded archive contained `.env` and `.git/`. Keep `.env` only on the server (outside the public document root), do not put it in a public repo or download, and never publish `.git`, `vendor`, `node_modules`, database copies, or tests as web-accessible files. If secrets from an old `.env` have ever been exposed, rotate API/database/mail credentials. Rotate `APP_KEY` only with a planned migration because existing encrypted data and sessions can be affected.
2. Set the web server document root to the project's `public/` directory, not the Laravel project root. Set HTTPS with valid certificates and redirect plain HTTP to HTTPS.
3. Configure production `.env` with `APP_ENV=production`, `APP_DEBUG=false`, a correct `APP_URL=https://...`, strong unique `APP_KEY`, MySQL credentials, `LOG_LEVEL=warning` (or an appropriate production level), and a real mail transport if credential emails are required. Never generate a new `APP_KEY` when redeploying an existing DB.
4. Ensure PHP >=8.3 with `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `dom`, `xml`, `curl`, `zip`; Composer and Node.js versions compatible with the lockfiles.
5. Back up DB and user-uploaded files **before** running migrations.
6. On the target machine: `composer install --no-dev --prefer-dist --optimize-autoloader`; `npm ci && npm run build` (or upload a verified build); `php artisan storage:link`; `php artisan migrate --force`; `php artisan optimize:clear`; `php artisan config:cache`; `php artisan view:cache`. Queue workers and scheduled tasks must be configured if the chosen queue/mail setup needs them.
7. On a safe staging copy: `php artisan test`, `php artisan route:list`, `vendor/bin/pint --test`, `composer audit`, and `npm audit --omit=dev` where network access permits. Load-test a staging server, not live customer data. Check Chrome Android and Safari iPhone with actual login and meal/measurement/profile screens.
8. `DatabaseSeeder` now seeds shared exercises only. Do not run `migrate:fresh`, `db:wipe`, or arbitrary seeders on an existing customer DB. For a new installation only, explicitly seed the first admin by temporarily setting `INITIAL_ADMIN_EMAIL`, `INITIAL_ADMIN_PASSWORD` (12+ characters) and optionally `INITIAL_ADMIN_PHONE`, running `php artisan db:seed --class=SuperAdminSeeder` **before** `config:cache`; then remove the temporary password setting and refresh config cache. No existing admin password will be overwritten.
9. Confirm `public/build/manifest.json` points to uploaded asset files; remove stale `public/hot` on production. Never expose a Vite development server to the public internet.
10. Confirm email/WhatsApp sharing does not expose credentials to other members. Prefer password-reset links to distributing plaintext passwords for public use. Review logs for sensitive values and configure HTTPS-only cookies with correct session domain.

## Audit limitations and deployment gate

PHP syntax check passed for the locally reconstructed project; Node syntax and existing Vite manifest references were checked. Runtime Laravel tests were **not run** here because the execution environment lacks the required `pdo_sqlite`, `mbstring` and other PHP extensions/vendor setup. External dependency advisories, real MySQL migrations, email delivery, server permissions, penetration tests, mobile-browser rendering and capacity numbers remain unverified. A clean full test run and a staged end-to-end pass are required before publishing.
