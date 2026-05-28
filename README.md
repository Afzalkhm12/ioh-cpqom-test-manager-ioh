# Salesforce Test Manager

Internal tooling platform for QA engineers working with a Salesforce B2B CPQ/OM environment. Built with Laravel 13, PHP 8.4, and PostgreSQL.

---

## Features

- **Test Suite Manager** — organise test modules, maintain per-module JSONB parameter sets, and track shared runtime state (e.g. `opportunityId`, `quoteId`) across test runs.
- **CPQ Simulator** — multi-step wizard UI that walks through the Vlocity CPQ quote flow by proxying API calls through the backend to avoid CORS issues.
- **Object Sync Manager** — pull Salesforce object/field metadata via `sobjects/{apiName}/describe` and store it locally for accurate test configuration.
- **Salesforce User (Persona) Manager** — manage Salesforce OAuth credentials per tester persona using the Web Server (authorization code + refresh token) flow.

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | ^8.4 |
| Composer | 2.x |
| Node.js | 22+ |
| PostgreSQL | 14+ |

---

## Local Development

```bash
# 1. Install dependencies
composer install
npm install

# 2. Copy and configure environment
cp .env.example .env
php artisan key:generate

# 3. Run migrations
php artisan migrate

# 4. Start all dev services (server + queue + pail + vite)
composer dev
```

The app will be available at `http://localhost:8000`.

---

## Environment Variables

| Variable | Purpose |
|---|---|
| `DB_CONNECTION=pgsql` | PostgreSQL; DB name `sfdc_test_manager` |
| `SALESFORCE_URL` | Base SF instance URL — **no trailing slash** |
| `SALESFORCE_CLIENT_ID` | Connected App consumer key |
| `SALESFORCE_CLIENT_SECRET` | Connected App consumer secret |
| `QUEUE_CONNECTION=database` | Queued jobs stored in PostgreSQL |
| `CACHE_STORE=database` | Cache stored in PostgreSQL |

---

## Docker / Production Deployment

The project ships with a multi-stage `Dockerfile`:

- **Stage 1** (`node:22-alpine`) — installs npm dependencies and runs `vite build`
- **Stage 2** (`php:8.4-fpm-alpine`) — installs Composer dependencies, runs nginx + php-fpm via supervisord

The app is served on **port 1100**.

```bash
# Build the image
docker build -t sfdc-test-manager .

# Run (pass env vars from a file or directly)
docker run -p 1100:1100 --env-file .env sfdc-test-manager
```

Supporting config files live in [.docker/](.docker/):
- `nginx.conf` — serves Laravel from `/app/public`, proxies PHP to php-fpm on port 9000
- `supervisord.conf` — manages nginx and php-fpm processes

> **Note:** The `.env` file is excluded from the Docker image (see `.dockerignore`). Inject all environment variables at runtime via your deployment platform (e.g. EasyPanel environment settings).

---

## Common Commands

```bash
# Start all dev services
composer dev

# Run migrations
php artisan migrate

# Run tests
composer test

# Lint / fix code style
./vendor/bin/pint

# Build frontend assets
npm run build
```
