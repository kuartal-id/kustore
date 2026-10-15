#!/usr/bin/env node
// Idempotently adds permissions, usage descriptions, deep links and Android SDK levels to the generated
// native projects. Runs after `cap add` / `cap sync` (the npm scripts do this for you). Safe to run again.
import { existsSync, readFileSync, writeFileSync } from 'node:fs';

const HOST = 'kustore.id';
const SCHEME = 'id.kustore.app';

// ── Android ───────────────────────────────────────────────────────────────
const manifest = 'android/app/src/main/AndroidManifest.xml';
if (existsSync(manifest)) {
  let x = readFileSync(manifest, 'utf8');
  // CAMERA: the web page's photo inputs offer "Take photo" (product photos, QRIS code).
  for (const p of ['android.permission.INTERNET', 'android.permission.ACCESS_NETWORK_STATE', 'android.permission.CAMERA']) {
    if (!x.includes(`"${p}"`)) x = x.replace('</manifest>', `    <uses-permission android:name="${p}" />\n</manifest>`);
  }
  if (!x.includes('android.hardware.camera')) {
    x = x.replace('</manifest>', '    <uses-feature android:name="android.hardware.camera" android:required="false" />\n</manifest>');
  }
  if (!x.includes(`android:host="${HOST}"`)) {
    const filters = `
            <intent-filter android:autoVerify="true">
                <action android:name="android.intent.action.VIEW" />
                <category android:name="android.intent.category.DEFAULT" />
                <category android:name="android.intent.category.BROWSABLE" />
                <data android:scheme="https" android:host="${HOST}" />
            </intent-filter>
            <intent-filter>
                <action android:name="android.intent.action.VIEW" />
                <category android:name="android.intent.category.DEFAULT" />
                <category android:name="android.intent.category.BROWSABLE" />
                <data android:scheme="${SCHEME}" />
            </intent-filter>`;
    x = x.replace(/(<intent-filter>\s*<action android:name="android.intent.action.MAIN" \/>[\s\S]*?<\/intent-filter>)/, `$1${filters}`);
  }
  writeFileSync(manifest, x);
  console.log('patched', manifest);
}

// Google Play requires target API 36 for new apps and updates (since 31 Aug 2026).
const vars = 'android/variables.gradle';
if (existsSync(vars)) {
  let v = readFileSync(vars, 'utf8');
  v = v.replace(/compileSdkVersion = \d+/, 'compileSdkVersion = 36').replace(/targetSdkVersion = \d+/, 'targetSdkVersion = 36');
  writeFileSync(vars, v);
  console.log('patched', vars);
}

// ── iOS ───────────────────────────────────────────────────────────────────
const plist = 'ios/App/App/Info.plist';
if (existsSync(plist)) {
  let x = readFileSync(plist, 'utf8');
  const add = {
    NSCameraUsageDescription: 'Kustore uses the camera when you take a photo for a product, your profile or your QRIS code.',
    NSPhotoLibraryUsageDescription: 'Kustore opens your photos when you choose a picture for a product, your profile or your QRIS code.',
  };
  for (const [k, v] of Object.entries(add)) {
    if (!x.includes(`<key>${k}</key>`)) x = x.replace(/<dict>/, `<dict>\n\t<key>${k}</key>\n\t<string>${v}</string>`);
  }
  if (!x.includes('<key>CFBundleURLTypes</key>')) {
    x = x.replace(/<dict>/, `<dict>\n\t<key>CFBundleURLTypes</key>\n\t<array><dict><key>CFBundleURLSchemes</key><array><string>${SCHEME}</string></array></dict></array>`);
  }
  writeFileSync(plist, x);
  console.log('patched', plist);
}
const ent = 'ios/App/App/App.entitlements';
if (existsSync('ios/App/App') && !existsSync(ent)) {
  writeFileSync(ent, `<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
\t<key>com.apple.developer.associated-domains</key>
\t<array><string>applinks:${HOST}</string></array>
</dict>
</plist>
`);
  console.log('wrote', ent, '(add it to the App target in Xcode: Signing & Capabilities)');
}
