# Kustore: product brief (MVP rebuild)

Summary of the brief used for the October 2026 from-scratch rebuild.

## Product
Kustore is Kuartal's storefront/creator-commerce platform: **link-in-bio + personal site + lightweight storefront**.
"One person. One identity. One storefront." The brand is always written **Kustore**; tagline usage "Kustore by Kuartal".
Indonesian-first: IDR is the default currency. UI copy may be English but uses plain words (Sales, Customers, Products), no jargon.

## Deploy constraints (Hostinger shared hosting, GitHub → Hostinger Git deploy, no Node.js on the server)
- Production needs only PHP + Composer dependencies. No npm/Vite/Node to boot or serve. No `@vite`, no Tailwind Play CDN.
  CSS is built ahead of time and committed. Minimal vanilla JS only.
- The old deploy exposed `storage/logs/laravel.log`, `vendor/` and `composer.json` because the repository root was the web root.
  Keep `public/` as the docroot and ship a root `.htaccess` that rewrites into `public/` and denies framework paths.
- `.env.example` with the Kustore/Kuartal ID/payment variables. Never commit secrets. Database sessions/cache are fine.

## Auth
- Primary: "Continue with Kuartal ID": OIDC Authorization Code + PKCE S256, state and nonce verified, ID token verified
  (signature, issuer, audience, expiry, nonce). Discovery at `https://id.kuartal.id/.well-known/openid-configuration` (Laravel Passport IdP, RS256).
  Scopes `openid profile email` (configurable). Identify users by `sub`. Never link accounts by email.
- Google login is provided **through Kuartal ID** (Kuartal ID adds "Continue with Google"). There is no separate Google login in Kustore.
- Fallback: email/password (secondary): hashed passwords, email verification before publishing, rate-limited, session regeneration, CSRF.
- POST logout. Security headers (HSTS, CSP, frame-ancestors none, nosniff, Referrer-Policy, Permissions-Policy). Secure cookies.
- First login: choose a username (3–30, lowercase, URL-safe, reserved list) → account type (individual/business/organisation) → store → dashboard.

## MVP features
- Public storefront `kustore.id/{username}`: avatar, name, @username, verified badge, bio, location, website, category; links with
  14 icon presets, visibility and order; product grid; share buttons (copy, WhatsApp, X, Facebook, LinkedIn, Telegram); SEO/OG/Twitter/canonical.
  Unpublished or suspended stores return 404.
- Product page with JSON-LD. Checkout with a manual payment provider → pending order + seller's payment instructions; stock decrement; rate limited.
- Payment abstraction: `PaymentProviderInterface` + `ManualPaymentProvider`, chosen by `KUSTORE_PAYMENT_PROVIDER`.
- Dashboard: Overview, Store editor (profile, avatar, light/dark default, layout preset, SEO), Links, Products, Orders.
  Customers/Analytics/Payments shown as "Coming soon". Policies: users only touch their own store.
- First-party analytics (store view, link click redirect, product view), no third-party trackers, no raw IPs.
- Image uploads only (jpg/png/webp, ≤4 MB), random names, no execution in the uploads folder.
- Basic admin (`is_admin`): list users and stores, suspend stores.
- Landing page, login, Terms/Privacy placeholders (marked draft).

## Design
Navy `#1C3640`, green `#36CC64` (sparingly), black, ice `#F1FBFF`. Poppins (self-hosted) + Arial Nova/Arial body.
Generous whitespace, restrained borders, subtle shadows, rounded cards, pill buttons, editorial hierarchy. No gradients, blobs,
glassmorphism or excessive animation. Light and dark modes (localStorage + prefers-color-scheme). Mobile-first dashboard.
Match the clean look of kuartal.id and kuartalgroup.com.

## Roadmap (phases 2–4)
2. Payment gateway + webhooks, customers, notifications, digital delivery. 3. Analytics page, custom domains, more layouts,
verification workflow. 4. Kuartal ID entitlements/plans, teams, API/integrations, discovery.
