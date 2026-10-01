/* HairSoft Klient V212 – společná Správa dat napříč sekcemi. */
(function () {
  'use strict';

  function tr(source) {
    return window.hsTranslate ? window.hsTranslate(source) : source;
  }

  function setText(node, source) {
    if (!node) return;
    if (window.hsSetTranslatedText) window.hsSetTranslatedText(node, source);
    else node.textContent = tr(source);
  }

  function clean(value) {
    return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
  }

  function settingsIcon() {
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 17h16M8 4v6M16 14v6"></path></svg>';
  }

  function isExplicitManagement(node) {
    if (!node || !node.matches) return false;
    return node.matches([
      '.hs-customer-data-management',
      '.hs-stock-data-management',
      '.hs-client-data-management',
      '.hs-daily-options-card',
      '.hs-monthly-options-card',
      '.hs-annual-options-card',
      '.hs-lifetime-options-card',
      '.hs-voucher-delete-section'
    ].join(','));
  }

  function managementText(node) {
    var head;
    if (!node) return '';
    if (node.tagName === 'DETAILS') head = node.querySelector(':scope > summary');
    else head = node.querySelector(':scope > .panel-heading');
    return clean(head ? head.textContent : '');
  }

  function isManagement(node) {
    var text;
    if (isExplicitManagement(node)) return true;
    if (!node.classList || !node.classList.contains('panel')) return false;
    text = managementText(node);
    return text === 'správa dat' || text === 'správa dat zákazníků' ||
      text === 'správa údajov' || text === 'správa údajov zákazníkov' ||
      text === 'data management' || text === 'customer data management' ||
      text === 'datenverwaltung' || text === 'kundendaten verwalten';
  }

  function ensureDetailsBody(node, header) {
    var body = node.querySelector(':scope > .hs-data-management-body');
    var children;

    if (body) return body;

    children = Array.prototype.filter.call(node.children, function (child) {
      return child !== header;
    });
    if (!children.length) return null;

    /*
     * V212: details varianty (Sklad, Seznam zákazníků, Karta zákazníka)
     * dříve používaly přímo action wrapper jako body. Tím se ztratil stejný
     * vnitřní prostor, jaký má panel-body v Měsíčních tržbách. Vytvoříme proto
     * skutečný společný body wrapper a původní obsah pouze přesuneme dovnitř.
     */
    body = document.createElement('div');
    body.className = 'hs-data-management-body';
    children.forEach(function (child) { body.appendChild(child); });
    node.appendChild(body);
    return body;
  }

  function findPanelBody(node) {
    return node.querySelector(':scope > .panel-body');
  }

  function buildHeading(header) {
    if (!header) return;
    header.textContent = '';
    var heading = document.createElement('span');
    heading.className = 'hs-data-management-heading';
    var icon = document.createElement('span');
    icon.className = 'hs-data-management-heading__icon';
    icon.innerHTML = settingsIcon();
    var copy = document.createElement('span');
    copy.className = 'hs-data-management-heading__copy';
    var strong = document.createElement('strong');
    var small = document.createElement('small');
    setText(strong, 'Správa dat');
    setText(small, 'Načtení, synchronizace a odstranění zobrazených dat');
    copy.appendChild(strong);
    copy.appendChild(small);
    heading.appendChild(icon);
    heading.appendChild(copy);
    header.appendChild(heading);
  }

  function normalize(node) {
    if (!node || !isManagement(node)) return;

    var isDetails = node.tagName === 'DETAILS';
    var header = isDetails ? node.querySelector(':scope > summary') : node.querySelector(':scope > .panel-heading');
    var body;
    if (!header) return;

    body = isDetails ? ensureDetailsBody(node, header) : findPanelBody(node);
    if (!body) return;

    node.classList.add('hs-data-management-unified');
    body.classList.add('hs-data-management-body');

    if (!node.dataset.hsDmInitialized) {
      node.dataset.hsDmInitialized = '1';
      buildHeading(header);

      if (isDetails) {
        /* Správa dat je po každém načtení stránky výchozí sbalená. */
        node.removeAttribute('open');
      } else {
        node.setAttribute('data-hs-dm-open', '0');
        header.setAttribute('role', 'button');
        header.setAttribute('tabindex', '0');
        header.setAttribute('aria-expanded', 'false');
        if (!body.id) body.id = 'hs-data-management-' + Math.random().toString(36).slice(2, 10);
        header.setAttribute('aria-controls', body.id);

        function toggle() {
          var open = node.getAttribute('data-hs-dm-open') === '1';
          node.setAttribute('data-hs-dm-open', open ? '0' : '1');
          header.setAttribute('aria-expanded', open ? 'false' : 'true');
        }

        header.addEventListener('click', toggle);
        header.addEventListener('keydown', function (event) {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            toggle();
          }
        });
      }
    } else if (!header.querySelector('.hs-data-management-heading')) {
      buildHeading(header);
    }
  }

  function scan() {
    var main = document.getElementById('main-content');
    if (!main) return;
    var nodes = main.querySelectorAll([
      'details.hs-customer-data-management',
      'details.hs-stock-data-management',
      'details.hs-client-data-management',
      '.hs-daily-options-card',
      '.hs-monthly-options-card',
      '.hs-annual-options-card',
      '.hs-lifetime-options-card',
      '.hs-voucher-delete-section',
      '.panel'
    ].join(','));
    Array.prototype.forEach.call(nodes, normalize);
  }

  function boot() {
    scan();
    window.setTimeout(scan, 100);
    window.setTimeout(scan, 500);
    window.setTimeout(scan, 1200);
    window.setTimeout(scan, 3000);
    var main = document.getElementById('main-content');
    if (main && window.MutationObserver) {
      var queued = false;
      new MutationObserver(function () {
        if (queued) return;
        queued = true;
        window.requestAnimationFrame(function () {
          queued = false;
          scan();
        });
      }).observe(main, { childList: true, subtree: true });
    }
  }

  document.addEventListener('hs:languagechange', function () {
    window.setTimeout(function () {
      document.querySelectorAll('.hs-data-management-heading__copy strong').forEach(function (node) { setText(node, 'Správa dat'); });
      document.querySelectorAll('.hs-data-management-heading__copy small').forEach(function (node) { setText(node, 'Načtení, synchronizace a odstranění zobrazených dat'); });
    }, 0);
  });

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
}());
