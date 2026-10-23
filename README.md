# Lead Management

A Laravel 12 application for authenticated lead management, built with Inertia.js, React, and TypeScript.

## Features

- Session-based registration, login, throttled authentication, and logout.
- Authenticated lead creation, editing, and deletion with server-side validation.
- Inline status updates with policy authorization and server-side enum validation.
- Phone numbers validated as 7–15 digits, with optional international formatting.
- Confirmation before deleting a lead or signing out.
- Per-account lead ownership and policy-protected edits and deletes.
- Server-side search by name or email, status filtering, created-date sorting, and pagination.
- Responsive, accessible Inertia pages with form errors, confirmation before deletion, and empty states.

## Verified versions

Versions installed and verified for this implementation:

| Component | Version |
| --- | --- |
| PHP | 8.4.0 |
| Composer | 2.8.11 |
| Laravel | 12.70.1 |
| Inertia Laravel adapter | 2.0.28 |
| Node.js | 22.19.0 |
| npm | 10.9.3 |
| Inertia React | 2.3.29 |
| React / React DOM | 19.3.1 |
| TypeScript | 5.9.3 |
| SQLite | 3.46.0 |

The exact dependency versions are pinned by `composer.lock` and `package-lock.json`.

## Setup and run (Windows / PowerShell)

Requirements: PHP 8.2 or later with the SQLite extension enabled, Composer, and Node.js/npm. The versions listed above are the versions used to verify this project.

### First-time setup

Open PowerShell in the project directory (`C:\wamp64\lead-management`) and run:

```powershell
composer install
npm ci

if (-not (Test-Path .env)) { Copy-Item .env.example .env }
if (-not (Test-Path database\database.sqlite)) {
    New-Item -ItemType File -Path database\database.sqlite | Out-Null
}
if (Select-String -Path .env -Pattern '^APP_KEY=$' -Quiet) {
    php artisan key:generate
}

php artisan migrate
```

The app uses SQLite by default; you do not need to install or start a separate database server. Migrations create the application and session tables.

### Start the app

After setup, open two PowerShell terminals in the project directory.

Terminal 1 — start Laravel:

```powershell
php artisan serve
```

Terminal 2 — start the Vite development server:

```powershell
npm run dev
```

Visit **http://127.0.0.1:8000** (or the URL printed by `php artisan serve`), register an account, and start managing leads. Keep both terminals running while using the app.

### Stop and restart

Press `Ctrl+C` in each terminal to stop the servers. On later runs, no setup commands are needed: start `php artisan serve` and `npm run dev` again in separate terminals.

### Production frontend build

To compile optimized frontend assets instead of using the Vite development server, run:

```powershell
npm run build
```

Registration is enabled for this machine test; disable or replace public registration before deploying a private production CRM.

## Database design

The `leads` migration enforces a globally unique email and references the owning user. Lead data is scoped to that authenticated user. Status values are defined by `App\Enums\LeadStatus` and validated on the server.

Indexes support the actual listing patterns:

- The unique email index enforces uniqueness.
- `(user_id, created_at)` supports a user's unfiltered date-ordered listing.
- `(user_id, status, created_at)` supports status filtering and date ordering together.

Name/email search uses parameterized `LIKE` conditions with a leading wildcard; ordinary B-tree indexes do not accelerate that substring search, so no ineffective search indexes are added. Search input is capped at 100 characters and pagination is fixed at 10 rows.

## Filter URL behavior

Search, status, and sort values are submitted in a CSRF-protected POST body and stored in the authenticated session. The listing URL contains only an opaque random session key, not the filter values. Pagination may add a `page` query parameter; it does not serialize any filters. A distinct key is issued when filters change, allowing pagination, reloads, and browser history to resolve the corresponding session state. Filter states expire with the Laravel session.

## Tests and checks

```powershell
php artisan test
npm run typecheck
npm run build
vendor\bin\pint --test
composer audit --locked
npm audit --audit-level=moderate
```

The feature tests cover authentication, ownership, CRUD and inline status validation, email uniqueness (including the database constraint), enum values, combined filters, sorting, pagination, browser-history state, URL behavior, and the relevant SQLite query plan.

## Production notes

- Set `APP_ENV=production`, `APP_DEBUG=false`, a correct HTTPS `APP_URL`, and `SESSION_SECURE_COOKIE=true` when serving over HTTPS.
- Keep `.env` and credentials private; `.env` is ignored by Git.
- Use a persistent production database and run migrations during deployment.
- The example intentionally uses ordinary deletion and does not implement import/export or soft deletes; these are not required by the assessment.

## Time taken

Approximately 4 hours, including project setup, implementation, dependency installation, verification and testing.
