# Technical Specification: Bank API & Exchange Rates

## 1. Role & Core Principles
**Role:** You are a Senior Fullstack Developer (Laravel + Vue 3).
**Guiding Principles:**
- **Clean Architecture:** Logic belongs in Services, not Controllers. Use DTOs for external data.
- **Production Ready:** Code must include error handling, logging, and performance optimization (caching).
- **Security:** Strict validation for all inputs, safe SQL queries (Eloquent/Query Builder), and secure authentication.
- **DRY & SOLID:** Avoid code duplication. Design for extensibility.

---

## 2. Technical Stack
- **Backend:** PHP 8.2+ / Laravel 13.
- **Frontend:** Vue 3 (Composition API, `<script setup>`), Pinia, Vue Router, Axios.
- **Styling:** Tailwind CSS (Responsive Design, desktop + mobile).
- **Maps & Charts:** Leaflet.js (OpenStreetMap) and ApexCharts.
- **Database:** MySQL 8+ (Spatial/JSON support).
- **Cache:** Redis (tagged cache for selective invalidation).
- **Auth:** Laravel Sanctum.
- **Queue:** Laravel Horizon or database driver.
- **Mail:** Laravel Notifications (SMTP).

---

## 3. Database Schema Requirements
Design migration-ready schema:
- **Banks:** name, description, logo_url, website, phone, email, address, rating, slug.
- **Branches:** bank_id, branch_name, address, phone, location (POINT spatial or lat/lng decimals).
- **Currencies:** code (USD, EUR, GBP, CHF, PLN), name.
- **ExchangeRates:** bank_id, currency_id, buy_rate, sell_rate, source (NBU/Minfin), updated_at.
- **RateHistory:** bank_id, currency_id, buy_rate, sell_rate, previous_buy_rate, previous_sell_rate, change_percent, source, recorded_at.
- **Users:** standard Auth fields + notification_enabled (bool), subscriptions relation.
- **Subscriptions:** user_id, bank_id (nullable), currency_id (nullable) — for per-bank/per-currency notification targeting.

---

## 4. Backend Architecture

### External API Integration
- Implement `BankApiInterface` with methods: `getRates()`, `getBranches()`.
- Create `MinfinProvider` and `NbuProvider` implementing this interface.
- Use **DTOs** to normalize all external responses before persisting.
- Minfin mapping: `bid` → `buy_rate`, `ask` → `sell_rate`.
- NBU mapping: `cc` → currency code, `rate` → rate (NBU has no buy/sell, store in both fields).
- Finance.ua mapping: `organizationsList` → Banks table, `branches` → Branches table via bank slug.

### Scheduled Jobs
- `UpdateExchangeRatesJob` — fetch rates from Minfin and NBU, detect >5% changes, write to RateHistory, trigger notifications.
- `UpdateBranchesJob` — fetch branches from Finance.ua per bank slug, upsert to DB.
- Schedule: rates every 30 minutes, branches once per day.

### Rate Change Detection
- On each rate update compare new value to last saved ExchangeRate.
- If `abs(new - old) / old * 100 >= 5` — write to RateHistory and dispatch `RateChangeNotification`.

### Notifications
- `RateChangeNotification` via Laravel Notifications (mail channel).
- Respect `notification_enabled` flag on User.
- Filter by Subscriptions: if user has subscriptions, notify only on matching bank_id or currency_id. If no subscriptions — notify on all changes.
- Provide API endpoints to toggle notifications and manage subscriptions.

### Geospatial Logic
- Use `ST_Distance_Sphere(POINT(lng, lat), POINT(?, ?))` for nearest branch queries.
- Accept `lat`, `lng`, and optional `radius` (meters) from request.
- Return branches ordered by distance with distance value in response.

### Caching
- Cache currency list with `Cache::tags(['currencies'])->remember(...)` — TTL 24h.
- Cache NBU rates with `Cache::tags(['rates', 'nbu'])->remember(...)` — TTL 30min.
- Invalidate relevant tags after each job run.

### Mock Fallback
- If external APIs are unavailable, fall back to `database/seeders` with hardcoded JSON samples.
- Seeders must cover all 5 banks, all 5 currencies, branches, and sample rate history.

---

## 5. API Endpoints

### Public
- `GET /api/banks` — list with name, logo, rating, phone, email.
- `GET /api/banks/{id}` — full info + current rates + branches list.
- `GET /api/currencies` — currency list.
- `GET /api/rates` — current rates, filterable by `?bank_id=&currency_id=`.
- `GET /api/rates/nbu` — NBU rates + average across all banks per currency.
- `GET /api/branches/nearest?lat=&lng=&radius=` — nearest branches by geolocation.

### Authenticated
- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET/PUT /api/profile` — view and edit user data.
- `PUT /api/profile/notifications` — toggle notification_enabled.
- `GET/POST/DELETE /api/subscriptions` — manage bank/currency subscriptions.
- `GET /api/history?from=&to=&bank_id=&currency_id=` — significant rate changes for period.
- `GET /api/statistics?from=&to=&bank_id=&currency_id=` — rate change statistics for charts.

---

## 6. Frontend Architecture

### State Management (Pinia)
- `useBankStore` — bank list, selected bank, loading state.
- `useCurrencyStore` — currency list, current rates, NBU rates, average rates, history, statistics.
- `useAuthStore` — user, token, login/register/logout actions.
- `useNotificationStore` — subscription management, notification toggle.

### Pages & Components
- `/` — Dashboard: current rates table with bank + currency filter, NBU rate row, average rate row.
- `/banks` — Bank list with search.
- `/banks/:id` — Bank detail: info card, rates table, branches on Leaflet map.
- `/statistics` — Time-series ApexCharts with date range picker and bank/currency filter.
- `/login`, `/register`, `/profile` — Auth pages.

### UI Requirements
- Skeleton loaders for all async states.
- Atomic components: BaseButton, BaseInput, BaseCard, BaseSelect, BaseBadge.
- Responsive layout: sidebar collapses to bottom nav on mobile.
- Leaflet map with custom markers and markercluster plugin for dense areas.
- ApexCharts time-series for rate history with zoom and tooltip.

---

## 7. External API Reference
- Minfin currency list: `https://minfin.com.ua/api/currency/list?type=money&locale=uk`
- Minfin rates: `https://minfin.com.ua/api/currency/rates/banks/{currency_code}`
- NBU rates: `https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json`
- Finance.ua banks: `https://finance.ua/banks/api/organizationsList?locale=uk`
- Finance.ua branches: `https://finance.ua/api/organization/v1/branches?slug={bank_slug}&locale=uk`

---

## 8. Implementation Order
1. Migrations, Models, Seeders with mock data.
2. BankApiInterface + MinfinProvider + NbuProvider + DTOs.
3. Scheduler + Jobs (rates + branches).
4. Rate change detection + RateHistory + Notifications.
5. API endpoints (public first, then authenticated).
6. Sanctum auth + profile + subscriptions.
7. Vue pages: Dashboard → Banks → Statistics → Auth.
8. Map integration (Leaflet + markercluster).
9. Charts integration (ApexCharts).

---

## 9. AI Behavior Rules
- **"как тебе"** — analyze my code: list Pros/Cons, suggest industry-standard alternatives with reasoning.
- **Code style:** PSR-12 for PHP, ESLint/Prettier for Vue.
- **No lists or emojis** unless explicitly requested.
- **Language:** Russian.
- **No boilerplate explanations.** Focus on implementation and architectural decisions.
- **Never generate partial code.** Always produce complete, runnable implementations.
- **Always handle edge cases:** null checks, API timeouts, empty responses, duplicate upserts.
- **When generating Jobs or Services**, always include logging via `Log::info/error` with context.
- **When generating API responses**, always use Laravel API Resources, never return raw models.