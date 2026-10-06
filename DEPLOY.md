# Deploying Kustore to Hostinger

Kustore needs **PHP 8.4 + Composer only**. No Node.js, npm or Vite on the server:
the stylesheet is prebuilt and committed (`public/css/app.css`), and fonts and JS are plain files in `public/`.

## 1. How the current kustore.id deployment works

Taken from the earlier `DEPLOYMENT_HANDOFF.md` (branch `docs/handoff-current-deployment-state`):

| Item | Value |
|---|---|
| Deploy method | Hostinger **Git deployment** from GitHub `kuartal-id/kustore`, branch `main` |
| Deploy target | `~/domains/kustore.id/public_html` (the repository root **is** the web root) |
| Composer | Hostinger runs `composer install --prefer-dist --quiet --no-interaction` on deploy (this failed once, then worked) |
| PHP | Website must use PHP 8.4. On SSH the default `php` is **8.3**; use `/opt/alt/php84/usr/bin/php` |
| Database | MySQL on `localhost`, database/user `u375048224_kustore` (password only in the server `.env`) |
| `.env` | Exists only on the server, untracked |
| Server-only changes | `cache` and `sessions` migrations were created **on the server** (`2026_10_04_122652_create_cache_table.php`, `2026_10_04_124411_create_sessions_table.php`). Runtime folders under `storage/` and `bootstrap/cache` were recreated by hand after a deploy removed them |
| Last known state | `403 Forbidden` after a deploy. An earlier root `.htaccess` rewrite attempt returned 500, but the cause was never confirmed |

**Security incident:** because the repository root was the web root, `storage/logs/laravel.log`, `vendor/` and `composer.json`
were public. Treat **APP_KEY, the DB password and every OAuth client secret (Kuartal ID, Google)** as leaked and rotate them.

## 2. How this version handles the web root

- Standard Laravel layout. `public/` is the real document root.
- The **root `.htaccess`** sends every request into `public/` and returns 403 for `app/`, `bootstrap/`, `config/`, `database/`,
  `resources/`, `routes/`, `tests/`, `vendor/`, `storage/{logs,framework,app}`, dotfiles (`.env`, `.git`), `composer.*`, `artisan`, `*.md`, `*.log`, `*.sqlite`.
- Uploaded files are served from `/storage/...` through the `public/storage` symlink. `storage/app/public/.htaccess` turns off PHP and script execution there.
- Tested locally on Apache 2.4 with the **repository root as DocumentRoot** (the same setup as Hostinger):
  `/`, `/demo`, CSS/JS/fonts/uploads returned 200. `/storage/logs/laravel.log`, `/.env`, `/vendor/autoload.php`,
  `/composer.json`, `/artisan`, `/README.md`, `/app/...`, `/config/...`, `/.git/config`, `/database/database.sqlite` returned 403.
  A PHP file dropped into uploads returned 403. Generated URLs had no `/public` prefix.
  Hostinger runs LiteSpeed, which reads the same `.htaccess` syntax, but **re-run the curl checks in step 4 on the live server**.
- If hPanel lets you set the domain's document root to `public_html/public`, do that as well. The root `.htaccess` then simply stops being used.

## 3. Safe deploy sequence

Do these in order. Nothing here touches the existing production database.

### Before merging the PR
1. **Back up**: in hPanel → Databases → phpMyAdmin, export `u375048224_kustore`. Download a private copy of the server `.env`. Never commit either.
2. **New database** (recommended): the new migrations are not compatible with the old schema (`users`, `stores` … already exist with different columns), so `migrate` against the old database would fail.
   Create a fresh MySQL database and user (e.g. `u375048224_kustore2`) with a new strong password. Keep the old database untouched so you can roll back or move data over later.
3. **PHP version**: hPanel → Advanced → PHP Configuration → **PHP 8.4** for kustore.id. `composer.json` requires `^8.4`, and Composer's platform check fails on 8.3.
4. **Kuartal ID client** (at id.kuartal.id): create a confidential client, or rotate the existing secret, with
   - Redirect URI: `https://kustore.id/auth/kuartal/callback`
   - Scopes: `openid profile email` (do not request `entitlements` until the IdP advertises it, then add it via `KUARTAL_ID_SCOPES`)
   - Grant: authorization_code with PKCE S256. Token auth: `client_secret_post`.
5. **Google**: Kustore no longer has its own Google login (Google comes through Kuartal ID). Revoke the old Google OAuth client secret in Google Cloud Console.

### Deploy
6. Merge the PR into `main`. Hostinger auto-deploys, or use hPanel → Git → Deploy.
7. SSH in and run:

```bash
cd ~/domains/kustore.id/public_html
PHP=/opt/alt/php84/usr/bin/php

# Remove leftovers from the old app that Git may not delete (they are untracked)
rm -f storage/logs/*.log                                   # the leaked log
rm -f database/migrations/2026_10_04_122652_create_cache_table.php \
      database/migrations/2026_10_04_124411_create_sessions_table.php
rm -f index.php                                            # old root entry point, no longer used
git status --short                                         # should show nothing unexpected

# Runtime folders (tracked with .gitignore placeholders, but make sure)
mkdir -p storage/framework/{cache/data,sessions,views,testing} storage/logs storage/app/public bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Dependencies (production only)
$PHP "$(command -v composer)" install --no-dev --optimize-autoloader --no-interaction

# Environment: start from .env.example and fill in values
cp .env .env.old-backup 2>/dev/null || true               # keep privately, delete after go-live
cp .env.example .env
nano .env    # DB_* = NEW database, KUARTAL_ID_CLIENT_ID/SECRET = new values, APP_DEBUG=false
$PHP artisan key:generate --force                          # rotates APP_KEY

$PHP artisan migrate --force
$PHP artisan storage:link || ln -s ../storage/app/public public/storage
$PHP artisan optimize:clear
$PHP artisan config:cache && $PHP artisan route:cache && $PHP artisan view:cache
```

Required `.env` values (see `.env.example`): `APP_NAME=Kustore`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://kustore.id`,
`DB_CONNECTION=mysql`, `DB_HOST=localhost`, `DB_PORT=3306`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`,
`KUARTAL_ID_ISSUER=https://id.kuartal.id`, `KUARTAL_ID_CLIENT_ID`, `KUARTAL_ID_CLIENT_SECRET`,
`KUARTAL_ID_REDIRECT_URI=https://kustore.id/auth/kuartal/callback`, `KUARTAL_ID_SCOPES="openid profile email"`,
`KUSTORE_PAYMENT_PROVIDER=manual`, `KUSTORE_CURRENCY=IDR`, `SESSION_DRIVER=database`, `SESSION_SECURE_COOKIE=true`, `CACHE_STORE=database`.
Remove any old `GOOGLE_*` variables. **Never run `db:seed` in production** (the seeder refuses to run there anyway).

### Verify
8. From your computer:

```bash
for u in / /login /terms /auth/kuartal/redirect; do curl -s -o /dev/null -w "%{http_code} $u\n" https://kustore.id$u; done   # 200 / 200 / 200 / 302
for u in /.env /composer.json /composer.lock /storage/logs/laravel.log /vendor/autoload.php /artisan /README.md /app/Models/User.php /.git/config; do
  curl -s -o /dev/null -w "%{http_code} $u\n" https://kustore.id$u; done                                                      # all 403 (or 404)
curl -sI https://kustore.id/ | grep -iE 'strict-transport|content-security|x-frame|x-content-type|referrer-policy|permissions-policy|x-powered-by'
```

9. Sign in with Kuartal ID, create your store, then give yourself admin access:
   `$PHP artisan kustore:admin <your user id or email>`.
10. Publish a test store, place a test order, mark it paid in the dashboard, then unpublish or delete it.

### Roll back
Redeploy the previous `main` commit in hPanel and put the backed-up `.env` back. It points at the old, untouched database.

## 4. Every later deploy
```bash
cd ~/domains/kustore.id/public_html && PHP=/opt/alt/php84/usr/bin/php
$PHP "$(command -v composer)" install --no-dev --optimize-autoloader --no-interaction
$PHP artisan migrate --force
$PHP artisan optimize:clear && $PHP artisan config:cache && $PHP artisan route:cache && $PHP artisan view:cache
```
If you changed any styles, run `bin/build-css.sh` **locally** and commit `public/css/app.css` before deploying.

## Notes
- If kustore.id is put behind Cloudflare or another proxy, configure trusted proxies in `bootstrap/app.php` so HTTPS and client IPs (used for rate limits) are detected correctly.
- Logs use the `daily` channel with 14 days kept, at `warning` level. They live in `storage/logs`, which is blocked from the web.
- Mail defaults to `log`. Configure SMTP (Hostinger mail) so email/password users receive verification links.
