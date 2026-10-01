    
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

if (!defined('HS_CLIENT_UI_ROOT')) define('HS_CLIENT_UI_ROOT', dirname(__DIR__, 3));
require HS_CLIENT_UI_ROOT . '/str/strana/_zabezpeceni.php';

  // Prava na stranku aby neslo podstrcit stranku s GET URL v oblibenych
  if ($_SESSION["JePoduzivatel"]=="1" and in_array("Sklad", $_SESSION["SeznamPravPoduzivatele"])=="0") {
    echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=PrazdnaStrana\">";
    exit;
  }
  // Prava na stranku
  

require HS_CLIENT_UI_ROOT . '/fce/GeneratorBarev.php';
require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';
  //require_once '../../cfg/nastaveni.php';


$SkladyCNT = 0;
$jmenoStranky = "Přehled skladu";
$jmenoStrankyPopis = "Přehled aktuálního pohybu na skladě";

if ($_SESSION["pobocka_jmeno"]=="") {
 $pobocka_jmeno =  "Nevybrána" ;
}else {
  $pobocka_jmeno =  $_SESSION["pobocka_jmeno"] ;  
}




/*
Presmetrovani na nove sklady z xml
$_SESSION["pobocka_id"]
*/

    $sql_skladXml= "SELECT count(*) as 'Pocet' FROM `sklady_xml` WHERE `sw_id` = ".$_SESSION["pobocka_id"];
    $vysledek_sql_skladXml=$mysqli->query($sql_skladXml);
    $data_sql_skladXml=MySQLi_Fetch_Array($vysledek_sql_skladXml);

    if ($data_sql_skladXml["Pocet"]>0) {
      echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=SkladXml\">";
      exit;  
    }
    




//AKCE
if (!isset($_POST['idSkladuCombo']) || is_array($_POST['idSkladuCombo'])){$_POST['idSkladuCombo']='';}
$idSkladuCombo  =  htmlspecialchars($_POST['idSkladuCombo'], ENT_COMPAT);

if (!isset($_POST['SmazatSkladForm']) || is_array($_POST['SmazatSkladForm'])){$_POST['SmazatSkladForm']='';}
$SmazatSkladForm  =  htmlspecialchars($_POST['SmazatSkladForm'], ENT_COMPAT);

if (!isset($_POST['SmazatSkladFormId']) || is_array($_POST['SmazatSkladFormId'])){$_POST['SmazatSkladFormId']='';}
$SmazatSkladFormId  =  htmlspecialchars($_POST['SmazatSkladFormId'], ENT_COMPAT);



if (!isset($_POST['datum']) || is_array($_POST['datum'])){$_POST['datum']='';}
$datum  =  htmlspecialchars($_POST['datum'], ENT_COMPAT);

if ($datum=="") {
  $datum = date("d.m.Y"); 
}

$originalDate = $datum;
$newDate = date("d.m.Y", strtotime($originalDate));

$newYear = date("Y", strtotime($originalDate));


        // $datum ="2016-10-20";

        /*

            <div class="alert alert-success alert-dismissable">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
              Na stránce se pracuje...
            </div>
        */

        //////////////////////////////////////////////////////////////////////////////////////
        //DEMO
          if ($_SESSION["pobocka_id"]=="") {
            $sw_id = 0;
            $skupina_id = 0;  
          }else{
            $sw_id =  $_SESSION["pobocka_id"];  
          }
/*


                            ___ ___                __   .__                
                           /   |   \_____    ____ |  | _|__| ____    ____  
                          /    ~    \__  \ _/ ___\|  |/ /  |/    \  / ___\ 
                          \    Y    // __ \\  \___|    <|  |   |  \/ /_/  >
                           \___|_  /(____  /\___  >__|_ \__|___|  /\___  / 
                                 \/      \/     \/     \/       \//_____/  


*/

if ($_SESSION["k_id"]=="10") {
    //$Uzivatel_ID =11;
   // $sw_id = 1081;
    //$sw_id = 2387;    //BS
      //$Uzivatel_ID =11;
      //$sw_id = 2722;
    //$skupina_id = 5;  // BarberCooper
    //$skupina_id = 84; // Thomas
   //$sw_id = 961;
}
//echo $sw_id;

        //////////////////////////////////////////////////////////////////////////////////////


//$sw_id =  $_SESSION["pobocka_id"];
        //echo $sw_id;
        //$sw_id =  1081;//$_SESSION["pobocka_id"];

        //Mena
$Mena_Klienta = $_SESSION["Mena_Klienta"];   


if (!isset($_GET['rok']) || is_array($_GET['rok'])){$_GET['rok']='';}
$rok  =  htmlspecialchars($_GET['rok'], ENT_COMPAT);

$SQL_ROK = $_SESSION["SQL_ROK"] ; 


if ($rok=="1") {
  $TestSQL_ROK = $SQL_ROK +1; 
  $TestTabulka = "trzby_strediska_".$TestSQL_ROK;
  $TestSql = "SHOW TABLES LIKE '$TestTabulka'";
  $TestResult = $mysqli->query($TestSql);

  if ($TestResult->num_rows > 0) {
    $SQL_ROK = $SQL_ROK+1; 
    $_SESSION["SQL_ROK"] = $SQL_ROK;
  }     
}elseif ($rok=="0") {
  $TestSQL_ROK = $SQL_ROK -1;
  $TestTabulka =  "trzby_strediska_".$TestSQL_ROK;
  $TestSql = "SHOW TABLES LIKE '$TestTabulka'";
  $TestResult = $mysqli->query($TestSql);

  if ($TestResult->num_rows > 0) {
    $SQL_ROK = $SQL_ROK-1; 
    $_SESSION["SQL_ROK"] = $SQL_ROK;
  }
}


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
        <li class="active"><b><?php echo $pobocka_jmeno;?> </b><?php echo JePobockaOnline($sw_id,$mysqli) ?>  </li> <font color='#999' size='1'><?php echo "&nbsp; &nbsp; SW ID: ".$sw_id; ?></font>
      </ol>
    </div>           
  </div>      

  
 <section id="main-content" class="hs-stock-page">                


<?php  
//cnt
    $sql_skladcnt= "SELECT count(*) as 'COUNT', sw_sklad_id as 'PRVNI'  FROM `sklady` WHERE `sw_id` = $sw_id Order by 2 ASC";
    $vysledek_sql_skladcnt=$mysqli->query($sql_skladcnt);
    $data_sql_skladcnt=MySQLi_Fetch_Array($vysledek_sql_skladcnt);
    

    $sql_skladSeznam= "SELECT sw_sklad_id as 'PRVNI'  FROM `sklady` WHERE `sw_id` = $sw_id Order by 1 ASC";
    $vysledek_sql_skladSeznam=$mysqli->query($sql_skladSeznam);
    $data_sql_skladSeznam=MySQLi_Fetch_Array($vysledek_sql_skladSeznam);


    $sklady_count =$data_sql_skladcnt["COUNT"]; 
    $sklady_prvni =$data_sql_skladSeznam["PRVNI"]; 

    //$sklady_count = 1; //Trvalke odblokovano tlacitko refresh protoze kdyz byla prazdna db a novy klient nemel sklady na webu a nemohl udelat refresh
  
  // if ($sklady_count==0) {
              $sklady_datum ="";
              $sklady_akt_hodn_skladu = number_format("0", 2, ',', ' ');
              $sklady_akt_prijem_sklad = number_format("0", 2, ',', ' ');
              $sklady_hodn_skladu_bezDPH = number_format("0", 2, ',', ' ');
              $sklady_prijem_sklad_bezDPH = number_format("0", 2, ',', ' ');
              $sklady_akt_stav_zasob = "";
              $sklady_seznam_chyb_zbozi = "";
              


   //}else{

                                   
                                  if ($SmazatSkladForm=="1" and $SmazatSkladFormId !="" and $sw_id>0) {  //0 kvuli demu aby nesel smayat sklad

                                     $sql1 = "DELETE FROM `sklady` WHERE `sw_id`= ".$sw_id." and `sw_sklad_id` = ".$SmazatSkladFormId." LIMIT 1";
                                     $vysledek_smazani_skladu = @$mysqli->query($sql1);
                                     //echo $sql1;

                                      if ($vysledek_smazani_skladu) {

                                            ?>
                                              <div class="alert alert-success alert-dismissable">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                                Sklad byl smazán. 
                                              </div>
                                            <?php
                                          echo "<meta http-equiv=\"refresh\" content=\"0;URL=index.php?strana=Sklad\">";
                                         return;
                                       }else{
                                         $error = $mysqli->error;
                                          echo $error;
                                          return;
                                      }                                      
                                    
                                  }


?>
<?php
// Stav ručního refreshu skladu se pouze načte; ovládání je ve V199 dole ve sbalené sekci Správa dat.
$hs_stock_refresh_count = 0;
$hs_stock_refresh_state = null;
$sql_existuje_vsechny = "SELECT * FROM `sklady_pozadavky_refresh` WHERE `pozadavky_sw` = $sw_id order by pozadavky_id desc ;";
$vysledek_existuje_vsechny = $mysqli->query($sql_existuje_vsechny);
if ($vysledek_existuje_vsechny) {
    $hs_stock_refresh_count = $vysledek_existuje_vsechny->num_rows;
    $hs_stock_refresh_row = MySQLi_Fetch_Array($vysledek_existuje_vsechny);
    if (is_array($hs_stock_refresh_row)) {
        $hs_stock_refresh_state = $hs_stock_refresh_row["pozadavky_stav"];
    }
}
?>

<?php  



          //Pokud je sklad = 1 zobrazit jeden //vychozi sklad
          //echo $sklady_count;
          if ($sklady_count==1 or $sklady_count==0) {

            
          }else{
            ?>
                    <div class="col-md-12">


                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Výběr skladu <?php //echo $idSkladuCombo; ?></h3>
                                
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                           <form action="index.php?strana=Sklad" method="POST" id="idSkladuForm">                                  
                                              <select class="form-control" id="idSkladuCombo" name="idSkladuCombo">
                                                  <?php 

                                                   $sqldotaz= "SELECT * FROM `sklady` WHERE `sw_id` = $sw_id Order by 1 ASC";

                                                   if (!$sqldotaz) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                       $vysledek=$mysqli->query("$sqldotaz");
                                                                                                                               
                                                          while ($sklady_seznam=MySQLi_Fetch_Array($vysledek)):   
                                                                $SkladyCNT = $SkladyCNT + 1;
                                                                 $sklady_seznam["ts_Datum"];
                                                                 
                                                                 if ($idSkladuCombo==$sklady_seznam["sw_sklad_id"]) {
                                                                  $ComboSelected = " selected ";
                                                                 }else{
                                                                  $ComboSelected = "";
                                                                 }

                                                                 if ($sklady_seznam["sw_sklad_jmeno"]=="") {
                                                                  $JmenoSkladuCombo = "Výchozí sklad";
                                                                 }else{
                                                                  $JmenoSkladuCombo = $sklady_seznam["sw_sklad_jmeno"];
                                                                 }



                                                                 echo "<option $ComboSelected value=\"".$sklady_seznam["sw_sklad_id"]."\">".$JmenoSkladuCombo."</option>";
                                                          endwhile; 

                                                  ?>
                                                  
                                              </select>
                                           </form>     
                                </div>
                            </div>
                        </div>
                    </div>

            <?php 
          }



          //SkladSQL
              //Pro starou funckionalitu a demo
              if ($sklady_count==1 or $sklady_count==0) {
                $sql_sklad= "SELECT * FROM `sklady` WHERE `sw_id` = $sw_id ";  
                $idSkladuCombo=0; //Prvotni sklad je vzdy 0
              }else{
                //visesklad
                if ($idSkladuCombo=="") {
                  $idSkladuCombo = $sklady_prvni;  
                }

                $sql_sklad= "SELECT * FROM `sklady` WHERE `sw_id` = $sw_id  and `sw_sklad_id` = $idSkladuCombo ";  
              }

                
              

          

              $vysledek_sql_sklad=$mysqli->query($sql_sklad);
              $data_sql_sklad=MySQLi_Fetch_Array($vysledek_sql_sklad);
              $radku_existuje_pozadavek=$vysledek_sql_sklad->num_rows; 

          //echo $radku_existuje_pozadavek;

              $sklady_datum =date("d.m.Y v H:i", strtotime($data_sql_sklad["sklady_datum"])); 
              $sklady_akt_hodn_skladu = number_format($data_sql_sklad["sklady_akt_hodn_skladu"], 2, ',', ' ');
              $sklady_akt_prijem_sklad = number_format($data_sql_sklad["sklady_akt_prijem_sklad"], 2, ',', ' ');
              $sklady_hodn_skladu_bezDPH = number_format($data_sql_sklad["sklady_hodn_skladu_bezDPH"], 2, ',', ' ');
              $sklady_prijem_sklad_bezDPH = number_format($data_sql_sklad["sklady_prijem_sklad_bezDPH"], 2, ',', ' ');
              $sklady_akt_stav_zasob = $data_sql_sklad["sklady_akt_stav_zasob"];
              $sklady_seznam_chyb_zbozi = $data_sql_sklad["sklady_seznam_chyb_zbozi"];


  // }

/*
//SkladSQL
    $sql_sklad= "SELECT * FROM `sklady` WHERE `sw_id` = $sw_id ";


//ECHO $sql_sklad;

    $vysledek_sql_sklad=$mysqli->query($sql_sklad);
    $data_sql_sklad=MySQLi_Fetch_Array($vysledek_sql_sklad);
    $radku_existuje_pozadavek=$vysledek_sql_sklad->num_rows; 

//echo $radku_existuje_pozadavek;

    $sklady_datum =date("d.m.Y v H:i", strtotime($data_sql_sklad["sklady_datum"])); 
    $sklady_akt_hodn_skladu = number_format($data_sql_sklad["sklady_akt_hodn_skladu"], 2, ',', ' ');
    $sklady_akt_prijem_sklad = number_format($data_sql_sklad["sklady_akt_prijem_sklad"], 2, ',', ' ');
    $sklady_hodn_skladu_bezDPH = number_format($data_sql_sklad["sklady_hodn_skladu_bezDPH"], 2, ',', ' ');
    $sklady_prijem_sklad_bezDPH = number_format($data_sql_sklad["sklady_prijem_sklad_bezDPH"], 2, ',', ' ');
    $sklady_akt_stav_zasob = $data_sql_sklad["sklady_akt_stav_zasob"];
    $sklady_seznam_chyb_zbozi = $data_sql_sklad["sklady_seznam_chyb_zbozi"];
*/
    ?>

    <div class="row hs-stock-kpis">
      <div class="col-md-12 col-lg-6">
        <div class="row">
          <div class="col-md-6">
            <div class="panel panel-solid-success widget-mini">
              <div class="panel-body">
                <i class="icon-bar-chart"></i>
                <span class="total text-center"><b><font size="5"><?php echo $sklady_akt_hodn_skladu."&nbsp;".$Mena_Klienta; ?></font></b></span>
                <span class="title text-center">Aktuální hodnota skladu</span>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="panel widget-mini">
              <div class="panel-body">
                <i class="icon-bar-chart"></i>
                <span class="total text-center"><font size="5"><?php echo $sklady_akt_prijem_sklad."&nbsp;".$Mena_Klienta; ?></font></span>
                <span class="title text-center">Aktuální příjem na sklad</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-12 col-lg-6">
       <div class="row">
        <div class="col-md-6">
          <div class="panel panel-solid-danger widget-mini">
            <div class="panel-body">
              <i class="icon-bar-chart"></i>
              <span class="total text-center"><font size="5"><b><?php echo $sklady_hodn_skladu_bezDPH."&nbsp;".$Mena_Klienta ?></b></font></span>              
              <span class="title text-center">Hodnota skladu bez DPH</span>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="panel widget-mini">
            <div class="panel-body">
              <i class="icon-bar-chart"></i>
              <span class="total text-center"><font size="5"><?php echo $sklady_prijem_sklad_bezDPH."&nbsp;".$Mena_Klienta; ?></font></span>
              <span class="title text-center">Příjem na sklad bez DPH</span>
            </div>
          </div>
        </div>
      </div>           
    </div>  
  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="panel panel-default" >
                        <div class="panel-heading">
                          <h3 class="panel-title"><font face="tahoma"><b>Aktuální stav zásob</b></font></h3>
                          <div class="actions pull-right">
                            <i class="fa fa-expand"></i>
                            <i class="fa fa-chevron-down"></i>
                            <i class="fa fa-times"></i>
                          </div>
                        </div>
        
                                    <div class="panel-body">
                                      <div class="table-responsive">                                
                                      <table id="sklad1" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead>
                                          <tr>
                                            <td align="center"><b>#</b></td>
                                            <td align="center"><b>PLU</b></td>
                                            <td align="center"><b>Název</b></td>
                                            <td align="center"><b>Množství</b></td>
                                          </tr>
                                        </thead>

                                        <tbody>
                                          <?php
                                            echo $sklady_akt_stav_zasob; 
                                          ?>
                                       
                                         </tbody>
                                       </table>
                                      </div>
                                     </div>

                      </div>
                    </div>


                    <div class="col-md-6">
                    <div class="panel panel-default" >
                      <div class="panel-heading">
                        <h3 class="panel-title"><font face="tahoma"><b>Seznam chybějícího zboží</b></font></h3>
                        <div class="actions pull-right">
                          <i class="fa fa-expand"></i>
                          <i class="fa fa-chevron-down"></i>
                          <i class="fa fa-times"></i>
                        </div>
                      </div>
                                    <div class="panel-body">
                                      <div class="table-responsive">                                
                                      <table id="sklad2" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead>
                                          <tr>
                                            <td align="center"><b>#</b></td>
                                            <td align="center"><b>PLU</b></td>
                                            <td align="center"><b>Název</b></td>
                                            <td align="center"><b>Množství</b></td>
                                          </tr>
                                        </thead>

                                        <tbody>
                                          <?php
                                            echo $sklady_seznam_chyb_zbozi; 
                                          ?>
                                         
                                         </tbody>
                                       </table>
                                      </div>
                                     </div>
                       </div>
                     </div>
                  <br>
                                              
                  </div>

                  <div class="row hs-stock-management-row">
                    <div class="col-md-12">
                      <details class="hs-stock-data-management">
                        <summary>
                          <span class="hs-stock-data-management-icon" aria-hidden="true"></span>
                          <span class="hs-stock-data-management-label">Správa dat</span>
                        </summary>
                        <div class="hs-stock-data-management-actions">
                          <?php if ($hs_stock_refresh_state == 1) { ?>
                            <div class="alert alert-success alert-dismissable hs-stock-refresh-status">
                              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                              Váš požadavek na refresh skladových dat byl zadán
                            </div>
                          <?php } elseif ($hs_stock_refresh_state == 2) { ?>
                            <div class="alert alert-info alert-dismissable hs-stock-refresh-status">
                              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                              Právě probíhá synchronizace skladových dat... čekejte prosím
                            </div>
                          <?php } elseif ($hs_stock_refresh_state == 9) { ?>
                            <div class="alert alert-danger alert-dismissable hs-stock-refresh-status">
                              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                              Synchronizace dat z PC byla zrušena ze strany uživatele.
                            </div>
                          <?php } ?>

                          <?php if ($hs_stock_refresh_state == 9 || $hs_stock_refresh_count == 0) { ?>
                            <div class="hs-stock-management-action">
                              <form action="../str/akce/SkladyRefresh.php" method="POST">
                                <input type="hidden" class="form-control" name="sw_id" value="<?php echo $sw_id; ?>">
                                <input type="hidden" class="form-control" name="RefreshSkladuZPC" value="1">
                                <button type="submit" class="btn hs-stock-action hs-stock-action--refresh"><i class="fa icon-refresh"></i><?php echo ($hs_stock_refresh_state == 9 ? 'Opakovat akci' : 'Refresh skladových dat z PC'); ?></button>
                              </form>
                            </div>
                          <?php } ?>

                          <div class="hs-stock-management-action">
                            <form action="https://klient.hairsoft.cz/str/index.php?strana=Sklad" method="POST">
                              <input type="hidden" class="form-control" name="SmazatSkladForm" value="1">
                              <input type="hidden" class="form-control" name="SmazatSkladFormId" value="<?php echo $idSkladuCombo; ?>">
                              <button type="submit" onClick="if(!confirm('Opravdu chcete SMAZAT všechny informace o skladu? Tato akce je nevratná! Nové informace budou načteny do 20 minut.')){return false;}" class="btn hs-stock-action hs-stock-action--delete"><i class="fa icon-trash"></i>Smazat informace o skladu</button>
                            </form>
                          </div>
                        </div>
                      </details>
                    </div>
                  </div>


                               

















                               <div class="row">
                                <div class="col-md-12">
                                  <div class="panel panel-default">
                                    <div class="panel-body">
                                      <font size="-2">Synchronizováno: 
                                        <b>
                                          <?php 
                                          echo $sklady_datum;

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

                            </section>        
                          </section>

                          <!-- echo random_color(); -->










<noscript><style>.hs-stock-page #sklad1,.hs-stock-page #sklad2{visibility:visible!important}</style></noscript>
