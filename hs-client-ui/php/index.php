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
<html class="no-js hs-ui-preparing<?php if (isset($_GET['strana']) && is_string($_GET['strana']) && in_array($_GET['strana'], array('CelkoveTrzby', 'MesicniTrzby', 'TrzbyDleObsluhy', 'TrzbyOdPocatku'), true)) { echo ' hs-revenue-page'; } ?>" lang="cs">
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
    <link rel="stylesheet" href="/hs-client-ui/css/client-ui.css?v=232">
    <link rel="stylesheet" href="/hs-client-ui/css/company-switch.css?v=221">
    <link rel="stylesheet" href="/hs-client-ui/css/dashboard-charts.css?v=197">
    <link rel="stylesheet" href="/hs-client-ui/css/client-form.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/client-customers.css?v=235">
    <link rel="stylesheet" href="/hs-client-ui/css/client-customers-mobile.css?v=222">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-desktop.css?v=211">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-profile.css?v=223">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-timeline.css?v=210">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-sms-chat.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-gallery.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-programs.css?v=244">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-ratings.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-files.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/client-copy-test.css?v=205">
    <link rel="stylesheet" href="/hs-client-ui/css/client-export-menu.css?v=238">
    <link rel="stylesheet" href="/hs-client-ui/css/client-detail-export.css?v=245">
    <link rel="stylesheet" href="/hs-client-ui/css/client-confirm.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/reservations-embed.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/daily-revenue.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/monthly-revenue.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/i18n.css?v=196">
    <script>
      /* Bezpečnostní pojistka: obsah nezůstane skrytý ani při chybě dalšího skriptu. */
      window.addEventListener("load", function () {
        document.documentElement.classList.remove("hs-ui-preparing");
      });
    </script>
      
    <!-- C3 Chart-->
    <link rel="stylesheet" href="../plugins/c3Chart/css/c3.css">
    <link rel="stylesheet" href="../plugins/c3Chart/css/c3.min.css">
    <!--Page Leve JS -->
    <script src="../plugins/c3Chart/js/d3.v3.min.js"></script>
    <script src="../plugins/c3Chart/js/c3.js"></script>
    <script src="../plugins/c3Chart/js/c3-demo.js"></script>


        



    
    <!-- Feature detection -->
    <script src="../js/vendor/modernizr-2.6.2.min.js"></script>
    <script src="/hs-client-ui/js/i18n.js?v=245" defer></script>
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
    <script src="/hs-client-ui/js/menu.js?v=199" defer></script>
    <script src="/hs-client-ui/js/company-switch.js?v=196" defer></script>
    <script src="/hs-client-ui/js/customer-sync-hold.js?v=202" defer></script>
    <script src="/hs-client-ui/js/dashboard-charts.js?v=197" defer></script>
    <script src="/hs-client-ui/js/client-form.js?v=196" defer></script>
    <script src="/hs-client-ui/js/client-customers.js?v=237" defer></script>
    <script src="/hs-client-ui/js/client-customers-pdf.js?v=236" defer></script>
    <script src="/hs-client-ui/js/client-detail-desktop.js?v=200" defer></script>
    <script src="/hs-client-ui/js/client-detail-profile.js?v=223" defer></script>
    <script src="/hs-client-ui/js/client-detail-sms-chat.js?v=196" defer></script>
    <script src="/hs-client-ui/js/client-detail-gallery.js?v=196" defer></script>
    <script src="/hs-client-ui/js/client-detail-programs.js?v=244" defer></script>
    <script src="/hs-client-ui/js/client-detail-ratings.js?v=196" defer></script>
    <script src="/hs-client-ui/js/client-detail-files.js?v=196" defer></script>
    <script src="/hs-client-ui/js/client-detail-export.js?v=245" defer></script>
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

                        