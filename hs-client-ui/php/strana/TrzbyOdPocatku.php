<?php
$hs_lifetime_started = microtime(true);
// Modern lifetime revenue page. Keep authentication at the original location.
if (!defined('HS_CLIENT_UI_ROOT')) { define('HS_CLIENT_UI_ROOT', dirname(__DIR__, 3)); }
require HS_CLIENT_UI_ROOT . '/str/strana/_zabezpeceni.php';
require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';
if (($_SESSION['JePoduzivatel'] ?? 0) == 1 && !in_array('Trzby', $_SESSION['SeznamPravPoduzivatele'] ?? array(), true)) {
    echo '<meta http-equiv="refresh" content="0;URL=index.php?strana=PrazdnaStrana">';
    return;
}
$sw_id = (int) ($_SESSION['pobocka_id'] ?? 0);
if (($_SESSION['k_id'] ?? 0) == 10) { $sw_id = 0; }
$hs_currency = (string) ($_SESSION['Mena_Klienta'] ?? 'Kč');
$hs_branch = (string) ($_SESSION['pobocka_jmeno'] ?? '');
if ($hs_branch === '') { $hs_branch = 'Nevybrána'; }
$hs_escape = static function($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
$hs_money = static function($n) { return number_format((float)$n, 2, ',', ' '); };
$hs_metrics = array('total','totalNet','services','servicesNet','sales','salesNet','vouchers','vouchersNet','credit','creditNet');
$hs_zero = array_fill_keys($hs_metrics, 0.0);
$hs_centers = array(); $hs_staff = array(); $hs_years = array(); $hs_last_sync = ''; $hs_data_error = false;
$hs_query = static function($sql) use ($mysqli) { $r=$mysqli->query($sql); if (!$r) { throw new RuntimeException('Lifetime revenue query failed'); } return $r; };
$hs_sum_into = static function(&$target, $row) use ($hs_metrics) { foreach ($hs_metrics as $key) { $target[$key] += (float)($row[$key] ?? 0); } };
try {
    // Discover the connected database, independent of its deployment name.
    $hs_tables = $hs_query("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = DATABASE() AND (TABLE_NAME LIKE 'trzby_strediska_%' OR TABLE_NAME LIKE 'trzby_jednotlivci_%') ORDER BY TABLE_NAME");
    while ($hs_table = $hs_tables->fetch_assoc()) {
        $hs_name = $hs_table['TABLE_NAME'];
        if (!preg_match('/^trzby_(strediska|jednotlivci)_([0-9]{4})$/D', $hs_name, $hs_match)) { continue; }
        $hs_year = (int)$hs_match[2]; $hs_is_staff = $hs_match[1] === 'jednotlivci'; $hs_prefix = $hs_is_staff ? 'tj_' : 'ts_';
        if (!isset($hs_years[$hs_year])) { $hs_years[$hs_year] = $hs_zero + array('receipts'=>0, 'hasData'=>false); }
        $hs_columns = array('TrzbyCelkem','TrzbyCelkemBezDPH','ZaSluzby','ZaSluzbyBezDPH','ZaProdej','ZaProdejBezDPH','Celkem_Voucher','Celkem_Voucher_bezDPH','Celkem_Credit','Celkem_Credit_bezDPH');
        $hs_select = array();
        foreach ($hs_columns as $hs_i=>$hs_column) { $hs_db_column = $hs_i < 6 ? $hs_prefix.$hs_column : $hs_column; $hs_select[] = 'COALESCE(SUM(`'.$hs_db_column.'`),0) AS `'.$hs_metrics[$hs_i].'`'; }
        $hs_identity = '`'.$hs_prefix.'NazevStrediska` AS center';
        $hs_group = '`'.$hs_prefix.'NazevStrediska`';
        if ($hs_is_staff) { $hs_identity .= ', `tj_NazevJednotlivce` AS staff'; $hs_group .= ', `tj_NazevJednotlivce`'; }
        else { $hs_select[] = 'COALESCE(SUM(`ts_uctenek`),0) AS receipts'; }
        $hs_select[] = 'MAX(`'.$hs_prefix.'Datum`) AS lastDate';
        $hs_result = $hs_query('SELECT '.$hs_identity.', '.implode(', ', $hs_select).' FROM `'.$hs_name.'` WHERE `sw_id` = '.$sw_id.' GROUP BY '.$hs_group.' ORDER BY '.$hs_group);
        while ($hs_row = $hs_result->fetch_assoc()) {
            $hs_center = (string)$hs_row['center'];
            if ($hs_is_staff) {
                $hs_staff_name = (string)$hs_row['staff']; $hs_key = json_encode(array($hs_center,$hs_staff_name));
                if (!isset($hs_staff[$hs_key])) { $hs_staff[$hs_key] = $hs_zero + array('center'=>$hs_center,'staff'=>$hs_staff_name); }
                $hs_sum_into($hs_staff[$hs_key],$hs_row);
            } else {
                if (!isset($hs_centers[$hs_center])) { $hs_centers[$hs_center] = $hs_zero + array('center'=>$hs_center); }
                $hs_sum_into($hs_centers[$hs_center],$hs_row); $hs_sum_into($hs_years[$hs_year],$hs_row);
                $hs_years[$hs_year]['receipts'] += (int)($hs_row['receipts'] ?? 0);
            }
            $hs_years[$hs_year]['hasData'] = true;
            if (!empty($hs_row['lastDate']) && $hs_row['lastDate'] > $hs_last_sync) { $hs_last_sync = $hs_row['lastDate']; }
        }
    }
} catch (Throwable $hs_error) { error_log('HairSoft lifetime revenue: '.$hs_error->getMessage()); $hs_data_error = true; }
ksort($hs_years, SORT_NUMERIC); ksort($hs_centers, SORT_NATURAL | SORT_FLAG_CASE);
uasort($hs_staff, static function($a,$b){return strnatcasecmp($a['center'].' '.$a['staff'],$b['center'].' '.$b['staff']);});
$hs_active_years = array_keys(array_filter($hs_years,static function($r){return $r['hasData'];}));
// Keep gaps inside the actual history as zero years; do not extend to unused future tables.
if ($hs_active_years) {
    $hs_first=min($hs_active_years); $hs_last=max($hs_active_years);
    $hs_years=array_intersect_key($hs_years,array_flip(range($hs_first,$hs_last)));
    for($hs_y=$hs_first;$hs_y<=$hs_last;$hs_y++){if(!isset($hs_years[$hs_y]))$hs_years[$hs_y]=$hs_zero+array('receipts'=>0,'hasData'=>false);}
    ksort($hs_years,SORT_NUMERIC);
} else { $hs_years=array(); }
$hs_best = static function($rows,$metric) { $best=null;foreach($rows as $key=>$r){if($best===null || $r[$metric]>$best['value'])$best=array('key'=>$key,'row'=>$r,'value'=>$r[$metric]);}return $best; };
$hs_best_services=$hs_best($hs_staff,'services');$hs_best_sales=$hs_best($hs_staff,'sales');
$hs_best_year_services=$hs_best(array_filter($hs_years,static function($r){return $r['hasData'];}),'services');
$hs_best_year_sales=$hs_best(array_filter($hs_years,static function($r){return $r['hasData'];}),'sales');
$hs_period=$hs_active_years ? min($hs_active_years).'–'.max($hs_active_years) : 'Celé období';
$hs_actions='<div class="actions pull-right"><i class="fa fa-expand"></i><i class="fa fa-chevron-down"></i><i class="fa fa-times"></i></div>';
$hs_heading=static function($title) use($hs_escape,$hs_actions){echo '<div class="panel-heading"><h3 class="panel-title">'.$hs_escape($title).'</h3>'.$hs_actions.'</div>';};
$hs_table=static function($id,$title,$rows,$individual) use($hs_escape,$hs_heading,$hs_money,$hs_metrics,$hs_zero,$hs_sum_into,$hs_currency){
    echo '<div class="row hs-lifetime-table-row"><div class="col-md-12"><section class="panel panel-default">';$hs_heading($title);echo '<div class="panel-body"><div class="table-responsive">';
    if(!$rows){echo '<p class="hs-lifetime-empty">Pro celé období nejsou k dispozici žádné záznamy.</p>';}
    else{
        echo '<table id="'.$id.'" class="table table-bordered table-striped"><thead><tr><th>#</th><th>Středisko | firmy</th>';
        if($individual)echo '<th>Jméno obsluhy</th>';
        foreach(array('Tržby celkem','Za služby','Za prodej','Ceniny','Kredit') as $label)echo '<th>'.$label.'</th><th>bez DPH</th>';
        echo '</tr></thead><tbody>';$sum=$hs_zero;$index=0;
        foreach($rows as $row){$hs_sum_into($sum,$row);echo '<tr><td>'.(++$index).'</td><td>'.$hs_escape($row['center']).'</td>';if($individual)echo '<td>'.$hs_escape($row['staff']).'</td>';foreach($hs_metrics as $metric)echo '<td>'.$hs_money($row[$metric]).'</td>';echo '</tr>';}
        echo '<tr><td></td><td>Celkem ('.$hs_escape($hs_currency).'):</td>';if($individual)echo '<td></td>';foreach($hs_metrics as $metric)echo '<td>'.$hs_money($sum[$metric]).'</td>';echo '</tr></tbody></table>';
    }
    echo '</div></div></section></div></div>';
};
$hs_delta=static function($current,$previous){return $previous===null || $previous==0 ? null : ($current-$previous)/abs($previous)*100;};
?>
<section class="main-content-wrapper hs-lifetime-revenue-page">
<div class="pageheader"><h1>Tržby od počátku věků</h1><p class="description">Přehled tržeb za celé období</p><div class="breadcrumb-wrapper hidden-xs"><span class="label">Pobočka:</span><ol class="breadcrumb"><li class="active"><b><?= $hs_escape($hs_branch) ?></b><?= JePobockaOnline($sw_id,$mysqli) ?></li></ol></div></div>
<section id="main-content" class="hs-lifetime-revenue">
<?php if($hs_data_error){ ?><div class="alert alert-danger">Tržby se nepodařilo načíst. Zkuste stránku znovu načíst.</div></section></section><?php return; } ?>
<div class="row">
<?php foreach(array(
array('TOP obsluha za služby',$hs_best_services ? $hs_best_services['row']['staff'] : '—','panel-solid-success'),
array('TOP obsluha za prodej',$hs_best_sales ? $hs_best_sales['row']['staff'] : '—',''),
array('TOP rok za služby'.($hs_best_year_services?' '.$hs_best_year_services['key']:''),$hs_money($hs_best_year_services['value']??0).' '.$hs_currency,'panel-solid-danger'),
array('TOP rok za prodej'.($hs_best_year_sales?' '.$hs_best_year_sales['key']:''),$hs_money($hs_best_year_sales['value']??0).' '.$hs_currency,'')
) as $hs_kpi){ ?><div class="col-md-3"><div class="panel widget-mini <?= $hs_kpi[2] ?>"><div class="panel-body"><i class="icon-bar-chart"></i><span class="total text-center"><?= $hs_escape($hs_kpi[1]) ?></span><span class="title text-center"><?= $hs_escape($hs_kpi[0]) ?></span></div></div></div><?php } ?>
</div>
<?php $hs_table('table_CelkovySumarZaStrediska','Celkový sumář za střediska | firmy',$hs_centers,false);$hs_table('table_CelkoveTrzbyZaObsluhu','Celkové tržby za obsluhu',$hs_staff,true); ?>
<div class="row"><div class="col-md-12"><section class="panel panel-default"><?php $hs_heading('Tržby v jednotlivých letech'); ?><div class="panel-body"><canvas id="canvas"></canvas></div></section></div></div>
<div class="row hs-lifetime-table-row"><div class="col-md-8"><section class="panel panel-default"><?php $hs_heading('Meziroční vývoj tržeb'); ?><div class="panel-body"><div class="table-responsive">
<table id="table_PrehledZiskovostVhednotlivychLetech" class="table table-bordered table-striped"><thead><tr><th>Rok</th><th>Služby</th><th>Změna služeb</th><th>Prodej</th><th>Změna prodeje</th></tr></thead><tbody>
<?php $hs_previous=null;$hs_comparison=array();foreach($hs_years as $hs_y=>$hs_row){$hs_comparison[$hs_y]=array($hs_delta($hs_row['services'],$hs_previous['services']??null),$hs_delta($hs_row['sales'],$hs_previous['sales']??null));$hs_previous=$hs_row;}
foreach(array_reverse($hs_years,true) as $hs_y=>$hs_row){ ?><tr><td><?= $hs_y ?></td><td><?= $hs_money($hs_row['services']).' '.$hs_escape($hs_currency) ?></td><td><?= $hs_comparison[$hs_y][0]===null ? '—' : $hs_money($hs_comparison[$hs_y][0]).' %' ?></td><td><?= $hs_money($hs_row['sales']).' '.$hs_escape($hs_currency) ?></td><td><?= $hs_comparison[$hs_y][1]===null ? '—' : $hs_money($hs_comparison[$hs_y][1]).' %' ?></td></tr><?php } ?>
</tbody></table></div><p class="hs-lifetime-comparison-note">Změna vůči předchozímu roku; při nulovém základu se procento neuvádí.</p></div></section></div>
<div class="col-md-4"><section class="panel panel-default"><?php $hs_heading('Účtenky za jednotlivé roky'); ?><div class="panel-body"><div class="row"><div class="col-lg-5"><table><?php foreach($hs_years as $hs_y=>$hs_row){ ?><tr><td><div></div></td><td><?= $hs_y ?></td><td><?= $hs_row['receipts'] ?></td></tr><?php } ?></table></div><div class="col-lg-7"><canvas id="chart-area1"></canvas></div></div></div></section></div></div>
<div class="row"><?php foreach(array('canvas2'=>'Služby v jednotlivých letech','canvas3'=>'Prodej v jednotlivých letech') as $hs_id=>$hs_title){ ?><div class="col-md-6"><section class="panel panel-default"><?php $hs_heading($hs_title); ?><div class="panel-body"><canvas id="<?= $hs_id ?>"></canvas></div></section></div><?php } ?></div>
<?php if($sw_id!==0){ ?><div class="row hs-lifetime-options-row"><div class="col-md-12"><section class="panel panel-default hs-lifetime-card hs-lifetime-options-card"><?php $hs_heading('Správa dat'); ?><div class="panel-body"><form class="hs-lifetime-action-form" action="../str/akce/TrzbyOdPocatku.php" method="POST"><input type="hidden" name="sw_id" value="<?= $sw_id ?>"><input type="hidden" name="DeleteCelkemData" value="1"><input type="hidden" name="DeleteCelkem" value="<?= (int)($_SESSION['SQL_ROK']??date('Y')) ?>"><button class="btn btn-danger hs-lifetime-action hs-lifetime-action--delete" type="submit" onclick="return confirm('Opravdu chcete SMAZAT kompletní data vašich tržeb?')">Smazat kompletní data vašich tržeb</button></form></div></section></div></div><?php } ?>
<?php $hs_load_seconds = max(0, microtime(true) - (isset($DURATION_start) && is_numeric($DURATION_start) ? (float)$DURATION_start : $hs_lifetime_started)); ?>
<footer class="hs-lifetime-status">
<div class="hs-lifetime-status__item"><span class="hs-lifetime-status__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16M3 22v-6h6M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8M15 8h6V2"></path></svg></span><div><span class="hs-lifetime-status__label">Synchronizováno:</span><strong><?= $hs_last_sync ? $hs_escape(date('d.m.Y H:i',strtotime($hs_last_sync))) : '—' ?></strong></div></div>
<div class="hs-lifetime-status__item"><span class="hs-lifetime-status__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v4l3 2"></path></svg></span><div><span class="hs-lifetime-status__label">Automatické načítání dat</span><strong>Každých 15 minut</strong></div></div>
<div class="hs-lifetime-status__item"><span class="hs-lifetime-status__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 17a8 8 0 1 1 14 0M12 13l4-5"></path></svg></span><div><span class="hs-lifetime-status__label">Strana načtena za</span><strong><?= number_format($hs_load_seconds,3,',',' ') ?> s</strong></div></div>
</footer>
</section></section>
<noscript><style>.hs-lifetime-table-row{visibility:visible!important}</style></noscript>
<?php
$hs_labels=array_map('strval',array_keys($hs_years));if(!$hs_labels)$hs_labels=array('Celé období');
$hs_values=static function($key) use($hs_years){return $hs_years?array_values(array_map(static function($r)use($key){return $r[$key];},$hs_years)):array(0);};
$hs_bar=static function($series)use($hs_labels,$hs_values){$datasets=array();foreach($series as $key=>$label)$datasets[]=array('label'=>$label,'data'=>$hs_values($key));return array('type'=>'bar','data'=>array('labels'=>$hs_labels,'datasets'=>$datasets));};
$hs_configs=array('canvas'=>$hs_bar(array('services'=>'Služby','sales'=>'Prodej','vouchers'=>'Ceniny','credit'=>'Kredit')),'canvas2'=>$hs_bar(array('services'=>'Služby')),'canvas3'=>$hs_bar(array('sales'=>'Prodej')),'chart-area1'=>array('type'=>'doughnut','data'=>array('labels'=>$hs_years?$hs_labels:array(),'datasets'=>array(array('data'=>$hs_years?$hs_values('receipts'):array()))),'options'=>array('animation'=>array('duration'=>0))));
$hs_json_flags=JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE;
?>
<script>window.hsLifetimeBranch=<?= json_encode($hs_branch,$hs_json_flags) ?>;window.hsLifetimeCurrency=<?= json_encode($hs_currency,$hs_json_flags) ?>;window.hsLifetimePeriod=<?= json_encode($hs_period,$hs_json_flags) ?>;window.hsLifetimeConfigs=<?= json_encode($hs_configs,$hs_json_flags) ?>;</script>

