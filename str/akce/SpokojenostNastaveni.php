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



  session_start();

  session_cache_expire(480);

  session_cache_limiter(14400);



  require_once '../../cfg/nastaveni.php';

  require '../strana/_zabezpeceni.php';



  header('Content-Type: text/html; charset=utf-8');



        function encode_header($string) {

          return  '=?UTF-8?B?' . base64_encode($string) . '?=';

        }



        function PosliEmail($komu, $predmet, $zprava)

        {



              $to = $komu;

              $message = "";

              $subject = $predmet;

              $odesilatel = "info@hairsoft.cz";

              

              

              $headers = "From: " . $odesilatel . "\r\n";

              $headers .= "Reply-To: ". $odesilatel. "\r\n";

              

              //$headers .= "CC: susan@example.com\r\n"; // Skryta kopie

              $headers .= "MIME-Version: 1.0\r\n";

              $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

              

              $message .= $zprava;

              

              mail($to, encode_header($subject), $message, $headers);

            

        }



        function OdstraneniDiakritiky($text)

        {

            $return = Str_Replace(

                        Array("À","Á","Â","Ã","Ä","Å","Ç","È","É","Ê","Ë","Ì","Í","Î","Ï","Ñ","Ò","Ó","Ô","Õ","Ö","Ù","Ú","Û","Ü","Ý","ß","à","á","â","ã","ä","å","ç","è","é","ê","ë","ì","í","î","ï","ñ","ò","ó","ô","õ","ö","ù","ú","û","ü","ý","ÿ","Ā","ā","Ă","ă","Ą","ą","Ć","ć","Ĉ","ĉ","Ċ","ċ","Č","č","Ď","ď","Đ","đ","Ē","ē","Ĕ","ĕ","Ė","ė","Ę","ę","Ě","ě","Ĝ","ĝ","Ğ","ğ","Ġ","ġ","Ģ","ģ","Ĥ","ĥ","Ħ","ħ","Ĩ","ĩ","Ī","ī","Ĭ","ĭ","Į","į","İ","ı","Ķ","ķ","ĸ","Ĺ","ĺ","Ļ","ļ","Ľ","ľ","Ŀ","ŀ","Ł","ł","Ń","ń","Ņ","ņ","Ň","ň","ŉ","Ŋ","ŋ","Ō","ō","Ŏ","ŏ","Ő","ő","Ŕ","ŕ","Ŗ","ŗ","Ř","ř","Ś","ś","Ŝ","ŝ","Ş","ş","Š","š","Ţ","ţ","Ť","ť","Ŧ","ŧ","Ũ","ũ","Ū","ū","Ŭ","ŭ","Ů","ů","Ű","ű","Ų","ų","Ŵ","ŵ","Ŷ","ŷ","Ÿ","Ź","ź","Ż","ż","Ž","ž","ſ") ,

                        Array("A","A","A","A","A","A","C","E","E","E","E","I","I","I","I","N","O","O","O","O","O","U","U","U","U","Y","s","a","a","a","a","a","a","c","e","e","e","e","i","i","i","i","n","o","o","o","o","o","u","u","u","u","y","y","A","a","A","a","A","a","C","c","C","c","C","c","C","c","D","d","D","d","E","e","E","e","E","e","E","e","E","e","G","g","G","g","G","g","G","g","H","h","H","h","I","i","I","i","I","i","I","i","I","i","K","k","k","L","l","L","l","L","l","L","l","L","l","N","n","N","n","N","n","N","n","N","O","o","O","o","O","o","R","r","R","r","R","r","S","s","S","s","S","s","S","s","T","t","T","t","T","t","U","u","U","u","U","u","U","u","U","u","U","u","W","w","Y","y","Y","Z","z","Z","z","Z","z","s") ,

                        $text);



                return $return;

        }









  

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



  if (!isset($_POST['ZalozitNovouOtazku']) || is_array($_POST['ZalozitNovouOtazku'])){$_POST['ZalozitNovouOtazku']='';}

  $ZalozitNovouOtazku = htmlspecialchars($_POST['ZalozitNovouOtazku'], ENT_COMPAT);



  if (!isset($_POST['EditaceTextuEmailu']) || is_array($_POST['EditaceTextuEmailu'])){$_POST['EditaceTextuEmailu']='';}

  $EditaceTextuEmailu = htmlspecialchars($_POST['EditaceTextuEmailu'], ENT_COMPAT);



  if (!isset($_POST['HodnoceniTextEmailu']) || is_array($_POST['HodnoceniTextEmailu'])){$_POST['HodnoceniTextEmailu']='';}

  $HodnoceniTextEmailu = htmlspecialchars($_POST['HodnoceniTextEmailu'], ENT_COMPAT);



  if (!isset($_POST['HodnoceniVetaTypHodnoceni']) || is_array($_POST['HodnoceniVetaTypHodnoceni'])){$_POST['HodnoceniVetaTypHodnoceni']='';}

  $HodnoceniVetaTypHodnoceni = htmlspecialchars($_POST['HodnoceniVetaTypHodnoceni'], ENT_COMPAT);



  if (!isset($_POST['HodnoceniVetaPoradi']) || is_array($_POST['HodnoceniVetaPoradi'])){$_POST['HodnoceniVetaPoradi']='';}

  $HodnoceniVetaPoradi = htmlspecialchars($_POST['HodnoceniVetaPoradi'], ENT_COMPAT);



  if (!isset($_POST['HodnoceniVetaPoradiPuvodni']) || is_array($_POST['HodnoceniVetaPoradiPuvodni'])){$_POST['HodnoceniVetaPoradiPuvodni']='';}

  $HodnoceniVetaPoradiPuvodni = htmlspecialchars($_POST['HodnoceniVetaPoradiPuvodni'], ENT_COMPAT);



  if (!isset($_POST['EditaceTextuSMS']) || is_array($_POST['EditaceTextuSMS'])){$_POST['EditaceTextuSMS']='';}

  $EditaceTextuSMS = htmlspecialchars($_POST['EditaceTextuSMS'], ENT_COMPAT);



  if (!isset($_POST['HodnoceniTextSMS']) || is_array($_POST['HodnoceniTextSMS'])){$_POST['HodnoceniTextSMS']='';}

  $HodnoceniTextSMS = htmlspecialchars($_POST['HodnoceniTextSMS'], ENT_COMPAT);

  if (!isset($_POST['EditaceGoogleSMS']) || is_array($_POST['EditaceGoogleSMS'])){$_POST['EditaceGoogleSMS']='';}
  $EditaceGoogleSMS = htmlspecialchars($_POST['EditaceGoogleSMS'], ENT_COMPAT);

  if (!isset($_POST['HodnoceniSMSRezim']) || is_array($_POST['HodnoceniSMSRezim'])){$_POST['HodnoceniSMSRezim']='';}
  $HodnoceniSMSRezim = htmlspecialchars($_POST['HodnoceniSMSRezim'], ENT_COMPAT);

  $GoogleReviewURL = (!isset($_POST['GoogleReviewURL']) || is_array($_POST['GoogleReviewURL'])) ? '' : trim((string) $_POST['GoogleReviewURL']);
  $GoogleReviewTextSMS = (!isset($_POST['GoogleReviewTextSMS']) || is_array($_POST['GoogleReviewTextSMS'])) ? '' : (string) $_POST['GoogleReviewTextSMS'];



  if (!isset($_POST['TestDotaznikuEmailOdeslano']) || is_array($_POST['TestDotaznikuEmailOdeslano'])){$_POST['TestDotaznikuEmailOdeslano']='';}

  $TestDotaznikuEmailOdeslano = htmlspecialchars($_POST['TestDotaznikuEmailOdeslano'], ENT_COMPAT);



  if (!isset($_POST['TestDotaznikuEmail']) || is_array($_POST['TestDotaznikuEmail'])){$_POST['TestDotaznikuEmail']='';}

  $TestDotaznikuEmail = htmlspecialchars($_POST['TestDotaznikuEmail'], ENT_COMPAT);



  if (!isset($_POST['TextEmailu']) || is_array($_POST['TextEmailu'])){$_POST['TextEmailu']='';}

  $TextEmailu = htmlspecialchars($_POST['TextEmailu'], ENT_COMPAT);



  if (!isset($_POST['TestDotaznikuSMSOdeslano']) || is_array($_POST['TestDotaznikuSMSOdeslano'])){$_POST['TestDotaznikuSMSOdeslano']='';}

  $TestDotaznikuSMSOdeslano = htmlspecialchars($_POST['TestDotaznikuSMSOdeslano'], ENT_COMPAT);



  if (!isset($_POST['TestDotaznikuSMS']) || is_array($_POST['TestDotaznikuSMS'])){$_POST['TestDotaznikuSMS']='';}

  $TestDotaznikuSMS = htmlspecialchars($_POST['TestDotaznikuSMS'], ENT_COMPAT);















  function HodnoceniGoogleSMSTableExists(mysqli $mysqli) {
      $result = @$mysqli->query("SHOW TABLES LIKE 'HodnoceniGoogleSMS'");
      return $result && $result->num_rows > 0;
  }

  function HodnoceniGoogleSMSEnsureTable(mysqli $mysqli) {
      $sql = "CREATE TABLE IF NOT EXISTS `HodnoceniGoogleSMS` (
                `HodnoceniGoogleSMSID` INT NOT NULL AUTO_INCREMENT,
                `HodnoceniPobocka` INT NOT NULL,
                `HodnoceniStredisko` INT NOT NULL,
                `HodnoceniGoogleAktivni` TINYINT(1) NOT NULL DEFAULT 0,
                `HodnoceniGoogleURL` VARCHAR(2048) NOT NULL,
                `HodnoceniGoogleTextSMS` TEXT NOT NULL,
                PRIMARY KEY (`HodnoceniGoogleSMSID`),
                UNIQUE KEY `uq_HodnoceniGoogleSMS_pobocka_stredisko` (`HodnoceniPobocka`,`HodnoceniStredisko`)
              ) ENGINE=InnoDB";
      return @$mysqli->query($sql) === true;
  }

  function HodnoceniGoogleSMSDisable(mysqli $mysqli, $pobocka, $stredisko) {
      if (!HodnoceniGoogleSMSTableExists($mysqli)) {
          return true;
      }
      $pobocka = (int) $pobocka;
      $stredisko = (int) $stredisko;
      return @$mysqli->query("UPDATE `HodnoceniGoogleSMS` SET `HodnoceniGoogleAktivni` = 0 WHERE `HodnoceniPobocka` = ".$pobocka." AND `HodnoceniStredisko` = ".$stredisko) !== false;
  }

  if (!isset($_POST['POSTsw_id']) || is_array($_POST['POSTsw_id'])){$_POST['POSTsw_id']='';}

  $POSTsw_id = htmlspecialchars($_POST['POSTsw_id'], ENT_COMPAT);







  $sw_id = $POSTsw_id;







  //Prepocet poradi

  function PrepocitejPoradiProStredisko(mysqli $mysqli,$Stredisko){

    

    $IDStrediska = $_SESSION["VybraneStrediskoID"];

    $IDPobocka = $Stredisko; 

    

      if ($IDStrediska!="" and $IDPobocka !="") {



                    $select_na_hodnoceni= "SELECT * FROM `HodnoceniNastaveni` WHERE `HodnoceniPobocka` =".$IDPobocka ." and `HodnoceniStredisko` = ".$IDStrediska." order by HodnoceniVetaPoradi ASC";

                    if (!$select_na_hodnoceni) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

 

                      $vysledek_na_hodnoceni=$mysqli->query($select_na_hodnoceni);

                      $radku_na_hodnoceni=$vysledek_na_hodnoceni->num_rows; 



                      if ($radku_na_hodnoceni>0) {

                        $Poradi=1;

                              while ($na_hodnoceni=MySQLi_Fetch_Array($vysledek_na_hodnoceni)):   

                                       

                                  $HodnoceniIDdb = $na_hodnoceni["HodnoceniID"];



                                  $sql = "UPDATE `HodnoceniNastaveni` SET `HodnoceniVetaPoradi`=".$Poradi." WHERE `HodnoceniID` = ".$HodnoceniIDdb;

                                  $vysledek_update_vsechny = @$mysqli->query($sql);



                                $Poradi = $Poradi +1;

                              endwhile;

                      }

      }

  }





































       if ($ZalozitNovouOtazku=="1") {

                       if ($sw_id!="") {

                          if ($HodnoceniVeta!="") {



                                  $sql_existuje_otazka= "SELECT count(*) as CNT_otazka FROM `HodnoceniNastaveni` WHERE HodnoceniID =".$HodnoceniID;

                                  //echo $sql_existuje_otazka;

                                  $vysledek_existuje_otazka=$mysqli->query($sql_existuje_otazka);

                                  // MIGRACE PHP 7.4 -> 8.3: původní mysql funkce již není dostupná.

                                  // $sql_existuje_otazka=MySQL_Fetch_Array($vysledek_existuje_otazka);

                                  // Nový kód načte řádek ve stejném výchozím režimu MYSQLI_BOTH.

                                  $sql_existuje_otazka=mysqli_fetch_array($vysledek_existuje_otazka);





                                  if ($HodnoceniID!="") {

                                        //UPDATE

                                         

                                        if ($HodnoceniVetaPoradi < $HodnoceniVetaPoradiPuvodni) {

                                         //Poradi 

                                         $HodnoceniVetaPoradi = $HodnoceniVetaPoradi - 0.5; 

                                        }else{

                                          if ($HodnoceniVetaPoradi == $HodnoceniVetaPoradiPuvodni){

                                            $HodnoceniVetaPoradi = $HodnoceniVetaPoradi; 

                                          }else{

                                            $HodnoceniVetaPoradi = $HodnoceniVetaPoradi + 0.5; 

                                          }

                                         

                                        }





                                         $sql = "UPDATE `HodnoceniNastaveni` SET `HodnoceniVeta`='".$HodnoceniVeta."',`HodnoceniVetaTyp`='".$HodnoceniVetaTyp."',`HodnoceniVetaTypHodnoceni`='".$HodnoceniVetaTypHodnoceni."' ,`HodnoceniVetaPoradi`=".$HodnoceniVetaPoradi." WHERE `HodnoceniID` = ".$HodnoceniID;

                                            $vysledek_update_vsechny = @$mysqli->query($sql);



                                            if ($vysledek_update_vsechny) {

                                               //echo $sql;

                                               PrepocitejPoradiProStredisko($mysqli,$POSTsw_id);



                                               echo "<meta http-equiv=\"refresh\" content=\"0;URL=../index.php?strana=SpokojenostNastaveni\">";

                                               return;

                                             }else{

                                               $error = $mysqli->error;

                                                echo $error;

                                                return;

                                            }





                                  }else {

                                        if ($sql_existuje_otazka["CNT_otazka"]>0) {

                                           echo "Nelze zalozit otázku se stejným jménem!";

                                           echo "<meta http-equiv=\"refresh\" content=\"2;URL=../index.php?strana=SpokojenostNastaveni\">";



                                        }else {

                                            $sql = "INSERT INTO `HodnoceniNastaveni`(`HodnoceniID`, `HodnoceniPobocka`, `HodnoceniVeta`, `HodnoceniVetaTyp`,`HodnoceniStredisko`,`HodnoceniVetaTypHodnoceni`,`HodnoceniVetaPoradi`) VALUES (NULL,$sw_id,'".$HodnoceniVeta."','".$HodnoceniVetaTyp."',".$_SESSION["VybraneStrediskoID"].",'".$HodnoceniVetaTypHodnoceni."',$HodnoceniVetaPoradi)";

                                              //echo $sql;

                                            $vysledek_zalozeni_vsechny = @$mysqli->query($sql);



                                            if ($vysledek_zalozeni_vsechny) {

                                               //echo $sql;

                                               echo "<meta http-equiv=\"refresh\" content=\"0;URL=../index.php?strana=SpokojenostNastaveni\">";

                                               return;

                                             }else{

                                               $error = $mysqli->error;

                                                echo $error;

                                                return;

                                            }

                                        }

                                  }







                          }else {

                                echo "Otázka nemá text.";

                          }

                       }

       }



       if ($smazat_otazku=="1") {

                       if ($HodnoceniID!="" and $sw_id!="") {

                                 //jednotlivci

                                 $sql1 = "DELETE FROM `HodnoceniNastaveni` WHERE `HodnoceniID` = ".$HodnoceniID;

                                 $vysledek_smazani_otazky = @$mysqli->query($sql1);

                                 //echo $sql1;



                                  if ($vysledek_smazani_otazky) {

                                     //echo $sql;

                                     echo "<meta http-equiv=\"refresh\" content=\"0;URL=../index.php?strana=SpokojenostNastaveni\">";

                                     return;

                                   }else{

                                     $error = $mysqli->error;

                                      echo $error;

                                      return;

                                  }

                       }

       }



       //Email

       if ($TestDotaznikuEmailOdeslano=="1") {

                       if ($TestDotaznikuEmail!="" and $TextEmailu !="" ) {

                             //$TextEmailu = nl2br($TextEmailu);

                             $TextEmailuKomplet = "<html><body>".$TextEmailu."</body></html>";

                             $TextEmailuKomplet = htmlspecialchars_decode($TextEmailuKomplet );

                             //echo "hu".$TextEmailuKomplet;

                             PosliEmail($TestDotaznikuEmail, "Dotazník spokojenosti - Test", $TextEmailuKomplet);

                             

                       }

       }







       //SMS

       if ($TestDotaznikuSMSOdeslano=="1") {

                       if ($TestDotaznikuSMS!="" and $HodnoceniTextSMS !="" ) {

                             

                             



                            $select_na_existenci_hash= "SELECT * FROM `sw_info` WHERE `sw_id` = ".$POSTsw_id;

                                      if (!$select_na_existenci_hash) { die('Chyba pripojeni do DB!');}

                                        $vysledek_na_akci_hash=$mysqli->query("$select_na_existenci_hash");

                                        $radku_na_akci_hash=$vysledek_na_akci_hash->num_rows;

                                        $data_hash=MySQLi_Fetch_Array($vysledek_na_akci_hash);                                                              

                                  

                                   

                                         $mobil = $TestDotaznikuSMS;

                                         $Prijemce_TextSMS = $HodnoceniTextSMS;

                                         $sms_poznamka = "Dotaznik";

                                         $rez_objId = ""; //id objednavky

                                         





                                         //osetreni z rezervacniho systemu chodi cisla v 9 mistenem formatu 606467039

                                         $mobil = trim($mobil);

                                         $mobil = str_replace(' ', '', $mobil);

                                         

                                        

                                                    if (strlen($mobil)==9) {

                                                       /*bez predvolacky*/

                                                       if (substr($mobil,0 ,1)==9) {

                                                           /*Slovak*/

                                                           $mobil = "421".$mobil;

                                                       }else{

                                                           /*čech*/

                                                           $mobil = "420".$mobil;

                                                       }

                                                     }elseif (strlen($mobil)==10) {

                                                        /* 10 Slovak 421*/ 

                                                        if (strlen($mobil)==10) {

                                                          $mobil = "421".substr($mobil,1 ,9) ;

                                                        }

                                                     }else{

                                                        /*Slovak+cech s predvolackou*/

                                                     }



                                         

                                              //'odstraneni +'

                                              $mobil = str_replace('+', '', $mobil);

                                              $DatumCas = date('d.m.Y H:i'); //odeslat hned

                                              $date = DateTime::createFromFormat('d.m.Y H:i', $DatumCas);

                                              $dateToBeInserted = $date->format('Y-m-d H:i:s');

                                              

                                                 //co cim kde

                                                 // hlavni REPLACE                                                 



                                                 //replace apostrofu

                                                 $Prijemce_TextSMS= str_replace("'" , "''", $Prijemce_TextSMS);

                                                 $Prijemce_TextSMS= str_replace("´" , "´´", $Prijemce_TextSMS);

                                                 $Prijemce_TextSMS= str_replace("“" , "'", $Prijemce_TextSMS);

                                                 $Prijemce_TextSMS= str_replace("”" , "'", $Prijemce_TextSMS);

                                                 $Prijemce_TextSMS= str_replace("|" , "", $Prijemce_TextSMS);

                                                 $Prijemce_TextSMS= str_replace("°" , "", $Prijemce_TextSMS);

                                                 $Prijemce_TextSMS= str_replace("~" , "", $Prijemce_TextSMS);



                                                 //yakayane ynakz ktere mame programove osetrenz vztahuji se na rezervace

                                                 $Prijemce_TextSMS= str_replace("&amp;" , "&", $Prijemce_TextSMS);

                                                 $Prijemce_TextSMS= str_replace("&quot;" , "", $Prijemce_TextSMS);

                                                 $Prijemce_TextSMS= str_replace("&apos;" , "", $Prijemce_TextSMS);

                                                 $Prijemce_TextSMS= str_replace("&gt;" , "", $Prijemce_TextSMS);

                                                 $Prijemce_TextSMS= str_replace("&lt;" , "", $Prijemce_TextSMS);

                                                 $Prijemce_TextSMS= str_replace("&nbsp;" , "", $Prijemce_TextSMS);

                                                 $Prijemce_TextSMS= str_replace("&euro;" , "EUR", $Prijemce_TextSMS);



                                                 //Zakazane znaky 

                                                 $Prijemce_TextSMS = str_replace("&","_38_",$Prijemce_TextSMS);

                                                 $Prijemce_TextSMS=OdstraneniDiakritiky($Prijemce_TextSMS);                                                                                              

                                                 $Prijemce_TextSMS=preg_replace('~[\r\n\t]+~', '', $Prijemce_TextSMS);         // odstrani znaky nakonci

                                                                                               

                                                    

                                                    //Pokud je blbost stornuj ale posli do systemu

                                                    if (strlen($mobil) < 9) {

                                                      $je_blokovan_stav = 9;

                                                      $sms_poznamka =  $sms_poznamka . "Stornováno z nesmysleného čísla: ".$mobil;

                                                    }



                                                  

                                                  $sql = "INSERT INTO `sms_odeslane_fronta` (`sms_od_id`, `sms_od_date`, `sms_od_pcid`, `sms_od_dc`, `sms_od_uzivatel`, `sms_od_text`, `sms_mobil`, `sms_stav`, `platce_id`, `platce_jmeno`, `platce_prijmeni`, `platce_pohlavi`, `sms_skupina_ID`, `sms_sablona_typ` ,`sms_kdy_odeslat`, `obj_id_obj`,`sms_zdroj`,`sms_poznamka`,`sms_stat`) VALUES (NULL, CURRENT_TIMESTAMP, '', '', '', '$Prijemce_TextSMS', '$mobil', '0', '', '$jmeno', '', '','$data_hash[sw_skupina_id]','D','$dateToBeInserted', '$rez_objId','D','$sms_poznamka','$data_hash[sms_id_statu]');";

                                                    $vysledek_ulozeni_sms_do_fronty = @$mysqli->query($sql);

 

                                                      if ($vysledek_ulozeni_sms_do_fronty) {

                                                         //echo "sms odeslana";

                                                      }else{

                                                         //echo "sms error";

                                                      }



                       }

                       echo "<meta http-equiv=\"refresh\" content=\"0;URL=../index.php?strana=SpokojenostNastaveni\">";

                    return;

       }





















      if ($EditaceTextuEmailu=="1") {

                       if ($sw_id!="") {

                          if ($HodnoceniTextEmailu!="") {



                                        //UPDATE

                                         $sql = "UPDATE `HodnoceniTextEmail` SET `HodnoceniTextEmailu`='".$HodnoceniTextEmailu."' WHERE `HodnoceniStredisko` = ".$_SESSION["VybraneStrediskoID"]." and `HodnoceniPobocka` =  ".$sw_id;

                                              

                                            $vysledek_update_vsechny = @$mysqli->query($sql);



                                            if ($vysledek_update_vsechny) {

                                               //echo $sql;

                                               echo "<meta http-equiv=\"refresh\" content=\"0;URL=../index.php?strana=SpokojenostNastaveni\">";

                                               return;

                                             }else{

                                               $error = $mysqli->error;

                                                echo $error;

                                                return;

                                            }



                          }else {

                                echo "Email nemá text.";

                          }

                       }

       }





       if ($EditaceTextuSMS=="1") {

                       if ($sw_id!="") {

                          if ($HodnoceniTextSMS!="") {



                                        //UPDATE

                                         $sql = "UPDATE `HodnoceniTextSMS` SET `HodnoceniTextSMS`='".$HodnoceniTextSMS."' WHERE `HodnoceniStredisko` = ".$_SESSION["VybraneStrediskoID"]." and `HodnoceniPobocka` =  ".$sw_id;

                                              

                                            $vysledek_update_vsechny = @$mysqli->query($sql);



                                            if ($vysledek_update_vsechny) {

                                               // V183: puvodni SMS update zustava prvni a nezavisly na Google tabulce.
                                               if ($HodnoceniSMSRezim === "internal") {
                                                 if (!HodnoceniGoogleSMSDisable($mysqli, $sw_id, $_SESSION["VybraneStrediskoID"])) {
                                                   echo "Text interní SMS byl uložen, ale nepodařilo se přepnout cíl z Google recenzí zpět na interní hodnocení.";
                                                   return;
                                                 }
                                               }

                                               echo "<meta http-equiv=\"refresh\" content=\"0;URL=../index.php?strana=SpokojenostNastaveni\">";

                                               return;

                                             }else{

                                               $error = $mysqli->error;

                                                echo $error;

                                                return;

                                            }



                          }else {

                                echo "SMS nemá text.";

                          }

                       }

       }













       /* V183: Google recenze - samostatna SMS konfigurace.
          Puvodni HodnoceniTextSMS se zde nikdy nemeni. */
       if ($EditaceGoogleSMS=="1") {
          $googlePobocka = (int) $sw_id;
          $googleStredisko = isset($_SESSION["VybraneStrediskoID"]) ? (int) $_SESSION["VybraneStrediskoID"] : 0;
          $googleUrl = trim($GoogleReviewURL);
          $googleText = trim($GoogleReviewTextSMS);

          $googleUrlParts = @parse_url($googleUrl);
          $googleUrlValid = is_array($googleUrlParts) && isset($googleUrlParts['scheme']) && in_array(strtolower($googleUrlParts['scheme']), array('http','https'), true) && filter_var($googleUrl, FILTER_VALIDATE_URL);

          if ($googlePobocka <= 0 || $googleStredisko <= 0) {
             echo "Nelze uložit Google recenze: není vybrána pobočka nebo středisko.";
             echo "<meta http-equiv=\"refresh\" content=\"3;URL=../index.php?strana=SpokojenostNastaveni\">";
             return;
          }
          if (!$googleUrlValid) {
             echo "Nelze uložit Google recenze: zadejte platný odkaz začínající http:// nebo https://.";
             echo "<meta http-equiv=\"refresh\" content=\"3;URL=../index.php?strana=SpokojenostNastaveni\">";
             return;
          }
          if ($googleText === '' || strpos($googleText, '%ODKAZ_SPOKOJENOST%') === false) {
             echo "Nelze uložit Google recenze: text SMS musí obsahovat %ODKAZ_SPOKOJENOST%.";
             echo "<meta http-equiv=\"refresh\" content=\"3;URL=../index.php?strana=SpokojenostNastaveni\">";
             return;
          }
          if (!HodnoceniGoogleSMSEnsureTable($mysqli)) {
             echo "Google nastavení se nepodařilo připravit v databázi. Interní hodnocení zůstává beze změny.";
             echo "<meta http-equiv=\"refresh\" content=\"3;URL=../index.php?strana=SpokojenostNastaveni\">";
             return;
          }

          $stmtGoogle = $mysqli->prepare("INSERT INTO `HodnoceniGoogleSMS` (`HodnoceniPobocka`,`HodnoceniStredisko`,`HodnoceniGoogleAktivni`,`HodnoceniGoogleURL`,`HodnoceniGoogleTextSMS`) VALUES (?,?,1,?,?) ON DUPLICATE KEY UPDATE `HodnoceniGoogleAktivni`=1, `HodnoceniGoogleURL`=VALUES(`HodnoceniGoogleURL`), `HodnoceniGoogleTextSMS`=VALUES(`HodnoceniGoogleTextSMS`)");
          if (!$stmtGoogle) {
             echo $mysqli->error;
             return;
          }
          $stmtGoogle->bind_param("iiss", $googlePobocka, $googleStredisko, $googleUrl, $GoogleReviewTextSMS);
          $googleSaved = $stmtGoogle->execute();
          $stmtGoogle->close();

          if ($googleSaved) {
             echo "<meta http-equiv=\"refresh\" content=\"0;URL=../index.php?strana=SpokojenostNastaveni\">";
             return;
          }

          echo $mysqli->error;
          return;
       }


    echo "<meta http-equiv=\"refresh\" content=\"2;URL=../index.php?strana=SpokojenostNastaveni\">";

   return;





?>

