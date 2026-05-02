# Snippit — URL shortener

A full-stack **Laravel** application that lets signed-in users create short links, track clicks (including QR-assisted visits), browse and manage links from a dashboard, and inspect per-link analytics. The UI is built with **Livewire** and **Flux UI** (Tailwind CSS v4); authentication is handled by **Laravel Fortify**.

---

## Features

| Area | What it does |
|------|----------------|
| **Shortening** | Authenticated users paste a long URL on `/` (home). The app validates it, generates a **unique 6-character** `short_code`, stores the row, and shows the public short URL. |
| **Redirects** | `GET /{shortCode}` resolves an active link and **302 redirects** to the original destination while recording a **`UrlClick`** (`via_qr = false`). |
| **QR codes** | `GET /{shortCode}/qr` redirects like the short link but marks **`via_qr = true`** for attribution. Owner-only PNG QR endpoints encode that QR URL. The link shelf shows a thumbnail; **click opens a modal** with a larger preview and **Download PNG**. |
| **Dashboard** | `/dashboard` lists the user’s URLs with search, total clicks, last-7-days clicks, analytics and delete actions. |
| **Analytics** | Per-link page at `/urls/{id}/analytics` with a **Livewire chart** component. |
| **Auth** | Register, login, logout, password reset, email verification, optional 2FA — via Fortify and starter-kit views. |
| **Rate limiting** | Shortening is limited to **30 successful shorten operations per user per minute** (see `AppServiceProvider`). |

---

## Tech stack

| Layer | Packages / tools |
|--------|-------------------|
| Runtime | PHP **^8.3**, Laravel **^13** |
| Auth | `laravel/fortify` |
| UI | `livewire/livewire` **^4**, `livewire/flux` **^2** (Flux free edition) |
| QR images | `bacon/bacon-qr-code` (GD renderer in `QrCodeController`) |
| Frontend | Vite **^8**, Tailwind **^4**, `@tailwindcss/vite` |
| Tests | Pest **^4**, `pestphp/pest-plugin-laravel` |
| Quality | Laravel Pint, Laravel Boost (optional MCP in Cursor) |

---

## Requirements

- PHP **8.3+** with extensions Laravel needs (mbstring, openssl, pdo, tokenizer, xml, ctype, json, etc.) and **GD** (for QR PNG generation).
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) **18+** (for Vite / npm)
- A database: **SQLite** works out of the box; MySQL/PostgreSQL are supported via Laravel configuration.

---

## Quick start

### 1. Clone and install PHP dependencies

```bash
git clone <repository-url> url-shortner
cd url-shortner
composer install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit **`.env`** at minimum:

| Variable | Purpose |
|----------|---------|
| `APP_NAME` | Shown in the UI wordmark (default in `config/app.php` is `Snippit` if unset). |
| `APP_URL` | **Must match** your public base URL so generated short links and QR targets are correct. |
| `DB_*` | Database connection (default `.env.example` uses SQLite). |

For SQLite, ensure the file exists:

```bash
touch database/database.sqlite
```

### 3. Database and storage

```bash
php artisan migrate
```

If you use the `database` queue driver or session driver, ensure the default migrations for **jobs**, **cache**, and **sessions** have been applied (included in a fresh Laravel app).

### 4. Frontend assets

```bash
npm install
npm run build
```

For local development with hot reload:

```bash
npm run dev
```

### 5. Run the app

```bash
php artisan serve
```

Visit `APP_URL`, register or log in, and use **Clip URL** (home) or **Link shelf** (dashboard).

**Composer shortcut:** the project defines a `setup` script that runs install, copies `.env` if missing, generates a key, migrates, and builds front-end assets — inspect `composer.json` → `scripts.setup` for the exact sequence.

**All-in-one dev (optional):**

```bash
composer run dev
```

This runs the dev server, queue listener, Pail, and Vite concurrently (see `composer.json` → `scripts.dev`).

---

## Project structure (high level)

```
app/
├── Http/Controllers/
│   ├── QrCodeController.php      # PNG QR for authenticated owner
│   └── RedirectController.php    # Public short link + /qr redirect + click logging
├── Livewire/
│   ├── ShortenUrl.php            # Home shorten form
│   ├── UrlList.php               # Dashboard table, QR modal, delete
│   └── UrlChart.php              # Analytics chart (by url id)
├── Models/
│   ├── Url.php                   # Global scope: active links only; scopes for user ownership
│   ├── UrlClick.php
│   └── User.php
└── Services/
    └── UrlShortenerService.php   # Creates Url + returns public short URL

resources/views/
├── home.blade.php                # / — shorten Livewire
├── dashboard.blade.php           # /dashboard — url list
├── analytics.blade.php           # /urls/{id}/analytics
├── livewire/                     # Livewire Blade views
└── layouts/app/                  # Header / sidebar layouts, branding

routes/web.php                    # Auth routes + wildcard redirects (order matters)
tests/                            # Pest feature + unit tests
```

---

## Routing (important)

Authenticated routes live inside the `auth` middleware group:

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | `/` | `home` | Shorten form |
| GET | `/dashboard` | `dashboard` | Link shelf |
| GET | `/urls/{id}/analytics` | `urls.analytics` | Analytics (numeric `id`) |
| GET | `/urls/{id}/qr` | `urls.qr` | QR PNG (owner) |
| GET | `/urls/{id}/qr/download` | `urls.qr.download` | QR PNG attachment (owner) |

**Wildcard public routes are registered last** so they do not steal paths like `login` or `dashboard`:

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | `/{shortCode}` | `redirect` | `shortCode`: `[A-Za-z0-9]+` |
| GET | `/{shortCode}/qr` | `redirect.qr` | Same pattern; QR click path |

Fortify’s post-login **home** is configured to **`/`** in `config/fortify.php`.

---

## Data model

### `urls`

| Column | Notes |
|--------|--------|
| `user_id` | Owner; foreign key to `users` |
| `original_url` | Destination |
| `short_code` | **6** chars, **unique**, indexed |
| `is_active` | Default `true`; global scope on `Url` hides inactive rows from normal queries |

### `url_clicks`

Each redirect (normal or QR) creates a row with `clicked_at`, `ip_address`, `user_agent`, `referrer`, and **`via_qr`** (boolean).

---

## Security and authorization

- Shortening, dashboard, analytics, and owner QR routes require **authentication**.
- `Url::forUser($userId)` (and similar queries) enforce **ownership** for analytics and QR endpoints.
- Password rules tighten automatically in **production** (see `AppServiceProvider::configureDefaults()`).

---

## Testing and code style

Run the full test suite:

```bash
php artisan test --compact
```

Run a single file or filter:

```bash
php artisan test --compact tests/Feature/RedirectControllerTest.php
php artisan test --compact --filter=shorten
```

Lint PHP with Pint:

```bash
vendor/bin/pint --dirty
# or
composer run lint
```

CI-oriented script (clears config, runs Pint in test mode, then Pest):

```bash
composer run test
```

---

## Configuration reference

Beyond `.env`, notable config files:

| File | Relevance |
|------|-----------|
| `config/fortify.php` | Features (registration, verification, 2FA, etc.) and **`home`** redirect path |
| `config/auth.php` | Guards and providers |
| `config/database.php` | Connection used by migrations and Eloquent |

---

## Troubleshooting

| Issue | Suggestion |
|-------|------------|
| Generated short URLs point to the wrong host | Set **`APP_URL`** to your real public URL (include `https` in production). |
| QR or shorten errors | Ensure **GD** is enabled; QR generation uses `GDLibRenderer`. |
| Vite / asset errors in the browser | Run `npm run dev` or `npm run build`. |
| Clipboard / “copy” in Flux | Many browsers require **HTTPS** (or `localhost`) for the Clipboard API. |

---

## License

This project inherits the **MIT** license from the Laravel / starter-kit lineage. See `composer.json` and repository `LICENSE` if present.

---

## Further reading

- [Laravel documentation](https://laravel.com/docs)
- [Livewire documentation](https://livewire.laravel.com/docs)
- [Flux UI components](https://fluxui.dev/components) (free tier)
- [Laravel Fortify](https://laravel.com/docs/fortify)
