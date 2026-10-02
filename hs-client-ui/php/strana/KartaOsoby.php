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

  require_once HS_CLIENT_UI_ROOT . '/str/strana/_chatGPT.php';



  // Prava na stranku aby neslo podstrcit stranku s GET URL v oblibenych

  if (($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("NovyZakaznik", $_SESSION["SeznamPravPoduzivatele"])=="0") {
    echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=PrazdnaStrana\">";

    exit;

  }

  // Prava na stranku



  require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';

  require_once HS_CLIENT_UI_ROOT . '/hs-client-ui/php/multi_company_auth.php';

  require_once HS_CLIENT_UI_ROOT . '/hs-client-ui/php/customer-copy-test-lib.php';

  require_once HS_CLIENT_UI_ROOT . '/hs-client-ui/php/customer-dependent-sync.php';

  require_once HS_CLIENT_UI_ROOT . '/hs-client-ui/php/strana/_programs_data.php';

  

  $jmenoStranky = "Karta osoby";// Dole se prepise !!!

  $jmenoStrankyPopis = "Detailní informace o osobě";

  

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



  $sw_id = $_SESSION["pobocka_id"];



  if ($_SESSION["k_id"]=="10") {

     //$Uzivatel_ID =11;

     //$sw_id = 1081;

  }

  //$Uzivatel_ID = 11;

  



  function cleanUsername($username) {

    $username = trim($username);

    $username = ltrim($username, '@#');

    return $username;

  }



  function cleanUrl($url) {

    $url = trim($url);



    // pokud nezačíná http/https, přidáme https://

    if (!empty($url) && !preg_match("~^(?:f|ht)tps?://~i", $url)) {

        $url = "https://" . $url;

    }



    return $url;

  }













  if ($sw_id==3316) {

    $sw_id = 4181; // PODSTRCENENEJ ZDENDA !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

  }









  //GET

  if (!isset($_GET['osoba_guid']) || is_array($_GET['osoba_guid'])){$_GET['osoba_guid']='';}

  $osoba_guid = htmlspecialchars($_GET['osoba_guid'], ENT_COMPAT);

  $osoba_guid_get = $osoba_guid;



  if (!isset($_GET['fotoaparat']) || is_array($_GET['fotoaparat'])){$_GET['fotoaparat']='';}

  $fotoaparat = htmlspecialchars($_GET['fotoaparat'], ENT_COMPAT);



  if (!isset($_GET['NastavitJakoProfilovku']) || is_array($_GET['NastavitJakoProfilovku'])){$_GET['NastavitJakoProfilovku']='';}

  $NastavitJakoProfilovku = htmlspecialchars($_GET['NastavitJakoProfilovku'], ENT_COMPAT);



  if (!isset($_GET['SmazatSoubor']) || is_array($_GET['SmazatSoubor'])){$_GET['SmazatSoubor']='';}

  $SmazatSoubor = htmlspecialchars($_GET['SmazatSoubor'], ENT_COMPAT);



  if (!isset($_GET['JmenoSouboru']) || is_array($_GET['JmenoSouboru'])){$_GET['JmenoSouboru']='';}

  $JmenoSouboru = htmlspecialchars($_GET['JmenoSouboru'], ENT_COMPAT);



  if (!isset($_GET['OtocitSoubor']) || is_array($_GET['OtocitSoubor'])){$_GET['OtocitSoubor']='';}

  $OtocitSoubor = htmlspecialchars($_GET['OtocitSoubor'], ENT_COMPAT);



  if (!isset($_GET['EditaceZaznamZeSystemu']) || is_array($_GET['EditaceZaznamZeSystemu'])){$_GET['EditaceZaznamZeSystemu']='';}

  $EditaceZaznamZeSystemu = htmlspecialchars($_GET['EditaceZaznamZeSystemu'], ENT_COMPAT);



  if (!isset($_GET['SmazaniZaznamZeSystemu']) || is_array($_GET['SmazaniZaznamZeSystemu'])){$_GET['SmazaniZaznamZeSystemu']='';}

  $SmazaniZaznamZeSystemu = htmlspecialchars($_GET['SmazaniZaznamZeSystemu'], ENT_COMPAT);



  if (!isset($_GET['timelineIDGET']) || is_array($_GET['timelineIDGET'])){$_GET['timelineIDGET']='';}

  $timelineIDGET = htmlspecialchars($_GET['timelineIDGET'], ENT_COMPAT);



  











  if (!isset($_GET['NavratGalerie']) || is_array($_GET['NavratGalerie'])){$_GET['NavratGalerie']='';}

  $NavratGalerie = htmlspecialchars($_GET['NavratGalerie'], ENT_COMPAT);



  if (!isset($_GET['NavratTimeline']) || is_array($_GET['NavratTimeline'])){$_GET['NavratTimeline']='';}

  $NavratTimeline = htmlspecialchars($_GET['NavratTimeline'], ENT_COMPAT);



  if (!isset($_GET['NavratSoubory']) || is_array($_GET['NavratSoubory'])){$_GET['NavratSoubory']='';}

  $NavratSoubory = htmlspecialchars($_GET['NavratSoubory'], ENT_COMPAT);

  if (!isset($_GET['NavratProgramy']) || is_array($_GET['NavratProgramy'])){$_GET['NavratProgramy']='';}

  $NavratProgramy = htmlspecialchars($_GET['NavratProgramy'], ENT_COMPAT);

  if (!isset($_GET['hs_program_id']) || is_array($_GET['hs_program_id'])){$_GET['hs_program_id']='';}

  $hsProgramRequestedId = max(0, (int) $_GET['hs_program_id']);









  // V201: vzdy inicializovat stav zalozek a pri explicitnim navratu dat prednost Souborum.

  // Tim stary parametr NavratTimeline nemuze po uploadu prebit pozadovanou zalozku Soubory.

  $AktivniZalozkaGalerie = 0;

  $AktivniZalozkaTimeline = 0;

  $AktivniZalozkaSoubory = 0;

  $AktivniZalozkaProgramy = 0;



  if ($NavratSoubory=="1") {

    $AktivniZalozkaSoubory = 1;

  }elseif ($NavratGalerie=="1") {

    $AktivniZalozkaGalerie = 1;

  }elseif ($NavratTimeline=="1") {

    $AktivniZalozkaTimeline = 1;

  }elseif ($NavratProgramy=="1") {

    $AktivniZalozkaProgramy = 1;

  }  

  

  if ($osoba_guid=="") {

    if (!isset($_POST['osoba_guid']) || is_array($_POST['osoba_guid'])){$_POST['osoba_guid']='';}

    $osoba_guid = htmlspecialchars($_POST['osoba_guid'], ENT_COMPAT);

  }

  

  // V208: nejdrive spolehlive svazeme cilovou kopii s aktualnim DB radkem zakaznika.
  // Diky tomu se rucni rezim neztrati ani pokud legacy HairSoft pri prvnim syncu zmeni GUID.
  $hsCustomerCopyEarlyIdentity = null;
  $hsCustomerCopyTargetEarlyLog = null;
  if ($osoba_guid!="" && isset($hsMultiReady) && $hsMultiReady) {
    $hsCustomerCopyEarlyIdentity = hsCustomerCopyTestCurrentIdentity($mysqli);
    if ($hsCustomerCopyEarlyIdentity) {
      $hsCustomerCopyTargetEarlyLog = hsCustomerCopyTestTargetLog($mysqli, (int) $hsCustomerCopyEarlyIdentity['owner_id'], $osoba_guid);
      if ($hsCustomerCopyTargetEarlyLog && !hsCustomerCopyTestIdentityAllowedOnBranch($mysqli, $hsCustomerCopyEarlyIdentity, (int) $hsCustomerCopyTargetEarlyLog['target_branch_id'])) {
        $hsCustomerCopyTargetEarlyLog = null;
      }
      if ($hsCustomerCopyTargetEarlyLog && function_exists('hsCustomerCopyTestPurgeUnexpectedPendingTimeline')) {
        hsCustomerCopyTestPurgeUnexpectedPendingTimeline($mysqli, $hsCustomerCopyTargetEarlyLog, $osoba_guid);
      }
    }
  }

  // V202/V208: ochrana navaznych dat pred pouzitim docasneho HairSoft ID 11111111.
  if ($osoba_guid!="") {
    hsCustomerSyncHoldSynchronizeGuid($mysqli, $osoba_guid);
  }

  $hsCustomerWaitForHairSoftId = ($osoba_guid!="" && hsCustomerSyncHoldShouldWait($mysqli, $osoba_guid));

  $hsCustomerManualCopyEarlyState = $hsCustomerCopyTargetEarlyLog ? hsCustomerSyncHoldManualState($mysqli, $osoba_guid, (int) $hsCustomerCopyTargetEarlyLog['id']) : null;

  $hsCustomerManualConfirmed = ($hsCustomerManualCopyEarlyState && !empty($hsCustomerManualCopyEarlyState['manual_confirmed']));

  $hsCustomerManualPending = ($hsCustomerManualCopyEarlyState && !$hsCustomerManualConfirmed);

  $hsCustomerManualTimelineHold = ($hsCustomerManualCopyEarlyState && (!$hsCustomerManualConfirmed || (int) $hsCustomerManualCopyEarlyState['timeline_hold'] > 0));

  $hsPhotoSyncState = ($hsCustomerWaitForHairSoftId || $hsCustomerManualPending) ? (int) HS_CUSTOMER_PHOTO_HOLD : 0;

  $hsFileSyncState = ($hsCustomerWaitForHairSoftId || $hsCustomerManualPending) ? (int) HS_CUSTOMER_FILE_HOLD : 0;

  $hsTimelineSyncFlag = ($hsCustomerWaitForHairSoftId || $hsCustomerManualTimelineHold) ? HS_CUSTOMER_TIMELINE_HOLD : 'N';

  

  if (!isset($_POST['akce']) || is_array($_POST['akce'])){$_POST['akce']='';}

  $akce = htmlspecialchars($_POST['akce'], ENT_COMPAT);



//POST

  if (!isset($_POST['lidi_hs_name']) || is_array($_POST['lidi_hs_name'])){$_POST['lidi_hs_name']='';}

  $lidi_hs_name = htmlspecialchars($_POST['lidi_hs_name'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_surname']) || is_array($_POST['lidi_hs_surname'])){$_POST['lidi_hs_surname']='';}

  $lidi_hs_surname = htmlspecialchars($_POST['lidi_hs_surname'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_title']) || is_array($_POST['lidi_hs_title'])){$_POST['lidi_hs_title']='';}

  $lidi_hs_title = htmlspecialchars($_POST['lidi_hs_title'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_genre']) || is_array($_POST['lidi_hs_genre'])){$_POST['lidi_hs_genre']='';}

  $lidi_hs_genre = htmlspecialchars($_POST['lidi_hs_genre'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_email']) || is_array($_POST['lidi_hs_email'])){$_POST['lidi_hs_email']='';}

  $lidi_hs_email = htmlspecialchars($_POST['lidi_hs_email'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_street']) || is_array($_POST['lidi_hs_street'])){$_POST['lidi_hs_street']='';}

  $lidi_hs_street = htmlspecialchars($_POST['lidi_hs_street'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_city']) || is_array($_POST['lidi_hs_city'])){$_POST['lidi_hs_city']='';}

  $lidi_hs_city = htmlspecialchars($_POST['lidi_hs_city'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_zip']) || is_array($_POST['lidi_hs_zip'])){$_POST['lidi_hs_zip']='';}

  $lidi_hs_zip = htmlspecialchars($_POST['lidi_hs_zip'], ENT_COMPAT);



  if (!isset($_POST['cameraFileInput']) || is_array($_POST['cameraFileInput'])){$_POST['cameraFileInput']='';}

  $cameraFileInput = htmlspecialchars($_POST['cameraFileInput'], ENT_COMPAT);



  if (!isset($_POST['cameraFileInput2']) || is_array($_POST['cameraFileInput2'])){$_POST['cameraFileInput2']='';}

  $cameraFileInput2 = htmlspecialchars($_POST['cameraFileInput2'], ENT_COMPAT);

  

  if ($lidi_hs_zip=="") {

    $lidi_hs_zip="";

  }

  

  if (!isset($_POST['lidi_hs_phone']) || is_array($_POST['lidi_hs_phone'])){$_POST['lidi_hs_phone']='';}

  $lidi_hs_phone = htmlspecialchars($_POST['lidi_hs_phone'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_cell']) || is_array($_POST['lidi_hs_cell'])){$_POST['lidi_hs_cell']='';}

  $lidi_hs_cell = htmlspecialchars($_POST['lidi_hs_cell'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_BirthDay']) || is_array($_POST['lidi_hs_BirthDay'])){$_POST['lidi_hs_BirthDay']='';}

  $lidi_hs_BirthDay = htmlspecialchars($_POST['lidi_hs_BirthDay'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_firm_name']) || is_array($_POST['lidi_hs_firm_name'])){$_POST['lidi_hs_firm_name']='';}

  $lidi_hs_firm_name = htmlspecialchars($_POST['lidi_hs_firm_name'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_firm_register_number']) || is_array($_POST['lidi_hs_firm_register_number'])){$_POST['lidi_hs_firm_register_number']='';}

  $lidi_hs_firm_register_number = htmlspecialchars($_POST['lidi_hs_firm_register_number'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_www']) || is_array($_POST['lidi_hs_www'])){$_POST['lidi_hs_www']='';}

  $lidi_hs_www = htmlspecialchars($_POST['lidi_hs_www'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_skype']) || is_array($_POST['lidi_hs_skype'])){$_POST['lidi_hs_skype']='';}

  $lidi_hs_skype = htmlspecialchars($_POST['lidi_hs_skype'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_facebook']) || is_array($_POST['lidi_hs_facebook'])){$_POST['lidi_hs_facebook']='';}

  $lidi_hs_facebook = htmlspecialchars($_POST['lidi_hs_facebook'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_note']) || is_array($_POST['lidi_hs_note'])){$_POST['lidi_hs_note']='';}

  $lidi_hs_note = htmlspecialchars($_POST['lidi_hs_note'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_card']) || is_array($_POST['lidi_hs_card'])){$_POST['lidi_hs_card']='';}

  $lidi_hs_card = htmlspecialchars($_POST['lidi_hs_card'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_NoSMS']) || is_array($_POST['lidi_hs_NoSMS'])){$_POST['lidi_hs_NoSMS']='';}

  $lidi_hs_NoSMS = htmlspecialchars($_POST['lidi_hs_NoSMS'], ENT_COMPAT);

  

  if (!isset($_POST['lidi_hs_loyalityPoints']) || is_array($_POST['lidi_hs_loyalityPoints'])){$_POST['lidi_hs_loyalityPoints']='';}

  $lidi_hs_loyalityPoints = htmlspecialchars($_POST['lidi_hs_loyalityPoints'], ENT_COMPAT);

//POST FORM                                                           

                           

  if (!isset($_POST['lidi_combo_pobocka']) || is_array($_POST['lidi_combo_pobocka'])){$_POST['lidi_combo_pobocka']='';}

  $lidi_combo_pobocka = htmlspecialchars($_POST['lidi_combo_pobocka'], ENT_COMPAT);



  if (!isset($_POST['TimelinePoznamka']) || is_array($_POST['TimelinePoznamka'])){$_POST['TimelinePoznamka']='';}

  $TimelinePoznamka = htmlspecialchars($_POST['TimelinePoznamka'], ENT_COMPAT);



  if (!isset($_POST['k_poduzivatele_id']) || is_array($_POST['k_poduzivatele_id'])){$_POST['k_poduzivatele_id']='';}

  $k_poduzivatele_id = htmlspecialchars($_POST['k_poduzivatele_id'], ENT_COMPAT);



  if (!isset($_POST['timelineID']) || is_array($_POST['timelineID'])){$_POST['timelineID']='';}

  $timelineID = htmlspecialchars($_POST['timelineID'], ENT_COMPAT);



  if (!isset($_POST['lidi_sw_id']) || is_array($_POST['lidi_sw_id'])){$_POST['lidi_sw_id']='';}

  $lidi_sw_id = htmlspecialchars($_POST['lidi_sw_id'], ENT_COMPAT);





  if (!isset($_POST['lidi_hs_pojistovnaGUID']) || is_array($_POST['lidi_hs_pojistovnaGUID'])){$_POST['lidi_hs_pojistovnaGUID']='';}

  $lidi_hs_pojistovnaGUID = htmlspecialchars($_POST['lidi_hs_pojistovnaGUID'], ENT_COMPAT);



  if (!isset($_POST['lidi_hs_ostatni']) || is_array($_POST['lidi_hs_ostatni'])){$_POST['lidi_hs_ostatni']='';}

  $lidi_hs_ostatni = htmlspecialchars($_POST['lidi_hs_ostatni'], ENT_COMPAT);



  if (!isset($_POST['lidi_hs_rc']) || is_array($_POST['lidi_hs_rc'])){$_POST['lidi_hs_rc']='';}

  $lidi_hs_rc = htmlspecialchars($_POST['lidi_hs_rc'], ENT_COMPAT);



/*Soubory*/



  if (!isset($_POST['guid_souboru']) || is_array($_POST['guid_souboru'])){$_POST['guid_souboru']='';}

  $guid_souboru = htmlspecialchars($_POST['guid_souboru'], ENT_COMPAT);



  if (!isset($_POST['ulozeny_nazev']) || is_array($_POST['ulozeny_nazev'])){$_POST['ulozeny_nazev']='';}

  $ulozeny_nazev = htmlspecialchars($_POST['ulozeny_nazev'], ENT_COMPAT);







 





 







/*GET AKCE*/

 if ($JmenoSouboru!="") {

      



        if ($NastavitJakoProfilovku==1) {

          

          //Odmazat informaci o profilovce jinemu souboru 

           $sql_pi = "UPDATE `klient_lidi_obrazky` SET `obrazek_profilovka`=0 WHERE `obrazek_osoba_GUID` = '".$osoba_guid."' ";

           $vysledek_smazat_profilovku_informaci = @$mysqli->query($sql_pi);



          //Nastavit jako profilovku a nastavit jako nestazeno

           $sql_np = "UPDATE `klient_lidi_obrazky` SET `obrazek_stazeno`=".$hsPhotoSyncState.",`obrazek_profilovka`=1 WHERE `obrazek_osoba_GUID` = '".$osoba_guid."' and `obrazek_jmeno_GUID` = '".$JmenoSouboru."'";

           $vysledek_profilova = @$mysqli->query($sql_np);



           //Zalozky

           $AktivniZalozkaGalerie = 1;



        }



        if ($SmazatSoubor==1) {

          // V202: fotografie u zakaznika bez realneho HairSoft ID jeste nikdy nebyla v PC.

          // V takovem pripade se metadata odstrani rovnou; jinak zustava puvodni mazaci synchronizace.

          if ($hsCustomerWaitForHairSoftId) {

            $sql_de = "DELETE FROM `klient_lidi_obrazky` WHERE `obrazek_osoba_GUID` = '".$osoba_guid."' and `obrazek_jmeno_GUID` = '".$JmenoSouboru."'";

          }else{

            $sql_de = "UPDATE `klient_lidi_obrazky` SET `obrazek_stazeno`=0,`obrazek_smazan` = 1 WHERE `obrazek_osoba_GUID` = '".$osoba_guid."' and `obrazek_jmeno_GUID` = '".$JmenoSouboru."'";

          }

           $vysledek_profilova = @$mysqli->query($sql_de);



                //Smazat obrazek 

                $CestaIMG = "strana/galerie/";

                @unlink($CestaIMG.$JmenoSouboru);

                @unlink($CestaIMG."m".$JmenoSouboru);



                //Zalozky

                $AktivniZalozkaGalerie = 1;



        }



 }





// ================= UPLOAD LOGIKA =================

$zprava = "";



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {



    $allowed = [

    'pdf',   // dokumenty

    'jpg',   // obrázky

    'jpeg',  // obrázky

    'png',   // obrázky

    'gif',   // obrázky

    'csv',   // data

    'doc',   // starý Word

    'docx',  // Word

    'xls',   // Excel

    'xlsx',  // Excel

    'txt'    // textové soubory

    ];



    $maxSize = 30 * 1024 * 1024;



    $file = $_FILES['file'];



    if ($file['error'] !== UPLOAD_ERR_OK) {

        $chyba = "Chyba uploadu";

    } else {



        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));



        if (!in_array($ext, $allowed)) {

            $chyba = "Nepovolený typ souboru";

        } elseif ($file['size'] > $maxSize) {

            $chyba = "Soubor je příliš velký";

        } else {



            $guid = bin2hex(random_bytes(16));



            $originalName = basename($file['name']);

            $safeName = preg_replace("/[^a-zA-Z0-9._-]/", "_", $originalName);

            $newName = $guid . "_" . $safeName;



            $uploadDir = HS_CLIENT_UI_ROOT . "/str/strana/Soubory/";



            if (!is_dir($uploadDir)) {

                mkdir($uploadDir, 0777, true);

            }



            $path = $uploadDir . $newName;



            if (move_uploaded_file($file['tmp_name'], $path)) {



                // TODO: napoj na session

                

                              

                

                // V202: u weboveho zakaznika bez realneho HairSoft ID soubor nejprve drzet ve stavu 2.
                // Teprve po navratu realneho ID se frontou zmeni na stav=0 a PC jej muze stahnout do spravne slozky.
                $statement = null;
                try {
                    if (($hsCustomerWaitForHairSoftId || $hsCustomerManualTimelineHold) && !hsCustomerSyncHoldQueueGuid($mysqli, $osoba_guid, (int) $sw_id)) {
                        throw new RuntimeException('Sync hold queue preparation failed');
                    }
                    $statement = $mysqli->prepare("INSERT INTO soubory (guid_souboru, puvodni_nazev, ulozeny_nazev, velikost, guid_cloveka, stav) VALUES (?, ?, ?, ?, ?, ?)");
                    if (!$statement) { throw new RuntimeException('File metadata preparation failed'); }
                    $statement->bind_param('sssisi', $guid, $originalName, $newName, $file['size'], $osoba_guid, $hsFileSyncState);
                    if (!$statement->execute()) { throw new RuntimeException('File metadata insert failed'); }
                    if ($hsCustomerWaitForHairSoftId) {
                        // Znovu oznacit frontu az po vlozeni souboru; zavira race, kdy ID dorazi behem uploadu.
                        hsCustomerSyncHoldQueueGuid($mysqli, $osoba_guid, (int) $sw_id);
                        hsCustomerSyncHoldSynchronizeGuid($mysqli, $osoba_guid);
                        $hsCustomerWaitForHairSoftId = hsCustomerSyncHoldShouldWait($mysqli, $osoba_guid);
                    }
                    $zprava = $hsCustomerWaitForHairSoftId ? "Soubor uložen. Čeká na přidělení ID HairSoft." : "Soubor uložen.";
                } catch (Throwable $error) {
                    @unlink($path);
                    $chyba = "Soubor se nepodařilo uložit. Zkuste to prosím znovu.";
                } finally {
                    if ($statement) { $statement->close(); }
                }

                //Zalozky

                $AktivniZalozkaSoubory = 1;





            } else {

                $chyba = "Chyba uložení souboru";

            }

        }

    }

}















                            

/*GET AKCE*/

if ($osoba_guid!="" and $timelineIDGET!="") {

  



  if ($SmazaniZaznamZeSystemu=="1") {

        if ($hsCustomerWaitForHairSoftId) {

          // V202: HOLD Timeline jeste nikdy nebyla v PC, proto ji nema smysl posilat jako D.

          $sql_u = "DELETE FROM `timeline` WHERE `timelineOsobaGuid` = '".$osoba_guid."' and `timelineID` = ".$timelineIDGET." AND `timelineIDHS` IS NULL;";

        }else{

          $sql_u = "UPDATE `timeline` SET `timelineDoPCZnak` = 'D', `timelineValid` = '0', `timelineDoPCSynchro` = NULL WHERE `timelineOsobaGuid` = '".$osoba_guid."' and `timelineID` = ".$timelineIDGET.";";

        }

           $vysledek_smazat = @$mysqli->query($sql_u);



           //echo $sql_u;

                        

           //Zalozky

           $AktivniZalozkaTimeline = 1;  

           $timelineIDGET = "";

          

  }

}







 

 





/*POST AKCE*/

switch ($akce){











   case 'smazatSoubor':

          if ($guid_souboru!="" and $ulozeny_nazev!="") {

           





           // V202: soubor cekajici na prvni HairSoft ID nebyl nikdy v PC, proto se pri smazani odstrani rovnou.

           // U jiz synchronizovaneho zakaznika zustava puvodni stav=9 pro standardni mazaci synchronizaci.

           if ($hsCustomerWaitForHairSoftId) {

             $sql_d = "DELETE FROM `soubory` WHERE `guid_souboru` = '".$guid_souboru."' AND `guid_cloveka` = '".$osoba_guid."'";

           }else{

             $sql_d = "UPDATE `soubory` SET `stav` = '9' WHERE `guid_souboru` = '".$guid_souboru."'";

           }

           $vysledek_smazat_soubor = @$mysqli->query($sql_d);





                     

              if ($vysledek_smazat_soubor) {

                 $zprava = "Soubor byl smazán.";



                 //Smazat obrazek 

                    $CestaIMG = "strana/Soubory/";

                    @unlink($CestaIMG.$ulozeny_nazev);



                    //Zalozky

                    $AktivniZalozkaSoubory = 1;

                    echo "<meta http-equiv=\"refresh\" content=\"0;URL=index.php?strana=KartaOsoby&osoba_guid=".$osoba_guid."&NavratSoubory=1\">";

                    return;




