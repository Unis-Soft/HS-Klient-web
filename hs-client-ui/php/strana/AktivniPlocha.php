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
     Pole vznikalo az pri nalezeni databazoveho radku. Pri prazdnem vysledku
     volani count() dostalo nedefinovanou hodnotu a PHP 8.3 vyvolalo TypeError.

     STARY KOD PHP 5.5:
     $aSeznamStredisek nebylo pred databazovou smyckou inicializovano.
  */
  if (!isset($aSeznamStredisek) || !is_array($aSeznamStredisek)) {
    $aSeznamStredisek = array();
  }
  
  // Prava na stranku aby neslo podstrcit stranku s GET URL v oblibenych
  if ($_SESSION["JePoduzivatel"]=="1" and in_array("Dashboard", $_SESSION["SeznamPravPoduzivatele"])=="0") {
    echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=PrazdnaStrana\">";
    exit;
  }
  // Prava na stranku



  require HS_CLIENT_UI_ROOT . '/fce/GeneratorBarev.php';
  require HS_CLIENT_UI_ROOT . '/fce/DemoData.php';
  require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';
  
  $jmenoStranky = "Dashboard";
  $jmenoStrankyPopis = "Dashboard s rychlými přehledy za všechny pobočky";
  
  if ($_SESSION["pobocka_jmeno"]=="") {
    $pobocka_jmeno =  "Nevybrána" ;
  }else {
    $pobocka_jmeno =  $_SESSION["pobocka_jmeno"] ;  
  }
  
  $Uzivatel_ID = $_SESSION["k_id"];
  
  $sw_id = $_SESSION["pobocka_id"];
  
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////DEBUG/////////////////////////////////////////////////////////////////
  
  //$Uzivatel_ID = 11; 
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////
  
   if ($_SESSION["k_id"]=="10") {
    //$Uzivatel_ID =11;
    //$sw_id = 1081;
  }
  
  //Mena
  $Mena_Klienta = $_SESSION["Mena_Klienta"];
  
        //////////////////////////////////////////////////////////////////////////////////////
        //DEMO
        if ($_SESSION["pobocka_id"]=="") {
          $sw_id = 0;
          $Mena_Klienta = "Kč";
        }
        //////////////////////////////////////////////////////////////////////////////////////

  
  
 
  

  
  $AktualniRokProDashboard = $newYear = date("Y");
  
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
  
  //echo $SQL_ROK;
  


//START
//  ______     ______   ______   __     __    __     ______     __         __     ______     ______     ______     ______
// /\  __ \   /\  == \ /\__  _\ /\ \   /\ "-./  \   /\  __ \   /\ \       /\ \   /\___  \   /\  __ \   /\  ___\   /\  ___\
// \ \ \/\ \  \ \  _-/ \/_/\ \/ \ \ \  \ \ \-./\ \  \ \  __ \  \ \ \____  \ \ \  \/_/  /__  \ \  __ \  \ \ \____  \ \  __\
//  \ \_____\  \ \_\      \ \_\  \ \_\  \ \_\ \ \_\  \ \_\ \_\  \ \_____\  \ \_\   /\_____\  \ \_\ \_\  \ \_____\  \ \_____\
//   \/_____/   \/_/       \/_/   \/_/   \/_/  \/_/   \/_/\/_/   \/_____/   \/_/   \/_____/   \/_/\/_/   \/_____/   \/_____/



//Zjisteni poctu stredisek

           $sqldotaz_zjistit_strediska= "SELECT distinct `ts_NazevStrediska` as 'Stredisko' FROM `trzby_strediska_$SQL_ROK` join `sw_email_pobocka` on `trzby_strediska_$SQL_ROK`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID order by ts_ID ASC" ;
           if (!$sqldotaz_zjistit_strediska) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}

           

                  $vysledek_zjistit_strediska=$mysqli->query("$sqldotaz_zjistit_strediska");
                  $pocet_radku_zjistit_strediska = $vysledek_zjistit_strediska->num_rows;

                    if ($pocet_radku_zjistit_strediska==0) {
                      //echo "Optimalizace: nenalezeny žádná strediska pro rok $SQL_ROK.";
                    }else {

                      while ($seznam_stredisek=MySQLi_Fetch_Array($vysledek_zjistit_strediska)):
                             $strediskaCNT = $strediskaCNT + 1;
                             //POLE// pocit od 1
                             $aSeznamStredisek[$strediskaCNT] = $seznam_stredisek["Stredisko"];

                             //BARVA
                             $BarvaStrediska[$strediskaCNT] = random_color();

                             //echo $seznam_stredisek["Stredisko"];
                      endwhile;
                    }

                    //echo count($aSeznamStredisek);

//---------------------------------------------------
      //SELECT CASE WHEN SUM(`trzby_strediska_$SQL_ROK`.`ts_ZaSluzby`) IS NOT NULL THEN SUM(`trzby_strediska_$SQL_ROK`.`ts_ZaSluzby`) ELSE 0.00 END AS 'TRZBY1' FROM `trzby_strediska_$SQL_ROK` join `sw_email_pobocka` on `trzby_strediska_$SQL_ROK`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = 11 and `ts_NazevStrediska` = 'Zdeněk Bednář' and MONTH(`trzby_strediska_$SQL_ROK`.`ts_Datum`) = 1 AND YEAR(`trzby_strediska_$SQL_ROK`.`ts_Datum`) = $SQL_ROK
//

              $TrzbyCELKEM_ROK = 0;
              $ProdejCELKEM_ROK = 0;
              $KreditCELKEM_ROK = 0;
              $VoucherCELKEM_ROK = 0;

              $TrzbyCELKEM_SUM = 0;
              $ProdejCELKEM_SUM = 0;
              $KreditCELKEM_SUM = 0;
              $VoucherCELKEM_SUM = 0;

       //Strediska
       if ($pocet_radku_zjistit_strediska!=0) {
        //smycka pro strediska
        for ($stredisko = 1; $stredisko <= count($aSeznamStredisek); $stredisko++) {

            //echo $aSeznamStredisek[$stredisko];
              $TrzbyCELKEM_ROK = 0;
              $ProdejCELKEM_ROK = 0;
              $KreditCELKEM_ROK = 0;
              $VoucherCELKEM_ROK = 0;
              

              //Smycky na mesice
              for ($mesic = 1; $mesic <= 12; $mesic++) {

                    //TRZBY Mesice
                    //-------------
                    $sql_trzby_mesice= "SELECT CASE WHEN SUM(`trzby_strediska_$SQL_ROK`.`ts_ZaSluzby`) IS NOT NULL THEN SUM(`trzby_strediska_$SQL_ROK`.`ts_ZaSluzby`) ELSE 0.00 END AS 'TRZBY$mesic' FROM `trzby_strediska_$SQL_ROK` join `sw_email_pobocka` on `trzby_strediska_$SQL_ROK`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and `ts_NazevStrediska` = '$aSeznamStredisek[$stredisko]' and MONTH(`trzby_strediska_$SQL_ROK`.`ts_Datum`) = $mesic AND YEAR(`trzby_strediska_$SQL_ROK`.`ts_Datum`) = $SQL_ROK";
                    //echo $sql_trzby_mesice;
                    $vysledek_sql_trzby_mesice=$mysqli->query($sql_trzby_mesice);
                    $radku_sql_trzby_mesice=$vysledek_sql_trzby_mesice->num_rows;
                    $data_sql_trzby_mesice=MySQLi_Fetch_Array($vysledek_sql_trzby_mesice);

                    //POLE Trzby Celkem
                    $TrzbyCELKEM = $data_sql_trzby_mesice["TRZBY$mesic"];
                    $TrzbyCELKEM_SUM = $TrzbyCELKEM_SUM +$TrzbyCELKEM;
                    $TrzbyCELKEM_ROK = $TrzbyCELKEM_ROK +$TrzbyCELKEM;
                    //echo $TrzbyCELKEM."<BR>";

                     //Roydeleni mesicu na jedotlive pole index pole - stredisko
                     switch ($mesic) {
                       case 1:
                          $sTrzbyCELKEM_1[$stredisko] = $TrzbyCELKEM;
                         break;
                       case 2:
                          $sTrzbyCELKEM_2[$stredisko] = $TrzbyCELKEM;
                         break;
                       case 3:
                          $sTrzbyCELKEM_3[$stredisko] = $TrzbyCELKEM;
                         break;
                       case 4:
                          $sTrzbyCELKEM_4[$stredisko] = $TrzbyCELKEM;
                         break;
                       case 5:
                          $sTrzbyCELKEM_5[$stredisko] = $TrzbyCELKEM;
                         break;
                       case 6:
                          $sTrzbyCELKEM_6[$stredisko] = $TrzbyCELKEM;
                         break;
                       case 7:
                          $sTrzbyCELKEM_7[$stredisko] = $TrzbyCELKEM;
                         break;
                       case 8:
                          $sTrzbyCELKEM_8[$stredisko] = $TrzbyCELKEM;
                         break;
                       case 9:
                          $sTrzbyCELKEM_9[$stredisko] = $TrzbyCELKEM;
                         break;
                       case 10:
                          $sTrzbyCELKEM_10[$stredisko] = $TrzbyCELKEM;
                         break;
                       case 11:
                          $sTrzbyCELKEM_11[$stredisko] = $TrzbyCELKEM;
                         break;
                       case 12:
                          $sTrzbyCELKEM_12[$stredisko] = $TrzbyCELKEM;
                         break;

                       default:
                        break;
                     }


                     //PRODEDEJ
                     //--------------

                      //Prodej Mesice
                    //-------------
                    $sql_Prodej_mesice= "SELECT CASE WHEN SUM(`trzby_strediska_$SQL_ROK`.`ts_ZaProdej`) IS NOT NULL THEN SUM(`trzby_strediska_$SQL_ROK`.`ts_ZaProdej`) ELSE 0.00 END AS 'PRODEJ$mesic' FROM `trzby_strediska_$SQL_ROK` join `sw_email_pobocka` on `trzby_strediska_$SQL_ROK`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and `ts_NazevStrediska` = '$aSeznamStredisek[$stredisko]' and MONTH(`trzby_strediska_$SQL_ROK`.`ts_Datum`) = $mesic AND YEAR(`trzby_strediska_$SQL_ROK`.`ts_Datum`) = $SQL_ROK";
                    //echo $sql_Prodej_mesice;
                    $vysledek_sql_Prodej_mesice=$mysqli->query($sql_Prodej_mesice);
                    $radku_sql_Prodej_mesice=$vysledek_sql_Prodej_mesice->num_rows;
                    $data_sql_Prodej_mesice=MySQLi_Fetch_Array($vysledek_sql_Prodej_mesice);

                    //POLE Prodej Celkem
                    $ProdejCELKEM = $data_sql_Prodej_mesice["PRODEJ$mesic"];
                    $ProdejCELKEM_SUM =$ProdejCELKEM_SUM + $ProdejCELKEM;
                    $ProdejCELKEM_ROK =$ProdejCELKEM_ROK + $ProdejCELKEM;
                    //echo $ProdejCELKEM."<BR>";

                     //Roydeleni mesicu na jedotlive pole index pole - stredisko
                     switch ($mesic) {
                       case 1:
                          $sProdejCELKEM_1[$stredisko] = $ProdejCELKEM;
                         break;
                       case 2:
                          $sProdejCELKEM_2[$stredisko] = $ProdejCELKEM;
                         break;
                       case 3:
                          $sProdejCELKEM_3[$stredisko] = $ProdejCELKEM;
                         break;
                       case 4:
                          $sProdejCELKEM_4[$stredisko] = $ProdejCELKEM;
                         break;
                       case 5:
                          $sProdejCELKEM_5[$stredisko] = $ProdejCELKEM;
                         break;
                       case 6:
                          $sProdejCELKEM_6[$stredisko] = $ProdejCELKEM;
                         break;
                       case 7:
                          $sProdejCELKEM_7[$stredisko] = $ProdejCELKEM;
                         break;
                       case 8:
                          $sProdejCELKEM_8[$stredisko] = $ProdejCELKEM;
                         break;
                       case 9:
                          $sProdejCELKEM_9[$stredisko] = $ProdejCELKEM;
                         break;
                       case 10:
                          $sProdejCELKEM_10[$stredisko] = $ProdejCELKEM;
                         break;
                       case 11:
                          $sProdejCELKEM_11[$stredisko] = $ProdejCELKEM;
                         break;
                       case 12:
                          $sProdejCELKEM_12[$stredisko] = $ProdejCELKEM;
                         break;

                       default:
                        break;
                     }

                     //Voucher
                     //--------------
                     //-------------
                    $sql_Voucher_mesice= "SELECT CASE WHEN SUM(`trzby_strediska_$SQL_ROK`.`Celkem_Voucher`) IS NOT NULL THEN SUM(`trzby_strediska_$SQL_ROK`.`Celkem_Voucher`) ELSE 0.00 END AS 'VOUCHER$mesic' FROM `trzby_strediska_$SQL_ROK` join `sw_email_pobocka` on `trzby_strediska_$SQL_ROK`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and `ts_NazevStrediska` = '$aSeznamStredisek[$stredisko]' and MONTH(`trzby_strediska_$SQL_ROK`.`ts_Datum`) = $mesic AND YEAR(`trzby_strediska_$SQL_ROK`.`ts_Datum`) = $SQL_ROK";
                    //echo $sql_Voucher_mesice;
                    $vysledek_sql_Voucher_mesice=$mysqli->query($sql_Voucher_mesice);
                    $radku_sql_Voucher_mesice=$vysledek_sql_Voucher_mesice->num_rows;
                    $data_sql_Voucher_mesice=MySQLi_Fetch_Array($vysledek_sql_Voucher_mesice);

                    //POLE Prodej Celkem
                    $VoucherCELKEM = $data_sql_Voucher_mesice["VOUCHER$mesic"];
                    $VoucherCELKEM_SUM = $VoucherCELKEM_SUM + $VoucherCELKEM;
                    $VoucherCELKEM_ROK = $VoucherCELKEM_ROK + $VoucherCELKEM;

                     //Roydeleni mesicu na jedotlive pole index pole - stredisko
                     switch ($mesic) {
                       case 1:
                          $sVoucherCELKEM_1[$stredisko] = $VoucherCELKEM;
                         break;
                       case 2:
                          $sVoucherCELKEM_2[$stredisko] = $VoucherCELKEM;
                         break;
                       case 3:
                          $sVoucherCELKEM_3[$stredisko] = $VoucherCELKEM;
                         break;
                       case 4:
                          $sVoucherCELKEM_4[$stredisko] = $VoucherCELKEM;
                         break;
                       case 5:
                          $sVoucherCELKEM_5[$stredisko] = $VoucherCELKEM;
                         break;
                       case 6:
                          $sVoucherCELKEM_6[$stredisko] = $VoucherCELKEM;
                         break;
                       case 7:
                          $sVoucherCELKEM_7[$stredisko] = $VoucherCELKEM;
                         break;
                       case 8:
                          $sVoucherCELKEM_8[$stredisko] = $VoucherCELKEM;
                         break;
                       case 9:
                          $sVoucherCELKEM_9[$stredisko] = $VoucherCELKEM;
                         break;
                       case 10:
                          $sVoucherCELKEM_10[$stredisko] = $VoucherCELKEM;
                         break;
                       case 11:
                          $sVoucherCELKEM_11[$stredisko] = $VoucherCELKEM;
                         break;
                       case 12:
                          $sVoucherCELKEM_12[$stredisko] = $VoucherCELKEM;
                         break;

                       default:
                        break;
                     }

                     //Kredit
                     //--------------
                     //-------------
                    $sql_Kredit_mesice= "SELECT CASE WHEN SUM(`trzby_strediska_$SQL_ROK`.`Celkem_Credit`) IS NOT NULL THEN SUM(`trzby_strediska_$SQL_ROK`.`Celkem_Credit`) ELSE 0.00 END AS 'KREDIT$mesic' FROM `trzby_strediska_$SQL_ROK` join `sw_email_pobocka` on `trzby_strediska_$SQL_ROK`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and `ts_NazevStrediska` = '$aSeznamStredisek[$stredisko]' and MONTH(`trzby_strediska_$SQL_ROK`.`ts_Datum`) = $mesic AND YEAR(`trzby_strediska_$SQL_ROK`.`ts_Datum`) = $SQL_ROK";
                    //echo $sql_Kredit_mesice;
                    $vysledek_sql_Kredit_mesice=$mysqli->query($sql_Kredit_mesice);
                    $radku_sql_Kredit_mesice=$vysledek_sql_Kredit_mesice->num_rows;
                    $data_sql_Kredit_mesice=MySQLi_Fetch_Array($vysledek_sql_Kredit_mesice);

                    //POLE Prodej Celkem
                    $KreditCELKEM = $data_sql_Kredit_mesice["KREDIT$mesic"];
                    //echo "****".$KreditCELKEM ;
                    $KreditCELKEM_SUM = $KreditCELKEM_SUM + $KreditCELKEM;
                    $KreditCELKEM_ROK = $KreditCELKEM_ROK + $KreditCELKEM;
                    //echo $ProdejCELKEM."<BR>";

                     //Roydeleni mesicu na jedotlive pole index pole - stredisko
                     switch ($mesic) {
                       case 1:
                          $sKreditCELKEM_1[$stredisko] = $KreditCELKEM;
                         break;
                       case 2:
                          $sKreditCELKEM_2[$stredisko] = $KreditCELKEM;
                         break;
                       case 3:
                          $sKreditCELKEM_3[$stredisko] = $KreditCELKEM;
                         break;
                       case 4:
                          $sKreditCELKEM_4[$stredisko] = $KreditCELKEM;
                         break;
                       case 5:
                          $sKreditCELKEM_5[$stredisko] = $KreditCELKEM;
                         break;
                       case 6:
                          $sKreditCELKEM_6[$stredisko] = $KreditCELKEM;
                         break;
                       case 7:
                          $sKreditCELKEM_7[$stredisko] = $KreditCELKEM;
                         break;
                       case 8:
                          $sKreditCELKEM_8[$stredisko] = $KreditCELKEM;
                         break;
                       case 9:
                          $sKreditCELKEM_9[$stredisko] = $KreditCELKEM;
                         break;
                       case 10:
                          $sKreditCELKEM_10[$stredisko] = $KreditCELKEM;
                         break;
                       case 11:
                          $sKreditCELKEM_11[$stredisko] = $KreditCELKEM;
                         break;
                       case 12:
                          $sKreditCELKEM_12[$stredisko] = $KreditCELKEM;
                         break;

                       default:
                        break;
                     }

             }


             $SumaKompletStrediskoTrzba[$stredisko] = $TrzbyCELKEM_ROK;
             $SumaKompletStrediskoProdej[$stredisko] = $ProdejCELKEM_ROK;
             $SumaKompletStrediskoVoucher[$stredisko] = $VoucherCELKEM_ROK;
             $SumaKompletStrediskoKredit[$stredisko] = $KreditCELKEM_ROK;


         }
        }

 //Trzby
 $data1="";
  $carka = "";
     for ($i = 1; $i <= $strediskaCNT; $i++) {

           if ($i == $strediskaCNT) {
             $carka = "";
           }else {
             $carka = ",";
           }

       $data1= $data1 . "{ ";
       $data1= $data1 . "label: '".$aSeznamStredisek[$i]."',";
       $data1= $data1 . "backgroundColor: \"#".$BarvaStrediska[$i]."\",";
       $data1= $data1 . "data: [";
       $data1= $data1 .  $sTrzbyCELKEM_1[$i].",".$sTrzbyCELKEM_2[$i].",".$sTrzbyCELKEM_3[$i].",".$sTrzbyCELKEM_4[$i].",".$sTrzbyCELKEM_5[$i].",".$sTrzbyCELKEM_6[$i].",".$sTrzbyCELKEM_7[$i].",".$sTrzbyCELKEM_8[$i].",".$sTrzbyCELKEM_9[$i].",".$sTrzbyCELKEM_10[$i].",".$sTrzbyCELKEM_11[$i].",".$sTrzbyCELKEM_12[$i];
       $data1= $data1 . "]";
       $data1= $data1 . "} ";
       $data1= $data1 . $carka;

     }

    // echo $data1;

  //Prodej
  $data2="";
  $carka = "";
     for ($i = 1; $i <= $strediskaCNT; $i++) {

           if ($i == $strediskaCNT) {
             $carka = "";
           }else {
             $carka = ",";
           }

       $data2= $data2 . "{ ";
       $data2= $data2 . "label: '".$aSeznamStredisek[$i]."',";
       $data2= $data2 . "backgroundColor: \"#".$BarvaStrediska[$i]."\",";
       $data2= $data2 . "data: [";
       $data2= $data2 .  $sProdejCELKEM_1[$i].",".$sProdejCELKEM_2[$i].",".$sProdejCELKEM_3[$i].",".$sProdejCELKEM_4[$i].",".$sProdejCELKEM_5[$i].",".$sProdejCELKEM_6[$i].",".$sProdejCELKEM_7[$i].",".$sProdejCELKEM_8[$i].",".$sProdejCELKEM_9[$i].",".$sProdejCELKEM_10[$i].",".$sProdejCELKEM_11[$i].",".$sProdejCELKEM_12[$i];
       $data2= $data2 . "]";
       $data2= $data2 . "} ";
       $data2= $data2 . $carka;

     }

      //Voucher
  $data3="";
  $carka = "";
     for ($i = 1; $i <= $strediskaCNT; $i++) {

           if ($i == $strediskaCNT) {
             $carka = "";
           }else {
             $carka = ",";
           }

       $data3= $data3 . "{ ";
       $data3= $data3 . "label: '".$aSeznamStredisek[$i]."',";
       $data3= $data3 . "backgroundColor: \"#".$BarvaStrediska[$i]."\",";
       $data3= $data3 . "data: [";
       $data3= $data3 .  $sVoucherCELKEM_1[$i].",".$sVoucherCELKEM_2[$i].",".$sVoucherCELKEM_3[$i].",".$sVoucherCELKEM_4[$i].",".$sVoucherCELKEM_5[$i].",".$sVoucherCELKEM_6[$i].",".$sVoucherCELKEM_7[$i].",".$sVoucherCELKEM_8[$i].",".$sVoucherCELKEM_9[$i].",".$sVoucherCELKEM_10[$i].",".$sVoucherCELKEM_11[$i].",".$sVoucherCELKEM_12[$i];
       $data3= $data3 . "]";
       $data3= $data3 . "} ";
       $data3= $data3 . $carka;

     }
      //Kredit
  $data4="";
  $carka = "";
     for ($i = 1; $i <= $strediskaCNT; $i++) {

           if ($i == $strediskaCNT) {
             $carka = "";
           }else {
             $carka = ",";
           }

       $data4= $data4 . "{ ";
       $data4= $data4 . "label: '".$aSeznamStredisek[$i]."',";
       $data4= $data4 . "backgroundColor: \"#".$BarvaStrediska[$i]."\",";
       $data4= $data4 . "data: [";
       $data4= $data4 .  $sKreditCELKEM_1[$i].",".$sKreditCELKEM_2[$i].",".$sKreditCELKEM_3[$i].",".$sKreditCELKEM_4[$i].",".$sKreditCELKEM_5[$i].",".$sKreditCELKEM_6[$i].",".$sKreditCELKEM_7[$i].",".$sKreditCELKEM_8[$i].",".$sKreditCELKEM_9[$i].",".$sKreditCELKEM_10[$i].",".$sKreditCELKEM_11[$i].",".$sKreditCELKEM_12[$i];
       $data4= $data4 . "]";
       $data4= $data4 . "} ";
       $data4= $data4 . $carka;

     }


        //echo $data2;

  /*
      {
        label: 'Dataset 1',
        backgroundColor: "rgba(220,220,220,1)",
        data: [randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor()]
      },


       {
         label: 'UnisSoft s.r.o.',
         backgroundColor: "#2b85f6",
         data: [0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00]} ,{ label: 'Zdeněk Bednář',backgroundColor: "#8b07ef",data: [0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00]}             ]

  */


        /*
            {
                label: 'CPU Load',
                fillColor: 'rgba(26,188,156,0.5)',
                strokeColor: 'rgba(255,255,255,0.8)',
                highlightFill: 'rgba(26,188,156,1)',
                highlightStroke: 'rgba(255,255,255,0.8)',
                data: [randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor(), randomScalingFactor()]
            },
        */


//KONEC

            // pro ladeni je prikaz EXPLAIN
//  ______     ______   ______   __     __    __     ______     __         __     ______     ______     ______     ______
// /\  __ \   /\  == \ /\__  _\ /\ \   /\ "-./  \   /\  __ \   /\ \       /\ \   /\___  \   /\  __ \   /\  ___\   /\  ___\
// \ \ \/\ \  \ \  _-/ \/_/\ \/ \ \ \  \ \ \-./\ \  \ \  __ \  \ \ \____  \ \ \  \/_/  /__  \ \  __ \  \ \ \____  \ \  __\
//  \ \_____\  \ \_\      \ \_\  \ \_\  \ \_\ \ \_\  \ \_\ \_\  \ \_____\  \ \_\   /\_____\  \ \_\ \_\  \ \_____\  \ \_____\
//   \/_____/   \/_/       \/_/   \/_/   \/_/  \/_/   \/_/\/_/   \/_____/   \/_/   \/_____/   \/_/\/_/   \/_____/   \/_____/












 
?>





<section class="main-content-wrapper">            
 

  <div class="pageheader hs-dashboard-header">                
    <div class="hs-dashboard-heading">
      <h1><?php echo $jmenoStranky; ?></h1>                
      <p class="description"><?php echo $jmenoStrankyPopis; ?></p>
    </div>
    <div class="breadcrumb-wrapper hidden-xs hs-dashboard-meta">
      <span class="hs-dashboard-meta-icon" aria-hidden="true">
        <svg><use href="#hs-icon-building" xlink:href="#hs-icon-building"></use></svg>
      </span>
      <span class="hs-dashboard-meta-group">
        <span class="label">Pobočka</span>
        <span class="hs-dashboard-branch-value"><?php echo JePobockaOnline($sw_id,$mysqli) ?><b data-hs-i18n-ignore><?php echo $pobocka_jmeno; ?></b></span>
      </span>
      <span class="hs-dashboard-meta-divider" aria-hidden="true"></span>
      <span class="hs-dashboard-meta-group">
        <span class="label">SW ID</span>
        <span class="hs-dashboard-id-value" data-hs-i18n-ignore><?php echo $sw_id; ?></span>
      </span>
    </div>
                                                                                    
  </div>   
       
            
       <?php
            
         
         //TRZBY DNES!
         $sql_celkove_trzby= "SELECT CASE WHEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_TrzbyCelkem`) IS NOT NULL THEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_TrzbyCelkem`) ELSE 0.00 END AS 'TrzbyCelkem' FROM `trzby_strediska_$AktualniRokProDashboard` join `sw_email_pobocka` on `trzby_strediska_$AktualniRokProDashboard`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and DATE(`trzby_strediska_$AktualniRokProDashboard`.`ts_Datum`) = CURDATE();";
                        $vysledek_sql_celkove_trzby=$mysqli->query($sql_celkove_trzby);
                        //$radku_sql_celkove_trzby=$vysledek_sql_celkove_trzby->num_rows; 
                        $data_sql_celkove_trzby=MySQLi_Fetch_Array($vysledek_sql_celkove_trzby);
                        $CelkoveTrzby = number_format($data_sql_celkove_trzby["TrzbyCelkem"], 2, ',', ' ');
                        //echo $sql_celkove_trzby;
                        
         $sql_dnes_za_sluzby= "SELECT CASE WHEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_ZaSluzby`) IS NOT NULL THEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_ZaSluzby`) ELSE 0.00 END AS 'DnesZaSluzbyCelkem' FROM `trzby_strediska_$AktualniRokProDashboard` join `sw_email_pobocka` on `trzby_strediska_$AktualniRokProDashboard`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and DATE(`trzby_strediska_$AktualniRokProDashboard`.`ts_Datum`) = CURDATE();";
                        $vysledek_sql_dnes_za_sluzby=$mysqli->query($sql_dnes_za_sluzby);
                        //$radku_sql_dnes_za_sluzby=$vysledek_sql_dnes_za_sluzby->num_rows; 
                        $data_sql_dnes_za_sluzby=MySQLi_Fetch_Array($vysledek_sql_dnes_za_sluzby);
                        $DnesZaSluzby = number_format($data_sql_dnes_za_sluzby["DnesZaSluzbyCelkem"], 2, ',', ' ');
                       // echo $sql_dnes_za_sluzby;

         $sql_dnes_za_prodej= "SELECT CASE WHEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_ZaProdej`) IS NOT NULL THEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_ZaProdej`) ELSE 0.00 END AS 'DnesZaprodejCelkem' FROM `trzby_strediska_$AktualniRokProDashboard` join `sw_email_pobocka` on `trzby_strediska_$AktualniRokProDashboard`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and DATE(`trzby_strediska_$AktualniRokProDashboard`.`ts_Datum`) = CURDATE();";
                        $vysledek_sql_dnes_za_prodej=$mysqli->query($sql_dnes_za_prodej);
                        //$radku_sql_dnes_za_prodej=$vysledek_sql_dnes_za_prodej->num_rows; 
                        $data_sql_dnes_za_prodej=MySQLi_Fetch_Array($vysledek_sql_dnes_za_prodej);
                        $DnesZaprodej = number_format($data_sql_dnes_za_prodej["DnesZaprodejCelkem"], 2, ',', ' ');                        
                        //echo $sql_dnes_za_prodej;
                        
         $sql_celkove_mesic= "SELECT CASE WHEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_TrzbyCelkem`) IS NOT NULL THEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_TrzbyCelkem`) ELSE 0.00 END AS 'MesicCelkem' FROM `trzby_strediska_$AktualniRokProDashboard` join `sw_email_pobocka` on `trzby_strediska_$AktualniRokProDashboard`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and `trzby_strediska_$AktualniRokProDashboard`.`ts_Datum`  >= CURDATE() - INTERVAL DAY(CURDATE())-1 DAY";
                        $vysledek_sql_celkove_mesic=$mysqli->query($sql_celkove_mesic);
                        //$radku_sql_celkove_mesic=$vysledek_sql_celkove_mesic->num_rows; 
                        $data_sql_celkove_mesic=MySQLi_Fetch_Array($vysledek_sql_celkove_mesic);
                        $CelkoveMesic = number_format($data_sql_celkove_mesic["MesicCelkem"], 2, ',', ' ');                
                        
        $sql_celkove_mesic_prodej= "SELECT CASE WHEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_ZaProdej`) IS NOT NULL THEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_ZaProdej`) ELSE 0.00 END AS 'MesicCelkemProdej' FROM `trzby_strediska_$AktualniRokProDashboard` join `sw_email_pobocka` on `trzby_strediska_$AktualniRokProDashboard`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and `trzby_strediska_$AktualniRokProDashboard`.`ts_Datum`  >= CURDATE() - INTERVAL DAY(CURDATE())-1 DAY";
                        $vysledek_sql_celkove_mesic_prodej=$mysqli->query($sql_celkove_mesic_prodej);
                        //$radku_sql_celkove_mesic_prodej=$vysledek_sql_celkove_mesic_prodej->num_rows; 
                        $data_sql_celkove_mesic_prodej=MySQLi_Fetch_Array($vysledek_sql_celkove_mesic_prodej);
                        $MesicCelkemProdej = number_format($data_sql_celkove_mesic_prodej["MesicCelkemProdej"], 2, ',', ' ');                        
                        
        $sql_celkove_mesic_sluzby= "SELECT CASE WHEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_ZaSluzby`) IS NOT NULL THEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_ZaSluzby`) ELSE 0.00 END AS 'MesicCelkemSluzby' FROM `trzby_strediska_$AktualniRokProDashboard` join `sw_email_pobocka` on `trzby_strediska_$AktualniRokProDashboard`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and `trzby_strediska_$AktualniRokProDashboard`.`ts_Datum`  >= CURDATE() - INTERVAL DAY(CURDATE())-1 DAY";
                        $vysledek_sql_celkove_mesic_sluzby=$mysqli->query($sql_celkove_mesic_sluzby);
                        //$radku_sql_celkove_mesic_sluzby=$vysledek_sql_celkove_mesic_sluzby->num_rows; 
                        $data_sql_celkove_mesic_sluzby=MySQLi_Fetch_Array($vysledek_sql_celkove_mesic_sluzby);
                        $MesicCelkemSluzby = number_format($data_sql_celkove_mesic_sluzby["MesicCelkemSluzby"], 2, ',', ' ');
                        
        //Uctenek dnes
         $sql_dnes_uctenek= "SELECT CASE WHEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_uctenek`) IS NOT NULL THEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_uctenek`) ELSE 0 END AS 'UctenkyDnes' FROM `trzby_strediska_$AktualniRokProDashboard` join `sw_email_pobocka` on `trzby_strediska_$AktualniRokProDashboard`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and DATE(`trzby_strediska_$AktualniRokProDashboard`.`ts_Datum`) = CURDATE();";
                        $vysledek_sql_dnes_uctenek=$mysqli->query($sql_dnes_uctenek);
                        //$radku_sql_dnes_uctenek=$vysledek_sql_dnes_uctenek->num_rows; 
                        $data_sql_dnes_uctenek=MySQLi_Fetch_Array($vysledek_sql_dnes_uctenek);
                        $Uctenek_dnes = $data_sql_dnes_uctenek["UctenkyDnes"];
        
        //Uctenek mesic
         //$sql_dnes_uctenek= "SELECT CASE WHEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_uctenek`) IS NOT NULL THEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_uctenek`) ELSE 0 END AS 'UctenkyDnes' FROM `trzby_strediska_$AktualniRokProDashboard` join `sw_email_pobocka` on `trzby_strediska_$AktualniRokProDashboard`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and DATE(`trzby_strediska_$AktualniRokProDashboard`.`ts_Datum`) = CURDATE();";
        $sql_meisc_uctenek= "SELECT CASE WHEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_uctenek`) IS NOT NULL THEN SUM(`trzby_strediska_$AktualniRokProDashboard`.`ts_uctenek`) ELSE 0 END AS 'UctenkyMesic' FROM `trzby_strediska_$AktualniRokProDashboard` join `sw_email_pobocka` on `trzby_strediska_$AktualniRokProDashboard`.`sw_id` = `sw_email_pobocka`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID and  `trzby_strediska_$AktualniRokProDashboard`.`ts_Datum`  >= CURDATE() - INTERVAL DAY(CURDATE())-1 DAY";
                        $vysledek_sql_meisc_uctenek=$mysqli->query($sql_meisc_uctenek);
                        //$radku_sql_meisc_uctenek=$vysledek_sql_meisc_uctenek->num_rows; 
                        $data_sql_meisc_uctenek=MySQLi_Fetch_Array($vysledek_sql_meisc_uctenek);
                        $Uctenek_Mesic = $data_sql_meisc_uctenek["UctenkyMesic"];                

        
        
        
        
                                                      //SMS dnes
                                                      $select_na_sumu_utraceno_dnes= "SELECT sum(`sms_odelano_sms`) as SUMA_UTRACENO FROM `sms_odeslane_fronta` WHERE `sms_stav` = '1' and `sms_odelano_sms` > 0 and `sms_skupina_ID` in (SELECT `sw_skupina_id` FROM `sw_email_pobocka` join `sw_info` ON `sw_email_pobocka`.`sw_id` = `sw_info`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID)  and DATE(`sms_skutecne_odeslana`) = CURDATE()";
                                                      if (!$select_na_sumu_utraceno_dnes) { die('Chyba pripojeni do DB!');}
                                                      $vysledek_na_sumu_utraceno_dnes=$mysqli->query("$select_na_sumu_utraceno_dnes");
                                                      $data_suma_utraceno_dnes=MySQLi_Fetch_Array($vysledek_na_sumu_utraceno_dnes);
                    
                                                       if ($data_suma_utraceno_dnes["SUMA_UTRACENO"]=="") {
                                                         $suma_sms_odeslano_dnes = 0 ;
                                                       }else {
                                                         $suma_sms_odeslano_dnes = $data_suma_utraceno_dnes["SUMA_UTRACENO"];
                                                       }
                                                      
                                                       
                                                     


//OPTIMALIZACE MYSQL - seznam stredisek oddelenych carkou pro in
  

  $seznam_stredisek_do_sql = "";
  
  $sqldotaz_optimalizace_strediska= "SELECT distinct `sw_skupina_id` as 'StrediskoID' FROM `sw_email_pobocka` join `sw_info` ON `sw_email_pobocka`.`sw_id` = `sw_info`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID" ;
           if (!$sqldotaz_optimalizace_strediska) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}

      //echo $sqldotaz_optimalizace_strediska;     

                  $vysledek_optimalizace_strediska=$mysqli->query("$sqldotaz_optimalizace_strediska");
                  $pocet_radku_optimalizace_strediska = $vysledek_optimalizace_strediska->num_rows;

                    if ($pocet_radku_optimalizace_strediska==0) {
                      $seznam_stredisek_do_sql = "";

                    }else {

                      $strediskaprvni = 1;
                      while ($seznam_optimalizace=MySQLi_Fetch_Array($vysledek_optimalizace_strediska)):
                             
                             if ($strediskaprvni == 1) {
                                 $strediskaprvni = 0;
                                 
                                 $seznam_stredisek_do_sql = $seznam_stredisek_do_sql . $seznam_optimalizace["StrediskoID"];
                             }else{
                                 //', na zacatku'
                                 $seznam_stredisek_do_sql = $seznam_stredisek_do_sql .",". $seznam_optimalizace["StrediskoID"];
                             }
                           
                             
                      endwhile;
                    }
                                                       

                                                     // SMS mesic                                                                                                                            
//puvodni $select_na_sumu_utraceno_mesic= "SELECT sum(`sms_odelano_sms`) as SUMA_UTRACENO FROM `sms_odeslane_fronta` WHERE `sms_stav` = 1 and `sms_odelano_sms` > 0 and `sms_skupina_ID` in (SELECT `sw_skupina_id` FROM `sw_email_pobocka` join `sw_info` ON `sw_email_pobocka`.`sw_id` = `sw_info`.`sw_id` where `sw_email_pobocka`.`k_id` = $Uzivatel_ID) and `sms_skutecne_odeslana` >= CURDATE() - INTERVAL DAY(CURDATE())-1 DAY";

//echo $seznam_stredisek_do_sql."*****";
                                                          //vystup z optimalizace
                                                          if ($seznam_stredisek_do_sql!="") {
                                                            $mysqlIn = "and `sms_skupina_ID` in ( ".$seznam_stredisek_do_sql." )";
                                                          }else {
                                                            $mysqlIn = "";
                                                          }

                                                      $select_na_sumu_utraceno_mesic= "SELECT sum(`sms_odelano_sms`) as SUMA_UTRACENO FROM `sms_odeslane_fronta` WHERE `sms_stav` = 1 and `sms_odelano_sms` > 0 ". $mysqlIn  ." and `sms_skutecne_odeslana` >= CURDATE() - INTERVAL DAY(CURDATE())-1 DAY";
                                                      if (!$select_na_sumu_utraceno_mesic) { die('Chyba pripojeni do DB!');}
                                                      $vysledek_na_sumu_utraceno_mesic=$mysqli->query("$select_na_sumu_utraceno_mesic");
                                                      $data_suma_utraceno_mesic=MySQLi_Fetch_Array($vysledek_na_sumu_utraceno_mesic);
                    
                                                       if ($data_suma_utraceno_mesic["SUMA_UTRACENO"]=="") {
                                                         $suma_sms_odeslano_mesic = 0 ;
                                                       }else {
                                                         $suma_sms_odeslano_mesic = $data_suma_utraceno_mesic["SUMA_UTRACENO"];
                                                       } 
                                                       

//echo $select_na_sumu_utraceno_mesic;

          //DEMO DATA
          if ($sw_id==0) {
             $CelkoveTrzby = number_format(rand(1000,20000), 2, ',', ' ');
             $DnesZaprodej = number_format(rand(1000,10000), 2, ',', ' ');
             $DnesZaSluzby = number_format(rand(1000,20000), 2, ',', ' ');
             $Uctenek_dnes = rand(1,50);
             $suma_sms_odeslano_dnes = rand(1,50);
             $CelkoveMesic = number_format(rand(20000,50000), 2, ',', ' ');
             $MesicCelkemProdej = number_format(rand(10000,30000), 2, ',', ' ');
             $MesicCelkemSluzby = number_format(rand(10000,50000), 2, ',', ' ');
             $Uctenek_Mesic = rand(100,5000);
             $suma_sms_odeslano_mesic = rand(10,500);
           }

                         
       ?>
       
       
           
  <section id="main-content" class="animated fadeInUp hs-dashboard-main">
                <div class="row hs-dashboard-kpis">
                    <div class="col-md-12 col-lg-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="panel panel-solid-success widget-mini">
                                    <div class="panel-body">
                                        <span class="hs-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m7 16 4-5 4 3 5-7"/><path d="M16 7h4v4"/></svg></span>
                                        <span class="total text-center"><?php echo $CelkoveTrzby."&nbsp;".$Mena_Klienta; ?></span>
                                        <span class="title text-center">Tržby dnes</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <span class="hs-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 8h12l1 13H5L6 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg></span>
                                        <span class="total text-center"><?php echo $DnesZaprodej."&nbsp;".$Mena_Klienta; ?></span>
                                        <span class="title text-center">Dnes za prodej</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <span class="hs-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.6 2.6L16.5 9"/></svg></span>
                                        <span class="total text-center"><?php echo $DnesZaSluzby."&nbsp;".$Mena_Klienta ?></span>
                                        <span class="title text-center">Dnes za služby</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-solid-info widget-mini">
                                    <div class="panel-body">
                                        <span class="hs-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6M9 16h3"/></svg></span>
                                        <span class="total text-center"><?php echo $Uctenek_dnes; ?></span>              
                                        <span class="title text-center">Počet&nbsp;účtenek</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="panel panel-solid-info widget-mini">
                                    <div class="panel-body">
                                        <span class="hs-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/><path d="M8 9h8M8 13h5"/></svg></span>
                                        <span class="total text-center"><?php echo $suma_sms_odeslano_dnes ?></span>              
                                        <span class="title text-center">SMS&nbsp;dnes</span>
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
                                        <span class="hs-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 2v4M16 2v4M3 9h18"/><rect x="3" y="4" width="18" height="17" rx="3"/><path d="m8 15 3 3 5-6"/></svg></span>
                                        <span class="total text-center"><?php echo $CelkoveMesic."&nbsp;".$Mena_Klienta ?></span>              
                                        <span class="title text-center">Celkem za měsíc</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <span class="hs-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 8h12l1 13H5L6 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/></svg></span>
                                        <span class="total text-center"><?php echo $MesicCelkemProdej."&nbsp;".$Mena_Klienta; ?></span>
                                        <span class="title text-center">Prodej za měsíc</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <span class="hs-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.6 2.6L16.5 9"/></svg></span>
                                        <span class="total text-center"><?php echo $MesicCelkemSluzby."&nbsp;".$Mena_Klienta ?></span>
                                        <span class="title text-center">Služby za měsíc</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-solid-primary widget-mini">
                                    <div class="panel-body">
                                        <span class="hs-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6M9 16h3"/></svg></span>
                                        <span class="total text-center"><?php echo $Uctenek_Mesic; ?></span>              
                                        <span class="title text-center">Počet&nbsp;účtenek</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="panel panel-solid-primary widget-mini">
                                    <div class="panel-body">
                                        <span class="hs-kpi-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"/><path d="M8 9h8M8 13h5"/></svg></span>
                                        <span class="total text-center"><?php echo $suma_sms_odeslano_mesic ?></span>              
                                        <span class="title text-center">SMS&nbsp;za&nbsp;měsíc</span>
                                    </div>
                                </div>
                            </div>
                            
                        
                        
                        
                        </div>           
                        
                        <!-- <div class="panel panel-default browser-chart">
                            <div class="panel-heading">
                                <h3 class="panel-title">Tržby</h3>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                                        <ul>
                                            <li><i class="fa fa-circle success-color"></i> Zuzka</li>
                                            <li><i class="fa fa-circle primary-color"></i> Jana</li>
                                            <li><i class="fa fa-circle warning-color"></i> Pavla</li>
                                            <li><i class="fa fa-circle info-color"></i> Zdeněk</li>
                                            <li><i class="fa fa-circle default-color"></i> Maruška</li>
                                        </ul>
                                    </div>
                                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                                        <div id="doughnut-canvas-holder">
                                            <canvas id="doughnut" width="137" height="137"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>   --> 
                    </div>  
                </div>
                
                 <div class="row">
     
     
                
                
                     <div class="col-lg-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Tržby za služby</b></font></h3>
                                <div class="reportdate">
                                      
                                    <!-- <b class="caret"></b> -->
                                </div>
                              
                                <div class="actions ">
                                   <!--  <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i> -->
                                    <!-- <i class="fa fa-calendar-o"></i> -->
                                     
                                     <a href="index.php?rok=0"><i class="fa fa-chevron-left"></i></a>
                                    <span><?php echo $SQL_ROK; ?></span>
                                     <a href="index.php?rok=1"><i class="fa fa-chevron-right"></i></a>
                                     
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                     
                                </div>
                            </div>

                            <?php


                                //DEMO DATA
                                if ($sw_id==0) {
                                   $aSeznamStredisek[1] = "Firma A";
                                   $aSeznamStredisek[2] = "Firma B";
                                   $BarvaStrediska[1] = "b4e1ff";
                                   $BarvaStrediska[2] = "b42d00";
                                }




                            ?>


                            <div class="col-lg-12" align="center">
                                            <table border="0">
                                                <?php
                                                   for ($i = 1; $i <= count($aSeznamStredisek); $i++) {
                                                      if ($aSeznamStredisek[$i]!="") {
                                                        echo "<tr>";
                                                        echo "<td><div style=\"width: 15px; height: 15px; background: #$BarvaStrediska[$i]; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;\"></div></td>";
                                                        echo "<td>&nbsp;".$aSeznamStredisek[$i]."&nbsp;&nbsp;&nbsp;</td>";
                                                        echo "<td align='right'>&nbsp;".number_format($SumaKompletStrediskoTrzba[$i], 2, ',', ' ')." ".$Mena_Klienta."</td>";
                                                        echo"</tr>";  
                                                      }
                                                   }
                                                ?>
                                           </table>
                                </div>
                            <div class="panel-body text-center">
                                <div>
                                    <!-- <canvas id="bar1" height="100"></canvas> -->
                                    <canvas id="canvas"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Tržby za prodej</b></font></h3>
                                <div class="reportdate">
                                      
                                    <!-- <b class="caret"></b> -->
                                </div>
                              
                                <div class="actions ">
                                   <!--  <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i> -->
                                    <!-- <i class="fa fa-calendar-o"></i> -->
                                     <a href="index.php?rok=0"><i class="fa fa-chevron-left"></i></a>
                                    <span><?php echo $SQL_ROK; ?></span>
                                     <a href="index.php?rok=1"><i class="fa fa-chevron-right"></i></a>
                                     
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                                
                            </div>
                            <div class="col-lg-12" align="center">
                                            <table border="0">
                                                <?php
                                                   for ($i = 1; $i <= count($aSeznamStredisek); $i++) {
                                                      if ($aSeznamStredisek[$i]!="") {
                                                        echo "<tr>";
                                                        echo "<td><div style=\"width: 15px; height: 15px; background: #$BarvaStrediska[$i]; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;\"></div></td>";
                                                        echo "<td>&nbsp;".$aSeznamStredisek[$i]."&nbsp;&nbsp;&nbsp;</td>";
                                                        echo "<td align='right'>".number_format($SumaKompletStrediskoProdej[$i], 2, ',', ' ')." ".$Mena_Klienta."</td>";
                                                        echo"</tr>";  
                                                      }
                                                   }
                                                ?>
                                           </table>
                            </div>     
                            <div class="panel-body text-center">
                                <div>                                       
                                    <canvas id="canvas2"></canvas>
                                    <!-- <canvas id="bar2" height="100"></canvas> -->
                                </div>
                            </div>
                        </div>
                    </div>             
                 </div>
                




                 <div class="row">
     
     
                    <?php 

                        if ($KreditCELKEM_SUM=="0") {
                            
                            $SirkaVoucheru = "12";
                            $maintainAspectRatio3 = " maintainAspectRatio: false, ";
                            $maintainAspectRatio4 = " maintainAspectRatio: false, ";
                            $StylProCelyRadek = " height=\"300\" ";
                            
                            
                        }else{
                            /*Pro oba*/
                            $SirkaVoucheru = "6";
                            $maintainAspectRatio3 = " maintainAspectRatio: false, ";
                            $maintainAspectRatio4 = " maintainAspectRatio: false, ";
                            $StylProCelyRadek = " height=\"300\" ";
                        }
                     ?>           
                
                     <div class="col-lg-<?php echo $SirkaVoucheru; ?>">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Tržby za Vouchery     </b></font></h3>
                                <div class="reportdate">
                                      
                                    <!-- <b class="caret"></b> -->
                                </div>
                              
                                <div class="actions ">
                                   <!--  <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i> -->
                                    <!-- <i class="fa fa-calendar-o"></i> -->
                                     
                                     <a href="index.php?rok=0"><i class="fa fa-chevron-left"></i></a>
                                    <span><?php echo $SQL_ROK; ?></span>
                                     <a href="index.php?rok=1"><i class="fa fa-chevron-right"></i></a>
                                     
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                     
                                </div>
                            </div>

                            <?php


                                //DEMO DATA
                                if ($sw_id==0) {
                                   $aSeznamStredisek[1] = "Firma A";
                                   $aSeznamStredisek[2] = "Firma B";
                                   $BarvaStrediska[1] = "b4e1ff";
                                   $BarvaStrediska[2] = "b42d00";
                                }




                            ?>


                            <div class="col-lg-12" align="center">
                                            <table border="0">
                                                <?php
                                                   for ($i = 1; $i <= count($aSeznamStredisek); $i++) {
                                                      if ($aSeznamStredisek[$i]!="") {
                                                        echo "<tr>";
                                                        echo "<td><div style=\"width: 15px; height: 15px; background: #$BarvaStrediska[$i]; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;\"></div></td>";
                                                        echo "<td>&nbsp;".$aSeznamStredisek[$i]."&nbsp;&nbsp;&nbsp;</td>";
                                                        echo "<td align='right'>".number_format($SumaKompletStrediskoVoucher[$i], 2, ',', ' ')." ".$Mena_Klienta."</td>";
                                                        echo"</tr>";  
                                                      }
                                                   }
                                                ?>
                                           </table>
                                </div>
                            <div class="panel-body text-center">
                                <div >
                                    <!-- <canvas id="bar1" height="100"></canvas> -->
                                    <canvas id="canvas3" <?php echo $StylProCelyRadek; ?>></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php 

                            //schovat pokud je 0
                            if ($KreditCELKEM_SUM!="0") {
                                
                            

                                         ?>

                                        <div class="col-lg-6" >
                                            <div class="panel panel-default">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title"><font face="tahoma"><b>Tržby za Kredit </b></font></h3>
                                                    <div class="reportdate">
                                                          
                                                        <!-- <b class="caret"></b> -->
                                                    </div>
                                                  
                                                    <div class="actions ">
                                                       <!--  <i class="fa fa-expand"></i>
                                                        <i class="fa fa-chevron-down"></i>
                                                        <i class="fa fa-times"></i> -->
                                                        <!-- <i class="fa fa-calendar-o"></i> -->
                                                         <a href="index.php?rok=0"><i class="fa fa-chevron-left"></i></a>
                                                        <span><?php echo $SQL_ROK; ?></span>
                                                         <a href="index.php?rok=1"><i class="fa fa-chevron-right"></i></a>
                                                         
                                                        <i class="fa fa-expand"></i>
                                                        <i class="fa fa-chevron-down"></i>
                                                        <i class="fa fa-times"></i>
                                                    </div>
                                                    
                                                </div>
                                                <div class="col-lg-12" align="center">
                                                                <table border="0">
                                                                    <?php
                                                                       for ($i = 1; $i <= count($aSeznamStredisek); $i++) {
                                                                          if ($aSeznamStredisek[$i]!="") {
                                                                            echo "<tr>";
                                                                            echo "<td><div style=\"width: 15px; height: 15px; background: #$BarvaStrediska[$i]; -moz-border-radius: 50px; -webkit-border-radius: 50px; border-radius: 50px;\"></div></td>";
                                                                            echo "<td>&nbsp;".$aSeznamStredisek[$i]."&nbsp;&nbsp;&nbsp;</td>";
                                                                            echo "<td align='right'>".number_format($SumaKompletStrediskoKredit[$i], 2, ',', ' ')." ".$Mena_Klienta."</td>";
                                                                            echo"</tr>";  
                                                                          }
                                                                       }
                                                                    ?>
                                                               </table>
                                                </div>     
                                                <div class="panel-body text-center">
                                                    <div >                                       
                                                        <canvas id="canvas4" <?php echo $StylProCelyRadek; ?>></canvas>
                                                        <!-- <canvas id="bar2" height="100"></canvas> -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div> 
                                        <?php 

    
                                
                            }

                     ?>
            
                 </div>
                
                  <?php
                        
                        
                        //Pokud se nezobrazuje paticka neni zaznak o trzbe
                        //posledni zaznam v tabulce
                        $sql_PosledniAktualizace= "SELECT `ts_Datum` FROM `trzby_strediska_$SQL_ROK` WHERE `sw_id` = $sw_id order by ts_Datum Desc LIMIT 1";
                        //echo $sql_PosledniAktualizace;
                        $vysledek_sql_PosledniAktualizace=$mysqli->query($sql_PosledniAktualizace);
                        $data_sql_PosledniAktualizace=MySQLi_Fetch_Array($vysledek_sql_PosledniAktualizace);
                        //$CelkoveTrzby = number_format($data_sql_PosledniAktualizace["TrzbyCelkem"], 2, ',', ' ');
                        $PosledniAktualizace = $data_sql_PosledniAktualizace["ts_Datum"];
                        
                        if ($PosledniAktualizace=="") {
                          $PosledniAktualizace = " nezjištěno ";
                        }
                          
                         //echo $sqldotaz_zjistit_strediska;
                            //echo $PosledniAktualizace;

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
          //DEMO DATA
          if ($sw_id==0) {
             $data1= DemoDataGrafSloupcovy(80000);
             $data2= DemoDataGrafSloupcovy(40000);
             $data3= DemoDataGrafSloupcovy(40000);
             $data4= DemoDataGrafSloupcovy(40000);
          }
        ?>


   <script>
        
        var barChartData = {
            labels: ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
            datasets: [
                 <?php echo $data1; ?>
            ]

        };
        
        
        var barChartData2 = {
            labels: ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
            datasets: [
                 <?php echo $data2; ?>
            ]

        };

        var barChartData3 = {
            labels: ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
            datasets: [
                 <?php echo $data3; ?>
            ]

        };

        var barChartData4 = {
            labels: ["Leden", "Únor", "Březen", "Duben", "Květen", "Červen", "Červenec", "Srpen", "Září", "Říjen", "Listopad", "Prosinec"],
            datasets: [
                 <?php echo $data4; ?>
            ]

        };

        window.onload = function() {
            window.myBar = window.hsCreateDashboardChart("canvas", barChartData);
            window.myBar2 = window.hsCreateDashboardChart("canvas2", barChartData2);
            window.myBar3 = window.hsCreateDashboardChart("canvas3", barChartData3);
            window.myBar4 = window.hsCreateDashboardChart("canvas4", barChartData4);
        };

        
    </script>

