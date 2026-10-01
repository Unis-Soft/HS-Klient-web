    
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

  if (!defined('HS_CLIENT_UI_ROOT')) { define('HS_CLIENT_UI_ROOT', dirname(__DIR__, 3)); }
  require HS_CLIENT_UI_ROOT . '/str/strana/_zabezpeceni.php';
if (!isset($data_sql_TopObsluhaSluzby)) { $data_sql_TopObsluhaSluzby = array(); }
if (!isset($data_sql_TopObsluhaProdej)) { $data_sql_TopObsluhaProdej = array(); }
if (!isset($data_sql_TopDenSluzby)) { $data_sql_TopDenSluzby = array(); }
if (!isset($data_sql_TopDenProdej)) { $data_sql_TopDenProdej = array(); }
if (!isset($Mesice)) { $Mesice = array(); }
if (!isset($sql_existuje_pozadavek)) { $sql_existuje_pozadavek = array(); }
if (!isset($JmenoObsluhy)) { $JmenoObsluhy = array(); }
if (!isset($trzby_obsluha_seznam)) { $trzby_obsluha_seznam = array(); }
if (!isset($TrzbyCelkemObsluhy)) { $TrzbyCelkemObsluhy = array(); }
if (!isset($ProdejCelkemObsluhy)) { $ProdejCelkemObsluhy = array(); }
if (!isset($BarvaObsluhy)) { $BarvaObsluhy = array(); }
if (!isset($BarvaObsluhyPozadi)) { $BarvaObsluhyPozadi = array(); }
if (!isset($aSeznamObsluhy)) { $aSeznamObsluhy = array(); }
if (!isset($seznam_obsluhy)) { $seznam_obsluhy = array(); }
if (!isset($aSeznamStredisek)) { $aSeznamStredisek = array(); }
if (!isset($Barvaobsluha)) { $Barvaobsluha = array(); }
if (!isset($seznam_stredisek)) { $seznam_stredisek = array(); }
if (!isset($data_sql_trzby_mesice)) { $data_sql_trzby_mesice = array(); }
if (!isset($sTrzbyCELKEM_1)) { $sTrzbyCELKEM_1 = array(); }
if (!isset($sTrzbyCELKEM_2)) { $sTrzbyCELKEM_2 = array(); }
if (!isset($sTrzbyCELKEM_3)) { $sTrzbyCELKEM_3 = array(); }
if (!isset($sTrzbyCELKEM_4)) { $sTrzbyCELKEM_4 = array(); }
if (!isset($sTrzbyCELKEM_5)) { $sTrzbyCELKEM_5 = array(); }
if (!isset($sTrzbyCELKEM_6)) { $sTrzbyCELKEM_6 = array(); }
if (!isset($sTrzbyCELKEM_7)) { $sTrzbyCELKEM_7 = array(); }
if (!isset($sTrzbyCELKEM_8)) { $sTrzbyCELKEM_8 = array(); }
if (!isset($sTrzbyCELKEM_9)) { $sTrzbyCELKEM_9 = array(); }
if (!isset($sTrzbyCELKEM_10)) { $sTrzbyCELKEM_10 = array(); }
if (!isset($sTrzbyCELKEM_11)) { $sTrzbyCELKEM_11 = array(); }
if (!isset($sTrzbyCELKEM_12)) { $sTrzbyCELKEM_12 = array(); }
if (!isset($data_sql_Prodej_mesice)) { $data_sql_Prodej_mesice = array(); }
if (!isset($sProdejCELKEM_1)) { $sProdejCELKEM_1 = array(); }
if (!isset($sProdejCELKEM_2)) { $sProdejCELKEM_2 = array(); }
if (!isset($sProdejCELKEM_3)) { $sProdejCELKEM_3 = array(); }
if (!isset($sProdejCELKEM_4)) { $sProdejCELKEM_4 = array(); }
if (!isset($sProdejCELKEM_5)) { $sProdejCELKEM_5 = array(); }
if (!isset($sProdejCELKEM_6)) { $sProdejCELKEM_6 = array(); }
if (!isset($sProdejCELKEM_7)) { $sProdejCELKEM_7 = array(); }
if (!isset($sProdejCELKEM_8)) { $sProdejCELKEM_8 = array(); }
if (!isset($sProdejCELKEM_9)) { $sProdejCELKEM_9 = array(); }
if (!isset($sProdejCELKEM_10)) { $sProdejCELKEM_10 = array(); }
if (!isset($sProdejCELKEM_11)) { $sProdejCELKEM_11 = array(); }
if (!isset($sProdejCELKEM_12)) { $sProdejCELKEM_12 = array(); }
if (!isset($aUctenek_jednotlivci_rok)) { $aUctenek_jednotlivci_rok = array(); }
if (!isset($data_sql_Uctenek_jednotlivci_rok)) { $data_sql_Uctenek_jednotlivci_rok = array(); }
if (!isset($JmenoStrediska)) { $JmenoStrediska = array(); }
if (!isset($SQL_DotazGraf1_Data)) { $SQL_DotazGraf1_Data = array(); }
if (!isset($UctenkyStredisko)) { $UctenkyStredisko = array(); }
if (!isset($SQL_DotazGraf2_Data)) { $SQL_DotazGraf2_Data = array(); }
if (!isset($UctenkyMesic1)) { $UctenkyMesic1 = array(); }
if (!isset($UctenkyMesic2)) { $UctenkyMesic2 = array(); }
if (!isset($UctenkyMesic3)) { $UctenkyMesic3 = array(); }
if (!isset($UctenkyMesic4)) { $UctenkyMesic4 = array(); }
if (!isset($UctenkyMesic5)) { $UctenkyMesic5 = array(); }
if (!isset($UctenkyMesic6)) { $UctenkyMesic6 = array(); }
if (!isset($UctenkyMesic7)) { $UctenkyMesic7 = array(); }
if (!isset($UctenkyMesic8)) { $UctenkyMesic8 = array(); }
if (!isset($UctenkyMesic9)) { $UctenkyMesic9 = array(); }
if (!isset($UctenkyMesic10)) { $UctenkyMesic10 = array(); }
if (!isset($UctenkyMesic11)) { $UctenkyMesic11 = array(); }
if (!isset($UctenkyMesic12)) { $UctenkyMesic12 = array(); }
if (!isset($BarvaStrediska)) { $BarvaStrediska = array(); }
if (!isset($BarvaStrediskaPozadi)) { $BarvaStrediskaPozadi = array(); }
if (!isset($TrzbyMesic1)) { $TrzbyMesic1 = array(); }
if (!isset($TrzbyMesic2)) { $TrzbyMesic2 = array(); }
if (!isset($TrzbyMesic3)) { $TrzbyMesic3 = array(); }
if (!isset($TrzbyMesic4)) { $TrzbyMesic4 = array(); }
if (!isset($TrzbyMesic5)) { $TrzbyMesic5 = array(); }
if (!isset($TrzbyMesic6)) { $TrzbyMesic6 = array(); }
if (!isset($TrzbyMesic7)) { $TrzbyMesic7 = array(); }
if (!isset($TrzbyMesic8)) { $TrzbyMesic8 = array(); }
if (!isset($TrzbyMesic9)) { $TrzbyMesic9 = array(); }
if (!isset($TrzbyMesic10)) { $TrzbyMesic10 = array(); }
if (!isset($TrzbyMesic11)) { $TrzbyMesic11 = array(); }
if (!isset($TrzbyMesic12)) { $TrzbyMesic12 = array(); }
if (!isset($ProdejMesic1)) { $ProdejMesic1 = array(); }
if (!isset($ProdejMesic2)) { $ProdejMesic2 = array(); }
if (!isset($ProdejMesic3)) { $ProdejMesic3 = array(); }
if (!isset($ProdejMesic4)) { $ProdejMesic4 = array(); }
if (!isset($ProdejMesic5)) { $ProdejMesic5 = array(); }
if (!isset($ProdejMesic6)) { $ProdejMesic6 = array(); }
if (!isset($ProdejMesic7)) { $ProdejMesic7 = array(); }
if (!isset($ProdejMesic8)) { $ProdejMesic8 = array(); }
if (!isset($ProdejMesic9)) { $ProdejMesic9 = array(); }
if (!isset($ProdejMesic10)) { $ProdejMesic10 = array(); }
if (!isset($ProdejMesic11)) { $ProdejMesic11 = array(); }
if (!isset($ProdejMesic12)) { $ProdejMesic12 = array(); }
if (!isset($JmenoStrediskaTypPlatby)) { $JmenoStrediskaTypPlatby = array(); }
if (!isset($DataStrediska)) { $DataStrediska = array(); }
if (!isset($BarvaObsluhyUctenky)) { $BarvaObsluhyUctenky = array(); }
if (!isset($BarvaObsluhyPozadiUctenky)) { $BarvaObsluhyPozadiUctenky = array(); }
if (!isset($DataStredisek)) { $DataStredisek = array(); }
if (!isset($row)) { $row = array(); }
if (!isset($stredisko)) { $stredisko = array(); }
if (!isset($data_sql_PosledniAktualizace)) { $data_sql_PosledniAktualizace = array(); }
if (!isset($Castka_TypPlatby0)) { $Castka_TypPlatby0 = array(); }
if (!isset($BarvaTypu0)) { $BarvaTypu0 = array(); }
if (!isset($Typ_TypPlatby0)) { $Typ_TypPlatby0 = array(); }
if (!isset($Castka_TypPlatby1)) { $Castka_TypPlatby1 = array(); }
if (!isset($BarvaTypu1)) { $BarvaTypu1 = array(); }
if (!isset($Typ_TypPlatby1)) { $Typ_TypPlatby1 = array(); }
if (!isset($Castka_TypPlatby2)) { $Castka_TypPlatby2 = array(); }
if (!isset($BarvaTypu2)) { $BarvaTypu2 = array(); }
if (!isset($Typ_TypPlatby2)) { $Typ_TypPlatby2 = array(); }
if (!isset($Castka_TypPlatby3)) { $Castka_TypPlatby3 = array(); }
if (!isset($BarvaTypu3)) { $BarvaTypu3 = array(); }
if (!isset($Typ_TypPlatby3)) { $Typ_TypPlatby3 = array(); }
$trzby_obsluhaCNT = 0; $obsluhaCNT = 0; $DotazGraf2CNT = 0; $CNT_PocetStredisekTypPlatby = 0;


  /* MIGRACE PHP 5.5 -> 8.3
     Duvod:
     Seznamy vznikaly pouze pri nalezeni databazovych radku. PHP 8.3 vyvola
     TypeError pri count() nad nedefinovanou hodnotou. Prazdna pole zachovavaji
     puvodni pocet nula a smycky se neprovedou.

     STARY KOD PHP 5.5:
     $aSeznamObsluhy a $aSeznamStredisek nebyly pred smyckou inicializovany.
  */
  if (!isset($aSeznamObsluhy) || !is_array($aSeznamObsluhy)) { $aSeznamObsluhy = array(); }
  if (!isset($aSeznamStredisek) || !is_array($aSeznamStredisek)) { $aSeznamStredisek = array(); }


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
  
  //$Mesice("Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec");
  
  $Mesice = array("Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec");
    
  $jmenoStranky = "Celkové roční tržby dle obsluhy";
  $jmenoStrankyPopis = "Přehled Vašich celkových ročních tržeb dle obsluhy za pobočku: <B>".$_SESSION["pobocka_jmeno"]."</B>";
  
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
        
               $originalDate = $datum;
               $newDate = date("d.m.Y", strtotime($originalDate));
               
               $newYear = date("Y", strtotime($originalDate));
               
        
        // $datum ="2016-10-20";
         
        
        $sw_id =  $_SESSION["pobocka_id"];

       //$sw_id =  1081;//$_SESSION["pobocka_id"];
       
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
  //  $sw_id = 1999;    //BS
    //$Uzivatel_ID =11;
    //$sw_id = 2346;    //BS
  }
  //$Uzivatel_ID = 11; 
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////
       

       // V118: absolute, validated year/staff selection; refreshing is idempotent.
       $SQL_ROK = isset($_SESSION['SQL_ROK']) ? (int) $_SESSION['SQL_ROK'] : (int) date('Y');
       if ($SQL_ROK < 1900 || $SQL_ROK > 9999) { $SQL_ROK = (int) date('Y'); }
       $hs_requested_year = isset($_GET['NavratovyRok']) && !is_array($_GET['NavratovyRok']) ? filter_var($_GET['NavratovyRok'], FILTER_VALIDATE_INT, array('options'=>array('min_range'=>1900,'max_range'=>9999))) : false;
       if ($hs_requested_year !== false && $hs_requested_year !== $SQL_ROK) {
           $hs_year_table = 'trzby_strediska_' . $hs_requested_year;
           $hs_year_result = $mysqli->query("SHOW TABLES LIKE '$hs_year_table'");
           if ($hs_year_result && $hs_year_result->num_rows > 0) { $SQL_ROK = $hs_requested_year; }
       }
       $_SESSION['SQL_ROK'] = $SQL_ROK;
       $hs_requested_staff = isset($_GET['obsluha']) && !is_array($_GET['obsluha']) ? filter_var($_GET['obsluha'], FILTER_VALIDATE_INT, array('options'=>array('min_range'=>1))) : false;
       $StrObsluha = $hs_requested_staff !== false ? $hs_requested_staff : max(1, (int) ($_SESSION['obsluha'] ?? 1));
       $_SESSION['obsluha'] = $StrObsluha;
       if (!isset($_SESSION['SQL_Mesic'])) { $_SESSION['SQL_Mesic'] = (int) date('n'); }

?>


<section class="main-content-wrapper">            
  <div class="pageheader">                
    <h1><?php echo $jmenoStranky; ?></h1>                
    <p class="description">
      <?php echo $jmenoStrankyPopis; ?>
    </p>                
    <div class="breadcrumb-wrapper hidden-xs">                    
      <span class="label">Pobočka:</span>                    
      <ol class="breadcrumb">                        
        <li class="active"><b><?php echo $pobocka_jmeno; ?></b><?php echo JePobockaOnline($sw_id,$mysqli) ?></li> 
      </ol>
       
     
                    
    </div>            
  </div>            
  <section id="main-content" class="hs-annual-revenue">                
  
                    <?php
                        //Top obsluha sluzby
                        $sql_TopObsluhaSluzby= "SELECT concat (`tj_NazevStrediska` , `tj_NazevJednotlivce`) as Jmeno, `tj_NazevJednotlivce`, SUM(`tj_TrzbyCelkem`) as 'TrzbyCelkem' ,SUM(`tj_ZaSluzby`) as 'ZaSluzby',SUM(`tj_ZaProdej`) as 'ZaProdej' FROM `trzby_jednotlivci_$SQL_ROK` WHERE `sw_id` = $sw_id and YEAR(`tj_Datum`) = '$SQL_ROK' group by Jmeno order by ZaSluzby Desc LIMIT 1";
                        $vysledek_sql_TopObsluhaSluzby=$mysqli->query($sql_TopObsluhaSluzby);
                        $data_sql_TopObsluhaSluzby=MySQLi_Fetch_Array($vysledek_sql_TopObsluhaSluzby);
                        //$CelkoveTrzby = number_format($data_sql_TopObsluhaSluzby["TrzbyCelkem"], 2, '.', '&nbsp;');
                        $TopObsluhaSluzby = ($data_sql_TopObsluhaSluzby["tj_NazevJednotlivce"] ?? '');
                        
                        if ($TopObsluhaSluzby=="") {
                          $TopObsluhaSluzby = " - ";
                        }
                                                                                              
                        
                        //Top obsluha prodej
                        $sql_TopObsluhaProdej= "SELECT concat (`tj_NazevStrediska` , `tj_NazevJednotlivce`) as Jmeno, `tj_NazevJednotlivce`, SUM(`tj_TrzbyCelkem`) as 'TrzbyCelkem' ,SUM(`tj_ZaSluzby`) as 'ZaSluzby',SUM(`tj_ZaProdej`) as 'ZaProdej' FROM `trzby_jednotlivci_$SQL_ROK` WHERE `sw_id` = $sw_id and YEAR(`tj_Datum`) = '$SQL_ROK' group by Jmeno order by ZaProdej Desc LIMIT 1";
                        $vysledek_sql_TopObsluhaProdej=$mysqli->query($sql_TopObsluhaProdej);
                        $data_sql_TopObsluhaProdej=MySQLi_Fetch_Array($vysledek_sql_TopObsluhaProdej);
                        //$CelkoveTrzby = number_format($data_sql_TopObsluhaProdej["TrzbyCelkem"], 2, '.', '&nbsp;');
                        $TopObsluhaProdej = ($data_sql_TopObsluhaProdej["tj_NazevJednotlivce"] ?? '');
                      
                        if ($TopObsluhaProdej=="") {
                          $TopObsluhaProdej = " - ";
                        }
                        
                      
                        //Top obsluha sluzby
                        $sql_TopDenSluzby= "SELECT `tj_Datum` ,SUM(`tj_ZaSluzby`) as 'ZaSluzby' FROM `trzby_jednotlivci_$SQL_ROK` WHERE `sw_id` = $sw_id and YEAR(`tj_Datum`) = '$SQL_ROK' group by MONTH(`tj_Datum` ) order by `ZaSluzby` DESC LIMIT 1";
                        $vysledek_sql_TopDenSluzby=$mysqli->query($sql_TopDenSluzby);
                        $data_sql_TopDenSluzby=MySQLi_Fetch_Array($vysledek_sql_TopDenSluzby);
                        //$CelkoveTrzby = number_format($data_sql_TopDenSluzby["TrzbyCelkem"], 2, '.', '&nbsp;');
                        $TopDenSluzbyDatum =(!empty($data_sql_TopDenSluzby["tj_Datum"]) ? (int)date("m", strtotime($data_sql_TopDenSluzby["tj_Datum"])) : 0); 
                        $TopDenSluzbySuma = number_format($data_sql_TopDenSluzby["ZaSluzby"] ?? 0, 2, '.', '&nbsp;');
                        $TopDenSluzbyPopisek = $TopDenSluzbyDatum . " - " .$TopDenSluzbySuma; 
                        
                        //Top obsluha sluzby
                        $sql_TopDenProdej= "SELECT `tj_Datum` ,SUM(`tj_ZaSluzby`) as 'ZaSluzby',SUM(`tj_ZaProdej`) as 'ZaProdej' FROM `trzby_jednotlivci_$SQL_ROK` WHERE `sw_id` = $sw_id and YEAR(`tj_Datum`) = '$SQL_ROK' group by MONTH(`tj_Datum` ) order by `ZaProdej` DESC LIMIT 1";
                        //echo $sql_TopDenProdej;
                        $vysledek_sql_TopDenProdej=$mysqli->query($sql_TopDenProdej);
                        $data_sql_TopDenProdej=MySQLi_Fetch_Array($vysledek_sql_TopDenProdej);
                        //$CelkoveTrzby = number_format($data_sql_TopDenProdej["TrzbyCelkem"], 2, '.', '&nbsp;');
                        $TopDenProdejDatum =(!empty($data_sql_TopDenProdej["tj_Datum"]) ? (int)date("m", strtotime($data_sql_TopDenProdej["tj_Datum"])) : 0); 
                        $TopDenProdejSuma = number_format($data_sql_TopDenProdej["ZaProdej"] ?? 0, 2, '.', '&nbsp;');
                        $TopDenProdejPopisek = $TopDenProdejDatum . " - " .$TopDenProdejSuma;
                      

          //DEMO DATA
          if ($sw_id==0) {
             $TopObsluhaSluzby  = "Obsluha A";
             $TopObsluhaProdej  = "Obsluha B";
             $TopDenSluzbySuma  = rand(1000,10000);
             $TopDenProdejSuma  = rand(1000,10000);
          }

                      
                        
                      ?>
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="panel panel-solid-success widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo ZkratDlouheJmeno($TopObsluhaSluzby); ?></font></span>
                                        <span class="title text-center">TOP&nbsp;obsluha&nbsp;za&nbsp;služby</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        
                                        <span class="total text-center"><font size="5"><?php echo ZkratDlouheJmeno($TopObsluhaProdej); ?></font></span>
                                        <span class="title text-center">TOP&nbsp;obsluha&nbsp;za&nbsp;prodej</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-solid-danger widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $TopDenSluzbySuma."&nbsp;".$Mena_Klienta ?></font></span>
                                        <span class="title text-center">TOP&nbsp;za&nbsp;služby&nbsp;<b><?php echo ($Mesice[$TopDenSluzbyDatum-1] ?? ''); ?></b></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <?php
                                          //echo $Mesice[1];
                                              //$TopDenProdejPopisek
                                        ?>
                                        <span class="total text-center"><font size="5"><?php echo $TopDenProdejSuma."&nbsp;".$Mena_Klienta ?></font></span>              
                                        <span class="title text-center">TOP&nbsp;za&nbsp;prodej&nbsp;<b><?php echo ($Mesice[$TopDenProdejDatum-1] ?? ''); ?></b></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                            
                
<div class="row hs-annual-period-row"><div class="col-md-12"><section class="panel panel-default hs-annual-card hs-annual-period-card"><div class="panel-body hs-annual-period-body"><div class="hs-annual-period-groups"><div class="hs-annual-period-group" role="group" aria-label="Rok"><div class="hs-annual-period-controls"><a class="hs-annual-period-nav hs-annual-period-nav--previous" aria-label="Předchozí rok" href="index.php?strana=TrzbyDleObsluhy&amp;NavratovyRok=<?php echo $SQL_ROK - 1; ?>&amp;obsluha=<?php echo $StrObsluha; ?>"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m15 18-6-6 6-6"></path></svg></a><strong class="hs-annual-period-value"><?php echo $SQL_ROK; ?></strong><a class="hs-annual-period-nav hs-annual-period-nav--next" aria-label="Následující rok" href="index.php?strana=TrzbyDleObsluhy&amp;NavratovyRok=<?php echo $SQL_ROK + 1; ?>&amp;obsluha=<?php echo $StrObsluha; ?>"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m9 18 6-6-6-6"></path></svg></a></div></div></div></div></section></div></div>
<noscript><style>.hs-annual-table-row { visibility:visible !important; }</style></noscript>            














                <div class="row hs-annual-table-row">
                   <div class="col-md-12">
                    <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Roční celkové tržby za obsluhu</b></font></h3>
                                <div class="actions pull-right">
                                    
                                    <a href="index.php?strana=TrzbyDleObsluhy&NavratovyRok=<?php echo $SQL_ROK - 1; ?>&obsluha=<?php echo $StrObsluha; ?>"><i class="fa fa-chevron-left"></i></a>
                                    <span><?php echo $SQL_ROK; ?></span>
                                    <a href="index.php?strana=TrzbyDleObsluhy&NavratovyRok=<?php echo $SQL_ROK + 1; ?>&obsluha=<?php echo $StrObsluha; ?>"><i class="fa fa-chevron-right"></i></a>
                                    
                                    
                                    
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                 
                                </div>
                                
                            </div>
                            <div class="panel-body">
                                          
                                <div class="table-responsive">
                                    
                                   <?php
                                      
                                      $originalDate = $datum;
                                      $newDate = date("Y-m-d", strtotime($originalDate));
                                      
                                      
                                          $JednotlivciCNT = 0;
                                                                
                                          $sqldotaz_trzby_obsluha= "SELECT concat (`tj_NazevStrediska` , `tj_NazevJednotlivce`) as Jmeno,`tj_NazevStrediska`, `tj_NazevJednotlivce`, SUM(`tj_TrzbyCelkem`) as 'TrzbyCelkem' ,SUM(`tj_TrzbyCelkemBezDPH`) as 'TrzbyCelkemBezDPH' ,SUM(`tj_ZaSluzby`) as 'ZaSluzby',SUM(`tj_ZaSluzbyBezDPH`)as 'ZaSluzbyBezDPH' ,SUM(`tj_ZaProdej`) as 'ZaProdej',SUM(`tj_ZaProdejBezDPH`) as 'ZaPRodejBezDPH',sum(`Celkem_Voucher`)as Celkem_Voucher,sum(`Celkem_Voucher_bezDPH`)as Celkem_Voucher_bezDPH,sum(`Celkem_Credit`)as Celkem_Credit,sum(`Celkem_Credit_bezDPH`)as Celkem_Credit_bezDPH FROM `trzby_jednotlivci_$SQL_ROK` WHERE `sw_id` = $sw_id group by Jmeno order by 1,2 DESC " ;
                                           //echo $sqldotaz_trzby_obsluha;

                                     if (!$sqldotaz_trzby_obsluha) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                                        
                                                                        
                                                                        $vysledek_trzby_obsluha=$mysqli->query("$sqldotaz_trzby_obsluha");
                                                                        $pocet_radku_trzby_obsluha = $vysledek_trzby_obsluha->num_rows;                                       
                                                                        
                                                                        //echo $pocet_radku_strediska;
                                                                
                                                                        
                                                                        if ($pocet_radku_trzby_obsluha==0 and $sw_id!=0) {
                                                                          echo "<p class=\"hs-annual-empty\">Pro vybraný rok nejsou k dispozici žádné záznamy.</p>";  
                                                                        }else {

                                                                          //OSTRA DATA
                                                                          if ($sw_id!=0) {

                                                                                              ?>

                                                                                                <table width="99%" class="table table-bordered table-striped" id="table_RocniCelkoveTrzbyZaObsluhu">
                                                                                                    <thead>
                                                                                                        <tr align="center">
                                                                                                              <th>#</th>
                                                                                                              <th>Středisko | firmy</th>
                                                                                                              <th >Jméno obsluhy</th>
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


                                                                                             /* MIGRACE PHP 5.5 -> 8.3
                                                                                                Duvod: Soucty nesmi zacinat prazdnym retezcem, jinak PHP 8.3 vyvola TypeError.
                                                                                                STARY KOD PHP 5.5:
                                                                                                $tj_TrzbyCelkemSUM=""; $tj_TrzbyCelkemBezDPHSUM="";
                                                                                                $tj_ZaSluzbySUM=""; $tj_ZaSluzbyBezDPHSUM="";
                                                                                                $tj_ZaProdejSUM=""; $tj_ZaProdejBezDPHSUM="";
                                                                                                $ts_ZaVoucherSUM=""; $ts_ZaVoucherBezDPHSUM="";
                                                                                                $ts_ZaKreditSUM=""; $ts_ZaKreditBezDPHSUM="";
                                                                                             */
                                                                                             $tj_TrzbyCelkemSUM=0;
                                                                                             $tj_TrzbyCelkemBezDPHSUM=0;
                                                                                             $tj_ZaSluzbySUM=0;
                                                                                             $tj_ZaSluzbyBezDPHSUM=0;
                                                                                             $tj_ZaProdejSUM=0;
                                                                                             $tj_ZaProdejBezDPHSUM=0;
                                                                                             $ts_ZaVoucherSUM=0;
                                                                                             $ts_ZaVoucherBezDPHSUM=0;
                                                                                             $ts_ZaKreditSUM=0;
                                                                                             $ts_ZaKreditBezDPHSUM=0;


                                                                                            echo "<tbody>";
                                                                                            while ($trzby_obsluha_seznam=MySQLi_Fetch_Array($vysledek_trzby_obsluha)):
                                                                                                  $trzby_obsluhaCNT = $trzby_obsluhaCNT + 1;

                                                                                                    //POLE
                                                                                                     $JmenoObsluhy[$trzby_obsluhaCNT] = $trzby_obsluha_seznam["tj_NazevJednotlivce"];
                                                                                               $TrzbyCelkemObsluhy[$trzby_obsluhaCNT] = $trzby_obsluha_seznam["ZaSluzby"];
                                                                                              $ProdejCelkemObsluhy[$trzby_obsluhaCNT] = $trzby_obsluha_seznam["ZaProdej"];

                                                                                                     $TabBarva = random_color();
                                                                                                     $BarvaObsluhy[$trzby_obsluhaCNT] = $TabBarva ;
                                                                                                     $BarvaObsluhyPozadi[$trzby_obsluhaCNT] = random_no_hex_color() ;

                                                                                                         //SUM
                                                                                                         $tj_TrzbyCelkemSUM= $tj_TrzbyCelkemSUM + $trzby_obsluha_seznam["TrzbyCelkem"];
                                                                                                         $tj_TrzbyCelkemBezDPHSUM= $tj_TrzbyCelkemBezDPHSUM + $trzby_obsluha_seznam["TrzbyCelkemBezDPH"];
                                                                                                         $tj_ZaSluzbySUM= $tj_ZaSluzbySUM + $trzby_obsluha_seznam["ZaSluzby"];
                                                                                                         $tj_ZaSluzbyBezDPHSUM= $tj_ZaSluzbyBezDPHSUM + $trzby_obsluha_seznam["ZaSluzbyBezDPH"];
                                                                                                         $tj_ZaProdejSUM=$tj_ZaProdejSUM + $trzby_obsluha_seznam["ZaProdej"];
                                                                                                         $tj_ZaProdejBezDPHSUM=$tj_ZaProdejBezDPHSUM+ $trzby_obsluha_seznam["ZaPRodejBezDPH"];

                                                                                                         $ts_ZaVoucherSUM= $ts_ZaVoucherSUM + $trzby_obsluha_seznam["Celkem_Voucher"];
                                                                                                         $ts_ZaVoucherBezDPHSUM= $ts_ZaVoucherBezDPHSUM + $trzby_obsluha_seznam["Celkem_Voucher_bezDPH"];
                                                                                                         $ts_ZaKreditSUM=$ts_ZaKreditSUM + $trzby_obsluha_seznam["Celkem_Credit"];
                                                                                                         $ts_ZaKreditBezDPHSUM=$ts_ZaKreditBezDPHSUM+ $trzby_obsluha_seznam["Celkem_Credit_bezDPH"];

                                                                                                        echo "<tr>";
                                                                                                            echo "<td>$trzby_obsluhaCNT</td>";
                                                                                                            echo "<td>".$trzby_obsluha_seznam["tj_NazevStrediska"]."</td>";
                                                                                                            echo "<td><div style=\"width: 15px; height: 15px; float: left; margin-top:5px; background: #$TabBarva; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;\"></div><div>&nbsp;&nbsp;".$trzby_obsluha_seznam["tj_NazevJednotlivce"]."</div></td>";
                                                                                                            echo "<td><div align=\"right\" style=\"width: 80%;\"> <b>".number_format($trzby_obsluha_seznam["TrzbyCelkem"], 2, '.', '&nbsp;')."</b></div></td>";
                                                                                                            echo "<td><font size=\"-1\"><div align=\"right\" style=\"width: 80%;\">".number_format($trzby_obsluha_seznam["TrzbyCelkemBezDPH"], 2, '.', '&nbsp;')."</div></font></td>";
                                                                                                            echo "<td><div align=\"right\" style=\"width: 80%;\"><b>".number_format($trzby_obsluha_seznam["ZaSluzby"], 2, '.', '&nbsp;')."</b></p></div></td>";
                                                                                                            echo "<td><font size=\"-1\"><div align=\"right\" style=\"width: 80%;\">".number_format($trzby_obsluha_seznam["ZaSluzbyBezDPH"], 2, '.', '&nbsp;')."</div></font></td>";
                                                                                                            echo "<td><div align=\"right\" style=\"width: 80%;\"><b>".number_format($trzby_obsluha_seznam["ZaProdej"], 2, '.', '&nbsp;')."</b></div></td>";
                                                                                                            echo "<td><font size=\"-1\"><div align=\"right\" style=\"width: 80%;\">".number_format($trzby_obsluha_seznam["ZaPRodejBezDPH"], 2, '.', '&nbsp;')."</font></div></td>";

                                                                                                            echo "<td><div align=\"right\" style=\"width: 80%;\"><b>".number_format($trzby_obsluha_seznam["Celkem_Voucher"], 2, '.', '&nbsp;')."</b></div></td>";
                                                                                                            echo "<td><font size=\"-1\"><div align=\"right\" style=\"width: 80%;\">".number_format($trzby_obsluha_seznam["Celkem_Voucher_bezDPH"], 2, '.', '&nbsp;')."</font></div></td>";

                                                                                                            echo "<td><div align=\"right\" style=\"width: 80%;\"><b>".number_format($trzby_obsluha_seznam["Celkem_Credit"], 2, '.', '&nbsp;')."</b></div></td>";
                                                                                                            echo "<td><font size=\"-1\"><div align=\"right\" style=\"width: 80%;\">".number_format($trzby_obsluha_seznam["Celkem_Credit_bezDPH"], 2, '.', '&nbsp;')."</font></div></td>";
                                                                                                        echo "</tr>";
                                                                                            endwhile;


                                                                                            //celkem
                                                                                                       echo "<tr>";
                                                                                                            echo "<td></td>";
                                                                                                            echo "<td><b>Celkem ($Mena_Klienta):</b></td>";
                                                                                                            echo "<td></td>";
                                                                                                            echo "<td><font color=\"#E25D5D\"><div align=\"right\" style=\"width: 80%;\"><b>".number_format($tj_TrzbyCelkemSUM, 2, '.', '&nbsp;')."</b></div></font></td>";
                                                                                                            echo "<td><font color=\"#556B8D\" size=\"-1\"><div align=\"right\" style=\"width: 80%;\"><b>".number_format($tj_TrzbyCelkemBezDPHSUM, 2, '.', '&nbsp;')."</b></div></font></td>";
                                                                                                            echo "<td><font color=\"#E25D5D\"><div align=\"right\" style=\"width: 80%;\"><b>".number_format($tj_ZaSluzbySUM, 2, '.', '&nbsp;')."</b></div></font></td>";
                                                                                                            echo "<td><font color=\"#556B8D\" size=\"-1\"><div align=\"right\" style=\"width: 80%;\"><b>".number_format($tj_ZaSluzbyBezDPHSUM, 2, '.', '&nbsp;')."</b></div></font></td>";
                                                                                                            echo "<td><font color=\"#E25D5D\"><div align=\"right\" style=\"width: 80%;\"><b>".number_format($tj_ZaProdejSUM, 2, '.', '&nbsp;')."</b></div></font></td>";
                                                                                                            echo "<td><font color=\"#556B8D\" size=\"-1\"><div align=\"right\" style=\"width: 80%;\"><b>".number_format($tj_ZaProdejBezDPHSUM, 2, '.', '&nbsp;')."</b></div></font></td>";

                                                                                                            echo "<td><font color=\"#E25D5D\"><div align=\"right\" style=\"width: 80%;\"><b>".number_format($ts_ZaVoucherSUM, 2, '.', '&nbsp;')."</b></div></font></td>";
                                                                                                            echo "<td><font color=\"#556B8D\" size=\"-1\"><div align=\"right\" style=\"width: 80%;\"><b>".number_format($ts_ZaVoucherBezDPHSUM, 2, '.', '&nbsp;')."</b></div></font></td>";

                                                                                                            echo "<td><font color=\"#E25D5D\"><div align=\"right\" style=\"width: 80%;\"><b>".number_format($ts_ZaKreditSUM, 2, '.', '&nbsp;')."</b></div></font></td>";
                                                                                                            echo "<td><font color=\"#556B8D\" size=\"-1\"><div align=\"right\" style=\"width: 80%;\"><b>".number_format($ts_ZaKreditBezDPHSUM, 2, '.', '&nbsp;')."</b></div></font></td>";
                                                                                                        echo "</tr>";





                                                                                            echo "</tbody>";
                                                   ?>
                                                                                                  </table>
                                                                                       <?php

                                                                      }else {
                                                                       //DEMO DATA
                                                                         ?>
                                                                               <table class="table table-bordered table-striped" id="table_RocniCelkoveTrzbyZaObsluhu">
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
                                                                   ?>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                 </div>
                 
                 <?php



//START
//  ______     ______   ______   __     __    __     ______     __         __     ______     ______     ______     ______
// /\  __ \   /\  == \ /\__  _\ /\ \   /\ "-./  \   /\  __ \   /\ \       /\ \   /\___  \   /\  __ \   /\  ___\   /\  ___\
// \ \ \/\ \  \ \  _-/ \/_/\ \/ \ \ \  \ \ \-./\ \  \ \  __ \  \ \ \____  \ \ \  \/_/  /__  \ \  __ \  \ \ \____  \ \  __\
//  \ \_____\  \ \_\      \ \_\  \ \_\  \ \_\ \ \_\  \ \_\ \_\  \ \_____\  \ \_\   /\_____\  \ \_\ \_\  \ \_____\  \ \_____\
//   \/_____/   \/_/       \/_/   \/_/   \/_/  \/_/   \/_/\/_/   \/_____/   \/_/   \/_____/   \/_/\/_/   \/_____/   \/_____/



//Zjisteni poctu stredisek


           $sqldotaz_zjistit_obsluha= "SELECT distinct concat (`tj_NazevStrediska` , `tj_NazevJednotlivce`) as Jmeno,  `tj_NazevStrediska` as 'Stredisko', `tj_NazevJednotlivce` as 'Obsluha' FROM `trzby_jednotlivci_$SQL_ROK` join `sw_email_pobocka` on `trzby_jednotlivci_$SQL_ROK`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and `trzby_jednotlivci_$SQL_ROK`.`sw_id` = $sw_id order by 1,2 DESC" ;
           if (!$sqldotaz_zjistit_obsluha) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}

           //echo $sqldotaz_zjistit_obsluha;


                  $vysledek_zjistit_obsluha=$mysqli->query("$sqldotaz_zjistit_obsluha");
                  $pocet_radku_zjistit_obsluha = $vysledek_zjistit_obsluha->num_rows;

                    if ($pocet_radku_zjistit_obsluha==0) {
                      //echo "Optimalizace: nenalezena žádná obsluha pro rok $SQL_ROK.";
                    }else {

                      while ($seznam_obsluhy=MySQLi_Fetch_Array($vysledek_zjistit_obsluha)):
                             $obsluhaCNT = $obsluhaCNT + 1;

                             //POLE// pocit od 1
                               $aSeznamObsluhy[$obsluhaCNT] = $seznam_obsluhy["Obsluha"];
                             $aSeznamStredisek[$obsluhaCNT] = $seznam_obsluhy["Stredisko"];

                             //BARVA
                             $Barvaobsluha[$obsluhaCNT] = random_color();

                             //echo $seznam_stredisek["Stredisko"];
                      endwhile;
                    }

                    //echo count($aSeznamObsluhy);


 
        if ($pocet_radku_zjistit_obsluha!=0) {
        //smycka pro obsluha
        for ($obsluha = 1; $obsluha <= count($aSeznamStredisek); $obsluha++) {

            //echo $aSeznamStredisek[$obsluha];

              //Smycky na mesice
              for ($mesic = 1; $mesic <= 12; $mesic++) {

                    //SELECT CASE WHEN SUM(`tj_ZaSluzby`) IS NOT NULL THEN SUM(`tj_ZaSluzby`) ELSE 0.00 END as 'Trzby$mesic' FROM `trzby_jednotlivci_$SQL_ROK` where MONTH(`tj_Datum`) = $mesic AND `tj_NazevStrediska` = '".$mysqli->real_escape_string($aSeznamStredisek[$obsluha])."' and `tj_NazevJednotlivce` = '".$mysqli->real_escape_string($aSeznamObsluhy[$obsluha])."' and `sw_id` = $sw_id  and YEAR(`tj_Datum`) = 2016

                    //TRZBY Mesice
                    //-------------
                    $sql_trzby_mesice= "SELECT CASE WHEN SUM(`tj_ZaSluzby`) IS NOT NULL THEN SUM(`tj_ZaSluzby`) ELSE 0.00 END as 'Trzby$mesic' FROM `trzby_jednotlivci_$SQL_ROK` where MONTH(`tj_Datum`) = $mesic AND `tj_NazevStrediska` = '".$mysqli->real_escape_string($aSeznamStredisek[$obsluha])."' and `tj_NazevJednotlivce` = '".$mysqli->real_escape_string($aSeznamObsluhy[$obsluha])."' and `sw_id` = $sw_id  and YEAR(`tj_Datum`) = $SQL_ROK";
                  // echo $sql_trzby_mesice;

//exit;
//return;                                                              
                    $vysledek_sql_trzby_mesice=$mysqli->query($sql_trzby_mesice);
                    $radku_sql_trzby_mesice=$vysledek_sql_trzby_mesice->num_rows;
                    $data_sql_trzby_mesice=MySQLi_Fetch_Array($vysledek_sql_trzby_mesice);

                    //POLE Trzby Celkem
                    $TrzbyCELKEM = $data_sql_trzby_mesice["Trzby$mesic"];
                    //echo $TrzbyCELKEM."<BR>";

                     //Roydeleni mesicu na jedotlive pole index pole - obsluha
                     switch ($mesic) {
                       case 1:
                          $sTrzbyCELKEM_1[$obsluha] = $TrzbyCELKEM;
                         break;
                       case 2:
                          $sTrzbyCELKEM_2[$obsluha] = $TrzbyCELKEM;
                         break;
                       case 3:
                          $sTrzbyCELKEM_3[$obsluha] = $TrzbyCELKEM;
                         break;
                       case 4:
                          $sTrzbyCELKEM_4[$obsluha] = $TrzbyCELKEM;
                         break;
                       case 5:
                          $sTrzbyCELKEM_5[$obsluha] = $TrzbyCELKEM;
                         break;
                       case 6:
                          $sTrzbyCELKEM_6[$obsluha] = $TrzbyCELKEM;
                         break;
                       case 7:
                          $sTrzbyCELKEM_7[$obsluha] = $TrzbyCELKEM;
                         break;
                       case 8:
                          $sTrzbyCELKEM_8[$obsluha] = $TrzbyCELKEM;
                         break;
                       case 9:
                          $sTrzbyCELKEM_9[$obsluha] = $TrzbyCELKEM;
                         break;
                       case 10:
                          $sTrzbyCELKEM_10[$obsluha] = $TrzbyCELKEM;
                         break;
                       case 11:
                          $sTrzbyCELKEM_11[$obsluha] = $TrzbyCELKEM;
                         break;
                       case 12:
                          $sTrzbyCELKEM_12[$obsluha] = $TrzbyCELKEM;
                         break;

                       default:
                        break;
                     }


                     //PRODEDEJ
                     //--------------

                      //Prodej Mesice
                    //-------------
                    $sql_Prodej_mesice= "SELECT CASE WHEN SUM(`tj_ZaProdej`) IS NOT NULL THEN SUM(`tj_ZaProdej`) ELSE 0.00 END as 'Prodej$mesic' FROM `trzby_jednotlivci_$SQL_ROK` where MONTH(`tj_Datum`) = $mesic AND `tj_NazevStrediska` = '".$mysqli->real_escape_string($aSeznamStredisek[$obsluha])."' and `tj_NazevJednotlivce` = '".$mysqli->real_escape_string($aSeznamObsluhy[$obsluha])."' and `sw_id` = $sw_id  and YEAR(`tj_Datum`) = $SQL_ROK";
                    //echo $sql_Prodej_mesice;
                    $vysledek_sql_Prodej_mesice=$mysqli->query($sql_Prodej_mesice);
                    $radku_sql_Prodej_mesice=$vysledek_sql_Prodej_mesice->num_rows;
                    $data_sql_Prodej_mesice=MySQLi_Fetch_Array($vysledek_sql_Prodej_mesice);

                    //POLE Prodej Celkem
                    $ProdejCELKEM = $data_sql_Prodej_mesice["Prodej$mesic"];
                    //echo $ProdejCELKEM."<BR>";

                     //Roydeleni mesicu na jedotlive pole index pole - obsluha
                     switch ($mesic) {
                       case 1:
                          $sProdejCELKEM_1[$obsluha] = $ProdejCELKEM;
                         break;
                       case 2:
                          $sProdejCELKEM_2[$obsluha] = $ProdejCELKEM;
                         break;
                       case 3:
                          $sProdejCELKEM_3[$obsluha] = $ProdejCELKEM;
                         break;
                       case 4:
                          $sProdejCELKEM_4[$obsluha] = $ProdejCELKEM;
                         break;
                       case 5:
                          $sProdejCELKEM_5[$obsluha] = $ProdejCELKEM;
                         break;
                       case 6:
                          $sProdejCELKEM_6[$obsluha] = $ProdejCELKEM;
                         break;
                       case 7:
                          $sProdejCELKEM_7[$obsluha] = $ProdejCELKEM;
                         break;
                       case 8:
                          $sProdejCELKEM_8[$obsluha] = $ProdejCELKEM;
                         break;
                       case 9:
                          $sProdejCELKEM_9[$obsluha] = $ProdejCELKEM;
                         break;
                       case 10:
                          $sProdejCELKEM_10[$obsluha] = $ProdejCELKEM;
                         break;
                       case 11:
                          $sProdejCELKEM_11[$obsluha] = $ProdejCELKEM;
                         break;
                       case 12:
                          $sProdejCELKEM_12[$obsluha] = $ProdejCELKEM;
                         break;

                       default:
                        break;
                     }

             }


             //SMYCKA PRO kolacovy graf uctenek
             //SELECT sum(`tj_uctenek`) FROM `trzby_jednotlivci_$SQL_ROK` WHERE `tj_NazevStrediska` = '".$mysqli->real_escape_string($aSeznamStredisek[$obsluha])."' and `tj_NazevJednotlivce` = '".$mysqli->real_escape_string($aSeznamObsluhy[$obsluha])."' and YEAR(`tj_Datum`) = $SQL_ROK and `sw_id` =$sw_id

                        $sql_Uctenek_jednotlivci_rok= "SELECT   IFNULL(SUM(`tj_uctenek`), 0) AS 'JmenoSumUctenek' FROM `trzby_jednotlivci_$SQL_ROK` WHERE `tj_NazevStrediska` = '".$mysqli->real_escape_string($aSeznamStredisek[$obsluha])."' and `tj_NazevJednotlivce` = '".$mysqli->real_escape_string($aSeznamObsluhy[$obsluha])."' and YEAR(`tj_Datum`) = $SQL_ROK and `sw_id` =$sw_id";
                        //echo $sql_Uctenek_jednotlivci_rok;
                        $vysledek_sql_Uctenek_jednotlivci_rok=$mysqli->query($sql_Uctenek_jednotlivci_rok);
                        $data_sql_Uctenek_jednotlivci_rok=MySQLi_Fetch_Array($vysledek_sql_Uctenek_jednotlivci_rok);

                                    $aUctenek_jednotlivci_rok[$obsluha] = $data_sql_Uctenek_jednotlivci_rok["JmenoSumUctenek"];

         }
        }





//KONEC
//  ______     ______   ______   __     __    __     ______     __         __     ______     ______     ______     ______
// /\  __ \   /\  == \ /\__  _\ /\ \   /\ "-./  \   /\  __ \   /\ \       /\ \   /\___  \   /\  __ \   /\  ___\   /\  ___\
// \ \ \/\ \  \ \  _-/ \/_/\ \/ \ \ \  \ \ \-./\ \  \ \  __ \  \ \ \____  \ \ \  \/_/  /__  \ \  __ \  \ \ \____  \ \  __\
//  \ \_____\  \ \_\      \ \_\  \ \_\  \ \_\ \ \_\  \ \_\ \_\  \ \_____\  \ \_\   /\_____\  \ \_\ \_\  \ \_____\  \ \_____\
//   \/_____/   \/_/       \/_/   \/_/   \/_/  \/_/   \/_/\/_/   \/_____/   \/_/   \/_____/   \/_/\/_/   \/_____/   \/_____/






/*Tady to je puvodni ale divne

$SQL_DotazGraf2=" SELECT `ts_NazevStrediska` as Stredisko,  
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 1 AND `ts_NazevStrediska` = Stredisko and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK)as 'Uctenky1',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 2 AND `ts_NazevStrediska` = Stredisko and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK)as 'Uctenky2',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 3 AND `ts_NazevStrediska` = Stredisko and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK)as 'Uctenky3',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 4 AND `ts_NazevStrediska` = Stredisko and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK)as 'Uctenky4',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 5 AND `ts_NazevStrediska` = Stredisko and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK)as 'Uctenky5',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 6 AND `ts_NazevStrediska` = Stredisko and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK)as 'Uctenky6',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 7 AND `ts_NazevStrediska` = Stredisko and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK)as 'Uctenky7',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 8 AND `ts_NazevStrediska` = Stredisko and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK)as 'Uctenky8',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 9 AND `ts_NazevStrediska` = Stredisko and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK)as 'Uctenky9',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 10 AND `ts_NazevStrediska` = Stredisko and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK)as 'Uctenky10',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 11 AND `ts_NazevStrediska` = Stredisko and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK)as 'Uctenky11',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 12 AND `ts_NazevStrediska` = Stredisko and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK)as 'Uctenky12'
                    FROM `trzby_strediska_$SQL_ROK` WHERE `sw_id` = $sw_id group by Stredisko order by 1 ASC";             

*/



                        
                                                                        
                                                                        
//GRAF UCTENEK ZA jednotlive mesice.                                                                       
                                                                        
/*
$SQL_DotazGraf2=" SELECT `ts_NazevStrediska` as nz,  
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 1  and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK  and `ts_NazevStrediska` = nz) as 'Uctenky1',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 2  and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK  and `ts_NazevStrediska` = nz) as 'Uctenky2',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 3  and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK  and `ts_NazevStrediska` = nz) as 'Uctenky3',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 4  and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK  and `ts_NazevStrediska` = nz) as 'Uctenky4',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 5  and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK  and `ts_NazevStrediska` = nz) as 'Uctenky5',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 6  and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK  and `ts_NazevStrediska` = nz) as 'Uctenky6',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 7  and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK  and `ts_NazevStrediska` = nz) as 'Uctenky7',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 8  and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK  and `ts_NazevStrediska` = nz) as 'Uctenky8',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 9  and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK  and `ts_NazevStrediska` = nz) as 'Uctenky9',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 10  and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK  and `ts_NazevStrediska` = nz) as 'Uctenky10',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 11  and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK  and `ts_NazevStrediska` = nz) as 'Uctenky11',
                    (SELECT CASE WHEN SUM(`ts_uctenek`) IS NOT NULL THEN SUM(`ts_uctenek`) ELSE 0 END FROM `trzby_strediska_$SQL_ROK` where MONTH(`ts_Datum`) = 12  and `sw_id` = $sw_id and YEAR(`ts_Datum`) = $SQL_ROK  and `ts_NazevStrediska` = nz) as 'Uctenky12'
                    FROM `trzby_strediska_$SQL_ROK` WHERE `sw_id` = $sw_id group by `ts_NazevStrediska` order by `ts_ID` ASC";  
*/

$SQL_DotazGraf2=" SELECT  `ts_NazevStrediska` AS nz,
                          COALESCE( SUM(CASE WHEN MONTH(`ts_Datum`) = 1 THEN `ts_uctenek` ELSE 0 END), 0 ) AS Uctenky1,
                          COALESCE( SUM(CASE WHEN MONTH(`ts_Datum`) = 2 THEN `ts_uctenek` ELSE 0 END), 0 ) AS Uctenky2,
                          COALESCE( SUM(CASE WHEN MONTH(`ts_Datum`) = 3 THEN `ts_uctenek` ELSE 0 END), 0 ) AS Uctenky3,
                          COALESCE( SUM(CASE WHEN MONTH(`ts_Datum`) = 4 THEN `ts_uctenek` ELSE 0 END), 0 ) AS Uctenky4,
                          COALESCE( SUM(CASE WHEN MONTH(`ts_Datum`) = 5 THEN `ts_uctenek` ELSE 0 END), 0 ) AS Uctenky5,
                          COALESCE( SUM(CASE WHEN MONTH(`ts_Datum`) = 6 THEN `ts_uctenek` ELSE 0 END), 0 ) AS Uctenky6,
                          COALESCE( SUM(CASE WHEN MONTH(`ts_Datum`) = 7 THEN `ts_uctenek` ELSE 0 END), 0 ) AS Uctenky7,
                          COALESCE( SUM(CASE WHEN MONTH(`ts_Datum`) = 8 THEN `ts_uctenek` ELSE 0 END), 0 ) AS Uctenky8,
                          COALESCE( SUM(CASE WHEN MONTH(`ts_Datum`) = 9 THEN `ts_uctenek` ELSE 0 END), 0 ) AS Uctenky9,
                          COALESCE( SUM(CASE WHEN MONTH(`ts_Datum`) = 10 THEN `ts_uctenek` ELSE 0 END), 0 ) AS Uctenky10,
                          COALESCE( SUM(CASE WHEN MONTH(`ts_Datum`) = 11 THEN `ts_uctenek` ELSE 0 END), 0 ) AS Uctenky11,
                          COALESCE( SUM(CASE WHEN MONTH(`ts_Datum`) = 12 THEN `ts_uctenek` ELSE 0 END), 0 ) AS Uctenky12    
                      FROM
                          `trzby_strediska_$SQL_ROK`
                      WHERE
                          `sw_id` = $sw_id
                          AND YEAR(`ts_Datum`) = $SQL_ROK
                      GROUP BY
                          `ts_NazevStrediska`
                      ORDER BY
                          `ts_ID` ASC;";                                                                                            
                                                                        
                                     //echo $SQL_DotazGraf2;                                   
                                     

                                     $DotazGraf2CNT = 0;
                                     if (!$SQL_DotazGraf2) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                                        
                                                                        
                                                                        $vysledek_SQL_DotazGraf2=$mysqli->query("$SQL_DotazGraf2");
                                                                        $pocet_radku_SQL_DotazGraf2 = $vysledek_SQL_DotazGraf2->num_rows;                                       
                                                                        
                                                                      // echo $pocet_radku_SQL_DotazGraf1;
                                                                                                                                                
                                                                        while ($SQL_DotazGraf2_Data=MySQLi_Fetch_Array($vysledek_SQL_DotazGraf2)):   
                                                                             
                                                                              $DotazGraf2CNT = $DotazGraf2CNT + 1;

                                                                                //POLE
                                                                                //$JmenoStrediska[$DotazGraf1CNT] = $SQL_DotazGraf1_Data["Stredisko"]; 
                                                                                  
                                                                                   $UctenkyStredisko[$DotazGraf2CNT] = $SQL_DotazGraf2_Data["nz"];
                                                                                  
                                                                                   $UctenkyMesic1[$DotazGraf2CNT] = $SQL_DotazGraf2_Data["Uctenky1"];
                                                                                   $UctenkyMesic2[$DotazGraf2CNT] = $SQL_DotazGraf2_Data["Uctenky2"];
                                                                                   $UctenkyMesic3[$DotazGraf2CNT] = $SQL_DotazGraf2_Data["Uctenky3"];
                                                                                   $UctenkyMesic4[$DotazGraf2CNT] = $SQL_DotazGraf2_Data["Uctenky4"];
                                                                                   $UctenkyMesic5[$DotazGraf2CNT] = $SQL_DotazGraf2_Data["Uctenky5"];
                                                                                   $UctenkyMesic6[$DotazGraf2CNT] = $SQL_DotazGraf2_Data["Uctenky6"];
                                                                                   $UctenkyMesic7[$DotazGraf2CNT] = $SQL_DotazGraf2_Data["Uctenky7"];
                                                                                   $UctenkyMesic8[$DotazGraf2CNT] = $SQL_DotazGraf2_Data["Uctenky8"];
                                                                                   $UctenkyMesic9[$DotazGraf2CNT] = $SQL_DotazGraf2_Data["Uctenky9"];
                                                                                  $UctenkyMesic10[$DotazGraf2CNT] = $SQL_DotazGraf2_Data["Uctenky10"];
                                                                                  $UctenkyMesic11[$DotazGraf2CNT] = $SQL_DotazGraf2_Data["Uctenky11"];
                                                                                  $UctenkyMesic12[$DotazGraf2CNT] = $SQL_DotazGraf2_Data["Uctenky12"];
                                                                                
                                                                                
                                                                                 $UctenkyTabBarva = random_color(); 
                                                                                 $BarvaStrediska[$DotazGraf2CNT] = $UctenkyTabBarva ; 
                                                                                 $BarvaStrediskaPozadi[$DotazGraf2CNT] = random_no_hex_color() ;

                                                                                                                                                 
                                                                        endwhile;




                /*
                {
                    label: "My First dataset1",
                    backgroundColor: "rgba(220,220,220,0.5)",
                    borderColor : "rgba(220,220,220,1)",
                    pointBorderColor : "rgba(220,220,220,1)",
                    pointBackgroundColor : "rgba(220,220,220,1)",
                    pointBorderWidth : 1,
                    data: [randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor()],
                }                
                */

/*
  $data1=""; 
     for ($i = 1; $i <= $trzby_obsluhaCNT; $i++) {
    
           if ($i == $trzby_obsluhaCNT) {
             $carka1 = "";
           }else {
             $carka1 = ",";
           }
    
       $data1= $data1 . "{ ";
       $data1= $data1 . "label: \"".$JmenoObsluhy[$i]."\",";
       $data1= $data1 . "backgroundColor: \"rgba(".$BarvaObsluhyPozadi[$i].",0.1)\",";
       $data1= $data1 . "borderColor: \"#".$BarvaObsluhy[$i]."\",";
       $data1= $data1 . "pointBorderColor: \"#".$BarvaObsluhy[$i]."\",";
       $data1= $data1 . "pointBackgroundColor: \"#".$BarvaObsluhy[$i]."\",";
       $data1= $data1 . "pointBorderWidth: 1,";
       $data1= $data1 . "data: [";
       $data1= $data1 .  $TrzbyMesic1[$i].",".$TrzbyMesic2[$i].",".$TrzbyMesic3[$i].",".$TrzbyMesic4[$i].",".$TrzbyMesic5[$i].",".$TrzbyMesic6[$i].",".$TrzbyMesic7[$i].",".$TrzbyMesic8[$i].",".$TrzbyMesic9[$i].",".$TrzbyMesic10[$i].",".$TrzbyMesic11[$i].",".$TrzbyMesic12[$i];
       $data1= $data1 . "]";
       $data1= $data1 . "} ";
       $data1= $data1 . $carka1;
        
     }
     
$data2=""; 
     for ($i = 1; $i <= $trzby_obsluhaCNT; $i++) {
    
           if ($i == $trzby_obsluhaCNT) {
             $carka2 = "";
           }else {
             $carka2 = ",";
           }
    
       $data2= $data2 . "{ ";
       $data2= $data2 . "label: \"".$JmenoObsluhy[$i]."\",";
       $data2= $data2 . "backgroundColor: \"rgba(".$BarvaObsluhyPozadi[$i].",0.1)\",";
       $data2= $data2 . "borderColor: \"#".$BarvaObsluhy[$i]."\",";
       $data2= $data2 . "pointBorderColor: \"#".$BarvaObsluhy[$i]."\",";
       $data2= $data2 . "pointBackgroundColor: \"#".$BarvaObsluhy[$i]."\",";
       $data2= $data2 . "pointBorderWidth: 1,";
       $data2= $data2 . "data: [";
       $data2= $data2 .  $ProdejMesic1[$i].",".$ProdejMesic2[$i].",".$ProdejMesic3[$i].",".$ProdejMesic4[$i].",".$ProdejMesic5[$i].",".$ProdejMesic6[$i].",".$ProdejMesic7[$i].",".$ProdejMesic8[$i].",".$ProdejMesic9[$i].",".$ProdejMesic10[$i].",".$ProdejMesic11[$i].",".$ProdejMesic12[$i];
       $data2= $data2 . "]";
       $data2= $data2 . "} ";
       $data2= $data2 . $carka2;
        
     }
     
  */
     

    
?>
                <div class="row">




                     <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Historie v čase za jednotlivce</b></font></h3>
                                <div class="reportdate">

                                    <!-- <b class="caret"></b> -->
                                </div>

                                <div class="actions ">
                                   <!--  <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i> -->
                                    <!-- <i class="fa fa-calendar-o"></i> -->

                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>

                                </div>
                            </div>

                            <div class="panel-body text-center"><?php


                                                                   //DEMO DATA
                                                                        if ($sw_id==0) {
                                                                            $JmenoObsluhy[1] = "Obsluha A" ;
                                                                            $JmenoObsluhy[2] = "Obsluha B" ;
                                                                            $JmenoObsluhy[3] = "Obsluha C" ;
                                                                            $JmenoObsluhy[4] = "Obsluha D" ;
                                                                            $JmenoObsluhy[5] = "Obsluha E" ;

                                                                            $aSeznamObsluhy[1] = "Obsluha A" ;
                                                                            $aSeznamObsluhy[2] = "Obsluha B" ;
                                                                            $aSeznamObsluhy[3] = "Obsluha C" ;
                                                                            $aSeznamObsluhy[4] = "Obsluha D" ;
                                                                            $aSeznamObsluhy[5] = "Obsluha E" ;

                                                                            $TrzbyCelkemObsluhy[1] = rand(1000,50000);
                                                                            $TrzbyCelkemObsluhy[2] = rand(1000,50000);
                                                                            $TrzbyCelkemObsluhy[3] = rand(1000,50000);
                                                                            $TrzbyCelkemObsluhy[4] = rand(1000,50000);
                                                                            $TrzbyCelkemObsluhy[5] = rand(1000,50000);

                                                                            $ProdejCelkemObsluhy[1] = rand(1000,10000);
                                                                            $ProdejCelkemObsluhy[2] = rand(1000,10000);
                                                                            $ProdejCelkemObsluhy[3] = rand(1000,10000);
                                                                            $ProdejCelkemObsluhy[4] = rand(1000,10000);
                                                                            $ProdejCelkemObsluhy[5] = rand(1000,10000);

                                                                            $aUctenek_jednotlivci_rok[1] = rand(100,1000);
                                                                            $aUctenek_jednotlivci_rok[2] = rand(100,1000);
                                                                            $aUctenek_jednotlivci_rok[3] = rand(100,1000);
                                                                            $aUctenek_jednotlivci_rok[4] = rand(100,1000);
                                                                            $aUctenek_jednotlivci_rok[5] = rand(100,1000);



                                                                            $BarvaObsluhy[1] = random_color();
                                                                            $BarvaObsluhy[2] = random_color();
                                                                            $BarvaObsluhy[3] = random_color();
                                                                            $BarvaObsluhy[4] = random_color();
                                                                            $BarvaObsluhy[5] = random_color();

                                                                            $BarvaObsluhyPozadi[1] = random_no_hex_color() ;
                                                                            $BarvaObsluhyPozadi[2] = random_no_hex_color() ;
                                                                            $BarvaObsluhyPozadi[3] = random_no_hex_color() ;
                                                                            $BarvaObsluhyPozadi[4] = random_no_hex_color() ;
                                                                            $BarvaObsluhyPozadi[5] = random_no_hex_color() ;

                                                                            $obsluhaCNT = 5;

                                                                            $trzby_obsluhaCNT = 5;


                                                                            $DotazGraf2CNT = 2;

                                                                            $UctenkyStredisko[1] ="Firma A";
                                                                            $UctenkyStredisko[2] ="Firma B";


                                                                                 $BarvaStrediska[1] = random_color();
                                                                                 $BarvaStrediska[2] = random_color();
                                                                                 $BarvaStrediskaPozadi[1] = random_no_hex_color() ;
                                                                                 $BarvaStrediskaPozadi[2] = random_no_hex_color() ;




                                                                           $UctenkyMesic1[1] =  rand(100,1000);
                                                                           $UctenkyMesic2[1] =  rand(100,1000);
                                                                           $UctenkyMesic3[1] =  rand(100,1000);
                                                                           $UctenkyMesic4[1] =  rand(100,1000);
                                                                           $UctenkyMesic5[1] =  rand(100,1000);
                                                                           $UctenkyMesic6[1] =  rand(100,1000);
                                                                           $UctenkyMesic7[1] =  rand(100,1000);
                                                                           $UctenkyMesic8[1] =  rand(100,1000);
                                                                           $UctenkyMesic9[1] =  rand(100,1000);
                                                                           $UctenkyMesic10[1] =  rand(100,1000);
                                                                           $UctenkyMesic11[1] =  rand(100,1000);
                                                                           $UctenkyMesic12[1] =  rand(100,1000);

                                                                           $UctenkyMesic1[2] =  rand(100,1000);
                                                                           $UctenkyMesic2[2] =  rand(100,1000);
                                                                           $UctenkyMesic3[2] =  rand(100,1000);
                                                                           $UctenkyMesic4[2] =  rand(100,1000);
                                                                           $UctenkyMesic5[2] =  rand(100,1000);
                                                                           $UctenkyMesic6[2] =  rand(100,1000);
                                                                           $UctenkyMesic7[2] =  rand(100,1000);
                                                                           $UctenkyMesic8[2] =  rand(100,1000);
                                                                           $UctenkyMesic9[2] =  rand(100,1000);
                                                                           $UctenkyMesic10[2] =  rand(100,1000);
                                                                           $UctenkyMesic11[2] =  rand(100,1000);
                                                                           $UctenkyMesic12[2] =  rand(100,1000);


                                                                           $sTrzbyCELKEM_1[1] = rand(100,10000);
                                                                           $sTrzbyCELKEM_2[1] = rand(100,10000);
                                                                           $sTrzbyCELKEM_3[1] = rand(100,10000);
                                                                           $sTrzbyCELKEM_4[1] = rand(100,10000);
                                                                           $sTrzbyCELKEM_5[1] = rand(100,10000);
                                                                           $sTrzbyCELKEM_6[1] = rand(100,10000);
                                                                           $sTrzbyCELKEM_7[1] = rand(100,10000);
                                                                           $sTrzbyCELKEM_8[1] = rand(100,10000);
                                                                           $sTrzbyCELKEM_9[1] = rand(100,10000);
                                                                           $sTrzbyCELKEM_10[1] = rand(100,10000);
                                                                           $sTrzbyCELKEM_11[1] = rand(100,10000);
                                                                           $sTrzbyCELKEM_12[1] = rand(100,10000);

                                                                           $sTrzbyCELKEM_1[2] = rand(100,10000);
                                                                           $sTrzbyCELKEM_2[2] = rand(100,10000);
                                                                           $sTrzbyCELKEM_3[2] = rand(100,10000);
                                                                           $sTrzbyCELKEM_4[2] = rand(100,10000);
                                                                           $sTrzbyCELKEM_5[2] = rand(100,10000);
                                                                           $sTrzbyCELKEM_6[2] = rand(100,10000);
                                                                           $sTrzbyCELKEM_7[2] = rand(100,10000);
                                                                           $sTrzbyCELKEM_8[2] = rand(100,10000);
                                                                           $sTrzbyCELKEM_9[2] = rand(100,10000);
                                                                           $sTrzbyCELKEM_10[2] = rand(100,10000);
                                                                           $sTrzbyCELKEM_11[2] = rand(100,10000);
                                                                           $sTrzbyCELKEM_12[2] = rand(100,10000);

                                                                           $sTrzbyCELKEM_1[3] = rand(100,10000);
                                                                           $sTrzbyCELKEM_2[3] = rand(100,10000);
                                                                           $sTrzbyCELKEM_3[3] = rand(100,10000);
                                                                           $sTrzbyCELKEM_4[3] = rand(100,10000);
                                                                           $sTrzbyCELKEM_5[3] = rand(100,10000);
                                                                           $sTrzbyCELKEM_6[3] = rand(100,10000);
                                                                           $sTrzbyCELKEM_7[3] = rand(100,10000);
                                                                           $sTrzbyCELKEM_8[3] = rand(100,10000);
                                                                           $sTrzbyCELKEM_9[3] = rand(100,10000);
                                                                           $sTrzbyCELKEM_10[3] = rand(100,10000);
                                                                           $sTrzbyCELKEM_11[3] = rand(100,10000);
                                                                           $sTrzbyCELKEM_12[3] = rand(100,10000);

                                                                           $sTrzbyCELKEM_1[4] = rand(100,10000);
                                                                           $sTrzbyCELKEM_2[4] = rand(100,10000);
                                                                           $sTrzbyCELKEM_3[4] = rand(100,10000);
                                                                           $sTrzbyCELKEM_4[4] = rand(100,10000);
                                                                           $sTrzbyCELKEM_5[4] = rand(100,10000);
                                                                           $sTrzbyCELKEM_6[4] = rand(100,10000);
                                                                           $sTrzbyCELKEM_7[4] = rand(100,10000);
                                                                           $sTrzbyCELKEM_8[4] = rand(100,10000);
                                                                           $sTrzbyCELKEM_9[4] = rand(100,10000);
                                                                           $sTrzbyCELKEM_10[4] = rand(100,10000);
                                                                           $sTrzbyCELKEM_11[4] = rand(100,10000);
                                                                           $sTrzbyCELKEM_12[4] = rand(100,10000);

                                                                           $sTrzbyCELKEM_1[5] = rand(100,10000);
                                                                           $sTrzbyCELKEM_2[5] = rand(100,10000);
                                                                           $sTrzbyCELKEM_3[5] = rand(100,10000);
                                                                           $sTrzbyCELKEM_4[5] = rand(100,10000);
                                                                           $sTrzbyCELKEM_5[5] = rand(100,10000);
                                                                           $sTrzbyCELKEM_6[5] = rand(100,10000);
                                                                           $sTrzbyCELKEM_7[5] = rand(100,10000);
                                                                           $sTrzbyCELKEM_8[5] = rand(100,10000);
                                                                           $sTrzbyCELKEM_9[5] = rand(100,10000);
                                                                           $sTrzbyCELKEM_10[5] = rand(100,10000);
                                                                           $sTrzbyCELKEM_11[5] = rand(100,10000);
                                                                           $sTrzbyCELKEM_12[5] = rand(100,10000);

                                                                           $sProdejCELKEM_1[1] = rand(100,10000);
                                                                           $sProdejCELKEM_2[1] = rand(100,10000);
                                                                           $sProdejCELKEM_3[1] = rand(100,10000);
                                                                           $sProdejCELKEM_4[1] = rand(100,10000);
                                                                           $sProdejCELKEM_5[1] = rand(100,10000);
                                                                           $sProdejCELKEM_6[1] = rand(100,10000);
                                                                           $sProdejCELKEM_7[1] = rand(100,10000);
                                                                           $sProdejCELKEM_8[1] = rand(100,10000);
                                                                           $sProdejCELKEM_9[1] = rand(100,10000);
                                                                           $sProdejCELKEM_10[1] = rand(100,10000);
                                                                           $sProdejCELKEM_11[1] = rand(100,10000);
                                                                           $sProdejCELKEM_12[1] = rand(100,10000);

                                                                           $sProdejCELKEM_1[2] = rand(100,10000);
                                                                           $sProdejCELKEM_2[2] = rand(100,10000);
                                                                           $sProdejCELKEM_3[2] = rand(100,10000);
                                                                           $sProdejCELKEM_4[2] = rand(100,10000);
                                                                           $sProdejCELKEM_5[2] = rand(100,10000);
                                                                           $sProdejCELKEM_6[2] = rand(100,10000);
                                                                           $sProdejCELKEM_7[2] = rand(100,10000);
                                                                           $sProdejCELKEM_8[2] = rand(100,10000);
                                                                           $sProdejCELKEM_9[2] = rand(100,10000);
                                                                           $sProdejCELKEM_10[2] = rand(100,10000);
                                                                           $sProdejCELKEM_11[2] = rand(100,10000);
                                                                           $sProdejCELKEM_12[2] = rand(100,10000);

                                                                           $sProdejCELKEM_1[3] = rand(100,10000);
                                                                           $sProdejCELKEM_2[3] = rand(100,10000);
                                                                           $sProdejCELKEM_3[3] = rand(100,10000);
                                                                           $sProdejCELKEM_4[3] = rand(100,10000);
                                                                           $sProdejCELKEM_5[3] = rand(100,10000);
                                                                           $sProdejCELKEM_6[3] = rand(100,10000);
                                                                           $sProdejCELKEM_7[3] = rand(100,10000);
                                                                           $sProdejCELKEM_8[3] = rand(100,10000);
                                                                           $sProdejCELKEM_9[3] = rand(100,10000);
                                                                           $sProdejCELKEM_10[3] = rand(100,10000);
                                                                           $sProdejCELKEM_11[3] = rand(100,10000);
                                                                           $sProdejCELKEM_12[3] = rand(100,10000);

                                                                           $sProdejCELKEM_1[4] = rand(100,10000);
                                                                           $sProdejCELKEM_2[4] = rand(100,10000);
                                                                           $sProdejCELKEM_3[4] = rand(100,10000);
                                                                           $sProdejCELKEM_4[4] = rand(100,10000);
                                                                           $sProdejCELKEM_5[4] = rand(100,10000);
                                                                           $sProdejCELKEM_6[4] = rand(100,10000);
                                                                           $sProdejCELKEM_7[4] = rand(100,10000);
                                                                           $sProdejCELKEM_8[4] = rand(100,10000);
                                                                           $sProdejCELKEM_9[4] = rand(100,10000);
                                                                           $sProdejCELKEM_10[4] = rand(100,10000);
                                                                           $sProdejCELKEM_11[4] = rand(100,10000);
                                                                           $sProdejCELKEM_12[4] = rand(100,10000);

                                                                           $sProdejCELKEM_1[5] = rand(100,10000);
                                                                           $sProdejCELKEM_2[5] = rand(100,10000);
                                                                           $sProdejCELKEM_3[5] = rand(100,10000);
                                                                           $sProdejCELKEM_4[5] = rand(100,10000);
                                                                           $sProdejCELKEM_5[5] = rand(100,10000);
                                                                           $sProdejCELKEM_6[5] = rand(100,10000);
                                                                           $sProdejCELKEM_7[5] = rand(100,10000);
                                                                           $sProdejCELKEM_8[5] = rand(100,10000);
                                                                           $sProdejCELKEM_9[5] = rand(100,10000);
                                                                           $sProdejCELKEM_10[5] = rand(100,10000);
                                                                           $sProdejCELKEM_11[5] = rand(100,10000);
                                                                           $sProdejCELKEM_12[5] = rand(100,10000);



                                                                        }






//Graf uctenky DATA
?><?php $StrObsluha = count($aSeznamObsluhy) ? min(max(1,$StrObsluha),count($aSeznamObsluhy)) : 1; $_SESSION['obsluha']=$StrObsluha; ?>
<div class="hs-annual-staff-picker"><label for="hs-annual-staff">Obsluha</label><select id="hs-annual-staff" <?php if (!count($aSeznamObsluhy)) echo 'disabled'; ?>><?php if (!count($aSeznamObsluhy)) { ?><option>Žádná obsluha</option><?php } foreach ($aSeznamObsluhy as $hs_i=>$hs_name) { ?><option value="<?php echo (int)$hs_i; ?>" <?php if ($hs_i==$StrObsluha) echo 'selected'; ?>><?php echo htmlspecialchars(($aSeznamStredisek[$hs_i] ?? '') . ' · ' . $hs_name, ENT_QUOTES, 'UTF-8'); ?></option><?php } ?></select></div>
                                <div>
                                    <!-- <canvas id="bar1" height="100"></canvas> -->
                                    <canvas id="canvas" height="70"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                  </div>
                        


                        



















                        
                 <div class="row">
                   <div class="col-md-12">
                    <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Historie tržeb za služby v čase</b></font></h3>
                                <div class="actions pull-right">
                                    
                                    <a href="index.php?strana=TrzbyDleObsluhy&NavratovyRok=<?php echo $SQL_ROK - 1; ?>&obsluha=<?php echo $StrObsluha; ?>"><i class="fa fa-chevron-left"></i></a>
                                    <span><?php echo $SQL_ROK; ?></span>
                                    <a href="index.php?strana=TrzbyDleObsluhy&NavratovyRok=<?php echo $SQL_ROK + 1; ?>&obsluha=<?php echo $StrObsluha; ?>"><i class="fa fa-chevron-right"></i></a>
                                    
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                 
                                </div>
                                
                            </div>
                            <div class="panel-body text-center">
                                <?php
                                                                                                          
                                   { // Render the chart for populated and empty years alike.
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

                 </div>
                 
                 <!--  <div class="col-lg-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Line</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body text-center">
                                <div>
                                    <canvas id="line" height="140"></canvas>
                                </div>
                            </div>
                        </div>
                    </div> -->
                 
                 
                 <div class="row">
                   <div class="col-md-12">
                    <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Historie prodeje v čase</b></font></h3>
                                <div class="actions pull-right">
                                    
                                    <a href="index.php?strana=TrzbyDleObsluhy&NavratovyRok=<?php echo $SQL_ROK - 1; ?>&obsluha=<?php echo $StrObsluha; ?>"><i class="fa fa-chevron-left"></i></a>
                                    <span><?php echo $SQL_ROK; ?></span>
                                    <a href="index.php?strana=TrzbyDleObsluhy&NavratovyRok=<?php echo $SQL_ROK + 1; ?>&obsluha=<?php echo $StrObsluha; ?>"><i class="fa fa-chevron-right"></i></a>
                                    
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                 
                                </div>
                                
                            </div>
                            <div class="panel-body text-center">
                                <?php
                                                                                                          
                                   { // Render the chart for populated and empty years alike.
                                   ?>
                                    <div>
                                        <canvas id="canvas2" height="70"></canvas>
                                        <!-- <canvas id="line1" height="50"></canvas> -->
                                    </div>
                                   
                                   <?php  
                                   }
                                 ?>
                            </div>
                        </div>
                    </div>
                 </div>
                 

                 <!-- KOLACE -->
                 <div class="row">


                 <div class="col-lg-4">
                         <div class="panel panel-default ">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="Tahoma"><b>Celkem služby</b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body ">
                               <div class="row">
                                 <?php
                                   if ($trzby_obsluhaCNT==0 and $sw_id!=0) {
                                       // Empty charts are rendered as zero states.
                                   }
                                 ?>

                                 <div class="col-lg-5" >

                                           <table border="0"><?php foreach ($JmenoObsluhy as $hs_i=>$hs_name) { ?><tr><td><div style="width:15px;height:15px;border-radius:50%;background:#17A7A2"></div></td><td><?php echo htmlspecialchars($hs_name, ENT_QUOTES, 'UTF-8'); ?></td><td><b><?php echo number_format($TrzbyCelkemObsluhy[$hs_i] ?? 0, 2, '.', '&nbsp;'); ?></b></td></tr><?php } ?></table>


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
                                <h3 class="panel-title"><font face="Tahoma"><b>Celkem prodej</b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body ">
                               <div class="row">
                                 <?php
                                   if ($trzby_obsluhaCNT==0 and $sw_id!=0) {
                                       // Empty charts are rendered as zero states.
                                   }
                                 ?>

                                 <div class="col-lg-5" >

                                           <table border="0"><?php foreach ($JmenoObsluhy as $hs_i=>$hs_name) { ?><tr><td><div style="width:15px;height:15px;border-radius:50%;background:#17A7A2"></div></td><td><?php echo htmlspecialchars($hs_name, ENT_QUOTES, 'UTF-8'); ?></td><td><b><?php echo number_format($ProdejCelkemObsluhy[$hs_i] ?? 0, 2, '.', '&nbsp;'); ?></b></td></tr><?php } ?></table>


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
                                <h3 class="panel-title"><font face="Tahoma"><b>Účtenky celkem</b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body ">
                               <div class="row">
                                 <?php
                                   if ($trzby_obsluhaCNT==0 and $sw_id!=0) {
                                       // Empty charts are rendered as zero states.
                                   }
                                 ?>

                                 <div class="col-lg-5" >

                                           <table border="0"><?php foreach ($aSeznamObsluhy as $hs_i=>$hs_name) { ?><tr><td><div style="width:15px;height:15px;border-radius:50%;background:#17A7A2"></div></td><td><?php echo htmlspecialchars($hs_name, ENT_QUOTES, 'UTF-8'); ?></td><td><b><?php echo number_format($aUctenek_jednotlivci_rok[$hs_i] ?? 0, 0, '.', '&nbsp;'); ?></b></td></tr><?php } ?></table>


                                </div>
                                <div>
                                 <div class="col-lg-7" align="center">
                                  <canvas height="250" id="chart-area3"></canvas>
                                 </div>
                                </div>
                              </div>
                            </div>
                        </div>
                    </div>


                 </div>

                 
                 <div class="row">
                   <div class="col-md-12">
                    <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Počty účtenek za jednotlivá střediska</b></font></h3>
                                <div class="actions pull-right">
                                    
                                    <a href="index.php?strana=TrzbyDleObsluhy&NavratovyRok=<?php echo $SQL_ROK - 1; ?>&obsluha=<?php echo $StrObsluha; ?>"><i class="fa fa-chevron-left"></i></a>
                                    <span><?php echo $SQL_ROK; ?></span>
                                    <a href="index.php?strana=TrzbyDleObsluhy&NavratovyRok=<?php echo $SQL_ROK + 1; ?>&obsluha=<?php echo $StrObsluha; ?>"><i class="fa fa-chevron-right"></i></a>
                                    
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                 
                                </div>
                                
                            </div>
                            <div class="panel-body text-center">
                                <?php
                                                                                                          
                                   { // Render the chart for populated and empty years alike.
                                   ?>
                                    <div>
                                        <canvas id="canvas3" height="60"></canvas>
                                        <!-- <canvas id="line1" height="50"></canvas> -->
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
                         $sqldotaz_strediska= "SELECT `trzby_typy_stredisko` FROM `trzby_typy` WHERE `trzby_typy_sw_id` = $sw_id and YEAR(`trzby_typy_datum`) = $SQL_ROK  group by `trzby_typy_stredisko` order by trzby_typy_id ASC" ;
                 //        echo $sqldotaz_strediska;

                         if (!$sqldotaz_strediska) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                         $vysledek_strediska=$mysqli->query("$sqldotaz_strediska");
                         $pocet_radku_strediska = $vysledek_strediska->num_rows;                                       
                           
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







                           $DataStredisek = [];

                                  for ($s = 0; $s < $pocet_radku_strediska; $s++) {

                                      $nazevStrediska = $JmenoStrediskaTypPlatby[$s];

                                      $sql = "
                                          SELECT 
                                              trzby_typy_typ AS typ,
                                              SUM(trzby_typy_castkaDPH) AS castka
                                          FROM trzby_typy
                                          WHERE 
                                              trzby_typy_sw_id = $sw_id
                                              AND YEAR(trzby_typy_datum) = $SQL_ROK
                                              AND trzby_typy_stredisko = '".$mysqli->real_escape_string($nazevStrediska)."'
                                          GROUP BY trzby_typy_typ
                                          ORDER BY trzby_typy_id ASC
                                      ";

                                      $res = $mysqli->query($sql);

                                      $DataStredisek[$s] = [
                                          'nazev'  => $nazevStrediska,
                                          'typy'   => [],
                                          'castky' => [],
                                          'barvy'  => []
                                      ];

                                      if ($res && $res->num_rows > 0) {
                                          while ($row = $res->fetch_assoc()) {
                                              $DataStredisek[$s]['typy'][]   = $row['typ'];
                                              $DataStredisek[$s]['castky'][] = $row['castka'];
                                              $DataStredisek[$s]['barvy'][]  = random_color();
                                          }
                                      }
                                  }


                            if (!$DataStredisek) { $DataStredisek[] = array('nazev'=>$pobocka_jmeno, 'typy'=>array(), 'castky'=>array(), 'barvy'=>array()); }
                            foreach ($DataStredisek as $idx => $stredisko) {
                                      ?>
                                      <div class="col-lg-6">
                                          <div class="panel panel-default">
                                              <div class="panel-heading">
                                                  <h3 class="panel-title"><b>Tržby dle typu platby</b></h3>
                                              </div>

                                              <div class="panel-body">
                                                  <div class="row">
                                                      <div class="col-lg-5">
                                                          Firma: <b><?= htmlspecialchars($stredisko['nazev'], ENT_COMPAT) ?></b><br><br>

                                                          <table border="0">
                                                              <?php
                                                              for ($i = 0; $i < count($stredisko['typy']); $i++) {
                                                                  $castka = number_format($stredisko['castky'][$i], 2, '.', '&nbsp;');
                                                              ?>
                                                              <tr>
                                                                  <td>
                                                                      <div style="width:15px;height:15px;
                                                                          background:#<?= $stredisko['barvy'][$i] ?>;
                                                                          border-radius:50%;">
                                                                      </div>
                                                                  </td>
                                                                  <td>&nbsp;<?= htmlspecialchars($stredisko['typy'][$i], ENT_QUOTES, 'UTF-8') ?></td>
                                                                  <td align="right"><b><?= $castka ?></b></td>
                                                              </tr>
                                                              <?php } ?>
                                                          </table>
                                                      </div>

                                                      <div class="col-lg-7" align="center">
                                                          <canvas height="250" id="chart-area<?= $idx + 5 ?>"></canvas>
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
                        
                        
                                 <div class="row hs-annual-options-row">
                                      <div class="col-md-12 col-lg-12">
                                        <div class="panel panel-default hs-annual-card hs-annual-options-card"><div class="panel-heading"><h3 class="panel-title">Správa dat</h3></div>
                                          <div class="panel-body ng-binding">
                                             <?php
                                               $originalDate = $datum;
                                               $newDate = date("d.m.Y", strtotime($originalDate));

                                               $newYear = date("Y", strtotime($originalDate));

                                             ?>


                                                  <?php
                                                              //Schovat tl.refresh pokud je v db pozadavek na den
                                                              $radku_existuje_pozadavek = 0;
                                                              $SQL_mesic =$_SESSION["SQL_Mesic"];
                                                              $sql_existuje_pozadavek= "SELECT count(*) as CNT_pozadavek FROM `trzby_pozadavky_mesice` WHERE `sw_id` = $sw_id and `trm_rok` = '$SQL_ROK'";
                                                              //echo $sql_existuje_pozadavek;

                                                              $vysledek_existuje_pozadavek=$mysqli->query($sql_existuje_pozadavek);
                                                              //$radku_existuje_pozadavek=$vysledek_existuje_pozadavek->num_rows;
                                                              $sql_existuje_pozadavek=MySQLi_Fetch_Array($vysledek_existuje_pozadavek);

                                                              if ( ($sql_existuje_pozadavek["CNT_pozadavek"] ?? 0)>0  ) {
                                                                 ?>
                                                                    <div class="alert alert-success alert-dismissable">
                                                                      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                                                      <!-- <strong>Informace </strong> -->Váš požadavek na refresh dat pro tento rok byl zadán
                                                                    </div>
                                                                <?php
                                                              }




                                                     if ( ($sql_existuje_pozadavek["CNT_pozadavek"] ?? 0)==0 ) {
                                                  ?>
                                                              <div class="rada hs-annual-action-block">
                                                                 <form class="hs-annual-action-form" action="../str/akce/MesicniTrzby.php" method="POST" >
                                                                  <input type="hidden" class="form-control" name="sw_id" value="<?php echo $sw_id; ?>">
                                                                  <input type="hidden" class="form-control" name="RefreshMesicData" value="1">
                                                                  <input type="hidden" class="form-control" name="RefreshRok" value="<?php echo $SQL_ROK; ?>">
                                                                  <input type="hidden" class="form-control" name="RefreshMesic" value="<?php echo $_SESSION["SQL_Mesic"]; ?>">
                                                                  <input type="hidden" class="form-control" name="RefreshVsechnyZaRok" value="ANO">
                                                                  
                                                                  <button type="submit" class="btn btn-info hs-annual-action hs-annual-action--refresh"><i class="fa icon-refresh"></i>Refresh dat pro tento rok</button>
                                                                </form>
                                                              </div>
                                                  <?php
                                                      }
                                                  ?>


                                                  <div class="rada hs-annual-action-block">
                                                    <form class="hs-annual-action-form" action="../str/akce/TrzbyDleObsluhy.php" method="POST" >
                                                      <input type="hidden" class="form-control" name="sw_id" value="<?php echo $sw_id; ?>">
                                                      <input type="hidden" class="form-control" name="DeleteRokData" value="1">
                                                      <input type="hidden" class="form-control" name="DeleteRok" value="<?php echo $SQL_ROK; ?>">
                                                      <button type="submit" onClick="if(!confirm('Opravdu chcete SMAZAT data středisek a jednotlivců pro tento rok?')){return false;}" class="btn btn-danger hs-annual-action hs-annual-action--delete"><i class="fa icon-trash"></i>Smazat data pro tento rok</button>
                                                    </form>
                                                  </div>



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
                        $PosledniAktualizace = ($data_sql_PosledniAktualizace["tj_Datum"] ?? "");
                        
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
                 
                  
  </section>        
</section>
<?php
// Encode data as JSON, including quotes and HTML-sensitive characters in names.
$hs_annual_monthly_values = array();
foreach (array('sTrzbyCELKEM_', 'sProdejCELKEM_', 'UctenkyMesic') as $hs_prefix) {
    for ($hs_month = 1; $hs_month <= 12; $hs_month++) { $hs_annual_monthly_values[$hs_prefix.$hs_month] = ${$hs_prefix.$hs_month} ?? array(); }
}
$hs_annual_series = static function ($prefix, $index) use ($hs_annual_monthly_values) {
    $values = array();
    for ($month = 1; $month <= 12; $month++) {
        $value = $hs_annual_monthly_values[$prefix . $month][$index] ?? 0;
        $values[] = is_numeric($value) ? (float) $value : 0;
    }
    return $values;
};
$hs_annual_config = static function ($type, $labels, $datasets) {
    return array('type'=>$type, 'data'=>array('labels'=>array_values($labels), 'datasets'=>$datasets),
        'options'=>array('responsive'=>true,'maintainAspectRatio'=>false,'animation'=>array('duration'=>0),
        'legend'=>array('display'=>false), 'scales'=>$type === 'doughnut' ? array() : array('yAxes'=>array(array('ticks'=>array('beginAtZero'=>true))))));
};
$hs_annual_ring = static function ($labels, $values) use ($hs_annual_config) {
    return $hs_annual_config('doughnut', $labels, array(array('data'=>array_map(static function($v){return is_numeric($v)?(float)$v:0;}, array_values($values)))));
};
$hs_annual_configs = array();
$hs_annual_configs['canvas'] = $hs_annual_config('bar', $Mesice, array(
    array('label'=>'Služby','data'=>$hs_annual_series('sTrzbyCELKEM_', $StrObsluha)),
    array('label'=>'Prodej','data'=>$hs_annual_series('sProdejCELKEM_', $StrObsluha))));
$hs_annual_services = array(); $hs_annual_sales = array(); $hs_annual_receipts = array();
foreach ($aSeznamObsluhy as $hs_index=>$hs_name) {
    $hs_label = !empty($aSeznamStredisek[$hs_index]) ? $aSeznamStredisek[$hs_index] . ' · ' . $hs_name : $hs_name;
    $hs_annual_services[] = array('label'=>$hs_label,'data'=>$hs_annual_series('sTrzbyCELKEM_', $hs_index));
    $hs_annual_sales[] = array('label'=>$hs_label,'data'=>$hs_annual_series('sProdejCELKEM_', $hs_index));
    $hs_annual_receipts[] = $aUctenek_jednotlivci_rok[$hs_index] ?? 0;
}
$hs_annual_configs['canvas1'] = $hs_annual_config('line', $Mesice, $hs_annual_services);
$hs_annual_configs['canvas2'] = $hs_annual_config('line', $Mesice, $hs_annual_sales);
$hs_annual_configs['chart-area1'] = $hs_annual_ring($JmenoObsluhy, $TrzbyCelkemObsluhy);
$hs_annual_configs['chart-area2'] = $hs_annual_ring($JmenoObsluhy, $ProdejCelkemObsluhy);
$hs_annual_configs['chart-area3'] = $hs_annual_ring($aSeznamObsluhy, $hs_annual_receipts);
$hs_annual_centers = array();
foreach ($UctenkyStredisko as $hs_index=>$hs_name) { $hs_annual_centers[] = array('label'=>$hs_name,'data'=>$hs_annual_series('UctenkyMesic', $hs_index)); }
$hs_annual_configs['canvas3'] = $hs_annual_config('line', $Mesice, $hs_annual_centers);
foreach ($DataStredisek as $hs_index=>$hs_center) { $hs_annual_configs['chart-area'.($hs_index+5)] = $hs_annual_ring($hs_center['typy'], $hs_center['castky']); }
?>
<script>
window.hsAnnualYear = <?php echo (int)$SQL_ROK; ?>;
window.hsAnnualCurrency = <?php echo json_encode($_SESSION['Mena_Klienta'] ?? 'Kč', JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE); ?>;
window.hsAnnualConfigs = <?php echo json_encode($hs_annual_configs, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE); ?>;
(function(){var url=new URL(location.href);url.searchParams.set('NavratovyRok',window.hsAnnualYear);url.searchParams.set('obsluha',<?php echo (int)$StrObsluha; ?>);url.searchParams.delete('rok');history.replaceState(null,'',url.href);}());
</script>
