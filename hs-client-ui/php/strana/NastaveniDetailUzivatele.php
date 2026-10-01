<?php
/* HairSoft Klient V177 – moderní detail manažera a přístupových práv. */
if (!defined('HS_CLIENT_UI_ROOT')) { define('HS_CLIENT_UI_ROOT', dirname(__DIR__, 3)); }
require HS_CLIENT_UI_ROOT . '/str/strana/_zabezpeceni.php';
require_once HS_CLIENT_UI_ROOT . '/fce/PosliEmail.php';
require_once HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';

function hsAccessDetailEsc($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function hsAccessDetailInput($source, $key) {
    if (!isset($source[$key]) || is_array($source[$key])) return '';
    return htmlspecialchars((string)$source[$key], ENT_COMPAT);
}
function hsAccessDetailRedirect($url) { echo '<meta http-equiv="refresh" content="0;URL=' . hsAccessDetailEsc($url) . '">'; }
function hsAccessDetailInitials($name) {
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
function hsAccessSavePermission($userId, $branchId, $key, $enabled, $mysqli) {
    $userId = (int)$userId;
    $branchId = (int)$branchId;
    if ($enabled === '1') {
        $sql = "SELECT `prava_id` FROM `k_poduzivatele_prava` WHERE `k_poduzivatele_id`=" . $userId . " AND `prava_uzivatel_menu`='" . $key . "' AND `pobocka_id`=" . $branchId . " AND `prava_uzivatel_pravo`='1' LIMIT 1";
        $result = $mysqli->query($sql);
        if ($result && $result->num_rows == 0) {
            $insert = "INSERT INTO `k_poduzivatele_prava` (`prava_id`,`k_poduzivatele_id`,`prava_uzivatel_menu`,`prava_uzivatel_pravo`,`pobocka_id`) VALUES (NULL,'" . $userId . "','" . $key . "','1','" . $branchId . "')";
            @$mysqli->query($insert);
        }
    } else {
        $delete = "DELETE FROM `k_poduzivatele_prava` WHERE `k_poduzivatele_id`=" . $userId . " AND `prava_uzivatel_menu`='" . $key . "' AND `pobocka_id`=" . $branchId . " AND `prava_uzivatel_pravo`='1'";
        @$mysqli->query($delete);
    }
}

$hsIsMainAdministrator = (isset($_SESSION['JePoduzivatel'], $_SESSION['JeObsluha']) && $_SESSION['JePoduzivatel'] == '0' && $_SESSION['JeObsluha'] == '0');
if (!$hsIsMainAdministrator) { hsAccessDetailRedirect('index.php'); return; }

$mainUserId = !empty($_SESSION['k_id']) ? (int)$_SESSION['k_id'] : 0;
$guid = hsAccessDetailInput($_GET, 'Uzivatel_GUID');
if ($guid === '') $guid = hsAccessDetailInput($_POST, 'Uzivatel_POST_GUID');
$changePassword = hsAccessDetailInput($_POST, 'ZmenaHeslaPoduzivateleSystemu');
$newPassword = hsAccessDetailInput($_POST, 'k_poduzivatele_heslo');
$changeBranch = hsAccessDetailInput($_POST, 'ZmenaPobockyPrava');
$changePermission = hsAccessDetailInput($_POST, 'ZmenaKonkretnihoPrava');
$selectedBranch = hsAccessDetailInput($_POST, 'VybranaPobockaID');

$manager = null;
if ($guid !== '' && $mainUserId > 0) {
    $sql = "SELECT * FROM `k_poduzivatele` WHERE `k_poduzivatele_hash`='" . $guid . "' AND `k_poduzivatele_hlavni`=" . $mainUserId . " LIMIT 1";
    $result = $mysqli->query($sql);
    if ($result && $result->num_rows === 1) $manager = MySQLi_Fetch_Array($result);
}
if (!$manager) {
    hsAccessDetailRedirect('index.php?strana=NastaveniPristupy');
    return;
}

$managerId = (int)$manager['k_poduzivatele_id'];
$managerName = (string)$manager['k_poduzivatele_jmeno'];
$managerEmail = (string)$manager['k_poduzivatele_email'];
$managerPhoto = isset($manager['k_poduzivatele_foto']) ? (string)$manager['k_poduzivatele_foto'] : '';
$notice = '';
$noticeType = 'success';

if ($changePassword === '1' && $newPassword !== '') {
    $k_poduzivatele_email = $managerEmail;
    $k_poduzivatele_heslo = $newPassword;
    $Sablona = "<div><span style=\" font-family:Tahoma, Arial, sans-serif; font-size: 19pt;\"><b>HairSoft - Klient<br><br></b><span style=\" font-size: 10pt;\">Zasíláme Vám nové přihlašovací údaje do HairSoft Systému<br><br>Link na Váš klientský web: <a href=\"https://klient.hairsoft.cz\" target=\"_self\"><b>klient.hairsoft.cz</b></a><br>Přihlašovací email: <b>".$k_poduzivatele_email."</b><br>Přihlašovací heslo: <b>".$k_poduzivatele_heslo."</b><br><br><i>HairSoft Team <br><br><img src=\"https://admin.hairsoft.cz/dashboard/img/email/email.jpg\" style=\"padding : 1px;\" alt=\"\" width=\"217\" height=\"99\"><br><br></i><span style=\" color: #333333;\"><b>UnisSoft s.r.o.</b> | produkt HairSoft<br><br><span style=\" color: #000000;\">IČ: 277 40 013<br><span style=\" color: #333333;\">Nad Jihlávkou 5064/6, 586 01, Jihlava<br>Czech Republic<br><br>mobil: +420 608 936 960<br>e-mail: </span></span></span></span></span><a style=\" color: #333333; font-family:Tahoma, Arial, sans-serif; font-size: 10pt;\" target=\"_blank\">info@hairsoft.cz</a><br> <span style=\" font-family:Tahoma, Arial, sans-serif; font-size: 10pt; color: #333333;\">web: </span><a href=\"http://www.hairsoft.cz\" style=\" color: #333333; font-family:Tahoma, Arial, sans-serif; font-size: 10pt;\" target=\"_blank\">www.hairsoft.cz</a></span></div>";
    PosliEmail($managerEmail, 'HairSoft Klient - Změna hesla', $Sablona);
    $sql = "UPDATE `k_poduzivatele` SET `k_poduzivatele_heslo`='" . SHA1($newPassword) . "' WHERE `k_poduzivatele_hash`='" . $guid . "' AND `k_poduzivatele_hlavni`=" . $mainUserId;
    $result = @$mysqli->query($sql);
    if ($result) $notice = 'Změna nového hesla byla úspěšně provedena.';
    else { $notice = $mysqli->error; $noticeType = 'error'; }
}

$branches = array();
if (!empty($_SESSION['k_email'])) {
    $email = $_SESSION['k_email'];
    $sql = "SELECT `sw_info`.`sw_jmeno_pobocky`, `sw_info`.`sw_id` FROM `sw_email_pobocka` JOIN `k_uzivatele` ON `sw_email_pobocka`.`k_id`=`k_uzivatele`.`k_id` JOIN `sw_info` ON `sw_info`.`sw_id`=`sw_email_pobocka`.`sw_id` WHERE `k_uzivatele`.`k_email`='" . $email . "'";
    $result = $mysqli->query($sql);
    if ($result) while ($row = MySQLi_Fetch_Array($result)) $branches[] = $row;
}
if ($selectedBranch === '' && !empty($branches)) $selectedBranch = (string)$branches[0]['sw_id'];
$selectedBranchId = (int)$selectedBranch;

$permissionMap = array(
    'Prava_Dashboard' => array('Dashboard', 'Dashboard'),
    'Prava_NovyZakaznik' => array('NovyZakaznik', 'Nový zákazník'),
    'Prava_Zakaznici' => array('Zakaznici', 'Zákazníci'),
    'Prava_Rezervace' => array('Rezervace', 'Rezervace'),
    'Prava_Trzby' => array('Trzby', 'Tržby'),
    'Prava_Sklad' => array('Sklad', 'Sklad'),
    'Prava_Voucher' => array('Voucher', 'Voucher'),
    'Prava_SMS' => array('SMS', 'SMS a hovory'),
    'Prava_Kamery' => array('Kamery', 'Kamery'),
    'Prava_Hodnoceni' => array('Hodnoceni', 'Hodnocení'),
    'Prava_Uzivatele' => array('Uzivatele', 'Uživatelé'),
    'Prava_Cenik' => array('Cenik', 'Ceník')
);

if ($changePermission === '1' && $selectedBranchId > 0) {
    foreach ($permissionMap as $postName => $data) {
        $enabled = hsAccessDetailInput($_POST, $postName);
        hsAccessSavePermission($managerId, $selectedBranchId, $data[0], $enabled, $mysqli);
    }
}

$rights = array();
if ($selectedBranchId > 0) {
    $sql = "SELECT `prava_uzivatel_menu` FROM `k_poduzivatele_prava` WHERE `k_poduzivatele_id`=" . $managerId . " AND `pobocka_id`=" . $selectedBranchId . " AND `prava_uzivatel_pravo`=1";
    $result = $mysqli->query($sql);
    if ($result) while ($row = MySQLi_Fetch_Array($result)) $rights[] = $row['prava_uzivatel_menu'];
}
$branchName = '';
foreach ($branches as $branch) if ((int)$branch['sw_id'] === $selectedBranchId) { $branchName = $branch['sw_jmeno_pobocky']; break; }
?>
<section class="main-content-wrapper">
  <div class="pageheader">
    <h1><span>Detail uživatele</span>: <b><?php echo hsAccessDetailEsc($managerName); ?></b></h1>
    <p class="description">Detail uživatele - podrobné nastavení.</p>
    <div class="breadcrumb-wrapper hidden-xs"><a class="hs-access-back-link" href="index.php?strana=NastaveniPristupy">← Zpět na přístupy</a></div>
  </div>

  <section id="main-content" class="hs-access-page hs-access-detail-page">
    <?php if ($notice !== ''): ?><div class="hs-access-notice hs-access-notice--<?php echo $noticeType; ?>" role="status"><?php echo hsAccessDetailEsc($notice); ?></div><?php endif; ?>

    <section class="panel panel-default hs-access-card hs-access-identity-card">
      <div class="panel-body">
        <div class="hs-access-detail-avatar<?php echo $managerPhoto === '' ? ' hs-access-detail-avatar--initials' : ''; ?>">
          <?php if ($managerPhoto !== ''): ?><img src="https://klient.hairsoft.cz/str/strana/galerie/manager/m<?php echo rawurlencode($managerPhoto); ?>" alt=""><?php else: ?><span><?php echo hsAccessDetailEsc(hsAccessDetailInitials($managerName)); ?></span><?php endif; ?>
        </div>
        <div class="hs-access-identity-copy"><span class="hs-access-role">Manažer</span><h2><?php echo hsAccessDetailEsc($managerName); ?></h2><p><?php echo hsAccessDetailEsc($managerEmail); ?></p></div>
        <a class="btn hs-access-btn hs-access-btn--secondary hs-access-detail-back" href="index.php?strana=NastaveniPristupy">Zpět na přístupy</a>
      </div>
    </section>

    <div class="hs-access-detail-grid">
      <section class="panel panel-default hs-access-card">
        <div class="panel-heading"><span class="hs-access-heading-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 21h18"></path><path d="M6 21V7l6-4 6 4v14"></path><path d="M9 10h6M9 14h6M9 18h6"></path></svg></span><div><h3 class="panel-title">Výběr pobočky</h3><span class="hs-access-heading-note">Práva se nastavují samostatně pro každou pobočku.</span></div></div>
        <div class="panel-body">
          <form action="index.php?strana=NastaveniDetailUzivatele" method="POST" id="hs_access_branch_form">
            <input type="hidden" name="Uzivatel_POST_GUID" value="<?php echo hsAccessDetailEsc($guid); ?>">
            <input type="hidden" name="ZmenaPobockyPrava" value="1">
            <label class="hs-access-field"><span>Pobočka</span><select class="form-control" name="VybranaPobockaID" id="hs_access_branch_select">
              <?php foreach ($branches as $branch): ?><option value="<?php echo hsAccessDetailEsc($branch['sw_id']); ?>" <?php echo (int)$branch['sw_id'] === $selectedBranchId ? 'selected' : ''; ?>><?php echo hsAccessDetailEsc($branch['sw_jmeno_pobocky']); ?></option><?php endforeach; ?>
            </select></label>
          </form>
        </div>
      </section>

      <section class="panel panel-default hs-access-card">
        <div class="panel-heading"><span class="hs-access-heading-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg></span><div><h3 class="panel-title">Změna hesla uživatele</h3><span class="hs-access-heading-note">Nastavte nové přihlašovací heslo manažera.</span></div></div>
        <div class="panel-body">
          <form action="index.php?strana=NastaveniDetailUzivatele" method="POST" class="hs-access-password-form" id="hs_access_password_form">
            <input type="hidden" name="Uzivatel_POST_GUID" value="<?php echo hsAccessDetailEsc($guid); ?>">
            <input type="hidden" name="ZmenaHeslaPoduzivateleSystemu" value="1">
            <input type="hidden" name="VybranaPobockaID" value="<?php echo hsAccessDetailEsc($selectedBranchId); ?>">
            <label class="hs-access-field"><span>Nové heslo</span><input class="form-control" placeholder="Min 4 znaky" type="password" name="k_poduzivatele_heslo" id="hs_access_password" minlength="4" required></label>
            <button type="submit" id="hs_access_password_submit" class="btn hs-access-btn hs-access-btn--primary" disabled>Změnit heslo</button>
          </form>
        </div>
      </section>
    </div>

    <section class="panel panel-default hs-access-card hs-access-permissions-card">
      <div class="panel-heading"><span class="hs-access-heading-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"></path><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg></span><div class="hs-access-heading-copy"><h3 class="panel-title">Práva na menu</h3><span class="hs-access-heading-note"><span>Povolené části systému pro vybranou pobočku</span><?php if ($branchName !== ''): ?>: <strong><?php echo hsAccessDetailEsc($branchName); ?></strong><?php endif; ?>.</span></div></div>
      <div class="panel-body">
        <form action="index.php?strana=NastaveniDetailUzivatele" method="POST" id="hs_access_permissions_form">
          <input type="hidden" name="Uzivatel_POST_GUID" value="<?php echo hsAccessDetailEsc($guid); ?>">
          <input type="hidden" name="k_poduzivatele_id" value="<?php echo hsAccessDetailEsc($managerId); ?>">
          <input type="hidden" name="VybranaPobockaID" value="<?php echo hsAccessDetailEsc($selectedBranchId); ?>">
          <input type="hidden" name="ZmenaKonkretnihoPrava" value="1">
          <div class="hs-access-permission-grid">
            <?php $idx = 0; foreach ($permissionMap as $postName => $data): $idx++; $checked = in_array($data[0], $rights, true); ?>
            <label class="hs-access-permission-item" for="hs_access_permission_<?php echo $idx; ?>">
              <span class="hs-access-permission-copy"><strong><?php echo hsAccessDetailEsc($data[1]); ?></strong><small><?php echo $checked ? 'Povoleno' : 'Zakázáno'; ?></small></span>
              <span class="hs-access-switch"><input type="checkbox" id="hs_access_permission_<?php echo $idx; ?>" name="<?php echo hsAccessDetailEsc($postName); ?>" value="1" <?php echo $checked ? 'checked' : ''; ?>><span aria-hidden="true"></span></span>
            </label>
            <?php endforeach; ?>
          </div>
        </form>
      </div>
    </section>
  </section>
</section>
