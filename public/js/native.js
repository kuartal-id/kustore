/* Kustore: extra behaviour inside the Android/iOS app (mobile/, Capacitor).
 * In a normal browser it only turns on the Web Share API buttons where supported; everything else is a no-op. */
(function () {
  'use strict';
  var $$ = function (sel) { return Array.prototype.slice.call(document.querySelectorAll(sel)); };
  var Cap = window.Capacitor;
  var native = !!(Cap && Cap.isNativePlatform && Cap.isNativePlatform());
  var P = (Cap && Cap.Plugins) || {};

  if (native) document.documentElement.classList.add('native-app', 'native-' + Cap.getPlatform());

  // Share buttons: <button data-native-share data-share-url=".." data-share-title=".." hidden>
  var canShare = (native && P.Share) || !!navigator.share;
  $$('[data-native-share]').forEach(function (btn) {
    if (!canShare) return;
    btn.removeAttribute('hidden');
    btn.addEventListener('click', function () {
      var data = {
        title: btn.getAttribute('data-share-title') || document.title,
        text: btn.getAttribute('data-share-text') || undefined,
        url: btn.getAttribute('data-share-url') || location.href,
      };
      var p = native && P.Share ? P.Share.share(data) : navigator.share(data);
      if (p && p.catch) p.catch(function () {});
    });
  });

  if (!native) return;

  // Android back button: go back, or leave the app on the first page.
  if (P.App) {
    P.App.addListener('backButton', function (e) {
      if (e && e.canGoBack) history.back(); else P.App.exitApp();
    });
    // Deep links: https://kustore.id/... and id.kustore.app://path open inside the app.
    P.App.addListener('appUrlOpen', function (e) {
      try {
        var u = new URL(e.url);
        var path = u.protocol === 'https:' ? u.pathname + u.search + u.hash : '/' + (u.host + u.pathname).replace(/^\/+/, '');
        if (u.protocol === 'https:' && u.host !== 'kustore.id') return;
        location.href = 'https://kustore.id' + path;
      } catch (err) {}
    });
  }

  // Offline banner.
  if (P.Network) {
    var bar = document.createElement('div');
    bar.className = 'fixed inset-x-0 top-0 z-50 bg-navy px-4 py-2 text-center font-display text-sm text-white';
    bar.setAttribute('role', 'status');
    bar.textContent = 'You are offline. Changes will not be saved until you reconnect.';
    bar.hidden = true;
    document.body.appendChild(bar);
    var show = function (s) { bar.hidden = !!(s && s.connected); };
    P.Network.getStatus().then(show).catch(function () {});
    P.Network.addListener('networkStatusChange', show);
  }
})();
