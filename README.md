# metrics.philipnewborough.co.uk

A self-hosted, multi-domain web analytics platform built with [CodeIgniter 4](https://codeigniter.com/). External websites POST page-view events to a JSON API; the platform stores them and presents aggregated statistics in a protected admin dashboard.

---

## Features

- **Hit ingestion API** — accepts page-view events (domain, path, device type, anonymized IP, load time, viewport size, optional user identity) via `POST /api/metrics/receive`
- **Multi-domain support** — tracks hits across any number of domains with per-domain drill-down views
- **Admin dashboard** — Chart.js graphs showing hit counts, unique visitors, device breakdowns, top paths, and load-time statistics
- **Paginated hit log** — filterable table of raw hits with domain and device-type filters
- **API key authentication** — all API routes require an `apikey` header; keys are validated against either a local master key or a central external auth server
- **Session-based admin auth** — admin routes are gated by `is_admin` session state, which is hydrated by validating cookies against the external auth server

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | CodeIgniter 4 (`^4.7`), PHP `^8.2` |
| Frontend | Bootstrap 5, Chart.js, vanilla JS (Airbnb style guide) |
| Database | MySQL (migrations managed via CI4 Migrations) |
| Auth | External auth server (token/cookie validation via cURL) |
| Testing | PHPUnit `^10.5`, FakerPHP |
| Linting | ESLint `^10`, PSR-12 (PHP) |

---

## Requirements

- PHP 8.2+
- MySQL
- Composer
- Node.js / npm (for JS linting only)

---

## Installation

1. Clone the repository and install PHP dependencies:

   ```bash
   composer install
   ```

2. Copy the environment file and configure it:

   ```bash
   cp env .env
   ```

   Key `.env` values to set:

   ```ini
   app.baseURL = 'https://metrics.example.com/'
   app.siteName = 'Metrics'

   # Database
   database.default.hostname = localhost
   database.default.database = metrics
   database.default.username = your_db_user
   database.default.password = your_db_password

   # API master key
   apikeys.masterKey = your_secret_key

   # External service URLs
   urls.auth = 'https://auth.example.com/'
   urls.tld  = 'example.com'
   ```

3. Run database migrations:

   ```bash
   php spark migrate
   ```

4. Point your web server document root at the `public/` directory.

---

## API

All API requests require an `apikey` header.

### `GET /api/test/ping`

Health check. Returns:

```json
{"status": "success", "message": "pong"}
```

### `POST /api/metrics/receive`

Ingest a page-view event.

**Request body (JSON):**

| Field | Type | Required | Notes |
|---|---|---|---|
| `domain` | string | Yes | Max 255 chars |
| `path` | string | Yes | |
| `device_type` | string | Yes | Max 20 chars |
| `anonymized_ip` | string | Yes | Supports IPv6 (max 45 chars) |
| `user_uuid` | string | No | Max 36 chars |
| `username` | string | No | Max 100 chars |
| `is_admin` | int | No | `0` or `1` |
| `useragent` | string | No | |
| `load_time_ms` | int | No | Page load time in milliseconds |
| `window_width` | int | No | Viewport width in pixels |
| `window_height` | int | No | Viewport height in pixels |

---

## Admin Dashboard

The dashboard is accessible at `/admin` and requires `is_admin` session state. It provides:

- **Overview** — total, today, yesterday, this week/month/year hit and unique-visitor counts
- **Charts** — daily hit histogram (Chart.js)
- **Device breakdown** — hits split by device type
- **Top domains / paths** — ranked by hit count
- **Hit log** (`/admin/metrics`) — paginated, filterable raw event log
- **Domains overview** (`/admin/metrics/domains`) — all domains with aggregated stats
- **Domain drill-down** (`/admin/metrics/domain/{domain}`) — per-domain stats and charts

---

## Authentication

Authentication is fully delegated to an external auth server (configured via `urls.auth` in `.env`). This application:

1. Reads `user_uuid` and `token` cookies on each request.
2. Validates them against the external auth server via cURL.
3. Hydrates the CI4 session with user data (`username`, `email`, `is_admin`, `groups`, etc.).
4. Admin routes additionally check `is_admin` and redirect to `/unauthorised` if the condition is not met.

Logout destroys the local CI4 session and redirects to the external auth service's logout endpoint.

---

## Database Schema

Single table: **`metrics`**

| Column | Type |
|---|---|
| `id` | `INT UNSIGNED AUTO_INCREMENT` PK |
| `domain` | `VARCHAR(255)` |
| `path` | `TEXT` |
| `user_uuid` | `CHAR(36)` nullable |
| `username` | `VARCHAR(100)` nullable |
| `is_admin` | `TINYINT(1)` default `0` |
| `device_type` | `VARCHAR(20)` |
| `anonymized_ip` | `VARCHAR(45)` |
| `useragent` | `TEXT` nullable |
| `load_time_ms` | `INT(11)` nullable |
| `window_width` | `SMALLINT(5) UNSIGNED` nullable |
| `window_height` | `SMALLINT(5) UNSIGNED` nullable |
| `created_at` | `DATETIME` nullable |

Indexes: composite `(domain, user_uuid)`, `created_at`, `domain`, `device_type`.

---

## Testing

```bash
composer test
```

Test output is written to `build/logs/`. Test suites are located under `tests/unit/`, `tests/database/`, and `tests/session/`.

---

## Code Style

- **PHP** — PSR-12
- **JavaScript** — Airbnb Style Guide
- **CSS** — BEM naming convention

