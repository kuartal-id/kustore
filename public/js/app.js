/* Kustore: small progressive enhancements. No framework, no build step. */
(function () {
  'use strict';
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

  // Colour mode toggle (persisted)
  $$('[data-theme-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var dark = !document.documentElement.classList.contains('dark');
      document.documentElement.classList.toggle('dark', dark);
      try { localStorage.setItem('kustore-theme', dark ? 'dark' : 'light'); } catch (e) {}
    });
  });

  // Copy to clipboard
  $$('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var text = btn.getAttribute('data-copy');
      var label = btn.querySelector('[data-copy-label]');
      var done = function () {
        if (!label) return;
        var old = label.textContent;
        label.textContent = 'Copied';
        setTimeout(function () { label.textContent = old; }, 1600);
      };
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(done, function () {});
      } else {
        var ta = document.createElement('textarea');
        ta.value = text; ta.setAttribute('readonly', ''); ta.style.position = 'absolute'; ta.style.left = '-9999px';
        document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); done(); } catch (e) {}
        document.body.removeChild(ta);
      }
    });
  });

  // Simple disclosure (mobile menu etc.)
  $$('[data-toggle]').forEach(function (btn) {
    var target = document.getElementById(btn.getAttribute('data-toggle'));
    if (!target) return;
    btn.addEventListener('click', function () {
      var open = target.hasAttribute('hidden');
      target.toggleAttribute('hidden', !open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.documentElement.classList.toggle('overflow-hidden', open && target.hasAttribute('data-modal'));
    });
  });

  // Confirm destructive actions
  $$('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  // Image preview for uploads
  $$('input[type=file][data-preview]').forEach(function (input) {
    input.addEventListener('change', function () {
      var img = document.getElementById(input.getAttribute('data-preview'));
      if (!img || !input.files || !input.files[0]) return;
      img.src = URL.createObjectURL(input.files[0]);
      img.removeAttribute('hidden');
      var ph = document.getElementById(input.getAttribute('data-preview') + '-placeholder');
      if (ph) ph.setAttribute('hidden', '');
    });
  });

  // Product form: unlimited stock disables quantity; type sets shipping default until user touches it
  var unlimited = document.querySelector('[data-unlimited]');
  var stock = document.querySelector('[data-stock]');
  if (unlimited && stock) {
    var sync = function () { stock.disabled = unlimited.checked; };
    unlimited.addEventListener('change', sync); sync();
  }
  var shipping = document.querySelector('[data-shipping]');
  if (shipping) {
    var touched = false;
    shipping.addEventListener('change', function () { touched = true; });
    $$('input[name=type]').forEach(function (r) {
      r.addEventListener('change', function () { if (!touched) shipping.checked = r.value === 'physical'; });
    });
  }

  // Checkout: live total
  var qty = document.querySelector('[data-qty]');
  if (qty) {
    var unit = parseInt(qty.getAttribute('data-unit'), 10) || 0;
    var fmt = new Intl.NumberFormat('id-ID');
    var update = function () {
      var q = Math.max(1, parseInt(qty.value, 10) || 1);
      $$('[data-total]').forEach(function (el) { el.textContent = 'Rp ' + fmt.format(unit * q); });
      $$('[data-qty-label]').forEach(function (el) { el.textContent = q; });
    };
    qty.addEventListener('input', update); qty.addEventListener('change', update);
  }

  // Username preview on onboarding
  var uname = document.querySelector('[data-username]');
  if (uname) {
    var out = document.querySelector('[data-username-preview]');
    uname.addEventListener('input', function () {
      uname.value = uname.value.toLowerCase().replace(/[^a-z0-9_-]/g, '');
      if (out) out.textContent = uname.value || 'yourname';
    });
  }
})();
