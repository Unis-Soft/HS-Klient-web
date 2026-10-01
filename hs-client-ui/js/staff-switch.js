(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  function languageCode() {
    var lang = (document.documentElement.lang || 'cs').toLowerCase().slice(0, 2);
    return ['cs', 'sk', 'en', 'de'].indexOf(lang) >= 0 ? lang : 'cs';
  }

  function frameTexts() {
    var texts = {
      cs: {
        title: 'Změna obsluhy',
        subtitle: 'Vyberte obsluhu, na kterou se chcete přihlásit.',
        close: 'Zavřít',
        frameTitle: 'Výběr obsluhy'
      },
      sk: {
        title: 'Zmena obsluhy',
        subtitle: 'Vyberte obsluhu, na ktorú sa chcete prihlásiť.',
        close: 'Zavrieť',
        frameTitle: 'Výber obsluhy'
      },
      en: {
        title: 'Change staff member',
        subtitle: 'Choose the staff member you want to sign in as.',
        close: 'Close',
        frameTitle: 'Staff selection'
      },
      de: {
        title: 'Mitarbeiter wechseln',
        subtitle: 'Wählen Sie den Mitarbeiter aus, als den Sie sich anmelden möchten.',
        close: 'Schließen',
        frameTitle: 'Mitarbeiterauswahl'
      }
    };
    return texts[languageCode()] || texts.cs;
  }

  function isEmbedRequest() {
    try {
      return new URL(window.location.href).searchParams.get('hs_embed') === '1';
    } catch (error) {
      return false;
    }
  }

  function setupFrameLauncher() {
    if (isEmbedRequest()) return;

    var launchers = document.querySelectorAll('[data-hs-staff-switch-launch]');
    if (!launchers.length) return;

    var modal = null;
    var iframe = null;
    var loading = null;
    var closeButton = null;
    var lastFocus = null;
    var clearTimer = null;

    function createModal() {
      if (modal) return modal;
      var t = frameTexts();
      modal = document.createElement('div');
      modal.className = 'hs-staff-switch-frame-modal';
      modal.hidden = true;
      modal.setAttribute('aria-hidden', 'true');
      modal.innerHTML = '' +
        '<div class="hs-staff-switch-frame-backdrop" data-hs-staff-frame-close></div>' +
        '<section class="hs-staff-switch-frame-dialog" role="dialog" aria-modal="true" aria-labelledby="hsStaffFrameTitle">' +
          '<header class="hs-staff-switch-frame-header">' +
            '<div class="hs-staff-switch-frame-heading">' +
              '<span class="hs-staff-switch-frame-icon" aria-hidden="true">' +
                '<svg viewBox="0 0 24 24"><path d="m16 3 4 4-4 4"/><path d="M20 7H9a5 5 0 0 0-5 5v1"/><path d="m8 21-4-4 4-4"/><path d="M4 17h11a5 5 0 0 0 5-5v-1"/></svg>' +
              '</span>' +
              '<div><h2 id="hsStaffFrameTitle">' + t.title + '</h2><p>' + t.subtitle + '</p></div>' +
            '</div>' +
            '<button type="button" class="hs-staff-switch-frame-close" data-hs-staff-frame-close aria-label="' + t.close + '">' +
              '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>' +
            '</button>' +
          '</header>' +
          '<div class="hs-staff-switch-frame-body">' +
            '<div class="hs-staff-switch-frame-loading" aria-hidden="true"><span></span></div>' +
            '<iframe class="hs-staff-switch-frame" title="' + t.frameTitle + '" src="about:blank"></iframe>' +
          '</div>' +
        '</section>';
      document.body.appendChild(modal);

      iframe = modal.querySelector('.hs-staff-switch-frame');
      loading = modal.querySelector('.hs-staff-switch-frame-loading');
      closeButton = modal.querySelector('.hs-staff-switch-frame-close');

      modal.querySelectorAll('[data-hs-staff-frame-close]').forEach(function (node) {
        node.addEventListener('click', function () { closeFrame(true); });
      });

      iframe.addEventListener('load', function () {
        if (loading) loading.classList.add('is-hidden');
      });

      return modal;
    }

    function frameUrl(href) {
      try {
        var url = new URL(href, window.location.href);
        url.searchParams.set('hs_embed', '1');
        return url.toString();
      } catch (error) {
        return 'index.php?strana=ZmenaObsluhy&hs_embed=1';
      }
    }

    function openFrame(launcher) {
      createModal();
      if (clearTimer) {
        window.clearTimeout(clearTimer);
        clearTimer = null;
      }
      lastFocus = document.activeElement;
      if (loading) loading.classList.remove('is-hidden');
      iframe.src = frameUrl(launcher.getAttribute('href') || 'index.php?strana=ZmenaObsluhy');
      modal.hidden = false;
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('hs-staff-switch-frame-open');
      window.setTimeout(function () {
        if (closeButton) closeButton.focus();
      }, 20);
    }

    function closeFrame(restoreFocus) {
      if (!modal || modal.hidden) return;
      modal.hidden = true;
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('hs-staff-switch-frame-open');
      clearTimer = window.setTimeout(function () {
        if (iframe) iframe.src = 'about:blank';
      }, 120);
      if (restoreFocus && lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    }

    launchers.forEach(function (launcher) {
      launcher.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        openFrame(launcher);
      });
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && modal && !modal.hidden) closeFrame(true);
    });

    window.addEventListener('message', function (event) {
      if (event.origin !== window.location.origin || !event.data || typeof event.data !== 'object') return;
      if (event.data.type === 'hs-staff-switch-close') {
        closeFrame(true);
        return;
      }
      if (event.data.type === 'hs-staff-switch-success') {
        var redirectUrl = typeof event.data.redirectUrl === 'string' && event.data.redirectUrl ? event.data.redirectUrl : 'index.php';
        closeFrame(false);
        window.location.href = redirectUrl;
      }
    });
  }

  function setupStaffPage() {
    var page = document.querySelector('.hs-staff-switch-page');
    var modal = document.getElementById('hsStaffSwitchModal');
    if (!page || !modal) return;

    var isEmbed = page.getAttribute('data-staff-switch-embed') === '1';
    var nameNode = document.getElementById('hsStaffSwitchName');
    var photoNode = document.getElementById('hsStaffSwitchPhoto');
    var idInput = document.getElementById('hsStaffSwitchId');
    var nameInput = document.getElementById('inputObsluhaJmeno');
    var passwordInput = document.getElementById('inputObsluhaPassword');
    var activeCard = null;
    var lastFocus = null;

    function setStaff(card) {
      if (!card) return;
      var id = card.getAttribute('data-staff-id') || '';
      var name = card.getAttribute('data-staff-name') || '';
      var photo = card.getAttribute('data-staff-photo') || '../img/obsluhy/no_image.png';
      idInput.value = id;
      nameInput.value = name;
      nameNode.textContent = name;
      photoNode.src = photo;
      photoNode.alt = name;
      activeCard = card;
    }

    function openModal(card) {
      lastFocus = document.activeElement;
      setStaff(card);
      modal.hidden = false;
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('hs-staff-switch-modal-open');
      if (isEmbed) document.body.classList.add('hs-staff-switch-login-stage');
      window.setTimeout(function () {
        if (passwordInput) {
          if (!page.getAttribute('data-reopen-id')) passwordInput.value = '';
          passwordInput.focus();
        }
      }, 40);
    }

    function closeModal() {
      modal.hidden = true;
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('hs-staff-switch-modal-open');
      document.body.classList.remove('hs-staff-switch-login-stage');
      if (passwordInput) passwordInput.value = '';
      if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
      activeCard = null;
    }

    document.querySelectorAll('[data-staff-switch-card]').forEach(function (card) {
      card.addEventListener('click', function () { openModal(card); });
    });

    modal.querySelectorAll('[data-staff-switch-close]').forEach(function (node) {
      node.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Escape') return;
      if (!modal.hidden) {
        closeModal();
      } else if (isEmbed) {
        try {
          window.parent.postMessage({ type: 'hs-staff-switch-close' }, window.location.origin);
        } catch (error) {}
      }
    });

    var form = modal.querySelector('form');
    if (form) {
      form.addEventListener('submit', function () {
        var submit = form.querySelector('button[type="submit"]');
        if (submit) {
          submit.disabled = true;
          submit.setAttribute('aria-busy', 'true');
        }
      });
    }

    var reopenId = page.getAttribute('data-reopen-id') || '';
    if (reopenId) {
      var cards = document.querySelectorAll('[data-staff-switch-card]');
      for (var i = 0; i < cards.length; i += 1) {
        if ((cards[i].getAttribute('data-staff-id') || '') === reopenId) {
          openModal(cards[i]);
          break;
        }
      }
    }
  }

  ready(function () {
    setupFrameLauncher();
    setupStaffPage();
  });
})();
