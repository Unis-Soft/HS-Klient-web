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


  // Prava na stranku aby neslo podstrcit stranku s GET URL v oblibenych
  if ($_SESSION["JePoduzivatel"]=="1" and in_array("SMS", $_SESSION["SeznamPravPoduzivatele"])=="0") {
    echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=PrazdnaStrana\">";
    exit;
  }
  // Prava na stranku
  

  require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';
  //require_once '../../cfg/nastaveni.php';
  
  
  $jmenoStranky = "SMS a hovory";
  $jmenoStrankyPopis = "Přehled odeslaných a přijatých SMS a hovorů";
  
  $s_email = isset($_SESSION["k_email"]) ? (string) $_SESSION["k_email"] : "";
  $RezervaceCNT_SMS = 0;
  $selected = "";

  if ($_SESSION["pobocka_jmeno"]=="") {
    $pobocka_jmeno =  "Nevybrána" ;
  }else {
    $pobocka_jmeno =  $_SESSION["pobocka_jmeno"] ;  
  }

        //AKCE
        if (!isset($_POST['datum']) || is_array($_POST['datum'])){$_POST['datum']='';}
        $datum  =  htmlspecialchars($_POST['datum'], ENT_COMPAT);

        if (!isset($_POST['Uzivatel_POST_GUID']) || is_array($_POST['Uzivatel_POST_GUID'])){$_POST['Uzivatel_POST_GUID']='';}
        $Uzivatel_POST_GUID  =  htmlspecialchars($_POST['Uzivatel_POST_GUID'], ENT_COMPAT);

        if (!isset($_POST['VybranaPobockaID']) || is_array($_POST['VybranaPobockaID'])){$_POST['VybranaPobockaID']='';}
        $VybranaPobockaID  =  htmlspecialchars($_POST['VybranaPobockaID'], ENT_COMPAT);

        if (!isset($_POST['VybranaPobockaJmeno']) || is_array($_POST['VybranaPobockaJmeno'])){$_POST['VybranaPobockaJmeno']='';}
        $VybranaPobockaJmeno  =  htmlspecialchars($_POST['VybranaPobockaJmeno'], ENT_COMPAT);

        $VybranaPobockaJmenoBezDiakritiky = odstranitCeskouDiakritiku($VybranaPobockaJmeno);
        

        
        if ($datum=="") {
          $datum = date("d.m.Y"); 
        }
        
               $originalDate = $datum;
               $newDate = date("d.m.Y", strtotime($originalDate));
               
               $newYear = date("Y", strtotime($originalDate));
               
        
        // $datum ="2016-10-20";
        
       
         
        
       
       
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////DEBUG/////////////////////////////////////////////////////////////////
  
  $skupina_id = $_SESSION["skupina_id"];
  $sw_id = $_SESSION["pobocka_id"];

  if ($_SESSION["k_id"]=="10") {
    //$Uzivatel_ID =11;
   // $sw_id = 1081;
    
    //$skupina_id = 5;  // BarberCooper
   // $skupina_id = 84; // Thomas
   //$sw_id = 961;
  }
  
        //////////////////////////////////////////////////////////////////////////////////////
        //DEMO
        if ($_SESSION["pobocka_id"]=="") {
          $sw_id = 0;
          $skupina_id = 0;  
        }
        //////////////////////////////////////////////////////////////////////////////////////
  
  
  
  
        
        //Mena
        $Mena_Klienta = $_SESSION["Mena_Klienta"];   


     if (!isset($_GET['rok']) || is_array($_GET['rok'])){$_GET['rok']='';}
     $rok  =  htmlspecialchars($_GET['rok'], ENT_COMPAT);
  
     $SQL_ROK = $_SESSION["SQL_ROK"] ; 
  
  
      if ($rok=="1") {
          //$TestSQL_ROK = $SQL_ROK +1; 
          //$TestTabulka = "trzby_strediska_".$TestSQL_ROK;
          //$TestSql = "SHOW TABLES LIKE '$TestTabulka'";
          //$TestResult = $mysqli->query($TestSql);
         
          //if ($TestResult->num_rows > 0) {
            $SQL_ROK = $SQL_ROK+1; 
            $_SESSION["SQL_ROK"] = $SQL_ROK;
          //}     
      }elseif ($rok=="0") {
          //$TestSQL_ROK = $SQL_ROK -1;
          //$TestTabulka =  "trzby_strediska_".$TestSQL_ROK;
          //$TestSql = "SHOW TABLES LIKE '$TestTabulka'";
          //$TestResult = $mysqli->query($TestSql);
          
          //if ($TestResult->num_rows > 0) {
            $SQL_ROK = $SQL_ROK-1; 
            $_SESSION["SQL_ROK"] = $SQL_ROK;
          //}
      }





        if (!isset($_POST['PocetSmsFiltr']) || is_array($_POST['PocetSmsFiltr'])){$_POST['PocetSmsFiltr']='';}
        $PocetSmsFiltr  =  htmlspecialchars($_POST['PocetSmsFiltr'], ENT_COMPAT);


        if (!isset($_POST['PocetSmsFiltrMesic']) || is_array($_POST['PocetSmsFiltrMesic'])){$_POST['PocetSmsFiltrMesic']='';}
        $PocetSmsFiltrMesic  =  htmlspecialchars($_POST['PocetSmsFiltrMesic'], ENT_COMPAT);


        if (!isset($_POST['PocetSmsFiltrRok']) || is_array($_POST['PocetSmsFiltrRok'])){$_POST['PocetSmsFiltrRok']='';}
        $PocetSmsFiltrRok  =  htmlspecialchars($_POST['PocetSmsFiltrRok'], ENT_COMPAT);


        if (!isset($_POST['PocetSmsFiltrText']) || is_array($_POST['PocetSmsFiltrText'])){$_POST['PocetSmsFiltrText']='';}
        $PocetSmsFiltrText  =  htmlspecialchars($_POST['PocetSmsFiltrText'], ENT_COMPAT);



                    /*Rezervace  + objednavky + blacklistfiltr*/
                    if ($PocetSmsFiltr=="1" and $VybranaPobockaJmenoBezDiakritiky!="") {
                      
                        $PocetSmsFiltrText = trim($PocetSmsFiltrText);
                      
                       
                        /*zjisti DC*/
                        //SELECT sms_od_dc FROM `sms_odeslane_fronta` WHERE `sms_skupina_ID` = 167 and year(`sms_kdy_odeslat`) = 2023 and month(`sms_kdy_odeslat`) = 4 and `sms_stav` = 1 and `sms_zdroj` = 'R' and `sms_od_text` like '%Zdar%' and sms_od_dc <> '' group by sms_od_dc
                        $select_na_dc= "SELECT `sw_info`.`sw_jmeno_pobocky`, `sw_info`.`sw_id`, `sw_info`.`sw_dodatkove` FROM `sw_email_pobocka` join `k_uzivatele` on `sw_email_pobocka`.`k_id` = `k_uzivatele`.`k_id` join `sw_info` on `sw_info`.`sw_id` = `sw_email_pobocka`.`sw_id` WHERE `k_uzivatele`.`k_email` = '".$s_email."' and `sw_info`.`sw_jmeno_pobocky` = '".$VybranaPobockaJmeno."'";
                        //echo $select_na_dc;
                        if (!$select_na_dc) { die('Chyba pripojeni do DB!');}
                        $vysledek_na_dc=$mysqli->query("$select_na_dc");
                        $data_dc=MySQLi_Fetch_Array($vysledek_na_dc);
                        
                        if (is_null($data_dc["sw_dodatkove"])) {  
                          $sqlDC = "";
                        }else{
                          $sqlDC = $data_dc["sw_dodatkove"];
                        }

                        

                       //SELECT sum(`sms_odelano_sms`) as 'RezervaceCNT' FROM `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'R' and  `sms_skupina_ID` = 167 and `sms_stav` = 1 and `sms_od_text` like '%%' and   year(`sms_skutecne_odeslana`) = 2023 and month(`sms_skutecne_odeslana`) = 05
                       $select_na_rezervace= "SELECT  sum(`sms_odelano_sms`) as 'pocet' FROM `sms_odeslane_fronta` WHERE `sms_skupina_ID` = ".$skupina_id."  and year(`sms_kdy_odeslat`) = ".$PocetSmsFiltrRok." and month(`sms_kdy_odeslat`) = ".$PocetSmsFiltrMesic." and `sms_stav` = 1 and (`sms_od_dc` = '".$sqlDC."' or (`sms_zdroj` = 'R' and `sms_od_text` like '%".$VybranaPobockaJmenoBezDiakritiky."%'))";
                                                                        
                        if (!$select_na_rezervace) { die('Chyba pripojeni do DB!');}
                        $vysledek_na_rezervace=$mysqli->query("$select_na_rezervace");
                        $data_rezervace=MySQLi_Fetch_Array($vysledek_na_rezervace);
                        
                        if (is_null($data_rezervace["pocet"])) {  
                            $RezervaceCNT_SMS = 0;           
                        }else{
                            $RezervaceCNT_SMS = $data_rezervace["pocet"];           
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
  <section id="main-content" class="hs-sms-page">    
          <div class="row">
                <div class="col-md-6">

                        <div class="panel panel-default hs-sms-branch-card" >
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Výběr pobočky</b></font></h3>
                                
                            </div>
                            <div class="panel-body">
                                <div class="form-group">



                                <form action="index.php?strana=Sms" method="POST" class="form-horizontal form-border" name="ZmenaPobockyPravaForm" id="ZmenaPobockyPravaForm">
                                    <input type="hidden" class="form-control" name="Uzivatel_POST_GUID" value="<?php echo $Uzivatel_GUID; ?>">
                                    <input type="hidden" class="form-control" name="ZmenaPobockySMS" value="1">

                                      <select class="form-control input-lg" name="VybranaPobockaID" id="VybranaPobockaID" >
                                        <?php 
                                              //Zjisteni poctu vyplnenych emailu
                                              if ($_SESSION["k_email"]!="") {
                                                  $s_email = $_SESSION["k_email"];
                                                 
                                                 $select_na_pobocku= "SELECT `sw_info`.`sw_jmeno_pobocky`, `sw_info`.`sw_id` FROM `sw_email_pobocka` join `k_uzivatele` on `sw_email_pobocka`.`k_id` = `k_uzivatele`.`k_id` join `sw_info` on `sw_info`.`sw_id` = `sw_email_pobocka`.`sw_id` WHERE `k_uzivatele`.`k_email` = '$s_email'";

                                                 if (!$select_na_pobocku) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

                                                  $vysledek_na_pobocku=$mysqli->query($select_na_pobocku);
                                                  $radku_na_pobocku=$vysledek_na_pobocku->num_rows;                                       
                                                    
                                                    $prvni="1";

                                                    if ($VybranaPobockaID=="") {
                                                            $selected="selected=\"selected\"";
                                                    }

                                                    echo "<option ".$selected." value=\"\">Vše</option>";
                                                    while ($na_pobocku=MySQLi_Fetch_Array($vysledek_na_pobocku)):   
                                                             
                                                      
                                                        if ($VybranaPobockaID==$na_pobocku["sw_id"]) {
                                                            $selected="selected=\"selected\"";
                                                          }else{
                                                            $selected="";
                                                          }

                                                      echo "<option ".$selected." value=\"".$na_pobocku["sw_id"]."\">".$na_pobocku["sw_jmeno_pobocky"]."</option>";
                                                                                                              
                                                    endwhile;
                                                


                                                }  
                                         ?>
                                       </select>
                                                   <input type="submit" name="submit" value="Submit" style="display: none" >
                                 </form>

                                </div>
                            </div>
                        </div>
                </div>


                 <div class="col-md-6">

                        <div class="panel panel-default hs-sms-count-card" >
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Filtr odeslaných SMS</b></font></h3>
                                <span class="hs-sms-heading-note">Online rezervace, objednávky a blacklist</span>
                               
                            </div>
                            



                                <form class="form-horizontal form-border" action="index.php?strana=Sms" method="POST" id="VoucherOdDo">
                                                  <input type="hidden" class="form-control" name="PocetSmsFiltr" value="1">
                                                      <div class="panel-body">
                                                                  
                                                                    <div class="form-group">
                                                                    
                                                                        <div class="col-sm-2">
                                                                            <select style = "height:45px;font: inherit;" class="form-control input-lg" name="PocetSmsFiltrMesic">
                                                                               

                                                                              <?php   

                                                                                  if ($PocetSmsFiltrMesic=="") {
                                                                                    $PocetSmsFiltrMesic = date('m');
                                                                                  }


                                                                               ?>


                                                                               <option <?php echo $PocetSmsFiltrMesic=="01" ? 'selected=\"selected\"' : '' ?> value="01">Leden</option>
                                                                               <option <?php echo $PocetSmsFiltrMesic=="02" ? 'selected=\"selected\"' : '' ?> value="02">Únor</option>
                                                                               <option <?php echo $PocetSmsFiltrMesic=="03" ? 'selected=\"selected\"' : '' ?> value="03">Březen</option>
                                                                               <option <?php echo $PocetSmsFiltrMesic=="04" ? 'selected=\"selected\"' : '' ?> value="04">Duben</option>
                                                                               <option <?php echo $PocetSmsFiltrMesic=="05" ? 'selected=\"selected\"' : '' ?> value="05">Květen</option>
                                                                               <option <?php echo $PocetSmsFiltrMesic=="06" ? 'selected=\"selected\"' : '' ?> value="06">Červen</option>
                                                                               <option <?php echo $PocetSmsFiltrMesic=="07" ? 'selected=\"selected\"' : '' ?> value="07">Červenec</option>
                                                                               <option <?php echo $PocetSmsFiltrMesic=="08" ? 'selected=\"selected\"' : '' ?> value="08">Srpen</option>
                                                                               <option <?php echo $PocetSmsFiltrMesic=="09" ? 'selected=\"selected\"' : '' ?> value="09">Září</option>
                                                                               <option <?php echo $PocetSmsFiltrMesic=="10" ? 'selected=\"selected\"' : '' ?> value="10">Říjen</option>
                                                                               <option <?php echo $PocetSmsFiltrMesic=="11" ? 'selected=\"selected\"' : '' ?> value="11">Listopad</option>
                                                                               <option <?php echo $PocetSmsFiltrMesic=="12" ? 'selected=\"selected\"' : '' ?> value="12">Prosinec</option>
                                                                            </select>
                                                                        </div>

                                                                        <label class="control-label"></label>
                                                                        <div class="col-sm-2">
                                                                            <?php 
                                                                          

                                                                             ?>
                                                                            <select style = "height:45px;font: inherit;" class="form-control input-lg" name="PocetSmsFiltrRok">
                                                                               <?php 
                                                                                     
                                                                                    if ($PocetSmsFiltrRok=="") {
                                                                                      $PocetSmsFiltrRok = date('Y');
                                                                                    }

                                                                                ?>

                                                                                <option <?php echo ($PocetSmsFiltrRok==date('Y', strtotime('-1 year'))) ? 'selected=\"selected\"' : ''; ?> value="<?php echo date('Y', strtotime('-1 year')); ?>"><?php echo date('Y', strtotime('-1 year'));?></option>
                                                                                <option <?php echo ($PocetSmsFiltrRok==date('Y')) ? 'selected=\"selected\"' : ''; ?> value="<?php echo date('Y'); ?>"> <?php echo date('Y');?></option>

                                                                               
                                                                            </select>
                                                                        </div>

                                                                        <label class="control-label"></label>
                                                                        <div class="col-sm-3">
                                                                            <select style = "height:45px;font: inherit;" class="form-control input-lg" name="VybranaPobockaJmeno" id="VybranaPobockaJmeno" >
                                                                                  <?php 
                                                                                        //Zjisteni poctu vyplnenych emailu
                                                                                        if ($_SESSION["k_email"]!="") {
                                                                                            $s_email = $_SESSION["k_email"];
                                                                                           
                                                                                           $select_na_pobocku= "SELECT `sw_info`.`sw_jmeno_pobocky`, `sw_info`.`sw_id` FROM `sw_email_pobocka` join `k_uzivatele` on `sw_email_pobocka`.`k_id` = `k_uzivatele`.`k_id` join `sw_info` on `sw_info`.`sw_id` = `sw_email_pobocka`.`sw_id` WHERE `k_uzivatele`.`k_email` = '$s_email'";

                                                                                           if (!$select_na_pobocku) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

                                                                                            $vysledek_na_pobocku=$mysqli->query($select_na_pobocku);
                                                                                            $radku_na_pobocku=$vysledek_na_pobocku->num_rows;                                       
                                                                                              
                                                                                            $prvni="1";

                                                                                              //echo "<option ".$selected." value=\"\"></option>";
                                                                                              while ($na_pobocku=MySQLi_Fetch_Array($vysledek_na_pobocku)):   
                                                                                                                                                                                                       
                                                                                                  if ($VybranaPobockaJmeno==$na_pobocku["sw_jmeno_pobocky"]) {
                                                                                                      $selected="selected=\"selected\"";
                                                                                                    }else{
                                                                                                      $selected="";
                                                                                                    }

                                                                                                echo "<option ".$selected." value=\"".$na_pobocku["sw_jmeno_pobocky"]."\">".$na_pobocku["sw_jmeno_pobocky"]."</option>";
                                                                                                                                                        
                                                                                              endwhile;
                                                                                          


                                                                                          }  
                                                                                   ?>
                                                                                 </select>

                                                                        </div>
                                                                    
                                                                        
                                                                        
                                                                        

                                                                        
                                                                    </div>
                                                                    <?php
                                                                      $SMSVysledek = ($RezervaceCNT_SMS !== "" && is_numeric($RezervaceCNT_SMS)) ? (int)$RezervaceCNT_SMS : 0;
                                                                    ?>
                                                                    <div class="hs-sms-filter-actions">
                                                                        <div class="hs-sms-filter-result" data-hs-sms-count="<?php echo $SMSVysledek; ?>">
                                                                            <span class="hs-sms-filter-result__label">Počet odeslaných SMS</span>
                                                                            <strong class="hs-sms-filter-result__value"><?php echo $SMSVysledek; ?> SMS</strong>
                                                                        </div>
                                                                        <div class="hs-sms-filter-submit">
                                                                            <button type="submit" class="btn btn-primary">Zjistit počet</button>
                                                                        </div>
                                                                    </div>
                                                      </div>

                                                     
                                                </form>

                                
                        </div>
                </div>
          </div>














  
  <?php
     
     
                          // Zjisteni podle is_sw jakej ma pc id a dohledani SMS
                          $SQLdodatecnyNaID = "";
                          if ($VybranaPobockaID!="") {
                            
                              $select_na_pobocka= "SELECT `sw_info`.`sw_jmeno_pobocky`, `sw_info`.`sw_id`, `sw_info`.`sw_sn`, `sw_info`.`sw_dodatkove` FROM `sw_email_pobocka` join `k_uzivatele` on `sw_email_pobocka`.`k_id` = `k_uzivatele`.`k_id` join `sw_info` on `sw_info`.`sw_id` = `sw_email_pobocka`.`sw_id` WHERE  `sw_info`.`sw_id` =  ".$VybranaPobockaID;
                              if (!$select_na_pobocka) { die('Chyba pripojeni do DB!');}
                              $vysledek_na_pobocka=$mysqli->query("$select_na_pobocka");
                              $data_pobocka=MySQLi_Fetch_Array($vysledek_na_pobocka);

                            $SQLdodatecnyNaID = " and `sms_od_pcid` = '".$data_pobocka["sw_sn"]."' and `sms_od_dc` = '".$data_pobocka["sw_dodatkove"]."' ";
                          }






                          //Kolik utraceno ve skupine
                          $select_na_sumu_utraceno= "SELECT sum(`sms_odelano_sms`) as SUMA_UTRACENO  FROM `sms_odeslane_fronta` WHERE `sms_stav` = 1 and `sms_odelano_sms` > 0 and `sms_skupina_ID` ='$skupina_id' $SQLdodatecnyNaID";
                          //echo $select_na_sumu_utraceno;
                          if (!$select_na_sumu_utraceno) { die('Chyba pripojeni do DB!');}
                          $vysledek_na_sumu_utraceno=$mysqli->query("$select_na_sumu_utraceno");
                          $data_suma_utraceno=MySQLi_Fetch_Array($vysledek_na_sumu_utraceno);

                           if ($data_suma_utraceno["SUMA_UTRACENO"]=="") {
                             $suma_sms_odeslano = 0 ;
                           }else {
                             $suma_sms_odeslano = $data_suma_utraceno["SUMA_UTRACENO"];
                           }

                         //KREDIT vypocitany
                         $select_na_sumu= "SELECT sum(`sms_dobito`) as SUMA FROM `sms_kredit_dobiti` WHERE `sms_sw_skupina_id` ='$skupina_id'";
                          if (!$select_na_sumu) { die('Chyba pripojeni do DB!');}
                          $vysledek_na_sumu=$mysqli->query("$select_na_sumu");
                          $data_suma=MySQLi_Fetch_Array($vysledek_na_sumu);   
                            
                           if ($data_suma["SUMA"]=="") {
                             $suma_kreditu_dobito = 0 ;
                           }else {
                             $suma_kreditu_dobito = $data_suma["SUMA"]; 
                           }
                       
                         /*CENA 1 SMS*/
                         $select_na_1smscena= "SELECT `sms_kredit_cena1sms_cena1sms` FROM `sms_kredit_cena1sms` WHERE `sms_kredit_cena1sms_skupina` ='$skupina_id'";
                          if (!$select_na_1smscena) { die('Chyba pripojeni do DB!');}
                          $vysledek_na_1smscena=$mysqli->query("$select_na_1smscena");
                          $data_1smscena=MySQLi_Fetch_Array($vysledek_na_1smscena);   
                            
                           if ($data_1smscena["sms_kredit_cena1sms_cena1sms"]=="") {
                             $suma_kreditu_dobito_Cena = "Není" ;
                             $suma_kreditu_dobito_Cena_sms = 0;
                           }else {
                             $suma_kreditu_dobito_Cena = $data_1smscena["sms_kredit_cena1sms_cena1sms"];

                               if ($suma_kreditu_dobito==0 || !is_numeric($suma_kreditu_dobito_Cena) || (float)$suma_kreditu_dobito_Cena <= 0) {
                                  $suma_kreditu_dobito_Cena_sms=0;
                               }else {
                                  $suma_kreditu_dobito_Cena_sms=$suma_kreditu_dobito / (float)$suma_kreditu_dobito_Cena;
                               }
                            }

                           // Pro výpočty ceny používáme pouze numerickou hodnotu.
                           // Zobrazení "Není" zůstává zachované, pokud tarif není nastaven.
                           $hsSmsTariffNumeric = (is_numeric($suma_kreditu_dobito_Cena) && (float)$suma_kreditu_dobito_Cena > 0)
                             ? (float)$suma_kreditu_dobito_Cena
                             : 0.0;
                              
                             // FRONTA_ODESLANYCH_HNED_ZA_MESIC 
                             // SELECT COUNT(*) as 'FRONTA_ODESLANYCH_HNED_ZA_MESIC' FROM `sms_odeslane_fronta` WHERE `sms_stav` = '1' and `sms_skupina_ID` = '$data[sw_skupina_id]' and `sms_od_date` >= CURDATE() - INTERVAL DAY(CURDATE())-1 DAY
                                $select_FRONTA_ODESLANYCH_HNED_ZA_MESIC= "SELECT COUNT(*) as 'FRONTA_ODESLANYCH_HNED_ZA_MESIC' FROM `sms_odeslane_fronta` WHERE `sms_stav` = '1' and `sms_skupina_ID` = '$skupina_id' and `sms_od_date` >= CURDATE() - INTERVAL DAY(CURDATE())-1 DAY $SQLdodatecnyNaID" ;
                                if (!$select_FRONTA_ODESLANYCH_HNED_ZA_MESIC) { die('Chyba pripojeni do DB!');}
                                $vysledek_FRONTA_ODESLANYCH_HNED_ZA_MESIC=$mysqli->query("$select_FRONTA_ODESLANYCH_HNED_ZA_MESIC");
                                $FRONTA_FRONTA_ODESLANYCH_HNED_ZA_MESIC=MySQLi_Fetch_Array($vysledek_FRONTA_ODESLANYCH_HNED_ZA_MESIC);
                             
                             // FRONTA_ODESLANYCH_CEKAJICICH_ZA_MESIC
                             //SELECT COUNT(*) as 'FRONTA_ODESLANYCH_CEKAJICICH_ZA_MESIC' FROM `sms_odeslane_fronta` WHERE `sms_stav` = '1' and `sms_skupina_ID` = '$data[sw_skupina_id]' and `sms_kdy_odeslat` >= CURDATE() - INTERVAL DAY(CURDATE())-1 DAY
                                $select_FRONTA_ODESLANYCH_CEKAJICICH_ZA_MESIC= "SELECT COUNT(*) as 'FRONTA_ODESLANYCH_CEKAJICICH_ZA_MESIC' FROM `sms_odeslane_fronta` WHERE `sms_stav` = '1' and `sms_skupina_ID` = '$skupina_id' and `sms_kdy_odeslat` >= CURDATE() - INTERVAL DAY(CURDATE())-1 DAY $SQLdodatecnyNaID";
                                if (!$select_FRONTA_ODESLANYCH_CEKAJICICH_ZA_MESIC) { die('Chyba pripojeni do DB!');}
                                $vysledek_FRONTA_ODESLANYCH_CEKAJICICH_ZA_MESIC=$mysqli->query("$select_FRONTA_ODESLANYCH_CEKAJICICH_ZA_MESIC");
                                $FRONTA_ODESLANYCH_CEKAJICICH_ZA_MESIC=MySQLi_Fetch_Array($vysledek_FRONTA_ODESLANYCH_CEKAJICICH_ZA_MESIC);
                            
                            //Fronta sms za salon
                            //SELECT COUNT(*) as 'FRONTA1' FROM `sms_odeslane_fronta` WHERE `sms_stav` = '0' and `sms_skupina_ID` = ''
                                $select_na_fronta_salonu= "SELECT COUNT(*) as 'FRONTA_NEODESLANYCH' FROM `sms_odeslane_fronta` WHERE `sms_stav` = '0' and `sms_skupina_ID` = '$skupina_id' $SQLdodatecnyNaID";
                                //echo $select_na_fronta_salonu;
                                if (!$select_na_fronta_salonu) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_fronta_salonu=$mysqli->query("$select_na_fronta_salonu");
                                $suma_sms_ve_fronte=MySQLi_Fetch_Array($vysledek_na_fronta_salonu);
     
  ?>
  
  
  
  
  
                <div class="row">
                            <div class="col-md-3">
                                <div class="panel panel-solid-success widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo floor(($suma_kreditu_dobito_Cena_sms - $suma_sms_odeslano)) ." SMS"; ?></font></span>
                                        <span class="title text-center">SMS&nbsp;kredit</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $suma_sms_ve_fronte["FRONTA_NEODESLANYCH"]." SMS"; ?></font></span>
                                        <span class="title text-center">SMS&nbsp;fronta</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-solid-danger widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $suma_sms_odeslano ?></font></span>
                                        <span class="title text-center">Celkem&nbsp;odeslané</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $suma_kreditu_dobito_Cena."&nbsp;".$Mena_Klienta ?></font></span>              
                                        <span class="title text-center">Tarif&nbsp;SMS</span>
                                    </div>
                                </div>
                            </div>
                  </div>             
                
                <?php
                                       
                                       //echo $SQL_ROK;
                                       //Zjistit posledni 3 mesice
                                       $SQL_MESIC1 = date("n", strtotime("-0 month", strtotime(date("Y-m"))));
                                       $SQL_MESIC2 = date("n", strtotime("-1 month", strtotime(date("Y-m"))));
                                       $SQL_MESIC3 = date("n", strtotime("-2 month", strtotime(date("Y-m"))));

                                       $SQL_ROK1 = date("Y", strtotime("-0 month", strtotime(date("Y-m"))));
                                       $SQL_ROK2 = date("Y", strtotime("-1 month", strtotime(date("Y-m"))));
                                       $SQL_ROK3 = date("Y", strtotime("-2 month", strtotime(date("Y-m"))));
                                       
                                       

                                       $nazvy = array(1 => 'Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec');
                                      




                                       $sqldotazSMS= "SELECT 'Objednávky' as 'TYP',
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'O' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK1 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC1)  as '1', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'O' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK2 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC2)  as '2', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'O' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK3 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC3)  as '3' 
                                                      
                                                      
                                                      UNION ALL
                                                      
                                                      SELECT 'Rezervace',
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'R' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK1 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC1) as '1', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'R' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK2 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC2) as '2', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'R' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK3 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC3) as '3' 
                                                      
                                                      UNION ALL
                                                      
                                                      SELECT 'Svátek',
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'S' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK1 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC1) as '1', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'S' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK2 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC2) as '2', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'S' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK3 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC3) as '3' 
                                                      
                                                      UNION ALL
                                                      
                                                      SELECT 'Narozeniny',
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'N' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK1 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC1) as '1', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'N' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK2 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC2) as '2', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'N' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK3 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC3) as '3' 
                                                      
                                                      UNION ALL
                                                      
                                                      SELECT 'Chat',
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'CH' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK1 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC1) as '1', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'CH' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK2 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC2) as '2', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'CH' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK3 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC3) as '3' 
                                                      
                                                      UNION ALL
                                                      
                                                      SELECT 'Volné',
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = ' ' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK1 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC1) as '1', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = ' ' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK2 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC2) as '2', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = ' ' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK3 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC3) as '3' 
                                                      
                                                      UNION ALL
                                                      
                                                      SELECT 'Anonymní',
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'A' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK1 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC1) as '1', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'A' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK2 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC2) as '2', 
                                                      (select Case WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END FROM  `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'A' $SQLdodatecnyNaID and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id and YEAR(`sms_skutecne_odeslana`) = $SQL_ROK3 and MONTH(`sms_skutecne_odeslana`) = $SQL_MESIC3) as '3' 
                                                      " ;







                                     if (!$sqldotazSMS) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                       //echo  $sqldotazSMS;                                                                        
                                            $vysledekSMS=$mysqli->query("$sqldotazSMS") ;
                                            
                                            $pocet_radku_SMS = $vysledekSMS->num_rows or die($mysqli->error);                                       
                                             
                                            $TypSMS = 0;
                                            $hsSmsLineRgb = array('22,143,139','83,109,145','234,140,89','102,126,159','92,161,158','190,126,104','116,139,172');
                                            $hsSmsLineHex = array('168f8b','536d91','ea8c59','667e9f','5ca19e','be7e68','748bac');
                                            
                                           if ($pocet_radku_SMS<> 0) {
                                            
                                            while ($SMS_seznam=MySQLi_Fetch_Array($vysledekSMS)):   
                                                   
                                                  $TypSMS = $TypSMS + 1;
                                                  
                                                  $MesicTyp[$TypSMS] = $SMS_seznam["TYP"]; 
                                                    $Mesic1[$TypSMS] = $SMS_seznam["1"];
                                                    $Mesic2[$TypSMS] = $SMS_seznam["2"];
                                                    $Mesic3[$TypSMS] = $SMS_seznam["3"];
                                                   /* 
                                                    $Mesic4[$TypSMS] = $SMS_seznam["4"];
                                                    $Mesic5[$TypSMS] = $SMS_seznam["5"];
                                                    $Mesic6[$TypSMS] = $SMS_seznam["6"];
                                                    $Mesic7[$TypSMS] = $SMS_seznam["7"];
                                                    $Mesic8[$TypSMS] = $SMS_seznam["8"];
                                                    $Mesic9[$TypSMS] = $SMS_seznam["9"];
                                                   $Mesic10[$TypSMS] = $SMS_seznam["10"];
                                                   $Mesic11[$TypSMS] = $SMS_seznam["11"];
                                                   $Mesic12[$TypSMS] = $SMS_seznam["12"];
                                                   */
                                                   
                                                   $MesicTypBarva[$TypSMS] = $hsSmsLineRgb[($TypSMS - 1) % count($hsSmsLineRgb)];
                                                   $MesicTypBarvaPozadi[$TypSMS] = $hsSmsLineHex[($TypSMS - 1) % count($hsSmsLineHex)];
                                                   
                                                  
                                                  //echo $SMS_seznam["TYP"];
                                                  
                                            endwhile;
                                           } 
                                             
                                            
                
               $data1=""; 
               
               
               for ($i = 1; $i <= $TypSMS; $i++) {
              
                     if ($i == $TypSMS) {
                       $carka1 = "";
                     }else {
                       $carka1 = ",";
                     }

                 
                 $data1= $data1 . "{ ";
                 $data1= $data1 . "label: \"".$MesicTyp[$i]."\",";
                 $data1= $data1 . "backgroundColor: \"rgba(".$MesicTypBarva[$i].",0.1)\",";
                 $data1= $data1 . "borderColor: \"#".$MesicTypBarvaPozadi[$i]."\",";
                 $data1= $data1 . "pointBorderColor: \"#".$MesicTypBarvaPozadi[$i]."\",";
                 $data1= $data1 . "pointBackgroundColor: \"#".$MesicTypBarvaPozadi[$i]."\",";
                 $data1= $data1 . "pointBorderWidth: 1,";
                 $data1= $data1 . "data: [";
                 $data1= $data1 .  $Mesic1[$i].",".$Mesic2[$i].",".$Mesic3[$i];
               //$data1= $data1 .  $Mesic1[$i].",".$Mesic2[$i].",".$Mesic3[$i].",".$Mesic4[$i].",".$Mesic5[$i].",".$Mesic6[$i].",".$Mesic7[$i].",".$Mesic8[$i].",".$Mesic9[$i].",".$Mesic10[$i].",".$Mesic11[$i].",".$Mesic12[$i];
                 $data1= $data1 . "]";
                 $data1= $data1 . "} ";
                 $data1= $data1 . $carka1;
             
                 
                 
               }
             ?>
             
            
             <div class="row">
                   <div class="col-md-6">
                    <div class="panel panel-default hs-sms-line-card">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Dle typu SMS</b></font></h3>
                                <div class="actions pull-right">
                                   <!-- 
                                    <a href="index.php?strana=Sms&rok=0"><i class="fa fa-chevron-left"></i></a>
                                    <span><?php echo $SQL_ROK; ?></span>
                                    <a href="index.php?strana=Sms&rok=1"><i class="fa fa-chevron-right"></i></a>
                                    --> 
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                 
                                </div>
                                
                            </div>
                            <div class="panel-body text-center">
                                <?php
                                                                                                          
                                   if ($pocet_radku_SMS==0) {
                                       echo "Nejsou žádná data k zobrazení";
                                   }else {
                                   ?>
                                    <div>
                                        <canvas id="canvas1" height="96"></canvas>
                                        <!-- <canvas id="line1" height="50"></canvas> -->
                                    </div>
                                   
                                   <?php  
                                   }
                                 ?>
                                
                                
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                         <div class="panel panel-default hs-sms-type-card">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="Tahoma"><b>Zprávy dle typu</b></font></h3>
                                <div class="actions pull-right">
                                
                                    
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body">
                               <div class="row">
                                 
                                 
                                 <div class="col-lg-5" >

                                           <table border="0">
                                               
                                               <?php
                                                 $barva1 = '168f8b';
                                                 $barva2 = '536d91';
                                                 $barva3 = 'ea8c59';
                                                 $barva4 = '667e9f';
                                                 $barva5 = '5ca19e';
                                                 $barva6 = 'be7e68';
                                                 $barva7 = '748bac';
                                                 $barva8 = '8ca7b9';
                                                 $barva9 = 'b46f8e';
                                                 
                                                 
                                                 $BarvyTypy = "'#$barva8','#$barva1','#$barva2','#$barva3','#$barva4','#$barva5','#$barva6','#$barva7','#$barva9'";
                                                 
                                                    
                                                    //Anonymni SUM
                                                    // select CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'A' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id 
                                                    //+ Archiv
                                                    // SELECT (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta` WHERE sms_sablona_typ` = 'A' and `sms_stav` = 1 and `sms_skupina_ID` = 5) + (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) LSE 0 END as Celkem FROM `sms_odeslane_fronta_archiv` WHERE `sms_sablona_typ` = 'A' and `sms_stav` = 1 and `sms_skupina_ID` = 5) AS `sumf`;
                                                    
                                                    $select_1= "SELECT (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'A' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id) + (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta_archiv` WHERE `sms_sablona_typ` = 'A' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id $SQLdodatecnyNaID ) as Celkem";
                                                    //echo $select_1;
                                                    if (!$select_1) { die('Chyba pripojeni do DB!');}
                                                    $vysledek_1=$mysqli->query("$select_1");
                                                    $SumaSQL1=MySQLi_Fetch_Array($vysledek_1);
                                                    
                                                    //Volne SUM
                                                    $select_2= "SELECT (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta` WHERE `sms_sablona_typ` = ' ' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id) + (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta_archiv` WHERE `sms_sablona_typ` = ' ' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id $SQLdodatecnyNaID ) as Celkem ";
                                                    if (!$select_2) { die('Chyba pripojeni do DB!');}
                                                    $vysledek_2=$mysqli->query("$select_2");
                                                    $SumaSQL2=MySQLi_Fetch_Array($vysledek_2);
                                                    
                                                    //Chat SUM
                                                    $select_3= "SELECT (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'CH' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id) + (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta_archiv` WHERE `sms_sablona_typ` = 'CH' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id $SQLdodatecnyNaID ) as Celkem";
                                                    if (!$select_3) { die('Chyba pripojeni do DB!');}
                                                    $vysledek_3=$mysqli->query("$select_3");
                                                    $SumaSQL3=MySQLi_Fetch_Array($vysledek_3);
                                                    
                                                    //narozeniny SUM
                                                    $select_4= "SELECT (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'N' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id) + (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta_archiv` WHERE `sms_sablona_typ` = 'N' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id $SQLdodatecnyNaID ) as Celkem";
                                                    if (!$select_4) { die('Chyba pripojeni do DB!');}
                                                    $vysledek_4=$mysqli->query("$select_4");
                                                    $SumaSQL4=MySQLi_Fetch_Array($vysledek_4);
                                                    
                                                    //Svatky SUM
                                                    $select_5= "SELECT (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'S' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id) + (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta_archiv` WHERE `sms_sablona_typ` = 'S' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id $SQLdodatecnyNaID ) as Celkem";
                                                    if (!$select_5) { die('Chyba pripojeni do DB!');}
                                                    $vysledek_5=$mysqli->query("$select_5");
                                                    $SumaSQL5=MySQLi_Fetch_Array($vysledek_5);
                                                    
                                                    //Rezervace SUM
                                                    $select_6= "SELECT (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'R' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id) + (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta_archiv` WHERE `sms_sablona_typ` = 'R' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id $SQLdodatecnyNaID ) as Celkem";
                                                    if (!$select_6) { die('Chyba pripojeni do DB!');}
                                                    $vysledek_6=$mysqli->query("$select_6");
                                                    $SumaSQL6=MySQLi_Fetch_Array($vysledek_6);
                                                    
                                                    //Objednavky SUM
                                                    $select_7= "SELECT (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'O' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id) + (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta_archiv` WHERE `sms_sablona_typ` = 'O' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id $SQLdodatecnyNaID ) as Celkem";
                                                    if (!$select_7) { die('Chyba pripojeni do DB!');}
                                                    $vysledek_7=$mysqli->query("$select_7");
                                                    $SumaSQL7=MySQLi_Fetch_Array($vysledek_7);


                                                    //poradnik SUM
                                                    $select_8= "SELECT (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'P' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id) + (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta_archiv` WHERE `sms_sablona_typ` = 'P' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id $SQLdodatecnyNaID ) as Celkem";
                                                    if (!$select_8) { die('Chyba pripojeni do DB!');}
                                                    $vysledek_8=$mysqli->query("$select_8");
                                                    $SumaSQL8=MySQLi_Fetch_Array($vysledek_8);

                                                    //hodnoceni SUM
                                                    $select_9= "SELECT (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta` WHERE `sms_sablona_typ` = 'D' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id) + (SELECT CAse WHEN SUM(`sms_odelano_sms`) IS NOT NULL THEN SUM(`sms_odelano_sms`) ELSE 0 END as Celkem FROM `sms_odeslane_fronta_archiv` WHERE `sms_sablona_typ` = 'D' and `sms_stav` = 1 and `sms_skupina_ID` = $skupina_id $SQLdodatecnyNaID ) as Celkem";
                                                    //echo $select_9;
                                                    if (!$select_9) { die('Chyba pripojeni do DB!');}
                                                    $vysledek_9=$mysqli->query("$select_9");
                                                    $SumaSQL9=MySQLi_Fetch_Array($vysledek_9);


                                                    
                                                    
                                                 $TypySUM = $SumaSQL8["Celkem"].",".$SumaSQL7["Celkem"].",".$SumaSQL6["Celkem"].",".$SumaSQL5["Celkem"].",".$SumaSQL4["Celkem"].",".$SumaSQL3["Celkem"].",".$SumaSQL2["Celkem"].",".$SumaSQL1["Celkem"].",".$SumaSQL9["Celkem"];
                                                 
                                                 
                                                 
                                               ?>
                                               
                                               
                                               
                                               
                                               
                                                        
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $barva1; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Objednávky z programu</td>
                                                          <td align="right"><b>&nbsp;<?php echo $SumaSQL7["Celkem"]; ?></b></td>           
                                                        </tr> 
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $barva2; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Rezervační systém </td>
                                                          <td align="right"><b>&nbsp;<?php echo $SumaSQL6["Celkem"]; ?></b></td>           
                                                        </tr>                                                         <tr>
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $barva8; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Pořadník </td>
                                                          <td align="right"><b>&nbsp;<?php echo  $SumaSQL8["Celkem"]; ?></b></td>           
                                                        </tr>
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $barva3; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Svátky </td>
                                                          <td align="right"><b>&nbsp;<?php echo $SumaSQL5["Celkem"]; ?></b></td>           
                                                        </tr>
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $barva4; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Narozeniny </td>
                                                          <td align="right"><b>&nbsp;<?php echo $SumaSQL4["Celkem"]; ?></b></td>           
                                                        </tr>
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $barva5; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Chat </td>
                                                          <td align="right"><b>&nbsp;<?php echo  $SumaSQL3["Celkem"]; ?></b></td>           
                                                        </tr>
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $barva6; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Volné </td>
                                                          <td align="right"><b>&nbsp;<?php echo  $SumaSQL2["Celkem"]; ?></b></td>           
                                                        </tr>
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $barva7; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Pro majitele </td>
                                                          <td align="right"><b>&nbsp;<?php echo  $SumaSQL1["Celkem"]; ?></b></td>           
                                                        </tr>
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $barva9; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Hodnocení </td>
                                                          <td align="right"><b>&nbsp;<?php echo  $SumaSQL9["Celkem"]; ?></b></td>           
                                                        </tr>
                                                       
                                               
                                               <?php
                                                 //SMS z programu krome rezervaci
                                                 $SUMSMSbezRezervaci = $SumaSQL7["Celkem"]+ $SumaSQL5["Celkem"] + $SumaSQL4["Celkem"] + $SumaSQL3["Celkem"] + $SumaSQL2["Celkem"] + $SumaSQL1["Celkem"];
                                                 
                                                 
                                               ?>
                                               
                                               
                                           </table>
                                        
                                  </div>
                                <div>
                                  <div class="col-lg-7" align="center">
                                    <!-- <canvas id="doughnut1" height="250"></canvas> -->
                                        <?php
                                                                                                          
                                           if ($pocet_radku_SMS==0) {
                                               echo "Nejsou žádná data k zobrazení";
                                           }else {
                                           ?>
                                            <div>
                                                <canvas id="chart-area1" height="170"></canvas>
                                            </div>
                                           
                                           <?php  
                                           }
                                         ?>
                                        
                                        
                                  </div>
                                </div>
                                 
                                
                              </div>
                            </div>
                        </div>
                       </div> 
                    
                 </div>   
                 
                 
                 <div class="row">
                    <div class="col-md-6">
                            
                        <div class="panel panel-default hs-sms-credit-card" >
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b><span>Přehled dobití kreditu</span>: <span>Tarif</span> <?php echo $suma_kreditu_dobito_Cena."&nbsp;".$Mena_Klienta ?> / 1 SMS</b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <td align="center"><b>#</b></td>
                                            <td align="center"><b>Datum a čas</b></td>
                                            <td align="center"><b>Částka</b></td>
                                            <td align="center"><b>Výše kreditu</b></td>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        
                                                 $sqldotaz_kredit= "SELECT sms_timestamp,sms_dobito FROM `sms_kredit_dobiti` WHERE `sms_sw_skupina_id` = ".$skupina_id." order by sms_timestamp DESC";
                                                  if (!$sqldotaz_kredit) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                    $vysledek_kredit=$mysqli->query("$sqldotaz_kredit");                                       
                                                  
                                                  
                                                  $pocitadlo_radku_kredit = 0;
                                                  $PocitadloSUMkreditu = 0;
                                                  
                                                  
                                                  while ($option=MySQLi_Fetch_Array($vysledek_kredit)):   
                                                     
                                                     
                                                    $pocitadlo_radku_kredit = $pocitadlo_radku_kredit +1;   
                                                     
                                                     echo "<tr>";
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">$pocitadlo_radku_kredit</div></td>";
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">".date("d.m.Y v H:i", strtotime($option["sms_timestamp"]))."</div></td>";
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">".number_format($option["sms_dobito"], 2, ',', ' ')." ".$Mena_Klienta."</div></td>";
                                                     
                                                     $PocitadloSUMkreditu = $PocitadloSUMkreditu + ($option["sms_dobito"] ); 
                                                                                                          
                                                     $dobito_kredit = ($hsSmsTariffNumeric > 0) ? number_format($option["sms_dobito"] / $hsSmsTariffNumeric, 0, ',', ' ') . " SMS" : "0 SMS";
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">".$dobito_kredit."</div></td>";
                                                              
                                                     echo "</tr>";
                                                  endwhile;
                                        ?>
                                   
                                        <!--
                                           <tr>
                                            <td>Tiger Nixon</td>
                                            <td>System Architect</td>
                                            <td>Edinburgh</td>
                                            <td>61</td>
                                            <td>2011/04/25</td>
                                            <td>$320,800</td>
                                           </tr>
                                         -->
                                        
                                    </tbody>
                                </table>

                            </div>
                        </div>
                      </div>
                      
                      
                      
                       
                        
                        
                        
                        
                        
                        
                        
                        <div class="col-lg-6">
                         <div class="panel panel-default hs-sms-outgoing-card">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="Tahoma"><b>Přehled odchozích zpráv</b></font></h3>
                                <div class="actions pull-right">
                                
                                    
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body ">
                               <?php
                                  
                                  //Dnes
                                  $select_na_sumu_utraceno_dnes= "SELECT sum(`sms_odelano_sms`) as SUMA_UTRACENO FROM `sms_odeslane_fronta` WHERE `sms_stav` = '1' and `sms_odelano_sms` > 0 and `sms_skupina_ID` = '$skupina_id' and DATE(`sms_skutecne_odeslana`) = CURDATE() $SQLdodatecnyNaID ";
                                  if (!$select_na_sumu_utraceno_dnes) { die('Chyba pripojeni do DB!');}
                                  $vysledek_na_sumu_utraceno_dnes=$mysqli->query("$select_na_sumu_utraceno_dnes");
                                  $data_suma_utraceno_dnes=MySQLi_Fetch_Array($vysledek_na_sumu_utraceno_dnes);

                                   if ($data_suma_utraceno_dnes["SUMA_UTRACENO"]=="") {
                                     $suma_sms_odeslano_dnes = 0 ;
                                   }else {
                                     $suma_sms_odeslano_dnes = $data_suma_utraceno_dnes["SUMA_UTRACENO"];
                                   }
                                   
                                   
                                   //Tyden 7 dni
                                  $select_na_sumu_utraceno_tyden= "SELECT sum(`sms_odelano_sms`) as SUMA_UTRACENO FROM `sms_odeslane_fronta` WHERE `sms_stav` = 1 and `sms_odelano_sms` > 0 and `sms_skupina_ID` = '$skupina_id' and `sms_skutecne_odeslana` > TIMESTAMPADD(DAY , -7, NOW( ) ) $SQLdodatecnyNaID ";
                                  if (!$select_na_sumu_utraceno_tyden) { die('Chyba pripojeni do DB!');}
                                  $vysledek_na_sumu_utraceno_tyden=$mysqli->query("$select_na_sumu_utraceno_tyden");
                                  $data_suma_utraceno_tyden=MySQLi_Fetch_Array($vysledek_na_sumu_utraceno_tyden);

                                   if ($data_suma_utraceno_tyden["SUMA_UTRACENO"]=="") {
                                     $suma_sms_odeslano_tyden = 0 ;
                                   }else {
                                     $suma_sms_odeslano_tyden = $data_suma_utraceno_tyden["SUMA_UTRACENO"];
                                   }
                                   
                                   //mesic
                                  $select_na_sumu_utraceno_mesic= "SELECT sum(`sms_odelano_sms`) as SUMA_UTRACENO FROM `sms_odeslane_fronta` WHERE `sms_stav` = 1 and `sms_odelano_sms` > 0 and `sms_skupina_ID` = '$skupina_id' and `sms_skutecne_odeslana`  >= CURDATE() - INTERVAL DAY(CURDATE())-1 DAY $SQLdodatecnyNaID ";
                                  if (!$select_na_sumu_utraceno_mesic) { die('Chyba pripojeni do DB!');}
                                  $vysledek_na_sumu_utraceno_mesic=$mysqli->query("$select_na_sumu_utraceno_mesic");
                                  $data_suma_utraceno_mesic=MySQLi_Fetch_Array($vysledek_na_sumu_utraceno_mesic);

                                   if ($data_suma_utraceno_mesic["SUMA_UTRACENO"]=="") {
                                     $suma_sms_odeslano_mesic = 0 ;
                                   }else {
                                     $suma_sms_odeslano_mesic = $data_suma_utraceno_mesic["SUMA_UTRACENO"];
                                   }
                                   
                               ?>
                               
                               
                               
                               
                               
                               
                               
                               
                               
                               
                               
                               
                                                           <div class="table-responsive">
                               
                                                                      <table class="table table-bordered table-striped">
                                                                        <tbody>
                                                                                    <tr>
                                                                                        <td>Dnes odeslané</td>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo $suma_sms_odeslano_dnes; ?></div></td>
                                                                                        <?php
                                                                                          $celekm1 = $suma_sms_odeslano_dnes * $hsSmsTariffNumeric; 
                                                                                        ?>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo number_format($celekm1, 2, ',', ' ')." ".$Mena_Klienta; ?></div></td>
                                                                                    </tr> 
                                                                                    
                                                                                    <tr>
                                                                                        <td>Odeslané za týden</td>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo $suma_sms_odeslano_tyden; ?></div></td>
                                                                                         <?php
                                                                                          $celekm2 = $suma_sms_odeslano_tyden * $hsSmsTariffNumeric; 
                                                                                         ?>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo number_format($celekm2, 2, ',', ' ')." ".$Mena_Klienta; ?></div></td>
                                                                                    </tr>
                                                                                    
                                                                                    <tr>
                                                                                        <td>Odeslané za měsíc</td>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo $suma_sms_odeslano_mesic; ?></div></td>
                                                                                        <?php
                                                                                          $celekm3 = $suma_sms_odeslano_mesic * $hsSmsTariffNumeric; 
                                                                                        ?>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo number_format($celekm3, 2, ',', ' ')." ".$Mena_Klienta; ?></div></td>
                                                                                    </tr>
                                                                                    
                                                                                    <tr>
                                                                                      <td colspan="3"></td>
                                                                                    </tr>
                                                                              
                                                                                    <tr>
                                                                                        <td>Celkové SMS zprávy z programu</td>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo $SUMSMSbezRezervaci; ?></div></td>
                                                                                        <?php
                                                                                          $celekm4 = $SUMSMSbezRezervaci * $hsSmsTariffNumeric; 
                                                                                        ?>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo number_format($celekm4, 2, ',', ' ')." ".$Mena_Klienta; ?></div></td>
                                                                                    </tr>
                                                                                    
                                                                                    <tr>
                                                                                        <td>Objednávky z Rezervačního systému</td>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo ($SumaSQL8["Celkem"] +$SumaSQL6["Celkem"]); ?></div></td>
                                                                                        <?php
                                                                                          $celekm5 = ($SumaSQL8["Celkem"] + $SumaSQL6["Celkem"]) * $hsSmsTariffNumeric; 
                                                                                        ?>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo number_format($celekm5, 2, ',', ' ')." ".$Mena_Klienta; ?></div></td>
                                                                                    </tr>

                                                                                    <tr>
                                                                                        <td>SMS Hodnocení</td>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo ($SumaSQL9["Celkem"] ); ?></div></td>
                                                                                        <?php
                                                                                          $celekm6 = ($SumaSQL9["Celkem"] * $hsSmsTariffNumeric); 
                                                                                        ?>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo number_format($celekm6, 2, ',', ' ')." ".$Mena_Klienta; ?></div></td>
                                                                                    </tr>
                                                                                    
                                                                                    <tr>
                                                                                        <td><b>Celkem za SMS</b></td>
                                                                                        <td><div align="right" style="width: 70%;"><b><?php ; ?></b></div></td>
                                                                                        <td><div align="right" style="width: 70%;"><b><font color="#E25D5D"><?php echo number_format( $suma_sms_odeslano * $hsSmsTariffNumeric, 2, ',', ' ') ." ".$Mena_Klienta; ?></font></b></div></td>
                                                                                    </tr>
                                                                                    
                                                                                    <tr>
                                                                                        <td><b>Celková výše dobitého kreditu</b></td>
                                                                                        <td><div align="right" style="width: 70%;"><b><?php ; ?></b></div></td>
                                                                                        <td><div align="right" style="width: 70%;"><b><font color="#E25D5D"><?php echo number_format( $PocitadloSUMkreditu, 2, ',', ' ')." ".$Mena_Klienta; ?></font></b></div></td>
                                                                                    </tr>


                                                                        
                                                                        </tbody>
                                                                      </table>  
                               
                                         </div>
                               
                                <!-- Tlacitko kreditu -->
                               
                               <a href="https://www.hairsoft.cz/dobiti-kreditu-sms" target="_blank">
                                 <div class="panel panel-solid-danger widget-mini">
                                      <div class="panel-body">
                                          <!-- <i class="icon-bar-chart"></i> -->
                                          <span class="total text-center"><font size="6">DOBÍT KREDIT</font></span>
                                          <!-- <span class="title text-center">&nbsp;</span> -->
                                      </div>
                                  </div>
                               </a>
                               
                               
                               
                               
                            </div>
                         </div>
                        </div>
                        
                        
                        
<?php 
/*SMS a hovory*/
 ?>
                        <div class="col-lg-6">
                         <div class="panel panel-default hs-sms-incoming-card">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="Tahoma"><b>Přehled příchozích hovorů a SMS</b></font></h3>
                                <div class="actions pull-right">
                                
                                    
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body ">
                               <?php
                                  
                                  //Dnes hovory
                                  $select_na_sumu_hovory_dnes= "SELECT count(*) as HovoryDnes FROM `sms_hovory` WHERE  `hovory_salonID` = '$skupina_id' and DATE(`hovory_cas`) = CURDATE()";
                                  if (!$select_na_sumu_hovory_dnes) { die('Chyba pripojeni do DB!');}
                                  $vysledek_na_sumu_hovory_dnes=$mysqli->query("$select_na_sumu_hovory_dnes");
                                  $data_suma_hovory_dnes=MySQLi_Fetch_Array($vysledek_na_sumu_hovory_dnes);

                                   if ($data_suma_hovory_dnes["HovoryDnes"]=="") {
                                     $suma_sms_hovory_dnes = 0 ;
                                   }else {
                                     $suma_sms_hovory_dnes = $data_suma_hovory_dnes["HovoryDnes"];
                                   }
                                   
                                   
                                   //mesic
                                  $select_na_sumu_hovory_mesic= "SELECT count(*) as HovoryMesic FROM `sms_hovory` WHERE  `hovory_salonID` = '$skupina_id' and DATE(`hovory_cas`) >= CURDATE() - INTERVAL DAY(CURDATE())-1 DAY";
                                  if (!$select_na_sumu_hovory_mesic) { die('Chyba pripojeni do DB!');}
                                  $vysledek_na_sumu_hovory_mesic=$mysqli->query("$select_na_sumu_hovory_mesic");
                                  $data_suma_hovory_mesic=MySQLi_Fetch_Array($vysledek_na_sumu_hovory_mesic);

                                   if ($data_suma_hovory_mesic["HovoryMesic"]=="") {
                                     $suma_hovory_prijato_mesic = 0 ;
                                   }else {
                                     $suma_hovory_prijato_mesic = $data_suma_hovory_mesic["HovoryMesic"];
                                   }


                                   //celkem
                                  $select_na_sumu_hovory_celkem= "SELECT count(*) as Hovorycelkem FROM `sms_hovory` WHERE  `hovory_salonID` = '$skupina_id' ";
                                  if (!$select_na_sumu_hovory_celkem) { die('Chyba pripojeni do DB!');}
                                  $vysledek_na_sumu_hovory_celkem=$mysqli->query("$select_na_sumu_hovory_celkem");
                                  $data_suma_hovory_celkem=MySQLi_Fetch_Array($vysledek_na_sumu_hovory_celkem);

                                   if ($data_suma_hovory_celkem["Hovorycelkem"]=="") {
                                     $suma_hovory_prijato_celkem = 0 ;
                                   }else {
                                     $suma_hovory_prijato_celkem = $data_suma_hovory_celkem["Hovorycelkem"];
                                   }


//prichozi sms
 
                                  $select_na_sumu_sms_dnes= "SELECT count(*) as SmsDnes FROM `sms_prichozi_sms` WHERE  `sms_pri_salon_ID` = '$skupina_id' and DATE(`sms_pri_cas_prichodu`) = CURDATE() ";
                                  if (!$select_na_sumu_sms_dnes) { die('Chyba pripojeni do DB!');}
                                  $vysledek_na_sumu_sms_dnes=$mysqli->query("$select_na_sumu_sms_dnes");
                                  $data_suma_sms_dnes=MySQLi_Fetch_Array($vysledek_na_sumu_sms_dnes);

                                   if ($data_suma_sms_dnes["smsDnes"]=="") {
                                     $suma_sms_sms_dnes = 0 ;
                                   }else {
                                     $suma_sms_sms_dnes = $data_suma_sms_dnes["smsDnes"];
                                   }
                                   
                                   
                                   //mesic
                                  $select_na_sumu_sms_mesic= "SELECT count(*) as SmsMesic FROM `sms_prichozi_sms` WHERE  `sms_pri_salon_ID` = '$skupina_id' and DATE(`sms_pri_cas_prichodu`) >= CURDATE() - INTERVAL DAY(CURDATE())-1 DAY";
                                  if (!$select_na_sumu_sms_mesic) { die('Chyba pripojeni do DB!');}
                                  $vysledek_na_sumu_sms_mesic=$mysqli->query("$select_na_sumu_sms_mesic");
                                  $data_suma_sms_mesic=MySQLi_Fetch_Array($vysledek_na_sumu_sms_mesic);

                                   if ($data_suma_sms_mesic["SmsMesic"]=="") {
                                     $suma_sms_prijato_mesic = 0 ;
                                   }else {
                                     $suma_sms_prijato_mesic = $data_suma_sms_mesic["SmsMesic"];
                                   }


                                   //celkem
                                  $select_na_sumu_sms_celkem= "SELECT count(*) as SmsCelkem FROM `sms_prichozi_sms` WHERE  `sms_pri_salon_ID` = '$skupina_id' ";
                                  if (!$select_na_sumu_sms_celkem) { die('Chyba pripojeni do DB!');}
                                  $vysledek_na_sumu_sms_celkem=$mysqli->query("$select_na_sumu_sms_celkem");
                                  $data_suma_sms_celkem=MySQLi_Fetch_Array($vysledek_na_sumu_sms_celkem);

                                   if ($data_suma_sms_celkem["SmsCelkem"]=="") {
                                     $suma_sms_prijato_celkem = 0 ;
                                   }else {
                                     $suma_sms_prijato_celkem = $data_suma_sms_celkem["SmsCelkem"];
                                   }









                                  
                                   
                                  
                                   
                               ?>
                               
                               
                               
                               
                               
                               
                               
                               
                               
                               
                               
                               
                                                           <div class="table-responsive">
                               
                                                                      <table class="table table-bordered table-striped">
                                                                        <tbody>
                                                                                    <tr>
                                                                                        <td>Hovory dnes</td>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo $suma_sms_hovory_dnes; ?></div></td>
                                                                                    </tr> 
                                                                                    
                                                                                    <tr>
                                                                                        <td>Hovory aktuální měsíc</td>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo $suma_hovory_prijato_mesic; ?></div></td>
                                                                                    </tr>
                                                                                    
                                                                                    <tr>
                                                                                        <td>Hovory celkem</td>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo $suma_hovory_prijato_celkem; ?></div></td>
                                                                                    </tr>
                                                                                    
                                                                                    <tr>
                                                                                      <td colspan="3"></td>
                                                                                    </tr>

                                                                                     <tr>
                                                                                        <td>Příchozí SMS dnes</td>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo $suma_sms_sms_dnes; ?></div></td>
                                                                                                                                                                                
                                                                                    </tr> 
                                                                                    
                                                                                    <tr>
                                                                                        <td>Příchozí SMS aktuální měsíc</td>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo $suma_sms_prijato_mesic; ?></div></td>
                                                                                                                                                                                
                                                                                    </tr>
                                                                                    
                                                                                    <tr>
                                                                                        <td>Příchozí SMS Celkem</td>
                                                                                        <td><div align="right" style="width: 70%;"><?php echo $suma_sms_prijato_celkem; ?></div></td>
                                                                                        
                                                                                        
                                                                                    </tr>
                                                                              
                                                                                   

                                                                        
                                                                        </tbody>
                                                                      </table>  
                               
                                         </div>
                               
                             
                               
                               
                               
                               
                            </div>
                         </div>
                        </div>
                        
                        
                        
                        
                        
                        
                        
                               
                              
                              
                              
                          
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                        
                    
                  </div>
</section>        
</section>



<script>
window.hsSmsChartData = {
  line: {
    labels: [<?php echo $DataMesice; ?>],
    datasets: [<?php echo $data1; ?>]
  },
  doughnut: {
    labels: ['Pořadník','Objednávky z programu','Rezervační systém','Svátky','Narozeniny','Chat','Volné','Pro majitele','Hodnocení'],
    data: [<?php echo $TypySUM; ?>],
    colors: [<?php echo $BarvyTypy; ?>]
  }
};
</script>





      
      

 