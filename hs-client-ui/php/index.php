<?php
// Přímé otevření moderní vrstvy přesměruje na veřejný vstup.
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    header('Location: ../../str/index.php');
    exit;
}

/**
 * Moderní větev použije vlastní upravenou stránku, pokud existuje.
 * Ostatní funkční stránky nadále načítá z původního adresáře /str/strana.
 */
function hsClientUiPagePath($relativePath)
{
    $relativePath = ltrim((string) $relativePath, '/');
    $prefix = 'strana/';

    if (strpos($relativePath, $prefix) === 0) {
        $override = __DIR__ . '/strana/' . substr($relativePath, strlen($prefix));
        if (is_file($override)) {
            return $override;
        }
    }

    return $relativePath;
}

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

  // MIGRACE PHP 7.4 -> 8.3: odstraněn UTF-8 BOM před PHP tagem, který mohl způsobit warning "headers already sent".
  session_start();
  /* MIGRACE PHP 5.5 -> 8.3
     Duvod:
     Starsi nebo rozpracovana session muze obsahovat prazdny string misto seznamu.
     PHP 8.3 vyzaduje pro druhy parametr in_array() pole. Prevod neplatne hodnoty
     na prazdne pole zachovava puvodni vysledek: pravo ani pobocka nejsou nalezeny.

     STARY KOD PHP 5.5:
     Typ techto session hodnot nebyl pred volanim in_array() overen.
  */
  if (!isset($_SESSION["SeznamPravPoduzivatele"]) || !is_array($_SESSION["SeznamPravPoduzivatele"])) {
    $_SESSION["SeznamPravPoduzivatele"] = array();
  }
  if (!isset($_SESSION["SeznamPravPoduzivateleNaPobocky"]) || !is_array($_SESSION["SeznamPravPoduzivateleNaPobocky"])) {
    $_SESSION["SeznamPravPoduzivateleNaPobocky"] = array();
  }

  // V169: customer DataTable uses these values even in a fresh staff session.
  // Keep the existing value when present; only initialise missing/invalid state.
  if (!isset($_SESSION["Blacklist"]) || is_array($_SESSION["Blacklist"])) {
    $_SESSION["Blacklist"] = "";
  }
  if (!isset($_SESSION["ZakazniciRazeniFiltr"]) || is_array($_SESSION["ZakazniciRazeniFiltr"])) {
    $_SESSION["ZakazniciRazeniFiltr"] = "";
  }
  if (!isset($_SESSION["ZakazniciRazeniFiltrRazeni"]) || is_array($_SESSION["ZakazniciRazeniFiltrRazeni"])) {
    $_SESSION["ZakazniciRazeniFiltrRazeni"] = "";
  }
  if (!isset($_SESSION["ZakazniciRazeniFiltrHledani"]) || is_array($_SESSION["ZakazniciRazeniFiltrHledani"])) {
    $_SESSION["ZakazniciRazeniFiltrHledani"] = "";
  }
  $hairsoftLanguages = array(
    'cs' => array('code' => 'CS', 'label' => 'Čeština', 'flag' => '🇨🇿'),
    'en' => array('code' => 'EN', 'label' => 'English', 'flag' => '🇬🇧'),
    'de' => array('code' => 'DE', 'label' => 'Deutsch', 'flag' => '🇩🇪'),
    'sk' => array('code' => 'SK', 'label' => 'Slovenčina', 'flag' => '🇸🇰')
  );

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['languageCombo']) && !is_array($_POST['languageCombo']) && array_key_exists($_POST['languageCombo'], $hairsoftLanguages)) {
    $_SESSION['languageCombo'] = $_POST['languageCombo'];
  }

  $selectedLanguage = isset($_SESSION['languageCombo']) && array_key_exists($_SESSION['languageCombo'], $hairsoftLanguages)
    ? $_SESSION['languageCombo']
    : 'cs';

  // KLIENT
  
  //nacitani casu stranky
  $DURATION_start=microtime(true);
       
  require_once '../cfg/nastaveni.php';
  require_once __DIR__ . '/multi_company_auth.php';
  require_once __DIR__ . '/customer-dependent-sync.php';

  // V169: both staff login paths must provide the same customer/privacy session state.
  // Existing sessions are repaired here so testing does not require logging out first.
  if (isset($_SESSION["JeObsluha"]) && $_SESSION["JeObsluha"] === "1") {
    if (!isset($_SESSION["SQL_ROK"]) || is_array($_SESSION["SQL_ROK"]) || !preg_match('/^[0-9]{4}$/', (string) $_SESSION["SQL_ROK"])) {
      $_SESSION["SQL_ROK"] = date('Y');
    }

    if (!isset($_SESSION["k_poduzivatele_skryt_citliva_data"]) || is_array($_SESSION["k_poduzivatele_skryt_citliva_data"])) {
      // Security-first fallback: hide sensitive data if the DB lookup cannot be completed.
      $_SESSION["k_poduzivatele_skryt_citliva_data"] = "1";
      $hsObsluhaId = (isset($_SESSION["k_poduzivatele_id"]) && !is_array($_SESSION["k_poduzivatele_id"]))
        ? (int) $_SESSION["k_poduzivatele_id"]
        : 0;

      if ($hsObsluhaId > 0) {
        $hsPrivacyResult = $mysqli->query(
          "SELECT `k_poduzivatele_skryt_citliva_data` FROM `k_poduzivatele_obsluha` WHERE `k_poduzivatele_obsluha_id` = " . $hsObsluhaId . " LIMIT 1"
        );
        if ($hsPrivacyResult && ($hsPrivacyRow = $hsPrivacyResult->fetch_assoc())) {
          $_SESSION["k_poduzivatele_skryt_citliva_data"] = ((string) $hsPrivacyRow["k_poduzivatele_skryt_citliva_data"] === "1") ? "1" : "0";
        }
      }
    }
  } elseif (!isset($_SESSION["k_poduzivatele_skryt_citliva_data"]) || is_array($_SESSION["k_poduzivatele_skryt_citliva_data"])) {
    // Owner / manager sessions do not use the staff anonymisation switch.
    $_SESSION["k_poduzivatele_skryt_citliva_data"] = "0";
  }
  
  //TCPDF
  //require_once('../tcpdf/config/tcpdf_config.php');

      function GridTest($ID) {
            return $ID & "+++";
      }  

  //Zjisti zda je pobočka online nebo ne 
      function JePobockaOnlineMenu($ID,$mysqli) {
        //cervena  #ff0000
        //zelena   #00cc00
        
          //$stav = "#ff0000";
           $stav = "#FF8050";        

          if ($ID!="") {
                //SQL
                $select_online= "SELECT `sw_online` FROM `sw_info` WHERE `sw_id` = ".$ID;
                //echo $select_online."***";
                if (!$select_online) { die('Chyba pripojeni do DB!');}
                $vysledek_online=$mysqli->query("$select_online");
                $data_online=MySQLi_Fetch_Array($vysledek_online);

                 if ($data_online["sw_online"]=="1") {
                    $stav = "#00cc00";        
                    

                 }
          }

        echo  "<div style=\"width: 10px; height: 10px; margin-top:8px; float: left; background: ".$stav."; -moz-border-radius: 10px; -webkit-border-radius: 10px; border-radius: 10px;\"></div>";
      }




  //Prijde upravit
  if ($_SESSION["uzivatel_prihlasen"] != "ano") {
    echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/login.php\">";
    return;
  }

  // V202: pokud HairSoft mezitim pridelil webovemu zakaznikovi realne lokalni ID,
  // uvolnit jeho dosud zadrzene Timeline/fotografie/soubory pro dalsi sync cyklus.
  // Zpracovava se jen mala izolovana fronta; bez fronty se velke tabulky neskenuji.
  hsCustomerSyncHoldProcessReady($mysqli, 50, isset($_SESSION["k_id"]) ? (int) $_SESSION["k_id"] : 0);

  // V191: registrace aktualni firmy do bezpecneho dlouhodobeho prihlaseni.
  $hsMultiReady = hsMultiEnsureTable($mysqli);
  if ($hsMultiReady) {
    hsMultiEnsureCurrentSessionRegistered($mysqli);
    $hsRestoreState = hsMultiConsumeRestoreState($mysqli);
    $hsRestoreBranchId = (int) $hsRestoreState['branch_id'];
    $hsRestorePage = (string) $hsRestoreState['page'];
    if ($hsRestoreBranchId > 0 || $hsRestorePage !== '') {
      header('Location: ' . hsMultiBuildAppUrl($hsRestoreBranchId, $hsRestorePage));
      exit;
    }
  }
  
  $Uzivatel_ID =$_SESSION["k_id"];

  //DEBUG HACK
  if ($_SESSION["k_id"]=="10") {
     //echo $Uzivatel_ID ;
     //$Uzivatel_ID =11;
     //$sw_id = 1081;
  }
  
  $sw_id = $_SESSION["pobocka_id"]; 

  //Demo
  if ($_SESSION["k_id"]=="2") {
     $sw_id = 0;
     $_SESSION["pobocka_id"] = 0;
  }
  
        //AKCE
        if (!isset($_POST['datum']) || is_array($_POST['datum'])){$_POST['datum']='';}
        $datum  =  htmlspecialchars($_POST['datum'], ENT_COMPAT);
        //echo $datum;
        if ($datum=="") {
          $datum = date("d.m.Y"); 
        }

        $originalDate = $datum;
        $newDate = date("d.m.Y", strtotime($originalDate));
  


  if (!isset($_GET['Uzivatel_GUID']) || is_array($_GET['Uzivatel_GUID'])){$_GET['Uzivatel_GUID']='';}
  $Uzivatel_GUID  =  htmlspecialchars($_GET['Uzivatel_GUID'], ENT_COMPAT);

  if (!isset($_GET['strana']) || is_array($_GET['strana'])){$_GET['strana']='';}
  $strana  =  htmlspecialchars($_GET['strana'], ENT_COMPAT);

  // V174: systémový číselník pojišťoven je administrátorské nastavení.
  // Přímý odkaz nesmí obejít stejné omezení, které používá levé menu.
  $hsIsMainAdministrator = ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0");
  if (in_array($strana, array('NastaveniCiselnikPojistoven','NastaveniPristupy','NastaveniDetailUzivatele','NastaveniDetailObsluhy'), true) && !$hsIsMainAdministrator) {
    $strana = '';
  }
  
  if (!isset($_GET['zmena_pobocky']) || is_array($_GET['zmena_pobocky'])){$_GET['zmena_pobocky']='';}
  $zmena_pobocky  =  htmlspecialchars($_GET['zmena_pobocky'], ENT_COMPAT);
  
  if (!isset($_GET['aktivni_pobocka']) || is_array($_GET['aktivni_pobocka'])){$_GET['aktivni_pobocka']='';}
  $aktivni_pobocka  =  htmlspecialchars($_GET['aktivni_pobocka'], ENT_COMPAT);
  
  
  if (!isset($_POST['zakaznici_tabulka_vse']) || is_array($_POST['zakaznici_tabulka_vse'])){$_POST['zakaznici_tabulka_vse']='';}
  $zakaznici_tabulka_vse  =  htmlspecialchars($_POST['zakaznici_tabulka_vse'], ENT_COMPAT);


  if ($zakaznici_tabulka_vse=="100000") {
    //$strankovani_zakaznici_tabulka = "100000";
    $_SESSION["strankovani_zakaznici_tabulka"]= "100000";
  }else{
    //$strankovani_zakaznici_tabulka = "10";
    $_SESSION["strankovani_zakaznici_tabulka"]= "20";
  }


  
  //Zmena pobocky
  if ($zmena_pobocky=="1") {
    


    //
    // KONTROLA NA VLASTNIKA POBOCKY !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //
    
    //Kontrola zda uzivatel si neprepsal id pobocky
                
               $id_uziv = $_SESSION["k_id"];
                
                              
               $sql_existuje_pobocka= "SELECT * FROM `sw_email_pobocka` WHERE `sw_id` = $aktivni_pobocka and `k_id` = $id_uziv";
               //echo $sql_existuje_pobocka;
               $vysledek_existuje_pobocka=$mysqli->query($sql_existuje_pobocka);
               $radku_existuje_pobocka=$vysledek_existuje_pobocka->num_rows; 
               $sql_existuje_pobocka=MySQLi_Fetch_Array($vysledek_existuje_pobocka);               
                             
               if ($radku_existuje_pobocka==1) {


                  
                  //Nacteni Prav Pro Poduzivatele a Obsluhu
                    
                    if (($_SESSION["JePoduzivatel"] == "1" and $_SESSION["k_poduzivatele_id"]!="") OR ($_SESSION["JeObsluha"] == "1" and $_SESSION["k_poduzivatele_id"]!="")) {



                             /*rozpoznat obsluhu nebo pouzivatele(manazera)*/
                             if ($_SESSION["JePoduzivatel"] == "1") {
                                /*Poduzivatel - manazer*/
                                $select_na_pravo= "SELECT * FROM `k_poduzivatele_prava` WHERE `k_poduzivatele_id` = ".$_SESSION["k_poduzivatele_id"]." and `pobocka_id` = ".$aktivni_pobocka." and `prava_uzivatel_pravo` = 1";
                             }else{
                              /*Obnsluha*/
                                $select_na_pravo= "SELECT * FROM `k_poduzivatele_prava` WHERE `k_poduzivatele_je_obsluha` = 1  and `k_poduzivatele_id_obsluha` = ".$_SESSION["k_poduzivatele_id"]." and `pobocka_id` = ".$aktivni_pobocka." and `prava_uzivatel_pravo` = 1";
                             }
                             
                             //echo "<script type='text/javascript'>alert('".$select_na_pravo."');</script>";
                             
                             //echo $select_na_pravo;
                             if (!$select_na_pravo) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

                              $vysledek_na_pravo=$mysqli->query($select_na_pravo);
                              //$radku_na_pravo=$vysledek_na_pravo->num_rows;                                       
                                
                                //prazdne pole
                                $PolePrav[]="";

                                while ($na_pravo=MySQLi_Fetch_Array($vysledek_na_pravo)):   
                                    array_push($PolePrav, $na_pravo["prava_uzivatel_menu"]); // Menu
                                    //array_push($PolePravPobocka, $na_pravo["pobocka_id"]); // Pobocky
                                    //echo $na_pravo["pobocka_id"];
                                endwhile;

                                $_SESSION["SeznamPravPoduzivatele"] = $PolePrav;

                             


                             /*Prava na pobocku*/
                             if ($_SESSION["JePoduzivatel"] == "1") {
                                /*Poduzivatel - manazer*/
                                $select_na_pravo_pobockaid= "SELECT * FROM `k_poduzivatele_prava` WHERE `k_poduzivatele_id` = ".$_SESSION["k_poduzivatele_id"]." and `prava_uzivatel_pravo` = 1";
                             }else{
                              /*Obnsluha*/
                                $select_na_pravo_pobockaid= "SELECT * FROM `k_poduzivatele_prava` WHERE `k_poduzivatele_id_obsluha` = ".$_SESSION["k_poduzivatele_id"]." and `prava_uzivatel_pravo` = 1";
                             }

                             
                             //echo $select_na_pravo_pobockaid;
                             if (!$select_na_pravo_pobockaid) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

                              $vysledek_na_pravo_pobockaid=$mysqli->query($select_na_pravo_pobockaid);
                              //$radku_na_pravo_pobockaid=$vysledek_na_pravo_pobockaid->num_rows;                                       
                                
                                //prazdne pole
                                $PolePravPobocka[]="";

                                while ($na_pravo_pobockaid=MySQLi_Fetch_Array($vysledek_na_pravo_pobockaid)):   
                                    array_push($PolePravPobocka, $na_pravo_pobockaid["pobocka_id"]); // Pobocky
                                    //echo $na_pravo_pobockaid["pobocka_id"];
                                endwhile;
                                $_SESSION["SeznamPravPoduzivateleNaPobocky"] = $PolePravPobocka;

                                

                                //Na kolik pobocek ma pravo poduzivatel?
                                /*Prava na pobocku*/
                             if ($_SESSION["JePoduzivatel"] == "1") {
                                /*Poduzivatel - manazer*/
                                $sql_existuje_poduzivatel_pobocek= "SELECT distinct `pobocka_id` FROM `k_poduzivatele_prava` WHERE `k_poduzivatele_id` = ".$_SESSION["k_poduzivatele_id"]." and `prava_uzivatel_pravo` = 1 group by `pobocka_id`";
                             }else{
                              /*Obnsluha*/
                                $sql_existuje_poduzivatel_pobocek= "SELECT distinct `pobocka_id` FROM `k_poduzivatele_prava` WHERE `k_poduzivatele_id_obsluha` = ".$_SESSION["k_poduzivatele_id"]." and `prava_uzivatel_pravo` = 1 group by `pobocka_id`";
                             }
                                
                                $vysledek_existuje_poduzivatel_pobocek=$mysqli->query($sql_existuje_poduzivatel_pobocek);
                                $radku_existuje_poduzivatel_pobocek=$vysledek_existuje_poduzivatel_pobocek->num_rows; 
                                $sql_existuje_poduzivatel_pobocek_vysledek=MySQLi_Fetch_Array($vysledek_existuje_poduzivatel_pobocek);
                                
                                $_SESSION["PocetPovolenychPobocek"] = $radku_existuje_poduzivatel_pobocek;

                                //echo  $sql_existuje_poduzivatel_pobocek."***".$_SESSION["PocetPovolenychPobocek"];
                       
                    } 




                  
               
                  






                  //Jmeno a pridani do promene pro kazdou stranaku
                  $_SESSION["pobocka_id"] = $aktivni_pobocka;
    
                            $sql_pobocka_jmeno= "SELECT `sw_jmeno_pobocky`,`sw_mena` FROM `sw_info` WHERE `sw_id` = $aktivni_pobocka";
                            $vysledek_pobocka_jmeno=$mysqli->query($sql_pobocka_jmeno);
                            $sql_pobocka_jmeno=MySQLi_Fetch_Array($vysledek_pobocka_jmeno);
                            
                  $_SESSION["pobocka_jmeno"] = $sql_pobocka_jmeno["sw_jmeno_pobocky"];
                  $_SESSION["Mena_Klienta"] = $sql_pobocka_jmeno["sw_mena"];
                  
                  
                  
                               //jisteni skupiny
                               $sqldotaz= "SELECT `sw_skupina_id` FROM `sw_info` WHERE `sw_id` = $aktivni_pobocka ";
                               if (!$sqldotaz) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}
                               $vysledek=$mysqli->query("$sqldotaz");
                               $sms_fronta=MySQLi_Fetch_Array($vysledek);
                               //echo $sms_fronta[sw_skupina_id];

                                 if ($sms_fronta["sw_skupina_id"]=="") {
                                    $_SESSION["skupina_id"]="";
                                 }else{
                                    $_SESSION["skupina_id"] = $sms_fronta["sw_skupina_id"];
                                 }

                  // V191: kazda zapamatovana firma si drzi posledni vybranou pobocku.
                  if ($hsMultiReady) {
                    hsMultiUpdateLastBranch($mysqli, (int) $aktivni_pobocka);
                  }
                  
                
               }else {
                  echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=404\">";
               }             
                                                                                                                                                     
    
                                                                                                                                                     
  }
  
  
  

  // V194: kazda firma si pamatuje i posledni bezpecnou pracovni sekci.
  // Detailni identifikatory (zakaznik, kamera apod.) se zamerne neukladaji.
  if ($hsMultiReady) {
    hsMultiUpdateLastPage($mysqli, $strana);
  }

  // V191: data pro prepinac firem se nacitaji az po pripadne zmene pobocky.
  $hsMultiAccounts = $hsMultiReady ? hsMultiListAccounts($mysqli, isset($_SESSION['k_soft']) ? (string) $_SESSION['k_soft'] : 'HairSoft') : array();
  $hsMultiCsrf = hsMultiCsrfToken();
  $hsMultiFlash = $hsMultiReady ? hsMultiTakeFlash() : null;
  $hsCompanyTextMap = array(
    'cs' => array('switch' => 'Přepnout firmu', 'add' => 'Přidat firmu', 'addTitle' => 'Přidat další firmu', 'login' => 'Login / Email', 'password' => 'Heslo', 'addSwitch' => 'Přidat a přepnout', 'cancel' => 'Zrušit', 'current' => 'Aktuální', 'remove' => 'Odpojit', 'logoutCurrent' => 'Odhlásit tuto firmu', 'logoutAll' => 'Odhlásit všechny firmy', 'saved' => 'Přihlášení je bezpečně uložené na tomto zařízení.', 'admin' => 'Administrátor', 'manager' => 'Manažer', 'staff' => 'Obsluha'),
    'sk' => array('switch' => 'Prepnúť firmu', 'add' => 'Pridať firmu', 'addTitle' => 'Pridať ďalšiu firmu', 'login' => 'Login / Email', 'password' => 'Heslo', 'addSwitch' => 'Pridať a prepnúť', 'cancel' => 'Zrušiť', 'current' => 'Aktuálna', 'remove' => 'Odpojiť', 'logoutCurrent' => 'Odhlásiť túto firmu', 'logoutAll' => 'Odhlásiť všetky firmy', 'saved' => 'Prihlásenie je bezpečne uložené na tomto zariadení.', 'admin' => 'Administrátor', 'manager' => 'Manažér', 'staff' => 'Obsluha'),
    'en' => array('switch' => 'Switch company', 'add' => 'Add company', 'addTitle' => 'Add another company', 'login' => 'Login / Email', 'password' => 'Password', 'addSwitch' => 'Add and switch', 'cancel' => 'Cancel', 'current' => 'Current', 'remove' => 'Remove', 'logoutCurrent' => 'Sign out of this company', 'logoutAll' => 'Sign out of all companies', 'saved' => 'Sign-in is securely saved on this device.', 'admin' => 'Administrator', 'manager' => 'Manager', 'staff' => 'Staff'),
    'de' => array('switch' => 'Firma wechseln', 'add' => 'Firma hinzufügen', 'addTitle' => 'Weitere Firma hinzufügen', 'login' => 'Login / E-Mail', 'password' => 'Passwort', 'addSwitch' => 'Hinzufügen und wechseln', 'cancel' => 'Abbrechen', 'current' => 'Aktuell', 'remove' => 'Entfernen', 'logoutCurrent' => 'Von dieser Firma abmelden', 'logoutAll' => 'Von allen Firmen abmelden', 'saved' => 'Die Anmeldung ist auf diesem Gerät sicher gespeichert.', 'admin' => 'Administrator', 'manager' => 'Manager', 'staff' => 'Mitarbeiter')
  );
  $hsCompanyTexts = isset($hsCompanyTextMap[$selectedLanguage]) ? $hsCompanyTextMap[$selectedLanguage] : $hsCompanyTextMap['cs'];
  $hsMultiActiveAccount = null;
  foreach ($hsMultiAccounts as $hsAccountItem) {
    if (!empty($hsAccountItem['active'])) {
      $hsMultiActiveAccount = $hsAccountItem;
      break;
    }
  }

?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js<?php if (isset($_GET['strana']) && is_string($_GET['strana']) && in_array($_GET['strana'], array('CelkoveTrzby', 'MesicniTrzby', 'TrzbyDleObsluhy', 'TrzbyOdPocatku'), true)) { echo ' hs-revenue-page'; } ?>" lang="cs">
<!--<html class="no-js" lang="<?php echo $selectedLanguage; ?>">-->
<!--<![endif]-->

<head>
<!-- V130: guard starts before the streamed voucher header; never reveal the legacy layout on a timer. -->
<script>
document.documentElement.classList.add('hs-voucher-boot');
window.addEventListener('load',function(){setTimeout(function(){document.querySelectorAll('.hs-voucher-shell:not(.hs-voucher-shell-ready)').forEach(function(shell){
 var note=document.createElement('p');note.className='hs-voucher-load-error';note.setAttribute('role','alert');
 var texts={cs:'Stránku se nepodařilo připravit. Obnovte ji prosím.',sk:'Stránku sa nepodarilo pripraviť. Obnovte ju prosím.',en:'The page could not be prepared. Please reload it.',de:'Die Seite konnte nicht vorbereitet werden. Bitte laden Sie sie neu.'};
 note.textContent=texts[document.documentElement.lang]||texts.cs;shell.parentNode.insertBefore(note,shell);
});},2000);});
</script>
<style>html.hs-voucher-boot .hs-voucher-shell:not(.hs-voucher-shell-ready){visibility:hidden!important;pointer-events:none!important}.hs-voucher-load-error{margin:30px;padding:24px;background:white;border-radius:16px;color:#334861}
</style>

    <meta charset="utf-8">
    <!-- <meta http-equiv="X-UA-Compatible" content="IE=edge"> -->
    <title>HairSoft Klient</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="icon" href="/hs-client-ui/img/favicon.ico?v=41" sizes="any">
    
    <link rel="stylesheet" href="../plugins/switchery/switchery.min.css">
    
    <!-- TMK STYL -->
    <link rel="stylesheet" href="../css/_tmk.css">
   
   
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="../plugins/bootstrap/css/bootstrap.min.css">
    <!-- Fonts  -->
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/simple-line-icons.css">
    <!-- CSS Animate -->
    <link rel="stylesheet" href="../css/animate.css">
    <!-- Custom styles for this theme -->
    <link rel="stylesheet" href="../css/main.css">
    
    <link rel="stylesheet" href="../plugins/dataTables/css/dataTables.css">
    
    <link rel="stylesheet" href="../plugins/icheck/css/all.css">
    

    <!-- Custom styles for this theme -->
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="/hs-client-ui/css/client-ui.css?v=228">
    <link rel="stylesheet" href="/hs-client-ui/css/company-switch.css?v=221">
    <link rel="stylesheet" href="/hs-client-ui/css/dashboard-charts.css?v=197">
    <link rel="stylesheet" href="/hs-client-ui/css/client-form.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/client-customers.css?v=223">
    <link rel="stylesheet" href="/hs-client-ui/css/client-customers-mobile.css?v=222">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-desktop.css?v=211">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-profile.css?v=223">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-timeline.css?v=210">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-sms-chat.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-gallery.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-programs.css?v=225">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-ratings.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-files.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/client-copy-test.css?v=205">
    <link rel="stylesheet" href="/hs-client-ui/css/client-export-menu.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/client-confirm.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/reservations-embed.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/daily-revenue.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/monthly-revenue.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/i18n.css?v=196">
      
    <!-- C3 Chart-->
    <link rel="stylesheet" href="../plugins/c3Chart/css/c3.css">
    <link rel="stylesheet" href="../plugins/c3Chart/css/c3.min.css">
    <!--Page Leve JS -->
    <script src="../plugins/c3Chart/js/d3.v3.min.js"></script>
    <script src="../plugins/c3Chart/js/c3.js"></script>
    <script src="../plugins/c3Chart/js/c3-demo.js"></script>


        



    
    <!-- Feature detection -->
    <script src="../js/vendor/modernizr-2.6.2.min.js"></script>
    <script src="/hs-client-ui/js/i18n.js?v=222" defer></script>
    <script>
      window.hsPageHeaderData = <?php
        echo json_encode(
          array(
            'branchName' => isset($_SESSION['pobocka_jmeno']) ? (string) $_SESSION['pobocka_jmeno'] : '',
            'swId' => isset($_SESSION['pobocka_id']) ? (string) $_SESSION['pobocka_id'] : ''
          ),
          JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
      ?>;
    </script>
    <script src="/hs-client-ui/js/menu.js?v=228" defer></script>
    <script src="/hs-client-ui/js/company-switch.js?v=196" defer></script>
    <script src="/hs-client-ui/js/customer-sync-hold.js?v=202" defer></script>
    <script src="/hs-client-ui/js/dashboard-charts.js?v=197" defer></script>
    <script src="/hs-client-ui/js/client-form.js?v=196" defer></script>
    <script src="/hs-client-ui/js/client-customers.js?v=225" defer></script>
    <script src="/hs-client-ui/js/client-detail-desktop.js?v=200" defer></script>
    <script src="/hs-client-ui/js/client-detail-profile.js?v=223" defer></script>
    <script src="/hs-client-ui/js/client-detail-sms-chat.js?v=196" defer></script>
    <script src="/hs-client-ui/js/client-detail-gallery.js?v=196" defer></script>
    <script src="/hs-client-ui/js/client-detail-programs.js?v=225" defer></script>
    <script src="/hs-client-ui/js/client-detail-ratings.js?v=196" defer></script>
    <script src="/hs-client-ui/js/client-detail-files.js?v=196" defer></script>
    <script src="/hs-client-ui/js/client-copy-test.js?v=209" defer></script>
    <script src="/hs-client-ui/js/client-timeline-pdf.js?v=196" defer></script>
    <script src="/hs-client-ui/js/client-ratings-pdf.js?v=196" defer></script>
    <script src="/hs-client-ui/js/daily-revenue-pdf.js?v=196" defer></script>
    <script src="/hs-client-ui/js/monthly-revenue-pdf.js?v=196" defer></script>
    <script src="/hs-client-ui/js/client-export-menu.js?v=196" defer></script>
    <script src="/hs-client-ui/js/client-confirm.js?v=196" defer></script>
    <script src="/hs-client-ui/js/reservations-embed.js?v=196" defer></script>
    <script src="/hs-client-ui/js/daily-revenue.js?v=197" defer></script>
    <script src="/hs-client-ui/js/monthly-revenue.js?v=196" defer></script>
    

    
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="../js/vendor/html5shiv.js"></script>
    <script src="../js/vendor/respond.min.js"></script>
    <![endif]-->
    
    
<style type="text/css">
div.dt-buttons {
  font-family: Tahoma, Arial, sans-serif;
}
</style>

    
    
    
    
    
<link rel="stylesheet" href="/hs-client-ui/css/annual-revenue.css?v=196">
<script src="/hs-client-ui/js/annual-revenue-pdf.js?v=196" defer></script>
<script src="/hs-client-ui/js/annual-revenue.js?v=196" defer></script>
<link rel="stylesheet" href="/hs-client-ui/css/lifetime-revenue.css?v=196">
<script src="/hs-client-ui/js/lifetime-revenue-pdf.js?v=196" defer></script>
<script src="/hs-client-ui/js/lifetime-revenue.js?v=196" defer></script>
<link rel="stylesheet" href="/hs-client-ui/css/page-status.css?v=196">
<script src="/hs-client-ui/js/page-status.js?v=196" defer></script>
<link rel="stylesheet" href="/hs-client-ui/css/calendar.css?v=196">
<script src="/hs-client-ui/js/calendar.js?v=196" defer></script>
<link rel="stylesheet" href="/hs-client-ui/css/voucher.css?v=196">
<script src="/hs-client-ui/js/voucher-pdf.js?v=196" defer></script>
<script src="/hs-client-ui/js/voucher.js?v=196" defer></script>
<link rel="stylesheet" href="/hs-client-ui/css/stock.css?v=199">
<script src="/hs-client-ui/js/stock-pdf.js?v=196" defer></script>
<script src="/hs-client-ui/js/stock.js?v=199" defer></script>
<link rel="stylesheet" href="/hs-client-ui/css/feedback-table.css?v=196">
<link rel="stylesheet" href="/hs-client-ui/css/feedback.css?v=219">
<script src="/hs-client-ui/js/feedback.js?v=217" defer></script>
<link rel="stylesheet" href="/hs-client-ui/css/sms-overview.css?v=196">
<script src="/hs-client-ui/js/sms-overview-pdf.js?v=196" defer></script>
<script src="/hs-client-ui/js/sms-overview.js?v=196" defer></script>
<link rel="stylesheet" href="/hs-client-ui/css/sms-lists.css?v=196">
<script src="/hs-client-ui/js/sms-lists-pdf.js?v=196" defer></script>
<script src="/hs-client-ui/js/sms-lists.js?v=196" defer></script>
<link rel="stylesheet" href="/hs-client-ui/css/users.css?v=196">
<script src="/hs-client-ui/js/users.js?v=196" defer></script>
<link rel="stylesheet" href="/hs-client-ui/css/insurance.css?v=196">
<script src="/hs-client-ui/js/insurance.js?v=196" defer></script>
<link rel="stylesheet" href="/hs-client-ui/css/access.css?v=196">
<script src="/hs-client-ui/js/access.js?v=196" defer></script>
<link rel="stylesheet" href="/hs-client-ui/css/attendance.css?v=196">
<script src="/hs-client-ui/js/attendance-pdf.js?v=196"></script>
<script src="/hs-client-ui/js/attendance.js?v=196" defer></script>
<link rel="stylesheet" href="/hs-client-ui/css/staff-switch.css?v=196">
<link rel="stylesheet" href="/hs-client-ui/css/data-management.css?v=212">
<script src="/hs-client-ui/js/staff-switch.js?v=196" defer></script>
<script src="/hs-client-ui/js/data-management.js?v=212" defer></script>
</head>

<body>

 <!-- <div id="contentLanguage">--> 
    <section id="main-wrapper" class="theme-default">
        <script>
            try {
                if (window.localStorage.getItem('hairsoft.sidebar.collapsed.v109') === '1' && window.innerWidth >= 768) {
                    document.getElementById('main-wrapper').classList.add('sidebar-mini');
                }
            } catch (error) {}
        </script>

        <!-- Lokální SVG symboly Lucide pro hlavní navigaci. -->
        <svg class="hs-menu-symbols" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true">
            <symbol id="hs-icon-switch-user" viewBox="0 0 24 24"><path d="m16 3 4 4-4 4"/><path d="M20 7H9a5 5 0 0 0-5 5v1"/><path d="m8 21-4-4 4-4"/><path d="M4 17h11a5 5 0 0 0 5-5v-1"/></symbol>
            <symbol id="hs-icon-dashboard" viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></symbol>
            <symbol id="hs-icon-user-plus" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></symbol>
            <symbol id="hs-icon-building" viewBox="0 0 24 24"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></symbol>
            <symbol id="hs-icon-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
            <symbol id="hs-icon-calendar" viewBox="0 0 24 24"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></symbol>
            <symbol id="hs-icon-chart" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></symbol>
            <symbol id="hs-icon-package" viewBox="0 0 24 24"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></symbol>
            <symbol id="hs-icon-ticket" viewBox="0 0 24 24"><path d="M2 9a3 3 0 0 0 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 0 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></symbol>
            <symbol id="hs-icon-star" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></symbol>
            <symbol id="hs-icon-messages" viewBox="0 0 24 24"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V8a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/><path d="M8 9h8"/><path d="M8 13h5"/></symbol>
            <symbol id="hs-icon-phone" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.69 2.8a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.33 1.84.56 2.8.69A2 2 0 0 1 22 16.92Z"/></symbol>
            <symbol id="hs-icon-send" viewBox="0 0 24 24"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></symbol>
            <symbol id="hs-icon-at-sign" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"/></symbol>
            <symbol id="hs-icon-user-cog" viewBox="0 0 24 24"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4"/><circle cx="17" cy="17" r="3"/><path d="M17 12v2"/><path d="M17 20v2"/><path d="m12.67 14.5 1.73 1"/><path d="m19.6 18.5 1.73 1"/><path d="m12.67 19.5 1.73-1"/><path d="m19.6 15.5 1.73-1"/></symbol>
            <symbol id="hs-icon-list" viewBox="0 0 24 24"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></symbol>
            <symbol id="hs-icon-settings" viewBox="0 0 24 24"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.51a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></symbol>
            <symbol id="hs-icon-server" viewBox="0 0 24 24"><rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/><path d="M10 6h8"/><path d="M10 18h8"/></symbol>
            <symbol id="hs-icon-logout" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></symbol>
        </svg>
        <header id="header">
            <!--logo start-->
            <div class="brand">
                <a href="index.php" class="logo">
<?php
                        
                            /*Logo softu*/
                            if ($_SESSION["k_soft"] =="HairSoft") {
                                echo "<span>HairSoft</span>&nbsp;klient</a>";
                            }elseif($_SESSION["k_soft"] =="ZooSoft"){
                                echo "<span>ZooSoft</span>&nbsp;klient</a>";
                            }



                        
                     ?>
                    

            </div>
            <!--logo end-->
            <ul class="nav navbar-nav navbar-left">
                

                 <li class="toggle-navigation toggle-left">
                    <button class="sidebar-toggle" id="toggle-left">
                        <i class="fa fa-bars"></i>
                    </button>
                </li>
                <li class="toggle-profile hidden-xs">
                    <button type="button" class="btn btn-default" id="toggle-profile">
                        <i class="icon-user"></i>
                    </button>
                </li>
                
                <li class="toggle-profile hidden-xs">
                    <form action="../str/akce/CelkoveTrzby.php" method="POST" >
                      <input type="hidden" class="form-control" name="sw_id" value="<?php echo $sw_id; ?>">
                      <input type="hidden" class="form-control" name="refresh_pro_den" value="1">
                      <input type="hidden" class="form-control" name="tj_datum" value="<?php echo $newDate; ?>">
                      <input type="hidden" class="form-control" name="datum" value="<?php echo $newDate; ?>">
                      <input type="hidden" class="form-control" name="odkud" value="index">
                      <!-- <button type="submit" class="btn btn-info"><i class="fa icon-refresh"></i>Refresh dat z PC</button>  -->


                      <button type="submit" class="btn btn-default" id="toggle-profile" title="Refresh dat pro dnešní den">
                          <i class="icon-refresh"></i>
                      </button>
                    </form>
                </li>

                
                <?php 

                    /*Existuje zaznam v tabulce dashboard?*/
                      $sql_existuje_dashboard= "SELECT count(*) as 'CNT_dashboard', `dashboardHash` FROM `Dashboardy` WHERE `sw_id` =  '".$_SESSION["pobocka_id"]."'";
                      //echo $sql_existuje_dashboard;
                      $vysledek_existuje_dashboard=$mysqli->query($sql_existuje_dashboard);
                      $sql_existuje_dashboard=MySQLi_Fetch_Array($vysledek_existuje_dashboard);

                      
                      
                      if ($sql_existuje_dashboard["CNT_dashboard"]>0 and $sql_existuje_dashboard["dashboardHash"] !="") {

                    
                        echo "<li class='toggle-profile hidden-xs'>";
                             echo "<button type='button' class='btn btn-default' id='toggle-profile'>";
                                echo "<a href='https://klient.hairsoft.cz/dashboard/hash.php?id=".$sql_existuje_dashboard["dashboardHash"]."' target='_blank'><i class='icon-share-alt'></i></a>";
                             echo "</button>";
                        echo "</li>";

                      }else{
                        //nic
                      }






                 ?>


                


                 <!--
                <li class="toggle-profile hidden-xs">
                    <button type="button" class="sidebar-toggle" id="toggle-left">
                        <i class="fa fa-bars"></i>
                    </button>
                </li> 
                


                       <li class= "toggle-navigation toggle-left">
                          <button class="sidebar-toggle" id="toggle">
                              
                               <a href="index.php?strana=SeznamObsluh" title="Přehlášení obsluhy"><i class="fa fa-power-off"></i></a>
                               
                          </button>
                      
                                      <li class="toggle-profile hidden-xs" >
                    <button type="button" class="sidebar-toggle" id="toggle-left">
                        <i class="fa fa-bars" ></i>
                    </button>
                </li>


                      </li> 


                <li class="toggle-profile hidden-xs">
                    <button type="button" class="btn btn-default" id="toggle-profile">
                        <i class="icon-user"></i>
                    </button>
                </li>

                    -->

                

                
                
               
               
                


               <!--
                  <li class="hidden-xs">
                    <input type="text" class="search" placeholder="Search project...">
                    <button type="submit" class="btn btn-sm btn-search"><i class="fa fa-search"></i>
                    </button>
                </li>
               -->
            
            </ul>
            
            
            <ul class="nav navbar-nav navbar-right">
                <?php if ($hsMultiReady && count($hsMultiAccounts) > 0) { ?>
                <li class="hs-company-switch-nav" id="hsCompanySwitchRoot">
                    <button type="button" class="btn btn-default hs-company-switch-toggle" id="hsCompanySwitchToggle" aria-haspopup="true" aria-expanded="false" title="<?php echo htmlspecialchars($hsCompanyTexts['switch'], ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fa fa-building-o" aria-hidden="true"></i>
                        <span class="hs-company-switch-current" data-hs-i18n-ignore><?php echo htmlspecialchars($hsMultiActiveAccount ? $hsMultiActiveAccount['label'] : ((isset($_SESSION['pobocka_jmeno']) && $_SESSION['pobocka_jmeno'] !== '') ? $_SESSION['pobocka_jmeno'] : $_SESSION['uzivatel_prijmeni_jmeno']), ENT_QUOTES, 'UTF-8'); ?></span>
                        <i class="fa fa-angle-down hs-company-switch-chevron" aria-hidden="true"></i>
                    </button>
                    <div class="hs-company-switch-panel" id="hsCompanySwitchPanel" aria-hidden="true">
                        <div class="hs-company-switch-panel-head">
                            <div class="hs-company-switch-panel-head-copy">
                                <strong><?php echo htmlspecialchars($hsCompanyTexts['switch'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <span><?php echo htmlspecialchars($hsCompanyTexts['saved'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <span class="hs-company-switch-count" data-hs-i18n-ignore aria-label="Počet uložených firem"><?php echo count($hsMultiAccounts); ?>/<?php echo defined('HS_MULTI_MAX_ACCOUNTS') ? HS_MULTI_MAX_ACCOUNTS : 30; ?></span>
                        </div>
                        <div class="hs-company-switch-list">
                        <?php foreach ($hsMultiAccounts as $hsCompanyAccount) {
                            $hsTypeKey = isset($hsCompanyTexts[$hsCompanyAccount['type']]) ? $hsCompanyAccount['type'] : 'admin';
                        ?>
                            <div class="hs-company-switch-item<?php echo !empty($hsCompanyAccount['active']) ? ' is-active' : ''; ?>">
                                <form method="post" action="/str/company-switch.php" class="hs-company-switch-form">
                                    <input type="hidden" name="action" value="switch">
                                    <input type="hidden" name="selector" value="<?php echo htmlspecialchars($hsCompanyAccount['selector'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($hsMultiCsrf, ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="hs-company-account-main"<?php echo !empty($hsCompanyAccount['active']) ? ' disabled' : ''; ?>>
                                        <span class="hs-company-account-icon"><i class="fa fa-building-o" aria-hidden="true"></i></span>
                                        <span class="hs-company-account-copy">
                                            <strong data-hs-i18n-ignore><?php echo htmlspecialchars($hsCompanyAccount['label'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <small><span data-hs-i18n-ignore><?php echo htmlspecialchars($hsCompanyAccount['login_label'], ENT_QUOTES, 'UTF-8'); ?></span> · <?php echo htmlspecialchars($hsCompanyTexts[$hsTypeKey], ENT_QUOTES, 'UTF-8'); ?></small>
                                        </span>
                                        <?php if (!empty($hsCompanyAccount['active'])) { ?>
                                            <span class="hs-company-current-badge"><i class="fa fa-check" aria-hidden="true"></i> <?php echo htmlspecialchars($hsCompanyTexts['current'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php } else { ?>
                                            <span class="hs-company-switch-arrow"><i class="fa fa-angle-right" aria-hidden="true"></i></span>
                                        <?php } ?>
                                    </button>
                                </form>
                                <?php if (empty($hsCompanyAccount['active'])) { ?>
                                <form method="post" action="/str/company-switch.php" class="hs-company-remove-form">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="selector" value="<?php echo htmlspecialchars($hsCompanyAccount['selector'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($hsMultiCsrf, ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="hs-company-remove-button" title="<?php echo htmlspecialchars($hsCompanyTexts['remove'], ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo htmlspecialchars($hsCompanyTexts['remove'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <i class="fa fa-times" aria-hidden="true"></i>
                                    </button>
                                </form>
                                <?php } ?>
                            </div>
                        <?php } ?>
                        </div>
                        <button type="button" class="hs-company-add-button" data-hs-company-add>
                            <span class="hs-company-account-icon"><i class="fa fa-plus" aria-hidden="true"></i></span>
                            <span><?php echo htmlspecialchars($hsCompanyTexts['add'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </button>
                        <div class="hs-company-switch-footer">
                            <form method="post" action="/str/company-switch.php">
                                <input type="hidden" name="action" value="logout_current">
                                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($hsMultiCsrf, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit"><i class="fa fa-sign-out" aria-hidden="true"></i><?php echo htmlspecialchars($hsCompanyTexts['logoutCurrent'], ENT_QUOTES, 'UTF-8'); ?></button>
                            </form>
                            <form method="post" action="/str/company-switch.php">
                                <input type="hidden" name="action" value="logout_all">
                                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($hsMultiCsrf, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="is-danger"><i class="fa fa-power-off" aria-hidden="true"></i><?php echo htmlspecialchars($hsCompanyTexts['logoutAll'], ENT_QUOTES, 'UTF-8'); ?></button>
                            </form>
                        </div>
                    </div>
                </li>
                <?php } ?>
                <li class="hidden-xs hs-header-actions">
                 <button id="ButtonVzdalenaPomoc" type="submit" onclick="window.location.href='https://www.hairsoft.cz/temp/UnisSoft_Hotline.exe'" class="btn btn-danger"><i class="fa fa-ambulance"></i><span class="hs-button-label">Vzdálená pomoc</span></button>
                 <?php 
                    //Demo
                      if ($_SESSION["k_id"]=="2") {
                        
                      }else{
                            ?>
                                <button id="ButtonManual" type="submit" onclick="window.open('https://doc.manualy.unissoft.cz/moduly/hairsoft-klient','_blank')" class="btn btn-danger"><i class="fa fa-book"></i><span class="hs-button-label">Manuál</span></button>
                            <?php 
                      }


                  ?>
                 
                </li>
                <li class="toggle-fullscreen hidden-xs">
                    <button type="button" class="btn btn-default expand" id="toggle-fullscreen">
                        <i class="fa fa-expand"></i>
                    </button>
                </li>

                <?php 
                  //Zakaznici prava 
                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Zakaznici", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" )) {
                ?> 

                      <li class="toggle-navigation toggle-right">
                          <button class="sidebar-toggle" id="toggle-right">
                              <i class="fa fa-indent"></i>
                          </button>
                      </li>

                <?php 
                  }
                ?> 
                      

            </ul>
            <!--
              <ul class="nav navbar-nav navbar-right">
                <li class="toggle-navigation toggle-right">
                    <button class="sidebar-toggle" id="toggle-right">
                        <i class="fa fa-indent"></i>
                    </button>
                </li>
            </ul>
            -->
        </header>

        <?php if ($hsMultiReady) { ?>
        <div class="hs-company-modal" id="hsCompanyAddModal" aria-hidden="true">
            <div class="hs-company-modal-backdrop" data-hs-company-close></div>
            <div class="hs-company-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="hsCompanyAddTitle">
                <button type="button" class="hs-company-modal-close" data-hs-company-close aria-label="<?php echo htmlspecialchars($hsCompanyTexts['cancel'], ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-times" aria-hidden="true"></i></button>
                <div class="hs-company-modal-icon"><i class="fa fa-building-o" aria-hidden="true"></i><span class="hs-company-modal-plus"><i class="fa fa-plus" aria-hidden="true"></i></span></div>
                <h3 id="hsCompanyAddTitle"><?php echo htmlspecialchars($hsCompanyTexts['addTitle'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?php echo htmlspecialchars($hsCompanyTexts['saved'], ENT_QUOTES, 'UTF-8'); ?></p>
                <form method="post" action="/str/company-switch.php" class="hs-company-add-form" autocomplete="on">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($hsMultiCsrf, ENT_QUOTES, 'UTF-8'); ?>">
                    <label for="hsCompanyLogin"><?php echo htmlspecialchars($hsCompanyTexts['login'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <div class="hs-company-field"><i class="fa fa-user" aria-hidden="true"></i><input id="hsCompanyLogin" name="company_login" type="text" autocomplete="username" required></div>
                    <label for="hsCompanyPassword"><?php echo htmlspecialchars($hsCompanyTexts['password'], ENT_QUOTES, 'UTF-8'); ?></label>
                    <div class="hs-company-field"><i class="fa fa-lock" aria-hidden="true"></i><input id="hsCompanyPassword" name="company_password" type="password" autocomplete="current-password" required></div>
                    <div class="hs-company-modal-actions">
                        <button type="button" class="hs-company-cancel" data-hs-company-close><?php echo htmlspecialchars($hsCompanyTexts['cancel'], ENT_QUOTES, 'UTF-8'); ?></button>
                        <button type="submit" class="hs-company-submit"><i class="fa fa-plus" aria-hidden="true"></i><?php echo htmlspecialchars($hsCompanyTexts['addSwitch'], ENT_QUOTES, 'UTF-8'); ?></button>
                    </div>
                </form>
            </div>
        </div>
        <?php if ($hsMultiFlash && isset($hsMultiFlash['message'])) { ?>
        <div class="hs-company-toast <?php echo (isset($hsMultiFlash['type']) && $hsMultiFlash['type'] === 'success') ? 'is-success' : 'is-error'; ?>" id="hsCompanyToast" role="status">
            <i class="fa <?php echo (isset($hsMultiFlash['type']) && $hsMultiFlash['type'] === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>" aria-hidden="true"></i>
            <span><?php echo htmlspecialchars($hsMultiFlash['message'], ENT_QUOTES, 'UTF-8'); ?></span>
            <button type="button" data-hs-toast-close aria-label="Zavřít"><i class="fa fa-times" aria-hidden="true"></i></button>
        </div>
        <?php } ?>
        <?php } ?>

        <?php if ($hsMultiReady) { ?>
        <form id="hsLogoutCurrentForm" method="post" action="/str/company-switch.php" style="display:none" aria-hidden="true">
            <input type="hidden" name="action" value="logout_current">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($hsMultiCsrf, ENT_QUOTES, 'UTF-8'); ?>">
        </form>
        <?php } ?>

        <!--sidebar left start-->
        <aside class="sidebar sidebar-left">
            <div class="sidebar-profile">
                <div class="avatar">
                    
                     <?php   
                        
                        if ($_SESSION["ObsluhaFoto"]!="") {
                            /*Logo obsluh*/
                            echo "<img class=\"img-circle profile-image\" src=\"../img/obsluhy/".$_SESSION["ObsluhaFoto"]."\" alt=\":)\">";
                        }elseif ($_SESSION["k_poduzivatele_foto"]!="") {
                            echo "<img class=\"profile-image img-circle_zakaznici\" src=\"/str/strana/galerie/manager/m".$_SESSION["k_poduzivatele_foto"]."\" alt=\"\">";
                        }else{
                            

                                if ( $_SESSION["JeObsluha"] == "1") {
                                    echo "<img class=\"img-circle profile-image\" src=\"../img/obsluhy/sys/default_user.png\" alt=\":)\">";
                                }else{
                                            /*Logo softu*/
                                            if ($_SESSION["k_soft"] =="HairSoft") {
                                                echo "<img class=\"img-circle profile-image\" src=\"../img/profile.jpg\" alt=\"profile\">";
                                            }elseif($_SESSION["k_soft"] =="ZooSoft"){
                                                echo "<img class=\"img-circle profile-image\" src=\"../img/profilezoo.png\" alt=\"profile\">";
                                            }   
                                }
                        }

                        
                        


                     ?>
                   
                    
                    <!-- <i class="on border-dark animated bounceIn"></i> -->
                </div>
                <div class="profile-body dropdown">
                    <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"></a>

                        <h4><?php echo $_SESSION["uzivatel_prijmeni_jmeno"]; ?></h4>


                    <!-- <small class="title">Front-end Developer</small> <span class="caret"></span>-->
                    <!--
                    <ul class="dropdown-menu animated fadeInRight" role="menu">
                      
                        <li class="divider"></li>
                        <li>
                            <a href="index.php?strana=MujUcet">
                                <span class="icon"><i class="fa fa-user"></i>
                                </span>Můj účet</a>
                        </li>
                        
                        <li class="divider"></li>
                        <li>
                            <a href="logout.php">
                                <span class="icon"><i class="fa fa-sign-out"></i>
                                </span>Odhlásit</a>
                        </li>
                    </ul>
                    -->
                    
                    
                    
                </div>
            </div>
            
            <nav>
                <h5 class="sidebar-header">Navigace</h5>
                <ul class="nav nav-pills nav-stacked">
                    
                  
                 <?php  
                  //Dashboard
                  if ((($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="1") )) {
                   ?> 
                     <li class="nav-dropdown">
                        <a href="index.php?strana=ZmenaObsluhy" title="Změna obsluhy" data-hs-staff-switch-launch>
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-switch-user" xlink:href="#hs-icon-switch-user"></use></svg>  Změna obsluhy
                        </a>
                     </li>
                    <?php  
                  } 
                  ?>   

                  <?php  
                  //Dashboard
                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Dashboard", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" )) {
                  //if (($_SESSION["JePoduzivatel"]=="1" and in_array("Dashboard", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0")) {
                   ?> 
                     <li class="nav-dropdown">
                        <a href="index.php" title="Dashboard">
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-dashboard" xlink:href="#hs-icon-dashboard"></use></svg>  Dashboard
                        </a>
                     </li>
                    <?php  
                  } 
                  ?>   


                  <?php  
                  //Novy zakaznik - samostatne pravo v nastaveni uzivatele/obsluhy
                    
                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("NovyZakaznik", $_SESSION["SeznamPravPoduzivatele"])) or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0")) {
                   ?> 
                     <li class="nav-dropdown">
                        <a href="index.php?strana=KartaOsoby" title="Nový zákazník">
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-user-plus" xlink:href="#hs-icon-user-plus"></use></svg>  Nový zákazník
                        </a>
                     </li>
                    <?php  
                  } 
                  ?>   




                    

                  <?php
                                                    //Zjisteni poctu vyplnenych emailu
                                                    
                                                    if ($_SESSION["k_email"]!="") {
                                                       $s_email = $_SESSION["k_email"];
                                                       
                                                       
                                                       /*Prava na pobocku*/
                                                           
                                                           if ($_SESSION["JeObsluha"] == "1") {
                                                              /*Obnsluha*/
                                                              $idpobockyprava = $_SESSION["k_poduzivatele_obsluha_sw_id"];
                                                              $select_na_pobocku= "SELECT `sw_info`.`sw_jmeno_pobocky`, `sw_info`.`sw_id` FROM `sw_email_pobocka` join `k_uzivatele` on `sw_email_pobocka`.`k_id` = `k_uzivatele`.`k_id` join `sw_info` on `sw_info`.`sw_id` = `sw_email_pobocka`.`sw_id` WHERE `k_uzivatele`.`k_email` = '$s_email' and `sw_info`.`sw_id` = ".$idpobockyprava;
                                                           }else{
                                                            /*Poduzivatel - manazer*/
                                                              $select_na_pobocku= "SELECT `sw_info`.`sw_jmeno_pobocky`, `sw_info`.`sw_id` FROM `sw_email_pobocka` join `k_uzivatele` on `sw_email_pobocka`.`k_id` = `k_uzivatele`.`k_id` join `sw_info` on `sw_info`.`sw_id` = `sw_email_pobocka`.`sw_id` WHERE `k_uzivatele`.`k_email` = '$s_email'";
                                                           }

                                                       //$select_na_pobocku= "SELECT `sw_info`.`sw_jmeno_pobocky`, `sw_info`.`sw_id` FROM `sw_email_pobocka` join `k_uzivatele` on `sw_email_pobocka`.`k_id` = `k_uzivatele`.`k_id` join `sw_info` on `sw_info`.`sw_id` = `sw_email_pobocka`.`sw_id` WHERE `k_uzivatele`.`k_email` = '$s_email'";
                                                       if (!$select_na_pobocku) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                                                                              
                                                        //echo $select_na_pobocku;


                                                        $vysledek_na_pobocku=$mysqli->query($select_na_pobocku);
                                                        $radku_na_pobocku=$vysledek_na_pobocku->num_rows;                                       

                                                        if ($radku_na_pobocku=="1" and $_SESSION["pobocka_id"]=="") {
                                                            while ($na_pobocku=MySQLi_Fetch_Array($vysledek_na_pobocku)):   
                                                              $idp= $na_pobocku["sw_id"] ;
                                                            endwhile;  

                                                                   //Vybrat prvni
                                                                   echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?zmena_pobocky=1&aktivni_pobocka=".$idp."\">";
                                                                   return;
                                                              
                                                        //Vice pobocek
                                                        }elseif($radku_na_pobocku > "1") {
                                                                                   
                                                            
                                                            if (($_SESSION["JePoduzivatel"]=="1" and $_SESSION["PocetPovolenychPobocek"] > "1" ) or ($_SESSION["JePoduzivatel"]=="0")) {
                                                              ?> 
                                                                <li class="nav-dropdown">
                                                                  <a href="#" title="Pobočky">
                                                                      <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-building" xlink:href="#hs-icon-building"></use></svg>Pobočky
                                                                  </a>
                                                                    <ul class="nav-sub">
                                                              <?php
                                                             }
                                                          
                                                          while ($na_pobocku=MySQLi_Fetch_Array($vysledek_na_pobocku)):   
                                                               
                                                              $idp= $na_pobocku["sw_id"] ;
                                                                





                                                              //pro poduzivatele vybrat jako prvni jen tu na kter ma pravo
                                                              if (($_SESSION["JePoduzivatel"]=="1" and $_SESSION["PocetPovolenychPobocek"] > "0" and in_array($idp, $_SESSION["SeznamPravPoduzivateleNaPobocky"])))  {
                                                                  
                                                                  //echo "<script type='text/javascript'>alert('".$idp."+".$aktivni_pobocka."*".$_SESSION["PocetPovolenychPobocek"] ."');</script>";
                                                                  //Vybrat prvni - pro poduzivatele na kterou ma prava 
                                                                   //if ($idp!="" and $_SESSION["pobocka_id"]=="" ) {
                                                                  if ($idp!="" and $_SESSION["PoduzivatelProbehloPresmerovani"]=="" ) {
                                                                                                                                    
                                                                    $_SESSION["PoduzivatelProbehloPresmerovani"]="1";
                                                                    echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?zmena_pobocky=1&aktivni_pobocka=".$idp."\">";
                                                                       return;
                                                                  }

                                                              }else{

                                                                  //Vybrat prvni - pro admina
                                                                  if ($idp!="" and $_SESSION["pobocka_id"]=="") {
                                                                    //echo "<script type='text/javascript'>alert('"."+".$aktivni_pobocka."*".$_SESSION["PocetPovolenychPobocek"] ."');</script>";
                                                                    echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?zmena_pobocky=1&aktivni_pobocka=".$idp."\">";
                                                                       return;
                                                                  }

                                                              }



                                                              
                                                                                                                          
                                                                  
                                                          
                                                               //Ma poduzivatel pravo na pobocku?
                                                               if (($_SESSION["JePoduzivatel"]=="1" and $_SESSION["PocetPovolenychPobocek"] > "1" and in_array($idp, $_SESSION["SeznamPravPoduzivateleNaPobocky"]))  or ($_SESSION["JePoduzivatel"]=="0")) {
                                                                ?>
                                                                        <li>
                                                                            <a href="index.php?zmena_pobocky=1&aktivni_pobocka=<?php echo $idp; ?>" title="">
                                                                                 <?php
                                                                                   /*JePobockaOnline($idp,$mysqli)*/

                                                                                   echo JePobockaOnlineMenu($idp,$mysqli)."&nbsp;&nbsp;<span data-hs-i18n-ignore>".$na_pobocku["sw_jmeno_pobocky"]."</span> ";
                                                                                 ?> 
                                                                            </a>
                                                                        </li>                                                          
                                                                 <?php
                                                                }  
                                                          endwhile;
                                                            


                                                          
                                                          if (($_SESSION["JePoduzivatel"]=="1" and $_SESSION["PocetPovolenychPobocek"] > "1" ) or ($_SESSION["JePoduzivatel"]=="0")) {
                                                              ?> 
                                                                    </ul>
                                                                   </li>                                    
                                                          
                                                           <?php
                                                          }
                                                        }
                                                      }  
                                                     
                                                     ?>
                      
                    
                  <?php  
                  //Zakaznici
                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Zakaznici", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" )) {
                  //if (($_SESSION["JePoduzivatel"]=="1" and in_array("Zakaznici", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0")) {
                   ?> 

                    <li class="nav-dropdown">
                        <a href="index.php?strana=Zakaznici&AkceTab=Vsechny" title="Seznam zákazníků">
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-users" xlink:href="#hs-icon-users"></use></svg>  Seznam zákazníků
                        </a>
                    </li>
                    <?php  
                  } 
                  ?>



                    
                    
                  <?php  
                  //rezervace
                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Rezervace", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" )) {
                  //if (($_SESSION["JePoduzivatel"]=="1" and in_array("Rezervace", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0")) {
                   ?> 
                    <li class="nav-dropdown<?php echo $strana === 'RezervaceBonfero' ? ' hs-submenu-open' : ''; ?>">
                        <a href="#" title="Rezervace" aria-expanded="<?php echo $strana === 'RezervaceBonfero' ? 'true' : 'false'; ?>">
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-calendar" xlink:href="#hs-icon-calendar"></use></svg>  Rezervace
                        </a>
                        <ul class="nav-sub">
                            <li<?php echo $strana === 'RezervaceBonfero' ? ' class="active"' : ''; ?>>
                                <a href="index.php?strana=RezervaceBonfero" title="Administrace Bonfero">
                                     Administrace
                                </a>
                            </li>
                            
                            <li>
                                <a target="_blank" href="https://calendar.google.com/calendar" title="Google kalendář">
                                     Google kalendář
                                </a>
                            </li>

                            <li style="display: none;">
                                <a target="_blank" href="http://www.volno.hairsoft.cz" title="Volno dnes / Pořadník">
                                    Volno dnes / Pořadník
                                </a>
                            </li>

                           

                        </ul>
                    </li>
                   <?php  
                  } 
                  ?>


                    
                    
                      
                      
                  <?php  
                  //trzby
                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Trzby", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" )) {
                  //if (($_SESSION["JePoduzivatel"]=="1" and in_array("Trzby", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0")) {
                   ?> 

                      <li class="nav-dropdown">
                        <a href="#" title="Tržby">
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-chart" xlink:href="#hs-icon-chart"></use></svg>  Tržby
                        </a>
                        <ul class="nav-sub">
                            <li>
                                <a href="index.php?strana=CelkoveTrzby" title="Denní tržby">
                                     Denní tržby
                                </a>
                            </li>
                            
                            <li>
                                <a href="index.php?strana=MesicniTrzby" title="Měsíční tržby">
                                     Měsíční tržby
                                </a>
                            </li>
                            
                            <li>
                                <a href="index.php?strana=TrzbyDleObsluhy" title="Roční tržby">
                                     Roční tržby
                                </a>
                            </li>
                            
                            
                            <li>
                                <a href="index.php?strana=TrzbyOdPocatku" title="Celkové tržby">
                                     Celkové tržby
                                </a>
                            </li>
                        </ul>
                    </li>
                  <?php  
                  } 
                  ?>

                    
                  <?php  
                  //sklad
                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Sklad", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" )) {
                  //if (($_SESSION["JePoduzivatel"]=="1" and in_array("Sklad", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0")) {
                   ?>    
                    <li class="nav-dropdown">
                        <a target="_self" href="index.php?strana=Sklad"  title="Sklad">
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-package" xlink:href="#hs-icon-package"></use></svg>  Sklad
                        </a>
                        
                    </li>
                  <?php  
                  } 
                  ?>

                    
                  <?php  
                  //Voucher
                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Voucher", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" )) {
                  //if (($_SESSION["JePoduzivatel"]=="1" and in_array("Voucher", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0")) {
                   ?>    
                    <li class="nav-dropdown">
                        <a target="_self" href="index.php?strana=Voucher"  title="Voucher">
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-ticket" xlink:href="#hs-icon-ticket"></use></svg>  Vouchery
                        </a>
                        
                    </li>
                  <?php  
                  } 
                  ?>

                  <?php  
                  //Hodnoceni spokojenosti
                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Hodnoceni", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" )) {
                  //if (($_SESSION["JePoduzivatel"]=="1" and in_array("SMS", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0")) {
                   ?>    

                    <li class="nav-dropdown">
                        <a target="_self" href="#"  title="Hodnocení spokojenosti">
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-star" xlink:href="#hs-icon-star"></use></svg>  Hodnocení 
                        </a>

                          <ul class="nav-sub">
                            <li>
                                <a href="index.php?strana=SpokojenostStatistika" title="Statistika hodnocení spokojenosti">
                                     Statistika
                                </a>
                            </li>
                            
                            
                            <li>
                                <a href="index.php?strana=SpokojenostNastaveni" title="Nastavení hodnocení spokojenosti">
                                     Nastavení
                                </a>
                            </li>
                         
                        </ul>
                    </li>
                  <?php  
                  } 
                  ?>



                    
                  <?php  
                  //SMS
                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("SMS", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" )) {
                  //if (($_SESSION["JePoduzivatel"]=="1" and in_array("SMS", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0")) {
                   ?>    

                    <li class="nav-dropdown">
                        <a target="_self" href="#"  title="SMS a hovory">
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-messages" xlink:href="#hs-icon-messages"></use></svg>  SMS a hovory
                        </a>

                          <ul class="nav-sub">
                            <li>
                                <a href="index.php?strana=Sms" title="">
                                     Přehled SMS a hovorů
                                </a>
                            </li>
                            
                            
                            <li>
                                <a href="index.php?strana=PrichoziHovory" title="Příchozí hovory">
                                     Příchozí hovory
                                </a>
                            </li>
                            
                           
                            <li>
                                <a href="index.php?strana=PrichoziSMS" title="Příchozí SMS">
                                     Příchozí SMS
                                </a>
                            </li>
                            
                            
                            
                            <li>
                                <a href="index.php?strana=OdchoziSMS" title="Odchozí SMS">
                                     Odchozí SMS
                                </a>
                            </li>
                        </ul>
                    </li>
                  <?php  
                  } 
                  ?>
                    
                <!--
                  <?php  
                  //Kamery
                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Kamery", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" )) {
                  //if (($_SESSION["JePoduzivatel"]=="1" and in_array("Kamery", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0")) {
                   ?>    
                    <li class="nav-dropdown">
                        <a href="#" title="Kamerový dohled">
                            <i class="fa fa-fw  icon-camcorder"></i>  Kamerový dohled
                        </a>
                        <ul class="nav-sub">





                             <?php
                                                    //vypis kamer



                                                       $select_na_kameru= "SELECT * FROM `k_kamery` WHERE Uzivatel_ID = $Uzivatel_ID";

                                                       if (!$select_na_kameru) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}

                                                        $vysledek_na_kameru=$mysqli->query($select_na_kameru);
                                                        $radku_na_kameru=$vysledek_na_kameru->num_rows;

                                                        if ($radku_na_kameru> 0) {

                                                          while ($na_kameru=MySQLi_Fetch_Array($vysledek_na_kameru)):

                                                              $id_kamery= $na_kameru["kamery_id"] ;

                                                              //

                                                              // SEM PRIJDE KONTROLA NA PRAVA NA KAMERU

                                                              //



                                                          ?>
                                                              <li>
                                                                  <a href="index.php?strana=KameraPage&kamera_id=<?php echo $id_kamery; ?>" title="">
                                                                       <?php
                                                                         echo $na_kameru["kamery_jmeno"]." ";
                                                                       ?>
                                                                  </a>
                                                              </li>

                                                          <?php

                                                          endwhile;

                                                        }

                                                     ?>




                            <li>
                                <a href="index.php?strana=NastaveniKamerovyDohled" title="Nastavení kamerového dohledu">
                                     Nastavení
                                </a>
                            </li>
                            
                            
                        </ul>
                    </li>
                    <?php  
                  } 
                  ?>

                  -->


                   




                    


                  <?php  
                  //Hodnoceni spokojenosti
                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Uzivatele", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" )) {
                   ?>    

                    <li class="nav-dropdown">
                        <a href="#" title="Uživatelé">
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-user-cog" xlink:href="#hs-icon-user-cog"></use></svg>  Uživatelé
                        </a>

                        <ul class="nav-sub">
                            <li>
                                <a href="index.php?strana=Uzivatele" title="Přehled uživatelů">
                                     Přehled uživatelů
                                </a>
                            </li>
                            
                            <li>
                                <a href="index.php?strana=Dochazka" title="Docházka">
                                     Docházka
                                </a>
                            </li>
                        </ul>
                      
                    </li>

                    
                    



                  <?php  
                  } 
                  ?>

                  <?php  
                  //Cenik
                  
                 if ($_SESSION["uzivatel_prijmeni_jmeno"]=="Tomáš Cabaj") {
                        
   
                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Cenik", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" )) {
                   ?>    

                    <li class="nav-dropdown">
                        <a href="index.php?strana=Cenik" title="Ceník">
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-list" xlink:href="#hs-icon-list"></use></svg>  Ceník
                        </a>

                          
                    </li>
                  <?php  
                  } 
                 } 
                  ?>





                  <?php  
                  // Nastavení: pouze hlavní administrátor, ne demo.
                  // V174: číselník pojišťoven je součást Nastavení; samostatné menu Systém bylo odstraněno.
                  if ($hsIsMainAdministrator and $_SESSION["pobocka_id"] > "0") {
                   ?>    
                    <li class="nav-dropdown">
                        <a target="_self" href="#"  title="Nastavení">
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-settings" xlink:href="#hs-icon-settings"></use></svg>  Nastavení
                        </a>

                          <ul class="nav-sub">
                            <li>
                                <a href="index.php?strana=NastaveniPristupy" title="Přístupy">
                                     Přístupy
                                </a>
                            </li>
                            <li>
                                <a href="index.php?strana=NastaveniCiselnikPojistoven" title="Číselník pojišťoven">
                                     Číselník pojišťoven
                                </a>
                            </li>
                        </ul>
                    </li>
                  <?php  
                  } 
                  ?>

                    


                    
                    <li class="nav-dropdown hs-menu-logout">
                        <a href="logout.php" title="Odhlásit" onclick="var f=document.getElementById('hsLogoutCurrentForm'); if(f){f.submit(); return false;}">
                            <svg class="hs-menu-icon" aria-hidden="true"><use href="#hs-icon-logout" xlink:href="#hs-icon-logout"></use></svg>  Odhlásit
                        </a>
                    </li>

                      <!--
                        <li class="nav-dropdown">
                        <a href="#" title="UI Elements">
                            <i class="fa fa-fw fa-file-text"></i> Pages
                        </a>
                        <ul class="nav-sub">
                            <li>
                                <a href="pages-blank.html" title="Buttons">
                                     Blank Page
                                </a>
                            </li>
                            <li>
                                <a href="pages-another-blank.html" title="Sliders &amp; Progress">
                                     Another Blank Page
                                </a>
                            </li>
                        </ul>
                    </li>
                      -->

                </ul>
            </nav>
                   


                    
        </aside>









        <!--sidebar left end-->
        <!--main content start-->
              
         <?php
               if ($strana!="") {
                 $filename = 'strana/'.$strana.'.php';

                            if ($strana=="SeznamObsluh") {
                                /*Zobrazeni stranky mimo hlavni bude ve fullscrenu*/
                                $page = "https://klient.hairsoft.cz/str/strana/".$strana.".php";
                                $sec = "300";
                                echo $page;
                                echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                                exit;


                            }else{

                                                    /*zobrazeni stranky v menu*/
                                                    if (file_exists($filename) || ($strana === 'RezervaceBonfero' && is_file(hsClientUiPagePath('strana/'.$strana.'.php')))) {
                                                                          
                                                      require hsClientUiPagePath('strana/'.$strana.'.php');

                                                      //REFRESHE na jednotlive stranky
                                                      //Trzby
                                                      if ($filename=="strana/CelkoveTrzby.php") {
                                                           $page = "https://klient.hairsoft.cz/str/index.php?strana=CelkoveTrzby";
                                                           $sec = "300";
                                                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                                                      }

                                                      //Sklady
                                                      if ($filename=="strana/Sklad.php") {
                                                           $page = "https://klient.hairsoft.cz/str/index.php?strana=Sklad";
                                                           $sec = "300";
                                                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                                                      }
                                                      //Skladyxml
                                                      if ($filename=="strana/SkladXml.php") {
                                                           $page = "https://klient.hairsoft.cz/str/index.php?strana=SkladXml";
                                                           $sec = "600";
                                                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                                                      }

                                                      //Voucher Historie
                                                      if ($filename=="strana/VoucherHistorie.php") {
                                                           $page = "https://klient.hairsoft.cz/str/index.php?strana=VoucherHistorie";
                                                           $sec = "3000";
                                                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                                                      }


                                                      

                                                      
                                                   } else {
                                                      require '404.php';
                                                  }

                            }  


                  
               }else {
                     
                    //Dashboard
                    if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1")and in_array("Dashboard", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0")) {    
                      require hsClientUiPagePath('strana/AktivniPlocha.php');  
                           $page = "https://klient.hairsoft.cz/str/index.php";
                           $sec = "600";
                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Zakaznici", $_SESSION["SeznamPravPoduzivatele"])) ) {    
                           //Zakaznici prvni v poradi
                      require hsClientUiPagePath('strana/Zakaznici.php');  
                           $page = "https://klient.hairsoft.cz/str/index.php";
                           $sec = "600";
                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("NovyZakaznik", $_SESSION["SeznamPravPoduzivatele"])) ) {    
                           //Zakaznici novy zakaznik
                      require hsClientUiPagePath('strana/KartaOsoby.php');  
                           $page = "https://klient.hairsoft.cz/str/index.php";
                           $sec = "600";
                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Trzby", $_SESSION["SeznamPravPoduzivatele"]))  ) {    
                      //Trzby denni 
                      require hsClientUiPagePath('strana/CelkoveTrzby.php');  
                           $page = "https://klient.hairsoft.cz/str/index.php";
                           $sec = "600";
                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Sklad", $_SESSION["SeznamPravPoduzivatele"]))  ) {    
                      //Sklad
                      require hsClientUiPagePath('strana/Sklad.php');  
                           $page = "https://klient.hairsoft.cz/str/index.php";
                           $sec = "600";
                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Voucher", $_SESSION["SeznamPravPoduzivatele"]))  ) {    
                      //Voucher
                      require hsClientUiPagePath('strana/Voucher.php');  
                           $page = "https://klient.hairsoft.cz/str/index.php";
                           $sec = "600";
                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("SMS", $_SESSION["SeznamPravPoduzivatele"]))  ) {    
                      //SMS
                      require hsClientUiPagePath('strana/Sms.php');  
                           $page = "https://klient.hairsoft.cz/str/index.php";
                           $sec = "600";
                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Cenik", $_SESSION["SeznamPravPoduzivatele"]))  ) {    
                      //SMS
                      require hsClientUiPagePath('strana/Cenik.php');  
                           $page = "https://klient.hairsoft.cz/str/index.php";
                           $sec = "600";
                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                    
                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Hodnoceni", $_SESSION["SeznamPravPoduzivatele"]))  ) {    
                      //Hodnoceni
                      require hsClientUiPagePath('strana/SpokojenostStatistika.php');  
                           $page = "https://klient.hairsoft.cz/str/index.php";
                           $sec = "600";
                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                    
                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Kamery", $_SESSION["SeznamPravPoduzivatele"]))  ) {    
                      //Kamery
                      require hsClientUiPagePath('strana/NastaveniKamerovyDohled.php');  
                           $page = "https://klient.hairsoft.cz/str/index.php";
                           $sec = "600";
                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                    }else{
                      require hsClientUiPagePath('strana/PrazdnaStrana.php');  
                           $page = "https://klient.hairsoft.cz/str/index.php";
                           $sec = "600";
                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";
                    }





                   































               }  
          ?>
        
               
        
        <!--main content end-->

    
    

    </section>

    <!--sidebar right start-->
   
      <?php 
           //jisteni skupiny
           /*$sqldotaz= "SELECT `sw_skupina_id` FROM `sw_info` WHERE `sw_id` = ".$_SESSION["pobocka_id"];
           if (!$sqldotaz) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}
           $vysledek=$mysqli->query("$sqldotaz");
           $sms_fronta=MySQLi_Fetch_Array($vysledek);*/
           //echo $sms_fronta[sw_skupina_id];
           //podmínka zobrazit jen u nas :)
           //if ($Uzivatel_ID==10 or $Uzivatel_ID==11 or $Uzivatel_ID==26 or $Uzivatel_ID==2) {
            

              /*
                Spocitat pocet klientu
              */

              

                  
                  



                                                                    $select_na_lidi_cnt= "SELECT count(*) as 'CELKEM' FROM `klient_lidi` where  `lidi_sw_id` =  ".$_SESSION["pobocka_id"]." and `lidi_aktivni` = 1;";
                                                                    //echo $select_na_lidi_cnt;

                                                                    if (!$select_na_lidi_cnt) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                                    $vysledek_na_lidi_cnt=$mysqli->query($select_na_lidi_cnt);
                                                                    //$radku_na_lidi_cnt=$vysledek_na_lidi_cnt->num_rows;                                       

                                                                    while ($na_cnt=MySQLi_Fetch_Array($vysledek_na_lidi_cnt)):   
                                                                            $radku_na_lidi_cnt = $na_cnt["CELKEM"];
                                                                    endwhile;
                                                              

                                                           ?>

                                                                                                 <aside id="sidebar-right">

                                                                                                  <h4 class="sidebar-title">
                                                                                                      <span class="hs-directory-title-icon" aria-hidden="true">
                                                                                                          <svg><use href="#hs-icon-users" xlink:href="#hs-icon-users"></use></svg>
                                                                                                      </span>
                                                                                                      <span class="hs-directory-title-text">
                                                                                                          <span>Adresář klientů</span>
                                                                                                          <small>Rychlý kontakt</small>
                                                                                                      </span>
                                                                                                      <span class="hs-directory-count">
                                                                                                          <span data-hs-i18n-ignore><?php echo number_format((int) $radku_na_lidi_cnt, 0, ',', ' ');?></span>
                                                                                                          <span>klientů</span>
                                                                                                      </span>
                                                                                                  </h4>
                                                                                                  <div id="contact-list-wrapper">
                                                                                                     <div class="heading">
                                                                                                          <ul>
                                                                                                              <?php
                                                                                                                if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("NovyZakaznik", $_SESSION["SeznamPravPoduzivatele"])) or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0")) {
                                                                                                              ?>
                                                                                                                <li class="new-contact"><a href="index.php?strana=KartaOsoby" title="Nový zákazník"><i class="fa fa-plus"></i></a></li>
                                                                                                              <?php
                                                                                                                }
                                                                                                              ?>
                                                                                                              <li>
                                                                                                                  <input id="txt_hledani_lidi" type="text" class="search" placeholder="Hledej...">
                                                                                                                  <button type="submit" class="btn btn-sm btn-search"><i class="fa fa-search"></i>
                                                                                                                  </button>
                                                                                                              </li>
                                                                                                          </ul>

                                                                                                          <ul>
                                                                                                              <li>
                                                                                                                
                                                                                                                 <span  id="Abeceda_A" class="HledejPismeno">A</span>
                                                                                                                 <span  id="Abeceda_B" class="HledejPismeno">B</span>
                                                                                                                 <span  id="Abeceda_C" class="HledejPismeno">C</span>
                                                                                                                 <span  id="Abeceda_D" class="HledejPismeno">D</span>
                                                                                                                 <span  id="Abeceda_E" class="HledejPismeno">E</span>
                                                                                                                 <span  id="Abeceda_F" class="HledejPismeno">F</span>
                                                                                                                 <span  id="Abeceda_G" class="HledejPismeno">G</span>
                                                                                                                 <span  id="Abeceda_H" class="HledejPismeno">H</span>
                                                                                                                 <span  id="Abeceda_I" class="HledejPismeno">I</span>
                                                                                                                 <span  id="Abeceda_J" class="HledejPismeno">J</span>
                                                                                                                 <span  id="Abeceda_K" class="HledejPismeno">K</span>
                                                                                                                 <span  id="Abeceda_L" class="HledejPismeno">L</span>
                                                                                                                 <span  id="Abeceda_M" class="HledejPismeno">M</span>
                                                                                                                 <span  id="Abeceda_N" class="HledejPismeno">N</span>
                                                                                                                 <span  id="Abeceda_O" class="HledejPismeno">O</span>
                                                                                                                 <span  id="Abeceda_P" class="HledejPismeno">P</span>
                                                                                                                 <span  id="Abeceda_Q" class="HledejPismeno">Q</span>
                                                                                                                 <span  id="Abeceda_R" class="HledejPismeno">R</span>
                                                                                                                 <span  id="Abeceda_S" class="HledejPismeno">S</span>
                                                                                                                 <span  id="Abeceda_T" class="HledejPismeno">T</span>
                                                                                                                 <span  id="Abeceda_U" class="HledejPismeno">U</span>
                                                                                                                 <span  id="Abeceda_V" class="HledejPismeno">V</span>
                                                                                                                 <span  id="Abeceda_W" class="HledejPismeno">W</span>
                                                                                                                 <span  id="Abeceda_X" class="HledejPismeno">X</span>
                                                                                                                 <span  id="Abeceda_Y" class="HledejPismeno">Y</span>
                                                                                                                 <span  id="Abeceda_Z" class="HledejPismeno">Z</span>
                                                                                                                 <span  id="Abeceda_~" class="HledejPismeno">~</span>
                                                                                                                 

                                                                                                                
                                                                                                              </li>
                                                                                                          </ul>

                                                                                                            <?php 
                                                                                                              echo "<ul id=\"ico_hledani_loader\" style=\"display:none;\">";
                                                                                                              echo " <li>";
                                                                                                              echo "   <center><img heigth=\"35\" width=\"35\" src=\"../img/BarberLoad.gif\" class=\"img-circle\" ></center>";
                                                                                                              echo " </li>";
                                                                                                              echo "</ul>";
                                                                                                             ?>
                                                                                                              
                                                                                                        
                                                                                                  </div>
                                                                                                      <div id="contact-list">

                                                                                                        

                                                                                                        <?php 
                                                                                                              /*
                                                                                                              echo "<ul>";
                                                                                                              echo " <li>";
                                                                                                              echo "  <div class=\"row\">";
                                                                                                              echo "   <div class=\"name\">Použij hledání...</div>";
                                                                                                              echo "  </div>";
                                                                                                              echo " </li>";
                                                                                                              echo "</ul>";
                                                                                                              */

                                                                                                                                
                                                                                                                               
                                                                                     

                                                                                                                                $select_na_lidi= "SELECT * FROM `klient_lidi` where  `lidi_sw_id` =  ".$_SESSION["pobocka_id"]." and `lidi_aktivni` = 1 order by `lidi_hs_surname` asc limit 20 ;";
                                                                                                                                 


                                                                                                                                       if (!$select_na_lidi) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   


                                                                                                                                        $vysledek_na_lidi=$mysqli->query($select_na_lidi);
                                                                                                                                        $radku_na_lidi=$vysledek_na_lidi->num_rows;                                       
                                                                                                                                        
                                                                                                                                        if ($radku_na_lidi>0) {
                                                                                                                                            while ($na_lidi=MySQLi_Fetch_Array($vysledek_na_lidi)):   
                                                                                                                                                   
                                                                                                                                                   $lidi_guid= $na_lidi["lidi_guid"] ;

                                                                                                                                                   $Jmeno= $na_lidi["lidi_hs_name"] ;
                                                                                                                                                   $Prijmeni= $na_lidi["lidi_hs_surname"] ;
                                                                                                                                                   $Telefon= $na_lidi["lidi_hs_phone"] ;
                                                                                                                                                   $Email= $na_lidi["lidi_hs_email"] ;

                                                                                                                                                /*Profilovka*/
                                                                                                                                                $select_na_profilovka= "SELECT `obrazek_jmeno_GUID` FROM `klient_lidi_obrazky` WHERE `obrazek_smazan` = 0 and `obrazek_profilovka` = 1 and `obrazek_osoba_GUID` =  '".$lidi_guid."' limit 1";
                                                                                                                                                if (!$select_na_profilovka) { die('Chyba pripojeni do DB!');}
                                                                                                                                                $vysledek_na_profilovka=$mysqli->query("$select_na_profilovka");
                                                                                                                                                $dataprofilovka=MySQLi_Fetch_Array($vysledek_na_profilovka);

                                                                                                                                                $obrazek_jmeno_GUID = $dataprofilovka["obrazek_jmeno_GUID"];



                                                                                                                                             //Telefon
                                                                                                                                             $Mobil= $na_lidi["lidi_hs_cell"] ;
                                                                                                                                                   $Tel = "";
                                                                                                                                                   if ($Telefon=="" and $Mobil!="") {
                                                                                                                                                    $Tel = $Mobil;
                                                                                                                                                   }

                                                                                                                                                   if ($Mobil=="" and $Telefon!="") {
                                                                                                                                                    $Tel = $Telefon;
                                                                                                                                                   }

                                                                                                                                                   if ($Mobil!="" and $Telefon!="") {
                                                                                                                                                    $Tel = $Mobil;
                                                                                                                                                   }

                                                                                                                                                   $WhatsappTel = preg_replace('/\D+/', '', $Tel);
                                                                                                                                                   if (substr($WhatsappTel, 0, 2) === "00") {
                                                                                                                                                     $WhatsappTel = substr($WhatsappTel, 2);
                                                                                                                                                   }
                                                                                                                                                   if (strlen($WhatsappTel) === 9) {
                                                                                                                                                     $WhatsappTel = "420".$WhatsappTel;
                                                                                                                                                   }
                                                                                                                                                   
                                                                                                                                                   //AVATAR
                                                                                                                                                   //1 = Muz, 2 = Zena, 3 = Dite (Customer.Genre) a neurceno je 0 ?
                                                                                                                                                   
                                                                                                                                                   switch ($na_lidi["lidi_hs_genre"] ) {
                                                                                                                                                    case '1':
                                                                                                                                                      $avatar = "../img/Muz.png";
                                                                                                                                                      break;
                                                                                                                                                    case '2':
                                                                                                                                                      $avatar = "../img/Zena.png";
                                                                                                                                                        break;                      
                                                                                                                                                    default:
                                                                                                                                                      $avatar = "../img/Nic.png";
                                                                                                                                                      break;
                                                                                                                                                   }


                                                                                                                                                   if ($obrazek_jmeno_GUID!="") {
                                                                                                                                                       $avatar = "https://klient.hairsoft.cz/str/strana/galerie/m".$obrazek_jmeno_GUID;
                                                                                                                                                   }



                                                                                              echo "<ul>";
                                                                                              echo " <li>";
                                                                                              echo "  <div class=\"row\" onclick=\"location.href='index.php?strana=KartaOsoby&osoba_guid=".$lidi_guid."';\">";
                                                                                              echo "   <div class=\"col-md-3\">";
                                                                                              echo "    <span class=\"avatar hs-contact-avatar\">";
                                                                                              if ($obrazek_jmeno_GUID!="") {
                                                                                                echo "     <img src=\"".$avatar."\" class=\"hs-contact-photo\" alt=\"\">";
                                                                                              }else{
                                                                                                echo "     <span class=\"hs-contact-avatar-fallback\" aria-hidden=\"true\"><svg viewBox=\"0 0 24 24\"><path d=\"M20 21a8 8 0 0 0-16 0\"></path><circle cx=\"12\" cy=\"7\" r=\"4\"></circle></svg></span>";
                                                                                              }
                                                                                              echo "     <i class=\"on animated bounceIn\"></i>";
                                                                                              echo "    </span>";
                                                                                              echo "   </div>";
                                                                                              echo "   <div class=\"col-md-9\">";
                                                                                              echo "    <div class=\"name\">".$Prijmeni." ".$Jmeno."</div>";
                                                                                              echo "     <small class=\"location text-muted\">".$Tel."</small>";
                                                                                              echo "     <div class=\"hs-contact-actions\">";
                                                                                              if ($Tel!="") {
                                                                                                echo "       <a class=\"hs-contact-action hs-contact-call\" href=\"tel:$Tel\" onclick=\"event.stopPropagation();\" title=\"Zavolat\"><svg aria-hidden=\"true\"><use href=\"#hs-icon-phone\" xlink:href=\"#hs-icon-phone\"></use></svg></a>";
                                                                                                echo "       <a class=\"hs-contact-action hs-contact-message\" href=\"sms:$Tel\" onclick=\"event.stopPropagation();\" title=\"Napsat SMS\"><svg aria-hidden=\"true\"><use href=\"#hs-icon-send\" xlink:href=\"#hs-icon-send\"></use></svg></a>";
                                                                                              }
                                                                                              if ($Email!="") {
                                                                                                echo "       <a class=\"hs-contact-action hs-contact-email\" href=\"mailto:$Email\" onclick=\"event.stopPropagation();\" title=\"Napsat e-mail\" aria-label=\"Napsat e-mail\"><svg aria-hidden=\"true\"><use href=\"#hs-icon-at-sign\" xlink:href=\"#hs-icon-at-sign\"></use></svg></a>";
                                                                                              }
                                                                                              if ($WhatsappTel!="") {
                                                                                                echo "       <a class=\"hs-contact-action hs-contact-whatsapp\" href=\"https://wa.me/$WhatsappTel\" target=\"_blank\" rel=\"noopener\" onclick=\"event.stopPropagation();\" title=\"Napsat přes WhatsApp\"><svg aria-hidden=\"true\"><use href=\"#hs-icon-messages\" xlink:href=\"#hs-icon-messages\"></use></svg></a>";
                                                                                              }
                                                                                              echo "     </div>";
                                                                                              echo "    </div>";
                                                                                              echo "   </div>";
                                                                                              echo " </li>";
                                                                                              echo "</ul>";

                                                                                                                                            endwhile;  


                                                                                                                                        }
































                                                                                                             
                                                                                                         ?>

                                                                                                             <!--
                                                                                                         
                                                                                                        <ul>
                                                                                                              <li>
                                                                                                                  <div class="row">
                                                                                                                      <div class="col-md-3">
                                                                                                                          <span class="avatar">
                                                                                                                  <img src="../img/avatar_lidi.jpg" class="img-circle" alt="">
                                                                                                                    <i class="on animated bounceIn"></i>
                                                                                                                  </span>
                                                                                                                      </div>
                                                                                                                      <div class="col-md-9">
                                                                                                                          <div class="name">Cabaj Tomáš</div>
                                                                                                                          <small class="location text-muted">+420 606 467 039 </small>
                                                                                                                          
                                                                                                                      </div>
                                                                                                                  </div>
                                                                                                              </li>
                                                                                                              
                                                                                                          </ul> 
                                                                                                      </div>
                                                                                                 
                                                                                                      <div id="contact-user">
                                                                                                          <div class="chat-user active"><span><i class="icon-bubble"></i></span>
                                                                                                          </div>
                                                                                                          <div class="email-user"><span><i class="icon-envelope-open"></i></span>
                                                                                                          </div>
                                                                                                          <div class="call-user"><span><i class="icon-call-out"></i></span>
                                                                                                          </div>
                                                                                                      </div> 
                                                                                                      -->
                                                                                                       



                                                                                                       </div>
                                                                                                </div>
                                                                                              </aside>

    <?php 
        //}
     ?>
    
       
    


    <!--/sidebar right end-->
    <!--Global JS-->
    <script src="../js/vendor/modernizr-2.6.2.min.js"></script>
    <script src="../js/vendor/jquery-1.11.1.min.js"></script>
    <script src="../plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="../plugins/navgoco/jquery.navgoco.min.js"></script>
    <script src="../plugins/pace/pace.min.js"></script>
    <script src="../js/src/app.js"></script>
    
        
        <link rel="stylesheet" href="../src/richtext.min.css">
        <script type="text/javascript" src="../src/jquery.richtext.js"></script>


    
    <script src="../plugins/switchery/switchery.min.js"></script>
    <script src="../plugins/fullscreen/jquery.fullscreen-min.js"></script>
    
   <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"> 
   <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script> 
  
   <!-- ../plugins/chartjs/Chart.min.js -->
   <script src="../plugins/chartjs/Chart.bundle.js"></script> 
   
 
    
    
    
    <!--Page Level JS-->
    <script src="../plugins/countTo/jquery.countTo.js"></script>
    <script src="../plugins/weather/js/skycons.js"></script>
    <script src="../plugins/daterangepicker/moment.min.js"></script>
    <script src="../plugins/daterangepicker/daterangepicker.js"></script>
    
        <!-- Morris  -->
    <script src="../plugins/morris/js/morris.min.js"></script>
    <script src="../plugins/morris/js/raphael.2.1.0.min.js"></script>
    <!-- Vector Map  -->
    <script src="../plugins/jvectormap/js/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="../plugins/jvectormap/js/jquery-jvectormap-world-mill-en.js"></script>
    <!-- Gauge  -->
    <script src="../plugins/gauge/gauge.min.js"></script>
    <script src="../plugins/gauge/gauge-demo.js"></script>
    <!-- Calendar  -->
    <script src="../plugins/calendar/clndr.js"></script>
    <script src="../plugins/calendar/clndr-demo.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js"></script>
    <!-- Switch -->
    <script src="../plugins/switchery/switchery.min.js"></script>
    <!--Load these page level functions-->
   
    
    <script src="../plugins/dataTables/js/dataTables.bootstrap.js"></script>
    
    <!--PDF
   <script src="../plugins/dataTables/js/jquery.dataTables.js"></script>

    https://code.jquery.com/jquery-1.12.4.js
    https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js
    https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js
    https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js
    https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js
    https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js
    https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js
    https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js
    https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js
    -->

    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script> 
    <script src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script> 
    <script src="//cdn.datatables.net/buttons/1.3.1/js/buttons.flash.min.js"></script> 
    
     <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script> 

    <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/pdfmake.min.js"></script> 
    <script src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.27/build/vfs_fonts.js"></script> 
    <script src="//cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script> 
    <script src="//cdn.datatables.net/buttons/1.3.1/js/buttons.print.min.js"></script> 

    <script src="../plugins/icheck/js/icheck.min.js"></script>
    <!-- <script src="../plugins/switchery/switchery.min.js"></script> -->

    

    <script src="/hs-client-ui/js/app.js?v=196"></script>


<link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css"> 
<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css"> 
    

    






    <script>
    /*
    Language zmena textu na en cliboardu do cz 
    https://datatables.net/extensions/buttons/examples/html5/copyi18n.html

    */ 
   

    $(document).ready(function() {

             app.customCheckbox();
             
             //Disable tlacitko odeslat
             $("#btn_pridat_poduzivatele").prop("disabled",true);
             $("#k_poduzivatele_zmenit_heslo_ko").prop("disabled",true);

             $('#ZobrazitDivPoznamkyTimeline').bind('click', function() {
                $("#formPoznamky").show();      
                $("#ZobrazitDivPoznamkyTimeline").hide(); 
             });
             




             $('#table_PrehledZiskovostVhednotlivychLetech,#table_CelkovySumarZaStrediska,#table_CelkoveTrzbyZaObsluhu,#table_RocniCelkoveTrzbyZaObsluhu,#table_MesicniSumarZaStrediska,#table_MesicniSumarZaJednotlivce,#table_DenniSumarZaJednotlivce,#table_DenniSumarZaStrediska').DataTable( {
                      
                      dom: 'Bfrtip',
                              extend: 'collection',
                              orientation: 'LETTER',
                              pageSize: 'A4',
                      
                    "buttons": [
                        {
                            extend: 'collection',
                            text: ' <i class="fa fa-save"></i><b> Eport</b> (pouze Web rozhraní)',
                            //className: 'fa fa-save',
                            
                            buttons: [
                                           {
                                              text: '<i class="fa fa-copy"></i><b> Copy</b>',
                                              extend: 'copy',
                                              //className: 'fa fa-copy'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> CSV</b>',
                                              extend: 'csv',
                                              charset: 'UTF-16LE',
                                              fieldSeparator: '\t',
                                              bom: true,
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> XLS</b>',
                                              extend: 'excelHtml5',
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-pdf-o"></i><b> PDF</b>',
                                              extend: 'pdfHtml5',
                                              orientation: 'landscape',
                                              pageSize: 'A4',
                                              filename: function () {
                                                if (window.hsLifetimeRevenuePdfPageActive && window.hsLifetimeRevenuePdfPageActive()) return window.hsLifetimeRevenuePdfFilename();
                                                if (window.hsAnnualRevenuePdfPageActive && window.hsAnnualRevenuePdfPageActive()) return window.hsAnnualRevenuePdfFilename();
                                                if (window.hsMonthlyRevenuePdfFilename && window.hsMonthlyRevenuePdfPageActive && window.hsMonthlyRevenuePdfPageActive()) {
                                                  return window.hsMonthlyRevenuePdfFilename();
                                                }
                                                if (window.hsDailyRevenuePdfFilename && window.hsDailyRevenuePdfPageActive && window.hsDailyRevenuePdfPageActive()) {
                                                  return window.hsDailyRevenuePdfFilename();
                                                }
                                                return 'HairSoft_export';
                                              },
                                              customize: function (doc) {
                                                if (window.hsLifetimeRevenuePdfPageActive && window.hsLifetimeRevenuePdfPageActive()) { window.hsCustomizeLifetimeRevenuePdf(doc); return; }
                                                if (window.hsAnnualRevenuePdfPageActive && window.hsAnnualRevenuePdfPageActive()) { window.hsCustomizeAnnualRevenuePdf(doc); return; }
                                                if (window.hsCustomizeMonthlyRevenuePdf && window.hsMonthlyRevenuePdfPageActive && window.hsMonthlyRevenuePdfPageActive()) {
                                                  window.hsCustomizeMonthlyRevenuePdf(doc);
                                                  return;
                                                }
                                                if (window.hsCustomizeDailyRevenuePdf && window.hsDailyRevenuePdfPageActive && window.hsDailyRevenuePdfPageActive()) {
                                                  window.hsCustomizeDailyRevenuePdf(doc);
                                                }
                                              },
                                              //className: 'fa fa-file-pdf-o'
                                           },
                                           {
                                              text: '<i class="fa fa-print"></i><b> Tisk</b>',
                                              extend: 'print',
                                              //className: 'fa fa-print'
                                           }
                            ]
                        }
                    ]
            ,

                          "scrollX": false,
                          "searching": false,
                          "paging":    false,
                          "responsive": false,
                          "ordering":  false,
                          "info":      false,     
                      });

       
       

       $('#TabulkaSeznamOtazek').DataTable( {
            /*
            "aoColumns": [
                            null,
                            { "sType": "date-cz" },
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            { "sType": "date-cz" },
                            { "sType": "date-cz" },
                            null,
                            null
            ],
*/
             //"order": [[ 0, "desc" ]],

            "responsive": true,
            dom: 'Bfrtip',
                              extend: 'collection',
                              orientation: 'LETTER',
                              pageSize: 'A4',
                      
                    "buttons": [
                        {
                            extend: 'collection',
                            text: ' <i class="fa fa-save"></i><b> Export</b> (pouze Web rozhraní)',
                            //className: 'fa fa-save',
                            
                            buttons: [
                                           {
                                              text: '<i class="fa fa-copy"></i><b> Copy</b>',
                                              extend: 'copy',
                                              //className: 'fa fa-copy'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> CSV</b>',
                                              extend: 'csv',
                                              charset: 'UTF-16LE',
                                              fieldSeparator: '\t',
                                              bom: true,
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> XLS</b>',
                                              extend: 'excelHtml5',
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-pdf-o"></i><b> PDF</b>',
                                              extend: 'pdfHtml5',
                                              orientation: 'landscape',
                                              pageSize: 'A4',
                                              //className: 'fa fa-file-pdf-o'
                                           },
                                           {
                                              text: '<i class="fa fa-print"></i><b> Tisk</b>',
                                              extend: 'print',
                                              //className: 'fa fa-print'
                                           }
                            ]
                        }
                    ],
            "searching": true,
            "paging":    true,
            "ordering":  true,
            "info":      true,
            "bPaginate": false,
            //"responsive": true,


           

        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Poslední",
            "sNext":     "Další",
            "sPrevious": "Předešlá"
        },
        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                 }
        }
       });

$('#zakaznici_tabulka_timeline').DataTable( {
           
            "responsive": true,
            dom: 'Bfrtip',
                              extend: 'collection',
                              orientation: 'LETTER',
                              pageSize: 'A4',
                      
                    "buttons": [
                        {
                            extend: 'collection',
                            text: '<i class="fa fa-save" aria-hidden="true"></i><b>Export</b>',
                            //className: 'fa fa-save',
                            
                            buttons: [
                                           {
                                              text: '<i class="fa fa-copy"></i><b> Copy</b>',
                                              extend: 'copy',
                                              //className: 'fa fa-copy'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> CSV</b>',
                                              extend: 'csv',
                                              charset: 'UTF-16LE',
                                              fieldSeparator: '\t',
                                              bom: true,
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> XLS</b>',
                                              extend: 'excelHtml5',
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-pdf-o"></i><b> PDF</b>',
                                              extend: 'pdfHtml5',
                                              orientation: 'landscape',
                                              pageSize: 'A4',
                                              title: null,
                                              filename: function () {
                                                  return window.hsTimelinePdfFilename ? window.hsTimelinePdfFilename() : 'HairSoft_Timeline';
                                              },
                                              exportOptions: {
                                                  columns: [0, 1, 2],
                                                  format: {
                                                      body: function (data, row, column, node) {
                                                          return window.hsTimelinePdfCell ? window.hsTimelinePdfCell(data, column, node) : data;
                                                      }
                                                  }
                                              },
                                              customize: function (documentDefinition) {
                                                  if (window.hsCustomizeTimelinePdf) {
                                                      window.hsCustomizeTimelinePdf(documentDefinition);
                                                  }
                                              },
                                              //className: 'fa fa-file-pdf-o'
                                           },
                                           {
                                              text: '<i class="fa fa-print"></i><b> Tisk</b>',
                                              extend: 'print',
                                              //className: 'fa fa-print'
                                           }
                            ]
                        }
                    ],
            "searching": true,
            "paging":    true,
            "ordering":  true,
            "order":     [],
            "columnDefs": [
                { "orderable": false, "targets": [3, 4] }
            ],
            "info":      true,
            "bPaginate": true,
            "pageLength": 25,
                   

        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Poslední",
            "sNext":     "Další",
            "sPrevious": "Předešlá"
        },
        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                 }
        }
       });



$('#TabulkaHodnoceni').DataTable( {
            // HairSoft V86: vlastní čitelný PDF export všech odpovědí.
           
            "responsive": false,
            dom: 'Bfrtip',
                              extend: 'collection',
                              orientation: 'LETTER',
                              pageSize: 'A4',
                      
                    "buttons": [
                        {
                            extend: 'collection',
                            text: ' <i class="fa fa-save"></i><b> Export</b>',
                            //className: 'fa fa-save',
                            
                            buttons: [
                                           {
                                              text: '<i class="fa fa-copy"></i><b> Copy</b>',
                                              extend: 'copy',
                                              //className: 'fa fa-copy'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> CSV</b>',
                                              extend: 'csv',
                                              charset: 'UTF-16LE',
                                              fieldSeparator: '\t',
                                              bom: true,
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> XLS</b>',
                                              extend: 'excelHtml5',
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-pdf-o"></i><b> PDF</b>',
                                              extend: 'pdfHtml5',
                                              orientation: 'landscape',
                                              pageSize: 'A4',
                                              title: null,
                                              filename: function () {
                                                  return window.hsRatingsPdfFilename ? window.hsRatingsPdfFilename() : 'HairSoft_Hodnoceni';
                                              },
                                              exportOptions: {
                                                  columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                                                  format: {
                                                      body: function (data, row, column, node) {
                                                          return window.hsRatingsPdfCell ? window.hsRatingsPdfCell(data, column, node) : data;
                                                      }
                                                  }
                                              },
                                              customize: function (documentDefinition) {
                                                  if (window.hsCustomizeRatingsPdf) {
                                                      window.hsCustomizeRatingsPdf(documentDefinition);
                                                  }
                                              },
                                              //className: 'fa fa-file-pdf-o'
                                           },
                                           {
                                              text: '<i class="fa fa-print"></i><b> Tisk</b>',
                                              extend: 'print',
                                              //className: 'fa fa-print'
                                           }
                            ]
                        }
                    ],
            "searching": true,
            "paging":    true,
            "ordering":  true,
            "info":      true,
            "bPaginate": true,
            "pageLength": 25,
                   

        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Poslední",
            "sNext":     "Další",
            "sPrevious": "Předešlá"
        },
        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                 }
        }
       });


$('#TabulkaVouchery').DataTable( {
            /*
            "aoColumns": [
                            null,
                            { "sType": "date-cz" },
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            { "sType": "date-cz" },
                            { "sType": "date-cz" },
                            null,
                            null
            ],
*/
             //"order": [[ 0, "desc" ]],

            "responsive": true,
            dom: 'Bfrtip',
                              extend: 'collection',
                              orientation: 'LETTER',
                              pageSize: 'A4',
                      
                    "buttons": [
                        {
                            extend: 'collection',
                            text: ' <i class="fa fa-save"></i><b> Export</b> (pouze Web rozhraní)',
                            //className: 'fa fa-save',
                            
                            buttons: [
                                           {
                                              text: '<i class="fa fa-copy"></i><b> Copy</b>',
                                              extend: 'copy',
                                              //className: 'fa fa-copy'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> CSV</b>',
                                              extend: 'csv',
                                              charset: 'UTF-16LE',
                                              fieldSeparator: '\t',
                                              bom: true,
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> XLS</b>',
                                              extend: 'excelHtml5',
                                              customize: function(xlsx) { if (document.querySelector('.hs-voucher-page') && window.hsVoucherExcel) window.hsVoucherExcel(xlsx); },
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-pdf-o"></i><b> PDF</b>',
                                              extend: 'pdfHtml5',
                                              customize: function(doc) { if (document.querySelector('.hs-voucher-page') && window.hsVoucherPdf) window.hsVoucherPdf(doc, 'TabulkaVouchery'); },
                                              orientation: 'landscape',
                                              pageSize: 'A3',
                                              //className: 'fa fa-file-pdf-o'
                                           },
                                           {
                                              text: '<i class="fa fa-print"></i><b> Tisk</b>',
                                              extend: 'print',
                                              //className: 'fa fa-print'
                                           }
                            ]
                        }
                    ],
            "searching": true,
            "paging":    true,
            "ordering":  true,
            "info":      true,
            "bPaginate": !!document.querySelector(".hs-voucher-page, .hs-sms-page"),
            //"responsive": true,


           

        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Poslední",
            "sNext":     "Další",
            "sPrevious": "Předešlá"
        },
        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                 }
        }
       });


        $('#example').DataTable( {
            /*
            "aoColumns": [
                            null,
                            { "sType": "date-cz" },
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            { "sType": "date-cz" },
                            { "sType": "date-cz" },
                            null,
                            null
            ],
*/
             //"order": [[ 0, "desc" ]],

            "responsive": true,
            dom: 'Bfrtip',
                              extend: 'collection',
                              orientation: 'LETTER',
                              pageSize: 'A4',
                      
                    "buttons": [
                        {
                            extend: 'collection',
                            text: ' <i class="fa fa-save"></i><b> Export</b> (pouze Web rozhraní)',
                            //className: 'fa fa-save',
                            
                            buttons: [
                                           {
                                              text: '<i class="fa fa-copy"></i><b> Copy</b>',
                                              extend: 'copy',
                                              //className: 'fa fa-copy'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> CSV</b>',
                                              extend: 'csv',
                                              charset: 'UTF-16LE',
                                              fieldSeparator: '\t',
                                              bom: true,
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> XLS</b>',
                                              extend: 'excelHtml5',
                                              customize: function(xlsx) { if (document.querySelector('.hs-voucher-page') && window.hsVoucherExcel) window.hsVoucherExcel(xlsx); },
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-pdf-o"></i><b> PDF</b>',
                                              extend: 'pdfHtml5',
                                              customize: function(doc) {
                                                if (document.querySelector('.hs-sms-page') && window.hsSmsOverviewPdf) { window.hsSmsOverviewPdf(doc, 'example'); return; }
                                                if (document.querySelector('.hs-voucher-page') && window.hsVoucherPdf) window.hsVoucherPdf(doc, 'example');
                                              },
                                              orientation: 'landscape',
                                              pageSize: 'A4',
                                              //className: 'fa fa-file-pdf-o'
                                           },
                                           {
                                              text: '<i class="fa fa-print"></i><b> Tisk</b>',
                                              extend: 'print',
                                              //className: 'fa fa-print'
                                           }
                            ]
                        }
                    ],
            "searching": true,
            "paging":    true,
            "ordering":  true,
            "info":      true,
            "bPaginate": !!document.querySelector(".hs-voucher-page, .hs-sms-page"),
            //"responsive": true,


           

        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Poslední",
            "sNext":     "Další",
            "sPrevious": "Předešlá"
        },
        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                 }
        }
       });

        $('#example').parent().addClass('table-responsive');

        $('#example2').DataTable( {
            //"responsive": true,
            "searching": false,
            "paging":    false,
            "ordering":  true,
            "info":      true,
            "bPaginate": false,

        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Poslední",
            "sNext":     "Další",
            "sPrevious": "Předešlá"
        },
        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                 }
        }
       });


        $('#VoucherSumar').DataTable( {
            "order": [[ 0, "desc" ]],
            //"responsive": true,
            "searching": false,
            "paging":    false,
            "ordering":  false,
            "info":      false,
            "bPaginate": false,

        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Poslední",
            "sNext":     "Další",
            "sPrevious": "Předešlá"
        },
        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                 }
        }
       });


        $('#VoucherSumarDeposit').DataTable( {
            "order": [[ 0, "desc" ]],
            //"responsive": true,
            "searching": false,
            "paging":    false,
            "ordering":  false,
            "info":      false,
            "bPaginate": false,

        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Poslední",
            "sNext":     "Další",
            "sPrevious": "Předešlá"
        },
        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                 }
        }
       });


        $('#dochazka_tabulka').DataTable( {
            dom: 'Brt',
            buttons: [{
                extend: 'collection',
                text: '<i class="fa fa-download"></i><b> Export</b>',
                buttons: [
                    { text:'<i class="fa fa-copy"></i><b> Copy</b>', extend:'copy', footer:true },
                    { text:'<i class="fa fa-file-text-o"></i><b> CSV</b>', extend:'csv', charset:'UTF-16LE', fieldSeparator:'\t', bom:true, footer:true },
                    { text:'<i class="fa fa-file-excel-o"></i><b> XLS</b>', extend:'excelHtml5', footer:true },
                    { text:'<i class="fa fa-file-pdf-o"></i><b> PDF</b>', extend:'pdfHtml5', filename:'HairSoft_Dochazka', orientation:'landscape', pageSize:'A4', footer:true, title:null, customize:function(doc){ if(typeof window.hsAttendancePdf==='function') window.hsAttendancePdf(doc); } },
                    { text:'<i class="fa fa-print"></i><b> Tisk</b>', extend:'print', footer:true }
                ]
            }],
            searching: false,
            paging: false,
            ordering: false,
            info: false,
            autoWidth: false,
            language: {
                zeroRecords:'Nic nenalezeno',
                infoEmpty:''
            }
        });
        

        $('#sklad1').DataTable( {
            dom: 'Bfrtip',
                              extend: 'collection',
                              orientation: 'LETTER',
                              pageSize: 'A4',
                      
                    "buttons": [
                        {
                            extend: 'collection',
                            text: ' <i class="fa fa-save"></i><b> Export</b> (pouze Web rozhraní)',
                            //className: 'fa fa-save',
                            
                            buttons: [
                                           {
                                              text: '<i class="fa fa-copy"></i><b> Copy</b>',
                                              extend: 'copy',
                                              //className: 'fa fa-copy'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> CSV</b>',
                                              extend: 'csv',
                                              charset: 'UTF-16LE',
                                              fieldSeparator: '\t',
                                              bom: true,
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> XLS</b>',
                                              extend: 'excelHtml5',
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-pdf-o"></i><b> PDF</b>',
                                              extend: 'pdfHtml5',
                                              filename: 'HairSoft_sklad1',
                                              customize: function(doc){window.hsStockPdf(doc,'sklad1');},
                                              orientation: 'landscape',
                                              pageSize: 'A4',
                                              //className: 'fa fa-file-pdf-o'
                                           },
                                           {
                                              text: '<i class="fa fa-print"></i><b> Tisk</b>',
                                              extend: 'print',
                                              //className: 'fa fa-print'
                                           }
                            ]
                        }
                    ],

            "searching": true,
            "paging":    true,
            "ordering":  true,
            "info":      true,
            "bPaginate":false,
        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Poslední",
            "sNext":     "Další",
            "sPrevious": "Předešlá"
        },
        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                 }
        }
       });

        $('#sklad2').DataTable( {
            dom: 'Bfrtip',
                              extend: 'collection',
                              orientation: 'LETTER',
                              pageSize: 'A4',
                      
                    "buttons": [
                        {
                            extend: 'collection',
                            text: ' <i class="fa fa-save"></i><b> Export</b> (pouze Web rozhraní)',
                            //className: 'fa fa-save',
                            
                            buttons: [
                                           {
                                              text: '<i class="fa fa-copy"></i><b> Copy</b>',
                                              extend: 'copy',
                                              //className: 'fa fa-copy'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> CSV</b>',
                                              extend: 'csv',
                                              charset: 'UTF-16LE',
                                              fieldSeparator: '\t',
                                              bom: true,
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-excel-o"></i><b> XLS</b>',
                                              extend: 'excelHtml5',
                                              //className: 'fa fa-file-excel-o'
                                           },
                                           {
                                              text: '<i class="fa fa-file-pdf-o"></i><b> PDF</b>',
                                              extend: 'pdfHtml5',
                                              filename: 'HairSoft_sklad2',
                                              customize: function(doc){window.hsStockPdf(doc,'sklad2');},
                                              orientation: 'landscape',
                                              pageSize: 'A4',
                                              //className: 'fa fa-file-pdf-o'
                                           },
                                           {
                                              text: '<i class="fa fa-print"></i><b> Tisk</b>',
                                              extend: 'print',
                                              //className: 'fa fa-print'
                                           }
                            ]
                        }
                    ],
            "searching": true,
            "paging":    true,
            "ordering":  true,
            "info":      true,
        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Další",
            "sNext":     "Další",
            "sPrevious": "Předešlá"
        },
        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                 }
        }
       });
        
        





var table = $('#prichozi_hovory_tabulka').DataTable( {
          
          "bProcessing": false,
          "bServerSide": true,
          "order": [[ 0, "desc" ]],

          "ajax":{
                  url :"strana/ssf/ssf_PrichoziHovory.php", // json datasource
                  type: "get"  // type of method  , by default would be get
                },

/*
            rowCallback: function(row, data, index){
               if(data[8]== "Přijato"){
                $(row).find('td:eq(8)').css('background-color', '#ffc000');
                $(row).find('td:eq(8)').css('color', '#ffffff');
               }
              
               if(data[8] == 'Potvrzeno'){
                $(row).find('td:eq(8)').css('background-color', '#d7e4bc');
                $(row).find('td:eq(8)').css('color', '#656666');
               }

               if(data[8] == 'Zrušeno'){
                $(row).find('td:eq(8)').css('background-color', '#ff0000');
                $(row).find('td:eq(8)').css('color', '#ffffff');
               }

               if(data[8] == 'Realizováno'){
                $(row).find('td:eq(8)').css('background-color', '#00b050');
                $(row).find('td:eq(8)').css('color', '#ffffff');
               }
             },

   */

          //stateSave: true,
          dom: 'Bfrtip',
             "buttons": [
              
                      
                      
                      
              {
                  //extend: 'pdfHtml5',
                  extend: 'collection',
                  orientation: 'landscape',
                  pageSize: 'LEGAL',
                  text: 'Export',
                  buttons: [
                   
                  //vlastni formatovani http://jsfiddle.net/andrew_safwat/cn1n060L/
                  // {
                  //     extend: 'pdfHtml5',
                  //     orientation: 'landscape',
                  //      pageSize: 'LEGAL',
                  //      exportOptions: {
                  //        columns: [ 0, 1, 5 ]
                  //      }
                  //  },
                      { extend: 'copy', text: 'Kopírovat' },
                      { extend: 'excel', text: 'Excel', customize: function(xlsx){ if (window.hsSmsListExcel) window.hsSmsListExcel(xlsx); } },
                      { extend: 'csv', text: 'CSV' },
                      { extend: 'pdfHtml5', text: 'PDF', orientation: 'portrait', pageSize: 'A4', customize: function(doc){ if (window.hsSmsListPdf) window.hsSmsListPdf(doc, 'prichozi_hovory_tabulka', 'Příchozí hovory'); } },
                      { extend: 'print', text: 'Tisk' }
                  ]

                     
              }
        ],
            "scrollX": false,
            "searching": true,
            "paging":    true,
            "aLengthMenu": [[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,-1], [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,'-1']],
            "iDisplayLength" : 10, //<?php echo $nastaveni_radku; ?>,
            "responsive": false,
            "ordering":  true,
            "info":      true,
       
        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Poslední",
            "sNext":     "Další",
            "sPrevious": "Předešlá",
          
        },

               

        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                }
        },

                "columns": [{}, {}, {}],

              "columnDefs": [
                { "orderable": false, "targets": [] },
                { className: "text-right", "targets": [] },
                { className: "text-left", "targets": [2] },
                { className: "text-center", "targets": [0,1] },


              ]
              });

var table = $('#prichozi_SMS_tabulka').DataTable( {
          
          "bProcessing": false,
          "bServerSide": true,
          "order": [[ 0, "desc" ]],

          "ajax":{
                  url :"strana/ssf/ssf_PrichoziSMS.php", // json datasource
                  type: "get"  // type of method  , by default would be get
                },

/*
            rowCallback: function(row, data, index){
               if(data[8]== "Přijato"){
                $(row).find('td:eq(8)').css('background-color', '#ffc000');
                $(row).find('td:eq(8)').css('color', '#ffffff');
               }
              
               if(data[8] == 'Potvrzeno'){
                $(row).find('td:eq(8)').css('background-color', '#d7e4bc');
                $(row).find('td:eq(8)').css('color', '#656666');
               }

               if(data[8] == 'Zrušeno'){
                $(row).find('td:eq(8)').css('background-color', '#ff0000');
                $(row).find('td:eq(8)').css('color', '#ffffff');
               }

               if(data[8] == 'Realizováno'){
                $(row).find('td:eq(8)').css('background-color', '#00b050');
                $(row).find('td:eq(8)').css('color', '#ffffff');
               }
             },

   */

          //stateSave: true,
          dom: 'Bfrtip',
             "buttons": [
              
                      
                      
                      
              {
                  //extend: 'pdfHtml5',
                  extend: 'collection',
                  orientation: 'landscape',
                  pageSize: 'LEGAL',
                  text: 'Export',
                  buttons: [
                   
                  //vlastni formatovani http://jsfiddle.net/andrew_safwat/cn1n060L/
                  // {
                  //     extend: 'pdfHtml5',
                  //     orientation: 'landscape',
                  //      pageSize: 'LEGAL',
                  //      exportOptions: {
                  //        columns: [ 0, 1, 5 ]
                  //      }
                  //  },
                      { extend: 'copy', text: 'Kopírovat' },
                      { extend: 'excel', text: 'Excel', customize: function(xlsx){ if (window.hsSmsListExcel) window.hsSmsListExcel(xlsx); } },
                      { extend: 'csv', text: 'CSV' },
                      { extend: 'pdfHtml5', text: 'PDF', orientation: 'portrait', pageSize: 'A4', customize: function(doc){ if (window.hsSmsListPdf) window.hsSmsListPdf(doc, 'prichozi_SMS_tabulka', 'Příchozí SMS'); } },
                      { extend: 'print', text: 'Tisk' }
                  ]

                     
              }
        ],
            "scrollX": false,
            "searching": true,
            "paging":    true,
            "aLengthMenu": [[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,-1], [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,'-1']],
            "iDisplayLength" : 10, //<?php echo $nastaveni_radku; ?>,
            "responsive": false,
            "ordering":  true,
            "info":      true,
       
        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Poslední",
            "sNext":     "Další",
            "sPrevious": "Předešlá",
          
        },

               

        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                }
        },

                "columns": [{}, {}, {}, {}, {}],

              "columnDefs": [
                { "orderable": false, "targets": [] },
                { className: "text-right", "targets": [] },
                { className: "text-left", "targets": [2,3] },
                { className: "text-center", "targets": [0,1,4] },


              ]
              });




/*// Call datatables, and return the API to the variable for use in our code
// Binds datatables to all elements with a class of datatable
var dtable = $("#zakaznici_tabulka").dataTable().api();

// Grab the datatables input box and alter how it is bound to events
$("#zakaznici_tabulka input")
    .unbind() // Unbind previous default bindings
    .bind("input", function(e) { // Bind our desired behavior
        // If the length is 3 or more characters, or the user pressed ENTER, search
        if(this.value.length >= 3 || e.keyCode == 13) {
            // Call the API search function
            dtable.search(this.value).draw();
        }
        // Ensure we clear the search if they backspace far enough
        if(this.value == "") {
            dtable.search("").draw();
        }
        return;
    });   

*/

/*$(function(){
  var myTable=$('#zakaznici_tabulka').dataTable();

   $('#zakaznici_tabulka input')
                .unbind('keypress keyup')
                .bind('keypress keyup', function(e){
                  if ($(this).val().length < 3 && e.keyCode != 13) return;
                  myTable.fnFilter($(this).val());
                });

});
*/




(function () {
  var headerRow = document.querySelector("#zakaznici_tabulka thead tr");
  var cells;
  var source;
  var cell;
  var i;
  if (!headerRow) return;
  cells = headerRow.children;
  for (i = 0; i < cells.length; i += 1) {
    if (String(cells[i].textContent || "").replace(/\s+/g, " ").trim() === "Programy") return;
  }
  if (cells.length < 10) return;
  source = cells[9];
  cell = document.createElement(source && source.tagName ? source.tagName : "th");
  cell.setAttribute("data-hs-programs-column", "1");
  cell.innerHTML = "<b>Programy</b>";
  headerRow.insertBefore(cell, source || null);
}());

function hsCustomerExportHeader(data, columnIdx, node) {
  if (columnIdx === 9 && typeof window.hsCustomersProgramExportHeader === 'function') {
    return window.hsCustomersProgramExportHeader();
  }
  var holder = document.createElement('div');
  holder.innerHTML = String(data || '');
  return String(holder.textContent || holder.innerText || '').replace(/\s+/g, ' ').trim();
}

function hsCustomerExportColumns(columnIdx, data, node) {
  if (columnIdx === 9 && typeof window.hsCustomersProgramColumnVisible === 'function') {
    return window.hsCustomersProgramColumnVisible();
  }
  return true;
}

function hsCustomerExportOptions() {
  return {
    columns: hsCustomerExportColumns,
    format: { header: hsCustomerExportHeader }
  };
}

var table = $('#zakaznici_tabulka').DataTable( {
          "search": {
            "search": '<?php echo $_SESSION["ZakazniciRazeniFiltrHledani"]; ?>'
          },      
          "bProcessing": false,
          "bServerSide": true,
          "order": [[ 0, "ASC" ]],// 0 je dulezita protoze si v ssf 0 prehodim na def co potrebuji

          "ajax":{
                  url :"strana/ssf/ssf_zakaznici.php", // json datasource
                  type: "get"  // type of method  , by default would be get
                },

              rowCallback: function(row, data, index){
               if(data[11]== "1"){
                $(row).find('td:eq(1)').css('background-color', '##ff5d5d');
                $(row).find('td:eq(2)').css('background-color', '##ff5d5d');
                
                $(row).find('td:eq(1)').css('color', '#ffffff');
                $(row).find('td:eq(2)').css('color', '#ffffff');
                
               }
            },

          //stateSave: true,
          dom: 'Bfrtip',
             "buttons": [
              
                      
                      
                      
              {
                  //extend: 'pdfHtml5',
                  extend: 'collection',
                  orientation: 'landscape',
                  pageSize: 'LEGAL',
                  text: 'Export',
                  buttons: [
                   
                  //vlastni formatovani http://jsfiddle.net/andrew_safwat/cn1n060L/
                  // {
                  //     extend: 'pdfHtml5',
                  //     orientation: 'landscape',
                  //      pageSize: 'LEGAL',
                  //      exportOptions: {
                  //        columns: [ 0, 1, 5 ]
                  //      }
                  //  },
                      { extend: 'copy', exportOptions: hsCustomerExportOptions() },
                      { extend: 'excel', exportOptions: hsCustomerExportOptions() },
                      { extend: 'csv', exportOptions: hsCustomerExportOptions() },
                      { extend: 'pdf', exportOptions: hsCustomerExportOptions() },
                      { extend: 'print', exportOptions: hsCustomerExportOptions() }
                  ]

                     
              }
        ],
            "scrollX": true,
            "searching": true,
            "paging":    true,
            "lengthMenu": [ [5, 10, 25, 50, -1], [5, 10, 25, 50, "All"] ],
            "iDisplayLength" : <?php echo $_SESSION["strankovani_zakaznici_tabulka"]; ?>, 
            "responsive": true,
            "ordering":  true,
            "info":      true,

       
        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Poslední",
            "sNext":     "Další",
            "sPrevious": "Předešlá",
          
        },

               

        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                }
        },

                /*
                "render": function(data, type, full, meta) {
                           return '<a style="color:#E25D5D" href="index.php?strana=KartaOsoby&osoba_guid=555' + data + '"><img  widht="30" height="30" class="img-circle profile-image" src="../img/obsluhy/sys/default_user.png" alt=":)"></a>';
                */
                "columns": [
                          {
                  "render": function(data, type, full, meta) {
                               
                              
                                
                             /*
                                var Vysledek ;
                                $.ajax({
                                    url: 'strana/ssf/ssf_zakazniciObliceje.php',
                                    type: 'POST',
                                    data: 'osoba_guid=' + data+ '&Akce=ZjistiJmenoProfilovkyPomociGuidOsoby'  ,
                                    cache: false,
                                    async: false,
                                    timeout: 30000,
                                          
                                    success: function(html) {
                                        Vysledek = html;
                                        
                                        
                                    }

                                    
                                });                               
                                */
                                
                                //console.log(Vysledek); // Inspect this in your console
                              /*
                                var ReturnData = null;
                                
                                if (Vysledek=="NIC") {
                                    ReturnData = '<a title="Detail zákazníka" href="index.php?strana=KartaOsoby&osoba_guid=' + data + '"><img widht="60" height="60"  class="img-circle profile-image" src="../img/mface.png"></a>';
                                }else{
                                    ReturnData = '<a title="Detail zákazníka" href="index.php?strana=KartaOsoby&osoba_guid=' + data + '"><img   class="img-circle_zakaznici" src="https://klient.hairsoft.cz/str/strana/galerie/m'+Vysledek+'"></a>';
                                }

                                */

                                var ReturnData = null;
                                var PoleFotka = data.split("**+-+**");                                                   
                                

                                var GuidOsoby = PoleFotka[0];
                                var GuidObrazku = PoleFotka[1];

                                //alert(GuidOsoby);


                                if (GuidObrazku=="NIC") {
                                    ReturnData = '<a title="Detail zákazníka" href="index.php?strana=KartaOsoby&osoba_guid=' + GuidOsoby + '"><img widht="60" height="60"  class="img-circle profile-image" src="../img/mface.png"></a>';
                                }else{
                                    ReturnData = '<a title="Detail zákazníka" href="index.php?strana=KartaOsoby&osoba_guid=' + GuidOsoby + '"><img   class="img-circle_zakaznici" src="https://klient.hairsoft.cz/str/strana/galerie/m'+GuidObrazku+'"></a>';
                                }
                                return ReturnData;    

                                

                                
                  }
                }, {}, {}, {
                  "render": function(data, type, full, meta) {
                    
                            <?php 
                                //funkce na anomymizaci
                                if ($_SESSION["k_poduzivatele_skryt_citliva_data"]=="1") {
                                        ?>
                                            return data;
                                        <?php 
                                }else{
                                        ?>
                                            return '<a  href="mailto:' + data + '">' + data + '</a>';
                                        <?php 
                                }
                            ?>

                  }
                }, {
                  "render": function(data, type, full, meta) {
                             <?php 
                                if ($_SESSION["k_poduzivatele_skryt_citliva_data"]=="1") {
                                        ?>
                                            return data;
                                        <?php 
                                }else{
                                        ?>
                                            return '<a style="color:#565656" href="tel:' + data + '">' + data + '</a>';
                                        <?php 
                                }
                            ?>
                   
                  }
                }, {}, {}, {}, {},
                            {
                                orderable: false,
                                searchable: false,
                            },
                            {},
                            {
                                visible: false,
                                searchable: false,
                            },
                            {
                                orderable: false,
                                searchable: false,
                            }
                ],

              "columnDefs": [
                { "orderable": false, "targets": [0,9,12] },
                { className: "text-right", "targets": [] },
                { className: "text-left", "targets": [] },
                { className: "text-center", "targets": [0,1,2,3,4,5,6,7,8,9,10] },


              ]
              });

//$('#zakaznici_tabulka_filter label input').attr('id', 'zakaznici_search');
//$('#zakaznici_tabulka_filter label input').attr("value", '<?php echo $_SESSION["ZakazniciRazeniFiltrHledani"]; ?>');




var table = $('#odchozi_sms_tabulka').DataTable( {
          
          "bProcessing": false,
          "bServerSide": true,
          "order": [[ 0, "desc" ]],

          "ajax":{
                  url :"strana/ssf/ssf_OdchozíSMS.php", // json datasource
                  type: "get"  // type of method  , by default would be get
                },

/*
            rowCallback: function(row, data, index){
               if(data[8]== "Přijato"){
                $(row).find('td:eq(8)').css('background-color', '#ffc000');
                $(row).find('td:eq(8)').css('color', '#ffffff');
               }
              
               if(data[8] == 'Potvrzeno'){
                $(row).find('td:eq(8)').css('background-color', '#d7e4bc');
                $(row).find('td:eq(8)').css('color', '#656666');
               }

               if(data[8] == 'Zrušeno'){
                $(row).find('td:eq(8)').css('background-color', '#ff0000');
                $(row).find('td:eq(8)').css('color', '#ffffff');
               }

               if(data[8] == 'Realizováno'){
                $(row).find('td:eq(8)').css('background-color', '#00b050');
                $(row).find('td:eq(8)').css('color', '#ffffff');
               }
             },

   */

          //stateSave: true,
          dom: 'Bfrtip',
             "buttons": [
              
                      
                      
                      
              {
                  //extend: 'pdfHtml5',
                  extend: 'collection',
                  orientation: 'landscape',
                  pageSize: 'LEGAL',
                  text: 'Export',
                  buttons: [
                   
                  //vlastni formatovani http://jsfiddle.net/andrew_safwat/cn1n060L/
                  // {
                  //     extend: 'pdfHtml5',
                  //     orientation: 'landscape',
                  //      pageSize: 'LEGAL',
                  //      exportOptions: {
                  //        columns: [ 0, 1, 5 ]
                  //      }
                  //  },
                      { extend: 'copy', text: 'Kopírovat' },
                      { extend: 'excel', text: 'Excel', customize: function(xlsx){ if (window.hsSmsListExcel) window.hsSmsListExcel(xlsx); } },
                      { extend: 'csv', text: 'CSV' },
                      { extend: 'pdfHtml5', text: 'PDF', orientation: 'landscape', pageSize: 'A4', customize: function(doc){ if (window.hsSmsListPdf) window.hsSmsListPdf(doc, 'odchozi_sms_tabulka', 'Odchozí SMS'); } },
                      { extend: 'print', text: 'Tisk' }
                  ]

                     
              }
        ],
            "scrollX": false,
            "searching": true,
            "paging":    true,
            "aLengthMenu": [[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,-1], [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,'-1']],
            "iDisplayLength" : 10, //<?php echo $nastaveni_radku; ?>,
            "responsive": false,
            "ordering":  true,
            "info":      true,
       
        "language": {
            "lengthMenu": "_MENU_ záznamů na stranu",
            "zeroRecords": "Nic nenalezeno",
            "info": "Strana _PAGE_ z _PAGES_",
            "infoEmpty": "",
            "infoFiltered": "(Celkem _MAX_ záznamů)",
            "sSearch":        "Hledat: ",
            "oPaginate": {
            "sFirst":    "První",
            "sLast":     "Poslední",
            "sNext":     "Další",
            "sPrevious": "Předešlá",
          
        },

               

        "oAria": {
            "sSortAscending":  ": A-Z",
            "sSortDescending": ": Z-A"
                }
        },

                "columns": [{}, {}, {}, {}, {}, {}, {}, {}, {}, {}, {}],

              "columnDefs": [
                { "orderable": false, "targets": [] },
                { className: "text-right", "targets": [] },
                { className: "text-left", "targets": [2,3,5] },
                { className: "text-center", "targets": [0,1,4,6,7,8,9,10] },


              ]
              });
       
        
    });

$('.htmledit').richText({

                  // text formatting
                  bold: true,
                  italic: true,
                  underline: true,

                  // text alignment
                  leftAlign: true,
                  centerAlign: true,
                  rightAlign: true,
                  justify: false,

                  // lists
                  ol: true,
                  ul: true,

                  // title
                  heading: false,
                  // fonts
                  fonts: true,
                  fontList: ["Arial",
                    "Comic Sans MS",
                    "Georgia",
                    "Lucida Console",
                    "Tahoma",
                    "Verdana"
                  ],
                  fontColor: true,
                  fontSize: true,
                  // uploads
                  imageUpload: false,
                  fileUpload: false,
                  videoEmbed: false,
                  // media
                  
                  // link
                  urls: false,

                  // tables
                  table: false,

                  // code
                  removeStyles: false,
                  code: false,

                  // privacy
                  youtubeCookies: false,

                  // preview
                  preview: false,

                  // placeholder
                  placeholder: '',

                  // dev settings
                  useSingleQuotes: false,
                  height: 0,
                  heightPercentage: 0,
                  id: "",
                  class: "",
                  useParagraph: false,
                  maxlength: 0,
                  useTabForNext: false
                  
    });

/*
          jQuery.extend( jQuery.fn.dataTableExt.oSort, {
          

          "date-cz-pre": function ( a ) {
            
             //2018</div>03<div style="width: 90%;" align="right">13
             //var str = "Visit Microsoft!";
             //var res = str.replace("Microsoft", "W3Schools");

             var ukDatea = a.split('.');

              alert(ukDatea[2] + ukDatea[1] + ukDatea[0]);
              return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
          },

          "date-cz-asc": function ( a, b ) {
               alert("Hello! I am an alert box!!0");
              return ((a < b) ? -1 : ((a > b) ? 1 : 0));
          },

          "date-cz-desc": function ( a, b ) {
               alert("Hello! I am an alert box!!1");
              return ((a < b) ? 1 : ((a > b) ? -1 : 0));
          }
          } );

*/

    </script>
   
  
  <script>


	$(function() {
      


      $('#datepicker1').datepicker(
      {
        duration: '',
        changeMonth: false,
        changeYear: false,
        yearRange: '2014:2034',
        showTime: false,
        time24h: true,
        
          onSelect : function(){
           $('#TrzbyDatum').submit();   
          }
        
      });

       $("#idSkladuCombo").change(function () {
        //alert("Hu");
        $('#idSkladuForm').submit();   
       });

     

      $('#datepicker_voucherod').datepicker(
      {
        duration: '',
        changeMonth: false,
        changeYear: false,
        yearRange: '2014:2034',
        showTime: false,
        time24h: true,
        
          //onSelect : function(){
          // $('#TrzbyDatum').submit();   
          //}
        
      });

      $('#datepicker_voucherdo').datepicker(
      {
        duration: '',
        changeMonth: false,
        changeYear: false,
        yearRange: '2014:2034',
        showTime: false,
        time24h: true,
        
          //onSelect : function(){
          // $('#TrzbyDatum').submit();   
          //}
        
      });

    $.datepicker.regional['cs'] = {
        closeText: 'Zavřít',
        prevText: '&#x3c;Dříve',
        nextText: 'Později&#x3e;',
        currentText: 'Nyní',
        monthNames: ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec'],
        monthNamesShort: ['led', 'úno', 'bře', 'dub', 'kvě', 'čer', 'čvc', 'srp', 'zář', 'říj', 'lis', 'pro'],
        dayNames: ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'],
        dayNamesShort: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
        dayNamesMin: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
        weekHeader: 'Týd',
        dateFormat: 'dd.mm.yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
         
    $.datepicker.setDefaults($.datepicker.regional['cs']);
      
  });


  $('#table_prehledUzivatelu').dataTable( {
      "columns": [{ "width": "10%" },    null,    null,    null,null  ],
   
            "scrollX": false,
            "searching": true,
            "paging":    false,
            "aLengthMenu": [[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,-1], 
                            [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,'-1']],
            "iDisplayLength" : 100, //<?php echo $nastaveni_radku; ?>,
            "responsive": true,
            "ordering":  true,
            "info":      false,

            "language": {
                          "lengthMenu": "_MENU_ záznamů na stranu",
                          "zeroRecords": "Nic nenalezeno",
                          "info": "Strana _PAGE_ z _PAGES_",
                          "infoEmpty": "",
                          "infoFiltered": "(Celkem _MAX_ záznamů)",
                          "sSearch":        "Hledat: ",
                          "oPaginate": {
                                        "sFirst":    "První",
                                        "sLast":     "Poslední",
                                        "sNext":     "Další",
                                        "sPrevious": "Předešlá",
                                       },

                                                "oAria": {
                                                         "sSortAscending":  ": A-Z",
                                                         "sSortDescending": ": Z-A"
                                                         }
            },
       }
  );

  $('#table_prehledUzivateluObsluha').dataTable( {
      "columns": [{ "width": "70px" },    null,    null,    { "width": "110px" }  ],
      "columnDefs": [
          { className: 'text-left', targets: [1, 2] },
          { className: 'text-center', targets: [0, 3] },
        ],

   
            "scrollX": false,
            "searching": true,
            "paging":    true,
            "bLengthChange": false,
            "aLengthMenu": [[10,20,50,-1], [10,20,50,'-1']],
            "iDisplayLength" : 10,
            "responsive": true,
            "ordering":  true,
            "info":      true,

            "language": {
                          "lengthMenu": "_MENU_ záznamů na stranu",
                          "zeroRecords": "Nic nenalezeno",
                          "info": "Strana _PAGE_ z _PAGES_",
                          "infoEmpty": "",
                          "infoFiltered": "(Celkem _MAX_ záznamů)",
                          "sSearch":        "Hledat: ",
                          "oPaginate": {
                                        "sFirst":    "První",
                                        "sLast":     "Poslední",
                                        "sNext":     "Další",
                                        "sPrevious": "Předešlá",
                                       },

                                                "oAria": {
                                                         "sSortAscending":  ": A-Z",
                                                         "sSortDescending": ": Z-A"
                                                         }
            },
       }
  );

  $('#table_prehledUzivateluObsluhaArchiv').dataTable( {
      "columns": [{ "width": "70px" },    null,    null,    { "width": "110px" }  ],
      "columnDefs": [
          { className: 'text-left', targets: [1, 2] },
          { className: 'text-center', targets: [0, 3] },
        ],

   
            "scrollX": false,
            "searching": true,
            "paging":    true,
            "bLengthChange": false,
            "aLengthMenu": [[10,20,50,-1], [10,20,50,'-1']],
            "iDisplayLength" : 10,
            "responsive": true,
            "ordering":  true,
            "info":      true,

            "language": {
                          "lengthMenu": "_MENU_ záznamů na stranu",
                          "zeroRecords": "Nic nenalezeno",
                          "info": "Strana _PAGE_ z _PAGES_",
                          "infoEmpty": "",
                          "infoFiltered": "(Celkem _MAX_ záznamů)",
                          "sSearch":        "Hledat: ",
                          "oPaginate": {
                                        "sFirst":    "První",
                                        "sLast":     "Poslední",
                                        "sNext":     "Další",
                                        "sPrevious": "Předešlá",
                                       },

                                                "oAria": {
                                                         "sSortAscending":  ": A-Z",
                                                         "sSortDescending": ": Z-A"
                                                         }
            },
       }
  );

    var archiv0Visible = false;
    
    $('#tlacitkoArchivace').click(function() {
        // Obnovování stránky
        //location.reload();

        $('[name="archiv0"]').css('display', archiv0Visible ? 'none' : 'block');
        $('[name="archiv1"]').css('display', archiv0Visible ? 'block' : 'none');
        archiv0Visible = !archiv0Visible;

         // Změna textu tlačítka
        var newText = archiv0Visible ? 'Zobrazit aktivní' : 'Zobrazit archivované';
        $(this).html('<i class="icon-layers"></i>&nbsp;&nbsp;' + newText);

        

    });
  

    document.getElementById("cameraFileInput").addEventListener("change", function () {
    document.getElementById("pictureFromCamera").setAttribute("src", window.URL.createObjectURL(this.files[0]));































    });


    

 
	</script>  

        <script type="text/javascript">
    
            

                //Zablokovat tlacitko zpět
                window.history.pushState(null, null, window.location.href);
                window.onpopstate = function(event) {
                window.history.pushState(null, null, window.location.href);
                };



               

                

                
            
        </script>

        
 

<script>window.hsPageRenderSeconds=<?= json_encode(round(max(0,microtime(true)-(isset($DURATION_start)?$DURATION_start:microtime(true))),3)) ?>;</script>
</body>

</html>



