# Kustore

**Kustore by Kuartal**: link-in-bio, personal site and lightweight storefront in one page at `kustore.id/{username}`.
*One person. One identity. One storefront.* Built for Indonesia: IDR by default, manual payments first.

> Brand: always write **Kustore** (never "KuStore" or "Ku Store"). Tagline: "Kustore by Kuartal".

## What's in the MVP
- **Sign in with Kuartal ID** (OpenID Connect, Authorization Code + PKCE S256, state + nonce, RS256 ID token verified against cached JWKS). Users are identified by the `sub` claim only. Accounts are never linked by email.
- Email/password as a secondary option: bcrypt, rate-limited, and email verification is required before a store can be published.
- Onboarding: pick a username (3–30 chars, lowercase, reserved list), then an account type (individual, business or organisation).
- Public storefront: profile, verified badge, links (14 icon presets), product grid, share buttons, SEO/OG/Twitter tags, canonical URLs. Unpublished or suspended stores return 404.
- Product page with schema.org `Product` JSON-LD, and a checkout that creates a **pending** order through the manual payment provider, shows the seller's payment instructions, and decrements stock.
- Dashboard (mobile-first, with bottom navigation on phones): Overview, Store editor, Links (CRUD + reorder), Products (CRUD + image), Orders (payment and fulfillment status).
- First-party analytics (store views, link clicks via `/go/{id}`, product views). No third-party trackers and no raw IP addresses.
- Basic admin at `/admin` (users with `is_admin`) with a suspend-store toggle.
- Security headers (CSP without `unsafe-inline`, HSTS, frame-ancestors none, nosniff, Referrer-Policy, Permissions-Policy), CSRF everywhere, POST logout, secure cookies.

## Stack
- Laravel 13, PHP 8.4, MySQL in production, SQLite for local dev and tests.
- Blade templates, Tailwind CSS v4 compiled **ahead of time** with the standalone CLI (`bin/build-css.sh`) into `public/css/app.css`, which is committed.
- Self-hosted Poppins (`public/fonts`, OFL). Body font: `'Arial Nova', Arial, Helvetica, sans-serif`.
- A few KB of vanilla JS (`public/js`): dark mode, copy link, image preview, small form helpers.
- **No Node.js is needed to install, boot or serve the app.**

## Local development
```bash
composer install
cp .env.example .env
# local overrides:
#   APP_ENV=local APP_DEBUG=true APP_URL=http://127.0.0.1:8000
#   DB_CONNECTION=sqlite (delete the other DB_* lines) SESSION_SECURE_COOKIE=false
touch database/database.sqlite
php artisan key:generate
php artisan migrate --seed       # demo data: /demo (Minimal layout) and /kopinusantara (Commerce layout)
php artisan storage:link
php artisan serve
```
Demo login (local only): `demo@kustore.test` / `kustore-demo`. This user is also an admin.

To try Kuartal ID locally, set `KUARTAL_ID_CLIENT_ID/SECRET` and `KUARTAL_ID_REDIRECT_URI=http://127.0.0.1:8000/auth/kuartal/callback`. That redirect URI must be registered at the IdP.

### Styles
Edit Blade/CSS, then run `bin/build-css.sh` (it downloads the Tailwind standalone binary on first run; add `--watch` while developing). Commit `public/css/app.css`.

### Tests
```bash
php artisan test        # SQLite in-memory; Kuartal ID is faked with a locally generated RSA key
```

## Architecture
```
app/
  Auth/KuartalId/KuartalIdClient.php   OIDC: discovery + JWKS cache, PKCE, token exchange, ID token verification
  Http/Controllers/                    Auth/, Dashboard/, Admin/, StorefrontController, CheckoutController, OnboardingController
  Http/Middleware/SecurityHeaders.php  CSP/HSTS/etc. on every response
  Models/                              User, Store, StoreLink, Product, ProductImage, Order, OrderItem, Customer, Payment, AnalyticsEvent
  Payments/                            PaymentProviderInterface, PaymentManager, ManualPaymentProvider, PaymentResult
  Policies/                            Store/Product/StoreLink/Order: owners only
  Services/                            CheckoutService (transaction + stock lock), Analytics, ImageUploader (re-encodes to WebP)
  Support/                             Money (integer minor units), Icons (inline SVG)
config/kustore.php                     reserved usernames, currencies, layouts, link icons, payment providers
```
- **Money** is stored as unsigned integers in the currency's minor unit. IDR uses exponent 0, so Rp 150.000 is stored as `150000`.
- **Uploads**: images only (JPG/PNG/WebP, max 4 MB). They are validated by extension, MIME and `getimagesize`, re-encoded with GD to WebP under a random name on the `public` disk, and served from a folder where execution is disabled.
- **Layouts**: `minimal` and `commerce` are designed. `creator` renders as minimal; `professional` and `editorial` render as commerce (`config/kustore.php` → `layout_map`).

### Payment providers
```php
interface PaymentProviderInterface {
    public function key(): string;               // "manual"
    public function label(): string;
    public function initiate(Order $order): PaymentResult;  // record a Payment, return instructions or a redirect URL
}
```
The active provider comes from `KUSTORE_PAYMENT_PROVIDER` and is mapped in `config('kustore.payment_providers')`. Resolve it through `PaymentManager` or by type-hinting `PaymentProviderInterface`. To add a gateway, implement the interface, register it in the map, and add a webhook controller that updates `payments` and `orders.payment_status`.

## Roadmap
- **Phase 2 (payments and customers):** a payment gateway (Xendit or Midtrans: QRIS, VA, e-wallets) with webhooks; a Customers page; order emails/WhatsApp notifications; digital file delivery; multiple product images; discount codes.
- **Phase 3 (growth):** an Analytics page (charts, referrers, top links/products); custom domains; more layout presets (creator, professional, editorial); store verification workflow (business/official); username changes with redirects.
- **Phase 4 (ecosystem):** Kuartal ID entitlements and plans (Pro features), team members for business/organisation accounts, API and integrations, marketplace discovery across Kustore.

See `DEPLOY.md` for Hostinger, `AGENTS.md` for contributor/AI rules, `docs/STATUS.md` for current state.

Logo: there is no official Kustore logo file yet. The text wordmark (`resources/views/components/wordmark.blade.php`) and `public/favicon.svg` are placeholders.
