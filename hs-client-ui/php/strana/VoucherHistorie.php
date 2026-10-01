    
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
  require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';
  require HS_CLIENT_UI_ROOT . '/fce/GeneratorBarev.php';
  //require_once '../../cfg/nastaveni.php';
  
  
  $jmenoStranky = "Voucher historie";
  $jmenoStrankyPopis = "Přehled historie vybraného voucheru";
  
  if ($_SESSION["pobocka_jmeno"]=="") {
    $pobocka_jmeno =  "Nevybrána" ;
  }else {
    $pobocka_jmeno =  $_SESSION["pobocka_jmeno"] ;  
  }

        //AKCE
        if (!isset($_POST['datum']) || is_array($_POST['datum'])){$_POST['datum']='';}
        $datum  =  htmlspecialchars($_POST['datum'], ENT_COMPAT);

        if (!isset($_GET['guid']) || is_array($_GET['guid'])){$_GET['guid']='';}
        $guid  =  htmlspecialchars($_GET['guid'], ENT_COMPAT);


        /*Editace ceny*/

        if (!isset($_POST['ZmenitHodnotuCeniny']) || is_array($_POST['ZmenitHodnotuCeniny'])){$_POST['ZmenitHodnotuCeniny']='';}
        $ZmenitHodnotuCeniny  =  htmlspecialchars($_POST['ZmenitHodnotuCeniny'], ENT_COMPAT);

        if (!isset($_POST['ZmenitHodnotuCeninyGUID']) || is_array($_POST['ZmenitHodnotuCeninyGUID'])){$_POST['ZmenitHodnotuCeninyGUID']='';}
        $ZmenitHodnotuCeninyGUID  =  htmlspecialchars($_POST['ZmenitHodnotuCeninyGUID'], ENT_COMPAT);

        if ($ZmenitHodnotuCeninyGUID!="") {
            $guid  = $ZmenitHodnotuCeninyGUID;
        }

        
/*Zmena platnosti*/

if (!isset($_POST['ZmenitPlatnost']) || is_array($_POST['ZmenitPlatnost'])){$_POST['ZmenitPlatnost']='';}
$ZmenitPlatnost = htmlspecialchars($_POST['ZmenitPlatnost'], ENT_COMPAT);

if (!isset($_POST['ZmenitPlatnostGUID']) || is_array($_POST['ZmenitPlatnostGUID'])){$_POST['ZmenitPlatnostGUID']='';}
$ZmenitPlatnostGUID = htmlspecialchars($_POST['ZmenitPlatnostGUID'], ENT_COMPAT);

if (!isset($_POST['UlozitPlatnostCeniny']) || is_array($_POST['UlozitPlatnostCeniny'])){$_POST['UlozitPlatnostCeniny']='';}
$UlozitPlatnostCeniny = htmlspecialchars($_POST['UlozitPlatnostCeniny'], ENT_COMPAT);

if (!isset($_POST['UlozitPlatnostCeninyGUID']) || is_array($_POST['UlozitPlatnostCeninyGUID'])){$_POST['UlozitPlatnostCeninyGUID']='';}
$UlozitPlatnostCeninyGUID = htmlspecialchars($_POST['UlozitPlatnostCeninyGUID'], ENT_COMPAT);

if (!isset($_POST['UlozitPlatnostCeninyDatum']) || is_array($_POST['UlozitPlatnostCeninyDatum'])){$_POST['UlozitPlatnostCeninyDatum']='';}
$UlozitPlatnostCeninyDatum = htmlspecialchars($_POST['UlozitPlatnostCeninyDatum'], ENT_COMPAT);

        
        if ($ZmenitPlatnostGUID!="") {
            $guid  = $ZmenitPlatnostGUID;
        }

        if ($UlozitPlatnostCeninyGUID!="") {
            $guid  = $UlozitPlatnostCeninyGUID;
        }




        
/*zmena zustatkove ceny*/
if (!isset($_POST['UlozitHodnotuCeniny']) || is_array($_POST['UlozitHodnotuCeniny'])){$_POST['UlozitHodnotuCeniny']='';}
$UlozitHodnotuCeniny = htmlspecialchars($_POST['UlozitHodnotuCeniny'], ENT_COMPAT);

if (!isset($_POST['UlozitHodnotuCeninyHodnota']) || is_array($_POST['UlozitHodnotuCeninyHodnota'])){$_POST['UlozitHodnotuCeninyHodnota']='';}
$UlozitHodnotuCeninyHodnota = htmlspecialchars($_POST['UlozitHodnotuCeninyHodnota'], ENT_COMPAT);

if (!isset($_POST['UlozitHodnotuCeninyGUID']) || is_array($_POST['UlozitHodnotuCeninyGUID'])){$_POST['UlozitHodnotuCeninyGUID']='';}
$UlozitHodnotuCeninyGUID = htmlspecialchars($_POST['UlozitHodnotuCeninyGUID'], ENT_COMPAT);

if (!isset($_POST['UlozitHodnotuCeninyPuvodniHodnota']) || is_array($_POST['UlozitHodnotuCeninyPuvodniHodnota'])){$_POST['UlozitHodnotuCeninyPuvodniHodnota']='';}
$UlozitHodnotuCeninyPuvodniHodnota = htmlspecialchars($_POST['UlozitHodnotuCeninyPuvodniHodnota'], ENT_COMPAT);

if (!isset($_POST['UlozitHodnotuCeninyMIN']) || is_array($_POST['UlozitHodnotuCeninyMIN'])){$_POST['UlozitHodnotuCeninyMIN']='';}
$UlozitHodnotuCeninyMIN = htmlspecialchars($_POST['UlozitHodnotuCeninyMIN'], ENT_COMPAT);

if (!isset($_POST['UlozitHodnotuCeninyMAX']) || is_array($_POST['UlozitHodnotuCeninyMAX'])){$_POST['UlozitHodnotuCeninyMAX']='';}
$UlozitHodnotuCeninyMAX = htmlspecialchars($_POST['UlozitHodnotuCeninyMAX'], ENT_COMPAT);


//DATA
        if (!isset($_POST['ReadDataVoucher01']) || is_array($_POST['ReadDataVoucher01'])){$_POST['ReadDataVoucher01']='';} //vou_id_hs
        $ReadDataVoucher01  =  htmlspecialchars($_POST['ReadDataVoucher01'], ENT_COMPAT);

        if (!isset($_POST['ReadDataVoucher02']) || is_array($_POST['ReadDataVoucher02'])){$_POST['ReadDataVoucher02']='';} //vou_placeno
        $ReadDataVoucher02  =  htmlspecialchars($_POST['ReadDataVoucher02'], ENT_COMPAT);

        if (!isset($_POST['ReadDataVoucher03']) || is_array($_POST['ReadDataVoucher03'])){$_POST['ReadDataVoucher03']='';} //vou_id_radek_uctenka
        $ReadDataVoucher03  =  htmlspecialchars($_POST['ReadDataVoucher03'], ENT_COMPAT);

        if (!isset($_POST['ReadDataVoucher04']) || is_array($_POST['ReadDataVoucher04'])){$_POST['ReadDataVoucher04']='';} //vou_kod
        $ReadDataVoucher04  =  htmlspecialchars($_POST['ReadDataVoucher04'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher05']) || is_array($_POST['ReadDataVoucher05'])){$_POST['ReadDataVoucher05']='';} //vou_GUID_strediska
        $ReadDataVoucher05  =  htmlspecialchars($_POST['ReadDataVoucher05'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher06']) || is_array($_POST['ReadDataVoucher06'])){$_POST['ReadDataVoucher06']='';} //vou_GUID_radku_uctenka
        $ReadDataVoucher06  =  htmlspecialchars($_POST['ReadDataVoucher06'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher07']) || is_array($_POST['ReadDataVoucher07'])){$_POST['ReadDataVoucher07']='';} //vou_datum_vytvoreni
        $ReadDataVoucher07  =  htmlspecialchars($_POST['ReadDataVoucher07'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher08']) || is_array($_POST['ReadDataVoucher08'])){$_POST['ReadDataVoucher08']='';} //vou_ID_prodejce_obsluhy
        $ReadDataVoucher08  =  htmlspecialchars($_POST['ReadDataVoucher08'], ENT_COMPAT);
        
        //_ nahrtazeno ,
        if (!isset($_POST['ReadDataVoucher09']) || is_array($_POST['ReadDataVoucher09'])){$_POST['ReadDataVoucher09']='';} //vou_sazba_DPH
        $ReadDataVoucher09  =  str_replace("_",".",htmlspecialchars($_POST['ReadDataVoucher09'], ENT_COMPAT));
        
        if (!isset($_POST['ReadDataVoucher10']) || is_array($_POST['ReadDataVoucher10'])){$_POST['ReadDataVoucher10']='';} //vou_id_radku_uctenky
        $ReadDataVoucher10  =  htmlspecialchars($_POST['ReadDataVoucher10'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher11']) || is_array($_POST['ReadDataVoucher11'])){$_POST['ReadDataVoucher11']='';} //vou_doba_platnosti_mesice
        $ReadDataVoucher11  =  htmlspecialchars($_POST['ReadDataVoucher11'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher12']) || is_array($_POST['ReadDataVoucher12'])){$_POST['ReadDataVoucher12']='';} //vou_datum_platnosti_do
        $ReadDataVoucher12  =  htmlspecialchars($_POST['ReadDataVoucher12'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher13']) || is_array($_POST['ReadDataVoucher13'])){$_POST['ReadDataVoucher13']='';} //vou_posledni_cerpani
        $ReadDataVoucher13  =  htmlspecialchars($_POST['ReadDataVoucher13'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher14']) || is_array($_POST['ReadDataVoucher14'])){$_POST['ReadDataVoucher14']='';} //vou_id_uzivatele_docerpal
        $ReadDataVoucher14  =  htmlspecialchars($_POST['ReadDataVoucher14'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher15']) || is_array($_POST['ReadDataVoucher15'])){$_POST['ReadDataVoucher15']='';} //vou_GUID_voucheru
        $ReadDataVoucher15  =  htmlspecialchars($_POST['ReadDataVoucher15'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher16']) || is_array($_POST['ReadDataVoucher16'])){$_POST['ReadDataVoucher16']='';} //vou_nazev_voucheru
        $ReadDataVoucher16  =  htmlspecialchars($_POST['ReadDataVoucher16'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher17']) || is_array($_POST['ReadDataVoucher17'])){$_POST['ReadDataVoucher17']='';} //vou_plu_cenik
        $ReadDataVoucher17  =  htmlspecialchars($_POST['ReadDataVoucher17'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher18']) || is_array($_POST['ReadDataVoucher18'])){$_POST['ReadDataVoucher18']='';} //vou_hodnota_voucheru
        $ReadDataVoucher18  =  htmlspecialchars($_POST['ReadDataVoucher18'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher19']) || is_array($_POST['ReadDataVoucher19'])){$_POST['ReadDataVoucher19']='';} //vou_hodnota_bezDPH
        $ReadDataVoucher19  =  htmlspecialchars($_POST['ReadDataVoucher19'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher20']) || is_array($_POST['ReadDataVoucher20'])){$_POST['ReadDataVoucher20']='';} //vou_cerpana_castka
        $ReadDataVoucher20  =  htmlspecialchars($_POST['ReadDataVoucher20'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher21']) || is_array($_POST['ReadDataVoucher21'])){$_POST['ReadDataVoucher21']='';} //vou_castka_predchozi
        $ReadDataVoucher21  =  htmlspecialchars($_POST['ReadDataVoucher21'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher22']) || is_array($_POST['ReadDataVoucher22'])){$_POST['ReadDataVoucher22']='';} //vou_cerpana_castkabezDPH
        $ReadDataVoucher22  =  htmlspecialchars($_POST['ReadDataVoucher22'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher23']) || is_array($_POST['ReadDataVoucher23'])){$_POST['ReadDataVoucher23']='';} //vou_castka_predchoziho_cerpani_bezDPH
        $ReadDataVoucher23  =  htmlspecialchars($_POST['ReadDataVoucher23'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher24']) || is_array($_POST['ReadDataVoucher24'])){$_POST['ReadDataVoucher24']='';} //vou_jmeno_uzivatele_prodal
        $ReadDataVoucher24  =  htmlspecialchars($_POST['ReadDataVoucher24'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher25']) || is_array($_POST['ReadDataVoucher25'])){$_POST['ReadDataVoucher25']='';} //vou_jmeno_uzivatele_cerpal
        $ReadDataVoucher25  =  htmlspecialchars($_POST['ReadDataVoucher25'], ENT_COMPAT);
        
        if (!isset($_POST['ReadDataVoucher26']) || is_array($_POST['ReadDataVoucher26'])){$_POST['ReadDataVoucher26']='';} //vou_stav
        $ReadDataVoucher26  =  htmlspecialchars($_POST['ReadDataVoucher26'], ENT_COMPAT);

        if (!isset($_POST['ReadDataVoucher28']) || is_array($_POST['ReadDataVoucher28'])){$_POST['ReadDataVoucher28']='';} //id skupiny
        $ReadDataVoucher28  =  htmlspecialchars($_POST['ReadDataVoucher28'], ENT_COMPAT);






      














      /*
        if ($datum=="") {
          $datum = date("d.m.Y"); 
        }
        
               $originalDate = $datum;
               $newDate = date("d.m.Y", strtotime($originalDate));
               
               $newYear = date("Y", strtotime($originalDate));
        */       
        
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
    //$skupina_id = 84; // Thomas
   //$sw_id = 961;
  }
  
        //////////////////////////////////////////////////////////////////////////////////////
        //DEMO
        if ($_SESSION["pobocka_id"]=="") {
          $sw_id = 0;
          $skupina_id = 0;  
        }
        //////////////////////////////////////////////////////////////////////////////////////
  
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
  
  
        
        //Mena
        $Mena_Klienta = $_SESSION["Mena_Klienta"];   


     


?>


<section class="main-content-wrapper hs-voucher-shell">            
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
  <section id="main-content" class="hs-voucher-page" data-voucher-page="VoucherHistorie">                
  


                    <?php         
                                /*DATUM*/
                                if ($UlozitPlatnostCeniny=="1" and $UlozitPlatnostCeninyGUID!="") {
                                  if ($UlozitPlatnostCeninyDatum!="") {

                                    $tj_datum =  date("Y-m-d", strtotime($UlozitPlatnostCeninyDatum));
                                    $RokProTabulku = date("Y-m-d", strtotime($tj_datum));
                                    
                                       $sql_voucher_update = "UPDATE `vouchers` SET `vou_datum_platnosti_do` = '$RokProTabulku 23:59:59', `vou_poznamka` = 'Změna data platnosti na: $UlozitPlatnostCeninyDatum' WHERE `vouchers`.`vou_GUID_voucheru` = '$UlozitPlatnostCeninyGUID' ";
                                       $vysledek_zalozeni_voucher_update = @$mysqli->query($sql_voucher_update);

                                       
                                       $sql_voucher_update_historie = "UPDATE `vouchers_historie` SET `vou_datum_platnosti_do` = '$RokProTabulku 23:59:59' WHERE `vouchers_historie`.`vou_GUID_voucheru` = '$UlozitPlatnostCeninyGUID' ";
                                       $vysledek_zalozeni_voucher_update_historie = @$mysqli->query($sql_voucher_update_historie);

                                  }
                                }


                                /*CASTKA*/
                                if ($UlozitHodnotuCeniny=="1" and $UlozitHodnotuCeninyGUID!="") {

                                      //Vypocet

                                      /*
                                      $ReadDataVoucher01 = $sql_existuje_voucher_data["vou_id_hs"];
                                      $ReadDataVoucher02 = $sql_existuje_voucher_data["vou_placeno"];
                                      $ReadDataVoucher03 = $sql_existuje_voucher_data["vou_id_radek_uctenka"];
                                      $ReadDataVoucher04 = $sql_existuje_voucher_data["vou_kod"];
                                      $ReadDataVoucher05 = $sql_existuje_voucher_data["vou_GUID_strediska"];
                                      $ReadDataVoucher06 = $sql_existuje_voucher_data["vou_GUID_radku_uctenka"];
                                      $ReadDataVoucher07 = $sql_existuje_voucher_data["vou_datum_vytvoreni"];
                                      $ReadDataVoucher08 = $sql_existuje_voucher_data["vou_ID_prodejce_obsluhy"];
                                      $ReadDataVoucher09 = $sql_existuje_voucher_data["vou_sazba_DPH"];
                                      $ReadDataVoucher10 = $sql_existuje_voucher_data["vou_id_radku_uctenky"];
                                      $ReadDataVoucher11 = $sql_existuje_voucher_data["vou_doba_platnosti_mesice"];
                                      $ReadDataVoucher12 = $sql_existuje_voucher_data["vou_datum_platnosti_do"];
                                      $ReadDataVoucher13 = $sql_existuje_voucher_data["vou_posledni_cerpani"];
                                      $ReadDataVoucher14 = $sql_existuje_voucher_data["vou_id_uzivatele_docerpal"];
                                      $ReadDataVoucher15 = $sql_existuje_voucher_data["vou_GUID_voucheru"];
                                      $ReadDataVoucher16 = $sql_existuje_voucher_data["vou_nazev_voucheru"];
                                      $ReadDataVoucher17 = $sql_existuje_voucher_data["vou_plu_cenik"];
                                      $ReadDataVoucher18 = $sql_existuje_voucher_data["vou_hodnota_voucheru"];
                                      $ReadDataVoucher19 = $sql_existuje_voucher_data["vou_hodnota_bezDPH"];
                                      $ReadDataVoucher20 = $sql_existuje_voucher_data["vou_cerpana_castka"];
                                      $ReadDataVoucher21 = $sql_existuje_voucher_data["vou_castka_predchozi"];
                                      $ReadDataVoucher22 = $sql_existuje_voucher_data["vou_cerpana_castkabezDPH"];
                                      $ReadDataVoucher23 = $sql_existuje_voucher_data["vou_castka_predchoziho_cerpani_bezDPH"];
                                      $ReadDataVoucher24 = $sql_existuje_voucher_data["vou_jmeno_uzivatele_prodal"];
                                      $ReadDataVoucher25 = $sql_existuje_voucher_data["vou_jmeno_uzivatele_cerpal"];
                                      $ReadDataVoucher26 = $sql_existuje_voucher_data["vou_stav"];*/

                                      //$ReadDataVoucher18 =    //$sql_existuje_voucher_data["vou_hodnota_voucheru"];
                                      //$ReadDataVoucher19 =    //$sql_existuje_voucher_data["vou_hodnota_bezDPH"];
                                      //$ReadDataVoucher09 =    //$sql_existuje_voucher_data["vou_sazba_DPH"];
                                      
                                      //$ReadDataVoucher09="0";

                                      $ReadDataVoucher21 = $ReadDataVoucher20 - $ReadDataVoucher21;                  //$sql_existuje_voucher_data["vou_castka_predchozi"];
                                      $ReadDataVoucher20 = $ReadDataVoucher18 - $UlozitHodnotuCeninyHodnota;  //OK   //$sql_existuje_voucher_data["vou_cerpana_castka"];
                                      

                                      /* zmena stavu */
                                      if ($ReadDataVoucher20==$ReadDataVoucher18) {
                                        $ReadDataVoucher26="Vyčerpaný";
                                      }


                                      if ($ReadDataVoucher09=="0") {
                                        //neni DPH
                                        $ReadDataVoucher23 = $ReadDataVoucher21;   //$sql_existuje_voucher_data["vou_castka_predchoziho_cerpani_bezDPH"];
                                        $ReadDataVoucher22 = $ReadDataVoucher20;   //$sql_existuje_voucher_data["vou_cerpana_castkabezDPH"];

                                      }else{
                                        //je DPH"""
                                        $ReadDataVoucher23 = $ReadDataVoucher21 / (($ReadDataVoucher09/100)+1);   //$sql_existuje_voucher_data["vou_castka_predchoziho_cerpani_bezDPH"];
                                        $ReadDataVoucher22 = $ReadDataVoucher20 / (($ReadDataVoucher09/100)+1);   //$sql_existuje_voucher_data["vou_cerpana_castkabezDPH"];
                                      }

                                      
                                   //INERT HISTORIE
                                   $sql_voucher_insert_historie2 = "INSERT INTO `vouchers_historie` (`vou_id`, `vou_id_hs`, `vou_placeno`, `vou_id_radek_uctenka`, `vou_kod`, `vou_GUID_strediska`, `vou_GUID_radku_uctenka`, `vou_datum_vytvoreni`, `vou_ID_prodejce_obsluhy`, `vou_sazba_DPH`, `vou_id_radku_uctenky`, `vou_doba_platnosti_mesice`, `vou_datum_platnosti_do`, `vou_posledni_cerpani`, `vou_id_uzivatele_docerpal`, `vou_GUID_voucheru`, `vou_nazev_voucheru`, `vou_plu_cenik`, `vou_hodnota_voucheru`, `vou_hodnota_bezDPH`, `vou_cerpana_castka`, `vou_castka_predchozi`, `vou_cerpana_castkabezDPH`,`vou_castka_predchoziho_cerpani_bezDPH`, `vou_jmeno_uzivatele_prodal`, `vou_jmeno_uzivatele_cerpal`, `vou_stav`, `vou_stav_skupinaID`, `vou_zmena`)VALUES (NULL,'$ReadDataVoucher01','$ReadDataVoucher02','$ReadDataVoucher03','$ReadDataVoucher04','$ReadDataVoucher05','$ReadDataVoucher06','$ReadDataVoucher07','$ReadDataVoucher08','$ReadDataVoucher09','$ReadDataVoucher10','$ReadDataVoucher11','$ReadDataVoucher12',CURRENT_TIMESTAMP,'$ReadDataVoucher14','$ReadDataVoucher15','$ReadDataVoucher16','$ReadDataVoucher17','$ReadDataVoucher18','$ReadDataVoucher19','$ReadDataVoucher20','$ReadDataVoucher21','$ReadDataVoucher22','$ReadDataVoucher23','$ReadDataVoucher24','$ReadDataVoucher25','$ReadDataVoucher26','$ReadDataVoucher28',CURRENT_TIMESTAMP);";

                                   $vysledek_zalozeni_voucher_insert_historie = @$mysqli->query($sql_voucher_insert_historie2);


                                   //UPDATE AKTUALNI
                                   $sql_voucher_update = "UPDATE `vouchers` SET `vou_id_hs`= '$ReadDataVoucher01',`vou_placeno` = '$ReadDataVoucher02',`vou_id_radek_uctenka` = '$ReadDataVoucher03',`vou_kod`  = '$ReadDataVoucher04', `vou_GUID_strediska`   = '$ReadDataVoucher05',`vou_GUID_radku_uctenka`       = '$ReadDataVoucher06', `vou_datum_vytvoreni`  = '$ReadDataVoucher07',`vou_ID_prodejce_obsluhy`      = '$ReadDataVoucher08',`vou_sazba_DPH`= '$ReadDataVoucher09',`vou_id_radku_uctenky` = '$ReadDataVoucher10',`vou_doba_platnosti_mesice`    = '$ReadDataVoucher11',`vou_datum_platnosti_do`= '$ReadDataVoucher12',`vou_posledni_cerpani` = '$ReadDataVoucher13',`vou_id_uzivatele_docerpal`    = '$ReadDataVoucher14',  `vou_nazev_voucheru`   = '$ReadDataVoucher16', `vou_plu_cenik`= '$ReadDataVoucher17',`vou_hodnota_voucheru` = '$ReadDataVoucher18', `vou_hodnota_bezDPH`   = '$ReadDataVoucher19',`vou_cerpana_castka`   = '$ReadDataVoucher20', `vou_castka_predchozi` = '$ReadDataVoucher21',`vou_cerpana_castkabezDPH`     = '$ReadDataVoucher22', `vou_castka_predchoziho_cerpani_bezDPH`= '$ReadDataVoucher23',`vou_jmeno_uzivatele_prodal`   = '$ReadDataVoucher24', `vou_jmeno_uzivatele_cerpal`   = '$ReadDataVoucher25',`vou_stav` = '$ReadDataVoucher26' WHERE `vou_stav_skupinaID` = '$ReadDataVoucher28' and `vou_GUID_voucheru` = '$ReadDataVoucher15';";

                                   
                                   //echo $sql_voucher_update;
                                   $vysledek_zalozeni_voucher_update = @$mysqli->query($sql_voucher_update);



                                      if ($vysledek_zalozeni_voucher_update and $vysledek_zalozeni_voucher_insert_historie) {
                                        $Stav_sql = "OK";   
                                       }else{
                                        $Stav_sql = "ERROR".$mysqli->error; 
                                      }



                                      
                                      
                                      




                                
                                        

                                }


                                //nechat predava guid
                                if ($UlozitHodnotuCeninyGUID!="") {
                                   $guid  = $UlozitHodnotuCeninyGUID;
                                }





             
                   
     
     
                                //Kolik voucher celkem
                                $select_na_sumu_voucher= "SELECT COUNT(*)as 'CNT_Voucheru' FROM `vouchers_historie` WHERE `vou_GUID_voucheru` = '$guid'";
                                if (!$select_na_sumu_voucher) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_sumu_voucher=$mysqli->query("$select_na_sumu_voucher");
                                $data_suma_voucher=MySQLi_Fetch_Array($vysledek_na_sumu_voucher);
    
                                // Celkova hodnota voucher
                                $select_na_sumu_voucheru_hodnota= "SELECT SUM(`vou_hodnota_voucheru`) as 'SUM_Hodnota_Voucheru' FROM `vouchers` WHERE `vou_GUID_voucheru` = '$guid'";
                                if (!$select_na_sumu_voucheru_hodnota) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_sumu_voucheru_hodnota=$mysqli->query("$select_na_sumu_voucheru_hodnota");
                                $data_suma_voucher_hodnota=MySQLi_Fetch_Array($vysledek_na_sumu_voucheru_hodnota);
                                
                                //Cerpano voucher
                                $select_na_cerpano_voucher= "SELECT SUM(`vou_cerpana_castka`) as 'SUM_Cerpano_SUM' FROM `vouchers` WHERE `vou_GUID_voucheru` = '$guid'";
                                //echo $select_na_cerpano_voucher;
                                if (!$select_na_cerpano_voucher) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_cerpano_voucher=$mysqli->query("$select_na_cerpano_voucher");
                                $suma_cerpano_voucher=MySQLi_Fetch_Array($vysledek_na_cerpano_voucher);

                                //Zustatek
                                $ZustatekSUMVoucheru = $data_suma_voucher_hodnota["SUM_Hodnota_Voucheru"] - $suma_cerpano_voucher["SUM_Cerpano_SUM"];
  ?>
  
                <div class="row">
                            <div class="col-md-3">
                                <div class="panel panel-solid-success widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $data_suma_voucher["CNT_Voucheru"] /*floor(($suma_kreditu_dobito_Cena_sms - $suma_sms_odeslano))*/ ." "; ?></font></span>
                                        <span class="title text-center">Záznamů</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <?php $CelkovaHodnotaVBoucheru = $data_suma_voucher_hodnota["SUM_Hodnota_Voucheru"]; ?>
                                        <span class="total text-center"><font size="5"><?php echo $data_suma_voucher_hodnota["SUM_Hodnota_Voucheru"]."&nbsp;".$Mena_Klienta ." "; ?></font></span>
                                        <span class="title text-center">Celková&nbsp;hodnota</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-solid-danger widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo number_format($ZustatekSUMVoucheru, 2, '.', ' ')."&nbsp;".$Mena_Klienta  ?></font></span>
                                        <span class="title text-center">Zůstatek&nbsp;</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo  $suma_cerpano_voucher["SUM_Cerpano_SUM"]."&nbsp;".$Mena_Klienta ?></font></span>              
                                        <span class="title text-center">Čerpáno&nbsp;</span>
                                    </div>
                                </div>
                            </div>
                  </div>             
                







                  <?php 
/*EDITACE*/
                    
                    if ($ZmenitHodnotuCeniny=="1") {

                      //Nacteni posledni vety z historie voucheru


                                $select_na_sumu_voucher= "SELECT * FROM `vouchers_historie` WHERE `vou_GUID_voucheru` = '$guid' order by 1 DESC";
                                if (!$select_na_sumu_voucher) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_sumu_voucher=$mysqli->query("$select_na_sumu_voucher");
                                $data_voucher=MySQLi_Fetch_Array($vysledek_na_sumu_voucher);


                                
                                $Zustatek_vypocitany_zmena = $data_voucher["vou_hodnota_voucheru"]-$data_voucher["vou_cerpana_castka"];



                                      $ReadDataVoucher01 =    $data_voucher["vou_id_hs"];
                                      $ReadDataVoucher02 =    $data_voucher["vou_placeno"];
                                      $ReadDataVoucher03 =    $data_voucher["vou_id_radek_uctenka"];
                                      $ReadDataVoucher04 =    $data_voucher["vou_kod"];
                                      $ReadDataVoucher05 =    $data_voucher["vou_GUID_strediska"];
                                      $ReadDataVoucher06 =    $data_voucher["vou_GUID_radku_uctenka"];
                                      $ReadDataVoucher07 =    $data_voucher["vou_datum_vytvoreni"];
                                      $ReadDataVoucher08 =    $data_voucher["vou_ID_prodejce_obsluhy"];
                                      $ReadDataVoucher09 =    $data_voucher["vou_sazba_DPH"];
                                      $ReadDataVoucher10 =    $data_voucher["vou_id_radku_uctenky"];
                                      $ReadDataVoucher11 =    $data_voucher["vou_doba_platnosti_mesice"];
                                      $ReadDataVoucher12 =    $data_voucher["vou_datum_platnosti_do"];
                                      $ReadDataVoucher13 =    $data_voucher["vou_posledni_cerpani"];
                                      $ReadDataVoucher14 =    $data_voucher["vou_id_uzivatele_docerpal"];
                                      $ReadDataVoucher15 =    $data_voucher["vou_GUID_voucheru"];
                                      $ReadDataVoucher16 =    $data_voucher["vou_nazev_voucheru"];
                                      $ReadDataVoucher17 =    $data_voucher["vou_plu_cenik"];
                                      $ReadDataVoucher18 =    $data_voucher["vou_hodnota_voucheru"];
                                      $ReadDataVoucher19 =    $data_voucher["vou_hodnota_bezDPH"];
                                      $ReadDataVoucher20 =    $data_voucher["vou_cerpana_castka"];
                                      $ReadDataVoucher21 =    $data_voucher["vou_castka_predchozi"];
                                      $ReadDataVoucher22 =    $data_voucher["vou_cerpana_castkabezDPH"];
                                      $ReadDataVoucher23 =    $data_voucher["vou_castka_predchoziho_cerpani_bezDPH"];
                                      $ReadDataVoucher24 =    $data_voucher["vou_jmeno_uzivatele_prodal"];
                                      $ReadDataVoucher25 =    $data_voucher["vou_jmeno_uzivatele_cerpal"];
                                      $ReadDataVoucher26 =    $data_voucher["vou_stav"];

                                      $ReadDataVoucher28 =    $data_voucher["vou_stav_skupinaID"];

                                        



/*
                                    $sql_voucher_insert_historie2 = "INSERT INTO `vouchers_historie` (`vou_id`, `vou_id_hs`, `vou_placeno`, `vou_id_radek_uctenka`, `vou_kod`, `vou_GUID_strediska`, `vou_GUID_radku_uctenka`, `vou_datum_vytvoreni`, `vou_ID_prodejce_obsluhy`, `vou_sazba_DPH`, `vou_id_radku_uctenky`, `vou_doba_platnosti_mesice`, `vou_datum_platnosti_do`, `vou_posledni_cerpani`, `vou_id_uzivatele_docerpal`, `vou_GUID_voucheru`, `vou_nazev_voucheru`, `vou_plu_cenik`, `vou_hodnota_voucheru`, `vou_hodnota_bezDPH`, `vou_cerpana_castka`, `vou_castka_predchozi`, `vou_cerpana_castkabezDPH`,`vou_castka_predchoziho_cerpani_bezDPH`, `vou_jmeno_uzivatele_prodal`, `vou_jmeno_uzivatele_cerpal`, `vou_stav`, `vou_stav_skupinaID`, `vou_zmena`)VALUES (NULL,'$PostDataVoucher01','$PostDataVoucher02','$PostDataVoucher03','$PostDataVoucher04','$PostDataVoucher05','$PostDataVoucher06','$PostDataVoucher07','$PostDataVoucher08','$PostDataVoucher09','$PostDataVoucher10','$PostDataVoucher11','$PostDataVoucher12','$PostDataVoucher13','$PostDataVoucher14','$PostDataVoucher15','$PostDataVoucher16','$PostDataVoucher17','$PostDataVoucher18','$PostDataVoucher19','$PostDataVoucher20','$PostDataVoucher21','$PostDataVoucher22','$PostDataVoucher23','$PostDataVoucher24','$PostDataVoucher25','$PostDataVoucher26','$sw_skupina_id',CURRENT_TIMESTAMP);";

                                   $vysledek_zalozeni_voucher_insert_historie = @$mysqli->query($sql_voucher_insert_historie2);


                                      if ($vysledek_zalozeni_voucher_insert and $vysledek_zalozeni_voucher_insert_historie) {
                                        $Stav_sql = "OK";   
                                       }else{
                                        $Stav_sql = "ERROR".$mysqli->error; 
                                      }


                                       $sql_voucher_update = "
                                   UPDATE `vouchers` SET `vou_id_hs`= '$PostDataVoucher01',`vou_placeno` = '$PostDataVoucher02',`vou_id_radek_uctenka` = '$PostDataVoucher03',`vou_kod`  = '$PostDataVoucher04', `vou_GUID_strediska`   = '$PostDataVoucher05',`vou_GUID_radku_uctenka`       = '$PostDataVoucher06', `vou_datum_vytvoreni`  = '$PostDataVoucher07',`vou_ID_prodejce_obsluhy`      = '$PostDataVoucher08',`vou_sazba_DPH`= '$PostDataVoucher09',`vou_id_radku_uctenky` = '$PostDataVoucher10',`vou_doba_platnosti_mesice`    = '$PostDataVoucher11',`vou_datum_platnosti_do`= '$PostDataVoucher12',`vou_posledni_cerpani` = '$PostDataVoucher13',`vou_id_uzivatele_docerpal`    = '$PostDataVoucher14',  `vou_nazev_voucheru`   = '$PostDataVoucher16', `vou_plu_cenik`= '$PostDataVoucher17',`vou_hodnota_voucheru` = '$PostDataVoucher18', `vou_hodnota_bezDPH`   = '$PostDataVoucher19',`vou_cerpana_castka`   = '$PostDataVoucher20', `vou_castka_predchozi` = '$PostDataVoucher21',`vou_cerpana_castkabezDPH`     = '$PostDataVoucher22', `vou_castka_predchoziho_cerpani_bezDPH`= '$PostDataVoucher23',`vou_jmeno_uzivatele_prodal`   = '$PostDataVoucher24', `vou_jmeno_uzivatele_cerpal`   = '$PostDataVoucher25',`vou_stav` = '$PostDataVoucher26' WHERE `vou_stav_skupinaID` = $sw_skupina_id and `vou_GUID_voucheru` = '$PostDataVoucher15';";

                                   
                                   $vysledek_zalozeni_voucher_update = @$mysqli->query($sql_voucher_update);

*/

                      
                    
                   ?>


                                <div class="row">
                                  <div class="col-md-6">
                                      <div class="panel panel-default hs-voucher-panel" >
                                          <div class="panel-heading">
                                              <h3 class="panel-title" data-hs-voucher-label="Editace zůstatkové ceny"><font face="tahoma"><b>Editace zůstatkové ceny</b></font></h3>
                                              <div class="actions pull-right">
                                                  <i class="fa fa-expand"></i>
                                                  <i class="fa fa-chevron-down"></i>
                                                  <i class="fa fa-times"></i>
                                              </div>
                                          </div>
                                          <div class="panel-body">


                                          <form action="https://klient.hairsoft.cz/str/index.php?strana=VoucherHistorie&guid=<?php echo $guid; ?>" method="POST" >
                                            <input type="hidden" class="form-control" name="UlozitHodnotuCeniny" value="1">
                                            <input type="hidden" class="form-control" name="UlozitHodnotuCeninyGUID" value="<?php echo $guid; ?>">

                                            <input type="hidden" class="form-control" name="UlozitHodnotuCeninyPuvodniHodnota" value="<?php echo $ZustatekSUMVoucheru; ?>">
                                            
                                            <input type="hidden" class="form-control" name="UlozitHodnotuCeninyMIN" value="<?php echo $ZustatekSUMVoucheru; ?>">
                                            <input type="hidden" class="form-control" name="UlozitHodnotuCeninyMAX" value="<?php echo $CelkovaHodnotaVBoucheru; ?>">

                                            <input type="hidden" class="form-control" name="ReadDataVoucher01" value="<?php echo $ReadDataVoucher01; ?>"> 
                                            <input type="hidden" class="form-control" name="ReadDataVoucher02" value="<?php echo $ReadDataVoucher02; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher03" value="<?php echo $ReadDataVoucher03; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher04" value="<?php echo $ReadDataVoucher04; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher05" value="<?php echo $ReadDataVoucher05; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher06" value="<?php echo $ReadDataVoucher06; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher07" value="<?php echo $ReadDataVoucher07; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher08" value="<?php echo $ReadDataVoucher08; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher09" value="<?php echo $ReadDataVoucher09; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher10" value="<?php echo $ReadDataVoucher10; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher11" value="<?php echo $ReadDataVoucher11; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher12" value="<?php echo $ReadDataVoucher12; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher13" value="<?php echo $ReadDataVoucher13; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher14" value="<?php echo $ReadDataVoucher14; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher15" value="<?php echo $ReadDataVoucher15; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher16" value="<?php echo $ReadDataVoucher16; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher17" value="<?php echo $ReadDataVoucher17; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher18" value="<?php echo $ReadDataVoucher18; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher19" value="<?php echo $ReadDataVoucher19; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher20" value="<?php echo $ReadDataVoucher20; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher21" value="<?php echo $ReadDataVoucher21; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher22" value="<?php echo $ReadDataVoucher22; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher23" value="<?php echo $ReadDataVoucher23; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher24" value="<?php echo $ReadDataVoucher24; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher25" value="<?php echo "RUČNÍ ZMĚNA ZŮSTATKU!"; ?>">  
                                            <input type="hidden" class="form-control" name="ReadDataVoucher26" value="<?php echo $ReadDataVoucher26; ?>">  

                                            <input type="hidden" class="form-control" name="ReadDataVoucher28" value="<?php echo $ReadDataVoucher28; ?>">  








                                              <div class="form-group">
                                                  <label class="col-sm-5 control-label">Aktuální zůstatková cena <?php echo "(".number_format(0, 2, '.', ' ')." - ".number_format($ZustatekSUMVoucheru, 2, '.', ' ').")" ?></label>
                                                  <div class="col-sm-2">
                                                      <?php 
                                                        
                                                       ?>
                                                      <input class="form-control" type="number"   name="UlozitHodnotuCeninyHodnota" value="<?php echo $ZustatekSUMVoucheru; ?>">
                                                  </div>
                                              </div>

                                                                                        


                                             <button type="submit" class="btn btn-primary" data-hs-voucher-label="Uložit novou zůstatkovou cenu">Uložit novou zůstatkovou cenu</button>

                                          </form>



                                          </div>
                                      </div>
                                    </div>
                                </div>

                  <?php 
                    }



/*ZMENA DATA*/
                                if ($ZmenitPlatnost=="1" and $ZmenitPlatnostGUID!="") {
                                ?>

 <div class="row">
                                  <div class="col-md-6">
                                      <div class="panel panel-default hs-voucher-panel" >
                                          <div class="panel-heading">
                                              <h3 class="panel-title" data-hs-voucher-label="Editace platnosti voucheru"><font face="tahoma"><b>Editace platnosti voucheru</b></font></h3>
                                              <div class="actions pull-right">
                                                  <i class="fa fa-expand"></i>
                                                  <i class="fa fa-chevron-down"></i>
                                                  <i class="fa fa-times"></i>
                                              </div>
                                          </div>
                                          <div class="panel-body">


                                          <form action="https://klient.hairsoft.cz/str/index.php?strana=VoucherHistorie&guid=<?php echo $guid; ?>" method="POST" >
                                            <input type="hidden" class="form-control" name="UlozitPlatnostCeniny" value="1">
                                            <input type="hidden" class="form-control" name="UlozitPlatnostCeninyGUID" value="<?php echo $guid; ?>">

                                            

                                              <?php 
                                                 $datum = date("d.m.Y"); 
                                                 $originalDate = $datum;
                                                 $newDate = date("d.m.Y", strtotime($originalDate));
                                               ?>


                                              <div class="form-group">
                                                  <label class="col-sm-3 control-label">Nová platnost voucheru</label>
                                                  <div class="col-sm-2">
                                                      <input type="text" id="datepicker1"  class="form-control"  size="8" name="UlozitPlatnostCeninyDatum" value="<?php echo $newDate; ?>">         
                                                  </div>
                                              </div>

                                                                                        


                                             <button type="submit" class="btn btn-primary" data-hs-voucher-label="Uložit novou platnost">Uložit novou platnost</button>

                                          </form>



                                          </div>
                                      </div>
                                    </div>
                                </div>



                                <?php 
                                }
                                ?>





















              
            
           
                 
                 
                 <div class="row">
                    <div class="col-md-12">
                            
                        <div class="panel panel-default hs-voucher-panel" >
                            <div class="panel-heading">
                                <h3 class="panel-title" data-hs-voucher-label="Historie vybraného voucheru"><font face="tahoma"><b>Historie vybraného voucheru</b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                               
                                
                                <table id="example" class="table table-striped table-bordered" cellspacing="0" width="99.8%" >
                                    <thead>
                                        <tr>
                                            <td align="center" data-hs-voucher-header="#"><b>#</b></td>
                                            <td align="center" data-hs-voucher-header="Kód"><b>Kód</b></td>
                                            <td align="center" data-hs-voucher-header="Stav"><b>Stav</b></td>
                                            <td align="center" data-hs-voucher-header="Hodnota"><b>Hodnota</b></td>

                                            
                                            <td align="center" data-hs-voucher-header="Datum prodeje"><b>Datum prodeje</b></td>
                                            <td align="center" data-hs-voucher-header="Prodejce"><b>Prodejce</b></td>
                                            <td align="center" data-hs-voucher-header="Placeno"><b>Placeno</b></td>
                                            <td align="center" data-hs-voucher-header="Realizoval"><b>Realizoval</b></td>
                                            
                                            <td align="center" data-hs-voucher-header="Položka"><b>Položka</b></td>
                                            
                                            <td align="center" data-hs-voucher-header="Před.čerpání"><b>Před.čerpání</b></td>
                                            <td align="center" data-hs-voucher-header="Čerpáno"><b>Čerpáno</b></td>
                                            <td align="center" data-hs-voucher-header="Zůstatek"><b>Zůstatek</b></td>
                                            <td align="center" data-hs-voucher-header="Platnost"><b>Platnost</b></td>
                                            <td align="center" data-hs-voucher-header="Do data"><b>Do data</b></td>
                                            <td align="center" data-hs-voucher-header="Datum čerpání"><b>Datum čerpání</b></td>
                                            
                                            

                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        



                                                 $sqldotaz_voucher= "SELECT * FROM `vouchers_historie` WHERE `vou_GUID_voucheru` = '$guid' order by 1 asc";
                                                  if (!$sqldotaz_voucher) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                    $vysledek_voucher=$mysqli->query("$sqldotaz_voucher");                                       
                                                  
                                                  
                                                  $pocitadlo_radku_voucher = 0;
                                                  $PocitadloSUMvoucheru = 0;
                                                  
                                                  
                                                  while ($option=MySQLi_Fetch_Array($vysledek_voucher)):   
                                                     
                                                     
                                                    $pocitadlo_radku_voucher = $pocitadlo_radku_voucher +1;   
                                                     
                                                    $Zustatek_vypocitany = $option["vou_hodnota_voucheru"]-$option["vou_cerpana_castka"];


                                                    $UpraveneCerpani = $option["vou_posledni_cerpani"];
                                                    
                                                    if($UpraveneCerpani=="0000-00-00 00:00:00" or $UpraveneCerpani=="1899-12-30 00:00:00"){
                                                      $UpraveneCerpani = "";
                                                    }else{
                                                      $UpraveneCerpani = VlozNedelitelneMezery(date("d.m.Y H:i", strtotime($UpraveneCerpani)));
                                                    }


                                                    switch (trim($option["vou_stav"])) {
                                                       case 'Neplatný':
                                                         $StavStyle = " style=\"color: #e45f60;\" ";
                                                        break;

                                                        case 'V oběhu':
                                                         $StavStyle = " style=\"color: #49bcb9;\" ";
                                                        break;

                                                        case 'Vyčerpaný':
                                                         $StavStyle = " style=\"color: #8eb5d2;\" ";
                                                        break;

                                                        case 'Částečně čerpaný':
                                                         $StavStyle = " style=\"color: #56688c;\" ";
                                                        break;
                                                      
                                                      default:
                                                        # code...
                                                        break;
                                                    }


                                                    echo "<tr>";
                                                     
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">$pocitadlo_radku_voucher</div></td>";
                                                          echo "<td><div align=\"left\" style=\"width: 90%;\">".$option["vou_kod"]."</div></td>";
                                                          echo "<td ".$StavStyle."><div align=\"center\" style=\"width: 90%;\">".VlozNedelitelneMezery($option["vou_stav"])."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">".VlozNedelitelneMezery(number_format($option["vou_hodnota_voucheru"], 2, ',', ' ')." ".$Mena_Klienta)."</div></td>";


                                                          echo "<td><div align=\"right\" style=\"width: 90%;\">".VlozNedelitelneMezery(date("d.m.Y H:i", strtotime($option["vou_datum_vytvoreni"])))."</div></td>";
                                                          echo "<td><div align=\"left\" style=\"width: 90%;\">".$option["vou_jmeno_uzivatele_prodal"]."</div></td>";
                                                          echo "<td><div align=\"left\" style=\"width: 90%;\">".$option["vou_placeno"]."</div></td>";
                                                          echo "<td><div align=\"left\" style=\"width: 90%;\">".$option["vou_jmeno_uzivatele_cerpal"]."</div></td>";
                                                          
                                                          echo "<td><div align=\"left\" style=\"width: 90%;\">".$option["vou_nazev_voucheru"]."</div></td>";
                                                          
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">".VlozNedelitelneMezery(number_format($option["vou_castka_predchozi"], 2, ',', ' ')." ".$Mena_Klienta)."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">".VlozNedelitelneMezery(number_format($option["vou_cerpana_castka"], 2, ',', ' ')." ".$Mena_Klienta)."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">".VlozNedelitelneMezery(number_format($Zustatek_vypocitany, 2, ',', ' ')." ".$Mena_Klienta)."</div></td>";
                                                          echo "<td><div align=\"center\" style=\"width: 90%;\">".$option["vou_doba_platnosti_mesice"]."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 90%;\">".VlozNedelitelneMezery(date("d.m.Y H:i", strtotime($option["vou_datum_platnosti_do"])))."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 90%;\">".$UpraveneCerpani."</div></td>";
                                                          
                                                          
                                                          


                                                     /*
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">$pocitadlo_radku_kredit</div></td>";
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">".date("d.m.Y v H:i", strtotime($option["sms_timestamp"]))."</div></td>";
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">".number_format($option["sms_dobito"], 2, ',', ' ')." ".$Mena_Klienta."</div></td>";
                                                     
                                                     $PocitadloSUMkreditu = $PocitadloSUMkreditu + ($option["sms_dobito"] ); 
                                                                                                          
                                                     $dobito_kredit =  number_format($option["sms_dobito"] / $suma_kreditu_dobito_Cena, 0, ',', ' ') . " SMS";
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">".$dobito_kredit."</div></td>";
                                                     */       
                                                    echo "</tr>";
                                                  endwhile;
                                        ?>
                                                                           
                                    </tbody>
                                </table>

                                                <br>
                                                  <div class="rada">
                                                    <form action="https://klient.hairsoft.cz/str/index.php?strana=Voucher" method="POST" >
                                                      <button type="submit" class="btn btn-info btn-square" data-hs-voucher-label="Zpět na přehled Voucherů"><i class="fa fa-chevron-left"></i>Zpět na přehled Voucherů</button>
                                                    </form>
                                                  </div>

                                                  <div class="rada">
                                                    <form action="https://klient.hairsoft.cz/str/index.php?strana=Voucher" method="POST" >
                                                      
                                                      <input type="hidden" class="form-control" name="SmazatCeninu" value="1">
                                                      <input type="hidden" class="form-control" name="SmazatCeninuGUID" value="<?php echo $guid; ?>">

                                                      <button type="submit" onClick="if(!confirm('Opravdu chcete SMAZAT tuto ceninu i její historii? Tato akce je nevratná!')){return false;}" class="btn btn-danger" data-hs-voucher-label="Smazat ceninu"><i class="fa icon-trash"></i>Smazat ceninu</button>
                                                    </form>
                                                  </div>

                                                  <div class="rada">
                                                    <form action="https://klient.hairsoft.cz/str/index.php?strana=VoucherHistorie" method="POST" >
                                                      
                                                      <input type="hidden" class="form-control" name="ZmenitHodnotuCeniny" value="1">
                                                      <input type="hidden" class="form-control" name="ZmenitHodnotuCeninyGUID" value="<?php echo $guid; ?>">

                                                      <button type="submit" class="btn btn-primary" data-hs-voucher-label="Editace zůstatkové ceny"><i class="fa fa-wrench"></i>Editace zůstatkové ceny</button>
                                                    </form>
                                                  </div>

                                                  <div class="rada">
                                                    <form action="https://klient.hairsoft.cz/str/index.php?strana=VoucherHistorie" method="POST" >
                                                      
                                                      <input type="hidden" class="form-control" name="ZmenitPlatnost" value="1">
                                                      <input type="hidden" class="form-control" name="ZmenitPlatnostGUID" value="<?php echo $guid; ?>">

                                                      <button type="submit" class="btn btn-success" data-hs-voucher-label="Editace platnosti"><i class="fa fa-calendar"></i>Editace platnosti</button>
                                                    </form>
                                                  </div>


                            </div>
                        </div>
                      </div>
                  </div>

                   <div class="row">
                    <div class="col-md-6">
                            
                        <div class="panel panel-default hs-voucher-panel" >
                            <div class="panel-heading">
                                <h3 class="panel-title" data-hs-voucher-label="Sumář"><font face="tahoma"><b>Sumář</b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                               
                              
                                <div class="table-responsive">                                
                                <table id="example2" class="table table-striped table-bordered" cellspacing="0" width="99%" >
                                    <thead>
                                        <tr>
                                            <td align="center" data-hs-voucher-header="Sazba"><b>Sazba</b></td>
                                            <td align="center" data-hs-voucher-header="Celkem"><b>Celkem</b></td>
                                            <td align="center" data-hs-voucher-header="DPH"><b>DPH</b></td>
                                            <td align="center" data-hs-voucher-header="Základ"><b>Základ</b></td>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        
                                                 $sqldotaz_voucherDPH= "SELECT `vou_sazba_DPH` as 'Sazba', SUM(`vou_hodnota_voucheru`) as 'Celkem', SUM(`vou_hodnota_voucheru`) - SUM(`vou_hodnota_bezDPH`) as 'DPH' ,SUM(`vou_hodnota_bezDPH`) as 'Základ' FROM `vouchers` WHERE `vou_GUID_voucheru` = '$guid' group by `vou_sazba_DPH`";
                                                  if (!$sqldotaz_voucherDPH) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                    $vysledek_voucherDPH=$mysqli->query("$sqldotaz_voucherDPH");                                       
                                                  
                                                  
                                                  $pocitadlo_radku_voucherDPH = 0;
                                                  $PocitadloSUMvoucherDPHu = 0;
                                                  
                                                  
                                                  while ($option=MySQLi_Fetch_Array($vysledek_voucherDPH)):   
                                                     
                                                     
                                                    $pocitadlo_radku_voucherDPH = $pocitadlo_radku_voucherDPH +1;   
                                                     
                                                     echo "<tr>";
                                                     
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">".$option["Sazba"]."%</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">".number_format($option["Celkem"], 2, ',', ' ')." ".$Mena_Klienta."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">".number_format($option["DPH"], 2, ',', ' ')." ".$Mena_Klienta."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">".number_format($option["Základ"], 2, ',', ' ')." ".$Mena_Klienta."</div></td>";
                                                         
                                                     /*
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">$pocitadlo_radku_kredit</div></td>";
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">".date("d.m.Y v H:i", strtotime($option["sms_timestamp"]))."</div></td>";
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">".number_format($option["sms_dobito"], 2, ',', ' ')." ".$Mena_Klienta."</div></td>";
                                                     
                                                     $PocitadloSUMkreditu = $PocitadloSUMkreditu + ($option["sms_dobito"] ); 
                                                                                                          
                                                     $dobito_kredit =  number_format($option["sms_dobito"] / $suma_kreditu_dobito_Cena, 0, ',', ' ') . " SMS";
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">".$dobito_kredit."</div></td>";
                                                     */       
                                                    echo "</tr>";
                                                  endwhile;
                                        ?>
                                                                         
                                        
                                    </tbody>
                                </table>
                            </div>  
                            </div>
                            
                        </div>
                      </div>
                      
                      
                        
                        
                    
                  </div>
                 
                 
                  <?php
                        
                        //echo "Dodelat paticku po prvnim voucheru + demo data. ";
                        //posledni zaznam v tabulce
                        $sql_PosledniAktualizace= "SELECT `vou_zmena` FROM `vouchers` WHERE `vou_stav_skupinaID` = $skupina_id order by `vou_zmena` Desc LIMIT 1";
                        //echo $sql_PosledniAktualizace;
                        $vysledek_sql_PosledniAktualizace=$mysqli->query($sql_PosledniAktualizace);
                        $data_sql_PosledniAktualizace=MySQLi_Fetch_Array($vysledek_sql_PosledniAktualizace);
                        //$CelkoveTrzby = number_format($data_sql_PosledniAktualizace["TrzbyCelkem"], 2, ',', ' ');
                        $PosledniAktualizace = $data_sql_PosledniAktualizace["vou_zmena"];
                        
                        if ($PosledniAktualizace=="") {
                          $PosledniAktualizace = " nezjištěno ";
                        }
                          
                         
                        
                           if (strtotime($PosledniAktualizace)!="") {
                        ?>
                                                                  <div class="row">
                                                                  <div class="col-md-12">
                                                                      <div class="panel panel-default hs-voucher-panel">
                                                                          <div class="panel-body">
                                                                              <font size="-2">Poslední změna: 
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




 
