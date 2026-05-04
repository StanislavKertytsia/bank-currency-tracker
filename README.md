# MultiBank Currency Tracker

A full-stack application for tracking Ukrainian bank exchange rates in real time. Aggregates data from multiple public APIs, detects significant rate changes, sends email notifications, and visualizes branch locations on an interactive map.

---

## Tech Stack

**Backend:** PHP 8.2+ / Laravel 13, MySQL 8+, Redis, Laravel Sanctum, Laravel Notifications

**Frontend:** Vue 3 (Composition API, `<script setup>`), Pinia, Vue Router, Tailwind CSS v4, ApexCharts, Leaflet.js

---

## Architecture Overview

### Backend

The backend follows clean architecture principles — all business logic lives in Services, controllers only delegate.

#### External API Integration

Implements `BankApiInterface` with `getRates(string $currency): array` and `getBranches(string $slug): array`. Five providers are registered in the service container:

- **NbuProvider** — fetches official NBU rates from `bank.gov.ua`. Single endpoint returns all currencies; response is cached in-process per job run. Maps `cc` → currency code, `rate` → both buy and sell fields.
- **MinfinProvider** — fetches commercial bank rates from `minfin.com.ua`. Maps `bid` → buy_rate, `ask` → sell_rate, `organization.slug` → bank slug.
- **PrivatBankProvider** — fetches USD/EUR directly from PrivatBank's public API (`api.privatbank.ua/p24api/pubinfo`). Maps `buy`/`sale` fields, filters by `base_ccy = UAH`.
- **MonoBankProvider** — fetches rates directly from MonoBank's public API.
- **FinanceUaProvider** — fetches branch locations from `finance.ua`. Used exclusively for branch data; also supports fetching the bank organization list (`organizationsList`).

All providers implement fallback data seeded with hardcoded values when the external API is unavailable.

#### DTOs

All external responses are normalized before persisting:

- `RateDTO` — `bankSlug`, `currencyCode`, `buyRate`, `sellRate`, `source`, `updatedAt`
- `BranchDTO` — `bankSlug`, `name`, `address`, `phone`, `latitude`, `longitude`
- `BankDTO` — `name`, `slug`, `logoUrl`, `phone`, `email`, `website`, `address`

#### Scheduled Jobs

- **UpdateExchangeRatesJob** — runs every 30 minutes. Fetches NBU rates, then PrivatBank and MonoBank direct APIs, then Minfin for banks without a public API (Oschadbank, PUMB, Ukrxeximbank). Flushes Redis cache tags `['rates', 'nbu']` after each run. Configured with 3 retries and 60-second backoff.
- **UpdateBranchesJob** — runs once per day. Iterates all non-NBU banks and upserts branches via FinanceUaProvider. Deduplication key: `(bank_id, latitude, longitude)`. Configured with 3 retries and 5-minute backoff.

#### Rate Change Detection

`ExchangeRateService::upsertRate()` wraps each write in a database transaction with `lockForUpdate()`. Before updating, it compares the incoming buy rate against the stored value:

```
abs(new - old) / old * 100 >= 5.0  →  write to rate_histories  →  dispatch notification
```

The resulting `RateHistory` record stores both old and new rates, the computed `change_percent`, source, and timestamp.

#### Notifications

`RateChangeNotification` (mail channel) is dispatched via `NotificationService`. Delivery is gated by:

1. `notification_enabled = true` on the user.
2. Subscription matching: if the user has subscriptions, only notify on matching `bank_id` or `currency_id`. If no subscriptions exist, notify on all changes.

#### Geospatial Queries

`BranchController::nearest()` uses `ST_Distance_Sphere(POINT(longitude, latitude), POINT(?, ?))` to find branches within a configurable radius (default 5 km, max 50 km). Results are ordered by distance with the computed `distance_meters` included in the response.

#### Caching

- Currency list: `Cache::tags(['currencies'])->remember(...)` — TTL 24 hours.
- NBU rates + averages: `Cache::tags(['rates', 'nbu'])->remember(...)` — TTL 30 minutes, flushed after each job run.

---

### Database Schema

| Table | Key Columns |
|---|---|
| `banks` | `name`, `slug`, `logo_url`, `website`, `phone`, `email`, `address`, `rating` |
| `branches` | `bank_id`, `branch_name`, `address`, `phone`, `latitude`, `longitude` |
| `currencies` | `code` (USD/EUR/GBP/CHF/PLN), `name` |
| `exchange_rates` | `bank_id`, `currency_id`, `buy_rate`, `sell_rate`, `source`, `updated_at` |
| `rate_histories` | `bank_id`, `currency_id`, `buy_rate`, `sell_rate`, `previous_buy_rate`, `previous_sell_rate`, `change_percent`, `source`, `recorded_at` |
| `users` | standard auth fields + `notification_enabled` |
| `subscriptions` | `user_id`, `bank_id` (nullable), `currency_id` (nullable) |

---

### API Endpoints

**Public**

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/banks` | Bank list with name, logo, rating, contacts |
| GET | `/api/banks/{id}` | Bank detail with current rates and branches |
| GET | `/api/currencies` | Currency list |
| GET | `/api/rates` | Current commercial rates, filterable by `?bank_id=&currency_id=` |
| GET | `/api/rates/nbu` | NBU official rates + per-currency averages across all banks |
| GET | `/api/branches/nearest` | Nearest branches by `?lat=&lng=&radius=` |
| POST | `/api/auth/register` | Register |
| POST | `/api/auth/login` | Login |

**Authenticated (Sanctum)**

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/auth/logout` | Logout |
| GET/PUT | `/api/profile` | View and update user profile |
| PUT | `/api/profile/notifications` | Toggle email notification flag |
| GET/POST/DELETE | `/api/subscriptions` | Manage bank/currency subscriptions |
| GET | `/api/history` | Significant rate changes, filterable by date range, bank, currency |
| GET | `/api/statistics` | Rate statistics for charts |

All responses use Laravel API Resources — no raw model data is returned.

---

### Frontend

#### State Management (Pinia)

- `useBankStore` — bank list, selected bank, loading state.
- `useCurrencyStore` — currencies, current rates, NBU rates, averages, history, statistics.
- `useAuthStore` — user, token, login/register/logout actions.
- `useNotificationStore` — subscription list, notification toggle.

#### Pages

| Route | Page | Auth |
|---|---|---|
| `/` | Dashboard — rates table with bank and currency filters, NBU row, average row | Public |
| `/banks` | Bank list with search | Public |
| `/banks/:id` | Bank detail — info card, rates table, Leaflet branch map | Public |
| `/statistics` | ApexCharts time-series with date range picker and filters | Required |
| `/profile` | User profile, notification toggle, subscription management | Required |
| `/login` | Login form | Guest only |
| `/register` | Registration form | Guest only |

Vue Router guards redirect unauthenticated users to `/login` and authenticated users away from guest-only routes.

#### Components

**Base UI:** `BaseButton`, `BaseInput`, `BaseCard`, `BaseSelect`, `BaseBadge`, `BaseSkeleton`

**Feature components:**
- `RatesTable` — filterable rates grid with NBU and average rows.
- `RateChart` — ApexCharts time-series with zoom and tooltip.
- `BranchMap` — Leaflet map with custom markers and `leaflet.markercluster` for dense areas.
- `BankCard` — compact bank summary card used on the banks listing page.

**Layout:** `AppLayout` wraps all main pages. `AppSidebar` collapses to `AppBottomNav` on mobile screens.

#### API Layer

Typed Axios clients per domain: `auth`, `banks`, `branches`, `currencies`, `rates`, `history`, `statistics`, `subscriptions`. All clients share a base instance with the Sanctum token injected from the auth store.

---

## Setup

### Backend

```bash
cd back
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan schedule:work   # runs UpdateExchangeRatesJob and UpdateBranchesJob
```

Redis must be running for tagged cache to work. Configure `REDIS_HOST` in `.env`.

For email notifications configure the `MAIL_*` variables in `.env`.

### Frontend

```bash
cd front
npm install
npm run dev
```

Production build: `npm run build`

---

## Testing

**Backend:** PHPUnit via `php artisan test`

**Frontend unit tests:** `npm run test:unit` (Vitest)

**Frontend e2e:** `npm run test:e2e` (Playwright)
