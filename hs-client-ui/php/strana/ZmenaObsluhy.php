<?php
/* HairSoft Klient V172 – moderní změna obsluhy + iframe režim + oprava autentizace. */
if (!defined('HS_CLIENT_UI_ROOT')) { define('HS_CLIENT_UI_ROOT', dirname(__DIR__, 3)); }
require HS_CLIENT_UI_ROOT . '/str/strana/_zabezpeceni.php';
require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';

function hsStaffSwitchInput($source, $key) {
    if (!isset($source[$key]) || is_array($source[$key])) { return ''; }
    return (string) $source[$key];
}

function hsStaffSwitchEsc($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$jmenoStranky = 'Změna obsluhy';
$jmenoStrankyPopis = 'Změna obsluhy na jinou osobu';
$pobocka_jmeno = (isset($_SESSION['pobocka_jmeno']) && $_SESSION['pobocka_jmeno'] !== '') ? $_SESSION['pobocka_jmeno'] : 'Nevybrána';
$sw_id = (isset($_SESSION['pobocka_id']) && !is_array($_SESSION['pobocka_id'])) ? (int) $_SESSION['pobocka_id'] : 0;
$currentStaffId = (isset($_SESSION['k_poduzivatele_id']) && !is_array($_SESSION['k_poduzivatele_id'])) ? (int) $_SESSION['k_poduzivatele_id'] : 0;
$isEmbed = isset($_GET['hs_embed']) && !is_array($_GET['hs_embed']) && (string) $_GET['hs_embed'] === '1';

// Stejná ochrana jako v původní stránce.
if (isset($_SESSION['JePoduzivatel']) && $_SESSION['JePoduzivatel'] === '1'
    && (!isset($_SESSION['SeznamPravPoduzivatele']) || !is_array($_SESSION['SeznamPravPoduzivatele']) || !in_array('Dashboard', $_SESSION['SeznamPravPoduzivatele'], true))) {
    echo '<meta http-equiv="refresh" content="0;URL=https://klient.hairsoft.cz/str/index.php?strana=PrazdnaStrana">';
    exit;
}

$inputObsluhaAkce = hsStaffSwitchInput($_POST, 'inputObsluhaAkce');
$inputObsluhaId = hsStaffSwitchInput($_POST, 'inputObsluhaId');
$inputObsluhaJmeno = trim(hsStaffSwitchInput($_POST, 'inputObsluhaJmeno'));
$inputObsluhaPassword = hsStaffSwitchInput($_POST, 'inputObsluhaPassword');
$ZobrazitChybu = '';

if ($inputObsluhaAkce === '1') {
    $staffId = ctype_digit($inputObsluhaId) ? (int) $inputObsluhaId : 0;
    $passwordSha1 = sha1(htmlspecialchars($inputObsluhaPassword, ENT_COMPAT));

    if ($sw_id > 0 && $staffId > 0) {
        $sql = "SELECT `k_poduzivatele_obsluha_id`,
                       `k_poduzivatele_obsluha_jmeno`,
                       `k_poduzivatele_obsluha_id_hs`,
                       `k_poduzivatele_obsluha_sw_id`,
                       `k_poduzivatele_skryt_citliva_data`,
                       `k_poduzivatele_obsluha_foto`
                FROM `k_poduzivatele_obsluha`
                WHERE `k_poduzivatele_obsluha_sw_id` = ?
                  AND `k_poduzivatele_obsluha_id` = ?
                  AND `k_poduzivatele_obsluha_heslo` = ?
                  AND `k_poduzivatele_archivace_obsluhy` = 1
                  AND `k_obsluha_moznostPrehlaset` = 1
                LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        if ($stmt) {
            /*
             * V172: autentizace pri prehlaseni nepouziva jmeno jako DB klic.
             * Puvodni loginObsluha.php porovnava TRIM(jmeno), zatimco V170/V171
             * po vyberu karty posilaly orezane jmeno a zde se porovnavalo presnou
             * rovnosti. Historicka data s mezerou v k_poduzivatele_obsluha_jmeno
             * proto sla prihlasit z uvodniho loginu, ale ne z prehlasovaciho modalu.
             * ID + SW ID + heslo + aktivni/povoleny stav jednoznacne urcuji obsluhu.
             */
            $stmt->bind_param('iis', $sw_id, $staffId, $passwordSha1);
            $stmt->execute();
            $stmt->bind_result($dbStaffId, $dbStaffName, $dbStaffIdHs, $dbStaffSwId, $dbPrivacy, $dbPhoto);
            $staffFound = $stmt->fetch();
            $stmt->close();

            if ($staffFound) {
                $_SESSION['JePoduzivatel'] = '0';
                $_SESSION['JeObsluha'] = '1';
                $_SESSION['k_poduzivatele_id'] = $dbStaffId;
                $_SESSION['uzivatel_prijmeni_jmeno'] = $dbStaffName;
                $_SESSION['k_poduzivatele_obsluha_jmeno'] = $dbStaffName;
                $_SESSION['k_poduzivatele_obsluha_id_hs'] = $dbStaffIdHs;
                $_SESSION['k_poduzivatele_obsluha_sw_id'] = $dbStaffSwId;
                $_SESSION['k_poduzivatele_skryt_citliva_data'] = ((string) $dbPrivacy === '1') ? '1' : '0';
                $_SESSION['SQL_ROK'] = date('Y');
                $_SESSION['ObsluhaFoto'] = $dbPhoto !== '' ? $dbPhoto : '';

                // Po změně obsluhy nesmí ani na okamžik zůstat práva předchozí osoby.
                // Index je při zmena_pobocky=1 znovu načte pro novou obsluhu.
                $_SESSION['SeznamPravPoduzivatele'] = array();
                $_SESSION['SeznamPravPoduzivateleNaPobocky'] = array();

                $switchRedirect = 'https://klient.hairsoft.cz/str/index.php?zmena_pobocky=1&aktivni_pobocka=' . $sw_id;
                if ($isEmbed) {
                    echo '<script>(function(){var u=' . json_encode($switchRedirect, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';try{window.parent.postMessage({type:"hs-staff-switch-success",redirectUrl:u},window.location.origin);}catch(e){window.top.location.href=u;}})();</script>';
                } else {
                    echo '<meta http-equiv="refresh" content="0;URL=' . hsStaffSwitchEsc($switchRedirect) . '">';
                }
                exit;
            }
        }
    }

    $ZobrazitChybu = 'ANO';
}

$staffList = array();
if ($sw_id > 0) {
    $sql = "SELECT `k_poduzivatele_obsluha_id`, `k_poduzivatele_obsluha_jmeno`, `k_poduzivatele_obsluha_foto`
            FROM `k_poduzivatele_obsluha`
            WHERE `k_poduzivatele_obsluha_sw_id` = ?
              AND `k_poduzivatele_archivace_obsluhy` = 1
              AND `k_obsluha_moznostPrehlaset` = 1
            ORDER BY `k_poduzivatele_obsluha_id_hs` ASC";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $sw_id);
        $stmt->execute();
        $stmt->bind_result($listStaffId, $listStaffName, $listStaffPhoto);
        while ($stmt->fetch()) {
            if ((int) $listStaffId === $currentStaffId) { continue; }
            $staffList[] = array(
                'k_poduzivatele_obsluha_id' => $listStaffId,
                'k_poduzivatele_obsluha_jmeno' => $listStaffName,
                'k_poduzivatele_obsluha_foto' => $listStaffPhoto,
            );
        }
        $stmt->close();
    }
}
?>
<?php if ($isEmbed): ?>
<script>document.body.classList.add('hs-staff-switch-embed-frame');</script>
<?php endif; ?>
<section class="main-content-wrapper<?php echo $isEmbed ? ' hs-staff-switch-embed-shell' : ''; ?>">
    <div class="pageheader">
        <h1><?php echo hsStaffSwitchEsc($jmenoStranky); ?></h1>
        <p class="description"><?php echo hsStaffSwitchEsc($jmenoStrankyPopis); ?></p>
        <div class="breadcrumb-wrapper hidden-xs">
            <span class="label">Pobočka:</span>
            <ol class="breadcrumb">
                <li class="active"><b><?php echo hsStaffSwitchEsc($pobocka_jmeno); ?></b> <?php echo JePobockaOnline($sw_id, $mysqli); ?></li>
            </ol>
            <font color="#999" size="1">&nbsp;&nbsp;SW ID: <?php echo hsStaffSwitchEsc($sw_id); ?></font>
        </div>
    </div>

    <section id="main-content" class="hs-staff-switch-page"
             data-staff-switch-embed="<?php echo $isEmbed ? '1' : '0'; ?>"
             data-reopen-id="<?php echo hsStaffSwitchEsc($ZobrazitChybu === 'ANO' ? $inputObsluhaId : ''); ?>">
        <?php if (count($staffList)): ?>
            <div class="hs-staff-switch-grid">
                <?php foreach ($staffList as $staff):
                    $staffName = trim((string) $staff['k_poduzivatele_obsluha_jmeno']);
                    $photo = trim((string) $staff['k_poduzivatele_obsluha_foto']);
                    if ($photo === '') { $photo = 'no_image.png'; }
                    $photoUrl = '../img/obsluhy/' . ltrim($photo, '/');
                ?>
                    <button type="button"
                            class="hs-staff-card"
                            data-staff-switch-card
                            data-staff-id="<?php echo hsStaffSwitchEsc($staff['k_poduzivatele_obsluha_id']); ?>"
                            data-staff-name="<?php echo hsStaffSwitchEsc($staffName); ?>"
                            data-staff-photo="<?php echo hsStaffSwitchEsc($photoUrl); ?>"
                            aria-label="Přihlásit jako <?php echo hsStaffSwitchEsc($staffName); ?>">
                        <span class="hs-staff-card-avatar-wrap">
                            <img class="hs-staff-card-avatar" src="<?php echo hsStaffSwitchEsc($photoUrl); ?>" alt="<?php echo hsStaffSwitchEsc($staffName); ?>">
                            <span class="hs-staff-card-status" aria-hidden="true"></span>
                        </span>
                        <span class="hs-staff-card-name"><?php echo hsStaffSwitchEsc($staffName); ?></span>
                        <span class="hs-staff-card-action">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 3h5v5M21 3l-7 7M8 7H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3"/></svg>
                            Přepnout obsluhu
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="hs-staff-switch-empty">
                <span class="hs-staff-switch-empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 11h-6M19 8v6"/></svg>
                </span>
                <strong>Žádná další obsluha není dostupná</strong>
                <p>Pro tuto pobočku není povoleno přehlášení na jinou aktivní obsluhu.</p>
            </div>
        <?php endif; ?>
    </section>
</section>

<div class="hs-staff-switch-modal" id="hsStaffSwitchModal" hidden aria-hidden="true">
    <div class="hs-staff-switch-backdrop" data-staff-switch-close></div>
    <section class="hs-staff-switch-dialog" role="dialog" aria-modal="true" aria-labelledby="hsStaffSwitchTitle">
        <button type="button" class="hs-staff-switch-close" data-staff-switch-close aria-label="Zavřít">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>

        <div class="hs-staff-switch-dialog-head">
            <div class="hs-staff-switch-dialog-avatar-wrap">
                <img id="hsStaffSwitchPhoto" class="hs-staff-switch-dialog-avatar" src="../img/obsluhy/no_image.png" alt="">
            </div>
            <span class="hs-staff-switch-eyebrow">Přehlášení obsluhy</span>
            <h3 id="hsStaffSwitchTitle">Přihlásit jako <span id="hsStaffSwitchName"></span></h3>
            <p>Zadejte heslo vybrané obsluhy.</p>
        </div>

        <form class="hs-staff-switch-form" action="index.php?strana=ZmenaObsluhy<?php echo $isEmbed ? '&amp;hs_embed=1' : ''; ?>" method="POST" autocomplete="off">
            <input type="hidden" name="inputObsluhaAkce" value="1">
            <input type="hidden" name="inputObsluhaId" id="hsStaffSwitchId" value="">
            <input type="hidden" name="inputObsluhaJmeno" id="inputObsluhaJmeno" value="">

            <?php if ($ZobrazitChybu === 'ANO'): ?>
                <div class="hs-staff-switch-error" role="alert">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 17h.01"/></svg>
                    <span>Chybné přihlášení. Zkontrolujte heslo a zkuste to znovu.</span>
                </div>
            <?php endif; ?>

            <label class="hs-staff-switch-field" for="inputObsluhaPassword">
                <span>Heslo obsluhy</span>
                <input type="password" id="inputObsluhaPassword" name="inputObsluhaPassword" class="form-control" placeholder="Zadejte heslo" autocomplete="current-password" required>
            </label>

            <div class="hs-staff-switch-dialog-actions">
                <button type="button" class="hs-staff-switch-btn hs-staff-switch-btn--secondary" data-staff-switch-close>Zpět</button>
                <button type="submit" class="hs-staff-switch-btn hs-staff-switch-btn--primary">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                    Přehlásit obsluhu
                </button>
            </div>
        </form>
    </section>
</div>
