<?php
require HS_CLIENT_UI_ROOT . '/str/strana/_zabezpeceni.php';

$hsReservationsAllowed =
    (isset($_SESSION['JePoduzivatel'], $_SESSION['JeObsluha'])
        && $_SESSION['JePoduzivatel'] == '0'
        && $_SESSION['JeObsluha'] == '0')
    || (isset($_SESSION['SeznamPravPoduzivatele'])
        && is_array($_SESSION['SeznamPravPoduzivatele'])
        && in_array('Rezervace', $_SESSION['SeznamPravPoduzivatele'], true));

if (!$hsReservationsAllowed) {
    echo '<meta http-equiv="refresh" content="0;URL=index.php?strana=PrazdnaStrana">';
    exit;
}

$jmenoStranky = 'Rezervace';
$jmenoStrankyPopis = 'Administrace rezervačního systému Bonfero přímo v HairSoft Klient';
$pobocka_jmeno = isset($_SESSION['pobocka_jmeno']) && $_SESSION['pobocka_jmeno'] !== ''
    ? $_SESSION['pobocka_jmeno']
    : 'Nevybrána';
$sw_id = isset($_SESSION['pobocka_id']) ? $_SESSION['pobocka_id'] : '';
// V210: po docasnem testu vracena produkcni administrace Bonfero.
$hsBonferoAdminUrl = 'https://app.bonfero.com/';

// V230 SECURITY: Bonfero auth zije ve vlastni cookie/session na app.bonfero.com.
// Pri vice ulozenych HairSoft firmach tedy nelze bez explicitniho SSO mapovani
// zarucit, ze iframe patri aktualne vybrane firme. V multi-firma rezimu se
// Bonfero proto vubec nenacita, dokud nebude zavedeno company-bound SSO.
$hsBonferoMultiCompanyBlocked =
    isset($hsMultiAccounts)
    && is_array($hsMultiAccounts)
    && count($hsMultiAccounts) > 1;

$hsBonferoCurrentCompanyLabel =
    isset($hsMultiActiveAccount['label']) && is_string($hsMultiActiveAccount['label'])
        ? $hsMultiActiveAccount['label']
        : $pobocka_jmeno;
?>

<section class="main-content-wrapper hs-reservations-page">
  <div class="pageheader hs-dashboard-header hs-reservations-page-header">
    <div class="hs-dashboard-heading">
      <h1><?php echo htmlspecialchars($jmenoStranky, ENT_COMPAT, 'UTF-8'); ?></h1>
      <p class="description"><?php echo htmlspecialchars($jmenoStrankyPopis, ENT_COMPAT, 'UTF-8'); ?></p>
    </div>

    <div class="breadcrumb-wrapper hidden-xs hs-dashboard-meta">
      <span class="hs-dashboard-meta-icon" aria-hidden="true">
        <svg><use href="#hs-icon-building" xlink:href="#hs-icon-building"></use></svg>
      </span>
      <span class="hs-dashboard-meta-group">
        <span class="label">Pobočka</span>
        <span class="hs-dashboard-branch-value">
          <?php echo JePobockaOnlineMenu($sw_id, $mysqli); ?>
          <b data-hs-i18n-ignore><?php echo htmlspecialchars($pobocka_jmeno, ENT_COMPAT, 'UTF-8'); ?></b>
        </span>
      </span>
      <span class="hs-dashboard-meta-divider" aria-hidden="true"></span>
      <span class="hs-dashboard-meta-group">
        <span class="label">SW ID</span>
        <span class="hs-dashboard-id-value" data-hs-i18n-ignore><?php echo htmlspecialchars((string) $sw_id, ENT_COMPAT, 'UTF-8'); ?></span>
      </span>
    </div>
  </div>

  <section id="main-content" class="animated fadeInUp hs-reservations-content">
    <article class="hs-reservations-card" aria-labelledby="hs-reservations-title">
      <header class="hs-reservations-toolbar">
        <div class="hs-reservations-heading">
          <span class="hs-reservations-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="4" width="18" height="18" rx="3"/><path d="m8 16 2 2 5-5"/></svg>
          </span>
          <span class="hs-reservations-heading-copy">
            <strong id="hs-reservations-title">Bonfero</strong>
            <small>Rezervace a jejich administrace</small>
          </span>
        </div>

        <?php if (!$hsBonferoMultiCompanyBlocked) { ?>
        <div class="hs-reservations-actions">
          <button class="hs-reservations-button hs-reservations-button--quiet" type="button" data-hs-reservations-reload title="Znovu načíst Bonfero">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11a8.1 8.1 0 1 0 .5 3"/><path d="M20 4v7h-7"/></svg>
            <span>Obnovit</span>
          </button>
          <a class="hs-reservations-button hs-reservations-button--primary" href="<?php echo htmlspecialchars($hsBonferoAdminUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" title="Otevřít Bonfero v novém okně">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h6v6M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
            <span>Otevřít zvlášť</span>
          </a>
        </div>
        <?php } ?>
      </header>

      <?php if ($hsBonferoMultiCompanyBlocked) { ?>
      <div class="hs-reservations-security-block" role="alert">
        <span class="hs-reservations-security-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M12 3 4 6v6c0 5 3.4 8.4 8 9 4.6-.6 8-4 8-9V6Z"/><path d="M9 12l2 2 4-4"/></svg>
        </span>
        <div>
          <strong>Bonfero je pro více firem dočasně zablokováno</strong>
          <p>
            Aktuálně vybraná firma v HairSoft Klientu je
            <b><?php echo htmlspecialchars($hsBonferoCurrentCompanyLabel, ENT_COMPAT, 'UTF-8'); ?></b>.
            Přihlášení do app.bonfero.com ale používá samostatnou prohlížečovou relaci a není zatím
            bezpečně svázané s vybranou HairSoft firmou. Z tohoto důvodu se Bonfero v multi-firma režimu
            nenačte ani uvnitř stránky, ani přes přímé tlačítko.
          </p>
          <small>Další verze integrace musí používat company-bound SSO/token pro konkrétní firmu.</small>
        </div>
      </div>
      <?php } else { ?>
      <div class="hs-reservations-frame-wrap is-loading" data-hs-reservations-wrap>
        <div class="hs-reservations-loading" data-hs-reservations-loading role="status" aria-live="polite">
          <span class="hs-reservations-spinner" aria-hidden="true"></span>
          <strong>Načítám Bonfero</strong>
          <small>Chvíli strpení…</small>
        </div>

        <iframe
          class="hs-reservations-frame"
          data-hs-reservations-frame
          src="<?php echo htmlspecialchars($hsBonferoAdminUrl, ENT_QUOTES, 'UTF-8'); ?>"
          title="Administrace rezervací Bonfero"
          loading="eager"
          referrerpolicy="strict-origin-when-cross-origin"
          allow="clipboard-read; clipboard-write"
          allowfullscreen></iframe>
      </div>
      <?php } ?>

      <noscript>
        <p class="hs-reservations-noscript">Pro zobrazení Bonfera uvnitř HairSoft Klient je potřeba povolit JavaScript. <a href="<?php echo htmlspecialchars($hsBonferoAdminUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Otevřít Bonfero samostatně</a>.</p>
      </noscript>
    </article>
  </section>
</section>
