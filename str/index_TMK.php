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

  $selectedLanguage = isset($_SESSION['language']) ? $_SESSION['language'] : 'cs';    



  // KLIENT

  

  //nacitani casu stranky

  $DURATION_start=microtime(true);

       

  require_once '../cfg/nastaveni.php';

  

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

                  

                

               }else {

                  echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=404\">";

               }             

                                                                                                                                                     

    

                                                                                                                                                     

  }

  

  

  

?>



<!DOCTYPE html>

<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->

<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->

<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->

<!--[if gt IE 8]><!-->

<html class="no-js" >

<!--<html class="no-js" lang="<?php echo $selectedLanguage; ?>">-->

<!--<![endif]-->



<head>

    <meta charset="utf-8">

    <!-- <meta http-equiv="X-UA-Compatible" content="IE=edge"> -->

    <title>HairSoft Klient</title>

    <meta name="description" content="">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    

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

      

    <!-- C3 Chart-->

    <link rel="stylesheet" href="../plugins/c3Chart/css/c3.css">

    <link rel="stylesheet" href="../plugins/c3Chart/css/c3.min.css">

    <!--Page Leve JS -->

    <script src="../plugins/c3Chart/js/d3.v3.min.js"></script>

    <script src="../plugins/c3Chart/js/c3.js"></script>

    <script src="../plugins/c3Chart/js/c3-demo.js"></script>





        







    

    <!-- Feature detection -->

    <script src="../js/vendor/modernizr-2.6.2.min.js"></script>

    



    

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



    

    

    

    

    

</head>



<body>

 <!-- <div id="contentLanguage">--> 

    <section id="main-wrapper" class="theme-default">

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

                <li class="hidden-xs" style="padding-top:13px">

                 <button id="ButtonVzdalenaPomoc" type="submit" onclick="window.location.href='https://www.hairsoft.cz/temp/UnisSoft_Hotline.exe'" class="btn btn-danger"><i class="fa fa-ambulance"></i>&nbsp&nbspVzdálená pomoc</button>&nbsp      

                 <?php 

                    //Demo

                      if ($_SESSION["k_id"]=="2") {

                        

                      }else{

                            ?>

                                <button id="ButtonManual" type="submit" onclick="window.open('https://doc.manualy.unissoft.cz/moduly/hairsoft-klient','_blank')" class="btn btn-danger"><i class="fa fa-book"></i>&nbsp&nbspManuál</button>       

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

                        <a href="index.php?strana=ZmenaObsluhy" title="Změna obsluhy">

                            <i class="fa fa-fw fa-retweet"></i>  Změna obsluhy

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

                            <i class="fa fa-fw fa-dashboard"></i>  Dashboard

                        </a>

                     </li>

                    <?php  

                  } 

                  ?>   





                  <?php  

                  //Dashboard

                    

                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("NovyZakaznik", $_SESSION["SeznamPravPoduzivatele"]))  ) {

                   ?> 

                     <li class="nav-dropdown">

                        <a href="index.php?strana=KartaOsoby" title="Nový zákazník">

                            <i class="fa fa-fw icon-user-follow"></i>  Nový zákazník

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

                                                                      <i class="fa fa-fw fa-building"></i>Pobočky

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



                                                                                   echo JePobockaOnlineMenu($idp,$mysqli)."&nbsp;&nbsp;".$na_pobocku["sw_jmeno_pobocky"]." ";

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

                        <a href="#" title="Zákazníci">

                            <i class="fa fa-fw icon-users"></i>  Zákazníci

                        </a>



                        <ul class="nav-sub">

                            <li>

                                <a href="index.php?strana=Zakaznici&AkceTab=Vsechny"  title="Seznam zákazníků">

                                     Seznam zákazníků

                                </a>

                            </li>

                            

                            <li>

                                <a href="index.php?strana=KartaOsoby" title="Nový zákazník">

                                     Nový zákazník

                                </a>

                            </li>                       

                        </ul>

                    </li>

                    <?php  

                  } 

                  ?>







                    

                    

                  <?php  

                  //rezervace

                  if ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Rezervace", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" )) {

                  //if (($_SESSION["JePoduzivatel"]=="1" and in_array("Rezervace", $_SESSION["SeznamPravPoduzivatele"]))  or ($_SESSION["JePoduzivatel"]=="0")) {

                   ?> 

                    <li class="nav-dropdown">

                        <a href="#"  title="Rezervace">

                            <i class="fa fa-fw  icon-calendar  "></i>  Rezervace

                        </a>

                        <ul class="nav-sub">

                            <li>

                                <a target="_blank" href="http://app.bonfero.com"  title="Administrace">

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

                            <i class="fa fa-fw icon-graph"></i>  Tržby

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

                            <i class="fa fa-fw  icon-layers "></i>  Sklad

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

                            <i class="fa fa-fw  icon-book-open  "></i>  Vouchery

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

                            <i class="fa fa-fw  icon-star"></i>  Hodnocení 

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

                            <i class="fa fa-fw  icon-speech"></i>  SMS a hovory

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

                            <i class="fa fa-fw icon-user-follow"></i>  Uživatelé

                        </a>



                        <ul class="nav-sub">

                            <li>

                                <a href="index.php?strana=Uzivatele" title="">

                                     Přehled uživatelů

                                </a>

                            </li>

                            

                            <li>

                                <a href="index.php?strana=Dochazka" title="Příchozí hovory">

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

                            <i class="fa fa-fw  fa-list-ul"></i>  Ceník

                        </a>



                          

                    </li>

                  <?php  

                  } 

                 } 

                  ?>











                  <?php  

                  //Nastaveni jen admin ne demo

                  

                  if ($_SESSION["JePoduzivatel"]=="0" and $_SESSION["JeObsluha"]=="0" and $_SESSION["pobocka_id"] > "0") {

                   ?>    

                    <li class="nav-dropdown">

                        <a target="_self" href="#"  title="Nastavení">

                            <i class="fa fa-fw icon-settings"></i>  Nastavení

                        </a>



                          <ul class="nav-sub">

                            <li>

                                <a href="index.php?strana=NastaveniPristupy" title="Přístupy">

                                     Přístupy

                                </a>

                            </li> 



                        </ul>

                    </li>

                  <?php  

                  } 

                  ?>



                  <?php  

                  //System Nastaveni jen ja a zdenda

                  

                  //echo $sw_id; 

                  if ( $sw_id== 5242 || $sw_id== 4181) {

                   ?>    

                    <li class="nav-dropdown">

                        <a target="_self" href="#"  title="Systémová nastavení">

                            <i class="fa fa-fw icon-settings"></i>  Systém

                        </a>



                          <ul class="nav-sub">

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



                    





                    

                    <li class="nav-dropdown">

                        <a href="logout.php"  title="Odhlásit">

                            <i class="fa fa-fw icon-logout"></i>  Odhlásit

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

                   





                    

                    <?php 

                        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["languageCombo"])) {

                            $selectedLanguage = $_POST["languageCombo"];

                            $_SESSION["languageCombo"] = $selectedLanguage;

                        } else {

                            $selectedLanguage = isset($_SESSION['languageCombo']) ? $_SESSION['languageCombo'] : 'cs';

                        }



                     ?>

                    

                   <!--  

                    <br><br>

                    <center>

                         <form method="post">

                                <select name="languageCombo" onchange="this.form.submit()">

                                    <option value="cs" <?php echo ($selectedLanguage === 'cs') ? 'selected' : ''; ?>>Čeština</option>

                                    <option value="en" <?php echo ($selectedLanguage === 'en') ? 'selected' : ''; ?>>Angličtina</option>

                                    <option value="de" <?php echo ($selectedLanguage === 'de') ? 'selected' : ''; ?>>Němčina</option>

                                    <option value="sk" <?php echo ($selectedLanguage === 'sk') ? 'selected' : ''; ?>>Slovenština</option>

                                </select>

                            </form>

                    </center>

                    -->







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

                                                    if (file_exists($filename)) {

                                                                          

                                                      require 'strana/'.$strana.'.php';



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

                      require 'strana/AktivniPlocha.php';  

                           $page = "https://klient.hairsoft.cz/str/index.php";

                           $sec = "600";

                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";

                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Zakaznici", $_SESSION["SeznamPravPoduzivatele"])) ) {    

                           //Zakaznici prvni v poradi

                      require 'strana/Zakaznici.php';  

                           $page = "https://klient.hairsoft.cz/str/index.php";

                           $sec = "600";

                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";

                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("NovyZakaznik", $_SESSION["SeznamPravPoduzivatele"])) ) {    

                           //Zakaznici novy zakaznik

                      require 'strana/KartaOsoby.php';  

                           $page = "https://klient.hairsoft.cz/str/index.php";

                           $sec = "600";

                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";

                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Trzby", $_SESSION["SeznamPravPoduzivatele"]))  ) {    

                      //Trzby denni 

                      require 'strana/CelkoveTrzby.php';  

                           $page = "https://klient.hairsoft.cz/str/index.php";

                           $sec = "600";

                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";

                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Sklad", $_SESSION["SeznamPravPoduzivatele"]))  ) {    

                      //Sklad

                      require 'strana/Sklad.php';  

                           $page = "https://klient.hairsoft.cz/str/index.php";

                           $sec = "600";

                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";

                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Voucher", $_SESSION["SeznamPravPoduzivatele"]))  ) {    

                      //Voucher

                      require 'strana/Voucher.php';  

                           $page = "https://klient.hairsoft.cz/str/index.php";

                           $sec = "600";

                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";

                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("SMS", $_SESSION["SeznamPravPoduzivatele"]))  ) {    

                      //SMS

                      require 'strana/Sms.php';  

                           $page = "https://klient.hairsoft.cz/str/index.php";

                           $sec = "600";

                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";

                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Cenik", $_SESSION["SeznamPravPoduzivatele"]))  ) {    

                      //SMS

                      require 'strana/Cenik.php';  

                           $page = "https://klient.hairsoft.cz/str/index.php";

                           $sec = "600";

                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";

                    

                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Hodnoceni", $_SESSION["SeznamPravPoduzivatele"]))  ) {    

                      //Hodnoceni

                      require 'strana/SpokojenostStatistika.php';  

                           $page = "https://klient.hairsoft.cz/str/index.php";

                           $sec = "600";

                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";

                    

                    }elseif ((($_SESSION["JePoduzivatel"]=="1" or $_SESSION["JeObsluha"]=="1") and in_array("Kamery", $_SESSION["SeznamPravPoduzivatele"]))  ) {    

                      //Kamery

                      require 'strana/NastaveniKamerovyDohled.php';  

                           $page = "https://klient.hairsoft.cz/str/index.php";

                           $sec = "600";

                           echo "<meta http-equiv=\"refresh\" content=\"$sec;URL=$page\">";

                    }else{

                      require 'strana/PrazdnaStrana.php';  

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



                                                                                                  <h4 class="sidebar-title"><font face="tahoma">Adresář klientů (<?php echo $radku_na_lidi_cnt;?>)</font></h4>

                                                                                                  <div id="contact-list-wrapper">

                                                                                                     <div class="heading">

                                                                                                          <ul>

                                                                                                              <li class="new-contact"><a href="index.php?strana=KartaOsoby"><i class="fa fa-plus"></i></a>

                                                                                                              </li>

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



                                                                                                                                                /*Profilovka*/

                                                                                                                                                $select_na_profilovka= "SELECT `obrazek_jmeno_GUID` FROM `klient_lidi_obrazky` WHERE `obrazek_smazan` = 0 and `obrazek_profilovka` = 1 and `obrazek_osoba_GUID` =  '".$lidi_guid."' limit 1";

                                                                                                                                                if (!$select_na_profilovka) { die('Chyba pripojeni do DB!');}

                                                                                                                                                $vysledek_na_profilovka=$mysqli->query("$select_na_profilovka");

                                                                                                                                                $dataprofilovka=MySQLi_Fetch_Array($vysledek_na_profilovka);



                                                                                                                                                $obrazek_jmeno_GUID = $dataprofilovka["obrazek_jmeno_GUID"];







                                                                                                                                             //Telefon

                                                                                                                                             $Mobil= $na_lidi["lidi_hs_cell"] ;

                                                                                                                                                   if ($Telefon=="" and $Mobil!="") {

                                                                                                                                                    $Tel = $Mobil;

                                                                                                                                                   }



                                                                                                                                                   if ($Mobil=="" and $Telefon!="") {

                                                                                                                                                    $Tel = $Telefon;

                                                                                                                                                   }



                                                                                                                                                   if ($Mobil!="" and $Telefon!="") {

                                                                                                                                                    $Tel = $Mobil;

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

                                                                                              echo "    <span class=\"avatar\">";

                                                                                              echo "     <img src=\"".$avatar."\" height=\"40\" class=\"img-circle_zakaznici_prave\" alt=\"\">";

                                                                                              echo "     <i class=\"on animated bounceIn\"></i>";

                                                                                              echo "    </span>";

                                                                                              echo "   </div>";

                                                                                              echo "   <div class=\"col-md-9\">";

                                                                                              echo "    <div class=\"name\">".$Prijmeni." ".$Jmeno."</div>";

                                                                                              echo "     <small class=\"location text-muted\">".$Tel."</small></br>";

                                                                                              echo "     <a style=\"color:black\" href=\"tel:$Tel\"><i class=\"icon-call-out\"></i>&nbsp;&nbsp;&nbsp;&nbsp;<i class=\"icon-envelope\"></i></a>";

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



    



    <script src="js/tmk_app.js?android=1"></script>





<link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css"> 

<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css"> 

    



    













    <script>

    /*

    Language zmena textu na en cliboardu do cz 

    https://datatables.net/extensions/buttons/examples/html5/copyi18n.html



    */ 

   



    $(document).ready(function() {



                $(document).ready(function () {

                    translateContent();

                });



             app.customCheckbox();

             

             //Disable tlacitko odeslat

             $("#btn_pridat_poduzivatele").prop("disabled",true);

             $("#k_poduzivatele_zmenit_heslo_ko").prop("disabled",true);



             $('#ZobrazitDivPoznamkyTimeline').bind('click', function() {

                $("#formPoznamky").show();      

                $("#ZobrazitDivPoznamkyTimeline").hide(); 

             });

             









             $('#table_CelkovySumarZaStrediska,#table_CelkoveTrzbyZaObsluhu,#table_RocniCelkoveTrzbyZaObsluhu,#table_MesicniSumarZaStrediska,#table_MesicniSumarZaJednotlivce,#table_DenniSumarZaJednotlivce,#table_DenniSumarZaStrediska').DataTable( {

                      

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

                                              //className: 'fa fa-file-excel-o'

                                           },

                                           {

                                              text: '<i class="fa fa-file-pdf-o"></i><b> PDF</b>',

                                              extend: 'pdfHtml5',

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

                                               title: '<?php echo $_SESSION["DochazkaTitle"]; ?>',

                                                /*

                                                customize: function ( pdf, btn, tbl ) {

                                                  delete pdf.styles.tableBodyOdd.fillColor;

                                                },

                                                */

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



            "searching": false,

            "paging":    false,

            "ordering":  false,

            "info":      false,

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

                      'copy',

                      'excel',

                      'csv',

                      'pdf',

                      'print'

                  ]



                     

              }

        ],

            "scrollX": true,

            "searching": true,

            "paging":    true,

            "aLengthMenu": [[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,-1], [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,'-1']],

            "iDisplayLength" : 10, //<?php echo $nastaveni_radku; ?>,

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

                      'copy',

                      'excel',

                      'csv',

                      'pdf',

                      'print'

                  ]



                     

              }

        ],

            "scrollX": true,

            "searching": true,

            "paging":    true,

            "aLengthMenu": [[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,-1], [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,'-1']],

            "iDisplayLength" : 10, //<?php echo $nastaveni_radku; ?>,

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

               if(data[10]== "1"){

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

                      'copy',

                      'excel',

                      'csv',

                      'pdf',

                      'print'

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

                  ,

                            {

                                visible: false,

                                searchable: false,

                            },

                            {}

                ],



              "columnDefs": [

                { "orderable": false, "targets": [] },

                { className: "text-right", "targets": [] },

                { className: "text-left", "targets": [] },

                { className: "text-center", "targets": [0,1,2,3,4,5,6,7,8,9] },





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

                      'copy',

                      'excel',

                      'csv',

                      'pdf',

                      'print'

                  ]



                     

              }

        ],

            "scrollX": true,

            "searching": true,

            "paging":    true,

            "aLengthMenu": [[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,-1], [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,'-1']],

            "iDisplayLength" : 10, //<?php echo $nastaveni_radku; ?>,

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

      "columns": [{ "width": "10%" },    null,    null,    { "width": "10%" }  ],

      "columnDefs": [

          { className: 'text-left', targets: [2, 3] },

          { className: 'text-center', targets: [0] },

        ],



   

            "scrollX": false,

            "searching": false,

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



  $('#table_prehledUzivateluObsluhaArchiv').dataTable( {

      "columns": [{ "width": "10%" },    null,    null,    { "width": "10%" }  ],

      "columnDefs": [

          { className: 'text-left', targets: [2, 3] },

          { className: 'text-center', targets: [0] },

        ],



   

            "scrollX": false,

            "searching": false,

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



        

 



</body>



</html>

