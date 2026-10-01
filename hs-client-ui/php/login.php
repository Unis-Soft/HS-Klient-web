<?php
// Přímé otevření moderní vrstvy přesměruje na veřejný vstup.
if (isset($_SERVER['SCRIPT_FILENAME']) && realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    header('Location: ../../str/login.php');
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
require_once __DIR__ . '/multi_company_auth.php';
require_once '../fce/GeneratorHesel.php';
//require_once '../fce/PosliEmail.php';
require_once 'strana/_VytvoritTabulkyTrzebProNovyRok.php';


function encode_header($string) {
  return  '=?UTF-8?B?' . base64_encode($string) . '?=';
}

function PosliEmail($komu, $predmet, $zprava, $odesilatel )
{

      $to = $komu;
      $message = "";
      $subject = $predmet;
      //$odesilatel = $odesilatel;
      
      
      $headers = "From: " . $odesilatel . "\r\n";
      $headers .= "Reply-To: ". $odesilatel. "\r\n";
      
      //$headers .= "CC: susan@example.com\r\n"; // Skryta kopie
      $headers .= "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
      
      $message .= $zprava;
      
      echo mail($to, encode_header($subject), $message, $headers);
    
}


 // LOGIN
         if (!isset($_POST['login']) || is_array($_POST['login'])){$_POST['login']='';}
         $login  =  htmlspecialchars($_POST['login'], ENT_COMPAT);
         
         if (!isset($_GET['zapomenute_heslo']) || is_array($_GET['zapomenute_heslo'])){$_GET['zapomenute_heslo']='';}
         $zapomenute_heslo  =  htmlspecialchars($_GET['zapomenute_heslo'], ENT_COMPAT);

         if (!isset($_GET['app']) || is_array($_GET['app'])){$_GET['app']='';}
         $app  =  htmlspecialchars($_GET['app'], ENT_COMPAT);
                      
         if (!isset($_POST['password']) || is_array($_POST['password'])){$_POST['password']='';}
         // Původní PHP 7.4: $sha1_password = sha1(htmlspecialchars($_POST['password']));
         // MIGRACE PHP 7.4 -> 8.3: zachování původního výchozího ENT_COMPAT, aby se heslo hashovalo stejně.
         $sha1_password = sha1(htmlspecialchars($_POST['password'], ENT_COMPAT));
         
         if (!isset($_POST['zapomenute_heslo_zadani']) || is_array($_POST['zapomenute_heslo_zadani'])){$_POST['zapomenute_heslo_zadani']='';}
         $zapomenute_heslo_zadani = htmlspecialchars($_POST['zapomenute_heslo_zadani'], ENT_COMPAT);
         
         if (!isset($_POST['zapomenute_heslo_zadani_email']) || is_array($_POST['zapomenute_heslo_zadani_email'])){$_POST['zapomenute_heslo_zadani_email']='';}
         $zapomenute_heslo_zadani_email = htmlspecialchars($_POST['zapomenute_heslo_zadani_email'], ENT_COMPAT);
         
         if (!isset($_SESSION["uzivatel_prihlasen"])){$_SESSION["uzivatel_prihlasen"]='';}


         if ($app=="") {
            $app="HairSoft";
         }elseif ($app=="ZooSoft") {
            $app="ZooSoft";
         }

         // V191: pokud bezna PHP session vypršela, obnov aktivní firmu z bezpečného dlouhodobého tokenu.
         if ($_SESSION["uzivatel_prihlasen"] !== "ano") {
            $hsMultiAutoLogin = hsMultiTryAutoLogin($mysqli, $app);
            if ($hsMultiAutoLogin && isset($_SESSION["uzivatel_prihlasen"]) && $_SESSION["uzivatel_prihlasen"] === "ano") {
                // Dlouhodobé přihlášení nesmí obejít původní novoroční přípravu tabulek tržeb.
                $rok_pro_test_tabulek = date("Y");
                OtestujVytvorTabulkuJednotlivci($rok_pro_test_tabulek, $mysqli);
                OtestujVytvorTabulkuStrediska($rok_pro_test_tabulek, $mysqli);
            }
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
               'headline' => 'Váš salon pod kontrolou.',
               'subline' => 'Přehledně, bezpečně a odkudkoliv.',
               'security' => 'Vaše data jsou v bezpečí',
               'welcome' => 'Vítejte zpět',
               'adminDescription' => 'Přihlaste se do účtu administrátora.',
               'loginLabel' => 'Login / Email',
               'loginPlaceholder' => 'Zadejte login nebo email',
               'password' => 'Heslo',
               'forgot' => 'Zapomněli jste heslo?',
               'passwordPlaceholder' => 'Zadejte heslo',
               'signIn' => 'Přihlásit',
               'otherOptions' => 'Další možnosti',
               'demo' => 'Vyzkoušet DEMO účet',
               'staff' => 'Přihlásit se jako obsluha',
               'recoveryHeadline' => 'Obnovení přístupu',
               'recoverySubline' => 'Nové heslo vám bezpečně zašleme emailem.',
               'recoverySecurity' => 'Bezpečné obnovení účtu',
               'forgotTitle' => 'Zapomenuté heslo',
               'forgotDescription' => 'Zadejte email registrovaný ve vašem účtu.',
               'email' => 'Email',
               'emailPlaceholder' => 'Zadejte svůj email',
               'sendPassword' => 'Zaslat nové heslo emailem',
               'back' => 'Zpět k přihlášení',
               'changeLanguage' => 'Změnit jazyk'
            ),
            'en' => array(
               'headline' => 'Your salon under control.',
               'subline' => 'Clear, secure and accessible anywhere.',
               'security' => 'Your data is safe',
               'welcome' => 'Welcome back',
               'adminDescription' => 'Sign in to your administrator account.',
               'loginLabel' => 'Login / Email',
               'loginPlaceholder' => 'Enter your login or email',
               'password' => 'Password',
               'forgot' => 'Forgot your password?',
               'passwordPlaceholder' => 'Enter your password',
               'signIn' => 'Sign in',
               'otherOptions' => 'Other options',
               'demo' => 'Try the DEMO account',
               'staff' => 'Sign in as staff',
               'recoveryHeadline' => 'Restore access',
               'recoverySubline' => 'We will securely send a new password to your email.',
               'recoverySecurity' => 'Secure account recovery',
               'forgotTitle' => 'Forgotten password',
               'forgotDescription' => 'Enter the email registered with your account.',
               'email' => 'Email',
               'emailPlaceholder' => 'Enter your email',
               'sendPassword' => 'Send a new password by email',
               'back' => 'Back to sign in',
               'changeLanguage' => 'Change language'
            ),
            'de' => array(
               'headline' => 'Ihr Salon unter Kontrolle.',
               'subline' => 'Übersichtlich, sicher und überall erreichbar.',
               'security' => 'Ihre Daten sind sicher',
               'welcome' => 'Willkommen zurück',
               'adminDescription' => 'Melden Sie sich bei Ihrem Administratorkonto an.',
               'loginLabel' => 'Login / E-Mail',
               'loginPlaceholder' => 'Login oder E-Mail eingeben',
               'password' => 'Passwort',
               'forgot' => 'Passwort vergessen?',
               'passwordPlaceholder' => 'Passwort eingeben',
               'signIn' => 'Anmelden',
               'otherOptions' => 'Weitere Möglichkeiten',
               'demo' => 'DEMO-Konto ausprobieren',
               'staff' => 'Als Mitarbeiter anmelden',
               'recoveryHeadline' => 'Zugang wiederherstellen',
               'recoverySubline' => 'Wir senden Ihnen sicher ein neues Passwort per E-Mail.',
               'recoverySecurity' => 'Sichere Kontowiederherstellung',
               'forgotTitle' => 'Passwort vergessen',
               'forgotDescription' => 'Geben Sie die in Ihrem Konto registrierte E-Mail-Adresse ein.',
               'email' => 'E-Mail',
               'emailPlaceholder' => 'E-Mail-Adresse eingeben',
               'sendPassword' => 'Neues Passwort per E-Mail senden',
               'back' => 'Zurück zur Anmeldung',
               'changeLanguage' => 'Sprache ändern'
            ),
            'sk' => array(
               'headline' => 'Váš salón pod kontrolou.',
               'subline' => 'Prehľadne, bezpečne a odkiaľkoľvek.',
               'security' => 'Vaše údaje sú v bezpečí',
               'welcome' => 'Vitajte späť',
               'adminDescription' => 'Prihláste sa do účtu administrátora.',
               'loginLabel' => 'Login / Email',
               'loginPlaceholder' => 'Zadajte login alebo email',
               'password' => 'Heslo',
               'forgot' => 'Zabudli ste heslo?',
               'passwordPlaceholder' => 'Zadajte heslo',
               'signIn' => 'Prihlásiť',
               'otherOptions' => 'Ďalšie možnosti',
               'demo' => 'Vyskúšať DEMO účet',
               'staff' => 'Prihlásiť sa ako obsluha',
               'recoveryHeadline' => 'Obnovenie prístupu',
               'recoverySubline' => 'Nové heslo vám bezpečne zašleme emailom.',
               'recoverySecurity' => 'Bezpečné obnovenie účtu',
               'forgotTitle' => 'Zabudnuté heslo',
               'forgotDescription' => 'Zadajte email registrovaný vo vašom účte.',
               'email' => 'Email',
               'emailPlaceholder' => 'Zadajte svoj email',
               'sendPassword' => 'Zaslať nové heslo emailom',
               'back' => 'Späť k prihláseniu',
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
    <!-- Favicon -->
    <link rel="icon" href="/hs-client-ui/img/favicon.ico?v=41" type="image/x-icon">
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="../plugins/bootstrap/css/bootstrap.min.css">
    <!-- Fonts  -->
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/simple-line-icons.css">
    <!-- CSS Animate -->
    <link rel="stylesheet" href="../css/animate.css">
    <!-- Custom styles for this theme -->
    <link rel="stylesheet" href="../css/main.css">
    
    
    <link rel="stylesheet" href="../css/_tmk.css">
    <link rel="stylesheet" href="/hs-client-ui/css/login.css?v=196">
    <link rel="stylesheet" href="/hs-client-ui/css/i18n.css?v=196">
    
    
    
    <!-- Feature detection -->
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
         
        


         
         //zmena hesla mysql 
         if ($zapomenute_heslo_zadani=="1") {
          
                $sql_existuje_heslo= "SELECT k_heslo,k_soft FROM k_uzivatele WHERE k_email='$zapomenute_heslo_zadani_email'";
                              
                              //echo $sql_existuje_heslo; 
                              $vysledek_existuje_heslo=$mysqli->query($sql_existuje_heslo);
                              $radku_existuje_heslo=$vysledek_existuje_heslo->num_rows; 
                              $sql_existuje_heslo=MySQLi_Fetch_Array($vysledek_existuje_heslo);
                              
                      
                              $k_soft = $sql_existuje_heslo["k_soft"];
                              
                              
                              if ($radku_existuje_heslo==1) {
                                
                                  //zaslani noveho hesla
                                  $heslo_klienta = VygenerujHeslo();                                  
                                  $email_klienta = $zapomenute_heslo_zadani_email;
                                  


                                  if($k_soft=="HairSoft" or $k_soft==""){
                                        //email
                                        $Sablona = "<div><span style=\" font-family:Tahoma, Arial, sans-serif; font-size: 19pt;\"><b>HairSoft - Klient<br><br></b><span style=\" font-size: 10pt;\">Zasíláme Vám přihlašovací údaje do HairSoft Systému<br><br>Link na Náš klientský web: <a href=\"http:\\\www.klient.hairsoft.cz\" target=\"_self\"><b>klient.hairsoft.cz</b></a><br>Přihlašovací email: <b>".$email_klienta."</b><br>Přihlašovací heslo: <b>".$heslo_klienta."</b><br><br><i>HairSoft Team <br><br><img src=\"https://admin.hairsoft.cz/dashboard/img/email/email.jpg\" style=\"padding : 1px;\" alt=\"\" width=\"217\" height=\"99\"><br><br></i><span style=\" color: #333333;\"><b>UnisSoft s.r.o.</b> | produkt HairSoft<br><br><span style=\" color: #000000;\">IČ: 277 40 013<br><span style=\" color: #333333;\">Nad Jihlávkou 5064/6, 586 01, Jihlava<br>Czech Republic<br><br>mobil: +420 608 936 960<br>e-mail: </span></span></span></span></span><a style=\" color: #333333; font-family:Tahoma, Arial, sans-serif; font-size: 10pt;\" target=\"_blank\">salon@hairsoft.cz</a><br> <span style=\" font-family:Tahoma, Arial, sans-serif; font-size: 10pt; color: #333333;\">web: </span><a href=\"https:\\\www.hairsoft.cz\" style=\" color: #333333; font-family:Tahoma, Arial, sans-serif; font-size: 10pt;\" target=\"_blank\">www.hairsoft.cz</a></span></div>"; 
                                        $HlavickaEmailu = "HairSoft Klient - Změna hesla";
                                        $Odesilatel = "info@hairsoft.cz";

                                  }elseif($k_soft=="ZooSoft"){
                                        //email zoo
                                        $Sablona = "<div><span style=\" font-family:Tahoma, Arial, sans-serif; font-size: 19pt;\"><b>ZOOSoft - Klient<br><br></b><span style=\" font-size: 10pt;\">Zasíláme Vám přihlašovací údaje do ZOOSoft Systému<br><br>Link na Náš klientský web: <a href=\"http:\\\www.klient.zoosoft.cz\" target=\"_self\"><b>klient.zoosoft.cz</b></a><br>Přihlašovací email: <b>".$email_klienta."</b><br>Přihlašovací heslo: <b>".$heslo_klienta."</b><br><br><i>ZOOSoft Team <br><br><img src=\"https://admin.hairsoft.cz/dashboard/img/email/emailzoosoft.jpg\" style=\"padding : 1px;\" alt=\"\" width=\"217\" height=\"99\"><br><br></i><span style=\" color: #333333;\"><b>UnisSoft s.r.o.</b> | produkt ZOOSoft<br><br><span style=\" color: #000000;\">IČ: 277 40 013<br><span style=\" color: #333333;\">Nad Jihlávkou 5064/6, 586 01, Jihlava<br>Czech Republic<br><br>mobil: +420 608 936 960<br>e-mail: </span></span></span></span></span><a style=\" color: #333333; font-family:Tahoma, Arial, sans-serif; font-size: 10pt;\" target=\"_blank\">info@zoosoft.cz</a><br> <span style=\" font-family:Tahoma, Arial, sans-serif; font-size: 10pt; color: #333333;\">web: </span><a href=\"https:\\\www.zoosoft.cz\" style=\" color: #333333; font-family:Tahoma, Arial, sans-serif; font-size: 10pt;\" target=\"_blank\">www.zoosoft.cz</a></span></div>"; 
                                        $HlavickaEmailu = "ZOOSoft Klient - Změna hesla";
                                        $Odesilatel = "info@zoosoft.cz";

                                  }


                                  





                                  PosliEmail($email_klienta,$HlavickaEmailu,$Sablona,$Odesilatel);
                                  
                                  //Uloz heslo
                                  $sql_ulozeni_hesla = "UPDATE `k_uzivatele` SET `k_heslo` = '".sha1($heslo_klienta)."' WHERE `k_email` = '".$email_klienta."';";
                                  $vysledek_ulozeni_hesla = @$mysqli->query($sql_ulozeni_hesla);
                     
                                  if ($vysledek_ulozeni_hesla) {
                                     //echo $sql;
                                      $uzivatel_zmena_hesla_text_ok = "Nové heslo Vám bylo zasláno na email.";
                                   }else{
                                     $error = $mysqli->error; 
                                      echo $error; 
                                      return;
                                  }
                                  
                                  
                                
                              }else {
                                 $uzivatel_zmena_hesla_text = "Vámi zadaný email, není registrován v klient systému! Kontaktujte administrátora.";
                              }
                              
                              //if ($radku_existuje_heslo == 1 and sha1($heslo_puvodni) == $sql_existuje_heslo["k_heslo"]) {
                
               } 

         
         
         
         
         if ($zapomenute_heslo=="ano"){
                
               ?>
               
                                      <section class="login-stage animated fadeInUp">
                                          <div class="login-shell login-shell--recovery" id="login-wrapper">
                                              <aside class="login-visual">
                                                  <a href="login.php?app=<?php echo $app; ?>" class="login-brand">
                                                      <span><?php echo $app; ?></span>
                                                      <small>Klient</small>
                                                  </a>
                                                  <div class="login-visual__content login-visual__content--simple">
                                                      <h1 class="login-visual__headline"><?php echo $loginText['recoveryHeadline']; ?></h1>
                                                      <p class="login-visual__subline"><?php echo $loginText['recoverySubline']; ?></p>
                                                  </div>
                                                  <div class="login-visual__footer">
                                                      <span class="login-security-icon"><i class="fa fa-shield"></i></span>
                                                      <span><?php echo $loginText['recoverySecurity']; ?></span>
                                                  </div>
                                              </aside>

                                              <main class="login-card">
                                                  <div class="login-card__header">
                                                      <h2><?php echo $loginText['forgotTitle']; ?></h2>
                                                      <p><?php echo $loginText['forgotDescription']; ?></p>
                                                  </div>

                                                  <form class="login-form" method="POST" action="https://klient.hairsoft.cz/str/login.php?app=<?php echo $app;?>">
                                                      <input type="hidden" id="login" name="zapomenute_heslo_zadani" value="1">

                                                      <div class="login-field-group">
                                                          <label for="recovery-email"><?php echo $loginText['email']; ?></label>
                                                          <div class="login-field">
                                                              <i class="fa fa-envelope"></i>
                                                              <input type="text" class="form-control" id="recovery-email" name="zapomenute_heslo_zadani_email" placeholder="<?php echo $loginText['emailPlaceholder']; ?>" autocomplete="email">
                                                          </div>
                                                      </div>

                                                      <button type="submit" class="login-button login-button--primary">
                                                          <span><?php echo $loginText['sendPassword']; ?></span>
                                                          <i class="fa fa-arrow-right"></i>
                                                      </button>
                                                  </form>

                                                  <a href="login.php?app=<?php echo $app; ?>" class="login-back-link">
                                                      <i class="fa fa-arrow-left"></i>
                                                      <span><?php echo $loginText['back']; ?></span>
                                                  </a>
                                              </main>
                                          </div>
                                      </section>
    <?php
      
    
               
           return;
         }
         
         
         
         
         if ($_SESSION["uzivatel_prihlasen"] != "ano" and $login ==""){
?> 

    <section class="login-stage animated fadeInUp">
        <div class="login-shell" id="login-wrapper">
            <aside class="login-visual">
                <a href="https://klient.hairsoft.cz/str/login.php?app=<?php echo $app; ?>" class="login-brand">
                    <span><?php echo $app; ?></span>
                    <small>Klient</small>
                </a>
                <div class="login-visual__content login-visual__content--simple">
                    <h1 class="login-visual__headline"><?php echo $loginText['headline']; ?></h1>
                    <p class="login-visual__subline"><?php echo $loginText['subline']; ?></p>
                </div>
                <div class="login-visual__footer">
                    <span class="login-security-icon"><i class="fa fa-shield"></i></span>
                    <span><?php echo $loginText['security']; ?></span>
                </div>
            </aside>

            <main class="login-card">
                <div class="login-card__header">
                    <h2><?php echo $loginText['welcome']; ?></h2>
                    <p><?php echo $loginText['adminDescription']; ?></p>
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
                            

                                if ($uzivatel_zmena_hesla_text!="") {
                                   ?>
                                          <div id="login_blokace_text" class="login-alert login-alert--error">
                                               <?php
                                                    echo $uzivatel_zmena_hesla_text;
                                               ?>
                                          </div> 
                                   <?php
                                }


                                if ($uzivatel_zmena_hesla_text_ok!="") {
                                   ?>
                                          <div id="login_uspesne_relogin_text" class="login-alert login-alert--success">
                                            <b>Informace</b><br>
                                               <?php
                                                    echo $uzivatel_zmena_hesla_text_ok;
                                               ?>
                                          </div> 
                                   <?php
                                }
                                 
                            
                            
                          ?>
                          
                            <form class="login-form" method="POST" action="https://klient.hairsoft.cz/str/login.php">
                                <div class="login-field-group">
                                    <label for="email"><?php echo $loginText['loginLabel']; ?></label>
                                    <div class="login-field">
                                        <i class="fa fa-user"></i>
                                        <input type="text" class="form-control" id="email" name="login" placeholder="<?php echo $loginText['loginPlaceholder']; ?>" autocomplete="username">
                                    </div>
                                </div>

                                <div class="login-field-group">
                                    <div class="login-label-row">
                                        <label for="password"><?php echo $loginText['password']; ?></label>
                                                <?php
                                                        if ($app=="HairSoft") {
                                                            echo "<a href=\"https://klient.hairsoft.cz/str/login.php?zapomenute_heslo=ano\" class=\"login-forgot\">".$loginText['forgot']."</a>";
                                                        }elseif ($app=="ZooSoft") {
                                                            echo "<a href=\"https://klient.hairsoft.cz/str/login.php?zapomenute_heslo=ano&app=ZooSoft\" class=\"login-forgot\">".$loginText['forgot']."</a>";
                                                        }else{
                                                            echo "<a href=\"https://klient.hairsoft.cz/str/login.php?zapomenute_heslo=ano\" class=\"login-forgot\">".$loginText['forgot']."</a>";
                                                        }
                                                 ?>
                                    </div>
                                    <div class="login-field">
                                        <i class="fa fa-lock"></i>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="<?php echo $loginText['passwordPlaceholder']; ?>" autocomplete="current-password">
                                    </div>
                                </div>
                                <button id="btn-prihlasit" class="login-button login-button--primary" type="submit">
                                    <span><?php echo $loginText['signIn']; ?></span>
                                    <i class="fa fa-arrow-right"></i>
                                </button>
                            </form>

                            <div class="login-divider"><span><?php echo $loginText['otherOptions']; ?></span></div>

                            <div class="login-alternatives">
                                <form method="POST" action="https://klient.hairsoft.cz/str/login.php?app=<?php echo $app;?>">
                                    <input type="hidden" id="login" name="login" value="DEMO">
                                    <input type="hidden" id="password" name="password" value="demo">
                                    <input type="hidden" id="soft" name="soft" value="<?php echo $app; ?>">
                                    <button type="submit" class="login-button login-button--demo">
                                        <i class="fa fa-desktop"></i>
                                        <span><?php echo $loginText['demo']; ?></span>
                                    </button>
                                </form>

                                <form method="POST" action="https://klient.hairsoft.cz/str/loginObsluha.php?app=<?php echo $app;?>">
                                    <button type="submit" class="login-button login-button--staff">
                                        <i class="fa fa-users"></i>
                                        <span><?php echo $loginText['staff']; ?></span>
                                    </button>
                                </form>
                            </div>
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

               // Pristup bez hesla
               
                //HACK LOGIN BEZ HESLA
                //info@cooperbarber.cz
                //thomas@thomasbarbershop.cz
                //radek.zdarsky@protonmail.com
                //scherysro@gmail.com
                //info@synergycosmetic.cz
                //
                //goldni@seznam.cz
                //jurabocek@gmail.com
                //info@unissoft.cz


                /* OBSLUHA
                if ($login=="info@cooperbarber.cz") {
                   $sql_existuje_uzivatel= "SELECT * FROM k_uzivatele WHERE k_email='$login'";  
                }else{
                   $sql_existuje_uzivatel= "SELECT * FROM k_uzivatele WHERE k_email='$login' and k_heslo = '$sha1_password'";
                }
                */
                
               
              /* Obsluha */
              $sql_existuje_obsluha= "SELECT * FROM k_poduzivatele_obsluha WHERE k_poduzivatele_obsluha_login='$login' and k_poduzivatele_obsluha_heslo = '$sha1_password'";
              
//hlavni select na kontrolu
              //echo $sql_existuje_obsluha;
              //exit;
              $vysledek_existuje_obsluha=$mysqli->query($sql_existuje_obsluha);
              $radku_existuje_obsluha=$vysledek_existuje_obsluha->num_rows; 

              $sql_existuje_obsluha=MySQLi_Fetch_Array($vysledek_existuje_obsluha);
              
              $pod_obsluha_login = $sql_existuje_obsluha["k_poduzivatele_obsluha_login"];
              $pod_obsluha_heslo = $sql_existuje_obsluha["k_poduzivatele_obsluha_heslo"];

              $_SESSION["k_poduzivatele_obsluha_jmeno"] = $sql_existuje_obsluha["k_poduzivatele_obsluha_jmeno"];
              $_SESSION["k_poduzivatele_obsluha_id_hs"] = $sql_existuje_obsluha["k_poduzivatele_obsluha_id_hs"];
              

              
              

              



               /* PODUZIVATEL */
               $sql_existuje_poduzivatel= "SELECT * FROM k_poduzivatele WHERE k_poduzivatele_email='$login' and k_poduzivatele_heslo = '$sha1_password'";

                /*if ($login=="info@.cz"){
                   $sql_existuje_uzivatel= "SELECT * FROM k_uzivatele WHERE  k_email='$login'";  
                }else{
                   $sql_existuje_uzivatel= "SELECT * FROM k_uzivatele WHERE  k_email='$login' and k_heslo = '$sha1_password'";
                } 
                */
              
              $vysledek_existuje_poduzivatel=$mysqli->query($sql_existuje_poduzivatel);
              $radku_existuje_poduzivatel=$vysledek_existuje_poduzivatel->num_rows; 
              $sql_existuje_poduzivatel=MySQLi_Fetch_Array($vysledek_existuje_poduzivatel);
              
              $pod_uzivatel_email = $sql_existuje_poduzivatel["k_poduzivatele_email"];
              $pod_uzivatel_heslo = $sql_existuje_poduzivatel["k_poduzivatele_heslo"];


               /*UZIVATEL*/
               $sql_existuje_uzivatel= "SELECT * FROM k_uzivatele WHERE k_email='$login' and k_heslo = '$sha1_password'";
               
               
                /*Prihlaseni pro majitele*/
                /*HACK*/
                //robert.kellner@seznam.cz
                //                info@synergycosmetic.cz
                //info@cooperbarber.cz
                //Info@oxisecret.com kutinska

                /*if ($login=="info@cooperbarber.cz") {
                   $sql_existuje_uzivatel= "SELECT * FROM k_uzivatele WHERE k_email='$login'";  
                }else{
                   $sql_existuje_uzivatel= "SELECT * FROM k_uzivatele WHERE k_email='$login' and k_heslo = '$sha1_password'";
                }*/
                
                /*if ($login=="info@cooperbarber.cz") {
                   $sql_existuje_uzivatel= "SELECT * FROM k_uzivatele WHERE k_email='$login'";  
                }else{
                   $sql_existuje_uzivatel= "SELECT * FROM k_uzivatele WHERE k_email='$login' and k_heslo = '$sha1_password'";
                }*/

              
              $vysledek_existuje_uzivatel=$mysqli->query($sql_existuje_uzivatel);
              $radku_existuje_uzivatel=$vysledek_existuje_uzivatel->num_rows; 
              $sql_existuje_uzivatel=MySQLi_Fetch_Array($vysledek_existuje_uzivatel);
              
              $uzivatel_email = $sql_existuje_uzivatel["k_email"];
              $uzivatel_heslo = $sql_existuje_uzivatel["k_heslo"];

              

              if ($pod_obsluha_login=="DEMO") {
                
                $_SESSION["k_soft"] = $sql_existuje_uzivatel["k_soft"];  

              }else{

                $_SESSION["k_soft"] = $app;
              }




              /*OBSLUHA*/
              if ($pod_obsluha_heslo = $sha1_password and $login = $pod_obsluha_login and $radku_existuje_obsluha == "1" ) {

                     $_SESSION["JePoduzivatel"] = "0";
                     $_SESSION["JeObsluha"] = "1";

                     $uzivatel_celejmeno = $sql_existuje_obsluha["k_poduzivatele_obsluha_jmeno"];
                     $_SESSION["uzivatel_prijmeni_jmeno"] = $uzivatel_celejmeno;

                     // vZdy aktivni bud existuje nebo ne
                     $_SESSION["uzivatel_prihlasen"] = "ano";
                     
                     $_SESSION["k_informace_blokace"] = "";
                     $_SESSION["k_aktivni"] = "1";

                                          
                     $_SESSION["k_poduzivatele_obsluha_sw_id"] = $sql_existuje_obsluha["k_poduzivatele_obsluha_sw_id"];
                     // V169: stejna privacy hodnota jako pri prihlaseni pres loginObsluha.php.
                     $_SESSION["k_poduzivatele_skryt_citliva_data"] = $sql_existuje_obsluha["k_poduzivatele_skryt_citliva_data"];
                     $_SESSION["SQL_ROK"] = date("Y");

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
                


                 echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/login.php\">";
                 return;


              /*UZIVATEL admin*/ 
              }elseif ($uzivatel_heslo = $sha1_password and $login = $uzivatel_email and $radku_existuje_uzivatel == "1" ) {

                    $uzivatel_celejmeno = $sql_existuje_uzivatel["k_celejmeno"];
                    
                     $_SESSION["JePoduzivatel"] = "0";
                     $_SESSION["JeObsluha"] = "0";

                     $_SESSION["uzivatel_prijmeni_jmeno"] = $uzivatel_celejmeno;
                     
                     if ($sql_existuje_uzivatel["k_aktivni"] =="0") {
                        $_SESSION["uzivatel_prihlasen"] = "ne";
                        
                     }else {
                        //echo "Přihlášení úspěšné.";
                        $_SESSION["uzivatel_prihlasen"] = "ano";
                     }
                                            
                     $_SESSION["k_informace_blokace"] = $sql_existuje_uzivatel["k_informace_blokace"];
                     $_SESSION["k_aktivni"] = $sql_existuje_uzivatel["k_aktivni"];
                     $_SESSION["k_id"] = $sql_existuje_uzivatel["k_id"];
                     $_SESSION["k_email"] = $sql_existuje_uzivatel["k_email"];
                     $_SESSION["k_poduzivatele_id"] = "";
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


                     $_SESSION["SQL_ROK"] = date("Y"); 
                     
                     

                 //VYTVORI TABULKY PRO NOVY ROK POKUD NEEXISTUJE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                 $rok_pro_test_tabulek = date("Y");
                 OtestujVytvorTabulkuJednotlivci($rok_pro_test_tabulek,$mysqli);
                 OtestujVytvorTabulkuStrediska($rok_pro_test_tabulek,$mysqli);
                 //VYTVORI TABULKY PRO NOVY ROK POKUD NEEXISTUJE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                


                 echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/login.php\">";
                 return;
                
              
              }elseif ($pod_uzivatel_heslo = $sha1_password and $login = $pod_uzivatel_email and $radku_existuje_poduzivatel == "1" ) {
                  /*
                    PODUZIVATELE
                  */

                    
                     $_SESSION["JePoduzivatel"] = "1";
                     $_SESSION["JeObsluha"] = "0";

                     $uzivatel_celejmeno = $sql_existuje_poduzivatel["k_poduzivatele_jmeno"];
                     $_SESSION["uzivatel_prijmeni_jmeno"] = $uzivatel_celejmeno;
                    
                     // vZdy aktivni bud existuje nebo ne
                     $_SESSION["uzivatel_prihlasen"] = "ano";
                     
                     $_SESSION["k_informace_blokace"] = "";
                     $_SESSION["k_aktivni"] = "1";
                     $_SESSION["k_id"] = $sql_existuje_poduzivatel["k_poduzivatele_hlavni"];
                     
                     $_SESSION["k_poduzivatele_foto"] = $sql_existuje_poduzivatel["k_poduzivatele_foto"];
                     $_SESSION["k_poduzivatele_id"] = $sql_existuje_poduzivatel["k_poduzivatele_id"];
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
                     

                     //zjisteni emaulu admina
                     $sql_existuje_infoAdmin= "SELECT * FROM `k_uzivatele` WHERE `k_id` = ".$sql_existuje_poduzivatel["k_poduzivatele_hlavni"];
                     $vysledek_existuje_infoAdmin=$mysqli->query($sql_existuje_infoAdmin);
                     $sql_existuje_infoAdmin=MySQLi_Fetch_Array($vysledek_existuje_infoAdmin);
                     
                     $infoAdmin_email = $sql_existuje_infoAdmin["k_email"];


                     // je potreba pro pobocky
                     $_SESSION["k_email"] = $infoAdmin_email;
                     
                     $_SESSION["SQL_ROK"] = date("Y"); 

                 $rok_pro_test_tabulek = date("Y");
                 OtestujVytvorTabulkuJednotlivci($rok_pro_test_tabulek,$mysqli);
                 OtestujVytvorTabulkuStrediska($rok_pro_test_tabulek,$mysqli);

                 echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/login.php\">";
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
                          echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/login.php\">";
                    }
                 
                  echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/login.php\">";
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
