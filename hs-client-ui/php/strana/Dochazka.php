<?php
/* HairSoft V160: moderní Docházka. Datová a refresh logika zachována z původního Dochazka.php. */
if (!defined('HS_CLIENT_UI_ROOT')) { define('HS_CLIENT_UI_ROOT', dirname(__DIR__, 3)); }
require HS_CLIENT_UI_ROOT . '/str/strana/_zabezpeceni.php';

if ($_SESSION["JePoduzivatel"]=="1" and in_array("Uzivatele", $_SESSION["SeznamPravPoduzivatele"])=="0") {
  echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=PrazdnaStrana\">";
  exit;
}

require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';
require HS_CLIENT_UI_ROOT . '/fce/GeneratorBarev.php';

function hsDochazkaDaysInMonth($mesic, $rok) {
  $mesic = (int)$mesic; $rok = (int)$rok;
  if ($mesic < 1 || $mesic > 12 || $rok < 1900) return 0;
  return cal_days_in_month(CAL_GREGORIAN, $mesic, $rok);
}
function hsDochazkaInput($source, $key) {
  if (!isset($source[$key]) || is_array($source[$key])) return '';
  return htmlspecialchars($source[$key], ENT_COMPAT);
}
function hsDochazkaEsc($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

$jmenoStranky = 'Docházka';
$jmenoStrankyPopis = 'Přehled docházky';
$pobocka_jmeno = $_SESSION['pobocka_jmeno']=='' ? 'Nevybrána' : $_SESSION['pobocka_jmeno'];

$AkceDochazka = hsDochazkaInput($_POST, 'AkceDochazka');
$VybranaPobockaID = hsDochazkaInput($_POST, 'VybranaPobockaID');
$DochazkaMesic = hsDochazkaInput($_POST, 'DochazkaMesic');
$DochazkaRok = hsDochazkaInput($_POST, 'DochazkaRok');
$ZobrazitServisni = hsDochazkaInput($_GET, 'ZobrazitServisni');

if ($VybranaPobockaID !== '') {
  $_SESSION['VybranaPobockaID'] = $VybranaPobockaID;
}

$skupina_id = $_SESSION['skupina_id'];
$sw_id = $_SESSION['pobocka_id'];
if ($_SESSION['pobocka_id']=='') { $sw_id = 0; $skupina_id = 0; }

if ($AkceDochazka === 'FormDochazka') {
  $sql_existuje_data = "SELECT * FROM `Dochazka_refresh` WHERE `sw_id` = ".$_SESSION['pobocka_id'];
  $vysledek_existuje_data = $mysqli->query($sql_existuje_data);
  $radku_existuje_data = $vysledek_existuje_data ? $vysledek_existuje_data->num_rows : 0;
  if ($radku_existuje_data == 0) {
    $sql = "INSERT INTO `Dochazka_refresh` (`Dochazka_refresh`, `sw_id`, `strediskoHSID`, `DochazkaMesic`, `DochazkaRok`) VALUES (NULL, '".$_SESSION['pobocka_id']."', '".$VybranaPobockaID."', '".$DochazkaMesic."', '".$DochazkaRok."');";
    $vysledek_zalozeni = @$mysqli->query($sql);
    if (!$vysledek_zalozeni) { echo hsDochazkaEsc($mysqli->error); return; }
  }
}

$dochazka_mesic=''; $dochazka_rok='';
$select_na_refresh = "SELECT distinct Month, Year FROM `Dochazka` WHERE `sw_id` = $sw_id";
$vysledek_na_refresh = $mysqli->query($select_na_refresh);
if ($vysledek_na_refresh && $vysledek_na_refresh->num_rows) {
  $data_refresh = MySQLi_Fetch_Array($vysledek_na_refresh);
  $dochazka_mesic = $data_refresh['Month'];
  $dochazka_rok = $data_refresh['Year'];
}
$_SESSION['DochazkaTitle'] = $dochazka_mesic!=='' ? 'Docházka za: '.$dochazka_mesic.' / '.$dochazka_rok : 'Docházka';

$select_pending = "SELECT count(*) as 'POCET' FROM `Dochazka_refresh` WHERE `sw_id` = $sw_id";
$vysledek_pending = $mysqli->query($select_pending);
$data_pending = $vysledek_pending ? MySQLi_Fetch_Array($vysledek_pending) : array('POCET'=>0);
$lidi_hs_pocet_refresh = isset($data_pending['POCET']) ? (int)$data_pending['POCET'] : 0;
$DISABLED_ODESLANI_NOVEHO = $lidi_hs_pocet_refresh > 0 ? " disabled='disabled' " : '';

if ($ZobrazitServisni === '1') {
  $SQLServisni = ' ';
  $SQLServisniTlac = '0';
  $SQLServisniTlacJmeno = 'Zobrazit jen obsluhy';
  $hsServiceMode = 'all';
} else {
  $SQLServisni = ' and Virtual = 0 ';
  $SQLServisniTlac = '1';
  $SQLServisniTlacJmeno = 'Zobrazit i servisní obsluhy';
  $hsServiceMode = 'regular';
}

$strediska = array();
if ($_SESSION['k_email']!='') {
  $select_na_pobocku = "SELECT `HodnoceniStrediskaStredisko`,`HodnoceniStrediskaStrediskoJmeno` FROM `HodnoceniStrediska` WHERE `HodnoceniStrediskaValid` = 1 and `HodnoceniStrediskaPobocka` =".$_SESSION['pobocka_id'];
  $vysledek_na_pobocku = $mysqli->query($select_na_pobocku);
  if ($vysledek_na_pobocku) while ($row=MySQLi_Fetch_Array($vysledek_na_pobocku)) $strediska[]=$row;
}

$attendanceRows = array();
$select_na_dochazku = "SELECT * FROM `Dochazka` WHERE `sw_id` = ".$sw_id." $SQLServisni order by 1 asc";
$vysledek_na_dochazku = $mysqli->query($select_na_dochazku);
if ($vysledek_na_dochazku) while ($row=MySQLi_Fetch_Array($vysledek_na_dochazku)) $attendanceRows[]=$row;
$dayCount = hsDochazkaDaysInMonth($dochazka_mesic, $dochazka_rok);

$selectedCenter = isset($_SESSION['VybranaPobockaID']) ? (string)$_SESSION['VybranaPobockaID'] : '';
$selectedCenterName='';
foreach($strediska as $s) if ((string)$s['HodnoceniStrediskaStredisko']===$selectedCenter) $selectedCenterName=$s['HodnoceniStrediskaStrediskoJmeno'];
if ($selectedCenterName==='' && count($strediska)) $selectedCenterName=$strediska[0]['HodnoceniStrediskaStrediskoJmeno'];
$currentMonth=(int)date('n'); $currentYear=(int)date('Y');
?>
<section class="main-content-wrapper">
  <div class="pageheader">
    <h1><?php echo $jmenoStranky; ?></h1>
    <p class="description"><?php echo $jmenoStrankyPopis; ?></p>
    <div class="breadcrumb-wrapper hidden-xs">
      <span class="label">Pobočka:</span>
      <ol class="breadcrumb"><li class="active"><b><?php echo hsDochazkaEsc($pobocka_jmeno); ?></b> <?php echo JePobockaOnline($sw_id,$mysqli); ?></li></ol>
      <font color="#999" size="1">&nbsp;&nbsp;SW ID: <?php echo hsDochazkaEsc($sw_id); ?></font>
    </div>
  </div>
  <section id="main-content" class="hs-attendance-page" data-attendance-period="<?php echo hsDochazkaEsc(($dochazka_mesic!==''?$dochazka_mesic.'/'.$dochazka_rok:'')); ?>" data-attendance-center="<?php echo hsDochazkaEsc($selectedCenterName); ?>" data-attendance-service="<?php echo hsDochazkaEsc($hsServiceMode); ?>">

    <div class="hs-attendance-controls-grid">
      <section class="panel panel-default hs-attendance-card hs-attendance-filter-card">
        <div class="panel-heading">
          <span class="hs-attendance-heading-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 5h18M6 12h12M10 19h4"/></svg></span>
          <div><h3 class="panel-title">Výběr docházky</h3><span class="hs-attendance-heading-note">Načtení dat z HairSoft</span></div>
        </div>
        <div class="panel-body">
          <div class="hs-attendance-controls-row">
            <form action="index.php?strana=Dochazka" method="POST" class="hs-attendance-filter-form">
              <input type="hidden" name="AkceDochazka" value="FormDochazka">
              <label class="hs-attendance-field"><span>Středisko</span>
                <select <?php echo $DISABLED_ODESLANI_NOVEHO; ?> name="VybranaPobockaID" id="VybranaPobockaID" class="form-control input-lg">
                  <?php foreach($strediska as $s): $sid=(string)$s['HodnoceniStrediskaStredisko']; ?>
                    <option value="<?php echo hsDochazkaEsc($sid); ?>" <?php echo ($selectedCenter===$sid?'selected':''); ?>><?php echo hsDochazkaEsc($s['HodnoceniStrediskaStrediskoJmeno']); ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label class="hs-attendance-field hs-attendance-field--small"><span>Měsíc</span>
                <select <?php echo $DISABLED_ODESLANI_NOVEHO; ?> name="DochazkaMesic" id="DochazkaMesic" class="form-control input-lg">
                  <?php for($m=1;$m<=12;$m++): ?><option value="<?php echo $m; ?>" <?php echo ($m===$currentMonth?'selected':''); ?>><?php echo str_pad((string)$m,2,'0',STR_PAD_LEFT); ?></option><?php endfor; ?>
                </select>
              </label>
              <label class="hs-attendance-field hs-attendance-field--small"><span>Rok</span>
                <select <?php echo $DISABLED_ODESLANI_NOVEHO; ?> name="DochazkaRok" id="DochazkaRok" class="form-control input-lg">
                  <?php for($i=0;$i<4;$i++): $year=$currentYear-$i; ?><option value="<?php echo $year; ?>"><?php echo $year; ?></option><?php endfor; ?>
                </select>
              </label>
              <div class="hs-attendance-action-field hs-attendance-filter-submit">
                <span class="hs-attendance-action-label" aria-hidden="true">&nbsp;</span>
                <button <?php echo $DISABLED_ODESLANI_NOVEHO; ?> type="submit" class="btn btn-primary"><span aria-hidden="true">↻</span> Refresh z PC</button>
              </div>
            </form>
            <form action="index.php?strana=Dochazka&amp;ZobrazitServisni=<?php echo hsDochazkaEsc($SQLServisniTlac); ?>" method="POST" class="hs-attendance-service-form">
              <div class="hs-attendance-action-field">
                <span class="hs-attendance-action-label" aria-hidden="true">&nbsp;</span>
                <button <?php echo $DISABLED_ODESLANI_NOVEHO; ?> type="submit" class="btn btn-success hs-attendance-service-btn"><span aria-hidden="true">↻</span> <?php echo hsDochazkaEsc($SQLServisniTlacJmeno); ?></button>
              </div>
            </form>
          </div>
          <div class="hs-attendance-filter-meta">
            <span>Načtená data</span><strong><?php echo $dochazka_mesic!=='' ? hsDochazkaEsc(str_pad((string)$dochazka_mesic,2,'0',STR_PAD_LEFT).'/'.$dochazka_rok) : '—'; ?></strong>
            <span class="hs-attendance-meta-separator" aria-hidden="true"></span>
            <span><?php echo $ZobrazitServisni==='1' ? 'Zobrazeny jsou běžné i servisní obsluhy.' : 'Servisní obsluhy jsou skryté.'; ?></span>
          </div>
          <?php if($lidi_hs_pocet_refresh>0): ?>
            <div class="hs-attendance-notice"><span class="hs-attendance-notice__dot"></span><span>Váš požadavek na refresh dat pro tento měsíc a rok byl zadán</span></div>
          <?php endif; ?>
        </div>
      </section>
    </div>

    <section class="panel panel-default hs-attendance-table-panel">
      <div class="panel-heading">
        <span class="hs-attendance-heading-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
        <div><h3 class="panel-title"><span>Docházka za:</span> <?php echo $dochazka_mesic!=='' ? hsDochazkaEsc(str_pad((string)$dochazka_mesic,2,'0',STR_PAD_LEFT).'/'.$dochazka_rok) : '—'; ?></h3><span class="hs-attendance-heading-note">Přehled odpracovaného času podle dnů a obsluh</span></div>
      </div>
      <div class="panel-body">
        <div class="hs-attendance-toolbar" aria-label="Nástroje tabulky"></div>
        <div class="hs-attendance-table-scroll">
          <table id="dochazka_tabulka" class="table hs-attendance-table" cellspacing="0" width="100%" style="min-width:<?php echo max(760, 120 + count($attendanceRows)*170); ?>px">
            <thead><tr><th>Den</th><?php foreach($attendanceRows as $row): ?><th><?php echo hsDochazkaEsc(trim($row['Surname'].' '.$row['Name'])); ?></th><?php endforeach; ?></tr></thead>
            <tbody>
              <?php if($dayCount>0): for($day=1;$day<=$dayCount;$day++): ?>
                <tr><td><?php echo $day; ?>.</td><?php foreach($attendanceRows as $row): $key='Den'.$day; ?><td><?php echo hsDochazkaEsc(isset($row[$key])?$row[$key]:''); ?></td><?php endforeach; ?></tr>
              <?php endfor; endif; ?>
            </tbody>
            <?php if(count($attendanceRows)): ?><tfoot><tr><th>Celkem</th><?php foreach($attendanceRows as $row): ?><th><?php echo hsDochazkaEsc($row['TotalTime']); ?></th><?php endforeach; ?></tr></tfoot><?php endif; ?>
          </table>
        </div>
        <div class="hs-attendance-mobile-cards" aria-live="polite"></div>
        <?php if(!count($attendanceRows) || !$dayCount): ?><div class="hs-attendance-empty">Pro vybrané období nejsou k dispozici žádné záznamy.</div><?php endif; ?>
      </div>
    </section>
  </section>
</section>
