/* Kustore: apply colour mode before first paint. Order: visitor choice (localStorage) > page default > system. */
(function () {
  try {
    var root = document.documentElement;
    var saved = localStorage.getItem('kustore-theme');
    var def = root.getAttribute('data-default-theme') || 'system';
    var dark = saved ? saved === 'dark'
      : def === 'dark' ? true
      : def === 'light' ? false
      : window.matchMedia('(prefers-color-scheme: dark)').matches;
    root.classList.toggle('dark', dark);
  } catch (e) {}
})();
