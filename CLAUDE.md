# Salesforce Test Manager — CLAUDE.md

Project context for AI coding assistants. Keep this file updated as the codebase evolves.

---

## Project Overview

A **Laravel 13** web application that serves as an internal tooling platform for QA engineers working with a Salesforce B2B CPQ/OM environment (`b2b-io--cpqpro.sandbox.my.salesforce.com`). It provides:

- **Test Suite Manager** — organise test modules, maintain per-module JSONB parameter sets, and track a shared runtime state (dynamic variables such as `opportunityId` that change across test runs).
- **CPQ Simulator** — a multi-step wizard UI that walks through the Vlocity CPQ quote flow (create quote → add products → configure attributes → update pricing) by proxying API calls through the Laravel backend to avoid CORS issues.
- **Object Sync Manager** — pull Salesforce object/field metadata (via `sobjects/{apiName}/describe`) and store it locally for accurate test configuration.
- **Salesforce User (Persona) Manager** — manage Salesforce OAuth credentials per tester persona using the Web Server (authorization code + refresh token) flow.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.4, Laravel 13 |
| Database | PostgreSQL (DB name: `sfdc_test_manager`) |
| Frontend | Blade templates, Tailwind CSS v3, Alpine.js v3, Vite |
| Auth (app) | Laravel Breeze (session-based) |
| Auth (SF) | OAuth 2.0 — Client Credentials (system) + Web Server Flow (per-persona) |
| Queue | Laravel queue via PostgreSQL (`QUEUE_CONNECTION=database`) |
| Cache | PostgreSQL (`CACHE_STORE=database`); `salesforce_access_token` cached for ~7000 s |
| Asset bundling | Vite + `laravel-vite-plugin` |
| Dev runner | `composer dev` — concurrently runs `php artisan serve`, queue listener, `pail`, and `vite` |

---

## Repository Layout

```
app/
  Http/
    Controllers/
      Admin/
        TesterController.php          # Manage QA tester users
        SalesforceUserController.php  # Manage SF personas (OAuth credentials)
      CpqSimulatorController.php      # CPQ simulator page + CORS proxy
      ModuleController.php            # Legacy module CRUD (dashboard)
      ObjectSyncController.php        # Object/field sync from SF describe API
      SalesforceOAuthController.php   # OAuth redirect + callback
      TestCaseController.php          # Old test-case CRUD (nested under modules)
      TestRunController.php           # Test run history
      TestSuiteController.php         # Test Suite: modules, parameters, runtime state
  Models/
    Module.php            # Legacy test module
    RuntimeState.php      # Global runtime state key-value store
    SalesforceField.php   # Field metadata synced from SF
    SalesforceObject.php  # Object metadata synced from SF
    SalesforceUser.php    # SF persona with OAuth tokens
    TestCase.php          # Old test case (belongs to Module)
    TestModule.php        # New test module (has TestParameters)
    TestParameter.php     # JSONB parameter set for a test case inside a TestModule
    TestRun.php           # Test run record
    User.php              # App user (Breeze)
  Services/
    SalesforceService.php # All SF API calls: auth, describe, API test, Apex test
resources/
  views/
    cpq-simulator/        # CPQ Simulator wizard UI
    layouts/              # Shared Blade layouts
    modules/              # Legacy module views
    object-sync/          # Object sync views
    sf-users/             # Persona management views
    test-suite/           # Test Suite index + show views
    testers/              # Tester admin views
database/
  migrations/             # All migrations, chronologically
  seeders/
routes/
  web.php                 # All HTTP routes (auth-protected group)
.docker/
  nginx.conf              # Nginx config: serves public/, proxies PHP to php-fpm :9000
  supervisord.conf        # Runs nginx + php-fpm as supervised processes
Dockerfile                # Multi-stage: node:22-alpine (assets) → php:8.4-fpm-alpine (runtime)
.dockerignore
```

---

## Key Domain Concepts

### TestModule vs Module

There are **two parallel module systems**:
- `Module` / `TestCase` / `TestRun` — the **legacy** system. Routes under `/modules` and `/test-runs`.
- `TestModule` / `TestParameter` / `RuntimeState` — the **new Test Suite** system. Routes under `/test-suite`.

New work should target the `TestModule` family unless specifically touching the legacy system.

### TestParameter (JSONB)

Each `TestParameter` belongs to a `TestModule` and holds a `parameters` JSONB column. The controller (`TestSuiteController`) merges submitted key-value pairs and casts numeric strings back to `int`/`float` before persisting. A GIN index (`idx_test_parameters_params_gin`) exists on this column for fast lookups.

### RuntimeState

A single flat table (`runtime_state`) that holds global key-value pairs used across test runs (e.g., `opportunityId`, `quoteId`). No foreign keys — it is truly global state. Managed via `TestSuiteController` (store / update / destroy).

### SalesforceService

Central service class at `app/Services/SalesforceService.php`. All HTTP calls to Salesforce go through it:
- `getAccessToken()` — Client Credentials flow, result cached under `salesforce_access_token`.
- `getAccessTokenForUser(SalesforceUser)` / `refreshUserToken(SalesforceUser)` — per-persona token management using stored `refresh_token`.
- `executeApiTest(config, token?, sfUser?)` — generic GET/POST with automatic 401 retry.
- `describeObject(apiName)` — describe endpoint with stale-token retry and 404 handling.
- `executeApexTest(className)` — Tooling API `runTestsSynchronous`.

### CPQ Simulator Proxy

`CpqSimulatorController::proxy()` is a generic HTTP proxy. The browser sends `{ endpoint, method, persona_id?, payload? }` as JSON and the backend forwards the request to Salesforce with the correct bearer token, returning the full response (status, headers, body). This sidesteps CORS for any Salesforce endpoint.

---

## Environment Variables

| Variable | Purpose |
|---|---|
| `DB_CONNECTION=pgsql` | PostgreSQL; DB name `sfdc_test_manager` |
| `SALESFORCE_URL` | Base SF instance URL (no trailing slash) |
| `SALESFORCE_CLIENT_ID` | Connected App consumer key |
| `SALESFORCE_CLIENT_SECRET` | Connected App consumer secret |
| `QUEUE_CONNECTION=database` | Queued jobs stored in PostgreSQL |
| `CACHE_STORE=database` | Cache stored in PostgreSQL |

---

## Docker / Deployment

The app is containerised with a multi-stage `Dockerfile`:

- **Stage 1** — `node:22-alpine` installs npm deps and runs `vite build`
- **Stage 2** — `php:8.4-fpm-alpine` installs Composer deps, starts nginx + php-fpm via supervisord

The container exposes **port 1100**. `.env` is excluded from the image; inject all env vars at runtime via EasyPanel's environment settings.

Supporting files: `.docker/nginx.conf`, `.docker/supervisord.conf`.

---

## Common Commands

```bash
# Start all dev services (server + queue + pail + vite)
composer dev

# Run migrations
php artisan migrate

# Run tests
composer test
# or directly:
php artisan test

# Lint / fix code style
./vendor/bin/pint

# Build frontend assets
npm run build
```

---

## Routing Conventions

All routes are auth-protected (`middleware('auth')`). Route names follow Laravel resource conventions where applicable:

| Area | Prefix | Named Routes |
|---|---|---|
| Test Suite | `/test-suite` | `test-suite.index`, `test-suite.show`, `test-suite.counter.*`, `test-suite.parameters.*`, `test-suite.runtime.*` |
| CPQ Simulator | `/cpq-simulator` | `cpq-simulator.index`, `cpq-simulator.proxy` |
| Object Sync | `/object-sync` | `object-sync.*` (resource) |
| SF Users | `/sf-users` | `sf-users.*` (resource) |
| Testers | `/testers` | `testers.*` (resource) |
| Modules (legacy) | `/modules` | `modules.*` (resource) |

---

## Coding Patterns & Conventions

- **PSR-4 autoloading** under the `App\` namespace.
- Controllers are thin — validation + model interaction + redirect/response. Business logic belongs in `SalesforceService` or future service classes.
- Use `updateOrCreate` for upsert-style operations (see `TestSuiteController::storeParameter`).
- Atomic counter increments via `Model::increment()` (see `TestModule::incrementCounter()`).
- Flash messages use `->with('success', ...)` / `->withErrors(...)` and are rendered in the shared layout.
- Tailwind CSS classes are used directly in Blade templates; Alpine.js (`x-data`, `x-show`, `@click`, etc.) handles interactive client-side state.
- Raw SQL is acceptable only when Laravel's schema builder lacks support (e.g., GIN indexes).

---

## Known Gotchas

1. **Stale system token** — `describeObject()` handles a 401/`INVALID_SESSION_ID` by forgetting the `salesforce_access_token` cache key and retrying once. Do not add arbitrary retries elsewhere.
2. **Persona token expiry** — Per-persona tokens are stored in `salesforce_users.access_token`. On a 401, call `SalesforceService::refreshUserToken()` and retry once (the proxy controller already does this).
3. **JSONB & SQLite** — The GIN index migration will fail on SQLite. Always use PostgreSQL in local dev; do not switch `DB_CONNECTION` to `sqlite` for this project.
4. **Two module systems** — `TestModule` (new) and `Module` (legacy) coexist. The dashboard (`/dashboard`) still uses the legacy `ModuleController`. Avoid mixing them.
5. **`SALESFORCE_URL` must have no trailing slash** — the service concatenates it with endpoint paths that start with `/`.
6. **No `.env` in Docker image** — the `.dockerignore` excludes `.env`. All environment variables must be injected at container runtime (e.g. via EasyPanel's environment settings). Running `php artisan config:cache` inside the image at build time will bake in empty values — don't do it.
