# Kustore: Android and iOS apps

This folder wraps the live website (https://kustore.id) in a native app with
[Capacitor 7](https://capacitorjs.com/). Nothing has been uploaded to a store yet.

- **App id / package name:** `id.kustore.app`. **Once an app is uploaded to Google Play this can never change.**
- **Opens:** `https://kustore.id/dashboard` (`server.url` in `capacitor.config.json`); signed-out users land on the login page.
  Website deploys update the app without a store release. `www/offline.html` shows if the first load fails.
- **Web build is unchanged:** the app-only behaviour lives in `public/js/native.js` on the website and does nothing in a normal browser
  (apart from turning on the Web Share API buttons where the browser supports them).
- `mobile/` is blocked from the web by the root `.htaccess`.

## What the app adds

| Feature | How |
| --- | --- |
| Photos from the camera | The website's photo inputs (products, profile, QRIS) offer "Take photo" or the photo library. Android declares `CAMERA`; iOS has `NSCameraUsageDescription` and `NSPhotoLibraryUsageDescription`. |
| Native share sheet | `@capacitor/share` for every `[data-native-share]` button (Overview and storefront share). |
| Android back button | `@capacitor/app`: goes back, or leaves the app on the first page. |
| Deep links | Android App Links (`https://kustore.id`, `autoVerify`) and iOS Universal Links (`applinks:kustore.id`), plus the scheme `id.kustore.app://`. |
| Offline banner | `@capacitor/network`. |
| Splash screen and status bar | `@capacitor/splash-screen`, `@capacitor/status-bar` (ice background, Kustore logo). |

Push notifications for new orders are not in this version: they need a Firebase project and server-side sending.
Orders are announced by email (Payments › Notifications) in the meantime.

## Prerequisites

- Node 20+.
- Android: JDK 21 and the Android SDK with platform 36, or Android Studio.
- iOS: a Mac with **Xcode 26 or later** and CocoaPods. iOS can't be built on Linux.

```sh
cd mobile
npm install
npm run sync          # cap sync + scripts/patch-native.mjs (permissions, deep links, target SDK 36)
```

Icons and splash screens are already generated from the Kustore app icon and logo (`assets/`). To regenerate:
`npx @capacitor/assets generate --iconBackgroundColor '#1C3640' --splashBackgroundColor '#F1FBFF' --splashBackgroundColorDark '#0A161B'`.

## Android

```sh
npm run apk:debug     # → android/app/build/outputs/apk/debug/app-debug.apk
npm run aab:release   # → android/app/build/outputs/bundle/release/app-release.aab (unsigned)
npm run open:android  # Android Studio
```

Target and compile SDK are **36** (Google Play's requirement since 31 Aug 2026). Bump `versionCode` in
`android/app/build.gradle` for every upload.

**Release signing:**
1. `keytool -genkeypair -v -keystore ~/kuartal-keys/kustore-upload.jks -alias upload -keyalg RSA -keysize 2048 -validity 10000`.
   Keep it out of git (`*.jks` is ignored) and back it up.
2. Sign the bundle: Android Studio › Build › Generate Signed App Bundle, or
   `jarsigner -keystore ~/kuartal-keys/kustore-upload.jks android/app/build/outputs/bundle/release/app-release.aab upload`.
3. Turn on **Play App Signing** when you create the app in Play Console.
4. **App Links:** copy the *App signing key* SHA-256 from Play Console › Setup › App signing into
   `public/.well-known/assetlinks.json` (replace the `00:00:…` placeholder), merge, deploy. Check with
   `adb shell pm get-app-links id.kustore.app`.

## iOS

```sh
npm run sync && cd ios/App && pod install && cd ../.. && npm run open:ios
```

In Xcode › Signing & Capabilities pick your team, add `App/App.entitlements` (Associated Domains) to the target, then
Product › Archive › Distribute › App Store Connect › TestFlight.

**Universal Links:** replace `SUPPLY_APPLE_TEAM_ID` in `resources/well-known/apple-app-site-association.json` with your
Apple Team ID, merge and deploy. The site serves it at `/.well-known/apple-app-site-association` as JSON.

Store listing text, privacy answers and reviewer notes: `docs/STORE-KIT.md`.
