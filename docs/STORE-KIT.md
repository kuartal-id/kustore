# Kustore: store kit (Google Play and App Store)

Written 15 October 2026 from the code in this repo. Items marked **[SUPPLY: …]** are for Diemas; **(check)** means a
store-policy point to confirm against the current Play/Apple text before submitting.

## App identity

| | |
|---|---|
| App name | Kustore |
| Package / bundle id | `id.kustore.app` (permanent once uploaded) |
| Category | Business (Play) / Business (App Store) |
| Website | https://kustore.id |
| Support URL | https://kustore.id/support |
| Privacy policy URL | https://kustore.id/privacy (still marked draft: needs legal review before submitting) |
| Account deletion URL | https://kustore.id/support#delete-account (in-app: Settings › Delete account) |
| Contact email | `config('kustore.support_email')`, default hello@kuartal.id. **[SUPPLY: confirm or set KUSTORE_SUPPORT_EMAIL]** |

## Listing text

Character counts checked: Play short ≤ 80, Apple subtitle ≤ 30.

**Play short description**
- ID: Halaman link-in-bio, profil, dan toko online Anda dalam satu alamat. (68)
- EN: Your link-in-bio, personal page and online shop at one address. (63)

**Apple subtitle**
- ID: Link, profil & toko Anda (24)
- EN: Your links, page and shop (25)

**Full description (ID)**

Kustore menaruh link, profil, dan produk yang Anda jual di satu halaman rapi, di alamat milik Anda: kustore.id/namaanda.

• Link in bio: Instagram, TikTok, WhatsApp, YouTube dan lainnya, dalam urutan yang Anda pilih.
• Toko: jual produk fisik, digital, atau jasa dengan harga dalam Rupiah. Tambahkan hingga enam foto per produk, langsung dari kamera.
• Pembayaran manual: transfer bank, e-wallet, atau kode QRIS Anda sendiri. Pembeli mendapat instruksi pembayaran dan email pesanan; Anda menandai pesanan sebagai lunas.
• Pesanan dan pelanggan: lihat siapa yang memesan, hubungi lewat WhatsApp, ekspor daftar pelanggan.
• Analitik sederhana dan privat: kunjungan, klik link, produk yang dilihat, dan penjualan. Tanpa pelacak pihak ketiga.
• Bagikan halaman Anda dengan satu ketukan.
• Masuk dengan Kuartal ID, akun yang sama untuk semua produk Kuartal.

Gratis untuk mulai.

**Full description (EN)**

Kustore puts your links, your profile and the things you sell on one clean page, at an address that is yours: kustore.id/yourname.

• Link in bio: Instagram, TikTok, WhatsApp, YouTube and more, in the order you choose.
• Storefront: sell physical products, digital goods or services, priced in Rupiah. Add up to six photos per product, straight from your camera.
• Manual payments: bank transfer, e-wallet or your own QRIS code. Buyers get payment instructions and an order email; you mark orders as paid.
• Orders and customers: see who ordered, message them on WhatsApp, export your customer list.
• Simple, private analytics: visits, link clicks, product views and sales. No third-party trackers.
• Share your page in one tap.
• Sign in with Kuartal ID, the same account for every Kuartal product.

Free to start.

## Screenshots

Phone: 1080×1920 (Play) and 1290×2796 or 1320×2868 (Apple 6.9"). Suggested order, using demo data (`php artisan migrate --seed`):
1. A storefront (kustore.id/demo). 2. Overview. 3. Add product with photos. 4. Orders. 5. Analytics. 6. Payments (QRIS).
Never use a real customer's details.

## Privacy answers (Play Data safety / Apple App Privacy)

| Data | Collected | Purpose | Linked to user | Shared |
|---|---|---|---|---|
| Name, email, user ID (Kuartal ID or email sign-up) | Yes | App functionality, account management | Yes | No |
| Seller's store content (bio, links, products, photos, QRIS image, WhatsApp number) | Yes | App functionality (published by the seller) | Yes | Shown publicly by the seller's choice |
| Buyer's name, email, phone, address (at checkout) | Yes | App functionality (orders) | Yes | Shared with the seller they buy from |
| Purchase history (orders) | Yes | App functionality | Yes | No |
| Photos | Only those the user uploads | App functionality | Yes | Public when the seller publishes them |
| App interactions (page views, link clicks) | Yes, aggregated, no IP stored | Analytics for the seller | No (daily-rotating hash) | No |
| Payment card / bank numbers | **No** (payments go buyer → seller directly) | | | |
| Ads / tracking SDKs | **None** | | | |

Encrypted in transit: yes (HTTPS only). Users can delete their data: yes, in-app (Settings › Delete account) and by email.
**(check)** Apple's definition of "Linked to you" for the aggregated analytics.

## Reviewer notes (paste into Play "App access" and App Store Connect)

```text
Kustore lets anyone publish a page with links and a small shop at kustore.id/username.
Sign in: tap "Use email and password instead" on the login screen.
Test account: [SUPPLY: reviewer email]  Password: [SUPPLY: reviewer password]
(This test account owns a published demo store with products and orders.)

Things to try:
- Overview: share your page with the native share sheet.
- Products > Add product: take a photo with the camera (up to six photos).
- Orders: open an order, mark it as paid.
- Customers, Analytics, Payments (upload a QRIS image), Settings.
- Account deletion: Settings > Delete account (type the username to confirm).

Payments are made directly between buyer and seller (bank transfer, e-wallet or the seller's QRIS).
The app sells no digital goods and has no in-app purchases.
```

## Review risks

- **Apple 4.2 (minimum functionality).** The app is the website in a native shell. Native parts: camera/photo picker,
  native share, deep links, offline banner, back button. Push notifications for new orders would strengthen it
  (needs Firebase; not built).
- **Apple 3.1.1 / Play payments (check).** Physical goods and services sold by independent sellers are outside
  in-app purchase rules. If Kustore later sells paid plans to sellers inside the app, that changes.
- **Privacy policy is a draft.** Both stores need a final policy URL. Get it reviewed first.
- **Sign in with Apple (check, Apple 4.8).** Kuartal ID offers Google sign-in. Email and password sign-up also exists.

## Owner checklist

- [ ] Confirm package id `id.kustore.app`.
- [ ] Reviewer test account with a published demo store **[SUPPLY]**.
- [ ] Final privacy policy and terms (legal review).
- [ ] Support email (`KUSTORE_SUPPORT_EMAIL`) and SMTP on the server (order and password-reset emails need it; mail is `log` until set).
- [ ] Play Console app, upload key, Play App Signing → SHA-256 into `public/.well-known/assetlinks.json`.
- [ ] Apple Team ID → `resources/well-known/apple-app-site-association.json`.
- [ ] Store links once live: `KUSTORE_PLAY_STORE_URL`, `KUSTORE_APP_STORE_URL` (the home page shows them).
