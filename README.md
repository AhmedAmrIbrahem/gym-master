# Gym Dashboard

Gym Dashboard is a Laravel 13 application for gym members and administrators. It provides workout programs, exercise tracking, body measurements, nutrition logging and an optional Gemini-powered chatbot.

## Requirements

- PHP 8.3 or later, with extensions required by Laravel and the project dependencies
- Composer 2
- Node.js 22 and npm
- MySQL for local or production use

## Local installation

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

On Windows PowerShell, replace the `cp` command with `Copy-Item .env.example .env`.

Configure `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` in your local `.env`. Create the database before running migrations.

```bash
php artisan migrate
npm run build
php artisan serve
```

For frontend development, run `npm run dev` in a separate terminal.

Do not commit `.env`, tokens or API keys. Configure Gemini only through local environment settings. The chatbot's other local responses remain separate from the Gemini integration.

## Project structure

| Location | Responsibility |
| --- | --- |
| `app/Http/Controllers` | HTTP entry points and response orchestration |
| `app/Http/Requests` | Request validation |
| `app/Actions` | Focused write operations for programs, nutrition and workouts |
| `app/Services` | Shared business logic and chatbot integration |
| `app/Policies` | Authorization and ownership rules |
| `app/Models` | Eloquent entities and query scopes |
| `resources/views/components` | Reusable Blade components |
| `routes/web.php` | Web routes for members and administrators |
| `database/migrations` | Schema changes and indexes |
| `tests/Feature`, `tests/Unit` | Regression and unit tests |

## Testing

The test suite uses in-memory SQLite through `phpunit.xml`. It does not require your local MySQL data.

```bash
php artisan test
php artisan test --filter=SecurityOwnershipTest
php artisan test --filter=ChatbotResponseTest
```

Before merging changes, check the PHP formatter, Blade templates, route registration and frontend build:

```bash
vendor/bin/pint --test
php artisan view:cache
php artisan route:list --except-vendor
php artisan view:clear
npm run build
```

To apply formatting locally, run `vendor/bin/pint`. Review any formatting changes before committing.

## Continuous integration

`.github/workflows/quality.yml` runs on pushes and pull requests. It installs locked PHP and npm dependencies, validates Composer, checks PHP formatting and syntax, compiles Blade views, verifies registered routes, runs Laravel tests with SQLite and builds frontend assets. These checks do not connect to the live MySQL database or call Gemini.

## Deployment checklist

1. Set production `.env` values on the server without committing them.
2. Install dependencies and build assets.
3. Back up the database before applying new migrations.
4. Run `php artisan migrate --force` during the deployment window.
5. Run `php artisan optimize:clear` if cached settings or views changed.
6. Check member workflows, administrator access, workout logging, nutrition, and chatbot responses after deployment.

Do not run test commands against production data. Avoid publishing default administrator credentials.

## Static analysis (opt-in)

`phpstan.neon.dist` starts Larastan analysis at level 5 for `app/` and `routes/`.
Larastan is **not yet installed** in this project and static analysis is **not yet part of CI**. Install the development dependency on your workstation using Composer so it can update both `composer.json` and `composer.lock` consistently:

```bash
composer require --dev "larastan/larastan:^3.0"
```

Then run from the project root:

```bash
vendor/bin/phpstan analyse --configuration=phpstan.neon.dist --no-progress
```

On Windows PowerShell, run `vendor\bin\phpstan analyse --configuration=phpstan.neon.dist --no-progress`.
Do not generate a baseline until the findings have been reviewed, and do not add this check to CI until it passes with the locked dependencies. Static analysis has not been run in this delivery environment because the Composer dependencies are unavailable.

On Windows PowerShell you can run `vendor\bin\pint --test` instead of `vendor/bin/pint --test`. If Pint reports style issues, run `vendor\bin\pint` and then rerun the tests.
