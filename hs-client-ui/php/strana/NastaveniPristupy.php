<?php
/* HairSoft Klient V177 – moderní Přístupy. Nativní layout, původní databázová logika zachována. */
if (!defined('HS_CLIENT_UI_ROOT')) { define('HS_CLIENT_UI_ROOT', dirname(__DIR__, 3)); }
require HS_CLIENT_UI_ROOT . '/str/strana/_zabezpeceni.php';
require_once HS_CLIENT_UI_ROOT . '/fce/PosliEmail.php';
require_once HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';

function hsAccessEsc($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function hsAccessInput($source, $key) {
    if (!isset($source[$key]) || is_array($source[$key])) return '';
    return htmlspecialchars((string)$source[$key], ENT_COMPAT);
}
function hsAccessRedirect($url) {
    echo '<meta http-equiv="refresh" content="0;URL=' . hsAccessEsc($url) . '">';
}
function hsAccessInitials($name) {
    $name = trim((string)$name);
    if ($name === '') return '?';
    $parts = preg_split('/\s+/u', $name);
    $out = '';
    foreach ($parts as $part) {
        if ($part === '') continue;
        $out .= function_exists('mb_substr') ? mb_substr($part, 0, 1, 'UTF-8') : substr($part, 0, 1);
        if ((function_exists('mb_strlen') ? mb_strlen($out, 'UTF-8') : strlen($out)) >= 2) break;
    }
    return function_exists('mb_strtoupper') ? mb_strtoupper($out, 'UTF-8') : strtoupper($out);
}

$hsIsMainAdministrator = (isset($_SESSION['JePoduzivatel'], $_SESSION['JeObsluha']) && $_SESSION['JePoduzivatel'] == '0' && $_SESSION['JeObsluha'] == '0');
if (!$hsIsMainAdministrator) {
    hsAccessRedirect('index.php');
    return;
}

$jmenoStranky = 'Přehled přístupů';
$jmenoStrankyPopis = 'Přehled aktuálních přístupů do systému';
$pobocka_jmeno = !empty($_SESSION['pobocka_jmeno']) ? $_SESSION['pobocka_jmeno'] : 'Nevybrána';
$sw_id = !empty($_SESSION['pobocka_id']) ? (int)$_SESSION['pobocka_id'] : 0;
$Uzivatel_ID = !empty($_SESSION['k_id']) ? (int)$_SESSION['k_id'] : 0;
$notice = '';
$noticeType = 'success';

$k_poduzivatele_jmeno = hsAccessInput($_POST, 'k_poduzivatele_jmeno');
$k_poduzivatele_email = hsAccessInput($_POST, 'k_poduzivatele_email');
$k_poduzivatele_heslo = hsAccessInput($_POST, 'k_poduzivatele_heslo');
$ZalozitUzivateleSystemu = hsAccessInput($_POST, 'ZalozitUzivateleSystemu');
$ZmenaManageraProfilovkaCombo = hsAccessInput($_POST, 'ZmenaManageraProfilovkaCombo');
$SmazatSouborManager = hsAccessInput($_GET, 'SmazatSouborManager');
$JmenoSouboruManager = hsAccessInput($_GET, 'JmenoSouboruManager');
$SmazaniPoduzivateleZeSystemu = hsAccessInput($_GET, 'SmazaniPoduzivateleZeSystemu');
$Uzivatel_GUID = hsAccessInput($_GET, 'Uzivatel_GUID');

/* Založení manažera – stejné názvy polí, SHA-1 i e-mailová logika jako v legacy stránce. */
if ($ZalozitUzivateleSystemu === '1' && $k_poduzivatele_jmeno !== '' && $k_poduzivatele_email !== '' && $k_poduzivatele_heslo !== '' && $Uzivatel_ID > 0) {
    $Sablona = "<div><span style=\" font-family:Tahoma, Arial, sans-serif; font-size: 19pt;\"><b>HairSoft - Klient<br><br></b><span style=\" font-size: 10pt;\">Zasíláme Vám přihlašovací údaje do HairSoft Systému<br><br>Link na Váš klientský web: <a href=\"https://klient.hairsoft.cz\" target=\"_self\"><b>klient.hairsoft.cz</b></a><br>Přihlašovací email: <b>".$k_poduzivatele_email."</b><br>Přihlašovací heslo: <b>".$k_poduzivatele_heslo."</b><br><br><i>HairSoft Team <br><br><img src=\"https://admin.hairsoft.cz/dashboard/img/email/email.jpg\" style=\"padding : 1px;\" alt=\"\" width=\"217\" height=\"99\"><br><br></i><span style=\" color: #333333;\"><b>UnisSoft s.r.o.</b> | produkt HairSoft<br><br><span style=\" color: #000000;\">IČ: 277 40 013<br><span style=\" color: #333333;\">Nad Jihlávkou 5064/6, 586 01, Jihlava<br>Czech Republic<br><br>mobil: +420 608 936 960<br>e-mail: </span></span></span></span></span><a style=\" color: #333333; font-family:Tahoma, Arial, sans-serif; font-size: 10pt;\" target=\"_blank\">salon@hairsoft.cz</a><br> <span style=\" font-family:Tahoma, Arial, sans-serif; font-size: 10pt; color: #333333;\">web: </span><a href=\"http://www.hairsoft.cz\" style=\" color: #333333; font-family:Tahoma, Arial, sans-serif; font-size: 10pt;\" target=\"_blank\">www.hairsoft.cz</a></span></div>";
    PosliEmail($k_poduzivatele_email, 'HairSoft Klient - Přihlašovací údaje', $Sablona);

    $sql = "INSERT INTO `k_poduzivatele` (`k_poduzivatele_id`, `k_poduzivatele_jmeno`, `k_poduzivatele_email`, `k_poduzivatele_heslo`, `k_poduzivatele_hlavni`, `k_poduzivatele_hash`) VALUES (NULL, '" . $k_poduzivatele_jmeno . "', '" . $k_poduzivatele_email . "', '" . SHA1($k_poduzivatele_heslo) . "', '" . $Uzivatel_ID . "', '" . SHA1($k_poduzivatele_heslo . date('U')) . "')";
    $result = @$mysqli->query($sql);
    if ($result) {
        hsAccessRedirect('index.php?strana=NastaveniPristupy');
        return;
    }
    $notice = $mysqli->error;
    $noticeType = 'error';
}

/* Smazání profilovky – funkce zachována, ale omezená jen na manažery aktuálního administrátora. */
if ($SmazatSouborManager === '1' && $JmenoSouboruManager !== '' && $Uzivatel_ID > 0) {
    $safeFile = basename($JmenoSouboruManager);
    $sql = "UPDATE `k_poduzivatele` SET `k_poduzivatele_foto`='' WHERE `k_poduzivatele_foto`='" . $safeFile . "' AND `k_poduzivatele_hlavni`=" . $Uzivatel_ID;
    $result = @$mysqli->query($sql);
    if ($result) {
        @unlink('strana/galerie/manager/' . $safeFile);
        @unlink('strana/galerie/manager/m' . $safeFile);
        hsAccessRedirect('index.php?strana=NastaveniPristupy');
        return;
    }
    $notice = $mysqli->error;
    $noticeType = 'error';
}

/* Smazání manažera – původní akce, nově svázaná s aktuálním hlavním účtem. */
if ($SmazaniPoduzivateleZeSystemu === '1' && $Uzivatel_GUID !== '' && $Uzivatel_ID > 0) {
    $sql = "DELETE FROM `k_poduzivatele` WHERE `k_poduzivatele_hash`='" . $Uzivatel_GUID . "' AND `k_poduzivatele_hlavni`=" . $Uzivatel_ID;
    $result = @$mysqli->query($sql);
    if ($result) {
        hsAccessRedirect('index.php?strana=NastaveniPristupy');
        return;
    }
    $notice = $mysqli->error;
    $noticeType = 'error';
}

$managers = array();
if ($Uzivatel_ID > 0) {
    $sql = "SELECT * FROM `k_poduzivatele` WHERE `k_poduzivatele_hlavni`=" . $Uzivatel_ID . " ORDER BY `k_poduzivatele_id` ASC";
    $result = $mysqli->query($sql);
    if ($result) {
        while ($row = MySQLi_Fetch_Array($result)) $managers[] = $row;
    }
}

$selectedManager = null;
if (!empty($managers)) {
    if ($ZmenaManageraProfilovkaCombo !== '') {
        foreach ($managers as $manager) {
            if ((string)$manager['k_poduzivatele_hash'] === (string)$ZmenaManageraProfilovkaCombo) {
                $selectedManager = $manager;
                break;
            }
        }
    }
    if (!$selectedManager) $selectedManager = $managers[0];
}

$adminName = !empty($_SESSION['uzivatel_prijmeni_jmeno']) ? $_SESSION['uzivatel_prijmeni_jmeno'] : 'Administrátor';
$adminEmail = !empty($_SESSION['k_email']) ? $_SESSION['k_email'] : '';
?>
<section class="main-content-wrapper">
  <div class="pageheader">
    <h1><?php echo hsAccessEsc($jmenoStranky); ?></h1>
    <p class="description"><?php echo hsAccessEsc($jmenoStrankyPopis); ?></p>
    <div class="breadcrumb-wrapper hidden-xs">
      <span class="label">Pobočka:</span>
      <ol class="breadcrumb"><li class="active"><b><?php echo hsAccessEsc($pobocka_jmeno); ?></b> <?php echo JePobockaOnline($sw_id, $mysqli); ?></li></ol>
      <font color="#999" size="1">&nbsp;&nbsp;SW ID: <?php echo hsAccessEsc($sw_id); ?></font>
    </div>
  </div>

  <section id="main-content" class="hs-access-page hs-access-overview">
    <?php if ($notice !== ''): ?>
      <div class="hs-access-notice hs-access-notice--<?php echo $noticeType === 'error' ? 'error' : 'success'; ?>" role="status"><?php echo hsAccessEsc($notice); ?></div>
    <?php endif; ?>

    <section class="panel panel-default hs-access-card hs-access-users-card">
      <div class="panel-heading">
        <span class="hs-access-heading-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </span>
        <div>
          <h3 class="panel-title">Seznam uživatelů</h3>
          <span class="hs-access-heading-note">Správa administrátora a manažerů s přístupem do HairSoft</span>
        </div>
      </div>
      <div class="panel-body">
        <div class="hs-access-table-toolbar"></div>
        <div class="hs-access-table-scroll">
          <table id="hs_access_users_table" class="table hs-access-table" width="100%">
            <thead>
              <tr>
                <th>Detail</th>
                <th>Role</th>
                <th>Jméno uživatele</th>
                <th>Email</th>
                <th>Akce</th>
              </tr>
            </thead>
            <tbody>
              <tr class="hs-access-clickable-row" data-href="index.php?strana=NastaveniDetailObsluhy" tabindex="0">
                <td>
                  <a class="hs-access-avatar hs-access-avatar--initials" href="index.php?strana=NastaveniDetailObsluhy" title="Detail">
                    <span><?php echo hsAccessEsc(hsAccessInitials($adminName)); ?></span>
                  </a>
                </td>
                <td><span class="hs-access-role hs-access-role--admin">Administrátor</span></td>
                <td><strong><?php echo hsAccessEsc($adminName); ?></strong></td>
                <td><?php echo hsAccessEsc($adminEmail); ?></td>
                <td><a class="hs-access-action hs-access-action--edit" href="index.php?strana=NastaveniDetailObsluhy" title="Detail"><svg viewBox="0 0 24 24"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"></path><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V21h-4v-.08A1.7 1.7 0 0 0 9 19.37a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.63 15a1.7 1.7 0 0 0-1.56-1.03H3v-4h.08A1.7 1.7 0 0 0 4.63 9a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.63a1.7 1.7 0 0 0 1.03-1.56V3h4v.08A1.7 1.7 0 0 0 15 4.63a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.37 9a1.7 1.7 0 0 0 1.56 1.03H21v4h-.08A1.7 1.7 0 0 0 19.4 15z"></path></svg></a></td>
              </tr>
              <?php foreach ($managers as $manager):
                  $guid = (string)$manager['k_poduzivatele_hash'];
                  $name = (string)$manager['k_poduzivatele_jmeno'];
                  $email = (string)$manager['k_poduzivatele_email'];
                  $photo = isset($manager['k_poduzivatele_foto']) ? (string)$manager['k_poduzivatele_foto'] : '';
                  $detailUrl = 'index.php?strana=NastaveniDetailUzivatele&Uzivatel_GUID=' . rawurlencode($guid);
              ?>
              <tr class="hs-access-clickable-row" data-href="<?php echo hsAccessEsc($detailUrl); ?>" tabindex="0">
                <td>
                  <a class="hs-access-avatar<?php echo $photo === '' ? ' hs-access-avatar--initials' : ''; ?>" href="<?php echo hsAccessEsc($detailUrl); ?>" title="Detail">
                    <?php if ($photo !== ''): ?><img src="https://klient.hairsoft.cz/str/strana/galerie/manager/m<?php echo rawurlencode($photo); ?>" alt=""><?php else: ?><span><?php echo hsAccessEsc(hsAccessInitials($name)); ?></span><?php endif; ?>
                  </a>
                </td>
                <td><span class="hs-access-role">Manažer</span></td>
                <td><strong><?php echo hsAccessEsc($name); ?></strong></td>
                <td><?php echo hsAccessEsc($email); ?></td>
                <td>
                  <div class="hs-access-actions">
                    <a class="hs-access-action hs-access-action--edit" href="<?php echo hsAccessEsc($detailUrl); ?>" title="Detail"><svg viewBox="0 0 24 24"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"></path><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V21h-4v-.08A1.7 1.7 0 0 0 9 19.37a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.63 15a1.7 1.7 0 0 0-1.56-1.03H3v-4h.08A1.7 1.7 0 0 0 4.63 9a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.63a1.7 1.7 0 0 0 1.03-1.56V3h4v.08A1.7 1.7 0 0 0 15 4.63a1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.37 9a1.7 1.7 0 0 0 1.56 1.03H21v4h-.08A1.7 1.7 0 0 0 19.4 15z"></path></svg></a>
                    <a class="hs-access-action hs-access-action--delete" href="index.php?strana=NastaveniPristupy&amp;SmazaniPoduzivateleZeSystemu=1&amp;Uzivatel_GUID=<?php echo rawurlencode($guid); ?>" onclick="if(!confirm('Opravdu chcete smazat tohoto manažera? Přístup bude trvale odstraněn.')){return false;}" data-hs-confirm-title="Potvrdit smazání uživatele" data-hs-confirm-action="Smazat uživatele" title="Smazat"><svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="m19 6-1 15H6L5 6"></path><path d="M10 11v5M14 11v5"></path></svg></a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="hs-access-mobile-cards"></div>
      </div>
    </section>

    <div class="hs-access-management-grid">
      <section class="panel panel-default hs-access-card hs-access-profile-card">
        <div class="panel-heading">
          <span class="hs-access-heading-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"></circle><path d="M4 21a8 8 0 0 1 16 0"></path></svg></span>
          <div><h3 class="panel-title">Profil manažera</h3><span class="hs-access-heading-note">Vyberte manažera a spravujte jeho profilovou fotografii</span></div>
        </div>
        <div class="panel-body">
          <?php if ($selectedManager):
              $selectedGuid = (string)$selectedManager['k_poduzivatele_hash'];
              $selectedName = (string)$selectedManager['k_poduzivatele_jmeno'];
              $selectedPhoto = isset($selectedManager['k_poduzivatele_foto']) ? (string)$selectedManager['k_poduzivatele_foto'] : '';
          ?>
          <div class="hs-access-profile-layout">
            <div class="hs-access-profile-preview<?php echo $selectedPhoto === '' ? ' hs-access-profile-preview--initials' : ''; ?>">
              <?php if ($selectedPhoto !== ''): ?><img src="https://klient.hairsoft.cz/str/strana/galerie/manager/m<?php echo rawurlencode($selectedPhoto); ?>" alt=""><?php else: ?><span><?php echo hsAccessEsc(hsAccessInitials($selectedName)); ?></span><?php endif; ?>
            </div>
            <div class="hs-access-profile-controls">
              <form action="index.php?strana=NastaveniPristupy" method="POST" class="hs-access-profile-select-form">
                <label class="hs-access-field"><span>Výběr manažera</span>
                  <select name="ZmenaManageraProfilovkaCombo" id="hs_access_profile_manager" class="form-control">
                    <?php foreach ($managers as $manager): ?>
                    <option value="<?php echo hsAccessEsc($manager['k_poduzivatele_hash']); ?>" <?php echo (string)$manager['k_poduzivatele_hash'] === $selectedGuid ? 'selected' : ''; ?>><?php echo hsAccessEsc($manager['k_poduzivatele_jmeno']); ?></option>
                    <?php endforeach; ?>
                  </select>
                </label>
              </form>
              <div class="hs-access-profile-actions">
                <button type="button" id="hs_access_profile_upload" class="btn hs-access-btn hs-access-btn--primary"><svg viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg><?php echo $selectedPhoto !== '' ? 'Změnit profilovku' : 'Nahrát profilovku'; ?></button>
                <?php if ($selectedPhoto !== ''): ?><a class="btn hs-access-btn hs-access-btn--danger" href="index.php?strana=NastaveniPristupy&amp;SmazatSouborManager=1&amp;JmenoSouboruManager=<?php echo rawurlencode($selectedPhoto); ?>" onclick="if(!confirm('Opravdu chcete smazat profilovou fotografii?')){return false;}" data-hs-confirm-title="Potvrdit smazání" data-hs-confirm-action="Smazat profilovku"><svg viewBox="0 0 24 24"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="m19 6-1 15H6L5 6"></path></svg>Smazat profilovku</a><?php endif; ?>
              </div>
              <form action="https://klient.hairsoft.cz/str/strana/NahratSoubor.php" method="POST" enctype="multipart/form-data" id="hs_access_profile_upload_form">
                <input hidden name="managerFileInput" id="hs_access_profile_file" type="file" accept="image/*" capture="environment">
                <input type="hidden" name="akce" value="managerFoto">
                <input type="hidden" name="k_poduzivatele_hash" value="<?php echo hsAccessEsc($selectedGuid); ?>">
              </form>
            </div>
          </div>
          <?php else: ?>
            <div class="hs-access-empty">Nejprve vytvořte manažera. Poté zde můžete nastavit jeho profilovou fotografii.</div>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel panel-default hs-access-card hs-access-new-card">
        <div class="panel-heading">
          <span class="hs-access-heading-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M15 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8" cy="7" r="4"></circle><path d="M19 8v6M22 11h-6"></path></svg></span>
          <div><h3 class="panel-title">Nový manažer</h3><span class="hs-access-heading-note">Vytvořte další přístup do HairSoft</span></div>
        </div>
        <div class="panel-body">
          <form action="index.php?strana=NastaveniPristupy" method="POST" class="hs-access-new-form" id="hs_access_new_user_form">
            <input type="hidden" name="Uzivatel_ID" value="<?php echo hsAccessEsc($Uzivatel_ID); ?>">
            <input type="hidden" name="ZalozitUzivateleSystemu" value="1">
            <label class="hs-access-field"><span>Jméno uživatele</span><input class="form-control" type="text" id="hs_access_new_name" name="k_poduzivatele_jmeno" placeholder="Jméno manažera" minlength="2" required></label>
            <label class="hs-access-field"><span>Email</span><input class="form-control" type="email" id="hs_access_new_email" name="k_poduzivatele_email" placeholder="Zadejte platný email" required></label>
            <label class="hs-access-field"><span>Heslo</span><input class="form-control" type="password" id="hs_access_new_password" name="k_poduzivatele_heslo" placeholder="Min 4 znaky" minlength="4" required></label>
            <div class="hs-access-new-actions"><button type="submit" id="hs_access_new_submit" class="btn hs-access-btn hs-access-btn--primary" disabled><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"></path></svg>Přidat uživatele</button></div>
          </form>
        </div>
      </section>
    </div>
  </section>
</section>
