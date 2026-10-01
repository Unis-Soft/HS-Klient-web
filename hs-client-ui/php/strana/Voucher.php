    
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
  if ($_SESSION["JePoduzivatel"]=="1" and in_array("Voucher", $_SESSION["SeznamPravPoduzivatele"])=="0") {
    echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=PrazdnaStrana\">";
    exit;
  }
  // Prava na stranku
  

  require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';
  require HS_CLIENT_UI_ROOT . '/fce/GeneratorBarev.php';
  //require_once '../../cfg/nastaveni.php';
  
  //Na zjisteni pobocky
  $s_email = $_SESSION["k_email"];  
  
  $jmenoStranky = "Vouchery";
  $jmenoStrankyPopis = "Přehled Vašich voucherů";
  
  if ($_SESSION["pobocka_jmeno"]=="") {
    $pobocka_jmeno =  "Nevybrána" ;
  }else {
    $pobocka_jmeno =  $_SESSION["pobocka_jmeno"] ;  
  }

        //AKCE
        if (!isset($_POST['datum']) || is_array($_POST['datum'])){$_POST['datum']='';}
        $datum  =  htmlspecialchars($_POST['datum'], ENT_COMPAT);

        if (!isset($_POST['SmazatCeninu']) || is_array($_POST['SmazatCeninu'])){$_POST['SmazatCeninu']='';}
        $SmazatCeninu  =  htmlspecialchars($_POST['SmazatCeninu'], ENT_COMPAT);

        if (!isset($_POST['SmazatCeninuGUID']) || is_array($_POST['SmazatCeninuGUID'])){$_POST['SmazatCeninuGUID']='';}
        $SmazatCeninuGUID  =  htmlspecialchars($_POST['SmazatCeninuGUID'], ENT_COMPAT);

        if (!isset($_POST['SmazatNeplatnouCeninu']) || is_array($_POST['SmazatNeplatnouCeninu'])){$_POST['SmazatNeplatnouCeninu']='';}
        $SmazatNeplatnouCeninu  =  htmlspecialchars($_POST['SmazatNeplatnouCeninu'], ENT_COMPAT);

        //Filtr
        if (!isset($_POST['DatumVoucheryFiltr']) || is_array($_POST['DatumVoucheryFiltr'])){$_POST['DatumVoucheryFiltr']='';}
        $DatumVoucheryFiltr  =  htmlspecialchars($_POST['DatumVoucheryFiltr'], ENT_COMPAT);
        
        if (!isset($_POST['VoucheryDatumOd']) || is_array($_POST['VoucheryDatumOd'])){$_POST['VoucheryDatumOd']='';}
        $VoucheryDatumOd  =  htmlspecialchars($_POST['VoucheryDatumOd'], ENT_COMPAT);
        
        if (!isset($_POST['VoucheryDatumDo']) || is_array($_POST['VoucheryDatumDo'])){$_POST['VoucheryDatumDo']='';}
        $VoucheryDatumDo  =  htmlspecialchars($_POST['VoucheryDatumDo'], ENT_COMPAT);

        if (!isset($_POST['VoucheryPobocka']) || is_array($_POST['VoucheryPobocka'])){$_POST['VoucheryPobocka']='';}
        $VoucheryPobocka  =  htmlspecialchars($_POST['VoucheryPobocka'], ENT_COMPAT);

        if (!isset($_POST['vou_jmeno_uzivatele_prodal']) || is_array($_POST['vou_jmeno_uzivatele_prodal'])){$_POST['vou_jmeno_uzivatele_prodal']='';}
        $vou_jmeno_uzivatele_prodal  =  htmlspecialchars($_POST['vou_jmeno_uzivatele_prodal'], ENT_COMPAT);


        if (!isset($_POST['SmazatVsechnyVouchery']) || is_array($_POST['SmazatVsechnyVouchery'])){$_POST['SmazatVsechnyVouchery']='';}
        $SmazatVsechnyVouchery  =  htmlspecialchars($_POST['SmazatVsechnyVouchery'], ENT_COMPAT);

        if (!isset($_POST['SmazatVsechnyVoucherySkupina']) || is_array($_POST['SmazatVsechnyVoucherySkupina'])){$_POST['SmazatVsechnyVoucherySkupina']='';}
        $SmazatVsechnyVoucherySkupina  =  htmlspecialchars($_POST['SmazatVsechnyVoucherySkupina'], ENT_COMPAT);
  
        //CHECK box

        if (!isset($_POST['StavVoucheru']) || is_array($_POST['StavVoucheru'])){$_POST['StavVoucheru']='';}
        $StavVoucheru  =  htmlspecialchars($_POST['StavVoucheru'], ENT_COMPAT);


        if (!isset($_POST['DatumVoucheryFiltrSmazat']) || is_array($_POST['DatumVoucheryFiltrSmazat'])){$_POST['DatumVoucheryFiltrSmazat']='';}
        $DatumVoucheryFiltrSmazat  =  htmlspecialchars($_POST['DatumVoucheryFiltrSmazat'], ENT_COMPAT);


        if ($DatumVoucheryFiltr==1) {
              $_SESSION["VoucheryDatumOd"]= $VoucheryDatumOd ;            
              $_SESSION["VoucheryDatumDo"]= $VoucheryDatumDo ;            
              $_SESSION["vou_jmeno_uzivatele_prodal"]= $vou_jmeno_uzivatele_prodal ;            
              $_SESSION["StavVoucheru"]= $StavVoucheru ;            
        }

        //Uruseni filtru
        if ($DatumVoucheryFiltrSmazat==1) {
              $_SESSION["VoucheryDatumOd"]= "" ;            
              $_SESSION["VoucheryDatumDo"]= "" ;            
              $_SESSION["vou_jmeno_uzivatele_prodal"]= "" ;            
              $_SESSION["StavVoucheru"]= "" ;            
        }
        
      /*

      --Pokud nic nebude aplikovat 
      $sqlBetweenVouchery = " AND (`vou_datum_vytvoreni` BETWEEN '".$newDate1."' AND '".$newDate2."' OR `vou_posledni_cerpani` BETWEEN '".$newDate1."' AND '".$newDate2."' ) ";

      Pokud bude prodej apikovat jen prodej 

      Pokud bude cerpani aplikovat jen cerpani 


      Pokud OBA vse


      */



      //AKCE

        /*smazat vouchery jen ja nebo zdenda*/
        if ($SmazatVsechnyVouchery=="1" and $SmazatVsechnyVoucherySkupina!="") {
          if ($SmazatVsechnyVoucherySkupina=="91" or $SmazatVsechnyVoucherySkupina=="487") {
           /*
            DELETE FROM `vouchers_historie` WHERE `vou_stav_skupinaID` = 
            DELETE FROM `vouchers` WHERE `vou_stav_skupinaID` = 
           */
               $sql_voucher_delete = "DELETE FROM `vouchers` WHERE `vou_stav_skupinaID` = ".$SmazatVsechnyVoucherySkupina;
               $vysledek_zalozeni_voucher_delete = @$mysqli->query($sql_voucher_delete);

               $sql_voucher_delete1 = "DELETE FROM `vouchers_historie` WHERE `vou_stav_skupinaID` = ".$SmazatVsechnyVoucherySkupina;
               $vysledek_zalozeni_voucher_delete1 = @$mysqli->query($sql_voucher_delete1);

          }
        }


        if ($_SESSION["VoucheryDatumOd"]!="" and $_SESSION["VoucheryDatumDo"]!="") {
               
               $originalDate = $_SESSION["VoucheryDatumOd"];
               $newDate1 = date("Y-m-d 00:00:00", strtotime($originalDate));

               $originalDate2 = $_SESSION["VoucheryDatumDo"];
               $newDate2 = date("Y-m-d 23:59:59", strtotime($originalDate2));
                 
                
/*
Vse
Prodano
Castecne
Vycerpano
Expirovano
*/
                switch ($StavVoucheru) {
                  case 'Vse':
                      $sqlBetweenVouchery = " AND (`vou_datum_vytvoreni` BETWEEN '".$newDate1."' AND '".$newDate2."' OR `vou_posledni_cerpani` BETWEEN '".$newDate1."' AND '".$newDate2."'  OR `vou_datum_platnosti_do` BETWEEN '".$newDate1."' AND '".$newDate2."')  "; 
                    break;

                  case 'Prodano':
                      //$sqlBetweenVouchery = " AND (`vou_datum_vytvoreni` BETWEEN '".$newDate1."' AND '".$newDate2."') AND  `vou_stav` = 'V oběhu' "; 
                      $sqlBetweenVouchery = " AND (`vou_datum_vytvoreni` BETWEEN '".$newDate1."' AND '".$newDate2."')  "; 
                    break;
                  case 'Castecne':
                      $sqlBetweenVouchery = " AND (`vou_posledni_cerpani` BETWEEN '".$newDate1."' AND '".$newDate2."' ) AND  `vou_stav` = 'Částečně čerpaný' "; 
                    break;
                  case 'Vycerpano':
                      $sqlBetweenVouchery = " AND (`vou_posledni_cerpani` BETWEEN '".$newDate1."' AND '".$newDate2."' ) AND  `vou_stav` = 'Vyčerpaný' "; 
                    break;
                  case 'Expirovano':
                      $sqlBetweenVouchery = " AND (`vou_datum_platnosti_do` BETWEEN '".$newDate1."' AND '".$newDate2."' ) "; 
                    break;
   
                  
                  default:
                      $sqlBetweenVouchery = " AND (`vou_datum_vytvoreni` BETWEEN '".$newDate1."' AND '".$newDate2."' OR `vou_posledni_cerpani` BETWEEN '".$newDate1."' AND '".$newDate2."'  OR `vou_datum_platnosti_do` BETWEEN '".$newDate1."' AND '".$newDate2."')  "; 
                    break;
                }



/*
                if ($_SESSION["chck_prodej"]=="1" and $_SESSION["chck_cerpany"]!="1") {
                            
                            $sqlBetweenVouchery = " AND (`vou_datum_vytvoreni` BETWEEN '".$newDate1."' AND '".$newDate2."') ";
                
                }elseif ($_SESSION["chck_prodej"]!="1" and $_SESSION["chck_cerpany"]=="1") {
                            
                            $sqlBetweenVouchery = " AND (`vou_posledni_cerpani` BETWEEN '".$newDate1."' AND '".$newDate2."' ) ";

                }elseif ($_SESSION["chck_prodej"]=="1" and $_SESSION["chck_cerpany"]=="1") {
                            //OBA
                            $sqlBetweenVouchery = " AND (`vou_datum_vytvoreni` BETWEEN '".$newDate1."' AND '".$newDate2."' OR `vou_posledni_cerpani` BETWEEN '".$newDate1."' AND '".$newDate2."' ) ";
                }else{
                            $sqlBetweenVouchery = " AND (`vou_datum_vytvoreni` BETWEEN '".$newDate1."' AND '".$newDate2."' OR `vou_posledni_cerpani` BETWEEN '".$newDate1."' AND '".$newDate2."' ) ";
                }

                if ($_SESSION["chck_vycerpany"]=="1") {
                  $sqlBetweenVouchery = " AND (`vou_posledni_cerpani` BETWEEN '".$newDate1."' AND '".$newDate2."' )  AND  `vou_stav` = 'Vyčerpaný' "; 
                }
*/


              


          
        }else{
          $sqlBetweenVouchery = "";
        }




                                                //Filtr prodejce
                                                if ($_SESSION["vou_jmeno_uzivatele_prodal"]!="") {
                                                  $FiltrProdejce = " and vou_jmeno_uzivatele_prodal = '".$vou_jmeno_uzivatele_prodal."'";
                                                } else{
                                                  $FiltrProdejce = "";
                                                } 





        
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

  if ($sw_id==3316) {
    //$sw_id = 3129; // PODSTRNENEJ ZDENDA !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //$skupina_id = 5;
  }




  if ($_SESSION["k_id"]=="10") {
    //$Uzivatel_ID =11;
   // $sw_id = 1081;
    
    //$sw_id = 1999;    //BS
    //$Uzivatel_ID =11;

    //$skupina_id = 5;  // BarberCooper
    //$skupina_id = 91;  // BarberCooper test
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



               $originalDate = $datum;
               $newDate = date("d.m.Y", strtotime($originalDate));

               $datum_vouchery1 = date("01.m.Y");
               
               $posledniden = date("t");
               $datum_vouchery2 = date($posledniden.".m.Y");
               
               //date("Y-m-t", strtotime($a_date));

               //$newYear = date("Y", strtotime($originalDate));
               //$datum_pozadavek =  date("Y-m-d", strtotime($newDate));
               
        


     


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
  <section id="main-content" class="hs-voucher-page" data-voucher-page="Voucher">                
  
  <?php
                                



                               //WHERE NEPLATNY
                                $VoucherNeplatnyAnd = " `vou_stav` <> 'Neplatný' and ";
                                $VoucherNeplatny    = " `vou_stav` <> 'Neplatný' ";

     

                                if ($VoucheryPobocka!="") {
                                  $sqlBetweenVouchery = $sqlBetweenVouchery . " and `sw_id` = ".  $VoucheryPobocka;
                                }
                                



                                //Kolik voucher celkem
                                $select_na_sumu_voucher= "SELECT COUNT(*)as 'CNT_Voucheru' FROM `vouchers` WHERE $VoucherNeplatnyAnd `vou_stav_skupinaID` = '$skupina_id' $FiltrProdejce $sqlBetweenVouchery";
                                //echo $select_na_sumu_voucher;
                                if (!$select_na_sumu_voucher) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_sumu_voucher=$mysqli->query("$select_na_sumu_voucher");
                                $data_suma_voucher=MySQLi_Fetch_Array($vysledek_na_sumu_voucher);


                                //Cenin v obehu
                                $select_na_sumu_voucher_vobehu= "SELECT COUNT(*)as 'CNT_Voucheru_vobehu' FROM `vouchers` WHERE $VoucherNeplatnyAnd `vou_stav` <> 'Vyčerpaný' and `vou_stav_skupinaID` = '$skupina_id' $FiltrProdejce $sqlBetweenVouchery";
                                if (!$select_na_sumu_voucher_vobehu) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_sumu_voucher_vobehu=$mysqli->query("$select_na_sumu_voucher_vobehu");
                                $data_suma_voucher_vobehu=MySQLi_Fetch_Array($vysledek_na_sumu_voucher_vobehu);
    
                                // Celkova hodnota voucher
                                $select_na_sumu_voucheru_hodnota= "SELECT SUM(`vou_hodnota_voucheru`) as 'SUM_Hodnota_Voucheru' FROM `vouchers` WHERE $VoucherNeplatnyAnd `vou_stav_skupinaID` = '$skupina_id' $FiltrProdejce $sqlBetweenVouchery";
                                if (!$select_na_sumu_voucheru_hodnota) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_sumu_voucheru_hodnota=$mysqli->query("$select_na_sumu_voucheru_hodnota");
                                $data_suma_voucher_hodnota=MySQLi_Fetch_Array($vysledek_na_sumu_voucheru_hodnota);
                                
                                //Cerpano voucher
                                $select_na_cerpano_voucher= "SELECT SUM(`vou_cerpana_castka`) as 'SUM_Cerpano_SUM' FROM `vouchers` WHERE $VoucherNeplatnyAnd `vou_stav_skupinaID` = '$skupina_id' $FiltrProdejce $sqlBetweenVouchery";
                                //echo $select_na_cerpano_voucher;
                                if (!$select_na_cerpano_voucher) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_cerpano_voucher=$mysqli->query("$select_na_cerpano_voucher");
                                $suma_cerpano_voucher=MySQLi_Fetch_Array($vysledek_na_cerpano_voucher);

                                //Zustatek
                                $ZustatekSUMVoucheru = $data_suma_voucher_hodnota["SUM_Hodnota_Voucheru"] - $suma_cerpano_voucher["SUM_Cerpano_SUM"];
                                


                                //SUM vycerpano
                                $select_na_sumu_voucher_vycerpany= "SELECT SUM(`vou_cerpana_castka`)as 'SUM_Vycerpano' FROM `vouchers` WHERE  `vou_stav` = 'Vyčerpaný' and `vou_stav_skupinaID` = '$skupina_id' $FiltrProdejce $sqlBetweenVouchery";
                                if (!$select_na_sumu_voucher_vycerpany) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_sumu_voucher_vycerpany=$mysqli->query("$select_na_sumu_voucher_vycerpany");
                                $data_suma_voucher_vycerpany=MySQLi_Fetch_Array($vysledek_na_sumu_voucher_vycerpany);


                                //SUM vycerpano castecne
                                $select_na_sumu_voucher_vycerpany_castecne= "SELECT SUM(`vou_cerpana_castka`)as 'SUM_Vycerpano_Castecne' FROM `vouchers` WHERE  `vou_stav` = 'Částečně čerpaný' and `vou_stav_skupinaID` = '$skupina_id' $FiltrProdejce $sqlBetweenVouchery";
                                if (!$select_na_sumu_voucher_vycerpany_castecne) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_sumu_voucher_vycerpany_castecne=$mysqli->query("$select_na_sumu_voucher_vycerpany_castecne");
                                $data_suma_voucher_vycerpany_castecne=MySQLi_Fetch_Array($vysledek_na_sumu_voucher_vycerpany_castecne);


                                $data_suma_voucher_vycerpanySUM = $data_suma_voucher_vycerpany["SUM_Vycerpano"];
                                $data_suma_voucher_vycerpany_castecneSUM = $data_suma_voucher_vycerpany_castecne["SUM_Vycerpano_Castecne"];



                                $VystavenoVoucheru = $data_suma_voucher["CNT_Voucheru"];
                                $VystavenoVoucheru_vobehu = $data_suma_voucher_vobehu["CNT_Voucheru_vobehu"];





                              //smazat voucher i historii
                              if ($SmazatCeninu=="1" and $SmazatCeninuGUID!="") {

                                //hlavni
                                 $sql1 = "DELETE FROM `vouchers` WHERE `vou_stav_skupinaID` = $skupina_id and `vou_GUID_voucheru`= '".$SmazatCeninuGUID."';";
                                 $vysledek_smazani_voucher = @$mysqli->query($sql1);
                                 //echo $sql1;
                                 
                                 //historie
                                 $sql2 = "DELETE FROM `vouchers_historie` WHERE `vou_stav_skupinaID` = $skupina_id and `vou_GUID_voucheru`= '".$SmazatCeninuGUID."';";
                                 $vysledek_smazani_historie = @$mysqli->query($sql2);
                                 //echo $sql2;
                                  
                                  if ($vysledek_smazani_voucher and $vysledek_smazani_historie) {
                                     //echo $sql;
                                     ?>
                                           <div class="row">
                                            <div class="col-md-12">
                                              <div class="alert alert-success alert-dismissable">
                                                         <button type="button" class="close" data-dismiss="alert" aria-hidden="true" data-hs-voucher-label="×">×</button>
                                                         <strong>Cenina byla úspěšně smazána.</strong>
                                              </div>
                                            </div>
                                          </div>
                                     <?php 

                                     echo "<meta http-equiv=\"refresh\" content=\"1;URL=https://klient.hairsoft.cz/str/index.php?strana=Voucher\">";
                                     return;
                                   }else{
                                     $error = $mysqli->error; 
                                      echo $error; 
                                      return;
                                  }
                              }



                              //Smazat všechny neplatné ceniny.
                              if ($SmazatNeplatnouCeninu=="1") {

                                //hlavni
                                 $sql1 = "DELETE FROM `vouchers_historie` WHERE `vou_stav_skupinaID` = $skupina_id and `vou_GUID_voucheru` in (SELECT `vou_GUID_voucheru` FROM `vouchers` WHERE `vou_stav_skupinaID` = $skupina_id and `vou_stav` = 'Neplatný');";
                                 $vysledek_smazani_voucher_neplatne = @$mysqli->query($sql1);
                                 //echo $sql1;
                                 
                                 //historie
                                 $sql2 = "DELETE FROM `vouchers` WHERE `vou_stav_skupinaID` = $skupina_id and `vou_stav` = 'Neplatný';";
                                 $vysledek_smazani_historie_neplatne = @$mysqli->query($sql2);
                                 //echo $sql2;
                                  
                                  if ($vysledek_smazani_voucher_neplatne and $vysledek_smazani_historie_neplatne) {
                                     //echo $sql;
                                     ?>
                                           <div class="row">
                                            <div class="col-md-12">
                                              <div class="alert alert-success alert-dismissable">
                                                         <button type="button" class="close" data-dismiss="alert" aria-hidden="true" data-hs-voucher-label="×">×</button>
                                                         <strong>Neplatné ceniny byly úspěšně smazány.</strong>
                                              </div>
                                            </div>
                                          </div>
                                     <?php 

                                     echo "<meta http-equiv=\"refresh\" content=\"1;URL=https://klient.hairsoft.cz/str/index.php?strana=Voucher\">";
                                     return;
                                   }else{
                                     $error = $mysqli->error; 
                                      echo $error; 
                                      return;
                                  }
                              }


                              


     
                                           

?>
                                          


                  
  
  
                <div class="row">
                            <div class="col-md-3">
                                <div class="panel panel-solid-success widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $data_suma_voucher["CNT_Voucheru"] /*floor(($suma_kreditu_dobito_Cena_sms - $suma_sms_odeslano))*/ ." "; ?></font></span>
                                        <span class="title text-center">Vystaveno&nbsp;voucherů</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        
                                        <span class="total text-center"><font size="5"><?php echo number_format($data_suma_voucher_hodnota["SUM_Hodnota_Voucheru"], 2, ',', ' ')."&nbsp;".$Mena_Klienta ." "; ?></font></span>
                                        <span class="title text-center">Celková&nbsp;hodnota</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-solid-danger widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo number_format($ZustatekSUMVoucheru, 2, ',', ' ')."&nbsp;".$Mena_Klienta  ?></font></span>
                                        <span class="title text-center">Zůstatek&nbsp;</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo  number_format($suma_cerpano_voucher["SUM_Cerpano_SUM"], 2, ',', ' ')."&nbsp;".$Mena_Klienta ?></font></span>              
                                        <span class="title text-center">Čerpáno&nbsp;</span>
                                    </div>
                                </div>
                            </div>
                  </div>             
                
              

<?php 

if ($_SESSION["VoucheryDatumOd"]!="" and $_SESSION["VoucheryDatumDo"]!="") {
  ?>     
                                          <div class="row">
                                            <div class="col-md-12">
                                              <div class="alert alert-info alert-dismissable">
                                                         <button type="button" class="close" data-dismiss="alert" aria-hidden="true" data-hs-voucher-label="×">×</button>
                                                    <form action="https://klient.hairsoft.cz/str/index.php?strana=Voucher" method="POST" >
                                                         Aplikován filtr data: 
                                                         <strong>
                                                           <?php 
                                                                  echo $_SESSION["VoucheryDatumOd"]." - ". $_SESSION["VoucheryDatumDo"];  
                                                                  
                                                                  echo " - Stav: ";
                                                                  switch ($StavVoucheru) {
                                                                        case 'Vse':
                                                                            echo " Vše";
                                                                          break;

                                                                        case 'Prodano':
                                                                            echo " Prodáno";
                                                                          break;
                                                                        case 'Castecne':
                                                                            echo " Částečně čerpáno";
                                                                          break;
                                                                        case 'Vycerpano':
                                                                            echo " Vyčerpáno";
                                                                          break;
                                                                        case 'Expirovano':
                                                                            echo " Expirováno";
                                                                          break;
                                                                      }


                                                                  if ($VoucheryPobocka!="") {
                                                                      
                                                                        $select_na_sPobocka= "SELECT `sw_info`.`sw_jmeno_pobocky`, `sw_info`.`sw_id` FROM `sw_email_pobocka` join `k_uzivatele` on `sw_email_pobocka`.`k_id` = `k_uzivatele`.`k_id` join `sw_info` on `sw_info`.`sw_id` = `sw_email_pobocka`.`sw_id` WHERE `sw_info`.`sw_id` = ".$VoucheryPobocka;
                                                                        //echo $select_na_sPobocka;
                                                                        if (!$select_na_sPobocka) { die('Chyba pripojeni do DB!');}
                                                                        $vysledek_na_sPobocka=$mysqli->query("$select_na_sPobocka");
                                                                        $data_Pobocka=MySQLi_Fetch_Array($vysledek_na_sPobocka);
                                                                        echo " - Pobočka: ".$data_Pobocka["sw_jmeno_pobocky"];
                                                                        
                                                                  
                                                                  }else{
                                                                    echo " - Všechny pobočky";
                                                                  }  

                                                                  if ($_SESSION["vou_jmeno_uzivatele_prodal"]!="") {
                                                                    echo " - Prodejce: ". $_SESSION["vou_jmeno_uzivatele_prodal"];
                                                                  }  


                                                                 
                                                           ?>
                                                         </strong>

                                                      <input type="hidden" class="form-control" name="DatumVoucheryFiltrSmazat" value="1">&nbsp;&nbsp;
                                                      <button type="submit" class="btn btn-danger" data-hs-voucher-label="Zrušit filtr"><i class="fa icon-close"></i>Zrušit filtr</button>
                                                    </form>
                                              </div>
                                            </div>
                                          </div>
  <?php 
     //}else{
      }
  ?>   
                                          <div class="row">
                                            <div class="col-md-12">
                                              <div class="panel panel-default hs-voucher-panel hs-voucher-filter" >
                                                <div class="panel-heading">
                                                    <h3 class="panel-title" data-hs-voucher-label="Filtr"><font face="tahoma"><b><b>Filtr </b> <i class="fa fa-calendar"></i></b></font></h3>
                                                    <div class="actions pull-right">
                                                        <i class="fa fa-expand"></i>
                                                        <i class="fa fa-chevron-down"></i>
                                                        <i class="fa fa-times"></i>
                                                    </div>
                                                </div>
                                                
                                                <form class="form-horizontal form-border" action="index.php?strana=Voucher" method="POST" id="VoucherOdDo">
                                                  <input type="hidden" class="form-control" name="DatumVoucheryFiltr" value="1">
                                                      <div class="panel-body">
                                                                  
                                                                    <div class="form-group">
                                                                        <label class="col-sm-2 control-label">Pobočka </label>
                                                                        <div class="col-sm-2">
                                                                            <select style = "height:37px;font: inherit;" class="form-control input-lg" name="VoucheryPobocka">
                                                                                
                                                                                <option value="" <?php echo $VoucheryPobocka=="" ? 'selected=\"selected\"' : '' ?>>Vše</option>

                                                                                <?php 
                                                                                    $select_na_pobocku= "SELECT `sw_info`.`sw_jmeno_pobocky`, `sw_info`.`sw_id` FROM `sw_email_pobocka` join `k_uzivatele` on `sw_email_pobocka`.`k_id` = `k_uzivatele`.`k_id` join `sw_info` on `sw_info`.`sw_id` = `sw_email_pobocka`.`sw_id` WHERE `k_uzivatele`.`k_email` = '$s_email'";
                                                                                     if (!$select_na_pobocku) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                                                                              
                                                                                      $vysledek_na_pobocku=$mysqli->query($select_na_pobocku);
                                                                                      $radku_na_pobocku=$vysledek_na_pobocku->num_rows;                                       

                                                                                          while ($na_pobocku=MySQLi_Fetch_Array($vysledek_na_pobocku)):   

                                                                                            //'selected=\"selected\"'
                                                                                            if ($VoucheryPobocka==$na_pobocku["sw_id"]) {
                                                                                              $PobockaVybrana = "  selected='selected' ";
                                                                                            }else{
                                                                                              $PobockaVybrana = "";
                                                                                            }
                                                                                            echo "<option value='".$na_pobocku["sw_id"]."'>".$na_pobocku["sw_jmeno_pobocky"]."</option>";
                                                                                          endwhile;


                                                                                 ?>  



                                                                                
                                                                                
                                                                            </select>
                                                                        </div>
                                                                    
                                                                        <label class="col-sm-1 control-label">Stav</label>
                                                                        <div class="col-sm-2">
                                                                            <select style = "height:37px;font: inherit;" class="form-control input-lg" name="StavVoucheru">
                                                                                <option value="Vse" <?php echo $_SESSION["StavVoucheru"]=="Vse" ? 'selected=\"selected\"' : '' ?>>Vše</option>
                                                                                <option value="Prodano" <?php echo $_SESSION["StavVoucheru"]=="Prodano" ? 'selected=\"selected\"' : '' ?>>Prodáno</option>
                                                                                <option value="Castecne" <?php echo $_SESSION["StavVoucheru"]=="Castecne" ? 'selected=\"selected\"' : '' ?>>Částečně čerpáno</option>
                                                                                <option value="Vycerpano" <?php echo $_SESSION["StavVoucheru"]=="Vycerpano" ? 'selected=\"selected\"' : '' ?>>Vyčerpáno</option>
                                                                                <option value="Expirovano" <?php echo $_SESSION["StavVoucheru"]=="Expirovano" ? 'selected=\"selected\"' : '' ?>>Expirováno</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                              
                                                      
                                                                    <div class="form-group">
                                                                        <label class="col-sm-2 control-label">Od </label>
                                                                        <div class="col-sm-2">
                                                                            <?php 
                                                                                
                                                                                if ($_SESSION["VoucheryDatumOd"]!="") {
                                                                                  $FiltrOD = $_SESSION["VoucheryDatumOd"];
                                                                                }else{
                                                                                  $FiltrOD = $datum_vouchery1;
                                                                                }

                                                                                if ($_SESSION["VoucheryDatumDo"]!="") {
                                                                                  $FiltrDO = $_SESSION["VoucheryDatumDo"];
                                                                                }else{
                                                                                  $FiltrDO = $datum_vouchery2;
                                                                                }

                                                                             ?>

                                                                            <input type="text" id="datepicker_voucherod"  class="form-control"  size="8" name="VoucheryDatumOd" value="<?php echo $FiltrOD; ?>">         
                                                                        </div>
                                                                    
                                                                        <label class="col-sm-1 control-label">Do</label>
                                                                        <div class="col-sm-2">
                                                                            <input type="text" id="datepicker_voucherdo"  class="form-control"  size="8" name="VoucheryDatumDo" value="<?php echo $FiltrDO; ?>">         
                                                                        </div>

                                                                        
                                                                        

                                                                        
                                                                    </div>

                                                                     <div class="form-group">
                                                                        <label class="col-sm-2 control-label">Prodejce </label>
                                                                        <div class="col-sm-2">
                                                                            <select style = "height:37px;font: inherit;" class="form-control input-lg" name="vou_jmeno_uzivatele_prodal">
                                                                                
                                                                                <option value="">Vše</option>

                                                                                <?php 
                                                                                    $select_na_pobocku= "SELECT distinct vou_jmeno_uzivatele_prodal FROM `vouchers` WHERE  `vou_stav_skupinaID` = ".$skupina_id." order by 1 DESC";
                                                                                     if (!$select_na_pobocku) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                                                                              
                                                                                      $vysledek_na_pobocku=$mysqli->query($select_na_pobocku);
                                                                                      $radku_na_pobocku=$vysledek_na_pobocku->num_rows;                                       

                                                                                          while ($na_prodejce=MySQLi_Fetch_Array($vysledek_na_pobocku)):   

                                                                                            //'selected=\"selected\"'
                                                                                            if ($_SESSION["vou_jmeno_uzivatele_prodal"]==$na_prodejce["vou_jmeno_uzivatele_prodal"]) {
                                                                                              $PobockaVybrana = "  selected='selected' ";
                                                                                            }else{
                                                                                              $PobockaVybrana = "";
                                                                                            }
                                                                                            echo "<option $PobockaVybrana value='".$na_prodejce["vou_jmeno_uzivatele_prodal"]."'>".$na_prodejce["vou_jmeno_uzivatele_prodal"]."</option>";
                                                                                          endwhile;


                                                                                 ?>  



                                                                                
                                                                                
                                                                            </select>
                                                                            
                                                                        </div>
                                                                        <label class="col-sm-1 control-label"> </label>
                                                                         <div class="col-sm-2">
                                                                            <button  type="submit" class="btn btn-primary" data-hs-voucher-label="Aplikovat filtr">Aplikovat filtr</button>    
                                                                        </div>
                                                                    
                                                                        
                                                                    </div>
                                                      </div>

                                                </form>
                                                                  
                                          </div>
                                         </div>
                                        </div>

  <?php
    // }
  ?>
                 











                                     




                 <div class="row">
                    <div class="col-md-12">
                            
                        <div class="panel panel-default hs-voucher-panel" >
                            <div class="panel-heading">
                                <h3 class="panel-title" data-hs-voucher-label="Přehled Voucherů"><font face="tahoma"><b>Přehled Voucherů</b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">                                
                                 <table id="TabulkaVouchery" class="table table-striped table-bordered" cellspacing="0" width="99%" >
                                    <thead>
                                        <tr>
                                            
                                            <td align="center" data-hs-voucher-header="Akce"><b>Akce</b></td>
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
                                                
                                                

                                                


                                                 $sqldotaz_voucher= "SELECT * FROM `vouchers` WHERE $VoucherNeplatnyAnd `vou_stav_skupinaID` = ".$skupina_id." $FiltrProdejce $sqlBetweenVouchery order by 1 DESC";
                                                 //echo $sqldotaz_voucher;
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
                                                      $UpraveneCerpani = str_replace("-", "v", $UpraveneCerpani);
                                                    
                                                    }

                                                    /*if($UpraveneCerpani=="0000-00-00 00:00:00"){
                                                      $UpraveneCerpani = "";
                                                    }else{
                                                      
                                                      
                                                      $UpraveneCerpani = VlozNedelitelneMezery(date("d.m.Y H:i", strtotime($UpraveneCerpani)));
                                                      $UpraveneCerpani = str_replace("-", "v", $UpraveneCerpani);
                                                    }*/


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
                                                     
                                                          //echo "<td><div align=\"right\" style=\"width: 70%;\">$pocitadlo_radku_voucher</div></td>";
                                                          echo "<td><div align=\"center\"  style=\"width: 100%;\"><a href=\"https://klient.hairsoft.cz/str/index.php?strana=VoucherHistorie&guid=".trim($option["vou_GUID_voucheru"])."\" title=\"Bližší informace\"><img   class=\"img-circle profile-image\" src=\"../img/icko.png\"></a></div></td>";
                                                          echo "<td><div align=\"left\" style=\"width: 90%;\">".$option["vou_kod"]."</div></td>";
                                                          echo "<td ".$StavStyle."><div align=\"left\" style=\"width: 90%;\">".VlozNedelitelneMezery($option["vou_stav"])."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">".VlozNedelitelneMezery(number_format($option["vou_hodnota_voucheru"], 2, ',', ' ')." ".$Mena_Klienta)."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 90%;\">".VlozNedelitelneMezery(date("d.m.Y  H:i", strtotime($option["vou_datum_vytvoreni"])))."</div></td>";
                                                          //echo "<td><div align=\"right\" style=\"width: 90%;\">".VlozNedelitelneMezery(date("d.m.Y v H:i", strtotime($option["vou_datum_vytvoreni"])))."</div></td>";
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
                              </div>

                                                <br>
                                                  <div class="rada">
                                                    <form action="https://klient.hairsoft.cz/str/index.php?strana=VoucherZnpeplatneny" method="POST" >
                                                      <button type="submit" class="btn btn-danger" data-hs-voucher-label="Zobrazit zneplatněné Vouchery"><i class="fa icon-trash"></i>Zobrazit zneplatněné Vouchery</button>
                                                    </form>
                                                  </div>  
                                                  

                                             

                                                  

                                                  <!--
                                                  <div class="rada">
                                                    <form  action="https://klient.hairsoft.cz/str/strana/ExportPDF.php?Sablona=Voucher" method="POST" >
                                                      <button type="submit" class="btn btn-info" data-hs-voucher-label="PDF Export"><i class="fa fa-file-pdf-o"></i>PDF Export</button>
                                                    </form>
                                                  </div>
                                                -->
                                                  


                            </div>

                        </div>
                      </div>
                  
                    <div class="col-md-4">
                            
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
                                <table id="VoucherSumar" class="table table-striped table-bordered" cellspacing="0" width="99%" >
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
                                        

                                                 /*
                                                 $sqldotaz_voucherDPH= "SELECT `vou_sazba_DPH` as 'Sazba', SUM(`vou_hodnota_voucheru`) as 'Celkem', SUM(`vou_hodnota_voucheru`) - SUM(`vou_hodnota_bezDPH`) as 'DPH' ,SUM(`vou_hodnota_bezDPH`) as 'Základ' FROM `vouchers` WHERE $VoucherNeplatnyAnd `vou_stav_skupinaID` = $skupina_id group by `vou_sazba_DPH`";
                                                  if (!$sqldotaz_voucherDPH) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                    $vysledek_voucherDPH=$mysqli->query("$sqldotaz_voucherDPH");                                       
                                                 */

                                                $sqldotaz_voucherDPH= "SELECT `vou_sazba_DPH` as 'Sazba', SUM(`vou_hodnota_voucheru`) - SUM(`vou_cerpana_castka`) as 'Celkem', (SUM(`vou_hodnota_voucheru`) - SUM(`vou_cerpana_castka`)) - (SUM(`vou_hodnota_bezDPH`) - SUM(`vou_cerpana_castkabezDPH`)) as 'DPH' ,SUM(`vou_hodnota_bezDPH`) - SUM(`vou_cerpana_castkabezDPH`) as 'Základ' FROM `vouchers` WHERE `vou_stav` <> 'Neplatný' and `vou_stav` <> 'Vyčerpaný' and `vou_stav_skupinaID` = $skupina_id $FiltrProdejce $sqlBetweenVouchery group by `vou_sazba_DPH`";
                                                
                                                  if (!$sqldotaz_voucherDPH) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                    $vysledek_voucherDPH=$mysqli->query("$sqldotaz_voucherDPH");                                       


                                                  
                                                  $pocitadlo_radku_voucherDPH = 0;
                                                  $PocitadloSUMvoucherDPHu = 0;
                                                  
                                                  
                                                  while ($option=MySQLi_Fetch_Array($vysledek_voucherDPH)):   
                                                     
                                                     
                                                    $pocitadlo_radku_voucherDPH = $pocitadlo_radku_voucherDPH +1;   
                                                     
                                                     echo "<tr>";
                                                     
                                                          echo "<td><div align=\"right\" style=\"width: 90%;\">".$option["Sazba"]."%</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 90%;\">".number_format($option["Celkem"], 2, ',', ' ')." ".$Mena_Klienta."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 90%;\">".number_format($option["DPH"], 2, ',', ' ')." ".$Mena_Klienta."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 90%;\">".number_format($option["Základ"], 2, ',', ' ')." ".$Mena_Klienta."</div></td>";
                                                         
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
                      
                      
                        
                        
                    
                 
                    <div class="col-md-4">
                            
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
                                <table id="VoucherSumarDeposit" class="table table-striped table-bordered" cellspacing="0" width="99%" >
                                    <thead>
                                        <tr>
                                            <td align="center" data-hs-voucher-header="Přehled"><b>Přehled</b></td>
                                            <td align="center" data-hs-voucher-header="Celkem"><b>Celkem</b></td>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        
                                                     
                                                     echo "<tr>";
                                                          echo "<td><div align=\"left\" style=\"width: 90%;\">Zůstatek v &apos;&apos;Depozitu&apos;&apos;</div></td>";
                                                          echo "<td><div align=\"center\" style=\"width: 100%;\">".number_format($ZustatekSUMVoucheru, 2, ',', ' ')." ".$Mena_Klienta."</div></td>";
                                                     echo "</tr>";
                                                     echo "<tr>";
                                                          echo "<td><div align=\"left\" style=\"width: 80%;\">Cenin v oběhu</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 60%;\">".$VystavenoVoucheru_vobehu."</div></td>";
                                                     echo "</tr>";

                                                  


                                        ?>
                                                                         
                                        
                                    </tbody>
                                </table>
                              

                                                 


                              </div>
                            </div>
                            
                        </div>
                      </div>














                        <div class="col-lg-4">
                         <div class="panel panel-default ">
                            <div class="panel-heading">
                                <h3 class="panel-title" data-hs-voucher-label="Graf čerpání"><font face="Tahoma"><b>Graf čerpání</b></font></h3>
                                <div class="actions pull-right">
                                
                                    
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body ">
                               <div class="row">
                                 
                                 
                                 <div class="col-lg-5" >

                                           <table border="0">
                                               
                                               <?php
                                                 $barva1 = random_color();
                                                 $barva2 = random_color();
                                                 $barva3 = random_color();
                                                 
                                                 
                                                 $BarvyTypy = "'#$barva3','#$barva2','#$barva1'";
                                                 
                                               
                                                    
                                                    
                                                 $TypySUM = $ZustatekSUMVoucheru.",".$data_suma_voucher_vycerpany_castecneSUM.",".$data_suma_voucher_vycerpanySUM;
                                                 
                                                 //number_format($ZustatekSUMVoucheru, 2, ',', ' ')." ".$Mena_Klienta
                                                 
                                               ?>
                                               
                                               
                                               
                                               
                                               
                                                        
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $barva1; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Vyčerpáno</td>
                                                          <td align="right" width="100"><b>&nbsp;<?php echo number_format($data_suma_voucher_vycerpanySUM, 2, ',', ' ');?></b></td>           
                                                        </tr> 
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $barva2; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Částečně </td>
                                                          <td align="right"><b>&nbsp;<?php echo number_format($data_suma_voucher_vycerpany_castecneSUM, 2, ',', ' '); ?></b></td>           
                                                        </tr>                                                         <tr>
                                                        <tr>
                                                          <td><div style="width: 15px; height: 15px; background: #<?php echo $barva3; ?>; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;"></div></td>
                                                          <td>&nbsp;Nečerpáno </td>
                                                          <td align="right"><b>&nbsp;<?php echo  number_format($ZustatekSUMVoucheru, 2, ',', ' '); ?></b></td>           
                                                        </tr>
                                                        
                                                       
                                               
                                                                                              
                                               
                                           </table>
                                        
                                  </div>
                                <div>
                                  <div class="col-lg-7" align="center">
                                    <!-- <canvas id="doughnut1" height="250"></canvas> -->
                                        <?php
                                                                                                          
                                           if ($VystavenoVoucheru==0) {
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



                    


<?php
    if ( $sw_id!=0) {

  ?>                      
                        
                        
                                 <div class="row">
                                      <div class="col-md-12 col-lg-12">
                                        <div class="panel panel-default hs-voucher-panel">
                                          <div class="panel-body ng-binding">
                                            
                                                          <div class="rada">
                                                            <form action="https://klient.hairsoft.cz/str/index.php?strana=Voucher" method="POST" >
                                                              
                                                              <input type="hidden" class="form-control" name="SmazatVsechnyVouchery" value="1">
                                                              <input type="hidden" class="form-control" name="SmazatVsechnyVoucherySkupina" value="<?php echo $skupina_id; ?>">

                                                              <button type="submit" onClick="if(!confirm('Opravdu chcete SMAZAT všechny vouchery? Tato akce je nevratná!')){return false;}" class="btn btn-danger" data-hs-voucher-label="Smazat všechny vouchery"><i class="fa icon-trash"></i>Smazat všechny vouchery</button>
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




 <script language="JavaScript">
        
        window.onload = function() {

            if (window.hsPrepareVoucherChart) { window.hsPrepareVoucherChart(); return; }
            var canvas = document.getElementById("chart-area1");
            if (!canvas) return;
            var ctx2 = canvas.getContext("2d");
            window.myDoughnut = new Chart(ctx2, config2);
            
        };
          
       
        
        
        var config2 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                                      data: [ <?php echo $TypySUM; ?> ],
                                      backgroundColor: [<?php echo $BarvyTypy; ?>],
                                      label: 'Dataset 1'
                                  }],
                          labels: ['Nečerpáno','Částečně','Vyčerpáno']
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
        
    </script>
