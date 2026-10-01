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




?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js">
<!--<![endif]-->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> 
    <title><?php echo $app;    ?> - Klient</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/simple-line-icons.css">
    <link rel="stylesheet" href="../css/animate.css">
    <link rel="stylesheet" href="../css/main.css">
    

    <link rel="stylesheet" href="../css/_tmk.css">
    
    
    <script src="../js/vendor/modernizr-2.6.2.min.js"></script>
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="../js/vendor/html5shiv.js"></script>
    <script src="../js/vendor/respond.min.js"></script>
    <![endif]-->
</head>
<body>

<?php
         
         if ($_SESSION["uzivatel_prihlasen"] != "ano" and $loginObsluhy ==""){
            ?> 

                <section class="container animated fadeInUp">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3">
                            <div id="login-wrapper">
                                <header>
                                    <div class="brand">
                                        <a href="https://klient.hairsoft.cz/str/loginObsluha.php?app=<?php $app; ?>" class="logo">
                                            <!-- <i class="icon-layers"></i> -->
                                            <span><?php echo $app; ?></span>Klient
                                        </a>
                                    </div>
                                </header>
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">     
                                       Přihlášení 
                                    </h3>
                                    </div>
                                    <div class="panel-body">

                                      <?php
                                        if ($_SESSION["uzivatel_prihlasen"]=="ne") {
                                           ?>
                                                  <div align="center" id="login_blokace_text">
                                                    <b align="center">Účet blokován !</b><br>
                                                       <?php
                                                            echo $_SESSION["k_informace_blokace"];
                                                            $_SESSION["k_informace_blokace"]="";
                                                       ?>
                                                  </div> 
                                           <?php
                                        }
                                        
                                          
                                         if ($_SESSION["chybne_udaje"]!="") {
                                           ?>
                                                  <div align="center" id="login_blokace_text">
                                                    <b align="center">Informace</b><br>
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
                                      
                                        
                                        <p> Přihlášení do účtu obsluhy</p>
                                        <form class="form-horizontal" method="POST" action="https://klient.hairsoft.cz/str/loginObsluha.php">
                                            <div class="form-group">
                                                <div class="col-md-12">
                                                    <input type="text" class="form-control" id="email" name="loginProvozovny" placeholder="Login / Email provozovny" value="<?php echo $loginProvozovny; ?>">
                                                    <i class="fa fa-user"></i>
                                                </div>
                                            </div>

                                            <div class="form-group" id="loginObsluhy" >
                                                <div class="col-md-12">
                                                    <input type="text" class="form-control" id="email" name="loginObsluhy" placeholder="Login / Jméno obsluhy">
                                                    <i class="fa fa-user"></i>
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <div class="col-md-12">
                                                    <input type="password" class="form-control" id="password" name="password" placeholder="Heslo">
                                                    <i class="fa fa-lock"></i>
                                                    
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-md-12">
                                                   
                                                    
                                                    <input type="submit" class="btn btn-primary btn-block" value="Přihlásit">  
                                        </form>            
                                                                                                

                                                      <hr />
                                                   

                                                       <form class="form-horizontal" method="POST" action="https://klient.hairsoft.cz/str/login.php?app=<?php echo $app;?>">
                                                            <input type="submit" class="btn btn-info btn-block" value="Přihlásit se jako Admin">
                                                       </form>
                                                </div>
                                            </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
