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

  // Prava na stranku aby neslo podstrcit stranku s GET URL v oblibenych
  if ($_SESSION["JePoduzivatel"]=="1" and in_array("Hodnoceni", $_SESSION["SeznamPravPoduzivatele"])=="0") {
    echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=PrazdnaStrana\">";
    exit;
  }
  // Prava na stranku 

  require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';
  
  $jmenoStranky = "Nastavení hodnocení spokojenosti";
  $jmenoStrankyPopis = "Administrace hodnocení spokojenosti";
  
  if ($_SESSION["pobocka_jmeno"]=="") {
    $pobocka_jmeno =  "Nevybrána" ;
  }else {
    $pobocka_jmeno =  $_SESSION["pobocka_jmeno"] ;  
  }
  
     //////////////////////////////////////////////////////////////////////////////////////
        //DEMO
        if ($_SESSION["pobocka_id"]=="") {
          $sw_id = 0;
        }
        //////////////////////////////////////////////////////////////////////////////////////

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////DEBUG/////////////////////////////////////////////////////////////////

  $Uzivatel_ID =$_SESSION["k_id"];

  if ($_SESSION["k_id"]=="10") {
     //$Uzivatel_ID =11;
     //$sw_id = 1081;
  }
  //$Uzivatel_ID = 11;


/*// Zapne zobrazování všech chyb
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);  
*/






  /*SABLONY*/
  $TextEmailu = addslashes("<font face='Calibri, sans-serif'> Dobrý den, <br> <br>děkujeme vám za vaši návštěvu <u>%DATUM_NAVSTEVY% - %JMENO_PROVOZOVNY%.</u> <br> <br>Velmi nám záleží na spokojenosti našich zákazníků a neustálém zkvalitňování služeb. <br>Budeme rádi, když vyplníte dotazník níže a podělíte se s námi o to, jak jste byli spokojeni. <br> <br>Vyplnění vám zabere 1-2 minuty a my se tak dozvíme cennou zpětnou vazbu. <br> <br> <br> %ODKAZ_SPOKOJENOST% <br> <br> <br>Děkujeme a přejeme příjemný den. <br>%JMENO_PROVOZOVNY% <br> </font>");
  $TextSMS ="Dobry den, budeme radi za vyplneni dotazniku spokojenosti. Vase navsteva byla %DATUM_NAVSTEVY% - %JMENO_PROVOZOVNY% %ODKAZ_SPOKOJENOST% ";
  $GoogleReviewDefaultSMS = "Dobrý den, děkujeme za Vaši návštěvu %DATUM_NAVSTEVY%. Budeme rádi, když nás ohodnotíte na Google: %ODKAZ_SPOKOJENOST% %JMENO_PROVOZOVNY%";
  $GoogleReviewMode = "internal";
  $GoogleReviewUrl = "";
  $GoogleReviewSms = $GoogleReviewDefaultSMS;
  $GoogleReviewTableExists = false;

  
  if (!isset($_GET['VychoziSablonaEmail']) || is_array($_GET['VychoziSablonaEmail'])){$_GET['VychoziSablonaEmail']='';}
  $VychoziSablonaEmail = htmlspecialchars($_GET['VychoziSablonaEmail'], ENT_COMPAT);

  if (!isset($_GET['VychoziSablonaSMS']) || is_array($_GET['VychoziSablonaSMS'])){$_GET['VychoziSablonaSMS']='';}
  $VychoziSablonaSMS = htmlspecialchars($_GET['VychoziSablonaSMS'], ENT_COMPAT);










  //EDITACE oTAZKY

  if (!isset($_POST['HodnoceniID']) || is_array($_POST['HodnoceniID'])){$_POST['HodnoceniID']='';}
  $HodnoceniID = htmlspecialchars($_POST['HodnoceniID'], ENT_COMPAT);

  if (!isset($_POST['HodnoceniVeta']) || is_array($_POST['HodnoceniVeta'])){$_POST['HodnoceniVeta']='';}
  $HodnoceniVeta = htmlspecialchars($_POST['HodnoceniVeta'], ENT_COMPAT);

  if (!isset($_POST['HodnoceniVetaTyp']) || is_array($_POST['HodnoceniVetaTyp'])){$_POST['HodnoceniVetaTyp']='';}
  $HodnoceniVetaTyp = htmlspecialchars($_POST['HodnoceniVetaTyp'], ENT_COMPAT);

  if (!isset($_POST['editovat_otazku']) || is_array($_POST['editovat_otazku'])){$_POST['editovat_otazku']='';}
  $editovat_otazku = htmlspecialchars($_POST['editovat_otazku'], ENT_COMPAT);

  if (!isset($_POST['smazat_otazku']) || is_array($_POST['smazat_otazku'])){$_POST['smazat_otazku']='';}
  $smazat_otazku = htmlspecialchars($_POST['smazat_otazku'], ENT_COMPAT);
  
  if (!isset($_POST['HodnoceniVetaTypHodnoceni']) || is_array($_POST['HodnoceniVetaTypHodnoceni'])){$_POST['HodnoceniVetaTypHodnoceni']='';}
  $HodnoceniVetaTypHodnoceni = htmlspecialchars($_POST['HodnoceniVetaTypHodnoceni'], ENT_COMPAT);

  if (!isset($_POST['HodnoceniVetaPoradi']) || is_array($_POST['HodnoceniVetaPoradi'])){$_POST['HodnoceniVetaPoradi']='';}
  $HodnoceniVetaPoradi = htmlspecialchars($_POST['HodnoceniVetaPoradi'], ENT_COMPAT);
  
  if ($editovat_otazku=="1") {
      $HodnoceniID_edit =  $HodnoceniID;
      $HodnoceniVeta_edit =  $HodnoceniVeta;
      $HodnoceniVetaTyp_edit =  $HodnoceniVetaTyp;
      $HodnoceniVetaTypHodnoceni_edit =  $HodnoceniVetaTypHodnoceni;
      $HodnoceniVetaPoradi_edit =  $HodnoceniVetaPoradi ;
  }

  
  if (!isset($_POST['ZpusobOdeslaniDotazniku']) || is_array($_POST['ZpusobOdeslaniDotazniku'])){$_POST['ZpusobOdeslaniDotazniku']='';}
  $ZpusobOdeslaniDotazniku = htmlspecialchars($_POST['ZpusobOdeslaniDotazniku'], ENT_COMPAT);

  if (!isset($_POST['TypOdeslani']) || is_array($_POST['TypOdeslani'])){$_POST['TypOdeslani']='';}
  $TypOdeslani = htmlspecialchars($_POST['TypOdeslani'], ENT_COMPAT);

  if (!isset($_POST['TypOdeslaniDnyDopredu']) || is_array($_POST['TypOdeslaniDnyDopredu'])){$_POST['TypOdeslaniDnyDopredu']='';}
  $TypOdeslaniDnyDopredu = htmlspecialchars($_POST['TypOdeslaniDnyDopredu'], ENT_COMPAT);

  if (!isset($_POST['HodnoceniKolikratDoroka']) || is_array($_POST['HodnoceniKolikratDoroka'])){$_POST['HodnoceniKolikratDoroka']='';}
  $HodnoceniKolikratDoroka = htmlspecialchars($_POST['HodnoceniKolikratDoroka'], ENT_COMPAT);

  /*if (!isset($_POST['HodnoceniKolikratDorokaDatum']) || is_array($_POST['HodnoceniKolikratDorokaDatum'])){$_POST['HodnoceniKolikratDorokaDatum']='';}
  $HodnoceniKolikratDorokaDatum = htmlspecialchars($_POST['HodnoceniKolikratDorokaDatum']);*/

 
  if (!isset($_POST['NastavitDatumOmezeniHodnoceni']) || is_array($_POST['NastavitDatumOmezeniHodnoceni'])){$_POST['NastavitDatumOmezeniHodnoceni']='';}
  $NastavitDatumOmezeniHodnoceni   = htmlspecialchars($_POST['NastavitDatumOmezeniHodnoceni'], ENT_COMPAT);

  





  //zmena strediska
  if (!isset($_POST['VyberStrediska']) || is_array($_POST['VyberStrediska'])){$_POST['VyberStrediska']='';}
  $VyberStrediska = htmlspecialchars($_POST['VyberStrediska'], ENT_COMPAT);

  if (!isset($_POST['VybraneStrediskoID']) || is_array($_POST['VybraneStrediskoID'])){$_POST['VybraneStrediskoID']='';}
  $VybraneStrediskoID = htmlspecialchars($_POST['VybraneStrediskoID'], ENT_COMPAT);

  if ($VybraneStrediskoID!="") {
      $_SESSION["VybraneStrediskoID"] = $VybraneStrediskoID;
  }

  if (!isset($_POST['ZmenaStrediskaStavHodnoceniAkce']) || is_array($_POST['ZmenaStrediskaStavHodnoceniAkce'])){$_POST['ZmenaStrediskaStavHodnoceniAkce']='';}
  $ZmenaStrediskaStavHodnoceniAkce = htmlspecialchars($_POST['ZmenaStrediskaStavHodnoceniAkce'], ENT_COMPAT);

  if (!isset($_POST['NovyStavHodnoceni']) || is_array($_POST['NovyStavHodnoceni'])){$_POST['NovyStavHodnoceni']='';}
  $NovyStavHodnoceni = htmlspecialchars($_POST['NovyStavHodnoceni'], ENT_COMPAT);
  

  //Zmena stavu hodnoceni u strediska zapnuto/vypnuto
  if ($ZmenaStrediskaStavHodnoceniAkce=="1") {

        if ($NovyStavHodnoceni=="0") {
            $DatumOD = "NULL";        
            $sql = "UPDATE `HodnoceniStrediska` SET `HodnoceniStrediskaZpusobZaslani` = 'VYPNUTO', `HodnoceniPlatnostOd` = ".$DatumOD.",`HodnoceniStrediskaZapnuto` = '".$NovyStavHodnoceni."' WHERE `HodnoceniStrediskaStredisko` = ".$VybraneStrediskoID." and `HodnoceniStrediskaPobocka` = ".$sw_id."; ";
        }else{
            $sql = "UPDATE `HodnoceniStrediska` SET `HodnoceniStrediskaZapnuto` = '".$NovyStavHodnoceni."' WHERE `HodnoceniStrediskaStredisko` = ".$VybraneStrediskoID." and `HodnoceniStrediskaPobocka` = ".$sw_id."; ";
        }
        
        $vysledek_zmeny_stavu = @$mysqli->query($sql);

        if ($vysledek_zmeny_stavu) {
         }else{
           $error = $mysqli->error; 
            echo $error; 
            exit;
        }
  }

   //Zmena typu odesilani sms/ email /vypnuto
  if ($ZpusobOdeslaniDotazniku=="1") {

        if ($TypOdeslani=="VYPNUTO") {
            $DatumOD = "NULL";        
        }else{
            $DatumOD = "NOW()";        
        }        



        $sql = "UPDATE `HodnoceniStrediska` SET `HodnoceniKolikratDoroka` = ".$HodnoceniKolikratDoroka.",`HodnoceniPlatnostOd` = ".$DatumOD.",`HodnoceniPlatnostOd` = ".$DatumOD.",`HodnoceniStrediskaZpusobZaslani` = '".$TypOdeslani."',`TypOdeslaniDnyDopredu` = '".$TypOdeslaniDnyDopredu."' WHERE `HodnoceniStrediskaStredisko` = ".$VybraneStrediskoID." and `HodnoceniStrediskaPobocka` = ".$sw_id."; ";
        $vysledek_zmeny_stavu = @$mysqli->query($sql);

        if ($vysledek_zmeny_stavu) {
         }else{
           $error = $mysqli->error; 
            echo $error; 
            exit;
        }
  }

  // Reset omeyerni hodnoceni datum na dnes
  if ($NastavitDatumOmezeniHodnoceni=="1") {

       
        $sql = "UPDATE `HodnoceniStrediska` SET `HodnoceniKolikratDorokaDatum` = NOW() WHERE `HodnoceniStrediskaStredisko` = ".$VybraneStrediskoID." and `HodnoceniStrediskaPobocka` = ".$sw_id."; ";
        echo $sql;
        $vysledek_zmeny_stavu = @$mysqli->query($sql);

        if ($vysledek_zmeny_stavu) {
         }else{
           $error = $mysqli->error; 
            echo $error; 
            exit;
        }
  }




  //-----
                   //zjisti zda ma zapnuto stredisko
                   if ( $_SESSION["VybraneStrediskoID"]=="") {
                        $sql_existuje_stredisko_stav= "SELECT `HodnoceniPlatnostOd` ,`HodnoceniStrediskaZapnuto`,`HodnoceniStrediskaZpusobZaslani`,`TypOdeslaniDnyDopredu`,`HodnoceniKolikratDoroka` ,`HodnoceniKolikratDorokaDatum` FROM `HodnoceniStrediska` WHERE `HodnoceniStrediskaPobocka` = ".$sw_id." order by `HodnoceniStrediskaID` ASC limit 1";
                        $_SESSION["VybraneStrediskoID"] = $sw_id;
                   }else{
                        $sql_existuje_stredisko_stav= "SELECT `HodnoceniPlatnostOd` ,`HodnoceniStrediskaZapnuto`,`HodnoceniStrediskaZpusobZaslani`,`TypOdeslaniDnyDopredu` ,`HodnoceniKolikratDoroka` ,`HodnoceniKolikratDorokaDatum` FROM `HodnoceniStrediska` WHERE `HodnoceniStrediskaPobocka` = ".$sw_id." and `HodnoceniStrediskaStredisko` = ".$_SESSION["VybraneStrediskoID"];
                   }
                   
                   
//echo $sql_existuje_stredisko_stav;

                   $vysledek_existuje_stredisko_stav=$mysqli->query($sql_existuje_stredisko_stav);
                   $radku_existuje_stredisko_stav=$vysledek_existuje_stredisko_stav->num_rows; 
                   $vysledek_stredisko_stav=MySQLi_Fetch_Array($vysledek_existuje_stredisko_stav);               
                                 
                   $StavZapnutiHodnoceni = "0";

                   if ($radku_existuje_stredisko_stav==1) {

                    
                    $StavZapnutiHodnoceni = $vysledek_stredisko_stav["HodnoceniStrediskaZapnuto"];
                    $TypZpusobuOdeslaniDotaniku = $vysledek_stredisko_stav["HodnoceniStrediskaZpusobZaslani"];
                    $TypOdeslaniDnyDopredu = $vysledek_stredisko_stav["TypOdeslaniDnyDopredu"];
                    $SystemZapnut = $vysledek_stredisko_stav["HodnoceniPlatnostOd"];

                    $HodnoceniKolikratDoroka = $vysledek_stredisko_stav["HodnoceniKolikratDoroka"];
                    $HodnoceniKolikratDorokaDatum = $vysledek_stredisko_stav["HodnoceniKolikratDorokaDatum"];
                                      



                    }else{
                    
                      
                    }

                    
                    
    
    if ($StavZapnutiHodnoceni=="1") {
        
        // Zjistit kolik vet ma pobocka pokud 0 tak založit default věty
        // SELECT count(*) as pocet FROM `HodnoceniNastaveni` WHERE `HodnoceniPobocka` = 2358

                 $select_na_sumu= "SELECT count(*) as pocet FROM `HodnoceniNastaveni` WHERE `HodnoceniPobocka` =".$sw_id ." and `HodnoceniStredisko` = ".$_SESSION["VybraneStrediskoID"];
                        if (!$select_na_sumu) { die('Chyba pripojeni do DB!');}
                              $vysledek_na_sumu=$mysqli->query("$select_na_sumu");
                              $data_suma=MySQLi_Fetch_Array($vysledek_na_sumu);   

                              $CntPocetVetProStredisko = $data_suma["pocet"];
                                
                               if ($data_suma["pocet"]=="0") {
                                 $PocetVetHodnoceni = 0 ;
                                 //Isertni Vety   
                                                 
                                        $sql = "INSERT INTO `HodnoceniNastaveni` (`HodnoceniID`, `HodnoceniPobocka`, `HodnoceniVeta`, `HodnoceniVetaTyp`,`HodnoceniStredisko`,`HodnoceniVetaTypHodnoceni`,`HodnoceniVetaPoradi`) 
                                                VALUES 
                                                (NULL, ".$sw_id.", 'Byl/a jste spokojen/a s vaší obsluhou?', 'Hvězdičky',".$_SESSION["VybraneStrediskoID"].",'Obsluha',1),
                                                (NULL, ".$sw_id.", 'Jaký byl váš celkový dojem z vaší návštěvy?', 'Hvězdičky',".$_SESSION["VybraneStrediskoID"].",'Atmosféra',2),
                                                (NULL, ".$sw_id.", 'Kolika hvězdami byste ohodnotili náš salon?', 'Hvězdičky',".$_SESSION["VybraneStrediskoID"].",'Prostředí',3),
                                                (NULL, ".$sw_id.", 'Je zde něco, s čím jste byl/a nespokojena nebo co bychom mohli udělat lépe?', 'Text',".$_SESSION["VybraneStrediskoID"].",'Nespecifikováno',4),
                                                (NULL, ".$sw_id.", 'Je něco, za co byste nás chtěl/a pochválit?', 'Text',".$_SESSION["VybraneStrediskoID"].",'Nespecifikováno',5);";

                                        //echo $sql;
                                        $vysledek_zalozeni_otazek = @$mysqli->query($sql);
                           
                                        if ($vysledek_zalozeni_otazek) {
                                           
                                         }else{
                                           $error = $mysqli->error; 
                                            echo $error; 
                                            exit;
                                        }


                               }else{
                                $PocetVetHodnoceni = $data_suma["pocet"] ;
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
        <li class="active"><b><?php echo $pobocka_jmeno; ?></b><?php echo JePobockaOnline($sw_id,$mysqli) ?>
        <?php //echo $sw_id ?>

        </li>                    
      </ol>                
    </div>            
  </div> 
             
  
           <section id="main-content" class="hs-feedback-page hs-feedback-settings">
                
           

                
                    <div class="row hs-feedback-settings-top__center">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Výběr střediska</b></font></h3>
                                 <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            
                            <div class="panel-body">

                            <?php
                                                $select_na_pobocku= "SELECT * FROM `HodnoceniStrediska` WHERE `HodnoceniStrediskaPobocka` = ".$sw_id." and `HodnoceniStrediskaValid` = 1 order by `HodnoceniStrediskaID` ASC";
                                                 //echo $select_na_pobocku;
                                                 if (!$select_na_pobocku) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

                                                  $vysledek_na_pobocku=$mysqli->query($select_na_pobocku);
                                                  $radku_na_pobocku=$vysledek_na_pobocku->num_rows; 

                            

                                                  if ($radku_na_pobocku>0) {
                                                    //echo "***".$_SESSION["VybraneStrediskoID"];
                                                    ?>


                                                                             <form action="index.php?strana=SpokojenostNastaveni" method="POST" class="form-horizontal form-border" name="ZmenaPobockyPravaForm" id="ZmenaPobockyPravaForm">
                                                                                <input type="hidden" class="form-control" name="VyberStrediska" value="1">
                                                                                  <select class="form-control input-lg" name="VybraneStrediskoID" id="VybraneStrediskoID" >
                                                                                    <?php 
                                                                                            $prvni="1";

                                                                                                while ($na_pobocku=MySQLi_Fetch_Array($vysledek_na_pobocku)):   
                                                                                                         
                                                                                                    if ($VybraneStrediskoID=="" and $prvni=="1") {
                                                                                                       $VybraneStrediskoID = $na_pobocku["HodnoceniStrediskaStredisko"];
                                                                                                       $JmenoStrediska = $na_pobocku["HodnoceniStrediskaStrediskoJmeno"];
                                                                                                       if ($_SESSION["VybraneStrediskoID"]=="") {
                                                                                                           $_SESSION["VybraneStrediskoID"] = $VybraneStrediskoID;
                                                                                                       }
                                                                                                       
                                                                                                       $prvni="";
                                                                                                    }                    

                                                                                                    if ($_SESSION["VybraneStrediskoID"]==$na_pobocku["HodnoceniStrediskaStredisko"]) {
                                                                                                        $selected="selected=\"selected\"";
                                                                                                        
                                                                                                        $JmenoStrediska = $na_pobocku["HodnoceniStrediskaStrediskoJmeno"];
                                                                                                        $VybraneStrediskoID = $_SESSION["VybraneStrediskoID"];
                                                                                                    }else{
                                                                                                        $selected="";
                                                                                                    }

                                                                                                  echo "<option ".$selected." value=\"".$na_pobocku["HodnoceniStrediskaStredisko"]."\">".$na_pobocku["HodnoceniStrediskaStrediskoJmeno"]."</option>";
                                                                                                endwhile;
                                                                                     ?>
                                                                                   </select>
                                                                                <input type="submit" name="submit" value="Submit" style="display: none" >
                                                                              </form>

                                                                              

                                                                              <form action="index.php?strana=SpokojenostNastaveni" method="POST" class="form-horizontal form-border" name="ZmenaStrediskaStavHodnoceni" id="ZmenaStrediskaStavHodnoceni">
                                    
                                    
                                                                                <input type="hidden" class="form-control" name="VybraneStrediskoID" value="<?php echo $VybraneStrediskoID; ?>">
                                                                                <input type="hidden" class="form-control" name="ZmenaStrediskaStavHodnoceniAkce" value="1">
                                                                                <br>

                                                                                <table width="99.9%" class="table table-bordered table-striped" id="table_prehledPravaNaMenu">
                                                                                          <tr>
                                                                                            <td>Povolení dotazníku spokojenosti</td>
                                                                                            <td align="center">
                                                                                               
                                                                                              <?php  
                                                                                                
                                                                                                if ($StavZapnutiHodnoceni=="1") {
                                                                                                      $checked="checked";
                                                                                                      $HodnotaValue = 0;
                                                                                                }else{
                                                                                                      $checked="";
                                                                                                      $HodnotaValue = 1;
                                                                                                }
                                                                                              ?> 

                                                                                                <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck1" name="StrediskoStav" value="1">

                                                                                            </td>
                                                                                          </tr>
                                                                                </table>
                                                                                <input type="hidden" class="form-control" name="NovyStavHodnoceni" value="<?php echo $HodnotaValue; ?>">
                                                                                <input type="submit" name="submit" value="Submit" style="display: none" >
                                                                              </form>

                                                                              <?php if ($StavZapnutiHodnoceni=="1") {
                                                                                  $HodnoceniKolikratDorokaDatumZobrazeni = "";
                                                                                  if ($HodnoceniKolikratDoroka!="0" && $HodnoceniKolikratDorokaDatum!="") {
                                                                                      $HodnoceniKolikratDorokaDatumTimestamp = strtotime($HodnoceniKolikratDorokaDatum);
                                                                                      if ($HodnoceniKolikratDorokaDatumTimestamp !== false && $HodnoceniKolikratDorokaDatumTimestamp > 0) {
                                                                                          $HodnoceniKolikratDorokaDatumZobrazeni = date("d.m.Y H:i:s", $HodnoceniKolikratDorokaDatumTimestamp);
                                                                                      }
                                                                                  }
                                                                              ?>
                                                                              <div class="hs-feedback-limit-setting hs-feedback-limit-setting--center">
                                                                                  <div class="hs-feedback-limit-setting__content">
                                                                                      <label class="control-label">Od jakého data počítat omezené hodnocení</label>
                                                                                      <input type="text" readonly="readonly" class="form-control" value="<?php echo $HodnoceniKolikratDorokaDatumZobrazeni; ?>">
                                                                                  </div>
                                                                                  <div class="hs-feedback-limit-setting__action">
                                                                                      <button type="button" class="btn btn-danger" onclick="if(confirm('Opravdu chcete změnit datum omezení hodnocení na dnešní datum?')){document.getElementById('hsFeedbackLimitForm').submit();}">Změnit omezení hodnocení</button>
                                                                                  </div>
                                                                              </div>

                                                                              <form id="hsFeedbackLimitForm" action="index.php?strana=SpokojenostNastaveni" method="POST" class="hs-feedback-limit-form">
                                                                                  <input type="hidden" name="NastavitDatumOmezeniHodnoceni" value="1">
                                                                                  <input type="hidden" name="VybraneStrediskoID" value="<?php echo $VybraneStrediskoID; ?>">
                                                                              </form>
                                                                              <?php } ?>

                                                    <?php 
                                                      

                                                    //jiz je po vyberu strediska
                                                    //Zalozit text do emailu pokud neni
                                                          $select_na_sumu= "SELECT count(*) as pocet, `HodnoceniTextEmailu` FROM `HodnoceniTextEmail` WHERE `HodnoceniPobocka` =".$sw_id." and `HodnoceniStredisko` = ".$_SESSION["VybraneStrediskoID"];
                                                                ///echo "***".$select_na_sumu."*".$_SESSION["pobocka_id"];
                                                                if (!$select_na_sumu) { die('Chyba pripojeni do DB!');}
                                                                      $vysledek_na_sumu=$mysqli->query("$select_na_sumu");
                                                                      $data_suma=MySQLi_Fetch_Array($vysledek_na_sumu);   
                                                                        
                                                                       if ($data_suma["pocet"]=="0") {
                                                                         $PocetVetHodnoceni = 0 ;
                                                                         //Isertni textu

                                                                            //$TextEmailu ="Dobrý den,<br><br>děkujeme vám za vaši návštěvu %DATUM_NAVSTEVY% - %JMENO_PROVOZOVNY%.<br><br>Velmi nám záleží na spokojenosti našich zákazníků a neustálém zkvalitňování služeb. <br>Budeme rádi, když vyplníte dotazník níže a podělíte se s námi o to, jak jste byli spokojeni. <br><br>Vyplnění vám zabere 1-2 minuty a my se tak dozvíme cennou zpětnou vazbu. <br><br><br>%ODKAZ_SPOKOJENOST% <br><br><br>Děkujeme a přejeme příjemný den.<br>%JMENO_PROVOZOVNY%<br>";
                                                                            //$TextEmailu = addslashes("<font face='Calibri, sans-serif'> Dobrý den, <br> <br>děkujeme vám za vaši návštěvu <u>%DATUM_NAVSTEVY% - %JMENO_PROVOZOVNY%.</u> <br> <br>Velmi nám záleží na spokojenosti našich zákazníků a neustálém zkvalitňování služeb. <br>Budeme rádi, když vyplníte dotazník níže a podělíte se s námi o to, jak jste byli spokojeni. <br> <br>Vyplnění vám zabere 1-2 minuty a my se tak dozvíme cennou zpětnou vazbu. <br> <br> <br> %ODKAZ_SPOKOJENOST% <br> <br> <br>Děkujeme a přejeme příjemný den. <br>%JMENO_PROVOZOVNY% <br> <br> <br></font>");
                                                                                

                                                                                $sql = "INSERT INTO `HodnoceniTextEmail` (`HodnoceniID`, `HodnoceniPobocka`, `HodnoceniTextEmailu`,`HodnoceniStredisko`) VALUES (NULL, ".$sw_id.", '$TextEmailu',".$_SESSION["VybraneStrediskoID"].");";
                                                                                //echo $sql;
                                                                                

                                                                                $vysledek_zalozeni_textu = @$mysqli->query($sql);
                                                                   
                                                                                if ($vysledek_zalozeni_textu) {
                                                                                   
                                                                                 }else{
                                                                                   $error = $mysqli->error; 
                                                                                    echo $error; 
                                                                                    exit;
                                                                                }


                                                                       }else{
                                                                          if ($VychoziSablonaEmail=="1") {
                                                                            //vem puvodni
                                                                          }else{
                                                                            $TextEmailu = $data_suma["HodnoceniTextEmailu"] ;
                                                                          }
                                                                       }  
                                                    //Text pro SMS
                                                    $select_na_sumu= "SELECT count(*) as pocet, `HodnoceniTextSMS` FROM `HodnoceniTextSMS` WHERE `HodnoceniPobocka` =".$sw_id." and `HodnoceniStredisko` = ".$_SESSION["VybraneStrediskoID"];
                                                                ///echo "***".$select_na_sumu."*".$_SESSION["pobocka_id"];
                                                                if (!$select_na_sumu) { die('Chyba pripojeni do DB!');}
                                                                      $vysledek_na_sumu=$mysqli->query("$select_na_sumu");
                                                                      $data_suma=MySQLi_Fetch_Array($vysledek_na_sumu);   
                                                                        
                                                                       if ($data_suma["pocet"]=="0") {
                                                                         $PocetVetHodnoceni = 0 ;
                                                                         //Isertni textu
                                                                               

                                                                               //$TextSMS ="Dobry den, budeme radi za vyplneni dotazniku spokojenosti. Vase navsteva byla %DATUM_NAVSTEVY% - %JMENO_PROVOZOVNY% %ODKAZ_SPOKOJENOST% ";
                                                                                

                                                                                $sql = "INSERT INTO `HodnoceniTextSMS` (`HodnoceniID`, `HodnoceniPobocka`, `HodnoceniTextSMS`,`HodnoceniStredisko`) VALUES (NULL, ".$sw_id.", '$TextSMS',".$_SESSION["VybraneStrediskoID"].");";

                                                                                

                                                                                $vysledek_zalozeni_textu = @$mysqli->query($sql);
                                                                   
                                                                                if ($vysledek_zalozeni_textu) {
                                                                                   
                                                                                 }else{
                                                                                   $error = $mysqli->error; 
                                                                                    echo $error; 
                                                                                    exit;
                                                                                }


                                                                       }else{
                                                                        if ($VychoziSablonaSMS=="1") {
                                                                            //vem puvodni
                                                                          }else{
                                                                            $TextSMS = $data_suma["HodnoceniTextSMS"] ;
                                                                          }

                                                                       }  

                                                    /* V183: Google recenze jsou samostatna SMS vetev.
                                                       Puvodni HodnoceniTextSMS se nemeni a zustava fallbackem. */
                                                    $googleTableCheck = @$mysqli->query("SHOW TABLES LIKE 'HodnoceniGoogleSMS'");
                                                    if ($googleTableCheck && $googleTableCheck->num_rows > 0) {
                                                      $GoogleReviewTableExists = true;
                                                      $googlePobocka = (int) $sw_id;
                                                      $googleStredisko = (int) $_SESSION["VybraneStrediskoID"];
                                                      $googleSelect = "SELECT `HodnoceniGoogleAktivni`, `HodnoceniGoogleURL`, `HodnoceniGoogleTextSMS` FROM `HodnoceniGoogleSMS` WHERE `HodnoceniPobocka` = ".$googlePobocka." and `HodnoceniStredisko` = ".$googleStredisko." LIMIT 1";
                                                      $googleResult = @$mysqli->query($googleSelect);
                                                      if ($googleResult && $googleResult->num_rows > 0) {
                                                        $googleData = MySQLi_Fetch_Array($googleResult);
                                                        $GoogleReviewUrl = isset($googleData["HodnoceniGoogleURL"]) ? trim((string) $googleData["HodnoceniGoogleURL"]) : "";
                                                        $GoogleReviewSmsDb = isset($googleData["HodnoceniGoogleTextSMS"]) ? (string) $googleData["HodnoceniGoogleTextSMS"] : "";
                                                        if (trim($GoogleReviewSmsDb) !== "") {
                                                          $GoogleReviewSms = $GoogleReviewSmsDb;
                                                        }
                                                        if ((string) $googleData["HodnoceniGoogleAktivni"] === "1") {
                                                          $GoogleReviewMode = "google";
                                                        }
                                                      }
                                                    }
















                                                  }else{
                                                                         ?>

                                                                                                <div class="row">
                                                                                                    <div class="col-md-12">
                                                                                                        <div class="panel panel-info">
                                                                                                            <div class="panel-heading">
                                                                                                                <h3 class="panel-title">Informace</h3>
                                                                                                            </div>
                                                                                                            <div class="panel-body">
                                                                                                                <b>
                                                                                                                  Nemáte aktuální verzi programu.
                                                                                                                </b>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>

                                                                         <?php 
                                                  }
                                                    
                                                    ?>




                                   





                                          

                                
                                
                            </div>
                    </div>
                   </div>
                
                </div>
                

                <?php 
                    if ($StavZapnutiHodnoceni=="1") {
                        
                ?>
                                    
                                    <div class="row hs-feedback-settings-top__delivery">
                                        <div class="col-md-12">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    
                                                    <h3 class="panel-title"><font face="tahoma"><b>Odeslání dotazníku</b></font></h3>
                                                     <div class="actions pull-right">
                                                        <i class="fa fa-expand"></i>
                                                        <i class="fa fa-chevron-down"></i>
                                                        <i class="fa fa-times"></i>
                                                    </div> 
                                                </div>
                                                <div class="panel-body">

                                                
                                                    
                                                              <form action="index.php?strana=SpokojenostNastaveni" method="POST" class="form-horizontal form-border">
                                                                <input type="hidden" class="form-control" name="ZpusobOdeslaniDotazniku" value="1">
                                                                <input type="hidden" class="form-control" name="VybraneStrediskoID" value="<?php echo $VybraneStrediskoID; ?>">
                                                                


                                                                   <div class="form-group">
                                                                      <label class="col-sm-3 control-label">Způsob odeslání dotazníku</label>
                                                                      <div class="col-sm-6">
                                                                      <select class="form-control input-lg" name="TypOdeslani">
                                                                            <option value="EMAIL + SMS" <?php echo $TypZpusobuOdeslaniDotaniku=="EMAIL + SMS" ? 'selected=\"selected\"' : '' ?>>EMAIL + SMS</option>
                                                                            <option value="EMAIL" <?php echo $TypZpusobuOdeslaniDotaniku=="EMAIL" ? 'selected=\"selected\"' : '' ?>>EMAIL</option>
                                                                            <option value="SMS" <?php echo $TypZpusobuOdeslaniDotaniku=="SMS" ? 'selected=\"selected\"' : '' ?> >SMS</option>
                                                                            <option value="VYPNUTO" <?php echo $TypZpusobuOdeslaniDotaniku=="VYPNUTO" ? 'selected=\"selected\"' : '' ?> >VYPNUTO</option>
                                                                      </select>


                                                                       </div>
                                                                  </div>

                                                                  <div class="form-group">
                                                                      <label class="col-sm-3 control-label">Kdy odesílat zákazníkovi hodnocení</label>
                                                                      <div class="col-sm-6">
                                                                      <select class="form-control input-lg" name="TypOdeslaniDnyDopredu">
                                                                            
                                                                            <option value="1" <?php echo $TypOdeslaniDnyDopredu=="1" ? 'selected=\"selected\"' : '' ?>>Zítra</option>
                                                                            <option value="2" <?php echo $TypOdeslaniDnyDopredu=="2" ? 'selected=\"selected\"' : '' ?>>Pozítří</option>
                                                                            <option value="3" <?php echo $TypOdeslaniDnyDopredu=="3" ? 'selected=\"selected\"' : '' ?>>Za 3 dny</option>
                                                                            
                                                                      </select>


                                                                       </div>
                                                                  </div>

                                                                  <div class="form-group hs-feedback-system-started">
                                                                      <label class="col-sm-3 control-label">Systém zapnut</label>
                                                                      <div class="col-sm-2">
                                                                        
                                                                        <?php 
                                                                            
                                                                              if ($SystemZapnut=="") {
                                                                                  $SystemZapnut = "";
                                                                              }

                                                                              $SystemZapnut = date("d.m.Y H:i:s", strtotime($SystemZapnut));
                                                                              if ($SystemZapnut=="01.01.1970 01:00:00") {
                                                                                  $SystemZapnut = "";
                                                                              }

                                                                         ?>
                                                                        <input type="text"  readonly="readonly"  class="form-control" name="SystemZapnut" value="<?php echo $SystemZapnut; ?>">

                                                                       </div>
                                                                  </div>



                                                                  <div class="form-group">
                                                                      <label class="col-sm-3 control-label">Kolikrát do roka odeslat hodnocení</label>
                                                                      <div class="col-sm-6">
                                                                      <select class="form-control input-lg" name="HodnoceniKolikratDoroka">
                                                                            
                                                                            <option value="0" <?php echo $HodnoceniKolikratDoroka=="0" ? 'selected=\"selected\"' : '' ?>>Neomezeně</option>
                                                                            <option value="1" <?php echo $HodnoceniKolikratDoroka=="1" ? 'selected=\"selected\"' : '' ?>>1x</option>
                                                                            <option value="2" <?php echo $HodnoceniKolikratDoroka=="2" ? 'selected=\"selected\"' : '' ?>>2x</option>
                                                                            <option value="3" <?php echo $HodnoceniKolikratDoroka=="3" ? 'selected=\"selected\"' : '' ?>>3x</option>
                                                                            <option value="4" <?php echo $HodnoceniKolikratDoroka=="4" ? 'selected=\"selected\"' : '' ?>>4x</option>
                                                                            <option value="5" <?php echo $HodnoceniKolikratDoroka=="5" ? 'selected=\"selected\"' : '' ?>>5x</option>
                                                                            <option value="6" <?php echo $HodnoceniKolikratDoroka=="6" ? 'selected=\"selected\"' : '' ?>>6x</option>
                                                                            <option value="7" <?php echo $HodnoceniKolikratDoroka=="7" ? 'selected=\"selected\"' : '' ?>>7x</option>
                                                                            <option value="8" <?php echo $HodnoceniKolikratDoroka=="8" ? 'selected=\"selected\"' : '' ?>>8x</option>
                                                                            <option value="9" <?php echo $HodnoceniKolikratDoroka=="9" ? 'selected=\"selected\"' : '' ?>>9x</option>
                                                                            <option value="10" <?php echo $HodnoceniKolikratDoroka=="10" ? 'selected=\"selected\"' : '' ?>>10x</option>
                                                                            <option value="11" <?php echo $HodnoceniKolikratDoroka=="11" ? 'selected=\"selected\"' : '' ?>>11x</option>
                                                                            <option value="12" <?php echo $HodnoceniKolikratDoroka=="12" ? 'selected=\"selected\"' : '' ?>>12x</option>
                                                                            
                                                                      </select>


                                                                       </div>
                                                                  </div>

                                                                  <div class="form-group hs-feedback-delivery-save">
                                                                      <label class="col-sm-3 control-label"></label>
                                                                      <div class="col-sm-2">
                                                                          <button type="submit" class="btn btn-primary">Uložit způsob zasílání</button>
                                                                      </div>
                                                                  </div>
                                                                  </form>




                                                              

                                                               

                                                    
                                                    
                                                </div>
                                        </div>
                                       </div>
                                    
                                    </div>

                                    <div class="row hs-feedback-settings-pair hs-feedback-settings-pair--questions">
                                       <div class="col-md-8 hs-feedback-settings-pair__wide">
                                        <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title"><font face="tahoma"><b>Seznam otázek</b></font></h3>
                                                    <div class="actions pull-right">



                                                        <i class="fa fa-expand"></i>
                                                        <i class="fa fa-chevron-down"></i>
                                                        <i class="fa fa-times"></i>

                                                    </div>

                                                </div>
                                                <div class="panel-body">

                                                    <div class="table-responsive">

                                                       <?php

                                                          $SelectOtazky = "SELECT * FROM `HodnoceniNastaveni` WHERE `HodnoceniPobocka` = $sw_id and `HodnoceniStredisko` = ".$_SESSION["VybraneStrediskoID"]." order by `HodnoceniVetaPoradi` ASC     ";
                                                          
                                                         if (!$SelectOtazky) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}

                                                                                            $vysledek_SelectOtazky=$mysqli->query("$SelectOtazky");
                                                                                            $pocet_radku_SelectOtazky = $vysledek_SelectOtazky->num_rows;

                                                                                            //echo $pocet_radku_strediska;


                                                                                            if ($pocet_radku_SelectOtazky==0) {
                                                                                              echo "Nenalezeny žádné otázky";
                                                                                            }else {
                                                                                              ?>

                                                                                                <table id="TabulkaSeznamOtazek" class="table table-striped table-bordered" cellspacing="1" width="100%">
                                                                                                
                                                                                                    <thead>
                                                                                                        <tr align="center">
                                                                                                              <td align="center"><b>#</b></td>
                                                                                                              <td align="center"><b>Otázka</b></td>
                                                                                                              <td align="center"><b>Druh</b></td>
                                                                                                              <td align="center"><b>Typ</b></td>
                                                                                                              <td align="center"><b>Akce</b></td>
                                                                                                        </tr>
                                                                                                    </thead>

                                                                                              <?php

                                                                                            $SelectOtazkyCNT=0;

                                                                                            echo "<tbody>";
                                                                                            while ($SelectOtazky_seznam=MySQLi_Fetch_Array($vysledek_SelectOtazky)):
                                                                                                  $SelectOtazkyCNT = $SelectOtazkyCNT + 1;



                                                                                              $HodnoceniVetaPoradi = $SelectOtazky_seznam["HodnoceniVetaPoradi"];
                                                                                                    $HodnoceniVeta = $SelectOtazky_seznam["HodnoceniVeta"];
                                                                                                      $HodnoceniID = $SelectOtazky_seznam["HodnoceniID"];
                                                                                                 $HodnoceniVetaTyp = $SelectOtazky_seznam["HodnoceniVetaTyp"];
                                                                                        $HodnoceniVetaTypHodnoceni = $SelectOtazky_seznam["HodnoceniVetaTypHodnoceni"];

                                                                                                       
                                                                                                       
                                                                                                            echo "<tr>";
                                                                                                            echo "<td align=\"center\"><BR>".round($HodnoceniVetaPoradi)."</td>";
                                                                                                            echo "<td><BR>".$HodnoceniVeta."</td>";
                                                                                                            echo "<td align=\"center\"><BR>".$HodnoceniVetaTypHodnoceni."</td>";
                                                                                                            echo "<td align=\"center\"><BR>".$HodnoceniVetaTyp."</td>";
                                                                                                            echo "<td align=\"center\">";

                                                                                                                 ?>

                                                                                                                 <table border="0">
                                                                                                                   <tr>
                                                                                                                       <td>
                                                                                                                              <!-- EDITOVAT -->
                                                                                                                              <form action="index.php?strana=SpokojenostNastaveni" method="POST" >
                                                                                                                                <input type="hidden" class="form-control" name="HodnoceniID" value="<?php echo $HodnoceniID; ?>">
                                                                                                                                <input type="hidden" class="form-control" name="HodnoceniVeta" value="<?php echo $HodnoceniVeta; ?>">
                                                                                                                                <input type="hidden" class="form-control" name="HodnoceniVetaTyp" value="<?php echo $HodnoceniVetaTyp; ?>">
                                                                                                                                <input type="hidden" class="form-control" name="HodnoceniVetaTypHodnoceni" value="<?php echo $HodnoceniVetaTypHodnoceni; ?>">
                                                                                                                                <input type="hidden" class="form-control" name="HodnoceniVetaPoradi" value="<?php echo $HodnoceniVetaPoradi; ?>">
                                                                                                                                <input type="hidden" class="form-control" name="editovat_otazku" value="1">
                                                                                                                                <button type="submit" class="btn btn-info" ><i class="fa icon-settings" ></i></button>
                                                                                                                                 &nbsp;
                                                                                                                              </form>
                                                                                                                       </td>

                                                                                                                       <td>

                                                                                                                              <!-- SMAZAT -->
                                                                                                                              <form action="../str/akce/SpokojenostNastaveni.php" method="POST" >
                                                                                                                                <input type="hidden" class="form-control" name="HodnoceniID" value="<?php echo $HodnoceniID; ?>">
                                                                                                                                <input type="hidden" class="form-control" name="smazat_otazku" value="1">
                                                                                                                                <input type="hidden" class="form-control" name="POSTsw_id" value="<?php echo $sw_id; ?>">
                                                                                                                                <button type="submit" onClick="if(!confirm('Opravdu chcete SMAZAT otázku pro hodnocení?')){return false;}" class="btn btn-danger"><i class="fa icon-trash" ></i></button>
                                                                                                                              </form>
                                                                                                                       </td>
                                                                                                                   </tr>
                                                                                                                 </table>








                                                                                                                 <?php


                                                                                                            echo "</td>";
                                                                                                        echo "</tr>";
                                                                                            endwhile;

                                                                                            echo "</tbody>";
                                                                                       ?>
                                                                                         </table>
                                                                                       <?php
                                                                                         }
                                                                                       ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                       <div class="col-md-4 hs-feedback-settings-pair__side">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title"><font face="tahoma"><b>Nová otázka / Editace otázky</b></font></h3>
                                                     <div class="actions pull-right">
                                                        <i class="fa fa-expand"></i>
                                                        <i class="fa fa-chevron-down"></i>
                                                        <i class="fa fa-times"></i>
                                                    </div> 
                                                </div>
                                                <div class="panel-body">

                                                <?php

                                                                       if ($editovat_otazku!="") {

                                                                       }else {
                                                                          $HodnoceniID_edit =  "";
                                                                          $HodnoceniVeta_edit =  "";
                                                                          $HodnoceniVetaTyp_edit =  "";
                                                                          $HodnoceniVetaTypHodnoceni_edit =  "";
                                                                          $HodnoceniVetaPoradi_edit = "";
                                                                       }




                                                ?>


                                                              <form action="../str/akce/SpokojenostNastaveni.php" method="POST" class="form-horizontal form-border">
                                                                <input type="hidden" class="form-control" name="ZalozitNovouOtazku" value="1">
                                                                <input type="hidden" class="form-control" name="HodnoceniID" value="<?php echo $HodnoceniID_edit; ?>">
                                                                <input type="hidden" class="form-control" name="POSTsw_id" value="<?php echo $sw_id; ?>">
                                                                <input type="hidden" class="form-control" name="HodnoceniVetaPoradiPuvodni" value="<?php echo $HodnoceniVetaPoradi_edit; ?>">


                                                                  <div class="form-group">
                                                                      <label class="col-sm-3 control-label">Otázka hodnocení</label>
                                                                      <div class="col-sm-6">
                                                                          <input class="form-control" type="text" name="HodnoceniVeta" value="<?php echo $HodnoceniVeta_edit; ?>">
                                                                      </div>
                                                                  </div>

                                                                   <div class="form-group">
                                                                      <label class="col-sm-3 control-label">Typ hodnocení</label>
                                                                      <div class="col-sm-6">
                                                                      <select class="form-control input-lg" name="HodnoceniVetaTyp">

                                                                            <option value="Hvězdičky" <?php echo $HodnoceniVetaTyp_edit=="Hvězdičky" ? 'selected=\"selected\"' : '' ?>>Hvězdičky</option>
                                                                            <option value="Text" <?php echo $HodnoceniVetaTyp_edit=="Text" ? 'selected=\"selected\"' : '' ?> >Text</option>
                                                                            
                                                                      </select>
                                                                       </div>
                                                                  </div>

                                                                  <?php 

                                                                   ?>
                                                                  <div class="form-group">
                                                                      <label class="col-sm-3 control-label">Typ otázky</label>
                                                                      <div class="col-sm-6">
                                                                      <select class="form-control input-lg" name="HodnoceniVetaTypHodnoceni">

                                                                            <option value="Obsluha" <?php echo $HodnoceniVetaTypHodnoceni_edit=="Obsluha" ? 'selected=\"selected\"' : '' ?>>Obsluha</option>
                                                                            <option value="Služba" <?php echo $HodnoceniVetaTypHodnoceni_edit=="Služba" ? 'selected=\"selected\"' : '' ?>>Služba</option>
                                                                            <option value="Prostředí" <?php echo $HodnoceniVetaTypHodnoceni_edit=="Prostředí" ? 'selected=\"selected\"' : '' ?>>Prostředí</option>
                                                                            <option value="Cena" <?php echo $HodnoceniVetaTypHodnoceni_edit=="Cena" ? 'selected=\"selected\"' : '' ?>>Cena</option>
                                                                            <option value="Atmosféra" <?php echo $HodnoceniVetaTypHodnoceni_edit=="Atmosféra" ? 'selected=\"selected\"' : '' ?>>Atmosféra</option>
                                                                            <option value="Nespecifikováno" <?php echo $HodnoceniVetaTypHodnoceni_edit=="Nespecifikováno" ? 'selected=\"selected\"' : '' ?>>Nespecifikováno</option>
                                                                            
                                                                      </select>
                                                                       </div>
                                                                  </div>

                                                                  <?php 
                                                                  //echo $CntPocetVetProStredisko;
                                                                   ?>

                                                                  <div class="form-group">
                                                                      <label class="col-sm-3 control-label">Pořadí otázky v dotazníku</label>
                                                                      <div class="col-sm-6">
                                                                      <select class="form-control input-lg" name="HodnoceniVetaPoradi">
                                                                                <?php 

                                                                                    if ($editovat_otazku!="") {
                                                                                        // je editace
                                                                                        for ($i=1; $i <= $CntPocetVetProStredisko; $i++) { 
                                                                                                ?>
                                                                                                    <option value="<?php echo $i; ?>" <?php echo $HodnoceniVetaPoradi_edit== $i ? 'selected=\"selected\"' : '' ?>><?php echo $i; ?></option>
                                                                                                <?php 
                                                                                        }
                                                                                    }else {
                                                                                        //nova +1
                                                                                        ?>
                                                                                                    <option value="<?php echo $CntPocetVetProStredisko +1;  ?>"><?php echo $CntPocetVetProStredisko +1; ?></option>
                                                                                        <?php 
                                                                                    }

                                                                                 ?>
                                                                      </select>
                                                                       </div>
                                                                  </div>

                                                                  <?php
                                                                  

                                                                       
                                                                       if ($editovat_otazku!="") {
                                                                            echo "<center><button type='submit' class='btn btn-primary'>Uložit otázku</button></center>";
                                                                       }else {
                                                                          if ($PocetVetHodnoceni < 10) {
                                                                            echo "<center><button type='submit' class='btn btn-primary'>Přidat novou otázku</button></center>";  
                                                                          }else{
                                                                            //nepovolit 
                                                                          }
                                                                                                                                                                              
                                                                       }
                                                                  ?>


                                                                 

                                                              </form>

                                                    
                                                    
                                                </div>
                                        </div>
                                       </div>
                                    
                                    </div>


                                    <div class="row hs-feedback-settings-pair hs-feedback-settings-pair--messages">
                                        <div class="col-md-6 hs-feedback-settings-pair__half hs-feedback-settings-pair__email">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title"><font face="tahoma"><b>Text emailu</b></font></h3>
                                                     <div class="actions pull-right">
                                                        <i class="fa fa-expand"></i>
                                                        <i class="fa fa-chevron-down"></i>
                                                        <i class="fa fa-times"></i>
                                                    </div> 
                                                </div>
                                                <div class="panel-body">

                                                <?php
                                                            //Nacist text emailu pro pobocku


                                                //$TextEmailu = str_replace("<br>", "\r\n", $TextEmailu);    
                                                $TextEmailu = stripcslashes($TextEmailu);    
                                                //$TextEmailu =strip_tags($TextEmailu);
                                                


                                                //$TextEmailu = "<font face='Calibri, sans-serif'> Dobrý den, <br> <br>děkujeme vám za vaši návštěvu <u>22.11.2022 v 14:15 - Barber De CoCo Bille.</u> <br> <br>Velmi nám záleží na spokojenosti našich zákazníků a neustálé zkvalitňování služeb. <br>Budeme rádi, když vyplníte dotazník níže a podělíte se s námi o to, jak jste byli spokojeni. <br> <br>Vyplnění vám zabere 1-2 minuty a my se tak dozvíme cennou zpětnou vazbu. <br> <br> <br> <font size='+1'><b><a href='https://dotaznik.net/form.php?id=OW9HHN' arget='_blank'>OTEVŘÍT DOTAZNÍK</a></b></font> <br> <br> <br>Děkujeme a přejeme příjemný den. <br>Barber De CoCo Bille. <br> <br> <br> <i><font size='-1'>Nepřejete si zasílat hodnocení služeb? Odhlásit se můžete <a href='https://dotaznik.net/form.php?id=OW9HHN&OdhlasitEmail=1' arget='_blank'>ZDE</a>. <br>V případě, že si to rozmyslíte, stačí opětovně kliknout na odkaz výše.</font></i></font>";

                                                //echo $TextEmailu;

                                                ?>


                                                              <form action="../str/akce/SpokojenostNastaveni.php" method="POST" class="form-horizontal form-border">
                                                                <input type="hidden" class="form-control" name="EditaceTextuEmailu" value="1">
                                                                <input type="hidden" class="form-control" name="POSTsw_id" value="<?php echo $sw_id; ?>">
                                                                
                                                                  <div class="form-group">
                                                                      <label class="col-sm-3 control-label">Text emailu</label>
                                                                      <div class="col-sm-6">
                                                                          <textarea  rows="16" name="HodnoceniTextEmailu" id="HodnoceniTextEmailu" class="htmledit"><?php echo $TextEmailu; ?></textarea>

                                                                      </div>
                                                                  </div>

                                                                  <div class="hs-feedback-sms-variables hs-feedback-email-variables" aria-label="Dostupné proměnné">
                                                                    <span class="hs-feedback-sms-variables__label">Dostupné proměnné</span>
                                                                    <code>%DATUM_NAVSTEVY%</code>
                                                                    <code>%JMENO_PROVOZOVNY%</code>
                                                                    <code>%ODKAZ_SPOKOJENOST%</code>
                                                                  </div>

                                                                  <center>
                                                                    <button type="submit" class="btn btn-primary">Uložit text emailu</button>
                                                                    <button type="button" class="btn btn-default" id="PuvodniEmailSablona">Výchozí šablona</button>
                                                                  </center>
                                                              </form>

                                                    
                                                    
                                                </div>
                                        </div>
                                       </div>
                                        <div class="col-md-6 hs-feedback-settings-pair__half hs-feedback-settings-pair__sms">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title"><font face="tahoma"><b>Text SMS</b></font></h3>
                                                     <div class="actions pull-right">
                                                        <i class="fa fa-expand"></i>
                                                        <i class="fa fa-chevron-down"></i>
                                                        <i class="fa fa-times"></i>
                                                    </div> 
                                                </div>
                                                <div class="panel-body">

                                                <?php
                                                            //Nacist text emailu pro pobocku


                                                $TextSMS = str_replace("<br>", "\r\n", $TextSMS);    

                                                ?>


                                                              <div class="hs-feedback-sms-destination" data-hs-sms-design data-hs-sms-initial="<?php echo $GoogleReviewMode === 'google' ? 'google' : 'internal'; ?>">
                                                                <div class="hs-feedback-sms-destination__intro">
                                                                  <div>
                                                                    <div class="hs-feedback-sms-destination__label">Cíl SMS hodnocení</div>
                                                                    <div class="hs-feedback-sms-destination__note">Google recenze budou použity pouze pro SMS. Email zůstává napojený na interní dotazník.</div>
                                                                  </div>
                                                                  <div class="hs-feedback-sms-switch" role="group" aria-label="Cíl SMS hodnocení">
                                                                    <button type="button" class="hs-feedback-sms-switch__item is-active" data-hs-sms-mode="internal" aria-pressed="true">
                                                                      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3.5 14.4 8l5 .7-3.6 3.5.9 5-4.7-2.5-4.7 2.5.9-5-3.6-3.5 5-.7L12 3.5Z"/></svg>
                                                                      <span>Interní hodnocení</span>
                                                                    </button>
                                                                    <button type="button" class="hs-feedback-sms-switch__item" data-hs-sms-mode="google" aria-pressed="false">
                                                                      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 5h5v5M19 5l-7 7M18 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>
                                                                      <span>Google recenze</span>
                                                                    </button>
                                                                  </div>
                                                                </div>

                                                                <div class="hs-feedback-sms-pane is-active" data-hs-sms-pane="internal">
                                                                  <form action="../str/akce/SpokojenostNastaveni.php" method="POST" class="form-horizontal form-border hs-feedback-sms-form">
                                                                    <input type="hidden" class="form-control" name="EditaceTextuSMS" value="1">
                                                                    <input type="hidden" class="form-control" name="HodnoceniSMSRezim" value="internal">
                                                                    <input type="hidden" class="form-control" name="POSTsw_id" value="<?php echo $sw_id; ?>">
                                                                    
                                                                      <div class="form-group">
                                                                          <label class="col-sm-3 control-label">Text SMS</label>
                                                                          <div class="col-sm-6">
                                                                              <textarea rows="16" name="HodnoceniTextSMS" id="HodnoceniTextSMS" class="textarea_sms"><?php echo $TextSMS; ?></textarea>
                                                                          </div>
                                                                      </div>

                                                                      <div class="hs-feedback-sms-variables" aria-label="Dostupné proměnné">
                                                                        <span class="hs-feedback-sms-variables__label">Dostupné proměnné</span>
                                                                        <code>%DATUM_NAVSTEVY%</code>
                                                                        <code>%JMENO_PROVOZOVNY%</code>
                                                                        <code>%ODKAZ_SPOKOJENOST%</code>
                                                                      </div>

                                                                      <center>
                                                                        <button type="submit" class="btn btn-primary">Uložit text SMS</button>
                                                                        <button type="button" class="btn btn-default" id="PuvodniSMSSablona">Výchozí šablona</button>
                                                                      </center>
                                                                  </form>
                                                                </div>

                                                                <div class="hs-feedback-sms-pane" data-hs-sms-pane="google" hidden>
                                                                  <form action="../str/akce/SpokojenostNastaveni.php" method="POST" class="hs-feedback-google-design">
                                                                    <input type="hidden" name="EditaceGoogleSMS" value="1">
                                                                    <input type="hidden" name="HodnoceniSMSRezim" value="google">
                                                                    <input type="hidden" name="POSTsw_id" value="<?php echo $sw_id; ?>">

                                                                    <div class="hs-feedback-google-design__status">
                                                                      <span>Google recenze</span>
                                                                      <span class="hs-feedback-google-design__phase">Pouze SMS</span>
                                                                    </div>

                                                                    <div class="hs-feedback-google-design__grid">
                                                                      <div class="form-group hs-feedback-google-url">
                                                                        <label class="control-label" for="hsGoogleReviewUrl">Odkaz na Google recenze</label>
                                                                        <input type="url" id="hsGoogleReviewUrl" name="GoogleReviewURL" class="form-control" placeholder="https://..." autocomplete="off" required value="<?php echo htmlspecialchars($GoogleReviewUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                                                        <div class="hs-feedback-field-hint">Vložte přímý odkaz, na kterém zákazník může napsat Google recenzi.</div>
                                                                      </div>

                                                                      <div class="form-group hs-feedback-google-message">
                                                                        <label class="control-label" for="hsGoogleReviewSms">Text SMS pro Google recenze</label>
                                                                        <textarea rows="9" id="hsGoogleReviewSms" name="GoogleReviewTextSMS" class="textarea_sms" required><?php echo htmlspecialchars($GoogleReviewSms, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                                        <div class="hs-feedback-field-hint">Proměnná %ODKAZ_SPOKOJENOST% se při odeslání nahradí uloženým Google odkazem.</div>
                                                                      </div>
                                                                    </div>

                                                                    <div class="hs-feedback-sms-variables" aria-label="Dostupné proměnné">
                                                                      <span class="hs-feedback-sms-variables__label">Dostupné proměnné</span>
                                                                      <code>%DATUM_NAVSTEVY%</code>
                                                                      <code>%JMENO_PROVOZOVNY%</code>
                                                                      <code>%ODKAZ_SPOKOJENOST%</code>
                                                                    </div>

                                                                    <div class="hs-feedback-google-design__actions">
                                                                      <button type="submit" class="btn btn-primary">Uložit Google nastavení</button>
                                                                      <button type="button" class="btn btn-default" id="hsGoogleDefaultTemplate">Výchozí šablona</button>
                                                                    </div>
                                                                  </form>
                                                                </div>
                                                              </div>

                                                    
                                                    
                                                </div>
                                        </div>
                                       </div>
                                    
                                    </div>



                                    <div class="row hs-feedback-test-row">
                                        <div class="col-md-12">
                                            <details class="panel panel-default hs-feedback-test-collapse">
                                                <summary class="panel-heading">
                                                    <h3 class="panel-title"><font face="tahoma"><b>Test dotazníku</b></font></h3>
                                                </summary>
                                                <div class="panel-body">
                                                
                                                              <form action="../str/akce/SpokojenostNastaveni.php" method="POST" class="form-horizontal form-border">
                                                                <input type="hidden" class="form-control" name="TestDotaznikuEmailOdeslano" value="1">
                                                                <input type="hidden" class="form-control" name="VybraneStrediskoID" value="<?php echo $VybraneStrediskoID; ?>">
                                                                <?php 
                                                                        

                                                                        //$TextEmailu = "";
                                                                        //$TextEmailu = $TextEmailu."<html><body>";
                                                                        $OdkazDotaznikTest = "https://dotaznik.net/T".$sw_id."-".$VybraneStrediskoID;
                                                                        $TextEmailu =  str_replace("%JMENO_PROVOZOVNY%", $JmenoStrediska, $TextEmailu);    
                                                                        $TextEmailu =  str_replace("%ODKAZ_SPOKOJENOST%", "<font size='+1'><b><a href='".$OdkazDotaznikTest."' target='_blank'>OTEVŘÍT DOTAZNÍK</a></b></font>", $TextEmailu);    
                                                                        $TextEmailu =  str_replace("%DATUM_NAVSTEVY%",  date("d.m.Y"), $TextEmailu);    
                                                                        //$TextEmailu = $TextEmailu."</body></html>";
                                                                        //$TextEmailu = htmlspecialchars_decode($TextEmailu);
                                                                        //echo $TextEmailu;
                                                                        
                                                                 ?>

                                                                <input type="hidden" class="form-control" name="TextEmailu" value="<?php echo $TextEmailu; ?>">

                                                                
                                                                
                                                                   <div class="form-group">
                                                                      <label class="col-sm-3 control-label">Email</label>
                                                                      <div class="col-sm-6">
                                                                            <input type="email" class="form-control" name="TestDotaznikuEmail" id="TestDotaznikuEmail" autocomplete="email"> 
                                                                      </div>
                                                                  </div>
                                                                  <center><button type='submit' id="btnHOdnoceniEmail" class='btn btn-primary' disabled aria-disabled="true">Zaslat testovací email</button></center>    
                                                              </form>

                                                              <br>

                                                              <form action="../str/akce/SpokojenostNastaveni.php" method="POST" class="form-horizontal form-border">
                                                                <input type="hidden" class="form-control" name="TestDotaznikuSMSOdeslano" value="1">
                                                                <input type="hidden" class="form-control" name="VybraneStrediskoID" value="<?php echo $VybraneStrediskoID; ?>">
                                                                <input type="hidden" class="form-control" name="POSTsw_id" value="<?php echo $sw_id; ?>">

                                                                <?php 
                                                                        /* V183: test SMS kopiruje ulozeny SMS rezim.
                                                                           Interni vetev zustava stejna a je bezpecny fallback. */
                                                                        $TextSMSTest = $TextSMS;
                                                                        $OdkazSMSTest = "www.dotaznik.net/T".$sw_id."-".$VybraneStrediskoID;
                                                                        if ($GoogleReviewMode === "google" && trim($GoogleReviewUrl) !== "" && trim($GoogleReviewSms) !== "") {
                                                                          $TextSMSTest = $GoogleReviewSms;
                                                                          // V190: test Google SMS pouziva kratky alias na dotaznik.net.
                                                                          // form.php jej rozpozna a presmeruje na aktualne ulozenou Google URL.
                                                                          $OdkazSMSTest = "www.dotaznik.net/T".$sw_id."-".$VybraneStrediskoID."-G";
                                                                        }
                                                                        $TextSMSTest = str_replace("%JMENO_PROVOZOVNY%", $JmenoStrediska, $TextSMSTest);
                                                                        $TextSMSTest = str_replace("%ODKAZ_SPOKOJENOST%", $OdkazSMSTest, $TextSMSTest);
                                                                        $TextSMSTest = str_replace("%DATUM_NAVSTEVY%", date("d.m.Y"), $TextSMSTest);
                                                                 ?>
                                                                 <input type="hidden" class="form-control" name="HodnoceniTextSMS" value="<?php echo htmlspecialchars($TextSMSTest, ENT_QUOTES, 'UTF-8'); ?>">

                                                                
                                                                
                                                                   <div class="form-group">
                                                                      <label class="col-sm-3 control-label">Mobil</label>
                                                                      <div class="col-sm-6">
                                                                            <input type="tel" class="form-control" name="TestDotaznikuSMS" id="TestDotaznikuSMS" value="" autocomplete="tel"> 
                                                                      </div>
                                                                  </div>
                                                                  <center><button type='submit' id="btnHodnoceniSMS" class='btn btn-primary' disabled aria-disabled="true">Zaslat testovací SMS</button></center>    
                                                              </form>

                                                    
                                                    
                                                </div>
                                            </details>
                                       </div>
                                    
                                    </div>

            <?php 
                }
             ?>




                                                                <?php
                                                                   $DURATION_end=microtime(true);
                                                                   $DURATION = $DURATION_end - $DURATION_start;
                                                                ?>

                                                                <div class="row">
                                                                  <div class="col-md-12">
                                                                      <div class="panel panel-default">
                                                                          <div class="panel-body">
                                                                              <font size="-2">
                                                                                Strana načtena za <b><?php echo round($DURATION,3)."s"; ?></b>
                                                                              </font>
                                                                          </div>
                                                                      </div>
                                                                  </div>
                                                              </div>




                
            </section>
  </section>        



















