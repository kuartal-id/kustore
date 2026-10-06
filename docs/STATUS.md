# Kustore: status

_Last updated: 2026-10-06 · branch `main`_

## Current state
A from-scratch Laravel 13 / PHP 8.4 rebuild that replaces the previous app (whose history is kept in git). It runs locally on SQLite
with demo data, and all feature tests pass.

## Production
- **Deployed to https://kustore.id on 2026-10-06** (PR #10, merge commit `cf8d52e`), via Hostinger Git deployment of `main`.
- Production DB: `u375048224_kustore_v2` (MySQL, localhost), freshly migrated. The old DB `u375048224_kustore` is left untouched for rollback.
- APP_KEY was rotated and a new DB was used. The old `GOOGLE_*` and old Kuartal ID values were removed from `.env`.
- Kuartal ID client registered at id.kuartal.id: **Kustore**, client id `01a11072-e3ad-71b3-9c38-6020f15d22c2`
  (confidential, authorization_code + refresh_token, redirect `https://kustore.id/auth/kuartal/callback`). The secret lives only in the server `.env`.
- `storage:link` doesn't work on Hostinger (`exec()` is disabled). `public/storage` was created by hand with `ln -s ../storage/app/public public/storage`.
- Hostinger's CDN (hcdn) replaces the app's `Content-Security-Policy` header with `upgrade-insecure-requests`. The app sends the full CSP (checked at origin).
  To fix this, look at the hPanel CDN/security settings.
- Rollback: redeploy the previous commit `6c2adb7` and restore the backed-up `.env` (server `~/backups/kustore-20261006T090044Z/`).

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
- **Full sign-in not yet tested end-to-end against the live Kuartal ID IdP.** The client is registered and `/auth/kuartal/redirect` sends a correct authorize request. Still needs a real sign-in.
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
1. Owner signs in at https://kustore.id with Kuartal ID, then grants admin on the server: `/opt/alt/php84/usr/bin/php artisan kustore:admin <email>`.
   Then do a test store, a test order and mark it paid.
2. Configure SMTP (`MAIL_MAILER` is `log` for now); add password reset for email accounts.
3. Official logo/favicon asset; revoke the old Google OAuth client and the old Kuartal ID / DB credentials, which are considered leaked.
4. Payment gateway (see Phase 2).
5. **Phase 2 (payments and customers):** Xendit or Midtrans provider (QRIS, VA, e-wallet) + webhook controller + idempotency;
   Customers page; order notification emails/WhatsApp links; digital download delivery; multiple product images; discount codes.
6. **Phase 3 (growth):** Analytics page (daily chart, referrers, top links/products); custom domains; design the remaining layout presets;
   verification workflow (business/official badges); username change with redirect; sitemap.xml.
7. **Phase 4 (ecosystem):** Kuartal ID `entitlements` scope → plans/Pro features; team members for business/organisation accounts;
   public API/webhooks; cross-store discovery.
