# AGENTS.md: rules for anyone (human or AI) working on Kustore

Read this first. Also read `docs/STATUS.md` (current state), `docs/PRODUCT_BRIEF.md` (product intent) and `DEPLOY.md` (production).

## What Kustore is
Kuartal's storefront and creator-commerce platform: link-in-bio + personal site + lightweight storefront at `kustore.id/{username}`.
"One person. One identity. One storefront." Indonesian-first: IDR default, manual payments first, plain-English UI
("Sales", "Customers", "Products"; no jargon).

## Brand rules
- Always write **Kustore**. Never "KuStore", "Ku Store" or "KUSTORE" in copy. Tagline: "Kustore by Kuartal".
- Palette: navy `#1C3640`, green `#36CC64` (accent, use sparingly), black `#000000`, ice `#F1FBFF`.
- Logo: use `<x-wordmark>` (sizes `sm`/`md`/`lg`), never a text wordmark. Files live in `public/images/brand/`: `kustore-logo-light.png` (light mode)
  and `kustore-logo-dark.png` (dark mode), 320x64 transparent PNGs, shown one at a time with `dark:hidden` / `hidden dark:block`; always `alt="Kustore"` with width/height.
  Favicon/app icons (`public/favicon.ico`, `apple-touch-icon.png`, `icon-192.png`, `icon-512.png`) use the green "re" mark; default og:image is `images/brand/kustore-og.png`.
  The 2000x1000 owner originals are not in the repo; regenerate from them (trim, resize, `pngquant`) rather than upscaling these files.
- Poppins (self-hosted in `public/fonts`) for headings/UI; body `'Arial Nova', Arial, Helvetica, sans-serif`.
- Restrained, editorial look: whitespace, rounded cards, pill buttons, subtle shadows. **No** gradients, glowing blobs,
  glassmorphism or heavy animation. Mobile-first; the dashboard must feel native on phones.

## Stack
Laravel 13 · PHP 8.4 · MySQL (prod) / SQLite (dev, tests) · Blade · Tailwind v4 prebuilt via standalone CLI · vanilla JS.

## Hard rules
1. **No Node.js in production.** Do not add `@vite`, `package.json` build steps, the Tailwind Play CDN, or Google Fonts/any CSS/JS CDN.
   After changing views or `resources/css/app.css`, run `bin/build-css.sh` and **commit `public/css/app.css`**.
2. **No inline scripts or styles** (`<script>…</script>` with code, `style="…"`, `onclick=`): the CSP is `script-src 'self'; style-src 'self'`.
   JSON-LD (`type="application/ld+json"`) is fine. Put JS in `public/js/app.js`.
3. **Never commit secrets** (`.env`, keys, DB passwords, OAuth secrets). Only `.env.example` holds placeholders.
4. Keep the standard Laravel layout with `public/` as the docroot. Keep the root `.htaccess` deny list in sync if you add top-level folders.
5. Don't hard-code a payment provider. Go through `PaymentProviderInterface` / `PaymentManager` and `config/kustore.php`.
6. Money is integer minor units (`App\Support\Money`). IDR exponent is 0.

## Security rules
- Auth primary: Kuartal ID OIDC (`app/Auth/KuartalId`). Keep PKCE S256, state and nonce checks, and ID token verification
  (signature/iss/aud/exp/nonce). Identify users by `kuartal_id_sub` only. **Never link or merge accounts by email.**
- Google sign-in comes **through Kuartal ID**. Do not add Socialite/Google directly to Kustore.
- Scopes come from `KUARTAL_ID_SCOPES` (default `openid profile email`). Add `entitlements` only once the IdP advertises it.
- Email/password is secondary: rate limited, session regenerated on login, email verification before publishing.
- Every dashboard action authorizes through policies (owner only). Public pages must 404 for unpublished or suspended stores.
- Uploads: images only, validated, re-encoded, random names, public disk only. Never trust a client filename.
- Analytics: first-party only, no raw IPs (use the daily-rotating hash in `App\Services\Analytics`).
- Keep `SecurityHeaders` middleware intact; tests assert it.

## Commands
```bash
composer install && cp .env.example .env    # then switch to sqlite for local (see README)
php artisan migrate --seed && php artisan storage:link && php artisan serve
php artisan test                             # must be green before every commit/PR
bin/build-css.sh                             # rebuild committed CSS
php artisan kustore:admin <id|email|sub>     # grant admin
```

## Tests
Feature tests live in `tests/Feature` (PHPUnit, SQLite in-memory). Add or adjust tests with every behaviour change,
especially auth, checkout/stock, policies, visibility and headers.

## Deploy process
GitHub `main` → Hostinger Git deployment into `public_html` (repository root = web root, protected by the root `.htaccess`).
Then over SSH with `/opt/alt/php84/usr/bin/php`: `composer install --no-dev -o`, `artisan migrate --force`, `config:cache`, `route:cache`, `view:cache`.
Full steps and the one-time cut-over checklist: `DEPLOY.md`. Work on branches and open PRs. Never force-push `main`. Never reset the production DB.
