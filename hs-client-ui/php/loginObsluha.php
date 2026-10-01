<?php
// Přímé otevření moderní vrstvy přesměruje na veřejný vstup.
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    header('Location: ../../str/loginObsluha.php');
    exit;
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



session_start();

session_cache_expire(432000);

session_cache_limiter(144000);

//nacitani casu stranky

$DURATION_start=microtime(true);

       

require_once '../cfg/nastaveni.php';

require_once '../fce/GeneratorHesel.php';

//require_once '../fce/PosliEmail.php';

require_once 'strana/_VytvoritTabulkyTrzebProNovyRok.php';









 // LOGIN

         if (!isset($_POST['loginProvozovny']) || is_array($_POST['loginProvozovny'])){$_POST['loginProvozovny']='';}

         $loginProvozovny  =  htmlspecialchars($_POST['loginProvozovny'], ENT_COMPAT);



         if (!isset($_POST['loginObsluhy']) || is_array($_POST['loginObsluhy'])){$_POST['loginObsluhy']='';}

         $loginObsluhy  =  htmlspecialchars($_POST['loginObsluhy'], ENT_COMPAT);





         if (!isset($_POST['password']) || is_array($_POST['password'])){$_POST['password']='';}

         // Původní PHP 7.4: $sha1_password = sha1(htmlspecialchars($_POST['password']));

         // MIGRACE PHP 7.4 -> 8.3: zachování původního výchozího ENT_COMPAT, aby se heslo hashovalo stejně.

         $sha1_password = sha1(htmlspecialchars($_POST['password'], ENT_COMPAT));





         if (!isset($_GET['app']) || is_array($_GET['app'])){$_GET['app']='';}

         $app  =  htmlspecialchars($_GET['app'], ENT_COMPAT);

        

         if (!isset($_SESSION["uzivatel_prihlasen"])){$_SESSION["uzivatel_prihlasen"]='';}





         if ($app=="") {
            $app="HairSoft";
         }elseif ($app=="ZooSoft") {
            $app="ZooSoft";
         }

         $loginLanguages = array(
            'cs' => array('code' => 'CS', 'label' => 'Čeština', 'flag' => '🇨🇿'),
            'en' => array('code' => 'EN', 'label' => 'English', 'flag' => '🇬🇧'),
            'de' => array('code' => 'DE', 'label' => 'Deutsch', 'flag' => '🇩🇪'),
            'sk' => array('code' => 'SK', 'label' => 'Slovenčina', 'flag' => '🇸🇰')
         );

         if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loginLanguage']) && !is_array($_POST['loginLanguage']) && array_key_exists($_POST['loginLanguage'], $loginLanguages)) {
            $_SESSION['languageCombo'] = $_POST['loginLanguage'];
         }

         // Výchozí text stránky zůstává česky. Překlad a jeho uložení řeší
         // společný klientský i18n modul bez odesílání přihlašovacího formuláře.
         $selectedLoginLanguage = 'cs';

         $loginTranslations = array(
            'cs' => array(
               'headline' => 'Přístup pro váš tým.',
               'subline' => 'Rychle, přehledně a bezpečně.',
               'access' => 'Pracovní přístup obsluhy',
               'title' => 'Přihlášení obsluhy',
               'description' => 'Zadejte údaje provozovny a svůj osobní přístup.',
               'companyLogin' => 'Login / Email provozovny',
               'companyPlaceholder' => 'Zadejte login nebo email provozovny',
               'staffLogin' => 'Login / Jméno obsluhy',
               'staffPlaceholder' => 'Zadejte login nebo jméno obsluhy',
               'password' => 'Heslo',
               'passwordPlaceholder' => 'Zadejte heslo',
               'signIn' => 'Přihlásit',
               'otherLogin' => 'Jiný typ přihlášení',
               'admin' => 'Přihlásit se jako administrátor',
               'changeLanguage' => 'Změnit jazyk'
            ),
            'en' => array(
               'headline' => 'Access for your team.',
               'subline' => 'Fast, clear and secure.',
               'access' => 'Staff access',
               'title' => 'Staff sign in',
               'description' => 'Enter the company details and your personal access.',
               'companyLogin' => 'Company login / Email',
               'companyPlaceholder' => 'Enter the company login or email',
               'staffLogin' => 'Staff login / Name',
               'staffPlaceholder' => 'Enter the staff login or name',
               'password' => 'Password',
               'passwordPlaceholder' => 'Enter your password',
               'signIn' => 'Sign in',
               'otherLogin' => 'Other sign-in option',
               'admin' => 'Sign in as administrator',
               'changeLanguage' => 'Change language'
            ),
            'de' => array(
               'headline' => 'Zugang für Ihr Team.',
               'subline' => 'Schnell, übersichtlich und sicher.',
               'access' => 'Mitarbeiterzugang',
               'title' => 'Mitarbeiter-Anmeldung',
               'description' => 'Geben Sie die Betriebsdaten und Ihren persönlichen Zugang ein.',
               'companyLogin' => 'Betriebslogin / E-Mail',
               'companyPlaceholder' => 'Betriebslogin oder E-Mail eingeben',
               'staffLogin' => 'Mitarbeiter-Login / Name',
               'staffPlaceholder' => 'Mitarbeiter-Login oder Namen eingeben',
               'password' => 'Passwort',
               'passwordPlaceholder' => 'Passwort eingeben',
               'signIn' => 'Anmelden',
               'otherLogin' => 'Andere Anmeldeoption',
               'admin' => 'Als Administrator anmelden',
               'changeLanguage' => 'Sprache ändern'
            ),
            'sk' => array(
               'headline' => 'Prístup pre váš tím.',
               'subline' => 'Rýchlo, prehľadne a bezpečne.',
               'access' => 'Pracovný prístup obsluhy',
               'title' => 'Prihlásenie obsluhy',
               'description' => 'Zadajte údaje prevádzky a svoj osobný prístup.',
               'companyLogin' => 'Login / Email prevádzky',
               'companyPlaceholder' => 'Zadajte login alebo email prevádzky',
               'staffLogin' => 'Login / Meno obsluhy',
               'staffPlaceholder' => 'Zadajte login alebo meno obsluhy',
               'password' => 'Heslo',
               'passwordPlaceholder' => 'Zadajte heslo',
               'signIn' => 'Prihlásiť',
               'otherLogin' => 'Iný typ prihlásenia',
               'admin' => 'Prihlásiť sa ako administrátor',
               'changeLanguage' => 'Zmeniť jazyk'
            )
         );

         $loginText = $loginTranslations[$selectedLoginLanguage];








?>



<!DOCTYPE html>

<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->

<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->

<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->

<!--[if gt IE 8]><!-->

<html class="no-js" lang="cs">
<!--<![endif]-->



<head>

    <meta charset="utf-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge"> 

    <title><?php echo $app;    ?> - Klient</title>

    <meta name="description" content="">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <link rel="icon" href="/hs-client-ui/img/favicon.ico?v=41" type="image/x-icon">
    <link rel="stylesheet" href="../plugins/bootstrap/css/bootstrap.min.css">

    <link rel="stylesheet" href="../css/font-awesome.min.css">

    <link rel="stylesheet" href="../css/simple-line-icons.css">

    <link rel="stylesheet" href="../css/animate.css">

    <link rel="stylesheet" href="../css/main.css">

    



    <link rel="stylesheet" href="../css/_tmk.css">
    <link rel="stylesheet" href="/hs-client-ui/css/login.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/i18n.css?v=196">
    

    

    <script src="../js/vendor/modernizr-2.6.2.min.js"></script>
    <script src="/hs-client-ui/js/i18n.js?v=198" defer></script>
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->

    <!--[if lt IE 9]>

    <script src="../js/vendor/html5shiv.js"></script>

    <script src="../js/vendor/respond.min.js"></script>

    <![endif]-->

</head>

<body class="login-page" data-app="<?php echo $app; ?>">


<?php

         

         if ($_SESSION["uzivatel_prihlasen"] != "ano" and $loginObsluhy ==""){

            ?> 



                <section class="login-stage animated fadeInUp">
                    <div class="login-shell login-shell--staff" id="login-wrapper">
                        <aside class="login-visual">
                            <a href="https://klient.hairsoft.cz/str/loginObsluha.php?app=<?php echo $app; ?>" class="login-brand">
                                <span><?php echo $app; ?></span>
                                <small>Klient</small>
                            </a>
                            <div class="login-visual__content login-visual__content--simple">
                                <h1 class="login-visual__headline"><?php echo $loginText['headline']; ?></h1>
                                <p class="login-visual__subline"><?php echo $loginText['subline']; ?></p>
                            </div>
                            <div class="login-visual__footer">
                                <span class="login-security-icon"><i class="fa fa-users"></i></span>
                                <span><?php echo $loginText['access']; ?></span>
                            </div>
                        </aside>

                        <main class="login-card">
                            <div class="login-card__header">
                                <h2><?php echo $loginText['title']; ?></h2>
                                <p><?php echo $loginText['description']; ?></p>
                            </div>

                                      <?php
                                        if ($_SESSION["uzivatel_prihlasen"]=="ne") {
                                           ?>
                                                  <div id="login_blokace_text" class="login-alert login-alert--error">
                                                    <b>Účet je blokován</b><br>
                                                       <?php
                                                            echo $_SESSION["k_informace_blokace"];
                                                            $_SESSION["k_informace_blokace"]="";
                                                       ?>

                                                  </div> 

                                           <?php

                                        }

                                        

                                          

                                         if ($_SESSION["chybne_udaje"]!="") {
                                           ?>
                                                  <div id="login_blokace_text" class="login-alert login-alert--error">
                                                    <b>Informace</b><br>
                                                       <?php
                                                            echo $_SESSION["chybne_udaje"];
                                                            $_SESSION["chybne_udaje"] = "";
                                                       ?>

                                                  </div> 

                                           <?php

                                        }

                                        

                                            if(!isset($_COOKIE["loginProvozovny"])) {

                                                $loginProvozovny ="";

                                            } else {

                                                $loginProvozovny =$_COOKIE["loginProvozovny"];

                                            }







                                      ?>

                                      

                                        

                                        <form class="login-form" method="POST" action="https://klient.hairsoft.cz/str/loginObsluha.php">
                                            <div class="login-field-group">
                                                <label><?php echo $loginText['companyLogin']; ?></label>
                                                <div class="login-field">
                                                    <i class="fa fa-user"></i>
                                                    <input type="text" class="form-control" id="email" name="loginProvozovny" placeholder="<?php echo $loginText['companyPlaceholder']; ?>" value="<?php echo $loginProvozovny; ?>" autocomplete="username">
                                                </div>
                                            </div>

                                            <div class="login-field-group" id="loginObsluhy">
                                                <label><?php echo $loginText['staffLogin']; ?></label>
                                                <div class="login-field">
                                                    <i class="fa fa-user"></i>
                                                    <input type="text" class="form-control" id="email" name="loginObsluhy" placeholder="<?php echo $loginText['staffPlaceholder']; ?>">
                                                </div>
                                            </div>

                                            <div class="login-field-group">
                                                <label for="password"><?php echo $loginText['password']; ?></label>
                                                <div class="login-field">
                                                    <i class="fa fa-lock"></i>
                                                    <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo $loginText['passwordPlaceholder']; ?>" autocomplete="current-password">
                                                </div>
                                            </div>
                                            <button type="submit" class="login-button login-button--staff-primary">
                                                <span><?php echo $loginText['signIn']; ?></span>
                                                <i class="fa fa-arrow-right"></i>
                                            </button>
                                        </form>

                                        <div class="login-divider"><span><?php echo $loginText['otherLogin']; ?></span></div>

                                        <form class="login-staff-switch" method="POST" action="https://klient.hairsoft.cz/str/login.php?app=<?php echo $app;?>">
                                            <button type="submit" class="login-button login-button--admin">
                                                <i class="fa fa-user"></i>
                                                <span><?php echo $loginText['admin']; ?></span>
                                            </button>
                                        </form>
                        </main>
                    </div>
                </section>
                

                <?php

                            return;           

         }elseif ($_SESSION["uzivatel_prihlasen"] == "ano") {

                                  

                                  

                        if ($_SESSION["JePoduzivatel"] == "0") {

                           //Pridani + k prihlaseni

                                  $sql = "UPDATE k_uzivatele SET k_pocet_prihlaseni = k_pocet_prihlaseni + 1, k_datum_naposledy = now()  WHERE k_id = ".$_SESSION["k_id"];

                                  //echo $sql;

                                  $vysledek_updatu_prihlaseni = @$mysqli->query($sql);

                     

                                  if ($vysledek_updatu_prihlaseni) {

                                     //echo $sql;

                                     //echo "<meta http-equiv=\"refresh\" content=\"0;URL= index.php\">";

                                     //return;

                                   }else{

                                     $error = $mysqli->error; 

                                      echo $error; 

                                      return;

                                  }

                        } 



                                  

                

                echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php\">";

                return;

         }else{



               

              $sql_existuje_obsluha= "SELECT * FROM k_poduzivatele_obsluha obsluha JOIN k_uzivatele uzivatele ON obsluha.k_poduzivatele_obsluha_hlavni = uzivatele.k_id WHERE trim(obsluha.k_poduzivatele_obsluha_jmeno)='$loginObsluhy' and k_poduzivatele_obsluha_heslo = '$sha1_password' and uzivatele.k_email = '$loginProvozovny'";

              //echo $sql_existuje_obsluha;

              //exit;



              $vysledek_existuje_obsluha=$mysqli->query($sql_existuje_obsluha);

              $radku_existuje_obsluha=$vysledek_existuje_obsluha->num_rows; 



              $sql_existuje_obsluha=MySQLi_Fetch_Array($vysledek_existuje_obsluha);

              

              $pod_obsluha_login = $sql_existuje_obsluha["k_poduzivatele_obsluha_jmeno"];

              $pod_obsluha_heslo = $sql_existuje_obsluha["k_poduzivatele_obsluha_heslo"];

              $pod_obsluha_email = $sql_existuje_obsluha["k_email"];



              $_SESSION["k_soft"] = $sql_existuje_obsluha["k_soft"];  

      



              /*OBSLUHA*/

              if ($pod_obsluha_heslo = $sha1_password and $loginObsluhy = $pod_obsluha_login and $pod_obsluha_email = $loginProvozovny and $radku_existuje_obsluha == "1"  ) {



                     setcookie("loginProvozovny", $loginProvozovny, time() + ((86400 * 30)*30), "/"); // 86400 = 1 day // 30 dni



                     $_SESSION["JePoduzivatel"] = "0";

                     $_SESSION["JeObsluha"] = "1";



                     $_SESSION["loginProvozovny"] = $loginProvozovny;

                     //$_SESSION["obsluhaHash"] = $sql_existuje_obsluha["k_poduzivatele_obsluha_hash"];  





                     



                     $uzivatel_celejmeno = $sql_existuje_obsluha["k_poduzivatele_obsluha_jmeno"];

                     $_SESSION["uzivatel_prijmeni_jmeno"] = $uzivatel_celejmeno;



                     $_SESSION["k_poduzivatele_obsluha_jmeno"] = $sql_existuje_obsluha["k_poduzivatele_obsluha_jmeno"];

                     $_SESSION["k_poduzivatele_obsluha_id_hs"] = $sql_existuje_obsluha["k_poduzivatele_obsluha_id_hs"];



                     //citliva data

                     $_SESSION["k_poduzivatele_skryt_citliva_data"] = $sql_existuje_obsluha["k_poduzivatele_skryt_citliva_data"];

                     $_SESSION["SQL_ROK"] = date("Y");



                     // vZdy aktivni bud existuje nebo ne

                     $_SESSION["uzivatel_prihlasen"] = "ano";

                     

                     $_SESSION["k_informace_blokace"] = "";

                     $_SESSION["k_aktivni"] = "1";



                     if ($sql_existuje_obsluha["k_poduzivatele_obsluha_foto"]=="") {

                        $_SESSION["ObsluhaFoto"] = "";    

                     }else{

                        $_SESSION["ObsluhaFoto"] = $sql_existuje_obsluha["k_poduzivatele_obsluha_foto"];

                     }



                                          

                     $_SESSION["k_poduzivatele_obsluha_sw_id"] = $sql_existuje_obsluha["k_poduzivatele_obsluha_sw_id"];



                     $_SESSION["k_id"] = $sql_existuje_obsluha["k_poduzivatele_obsluha_hlavni"];

                     $_SESSION["k_poduzivatele_id"] = $sql_existuje_obsluha["k_poduzivatele_obsluha_id"];



                     //zjisteni emaulu admina

                     $sql_existuje_infoAdmin= "SELECT * FROM `k_uzivatele` WHERE `k_id` = ".$sql_existuje_obsluha["k_poduzivatele_obsluha_hlavni"];

                     $vysledek_existuje_infoAdmin=$mysqli->query($sql_existuje_infoAdmin);

                     $sql_existuje_infoAdmin=MySQLi_Fetch_Array($vysledek_existuje_infoAdmin);

                     

                     $infoAdmin_email = $sql_existuje_infoAdmin["k_email"];





                     // je potreba pro pobocky

                     $_SESSION["k_email"] = $infoAdmin_email;

                     

                     /* MIGRACE PHP 5.5 -> 8.3

                        Duvod:

                        Session hodnoty predstavuji seznamy pro in_array(). PHP 8.3

                        nepovoluje prazdny string jako druhy parametr. Prazdne pole

                        zachovava puvodni vysledek hledani.



                        STARY KOD PHP 5.5:

                        $_SESSION["SeznamPravPoduzivatele"] = "";

                        $_SESSION["SeznamPravPoduzivateleNaPobocky"] = "";

                     */

                     $_SESSION["SeznamPravPoduzivatele"] = array();

                     $_SESSION["SeznamPravPoduzivateleNaPobocky"] = array();

                     





                 //VYTVORI TABULKY PRO NOVY ROK POKUD NEEXISTUJE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

                 $rok_pro_test_tabulek = date("Y");

                 OtestujVytvorTabulkuJednotlivci($rok_pro_test_tabulek,$mysqli);

                 OtestujVytvorTabulkuStrediska($rok_pro_test_tabulek,$mysqli);

                 //VYTVORI TABULKY PRO NOVY ROK POKUD NEEXISTUJE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

                





                 echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/loginObsluha.php\">";

                 return;



              }else {

                   

                   //Zapomenout aktualni a zacit znovu

                   

                   $_SESSION = array();

                   session_destroy();

                         

                    if (!isset($_SESSION["uzivatel_prijmeni_jmeno"])){$_SESSION["uzivatel_prijmeni_jmeno"]='';}

                         

                     if ($_SESSION["uzivatel_prijmeni_jmeno"]){

                          echo  "FATAL ERROR: Nemůžu odstranit SESSION! Kontaktujte administrátora webu!";

                     }else{



                          session_start();

                          $_SESSION["chybne_udaje"] = "Chybné přihlašovací údaje.";

                          echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/loginObsluha.php\">";

                    }

                 

                  echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/loginObsluha.php\">";

                 return;

              }

          }

         

   // LOGIN END

    ?>

    

    <!--Global JS-->

    <script src="../js/vendor/jquery-1.11.1.min.js"></script>

    <script src="../plugins/bootstrap/js/bootstrap.min.js"></script>

    <script src="../plugins/navgoco/jquery.navgoco.min.js"></script>

    <script src="../plugins/pace/pace.min.js"></script>

    <script src="../js/src/app.js"></script>

</body>



</html>

