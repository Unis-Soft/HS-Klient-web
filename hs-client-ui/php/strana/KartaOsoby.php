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





               }else{

                 $error = $mysqli->error; 

                  $TextDoInformace = $error; 

                  return;

              } 

          }

   break;



   



   case 'smazat':

          if ($osoba_guid!="") {

           $sql_u = "UPDATE `klient_lidi` SET `lidi_web_pc` = 'D', `lidi_aktivni` = 0 WHERE `lidi_guid` = '".$osoba_guid."'";

           $vysledek_smazat = @$mysqli->query($sql_u);

                     

              if ($vysledek_smazat) {

                 $lidi_web_pc = "D";

               }else{

                 $error = $mysqli->error; 

                  $TextDoInformace = $error; 

                  return;

              } 

          }

     break;

   

   case 'edit':

          

                          if ($osoba_guid!="") {

                                  $sql_existuje_guid= "SELECT count(*) as CNT_guid FROM `klient_lidi` WHERE `lidi_guid` = '".$osoba_guid."'";

                                  $vysledek_existuje_guid=$mysqli->query($sql_existuje_guid);

                                  $sql_existuje_guid=MySQLi_Fetch_Array($vysledek_existuje_guid);



                                  if ($sql_existuje_guid["CNT_guid"]>0) {

                                         // V202: dokud HairSoft nepridelil realne ID, musi webovy zakaznik zustat ve stavu N.

                                         // Editace (vcetne zdravotnich udaju) se tak prida do prvotniho vytvoreni a nezmeni N na U.

                                         $hsCustomerWebPcFlag = $hsCustomerWaitForHairSoftId ? 'N' : 'U';

                                         //UPDATE

                                         $sql = " UPDATE `klient_lidi` SET 

                                                         `lidi_hs_name`='".$lidi_hs_name."'

                                                        ,`lidi_hs_surname`='".$lidi_hs_surname."'

                                                        ,`lidi_hs_title`='".$lidi_hs_title."'

                                                        ,`lidi_hs_genre`='".$lidi_hs_genre."'

                                                        ,`lidi_hs_email`='".$lidi_hs_email."'

                                                        ,`lidi_hs_street`='".$lidi_hs_street."'

                                                        ,`lidi_hs_city`='".$lidi_hs_city."'

                                                        ,`lidi_hs_zip`='".$lidi_hs_zip."'

                                                        ,`lidi_hs_phone`='".$lidi_hs_phone."'

                                                        ,`lidi_hs_cell`='".$lidi_hs_cell."'

                                                        ,`lidi_hs_BirthDay`='".$lidi_hs_BirthDay."'

                                                        ,`lidi_hs_firm_name`='".$lidi_hs_firm_name."'

                                                        ,`lidi_hs_firm_register_number`='".$lidi_hs_firm_register_number."'

                                                        ,`lidi_hs_www`='".$lidi_hs_www."'

                                                        ,`lidi_hs_skype`='".$lidi_hs_skype."'

                                                        ,`lidi_hs_facebook`='".$lidi_hs_facebook."'

                                                        ,`lidi_hs_note`='".$lidi_hs_note."'

                                                        ,`lidi_hs_card`='".$lidi_hs_card."'

                                                        ,`lidi_hs_NoSMS`='".$lidi_hs_NoSMS."'

                                                        ,`lidi_hs_loyalityPoints`='".$lidi_hs_loyalityPoints."'

                                                        ,`lidi_web_pc`='".$hsCustomerWebPcFlag."'

                                                        ,`lidi_hs_pojistovnaGUID`='".$lidi_hs_pojistovnaGUID."'

                                                        ,`lidi_hs_ostatni`='".$lidi_hs_ostatni."'

                                                        ,`lidi_hs_rc`='".$lidi_hs_rc."'

                                                   WHERE `lidi_guid` = '".$osoba_guid."'";

                                              

                                            //echo $sql;

                                            $vysledek_update_osoby = @$mysqli->query($sql);

                                            if ($vysledek_update_osoby) {

                                              

                                              echo "<meta http-equiv=\"refresh\" content=\"0;URL=index.php?strana=KartaOsoby&osoba_guid=".$osoba_guid."\">";

                                               return;

                                             }else{

                                               $error = $mysqli->error;

                                                echo $error;

                                                return;

                                            }

                                  }

                          }

     break;



    /*timeline novy zaznam*/ 



    

    

    case 'TimelineUlozit':

            if ($osoba_guid!="") {



              //Pokud je admin tak neni jako obsluha takze bude pod jmenem admina v HS

              if ($_SESSION["k_poduzivatele_obsluha_id_hs"]=="" or $_SESSION["k_poduzivatele_obsluha_id_hs"] == "0") {

                               $IDObsluhy = "1";  

              }



              if ($timelineID!="") {

                //Update

                if ($hsCustomerWaitForHairSoftId || $hsCustomerManualTimelineHold) {

                  hsCustomerSyncHoldQueueGuid($mysqli, $osoba_guid, (int) $sw_id);

                }

                $timelineUpdateFlag = ($hsCustomerWaitForHairSoftId || $hsCustomerManualTimelineHold) ? HS_CUSTOMER_TIMELINE_HOLD : 'U';

                $sql_u = "UPDATE `timeline` SET `timelineDoPCZnak` = '".$timelineUpdateFlag."', `timelineValid` = '1', `timelineDoPCSynchro` = NULL,`timelineText` = '".$TimelinePoznamka."', `timelineObsluhaID` = ".$IDObsluhy.",`timelineObsluha` = '".$_SESSION["uzivatel_prijmeni_jmeno"]."' WHERE `timelineOsobaGuid` = '".$osoba_guid."' and `timelineID` = ".$timelineID.";";

                $vysledek_update_timeline = @$mysqli->query($sql_u);

                if ($vysledek_update_timeline && ($hsCustomerWaitForHairSoftId || $hsCustomerManualTimelineHold)) {

                  hsCustomerSyncHoldQueueGuid($mysqli, $osoba_guid, (int) $sw_id);

                  hsCustomerSyncHoldSynchronizeGuid($mysqli, $osoba_guid);

                }



                                                          //Zalozky

                                          $AktivniZalozkaTimeline = 1;             



                                          echo "<meta http-equiv=\"refresh\" content=\"0;URL=index.php?strana=KartaOsoby&osoba_guid=".$osoba_guid."&NavratTimeline=1\">";

                                          return;





              }else{

                //Insert

                //INSERT



                if ($hsCustomerWaitForHairSoftId && !hsCustomerSyncHoldQueueGuid($mysqli, $osoba_guid, (int) $sw_id)) {

                  $TextDoInformace = "Poznámku se nepodařilo bezpečně zařadit do čekající synchronizace.";

                  break;

                }



                             







                                         $sql = "INSERT INTO `timeline` (

                                          `timelineID`

                                          , `timelineOsobaGuid`

                                          , `timelineDatumCas`

                                          , `timelineText`

                                          , `timelineObsluha`

                                          , `timelineObsluhaID`

                                          , `timelineDoPCZnak`

                                          , `timelineDoPCSynchro`

                                          , `timelineIDHS`

                                          , `timelineSwID`

                                          , `timelineValid`) VALUES (

                                          NULL

                                          , '".$osoba_guid."'

                                          , CURRENT_TIMESTAMP

                                          , '".$TimelinePoznamka."'

                                          , '".$_SESSION["uzivatel_prijmeni_jmeno"]."'

                                          , '".$IDObsluhy."'

                                          , '".$hsTimelineSyncFlag."'

                                          , NULL

                                          , NULL

                                          , '".$lidi_sw_id."'

                                          , '1'

                                          ); ";                                            

                                            

                                          $vysledek_insert_timeline = @$mysqli->query($sql);

                                          if ($vysledek_insert_timeline && ($hsCustomerWaitForHairSoftId || $hsCustomerManualTimelineHold)) {

                                            hsCustomerSyncHoldQueueGuid($mysqli, $osoba_guid, (int) $sw_id);

                                            hsCustomerSyncHoldSynchronizeGuid($mysqli, $osoba_guid);

                                          }



                                          //echo $sql;



                                          //Zalozky

                                          $AktivniZalozkaTimeline = 1;             



                                          echo "<meta http-equiv=\"refresh\" content=\"0;URL=index.php?strana=KartaOsoby&osoba_guid=".$osoba_guid."&NavratTimeline=1\">";

                                          return;

                                          





              }



            }

    break;

    





    case 'novy':

                        if ($osoba_guid!="") {

                                  $sql_existuje_guid= "SELECT count(*) as CNT_guid FROM `klient_lidi` WHERE `lidi_guid` = '".$osoba_guid."'";

                                  $vysledek_existuje_guid=$mysqli->query($sql_existuje_guid);

                                  $sql_existuje_guid=MySQLi_Fetch_Array($vysledek_existuje_guid);





                                  if ($sql_existuje_guid["CNT_guid"]==0) {

                                       

                                         //INSERT

                                         $sql = "INSERT INTO `klient_lidi` (

                                            `lidi_id`

                                           ,`lidi_hs_id`

                                           ,`lidi_hs_name`

                                           ,`lidi_hs_surname`

                                           ,`lidi_hs_title`

                                           ,`lidi_hs_genre`

                                           ,`lidi_hs_email`

                                           ,`lidi_hs_street`

                                           ,`lidi_hs_city`

                                           ,`lidi_hs_zip`

                                           ,`lidi_hs_phone`

                                           ,`lidi_hs_cell`

                                           ,`lidi_hs_BirthDay`

                                           ,`lidi_hs_firm_name`

                                           ,`lidi_hs_firm_register_number`

                                           ,`lidi_hs_www`

                                           ,`lidi_hs_skype`

                                           ,`lidi_hs_facebook`

                                           ,`lidi_hs_note`

                                           ,`lidi_hs_card`

                                           ,`lidi_hs_NoSMS`

                                           ,`lidi_hs_loyalityPoints`

                                           ,`lidi_zmena`

                                           ,`lidi_zalozeno`

                                           ,`lidi_aktivni`

                                           ,`lidi_sw_id`

                                           ,`lidi_skupinaID`

                                           ,`lidi_web_pc`

                                           ,`lidi_guid`

                                           ,`lidi_hs_pojistovnaGUID`

                                           ,`lidi_hs_ostatni`

                                           ,`lidi_hs_rc`

                                             

                                             ) VALUES (

                                           NULL

                                           , '11111111'

                                           , '".$lidi_hs_name."'

                                           , '".$lidi_hs_surname."'

                                           , '".$lidi_hs_title."'

                                           , '".$lidi_hs_genre."'

                                           , '".$lidi_hs_email."'

                                           , '".$lidi_hs_street."'

                                           , '".$lidi_hs_city."'

                                           , '".$lidi_hs_zip."'

                                           , '".$lidi_hs_phone."'

                                           , '".$lidi_hs_cell."'

                                           , '".$lidi_hs_BirthDay."'

                                           , '".$lidi_hs_firm_name."'

                                           , '".$lidi_hs_firm_register_number."'

                                           , '".$lidi_hs_www."'

                                           , '".$lidi_hs_skype."'

                                           , '".$lidi_hs_facebook."'

                                           , '".$lidi_hs_note."'

                                           , '".$lidi_hs_card."'

                                           , '1'

                                           , '0'

                                           , CURRENT_TIMESTAMP

                                           , CURRENT_TIMESTAMP

                                           , '1'

                                           , '".$lidi_combo_pobocka."'

                                           , '".IdSkupiny($lidi_combo_pobocka,$mysqli)."'

                                           , 'N'

                                           , '".$osoba_guid."'

                                           , '".$lidi_hs_pojistovnaGUID."'

                                           , '".$lidi_hs_ostatni."'

                                           , '".$lidi_hs_rc."'

                                            );";

                                              

                                            

                                            

                                            $vysledek_insert_osoby = @$mysqli->query($sql);

                                            if ($vysledek_insert_osoby) {

                                               echo "<meta http-equiv=\"refresh\" content=\"0;URL=index.php?strana=KartaOsoby&osoba_guid=".$osoba_guid."\">";

                                               return;

                                             }else{

                                               $error = $mysqli->error;

                                                echo $error;

                                                return;

                                            }

                                  }

                          }

                          

                                 

       break;   

 }





 //Nacteni osoby

                            //if ($osoba_guid!="" and $akce =="") {                            

                            if ($osoba_guid!="" ) {

                                //jen 1 osoaba

                                $select_na_ososbu= "SELECT * FROM `klient_lidi` left join `klient_lidi_statistika` ON `klient_lidi`.`lidi_guid` = `klient_lidi_statistika`.`stat_klient_GUID` WHERE `lidi_guid` = '".$osoba_guid."'";

                                //echo $select_na_ososbu;



                                if (!$select_na_ososbu) { die('Chyba pripojeni do DB!');}

                                $vysledek_na_ososbu=$mysqli->query("$select_na_ososbu");

                                $data_osoby=MySQLi_Fetch_Array($vysledek_na_ososbu);

                                $lidi_hs_id = isset($data_osoby["lidi_hs_id"]) ? (int) $data_osoby["lidi_hs_id"] : 0;

                                $lidi_skupinaID = isset($data_osoby["lidi_skupinaID"]) ? (int) $data_osoby["lidi_skupinaID"] : 0;



                                $lidi_hs_name = $data_osoby["lidi_hs_name"];

                                $lidi_hs_surname = $data_osoby["lidi_hs_surname"];

                                $lidi_hs_title = $data_osoby["lidi_hs_title"];

                                $lidi_hs_genre = $data_osoby["lidi_hs_genre"];

                                $lidi_hs_email = $data_osoby["lidi_hs_email"];

                                $lidi_hs_street = $data_osoby["lidi_hs_street"];

                                $lidi_hs_city = $data_osoby["lidi_hs_city"];

                                $lidi_hs_zip = $data_osoby["lidi_hs_zip"];

                                $lidi_hs_phone = $data_osoby["lidi_hs_phone"];

                                $lidi_hs_cell = $data_osoby["lidi_hs_cell"];



                                $lidi_hs_pojistovnaGUID = $data_osoby["lidi_hs_pojistovnaGUID"];

                                $lidi_hs_rc = $data_osoby["lidi_hs_rc"];

                                $lidi_hs_ostatni = $data_osoby["lidi_hs_ostatni"];

                                



                                $lidi_hs_pocet_navstev = $data_osoby["stat_PocetNavstev"];

                                $lidi_hs_zrusene = $data_osoby["stat_ZruseneObjednavky"];

                                

                                if ($data_osoby["stat_PosledniNavsteva"]!="") {

                                    $lidi_hs_posledni_navsteva = date("d.m.Y H:i", strtotime($data_osoby["stat_PosledniNavsteva"]));   

                                }else{

                                    $lidi_hs_posledni_navsteva = ""; 

                                }



                                if ($data_osoby["stat_PristiNavsteva"]!="") {

                                    $lidi_hs_pristi_navsteva = date("d.m.Y H:i", strtotime($data_osoby["stat_PristiNavsteva"])); 

                                }else{

                                    $lidi_hs_pristi_navsteva = "";

                                }



                                

                                







                                $lidi_hs_BirthDay = date("d.m.Y", strtotime($data_osoby["lidi_hs_BirthDay"]));

                                

                                //$newDateSV = date("d.m.Y H:i", strtotime($na_hodnoceni["order_date"]));

                                $lidi_hs_firm_name = $data_osoby["lidi_hs_firm_name"];

                                $lidi_hs_firm_register_number = $data_osoby["lidi_hs_firm_register_number"];

                                $lidi_hs_www = $data_osoby["lidi_hs_www"];

                                $lidi_hs_skype = $data_osoby["lidi_hs_skype"];

                                $lidi_hs_facebook = $data_osoby["lidi_hs_facebook"];

                                $lidi_hs_note = $data_osoby["lidi_hs_note"];

                                $lidi_hs_card = $data_osoby["lidi_hs_card"];

                                $lidi_hs_NoSMS = $data_osoby["lidi_hs_NoSMS"];

                                $lidi_hs_loyalityPoints = $data_osoby["lidi_hs_loyalityPoints"];



                                $lidi_blacklist = $data_osoby["lidi_blacklist"];



                                



                                //sys

                                $lidi_web_pc = $data_osoby["lidi_web_pc"];

                                $lidi_zmena = $data_osoby["lidi_zmena"];

                                $lidi_zalozeno  = $data_osoby["lidi_zalozeno"];

                                $lidi_sw_id  = $data_osoby["lidi_sw_id"];









                                /*Profilovka*/

                                $select_na_profilovka= "SELECT `obrazek_jmeno_GUID` FROM `klient_lidi_obrazky` WHERE `obrazek_smazan` = 0 and `obrazek_profilovka` = 1 and `obrazek_osoba_GUID` =  '".$osoba_guid."' limit 1";

                                if (!$select_na_profilovka) { die('Chyba pripojeni do DB!');}

                                $vysledek_na_profilovka=$mysqli->query("$select_na_profilovka");

                                $dataprofilovka=MySQLi_Fetch_Array($vysledek_na_profilovka);



                                $obrazek_jmeno_GUID = $dataprofilovka["obrazek_jmeno_GUID"];

                                // V225: PROGRAMS se nesmi nacitat pri kazdem otevreni Karty osoby.
                                // V224 tim zpomalovala vsechny zalozky detailu a zviditelnila plny reload jako bilou stranku.
                                // Data nacteme jen pro specialni PROGRAMS pozadavek; bezne zalozky jedou stejnou cestou jako V223.
                                $hsProgramDetail = array('programs' => array(), 'selectedProgramId' => 0);
                                $hsProgramSelected = null;
                                if ($AktivniZalozkaProgramy == 1) {
                                    $hsProgramGroupId = $lidi_skupinaID > 0 ? $lidi_skupinaID : hsProgramsGroupId($mysqli, (int) $lidi_sw_id);
                                    $hsProgramDetail = hsProgramsCustomerDetail(
                                        $mysqli,
                                        (int) $lidi_sw_id,
                                        (int) $hsProgramGroupId,
                                        (int) $lidi_hs_id,
                                        (int) $hsProgramRequestedId
                                    );
                                    if (!empty($hsProgramDetail['programs'])) {
                                        foreach ($hsProgramDetail['programs'] as $hsProgramCandidate) {
                                            if ((int) $hsProgramCandidate['id'] === (int) $hsProgramDetail['selectedProgramId']) {
                                                $hsProgramSelected = $hsProgramCandidate;
                                                break;
                                            }
                                        }
                                    }
                                }

                                



                            }





 

 

 

 



//akce vycteni enumu na lidi_web_pc

switch ($lidi_web_pc) {

  case 'D':

    $TextDoInformace = "Osoba je přidána do seznamu ke smazání. Jakmile proběhne aktualizace s aplikací HS v pc, bude odstraněna i zde.";

    break;



  case 'N':

    $TextDoInformace = "Osoba je přidána do seznamu k synchronizaci do PC jako nová osoba.";

    break;



  case 'U':

    $TextDoInformace = "Osoba je přidána do seznamu k synchronizaci do PC jako aktualizovaná osoba.";

    break;

  

  default:

    # code...

    break;

}





 

$jmenoStranky = $lidi_hs_name." ".$lidi_hs_surname;



// V198 TEST: zdrojem je vzdy prave aktivni firma ve switchi. Cilem muze byt jina zapamatovana firma.

// Administratori i manageri mohou funkci pouzit jen na pobockach, kde maji pravo pracovat se zakazniky.

$hsCustomerCopyTestTargets = array();

$hsCustomerCopyTestLogs = array();

$hsCustomerCopyTestCanUse = false;

$hsCustomerCopyTestIdentity = null;

$hsCustomerCopyTargetLog = isset($hsCustomerCopyTargetEarlyLog) ? $hsCustomerCopyTargetEarlyLog : null;

$hsCustomerCopyManualState = null;

if ($osoba_guid!="" && isset($hsMultiReady) && $hsMultiReady) {

    $hsCustomerCopyTestIdentity = isset($hsCustomerCopyEarlyIdentity) && $hsCustomerCopyEarlyIdentity ? $hsCustomerCopyEarlyIdentity : hsCustomerCopyTestCurrentIdentity($mysqli);

    if ($hsCustomerCopyTestIdentity) {

        $hsCustomerCopyTestCanUse = true;

        $hsCustomerCopyTestTargets = hsCustomerCopyTestTargetAccounts($mysqli, isset($_SESSION['k_soft']) ? (string) $_SESSION['k_soft'] : 'HairSoft');

        $hsCustomerCopyTestLogs = hsCustomerCopyTestRecentLogs($mysqli, (int) $hsCustomerCopyTestIdentity['owner_id'], $osoba_guid);

        if (!$hsCustomerCopyTargetLog) {
            $hsCustomerCopyTargetLog = hsCustomerCopyTestTargetLog($mysqli, (int) $hsCustomerCopyTestIdentity['owner_id'], $osoba_guid);
        }

        if ($hsCustomerCopyTargetLog && hsCustomerCopyTestIdentityAllowedOnBranch($mysqli, $hsCustomerCopyTestIdentity, (int) $hsCustomerCopyTargetLog['target_branch_id'])) {

            $hsCustomerCopyManualState = hsCustomerSyncHoldManualState($mysqli, $osoba_guid, (int) $hsCustomerCopyTargetLog['id']);

        } else {

            $hsCustomerCopyTargetLog = null;

        }

    }

}



?>

 

<section class="main-content-wrapper">            

  <div class="pageheader">                

    <h1><?php 

              if ($osoba_guid!="") {

                echo $jmenoStranky;

              }

            

         ?>&nbsp;&nbsp;

         <?php 

              if ($osoba_guid!="") {

                  echo JeOsobaBan($lidi_blacklist);

              }

         ?>

    </h1>

    <p class="description">

      <?php

              if ($osoba_guid!="") {

                  echo $jmenoStrankyPopis; 

              }      

      

      ?>

    </p>                

    <div class="breadcrumb-wrapper hidden-xs">                    

      <span class="label">Pobočka:</span>                    

      <ol class="breadcrumb">                        

        <li class="active"><b><?php echo $pobocka_jmeno; ?></b><?php echo JePobockaOnline($sw_id,$mysqli) ?>

        </li>                    

      </ol>                

    </div>            

  </div> 

             

  

           <section id="main-content" class="animated fadeInUp">

                

                <?php

                

                  if ($lidi_web_pc!="" and $TextDoInformace!="") {

                   ?>



                        <div class="row">

                            <div class="col-md-12">

                                <div class="panel panel-info">

                                    <div class="panel-heading">

                                        <h3 class="panel-title">Akce</h3>

                                        <!-- <div class="actions pull-right">

                                            <i class="fa fa-expand"></i>

                                            <i class="fa fa-chevron-down"></i>

                                            <i class="fa fa-times"></i>

                                        </div> -->

                                    </div>

                                    <div class="panel-body">

                                        <b>

                                          <?php

                                            echo $TextDoInformace;

                                          ?>

                                        </b>

                                    </div>

                                </div>

                            </div>

                        </div>



                   <?php 

                  }

                ?>

                



                

                                    <?php 

                                        

                                        if ($akce!="novy") {





                                          

                                          //VOLAT

                                          if ($lidi_hs_cell!="") {

                                            $volat = $lidi_hs_cell;

                                          }else{

                                            if ($lidi_hs_phone!="") {

                                              $volat = $lidi_hs_phone;

                                            }else{

                                              $volat = "Číslo nezadáno";

                                            }

                                          }



                                          

                                          //psat

                                          $formaNapsat = "";

                                          if ($lidi_hs_cell!="") {

                                            $napsat = $lidi_hs_cell;

                                            $formaNapsat = "sms:";

                                          }else{

                                            if ($lidi_hs_email!="") {

                                              $napsat = $lidi_hs_email;

                                              $formaNapsat = "mailto:";

                                            }else{

                                              $napsat= "Není zadáno";

                                            }

                                          }





                                          if ($_SESSION["k_poduzivatele_skryt_citliva_data"]=="1") {

                                            $volat = "Anonymizováno";

                                            $napsat= "Anonymizováno";

                                          }







                                            //if ($fotoaparat=="1") {



                                            if ($osoba_guid!="") {

                                              

                                            

                                    ?>





                                                 <div class="row" style="display:none;" id="DIV_Fotoaparatu">

                                                   <div class="col-md-12">

                                                      <div class="panel panel-default">

                                                          <div class="panel-heading">

                                                              <h3 class="panel-title"><font face="tahoma"><b>Fotoaparát </b></font></h3>

                                                               <div class="actions pull-right">

                                                                  <i class="fa fa-expand"></i>

                                                                  <i class="fa fa-chevron-down"></i>

                                                                  <i class="fa fa-times"></i>

                                                              </div> 

                                                          </div>

                                                          <div class="panel-body">

                                                            <?php 

                                                                //echo $osoba_guid;



                                                             ?>

                                                          











                                                                <center><img style="max-height:500px;"id="pictureFromCamera" /><br></center>



                                                                

                                                                

                                                                <form action="https://klient.hairsoft.cz/str/strana/NahratSoubor.php" method="POST"  enctype="multipart/form-data">

                                                                  

                                                                  <input style="display:none;" name="cameraFileInput" id="cameraFileInput" type="file" accept="image/*" capture="environment"/>

                                                                  

                                                                  

                                                                  

                                                                            <input type="hidden" class="form-control" name="akce" value="fotoaparat">

                                                                            <input type="hidden" class="form-control" name="osoba_guid" value="<?php echo $osoba_guid;?>">

                                                                            <input type="hidden" class="form-control"  name="otoceni" id="otoceni" value="0">

                                                                            

                                                                            <br>

                                                                            <br>

                                                                            <center>

                                                                              <button type="submit" id="UlozitDoGalerie" onclick="UlozitButtonFce()" class="btn btn-success"><i class="fa fa-save"></i>Uložit do galerie</button> 

                                                                            </center>                                                                           

                                                                                                             

                                                                            <center><img style="display:none;" id="LoadingUlozit" heigth="35" width="35" src="https://klient.hairsoft.cz/img/BarberLoad.gif" class="img-circle" ></center>

                                                                                                             

                                                                </form>

                                                                

                                                              



                                                          <!-- 

                                                              <center>

                                                                <button type="button" style="width:110px;" id="OtocitDoleva" onclick="DolevaButtonFce()" class="btn btn-info"><i class="fa fa-rotate-left"></i>Doleva</button> 

                                                                <button type="button" id="OtocitVychozi" onclick="VychoziButtonFce()" class="btn btn-info">Výchozí</button> 

                                                                <button type="button" style="width:110px;" id="OtocitDoprava" onclick="DopravaButtonFce()" class="btn btn-info"><i class="fa fa-rotate-right"></i>Doprava</button> 

                                                              </center>                                                             

                                                          -->











                                                          </div>

                                                      </div>

                                                    </div>

                                                </div>



                                          <?php 

                                            //}

                                           ?>



                                                <div class="row">

                                                    

                                                    <div class="col-md-12 ">

                                                     

                                                              <div class="form-group" >

                                                                    <div class="col-sm-4">

                                                                      

                                                                    </div>

                                                                </div>







                                                              

                                                              <label for="cameraFileInput"  style="display:block;" id="cameraFileInputLabelDiv">

                                                              <div class="col-md-3">

                                                                <div class="panel widget-mini">

                                                                    <div class="panel-body">

                                                                        <i class="fa icon-camera"></i>

                                                                        

                                                                       <center> <img id="FotoaparatKartaOsoby" src="https://klient.hairsoft.cz/img/camera.png"></center>

                                                                    </div>

                                                                </div>

                                                              </div>

                                                              </label>  





                                                              <!-- <label for="cameraFileInput"  style="display:block;" >

                                                              <div class="col-md-3">

                                                                <div class="panel widget-mini">

                                                                    <div class="panel-body">

                                                                        <i class="fa icon-camera"></i>

                                                                        

                                                                        <span class="total text-center"><font  size="5">Vyfotit</font></span>

                                                                        

                                                                        

                                                                        <center><span class="fotolabel">Vytvořit fotku</span></center>

                                                                    </div>

                                                                </div>

                                                              </div>

                                                              </label>   -->

                                                              

                                                              

                                                              

                                                            

                                                            



                                                            <a style="color:white" href="tel:<?php echo $volat ?>">

                                                            <div class="col-md-3">

                                                                <div class="panel panel-solid-success widget-mini">

                                                                    <div class="panel-body">

                                                                        <i class="icon-call-out"></i>

                                                                        <span class="total text-center"><font  size="5">Volat</font></span>

                                                                        <span class="title text-center"><?php echo $volat ?></span>

                                                                    </div>

                                                                </div>

                                                            </div>

                                                            </a>

                                                            

                                                        

                                                   

                                                         

                                                            <a  href="<?php echo $formaNapsat.$napsat; ?>">

                                                            <div class="col-md-3">

                                                                <div class="panel widget-mini">

                                                                    <div class="panel-body">

                                                                        <i class="icon-envelope"></i>

                                                                        <span class="total text-center"><font  size="5">SMS</font></span>

                                                                        <span class="title text-center"><?php echo $napsat; ?></span>

                                                                    </div>

                                                                </div>

                                                            </div>

                                                            </a>



                                                            <div class="col-md-3">

                                                                <div class="panel panel-solid-danger widget-mini">

                                                                    <div class="panel-body">

                                                                        <i class=" icon-star"></i>

                                                                        <?php 

                                                                            

                                                                            if ($lidi_hs_loyalityPoints == "") {

                                                                              $lidi_hs_loyalityPoints = "0";

                                                                            }



                                                                         ?>

                                                                        <span class="total text-center"><font size="5"><?php echo $lidi_hs_loyalityPoints ?></font></span>              

                                                                        <span class="title text-center">Bonusové body</span>

                                                                    </div>

                                                                </div>

                                                            </div>

                                                            

                                                        

                                                </div>  

                                        </div>

                                    <?php  

                                        }

                                      }

                                    ?>





                                    <?php 

                                      if ($osoba_guid!="") {

                                     ?>









                                                   <div class="row hs-detail-data-management-row">

                                                     <div class="col-md-12">

                                                        <details class="hs-client-data-management">

                                                            <summary>

                                                              <span class="hs-client-data-management-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"></path><circle cx="16" cy="7" r="2"></circle><circle cx="8" cy="17" r="2"></circle></svg></span>

                                                              <span class="hs-client-data-management-label">Správa dat</span>

                                                            </summary>

                                                            <div class="hs-client-data-management-actions">





                                                                <div class="rada">

                                                                  <form action="../str/index.php?strana=KartaOsoby&osoba_guid=<?php echo $osoba_guid;?>" method="POST" >

                                                                    <button type="submit" class="btn btn-info"><i class="fa icon-refresh"></i>Refresh stránky</button> 

                                                                  </form>

                                                                </div>



                                                                <?php if ($hsCustomerCopyTestCanUse) { ?>

                                                                <div class="rada hs-copy-test-option">

                                                                  <?php if (count($hsCustomerCopyTestTargets) > 0) { ?>

                                                                    <button type="button" class="btn btn-info hs-copy-test-open" data-hs-copy-test-open>

                                                                      <span class="hs-copy-test-inline-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="8" y="8" width="11" height="11" rx="2"></rect><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"></path></svg></span>

                                                                      <span>Zkopírovat do jiné firmy</span>


                                                                    </button>

                                                                  <?php } else { ?>

                                                                    <button type="button" class="btn btn-default hs-copy-test-open is-disabled" disabled>

                                                                      <span class="hs-copy-test-inline-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="8" y="8" width="11" height="11" rx="2"></rect><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"></path></svg></span>

                                                                      <span>Zkopírovat do jiné firmy</span>


                                                                    </button>

                                                                    <small class="hs-copy-test-hint">Přidejte další firmu přes Přepnout firmu. Administrátor nebo manažer musí mít oprávnění k zákazníkům.</small>

                                                                  <?php } ?>

                                                                </div>

                                                                <?php } ?>



                                                                <?php if ($hsCustomerCopyTargetLog && $hsCustomerCopyManualState) { ?>

                                                                <div class="rada hs-copy-manual-option">

                                                                  <?php if (empty($hsCustomerCopyManualState['manual_confirmed'])) { ?>

                                                                    <button type="button" class="btn btn-warning hs-copy-manual-open" data-hs-copy-manual-open>

                                                                      <span class="hs-copy-test-inline-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 7h16M4 17h16"></path><path d="M8 3v8M16 13v8"></path><circle cx="8" cy="14" r="2"></circle><circle cx="16" cy="10" r="2"></circle></svg></span>

                                                                      <span>Dokončit synchronizaci do HairSoft</span>

                                                                    </button>

                                                                    <small class="hs-copy-test-hint"><?php if (!empty($hsCustomerCopyManualState['paired'])) { ?>HairSoft už u zákazníka eviduje ID <?php echo htmlspecialchars((string) $hsCustomerCopyManualState['hs_id'], ENT_QUOTES, 'UTF-8'); ?>. Potvrďte ho ručně, teprve potom se uvolní fotografie a soubory.<?php } else { ?>Nejdříve založte zákazníka v HairSoft a zjistěte jeho ID.<?php } ?></small><?php if (empty($hsCustomerCopyManualState['manual_confirmed']) && (int) $hsCustomerCopyManualState['timeline_prepared'] > 0) { ?><small class="hs-copy-test-hint"><strong>Pozor:</strong> <?php echo (int) $hsCustomerCopyManualState['timeline_prepared']; ?> Timeline položek už bylo připraveno starší automatickou frontou. Tyto řádky nelze bezpečně vrátit zpět bez potvrzení z HairSoft; pro nový čistý přenos vytvořte novou kopii.</small><?php } ?>

                                                                  <?php } else { ?>

                                                                    <div class="hs-copy-manual-ready">

                                                                      <span class="hs-copy-manual-ready-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"></path></svg></span>

                                                                      <span><strong>HairSoft ID: <?php echo htmlspecialchars((string) $hsCustomerCopyManualState['hs_id'], ENT_QUOTES, 'UTF-8'); ?></strong> · fotografie a soubory připraveny · Timeline čeká: <?php echo (int) $hsCustomerCopyManualState['timeline_hold']; ?></span>

                                                                      <?php if ((int) $hsCustomerCopyManualState['timeline_hold'] > 0) { ?><a href="index.php?strana=KartaOsoby&amp;osoba_guid=<?php echo rawurlencode($osoba_guid); ?>&amp;NavratTimeline=1">Otevřít Timeline</a><?php } ?>

                                                                    </div>

                                                                  <?php } ?>

                                                                </div>

                                                                <?php } ?>



                                                              



                                                                <div class="rada">

                                                                  <form action="../str/index.php?strana=KartaOsoby" method="POST" >

                                                                    <input type="hidden" class="form-control" name="akce" value="smazat">

                                                                    <input type="hidden" class="form-control" name="osoba_guid" value="<?php echo $osoba_guid;?>">

                                                                    <button type="submit" onClick="if(!confirm('Opravdu chcete smazat tuto osobu? Osoba bude smazána také v programu HairSoft.')){return false;}" class="btn btn-danger"><i class="fa icon-trash"></i>Smazat osobu</button>
                                                                  </form>

                                                                </div>

                                                                

                                                                <br class="clearBoth" /> 



                                                                <?php if ($hsCustomerCopyTestCanUse && count($hsCustomerCopyTestLogs) > 0) { ?>

                                                                <div class="hs-copy-test-history">

                                                                  <div class="hs-copy-test-history-title">Kopie tohoto zákazníka</div>

                                                                  <?php foreach ($hsCustomerCopyTestLogs as $hsCopyLog) {

                                                                    $hsCopyStatus = (string) $hsCopyLog['status'];

                                                                    $hsCopyWebPc = isset($hsCopyLog['lidi_web_pc']) ? (string) $hsCopyLog['lidi_web_pc'] : '';

                                                                    $hsCopyActive = isset($hsCopyLog['lidi_aktivni']) ? (string) $hsCopyLog['lidi_aktivni'] : '';

                                                                    $hsCopyStatusClass = 'is-waiting';

                                                                    $hsCopyStatusText = 'Čeká na synchronizaci';

                                                                    if ($hsCopyStatus === 'removed' || (!array_key_exists('lidi_web_pc', $hsCopyLog) || $hsCopyLog['lidi_web_pc'] === null) && $hsCopyStatus !== 'delete_requested') {

                                                                      $hsCopyStatusClass = 'is-removed';

                                                                      $hsCopyStatusText = 'Odstraněno';

                                                                    } elseif ($hsCopyStatus === 'delete_requested') {

                                                                      $hsCopyStatusClass = 'is-delete';

                                                                      $hsCopyStatusText = 'Čeká na smazání v HairSoft';

                                                                    } elseif ($hsCopyWebPc !== 'N' && $hsCopyActive === '1') {

                                                                      $hsCopyStatusClass = 'is-synced';

                                                                      $hsCopyStatusText = 'Předáno HairSoft';

                                                                    }

                                                                  ?>

                                                                  <div class="hs-copy-test-history-row">

                                                                    <div class="hs-copy-test-history-main">

                                                                      <strong data-hs-i18n-ignore><?php echo htmlspecialchars(trim((string) $hsCopyLog['target_company_label']) . ' · ' . (string) $hsCopyLog['target_branch_name'], ENT_QUOTES, 'UTF-8'); ?></strong>

                                                                      <small><span>Timeline</span>: <?php echo (int) $hsCopyLog['timeline_count']; ?> · <span>Fotografie</span>: <?php echo (int) $hsCopyLog['photo_count']; ?> · <?php echo htmlspecialchars(date('d.m.Y H:i', strtotime((string) $hsCopyLog['created_at'])), ENT_QUOTES, 'UTF-8'); ?></small>

                                                                    </div>

                                                                    <span class="hs-copy-test-status <?php echo $hsCopyStatusClass; ?>"><?php echo htmlspecialchars($hsCopyStatusText, ENT_QUOTES, 'UTF-8'); ?></span>

                                                                    <?php if ($hsCopyStatus !== 'removed' && $hsCopyStatus !== 'delete_requested' && !empty($hsCopyLog['target_available'])) { ?>

                                                                    <form method="post" action="/str/customer-copy-test.php" class="hs-copy-test-cleanup-form">

                                                                      <input type="hidden" name="action" value="cleanup">

                                                                      <input type="hidden" name="source_guid" value="<?php echo htmlspecialchars($osoba_guid, ENT_QUOTES, 'UTF-8'); ?>">

                                                                      <input type="hidden" name="log_id" value="<?php echo (int) $hsCopyLog['id']; ?>">

                                                                      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($hsMultiCsrf, ENT_QUOTES, 'UTF-8'); ?>">

                                                                      <button type="submit" class="hs-copy-test-cleanup">Odstranit kopii</button>

                                                                    </form>

                                                                    <?php } ?>

                                                                  </div>

                                                                  <?php } ?>

                                                                </div>

                                                                <?php } ?>





                                                            </div>

                                                        </details>

                                                      </div>

                                                    </div>



                                      <?php 

                                        }

                                       ?>



                                      <?php if ($hsCustomerCopyTestCanUse && count($hsCustomerCopyTestTargets) > 0) { ?>

                                      <div class="hs-copy-test-modal" id="hsCustomerCopyTestModal" aria-hidden="true">

                                        <div class="hs-copy-test-backdrop" data-hs-copy-test-close></div>

                                        <div class="hs-copy-test-dialog" role="dialog" aria-modal="true" aria-labelledby="hsCustomerCopyTestTitle">

                                          <button type="button" class="hs-copy-test-close" data-hs-copy-test-close aria-label="Zavřít"><svg class="hs-copy-test-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"></path></svg></button>

                                          <div class="hs-copy-test-icon"><svg class="hs-copy-test-svg" viewBox="0 0 24 24" aria-hidden="true"><rect x="8" y="8" width="11" height="11" rx="2"></rect><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"></path></svg></div>

                                          <h3 id="hsCustomerCopyTestTitle">Zkopírovat zákazníka do jiné firmy</h3>

                                          <p class="hs-copy-test-person" data-hs-i18n-ignore><?php echo htmlspecialchars($jmenoStranky, ENT_QUOTES, 'UTF-8'); ?></p>

                                          <div class="hs-copy-test-safety"><span class="hs-copy-test-safety-icon" aria-hidden="true"><svg class="hs-copy-test-svg" viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.6 2.8 7.9 7 10 4.2-2.1 7-5.4 7-10V6l-7-3Z"></path><path d="m9 12 2 2 4-4"></path></svg></span><span>Zdrojová firma zůstane beze změny. V cílové firmě vznikne nový zákazník s novým GUID.</span></div>

                                          <form method="post" action="/str/customer-copy-test.php" id="hsCustomerCopyTestForm">

                                            <input type="hidden" name="action" value="copy">

                                            <input type="hidden" name="source_guid" value="<?php echo htmlspecialchars($osoba_guid, ENT_QUOTES, 'UTF-8'); ?>">

                                            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($hsMultiCsrf, ENT_QUOTES, 'UTF-8'); ?>">

                                            <label for="hsCustomerCopyCompany"><span class="hs-copy-test-label-icon" aria-hidden="true"><svg class="hs-copy-test-svg" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"></rect><path d="M8 7h2M14 7h2M8 11h2M14 11h2M8 15h2M14 15h2M10 21v-3h4v3"></path></svg></span><span>Cílová firma</span></label>

                                            <select class="form-control" id="hsCustomerCopyCompany" name="target_selector" required>

                                              <option value="">Vyberte firmu...</option>

                                              <?php foreach ($hsCustomerCopyTestTargets as $hsCopyTarget) { ?>

                                                <option value="<?php echo htmlspecialchars($hsCopyTarget['selector'], ENT_QUOTES, 'UTF-8'); ?>" data-hs-i18n-ignore><?php echo htmlspecialchars($hsCopyTarget['label'] . ' · ' . $hsCopyTarget['login_label'], ENT_QUOTES, 'UTF-8'); ?></option>

                                              <?php } ?>

                                            </select>

                                            <label for="hsCustomerCopyBranch"><span class="hs-copy-test-label-icon" aria-hidden="true"><svg class="hs-copy-test-svg" viewBox="0 0 24 24"><path d="M6 4v13M6 8h9a3 3 0 0 1 3 3v6"></path><circle cx="6" cy="4" r="2"></circle><circle cx="6" cy="20" r="2"></circle><circle cx="18" cy="20" r="2"></circle></svg></span><span>Cílová pobočka / skupina</span></label>

                                            <select class="form-control" id="hsCustomerCopyBranch" name="target_branch_id" required disabled>

                                              <option value="">Nejdříve vyberte firmu...</option>

                                            </select>



                                            <div class="hs-copy-test-scope">

                                              <strong><span class="hs-copy-test-scope-title-icon" aria-hidden="true"><svg class="hs-copy-test-svg" viewBox="0 0 24 24"><path d="M9 6h11M9 12h11M9 18h11"></path><path d="m4 6 1 1 2-2M4 12l1 1 2-2M4 18l1 1 2-2"></path></svg></span><span>Zkopíruje se</span></strong>

                                              <div><span class="hs-copy-test-scope-icon" aria-hidden="true"><svg class="hs-copy-test-svg" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M4 19c.6-3.4 2.3-5 5-5s4.4 1.6 5 5M16 8h4M18 6v4"></path></svg></span><span>Základní a kontaktní údaje</span></div>

                                              <div><span class="hs-copy-test-scope-icon" aria-hidden="true"><svg class="hs-copy-test-svg" viewBox="0 0 24 24"><path d="M6 3h9l3 3v15H6z"></path><path d="M14 3v4h4M9 11h6M9 15h6"></path></svg></span><span>Poznámka a zdravotní údaje</span></div>

                                              <div><span class="hs-copy-test-scope-icon" aria-hidden="true"><svg class="hs-copy-test-svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"></circle><path d="M12 8v5l3 2"></path></svg></span><span>Timeline jako nové záznamy pro cílový HairSoft</span></div>

                                              <div><span class="hs-copy-test-scope-icon" aria-hidden="true"><svg class="hs-copy-test-svg" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"></rect><circle cx="9" cy="10" r="2"></circle><path d="m5 17 4-4 3 3 2-2 5 3"></path></svg></span><span>Fotografie včetně profilové fotografie</span></div>

                                            </div>

                                            <div class="hs-copy-test-not-copy"><span class="hs-copy-test-not-copy-icon" aria-hidden="true"><svg class="hs-copy-test-svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 11v6M12 7h.01"></path></svg></span><span>Číslo karty, bonusové body, blacklist, tržby, rezervace, SMS, hovory a hodnocení se nekopírují.</span></div>



                                            <div class="hs-copy-test-actions">

                                              <button type="button" class="btn btn-default" data-hs-copy-test-close><svg class="hs-copy-test-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"></path></svg><span>Zrušit</span></button>

                                              <button type="submit" class="btn btn-info" id="hsCustomerCopySubmit"><svg class="hs-copy-test-svg" viewBox="0 0 24 24" aria-hidden="true"><rect x="8" y="8" width="11" height="11" rx="2"></rect><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"></path></svg><span>Vytvořit kopii</span></button>

                                            </div>

                                          </form>

                                        </div>

                                      </div>

                                      <script type="application/json" id="hsCustomerCopyTestData"><?php echo json_encode($hsCustomerCopyTestTargets, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>

                                      <?php } ?>



                                      <?php if ($hsCustomerCopyTargetLog && $hsCustomerCopyManualState && empty($hsCustomerCopyManualState['manual_confirmed'])) { ?>

                                      <div class="hs-copy-test-modal hs-copy-manual-modal" id="hsCustomerCopyManualModal" aria-hidden="true">

                                        <div class="hs-copy-test-backdrop" data-hs-copy-manual-close></div>

                                        <div class="hs-copy-test-dialog hs-copy-manual-dialog" role="dialog" aria-modal="true" aria-labelledby="hsCustomerCopyManualTitle">

                                          <button type="button" class="hs-copy-test-close" data-hs-copy-manual-close aria-label="Zavřít"><svg class="hs-copy-test-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"></path></svg></button>

                                          <div class="hs-copy-test-icon hs-copy-manual-icon"><svg class="hs-copy-test-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 17h16"></path><path d="M8 3v8M16 13v8"></path><circle cx="8" cy="14" r="2"></circle><circle cx="16" cy="10" r="2"></circle></svg></div>

                                          <h3 id="hsCustomerCopyManualTitle">Dokončit synchronizaci do HairSoft</h3>

                                          <p class="hs-copy-test-person" data-hs-i18n-ignore><?php echo htmlspecialchars($jmenoStranky, ENT_QUOTES, 'UTF-8'); ?></p>

                                          <div class="hs-copy-test-safety"><span class="hs-copy-test-safety-icon" aria-hidden="true"><svg class="hs-copy-test-svg" viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.6 2.8 7.9 7 10 4.2-2.1 7-5.4 7-10V6l-7-3Z"></path><path d="m9 12 2 2 4-4"></path></svg></span><span>Zadejte ID, které má tento zákazník už vytvořené v cílovém programu HairSoft. Tím se fotografie a soubory přiřadí ke správnému zákazníkovi.</span></div>

                                          <form method="post" action="/str/customer-copy-sync.php" id="hsCustomerCopyManualForm">

                                            <input type="hidden" name="action" value="assign_id">

                                            <input type="hidden" name="target_guid" value="<?php echo htmlspecialchars($osoba_guid, ENT_QUOTES, 'UTF-8'); ?>">

                                            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($hsMultiCsrf, ENT_QUOTES, 'UTF-8'); ?>">

                                            <label for="hsCustomerCopyHairSoftId"><span class="hs-copy-test-label-icon" aria-hidden="true"><svg class="hs-copy-test-svg" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="3"></rect><path d="M8 9h8M8 13h5M8 17h3"></path></svg></span><span>HairSoft ID zákazníka</span></label>

                                            <input class="form-control" id="hsCustomerCopyHairSoftId" name="hairsoft_id" type="text" inputmode="numeric" autocomplete="off" pattern="[0-9]+" maxlength="10" placeholder="např. 798" value="<?php echo ($hsCustomerCopyManualState && !empty($hsCustomerCopyManualState['paired'])) ? htmlspecialchars((string) $hsCustomerCopyManualState['hs_id'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>

                                            <div class="hs-copy-test-not-copy"><span class="hs-copy-test-not-copy-icon" aria-hidden="true"><svg class="hs-copy-test-svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 11v6M12 7h.01"></path></svg></span><span>Timeline se po zadání ID neuvolní hromadně. Každý řádek odešlete samostatně ikonou synchronizace v Timeline.</span></div>

                                            <div class="hs-copy-test-actions">

                                              <button type="button" class="btn btn-default" data-hs-copy-manual-close><svg class="hs-copy-test-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"></path></svg><span>Zrušit</span></button>

                                              <button type="submit" class="btn btn-info"><svg class="hs-copy-test-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg><span>Uložit ID a připravit data</span></button>

                                            </div>

                                          </form>

                                        </div>

                                      </div>

                                      <?php } ?>



<?php 

/*INFO PROUZEK O SYNCHRONIZACI*/

/*

  <div class="rada">

                                  <form action="../str/index.php?strana=KartaOsoby" method="POST" >

                                    <input type="hidden" class="form-control" name="akce" value="novy">

                                    <button type="submit" class="btn btn-success"><i class="fa icon-plus"></i>Založit osobu</button> 

                                  </form>

                                </div>

*/







?>





<form action="../str/index.php?strana=KartaOsoby" method="POST" class="form-horizontal form-border">





                <div class="row">







                  <?php 

                      if ($osoba_guid!="") {



                   ?>

                                                  <div class="col-md-2 min350" >

                                                        <div class="panel panel-default">

                                                            <div class="panel-heading">

                                                                <h3 class="panel-title"><font face="tahoma"><b>Profilovka</b></font></h3>

                                                                 <div class="actions pull-right"></div> 

                                                            </div>

                                                            

                                                            <div class="panel-body" >

                                                                <div class="form-group">

                                                                  <?php 

                                                                    



                                                                    /*DEJ jmeno profilovky*/





                                                                    if ($obrazek_jmeno_GUID=="") {

                                                                      $obrazek_jmeno_GUID= "../img/obsluhy/no_image.jpg"."?t=".rand(1, 1500);

                                                                    }else{

                                                                      $obrazek_jmeno_GUID= "strana/galerie/m".$obrazek_jmeno_GUID."?t=".rand(1, 1500);

                                                                    }

                                                                    

                                                                    

                                                                  ?>

                                                                  



                                                                  <center>

                                                                    <div class="OrizlaKulataFotkaObal">

                                                                      <div class="OrizlaKulataFotka" style="background-image: url('<?php echo $obrazek_jmeno_GUID; ?>');"></div>

                                                                    </div>

                                                                  </center>

                                                                

                                                                </div>



                                                                

                                                                



                                                          

                                                            </div>



                                                            <div class="panel-heading">

                                                                <h3 class="panel-title"><font face="tahoma"><b>Poznámka</b></font></h3>

                                                                 <div class="actions pull-right"></div> 

                                                            </div>



                                                            <div class="panel-body" style="min-height: 330px;">

                                                                



                                                                

                                                                







                                                                <div class="panel-body" style="min-height: 330px;">

                                                                <div class="form-group">

                                                                  

                                                                    

                                                                  



                                                                  <div class="textareaLidiObal">

                                                                    <textarea name="lidi_hs_note" class="textareaLidi" style=""><?php echo $lidi_hs_note; ?></textarea>

                                                                  </div>  

                                                                

                                                                </div>

                                                            </div>

                                                            </div>







                                                        </div>

                                                      </div>



                      <?php 

                        }

                       ?>







                      













                    <?php 



                      if ($osoba_guid!="") {

                        $SirkaNOveho = "10";

                      }else{

                        $SirkaNOveho = "12";

                      }





                    ?>



                    <div class="col-md-<?php echo $SirkaNOveho; ?>">

                        <div class="panel panel-default">

                            <!-- <div class="panel-heading">

                                <h3 class="panel-title"><font face="tahoma"><b>Informace</b></font></h3>

                                 <div class="actions pull-right">

                                    <i class="fa fa-expand"></i>

                                    <i class="fa fa-chevron-down"></i>

                                    <i class="fa fa-times"></i>

                                </div> 

                            </div> -->

                            





                             <div class="tab-wrapper tab-primary">

                                    <ul class="nav nav-tabs">

                                        <?php 

                                            if ($AktivniZalozkaGalerie==1) {

                                                $ZalozkaInformace = "";

                                                $ZalozkaTimeline = "";

                                                $ZalozkaGalerie = "active";

                                                $ZalozkaProgramy = "";

                                                $ZalozkaSoubory = "";

                                                $ZalozkaSMSChat = "";

                                                $ZalozkaHodnoceni = "";

                                              }elseif ($AktivniZalozkaTimeline==1) {

                                                    $ZalozkaInformace = "";

                                                    $ZalozkaTimeline = "active";

                                                    $ZalozkaGalerie = "";

                                                    $ZalozkaProgramy = "";

                                                    $ZalozkaSoubory = "";

                                                    $ZalozkaSMSChat = ""; 

                                                    $ZalozkaHodnoceni = "";

                                              }elseif ($AktivniZalozkaSoubory==1) {

                                                    $ZalozkaInformace = "";

                                                    $ZalozkaTimeline = "";

                                                    $ZalozkaGalerie = "";

                                                    $ZalozkaProgramy = "";

                                                    $ZalozkaSoubory = "active";

                                                    $ZalozkaSMSChat = ""; 

                                                    $ZalozkaHodnoceni = "";

                                              }elseif ($AktivniZalozkaProgramy==1) {

                                                    $ZalozkaInformace = "";

                                                    $ZalozkaTimeline = "";

                                                    $ZalozkaGalerie = "";

                                                    $ZalozkaProgramy = "active";

                                                    $ZalozkaSoubory = "";

                                                    $ZalozkaSMSChat = "";

                                                    $ZalozkaHodnoceni = "";



                                              }else{

                                                    $ZalozkaInformace = "active";

                                                    $ZalozkaTimeline = "";

                                                    $ZalozkaGalerie = "";

                                                    $ZalozkaProgramy = "";

                                                    $ZalozkaSoubory = "";

                                                    $ZalozkaSMSChat = "";

                                                    $ZalozkaHodnoceni = "";

                                                                                                   

                                              } 



                                         ?>  





                                        <?php 

                                            if ($osoba_guid!="") {

                                              ?>

                                                <li class="<?php echo $ZalozkaInformace; ?>"><a href="#Informace" data-toggle="tab" aria-expanded="false">Informace</a> </li>        

                                                <li class="<?php echo $ZalozkaTimeline; ?>"><a href="#Timeline" data-toggle="tab" aria-expanded="false">Timeline</a> </li>

                                                <li class="<?php echo $ZalozkaSMSChat; ?>"><a href="#SMSChat" data-toggle="tab" aria-expanded="false">SMS Chat</a> </li>

                                                <li class="<?php echo $ZalozkaGalerie; ?>"><a href="#Galerie" data-toggle="tab" aria-expanded="false">Galerie</a> </li>

                                                <li class="<?php echo $ZalozkaProgramy; ?>"><a href="#Programy" data-toggle="tab" aria-expanded="false">Programy</a> </li>

                                                <li class="<?php echo $ZalozkaHodnoceni; ?>"><a href="#Hodnoceni" data-toggle="tab" aria-expanded="false">Hodnocení</a> </li>

                                                <li class="<?php echo $ZalozkaSoubory; ?>"><a href="#Soubory" data-toggle="tab" aria-expanded="false">Soubory</a> </li> 



                                              <?php 



                                            }else{

                                               ?>

                                                <li class="<?php echo $ZalozkaInformace; ?>"><a href="#Informace" data-toggle="tab" aria-expanded="false">Informace</a> </li>

                                              <?php 

                                            }

                                         ?>

                                        

                                        

                                        

                                    </ul>

                                    <div class="tab-content">

                                        <div class="tab-pane <?php echo $ZalozkaInformace; ?>" id="Informace">

                                            









                                        



                                                                              <div class="panel-body">



                                                                                            

                                                                                              <?php 

                                                                                                  //Novy clovek prekopiruj GUID

                                                                                                  if ($osoba_guid=="" ) {

                                                                                                      $osoba_guid = GUID();

                                                                                                      $form_akce = "novy";

                                                                                                      $zamcene_combo = 0;

                                                                                                      $pridatSQLpobocka = "";

                                                                                                      $schovatPobocku = "";

                                                                                                  }else{

                                                                                                      $schovatPobocku = "display: none";

                                                                                                      $form_akce = "edit";

                                                                                                      $zamcene_combo = 1;

                                                                                                      $pridatSQLpobocka = " and `sw_info`.`sw_id` = ".$lidi_sw_id;

                                                                                                  }

                                                                                              ?>



                                                                                              <input type="hidden" class="form-control" name="osoba_guid" value="<?php echo $osoba_guid; ?>">

                                                                                              <input type="hidden" class="form-control" name="akce" value="<?php echo $form_akce; ?>">

                                                                                              

                                                                                                

                                                                                                <div class="form-group" style="<?php echo $schovatPobocku; ?>">

                                                                                                    <label class="col-sm-3 control-label">Pobočka</label>

                                                                                                    <div class="col-sm-6">

                                                                                                      <select class="form-control" name="lidi_combo_pobocka" <?php echo $zamcene_combo==1 ? 'disabled' : '' ?>>

                                                                                                            

                                                  <?php 

                                                                                                                

                                                                                                                if ($_SESSION["k_email"]!="") {

                                                                                                                                                                      

                                                                                                                   $s_email = $_SESSION["k_email"];

                                                                                                                   $select_na_pobocku= "SELECT `sw_info`.`sw_jmeno_pobocky`, `sw_info`.`sw_id` FROM `sw_email_pobocka` join `k_uzivatele` on `sw_email_pobocka`.`k_id` = `k_uzivatele`.`k_id` join `sw_info` on `sw_info`.`sw_id` = `sw_email_pobocka`.`sw_id` WHERE `k_uzivatele`.`k_email` = '$s_email' ".$pridatSQLpobocka;

                                                                                                                   if (!$select_na_pobocku) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

                                                                                                                                     



                                                                                                                    $vysledek_na_pobocku=$mysqli->query($select_na_pobocku);

                                                                                                                    $radku_na_pobocku=$vysledek_na_pobocku->num_rows;                                       



                                                                                                                     while ($na_pobocku=MySQLi_Fetch_Array($vysledek_na_pobocku)):   

                                                                                                                      $idp= $na_pobocku["sw_id"] ;

                                                                                                                      echo "<option value=\"$idp\">".$na_pobocku["sw_jmeno_pobocky"]."</option>";

                                                                                                                     endwhile;

                                                                                                         

                                                                                                                }



                                                   ?>

                                                                                                            

                                                                                                            

                                                                                                      </select>

                                                                                                    </div>

                                                                                                </div>







                                                                                                <div class="form-group">

                                                                                                    <label class="col-sm-3 control-label">Jméno </label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" type="text" name="lidi_hs_name" value="<?php echo $lidi_hs_name; ?>">

                                                                                                    </div>

                                                                                                

                                                                                                    <label class="col-sm-2 control-label">Příjmení </label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" type="text" name="lidi_hs_surname" value="<?php echo $lidi_hs_surname; ?>">

                                                                                                    </div>

                                                                                                </div>



                                                                                                <div class="form-group">

                                                                                                    <label class="col-sm-3 control-label">Titul </label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" type="text" name="lidi_hs_title" value="<?php echo $lidi_hs_title; ?>">

                                                                                                    </div>

                                                                                                    <label class="col-sm-2 control-label">Datum narození </label>

                                                                                                    <div class="col-sm-2">

                                                                                                         <?php 

                                                                                                            if ($lidi_hs_BirthDay == "01.01.1970"){

                                                                                                              $lidi_hs_BirthDay = "";

                                                                                                            };

                                                                                                         ?>

                                                                                                        <input class="form-control" id="datepicker1"  type="text" name="lidi_hs_BirthDay" value="<?php echo $lidi_hs_BirthDay; ?>">

                                                                                                    </div>



                                                                                                </div>



                                                                                                <div class="form-group">

                                                                                                     <?php 



                                                                                                                  //Anonymizace

                                                                                                                  if ($_SESSION["k_poduzivatele_skryt_citliva_data"]=="1") {

                                                                                                                    ?>

                                                                                                                                                                                                                                            <label class="col-sm-3 control-label">Email </label>

                                                                                                                      <div class="col-sm-2">

                                                                                                                          <input class="form-control" type="text" name="lidi_hs_email_hidden" value="Anonymizováno" disabled>

                                                                                                                      </div>







                                                                                                                             

                                                                                                                    <?php 





                                                                                                                  }else{

                                                                                                                    ?>         

                                                                                                                      

                                                                                                                      <label class="col-sm-3 control-label">Email </label>

                                                                                                                      <div class="col-sm-2">

                                                                                                                          <input class="form-control" type="text" name="lidi_hs_email" value="<?php echo $lidi_hs_email; ?>">

                                                                                                                      </div>

                                                                                                                    

                                                                                                                    <?php 

                                                                                                                  }

                                                                                                      ?> 







                                                                                                    

                                                                                                

                                                                                                    <label class="col-sm-2 control-label">Pohlaví</label>

                                                                                                    <div class="col-sm-2">

                                                                                                      <select class="form-control" name="lidi_hs_genre">

                                                                                                            <option value="0" <?php echo $lidi_hs_genre=="0" ? 'selected=\"selected\"' : '' ?>>Neurčeno</option>

                                                                                                            <option value="1" <?php echo $lidi_hs_genre=="1" ? 'selected=\"selected\"' : '' ?>>Muž</option>

                                                                                                            <option value="2" <?php echo $lidi_hs_genre=="2" ? 'selected=\"selected\"' : '' ?>>Žena</option>

                                                                                                            <option value="3" <?php echo $lidi_hs_genre=="3" ? 'selected=\"selected\"' : '' ?>>Dítě</option>

                                                                                                      </select>

                                                                                                    </div>

                                                                                                </div>



                                                                                                

                                                                                                <div class="form-group">

                                                                                                    <label class="col-sm-3 control-label">Ulice </label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" type="text" name="lidi_hs_street" value="<?php echo $lidi_hs_street; ?>">

                                                                                                    </div>

                                                                                                </div>



                                                                                                <div class="form-group">

                                                                                                    <label class="col-sm-3 control-label">Město </label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" type="text" name="lidi_hs_city" value="<?php echo $lidi_hs_city; ?>">

                                                                                                    </div>

                                                                                                    <label class="col-sm-2 control-label">PSČ </label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" type="text" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')" name="lidi_hs_zip" value="<?php echo $lidi_hs_zip; ?>">

                                                                                                    </div>

                                                                                                </div>

                                                                                                



                                                                                                



                                                                                                <?php 



                                                                                                      //Anonymizace

                                                                                                      if ($_SESSION["k_poduzivatele_skryt_citliva_data"]=="1") {

                                                                                                        ?>

                                                                                                          

                                                                                                          <div class="form-group">

                                                                                                              <label class="col-sm-3 control-label">Mobil </label>

                                                                                                              <div class="col-sm-2">

                                                                                                                  <input class="form-control" type="text" name="lidi_hs_cell_hidden" id="lidi_hs_cell_hidden" value="Anonymizováno" disabled>

                                                                                                                  <input class="form-control" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')" type="hidden" name="lidi_hs_cell" id="lidi_hs_cell" value="<?php echo $lidi_hs_cell; ?>">

                                                                                                              </div>

                                                                                                              <P id="MobilExistuje" style="display:none;padding-top:7px;color: red"><b>Toto telefonní číslo již existuje v databázi!</b></P>

                                                                                                          

                                                                                                              <label class="col-sm-2 control-label">Telefon </label>

                                                                                                              <div class="col-sm-2">

                                                                                                                  <input class="form-control" type="text" name="lidi_hs_phone_hidden" id="lidi_hs_phone_hidden" value="Anonymizováno" disabled>

                                                                                                                  <input class="form-control" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')" type="hidden" name="lidi_hs_phone" id="lidi_hs_phone" value="<?php echo $lidi_hs_phone; ?>">

                                                                                                              </div>

                                                                                                           </div> 

                                                                                                           

                                                                                                        <?php 





                                                                                                      }else{

                                                                                                        ?>         

                                                                                                          

                                                                                                          <div class="form-group">

                                                                                                              <label class="col-sm-3 control-label">Mobil </label>

                                                                                                              <div class="col-sm-2">

                                                                                                                  <input class="form-control" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')" type="text" name="lidi_hs_cell" id="lidi_hs_cell" value="<?php echo $lidi_hs_cell; ?>">

                                                                                                              </div>

                                                                                                              <P id="MobilExistuje" style="display:none;padding-top:7px;color: red"><b>Toto telefonní číslo již existuje v databázi!</b></P>

                                                                                                          

                                                                                                              <label class="col-sm-2 control-label">Telefon </label>

                                                                                                              <div class="col-sm-2">

                                                                                                                  <input class="form-control" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')" type="text" name="lidi_hs_phone" id="lidi_hs_phone" value="<?php echo $lidi_hs_phone; ?>">

                                                                                                              </div>

                                                                                                          </div>

                                                                                                        

                                                                                                        <?php 

                                                                                                      }

                                                                                                ?>         

<hr>        

                                                                                                  

                                                                                                <div class="form-group">

                                                                                                    <label class="col-sm-3 control-label">Rodné číslo</label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" type="text" name="lidi_hs_rc" value="<?php echo $lidi_hs_rc; ?>">

                                                                                                    </div>

                                                                                                    

                                                                                                </div>



                                                                                               <div class="form-group">

                                                                                                      <label class="col-sm-3 control-label">Pojišťovna</label>

                                                                                                      <div class="col-sm-6">



                                                                                                          <select class="form-control" name="lidi_hs_pojistovnaGUID">

                                                                                                            <option>Vyberte...</option>

                                                                                                              <?php

                                                                                                              $sql = "SELECT * FROM cislenik_pojistovny ORDER BY statPojistovny ASC";

                                                                                                              $result = $mysqli->query($sql);



                                                                                                              while ($row = $result->fetch_assoc()) {



                                                                                                                  $guid = $row["pojistovnaGUID"];

                                                                                                                  $jmeno = $row["jmenoPojistovny"];

                                                                                                                  $zkratka = $row["zkratkaPojistovny"];

                                                                                                                  $kod = $row["kodPojistovny"];



                                                                                                                  // označení vybrané hodnoty

                                                                                                                  $selected = ($lidi_hs_pojistovnaGUID == $guid) ? "selected" : "";

                                                                                                                  if ($lidi_hs_pojistovnaGUID=="") {

                                                                                                                    $selected = "";

                                                                                                                  }



                                                                                                                  echo '<option value="' . $guid . '" ' . $selected . '>';

                                                                                                                  echo $jmeno . ' (' . $zkratka . ') - ' . $kod;

                                                                                                                  echo '</option>';

                                                                                                              }

                                                                                                              ?>



                                                                                                          </select>



                                                                                                      </div>

                                                                                                  </div>



                                                                                                <div class="form-group">

                                                                                                    <label class="col-sm-3 control-label">Další záznamy (léky, alergie)</label>

                                                                                                    <div class="col-sm-6">

                                                                                                        <textarea class="form-control" 

                                                                                                                  name="lidi_hs_ostatni" 

                                                                                                                  rows="3" 

                                                                                                                  style="width: 100%;"><?php echo $lidi_hs_ostatni; ?></textarea>

                                                                                                    </div>

                                                                                                </div>

                                                                                                <hr>                                                                                                         

                                                                                                



                                                                                               

                                                                                                



                                                                                                <div class="form-group">

                                                                                                    <label class="col-sm-3 control-label">Jméno firmy </label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" type="text" name="lidi_hs_firm_name" value="<?php echo $lidi_hs_firm_name; ?>">

                                                                                                    </div>

                                                                                                    <label class="col-sm-2 control-label">IČO </label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" type="text" name="lidi_hs_firm_register_number" value="<?php echo $lidi_hs_firm_register_number; ?>">

                                                                                                    </div>

                                                                                                </div>



                                                                                                 <?php 

                                                                                                      $instagram_clean = cleanUsername($lidi_hs_skype);

                                                                                                      $facebook_clean = cleanUsername($lidi_hs_facebook);

                                                                                                      $www_clean = cleanUrl($lidi_hs_www);

                                                                                                   ?>



                                                                                                <div class="form-group">

                                                                                                    <label class="col-sm-3 control-label">

                                                                                                        <?php if (!empty($www_clean)) { ?>

                                                                                                            <a href="<?php echo $www_clean; ?>" 

                                                                                                               target="_blank" 

                                                                                                               rel="noopener noreferrer"

                                                                                                               style="color: blue; text-decoration: underline;">

                                                                                                                WWW

                                                                                                            </a>

                                                                                                        <?php } else { ?>

                                                                                                            WWW

                                                                                                        <?php } ?>

                                                                                                    </label>



                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" type="text" name="lidi_hs_www" value="<?php echo $lidi_hs_www; ?>">

                                                                                                    </div>

                                                                                                </div>



                                                                                                <div class="form-group">





                                                                                                 



                                                                                                 

                                                                                                <label class="col-sm-3 control-label">

                                                                                                    <?php if (!empty($instagram_clean)) { ?>

                                                                                                        <a href="https://www.instagram.com/<?php echo $instagram_clean; ?>" 

                                                                                                           target="_blank" 

                                                                                                           rel="noopener noreferrer"

                                                                                                           style="color: blue; text-decoration: underline;">

                                                                                                            Instagram

                                                                                                        </a>

                                                                                                    <?php } else { ?>

                                                                                                        Instagram

                                                                                                    <?php } ?>

                                                                                                </label>



                                                                                                <div class="col-sm-2">

                                                                                                    <input class="form-control" type="text" name="lidi_hs_skype" value="<?php echo $lidi_hs_skype; ?>">

                                                                                                </div>



                                                                                                <label class="col-sm-2 control-label">

                                                                                                    <?php if (!empty($facebook_clean)) { ?>

                                                                                                        <a href="https://www.facebook.com/<?php echo $facebook_clean; ?>" 

                                                                                                           target="_blank" 

                                                                                                           rel="noopener noreferrer"

                                                                                                           style="color: blue; text-decoration: underline;">

                                                                                                            Facebook

                                                                                                        </a>

                                                                                                    <?php } else { ?>

                                                                                                        Facebook

                                                                                                    <?php } ?>

                                                                                                </label>



                                                                                                <div class="col-sm-2">

                                                                                                    <input class="form-control" type="text" name="lidi_hs_facebook" value="<?php echo $lidi_hs_facebook; ?>">

                                                                                                </div>

                                                                                            </div>



                                                                                                



                                                                                            



                                                                                                <div class="form-group">

                                                                                                    <label class="col-sm-3 control-label">Blokovat SMS </label>

                                                                                                         <div class="col-sm-2">

                                                                                                            <select class="form-control" name="lidi_hs_NoSMS">

                                                                                                                  <option value="0" <?php echo $lidi_hs_NoSMS=="0" ? 'selected=\"selected\"' : '' ?>>NE</option>

                                                                                                                  <option value="1" <?php echo $lidi_hs_NoSMS=="1" ? 'selected=\"selected\"' : '' ?>>ANO</option>

                                                                                                            </select>

                                                                                                          </div>

                                                                                                

                                                                                                    <label class="col-sm-2 control-label">Číslo karty </label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" type="text" name="lidi_hs_card" value="<?php echo $lidi_hs_card; ?>">

                                                                                                    </div>

                                                                                                </div>





                                                                                                <hr>                                                                                                         

                                                                                                <div class="form-group">

                                                                                                    <label class="col-sm-3 control-label">Počet návštěv</label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" readonly="readonly" type="text" name="lidi_hs_pocet_navstev" value="<?php echo $lidi_hs_pocet_navstev; ?>">

                                                                                                    </div>

                                                                                                    <label class="col-sm-2 control-label">Zrušené objednávky </label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" readonly="readonly" type="text" name="lidi_hs_zrusene" value="<?php echo $lidi_hs_zrusene; ?>">

                                                                                                    </div>

                                                                                                </div>



                                                                                                <div class="form-group">

                                                                                                    <label class="col-sm-3 control-label">Příští návštěva</label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" readonly="readonly" type="text" name="lidi_hs_pristi_navsteva" value="<?php echo $lidi_hs_pristi_navsteva; ?>">

                                                                                                    </div>

                                                                                                    <label class="col-sm-2 control-label">Poslední návštěva </label>

                                                                                                    <div class="col-sm-2">

                                                                                                        <input class="form-control" readonly="readonly" type="text" name="lidi_hs_posledni_navsteva" value="<?php echo $lidi_hs_posledni_navsteva; ?>">

                                                                                                    </div>

                                                                                                </div>









                                                                                                



                                                                                                



                                                                                                

                                                                                                

                                                                                                <div class="form-group">

                                                                                                    <label class="col-sm-3 control-label">Poslední změna </label>

                                                                                                    <div class="col-sm-6">

                                                                                                        <input class="form-control" readonly="readonly" type="text" name="lidi_zmena" value="<?php echo date("d.m.Y H:i", strtotime($lidi_zmena));  ?>">

                                                                                                    </div>

                                                                                                </div>





                                                                                               

                                                                                               <center><button type="submit" id="UlozitZmenyKartaOsobyButton" class="btn btn-primary">Uložit změny</button></center>



                                                                                            

                                                                              </div>

                                                                      </div>



                                                            </form>







                                        <div class="tab-pane <?php echo $ZalozkaTimeline; ?>" id="Timeline">

                                                                                                





                                                                                              <?php 

                                                                                                  

                                                                                                  if ($timelineIDGET=="") {

                                                                                                    $StylTlacitka = "padding-top: 8px;";

                                                                                                    $StylTimelineFormulare = "display: none;";

                                                                                                    $TextTimeline= "" ;

                                                                                                  }else{

                                                                                                    $StylTlacitka = "display: none;";

                                                                                                    $StylTimelineFormulare = "";

                                                                                                    /*Nacte text timeline id*/

                                                                                                      $sql_existuje_timeline= "SELECT `timelineText` FROM `timeline` WHERE `timelineID` = $timelineIDGET and `timelineOsobaGuid` =  '".$osoba_guid."'";

                                                                                                      $vysledek_existuje_timeline=$mysqli->query($sql_existuje_timeline);

                                                                                                      $sql_existuje_timeline=MySQLi_Fetch_Array($vysledek_existuje_timeline);

                                                                                                      

                                                                                                    $TextTimeline= $sql_existuje_timeline["timelineText"]  ;

                                                                                                  }



                                                                                               ?>

                                                      <div style="<?php echo $StylTlacitka; ?>">

                                                        <button  type="button" id="ZobrazitDivPoznamkyTimeline" class="btn btn-success"><i class="icon-plus"></i>Nová poznámka</button>          

                                                      </div>



                                                                                              

                                                                                             



                                                                                              <form id="formPoznamky" style="<?php echo $StylTimelineFormulare ?>" action="../str/index.php?strana=KartaOsoby" method="POST" class="form-horizontal form-border">

                                                                                                <input type="hidden" class="form-control" name="osoba_guid" value="<?php echo $osoba_guid; ?>">

                                                                                                <input type="hidden" class="form-control" name="akce" value="TimelineUlozit">

                                                                                                <input type="hidden" class="form-control" name="timelineID" value="<?php echo $timelineIDGET; ?>">

                                                                                                <input type="hidden" class="form-control" name="lidi_sw_id" value="<?php echo $lidi_sw_id; ?>">

                                                                                                <input type="hidden" class="form-control" name="k_poduzivatele_id" value="<?php echo $_SESSION["k_poduzivatele_id"]; ?>">

                                                                                               

                                                                                                <div class="form-group">

                                                                                                    <label class="col-sm-3 control-label">Poznámka</label>

                                                                                                    <div class="col-sm-6">

                                                                                                        <textarea class="form-control" rows="4" name="TimelinePoznamka" ><?php echo $TextTimeline; ?></textarea>

                                                                                                    </div>

                                                                                                </div>

                                                                                                <div class="form-group">

                                                                                                    <div class="col-sm-offset-3 col-sm-6">

                                                                                                        <button type="submit" class="btn btn-primary">Uložit poznámku</button>

                                                                                                    </div>

                                                                                                </div>                                                                                               

                                                                                              </form>

                                                                                            <hr>

                                                                                            <br>



                                                                                             

                                                                        <?php 



                                                                                        $select_na_timeline= "SELECT `timelineDatumCas`,`timelineText`,`timelineObsluha`,`timelineID`,`timelineDoPCZnak`,`timelineIDHS` FROM `timeline` WHERE `timelineValid` = 1 and `timelineOsobaGuid` = '".$osoba_guid."' order by 1 DESC ";

                                                                                        //echo $select_na_chat;

                                                                                        //echo $osoba_guid;



                                                                                        if (!$select_na_timeline) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

                                                                                              $vysledek=$mysqli->query("$select_na_timeline");

                                                                                              // V207: cekajici historicka Timeline kopie je mimo produkcni tabulku,
                                                                                              // aby ji legacy HairSoft nemohl precist pred rucnim kliknutim.
                                                                                              $hsTimelineRows = array();
                                                                                              while ($hsTimelineDbRow=MySQLi_Fetch_Array($vysledek)) {
                                                                                                $hsTimelineDbRow['hs_stage'] = 0;
                                                                                                $hsTimelineDbRow['hs_stage_id'] = 0;
                                                                                                $hsTimelineRows[] = $hsTimelineDbRow;
                                                                                              }
                                                                                              if ($hsCustomerCopyTargetLog && function_exists('hsCustomerCopyTestTimelineStageRowsByLog')) {
                                                                                                $hsTimelineStageRows = hsCustomerCopyTestTimelineStageRowsByLog($mysqli, (int) $hsCustomerCopyTargetLog['id']);
                                                                                                foreach ($hsTimelineStageRows as $hsStageRow) {
                                                                                                  $hsTimelineRows[] = array(
                                                                                                    'timelineDatumCas' => $hsStageRow['timeline_datum_cas'],
                                                                                                    'timelineText' => $hsStageRow['timeline_text'],
                                                                                                    'timelineObsluha' => $hsStageRow['timeline_obsluha'],
                                                                                                    'timelineID' => 0,
                                                                                                    'timelineDoPCZnak' => 'STAGE',
                                                                                                    'timelineIDHS' => null,
                                                                                                    'hs_stage' => 1,
                                                                                                    'hs_stage_id' => (int) $hsStageRow['id']
                                                                                                  );
                                                                                                }
                                                                                              }
                                                                                              usort($hsTimelineRows, function ($a, $b) {
                                                                                                $ta = isset($a['timelineDatumCas']) ? strtotime((string) $a['timelineDatumCas']) : 0;
                                                                                                $tb = isset($b['timelineDatumCas']) ? strtotime((string) $b['timelineDatumCas']) : 0;
                                                                                                if ($ta === $tb) { return 0; }
                                                                                                return ($ta > $tb) ? -1 : 1;
                                                                                              });

                                                                                         ?>

                                                                                         <?php if ($hsCustomerCopyTargetLog && $hsCustomerCopyManualState && !empty($hsCustomerCopyManualState['manual_confirmed']) && (int) $hsCustomerCopyManualState['timeline_prepared'] > 0) { ?>
                                                                                         <div class="hs-timeline-sync-gate" role="status" aria-live="polite">
                                                                                           <span class="hs-timeline-sync-gate-icon" aria-hidden="true">
                                                                                             <svg viewBox="0 0 24 24"><path d="M20 11a8 8 0 1 0-2.34 5.66"></path><path d="M20 4v7h-7"></path></svg>
                                                                                           </span>
                                                                                           <span class="hs-timeline-sync-gate-copy">
                                                                                             <strong>Timeline čeká na dokončení synchronizace v HairSoft.</strong>
                                                                                             <small>Po ověření v HairSoft potvrďte dokončení. Teprve potom lze připravit další záznam.</small>
                                                                                           </span>
                                                                                           <form method="post" action="/str/customer-copy-sync.php" class="hs-timeline-sync-confirm-form">
                                                                                             <input type="hidden" name="action" value="timeline_confirm">
                                                                                             <input type="hidden" name="target_guid" value="<?php echo htmlspecialchars($osoba_guid, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                             <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($hsMultiCsrf, ENT_QUOTES, 'UTF-8'); ?>">
                                                                                             <button type="submit" class="hs-timeline-sync-confirm-button">
                                                                                               <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg>
                                                                                               <span>Synchronizace proběhla – povolit další</span>
                                                                                             </button>
                                                                                           </form>
                                                                                         </div>
                                                                                         <?php } ?>

                                                                                                                                                                                  

                                                                                         <div class="table-responsive">                                

                                                                                            <table  align="Center" border="0" id="zakaznici_tabulka_timeline" class="table table-striped table-bordered<?php echo $hsCustomerCopyTargetLog ? ' hs-timeline-has-manual-sync' : ''; ?>" cellspacing="0" width="99.8%" >

                                                                                              <thead>
                                                                                                <tr align="center" style=" ">
                                                                                                  <td class="hs-timeline-date"><b>Datum</b></td>
                                                                                                  <td class="hs-timeline-note"><b>Poznámka</b></td>
                                                                                                  <td class="hs-timeline-staff"><b>Obsluha</b></td>
                                                                                                  <td class="hs-timeline-detail hs-timeline-static-column"><b>Detail</b></td>
                                                                                                  <td class="hs-timeline-delete hs-timeline-static-column"><b>Akce</b></td>
                                                                                                </tr>
                                                                                              </thead>

                                                                                                <tbody>

                                                                                                               <?php



                                                                                                                foreach ($hsTimelineRows as $sms_timeline): 
                                                                                                                  $chatTimeline= $chatTimeline + 1;
                                                                                                                    ?>
                                                                                                                          <tr>
                                                                                                                            <td class="hs-timeline-date">
                                                                                                                                   <?php 
                                                                                                                                            $newDateSV = date("d.m.Y H:i", strtotime($sms_timeline["timelineDatumCas"]));
                                                                                                                                            $DatumSV = str_replace(" ", " v ", $newDateSV); 
                                                                                                                                            echo $newDateSV;
                                                                                                                                   ?>
                                                                                                                            </td>                                                                                                                            
                                                                                                                            <td class="hs-timeline-note">
                                                                                                                                   <?php 
                                                                                                                                            echo str_replace("\r\n","<BR>",$sms_timeline["timelineText"]);
                                                                                                                                   ?>                                                                                                                                     
                                                                                                                            </td>
                                                                                                                            <td class="hs-timeline-staff">
                                                                                                                                   <?php 
                                                                                                                                            echo $sms_timeline["timelineObsluha"];
                                                                                                                                   ?>                                                                                                                                     
                                                                                                                            </td>
                                                                                                                            <td class="hs-timeline-detail" align="center">
                                                                                                                              <?php if (!empty($sms_timeline['hs_stage'])) { ?>
                                                                                                                                <button type="button" class="hs-timeline-action hs-timeline-action--detail is-disabled" disabled title="Čeká na ruční synchronizaci" aria-label="Čeká na ruční synchronizaci">
                                                                                                                                  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12z"></path><circle cx="12" cy="12" r="2.6"></circle></svg>
                                                                                                                                </button>
                                                                                                                              <?php } else { ?>
                                                                                                                                <a class="hs-timeline-action hs-timeline-action--detail" href="index.php?strana=KartaOsoby&EditaceZaznamZeSystemu=1&timelineIDGET=<?php echo $sms_timeline["timelineID"]; ?>&NavratTimeline=1&osoba_guid=<?php echo $osoba_guid;  ?>" title="Detail" aria-label="Detail">
                                                                                                                                  <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12z"></path><circle cx="12" cy="12" r="2.6"></circle></svg>
                                                                                                                                </a>
                                                                                                                              <?php } ?>
                                                                                                                            </td>
                                                                                                                            <td class="hs-timeline-delete" align="center">
  <span class="hs-timeline-action-group">
    <?php if (!empty($sms_timeline['hs_stage'])) { ?>
      <?php if ($hsCustomerCopyManualState && !empty($hsCustomerCopyManualState['manual_confirmed']) && (int) $hsCustomerCopyManualState['timeline_prepared'] === 0) { ?>
      <form method="post" action="/str/customer-copy-sync.php" class="hs-timeline-sync-form">
        <input type="hidden" name="action" value="timeline_release">
        <input type="hidden" name="target_guid" value="<?php echo htmlspecialchars($osoba_guid, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="timeline_id" value="<?php echo (int) $sms_timeline['hs_stage_id']; ?>">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($hsMultiCsrf, ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" class="hs-timeline-action hs-timeline-action--sync" title="Odeslat pouze tento záznam do HairSoft" aria-label="Odeslat pouze tento záznam do HairSoft">
          <svg class="hs-timeline-sync-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11a8 8 0 1 0-2.34 5.66"></path><path d="M20 4v7h-7"></path></svg>
        </button>
      </form>
      <?php } else { ?>
      <?php $hsTimelineBlockedByPrepared = ($hsCustomerCopyManualState && (int) $hsCustomerCopyManualState['timeline_prepared'] > 0); ?>
      <button type="button" class="hs-timeline-action hs-timeline-action--sync is-disabled" disabled title="<?php echo $hsTimelineBlockedByPrepared ? 'Nejdříve potvrďte dokončení předchozí synchronizace' : 'Nejdříve potvrďte HairSoft ID ve Správě dat'; ?>" aria-label="<?php echo $hsTimelineBlockedByPrepared ? 'Nejdříve potvrďte dokončení předchozí synchronizace' : 'Nejdříve potvrďte HairSoft ID ve Správě dat'; ?>">
        <svg class="hs-timeline-sync-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11a8 8 0 1 0-2.34 5.66"></path><path d="M20 4v7h-7"></path></svg>
      </button>
      <?php } ?>
    <?php } else { ?>
      <?php if ($hsCustomerCopyTargetLog && (string) $sms_timeline["timelineDoPCZnak"] === HS_CUSTOMER_TIMELINE_HOLD && empty($sms_timeline["timelineIDHS"])) { ?>
        <?php if ($hsCustomerCopyManualState && !empty($hsCustomerCopyManualState['manual_confirmed']) && (int) $hsCustomerCopyManualState['timeline_prepared'] === 0) { ?>
        <form method="post" action="/str/customer-copy-sync.php" class="hs-timeline-sync-form">
          <input type="hidden" name="action" value="timeline_release">
          <input type="hidden" name="target_guid" value="<?php echo htmlspecialchars($osoba_guid, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="timeline_id" value="<?php echo (int) $sms_timeline["timelineID"]; ?>">
          <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($hsMultiCsrf, ENT_QUOTES, 'UTF-8'); ?>">
          <button type="submit" class="hs-timeline-action hs-timeline-action--sync" title="Připravit tento starší HOLD záznam pro HairSoft" aria-label="Připravit tento starší HOLD záznam pro HairSoft">
            <svg class="hs-timeline-sync-svg" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11a8 8 0 1 0-2.34 5.66"></path><path d="M20 4v7h-7"></path></svg>
          </button>
        </form>
        <?php } ?>
      <?php } ?>
      <a class="hs-timeline-action hs-timeline-action--delete" onClick="if(!confirm('Opravdu chcete smazat tento záznam? Záznam bude trvale odstraněn ze systému.')){return false;}" href="index.php?strana=KartaOsoby&NavratTimeline=1&SmazaniZaznamZeSystemu=1&timelineIDGET=<?php echo $sms_timeline["timelineID"]; ?>&osoba_guid=<?php echo $osoba_guid ?>" title="Smazat" aria-label="Smazat">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"></path><path d="M8 6V4h8v2"></path><path d="m19 6-1 15H6L5 6"></path><path d="M10 11v5M14 11v5"></path></svg>
      </a>
    <?php } ?>
  </span>
</td>
                                                                                                                          </tr>

                                                                                                                <?php

                                                                                                                 endforeach;

                                                                                                                ?>

                                                                                              </tbody>

                                                                                            </table>

                                                                                          </div>

                                        </div>



                                        <div class="tab-pane <?php echo $ZalozkaSMSChat; ?>" id="SMSChat">



                                                  <?php 













                                                                                                 

                                                                                        

                                                                                       //SQL pro chat

                                                                                        $IDSKUPINY =  $_SESSION["skupina_id"];

                                                                                        //echo $lidi_hs_hone;

                                                                                        //echo $IDSKUPINY;

                                                                                        $MOBIL = $lidi_hs_cell;

                                                                                        $upraven_mobil =  substr($MOBIL,-9);

                                                                                        $upraven_mobil_mezery = substr($upraven_mobil,0,3) ." ". substr($upraven_mobil,3,3)." ".substr($upraven_mobil,6,3); 

                                                                                        $MOBIL = $upraven_mobil;

                                                                                        $datum_od_kdy = "2022-01-01" ;





                                                                                        if ($MOBIL=="") {

                                                                                          $MOBIL = "9999999999999";

                                                                                        }

                                                                                                 

                                                                                                





                                                                                        $select_na_chat= "SELECT `sms_pri_cas_prichodu` as 'CAS' ,'' as 'SALON',`sms_pri_text` as 'ZAKAZNIK','' as 'STAV' FROM `sms_prichozi_sms` WHERE `sms_sablona_typ` not in ('B','A') and  `sms_pri_mobil` LIKE '%$MOBIL' and `sms_pri_salon_ID` = '$IDSKUPINY' and `sms_pri_cas_prichodu` > '$datum_od_kdy' union SELECT `hovory_cas`, 'ZMH' ,'' ,'' FROM `sms_hovory` WHERE `hovory_telefon` LIKE '%$MOBIL' and `hovory_salonID` = '$IDSKUPINY' and `hovory_cas` > '$datum_od_kdy' union SELECT IFNULL(`sms_skutecne_odeslana`, `sms_od_date` ),`sms_od_text` ,'', CAST(`sms_stav` as CHAR(2)) FROM `sms_odeslane_fronta` WHERE `sms_sablona_typ` not in ('B','A') and  `sms_mobil` LIKE '%$MOBIL' and `sms_skupina_ID` = '$IDSKUPINY' and (`sms_skutecne_odeslana` > '$datum_od_kdy' or `sms_od_date` > '$datum_od_kdy' )  union SELECT IFNULL(`sms_skutecne_odeslana`, `sms_od_date` ),`sms_od_text` ,'', CAST(`sms_stav` as CHAR(2)) FROM `sms_odeslane_fronta_archiv` WHERE `sms_sablona_typ` not in ('B','A') and `sms_mobil` LIKE '%$MOBIL' and `sms_skupina_ID` = '$IDSKUPINY' and (`sms_skutecne_odeslana` > '$datum_od_kdy' or `sms_od_date` > '$datum_od_kdy' ) order by 1 DESC ";

                                                                                        //echo $select_na_chat;





                                                                                        if (!$select_na_chat) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

                                                                                              $vysledek=$mysqli->query("$select_na_chat");  

                                                                                       

                                                                                         ?>

                                                                                           

                                                                                        



                                                                                        



                                                                                         <div class="table-responsive">                                

                                                                                            <table  align="Center" border="0" id="zakaznici_tabulka_chat" class="table table-striped table-bordered" cellspacing="0" width="99.8%" >

                                                                                              <tr align="center" style=" ">

                                                                                                <td ><b>Datum</b></td>

                                                                                                <td style="max-width: 450px;"><b><?php echo $pobocka_jmeno; ?></b></td>

                                                                                                <td><b><?php echo $lidi_hs_name." ". $lidi_hs_surname; ?></b></td>

                                                                                              </tr>

                                                                                          <?php



                                                                                                while ($sms_chat=MySQLi_Fetch_Array($vysledek)): 

                                                                                                  $chatCNT = $chatCNT + 1;

                                                                                                    ?>

                                                                                                          <tr>

                                                                                                            <td  valign="bottom"  >

                                                                                                                  <?php

                                                                                                                         $date = new DateTime($sms_chat["CAS"]);

                                                                                                                         //echo $date->format('d.m.Y H:i');

                                                                                                                         echo str_replace(" ", "&nbsp;", $date->format('d.m.Y H:i'))

                                                                                                                  ?>  

                                                                                                                            

                                                                                                            </td>



                                                                                                            <td style="max-width: 450px; white-space: normal;">

                                                                                                                    <?php

                                                                                                                       ///ZMESKANY HOVOR

                                                                                                                        if (trim($sms_chat["SALON"])!="") {

                                                                                                                          if ($sms_chat["SALON"]=="ZMH") {

                                                                                                                          

                                                                                                                                      //echo "<img src=\"img/telefon_chat.png\" border=\"0\" style=\"top:-2px;margin-right: 10px;\" >";

                                                                                                                                      echo "<i style=\"margin-right: 10px;\" class=\"fa icon-call-in\"></i> ";



                                                                                                                                      if ($sms_chat["SALON"]=="ZMH") {

                                                                                                                                        echo " Zmeškaný hovor: &nbsp;".str_replace(" ", "&nbsp;", $upraven_mobil_mezery);

                                                                                                                                      }else {

                                                                                                                                        echo $sms_chat["SALON"];  

                                                                                                                                    }

                                                                                                                             ?>

                                                                                                                                 

                                                                                                                              



                                                                                                                       <?php

                                                                                                                          }else {

                                                                                                                      

                                                                                                                                          switch ($sms_chat["STAV"]) {

                                                                                                                                           case "0":

                                                                                                                                               echo "<i title=\"Čeká na odeslání\"  style=\"margin-right: 10px;\" class=\"fa icon-clock \"></i> ";

                                                                                                                                               //echo "<img src=\"img/time.gif\" border=\"0\">";                                                                          

                                                                                                                                             break;

                                                                                                                                           case "1":

                                                                                                                                               echo "<i title=\"SMS Odeslána\" style=\"margin-right: 10px;\" class=\"fa fa-check-square-o \"></i> ";

                                                                                                                                               //echo "<img src=\"img/ok.png\" border=\"0\">";                                                                          

                                                                                                                                             break;

                                                                                                                                           case "9":

                                                                                                                                               echo "<i title=\"SMS stornována\" style=\"margin-right: 10px;\" class=\"fa  fa-times \"></i> ";

                                                                                                                                               //echo "<img src=\"img/delete.png\" border=\"0\">";                                                                          

                                                                                                                                             break;

                                                                                                                                           }



                                                                                                                                           echo $sms_chat["SALON"];  

                                                                                                                      

                                                                                                                          }

                                                                                                                        }

                                                                                                                      ?>   

                                                                                                            </td>

                                                                                                            

                                                                                                            <td style="/*min-width: 200px;*/">

                                                                                                                    

                                                                                                                    <?php

                                                                                                                        



                                                                                                                        if (trim($sms_chat["ZAKAZNIK"])!="") {

                                                                                                                            echo "<i title=\"SMS od zákazníka\" style=\"margin-right: 10px;\" class=\"fa   fa-comments \"></i> ";

                                                                                                                            echo $sms_chat["ZAKAZNIK"];            

                                                                                                                        }

                                                                                                                    ?>      

                                                                                                            </td>

                                                                                                          </tr>

                                                                                                <?php

                                                                                                 endwhile;

                                                                                                ?>

                                                                                            </table>



                                                                                            </div>

                                                                                   

                                                                             

                                                                                        

                                                                                       













































                                        </div>



                                        



                                        <div class="tab-pane <?php echo $ZalozkaGalerie; ?>" id="Galerie">

                                           

                                            <div class="row">



                                              

                                              <?php 



                                                        $select_na_obrazek = "SELECT `obrazek_jmeno_GUID`,`obrazek_stazeno` FROM `klient_lidi_obrazky` WHERE `obrazek_smazan` = 0 and `obrazek_osoba_GUID` = '".$osoba_guid."'";                                                                                                                  

                                                        //echo $select_na_obrazek;

                                                        $vysledek_na_obrazek=$mysqli->query($select_na_obrazek);

                                                        $radku_na_obrazek=$vysledek_na_obrazek->num_rows;                                       



                                                        if ($radku_na_obrazek > 0 ) {

                                                            $JmenoObrazku = "";

                                                            $CestaObrazky = "https://klient.hairsoft.cz/str/strana/galerie/";

                                                            while ($na_obrazek=MySQLi_Fetch_Array($vysledek_na_obrazek)):   

                                                              

                                                              $JenJmenoObrazku = $na_obrazek["obrazek_jmeno_GUID"] ;

                                                              $JmenoObrazku = $CestaObrazky.$na_obrazek["obrazek_jmeno_GUID"] ;

                                                              $JmenoObrazkuMiniatura = $CestaObrazky."m".$na_obrazek["obrazek_jmeno_GUID"] ;

                                                              $obrazek_stazeno = $na_obrazek["obrazek_stazeno"] ;



                                                              if ($obrazek_stazeno==0 or $obrazek_stazeno==HS_CUSTOMER_PHOTO_HOLD) {

                                                                //nestazen

                                                                $StylGalerie = "thumbnail-nestazen";

                                                              }else{

                                                                //stazen

                                                                $StylGalerie = "thumbnail-stazen";

                                                              }





                                                              

                                                              

                                                              echo "<div class=\"col-sm-3\" >";

                                                                echo "<div class=\"gallery\" >";

                                                                echo "<img src=\"".$JmenoObrazkuMiniatura."?t=".rand(1, 1500)."\" id=\"ObrazekGalerie\" class=\"".$StylGalerie."\" oncontextmenu=\"showContextMenu(event,'".$JenJmenoObrazku."' )\" onclick=\"openModalGalerie('".$JmenoObrazku."?t=".rand(1, 1500)."')\">";

                                                                  echo "<div id=\"context_menu_galerie\"  class=\"context_menu_galerie_styl\" style=\"display:none;position:absolute;\" >";

                                                                    echo "<ul>";

                                                                      echo "<li><a href=\"#\" onclick=\"NastavitJakoProfilovku()\">Nastavit jako profilovku</a></li>";

                                                                      echo "<li><a href=\"#\" onclick=\"OtocitDoprava()\">Otočit doprava</a></li>";

                                                                      echo "<li><a href=\"#\" onclick=\"OtocitDoleva()\">Otočit doleva</a></li>";

                                                                      echo "<li><a href=\"#\" onclick=\"SmazatSoubor()\">Smazat obrázek</a></li>";

                                                                    echo "</ul>";

                                                                  echo "</div>";

                                                                echo "</div>";

                                                              echo "</div>";

                                                              

                                                              







                                                            endwhile;  

                                                        }



                                               ?>



                                               





                                              <div id="myModal" class="modal" onclick="closeModalGalerie()">

                                                <img class="modal-content" id="modalImg" onclick="closeModalGalerie()">

                                              </div>

                                             </div>



                                            </div>









                                            <div class="tab-pane <?php echo $ZalozkaProgramy; ?>" id="Programy" data-hs-programs-loaded="<?php echo $AktivniZalozkaProgramy == 1 ? '1' : '0'; ?>" data-hs-customer-guid="<?php echo htmlspecialchars($osoba_guid, ENT_QUOTES, 'UTF-8'); ?>">

                                              <section class="hs-client-programs" aria-labelledby="hs-programs-title">
                                                <div class="hs-programs-panel">
                                                  <div class="hs-programs-panel__heading">
                                                    <div class="hs-programs-panel__heading-copy">
                                                      <span class="hs-programs-eyebrow">Programy zákazníka</span>
                                                      <h3 id="hs-programs-title">Programy</h3>
                                                    </div>

                                                    <?php if (isset($hsProgramDetail) && count($hsProgramDetail['programs']) > 1) { ?>
                                                      <form class="hs-programs-picker" method="get" action="index.php" data-hs-programs-picker-form>
                                                        <input type="hidden" name="strana" value="KartaOsoby">
                                                        <input type="hidden" name="osoba_guid" value="<?php echo htmlspecialchars($osoba_guid, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <input type="hidden" name="NavratProgramy" value="1">
                                                        <label for="hs-program-detail-select">Program</label>
                                                        <select id="hs-program-detail-select" name="hs_program_id" data-hs-program-select>
                                                          <?php foreach ($hsProgramDetail['programs'] as $hsProgramOption) { ?>
                                                            <option value="<?php echo (int) $hsProgramOption['id']; ?>"<?php echo ((int) $hsProgramOption['id'] === (int) $hsProgramDetail['selectedProgramId']) ? ' selected' : ''; ?>><?php echo htmlspecialchars($hsProgramOption['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                          <?php } ?>
                                                        </select>
                                                      </form>
                                                    <?php } elseif (isset($hsProgramSelected) && is_array($hsProgramSelected)) { ?>
                                                      <div class="hs-programs-single-name" title="<?php echo htmlspecialchars($hsProgramSelected['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?php echo htmlspecialchars($hsProgramSelected['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                      </div>
                                                    <?php } ?>
                                                  </div>

                                                  <?php if ($AktivniZalozkaProgramy != 1) { ?>
                                                    <div class="hs-programs-empty hs-programs-empty--lazy">
                                                      <span class="hs-programs-empty__icon" aria-hidden="true">
                                                        <svg viewBox="0 0 24 24"><path d="M6 3h12v18H6z"></path><path d="M9 7h6M9 11h6M9 15h4"></path></svg>
                                                      </span>
                                                      <strong>Programy se načtou po otevření této sekce.</strong>
                                                      <span>Ostatní části detailu zákazníka se kvůli Programům už nenačítají znovu.</span>
                                                    </div>
                                                  <?php } elseif (!isset($hsProgramSelected) || !is_array($hsProgramSelected)) { ?>
                                                    <div class="hs-programs-empty">
                                                      <span class="hs-programs-empty__icon" aria-hidden="true">
                                                        <svg viewBox="0 0 24 24"><path d="M6 3h12v18H6z"></path><path d="M9 7h6M9 11h6M9 15h4"></path></svg>
                                                      </span>
                                                      <strong>Zákazník nemá žádný program.</strong>
                                                      <span>Jakmile HairSoft eviduje předplacené vstupy, docházku nebo vlastní hodnoty programu, zobrazí se zde automaticky.</span>
                                                    </div>
                                                  <?php } else { ?>
                                                    <div class="hs-programs-content">
                                                      <div class="hs-programs-summary" aria-label="Souhrn programu">
                                                        <div class="hs-programs-stat">
                                                          <span>Předplaceno</span>
                                                          <strong><?php echo (int) $hsProgramSelected['prepaid']; ?></strong>
                                                        </div>
                                                        <div class="hs-programs-stat">
                                                          <span>Vyčerpáno</span>
                                                          <strong><?php echo (int) $hsProgramSelected['used']; ?></strong>
                                                        </div>
                                                        <div class="hs-programs-stat hs-programs-stat--remaining">
                                                          <span>Zbývá</span>
                                                          <strong><?php echo (int) $hsProgramSelected['remaining']; ?></strong>
                                                        </div>
                                                        <div class="hs-programs-stat">
                                                          <span>Částka celkem</span>
                                                          <strong><?php echo number_format((float) $hsProgramSelected['amount'], 2, ',', ' '); ?> Kč</strong>
                                                        </div>
                                                      </div>

                                                      <div class="hs-programs-columns">
                                                        <section class="hs-programs-card">
                                                          <div class="hs-programs-card__heading">
                                                            <div>
                                                              <span>Docházka</span>
                                                              <strong><?php echo (int) $hsProgramSelected['used']; ?></strong>
                                                            </div>
                                                          </div>
                                                          <?php if (!empty($hsProgramSelected['visits'])) { ?>
                                                            <div class="hs-programs-table-wrap">
                                                              <table class="hs-programs-table">
                                                                <thead><tr><th>Datum</th><th>Počet</th></tr></thead>
                                                                <tbody>
                                                                  <?php foreach ($hsProgramSelected['visits'] as $hsProgramVisit) { ?>
                                                                    <tr>
                                                                      <td><?php echo $hsProgramVisit['visit'] !== '' ? date('d.m.Y H:i', strtotime($hsProgramVisit['visit'])) : '—'; ?></td>
                                                                      <td><?php echo (int) $hsProgramVisit['quantity']; ?></td>
                                                                    </tr>
                                                                  <?php } ?>
                                                                </tbody>
                                                              </table>
                                                            </div>
                                                          <?php } else { ?>
                                                            <div class="hs-programs-card__empty">Zatím bez docházky.</div>
                                                          <?php } ?>
                                                        </section>

                                                        <section class="hs-programs-card">
                                                          <div class="hs-programs-card__heading">
                                                            <div>
                                                              <span>Předplacené vstupy</span>
                                                              <strong><?php echo (int) $hsProgramSelected['prepaid']; ?></strong>
                                                            </div>
                                                          </div>
                                                          <?php if (!empty($hsProgramSelected['payments'])) { ?>
                                                            <div class="hs-programs-table-wrap">
                                                              <table class="hs-programs-table hs-programs-table--payments">
                                                                <thead><tr><th>Datum</th><th>Částka</th><th>DPH</th><th>Vstupů</th></tr></thead>
                                                                <tbody>
                                                                  <?php foreach ($hsProgramSelected['payments'] as $hsProgramPayment) { ?>
                                                                    <tr>
                                                                      <td><?php echo $hsProgramPayment['created'] !== '' ? date('d.m.Y H:i', strtotime($hsProgramPayment['created'])) : '—'; ?></td>
                                                                      <td><?php echo number_format((float) $hsProgramPayment['price'], 2, ',', ' '); ?> Kč</td>
                                                                      <td><?php echo (int) $hsProgramPayment['vat']; ?> %</td>
                                                                      <td><?php echo (int) $hsProgramPayment['visits']; ?></td>
                                                                    </tr>
                                                                  <?php } ?>
                                                                </tbody>
                                                              </table>
                                                            </div>
                                                          <?php } else { ?>
                                                            <div class="hs-programs-card__empty">Zatím bez předplacených vstupů.</div>
                                                          <?php } ?>
                                                        </section>
                                                      </div>

                                                      <?php if (!empty($hsProgramSelected['values'])) { ?>
                                                        <section class="hs-programs-values">
                                                          <div class="hs-programs-values__heading">Údaje programu</div>
                                                          <div class="hs-programs-values__grid">
                                                            <?php foreach ($hsProgramSelected['values'] as $hsProgramValue) { ?>
                                                              <div class="hs-programs-value">
                                                                <span><?php echo htmlspecialchars($hsProgramValue['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                                <strong><?php echo trim((string) $hsProgramValue['value']) !== '' ? nl2br(htmlspecialchars($hsProgramValue['value'], ENT_QUOTES, 'UTF-8')) : '—'; ?></strong>
                                                              </div>
                                                            <?php } ?>
                                                          </div>
                                                        </section>
                                                      <?php } ?>
                                                    </div>
                                                  <?php } ?>
                                                </div>
                                              </section>

                                            </div>



                                            <div class="tab-pane <?php echo $ZalozkaHodnoceni; ?>" id="Hodnoceni">

                                              <div class="row">



                                                

                                                        <div class="col-md-12">

                                                              <div class="panel panel-default" >

                                                                <div class="panel-heading">

                                                                    <h3 class="panel-title"><font face="tahoma"><b>Přehled hodnocení</b></font></h3>

                                                                    <div class="actions pull-right">

                                                                 





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

                                                                                       $select_na_hodnoceni= "SELECT * FROM `HodnoceniHlavni` WHERE `sw_id` = ".$sw_id." and KlientGuid = '".$osoba_guid."' and `HodnoceniStav` = 1  order by 1 desc";

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



                                            </div>



















































                                       <div class="tab-pane <?php echo $ZalozkaSoubory; ?>" id="Soubory">



                                          <h3><b>Nahrání souboru</b></h3>

                                          <br>



                                         <?php if ($zprava): ?>

                                            <div style="

                                                padding: 12px 15px;

                                                border-radius: 8px;

                                                margin-bottom: 10px;

                                                font-size: 14px;

                                                font-weight: 500;

                                                background: #e8f5e9;

                                                color: #2e7d32;

                                                border: 1px solid #66bb6a;

                                                box-shadow: 0 2px 6px rgba(0,0,0,0.08);

                                            ">

                                                <?php echo $zprava; ?>

                                            </div>

                                        <?php endif; ?>



                                        <?php if ($chyba): ?>

                                            <div style="

                                                padding: 12px 15px;

                                                border-radius: 8px;

                                                margin-bottom: 10px;

                                                font-size: 14px;

                                                font-weight: 500;

                                                background: #ffebee;

                                                color: #c62828;

                                                border: 1px solid #ef5350;

                                                box-shadow: 0 2px 6px rgba(0,0,0,0.08);

                                            ">

                                                <?php echo $chyba; ?>

                                            </div>

                                        <?php endif; ?>



                                       <form method="post" action="index.php?strana=KartaOsoby&amp;osoba_guid=<?php echo rawurlencode($osoba_guid); ?>&amp;NavratSoubory=1" enctype="multipart/form-data">



                                            <input type="hidden" class="form-control" name="osoba_guid" value="<?php echo $osoba_guid;?>">



                                            <div class="upload-container">



                                                

                                                    <button type="button" style="position:absolute; left:-9999px;" id="btnSelect">Vybrat soubor ručně</button>

                                                



                                                <div class="upload-right" id="dropZone">

                                                    Přetáhni soubor sem <br>

                                                    Max velikost 30MB  

                                                </div>



                                            </div>



                                            <!-- jeden input -->

                                            <input type="file" name="file" id="fileInput" required style="position:absolute; left:-9999px;">



                                        </form>



                                          <br>





                                          <div class="table-responsive">                                

                                          

                                          <table  align="CENTER" border="0" id="zakaznici_tabulka_soubory" class="table table-striped table-bordered" cellspacing="0" width="99.8%" >



                                              <tr>

                                                  <th >Název</th>

                                                  <th >Velikost</th>

                                                  <th >Datum</th>

                                                  <th >Stav</th>

                                                  <th ></th>

                                                  

                                              </tr>



                                              <?php $sql = "SELECT * FROM soubory WHERE guid_cloveka = '".$osoba_guid_get."' AND stav <> 9 ORDER BY datum_nahrani DESC";



                                                if (!$sql) {

                                                    die('Chyba SQL stringu');

                                                }



                                                $result = $mysqli->query($sql);



                                                if (!$result) {

                                                    die("Chyba dotazu: " . $mysqli->error);

                                                }



                                                while ($row = mysqli_fetch_array($result)) {



                                                    $nazev = $row['puvodni_nazev'];

                                                    $GUIDSouboru = $row['guid_souboru'];

                                                    $ulozeny_nazev = $row['ulozeny_nazev'];



                                                    $velikost = round($row['velikost']/1024,2);

                                                    //$datum = $row['datum_nahrani'];

                                                    //$newDateSV = date("d.m.Y H:i", strtotime($row['datum_nahrani']);

                                                    $datum = date("d.m.Y H:i", strtotime($row['datum_nahrani']));



                                                    $stavHodnota = (string)($row['stav'] ?? '');

                                                    $stavNazev = '';

                                                    $stavIkona = '';

                                                    $stavBarva = '#6c757d';



                                                    switch ($stavHodnota) {

                                                        case '2':

                                                            $stavNazev = 'Čeká na ID HairSoft';

                                                            $stavIkona = 'fa-clock-o';

                                                            $stavBarva = '#f0ad4e';

                                                            break;

                                                        case '1':

                                                            $stavNazev = 'Staženo';

                                                            $stavIkona = 'fa-check-circle';

                                                            $stavBarva = '#28a745';

                                                            break;

                                                        case '9':

                                                            $stavNazev = 'Smazáno';

                                                            $stavIkona = 'fa-trash';

                                                            $stavBarva = '#dc3545';

                                                            break;

                                                        default:

                                                            $stavNazev = 'Nestaženo';

                                                            $stavIkona = 'fa-clock-o';

                                                            $stavBarva = '#f0ad4e';

                                                            break;

                                                    }



                                                    $stavNazevEsc = htmlspecialchars($stavNazev, ENT_QUOTES, 'UTF-8');



                                                    echo "<tr>";

                                                    //echo "<td style='vertical-align: middle;'>$nazev</td>";

                                                    if ($stavHodnota === '1') {

                                                        echo "<td style='vertical-align: middle;'>" . htmlspecialchars($nazev, ENT_QUOTES, 'UTF-8') . "</td>";

                                                    } else {

                                                        $nazevEsc = htmlspecialchars($nazev, ENT_QUOTES, 'UTF-8');
                                                        echo "<td style='vertical-align: middle;'><a href='strana/Soubory/$ulozeny_nazev' download='".$nazevEsc."' style='text-decoration: underline;' >$nazevEsc</a></td>";

                                                    }

                                                    echo "<td style='vertical-align: middle;'>$velikost KB</td>";

                                                    echo "<td style='vertical-align: middle;'>$datum</td>";

                                                    echo "<td align='center' style='vertical-align: middle;'><i class='fa $stavIkona' style='color: $stavBarva; font-size: 22px;' title='$stavNazevEsc'></i></td>";



                                                    ?>



                                                    <td align="center" style='vertical-align: middle;'>

                                                        <form action="../str/index.php?strana=KartaOsoby" method="POST" >

                                                            <input type="hidden" class="form-control" name="akce" value="smazatSoubor">

                                                            <input type="hidden" class="form-control" name="osoba_guid" value="<?php echo $osoba_guid_get;?>">

                                                            <input type="hidden" class="form-control" name="guid_souboru" value="<?php echo $GUIDSouboru;?>">

                                                            <input type="hidden" class="form-control" name="ulozeny_nazev" value="<?php echo $ulozeny_nazev;?>">

                                                            <button title="Smazat soubor" type="submit" onClick="if(!confirm('Opravdu chcete smazat tento soubor? Soubor bude smazán také v programu HairSoft.')){return false;}" class="btn btn-danger"><i class="fa icon-trash"></i></button>
                                                        </form>

                                                    </td>                                                  

                                                    

                                                    <?php 

                                                    

                                                    

                                                    echo "</tr>";

                                                }



                                                 ?>



                                                 







                                          </table>

                                        </div>

                                      </div>























                                           

                                        </div>











                                    </div>

                                </div>

                                       





                    </div>

                  </div>



              























  </div>



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



<script type="text/javascript">

 

  /*var objekty = document.querySelectorAll(".gallery");





function zjistiSouradnice(event) {

  event.preventDefault(); // Zabrání výchozímu chování kontextového menu

  var boundingRect = this.getBoundingClientRect(); // Získání rozměrů a pozice objektu v rámci okna

  var x = event.clientX - boundingRect.left; // Výpočet souřadnice x myši v rámci objektu

  var y = event.clientY - boundingRect.top; // Výpočet souřadnice y myši v rámci objektu

  alert("Souřadnice myši v rámci objektu: " + x + ", " + y);

}



for (var i = 0; i < objekty.length; i++) {

  objekty[i].addEventListener("contextmenu", zjistiSouradnice);

}

*/





 

  document.getElementById("cameraFileInput").addEventListener("change", function () {

   document.getElementById("pictureFromCamera").setAttribute("src", window.URL.createObjectURL(this.files[0]));    



    $("#DIV_Fotoaparatu").show();  



    //$("#TlacitkaUkladaniFotek").show();      

   



  });



  

 function openModalGalerie(filename) {

    var modal = document.getElementById("myModal");

    var modalImg = document.getElementById("modalImg");

    modal.style.display = "block";

    modalImg.src = filename;

    scrollToTop();

  }



  function closeModalGalerie() {

    var modal = document.getElementById("myModal");

    modal.style.display = "none";

  }





  function scrollToTop() {

    const currentScroll = document.documentElement.scrollTop || document.body.scrollTop;

    if (currentScroll > 0) {

      window.requestAnimationFrame(scrollToTop);

      window.scrollTo(0, currentScroll - (currentScroll / 8));

    }

  }



var JmenoObrazkuProPraci;



function showContextMenu(event,JmenoSouboru) {

  //console.log(jmenoObrazku);

  event.preventDefault();

  //alert(JmenoSouboru);

  JmenoObrazkuProPraci = JmenoSouboru;

  var ObrazekGalerie = document.getElementById("ObrazekGalerie");

  var boundingRect = ObrazekGalerie.getBoundingClientRect(); // Získání rozměrů a pozice objektu v rámci okna



  var contextMenu = document.getElementById("context_menu_galerie");

  

  contextMenu.style.display = "block";

  contextMenu.style.left = event.clientX - boundingRect.left + "px";

  contextMenu.style.top = event.clientY - boundingRect.top + "px";

  

  // schovat menu po kliknuti mimo ne

  document.onclick = function(event) {

    if (!event.target.closest('#contextMenu')) {

      contextMenu.style.display = "none";

      document.onclick = null;

    }

  }

}







 



function NastavitJakoProfilovku() {

  

  window.location.href = "https://klient.hairsoft.cz/str/index.php?strana=KartaOsoby&osoba_guid=<?php echo $osoba_guid; ?>&NastavitJakoProfilovku=1&JmenoSouboru="+JmenoObrazkuProPraci;

  JmenoObrazkuProPraci = "";

  

}



function SmazatSoubor(JmenoSouboru) {

  function potvrditSmazaniObrazku() {
      window.location.href = "https://klient.hairsoft.cz/str/index.php?strana=KartaOsoby&osoba_guid=<?php echo $osoba_guid; ?>&SmazatSoubor=1&JmenoSouboru="+JmenoObrazkuProPraci;
      JmenoObrazkuProPraci = "";
  }

  if (window.hsConfirm) {
    window.hsConfirm('Opravdu chcete smazat tento obrázek? Obrázek bude trvale odstraněn.', potvrditSmazaniObrazku);
  } else if (confirm('Opravdu chcete smazat tento obrázek? Obrázek bude trvale odstraněn.')) {
    potvrditSmazaniObrazku();
  }
  
}




function OtocitDoprava(JmenoSouboru) {

      window.location.href = "https://klient.hairsoft.cz/str/strana/NahratSoubor.php?GETosoba_guid=<?php echo $osoba_guid; ?>&GETOtocitSoubor=Doprava&GETJmenoSouboru="+JmenoObrazkuProPraci;

      JmenoObrazkuProPraci = "";

}



function OtocitDoleva(JmenoSouboru) {

      window.location.href = "https://klient.hairsoft.cz/str/strana/NahratSoubor.php?GETosoba_guid=<?php echo $osoba_guid; ?>&GETOtocitSoubor=Doleva&GETJmenoSouboru="+JmenoObrazkuProPraci;

      JmenoObrazkuProPraci = "";

}





function DopravaButtonFce() {

  var img = document.getElementById("pictureFromCamera");

  img.style.transform = "rotate(90deg)";

  document.getElementById('otoceni').value ="90";

  img = "";

}



function DolevaButtonFce() {

  var img = document.getElementById("pictureFromCamera");

  img.style.transform = "rotate(-90deg)";

  document.getElementById('otoceni').value ="-90";

  img = "";

}



function VychoziButtonFce() {

  var img = document.getElementById("pictureFromCamera");

  img.style.transform = "rotate(0deg)";

  document.getElementById('otoceni').value ="0";

  img = "";

}







document.addEventListener("DOMContentLoaded", function () {



    const fileInput = document.getElementById("fileInput");

    const dropZone = document.getElementById("dropZone");

    const btnSelect = document.getElementById("btnSelect");

    const form = fileInput.form;



    // otevření dialogu

    btnSelect.addEventListener("click", () => fileInput.click());

    dropZone.addEventListener("click", () => fileInput.click());



    // drag & drop ochrana

    ['dragenter','dragover','dragleave','drop'].forEach(eventName => {

        dropZone.addEventListener(eventName, e => {

            e.preventDefault();

            e.stopPropagation();

        });

    });



    dropZone.addEventListener("dragover", () => {

        dropZone.classList.add("dragover");

    });



    dropZone.addEventListener("dragleave", () => {

        dropZone.classList.remove("dragover");

    });



    // DROP → nastav soubor + auto submit

    dropZone.addEventListener("drop", (e) => {

        dropZone.classList.remove("dragover");



        const files = e.dataTransfer.files;



        if (files.length > 0) {

            fileInput.files = files;

            form.submit(); // 🚀 automatický upload

        }

    });



    // SELECT → auto submit

    fileInput.addEventListener("change", () => {

        if (fileInput.files.length > 0) {

            form.submit(); // 🚀 automatický upload

        }

    });



});





</script>
