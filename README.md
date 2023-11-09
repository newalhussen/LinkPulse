# LinkPulse

URL shortener with link management, QR codes, expiry controls and privacy-friendly click analytics. Laravel 13, MySQL/MariaDB, Blade + Tailwind.

## Features
- Create short links with a random code or a **custom alias** (letters, digits, `-`, `_`; reserved words and duplicates rejected, case-insensitively).
- Fast `302` redirects from `/{code}`; edits, disabling and expiry take effect immediately (no cached redirects).
- **Expiration dates** and an **enable/disable** switch; expired or disabled links show a "no longer available" page (HTTP 410).
- **Click analytics**: total, 7-day and 30-day counts, a 30-day chart, top referring sites, device split (desktop/mobile/tablet) and recent clicks.
- **QR codes** as scalable SVG, inline and downloadable.
- Accounts (register/login/logout), per-user dashboard with search and pagination, edit destination/title/expiry, delete.

## Privacy and security
- A click stores only: time, the **referrer host** (not the full URL) and a device class. No IP address, user agent, cookie or fingerprint is saved. Bots, crawlers and link previews are redirected but not counted.
- Destinations must be `http(s)`, without embedded credentials, and cannot point back at this service (loop protection).
- Standard Laravel protections: CSRF, hashed passwords (min. 10 characters), session regeneration, validation on every input, login/register throttling, redirect throttling, ownership enforced by a policy (other users' links return 403).
- `login`, `register`, `dashboard`, `links`, `api` and other application paths are reserved and can never become short codes.

## Architecture
`routes/web.php` → controllers (`LinkController`, `RedirectController`, `QrController`, `AuthController`) → models `Link` / `Click`; request validation in `LinkRequest` and `SafeTargetUrl`; click classification in `ClickTracker`; code generation in `CodeGenerator`; QR via `bacon/bacon-qr-code` (SVG, no image extensions needed). Migrations in `database/migrations`.

## Setup
Prerequisites: PHP 8.3+ (mbstring, pdo_mysql), Composer, Node 20+, MySQL 8 or MariaDB 10.4+.
```bash
composer install
cp .env.example .env && php artisan key:generate    # then set DB_* and APP_URL
mysql -e "CREATE DATABASE linkpulse CHARACTER SET utf8mb4"
php artisan migrate
npm install && npm run build                        # or `npm run dev` while developing
php artisan serve                                   # http://localhost:8000
```
Set `APP_URL` to the public address you will serve short links from: it is used for the displayed short URL, the QR code, and the loop check.

## Tests
`php artisan test` runs 36 tests on in-memory SQLite (no database setup): link creation and every validation rule, uniqueness, ownership/authorization, redirects (counting, referrer host only, direct visits, bots not counted, 404/410, expiry, edits taking effect), QR output, analytics page, authentication, login throttling, and click classification.

## Notes
- Redirects use `302` on purpose so analytics and edits stay accurate; the cost is one request to your server per click.
- Counts are updated synchronously. For very high traffic, move click recording to a queue (`QUEUE_CONNECTION`) and aggregate periodically.
- Expiry dates are end-of-day in `APP_TIMEZONE`.
