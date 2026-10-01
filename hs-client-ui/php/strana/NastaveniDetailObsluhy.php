<?php
/* HairSoft Klient V180 – moderní detail hlavního administrátora / změna hesla. */
if (!defined('HS_CLIENT_UI_ROOT')) { define('HS_CLIENT_UI_ROOT', dirname(__DIR__, 3)); }
require HS_CLIENT_UI_ROOT . '/str/strana/_zabezpeceni.php';
require_once HS_CLIENT_UI_ROOT . '/fce/PosliEmail.php';

function hsAccessAdminEsc($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function hsAccessAdminInput($source, $key) {
    if (!isset($source[$key]) || is_array($source[$key])) return '';
    return htmlspecialchars((string)$source[$key], ENT_COMPAT);
}
function hsAccessAdminRedirect($url) { echo '<meta http-equiv="refresh" content="0;URL=' . hsAccessAdminEsc($url) . '">'; }
function hsAccessAdminInitials($name) {
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
if (!$hsIsMainAdministrator) { hsAccessAdminRedirect('index.php'); return; }

$adminId = !empty($_SESSION['k_id']) ? (int)$_SESSION['k_id'] : 0;
$adminName = isset($_SESSION['uzivatel_prijmeni_jmeno']) ? trim((string)$_SESSION['uzivatel_prijmeni_jmeno']) : '';
$adminEmail = isset($_SESSION['k_email']) ? trim((string)$_SESSION['k_email']) : '';
$changePassword = hsAccessAdminInput($_POST, 'ZmenaHeslaAdminaSystemu');
$newPassword = hsAccessAdminInput($_POST, 'k_admin_heslo');
$notice = '';
$noticeType = 'success';

if ($changePassword === '1' && $adminId > 0 && $newPassword !== '' && $adminEmail !== '') {
    /* Zachována původní logika: heslo se uloží SHA-1 a nové údaje se odešlou na e-mail administrátora. */
    $Sablona = "<div>
                    <span style=\" font-family:Tahoma, Arial, sans-serif; font-size: 19pt;\"><b>HairSoft - Klient </b></span><br><br>
                    <span style=\" font-size: 10pt;\">Zasíláme nové přihlašovací údaje do systému.</span>
                        <br>
                        <br>Link na klientský web:
                        <a href=\"https://klient.hairsoft.cz\" target=\"_self\"><b>klient.hairsoft.cz</b></a>
                        <br>Přihlašovací email: <b>" . $adminEmail . "</b>
                        <br>Přihlašovací heslo: <b>" . $newPassword . "</b>
                        <br>
                        <br>
                        <span style=\" color: #333333;\"><b>UnisSoft s.r.o.</b> | produkt HairSoft</span>
                </div>";

    PosliEmail($adminEmail, 'Změna hesla - HairSoft Klient', $Sablona);

    $sql = "UPDATE `k_uzivatele` SET `k_heslo` = '" . SHA1($newPassword) . "' WHERE `k_id` = '" . $adminId . "'; ";
    $result = @$mysqli->query($sql);
    if ($result) {
        $notice = 'Změna nového hesla byla úspěšně provedena.';
    } else {
        $notice = $mysqli->error;
        $noticeType = 'error';
    }
}
?>
<section class="main-content-wrapper">
  <div class="pageheader">
    <h1><span>Nastavení administrátora</span>: <b><?php echo hsAccessAdminEsc($adminName); ?></b></h1>
    <p class="description">Změna přihlašovacího hesla hlavního administrátora.</p>
    <div class="breadcrumb-wrapper hidden-xs"><a class="hs-access-back-link" href="index.php?strana=NastaveniPristupy">← Zpět na seznam</a></div>
  </div>

  <section id="main-content" class="hs-access-page hs-access-admin-detail-page">
    <?php if ($notice !== ''): ?>
      <div class="hs-access-notice hs-access-notice--<?php echo hsAccessAdminEsc($noticeType); ?>" role="status"><?php echo hsAccessAdminEsc($notice); ?></div>
    <?php endif; ?>

    <section class="panel panel-default hs-access-card hs-access-identity-card hs-access-admin-identity-card">
      <div class="panel-body">
        <div class="hs-access-detail-avatar hs-access-detail-avatar--initials" aria-hidden="true"><span><?php echo hsAccessAdminEsc(hsAccessAdminInitials($adminName)); ?></span></div>
        <div class="hs-access-identity-copy">
          <span class="hs-access-role hs-access-role--admin">Hlavní administrátor</span>
          <h2><?php echo hsAccessAdminEsc($adminName); ?></h2>
          <p><?php echo hsAccessAdminEsc($adminEmail); ?></p>
        </div>
        <a class="btn hs-access-btn hs-access-btn--secondary hs-access-detail-back" href="index.php?strana=NastaveniPristupy">Zpět na seznam</a>
      </div>
    </section>

    <section class="panel panel-default hs-access-card hs-access-admin-password-card">
      <div class="panel-heading">
        <span class="hs-access-heading-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        </span>
        <div class="hs-access-heading-copy">
          <h3 class="panel-title">Změna hesla administrátora</h3>
          <span class="hs-access-heading-note">Nastavte nové přihlašovací heslo administrátora.</span>
        </div>
      </div>
      <div class="panel-body">
        <form action="index.php?strana=NastaveniDetailObsluhy" method="POST" class="hs-access-password-form hs-access-admin-password-form" id="hs_access_admin_password_form">
          <input type="hidden" name="ZmenaHeslaAdminaSystemu" value="1">
          <label class="hs-access-field">
            <span>Nové heslo</span>
            <input class="form-control" placeholder="Min 4 znaky" type="password" name="k_admin_heslo" id="hs_access_admin_password" minlength="4" autocomplete="new-password" required>
          </label>
          <button type="submit" id="hs_access_admin_password_submit" class="btn hs-access-btn hs-access-btn--primary" disabled>Změnit heslo</button>
        </form>
        <p class="hs-access-admin-password-note">Heslo bude po změně odesláno také na e-mail administrátora.</p>
      </div>
    </section>
  </section>
</section>
