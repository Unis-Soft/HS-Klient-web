    
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
  require HS_CLIENT_UI_ROOT . '/fce/GeneratorBarev.php';
  //require_once '../../cfg/nastaveni.php';


  
/*
Filtr - prehled hodnoceni rok mesic
*/


        if (!isset($_GET['rok_zmena']) || is_array($_GET['rok_zmena'])){$_GET['rok_zmena']='';}
        $rok_zmena  =  htmlspecialchars($_GET['rok_zmena'], ENT_COMPAT);
        
        if (!isset($_GET['mesic']) || is_array($_GET['mesic'])){$_GET['mesic']='';}
        $mesic  =  htmlspecialchars($_GET['mesic'], ENT_COMPAT);

        $aMesice = array('Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec');
  
        if ($mesic=="1") {
          if ($_SESSION["SQL_Mesic"]!="12") {
            $_SESSION["SQL_Mesic"] = $_SESSION["SQL_Mesic"] +1;
          }else {
            $_SESSION["SQL_Mesic"] = 1;
          }
        }elseif($mesic=="0") {
          if ($_SESSION["SQL_Mesic"]!="1") {
            $_SESSION["SQL_Mesic"] = $_SESSION["SQL_Mesic"] -1;
          }else {
            $_SESSION["SQL_Mesic"] = 12;

          }
        }else {
          if ($_SESSION["SQL_Mesic"]=="") {
            $aktualniMesic = date("n");
            $_SESSION["SQL_Mesic"] = $aktualniMesic;  
          }
        }

          $aktualniMesic =$_SESSION["SQL_Mesic"]; 
        
      
      if ($rok_zmena=="1") {
          $_SESSION["SQL_ROK"] = $_SESSION["SQL_ROK"]+1;
      }elseif ($rok_zmena=="0") {
          $_SESSION["SQL_ROK"] = $_SESSION["SQL_ROK"]-1;
      }
        

        if ($_SESSION["SQL_ROK"]=="") {
          $SQL_ROK = date("Y");
          $_SESSION["SQL_ROK"] = $SQL_ROK;
        }else{
          $SQL_ROK = $_SESSION["SQL_ROK"];
        }  

        
        

        




        




   
  
  $jmenoStranky = "Statistika hodnocení";
  $jmenoStrankyPopis = "Přehled Vašich statistik a hodnocení od zákazníků";
  
  if ($_SESSION["pobocka_jmeno"]=="") {
    $pobocka_jmeno =  "Nevybrána" ;
  }else {
    $pobocka_jmeno =  $_SESSION["pobocka_jmeno"] ;  
  }

        //AKCE
        if (!isset($_POST['datum']) || is_array($_POST['datum'])){$_POST['datum']='';}
        $datum  =  htmlspecialchars($_POST['datum'], ENT_COMPAT);

      
//Filtr
        if (!isset($_POST['DatumVoucheryFiltr']) || is_array($_POST['DatumVoucheryFiltr'])){$_POST['DatumVoucheryFiltr']='';}
        $DatumVoucheryFiltr  =  htmlspecialchars($_POST['DatumVoucheryFiltr'], ENT_COMPAT);
        
        if (!isset($_POST['VoucheryDatumOd']) || is_array($_POST['VoucheryDatumOd'])){$_POST['VoucheryDatumOd']='';}
        $VoucheryDatumOd  =  htmlspecialchars($_POST['VoucheryDatumOd'], ENT_COMPAT);
        
        if (!isset($_POST['VoucheryDatumDo']) || is_array($_POST['VoucheryDatumDo'])){$_POST['VoucheryDatumDo']='';}
        $VoucheryDatumDo  =  htmlspecialchars($_POST['VoucheryDatumDo'], ENT_COMPAT);

      
  
/* FILTRY
        if ($_SESSION["VoucheryDatumOd"]!="" and $_SESSION["VoucheryDatumDo"]!="") {
               
               $originalDate = $_SESSION["VoucheryDatumOd"];
               $newDate1 = date("Y-m-d 00:00:00", strtotime($originalDate));

               $originalDate2 = $_SESSION["VoucheryDatumDo"];
               $newDate2 = date("Y-m-d 23:59:59", strtotime($originalDate2));
                 
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
              


          
        }else{
          $sqlBetweenVouchery = "";
        }
*/




        
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
    $sw_id = 4181; // PODSTRNENEJ ZDENDA !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
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
  
   
  
  
    /*
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
    */
  
     



               $originalDate = $datum;
               $newDate = date("d.m.Y", strtotime($originalDate));

               $datum_vouchery1 = date("01.m.Y");
               
               $posledniden = date("t");
               $datum_vouchery2 = date($posledniden.".m.Y");
               
          


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
  <section id="main-content" class="hs-feedback-page hs-feedback-stats">                
  
  <?php
                                



                              
     
                                //Kolik hodnoceni celkem
                                $select_na_hodnoceni_celkem= "SELECT count(*) as celkemOdeslano FROM `HodnoceniHlavni` WHERE `sw_id` = ".$sw_id." and `HodnoceniStav` in (0,1)";
                                if (!$select_na_hodnoceni_celkem) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_hodnoceni_celkem=$mysqli->query("$select_na_hodnoceni_celkem");
                                $data_hodnoceni_celkem=MySQLi_Fetch_Array($vysledek_na_hodnoceni_celkem);
                                //ho $select_na_hodnoceni_celkem;

                                //Kolik odpovedelo
                                $select_na_hodnoceni_odpovedelo= "SELECT count(*) as celkemOdpovedelo FROM `HodnoceniHlavni` WHERE `sw_id` = ".$sw_id." and `HodnoceniStav` in (1)";
                                if (!$select_na_hodnoceni_odpovedelo) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_hodnoceni_odpovedelo=$mysqli->query("$select_na_hodnoceni_odpovedelo");
                                $data_hodnoceni_odpovedelo=MySQLi_Fetch_Array($vysledek_na_hodnoceni_odpovedelo);


                                //odhlaseno z hodnoceni
                                $select_na_sumu_odhlaseno= "SELECT count(*) as NechceEmail FROM `HodnoceniNoEmail` WHERE `NoEmail` in (sELECT email FROM `HodnoceniHlavni` WHERE `sw_id` = ".$sw_id.")";
                                if (!$select_na_sumu_odhlaseno) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_sumu_odhlaseno=$mysqli->query("$select_na_sumu_odhlaseno");
                                $data_suma_odhlaseno=MySQLi_Fetch_Array($vysledek_na_sumu_odhlaseno);
                                //echo $select_na_sumu_voucher;



                                //odhlaseno z hodnoceni
                                $select_na_sumu_neodeslanych= "SELECT count(*) as Neodeslanych FROM `HodnoceniHlavni` WHERE `sw_id` = ".$sw_id." and `HodnoceniStav` in (-1)";
                                if (!$select_na_sumu_neodeslanych) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_sumu_neodeslanych=$mysqli->query("$select_na_sumu_neodeslanych");
                                $data_suma_neodeslanych=MySQLi_Fetch_Array($vysledek_na_sumu_neodeslanych);
                                



                                $ProcenentemOdpovedelo = (float)$data_hodnoceni_celkem["celkemOdeslano"] > 0 ? round((100 * $data_hodnoceni_odpovedelo["celkemOdpovedelo"]) / $data_hodnoceni_celkem["celkemOdeslano"],2) : 0;  
                                //Proti NAN
                                if (is_nan($ProcenentemOdpovedelo)) {
                                   $ProcenentemOdpovedelo = 0;
                                } 
                              
                                /* Vypocty hodnot 6 grafu */  

                                $HodnoceniObsluha = 0;  
                                $HodnoceniSluzba = 0;  
                                $HodnoceniProstredi = 0;  
                                $HodnoceniCena = 0;  
                                $HodnoceniAtmosfera = 0;  
                                $HodnoceniNespecifikovano = 0;  

                                

                                $HodnoceniObsluhaCNT = 0;  
                                $HodnoceniSluzbaCNT = 0;  
                                $HodnoceniProstrediCNT = 0;  
                                $HodnoceniCenaCNT = 0;  
                                $HodnoceniAtmosferaCNT = 0;  
                                $HodnoceniNespecifikovanoCNT = 0;  

                                


                                /*Hlavni smycka*/ 
                                 $SelectOtazky = "SELECT * FROM `HodnoceniHlavni` WHERE `HodnoceniStav` = 1 and `sw_id` = ".$sw_id;
                                 //echo $SelectOtazky;
                                                          
                                   if (!$SelectOtazky) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}

                                    $vysledek_SelectOtazky=$mysqli->query("$SelectOtazky");
                                    $pocet_radku_SelectOtazky = $vysledek_SelectOtazky->num_rows;

                                    $SelectOdpovediCNT=0;



                                          
                                                








                                              
                                    while ($SelectOtazky_seznam=MySQLi_Fetch_Array($vysledek_SelectOtazky)):
                                           $SelectOdpovediCNT = $SelectOdpovediCNT + 1;

                                                $HodnoceniObsluhaDen = 0;  
                                                $HodnoceniSluzbaDen = 0;  
                                                $HodnoceniProstrediDen = 0;  
                                                $HodnoceniCenaDen = 0;  
                                                $HodnoceniAtmosferaDen = 0;  
                                                $HodnoceniNespecifikovanoDen = 0;  
                                                
                                                $HodnoceniObsluhaCNTDen = 0;  
                                                $HodnoceniSluzbaCNTDen = 0;  
                                                $HodnoceniProstrediCNTDen = 0;  
                                                $HodnoceniCenaCNTDen = 0;  
                                                $HodnoceniAtmosferaCNTDen = 0;  
                                                $HodnoceniNespecifikovanoCNTDen = 0;  

                                           


                                  /*Obsluha*/
                                           if  ($SelectOtazky_seznam["Otazka_01_typ_hodnoceni"]=="Obsluha" and $SelectOtazky_seznam["Otazka_01_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniObsluhaDen = $HodnoceniObsluhaDen + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniObsluhaCNTDen = $HodnoceniObsluhaCNTDen +1;

                                              $HodnoceniObsluha = $HodnoceniObsluha + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniObsluhaCNT = $HodnoceniObsluhaCNT +1;






                                           }

                                           if  ($SelectOtazky_seznam["Otazka_02_typ_hodnoceni"]=="Obsluha" and $SelectOtazky_seznam["Otazka_02_typ_otazky"]=="Hvězdičky"){
                                              
                                              $HodnoceniObsluhaDen = $HodnoceniObsluhaDen + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniObsluhaCNTDen = $HodnoceniObsluhaCNTDen +1;

                                              $HodnoceniObsluha = $HodnoceniObsluha + $SelectOtazky_seznam["Otazka_02_odpoved"];
                                              $HodnoceniObsluhaCNT = $HodnoceniObsluhaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_03_typ_hodnoceni"]=="Obsluha" and $SelectOtazky_seznam["Otazka_03_typ_otazky"]=="Hvězdičky"){
                                              
                                              $HodnoceniObsluhaDen = $HodnoceniObsluhaDen + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniObsluhaCNTDen = $HodnoceniObsluhaCNTDen +1;

                                              $HodnoceniObsluha = $HodnoceniObsluha + $SelectOtazky_seznam["Otazka_03_odpoved"];
                                              $HodnoceniObsluhaCNT = $HodnoceniObsluhaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_04_typ_hodnoceni"]=="Obsluha" and $SelectOtazky_seznam["Otazka_04_typ_otazky"]=="Hvězdičky"){
                                              
                                              $HodnoceniObsluhaDen = $HodnoceniObsluhaDen + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniObsluhaCNTDen = $HodnoceniObsluhaCNTDen +1;

                                              $HodnoceniObsluha = $HodnoceniObsluha + $SelectOtazky_seznam["Otazka_04_odpoved"];
                                              $HodnoceniObsluhaCNT = $HodnoceniObsluhaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_05_typ_hodnoceni"]=="Obsluha" and $SelectOtazky_seznam["Otazka_05_typ_otazky"]=="Hvězdičky"){
                                              
                                              $HodnoceniObsluhaDen = $HodnoceniObsluhaDen + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniObsluhaCNTDen = $HodnoceniObsluhaCNTDen +1;

                                              $HodnoceniObsluha = $HodnoceniObsluha + $SelectOtazky_seznam["Otazka_05_odpoved"];
                                              $HodnoceniObsluhaCNT = $HodnoceniObsluhaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_06_typ_hodnoceni"]=="Obsluha" and $SelectOtazky_seznam["Otazka_06_typ_otazky"]=="Hvězdičky"){
                                              
                                              $HodnoceniObsluhaDen = $HodnoceniObsluhaDen + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniObsluhaCNTDen = $HodnoceniObsluhaCNTDen +1;

                                              $HodnoceniObsluha = $HodnoceniObsluha + $SelectOtazky_seznam["Otazka_06_odpoved"];
                                              $HodnoceniObsluhaCNT = $HodnoceniObsluhaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_07_typ_hodnoceni"]=="Obsluha" and $SelectOtazky_seznam["Otazka_07_typ_otazky"]=="Hvězdičky"){
                                              
                                              $HodnoceniObsluhaDen = $HodnoceniObsluhaDen + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniObsluhaCNTDen = $HodnoceniObsluhaCNTDen +1;

                                              $HodnoceniObsluha = $HodnoceniObsluha + $SelectOtazky_seznam["Otazka_07_odpoved"];
                                              $HodnoceniObsluhaCNT = $HodnoceniObsluhaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_08_typ_hodnoceni"]=="Obsluha" and $SelectOtazky_seznam["Otazka_08_typ_otazky"]=="Hvězdičky"){
                                              
                                              $HodnoceniObsluhaDen = $HodnoceniObsluhaDen + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniObsluhaCNTDen = $HodnoceniObsluhaCNTDen +1;

                                              $HodnoceniObsluha = $HodnoceniObsluha + $SelectOtazky_seznam["Otazka_08_odpoved"];
                                              $HodnoceniObsluhaCNT = $HodnoceniObsluhaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_09_typ_hodnoceni"]=="Obsluha" and $SelectOtazky_seznam["Otazka_09_typ_otazky"]=="Hvězdičky"){
                                             
                                              $HodnoceniObsluhaDen = $HodnoceniObsluhaDen + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniObsluhaCNTDen = $HodnoceniObsluhaCNTDen +1;

                                              $HodnoceniObsluha = $HodnoceniObsluha + $SelectOtazky_seznam["Otazka_09_odpoved"];
                                              $HodnoceniObsluhaCNT = $HodnoceniObsluhaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_10_typ_hodnoceni"]=="Obsluha" and $SelectOtazky_seznam["Otazka_10_typ_otazky"]=="Hvězdičky"){
                                              
                                              $HodnoceniObsluhaDen = $HodnoceniObsluhaDen + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniObsluhaCNTDen = $HodnoceniObsluhaCNTDen +1;

                                              $HodnoceniObsluha = $HodnoceniObsluha + $SelectOtazky_seznam["Otazka_10_odpoved"];
                                              $HodnoceniObsluhaCNT = $HodnoceniObsluhaCNT +1;
                                           }

                                           /*Vyhodnoceni SUMY*/




                                  /*Sluzba*/
                                           if  ($SelectOtazky_seznam["Otazka_01_typ_hodnoceni"]=="Služba" and $SelectOtazky_seznam["Otazka_01_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniSluzba = $HodnoceniSluzba + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniSluzbaCNT = $HodnoceniSluzbaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_02_typ_hodnoceni"]=="Služba" and $SelectOtazky_seznam["Otazka_02_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniSluzba = $HodnoceniSluzba + $SelectOtazky_seznam["Otazka_02_odpoved"];
                                              $HodnoceniSluzbaCNT = $HodnoceniSluzbaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_03_typ_hodnoceni"]=="Služba" and $SelectOtazky_seznam["Otazka_03_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniSluzba = $HodnoceniSluzba + $SelectOtazky_seznam["Otazka_03_odpoved"];
                                              $HodnoceniSluzbaCNT = $HodnoceniSluzbaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_04_typ_hodnoceni"]=="Služba" and $SelectOtazky_seznam["Otazka_04_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniSluzba = $HodnoceniSluzba + $SelectOtazky_seznam["Otazka_04_odpoved"];
                                              $HodnoceniSluzbaCNT = $HodnoceniSluzbaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_05_typ_hodnoceni"]=="Služba" and $SelectOtazky_seznam["Otazka_05_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniSluzba = $HodnoceniSluzba + $SelectOtazky_seznam["Otazka_05_odpoved"];
                                              $HodnoceniSluzbaCNT = $HodnoceniSluzbaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_06_typ_hodnoceni"]=="Služba" and $SelectOtazky_seznam["Otazka_06_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniSluzba = $HodnoceniSluzba + $SelectOtazky_seznam["Otazka_06_odpoved"];
                                              $HodnoceniSluzbaCNT = $HodnoceniSluzbaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_07_typ_hodnoceni"]=="Služba" and $SelectOtazky_seznam["Otazka_07_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniSluzba = $HodnoceniSluzba + $SelectOtazky_seznam["Otazka_07_odpoved"];
                                              $HodnoceniSluzbaCNT = $HodnoceniSluzbaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_08_typ_hodnoceni"]=="Služba" and $SelectOtazky_seznam["Otazka_08_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniSluzba = $HodnoceniSluzba + $SelectOtazky_seznam["Otazka_08_odpoved"];
                                              $HodnoceniSluzbaCNT = $HodnoceniSluzbaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_09_typ_hodnoceni"]=="Služba" and $SelectOtazky_seznam["Otazka_09_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniSluzba = $HodnoceniSluzba + $SelectOtazky_seznam["Otazka_09_odpoved"];
                                              $HodnoceniSluzbaCNT = $HodnoceniSluzbaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_10_typ_hodnoceni"]=="Služba" and $SelectOtazky_seznam["Otazka_10_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniSluzba = $HodnoceniSluzba + $SelectOtazky_seznam["Otazka_10_odpoved"];
                                              $HodnoceniSluzbaCNT = $HodnoceniSluzbaCNT +1;
                                           }

                                  /*Prostredi*/
                                           if  ($SelectOtazky_seznam["Otazka_01_typ_hodnoceni"]=="Prostředí" and $SelectOtazky_seznam["Otazka_01_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniProstredi = $HodnoceniProstredi + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniProstrediCNT = $HodnoceniProstrediCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_02_typ_hodnoceni"]=="Prostředí" and $SelectOtazky_seznam["Otazka_02_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniProstredi = $HodnoceniProstredi + $SelectOtazky_seznam["Otazka_02_odpoved"];
                                              $HodnoceniProstrediCNT = $HodnoceniProstrediCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_03_typ_hodnoceni"]=="Prostředí" and $SelectOtazky_seznam["Otazka_03_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniProstredi = $HodnoceniProstredi + $SelectOtazky_seznam["Otazka_03_odpoved"];
                                              $HodnoceniProstrediCNT = $HodnoceniProstrediCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_04_typ_hodnoceni"]=="Prostředí" and $SelectOtazky_seznam["Otazka_04_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniProstredi = $HodnoceniProstredi + $SelectOtazky_seznam["Otazka_04_odpoved"];
                                              $HodnoceniProstrediCNT = $HodnoceniProstrediCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_05_typ_hodnoceni"]=="Prostředí" and $SelectOtazky_seznam["Otazka_05_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniProstredi = $HodnoceniProstredi + $SelectOtazky_seznam["Otazka_05_odpoved"];
                                              $HodnoceniProstrediCNT = $HodnoceniProstrediCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_06_typ_hodnoceni"]=="Prostředí" and $SelectOtazky_seznam["Otazka_06_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniProstredi = $HodnoceniProstredi + $SelectOtazky_seznam["Otazka_06_odpoved"];
                                              $HodnoceniProstrediCNT = $HodnoceniProstrediCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_07_typ_hodnoceni"]=="Prostředí" and $SelectOtazky_seznam["Otazka_07_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniProstredi = $HodnoceniProstredi + $SelectOtazky_seznam["Otazka_07_odpoved"];
                                              $HodnoceniProstrediCNT = $HodnoceniProstrediCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_08_typ_hodnoceni"]=="Prostředí" and $SelectOtazky_seznam["Otazka_08_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniProstredi = $HodnoceniProstredi + $SelectOtazky_seznam["Otazka_08_odpoved"];
                                              $HodnoceniProstrediCNT = $HodnoceniProstrediCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_09_typ_hodnoceni"]=="Prostředí" and $SelectOtazky_seznam["Otazka_09_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniProstredi = $HodnoceniProstredi + $SelectOtazky_seznam["Otazka_09_odpoved"];
                                              $HodnoceniProstrediCNT = $HodnoceniProstrediCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_10_typ_hodnoceni"]=="Prostředí" and $SelectOtazky_seznam["Otazka_10_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniProstredi = $HodnoceniProstredi + $SelectOtazky_seznam["Otazka_10_odpoved"];
                                              $HodnoceniProstrediCNT = $HodnoceniProstrediCNT +1;
                                           }

                                /*Cena*/
                                           if  ($SelectOtazky_seznam["Otazka_01_typ_hodnoceni"]=="Cena" and $SelectOtazky_seznam["Otazka_01_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniCena = $HodnoceniCena + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniCenaCNT = $HodnoceniCenaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_02_typ_hodnoceni"]=="Cena" and $SelectOtazky_seznam["Otazka_02_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniCena = $HodnoceniCena + $SelectOtazky_seznam["Otazka_02_odpoved"];
                                              $HodnoceniCenaCNT = $HodnoceniCenaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_03_typ_hodnoceni"]=="Cena" and $SelectOtazky_seznam["Otazka_03_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniCena = $HodnoceniCena + $SelectOtazky_seznam["Otazka_03_odpoved"];
                                              $HodnoceniCenaCNT = $HodnoceniCenaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_04_typ_hodnoceni"]=="Cena" and $SelectOtazky_seznam["Otazka_04_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniCena = $HodnoceniCena + $SelectOtazky_seznam["Otazka_04_odpoved"];
                                              $HodnoceniCenaCNT = $HodnoceniCenaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_05_typ_hodnoceni"]=="Cena" and $SelectOtazky_seznam["Otazka_05_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniCena = $HodnoceniCena + $SelectOtazky_seznam["Otazka_05_odpoved"];
                                              $HodnoceniCenaCNT = $HodnoceniCenaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_06_typ_hodnoceni"]=="Cena" and $SelectOtazky_seznam["Otazka_06_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniCena = $HodnoceniCena + $SelectOtazky_seznam["Otazka_06_odpoved"];
                                              $HodnoceniCenaCNT = $HodnoceniCenaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_07_typ_hodnoceni"]=="Cena" and $SelectOtazky_seznam["Otazka_07_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniCena = $HodnoceniCena + $SelectOtazky_seznam["Otazka_07_odpoved"];
                                              $HodnoceniCenaCNT = $HodnoceniCenaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_08_typ_hodnoceni"]=="Cena" and $SelectOtazky_seznam["Otazka_08_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniCena = $HodnoceniCena + $SelectOtazky_seznam["Otazka_08_odpoved"];
                                              $HodnoceniCenaCNT = $HodnoceniCenaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_09_typ_hodnoceni"]=="Cena" and $SelectOtazky_seznam["Otazka_09_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniCena = $HodnoceniCena + $SelectOtazky_seznam["Otazka_09_odpoved"];
                                              $HodnoceniCenaCNT = $HodnoceniCenaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_10_typ_hodnoceni"]=="Cena" and $SelectOtazky_seznam["Otazka_10_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniCena = $HodnoceniCena + $SelectOtazky_seznam["Otazka_10_odpoved"];
                                              $HodnoceniCenaCNT = $HodnoceniCenaCNT +1;
                                           }

                                   /*Atmosfera*/
                                           if  ($SelectOtazky_seznam["Otazka_01_typ_hodnoceni"]=="Atmosféra" and $SelectOtazky_seznam["Otazka_01_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniAtmosfera = $HodnoceniAtmosfera + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniAtmosferaCNT = $HodnoceniAtmosferaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_02_typ_hodnoceni"]=="Atmosféra" and $SelectOtazky_seznam["Otazka_02_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniAtmosfera = $HodnoceniAtmosfera + $SelectOtazky_seznam["Otazka_02_odpoved"];
                                              $HodnoceniAtmosferaCNT = $HodnoceniAtmosferaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_03_typ_hodnoceni"]=="Atmosféra" and $SelectOtazky_seznam["Otazka_03_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniAtmosfera = $HodnoceniAtmosfera + $SelectOtazky_seznam["Otazka_03_odpoved"];
                                              $HodnoceniAtmosferaCNT = $HodnoceniAtmosferaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_04_typ_hodnoceni"]=="Atmosféra" and $SelectOtazky_seznam["Otazka_04_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniAtmosfera = $HodnoceniAtmosfera + $SelectOtazky_seznam["Otazka_04_odpoved"];
                                              $HodnoceniAtmosferaCNT = $HodnoceniAtmosferaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_05_typ_hodnoceni"]=="Atmosféra" and $SelectOtazky_seznam["Otazka_05_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniAtmosfera = $HodnoceniAtmosfera + $SelectOtazky_seznam["Otazka_05_odpoved"];
                                              $HodnoceniAtmosferaCNT = $HodnoceniAtmosferaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_06_typ_hodnoceni"]=="Atmosféra" and $SelectOtazky_seznam["Otazka_06_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniAtmosfera = $HodnoceniAtmosfera + $SelectOtazky_seznam["Otazka_06_odpoved"];
                                              $HodnoceniAtmosferaCNT = $HodnoceniAtmosferaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_07_typ_hodnoceni"]=="Atmosféra" and $SelectOtazky_seznam["Otazka_07_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniAtmosfera = $HodnoceniAtmosfera + $SelectOtazky_seznam["Otazka_07_odpoved"];
                                              $HodnoceniAtmosferaCNT = $HodnoceniAtmosferaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_08_typ_hodnoceni"]=="Atmosféra" and $SelectOtazky_seznam["Otazka_08_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniAtmosfera = $HodnoceniAtmosfera + $SelectOtazky_seznam["Otazka_08_odpoved"];
                                              $HodnoceniAtmosferaCNT = $HodnoceniAtmosferaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_09_typ_hodnoceni"]=="Atmosféra" and $SelectOtazky_seznam["Otazka_09_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniAtmosfera = $HodnoceniAtmosfera + $SelectOtazky_seznam["Otazka_09_odpoved"];
                                              $HodnoceniAtmosferaCNT = $HodnoceniAtmosferaCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_10_typ_hodnoceni"]=="Atmosféra" and $SelectOtazky_seznam["Otazka_10_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniAtmosfera = $HodnoceniAtmosfera + $SelectOtazky_seznam["Otazka_10_odpoved"];
                                              $HodnoceniAtmosferaCNT = $HodnoceniAtmosferaCNT +1;
                                           }

                                    /*Nespecifikováno*/
                                           if  ($SelectOtazky_seznam["Otazka_01_typ_hodnoceni"]=="Nespecifikováno" and $SelectOtazky_seznam["Otazka_01_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniNespecifikovano = $HodnoceniNespecifikovano + $SelectOtazky_seznam["Otazka_01_odpoved"];
                                              $HodnoceniNespecifikovanoCNT = $HodnoceniNespecifikovanoCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_02_typ_hodnoceni"]=="Nespecifikováno" and $SelectOtazky_seznam["Otazka_02_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniNespecifikovano = $HodnoceniNespecifikovano + $SelectOtazky_seznam["Otazka_02_odpoved"];
                                              $HodnoceniNespecifikovanoCNT = $HodnoceniNespecifikovanoCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_03_typ_hodnoceni"]=="Nespecifikováno" and $SelectOtazky_seznam["Otazka_03_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniNespecifikovano = $HodnoceniNespecifikovano + $SelectOtazky_seznam["Otazka_03_odpoved"];
                                              $HodnoceniNespecifikovanoCNT = $HodnoceniNespecifikovanoCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_04_typ_hodnoceni"]=="Nespecifikováno" and $SelectOtazky_seznam["Otazka_04_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniNespecifikovano = $HodnoceniNespecifikovano + $SelectOtazky_seznam["Otazka_04_odpoved"];
                                              $HodnoceniNespecifikovanoCNT = $HodnoceniNespecifikovanoCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_05_typ_hodnoceni"]=="Nespecifikováno" and $SelectOtazky_seznam["Otazka_05_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniNespecifikovano = $HodnoceniNespecifikovano + $SelectOtazky_seznam["Otazka_05_odpoved"];
                                              $HodnoceniNespecifikovanoCNT = $HodnoceniNespecifikovanoCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_06_typ_hodnoceni"]=="Nespecifikováno" and $SelectOtazky_seznam["Otazka_06_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniNespecifikovano = $HodnoceniNespecifikovano + $SelectOtazky_seznam["Otazka_06_odpoved"];
                                              $HodnoceniNespecifikovanoCNT = $HodnoceniNespecifikovanoCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_07_typ_hodnoceni"]=="Nespecifikováno" and $SelectOtazky_seznam["Otazka_07_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniNespecifikovano = $HodnoceniNespecifikovano + $SelectOtazky_seznam["Otazka_07_odpoved"];
                                              $HodnoceniNespecifikovanoCNT = $HodnoceniNespecifikovanoCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_08_typ_hodnoceni"]=="Nespecifikováno" and $SelectOtazky_seznam["Otazka_08_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniNespecifikovano = $HodnoceniNespecifikovano + $SelectOtazky_seznam["Otazka_08_odpoved"];
                                              $HodnoceniNespecifikovanoCNT = $HodnoceniNespecifikovanoCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_09_typ_hodnoceni"]=="Nespecifikováno" and $SelectOtazky_seznam["Otazka_09_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniNespecifikovano = $HodnoceniNespecifikovano + $SelectOtazky_seznam["Otazka_09_odpoved"];
                                              $HodnoceniNespecifikovanoCNT = $HodnoceniNespecifikovanoCNT +1;
                                           }

                                           if  ($SelectOtazky_seznam["Otazka_10_typ_hodnoceni"]=="Nespecifikováno" and $SelectOtazky_seznam["Otazka_10_typ_otazky"]=="Hvězdičky"){
                                              $HodnoceniNespecifikovano = $HodnoceniNespecifikovano + $SelectOtazky_seznam["Otazka_10_odpoved"];
                                              $HodnoceniNespecifikovanoCNT = $HodnoceniNespecifikovanoCNT +1;
                                           }





                                          /*VYSLEDKY DENNÍ*/
                                          $VysledekObsluhaDen = (float)$HodnoceniObsluhaCNTDen > 0 ? round($HodnoceniObsluhaDen / $HodnoceniObsluhaCNTDen,2) : 0;
                                          

                                            /*Napleni grafu obsluhy v tydnech*/
                                            /*zjistit prvni obsluhu*/
                                        /*

                            
                            
                                    
                            
                            otazka1(hodnoceni na tyden) podle order
                            podle data zjisti tyden to je array(x)

                            koukne jestli tam neco neni pokud ne prida pokud ano udela prumer








                            */

                                        //$PoleOtazkaObsluha1 

                                        /*zjisti tyden*/
                                        $date = new DateTime($SelectOtazky_seznam["order_date"]);
                                        $week = $date->format("W");
                                        


                                            
                                    endwhile;

                                      
                                      /*VYSLEDKY CELKOVE*/
                                      $VysledekObsluha = ((float)$SelectOdpovediCNT > 0 ? round($HodnoceniObsluha / $SelectOdpovediCNT,2) : 0);
                                      if (is_nan($VysledekObsluha)) {
                                        $VysledekObsluha = 0;
                                      }

                                      $VysledekSluzba = ((float)$SelectOdpovediCNT > 0 ? round($HodnoceniSluzba / $SelectOdpovediCNT,2) : 0);
                                      if (is_nan($VysledekSluzba)) {
                                        $VysledekSluzba = 0;
                                      }

                                      $VysledekProstredi = ((float)$SelectOdpovediCNT > 0 ? round($HodnoceniProstredi / $SelectOdpovediCNT,2) : 0);
                                      if (is_nan($VysledekProstredi)) {
                                        $VysledekProstredi = 0;
                                      }

                                      $VysledekCena = ((float)$SelectOdpovediCNT > 0 ? round($HodnoceniCena / $SelectOdpovediCNT,2) : 0);
                                      if (is_nan($VysledekCena)) {
                                        $VysledekCena = 0;
                                      }

                                      $VysledekAtmosfera = ((float)$SelectOdpovediCNT > 0 ? round($HodnoceniAtmosfera / $SelectOdpovediCNT,2) : 0);
                                      if (is_nan($VysledekAtmosfera)) {
                                        $VysledekAtmosfera = 0;
                                      }

                                      $VysledekNespecifikovano = ((float)$SelectOdpovediCNT > 0 ? round($HodnoceniNespecifikovano / $SelectOdpovediCNT,2) : 0);
                                      if (is_nan($VysledekNespecifikovano)) {
                                        $VysledekNespecifikovano = 0;
                                      }

                                      














?>
                              
                                          


                  
  
  
                <div class="row">
                            <div class="col-md-3">
                                <div class="panel panel-solid-success widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $data_hodnoceni_celkem["celkemOdeslano"] ; ?></font></span>
                                        <span class="title text-center">Hodnocení&nbsp;celkem</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $data_hodnoceni_odpovedelo["celkemOdpovedelo"]; ?></font></span>
                                        <span class="title text-center">Odpovědělo</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="panel panel-solid-danger widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $ProcenentemOdpovedelo."&nbsp;"."%"  ?></font></span>
                                        <span class="title text-center">Procentuálně&nbsp;</span>
                                    </div>
                                </div>
                            </div>
                            


                             <div class="col-md-2">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $data_suma_neodeslanych["Neodeslanych"] ; ?></font></span>              
                                        <span class="title text-center">Fronta&nbsp;neodeslaných</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="panel panel-solid-info widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo  $data_suma_odhlaseno["NechceEmail"] ?></font></span>              
                                        <span class="title text-center">Odhlášeno&nbsp;z&nbsp;hodnocení</span>
                                    </div>
                                </div>
                            </div>











                  </div>   




                <div class="row">
                                            <div class="col-md-12">
                                              <div class="panel panel-default" >
                                                <div class="panel-heading">
                                                    <h3 class="panel-title"><font face="tahoma"><b>Přehled hodnocení obsluh</b></font></h3>
                                                    <div class="actions pull-right">
                                                        <i class="fa fa-expand"></i>
                                                        <i class="fa fa-chevron-down"></i>
                                                        <i class="fa fa-times"></i>
                                                    </div>
                                                </div>
                                                <div class="panel-body hs-feedback-people">

                                        <?php       
                                              /*Naplnit hodnceni pro obluhu group by `k_poduzivatele_obsluha_jmeno`  order by `user` asc "; */
                                                $SelectObsluha = "SELECT   round((sum(IF(`Otazka_01_typ_hodnoceni`='Obsluha' and `Otazka_01_typ_otazky`='Hvězdičky', `Otazka_01_odpoved`, 0)) 
                                                                                + sum(IF(`Otazka_02_typ_hodnoceni`='Obsluha' and `Otazka_02_typ_otazky`='Hvězdičky', `Otazka_02_odpoved`, 0)) 
                                                                                + sum(IF(`Otazka_03_typ_hodnoceni`='Obsluha' and `Otazka_03_typ_otazky`='Hvězdičky', `Otazka_03_odpoved`, 0)) 
                                                                                + sum(IF(`Otazka_04_typ_hodnoceni`='Obsluha' and `Otazka_04_typ_otazky`='Hvězdičky', `Otazka_04_odpoved`, 0)) 
                                                                                + sum(IF(`Otazka_05_typ_hodnoceni`='Obsluha' and `Otazka_05_typ_otazky`='Hvězdičky', `Otazka_05_odpoved`, 0)) 
                                                                                + sum(IF(`Otazka_06_typ_hodnoceni`='Obsluha' and `Otazka_06_typ_otazky`='Hvězdičky', `Otazka_06_odpoved`, 0)) 
                                                                                + sum(IF(`Otazka_07_typ_hodnoceni`='Obsluha' and `Otazka_07_typ_otazky`='Hvězdičky', `Otazka_07_odpoved`, 0)) 
                                                                                + sum(IF(`Otazka_08_typ_hodnoceni`='Obsluha' and `Otazka_08_typ_otazky`='Hvězdičky', `Otazka_08_odpoved`, 0)) 
                                                                                + sum(IF(`Otazka_09_typ_hodnoceni`='Obsluha' and `Otazka_09_typ_otazky`='Hvězdičky', `Otazka_09_odpoved`, 0)) 
                                                                                + sum(IF(`Otazka_10_typ_hodnoceni`='Obsluha' and `Otazka_10_typ_otazky`='Hvězdičky', `Otazka_10_odpoved`, 0))) / count(`user`),1) as 'Hodnoceni'
                                                                                ,`k_poduzivatele_obsluha_jmeno`  as 'ObsluhaJmeno' 
                                                                                ,`k_poduzivatele_obsluha`.`k_poduzivatele_obsluha_foto` as 'Fotka'
                                                                                , count(*) as 'pocetHodnoceni'
                                                                                ,`k_poduzivatele_archivace_obsluhy`
                                                                                FROM `HodnoceniHlavni`  
                                                                                JOIN `k_poduzivatele_obsluha` ON `HodnoceniHlavni`.`id_user` = `k_poduzivatele_obsluha`.`k_poduzivatele_obsluha_id_hs`
                                                                                WHERE `HodnoceniStav` = 1 and `HodnoceniHlavni`.`sw_id` = ".$sw_id." and `k_poduzivatele_obsluha`.`k_poduzivatele_obsluha_sw_id` = ".$sw_id."  
                                                                                group by `k_poduzivatele_obsluha_id` order by `k_poduzivatele_archivace_obsluhy` desc, `id_user` asc ";

                                                //echo $SelectObsluha;
                                                //SELECT user FROM `HodnoceniHlavni` where  `sw_id` = ".$sw_id." group by user order by user asc";
                                                                      
                                               if (!$SelectObsluha) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}

                                                $vysledek_SelectObsluha=$mysqli->query("$SelectObsluha");
                                                $pocet_radku_SelectObsluha = $vysledek_SelectObsluha->num_rows;
                                                
                                                $poleObsluhaCNT = 0;
                                                $JavaScriptDonaty="";
                                                $JavaScriptDonatyConfig="";

                                                while ($SelectObsluha_seznam=MySQLi_Fetch_Array($vysledek_SelectObsluha)):
                                                    $poleObsluhaCNT = $poleObsluhaCNT +1;


                                                    /*
                                                      JS DEFINICE

                                                      var ctx60 = document.getElementById("chart-areaxx").getContext("2d");
                                                      window.myDoughnut = new Chart(ctx60, config60);

                                                    */
                                                    

                                                    //Definice
                                                    $JavaScriptDonaty = $JavaScriptDonaty. " var ctx_obluha".$poleObsluhaCNT." = document.getElementById('chart-area-obsluha".$poleObsluhaCNT."').getContext('2d');\r\n";
                                                    $JavaScriptDonaty = $JavaScriptDonaty. " window.myDoughnut = new Chart(ctx_obluha".$poleObsluhaCNT.", config_obsluha".$poleObsluhaCNT.");\r\n\n";

                                                    $JSHodnoceni =  $SelectObsluha_seznam["Hodnoceni"];
                                                    
                                                    //Config
                                                    $JavaScriptDonatyConfig= $JavaScriptDonatyConfig . "var config_obsluha".$poleObsluhaCNT." = {type: 'doughnut',data: {datasets: [{data: [ '".$JSHodnoceni."','".round(5-$JSHodnoceni ,2)."'],\r\n";
                                                    $JavaScriptDonatyConfig= $JavaScriptDonatyConfig . "backgroundColor: ['#".random_color()."',"."'#FAFAFA'],label: 'Dataset 1'}],labels: ['Hodnocení','K ideálu chybí']},\r\n";
                                                    $JavaScriptDonatyConfig= $JavaScriptDonatyConfig . "options: { responsive: true, legend: { display: false, position: 'top', }, title: {display: false,  text: 'Chart.js Doughnut Chart' }, animation: { animateScale: true, animateRotate: true, }, tooltips: { enabled: false }} }; \r\n\r\n";                      



                                                          if ($SelectObsluha_seznam["k_poduzivatele_archivace_obsluhy"]==1) {
                                                            $StylArchivace = "display: block;";
                                                          }else{
                                                            $StylArchivace = "display: none;";
                                                          }





                                                   ?>


                                                              


                                                              <div class="col-lg-2 hs-feedback-person" style="<?php echo $StylArchivace; ?> "  name="archiv<?php echo $SelectObsluha_seznam["k_poduzivatele_archivace_obsluhy"]; ?>" >
                                                               <div class="panel panel-default ">
                                                                  <div class="panel-heading">
                                                                      <h3 class="panel-title">
                                                                        <font face="Tahoma">
                                                                            <b>
                                                                                <?php echo $SelectObsluha_seznam["ObsluhaJmeno"]; ?> 
                                                                            </b>
                                                                        </font>
                                                                      </h3>
                                                                  </div>
                                                                  <div class="panel-body ">
                                                                     <div class="row">
                                                                      <div>
                                                                                  <div class="hs-feedback-portrait">
                                                                                      <canvas id="chart-area-obsluha<?php echo $poleObsluhaCNT; ?>" height="180"></canvas>
                                                                                        <?php 
                                                                                            if ($SelectObsluha_seznam["Fotka"]!="") {
                                                                                              $FotkaObsluhy = $SelectObsluha_seznam["Fotka"];
                                                                                            }else{
                                                                                              $FotkaObsluhy = "no_image.jpg";
                                                                                            }
                                                                                        ?>
                                                                                      <div class="hs-feedback-photo"><img title="<?php echo $SelectObsluha_seznam["ObsluhaJmeno"]." - Hodnocení: ".$SelectObsluha_seznam["Hodnoceni"]; ?>" src="../img/obsluhy/<?php echo $FotkaObsluhy;?>" class="hs-feedback-photo-img" ></div>
                                                                                  </div>                                                                              
                                                                               <br>
                                                                               <div >
                                                                                <center><font size="-1" style="color: #969696;">Hodnotilo: <B><?php echo $SelectObsluha_seznam["pocetHodnoceni"];?></B></font></center>
                                                                                  <center>
                                                                                    <b style="margin-left: 32px"><font size="+2"><?php echo "".$SelectObsluha_seznam["Hodnoceni"].""; ?></font></b>
                                                                                  <img width="28"  height="24" style="margin-bottom: 12px ;left:-30px;" src="../img/star-velka.png" class="img-circle" alt=""> 
                                                                                  </center>
                                                                                     
                                                                                  
                                                                               </div>
                                                                      </div>
                                                                    </div>
                                                                  </div>
                                                              </div>
                                                             </div> 


                                                   <?php 
                                                                                                    
                                                endwhile;       
                                       ?>


                                                  

                                                      
                                                        

                                                    

                                                </div>
                                                
                                                
                                          </div>

                                         </div>

                                         

                                            

                                        </div>

                                        <div class="row">                        
                                              <div class="col-md-12 col-lg-12">            
                                                <div class="panel panel-default">                
                                                  <div class="panel-body ng-binding"> 
                                                    
                                                    <button  id="tlacitkoArchivace" type="submit" class="btn btn-primary"><i class="icon-layers"></i>&nbsp&nbspZobrazit archivované</button>                               
                                                    <br class="clearBoth" />                                       

                                                </div>            
                                              </div>        
                                            </div>                
                                        </div> 


















                  <div class="row"> 
                         <div class="col-lg-2">
                         <div class="panel panel-default ">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="Tahoma"><b>Obsluha: <?php echo $VysledekObsluha ; ?> <img style="padding-bottom: 5px ;" src="../img/star.png" class="img-circle" alt=""> </i> </b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body ">
                               <div class="row">
                                <div>
                                        <?php
                                           
                                           /*if ($HodnoceniObsluhaCNT==0) {
                                               echo "<CENTER>Nejsou žádná data k zobrazení</CENTER>";
                                               echo "<canvas id='chart-area1' style='display: none;'></canvas>";
                                           }else {*/
                                           ?>
                                            <div>
                                                <canvas id="chart-area1"></canvas>
                                            </div>
                                           
                                           <?php  
                                           //}
                                         ?>
                                  <br><div><center><?php echo "Reakce: ".$HodnoceniObsluhaCNT.""; ?></center></div>
                                </div>
                              </div>
                            </div>
                        </div>
                       </div>  
                       <div class="col-lg-2">
                         <div class="panel panel-default ">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="Tahoma"><b>Služba: <?php echo $VysledekSluzba ; ?> <img style="padding-bottom: 5px ;" src="../img/star.png" class="img-circle" alt=""> </b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body ">
                               <div class="row">
                                <div>
                                        <?php
                                           
                                          /* $VysledekObsluha = 0;
                                           $VysledekSluzba = 0;
                                           $VysledekProstredi = 0;
                                           $VysledekCena = 0;
                                           $VysledekAtmosfera = 0;
                                           $VysledekNespecifikovano = 0;*/

                                           /*if ($VysledekSluzba==0) {
                                               echo "<CENTER>Nejsou žádná data k zobrazení</CENTER>";
                                               echo "<canvas id='chart-area2' style='display: none;'></canvas>";
                                           }else {*/
                                           ?>
                                            <div>
                                                <canvas id="chart-area2"></canvas>
                                            </div>
                                           
                                           <?php  
                                           //}
                                         ?>
                                    <br><div><center><?php echo "Reakce: ".$HodnoceniSluzbaCNT.""; ?></center></div>
                                </div>
                              </div>
                            </div>
                        </div>
                       </div>  
                       <div class="col-lg-2">
                         <div class="panel panel-default ">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="Tahoma"><b>Prostředí: <?php echo $VysledekProstredi ; ?> <img style="padding-bottom: 5px ;" src="../img/star.png" class="img-circle" alt=""> </b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body ">
                               <div class="row">
                                <div>
                                        <?php
                                           /*if ($VysledekProstredi==0) {
                                               echo "<CENTER>Nejsou žádná data k zobrazení</CENTER>";
                                               echo "<canvas id='chart-area3' style='display: none;'></canvas>";
                                           }else {*/
                                           ?>
                                            <div>
                                                <canvas id="chart-area3"></canvas>
                                            </div>
                                           
                                           <?php  
                                           //}
                                         ?>
                                   <br><div><center><?php echo "Reakce: ".$HodnoceniProstrediCNT.""; ?></center></div>      
                                </div>
                              </div>
                            </div>
                        </div>
                       </div>  
                       <div class="col-lg-2">
                         <div class="panel panel-default ">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="Tahoma"><b>Cena: <?php echo $VysledekCena ; ?> <img style="padding-bottom: 5px ;" src="../img/star.png" class="img-circle" alt=""> </b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body ">
                               <div class="row">
                                <div>
                                        <?php
                                           
                                           /*$VysledekCena=1;
                                           if ($VysledekCena==0) {
                                               echo "<CENTER>Nejsou žádná data k zobrazení</CENTER>";
                                               echo "<canvas id='chart-area4' style='display: none;'></canvas>";
                                           }else {*/
                                           ?>
                                            <div>
                                                <canvas id="chart-area4"></canvas>
                                            </div>
                                           
                                           <?php  
                                           //}
                                         ?>
                                  <br><div><center><?php echo "Reakce: ".$HodnoceniCenaCNT.""; ?></center></div>       
                                </div>
                              </div>
                            </div>
                        </div>
                       </div>  
                       <div class="col-lg-2">
                         <div class="panel panel-default ">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="Tahoma"><b>Atmosféra: <?php echo $VysledekAtmosfera ; ?> <img style="padding-bottom: 5px ;" src="../img/star.png" class="img-circle" alt=""> </b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body ">
                               <div class="row">
                                <div>
                                        <?php
                                            
                                           /*if ($VysledekAtmosfera==0) {
                                               echo "<CENTER>Nejsou žádná data k zobrazení</CENTER>";
                                               echo "<canvas id='chart-area5' style='display: none;'></canvas>";
                                           }else {*/
                                           ?>
                                            <div>
                                                <canvas id="chart-area5"></canvas>
                                            </div>
                                           
                                           <?php  
                                           //}
                                         ?>
                                 <br><div><center><?php echo "Reakce: ".$HodnoceniAtmosferaCNT.""; ?></center></div>        
                                </div>
                              </div>
                            </div>
                        </div>
                       </div>  
                       <div class="col-lg-2">
                         <div class="panel panel-default ">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="Tahoma"><b>Nespecifikováno: <?php echo $VysledekNespecifikovano ; ?> <img style="padding-bottom: 5px ;" src="../img/star.png" class="img-circle" alt=""> </b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div> 
                            </div>
                            <div class="panel-body ">
                               <div class="row">
                                <div>
                                        <?php
                                            
                                           /*if ($VysledekNespecifikovano==0) {
                                               echo "<CENTER>Nejsou žádná data k zobrazení</CENTER>";
                                               echo "<canvas id='chart-area6' style='display: none;'></canvas>";
                                           }else {*/
                                           ?>
                                            <div>
                                                <canvas id="chart-area6"></canvas>
                                            </div>
                                           
                                           <?php  
                                           //}
                                         ?>
                                         <br><div><center><?php echo "Reakce: ".$HodnoceniNespecifikovanoCNT.""; ?></center></div>
                                </div>
                              </div>
                            </div>
                        </div>
                       </div>  
                  


                   


                                                        
                                        
                                        <div class="row">
                                            <div class="col-md-12">
                                              <div class="panel panel-default" >
                                                <div class="panel-heading">
                                                    <h3 class="panel-title"><font face="tahoma"><b>Přehled hodnocení</b></font></h3>
                                                    <div class="actions pull-right">
                                                      <a href="index.php?strana=SpokojenostStatistika&mesic=0"><i class="fa fa-chevron-left"></i></a>
                                                       <span><?php echo $aMesice[$_SESSION["SQL_Mesic"]-1]; ?></span>
                                                      <a href="index.php?strana=SpokojenostStatistika&mesic=1"><i class="fa fa-chevron-right"></i></a>
                                                      
                                                      
                                                      <a href="index.php?strana=SpokojenostStatistika&rok_zmena=0"><i class="fa fa-chevron-left"></i></a>
                                                       <span><?php echo $SQL_ROK; ?></span>
                                                      <a href="index.php?strana=SpokojenostStatistika&rok_zmena=1"><i class="fa fa-chevron-right"></i></a>



                                                        <i class="fa fa-expand"></i>
                                                        <i class="fa fa-chevron-down"></i>
                                                        <i class="fa fa-times"></i>
                                                    </div>
                                                </div>
                                                <div class="panel-body">

                                                    <div class="table-responsive">                                
                                                         <table id="TabulkaHodnoceni" class="table table-striped table-bordered" cellspacing="0" width="99%" >
                                                            <thead>
                                                                <tr>
                                                                    <th align="center" rowspan="2"><b>#</b></th>
                                                                    <th align="center" rowspan="2"><b>Termín objednávky</b></th>
                                                                    <th align="center" rowspan="2"><b>Kdy bylo hodnoceno</b></th>
                                                                    <th align="center" rowspan="2"><b>Jméno zákazníka</b></th>
                                                                    <th align="center" rowspan="2"><b>Obsluha</b></th>
                                                                    <th align="center" colspan="10">Hodnocení otázky</th>
                                                                </tr>


                                                                    <?php  
                                                                    //SELECT * FROM `HodnoceniHlavni` WHERE `sw_id` = 3129 and `HodnoceniStav` = 1  order by 1 desc
                                                                    ?>
                                                                

                                                                <tr>
                                                                    <th align="center"><b>Otázka&nbsp;1</b></th>
                                                                    <th align="center"><b>Otázka&nbsp;2</b></th>
                                                                    <th align="center"><b>Otázka&nbsp;3</b></th>
                                                                    <th align="center"><b>Otázka&nbsp;4</b></th>
                                                                    <th align="center"><b>Otázka&nbsp;5</b></th>
                                                                    <th align="center"><b>Otázka&nbsp;6</b></th>
                                                                    <th align="center"><b>Otázka&nbsp;7</b></th>
                                                                    <th align="center"><b>Otázka&nbsp;8</b></th>
                                                                    <th align="center"><b>Otázka&nbsp;9</b></th>
                                                                    <th align="center"><b>Otázka&nbsp;10</b></th>                                                                    
                                                                </tr>


                                                            </thead>
                                                            <tbody>
                                                                





                                                                <?php   
                                                                       $select_na_hodnoceni= "SELECT * FROM `HodnoceniHlavni` WHERE MONTH(`order_date`) = '$aktualniMesic' and YEAR(`order_date`) = '$SQL_ROK' and `sw_id` = ".$sw_id." and `HodnoceniStav` = 1  order by 1 desc";
                                                                         //echo "***".$select_na_hodnoceni;

                                                                         if (!$select_na_pobocku) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

                                                                          $vysledek_na_hodnoceni=$mysqli->query($select_na_hodnoceni);
                                                                          $radku_na_hodnoceni=$vysledek_na_hodnoceni->num_rows; 

                                                                          if ($radku_na_hodnoceni>0) {
                                                                            $PocetHodnoceni=0;

                                                                                while ($na_hodnoceni=MySQLi_Fetch_Array($vysledek_na_hodnoceni)):   
                                                                                       $PocetHodnoceni = $PocetHodnoceni +1;
                                                                                       







                                                                                       echo "<tr>";  
                                                                                       echo "<td>".$PocetHodnoceni."</td>";
                                                                                       
                                                                                       /*Datum s v */
                                                                                        
                                                                                       $newDateSV = date("d.m.Y H:i", strtotime($na_hodnoceni["order_date"]));
                                                                                       //$DatumSV = str_replace(" ", " v ", $newDateSV); 
                                                                                       $DatumSV =  $newDateSV; 

                                                                                       $newDateSVHodnoceni = date("d.m.Y", strtotime($na_hodnoceni["Hodnoceni_KdyVyplneno"]));
                                                                                       $DatumSVHodnoceni = str_replace(" ", " v ", $newDateSVHodnoceni); 

                                                                                       /*
                                                                                        Razeni datatable podle datumu
                                                                                        https://datatables.net/forums/discussion/45692/how-to-date-sort-as-date-instead-of-string
    
                                                                                       */

                                                                                       echo "<td data-sort='". $na_hodnoceni["order_date"] ."'>".$DatumSV."</td>";
                                                                                       echo "<td data-sort='". $na_hodnoceni["Hodnoceni_KdyVyplneno"] ."'>".$DatumSVHodnoceni."</td>";

                                                                                       //https://klient.hairsoft.cz/str/index.php?strana=KartaOsoby&osoba_guid=6D19C95B86FD17C243B55CCA0A54FAF5

                                                                                       echo "<td class='CellWithComment'> <a style=\"color: #1f7bb6;\" href=\"https://klient.hairsoft.cz/str/index.php?strana=KartaOsoby&osoba_guid=".$na_hodnoceni["KlientGuid"]."\"> ".$na_hodnoceni["surname"]." ".$na_hodnoceni["name"]."</a><span class='CellComment'><B>".$na_hodnoceni["surname"]." ".$na_hodnoceni["name"]."</B><br><HR>Tel: <B>".$na_hodnoceni["cell"]."</B><br><HR>Email: <B>".$na_hodnoceni["email"]."</B></span></td>";
                                                                                       echo "<td>".$na_hodnoceni["user"]."</td>";

                                                                                       
                                                                                       /*Otazka 1*/
                                                                                       if ($na_hodnoceni["Otazka_01_typ_otazky"]=="Hvězdičky") {
                                                                                         echo "<td class='CellWithComment' align='center'>".$na_hodnoceni["Otazka_01_odpoved"]."<span class='CellComment'><B><I>Typ hodnocení:</B></I> ".$na_hodnoceni["Otazka_01_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_01_zadani"]."</span></td>";
                                                                                       }elseif ($na_hodnoceni["Otazka_01_typ_otazky"]=="Text") {
                                                                                         echo "<td class='CellWithComment' align='center'>Text<span class='CellComment'><B><I>Typ hodnocení: </I></B>".$na_hodnoceni["Otazka_01_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_01_zadani"]."<BR><HR><I><B>Odpověď: </B></I>".$na_hodnoceni["Otazka_01_odpoved"]."</span></td>";
                                                                                       }else{
                                                                                         echo "<td align='center'>-</td>";
                                                                                       }

                                                                                       /*Otazka 2*/
                                                                                       if ($na_hodnoceni["Otazka_02_typ_otazky"]=="Hvězdičky") {
                                                                                         echo "<td class='CellWithComment' align='center'>".$na_hodnoceni["Otazka_02_odpoved"]."<span class='CellComment'><B><I>Typ hodnocení:</B></I> ".$na_hodnoceni["Otazka_02_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_02_zadani"]."</span></td>";
                                                                                       }elseif ($na_hodnoceni["Otazka_02_typ_otazky"]=="Text") {
                                                                                         echo "<td class='CellWithComment' align='center'>Text<span class='CellComment'><B><I>Typ hodnocení: </I></B>".$na_hodnoceni["Otazka_02_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_02_zadani"]."<BR><HR><I><B>Odpověď: </B></I>".$na_hodnoceni["Otazka_02_odpoved"]."</span></td>";
                                                                                       }else{
                                                                                         echo "<td align='center'>-</td>";
                                                                                       }
                                                                                       /*Otazka 3*/
                                                                                       if ($na_hodnoceni["Otazka_03_typ_otazky"]=="Hvězdičky") {
                                                                                         echo "<td class='CellWithComment' align='center'>".$na_hodnoceni["Otazka_03_odpoved"]."<span class='CellComment'><B><I>Typ hodnocení:</B></I> ".$na_hodnoceni["Otazka_03_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_03_zadani"]."</span></td>";
                                                                                       }elseif ($na_hodnoceni["Otazka_03_typ_otazky"]=="Text") {
                                                                                         echo "<td class='CellWithComment' align='center'>Text<span class='CellComment'><B><I>Typ hodnocení: </I></B>".$na_hodnoceni["Otazka_03_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_03_zadani"]."<BR><HR><I><B>Odpověď: </B></I>".$na_hodnoceni["Otazka_03_odpoved"]."</span></td>";
                                                                                       }else{
                                                                                         echo "<td align='center'>-</td>";
                                                                                       }
                                                                                       /*Otazka 4*/
                                                                                       if ($na_hodnoceni["Otazka_04_typ_otazky"]=="Hvězdičky") {
                                                                                         echo "<td class='CellWithComment' align='center'>".$na_hodnoceni["Otazka_04_odpoved"]."<span class='CellComment'><B><I>Typ hodnocení:</B></I> ".$na_hodnoceni["Otazka_04_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_04_zadani"]."</span></td>";
                                                                                       }elseif ($na_hodnoceni["Otazka_04_typ_otazky"]=="Text") {
                                                                                         echo "<td class='CellWithComment' align='center'>Text<span class='CellComment'><B><I>Typ hodnocení: </I></B>".$na_hodnoceni["Otazka_04_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_04_zadani"]."<BR><HR><I><B>Odpověď: </B></I>".$na_hodnoceni["Otazka_04_odpoved"]."</span></td>";
                                                                                       }else{
                                                                                         echo "<td align='center'>-</td>";
                                                                                       }
                                                                                       /*Otazka 5*/
                                                                                       if ($na_hodnoceni["Otazka_05_typ_otazky"]=="Hvězdičky") {
                                                                                         echo "<td class='CellWithComment' align='center'>".$na_hodnoceni["Otazka_05_odpoved"]."<span class='CellComment'><B><I>Typ hodnocení:</B></I> ".$na_hodnoceni["Otazka_05_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_05_zadani"]."</span></td>";
                                                                                       }elseif ($na_hodnoceni["Otazka_05_typ_otazky"]=="Text") {
                                                                                         echo "<td class='CellWithComment' align='center'>Text<span class='CellComment'><B><I>Typ hodnocení: </I></B>".$na_hodnoceni["Otazka_05_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_05_zadani"]."<BR><HR><I><B>Odpověď: </B></I>".$na_hodnoceni["Otazka_05_odpoved"]."</span></td>";
                                                                                       }else{
                                                                                         echo "<td align='center'>-</td>";
                                                                                       }
                                                                                       /*Otazka 6*/
                                                                                       if ($na_hodnoceni["Otazka_06_typ_otazky"]=="Hvězdičky") {
                                                                                         echo "<td class='CellWithComment' align='center'>".$na_hodnoceni["Otazka_06_odpoved"]."<span class='CellComment'><B><I>Typ hodnocení:</B></I> ".$na_hodnoceni["Otazka_06_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_06_zadani"]."</span></td>";
                                                                                       }elseif ($na_hodnoceni["Otazka_06_typ_otazky"]=="Text") {
                                                                                         echo "<td class='CellWithComment' align='center'>Text<span class='CellComment'><B><I>Typ hodnocení: </I></B>".$na_hodnoceni["Otazka_06_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_06_zadani"]."<BR><HR><I><B>Odpověď: </B></I>".$na_hodnoceni["Otazka_06_odpoved"]."</span></td>";
                                                                                       }else{
                                                                                         echo "<td align='center'>-</td>";
                                                                                       }
                                                                                       /*Otazka 7*/
                                                                                       if ($na_hodnoceni["Otazka_07_typ_otazky"]=="Hvězdičky") {
                                                                                         echo "<td class='CellWithComment' align='center'>".$na_hodnoceni["Otazka_07_odpoved"]."<span class='CellComment'><B><I>Typ hodnocení:</B></I> ".$na_hodnoceni["Otazka_07_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_07_zadani"]."</span></td>";
                                                                                       }elseif ($na_hodnoceni["Otazka_07_typ_otazky"]=="Text") {
                                                                                         echo "<td class='CellWithComment' align='center'>Text<span class='CellComment'><B><I>Typ hodnocení: </I></B>".$na_hodnoceni["Otazka_07_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_07_zadani"]."<BR><HR><I><B>Odpověď: </B></I>".$na_hodnoceni["Otazka_07_odpoved"]."</span></td>";
                                                                                       }else{
                                                                                         echo "<td align='center'>-</td>";
                                                                                       }
                                                                                       /*Otazka 8*/
                                                                                       if ($na_hodnoceni["Otazka_08_typ_otazky"]=="Hvězdičky") {
                                                                                         echo "<td class='CellWithComment' align='center'>".$na_hodnoceni["Otazka_08_odpoved"]."<span class='CellComment'><B><I>Typ hodnocení:</B></I> ".$na_hodnoceni["Otazka_08_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_08_zadani"]."</span></td>";
                                                                                       }elseif ($na_hodnoceni["Otazka_08_typ_otazky"]=="Text") {
                                                                                         echo "<td class='CellWithComment' align='center'>Text<span class='CellComment'><B><I>Typ hodnocení: </I></B>".$na_hodnoceni["Otazka_08_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_08_zadani"]."<BR><HR><I><B>Odpověď: </B></I>".$na_hodnoceni["Otazka_08_odpoved"]."</span></td>";
                                                                                       }else{
                                                                                         echo "<td align='center'>-</td>";
                                                                                       }
                                                                                       /*Otazka 9*/
                                                                                       if ($na_hodnoceni["Otazka_09_typ_otazky"]=="Hvězdičky") {
                                                                                         echo "<td class='CellWithComment' align='center'>".$na_hodnoceni["Otazka_09_odpoved"]."<span class='CellComment'><B><I>Typ hodnocení:</B></I> ".$na_hodnoceni["Otazka_09_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_09_zadani"]."</span></td>";
                                                                                       }elseif ($na_hodnoceni["Otazka_09_typ_otazky"]=="Text") {
                                                                                         echo "<td class='CellWithComment' align='center'>Text<span class='CellComment'><B><I>Typ hodnocení: </I></B>".$na_hodnoceni["Otazka_09_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_09_zadani"]."<BR><HR><I><B>Odpověď: </B></I>".$na_hodnoceni["Otazka_09_odpoved"]."</span></td>";
                                                                                       }else{
                                                                                         echo "<td align='center'>-</td>";
                                                                                       }
                                                                                       /*Otazka 10*/
                                                                                       if ($na_hodnoceni["Otazka_10_typ_otazky"]=="Hvězdičky") {
                                                                                         echo "<td class='CellWithComment' align='center'>".$na_hodnoceni["Otazka_10_odpoved"]."<span class='CellComment'><B><I>Typ hodnocení:</B></I> ".$na_hodnoceni["Otazka_10_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_10_zadani"]."</span></td>";
                                                                                       }elseif ($na_hodnoceni["Otazka_10_typ_otazky"]=="Text") {
                                                                                         echo "<td class='CellWithComment' align='center'>Text<span class='CellComment'><B><I>Typ hodnocení: </I></B>".$na_hodnoceni["Otazka_10_typ_hodnoceni"]."<BR><HR><B><I>Otázka: </I></B>".$na_hodnoceni["Otazka_10_zadani"]."<BR><HR><I><B>Odpověď: </B></I>".$na_hodnoceni["Otazka_10_odpoved"]."</span></td>";
                                                                                       }else{
                                                                                         echo "<td align='center'>-</td>";
                                                                                       }
                                                                          
           

                                                                                       
                                                                                       
                                                                                       /*
                                                                                        
                                                                                          
                                                                                          <td class="CellWithComment">Cabaj Tomáš <span class="CellComment">Tel: 606/467039<br>Email: t.cabaj@email.cz</span></td>
                                                                                          <td>Valerie Nováková</td>
                                                                                          <td align="center">5</td>
                                                                                          <td align="center">4</td>
                                                                                          <td align="center">4</td>
                                                                                          <td align="center">Text</td>
                                                                                          <td align="center">-</td>
                                                                                          <td align="center">-</td>
                                                                                          <td align="center">-</td>
                                                                                          <td align="center">-</td>
                                                                                          <td align="center">-</td>
                                                                                          <td align="center">-</td>
                                                                                       */
                                                                                       
                                                                                       echo "</tr>";  
                                                                                endwhile;
                                                                            }

                                                                 ?>



                                                                
                                                                    

                                                              
                                                            </tbody>
                                                          </table>
                                                          <br><br><br><br><br><br><br><br>

                                                    </div>  
                                                </div>
                                          </div>
                                         </div>
                                        </div>
                
                 
                  <?php
                        
                        //echo "Dodelat paticku po prvnim voucheru + demo data. ";
                        //posledni zaznam v tabulce
                        //$sw_id = 3129;
                        $sql_PosledniAktualizace= "SELECT `Hodnoceni_KdyVlozeni` FROM `HodnoceniHlavni` WHERE `sw_id` = $sw_id order by `Hodnoceni_KdyVlozeni` Desc LIMIT 1";
                        //echo $sql_PosledniAktualizace;
                        $vysledek_sql_PosledniAktualizace=$mysqli->query($sql_PosledniAktualizace);
                        $data_sql_PosledniAktualizace=MySQLi_Fetch_Array($vysledek_sql_PosledniAktualizace);
                        $PosledniAktualizace = $data_sql_PosledniAktualizace["Hodnoceni_KdyVlozeni"];
                        
                        if ($PosledniAktualizace=="") {
                          $PosledniAktualizace = " nezjištěno ";
                        }
                          
                         
                        
                           if (strtotime($PosledniAktualizace)!="") {
                        ?>
                                                                  <div class="row">
                                                                  <div class="col-md-12">
                                                                      <div class="panel panel-default">
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



          <?php 
            /*Data pro grafy*/
            //Hodnoceni

            

           ?>
                 
                 
                 
                        
                
               
                  
  </section>        
</section>




 <script language="JavaScript">
        

        window.onload = function() {

            
            /*Kolacove grafy*/
            var ctx1 = document.getElementById("chart-area1").getContext("2d");
            window.myDoughnut = new Chart(ctx1, config1);

            var ctx2 = document.getElementById("chart-area2").getContext("2d");
            window.myDoughnut = new Chart(ctx2, config2);

            var ctx3 = document.getElementById("chart-area3").getContext("2d");
            window.myDoughnut = new Chart(ctx3, config3);

            var ctx4 = document.getElementById("chart-area4").getContext("2d");
            window.myDoughnut = new Chart(ctx4, config4);

            var ctx5 = document.getElementById("chart-area5").getContext("2d");
            window.myDoughnut = new Chart(ctx5, config5);

            var ctx6 = document.getElementById("chart-area6").getContext("2d");
            window.myDoughnut = new Chart(ctx6, config6);


            /*Kolacove linkovy graf obsluha*/
            /*var ctx10 = document.getElementById("canvas1").getContext("2d");
            window.myLine = new Chart(ctx10, config10);*/


            /*
            var ctx60 = document.getElementById("chart-areaxx").getContext("2d");
            window.myDoughnut = new Chart(ctx60, config60);
            */

            <?php 
              /*JS PHP obluha donuty definice*/
              echo $JavaScriptDonaty;
             ?>

             /*
             var ctx_obluha1 = document.getElementById('chart-area-obsluha1').getContext('2d');
             window.myDoughnut = new Chart(ctx_obluha1, config_obsluha1);
             */

          
            
        };





      
      
       


             
        
        
        var config1 = {
                    type: 'doughnut',
                     
                     
                    data: {
                        datasets: [{
                                      <?php 
                                          if ($VysledekObsluha==0) {
                                              echo "data: [ '0','100'],";
                                              echo "backgroundColor: ['#F8F8F8',"."'#F8F8F8'],";
                                              echo "label: 'Dataset 1'";
                                              echo "}],";
                                              echo "labels: ['','']";
                                              $obsluhaLabel = "false";

                                          }else{
                                              echo "data: [ '".$VysledekObsluha."','".round(5-$VysledekObsluha ,2)."'],";
                                              echo "backgroundColor: ['#".random_color()."',"."'#FAFAFA'],";
                                              echo "label: 'Dataset 1'";
                                              echo "}],";
                                              echo "labels: ['Obsluha','K ideálu Vám chybí']";
                                              $obsluhaLabel = "true";
                                          }
                                       ?>
                    },
                    
                    options: {
                        //cutoutPercentage: 80,
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
                            animateRotate: true,
                        },
                        tooltips: {
                           enabled: <?php echo $obsluhaLabel; ?>
                        }


                    }
        };   

        var config2 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                                      <?php 
                                          if ($VysledekSluzba==0) {
                                              echo "data: [ '0','100'],";
                                              echo "backgroundColor: ['#F8F8F8',"."'#F8F8F8'],";
                                              echo "label: 'Dataset 1'";
                                              echo "}],";
                                              echo "labels: ['','']";
                                              $sluzbaLabel = "false";

                                          }else{
                                              echo "data: [ '".$VysledekSluzba."','".round(5-$VysledekSluzba ,2)."'],";
                                              echo "backgroundColor: ['#".random_color()."',"."'#FAFAFA'],";
                                              echo "label: 'Dataset 1'";
                                              echo "}],";
                                              echo "labels: ['Služba','K ideálu Vám chybí']";
                                              $sluzbaLabel = "true";
                                          }
                                       ?>
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
                            animateRotate: true,
                        },
                        tooltips: {
                           enabled: <?php echo $sluzbaLabel; ?>
                        }
                    }
        };   

        var config3 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                                      <?php 
                                          if ($VysledekProstredi==0) {
                                              echo "data: [ '0','100'],";
                                              echo "backgroundColor: ['#F8F8F8',"."'#F8F8F8'],";
                                              echo "label: 'Dataset 1'";
                                              echo "}],";
                                              echo "labels: ['','']";
                                              $prostrediLabel = "false";

                                          }else{
                                              echo "data: [ '".$VysledekProstredi."','".round(5-$VysledekProstredi ,2)."'],";
                                              echo "backgroundColor: ['#".random_color()."',"."'#FAFAFA'],";
                                              echo "label: 'Dataset 1'";
                                              echo "}],";
                                              echo "labels: ['Prostředí','K ideálu Vám chybí']";
                                              $prostrediLabel = "true";
                                          }
                                       ?>
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
                            animateRotate: true,
                        },
                        tooltips: {
                           enabled: <?php echo $prostrediLabel; ?>
                        }
                    }
        };   

        var config4 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                                      <?php 
                                          if ($VysledekCena==0) {
                                              echo "data: [ '0','100'],";
                                              echo "backgroundColor: ['#F8F8F8',"."'#F8F8F8'],";
                                              echo "label: 'Dataset 1'";
                                              echo "}],";
                                              echo "labels: ['','']";
                                              $cenaLabel = "false";

                                          }else{
                                              echo "data: [ '".$VysledekCena."','".round(5-$VysledekCena ,2)."'],";
                                              echo "backgroundColor: ['#".random_color()."',"."'#FAFAFA'],";
                                              echo "label: 'Dataset 1'";
                                              echo "}],";
                                              echo "labels: ['Cena','K ideálu Vám chybí']";
                                              $cenaLabel = "true";
                                          }
                                       ?>
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
                            animateRotate: true,
                        },
                        tooltips: {
                           enabled: <?php echo $cenaLabel; ?>
                        }
                    }
        };   

var config5 = {
                    type: 'doughnut',
                    data: {
                      datasets: [{
                        <?php 
                                          if ($VysledekAtmosfera==0) {
                                              echo "data: [ '0','100'],";
                                              echo "backgroundColor: ['#F8F8F8',"."'#F8F8F8'],";
                                              echo "label: 'Dataset 1'";
                                              echo "}],";
                                              echo "labels: ['','']";
                                              $atmosferaLabel = "false";

                                          }else{
                                              echo "data: [ '".$VysledekAtmosfera."','".round(5-$VysledekAtmosfera ,2)."'],";
                                              echo "backgroundColor: ['#".random_color()."',"."'#FAFAFA'],";
                                              echo "label: 'Dataset 1'";
                                              echo "}],";
                                              echo "labels: ['Atmosféra','K ideálu Vám chybí']";
                                              $atmosferaLabel = "true";
                                          }
                                       ?>
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
                            animateRotate: true,
                        },
                        tooltips: {
                           enabled: <?php echo $atmosferaLabel; ?>
                        }

                    }
        };   

           var config6 = {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                                     <?php 
                                          if ($VysledekNespecifikovano==0) {
                                              echo "data: [ '0','100'],";
                                              echo "backgroundColor: ['#F8F8F8',"."'#F8F8F8'],";
                                              echo "label: 'Dataset 1'";
                                              echo "}],";
                                              echo "labels: ['','']";
                                              $nespecifikovanoLabel = "false";

                                          }else{
                                              echo "data: [ '".$VysledekNespecifikovano."','".round(5-$VysledekNespecifikovano ,2)."'],";
                                              echo "backgroundColor: ['#".random_color()."',"."'#FAFAFA'],";
                                              echo "label: 'Dataset 1'";
                                              echo "}],";
                                              echo "labels: ['Nespecifikováno','K ideálu Vám chybí']";
                                              $nespecifikovanoLabel = "true";
                                          }
                                       ?>
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
                            animateRotate: true,
                        },
                        tooltips: {
                           enabled: <?php echo $nespecifikovanoLabel; ?>
                        }
                    }
        };   




<?php 
  /*Donut oblsuha nastaveni*/
  echo $JavaScriptDonatyConfig;
 ?>

/*
var config_obsluha1 = {type: 'doughnut',data: {datasets: [{data: [ '4.9','0.1'],
backgroundColor: ['#97abae','#FAFAFA'],label: 'Dataset 1'}],labels: ['Hodnocení','K ideálu chybí']},
options: { responsive: true, legend: { display: false, position: 'top', }, title: {display: false,  text: 'Chart.js Doughnut Chart' }, animation: { animateScale: true, animateRotate: true, }, tooltips: { enabled: true }} }; 
*/

/*
 var config60 = {
                    type: 'doughnut',
                     
                     
                    data: {
                        datasets: [{
                                      <?php 
                                          
                                              echo "data: [ '0','100'],";
                                              echo "backgroundColor: ['#005EFF',"."'#005EFF'],";
                                              echo "label: 'Dataset 1'";
                                              echo "}],";
                                              echo "labels: ['','']";
                                              $obsluhaLabel = "false";

                                          
                                       ?>
                    },
                    
                    options: {
                        cutoutPercentage: 75,
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
                            animateRotate: true,
                        },
                        tooltips: {
                           enabled: true
                        }


                    }
        };

*/











       


         var config10 = {
            type: 'line',
            data: {
                /*
                labels: [<?php echo $datax; ?>],
                datasets: [<?php echo $data2; ?>]
                */


                /*
                    
                    -- pocet otazek je dynamicky 
                    -- zjistit pocet otazek na rok a pak k nim kazdy mesic pocitat odpoved
                    -- ke kazde otazce prumer k te otazce na ten tyden ne prumer na hodnoceni
                    --

                */

                labels: ['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52'],
                datasets: [
                           { label: "Otázka 1",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,0,0,0,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 2",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 3",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 4",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 5",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 6",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,5,4,2,3]},
                           { label: "Otázka 7",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 8",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 9",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 2",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 3",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 4",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 5",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 6",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,5,4,2,3]},
                           { label: "Otázka 7",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 8",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 9",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 2",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 3",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 4",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 5",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 6",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,5,4,2,3]},
                           { label: "Otázka 7",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 8",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 9",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 2",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 3",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 4",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 5",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 6",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,5,4,2,3]},
                           { label: "Otázka 7",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 8",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 9",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 2",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 3",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 4",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 5",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 6",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,5,4,2,3]},
                           { label: "Otázka 7",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 8",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 9",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 2",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 3",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 4",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 5",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 6",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,5,4,2,3]},
                           { label: "Otázka 7",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 8",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label: "Otázka 9",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3]},
                           { label:"Otázka 10",backgroundColor: "rgba(190,228,133,0.1)",borderColor: "#b27699",pointBorderColor: "#b27699",pointBackgroundColor: "#b27699",pointBorderWidth: 1,data: [4,4,3,1,4,2,3,2,1,4,4,3,1,4,2,3,2,1,4,4,3,1,4,5,3,2,1,4,4,3,1,4,2,3,2,1,4,1,2,1,1,1,1,1,1,1,1,1,1,1,1,1]} 
                          ]

                            
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



             
        
    </script>



