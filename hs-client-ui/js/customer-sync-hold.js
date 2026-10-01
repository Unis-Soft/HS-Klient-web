(function () {
  'use strict';

  if (!window.fetch) return;

  var inFlight = false;
  var timer = null;

  function run() {
    if (inFlight) return;
    inFlight = true;
    fetch('/str/customer-sync-hold.php', {
      method: 'POST',
      credentials: 'same-origin',
      cache: 'no-store',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).catch(function () {
      // Heartbeat je pomocná ochrana; chyba nesmí narušit běžnou práci v HS Klient.
    }).finally(function () {
      inFlight = false;
    });
  }

  function start() {
    if (timer !== null) return;
    window.setTimeout(run, 5000);
    timer = window.setInterval(run, 30000);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start, { once: true });
  } else {
    start();
  }

  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) run();
  });
})();
