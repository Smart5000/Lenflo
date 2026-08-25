# Lenflo — Local Development Setup

## Prerequisites

- **PHP 8.3+** (8.4 recommended)
- **Node.js 22+** and **npm**
- **Composer**
- **PostgreSQL** (production target) or **SQLite** (local dev via Laravel Herd)
- **Laravel Herd** (recommended for local PHP/SQLite workflow)

## Quick Start with Herd

1. Clone the repository.
2. Open the project folder in Herd.
3. Copy `.env.example` to `.env`:
   ```
   cp .env.example .env
   ```
4. For local Herd development, switch to SQLite in `.env`:
   ```
   DB_CONNECTION=sqlite
   DB_DATABASE=database.sqlite
   ```
   Herd manages SQLite automatically.
5. Generate an application key:
   ```
   php artisan key:generate
   ```
6. Run migrations:
   ```
   php artisan migrate
   ```
7. Install Node dependencies and build assets:
   ```
   npm install
   npm run build
   ```
8. Start the dev server:
   ```
   php artisan dev
   ```

## PostgreSQL Setup

For production-like local development with PostgreSQL:

1. Install PostgreSQL and create a `lenflo` database.
2. Update `.env`:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=lenflo
   DB_USERNAME=your_user
   DB_PASSWORD=your_password
   ```
3. Ensure the `pgsql` PHP extension is installed.
4. Run migrations:
   ```
   php artisan migrate
   ```

## Development Commands

| Command | Purpose |
|---------|---------|
| `php artisan dev` | Start Vite + artisan dev server |
| `npm run build` | Production asset build |
| `npm run dev` | Vite dev server only |
| `npm run lint:check` | ESLint check |
| `npm run format:check` | Prettier check |
| `npm run types:check` | TypeScript check |
| `./vendor/bin/pint --parallel --test` | PHP lint check |
| `php artisan test` | Run PHPUnit tests |
| `composer ci:check` | Full CI check (lint + types + tests) |

## Testing Commands

The project uses **PHPUnit** for PHP tests:

```bash
php artisan test                    # Run all tests
php artisan test --testsuite=Unit   # Unit tests only
php artisan test --testsuite=Feature # Feature tests only
```

Tests use an in-memory SQLite database by default (configured in `phpunit.xml`).

## Code Quality

- **PHP**: Laravel Pint (PSR-12 / Laravel preset) + PHPStan level 7
- **TypeScript**: Strict mode, `tsc --noEmit`
- **JavaScript**: ESLint with React, hooks, import ordering, stylistic rules
- **Formatting**: Prettier with Tailwind CSS plugin

All checks must pass before merging.

## Architecture Notes

- **Modular monolith**: Domain boundaries live under `app/Http/Controllers/` and `resources/js/pages/`.
- **Inertia 3**: Server-side routing, React page components.
- **Dark mode**: First-class. Uses `use-appearance` hook with system detection.
- **Aurora Flow palette**: Deep navy/slate base, indigo primary, teal accents, gold highlights. Defined in `resources/css/app.css`.
