# Kustore: status

_Last updated: 2026-10-06 · branch `rebuild/kustore-mvp`_

## Current state
A from-scratch Laravel 13 / PHP 8.4 rebuild that replaces the previous app (whose history is kept in git). It runs locally on SQLite
with demo data, and all feature tests pass. **Not deployed yet.** The production cut-over checklist is in `DEPLOY.md`.

## Done
- Kuartal ID OIDC login (PKCE S256, state, nonce, JWKS-verified RS256 ID token, discovery/JWKS caching, key-rotation retry), scopes via `KUARTAL_ID_SCOPES`.
- Email/password fallback with verification-before-publish, rate limits, session regeneration; no email-based account linking.
- Onboarding (username rules + reserved list, account type), one store per user.
- Storefront (Minimal and Commerce layouts, light/dark), product page with JSON-LD, checkout → pending order via `ManualPaymentProvider`,
  signed confirmation URL with the seller's instructions, stock locking/decrement.
- Dashboard: overview stats, publish toggle, store editor (avatar upload, appearance, layout, SEO, payment instructions),
  links CRUD + reorder, products CRUD + image, orders list/detail/status. Mobile bottom nav.
- Admin: users/stores list, suspend toggle, `php artisan kustore:admin`.
- First-party analytics with daily-rotating visitor hash.
- Security: CSP without unsafe-inline, HSTS, XFO DENY, nosniff, Referrer/Permissions-Policy, X-Powered-By removed, secure cookies,
  hardened root `.htaccess` (tested on Apache with the repository root as DocumentRoot), no-exec uploads folder.
- Tests: 86 PHPUnit tests (usernames, OIDC rejection paths, visibility, checkout/stock, policies, headers, uploads, auth).

## Known gaps / stubs
- **Not verified against the live Kuartal ID IdP**: needs a registered client. The flow follows the published discovery document and is covered by tests with a fake IdP.
- Password reset for email accounts is not implemented (the `password_reset_tokens` table exists).
- Outgoing mail is `log` by default. SMTP must be configured for verification emails.
- Username changes, account deletion and data export are not built.
- One image per product (the `product_images` table supports more).
- Layout presets `creator`, `professional` and `editorial` map to `minimal`/`commerce`.
- Customers, Analytics and Payments pages are "Coming soon" (data is already collected in `customers`, `analytics_events`, `payments`).
- Shipping cost is "arranged by seller" (`shipping_total` is always 0).
- Terms and Privacy are drafts and need legal review.
- Logo is a placeholder text wordmark and favicon; no official asset was found in the old repo.
- Production DB: the old schema is incompatible. The plan is a fresh database (see `DEPLOY.md`). Old data, if any matters, is not migrated.

## Next steps (priority order)
1. Cut over to production following `DEPLOY.md` (new DB, rotate APP_KEY/DB password/OAuth secrets, Kuartal ID client registration).
2. Configure SMTP; add password reset for email accounts.
3. **Phase 2 (payments and customers):** Xendit or Midtrans provider (QRIS, VA, e-wallet) + webhook controller + idempotency;
   Customers page; order notification emails/WhatsApp links; digital download delivery; multiple product images; discount codes.
4. **Phase 3 (growth):** Analytics page (daily chart, referrers, top links/products); custom domains; design the remaining layout presets;
   verification workflow (business/official badges); username change with redirect; sitemap.xml.
5. **Phase 4 (ecosystem):** Kuartal ID `entitlements` scope → plans/Pro features; team members for business/organisation accounts;
   public API/webhooks; cross-store discovery.
