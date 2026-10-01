<?php
/* HairSoft V175: moderní číselník pojišťoven. Datová logika zachována, přepracována pouze prezentace. */
if (!defined('HS_CLIENT_UI_ROOT')) { define('HS_CLIENT_UI_ROOT', dirname(__DIR__, 3)); }
require HS_CLIENT_UI_ROOT . '/str/strana/_zabezpeceni.php';
require_once HS_CLIENT_UI_ROOT . '/fce/PosliEmail.php';
require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';

function hsInsuranceInput($source, $key) {
    if (!isset($source[$key]) || is_array($source[$key])) return '';
    return htmlspecialchars($source[$key], ENT_COMPAT);
}
function hsInsuranceEsc($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$jmenoStranky = 'Nastavení číselníku pojišťoven';
$jmenoStrankyPopis = 'Správa zdravotních pojišťoven';
$pobocka_jmeno = isset($_SESSION['pobocka_jmeno']) && $_SESSION['pobocka_jmeno'] !== '' ? $_SESSION['pobocka_jmeno'] : 'Nevybrána';
$AkceTitulek = '';

$GETpojistovnaGUID = hsInsuranceInput($_GET, 'GETpojistovnaGUID');
$SmazaniPojistovnyZeSystemu = hsInsuranceInput($_GET, 'SmazaniPojistovnyZeSystemu');
$EditacePojitovny = hsInsuranceInput($_GET, 'EditacePojitovny');
$ZmenaPojistovny = hsInsuranceInput($_POST, 'ZmenaPojistovny');

if ($SmazaniPojistovnyZeSystemu === '1' && $GETpojistovnaGUID !== '') {
    $sql = "DELETE FROM `cislenik_pojistovny` WHERE `pojistovnaGUID` = '".$GETpojistovnaGUID."';";
    @$mysqli->query($sql);
    $AkceTitulek = 'Pojišťovna byla smazána.';
}

if ($ZmenaPojistovny == 1) {
    $jmeno = isset($_POST['jmenoPojistovny']) && !is_array($_POST['jmenoPojistovny']) ? $_POST['jmenoPojistovny'] : '';
    $kod = isset($_POST['kodPojistovny']) && !is_array($_POST['kodPojistovny']) ? $_POST['kodPojistovny'] : '';
    $zkratka = isset($_POST['zkratkaPojistovny']) && !is_array($_POST['zkratkaPojistovny']) ? $_POST['zkratkaPojistovny'] : '';
    $stat = isset($_POST['statPojistovny']) && !is_array($_POST['statPojistovny']) ? $_POST['statPojistovny'] : '';
    $guid = isset($_POST['pojistovnaGUID']) && !is_array($_POST['pojistovnaGUID']) ? $_POST['pojistovnaGUID'] : '';

    if ($guid !== '') {
        $sql = "UPDATE cislenik_pojistovny SET
                    jmenoPojistovny = '$jmeno',
                    kodPojistovny = '$kod',
                    zkratkaPojistovny = '$zkratka',
                    statPojistovny = '$stat'
                WHERE pojistovnaGUID = '$guid'";
        $mysqli->query($sql);
        $AkceTitulek = 'Pojišťovna byla editována.';
    } else {
        $newGUID = bin2hex(random_bytes(16));
        $sql = "INSERT INTO cislenik_pojistovny
                (id, jmenoPojistovny, kodPojistovny, zkratkaPojistovny, statPojistovny, pojistovnaGUID)
                VALUES
                (NULL, '$jmeno', '$kod', '$zkratka', '$stat', '$newGUID')";
        $mysqli->query($sql);
        $AkceTitulek = 'Pojišťovna byla založena.';
    }
}

$editData = array(
    'jmenoPojistovny' => '',
    'kodPojistovny' => '',
    'zkratkaPojistovny' => '',
    'statPojistovny' => 'CZ',
    'pojistovnaGUID' => ''
);
if ($EditacePojitovny == 1 && $GETpojistovnaGUID !== '') {
    $sqldotazPojistovna = "SELECT * FROM `cislenik_pojistovny` WHERE pojistovnaGUID = '".$GETpojistovnaGUID."' LIMIT 1";
    $vysledekPojistovna = $mysqli->query($sqldotazPojistovna);
    if ($vysledekPojistovna && $vysledekPojistovna->num_rows) {
        $row = MySQLi_Fetch_Array($vysledekPojistovna);
        foreach ($editData as $key => $value) {
            if (isset($row[$key])) $editData[$key] = $row[$key];
        }
    }
}

$insuranceRows = array();
$Selectpoduzivatele = "SELECT * FROM `cislenik_pojistovny` ORDER BY statPojistovny ASC, jmenoPojistovny ASC";
$vysledek_Selectpoduzivatele = $mysqli->query($Selectpoduzivatele);
if ($vysledek_Selectpoduzivatele) {
    while ($row = MySQLi_Fetch_Array($vysledek_Selectpoduzivatele)) $insuranceRows[] = $row;
}
?>
<section class="main-content-wrapper">
  <div class="pageheader">
    <h1><?php echo hsInsuranceEsc($jmenoStranky); ?></h1>
    <p class="description"><?php echo hsInsuranceEsc($jmenoStrankyPopis); ?></p>
    <div class="breadcrumb-wrapper hidden-xs">
      <span class="label">Pobočka:</span>
      <ol class="breadcrumb">
        <li class="active"><b><?php echo hsInsuranceEsc($pobocka_jmeno); ?></b> <?php echo JePobockaOnline($sw_id, $mysqli); ?></li>
      </ol>
      <font color="#999" size="1">&nbsp;&nbsp;SW ID: <?php echo hsInsuranceEsc($sw_id); ?></font>
    </div>
  </div>

  <section id="main-content" class="hs-insurance-page">
    <?php if ($AkceTitulek !== ''): ?>
      <div class="hs-insurance-notice" role="status">
        <span class="hs-insurance-notice-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"></path></svg>
        </span>
        <strong><?php echo hsInsuranceEsc($AkceTitulek); ?></strong>
      </div>
    <?php endif; ?>

    <?php if ($EditacePojitovny == 1): ?>
      <section class="panel panel-default hs-insurance-card hs-insurance-editor">
        <div class="panel-heading">
          <span class="hs-insurance-heading-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"></path></svg>
          </span>
          <div>
            <h3 class="panel-title"><?php echo $GETpojistovnaGUID !== '' ? 'Editace pojišťovny' : 'Nová pojišťovna'; ?></h3>
            <span class="hs-insurance-heading-note"><?php echo $GETpojistovnaGUID !== '' ? 'Upravte údaje vybrané pojišťovny' : 'Zadejte údaje nové pojišťovny'; ?></span>
          </div>
        </div>
        <div class="panel-body">
          <form action="index.php?strana=NastaveniCiselnikPojistoven" method="POST" class="hs-insurance-form">
            <input type="hidden" name="pojistovnaGUID" value="<?php echo hsInsuranceEsc($GETpojistovnaGUID); ?>">
            <input type="hidden" name="ZmenaPojistovny" value="1">
            <label class="hs-insurance-field hs-insurance-field--wide">
              <span>Jméno pojišťovny</span>
              <input type="text" name="jmenoPojistovny" class="form-control" value="<?php echo hsInsuranceEsc($editData['jmenoPojistovny']); ?>" required>
            </label>
            <label class="hs-insurance-field">
              <span>Kód pojišťovny</span>
              <input type="text" name="kodPojistovny" class="form-control" value="<?php echo hsInsuranceEsc($editData['kodPojistovny']); ?>">
            </label>
            <label class="hs-insurance-field">
              <span>Zkratka</span>
              <input type="text" name="zkratkaPojistovny" class="form-control" value="<?php echo hsInsuranceEsc($editData['zkratkaPojistovny']); ?>">
            </label>
            <label class="hs-insurance-field">
              <span>Stát</span>
              <select name="statPojistovny" class="form-control">
                <option value="CZ" <?php echo $editData['statPojistovny'] === 'CZ' ? 'selected' : ''; ?>>CZ</option>
                <option value="SK" <?php echo $editData['statPojistovny'] === 'SK' ? 'selected' : ''; ?>>SK</option>
              </select>
            </label>
            <div class="hs-insurance-form-actions">
              <a href="index.php?strana=NastaveniCiselnikPojistoven" class="btn hs-insurance-btn hs-insurance-btn--secondary">Zrušit</a>
              <button type="submit" class="btn hs-insurance-btn hs-insurance-btn--primary">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><path d="M17 21v-8H7v8"></path><path d="M7 3v5h8"></path></svg>
                Uložit pojišťovnu
              </button>
            </div>
          </form>
        </div>
      </section>
    <?php endif; ?>

    <section class="panel panel-default hs-insurance-card hs-insurance-list-card">
      <div class="panel-heading">
        <span class="hs-insurance-heading-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M3 10h18M9 4v16"></path></svg>
        </span>
        <div class="hs-insurance-heading-copy">
          <h3 class="panel-title">Seznam pojišťoven</h3>
          <span class="hs-insurance-heading-note">Přehled pojišťoven používaných v kartách zákazníků</span>
        </div>
        <a class="btn hs-insurance-add" href="index.php?strana=NastaveniCiselnikPojistoven&amp;GETpojistovnaGUID=&amp;EditacePojitovny=1">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>
          Přidat pojišťovnu
        </a>
      </div>
      <div class="panel-body">
        <div class="hs-insurance-table-toolbar"></div>
        <div class="hs-insurance-table-scroll">
          <table id="hs_insurance_table" class="table hs-insurance-table" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th>Detail</th>
                <th>Jméno pojišťovny</th>
                <th>Kód pojišťovny</th>
                <th>Zkratka</th>
                <th>Stát</th>
                <th>Akce</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($insuranceRows as $row):
                $guid = isset($row['pojistovnaGUID']) ? (string)$row['pojistovnaGUID'] : '';
                $name = isset($row['jmenoPojistovny']) ? $row['jmenoPojistovny'] : '';
                $code = isset($row['kodPojistovny']) ? $row['kodPojistovny'] : '';
                $abbr = isset($row['zkratkaPojistovny']) ? $row['zkratkaPojistovny'] : '';
                $country = isset($row['statPojistovny']) ? $row['statPojistovny'] : '';
            ?>
              <tr data-insurance-name="<?php echo hsInsuranceEsc($name); ?>">
                <td class="hs-insurance-detail-cell">
                  <a class="hs-insurance-row-action hs-insurance-row-action--edit" href="index.php?strana=NastaveniCiselnikPojistoven&amp;GETpojistovnaGUID=<?php echo rawurlencode($guid); ?>&amp;EditacePojitovny=1" title="Editovat pojišťovnu" aria-label="Editovat pojišťovnu">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"></path></svg>
                  </a>
                </td>
                <td><?php echo hsInsuranceEsc($name); ?></td>
                <td><?php echo hsInsuranceEsc($code); ?></td>
                <td><?php echo hsInsuranceEsc($abbr); ?></td>
                <td><span class="hs-insurance-country"><?php echo hsInsuranceEsc($country); ?></span></td>
                <td class="hs-insurance-actions-cell">
                  <a class="hs-insurance-row-action hs-insurance-row-action--delete"
                     href="index.php?strana=NastaveniCiselnikPojistoven&amp;GETpojistovnaGUID=<?php echo rawurlencode($guid); ?>&amp;SmazaniPojistovnyZeSystemu=1"
                     onclick="return confirm('Opravdu chcete SMAZAT tuto pojišťovnu?');"
                     data-hs-confirm-title="Potvrdit smazání"
                     data-hs-confirm-action="Smazat"
                     data-hs-confirm-tone="delete"
                     title="Smazat pojišťovnu" aria-label="Smazat pojišťovnu">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="m19 6-1 15H6L5 6"></path><path d="M10 11v5M14 11v5"></path></svg>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="hs-insurance-mobile-cards" aria-live="polite"></div>
      </div>
    </section>
  </section>
</section>
