<?php
/* MIGRACE PHP 5.5 -> 8.3
   Duvod:
   Vychozi flags htmlspecialchars() se v PHP 8 zmenily.
   Explicitni ENT_COMPAT zachovava stejny vysledek jako PHP 5.5,
   zejmena puvodni zachazeni s apostrofem.

   STARY KOD PHP 5.5:
   htmlspecialchars($hodnota)
*/

/* MIGRACE PHP 5.5 -> 8.3
   Duvod:
   Skalarni GET/POST vstupy mohou byt podvrzeny jako pole. PHP 8.3
   pak ve stringovych funkcich vyvola TypeError. Kontrola is_array()
   ponechava platne hodnoty beze zmeny a neplatne pole nahrazuje
   stejnym prazdnym stringem jako chybejici parametr.

   STARY KOD PHP 5.5:
   if (!isset($_POST['klic'])) { $_POST['klic'] = ''; }
   if (!isset($_GET['klic'])) { $_GET['klic'] = ''; }
*/

  require HS_CLIENT_UI_ROOT . '/str/strana/_zabezpeceni.php';

  /* MIGRACE PHP 5.5 -> 8.3
     Duvod:
     Tato pole vznikala pouze pri nalezeni databazovych radku. PHP 8.3 vyvola
     TypeError pri count() nebo array_sum() nad nedefinovanou hodnotou.
     Prazdna pole zachovavaji puvodni vysledek nula a prazdny vystup.

     STARY KOD PHP 5.5:
     Pole nebyla pred databazovymi smyckami explicitne inicializovana.
  */
  if (!isset($JmenoObsluhy) || !is_array($JmenoObsluhy)) { $JmenoObsluhy = array(); }
  if (!isset($JmenoObsluhyUctenky) || !is_array($JmenoObsluhyUctenky)) { $JmenoObsluhyUctenky = array(); }
  if (!isset($SluzbyMesic) || !is_array($SluzbyMesic)) { $SluzbyMesic = array(); }
  if (!isset($ProdejMesic) || !is_array($ProdejMesic)) { $ProdejMesic = array(); }
  if (!isset($CeninyMesic) || !is_array($CeninyMesic)) { $CeninyMesic = array(); }
  if (!isset($KreditMesic) || !is_array($KreditMesic)) { $KreditMesic = array(); }
  if (!isset($SluzbyObsluhy) || !is_array($SluzbyObsluhy)) { $SluzbyObsluhy = array(); }
  if (!isset($ProdejObsluhy) || !is_array($ProdejObsluhy)) { $ProdejObsluhy = array(); }

  // Prava na stranku aby neslo podstrcit stranku s GET URL v oblibenych
  if ($_SESSION["JePoduzivatel"]=="1" and in_array("Trzby", $_SESSION["SeznamPravPoduzivatele"])=="0") {
    echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=PrazdnaStrana\">";
    exit;
  }
  // Prava na stranku
  

  require HS_CLIENT_UI_ROOT . '/fce/GeneratorBarev.php';
  require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';
  require HS_CLIENT_UI_ROOT . '/fce/DemoData.php';

  //require_once '../../cfg/nastaveni.php';
  
  
  $jmenoStranky = "Měsíční tržby";
  $jmenoStrankyPopis = "Přehled Vašich měsíčních tržeb za pobočku: <B>".$_SESSION["pobocka_jmeno"]."</B>";
  
  if ($_SESSION["pobocka_jmeno"]=="") {
    $pobocka_jmeno =  "Nevybrána" ;
  }else {
    $pobocka_jmeno =  $_SESSION["pobocka_jmeno"] ;  
  }

        //AKCE
        if (!isset($_POST['datum']) || is_array($_POST['datum'])){$_POST['datum']='';}
        $datum  =  htmlspecialchars($_POST['datum'], ENT_COMPAT);
        
        if ($datum=="") {
          $datum = date("d.m.Y"); 
        }
        
       
      
        // $datum ="2016-10-20";
        $sw_id = $_SESSION["pobocka_id"]; 
        //$sw_id = 1081;
       
        //Mena
        $Mena_Klienta = $_SESSION["Mena_Klienta"];

        //////////////////////////////////////////////////////////////////////////////////////
        //DEMO
        if ($_SESSION["pobocka_id"]=="") {
          $sw_id = 0;
          $Mena_Klienta = "Kč";
        }
        //////////////////////////////////////////////////////////////////////////////////////
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////DEBUG/////////////////////////////////////////////////////////////////
  if ($_SESSION["k_id"]=="10") {
     //$sw_id = 1999;    //BS
     //$Uzivatel_ID =11;
     
     //$sw_id = 2346;    //BS
     //$Uzivatel_ID =11;

  }
  //$Uzivatel_ID = 11; 
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        
        

        
        if (!isset($_GET['rok_zmena']) || is_array($_GET['rok_zmena'])){$_GET['rok_zmena']='';}
        $rok_zmena  =  htmlspecialchars($_GET['rok_zmena'], ENT_COMPAT);
        
        if (!isset($_GET['mesic']) || is_array($_GET['mesic'])){$_GET['mesic']='';}
        $mesic  =  htmlspecialchars($_GET['mesic'], ENT_COMPAT);
        
        if (!isset($_GET['RefreshMesic']) || is_array($_GET['RefreshMesic'])){$_GET['RefreshMesic']='';}
        $RefreshMesic  =  htmlspecialchars($_GET['RefreshMesic'], ENT_COMPAT);
        
               
        $aMesice = array('Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec');
  
        // V108: navigation selects an absolute period; reloading never applies a delta.
        $hs_month = isset($_SESSION['SQL_Mesic']) ? (int) $_SESSION['SQL_Mesic'] : (int) date('n');
        $hs_year = isset($_SESSION['SQL_ROK']) ? (int) $_SESSION['SQL_ROK'] : (int) date('Y');
        if ($hs_month < 1 || $hs_month > 12) { $hs_month = (int) date('n'); }
        if ($hs_year < 1900 || $hs_year > 9999) { $hs_year = (int) date('Y'); }
        $NavratovyMesic = isset($_GET['NavratovyMesic']) && !is_array($_GET['NavratovyMesic'])
            ? filter_var($_GET['NavratovyMesic'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 12))) : false;
        $NavratovyRok = isset($_GET['NavratovyRok']) && !is_array($_GET['NavratovyRok'])
            ? filter_var($_GET['NavratovyRok'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1900, 'max_range' => 9999))) : false;
        if ($NavratovyMesic !== false) { $hs_month = $NavratovyMesic; }
        if ($NavratovyRok !== false && $NavratovyRok !== $hs_year) {
            // Preserve the existing availability check for yearly revenue tables.
            $TestTabulka = 'trzby_strediska_' . $NavratovyRok;
            $TestResult = $mysqli->query("SHOW TABLES LIKE '$TestTabulka'");
            if ($TestResult && $TestResult->num_rows > 0) { $hs_year = $NavratovyRok; }
        }
        $aktualniMesic = $_SESSION['SQL_Mesic'] = $hs_month;
        $SQL_ROK = $_SESSION['SQL_ROK'] = $hs_year;
        $hs_period_links = array(
            'month-prev' => 'index.php?strana=MesicniTrzby&NavratovyMesic=' . ($hs_month === 1 ? 12 : $hs_month - 1) . '&NavratovyRok=' . $hs_year,
            'month-next' => 'index.php?strana=MesicniTrzby&NavratovyMesic=' . ($hs_month === 12 ? 1 : $hs_month + 1) . '&NavratovyRok=' . $hs_year,
            'year-prev' => 'index.php?strana=MesicniTrzby&NavratovyMesic=' . $hs_month . '&NavratovyRok=' . ($hs_year - 1),
            'year-next' => 'index.php?strana=MesicniTrzby&NavratovyMesic=' . $hs_month . '&NavratovyRok=' . ($hs_year + 1)
        );
        // Pin this history entry to its period, including visits from old relative links.
        ?>
        
        <?php

?>

<section class="main-content-wrapper">
        <!-- Keep the content section adjacent to the sidebar for the existing layout CSS. -->
        <script>
        (function () {
            var url = new URL(window.location.href);
            url.searchParams.delete('mesic');
            url.searchParams.delete('rok_zmena');
            url.searchParams.set('NavratovyMesic', <?php echo (int) $hs_month; ?>);
            url.searchParams.set('NavratovyRok', <?php echo (int) $hs_year; ?>);
            window.history.replaceState(window.history.state, '', url.href);
        }());
        </script>            
  <div class="pageheader">                
    <h1><?php echo $jmenoStranky; ?></h1>                
    <p class="description">
      <?php echo $jmenoStrankyPopis; ?>
    </p>                
    <div class="breadcrumb-wrapper hidden-xs">                    
      <span class="label">Pobočka:</span>                    
      <ol class="breadcrumb">                        
        <li class="active"><b><?php echo $pobocka_jmeno; ?></b><?php echo JePobockaOnline($sw_id,$mysqli); ?></li> 
      </ol>
       
     
                    
    </div>            
  </div>            
  <section id="main-content" class="hs-monthly-revenue">                
  
                      <?php
                        //Top obsluha sluzby
                        $sql_TopObsluhaSluzby= "SELECT concat (`tj_NazevStrediska` , `tj_NazevJednotlivce`) as Jmeno, `tj_NazevJednotlivce`, SUM(`tj_TrzbyCelkem`) as 'TrzbyCelkem' ,SUM(`tj_ZaSluzby`) as 'ZaSluzby',SUM(`tj_ZaProdej`) as 'ZaProdej' FROM `trzby_jednotlivci_$SQL_ROK` WHERE `sw_id` = $sw_id and MONTH(`tj_Datum`) = '$aktualniMesic' and YEAR(`tj_Datum`) = '$SQL_ROK' group by Jmeno order by ZaSluzby Desc LIMIT 1";
                        $vysledek_sql_TopObsluhaSluzby=$mysqli->query($sql_TopObsluhaSluzby);
                        $data_sql_TopObsluhaSluzby=MySQLi_Fetch_Array($vysledek_sql_TopObsluhaSluzby);
                        //$CelkoveTrzby = number_format($data_sql_TopObsluhaSluzby["TrzbyCelkem"], 2, '.', '&nbsp;');
                        $TopObsluhaSluzby = $data_sql_TopObsluhaSluzby["tj_NazevJednotlivce"];
                        
                        if ($TopObsluhaSluzby=="") {
                          $TopObsluhaSluzby = " - ";
                        }
                                                                                              
                        
                        //Top obsluha prodej
                        $sql_TopObsluhaProdej= "SELECT concat (`tj_NazevStrediska` , `tj_NazevJednotlivce`) as Jmeno, `tj_NazevJednotlivce`, SUM(`tj_TrzbyCelkem`) as 'TrzbyCelkem' ,SUM(`tj_ZaSluzby`) as 'ZaSluzby',SUM(`tj_ZaProdej`) as 'ZaProdej' FROM `trzby_jednotlivci_$SQL_ROK` WHERE `sw_id` = $sw_id and MONTH(`tj_Datum`) = '$aktualniMesic' and YEAR(`tj_Datum`) = '$SQL_ROK' group by Jmeno order by ZaProdej Desc LIMIT 1";
                        $vysledek_sql_TopObsluhaProdej=$mysqli->query($sql_TopObsluhaProdej);
                        $data_sql_TopObsluhaProdej=MySQLi_Fetch_Array($vysledek_sql_TopObsluhaProdej);
                        //$CelkoveTrzby = number_format($data_sql_TopObsluhaProdej["TrzbyCelkem"], 2, '.', '&nbsp;');
                        $TopObsluhaProdej = $data_sql_TopObsluhaProdej["tj_NazevJednotlivce"];
                      
                        if ($TopObsluhaProdej=="") {
                          $TopObsluhaProdej = " - ";
                        }
                        
                      
                        //Top obsluha sluzby
                        $sql_TopDenSluzby= "SELECT `tj_Datum` ,SUM(`tj_ZaSluzby`) as 'ZaSluzby' FROM `trzby_jednotlivci_$SQL_ROK` WHERE `sw_id` = $sw_id and MONTH(`tj_Datum`) = '$aktualniMesic' and YEAR(`tj_Datum`) = '$SQL_ROK' group by DATE(`tj_Datum` ) order by `ZaSluzby` DESC LIMIT 1";
                        $vysledek_sql_TopDenSluzby=$mysqli->query($sql_TopDenSluzby);
                        $data_sql_TopDenSluzby=MySQLi_Fetch_Array($vysledek_sql_TopDenSluzby);
                        //$CelkoveTrzby = number_format($data_sql_TopDenSluzby["TrzbyCelkem"], 2, '.', '&nbsp;');
                        $TopDenSluzbyDatum =date("d.m.", strtotime($data_sql_TopDenSluzby["tj_Datum"])); 
                        $TopDenSluzbySuma = number_format($data_sql_TopDenSluzby["ZaSluzby"], 2, '.', '&nbsp;');
                        $TopDenSluzbyPopisek = $TopDenSluzbyDatum . " - " .$TopDenSluzbySuma; 
                        
                        //Top obsluha sluzby
                        $sql_TopDenProdej= "SELECT `tj_Datum` ,SUM(`tj_ZaSluzby`) as 'ZaSluzby',SUM(`tj_ZaProdej`) as 'ZaProdej' FROM `trzby_jednotlivci_$SQL_ROK` WHERE `sw_id` = $sw_id and MONTH(`tj_Datum`) = '$aktualniMesic' and YEAR(`tj_Datum`) = '$SQL_ROK' group by DATE(`tj_Datum` ) order by `ZaProdej` DESC LIMIT 1";
                        $vysledek_sql_TopDenProdej=$mysqli->query($sql_TopDenProdej);
                        $data_sql_TopDenProdej=MySQLi_Fetch_Array($vysledek_sql_TopDenProdej);
                        //$CelkoveTrzby = number_format($data_sql_TopDenProdej["TrzbyCelkem"], 2, '.', '&nbsp;');
                        $TopDenProdejDatum =date("d.m.", strtotime($data_sql_TopDenProdej["tj_Datum"])); 
                        $TopDenProdejSuma = number_format($data_sql_TopDenProdej["ZaProdej"], 2, '.', '&nbsp;');
                        $TopDenProdejPopisek = $TopDenProdejDatum . " - " .$TopDenProdejSuma;
                      
                      



                      //DEMO DATA
                      if ($sw_id==0) {
                         $TopObsluhaSluzby = "Obsluha A";
                         $TopObsluhaProdej = "Obsluha B";
                         $TopDenSluzbySuma = rand(1000,10000);
                         $TopDenProdejSuma = rand(1000,10000);
                         $TopDenSluzbyDatum = "01.02";
                         $TopDenProdejDatum = "01.02";
                      }
                      
                        
                      ?>
                      
                      
  
  
                      <div class="row">
                            <div class="col-md-3">
                                <div class="panel panel-solid-success widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $TopObsluhaSluzby; ?></font></span>
                                        <span class="title text-center">TOP&nbsp;obsluha&nbsp;za&nbsp;služby</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $TopObsluhaProdej; ?></font></span>
                                        <span class="title text-center">TOP&nbsp;obsluha&nbsp;za&nbsp;prodej</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-solid-danger widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $TopDenSluzbySuma."&nbsp;".$Mena_Klienta ?></font></span>
                                        <span class="title text-center">TOP&nbsp;den&nbsp;za&nbsp;služby&nbsp;<b><?php echo $TopDenSluzbyDatum; ?></b></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $TopDenProdejSuma."&nbsp;".$Mena_Klienta ?></font></span>              
                                        <span class="title text-center">TOP&nbsp;den&nbsp;za&nbsp;prodej&nbsp;<b><?php echo $TopDenProdejDatum; ?></b></span>
                                    </div>
                                </div>
                            </div>
                        </div> 
                        
                        
                        <!-- RADEK2 -->
                        <?php
                              //SUM UCTENEK
                            $sql_PocetUctenek= "SELECT  SUM(`ts_uctenek`) as 'CelkemUctenek' FROM `trzby_strediska_$SQL_ROK` WHERE `sw_id` = $sw_id and MONTH(`ts_Datum`) = '$aktualniMesic' and YEAR(`ts_Datum`) = '$SQL_ROK' LIMIT 1";
                            //echo $sql_PocetUctenek;
                            $vysledek_sql_PocetUctenek=$mysqli->query($sql_PocetUctenek);
                            $data_sql_PocetUctenek=MySQLi_Fetch_Array($vysledek_sql_PocetUctenek);
                            //$CelkoveTrzby = number_format($data_sql_PocetUctenek["TrzbyCelkem"], 2, '.', '&nbsp;');
                            $PocetUctenekSuma = $data_sql_PocetUctenek["CelkemUctenek"];
                            
                            if ($PocetUctenekSuma=="") {
                              $PocetUctenekSuma = " - ";
                            }


                              //DEMO DATA
                              if ($sw_id==0) {
                                 $PocetUctenekSuma = rand(100,1000);
                              }

                        ?>
                        
                        
                        <div class="row hs-monthly-table-row">
                            <div class="col-md-3">
                                <div class="panel panel-solid-danger widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $PocetUctenekSuma; ?></font></span>
                                        <span class="title text-center">Počet&nbsp;účtenek&nbsp;za&nbsp;měsíc</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $TopObsluhaProdej; ?></font></span>
                                        <span class="title text-center">TOP&nbsp;obsluha&nbsp;za&nbsp;prodej</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                 <div class="panel panel-solid-success widget-mini">                                
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $PocetUctenekSuma; ?></font></span>
                                        <span class="title text-center">Počet&nbsp;účtenek</span>
                                    </div>
                                </div>
                            </div> -->
                            <!--
                              <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $TopDenProdejSuma."&nbsp;".$Mena_Klienta ?></font></span>              
                                        <span class="title text-center">TOP&nbsp;den&nbsp;za&nbsp;prodej&nbsp;<b><?php echo $TopDenProdejDatum; ?></b></span>
                                    </div>
                                </div>
                            </div>
                            -->
                        </div> 
                        
                        
                     
                        
  
                            
  <!-- V112: fixed server-rendered position below all KPI cards, even without tables. -->
<div class="row hs-monthly-period-row"><div class="col-md-12"><section class="panel panel-default hs-monthly-card hs-monthly-period-card"><div class="panel-body hs-monthly-period-body"><div class="hs-monthly-period-groups"><div class="hs-monthly-period-group" role="group" aria-label="Měsíc"><div class="hs-monthly-period-controls"><a class="hs-monthly-period-nav hs-monthly-period-nav--previous" aria-label="Předchozí měsíc" href="<?php echo htmlspecialchars($hs_period_links['month-prev'], ENT_QUOTES, 'UTF-8'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></a><strong class="hs-monthly-period-value"><?php echo htmlspecialchars($aMesice[$hs_month - 1], ENT_QUOTES, 'UTF-8'); ?></strong><a class="hs-monthly-period-nav hs-monthly-period-nav--next" aria-label="Následující měsíc" href="<?php echo htmlspecialchars($hs_period_links['month-next'], ENT_QUOTES, 'UTF-8'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></a></div></div><div class="hs-monthly-period-group" role="group" aria-label="Rok"><div class="hs-monthly-period-controls"><a class="hs-monthly-period-nav hs-monthly-period-nav--previous" aria-label="Předchozí rok" href="<?php echo htmlspecialchars($hs_period_links['year-prev'], ENT_QUOTES, 'UTF-8'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></a><strong class="hs-monthly-period-value"><?php echo (int) $hs_year; ?></strong><a class="hs-monthly-period-nav hs-monthly-period-nav--next" aria-label="Následující rok" href="<?php echo htmlspecialchars($hs_period_links['year-next'], ENT_QUOTES, 'UTF-8'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></a></div></div></div></div></section></div></div>
<noscript><style>#main-content .hs-monthly-table-row { visibility: visible !important; }</style></noscript>
<div class="row hs-monthly-table-row">
                   <div class="col-md-12">
                    <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Měsíční sumář za střediska | firmy</b></font></h3>
                                <div class="actions pull-right">
                                  
                                   <!--
                                    <a href="../str/akce/MesicniTrzby.php?DeleteMesic=<?php echo $_SESSION["SQL_Mesic"]; ?>&DeleteRok=<?php echo $SQL_ROK; ?>"><i class="fa fa-times-circle" title="Smazat data měsíce"></i></a>
                                    <a href="../str/akce/MesicniTrzby.php?RefreshMesic=<?php echo $_SESSION["SQL_Mesic"]; ?>&RefreshRok=<?php echo $SQL_ROK; ?>"><i class="fa fa-refresh" title="Refresh dat pro tento měsíc"></i></a> 
                                   -->          
                                    
                                    
                                    <a data-hs-period="month-prev" href="<?php echo htmlspecialchars($hs_period_links['month-prev'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-left"></i></a>
                                     <span><?php echo $aMesice[$_SESSION["SQL_Mesic"]-1]; ?></span>
                                    <a data-hs-period="month-next" href="<?php echo htmlspecialchars($hs_period_links['month-next'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-right"></i></a>
                                    
                                    
                                    <a data-hs-period="year-prev" href="<?php echo htmlspecialchars($hs_period_links['year-prev'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-left"></i></a>
                                     <span><?php echo $SQL_ROK; ?></span>
                                    <a data-hs-period="year-next" href="<?php echo htmlspecialchars($hs_period_links['year-next'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-right"></i></a>
                                     
                                    
                                    
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                 
                                </div>
                                
                            </div>
                            <div class="panel-body">
                                          
                                <div class="table-responsive">
                                    
                                       <?php
                                             $originalDate = $datum;
                                             $newDate = date("d.m.Y", strtotime($originalDate));
                                             $newYear = date("Y", strtotime($originalDate));
                                                                                   
                                      $originalDate = $datum;
                                      $newDate_sql = date("Y-m-d", strtotime($originalDate));
                                                                            
                                          $StrediskaCNT = 0;
                                           
                                          //$sqldotaz= "SELECT * FROM `trzby_strediska_$newYear` WHERE `sw_id` = $sw_id and MONTH(`tj_Datum`) = '$aktualniMesic' and YEAR(`tj_Datum`) = '$SQL_ROK' order by `ts_NazevStrediska` ASC" ;
                                          $sqldotaz= "SELECT `ts_NazevStrediska`,sum(`ts_TrzbyCelkem`)as ts_TrzbyCelkem ,sum(`ts_TrzbyCelkemBezDPH`) as ts_TrzbyCelkemBezDPH,sum(`ts_ZaSluzby`) as ts_ZaSluzby,sum(`ts_ZaSluzbyBezDPH`) as ts_ZaSluzbyBezDPH,sum(`ts_ZaProdej`)as ts_ZaProdej,sum(`ts_ZaProdejBezDPH`)as ts_ZaProdejBezDPH ,sum(`Celkem_Voucher`)as Celkem_Voucher,sum(`Celkem_Voucher_bezDPH`)as Celkem_Voucher_bezDPH,sum(`Celkem_Credit`)as Celkem_Credit,sum(`Celkem_Credit_bezDPH`)as Celkem_Credit_bezDPH,`ts_Datum` FROM `trzby_strediska_$SQL_ROK`  WHERE `sw_id` = $sw_id and MONTH(`ts_Datum`) = '$aktualniMesic' and YEAR(`ts_Datum`) = '$SQL_ROK' group by `ts_NazevStrediska` order by `ts_NazevStrediska` ASC" ;
//echo $sqldotaz;
                                     if (!$sqldotaz) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                                        
                                                                //echo $sqldotaz;        
                                                                        $vysledek=$mysqli->query("$sqldotaz");
                                                                        $pocet_radku_strediska = $vysledek->num_rows;                                       
                                                                        
                                                                        //echo $pocet_radku_strediska;
                                                                
                                                                        
                                                                        if ($pocet_radku_strediska==0 and $sw_id!=0 ) {
                                                                          echo "Pro měsíc: <b>".$aMesice[$_SESSION["SQL_Mesic"]-1] . "</b> a rok <b>".$SQL_ROK."</b> na pobočce <b>".$pobocka_jmeno."</b> nejsou nalezeny žádné tržby za střediska";  
                                                                        }else {
                                                                                  if ($sw_id!=0) {
                                                                                    
                                                                                               ?>
                                                                                                      <table width="99%" class="table table-bordered table-striped" id="table_MesicniSumarZaStrediska">
                                                                                                          <thead>
                                                                                                              <tr>
                                                                                                                  <th>#</th>
                                                                                                                  <th>Název střediska | firmy</th>
                                                                                                                  <td align="center"><b>Tržby celkem</b></td>
                                                                                                                  <td align="center"><font size="-1">bez DPH</font></td>
                                                                                                                  <td align="center"><b>Za služby</b></td>
                                                                                                                  <td align="center"><font size="-1">bez DPH</font></td>
                                                                                                                  <td align="center"><b>Za prodej</b></td>
                                                                                                                  <td align="center"><font size="-1">bez DPH</font></td>

                                                                                                                  <td align="center"><b>Ceniny</b></td>
                                                                                                                  <td align="center"><font size="-1">bez DPH</font></td>
                                                                                                                  <td align="center"><b>Kredit</b></td>
                                                                                                                  <td align="center"><font size="-1">bez DPH</font></td>
                                                                                                              </tr>
                                                                                                          </thead>

                                                                                                    <?php
                                                                                                  echo "<tbody>";


                                                                                                   /* MIGRACE PHP 5.5 -> 8.3
                                                                                                      Duvod:
                                                                                                      Prazdny retezec nelze v PHP 8.3 scitat s ciselnym retezcem z databaze.
                                                                                                      Soucty zacinaji ciselnou nulou, vysledek vypoctu zustava stejny.
                                                                                                      STARY KOD PHP 5.5:
                                                                                                      $ts_TrzbyCelkemSUM=""; $ts_TrzbyCelkemBezDPHSUM="";
                                                                                                      $ts_ZaSluzbySUM=""; $ts_ZaSluzbyBezDPHSUM="";
                                                                                                      $ts_ZaProdejSUM=""; $ts_ZaProdejBezDPHSUM="";
                                                                                                      $ts_ZaVoucherSUM=""; $ts_ZaVoucherBezDPHSUM="";
                                                                                                      $ts_ZaKreditSUM=""; $ts_ZaKreditBezDPHSUM="";
                                                                                                   */
                                                                                                   $ts_TrzbyCelkemSUM=0;
                                                                                                   $ts_TrzbyCelkemBezDPHSUM=0;
                                                                                                   $ts_ZaSluzbySUM=0;
                                                                                                   $ts_ZaSluzbyBezDPHSUM=0;
                                                                                                   $ts_ZaProdejSUM=0;
                                                                                                   $ts_ZaProdejBezDPHSUM=0;
                                                                                                   $ts_ZaVoucherSUM=0;
                                                                                                   $ts_ZaVoucherBezDPHSUM=0;
                                                                                                   $ts_ZaKreditSUM=0;
                                                                                                   $ts_ZaKreditBezDPHSUM=0;


                                                                                                  while ($strediska_seznam=MySQLi_Fetch_Array($vysledek)):
                                                                                                        $StrediskaCNT = $StrediskaCNT + 1;

                                                                                                               $ts_TrzbyCelkemSUM= $ts_TrzbyCelkemSUM + $strediska_seznam["ts_TrzbyCelkem"];
                                                                                                               $ts_TrzbyCelkemBezDPHSUM= $ts_TrzbyCelkemBezDPHSUM + $strediska_seznam["ts_TrzbyCelkemBezDPH"];
                                                                                                               $ts_ZaSluzbySUM= $ts_ZaSluzbySUM + $strediska_seznam["ts_ZaSluzby"];
                                                                                                               $ts_ZaSluzbyBezDPHSUM= $ts_ZaSluzbyBezDPHSUM + $strediska_seznam["ts_ZaSluzbyBezDPH"];
                                                                                                               $ts_ZaProdejSUM=$ts_ZaProdejSUM + $strediska_seznam["ts_ZaProdej"];
                                                                                                               $ts_ZaProdejBezDPHSUM=$ts_ZaProdejBezDPHSUM+ $strediska_seznam["ts_ZaProdejBezDPH"];

                                                                                                               $ts_ZaVoucherSUM= $ts_ZaVoucherSUM + $strediska_seznam["Celkem_Voucher"];
                                                                                                               $ts_ZaVoucherBezDPHSUM= $ts_ZaVoucherBezDPHSUM + $strediska_seznam["Celkem_Voucher_bezDPH"];
                                                                                                               $ts_ZaKreditSUM=$ts_ZaKreditSUM + $strediska_seznam["Celkem_Credit"];
                                                                                                               $ts_ZaKreditBezDPHSUM=$ts_ZaKreditBezDPHSUM+ $strediska_seznam["Celkem_Credit_bezDPH"];



                                                                                                              echo "<tr>";
                                                                                                                  echo "<td>$StrediskaCNT</td>";
                                                                                                                  echo "<td>".$strediska_seznam["ts_NazevStrediska"]."</td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\">".number_format($strediska_seznam["ts_TrzbyCelkem"], 2, '.', '&nbsp;')."</div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font size=\"-1\">".number_format($strediska_seznam["ts_TrzbyCelkemBezDPH"], 2, '.', '&nbsp;')."</font></div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\">".number_format($strediska_seznam["ts_ZaSluzby"], 2, '.', '&nbsp;')."</div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font size=\"-1\">".number_format($strediska_seznam["ts_ZaSluzbyBezDPH"], 2, '.', '&nbsp;')."</font></div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\">".number_format($strediska_seznam["ts_ZaProdej"], 2, '.', '&nbsp;')."</div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font size=\"-1\">".number_format($strediska_seznam["ts_ZaProdejBezDPH"], 2, '.', '&nbsp;')."</font></div></td>";

                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\">".number_format($strediska_seznam["Celkem_Voucher"], 2, '.', '&nbsp;')."</div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font size=\"-1\">".number_format($strediska_seznam["Celkem_Voucher_bezDPH"], 2, '.', '&nbsp;')."</font></div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\">".number_format($strediska_seznam["Celkem_Credit"], 2, '.', '&nbsp;')."</div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font size=\"-1\">".number_format($strediska_seznam["Celkem_Credit_bezDPH"], 2, '.', '&nbsp;')."</font></div></td>";
                                                                                                              echo "</tr>";

                                                                                                          //$Posledniprectenydatum =  $strediska_seznam["ts_Datum"];

                                                                                                          //echo $Posledniprectenydatum;


                                                                                                          //echo "</tbody>";
                                                                                                  endwhile;

                                                                                                               $ts_TrzbyCelkemSUM= number_format($ts_TrzbyCelkemSUM, 2, '.', '&nbsp;');
                                                                                                               $ts_TrzbyCelkemBezDPHSUM= number_format($ts_TrzbyCelkemBezDPHSUM, 2, '.', '&nbsp;');
                                                                                                               $ts_ZaSluzbySUM= number_format($ts_ZaSluzbySUM, 2, '.', '&nbsp;');
                                                                                                               $ts_ZaSluzbyBezDPHSUM= number_format($ts_ZaSluzbyBezDPHSUM, 2, '.', '&nbsp;');
                                                                                                               $ts_ZaProdejSUM=number_format($ts_ZaProdejSUM, 2, '.', '&nbsp;');
                                                                                                               $ts_ZaProdejBezDPHSUM=number_format($ts_ZaProdejBezDPHSUM, 2, '.', '&nbsp;');

                                                                                                               $ts_ZaVoucherSUM= number_format($ts_ZaVoucherSUM, 2, '.', '&nbsp;');
                                                                                                               $ts_ZaVoucherBezDPHSUM= number_format($ts_ZaVoucherBezDPHSUM, 2, '.', '&nbsp;');
                                                                                                               $ts_ZaKreditSUM=number_format($ts_ZaKreditSUM, 2, '.', '&nbsp;');
                                                                                                               $ts_ZaKreditBezDPHSUM=number_format($ts_ZaKreditBezDPHSUM, 2, '.', '&nbsp;');


                                                                                                        //echo "<tbody>";
                                                                                                              echo "<tr>";
                                                                                                                  echo "<td></td>";
                                                                                                                  echo "<td><b>Celkem ($Mena_Klienta):</b></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font color=\"#E25D5D\"><b>$ts_TrzbyCelkemSUM</b></font></div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font color=\"#556B8D\" size=\"-1\"><b>$ts_TrzbyCelkemBezDPHSUM</b></font></div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font color=\"#E25D5D\"><b>$ts_ZaSluzbySUM</b></font></div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font color=\"#556B8D\" size=\"-1\"><b>$ts_ZaSluzbyBezDPHSUM</b></font></div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font color=\"#E25D5D\"><b>$ts_ZaProdejSUM</b></font></div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font color=\"#556B8D\" size=\"-1\"><b>$ts_ZaProdejBezDPHSUM</b></font></div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font color=\"#E25D5D\"><b>$ts_ZaVoucherSUM</b></font></div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font color=\"#556B8D\" size=\"-1\"><b>$ts_ZaVoucherBezDPHSUM</b></font></div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font color=\"#E25D5D\"><b>$ts_ZaKreditSUM</b></font></div></td>";
                                                                                                                  echo "<td><div align=\"right\" style=\"width: 70%;\"><font color=\"#556B8D\" size=\"-1\"><b>$ts_ZaKreditBezDPHSUM</b></font></div></td>";
                                                                                                              echo "</tr>";
                                                                                                          echo "</tbody>";

                                                                                                           ?>

                                                                                                        </table>
                                                                                                    <?php
                                                                                        }else {
                                                                                            ?>
                                                                                            <!-- DEMO DATA -->
                                                                                                  <table class="table table-bordered table-striped">
                                                                                                    <thead>
                                                                                                      <tr>
                                                                                                        <th>#
                                                                                                        </th>
                                                                                                        <th>Název střediska | firmy
                                                                                                        </th>
                                                                                                        <td align="center"><b>Tržby celkem</b></td>
                                                                                                        <td align="center">
                                                                                                          <font size="-1">bez DPH
                                                                                                          </font></td>
                                                                                                        <td align="center"><b>Za služby</b></td>
                                                                                                        <td align="center">
                                                                                                          <font size="-1">bez DPH
                                                                                                          </font></td>
                                                                                                        <td align="center"><b>Za prodej</b></td>
                                                                                                        <td align="center">
                                                                                                          <font size="-1">bez DPH
                                                                                                          </font></td>


                                                                                                      </tr>
                                                                                                    </thead>
                                                                                                    <tbody>
                                                                                                      <tr><td>1</td><td>Firma A</td><td>
                                                                                                          <div style="width: 70%;" align="right">66 371,00
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">
                                                                                                            <font size="-1">54 844,00
                                                                                                            </font>
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">59 399,00
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">
                                                                                                            <font size="-1">49 082,00
                                                                                                            </font>
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">6 972,00
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">
                                                                                                            <font size="-1">5 756,00
                                                                                                            </font>
                                                                                                          </div></td>
                                                                                                      </tr>
                                                                                                      <tr><td>2</td><td>Firma B</td><td>
                                                                                                          <div style="width: 70%;" align="right">87 383,00
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">
                                                                                                            <font size="-1">72 211,00
                                                                                                            </font>
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">75 919,00
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">
                                                                                                            <font size="-1">62 737,00
                                                                                                            </font>
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">11 464,00
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">
                                                                                                            <font size="-1">9 468,00
                                                                                                            </font>
                                                                                                          </div></td>
                                                                                                      </tr>
                                                                                                      <tr><td></td><td><b>Celkem (Kč):</b></td><td>
                                                                                                          <div style="width: 70%;" align="right">
                                                                                                            <font color="#E25D5D"><b>153 754,00</b>
                                                                                                            </font>
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">
                                                                                                            <font size="-1" color="#556B8D"><b>127 055.00</b>
                                                                                                            </font>
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">
                                                                                                            <font color="#E25D5D"><b>135 318.00</b>
                                                                                                            </font>
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">
                                                                                                            <font size="-1" color="#556B8D"><b>111 819.00</b>
                                                                                                            </font>
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">
                                                                                                            <font color="#E25D5D"><b>18 436.00</b>
                                                                                                            </font>
                                                                                                          </div></td><td>
                                                                                                          <div style="width: 70%;" align="right">
                                                                                                            <font size="-1" color="#556B8D"><b>15 224.00</b>
                                                                                                            </font>
                                                                                                          </div></td>
                                                                                                      </tr>
                                                                                                    </tbody>
                                                                                                  </table>



                                                                                            <?php
                                                                                        }



                                                                             }
                                                                          ?>
                                    
                                </div>

                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-12">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Měsíční sumář za jednotlivce</b></font></h3>
                                 <div class="actions pull-right">
                                    
                                    <a data-hs-period="month-prev" href="<?php echo htmlspecialchars($hs_period_links['month-prev'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-left"></i></a>
                                     <span><?php echo $aMesice[$_SESSION["SQL_Mesic"]-1]; ?></span>
                                    <a data-hs-period="month-next" href="<?php echo htmlspecialchars($hs_period_links['month-next'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-right"></i></a>
                                    
                                    <a data-hs-period="year-prev" href="<?php echo htmlspecialchars($hs_period_links['year-prev'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-left"></i></a>
                                     <span><?php echo $SQL_ROK; ?></span>
                                    <a data-hs-period="year-next" href="<?php echo htmlspecialchars($hs_period_links['year-next'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-right"></i></a>
                               
                               
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body">

                                <div class="table-responsive">
                                    <?php
                                      
                                          $JednotlivciCNT = 0;
                                                                                    
                                          
                                          //$sqldotaz_jednotlivci= "SELECT * FROM `trzby_jednotlivci_$newYear` WHERE `sw_id` = $sw_id  and MONTH(`tj_Datum`) = '$newMonthDate_sql' and DATE(`tj_Datum`) = '$aktualniMesic' order by `tj_NazevStrediska` ASC" ;
                                         $sqldotaz_jednotlivci= "SELECT concat (`tj_NazevStrediska` , `tj_NazevJednotlivce`) as Jmeno,`tj_NazevStrediska`, `tj_NazevJednotlivce`, SUM(`tj_TrzbyCelkem`) as 'TrzbyCelkem' ,SUM(`tj_TrzbyCelkemBezDPH`) as 'TrzbyCelkemBezDPH' ,SUM(`tj_ZaSluzby`) as 'ZaSluzby',SUM(`tj_ZaSluzbyBezDPH`)as 'ZaSluzbyBezDPH' ,SUM(`tj_ZaProdej`) as 'ZaProdej',SUM(`tj_ZaProdejBezDPH`) as 'ZaProdejBezDPH' ,sum(`Celkem_Voucher`)as 'Celkem_Voucher' ,sum(`Celkem_Voucher_bezDPH`)as 'Celkem_Voucher_bezDPH' ,sum(`Celkem_Credit`)as 'Celkem_Credit' ,sum(`Celkem_Credit_bezDPH`)as 'Celkem_Credit_bezDPH'FROM `trzby_jednotlivci_$SQL_ROK` WHERE `sw_id` = $sw_id and MONTH(`tj_Datum`) = '$aktualniMesic' and YEAR(`tj_Datum`) = '$SQL_ROK' group by Jmeno order by 1,2 DESC " ;
                                          //echo $sqldotaz_jednotlivci;
                                     if (!$sqldotaz_jednotlivci) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                                        
                                                                        
                                                                        $vysledek_jednotlivci=$mysqli->query("$sqldotaz_jednotlivci");
                                                                        $pocet_radku_jednotlivci = $vysledek_jednotlivci->num_rows;                                       
                                                                        
                                                                        //echo $pocet_radku_strediska;
                                                                
                                                                        
                                                                        if ($pocet_radku_jednotlivci==0 and $sw_id!=0) {
                                                                          echo "Pro měsíc: <b>".$aMesice[$_SESSION["SQL_Mesic"]-1] . "</b> a rok <b>".$SQL_ROK."</b> na pobočce <b>".$pobocka_jmeno."</b> nejsou nalezeny žádné tržby za jednotlivce";  
                                                                        }else {

                                                                             if ($sw_id!=0) {
                                                                              

                                                                                              ?>
                                                                                                <table width="99%" class="table table-bordered table-striped" id="table_MesicniSumarZaJednotlivce">
                                                                                                    <thead>
                                                                                                        <tr>
                                                                                                              <th>#</th>
                                                                                                              <th>Středisko | firmy</th>
                                                                                                              <th>Jméno obsluhy</th>
                                                                                                              <td align="center"><b>Tržby celkem</b></td>
                                                                                                              <td align="center"><font size="-1">bez DPH</font></td>
                                                                                                              <td align="center"><b>Za služby</b></td>
                                                                                                              <td align="center"><font size="-1">bez DPH</font></td>
                                                                                                              <td align="center"><b>Za prodej</b></td>
                                                                                                              <td align="center"><font size="-1">bez DPH</font></td>

                                                                                                              <td align="center"><b>Ceniny</b></td>
                                                                                                              <td align="center"><font size="-1">bez DPH</font></td>
                                                                                                              <td align="center"><b>Kredit</b></td>
                                                                                                              <td align="center"><font size="-1">bez DPH</font></td>
                                                                                                        </tr>
                                                                                                    </thead>

                                                                                              <?php




                                                                                            echo "<tbody>";
                                                                                            while ($jednotlivci_seznam=MySQLi_Fetch_Array($vysledek_jednotlivci)):
                                                                                                  $JednotlivciCNT = $JednotlivciCNT + 1;

                                                                                                    //POLE
                                                                                                     $JmenoObsluhy[$JednotlivciCNT] = $jednotlivci_seznam["tj_NazevJednotlivce"];
                                                                                                    $SluzbyObsluhy[$JednotlivciCNT] = $jednotlivci_seznam["ZaSluzby"];
                                                                                                    $ProdejObsluhy[$JednotlivciCNT] = $jednotlivci_seznam["ZaProdej"];

                                                                                                     $BarvaObsluhyTab = random_color();
                                                                                                     $BarvaObsluhy[$JednotlivciCNT] = $BarvaObsluhyTab;

                                                                                                        echo "<tr>";
                                                                                                            echo "<td>$JednotlivciCNT</td>";
                                                                                                            echo "<td>".$jednotlivci_seznam["tj_NazevStrediska"]."</td>";
                                                                                                            echo "<td><div style=\"width: 15px; height: 15px; float: left; margin-top:5px; background: #$BarvaObsluhyTab; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;\"></div> <div>&nbsp;&nbsp;".$jednotlivci_seznam["tj_NazevJednotlivce"]."</div> </td>";
                                                                                                            echo "<td><div align=\"right\" style=\"width: 70%;\"><b>".number_format($jednotlivci_seznam["TrzbyCelkem"], 2, '.', '&nbsp;')."</b></div></td>";
                                                                                                            echo "<td><div align=\"right\" style=\"width: 70%;\"><font size=\"-1\">".number_format($jednotlivci_seznam["TrzbyCelkemBezDPH"], 2, '.', '&nbsp;')."</font></div></td>";
                                                                                                            echo "<td><div align=\"right\" style=\"width: 70%;\"><b>".number_format($jednotlivci_seznam["ZaSluzby"], 2, '.', '&nbsp;')."</b></p></div></td>";
                                                                                                            echo "<td><div align=\"right\" style=\"width: 70%;\"><font size=\"-1\">".number_format($jednotlivci_seznam["ZaSluzbyBezDPH"], 2, '.', '&nbsp;')."</font></div></td>";
                                                                                                            echo "<td><div align=\"right\" style=\"width: 70%;\"><b>".number_format($jednotlivci_seznam["ZaProdej"], 2, '.', '&nbsp;')."</b></div></td>";
                                                                                                            echo "<td><div align=\"right\" style=\"width: 70%;\"><font size=\"-1\">".number_format($jednotlivci_seznam["ZaProdejBezDPH"], 2, '.', '&nbsp;')."</font></div></td>";

                                                                                                            echo "<td><div align=\"right\" style=\"width: 70%;\"><b>".number_format($jednotlivci_seznam["Celkem_Voucher"], 2, '.', '&nbsp;')."</b></div></td>";
                                                                                                            echo "<td><div align=\"right\" style=\"width: 70%;\"><font size=\"-1\">".number_format($jednotlivci_seznam["Celkem_Voucher_bezDPH"], 2, '.', '&nbsp;')."</font></div></td>";

                                                                                                            echo "<td><div align=\"right\" style=\"width: 70%;\"><b>".number_format($jednotlivci_seznam["Celkem_Credit"], 2, '.', '&nbsp;')."</b></div></td>";
                                                                                                            echo "<td><div align=\"right\" style=\"width: 70%;\"><font size=\"-1\">".number_format($jednotlivci_seznam["Celkem_Credit_bezDPH"], 2, '.', '&nbsp;')."</font></div></td>";
                                                                                                        echo "</tr>";
                                                                                            endwhile;





                                                                                            echo "</tbody>";



                                                                                             ?>
                                                                                                  </table>
                                                                               <?php
                                                                                }else {
                                                                                   //DEMO DATA
                                                                                   ?>
                                                                                     <table class="table table-bordered table-striped">
                                                                                      <thead>
                                                                                        <tr>
                                                                                          <th>#
                                                                                          </th>
                                                                                          <th>Středisko | firmy
                                                                                          </th>
                                                                                          <th>Jméno obsluhy
                                                                                          </th>
                                                                                          <td align="center"><b>Tržby celkem</b></td>
                                                                                          <td align="center">
                                                                                            <font size="-1">bez DPH
                                                                                            </font></td>
                                                                                          <td align="center"><b>Za služby</b></td>
                                                                                          <td align="center">
                                                                                            <font size="-1">bez DPH
                                                                                            </font></td>
                                                                                          <td align="center"><b>Za prodej</b></td>
                                                                                          <td align="center">
                                                                                            <font size="-1">bez DPH
                                                                                            </font></td>
                                                                                        </tr>
                                                                                      </thead>
                                                                                      <tbody>
                                                                                        <tr><td>1</td><td>Firma A</td><td>
                                                                                            <table border="0">
                                                                                              <tbody>
                                                                                                <tr><td>
                                                                                                    <div style="width: 15px; height: 15px; background: #0e08b9; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;">
                                                                                                    </div></td> <td>&nbsp;Obsluha A</td>
                                                                                                </tr>
                                                                                              </tbody>
                                                                                            </table></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>37 628,00</b>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">31 091,00
                                                                                              </font>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>33 694,00</b>
                                                                                              <p>
                                                                                              </p>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">27 841,00
                                                                                              </font>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>3 934,00</b>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">3 242,00
                                                                                              </font>
                                                                                            </div></td>
                                                                                        </tr>
                                                                                        <tr><td>2</td><td>Firma A</td><td>
                                                                                            <table border="0">
                                                                                              <tbody>
                                                                                                <tr><td>
                                                                                                    <div style="width: 15px; height: 15px; background: #1051f1; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;">
                                                                                                    </div></td> <td>&nbsp;Obsluha B</td>
                                                                                                </tr>
                                                                                              </tbody>
                                                                                            </table></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>28 743,00</b>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">23 749,00
                                                                                              </font>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>25 705,00</b>
                                                                                              <p>
                                                                                              </p>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">21 238,00
                                                                                              </font>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>3 038,00</b>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">2 505,00
                                                                                              </font>
                                                                                            </div></td>
                                                                                        </tr>
                                                                                        <tr><td>3</td><td>Firma B</td><td>
                                                                                            <table border="0">
                                                                                              <tbody>
                                                                                                <tr><td>
                                                                                                    <div style="width: 15px; height: 15px; background: #a044ae; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;">
                                                                                                    </div></td> <td>&nbsp;Obsluha C</td>
                                                                                                </tr>
                                                                                              </tbody>
                                                                                            </table></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>27 231,00</b>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">22 499,00
                                                                                              </font>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>25 102,00</b>
                                                                                              <p>
                                                                                              </p>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">20 739,00
                                                                                              </font>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>2 129,00</b>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">1 754,00
                                                                                              </font>
                                                                                            </div></td>
                                                                                        </tr>
                                                                                        <tr><td>4</td><td>Firma B</td><td>
                                                                                            <table border="0">
                                                                                              <tbody>
                                                                                                <tr><td>
                                                                                                    <div style="width: 15px; height: 15px; background: #eedf60; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;">
                                                                                                    </div></td> <td>&nbsp;Obsluha D</td>
                                                                                                </tr>
                                                                                              </tbody>
                                                                                            </table></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>29 372,00</b>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">24 267,00
                                                                                              </font>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>25 940,00</b>
                                                                                              <p>
                                                                                              </p>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">21 431,00
                                                                                              </font>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>3 432,00</b>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">2 829,00
                                                                                              </font>
                                                                                            </div></td>
                                                                                        </tr>
                                                                                        <tr><td>5</td><td>Firma B</td><td>
                                                                                            <table border="0">
                                                                                              <tbody>
                                                                                                <tr><td>
                                                                                                    <div style="width: 15px; height: 15px; background: #b032e4; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;">
                                                                                                    </div></td> <td>&nbsp;Obsluha E</td>
                                                                                                </tr>
                                                                                              </tbody>
                                                                                            </table></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>26 265,00</b>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">21 699,00
                                                                                              </font>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>24 877,00</b>
                                                                                              <p>
                                                                                              </p>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">20 553,00
                                                                                              </font>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right"><b>1 388,00</b>
                                                                                            </div></td><td>
                                                                                            <div style="width: 70%;" align="right">
                                                                                              <font size="-1">1 141,00
                                                                                              </font>
                                                                                            </div></td>
                                                                                        </tr>
                                                                                      </tbody>
                                                                                    </table>

                                                                                   <?php
                                                                                    







                                                                                }
                                                                        }



                                                                        //DEMO DATA
                                                                        if ($sw_id==0) {
                                                                            $JmenoObsluhy[1] = "Obsluha A" ;
                                                                            $JmenoObsluhy[2] = "Obsluha B" ;
                                                                            $JmenoObsluhy[3] = "Obsluha C" ;
                                                                            $JmenoObsluhy[4] = "Obsluha D" ;
                                                                            $JmenoObsluhy[5] = "Obsluha E" ;

                                                                            $SluzbyObsluhy[1] = rand(1000,5000);
                                                                            $SluzbyObsluhy[2] = rand(1000,5000);
                                                                            $SluzbyObsluhy[3] = rand(1000,5000);
                                                                            $SluzbyObsluhy[4] = rand(1000,5000);
                                                                            $SluzbyObsluhy[5] = rand(1000,5000);

                                                                            $ProdejObsluhy[1] = rand(1000,5000);
                                                                            $ProdejObsluhy[2] = rand(1000,5000);
                                                                            $ProdejObsluhy[3] = rand(1000,5000);
                                                                            $ProdejObsluhy[4] = rand(1000,5000);
                                                                            $ProdejObsluhy[5] = rand(1000,5000);

                                                                            $BarvaObsluhy[1] = random_color();
                                                                            $BarvaObsluhy[2] = random_color();
                                                                            $BarvaObsluhy[3] = random_color();
                                                                            $BarvaObsluhy[4] = random_color();
                                                                            $BarvaObsluhy[5] = random_color();

                                                                            $JednotlivciCNT = 5;
                                                                        }
                                                                               ?>
                                                                              
                                </div>
                            </div>
                        </div>
                        
                        
                    </div>
                 </div>
              
                       <?php
                                 $PocetDnuVMesici = cal_days_in_month(CAL_GREGORIAN, $_SESSION["SQL_Mesic"], $SQL_ROK); 
                                   $datax = ""; // 1 - Ne
                                   $select_na_mesic = "";
                                    
                                   for ($d = 1; $d <= $PocetDnuVMesici; $d++) {
                                    
                                             //Identifikace dne v tydnu
                                             $switchDatumPopisky = date("w", strtotime($SQL_ROK."-".$aktualniMesic."-".$d)) ;                                              
                                               
                                               switch ($switchDatumPopisky) {
                                                  case 0: //Ne
                                                       $Popisek = "Neděle";
                                                    break;
                                                  case 1:
                                                       $Popisek = "Pondělí";
                                                    break;
                                                  case 2:
                                                       $Popisek = "Úterý";
                                                    break;
                                                  case 3:
                                                       $Popisek = "Středa";
                                                    break;
                                                  case 4:
                                                       $Popisek = "Čtvrtek";
                                                    break;
                                                  case 5:
                                                       $Popisek = "Pátek";
                                                    break;
                                                  case 6: //So
                                                       $Popisek = "Sobota";
                                                    break;
                                                          
                                               default:
                                                    break;
                                               }             
                                    
                                    
                                    
                                    
                                    if ($d== $PocetDnuVMesici) {
                                       $select_na_mesic = $select_na_mesic ." SELECT $d, CASE WHEN SUM(`ts_ZaSluzby`) IS NOT NULL THEN SUM(`ts_ZaSluzby`) ELSE 0.00 END as 'SLUZBY' , CASE WHEN SUM(`ts_ZaProdej`) IS NOT NULL THEN SUM(`ts_ZaProdej`) ELSE 0.00 END as 'PRODEJ' , CASE WHEN SUM(`Celkem_Credit`) IS NOT NULL THEN SUM(`Celkem_Credit`) ELSE 0.00 END as 'KREDIT' , CASE WHEN SUM(`Celkem_Credit`) IS NOT NULL THEN SUM(`Celkem_Voucher`) ELSE 0.00 END as 'CENINY' FROM `trzby_strediska_$SQL_ROK` WHERE `sw_id` = $sw_id and MONTH(`ts_Datum`) = '$aktualniMesic' and YEAR(`ts_Datum`) = '$SQL_ROK' and DAY(`ts_Datum`) = '$d'";
                                       $datax = $datax. "'".$Popisek." - ".$d."'";
                                    }else {
                                       $select_na_mesic = $select_na_mesic ." SELECT $d, CASE WHEN SUM(`ts_ZaSluzby`) IS NOT NULL THEN SUM(`ts_ZaSluzby`) ELSE 0.00 END as 'SLUZBY' , CASE WHEN SUM(`ts_ZaProdej`) IS NOT NULL THEN SUM(`ts_ZaProdej`) ELSE 0.00 END as 'PRODEJ' , CASE WHEN SUM(`Celkem_Credit`) IS NOT NULL THEN SUM(`Celkem_Credit`) ELSE 0.00 END as 'KREDIT' , CASE WHEN SUM(`Celkem_Credit`) IS NOT NULL THEN SUM(`Celkem_Voucher`) ELSE 0.00 END as 'CENINY' FROM `trzby_strediska_$SQL_ROK` WHERE `sw_id` = $sw_id and MONTH(`ts_Datum`) = '$aktualniMesic' and YEAR(`ts_Datum`) = '$SQL_ROK' and DAY(`ts_Datum`) = '$d'";
                                       $select_na_mesic = $select_na_mesic ." UNION ALL ";
                                       $datax = $datax. "'".$Popisek." - ".$d."',";         
                                    }       
                                   }

    
    //echo $select_na_mesic;
                                     if (!$select_na_mesic) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                   
                                        $vysledek_mesic=$mysqli->query("$select_na_mesic");
                                        $pocet_radku_mesic = $vysledek_mesic->num_rows;
                                                                          
                                        
                                        if ($pocet_radku_mesic!=0) {
                                        
                                           $mesicCNT =0;
                                           
                                           $SUM_Po = 0;
                                           $SUM_Ut = 0;
                                           $SUM_St = 0;
                                           $SUM_Ct = 0;
                                           $SUM_Pa = 0;
                                           $SUM_So = 0;
                                           $SUM_Ne = 0;
                                           
                                           //date("w", strtotime("2011-02-09")); den v tydnu Nedele 0
                                         while ($mesic_seznam=MySQLi_Fetch_Array($vysledek_mesic)):   
                                               
                                               //POLE
                                               $SluzbyMesic[$mesicCNT] = $mesic_seznam["SLUZBY"];
                                               $ProdejMesic[$mesicCNT] = $mesic_seznam["PRODEJ"];                                                                                  
                                               $CeninyMesic[$mesicCNT] = $mesic_seznam["CENINY"];                                                                                  
                                               $KreditMesic[$mesicCNT] = $mesic_seznam["KREDIT"];                                                                                  
                                                $BarvaMesic[$mesicCNT] = random_color(); 
                                               
                                               
                                               $mesicCNT = $mesicCNT + 1;
                                               
                                               $switchDatum = date("w", strtotime($SQL_ROK."-".$aktualniMesic."-".$mesicCNT)) ;                                              
                                               
                                               switch ($switchDatum) {
                                                  case 0: //Ne
                                                       $SUM_Ne = $SUM_Ne + $mesic_seznam["SLUZBY"];
                                                    break;
                                                  case 1:
                                                       $SUM_Po = $SUM_Po + $mesic_seznam["SLUZBY"];
                                                    break;
                                                  case 2:
                                                       $SUM_Ut = $SUM_Ut + $mesic_seznam["SLUZBY"];
                                                    break;
                                                  case 3:
                                                       $SUM_St = $SUM_St + $mesic_seznam["SLUZBY"];
                                                    break;
                                                  case 4:
                                                       $SUM_Ct = $SUM_Ct + $mesic_seznam["SLUZBY"];
                                                    break;
                                                  case 5:
                                                       $SUM_Pa = $SUM_Pa + $mesic_seznam["SLUZBY"];
                                                    break;
                                                  case 6: //So
                                                       $SUM_So = $SUM_So + $mesic_seznam["SLUZBY"];
                                                    break;
                                                          
                                               default:
                                                    break;
                                               }                                               
                                               
                                         endwhile;
                                        
                                             //Dognut3

                                               $DnyBarvy1 = random_color(); 
                                               $DnyBarvy2 = random_color();
                                               $DnyBarvy3 = random_color();
                                               $DnyBarvy4 = random_color();
                                               $DnyBarvy5 = random_color();
                                               $DnyBarvy6 = random_color();
                                               $DnyBarvy7 = random_color();

                                               $DnyPopisky = "'Pondělí','Úterý','Středa','Čtvrtek','Pátek','Sobota','Nedělě'";

                                               $DnyBarvy = "'#$DnyBarvy1','#$DnyBarvy2','#$DnyBarvy3','#$DnyBarvy4','#$DnyBarvy5','#$DnyBarvy6','#$DnyBarvy7'";

                                               $DnySluzby = "$SUM_Po,$SUM_Ut,$SUM_St,$SUM_Ct,$SUM_Pa,$SUM_So,$SUM_Ne";

                                             //DEMO
                                             if ($sw_id==0) {
                                                 //$d1 = DemoDataGrafKolacovy(10);
                                                 //$data = $d1['DemoDataGrafKolacovy'];
                                                 //$labely = $d1['DemoDataGrafKolacovyLabel'];
                                                 //$pozadi = $d1['DemoDataGrafKolacovyPozadi'];

                                                 $SUM_Po= rand(1000,30000);
                                                 $SUM_Ut= rand(1000,30000);
                                                 $SUM_St= rand(1000,30000);
                                                 $SUM_Ct= rand(1000,30000);
                                                 $SUM_Pa= rand(1000,30000);
                                                 $SUM_So= rand(1000,30000);
                                                 $SUM_Ne= rand(1000,30000);

                                                 $DnySluzby =  $SUM_Po.",".$SUM_Ut.",".$SUM_St.",".$SUM_Ct.",".$SUM_Pa.",".$SUM_So.",".$SUM_Ne;
                                             }



                                                                               
                                        
                                             //Dognut1
                                                 $PopiskyObsluha1 = "";
                                                 
                                                 for ($d1 = 1; $d1 < count($JmenoObsluhy)+1; $d1++) {
                                                     
                                                      if ($d1==count($JmenoObsluhy)) {
                                                        $PopiskyObsluha1 = $PopiskyObsluha1 . "'".$JmenoObsluhy[$d1]."'";
                                                        $PopiskySluzby1 = $PopiskySluzby1 . $SluzbyObsluhy[$d1];
                                                        $PopiskyBarvy1 = $PopiskyBarvy1 . "'#".$BarvaObsluhy[$d1]."'";
                                                     
                                                      }else {
                                                        $PopiskyObsluha1 = $PopiskyObsluha1 . "'".$JmenoObsluhy[$d1]."',";
                                                        $PopiskySluzby1 = $PopiskySluzby1 . $SluzbyObsluhy[$d1].",";
                                                        $PopiskyBarvy1 = $PopiskyBarvy1 . "'#".$BarvaObsluhy[$d1]."',";   
                                                      }
                                                 }
                                             //Dognut2
                                                $PopiskyObsluha2 = "";
                                                 //echo $JmenoObsluhy[7];
                                                 
                                                 for ($d2 = 1; $d2 < count($JmenoObsluhy)+1; $d2++) {
                                                      if ($d2==count($JmenoObsluhy)) {
                                                        $PopiskyObsluha2 = $PopiskyObsluha2 . "'".$JmenoObsluhy[$d2]."'";
                                                        $PopiskySluzby2 = $PopiskySluzby2 . $ProdejObsluhy[$d2];
                                                        $PopiskyBarvy2 = $PopiskyBarvy2 . "'#".$BarvaObsluhy[$d2]."'";
                                                     
                                                      }else {
                                                        $PopiskyObsluha2 = $PopiskyObsluha2 . "'".$JmenoObsluhy[$d2]."',";
                                                        $PopiskySluzby2 = $PopiskySluzby2 . $ProdejObsluhy[$d2].",";
                                                        $PopiskyBarvy2 = $PopiskyBarvy2 . "'#".$BarvaObsluhy[$d2]."',";   
                                                      }
                                                 }
                                                 
                                                    
                                        }
                                        
                                        
                                        
                                   
                                   
                                   
                                   
                                  
                                   
                                        
                                   //Zjisteni obsluhy za mesic     
                                   //SELECT NA UCTENKY ZA JEDNOTLIVE LIDI VE DNY V MESICI
                                   $SQL_Mesic = $_SESSION["SQL_Mesic"]; 
                                   $PocetDnuVMesici = cal_days_in_month(CAL_GREGORIAN, $_SESSION["SQL_Mesic"], $SQL_ROK); 
                                   $select_na_mesic = "";
                                   
                                                               
                                    $select_na_mesicUctenky = "SELECT `tj_NazevJednotlivce` as OBSLUHA, CASE WHEN SUM(`tj_uctenek`) IS NOT NULL THEN SUM(`tj_uctenek`) ELSE 0 END as UCTENKY FROM `trzby_jednotlivci_$SQL_ROK` where `sw_id` = $sw_id and MONTH(`tj_Datum`) = $SQL_Mesic and YEAR(`tj_Datum`) = $SQL_ROK group by `tj_NazevJednotlivce`";
                                    if (!$select_na_mesicUctenky) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                      //echo $select_na_mesicUctenky;
                                        $vysledek_mesicUctenky=$mysqli->query("$select_na_mesicUctenky");
                                        $pocet_radku_mesicUctenky = $vysledek_mesicUctenky->num_rows;
                                        
                                        if ($pocet_radku_mesicUctenky!=0) {
                                        
                                           $mesicCNTUctenky =0;
                                           
                                           //date("w", strtotime("2011-02-09")); den v tydnu Nedele 0
                                           while ($mesic_seznamUctenky=MySQLi_Fetch_Array($vysledek_mesicUctenky)):   
                                                 //POLE
                                                 $JmenoObsluhyUctenky[$mesicCNTUctenky] = $mesic_seznamUctenky["OBSLUHA"];
                                                       $UctenekCelkem[$mesicCNTUctenky] = $mesic_seznamUctenky["UCTENKY"];
                                                 
                                                 $TabBarvaUctenky = random_color(); 
                                                 $BarvaObsluhyUctenky[$mesicCNTUctenky] = $TabBarvaUctenky ; 
                                                 $BarvaObsluhyPozadiUctenky[$mesicCNTUctenky] = random_no_hex_color() ;
                                                 
                                                 $mesicCNTUctenky = $mesicCNTUctenky + 1;
                                           endwhile;
                                        }
                                   
                                    //Dognut5 - uctenkz a lidi najednou celkem ya mesic
                                                $PopiskyObsluha3 = "";
                                                
                                                //echo $mesicCNTUctenky;
                                                                                                  
                                                 for ($d3 = 0; $d3 < count($JmenoObsluhyUctenky)+1; $d3++) {
                                                      if ($d3==count($JmenoObsluhyUctenky)) {
                                                        
                                                        $PopiskyObsluha3 = $PopiskyObsluha3 . "'".$JmenoObsluhyUctenky[$d3]."'";
                                                        $PopiskySluzby3 = $PopiskySluzby3 . $UctenekCelkem[$d3];
                                                        $PopiskyBarvy3 = $PopiskyBarvy3 . "'#".$BarvaObsluhyUctenky[$d3]."'";
                                                     
                                                      }else {
                                                        $PopiskyObsluha3 = $PopiskyObsluha3 . "'".$JmenoObsluhyUctenky[$d3]."',";
                                                        $PopiskySluzby3 = $PopiskySluzby3 . $UctenekCelkem[$d3].",";
                                                        $PopiskyBarvy3 = $PopiskyBarvy3 . "'#".$BarvaObsluhyUctenky[$d3]."',";  
                                                      }
                                                 }
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                  
                                  
                                  for ($w = 1; $w <= count($JmenoObsluhyUctenky); $w++) {  
                                   $select_na_mesic="";
                                   for ($d = 1; $d <= $PocetDnuVMesici; $d++) {
                                    if ($d== $PocetDnuVMesici) {
                                       $select_na_mesic = $select_na_mesic ." SELECT CASE WHEN SUM(`tj_uctenek`) IS NOT NULL THEN `tj_uctenek` ELSE 0 END FROM `trzby_jednotlivci_$SQL_ROK`  where `tj_NazevJednotlivce` = '".$JmenoObsluhyUctenky[$w-1]."' and `sw_id` = $sw_id and DAY(`tj_Datum`) = $d and MONTH(`tj_Datum`) = $SQL_Mesic and YEAR(`tj_Datum`) = $SQL_ROK";
                            
                                    }else {
                                       $select_na_mesic = $select_na_mesic ." SELECT CASE WHEN SUM(`tj_uctenek`) IS NOT NULL THEN `tj_uctenek` ELSE 0 END  as Uctenky FROM `trzby_jednotlivci_$SQL_ROK`  where `tj_NazevJednotlivce` = '".$JmenoObsluhyUctenky[$w-1]."' and `sw_id` = $sw_id and DAY(`tj_Datum`) = $d and MONTH(`tj_Datum`) = $SQL_Mesic and YEAR(`tj_Datum`) = $SQL_ROK";
                                       $select_na_mesic = $select_na_mesic ." UNION ALL ";
                                    }       
                                   }
                                   
                                   //SQL to POLE pro jednotlivce
                                    $select_na_mesicUctenky = $select_na_mesic;
                                    
                                    //echo $select_na_mesicUctenky;
                                    if (!$select_na_mesicUctenky) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                      //echo $select_na_mesicUctenky;
                                        $vysledek_mesicUctenky=$mysqli->query("$select_na_mesicUctenky");
                                        $pocet_radku_mesicUctenky = $vysledek_mesicUctenky->num_rows;
                                        
                                        if ($pocet_radku_mesicUctenky!=0) {
                                           $mesicCNTUctenky =0;
                                           //date("w", strtotime("2011-02-09")); den v tydnu Nedele 0
                                           
                                           $ObsahPole = "";
                                           while ($mesic_seznamUctenky=MySQLi_Fetch_Array($vysledek_mesicUctenky)):   
                                                 //POLE
                                                 $MesicDataUctenky[$mesicCNTUctenky-1] = $mesic_seznamUctenky["Uctenky"];
                                                 
                                                  switch ($mesicCNTUctenky) {
                                                  case 0: 
                                                       $MesicDataUctenky1[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 1: 
                                                       $MesicDataUctenky2[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 2: 
                                                       $MesicDataUctenky3[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 3: 
                                                       $MesicDataUctenky4[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 4: 
                                                       $MesicDataUctenky5[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 5: 
                                                       $MesicDataUctenky6[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 6: 
                                                       $MesicDataUctenky7[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 7: 
                                                       $MesicDataUctenky8[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 8: 
                                                       $MesicDataUctenky9[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 9: 
                                                       $MesicDataUctenky10[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 10: 
                                                       $MesicDataUctenky11[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 11: 
                                                       $MesicDataUctenky12[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 12: 
                                                       $MesicDataUctenky13[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 13: 
                                                       $MesicDataUctenky14[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 14: 
                                                       $MesicDataUctenky15[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 15: 
                                                       $MesicDataUctenky16[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 16: 
                                                       $MesicDataUctenky17[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 17: 
                                                       $MesicDataUctenky18[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 18: 
                                                       $MesicDataUctenky19[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 19: 
                                                       $MesicDataUctenky20[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 20: 
                                                       $MesicDataUctenky21[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 21: 
                                                       $MesicDataUctenky22[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 22: 
                                                       $MesicDataUctenky23[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 23: 
                                                       $MesicDataUctenky24[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 24: 
                                                       $MesicDataUctenky25[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 25: 
                                                       $MesicDataUctenky26[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 26: 
                                                       $MesicDataUctenky27[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 27: 
                                                       $MesicDataUctenky28[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 28: 
                                                       $MesicDataUctenky29[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 29: 
                                                       $MesicDataUctenky30[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                  case 30: 
                                                       $MesicDataUctenky31[$w-1] = $mesic_seznamUctenky["Uctenky"];
                                                    break;
                                                          
                                               default:
                                                    break;
                                               }            
                                                
                                                 $mesicCNTUctenky = $mesicCNTUctenky + 1;
                                           endwhile;
                                        }
                                  }

                       ?>

              
              
              
              <div class="row hs-monthly-table-row">
                   <div class="col-md-12">
                    <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Měsíční sumář za střediska | firmy</b></font></h3>
                                <div class="actions pull-right">
                                    
                                    <a data-hs-period="month-prev" href="<?php echo htmlspecialchars($hs_period_links['month-prev'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-left"></i></a>
                                     <span><?php echo $aMesice[$_SESSION["SQL_Mesic"]-1]; ?></span>
                                    <a data-hs-period="month-next" href="<?php echo htmlspecialchars($hs_period_links['month-next'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-right"></i></a>
                                    
                                    
                                    <a data-hs-period="year-prev" href="<?php echo htmlspecialchars($hs_period_links['year-prev'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-left"></i></a>
                                     <span><?php echo $SQL_ROK; ?></span>
                                    <a data-hs-period="year-next" href="<?php echo htmlspecialchars($hs_period_links['year-next'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-right"></i></a>
                                     
                                    
                                    
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                 
                                </div>
                                
                            </div>
                            <div class="panel-body">
                                  <div class="panel-body text-center">
                                     <div>        
                               
                                    
                                    <canvas height="50" id="canvas"></canvas>
                                    
                                </div>
                               </div>

                            </div>
                        </div>
                    </div>      
                    
              </div>
              
              <div class="row">
                      <?php
                         // if (array_sum($SluzbyMesic) !=0 and array_sum($ProdejMesic)!=0) {
                      ?>
                      
                      <div class="col-lg-4">
                         <div class="panel panel-default ">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="Tahoma"><b>Služby tento měsíc dle uživatelů</b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body ">
                               <div class="row">
                                 <?php
                                   if (array_sum($SluzbyObsluhy) ==0 and $sw_id!=0) {
                                       // V113: the chart itself shows the zero state.
                                   }
                                 ?>

                                 <div class="col-lg-5" >

                                           <table border="0">
                                                <?php

                                                   for ($i = 1; $i <= $JednotlivciCNT; $i++) {
                                                      if ($SluzbyObsluhy[$i]> 0) {

                                                         if ($sw_id==0) {
                                                            $PrijmeniObsluhy = $JmenoObsluhy[$i];
                                                         }else {
                                                            $PrijmeniObsluhy = VratPouzePrijmeni($JmenoObsluhy[$i]);
                                                         }


                                                      $SluzbyObsluhyCelk = number_format($SluzbyObsluhy[$i], 2, '.', '&nbsp;');
                                                      $SluzbyObsluhyCelk = str_replace(" ", "&nbsp;" , $SluzbyObsluhyCelk) ;

                                                      echo "<tr>";
                                                      echo "<td><div style=\"width: 15px; height: 15px; background: #$BarvaObsluhy[$i]; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;\"></div></td>";
                                                      echo "<td>&nbsp;$PrijmeniObsluhy</td>";
                                                      echo "<td align=\"right\">&nbsp;&nbsp;<b>$SluzbyObsluhyCelk</b></td>";
                                                      echo"</tr>";
                                                      }
                                                   }
                                                ?>
                                           </table>


                                </div>
                                <div>
                                 <div class="col-lg-7" align="center">
                                  <canvas height="250" id="chart-area1"></canvas>
                                 </div>
                                </div>
                              </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                         <div class="panel panel-default ">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="Tahoma"><b>Prodej tento měsíc dle uživatelů</b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body ">
                               <div class="row">
                                 <?php
                                   if ( array_sum($ProdejObsluhy)==0 and $sw_id!=0) {
                                       // V113: the chart itself shows the zero state.
                                   }
                                 ?>

                                  <div class="col-lg-5" >

                                           <table border="0">
                                                <?php

                                                   for ($i = 1; $i <= $JednotlivciCNT; $i++) {
                                                      if ($ProdejObsluhy[$i]> 0) {

                                                         if ($sw_id==0) {
                                                            $PrijmeniObsluhy = $JmenoObsluhy[$i];
                                                         }else {
                                                            $PrijmeniObsluhy = VratPouzePrijmeni($JmenoObsluhy[$i]);
                                                         }


                                                      $ProdejObsluhyCelk = number_format($ProdejObsluhy[$i], 2, '.', '&nbsp;');
                                                      $ProdejObsluhyCelk = str_replace(" ", "&nbsp;" , $ProdejObsluhyCelk) ;

                                                      echo "<tr>";
                                                      echo "<td><div style=\"width: 15px; height: 15px; background: #$BarvaObsluhy[$i]; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;\"></div></td>";
                                                      echo "<td>&nbsp;$PrijmeniObsluhy</td>";
                                                      echo "<td align=\"right\">&nbsp;&nbsp;<b>$ProdejObsluhyCelk</b></td>";
                                                      echo"</tr>";
                                                      }
                                                   }
                                                ?>
                                           </table>


                                </div>
                                <div>
                                 <div class="col-lg-7" align="center">
                                  <canvas height="250" id="chart-area2"></canvas>
                                 </div>
                                </div>
                              </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                         <div class="panel panel-default ">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="Tahoma"><b>Služby dle dnů v týdnu</b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body ">
                               <div class="row">
                                 <?php
                                   if (array_sum($SluzbyMesic) ==0 and array_sum($ProdejMesic)==0 and $sw_id!=0) {
                                       // V113: the chart itself shows the zero state.
                                   }
                                 ?>
                                 
                                 
                                 
                                 <div class="col-lg-5" >

                                           <table border="0">
                                               
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $DnyBarvy1; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Pondělí:</td>
                                                          <td align="right"><b>&nbsp;<?php echo number_format($SUM_Po, 2, '.', '&nbsp;'); ?></b></td>           
                                                        </tr> 
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $DnyBarvy2; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Úterý: </td>
                                                          <td align="right"><b>&nbsp;<?php echo number_format($SUM_Ut, 2, '.', '&nbsp;'); ?></b></td>           
                                                        </tr>                                                         <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $DnyBarvy3; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Středa: </td>
                                                          <td align="right"><b>&nbsp;<?php echo number_format($SUM_St, 2, '.', '&nbsp;'); ?></b></td>           
                                                        </tr>
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $DnyBarvy4; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Čtvrtek: </td>
                                                          <td align="right"><b>&nbsp;<?php echo number_format($SUM_Ct, 2, '.', '&nbsp;'); ?></b></td>           
                                                        </tr>
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $DnyBarvy5; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Pátek: </td>
                                                          <td align="right"><b>&nbsp;<?php echo number_format($SUM_Pa, 2, '.', '&nbsp;'); ?></b></td>           
                                                        </tr>
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $DnyBarvy6; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Sobota: </td>
                                                          <td align="right"><b>&nbsp;<?php echo number_format($SUM_So, 2, '.', '&nbsp;'); ?></b></td>           
                                                        </tr>
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $DnyBarvy7; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Neděle: </td>
                                                          <td align="right"><b>&nbsp;<?php echo number_format($SUM_Ne, 2, '.', '&nbsp;'); ?></b></td>           
                                                        </tr>
                                               
                                           </table>
                                        
                                  </div>
                                <div>
                                  <div class="col-lg-7" align="center">
                                    <!-- <canvas id="doughnut1" height="250"></canvas> -->
                                        <canvas id="chart-area3" height="250"></canvas>
                                  </div>
                                </div>
                                 
                                
                              </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    
                   
                    
                    <?php
                    //   }
                    ?>
                    
                  
                 
                 
          </div>
                
                
                 <div class="row">
                   <div class="col-md-8">
                    <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Počet účtenek v jednotlivých dnech</b></font></h3>
                                <div class="actions pull-right">
                                    
                                    <a data-hs-period="month-prev" href="<?php echo htmlspecialchars($hs_period_links['month-prev'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-left"></i></a>
                                     <span><?php echo $aMesice[$_SESSION["SQL_Mesic"]-1]; ?></span>
                                    <a data-hs-period="month-next" href="<?php echo htmlspecialchars($hs_period_links['month-next'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-right"></i></a>
                                    
                                    
                                    <a data-hs-period="year-prev" href="<?php echo htmlspecialchars($hs_period_links['year-prev'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-left"></i></a>
                                     <span><?php echo $SQL_ROK; ?></span>
                                    <a data-hs-period="year-next" href="<?php echo htmlspecialchars($hs_period_links['year-next'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-chevron-right"></i></a>
                                     
                                    
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                 
                                </div>
                                
                            </div>
                            <div class="panel-body text-center">
                                <?php
                                   if ($sw_id==0) {
                                      $mesicCNTUctenky= 10;
                                   }

                                   
                                   if (true) { // V113: render the timeline even when the period is empty.
                                   
                                   ?>
                                    <div>
                                        <canvas id="canvas1" height="70"></canvas>
                                        <!-- <canvas id="line1" height="50"></canvas> -->
                                    </div>
                                   
                                   <?php  
                                   }
                                 ?>
                                
                                
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                    <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Účtenky podle oblsuhy</b></font></h3>
                                <div class="actions pull-right">
                                    
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                 
                                </div>
                                
                            </div>
                            <div class="panel-body">
                                <?php
                                                                                                          
                                   if ($mesicCNTUctenky==0 and $sw_id!=0) {
                                       // V113: the chart itself shows the zero state.
                                   }else {
                                   ?>
                                     <div class="col-lg-5" >

                                           <table border="0">
                                                <?php

                                                    //DEMO DATA
                                                    if ($sw_id==0) {
                                                      $mesicCNTUctenky = 5;
                                                      $UctenekCelkem[1] = rand(1,100);
                                                      $UctenekCelkem[2] = rand(1,100);
                                                      $UctenekCelkem[3] = rand(1,100);
                                                      $UctenekCelkem[4] = rand(1,100);
                                                      $UctenekCelkem[5] = rand(1,100);

                                                      $JmenoObsluhyUctenky[1]="Obsluha A";
                                                      $JmenoObsluhyUctenky[2]="Obsluha B";
                                                      $JmenoObsluhyUctenky[3]="Obsluha C";
                                                      $JmenoObsluhyUctenky[4]="Obsluha D";
                                                      $JmenoObsluhyUctenky[5]="Obsluha E";

                                                      $BarvaObsluhyUctenky[1]=$BarvaObsluhy[1];
                                                      $BarvaObsluhyUctenky[2]=$BarvaObsluhy[2];
                                                      $BarvaObsluhyUctenky[3]=$BarvaObsluhy[3];
                                                      $BarvaObsluhyUctenky[4]=$BarvaObsluhy[4];
                                                      $BarvaObsluhyUctenky[5]=$BarvaObsluhy[5];

                                                    }

                                                   for ($i = 0; $i <= $mesicCNTUctenky; $i++) {
                                                      if ($UctenekCelkem[$i]> 0) {

                                                         if ($sw_id==0) {
                                                            $PrijmeniObsluhy = $JmenoObsluhyUctenky[$i];
                                                         }else {
                                                            $PrijmeniObsluhy = VratPouzePrijmeni($JmenoObsluhyUctenky[$i]);
                                                         }


                                                      echo "<tr>";
                                                      echo "<td><div style=\"width: 15px; height: 15px; background: #$BarvaObsluhyUctenky[$i]; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;\"></div></td>";
                                                      echo "<td>&nbsp;$PrijmeniObsluhy</td>";
                                                      echo "<td align=\"right\">&nbsp;&nbsp;<b>$UctenekCelkem[$i]</b></td>";
                                                      echo"</tr>";
                                                      }
                                                   }
                                                ?>
                                           </table>


                                </div>
                                <div>
                                 <div class="col-lg-7" align="center">
                                        <canvas height="250" id="chart-area4"></canvas>
                                        <!-- <canvas id="line1" height="50"></canvas> -->
                                    </div>
                                   </div>
                                   <?php  
                                   }
                                 ?>
                                
                                
                            </div>
                        </div>
                    </div>



                    
                    
                 </div>



                 <div class="row">
                      <?php
                         // Tržby dle typu platby
                        /*
                        DATA
                        */


                         //Dotaz na pocet a jmena stredisek
                         $sqldotaz_strediska= "SELECT `trzby_typy_stredisko` FROM `trzby_typy` WHERE `trzby_typy_sw_id` = $sw_id and MONTH(`trzby_typy_datum`) = '$SQL_Mesic' and YEAR(`trzby_typy_datum`) = '$SQL_ROK' group by `trzby_typy_stredisko` order by trzby_typy_id ASC" ;

                        /* klonovani dat na testy 
                        $sqldotaz_strediska= "          SELECT t.trzby_typy_stredisko
                                                        FROM (
                                                            SELECT trzby_typy_stredisko
                                                            FROM trzby_typy
                                                            WHERE trzby_typy_sw_id = $sw_id
                                                              AND MONTH(trzby_typy_datum) = $SQL_Mesic
                                                              AND YEAR(trzby_typy_datum) = $SQL_ROK
                                                            GROUP BY trzby_typy_stredisko
                                                        ) t
                                                        CROSS JOIN (
                                                            SELECT 1 AS n
                                                            UNION ALL SELECT 2
                                                            UNION ALL SELECT 3
                                                            UNION ALL SELECT 4
                                                        ) x
                                                        ORDER BY t.trzby_typy_stredisko ASC";
                                                        */


                         //echo $sqldotaz_strediska;
                         if (!$sqldotaz_strediska) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                         $vysledek_strediska=$mysqli->query("$sqldotaz_strediska");
                         $pocet_radku_strediska = $vysledek_strediska->num_rows;                                       

                         //echo $pocet_radku_strediska;
                           
                            if ($pocet_radku_strediska==0 and $sw_id!=0) {
                                //DEMO data
                            }else{
                                //smycka na nacteni stredisek 
                                 $CNT_PocetStredisekTypPlatby =0;


                                           while ($DataStrediska=MySQLi_Fetch_Array($vysledek_strediska)):   
                                                 $JmenoStrediskaTypPlatby[$CNT_PocetStredisekTypPlatby] = $DataStrediska["trzby_typy_stredisko"];
                                                 
                                                 //$TabBarvaUctenky = random_color(); 
                                                 //$BarvaObsluhyUctenky[$mesicCNTUctenky] = $TabBarvaUctenky ; 
                                                 //$BarvaObsluhyPozadiUctenky[$mesicCNTUctenky] = random_no_hex_color() ;
                                                 
                                                 $CNT_PocetStredisekTypPlatby = $CNT_PocetStredisekTypPlatby + 1;
                                           endwhile;
                            }







                                                                    
                                        /*
                                         * Všech 20 sad musí existovat i tehdy, když je středisek méně.
                                         * Původní continue přeskočilo inicializaci a následný count()
                                         * v PHP 8.3 ukončil celou stránku TypeErrorem.
                                         */
                                        for ($s = 0; $s < 20; $s++) {
                                            ${"Typ_TypPlatby".$s} = array();
                                            ${"Castka_TypPlatby".$s} = array();
                                            ${"BarvaTypu".$s} = array();
                                        }

                                        for ($s = 0; $s < 20; $s++) {

                                            if ($pocet_radku_strediska < ($s + 1)) {
                                                continue;
                                            }

                                            // SQL dotaz
                                            $sqldotaz_strediska_typ = "
                                                SELECT
                                                    trzby_typy_typ AS typ,
                                                    SUM(trzby_typy_castkaDPH) AS castka
                                                FROM trzby_typy
                                                WHERE trzby_typy_sw_id = $sw_id
                                                  AND MONTH(trzby_typy_datum) = '$SQL_Mesic'
                                                  AND YEAR(trzby_typy_datum) = '$SQL_ROK'
                                                  AND trzby_typy_stredisko = '".$JmenoStrediskaTypPlatby[$s]."'
                                                GROUP BY trzby_typy_typ
                                                ORDER BY trzby_typy_id ASC
                                            ";

                                            $vysledek = $mysqli->query($sqldotaz_strediska_typ);
                                            $pocet = $vysledek->num_rows;

                                             // inicializace číslovaných proměnných
                                            ${"Typ_TypPlatby".$s} = [];
                                            ${"Castka_TypPlatby".$s} = [];
                                            ${"BarvaTypu".$s} = [];

                                            if ($pocet > 0) {
                                                while ($row = mysqli_fetch_array($vysledek)) {
                                                    ${"Typ_TypPlatby".$s}[]    = $row["typ"];
                                                    ${"Castka_TypPlatby".$s}[] = $row["castka"];
                                                    ${"BarvaTypu".$s}[]        = random_color();
                                                }
                                            }
                                        ?>
                                            <div class="col-lg-6">
                                                <div class="panel panel-default">
                                                    <div class="panel-heading">
                                                        <h3 class="panel-title"><b>Tržby dle typu platby</b></h3>
                                                    </div>

                                                    <div class="panel-body">
                                                        <div class="row">

                                                            <div class="col-lg-5">
                                                                Firma: <b><?= $JmenoStrediskaTypPlatby[$s] ?></b><br><br>

                                                                <table border="0">
                                                                    <?php
                                                                    $CNT_PocetStredisekTypPlatby = count(${"Typ_TypPlatby".$s});
                                                                    for ($i = 0; $i < $CNT_PocetStredisekTypPlatby; $i++) {

                                                                        $castka = number_format(${"Castka_TypPlatby".$s}[$i], 2, '.', '&nbsp;');
                                                                        $typ    = ${"Typ_TypPlatby".$s}[$i];
                                                                        $barva  = ${"BarvaTypu".$s}[$i];

                                                                        echo "<tr>";
                                                                        echo "<td><div style='width:15px;height:15px;background:#{$barva};border-radius:50px;'></div></td>";
                                                                        echo "<td>&nbsp;{$typ}</td>";
                                                                        echo "<td align='right'>&nbsp;&nbsp;<b>{$castka}</b></td>";
                                                                        echo "</tr>";
                                                                    }
                                                                    ?>
                                                                    </table>
                                                            </div>

                                                            <div class="col-lg-7" align="center">
                                                                <?php 
                                                                    //echo $s + 4; 
                                                                    $chartNmb = $s + 5; 
                                                                    echo "<canvas height='250' id='chart-area". $chartNmb . "'></canvas>";
                                                                ?>
                                                                
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        }


                             


                            


                      ?>
                      </div>
                      
                














                          <?php
    if ( $sw_id!=0) {

  ?>                      
                        
                        
                                 <div class="row hs-monthly-options-row">
                                      <div class="col-md-12 col-lg-12">
                                        <div class="panel panel-default hs-monthly-card hs-monthly-options-card">
<div class="panel-heading"><h3 class="panel-title">Správa dat</h3></div>
                                          <div class="panel-body"><p class="hs-monthly-options-intro">Tyto akce ovlivňují data vybraného měsíce.</p>
                                             <?php
                                               $originalDate = $datum;
                                               $newDate = date("d.m.Y", strtotime($originalDate));

                                               $newYear = date("Y", strtotime($originalDate));

                                             ?>


                                                  <?php
                                                              //Schovat tl.refresh pokud je v db pozadavek na den
                                                              $radku_existuje_pozadavek = 0;
                                                              $SQL_mesic =$_SESSION["SQL_Mesic"];
                                                              $sql_existuje_pozadavek= "SELECT count(*) as CNT_pozadavek FROM `trzby_pozadavky_mesice` WHERE `sw_id` = $sw_id and `trm_mesic` = '$SQL_mesic' and `trm_rok` = '$SQL_ROK'";
                                                              //echo $sql_existuje_pozadavek;

                                                              $vysledek_existuje_pozadavek=$mysqli->query($sql_existuje_pozadavek);
                                                              //$radku_existuje_pozadavek=$vysledek_existuje_pozadavek->num_rows;
                                                              $sql_existuje_pozadavek=MySQLi_Fetch_Array($vysledek_existuje_pozadavek);

                                                              if ( $sql_existuje_pozadavek["CNT_pozadavek"]>0  ) {
                                                                 ?>
                                                                    <div class="alert alert-success alert-dismissable">
                                                                      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                                                      <!-- <strong>Informace </strong> -->Váš požadavek na refresh dat pro tento měsíc a rok byl zadán
                                                                    </div>
                                                                <?php
                                                              }




                                                     if ( $sql_existuje_pozadavek["CNT_pozadavek"]==0 ) {
                                                  ?>
                                                              <div class="rada hs-monthly-action-block">
                                                                 <form class="hs-monthly-action-form" action="../str/akce/MesicniTrzby.php" method="POST" >
                                                                  <input type="hidden" class="form-control" name="sw_id" value="<?php echo $sw_id; ?>">
                                                                  <input type="hidden" class="form-control" name="RefreshMesicData" value="1">
                                                                  <input type="hidden" class="form-control" name="RefreshRok" value="<?php echo $SQL_ROK; ?>">
                                                                  <input type="hidden" class="form-control" name="RefreshMesic" value="<?php echo $_SESSION["SQL_Mesic"]; ?>">
                                                                  <button type="submit" class="btn btn-info hs-monthly-action hs-monthly-action--refresh"><i class="fa icon-refresh"></i>Refresh dat pro tento měsíc</button>
                                                                </form>
                                                              </div>
                                                  <?php
                                                      }
                                                  ?>

                                                  <div class="rada hs-monthly-action-block">
                                                    <form class="hs-monthly-action-form" action="../str/akce/MesicniTrzby.php" method="POST" >
                                                      <input type="hidden" class="form-control" name="sw_id" value="<?php echo $sw_id; ?>">
                                                      <input type="hidden" class="form-control" name="DeleteMesicData" value="1">
                                                      <input type="hidden" class="form-control" name="DeleteRok" value="<?php echo $SQL_ROK; ?>">
                                                      <input type="hidden" class="form-control" name="DeleteMesic" value="<?php echo $_SESSION["SQL_Mesic"]; ?>">
                                                      <button type="submit" onClick="if(!confirm('Opravdu chcete SMAZAT data středisek a jednotlivců pro tento měsíc?')){return false;}" class="btn btn-danger hs-monthly-action hs-monthly-action--delete"><i class="fa icon-trash"></i>Smazat data pro tento měsíc</button>
                                                    </form>
                                                  </div>










                                             <!-- <font size="-2">Synchronizováno: <b>12:45</b></font> -->


                                          </div>
                                        </div>
                                      </div>
                                  </div>
   <?php
     }
   ?>
<?php
                        
                        
                        //posledni zaznam v tabulce
                        $sql_PosledniAktualizace= "SELECT `tj_Datum` FROM `trzby_jednotlivci_$SQL_ROK` WHERE `sw_id` = $sw_id order by tj_Datum Desc LIMIT 1";
                        //echo $sql_PosledniAktualizace;
                        $vysledek_sql_PosledniAktualizace=$mysqli->query($sql_PosledniAktualizace);
                        $data_sql_PosledniAktualizace=MySQLi_Fetch_Array($vysledek_sql_PosledniAktualizace);
                        //$CelkoveTrzby = number_format($data_sql_PosledniAktualizace["TrzbyCelkem"], 2, '.', '&nbsp;');
                        $PosledniAktualizace = $data_sql_PosledniAktualizace["tj_Datum"];
                        
                        if ($PosledniAktualizace=="") {
                          $PosledniAktualizace = " nezjištěno ";
                        }
                          
                         
                        
                           if (strtotime($PosledniAktualizace)!="") {
                        ?>
                                                                  <div class="row">
                                                                  <div class="col-md-12">
                                                                      <div class="panel panel-default">
                                                                          <div class="panel-body">
                                                                              <font size="-2">Synchronizováno: 
                                                                                  <b>
                                                                                                                              <?php 
                                                                                                                                
                                                                                                                                $newDate_stav1 = date("d.m.Y H:i", strtotime($PosledniAktualizace));
                                                                                                                                echo $newDate_stav1 ; 
                                                                                                                              
                                                                                                                                $DURATION_end=microtime(true);
                                                                                                                                $DURATION = $DURATION_end - $DURATION_start;
                                        
                                                                                                                              ?>
                                                                                                                          
                                                                                  </b>
                                                                                - Automatické načtení dat probíhá každých 15 minut.&nbsp; Strana načtena za <b><?php echo round($DURATION,3)."s"; ?></b> 
                                                                              </font> 
                                                                          </div>
                                                                      </div>
                                                                  </div>
                                                              </div>
                          
                        <?php
                          }
                        ?>     
           
                
                           
                        
                                                                                         


<?php


//Graf uctenky

    $data2=""; 
     for ($i = 0; $i <= count($JmenoObsluhyUctenky)-1; $i++) {
    
           if ($i == count($JmenoObsluhyUctenky)) {
             $carka2 = "";
           }else {
             $carka2 = ",";
           }
    
       $data2= $data2 . "{ ";
       $data2= $data2 . "label: \"".$JmenoObsluhyUctenky[$i]."\",";
       $data2= $data2 . "backgroundColor: \"rgba(".$BarvaObsluhyPozadiUctenky[$i].",0.1)\",";
       $data2= $data2 . "borderColor: \"#".$BarvaObsluhyUctenky[$i]."\",";
       $data2= $data2 . "pointBorderColor: \"#".$BarvaObsluhyUctenky[$i]."\",";
       $data2= $data2 . "pointBackgroundColor: \"#".$BarvaObsluhyUctenky[$i]."\",";
       $data2= $data2 . "pointBorderWidth: 1,";
       $data2= $data2 . "data: [";
       
       
       
         switch ($PocetDnuVMesici) {
                                      case 28: 
                                           $data2= $data2 .  $MesicDataUctenky1[$i].",".$MesicDataUctenky2[$i].",".$MesicDataUctenky3[$i].",".$MesicDataUctenky4[$i].",".$MesicDataUctenky5[$i].",".$MesicDataUctenky6[$i].",".$MesicDataUctenky7[$i].",".$MesicDataUctenky8[$i].",".$MesicDataUctenky9[$i].",".$MesicDataUctenky10[$i].",".$MesicDataUctenky11[$i].",".$MesicDataUctenky12[$i].",".$MesicDataUctenky13[$i].",".$MesicDataUctenky14[$i].",".$MesicDataUctenky15[$i].",".$MesicDataUctenky16[$i].",".$MesicDataUctenky17[$i].",".$MesicDataUctenky18[$i].",".$MesicDataUctenky19[$i].",".$MesicDataUctenky20[$i].",".$MesicDataUctenky21[$i].",".$MesicDataUctenky22[$i].",".$MesicDataUctenky23[$i].",".$MesicDataUctenky24[$i].",".$MesicDataUctenky25[$i].",".$MesicDataUctenky26[$i].",".$MesicDataUctenky27[$i].",".$MesicDataUctenky28[$i];
                                        break;
                                      case 29: 
                                           $data2= $data2 .  $MesicDataUctenky1[$i].",".$MesicDataUctenky2[$i].",".$MesicDataUctenky3[$i].",".$MesicDataUctenky4[$i].",".$MesicDataUctenky5[$i].",".$MesicDataUctenky6[$i].",".$MesicDataUctenky7[$i].",".$MesicDataUctenky8[$i].",".$MesicDataUctenky9[$i].",".$MesicDataUctenky10[$i].",".$MesicDataUctenky11[$i].",".$MesicDataUctenky12[$i].",".$MesicDataUctenky13[$i].",".$MesicDataUctenky14[$i].",".$MesicDataUctenky15[$i].",".$MesicDataUctenky16[$i].",".$MesicDataUctenky17[$i].",".$MesicDataUctenky18[$i].",".$MesicDataUctenky19[$i].",".$MesicDataUctenky20[$i].",".$MesicDataUctenky21[$i].",".$MesicDataUctenky22[$i].",".$MesicDataUctenky23[$i].",".$MesicDataUctenky24[$i].",".$MesicDataUctenky25[$i].",".$MesicDataUctenky26[$i].",".$MesicDataUctenky27[$i].",".$MesicDataUctenky28[$i].",".$MesicDataUctenky29[$i];
                                        break;
                                      case 30: 
                                           $data2= $data2 .  $MesicDataUctenky1[$i].",".$MesicDataUctenky2[$i].",".$MesicDataUctenky3[$i].",".$MesicDataUctenky4[$i].",".$MesicDataUctenky5[$i].",".$MesicDataUctenky6[$i].",".$MesicDataUctenky7[$i].",".$MesicDataUctenky8[$i].",".$MesicDataUctenky9[$i].",".$MesicDataUctenky10[$i].",".$MesicDataUctenky11[$i].",".$MesicDataUctenky12[$i].",".$MesicDataUctenky13[$i].",".$MesicDataUctenky14[$i].",".$MesicDataUctenky15[$i].",".$MesicDataUctenky16[$i].",".$MesicDataUctenky17[$i].",".$MesicDataUctenky18[$i].",".$MesicDataUctenky19[$i].",".$MesicDataUctenky20[$i].",".$MesicDataUctenky21[$i].",".$MesicDataUctenky22[$i].",".$MesicDataUctenky23[$i].",".$MesicDataUctenky24[$i].",".$MesicDataUctenky25[$i].",".$MesicDataUctenky26[$i].",".$MesicDataUctenky27[$i].",".$MesicDataUctenky28[$i].",".$MesicDataUctenky29[$i].",".$MesicDataUctenky30[$i];
                                        break;
                                      case 31: 
                                           $data2= $data2 .  $MesicDataUctenky1[$i].",".$MesicDataUctenky2[$i].",".$MesicDataUctenky3[$i].",".$MesicDataUctenky4[$i].",".$MesicDataUctenky5[$i].",".$MesicDataUctenky6[$i].",".$MesicDataUctenky7[$i].",".$MesicDataUctenky8[$i].",".$MesicDataUctenky9[$i].",".$MesicDataUctenky10[$i].",".$MesicDataUctenky11[$i].",".$MesicDataUctenky12[$i].",".$MesicDataUctenky13[$i].",".$MesicDataUctenky14[$i].",".$MesicDataUctenky15[$i].",".$MesicDataUctenky16[$i].",".$MesicDataUctenky17[$i].",".$MesicDataUctenky18[$i].",".$MesicDataUctenky19[$i].",".$MesicDataUctenky20[$i].",".$MesicDataUctenky21[$i].",".$MesicDataUctenky22[$i].",".$MesicDataUctenky23[$i].",".$MesicDataUctenky24[$i].",".$MesicDataUctenky25[$i].",".$MesicDataUctenky26[$i].",".$MesicDataUctenky27[$i].",".$MesicDataUctenky28[$i].",".$MesicDataUctenky29[$i].",".$MesicDataUctenky30[$i].",".$MesicDataUctenky31[$i];
                                        break;
                                      
                                      default: 
                                           //
                                        break;  
                                         
         }
              
       $data2= $data2 . "]";
       $data2= $data2 . "} ";
       
       if (count($JmenoObsluhyUctenky)>1) {
        $data2= $data2 . $carka2;  
       }else{
        $data2= $data2 ;
       }

       
        
     }

 
                                                 

  
       //SLUZBY
       $data1=""; 
       $data1= $data1 . "{ ";
       $data1= $data1 . "label: 'Služby',";
       $data1= $data1 . "backgroundColor: \"#".random_color()."\",";
       $data1= $data1 . "data: [";
        for ($s = 0; $s < count($SluzbyMesic); $s++) {
            if ($s==count($SluzbyMesic)-1) {
              $DataSluzby = $DataSluzby . $SluzbyMesic[$s];
            }else {
              $DataSluzby = $DataSluzby . $SluzbyMesic[$s].",";   
            }
        }     
       $data1= $data1 .  $DataSluzby;
       $data1= $data1 . "]";
       $data1= $data1 . "},";
       
       //PRODEJ
       $data1= $data1 . "{";
       $data1= $data1 . "label: 'Prodej',";
       $data1= $data1 . "backgroundColor: \"#".random_color()."\",";
       $data1= $data1 . "data: [";
        for ($p = 0; $p < count($ProdejMesic); $p++) {
            if ($p==count($ProdejMesic)-1) {
              $DataProdej = $DataProdej . $ProdejMesic[$p];
            }else {
              $DataProdej = $DataProdej . $ProdejMesic[$p].",";   
            }
        }     
       $data1= $data1 .  $DataProdej;
       $data1= $data1 . "]";
       $data1= $data1 . "}, ";
       
       //CENINY
       $data1= $data1 . "{";
       $data1= $data1 . "label: 'Ceniny',";
       $data1= $data1 . "backgroundColor: \"#".random_color()."\",";
       $data1= $data1 . "data: [";
        for ($p = 0; $p < count($CeninyMesic); $p++) {
            if ($p==count($CeninyMesic)-1) {
              $DataCeniny = $DataCeniny . $CeninyMesic[$p];
            }else {
              $DataCeniny = $DataCeniny . $CeninyMesic[$p].",";   
            }
        }     
       $data1= $data1 .  $DataCeniny;
       $data1= $data1 . "]";
       $data1= $data1 . "}, ";
       
       //KREDIT
       $data1= $data1 . "{";
       $data1= $data1 . "label: 'Kredit',";
       $data1= $data1 . "backgroundColor: \"#".random_color()."\",";
       $data1= $data1 . "data: [";
        for ($p = 0; $p < count($KreditMesic); $p++) {
            if ($p==count($KreditMesic)-1) {
              $DataKredit = $DataKredit . $KreditMesic[$p];
            }else {
              $DataKredit = $DataKredit . $KreditMesic[$p].",";   
            }
        }     
       $data1= $data1 .  $DataKredit;
       $data1= $data1 . "]";
       $data1= $data1 . "} ";


      /* ----------------------
          Typ platby - DataJS
                          
                                      data: [ 1,107,154,140,166,33, ],
                                      backgroundColor: ['#e0f197','#551459','#3f33a4','#292929','#766c6f','#4a8789','#'],
                                      labels: ['Bednář Zdeněk','Dohnalová Květa','Lukšová Zuzana','Machková Iva','Račická Veronika','Voucher','']

                                      
      */

                                      
                                      /*Typplatby stredisko 1 */
                                     for ($s = 0; $s < 20; $s++) {  // typy 1–20, index 0–19

                                            // inicializace proměnných
                                            ${"typplatby_suma".$s}   = "";
                                            ${"typplatby_barva".$s}  = "";
                                            ${"typplatby_labely".$s} = "";

                                            $count = count(${"Typ_TypPlatby".$s});  // počet položek pro daný typ

                                            for ($p = 0; $p < $count; $p++) {

                                                $last = ($p === $count - 1);

                                                // sčítání hodnot, generování číselných polí a řetězců pro JS
                                                ${"typplatby_suma".$s}   .= ${"Castka_TypPlatby".$s}[$p] . ($last ? "" : ",");
                                                ${"typplatby_barva".$s}  .= "'#".${"BarvaTypu".$s}[$p]."'" . ($last ? "" : ",");
                                                ${"typplatby_labely".$s} .= "'".${"Typ_TypPlatby".$s}[$p]."'" . ($last ? "" : ",");
                                            }
                                        }

                                      //echo "****".$typplatby_labely0;    
  
?>

                     
           
  </section>        
</section>
                                  
             <?php
                //DEMO DATA
                  if ($sw_id==0) {
                     //Sloupcový 1
                     $data1 = "{ label: 'Služby',backgroundColor: \"#f574d9\",data: [".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000).",".rand(1000,20000)."]},{label: 'Prodej',backgroundColor: \"#263c46\",data: [".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000).",".rand(1000,10000)."]}";

                     //Kolacovy uctenky
                     $PopiskyBarvy3 = $PopiskyBarvy1;
                     $PopiskyObsluha3 = $PopiskyObsluha2;
                     $PopiskySluzby3 = "$UctenekCelkem[1],$UctenekCelkem[2],$UctenekCelkem[3],$UctenekCelkem[4],$UctenekCelkem[5]";

                     //line graf
                     $data2 = "{ label: \"Obsluha A\",backgroundColor: \"rgba(172,85,50,0.1)\",borderColor: \"#8c8f49\",pointBorderColor: \"#8c8f49\",pointBackgroundColor: \"#8c8f49\",pointBorderWidth: 1,data: [".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20)."]},
                               { label: \"Obsluha B\",backgroundColor: \"rgba(245,15,52,0.1)\",borderColor: \"#66b34c\",pointBorderColor: \"#66b34c\",pointBackgroundColor: \"#66b34c\",pointBorderWidth: 1,data: [".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20)."]},
                               { label: \"Obsluha C\",backgroundColor: \"rgba(84,179,240,0.1)\",borderColor: \"#aaaf9e\",pointBorderColor: \"#aaaf9e\",pointBackgroundColor: \"#aaaf9e\",pointBorderWidth: 1,data: [".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20)."]},
                               { label: \"Obsluha D\",backgroundColor: \"rgba(130,136,130,0.1)\",borderColor: \"#4112dc\",pointBorderColor: \"#4112dc\",pointBackgroundColor: \"#4112dc\",pointBorderWidth: 1,data: [".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20)."]},
                               { label: \"Obsluha E\",backgroundColor: \"rgba(106,197,134,0.1)\",borderColor: \"#f609cb\",pointBorderColor: \"#f609cb\",pointBackgroundColor: \"#f609cb\",pointBorderWidth: 1,data: [".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20).",".rand(0,20)."]}";
                   }


             ?>              
                                                    

<!-- echo random_color(); -->
 <script type="application/x-hairsoft-legacy-monthly-charts">
          
        //sloupcovy graf suma
        var barChartData = {
            labels: [<?php echo $datax; ?>],
            datasets: [ <?php echo $data1; ?> ]
        };
          
          
           var config1 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                                                 data: [ <?php echo $PopiskySluzby1; ?>],
                                      backgroundColor: [ <?php echo $PopiskyBarvy1; ?>],
                                      label: 'Dataset 1'
                                  }],
                                               labels: [ <?php echo $PopiskyObsluha1; ?>]
                    },
                    
                    options: {
                        responsive: true,
                        legend: {
                            display: false,
                            position: 'top',
                        },
                        title: {
                            display: false,
                            text: 'Chart.js Doughnut Chart'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };
                
                
                var config2 = {
                    type: 'doughnut',
                    data: {
                       datasets: [{
                                                 data: [ <?php echo $PopiskySluzby2; ?>],
                                      backgroundColor: [<?php echo $PopiskyBarvy2; ?>],
                                      label: 'Dataset 1'
                                  }],
                                               labels: [<?php echo $PopiskyObsluha2; ?>]
                    },
                    
                    options: {
                        responsive: true,
                        legend: {
                            display: false,
                            position: 'top',
                        },
                        title: {
                            display: false,
                            text: 'Chart.js Doughnut Chart'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };
                


                var config3 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                                      data: [ <?php echo $DnySluzby; ?> ],
                                      backgroundColor: [<?php echo $DnyBarvy; ?>],
                                      label: 'Dataset 1'
                                  }],
                          labels: [<?php echo $DnyPopisky; ?>]
                    },
                    
                    options: {
                        responsive: true,
                        legend: {
                            display: false,
                            position: 'top',
                        },
                        title: {
                            display: false,
                            text: 'Chart.js Doughnut Chart'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };
                
                                                      
                var config5 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                                      data: [ <?php echo $PopiskySluzby3; ?> ],
                                      backgroundColor: [<?php echo $PopiskyBarvy3; ?>],
                                      label: 'Dataset 1'
                                  }],
                          labels: [<?php echo $PopiskyObsluha3; ?>]
                    },
                    
                    options: {
                        responsive: true,
                        legend: {
                            display: false,
                            position: 'top',
                        },
                        title: {
                            display: false,
                            text: 'Chart.js Doughnut Chart'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };

                

                //typplatby 1
                var config6 = {
                    type: 'doughnut',
                    data: {
                        /*
                          
                                      data: [ 1,107,154,140,166,33, ],
                                      backgroundColor: ['#e0f197','#551459','#3f33a4','#292929','#766c6f','#4a8789','#'],
                                      labels: ['Bednář Zdeněk','Dohnalová Květa','Lukšová Zuzana','Machková Iva','Račická Veronika','Voucher','']
                                      typplatby_suma
                                      typplatby_barva
                                      typplatby_labely

                        */
                        datasets: [{
                                      data: [ <?php echo $typplatby_suma0; ?> ],
                                      backgroundColor: [<?php echo $typplatby_barva0; ?>],
                                      label: 'Dataset 1'
                                  }],
                          labels: [<?php echo $typplatby_labely0; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: {
                            display: false,
                            position: 'top',
                        },
                        title: {
                            display: false,
                            text: 'Chart.js Doughnut Chart'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };

                 //typplatby 2
                var config7 = {
                    type: 'doughnut',
                    data: {
                        /*
                          
                                      data: [ 1,107,154,140,166,33, ],
                                      backgroundColor: ['#e0f197','#551459','#3f33a4','#292929','#766c6f','#4a8789','#'],
                                      labels: ['Bednář Zdeněk','Dohnalová Květa','Lukšová Zuzana','Machková Iva','Račická Veronika','Voucher','']
                                      typplatby_suma
                                      typplatby_barva
                                      typplatby_labely

                        */
                        datasets: [{
                                      data: [ <?php echo $typplatby_suma1; ?> ],
                                      backgroundColor: [<?php echo $typplatby_barva1; ?>],
                                      label: 'Dataset 1'
                                  }],
                          labels: [<?php echo $typplatby_labely1; ?>]
                    },
                    
                    options: {
                        responsive: true,
                        legend: {
                            display: false,
                            position: 'top',
                        },
                        title: {
                            display: false,
                            text: 'Chart.js Doughnut Chart'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };

                 //typplatby 3
                var config8 = {
                    type: 'doughnut',
                    data: {
                        /*
                          
                                      data: [ 1,107,154,140,166,33, ],
                                      backgroundColor: ['#e0f197','#551459','#3f33a4','#292929','#766c6f','#4a8789','#'],
                                      labels: ['Bednář Zdeněk','Dohnalová Květa','Lukšová Zuzana','Machková Iva','Račická Veronika','Voucher','']
                                      typplatby_suma
                                      typplatby_barva
                                      typplatby_labely

                        */
                        datasets: [{
                                      data: [ <?php echo $typplatby_suma2; ?> ],
                                      backgroundColor: [<?php echo $typplatby_barva2; ?>],
                                      label: 'Dataset 1'
                                  }],
                          labels: [<?php echo $typplatby_labely2; ?>]
                    },
                    
                    options: {
                        responsive: true,
                        legend: {
                            display: false,
                            position: 'top',
                        },
                        title: {
                            display: false,
                            text: 'Chart.js Doughnut Chart'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };

                 //typplatby 4
                var config9 = {
                    type: 'doughnut',
                    data: {
                        /*
                          
                                      data: [ 1,107,154,140,166,33, ],
                                      backgroundColor: ['#e0f197','#551459','#3f33a4','#292929','#766c6f','#4a8789','#'],
                                      labels: ['Bednář Zdeněk','Dohnalová Květa','Lukšová Zuzana','Machková Iva','Račická Veronika','Voucher','']
                                      typplatby_suma
                                      typplatby_barva
                                      typplatby_labely

                        */
                        datasets: [{
                                      data: [ <?php echo $typplatby_suma3; ?> ],
                                      backgroundColor: [<?php echo $typplatby_barva3; ?>],
                                      label: 'Dataset 1'
                                  }],
                          labels: [<?php echo $typplatby_labely3; ?>]
                    },
                    
                    options: {
                        responsive: true,
                        legend: {
                            display: false,
                            position: 'top',
                        },
                        title: {
                            display: false,
                            text: 'Chart.js Doughnut Chart'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };

                
                //typplatby 5
                var config10 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                                      data: [ <?php echo $typplatby_suma4; ?> ],
                                      backgroundColor: [<?php echo $typplatby_barva4; ?>],
                                      label: 'Dataset 1'
                                  }],
                          labels: [<?php echo $typplatby_labely4; ?>]
                    },
                    
                    options: {
                        responsive: true,
                        legend: {
                            display: false,
                            position: 'top',
                        },
                        title: {
                            display: false,
                            text: 'Chart.js Doughnut Chart'
                        },
                        animation: {
                            animateScale: true,
                            animateRotate: true
                        }
                    }
                };
                

                //typplatby 6
                var config11 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma5; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva5; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely5; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 7
                var config12 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma6; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva6; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely6; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 8
                var config13 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma7; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva7; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely7; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 9
                var config14 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma8; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva8; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely8; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 10
                var config15 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma9; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva9; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely9; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 11
                var config16 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma10; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva10; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely10; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 12
                var config17 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma11; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva11; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely11; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 13
                var config18 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma12; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva12; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely12; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 14
                var config19 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma13; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva13; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely13; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 15
                var config20 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma14; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva14; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely14; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 16
                var config21 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma15; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva15; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely15; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 17
                var config22 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma16; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva16; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely16; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 18
                var config23 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma17; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva17; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely17; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 19
                var config24 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma18; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva18; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely18; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };

                //typplatby 20
                var config25 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [ <?php echo $typplatby_suma19; ?> ],
                            backgroundColor: [<?php echo $typplatby_barva19; ?>],
                            label: 'Dataset 1'
                        }],
                        labels: [<?php echo $typplatby_labely19; ?>]
                    },
                    options: {
                        responsive: true,
                        legend: { display: false },
                        animation: { animateScale: true, animateRotate: true }
                    }
                };







                
         var config4 = {
            type: 'line',
            data: {
                labels: [<?php echo $datax; ?>],
                datasets: [<?php echo $data2; ?>]
            },
            options: {
                responsive: true,
                legend: {
                     display: false,
                    position: 'top',
                },
                title:{
                    display:false,
                    text:'Chart.js Line Chart'
                },
                tooltips: {
                    mode: 'label',
                    callbacks: {
                    }
                },
                hover: {
                    mode: 'dataset'
                },
            }
        };
                
                
          
          
          window.onload = function() {
            var ctx0 = document.getElementById("canvas").getContext("2d");
            
            var ctx1 = document.getElementById("chart-area1").getContext("2d");
            window.myDoughnut = new Chart(ctx1, config1);
            
            var ctx2 = document.getElementById("chart-area2").getContext("2d");
            window.myDoughnut = new Chart(ctx2, config2);
            
            var ctx3 = document.getElementById("chart-area3").getContext("2d");
            window.myDoughnut = new Chart(ctx3, config3);
            
            var ctx4 = document.getElementById("chart-area4").getContext("2d");
            window.myDoughnut = new Chart(ctx4, config5);

            
                                   
             window.myBar = new Chart(ctx0, {
                  type: 'bar',
                  data: barChartData,
                  options: {
                      elements: {
                          rectangle: {
                              borderWidth: 1,
                              borderColor: 'rgb(250, 192, 192)',
                              borderSkipped: 'bottom'
                          }
                      },
                      responsive: true,
                      legend: {
                          display: false,
                          position: 'top',
                      },
                      title: {
                          display: false,
                          text: 'Chart.js Bar Chart'
                      }
                  }
           });
           
           var ctx4 = document.getElementById("canvas1").getContext("2d");
            window.myLine = new Chart(ctx4, config4);
    
          

           //Typ platby 1
            var ctx5 = document.getElementById("chart-area5").getContext("2d");
            window.myDoughnut = new Chart(ctx5, config6);

            //Typ platby 2
            var ctx6 = document.getElementById("chart-area6").getContext("2d");
            window.myDoughnut = new Chart(ctx6, config7);

            //Typ platby 3
            var ctx7 = document.getElementById("chart-area7").getContext("2d");
            window.myDoughnut = new Chart(ctx7, config8);

            //Typ platby 4
            var ctx8 = document.getElementById("chart-area8").getContext("2d");
            window.myDoughnut = new Chart(ctx8, config9);

            //Typ platby 5
            var ctx9 = document.getElementById("chart-area9").getContext("2d");
            window.myDoughnut = new Chart(ctx9, config10);

            //Typ platby 6
            var ctx10 = document.getElementById("chart-area10").getContext("2d");
            window.myDoughnut = new Chart(ctx10, config11);

            //Typ platby 7
            var ctx11 = document.getElementById("chart-area11").getContext("2d");
            window.myDoughnut = new Chart(ctx11, config12);

            //Typ platby 8
            var ctx12 = document.getElementById("chart-area12").getContext("2d");
            window.myDoughnut = new Chart(ctx12, config13);

            //Typ platby 9
            var ctx13 = document.getElementById("chart-area13").getContext("2d");
            window.myDoughnut = new Chart(ctx13, config14);

            //Typ platby 10
            var ctx14 = document.getElementById("chart-area14").getContext("2d");
            window.myDoughnut = new Chart(ctx14, config15);

            //Typ platby 11
            var ctx15 = document.getElementById("chart-area15").getContext("2d");
            window.myDoughnut = new Chart(ctx15, config16);

            //Typ platby 12
            var ctx16 = document.getElementById("chart-area16").getContext("2d");
            window.myDoughnut = new Chart(ctx16, config17);

            //Typ platby 13
            var ctx17 = document.getElementById("chart-area17").getContext("2d");
            window.myDoughnut = new Chart(ctx17, config18);

            //Typ platby 14
            var ctx18 = document.getElementById("chart-area18").getContext("2d");
            window.myDoughnut = new Chart(ctx18, config19);

            //Typ platby 15
            var ctx19 = document.getElementById("chart-area19").getContext("2d");
            window.myDoughnut = new Chart(ctx19, config20);

            //Typ platby 16
            var ctx20 = document.getElementById("chart-area20").getContext("2d");
            window.myDoughnut = new Chart(ctx20, config21);

            //Typ platby 17
            var ctx21 = document.getElementById("chart-area21").getContext("2d");
            window.myDoughnut = new Chart(ctx21, config22);

            //Typ platby 18
            var ctx22 = document.getElementById("chart-area22").getContext("2d");
            window.myDoughnut = new Chart(ctx22, config23);

            //Typ platby 19
            var ctx23 = document.getElementById("chart-area23").getContext("2d");
            window.myDoughnut = new Chart(ctx23, config24);

            //Typ platby 20
            var ctx24 = document.getElementById("chart-area24").getContext("2d");
            window.myDoughnut = new Chart(ctx24, config25);


        };
            
    </script>

<?php
/*
 * Bezpečný datový payload grafů.
 * Původní výpočty a SQL zůstávají beze změny; zde se pouze existující PHP pole
 * převádějí přes json_encode místo ručního skládání JavaScriptových řetězců.
 */
$hs_chart_json_flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_NUMERIC_CHECK;

$hs_chart_values = static function ($values) {
    $result = array();
    if (!is_array($values)) {
        return $result;
    }
    foreach ($values as $value) {
        $result[] = is_numeric($value) ? (float) $value : 0;
    }
    return $result;
};

$hs_chart_color = static function ($value, $fallback) {
    $color = ltrim((string) $value, '#');
    return preg_match('/^[0-9a-fA-F]{6}$/', $color) ? '#'.$color : $fallback;
};

$hs_staff_labels = array();
$hs_staff_services = array();
$hs_staff_sales = array();
$hs_staff_colors = array();
$hs_staff_count = isset($JmenoObsluhy) && is_array($JmenoObsluhy) ? count($JmenoObsluhy) : 0;
for ($hs_i = 1; $hs_i <= $hs_staff_count; $hs_i++) {
    if (!isset($JmenoObsluhy[$hs_i])) {
        continue;
    }
    $hs_staff_labels[] = (string) $JmenoObsluhy[$hs_i];
    $hs_staff_services[] = isset($SluzbyObsluhy[$hs_i]) && is_numeric($SluzbyObsluhy[$hs_i]) ? (float) $SluzbyObsluhy[$hs_i] : 0;
    $hs_staff_sales[] = isset($ProdejObsluhy[$hs_i]) && is_numeric($ProdejObsluhy[$hs_i]) ? (float) $ProdejObsluhy[$hs_i] : 0;
    $hs_staff_colors[] = $hs_chart_color(isset($BarvaObsluhy[$hs_i]) ? $BarvaObsluhy[$hs_i] : '', '#17A7A2');
}

$hs_receipt_labels = array();
$hs_receipt_values = array();
$hs_receipt_colors = array();
$hs_receipt_keys = array();
$hs_receipt_count = isset($JmenoObsluhyUctenky) && is_array($JmenoObsluhyUctenky) ? count($JmenoObsluhyUctenky) : 0;
foreach ((isset($JmenoObsluhyUctenky) && is_array($JmenoObsluhyUctenky) ? $JmenoObsluhyUctenky : array()) as $hs_receipt_key => $hs_receipt_name) {
    $hs_receipt_keys[] = $hs_receipt_key;
    $hs_receipt_labels[] = (string) $hs_receipt_name;
    $hs_receipt_values[] = isset($UctenekCelkem[$hs_receipt_key]) && is_numeric($UctenekCelkem[$hs_receipt_key]) ? (float) $UctenekCelkem[$hs_receipt_key] : 0;
    $hs_receipt_colors[] = $hs_chart_color(isset($BarvaObsluhyUctenky[$hs_receipt_key]) ? $BarvaObsluhyUctenky[$hs_receipt_key] : '', '#526B91');
}

$hs_day_count = isset($PocetDnuVMesici) && is_numeric($PocetDnuVMesici) ? (int) $PocetDnuVMesici : 31;
$hs_day_labels = range(1, max(1, $hs_day_count));
$hs_line_datasets = array();
foreach ($hs_receipt_keys as $hs_i => $hs_receipt_key) {
    $hs_line_values = array();
    for ($hs_day = 1; $hs_day <= $hs_day_count; $hs_day++) {
        $hs_day_variable = 'MesicDataUctenky'.$hs_day;
        $hs_day_data = isset(${$hs_day_variable}) && is_array(${$hs_day_variable}) ? ${$hs_day_variable} : array();
        $hs_line_values[] = isset($hs_day_data[$hs_receipt_key]) && is_numeric($hs_day_data[$hs_receipt_key]) ? (float) $hs_day_data[$hs_receipt_key] : 0;
    }
    if (isset($sw_id) && (int) $sw_id === 0) {
        $hs_line_values = array();
        for ($hs_day = 1; $hs_day <= $hs_day_count; $hs_day++) {
            $hs_line_values[] = rand(0, 20);
        }
    }
    $hs_line_color = $hs_chart_color(isset($BarvaObsluhyUctenky[$hs_receipt_key]) ? $BarvaObsluhyUctenky[$hs_receipt_key] : '', '#17A7A2');
    $hs_line_datasets[] = array(
        'label' => (string) $JmenoObsluhyUctenky[$hs_receipt_key],
        'data' => $hs_line_values,
        'borderColor' => $hs_line_color,
        'backgroundColor' => 'rgba(23,167,162,0.10)',
        'pointBorderColor' => $hs_line_color,
        'pointBackgroundColor' => $hs_line_color,
        'pointBorderWidth' => 1
    );
}

$hs_payment_charts = array();
for ($hs_i = 0; $hs_i < 20; $hs_i++) {
    $hs_payment_labels_var = 'Typ_TypPlatby'.$hs_i;
    $hs_payment_values_var = 'Castka_TypPlatby'.$hs_i;
    $hs_payment_colors_var = 'BarvaTypu'.$hs_i;
    $hs_payment_labels = isset(${$hs_payment_labels_var}) && is_array(${$hs_payment_labels_var}) ? array_values(${$hs_payment_labels_var}) : array();
    $hs_payment_values = isset(${$hs_payment_values_var}) && is_array(${$hs_payment_values_var}) ? $hs_chart_values(${$hs_payment_values_var}) : array();
    $hs_payment_source_colors = isset(${$hs_payment_colors_var}) && is_array(${$hs_payment_colors_var}) ? array_values(${$hs_payment_colors_var}) : array();
    $hs_payment_colors = array();
    foreach ($hs_payment_labels as $hs_payment_index => $hs_payment_label) {
        $hs_payment_colors[] = $hs_chart_color(isset($hs_payment_source_colors[$hs_payment_index]) ? $hs_payment_source_colors[$hs_payment_index] : '', '#17A7A2');
    }
    $hs_payment_charts[] = array('labels' => $hs_payment_labels, 'values' => $hs_payment_values, 'colors' => $hs_payment_colors);
}

$hs_bar_services = $hs_chart_values(isset($SluzbyMesic) ? $SluzbyMesic : array());
$hs_bar_sales = $hs_chart_values(isset($ProdejMesic) ? $ProdejMesic : array());
$hs_bar_vouchers = $hs_chart_values(isset($CeninyMesic) ? $CeninyMesic : array());
$hs_bar_credit = $hs_chart_values(isset($KreditMesic) ? $KreditMesic : array());
if (isset($sw_id) && (int) $sw_id === 0) {
    $hs_bar_services = array();
    $hs_bar_sales = array();
    $hs_bar_vouchers = array_fill(0, $hs_day_count, 0);
    $hs_bar_credit = array_fill(0, $hs_day_count, 0);
    for ($hs_day = 1; $hs_day <= $hs_day_count; $hs_day++) {
        $hs_bar_services[] = rand(1000, 20000);
        $hs_bar_sales[] = rand(1000, 10000);
    }
}

$hs_monthly_chart_payload = array(
    'days' => $hs_day_labels,
    'bar' => array(
        'services' => $hs_bar_services,
        'sales' => $hs_bar_sales,
        'vouchers' => $hs_bar_vouchers,
        'credit' => $hs_bar_credit
    ),
    'services' => array('labels' => $hs_staff_labels, 'values' => $hs_staff_services, 'colors' => $hs_staff_colors),
    'sales' => array('labels' => $hs_staff_labels, 'values' => $hs_staff_sales, 'colors' => $hs_staff_colors),
    'weekdays' => array(
        'labels' => array('Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota', 'Neděle'),
        'values' => array(
            isset($SUM_Po) && is_numeric($SUM_Po) ? (float) $SUM_Po : 0,
            isset($SUM_Ut) && is_numeric($SUM_Ut) ? (float) $SUM_Ut : 0,
            isset($SUM_St) && is_numeric($SUM_St) ? (float) $SUM_St : 0,
            isset($SUM_Ct) && is_numeric($SUM_Ct) ? (float) $SUM_Ct : 0,
            isset($SUM_Pa) && is_numeric($SUM_Pa) ? (float) $SUM_Pa : 0,
            isset($SUM_So) && is_numeric($SUM_So) ? (float) $SUM_So : 0,
            isset($SUM_Ne) && is_numeric($SUM_Ne) ? (float) $SUM_Ne : 0
        ),
        'colors' => array('#17A7A2', '#526B91', '#E3A13A', '#887BD5', '#D76083', '#4D98C5', '#67AF7A')
    ),
    'receipts' => array('labels' => $hs_receipt_labels, 'values' => $hs_receipt_values, 'colors' => $hs_receipt_colors),
    'receiptTrend' => array('labels' => $hs_day_labels, 'datasets' => $hs_line_datasets),
    'payments' => $hs_payment_charts
);
?>
<script>
(function () {
    'use strict';

    var payload = <?php echo json_encode($hs_monthly_chart_payload, $hs_chart_json_flags); ?>;
    var initialized = false;

    function doughnutConfig(source) {
        source = source || { labels: [], values: [], colors: [] };
        return {
            type: 'doughnut',
            data: {
                labels: source.labels || [],
                datasets: [{
                    data: source.values || [],
                    backgroundColor: source.colors || [],
                    label: 'Dataset 1'
                }]
            },
            options: {
                responsive: true,
                legend: { display: false, position: 'top' },
                title: { display: false },
                animation: { animateScale: true, animateRotate: true }
            }
        };
    }

    window.barChartData = {
        labels: payload.days || [],
        datasets: [
            { label: 'Služby', data: payload.bar.services || [], backgroundColor: '#17A7A2' },
            { label: 'Prodej', data: payload.bar.sales || [], backgroundColor: '#526B91' },
            { label: 'Ceniny', data: payload.bar.vouchers || [], backgroundColor: '#E3A13A' },
            { label: 'Kredit', data: payload.bar.credit || [], backgroundColor: '#887BD5' }
        ]
    };
    window.config1 = doughnutConfig(payload.services);
    window.config2 = doughnutConfig(payload.sales);
    window.config3 = doughnutConfig(payload.weekdays);
    window.config4 = {
        type: 'line',
        data: payload.receiptTrend || { labels: [], datasets: [] },
        options: {
            responsive: true,
            legend: { display: false, position: 'top' },
            title: { display: false },
            tooltips: { mode: 'label', callbacks: {} },
            hover: { mode: 'dataset' }
        }
    };
    window.config5 = doughnutConfig(payload.receipts);
    (payload.payments || []).forEach(function (payment, index) {
        window['config' + (index + 6)] = doughnutConfig(payment);
    });

    function createChart(canvasId, config) {
        var canvas = document.getElementById(canvasId);
        if (!canvas || typeof window.Chart !== 'function' || !config) {
            return null;
        }
        try {
            return new window.Chart(canvas.getContext('2d'), config);
        } catch (error) {
            if (window.console && typeof window.console.warn === 'function') {
                window.console.warn('HairSoft: graf ' + canvasId + ' se nepodařilo vykreslit.', error);
            }
            return null;
        }
    }

    function initializeCharts() {
        var created = 0;
        if (initialized || typeof window.Chart !== 'function') {
            return false;
        }
        initialized = true;
        if (document.getElementById('canvas')) {
            window.myBar = createChart('canvas', {
                type: 'bar',
                data: window.barChartData,
                options: { responsive: true, legend: { display: false }, title: { display: false } }
            });
            if (window.myBar) created++;
        }
        window.myMonthlyServices = createChart('chart-area1', window.config1); if (window.myMonthlyServices) created++;
        window.myMonthlySales = createChart('chart-area2', window.config2); if (window.myMonthlySales) created++;
        window.myMonthlyWeekdays = createChart('chart-area3', window.config3); if (window.myMonthlyWeekdays) created++;
        window.myMonthlyReceipts = createChart('chart-area4', window.config5); if (window.myMonthlyReceipts) created++;
        window.myLine = createChart('canvas1', window.config4); if (window.myLine) created++;
        for (var index = 5; index <= 24; index++) {
            var paymentChart = createChart('chart-area' + index, window['config' + (index + 1)]);
            if (paymentChart) created++;
        }
        window.hsMonthlySourceChartsInitialized = created;
        return created > 0;
    }

    function start(attempt) {
        if (initializeCharts()) {
            return;
        }
        if (attempt < 20) {
            window.setTimeout(function () { start(attempt + 1); }, 150);
        }
    }

    window.hsMonthlyChartPayload = payload;
    window.hsInitializeMonthlySourceCharts = function () { initialized = false; return initializeCharts(); };
    window.addEventListener('load', function () { start(0); }, { once: true });
}());
</script>

    

            
