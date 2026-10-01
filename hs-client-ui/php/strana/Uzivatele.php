    
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

if (!defined('HS_CLIENT_UI_ROOT')) { define('HS_CLIENT_UI_ROOT', dirname(__DIR__, 3)); }
require HS_CLIENT_UI_ROOT . '/str/strana/_zabezpeceni.php';
require_once HS_CLIENT_UI_ROOT . '/fce/PosliEmail.php';    
require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';  

  //require '../fce/GeneratorBarev.php';
  //require_once '../../cfg/nastaveni.php';


$jmenoStranky = "Nastavení uživatelů ";
$jmenoStrankyPopis = "Správa uživatelů, přístupů a oprávnění";


if (!isset($_POST['VybranaPobockaID']) || is_array($_POST['VybranaPobockaID'])){$_POST['VybranaPobockaID']='';}
$VybranaPobockaID  =  htmlspecialchars($_POST['VybranaPobockaID'], ENT_COMPAT);

if (!isset($_GET['Obsluha_GUID']) || is_array($_GET['Obsluha_GUID'])){$_GET['Obsluha_GUID']='';}
$Obsluha_GUID  =  htmlspecialchars($_GET['Obsluha_GUID'], ENT_COMPAT);

if (!isset($_GET['PresunObsluhyDoArchivu']) || is_array($_GET['PresunObsluhyDoArchivu'])){$_GET['PresunObsluhyDoArchivu']='';}
$PresunObsluhyDoArchivu  =  htmlspecialchars($_GET['PresunObsluhyDoArchivu'], ENT_COMPAT);



if (!isset($_GET['ObnoveniObsluhyZarchivu']) || is_array($_GET['ObnoveniObsluhyZarchivu'])){$_GET['ObnoveniObsluhyZarchivu']='';}
$ObnoveniObsluhyZarchivu  =  htmlspecialchars($_GET['ObnoveniObsluhyZarchivu'], ENT_COMPAT);







if (!isset($_POST['Obsluha_GUID_POST']) || is_array($_POST['Obsluha_GUID_POST'])){$_POST['Obsluha_GUID_POST']='';}
$Obsluha_GUID_POST  =  htmlspecialchars($_POST['Obsluha_GUID_POST'], ENT_COMPAT);

if (!isset($_POST['smazat_obsluhu']) || is_array($_POST['smazat_obsluhu'])){$_POST['smazat_obsluhu']='';}
$smazat_obsluhu  =  htmlspecialchars($_POST['smazat_obsluhu'], ENT_COMPAT);

if (!isset($_POST['nezobrazovat_obsluhu']) || is_array($_POST['nezobrazovat_obsluhu'])){$_POST['nezobrazovat_obsluhu']='';}
$nezobrazovat_obsluhu  =  htmlspecialchars($_POST['nezobrazovat_obsluhu'], ENT_COMPAT);

if (!isset($_POST['nezobrazovat_obsluhu_stav']) || is_array($_POST['nezobrazovat_obsluhu_stav'])){$_POST['nezobrazovat_obsluhu_stav']='';}
$nezobrazovat_obsluhu_stav  =  htmlspecialchars($_POST['nezobrazovat_obsluhu_stav'], ENT_COMPAT);


/*Prava hodnopceni*/
if (!isset($_POST['ZmenaPravaHodnoceni']) || is_array($_POST['ZmenaPravaHodnoceni'])){$_POST['ZmenaPravaHodnoceni']='';}
$ZmenaPravaHodnoceni  =  htmlspecialchars($_POST['ZmenaPravaHodnoceni'], ENT_COMPAT);


if (!isset($_POST['chck_Hodnoceni']) || is_array($_POST['chck_Hodnoceni'])){$_POST['chck_Hodnoceni']='';}
$chck_Hodnoceni  =  htmlspecialchars($_POST['chck_Hodnoceni'], ENT_COMPAT);

if (!isset($_POST['chck_HodnoceniVsech']) || is_array($_POST['chck_HodnoceniVsech'])){$_POST['chck_HodnoceniVsech']='';}
$chck_HodnoceniVsech  =  htmlspecialchars($_POST['chck_HodnoceniVsech'], ENT_COMPAT);



if (!isset($_POST['k_id']) || is_array($_POST['k_id'])){$_POST['k_id']='';}
$k_id  =  htmlspecialchars($_POST['k_id'], ENT_COMPAT);

if (!isset($_POST['k_email']) || is_array($_POST['k_email'])){$_POST['k_email']='';}
$k_email  =  htmlspecialchars($_POST['k_email'], ENT_COMPAT);

if (!isset($_POST['ZmenaHeslaAdminaSystemu']) || is_array($_POST['ZmenaHeslaAdminaSystemu'])){$_POST['ZmenaHeslaAdminaSystemu']='';}
$ZmenaHeslaAdminaSystemu  =  htmlspecialchars($_POST['ZmenaHeslaAdminaSystemu'], ENT_COMPAT);

if (!isset($_POST['k_admin_heslo']) || is_array($_POST['k_admin_heslo'])){$_POST['k_admin_heslo']='';}
$k_admin_heslo  =  htmlspecialchars($_POST['k_admin_heslo'], ENT_COMPAT);




if ($Obsluha_GUID=="" and $Obsluha_GUID_POST!="") {
  $Obsluha_GUID = $Obsluha_GUID_POST;
}

if (!isset($_GET['Pobocka']) || is_array($_GET['Pobocka'])){$_GET['Pobocka']='';}
$PobockaID  =  htmlspecialchars($_GET['Pobocka'], ENT_COMPAT);

if (!isset($_GET['SmazaniObsluhyZeSystemu']) || is_array($_GET['SmazaniObsluhyZeSystemu'])){$_GET['SmazaniObsluhyZeSystemu']='';}
$SmazaniObsluhyZeSystemu  =  htmlspecialchars($_GET['SmazaniObsluhyZeSystemu'], ENT_COMPAT);


  if ($_SESSION["pobocka_jmeno"]=="") {
    $pobocka_jmeno =  "Nevybrána" ;
  }else {
    $pobocka_jmeno =  $_SESSION["pobocka_jmeno"] ;  
  }   
 

if ($VybranaPobockaID=="" and $PobockaID!="") {
  $VybranaPobockaID= $PobockaID;
}




/*

        //////////////////////////////////////////////////////////////////////////////////////
        //DEMO
          if ($_SESSION["poduzivatel_id"]=="") {
            $sw_id = 0;
            $skupina_id = 0;  
          }else{
            $sw_id =  $_SESSION["poduzivatel_id"];  
          }
/*
                            ___ ___                __   .__                
                           /   |   \_____    ____ |  | _|__| ____    ____  
                          /    ~    \__  \ _/ ___\|  |/ /  |/    \  / ___\ 
                          \    Y    // __ \\  \___|    <|  |   |  \/ /_/  >
                           \___|_  /(____  /\___  >__|_ \__|___|  /\___  / 
                                 \/      \/     \/     \/       \//_____/  
*/

if ($_SESSION["k_id"]=="10") {
    //$Uzivatel_ID =11;
   // $sw_id = 1081;
    //$sw_id = 2387;    //BS
      //$Uzivatel_ID =11;
      //$sw_id = 2722;
    //$skupina_id = 5;  // BarberCooper
    //$skupina_id = 84; // Thomas
   //$sw_id = 961;
}
//echo $sw_id;

        //////////////////////////////////////////////////////////////////////////////////////

$Uzivatel_ID =$_SESSION["k_id"];


//fmr prava
if (!isset($_POST['Prava_Dashboard']) || is_array($_POST['Prava_Dashboard'])){$_POST['Prava_Dashboard']='';}
$Prava_Dashboard  =  htmlspecialchars($_POST['Prava_Dashboard'], ENT_COMPAT);

if (!isset($_POST['Prava_Zakaznici']) || is_array($_POST['Prava_Zakaznici'])){$_POST['Prava_Zakaznici']='';}
$Prava_Zakaznici  =  htmlspecialchars($_POST['Prava_Zakaznici'], ENT_COMPAT);

if (!isset($_POST['Prava_Rezervace']) || is_array($_POST['Prava_Rezervace'])){$_POST['Prava_Rezervace']='';}
$Prava_Rezervace  =  htmlspecialchars($_POST['Prava_Rezervace'], ENT_COMPAT);

if (!isset($_POST['Prava_Trzby']) || is_array($_POST['Prava_Trzby'])){$_POST['Prava_Trzby']='';}
$Prava_Trzby  =  htmlspecialchars($_POST['Prava_Trzby'], ENT_COMPAT);

if (!isset($_POST['Prava_Sklad']) || is_array($_POST['Prava_Sklad'])){$_POST['Prava_Sklad']='';}
$Prava_Sklad  =  htmlspecialchars($_POST['Prava_Sklad'], ENT_COMPAT);

if (!isset($_POST['Prava_Voucher']) || is_array($_POST['Prava_Voucher'])){$_POST['Prava_Voucher']='';}
$Prava_Voucher  =  htmlspecialchars($_POST['Prava_Voucher'], ENT_COMPAT);

if (!isset($_POST['Prava_SMS']) || is_array($_POST['Prava_SMS'])){$_POST['Prava_SMS']='';}
$Prava_SMS  =  htmlspecialchars($_POST['Prava_SMS'], ENT_COMPAT);

if (!isset($_POST['Prava_Kamery']) || is_array($_POST['Prava_Kamery'])){$_POST['Prava_Kamery']='';}
$Prava_Kamery  =  htmlspecialchars($_POST['Prava_Kamery'], ENT_COMPAT);

if (!isset($_POST['Prava_Nastaveni']) || is_array($_POST['Prava_Nastaveni'])){$_POST['Prava_Nastaveni']='';}
$Prava_Nastaveni  =  htmlspecialchars($_POST['Prava_Nastaveni'], ENT_COMPAT);

if (!isset($_POST['Prava_NovyZakaznik']) || is_array($_POST['Prava_NovyZakaznik'])){$_POST['Prava_NovyZakaznik']='';}
$Prava_NovyZakaznik  =  htmlspecialchars($_POST['Prava_NovyZakaznik'], ENT_COMPAT);

if (!isset($_POST['Prava_Uzivatele']) || is_array($_POST['Prava_Uzivatele'])){$_POST['Prava_Uzivatele']='';}
$Prava_Uzivatele  =  htmlspecialchars($_POST['Prava_Uzivatele'], ENT_COMPAT);

if (!isset($_POST['Prava_Cenik']) || is_array($_POST['Prava_Cenik'])){$_POST['Prava_Cenik']='';}
$Prava_Cenik  =  htmlspecialchars($_POST['Prava_Cenik'], ENT_COMPAT);

if (!isset($_POST['Prava_Hodnoceni']) || is_array($_POST['Prava_Hodnoceni'])){$_POST['Prava_Hodnoceni']='';}
$Prava_Hodnoceni  =  htmlspecialchars($_POST['Prava_Hodnoceni'], ENT_COMPAT);


if (!isset($_POST['ZobrazitCitlivaData']) || is_array($_POST['ZobrazitCitlivaData'])){$_POST['ZobrazitCitlivaData']='';}
$ZobrazitCitlivaData  =  htmlspecialchars($_POST['ZobrazitCitlivaData'], ENT_COMPAT);




/**/

if (!isset($_POST['ZmenaKonkretnihoPravaObsluha']) || is_array($_POST['ZmenaKonkretnihoPravaObsluha'])){$_POST['ZmenaKonkretnihoPravaObsluha']='';}
$ZmenaKonkretnihoPravaObsluha  =  htmlspecialchars($_POST['ZmenaKonkretnihoPravaObsluha'], ENT_COMPAT);

if (!isset($_POST['ObsluhaSQLID']) || is_array($_POST['ObsluhaSQLID'])){$_POST['ObsluhaSQLID']='';}
$ObsluhaSQLID  =  htmlspecialchars($_POST['ObsluhaSQLID'], ENT_COMPAT);


/*zmena login - hesla*/
if (!isset($_POST['Obsluha_ID']) || is_array($_POST['Obsluha_ID'])){$_POST['Obsluha_ID']='';}
$Obsluha_ID  =  htmlspecialchars($_POST['Obsluha_ID'], ENT_COMPAT);

if (!isset($_POST['NastavitHesloObsluze']) || is_array($_POST['NastavitHesloObsluze'])){$_POST['NastavitHesloObsluze']='';}
$NastavitHesloObsluze  =  htmlspecialchars($_POST['NastavitHesloObsluze'], ENT_COMPAT);

if (!isset($_POST['k_poduzivatele_jmeno']) || is_array($_POST['k_poduzivatele_jmeno'])){$_POST['k_poduzivatele_jmeno']='';}
$k_poduzivatele_jmeno  =  htmlspecialchars($_POST['k_poduzivatele_jmeno'], ENT_COMPAT);


if (!isset($_POST['k_poduzivatele_login_puvodni']) || is_array($_POST['k_poduzivatele_login_puvodni'])){$_POST['k_poduzivatele_login_puvodni']='';}
$k_poduzivatele_login_puvodni  =  htmlspecialchars($_POST['k_poduzivatele_login_puvodni'], ENT_COMPAT);


if (!isset($_POST['k_poduzivatele_login']) || is_array($_POST['k_poduzivatele_login'])){$_POST['k_poduzivatele_login']='';}
$k_poduzivatele_login  =  htmlspecialchars($_POST['k_poduzivatele_login'], ENT_COMPAT);

if (!isset($_POST['k_poduzivatele_heslo']) || is_array($_POST['k_poduzivatele_heslo'])){$_POST['k_poduzivatele_heslo']='';}
$k_poduzivatele_heslo  =  htmlspecialchars($_POST['k_poduzivatele_heslo'], ENT_COMPAT);


if (!isset($_GET['Obsluha_ID_GET']) || is_array($_GET['Obsluha_ID_GET'])){$_GET['Obsluha_ID_GET']='';}
$Obsluha_ID_GET  =  htmlspecialchars($_GET['Obsluha_ID_GET'], ENT_COMPAT);

if (!isset($_POST['ImportObsluhyZHS']) || is_array($_POST['ImportObsluhyZHS'])){$_POST['ImportObsluhyZHS']='';}
$ImportObsluhyZHS  =  htmlspecialchars($_POST['ImportObsluhyZHS'], ENT_COMPAT);

if (!isset($_POST['k_obsluha_refersh_sw_id']) || is_array($_POST['k_obsluha_refersh_sw_id'])){$_POST['k_obsluha_refersh_sw_id']='';}
$k_obsluha_refersh_sw_id  =  htmlspecialchars($_POST['k_obsluha_refersh_sw_id'], ENT_COMPAT);


/*
if ($Obsluha_GUID =="" and  $ObsluhaSQLID!=""){
  $Obsluha_GUID = $ObsluhaSQLID;
}
*/








//Zmena hesla administratora
if($ZmenaHeslaAdminaSystemu=="1" and $k_id!="" and $k_admin_heslo!="" and $k_email!=""){
      //$Uzivatel_GUID = $Uzivatel_POST_GUID;

                  //email
                     $Sablona = "<div>
                                    <span style=\" font-family:Tahoma, Arial, sans-serif; font-size: 19pt;\"><b>HairSoft - Klient </b></span><br><br>
                                    <span style=\" font-size: 10pt;\">Zasíláme nové přihlašovací údaje do systému.</span>
                                        <br>
                                        <br>Link na klientský web: 
                                        <a href=\"https://klient.hairsoft.cz\" target=\"_self\"><b>klient.hairsoft.cz</b></a>
                                        <br>Přihlašovací email: <b>".$k_email."</b>
                                        <br>Přihlašovací heslo: <b>".$k_admin_heslo."</b>
                                        <br>
                                        <br>
                                        <span style=\" color: #333333;\"><b>UnisSoft s.r.o.</b> | produkt HairSoft</span>
                                </div>"; 
                                
                                  
                                  PosliEmail($k_email,"Změna hesla - HairSoft Klient",$Sablona);
                                  
                                  $sql = "UPDATE `k_uzivatele` SET `k_heslo` = '".SHA1($k_admin_heslo)."' WHERE `k_id` = '".$k_id."'; ";
                                  
                                  $vysledek_zalozeni = @$mysqli->query($sql);
                     
                                  if ($vysledek_zalozeni) {
                                     //echo $sql;
                                     $_SESSION["provedena_zmena"] = "Změna nového hesla byla úspěšně provedena.";
                                   }else{
                                     $error = $mysqli->error; 
                                      echo $error; 
                                      return;
                                  }
}





























// FUNKCE
function UlozPravaObsluha($ObsluhaID,$PobockaID,$PravoText,$PravoPrommenna,$mysqli) {
    
    //Dashboard
   if ($PravoPrommenna=="1") {
       $sql_existuje_pravo= "SELECT * FROM `k_poduzivatele_prava` WHERE `prava_uzivatel_menu` = '".$PravoText."' and `pobocka_id` = ".$PobockaID." and `prava_uzivatel_pravo` = 1 and `k_poduzivatele_je_obsluha` = 1 and `k_poduzivatele_id_obsluha` = $ObsluhaID";
       //echo $sql_existuje_pravo;
       $vysledek_existuje_pravo=$mysqli->query($sql_existuje_pravo);
       $radku_existuje_pravo=$vysledek_existuje_pravo->num_rows; 
         if ($radku_existuje_pravo=="0") {
            //Nema pravo v db - Insert

                $sql = "INSERT INTO `k_poduzivatele_prava` (`prava_id`, `k_poduzivatele_id`, `prava_uzivatel_menu`, `prava_uzivatel_pravo`, `pobocka_id`, `k_poduzivatele_je_obsluha`, `k_poduzivatele_id_obsluha`) VALUES (NULL, NULL, '".$PravoText."', 1, ".$PobockaID.", 1, '$ObsluhaID');";
           //     echo $sql;
                $vysledek_zalozeni = @$mysqli->query($sql);
   
                if ($vysledek_zalozeni) {
                   return;
                 }else{
                   $error = $mysqli->error; 
                    echo $error; 
                    return;
                }

         }else{
            //Existuje nastav - Update
            // neni potreba nehrajeme na vypnuto pouze na zapnuto.
          return;
         }
   }else{
    //odstranit pravo - delete

                $sql = "DELETE FROM `k_poduzivatele_prava` WHERE  `prava_uzivatel_menu` = '".$PravoText."' and `pobocka_id` = ".$PobockaID." and `prava_uzivatel_pravo` = '1' and `k_poduzivatele_je_obsluha` = 1 and `k_poduzivatele_id_obsluha` = $ObsluhaID;";
         //       echo $sql;

                $vysledek_smazani = @$mysqli->query($sql);
   
                if ($vysledek_smazani) {
                   return;
                 }else{
                   $error = $mysqli->error; 
                    echo $error; 
                    return;
                }

   }


}



/*Zmena prav hodnoceni*/
if ($ZmenaPravaHodnoceni=="1" and $ObsluhaSQLID!="") {

        if ($chck_HodnoceniVsech=="") {
            $chck_HodnoceniVsech = "0";
        }

        if ($chck_Hodnoceni=="") {
            $chck_Hodnoceni = "0";
            $chck_HodnoceniVsech = "0";
        }

        

   $sql = "UPDATE `k_poduzivatele_obsluha` SET `k_poduzivatele_zobrazit_hodnoceniHS` = ".$chck_Hodnoceni.", `k_poduzivatele_zobrazit_hodnoceni_vsech` = ".$chck_HodnoceniVsech." WHERE `k_poduzivatele_obsluha`.`k_poduzivatele_obsluha_id` = ".$ObsluhaSQLID;
   $vysledek_update = @$mysqli->query($sql);
   //echo $sql;

}    







//zmena pobocky
if ($ZmenaKonkretnihoPravaObsluha=="1" and $ObsluhaSQLID!="" and $VybranaPobockaID!="") {
   $Uzivatel_GUID = $Uzivatel_POST_GUID;
  
   UlozPravaObsluha($ObsluhaSQLID,$VybranaPobockaID,"Dashboard",$Prava_Dashboard,$mysqli);
   UlozPravaObsluha($ObsluhaSQLID,$VybranaPobockaID,"Zakaznici",$Prava_Zakaznici,$mysqli);
   UlozPravaObsluha($ObsluhaSQLID,$VybranaPobockaID,"Rezervace",$Prava_Rezervace,$mysqli);
   UlozPravaObsluha($ObsluhaSQLID,$VybranaPobockaID,"Trzby",$Prava_Trzby,$mysqli);
   UlozPravaObsluha($ObsluhaSQLID,$VybranaPobockaID,"Sklad",$Prava_Sklad,$mysqli);
   UlozPravaObsluha($ObsluhaSQLID,$VybranaPobockaID,"Voucher",$Prava_Voucher,$mysqli);
   UlozPravaObsluha($ObsluhaSQLID,$VybranaPobockaID,"SMS",$Prava_SMS,$mysqli);
   UlozPravaObsluha($ObsluhaSQLID,$VybranaPobockaID,"Kamery",$Prava_Kamery,$mysqli);
   UlozPravaObsluha($ObsluhaSQLID,$VybranaPobockaID,"NovyZakaznik",$Prava_NovyZakaznik,$mysqli);
   UlozPravaObsluha($ObsluhaSQLID,$VybranaPobockaID,"Uzivatele",$Prava_Uzivatele,$mysqli);
   UlozPravaObsluha($ObsluhaSQLID,$VybranaPobockaID,"Cenik",$Prava_Cenik,$mysqli);
   UlozPravaObsluha($ObsluhaSQLID,$VybranaPobockaID,"Hodnoceni",$Prava_Hodnoceni,$mysqli);


   //zmena citlivych dat
   
   
   
   //nevim proc u chceboxu je 0  prazdna hodnota
   if ($ZobrazitCitlivaData=="1" or $ZobrazitCitlivaData=="") {
     $ZobrazitCitlivaData = 0; 
   }else{
     $ZobrazitCitlivaData = 1;
   }
   

   $sql = "UPDATE `k_poduzivatele_obsluha` SET `k_poduzivatele_skryt_citliva_data` = '".$ZobrazitCitlivaData."' WHERE `k_poduzivatele_obsluha`.`k_poduzivatele_obsluha_id` = ".$ObsluhaSQLID;
   $vysledek_update = @$mysqli->query($sql);
   
   

   //UlozPrava($k_poduzivatele_id,$VybranaPobockaID,"Nastaveni",$Prava_Nastaveni,$mysqli);
} 





//AKCE
/*
Obsluha_ID
Obsluha_GUID_POST
NastavitHesloObsluze
k_poduzivatele_jmeno
k_poduzivatele_login
k_poduzivatele_heslo
*/

      


      if ($ImportObsluhyZHS=="1" and $k_obsluha_refersh_sw_id!="") {
                          
                                  $sql = "INSERT INTO `k_obsluha_refersh` (`k_obsluha_refersh_id`, `k_obsluha_refersh_sw_id`, `k_obsluha_refersh_datum`) VALUES (NULL, ".$k_obsluha_refersh_sw_id.", CURRENT_TIMESTAMP);";
                                  $vysledek_zalozeni_refresh = @$mysqli->query($sql);
                     
                                  if ($vysledek_zalozeni_refresh) {
                                     //echo $sql;
                                     
                                     echo "<script type='text/javascript'>alert('Váš požadavek na import obsluh byl zadán. Čekejte prosím a poté aktualizujte webovou stránku.');</script>";
                                     echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=Uzivatele\">";
                                     return;
                                   }else{
                                     $error = $mysqli->error; 
                                      echo $error; 
                                      return;
                                  }
                       
      }




/*SMAZAT Obsluhu*/
/*
PobockaID
Obsluha_GUID
SmazaniObsluhyZeSystemu
Obsluha_ID_GET

*/

if ($SmazaniObsluhyZeSystemu=="1" and $Obsluha_GUID!="" and $Obsluha_ID_GET!=""  )  {

                //Smazat obrazek obsluhy
                $CestaIMG = "../img/obsluhy/";
                
                @unlink($CestaIMG.$Obsluha_GUID_POST.".PNG");
                @unlink($CestaIMG.$Obsluha_GUID_POST.".png");

                @unlink($CestaIMG.$Obsluha_GUID_POST.".JPG");
                @unlink($CestaIMG.$Obsluha_GUID_POST.".jpg");

                @unlink($CestaIMG.$Obsluha_GUID_POST.".bmp");
                @unlink($CestaIMG.$Obsluha_GUID_POST.".BMP");


                $sql = "DELETE FROM `k_poduzivatele_prava` WHERE  `k_poduzivatele_id_obsluha` =".$Obsluha_ID_GET;
                $vysledek_smazani = @$mysqli->query($sql);

                $sql2 = "DELETE FROM `k_poduzivatele_obsluha` WHERE `k_poduzivatele_obsluha_hash` = '".$Obsluha_GUID."'";
                $vysledek_smazani2 = @$mysqli->query($sql2);
                
                if ($vysledek_smazani and $vysledek_smazani) {
                   echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=Uzivatele&Pobocka=".$PobockaID."\">";

                   return;
                 }else{
                   $error = $mysqli->error; 
                    echo $error; 
                    return;
                }

                
}

if ($PresunObsluhyDoArchivu=="1" and $Obsluha_GUID!="" and $Obsluha_ID_GET!=""  )  {
                
                $sql = "UPDATE `k_poduzivatele_obsluha` SET `k_poduzivatele_archivace_obsluhy` = '0' WHERE `k_poduzivatele_obsluha`.`k_poduzivatele_obsluha_id` = ".$Obsluha_ID_GET;
                $vysledek_smazani = @$mysqli->query($sql);
            
                if ($vysledek_smazani ) {
                   echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=Uzivatele&Pobocka=".$PobockaID."\">";

                   return;
                 }else{
                   $error = $mysqli->error; 
                    echo $error; 
                    return;
                }

                
}

if ($ObnoveniObsluhyZarchivu=="1" and $Obsluha_GUID!="" and $Obsluha_ID_GET!=""  )  {
                
                $sql = "UPDATE `k_poduzivatele_obsluha` SET `k_poduzivatele_archivace_obsluhy` = '1' WHERE `k_poduzivatele_obsluha`.`k_poduzivatele_obsluha_id` = ".$Obsluha_ID_GET;
                $vysledek_smazani = @$mysqli->query($sql);
            
                if ($vysledek_smazani ) {
                   echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=Uzivatele&Pobocka=".$PobockaID."\">";

                   return;
                 }else{
                   $error = $mysqli->error; 
                    echo $error; 
                    return;
                }

                
}


if ($nezobrazovat_obsluhu=="1" and $Obsluha_GUID_POST!="" and $Obsluha_ID!="" and $nezobrazovat_obsluhu_stav !="" )  {
//echo "******************************************************";
                
                    if ($nezobrazovat_obsluhu_stav =="1") {
                        $StavSQL = "0";       
                    }elseif ($nezobrazovat_obsluhu_stav =="0") {
                        $StavSQL = "1";           
                    }else{
                        $StavSQL = "1";       
                    }

                 


                 $sql = "UPDATE `k_poduzivatele_obsluha` SET `k_obsluha_moznostPrehlaset` = ".$StavSQL." WHERE `k_poduzivatele_obsluha_hash` = '".$Obsluha_GUID_POST."'; ";
                    
                                    
                    $vysledek_zalozeni = @$mysqli->query($sql);
       
                    if ($vysledek_zalozeni) {
                       $navratPobocka = ($VybranaPobockaID !== '') ? '&Pobocka='.rawurlencode($VybranaPobockaID) : '';
                       $navratObsluha = '&Obsluha_GUID='.rawurlencode($Obsluha_GUID_POST).'&Obsluha_ID_GET='.rawurlencode($Obsluha_ID);
                       echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=Uzivatele".$navratPobocka.$navratObsluha."\">";
                       return;
                     }else{
                       $error = $mysqli->error; 
                        echo $error; 
                        return;
                    }
                
}






//Zmena loginu hesla
if($NastavitHesloObsluze=="1" and $Obsluha_GUID_POST!="" and $k_poduzivatele_heslo!="" and $k_poduzivatele_login!=""){
      $Uzivatel_GUID = $Obsluha_GUID_POST;

             //Kontrola zda neexistuje v db login
             $select_na_lidi= "SELECT * FROM `k_poduzivatele_obsluha` WHERE `k_poduzivatele_obsluha_login` = '".$k_poduzivatele_login."'";
             if (!$select_na_lidi) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

              $vysledek_na_lidi=$mysqli->query($select_na_lidi);
              $radku_na_lidi=$vysledek_na_lidi->num_rows;   

              /*Pokud je login stejny jako predtim tak jen ulozit heslo*/                                    
              if ($k_poduzivatele_login_puvodni==$k_poduzivatele_login) {
                $radku_na_lidi = 0; 
              }
                                                                                                                                        
               
               if ($radku_na_lidi> 0) {
                    $_SESSION["provedena_zmena_chyba"] = "Toto přihlašovací jméno již existuje. Zvolte jiné.";

               }else{

                    $sql = "UPDATE `k_poduzivatele_obsluha` SET `k_poduzivatele_obsluha_heslo` = '".SHA1($k_poduzivatele_heslo)."',  `k_poduzivatele_obsluha_login` = '".$k_poduzivatele_login."'
                    WHERE `k_poduzivatele_obsluha_hash` = '".$Uzivatel_GUID."'; ";
                    $vysledek_zalozeni = @$mysqli->query($sql);
       
                    if ($vysledek_zalozeni) {
                       //echo $sql;
                       $_SESSION["provedena_zmena"] = "Změna loginu a hesla byla úspěšně provedena.";
                     }else{
                       $error = $mysqli->error; 
                        echo $error; 
                        return;
                    }

               }

                                  
                
}




?>


<section id="main-content" class="main-content-wrapper hs-users-page">            
  <div class="pageheader">                
    <h1><?php echo $jmenoStranky ; ?>
        <b> 
            <?php 
                  /*  
                    //kdyz je vybrana obsluha tak nezobrazovat admina ale jmeno obsluhy
                    if ( $Obsluha_GUID !="") {
                       $sqldotazObsluha_nadpis= "SELECT * FROM `k_poduzivatele_obsluha` WHERE `k_poduzivatele_obsluha_hash` = '$Obsluha_GUID'";
                       if (!$sqldotazObsluha_nadpis) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}
                       $vysledekObsluha_nadpis=$mysqli->query("$sqldotazObsluha_nadpis");
                       $VystupObsluha_nadpis=MySQLi_Fetch_Array($vysledekObsluha_nadpis);
                       
                       echo $VystupObsluha_nadpis["k_poduzivatele_obsluha_jmeno"];
                    }else{
                       echo $_SESSION["uzivatel_prijmeni_jmeno"];  
                    }
                    */
            ?>
        </b>
    </h1>                
    <p class="description">
      <?php echo $jmenoStrankyPopis; ?>
    </p>                
    <div class="breadcrumb-wrapper hidden-xs">                    
      <span class="label">Pobočka:</span>                    
      <ol class="breadcrumb">                        
        <li class="active"><b><?php echo $pobocka_jmeno;?> </b><?php echo JePobockaOnline($sw_id,$mysqli) ?>  </li> <font color='#999' size='1'><?php echo "&nbsp; &nbsp; SW ID: ".$sw_id; ?></font>
      </ol>
    </div>
  </div>      


                 <?php
                
                  if ($_SESSION["provedena_zmena"]!="" or $_SESSION["provedena_zmena_chyba"]!="") {
                   
                        if ($_SESSION["provedena_zmena_chyba"]!="") {
                        /*chyba*/
                          $panel = "panel panel-danger";
                          $_SESSION["provedena_zmena"] = $_SESSION["provedena_zmena_chyba"];
                          $_SESSION["provedena_zmena_chyba"] = "";
                        }else{
                          $panel = "panel panel-info";
                        }

                   ?>


                    
                    <br>
                    <div class="col-md-12">
                        <div class="<?php echo $panel ?>">
                            <div class="panel-heading">
                                <h3 class="panel-title">Informace</h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                                        <b>
                                          <?php
                                            echo $_SESSION["provedena_zmena"];
                                            $_SESSION["provedena_zmena"]="";
                                          ?>
                                        </b>

                            </div>
                        </div>
                    </div>
                    


                   <?php 
                  }


                                 /*
                                 $select_na_import= "SELECT * FROM `k_obsluha_refersh` WHERE `k_obsluha_refersh_sw_id` =".$VybranaPobockaID;
                                 if (!$select_na_import) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                 $vysledek_na_import=$mysqli->query($select_na_import);
                                 $radku_na_import=$vysledek_na_import->num_rows;   


                                  if ($radku_na_import> 0) {
                                    
                                        <div class="alert alert-success alert-dismissable">
                                          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                          Váš požadavek na import obsluh byl zadán. Čekejte prosím a poté aktualizujte webovou stránku.
                                        </div>
                                    
                                  }
                                  */
                             

                ?>

                



  
            








            <div class="row">
              <div class="col-md-12">

                        <div class="panel panel-default" style="height: 140px;">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Výběr pobočky</b></font></h3>
                                
                            </div>
                            <div class="panel-body">
                                <div class="form-group">



                                <form action="index.php?strana=Uzivatele" method="POST" class="form-horizontal form-border" name="ZmenaPobockyPravaForm" id="ZmenaPobockyPravaForm">
                                    <input type="hidden" class="form-control" name="Uzivatel_POST_GUID" value="<?php echo $Uzivatel_GUID; ?>">
                                    <input type="hidden" class="form-control" name="ZmenaPobockyPrava" value="1">

                                      <select class="form-control input-lg" name="VybranaPobockaID" id="VybranaPobockaID" >
                                        <?php 
                                              //Zjisteni poctu vyplnenych emailu
                                              if ($_SESSION["k_email"]!="") {
                                                  $s_email = $_SESSION["k_email"];
                                                 
                                                 $select_na_pobocku= "SELECT `sw_info`.`sw_jmeno_pobocky`, `sw_info`.`sw_id` FROM `sw_email_pobocka` join `k_uzivatele` on `sw_email_pobocka`.`k_id` = `k_uzivatele`.`k_id` join `sw_info` on `sw_info`.`sw_id` = `sw_email_pobocka`.`sw_id` WHERE `k_uzivatele`.`k_email` = '$s_email'";
                                                 if (!$select_na_pobocku) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

                                                  $vysledek_na_pobocku=$mysqli->query($select_na_pobocku);
                                                  $radku_na_pobocku=$vysledek_na_pobocku->num_rows;                                       
                                                    
                                                    $prvni="1";

                                                    while ($na_pobocku=MySQLi_Fetch_Array($vysledek_na_pobocku)):   
                                                             
                                                        if ($VybranaPobockaID=="" and $prvni=="1") {
                                                           $VybranaPobockaID = $na_pobocku["sw_id"];
                                                           $prvni="";
                                                        }                    

                                                          if ($VybranaPobockaID==$na_pobocku["sw_id"]) {
                                                            $selected="selected=\"selected\"";
                                                          }else{
                                                            $selected="";
                                                          }

                                                        //pobočka getem
                                                        if ($PobockaID==$na_pobocku["sw_id"]) {
                                                            $selected="selected=\"selected\"";
                                                        }
                                                          

                                                      echo "<option ".$selected." value=\"".$na_pobocku["sw_id"]."\">".$na_pobocku["sw_jmeno_pobocky"]."</option>";
                                                                                                              
                                                    endwhile;
                                                }  
                                         ?>
                                       </select>
                                                   <input type="submit" name="submit" value="Submit" style="display: none" >
                                 </form>

                                </div>
                            </div>
                        </div>
                </div>

                    





              </div>









            <?php 
 
                        if ($Obsluha_GUID!="") {
                       
                          //Nacist hodnoty o Oblsuze

                           $sqldotazObsluha= "SELECT * FROM `k_poduzivatele_obsluha` WHERE `k_poduzivatele_obsluha_hash` = '$Obsluha_GUID'";
                           if (!$sqldotazObsluha) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}
                           $vysledekObsluha=$mysqli->query("$sqldotazObsluha");
                           $VystupObsluha=MySQLi_Fetch_Array($vysledekObsluha);
                           //echo $sms_fronta[sw_skupina_id];
                           $ObsluhaSQLID = $VystupObsluha["k_poduzivatele_obsluha_id"];
                           $JmenoObsluhy = $VystupObsluha["k_poduzivatele_obsluha_jmeno"];
                             $JmenoLogin = $VystupObsluha["k_poduzivatele_obsluha_login"];

                             /*Prava ba hodnoceni*/
                             $k_poduzivatele_zobrazit_hodnoceniHS = $VystupObsluha["k_poduzivatele_zobrazit_hodnoceniHS"];
                             $k_poduzivatele_zobrazit_hodnoceni_vsech = $VystupObsluha["k_poduzivatele_zobrazit_hodnoceni_vsech"];

                             $k_poduzivatele_skryt_citliva_data = $VystupObsluha["k_poduzivatele_skryt_citliva_data"];
                             $k_obsluha_moznostPrehlaset = isset($VystupObsluha["k_obsluha_moznostPrehlaset"]) ? (string)$VystupObsluha["k_obsluha_moznostPrehlaset"] : "0";
                             $k_poduzivatele_obsluha_foto = $VystupObsluha["k_poduzivatele_obsluha_foto"];
                             




             ?>

<div class="row hs-users-detail-native-v156">
  <div class="col-md-12">
    <div class="panel panel-default hs-users-account-native-v153 hs-users-account-native-v156">
      <div class="panel-heading">
        <h3 class="panel-title"><font face="tahoma"><b>Detail obsluhy: <I><?php echo $JmenoObsluhy; ?></I></b></font></h3>
        <div class="actions pull-right"></div>
      </div>

      <div class="panel-body hs-users-detail-body-v156">
        <?php
          if ($k_poduzivatele_obsluha_foto=="") {
              $k_poduzivatele_obsluha_foto = "no_image.png";
          }
          $k_poduzivatele_obsluha_foto= "../img/obsluhy/".$k_poduzivatele_obsluha_foto;
        ?>

        <div class="hs-users-detail-layout-v156">
          <section class="hs-users-account-card-v156">
            <div class="hs-users-identity-v156">
              <div class="OrizlaKulataFotkaObal hs-users-avatar-v156">
                <div class="OrizlaKulataFotka" style="background-image: url('<?php echo $k_poduzivatele_obsluha_foto; ?>');"></div>
              </div>
              <div class="hs-users-identity-copy-v156">
                <strong class="hs-users-identity-name-v156"><?php echo $JmenoObsluhy; ?></strong>
                <span class="hs-users-identity-role-v156">Obsluha</span>
              </div>
            </div>

            <div class="hs-users-detail-subtitle-v156">Přístupové údaje</div>
            <form action="index.php?strana=Uzivatele" method="POST" class="hs-users-access-form-v156">
              <input type="hidden" class="form-control" name="Obsluha_ID" value="<?php echo $ObsluhaSQLID; ?>">
              <input type="hidden" class="form-control" name="Obsluha_GUID_POST" value="<?php echo $Obsluha_GUID; ?>">
              <input type="hidden" class="form-control" name="VybranaPobockaID" value="<?php echo $VybranaPobockaID; ?>">
              <input type="hidden" class="form-control" name="k_poduzivatele_login_puvodni" value="<?php echo $JmenoLogin; ?>">
              <input type="hidden" class="form-control" name="NastavitHesloObsluze" value="1">

              <div class="hs-users-access-fields-v156">
                <div class="hs-users-field-v156">
                  <label for="k_poduzivatele_jmeno">Login / Jméno uživatele</label>
                  <input class="form-control" type="text" disabled="disabled" id="k_poduzivatele_jmeno" name="k_poduzivatele_jmeno" value="<?php echo $JmenoObsluhy; ?>">
                  <label style="display: none" id="k_poduzivatele_jmeno_ko" class="error">Pole musí být vyplněno!</label>
                </div>

                <div class="hs-users-internal-login-v154" style="display: none;">
                  <label for="k_poduzivatele_login">Login</label>
                  <input class="form-control" type="text" id="k_poduzivatele_login" name="k_poduzivatele_login" value="<?php echo $JmenoLogin; ?>">
                  <label style="display: none" id="k_poduzivatele_login_ko" class="error">Login musí mít alespoň 3 znaky.</label>
                </div>

                <div class="hs-users-field-v156">
                  <label for="k_poduzivatele_heslo">Heslo</label>
                  <input class="form-control" type="password" placeholder="Min 4 znaky" name="k_poduzivatele_heslo" id="k_poduzivatele_heslo" value="">
                </div>
              </div>

              <div class="hs-users-save-row-v156">
                <button type="submit" id="btn_pridat_poduzivatele" class="btn btn-primary">Uložit údaje</button>
              </div>
            </form>

            <form action="index.php?strana=Uzivatele" method="POST" class="hs-users-switch-access-form-v172">
              <input type="hidden" name="Obsluha_ID" value="<?php echo $ObsluhaSQLID; ?>">
              <input type="hidden" name="Obsluha_GUID_POST" value="<?php echo $Obsluha_GUID; ?>">
              <input type="hidden" name="VybranaPobockaID" value="<?php echo $VybranaPobockaID; ?>">
              <input type="hidden" name="nezobrazovat_obsluhu" value="1">
              <input type="hidden" name="nezobrazovat_obsluhu_stav" value="<?php echo ($k_obsluha_moznostPrehlaset === '1') ? '1' : '0'; ?>">
              <label class="hs-users-switch-access-row-v172" for="hs_obsluha_moznost_prehlasit">
                <span class="hs-users-switch-access-copy-v172">
                  <strong>Povolit přepnutí na tuto obsluhu</strong>
                  <small>Obsluha se zobrazí v nabídce Změna obsluhy.</small>
                </span>
                <span class="hs-users-native-switch-v172">
                  <input type="checkbox" id="hs_obsluha_moznost_prehlasit" <?php echo ($k_obsluha_moznostPrehlaset === '1') ? 'checked' : ''; ?> onchange="this.form.submit();">
                  <span class="hs-users-native-switch-track-v172" aria-hidden="true"><span></span></span>
                </span>
              </label>
            </form>
          </section>

          <section class="hs-users-rating-card-v156">
            <div class="hs-users-detail-subtitle-v156">Nastavení hodnocení</div>
            <form action="index.php?strana=Uzivatele" method="POST" name="ZmenaPobockyPravaHodnoceni" id="ZmenaPobockyPravaHodnoceni">
              <input type="hidden" class="form-control" name="ObsluhaSQLID" value="<?php echo $ObsluhaSQLID; ?>">
              <input type="hidden" class="form-control" name="Obsluha_GUID_POST" value="<?php echo $Obsluha_GUID; ?>">
              <input type="hidden" class="form-control" name="VybranaPobockaID" value="<?php echo $VybranaPobockaID; ?>">
              <input type="hidden" class="form-control" name="ZmenaPravaHodnoceni" value="1">

              <div class="hs-users-rating-list-v156">
                <label class="hs-users-rating-row-v156" for="chck_Hodnoceni">
                  <span>Zobrazit hodnocení v programu HairSoft</span>
                  <?php
                    if ($k_poduzivatele_zobrazit_hodnoceniHS == "1") {
                        $checked="checked";
                        $diabledDruheho = "";
                    }else{
                        $checked="";
                        $diabledDruheho = " disabled ";
                    }
                  ?>
                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck_Hodnoceni" name="chck_Hodnoceni" value="1">
                </label>

                <label class="hs-users-rating-row-v156" for="chck_HodnoceniVsech">
                  <span>Zobrazit hodnocení všech obsluh</span>
                  <?php
                    if ($k_poduzivatele_zobrazit_hodnoceni_vsech == "1") {
                        $checked="checked";
                    }else{
                        $checked="";
                    }
                  ?>
                  <input type="checkbox" <?php echo $diabledDruheho; ?> class="js-switch" <?php echo $checked ?> id="chck_HodnoceniVsech" name="chck_HodnoceniVsech" value="1">
                </label>
              </div>
              <input type="submit" name="submit" value="Submit" style="display: none">
            </form>
          </section>
        </div>
      </div>
    </div>
  </div>
          
                <?php 
                }
                ?>



            
        <?php 
        if ($Obsluha_GUID!="") {
         ?>
            
               <div class="col-md-12 hs-users-permissions-native-v153 hs-users-permissions-native-v154">


                        <div class="panel panel-default hs-users-permissions-panel-v154">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Práva na menu pro obsluhu: <I><?php echo $JmenoObsluhy; ?></I> </b></font></h3>
                                
                            </div>
                            <div class="panel-body hs-users-permissions-body-v154" style="min-height: 330px;">
                                <div class="form-group">


                              <?php 
                              //Nacteni prav do pole
                                                                


                                                                            //echo $VybranaPobockaID;
                                                                            $select_na_pravo= "SELECT `prava_uzivatel_menu` FROM `k_poduzivatele_prava` WHERE 
                                                                            `k_poduzivatele_je_obsluha` = 1 and `pobocka_id` = ".$VybranaPobockaID." and `k_poduzivatele_id_obsluha` = ".$ObsluhaSQLID.";";
                                                                            

                                                                            //echo $select_na_pravo;

                                                                               if (!$select_na_pravo) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

                                                                                $vysledek_na_pravo=$mysqli->query($select_na_pravo);
                                                                                $radku_na_pravo=$vysledek_na_pravo->num_rows;                                       
                                                                                  
                                                                                  //prazdne pole
                                                                                  $PolePrav[]="";

                                                                                  while ($na_pravo=MySQLi_Fetch_Array($vysledek_na_pravo)):   

                                                                                      array_push($PolePrav, $na_pravo["prava_uzivatel_menu"]);
                                                                                    //echo $na_pravo["prava_uzivatel_menu"];
                                                                                  endwhile;



                                                                                  




                              //echo $Prava_Dashboard." - ".$Prava_Zakaznici
                              //echo $select_na_pravo;



                               ?> 


                                                                <form action="index.php?strana=Uzivatele" method="POST" class="form-horizontal form-border" name="ZmenaPobockyPravaFormPolozkyMenu" id="ZmenaPobockyPravaFormPolozkyMenu">

                                                                  <input type="hidden" class="form-control" name="ObsluhaSQLID" value="<?php echo $ObsluhaSQLID; ?>">
                                                                  <input type="hidden" class="form-control" name="Obsluha_GUID_POST" value="<?php echo $Obsluha_GUID; ?>">
                                                                  
                                                                  <input type="hidden" class="form-control" name="VybranaPobockaID" value="<?php echo $VybranaPobockaID; ?>">
                                                                  <input type="hidden" class="form-control" name="ZmenaKonkretnihoPravaObsluha" value="1">

                                                                  <table width="99.9%" class="table table-bordered table-striped hs-users-permissions-grid-v154" id="table_prehledPravaNaMenu">
                                                                            <tr>
                                                                              <td></td>
                                                                              <td></td>
                                                                              <td></td>
                                                                              <td align="center">Anonymizace citlivých dat</td>
                                                                            </tr>


                                                                            <tr>
                                                                              <td align="center">1</td>
                                                                              <td>Dashboard</td>
                                                                              <td align="center">
                                                                                 
                                                                                <?php  
                                                                                  if (in_array("Dashboard", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                  }else{
                                                                                        $checked="";
                                                                                  }
                                                                                ?> 

                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck1" name="Prava_Dashboard" value="1">

                                                                              </td>
                                                                              <td></td>
                                                                            </tr>

                                                                            <tr>
                                                                              <td align="center">2</td>
                                                                              <td>Nový zákazník</td>
                                                                              <td align="center">
                                                                                
                                                                                <?php  
                                                                                  if (in_array("NovyZakaznik", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                  }else{
                                                                                        $checked="";
                                                                                  }
                                                                                ?> 

                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck9" name="Prava_NovyZakaznik" value="1">
                                                                              </td>
                                                                              <td></td>
                                                                            </tr>

                                                                             <tr>
                                                                              <td align="center">3</td>
                                                                              <td>Zákazníci</td>
                                                                              <td align="center">
                                                                                
                                                                                <?php  
                                                                                  if (in_array("Zakaznici", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                        $ZobrazitPrepinacCitlivychDat = "1";
                                                                                  }else{
                                                                                        $checked="";
                                                                                        $ZobrazitPrepinacCitlivychDat = "";
                                                                                  }
                                                                                ?> 

                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck2" name="Prava_Zakaznici" value="1">
                                                                              </td>
                                                                              <td align="center">
                                                                                  <?php //citliva data 

                                                                                    if ($k_poduzivatele_skryt_citliva_data=="0") {
                                                                                        $checked="";
                                                                                    }else{
                                                                                        $checked="checked";
                                                                                    }

                                                                                    if ($ZobrazitPrepinacCitlivychDat == "1") {
                                                                                        
                                                                                   ?>
                                                                                   
                                                                                           <div class="hs-users-sensitive-v154">
                                                                                            <span class="hs-users-sensitive-label-v154">Anonymizace citlivých dat</span>
                                                                                            <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck_ZobrazitCitlivaData" name="ZobrazitCitlivaData" value="<?php echo $k_poduzivatele_skryt_citliva_data; ?>">
                                                                                           </div>
                                                                                   
                                                                                   <?php 
                                                                                    }
                                                                                    ?>

                                                                              </td>
                                                                            </tr>

                                                                                                                           <tr>
                                                                              <td align="center">4</td>
                                                                              <td>Rezervace</td>
                                                                              <td align="center">
                                                                                
                                                                                <?php  
                                                                                  if (in_array("Rezervace", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                  }else{
                                                                                        $checked="";
                                                                                  }
                                                                                ?> 

                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck3" name="Prava_Rezervace" value="1">
                                                                              </td>
                                                                              <td></td>
                                                                            </tr>

                                                                            <!--  <tr>
                                                                              <td align="center">2</td>
                                                                              <td>Zákazníci</td>
                                                                              <td align="center">
                                                                                
                                                                                <?php  
                                                                                  if (in_array("Pobocky", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                  }else{
                                                                                        $checked="";
                                                                                  }
                                                                                ?> 

                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck4" name="Pobocky" value="1">
                                                                              </td>
                                                                            </tr> -->

                                                                             <tr>
                                                                              <td align="center">5</td>
                                                                              <td>Tržby</td>
                                                                              <td align="center">
                                                                                
                                                                                <?php  
                                                                                  if (in_array("Trzby", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                  }else{
                                                                                        $checked="";
                                                                                  }
                                                                                ?> 

                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck5" name="Prava_Trzby" value="1">
                                                                              </td>
                                                                              <td></td>
                                                                            </tr>

                                                                             <tr>
                                                                              <td align="center">6</td>
                                                                              <td>Sklad</td>
                                                                              <td align="center">
                                                                                
                                                                                <?php  
                                                                                  if (in_array("Sklad", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                  }else{
                                                                                        $checked="";
                                                                                  }
                                                                                ?> 

                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck6" name="Prava_Sklad" value="1">
                                                                              </td>
                                                                              <td></td>
                                                                            </tr>

                                                                             <tr>
                                                                              <td align="center">7</td>
                                                                              <td>Voucher</td>
                                                                              <td align="center">
                                                                                
                                                                                <?php  
                                                                                  if (in_array("Voucher", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                  }else{
                                                                                        $checked="";
                                                                                  }
                                                                                ?> 

                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck7" name="Prava_Voucher" value="1">
                                                                              </td>
                                                                              <td></td>
                                                                            </tr>

                                                                            <tr>
                                                                              <td align="center">8</td>
                                                                              <td>Hodnocení</td>
                                                                              <td align="center">
                                                                                
                                                                                <?php  
                                                                                  if (in_array("Hodnoceni", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                  }else{
                                                                                        $checked="";
                                                                                  }
                                                                                ?> 

                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck8" name="Prava_Hodnoceni" value="1">
                                                                              </td>
                                                                              <td></td>
                                                                            </tr>

                                                                             <tr>
                                                                              <td align="center">9</td>
                                                                              <td>SMS a hovory</td>
                                                                              <td align="center">
                                                                                
                                                                                <?php  
                                                                                  if (in_array("SMS", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                  }else{
                                                                                        $checked="";
                                                                                  }
                                                                                ?> 

                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck9" name="Prava_SMS" value="1">
                                                                              </td>
                                                                              <td></td>
                                                                            </tr>

                                                                            <tr>
                                                                              <td align="center">10</td>
                                                                              <td>Uživatelé</td>
                                                                              <td align="center">
                                                                                
                                                                                <?php  
                                                                                  if (in_array("Uzivatele", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                  }else{
                                                                                        $checked="";
                                                                                  }
                                                                                ?> 

                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck10" name="Prava_Uzivatele" value="1">
                                                                              </td>
                                                                              <td></td>
                                                                            </tr>

<!--
                                                                            <tr>
                                                                              <td align="center">10</td>
                                                                              <td>Ceník</td>
                                                                              <td align="center">
                                                                                
                                                                                <?php  
                                                                                  if (in_array("Cenik", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                  }else{
                                                                                        $checked="";
                                                                                  }
                                                                                ?> 

                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck10" name="Prava_Cenik" value="1">
                                                                              </td>
                                                                            </tr>
-->                                                                            

                                                                            
<!--
                                                                            <tr>
                                                                              <td align="center">8</td>
                                                                              <td>Kamery</td>
                                                                              <td align="center">
                                                                                
                                                                                <?php  
                                                                                  /*
                                                                                  if (in_array("Kamery", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                  }else{
                                                                                        $checked="";
                                                                                  }
                                                                                  */
                                                                                ?> 



                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck9" name="Prava_Kamery" value="1">
                                                                              </td>
                                                                            </tr>
                                                                            -->                                                                                

                                                                           <!--  <tr>
                                                                              <td align="center">2</td>
                                                                              <td>Nastavení</td>
                                                                              <td align="center">
                                                                                
                                                                                <?php  
                                                                                  if (in_array("Nastaveni", $PolePrav)) {
                                                                                        $checked="checked";
                                                                                  }else{
                                                                                        $checked="";
                                                                                  }
                                                                                ?> 

                                                                                  <input type="checkbox" class="js-switch" <?php echo $checked ?> id="chck10" name="Prava_Nastaveni" value="1">
                                                                              </td>
                                                                            </tr> -->
                                                                  </table>
                                                                  <input type="submit" name="submit" value="Submit" style="display: none" >

                                                                </form> 

                                                              </div>
                                                          </div>
                                                      </div>
                                            </div>

                                            


                            </div>


<?php 
}
 ?>            


            <div class="row">
               <div class="col-md-12">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Seznam obsluh</b></font></h3>
                                
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">

                                                      <?php

                                                                     $Selectpoduzivatele = "SELECT * FROM `k_poduzivatele_obsluha` WHERE `k_poduzivatele_archivace_obsluhy` = 1 and `k_poduzivatele_obsluha_sw_id` = ".$VybranaPobockaID." order by k_poduzivatele_obsluha_id_hs ASC";
                                                                     //echo $Selectpoduzivatele;
                                                                     if (!$Selectpoduzivatele) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}

                                                                        $vysledek_Selectpoduzivatele=$mysqli->query("$Selectpoduzivatele");
                                                                        $pocet_radku_Selectpoduzivatele = $vysledek_Selectpoduzivatele->num_rows;
                                                                                                                                              
                                                                          ?>


                                                                               <table width="99.9%" class="table table-bordered table-striped" id="table_prehledUzivateluObsluha">
                                                                                    <thead>
                                                                                        <th width="50">Detail</th>
                                                                                        <th>Jméno obsluhy</th>
                                                                                        <th>Práva obsluhy</th>
                                                                                        <th align="center">Akce</th>
                                                                                        
                                                                                    </thead>




                                                                          <?php

                                                                          /*
                                                                                <?php 
                                                                                            if ($SelectObsluha_seznam["Fotka"]!="") {
                                                                                              $FotkaObsluhy = $SelectObsluha_seznam["Fotka"];
                                                                                            }else{
                                                                                              $FotkaObsluhy = "no_image.jpg";
                                                                                            }
                                                                                        */
                                                                                      /*<div id="imgdiv"><img title="<?php echo $SelectObsluha_seznam["ObsluhaJmeno"]." - Hodnocení: ".$SelectObsluha_seznam["Hodnoceni"]; " src="../img/obsluhy/<?php echo $FotkaObsluhy;" id="imgkolac" ></div>*/
                                                                          

                                                                        $SelectpoduzivateleCNT=0;
                                                                        echo "<tbody>";
                                                                          
                                                                        


                                                                        while ($Selectpoduzivatele_seznam=MySQLi_Fetch_Array($vysledek_Selectpoduzivatele)):
                                                                              $SelectpoduzivateleCNT = $SelectpoduzivateleCNT + 1;


                                                                                $k_poduzivatele_jmeno = $Selectpoduzivatele_seznam["k_poduzivatele_obsluha_jmeno"];
                                                                                $k_poduzivatele_login = $Selectpoduzivatele_seznam["k_poduzivatele_obsluha_login"];
                                                                                 $k_poduzivatele_hash = $Selectpoduzivatele_seznam["k_poduzivatele_obsluha_hash"];
                                                                                   $k_poduzivatele_id = $Selectpoduzivatele_seznam["k_poduzivatele_obsluha_id"];

                                                                                            if ($Selectpoduzivatele_seznam["k_poduzivatele_obsluha_foto"]!="") {
                                                                                              $FotkaObsluhy = $Selectpoduzivatele_seznam["k_poduzivatele_obsluha_foto"];
                                                                                            }else{
                                                                                              $FotkaObsluhy = "no_image.jpg";
                                                                                            }
                                                                                 
                                                                                    echo "<tr>";
                                                                                        //echo "<td align=\"center\">$SelectpoduzivateleCNT</td>";
                                                                                        echo "<td><a href=\"index.php?strana=Uzivatele&Pobocka=".$VybranaPobockaID."&Obsluha_GUID=".$k_poduzivatele_hash."&Obsluha_ID_GET=".$k_poduzivatele_id."\" title=\"Editace\"><div class=\"imgdivMini\"><center><img  width=\"40\" height=\"40\" src=\"../img/obsluhy/".$FotkaObsluhy."\" class=\"imgkolacmini\"></center></div></a></td>";
                                                                                        echo "<td>".$k_poduzivatele_jmeno."</td>";
                                                                                        
                                                                                                  //Prava do programu textově
                                                                                                  $PravaOsobyTextove = "";
                                                                                                  $select_na_pobocku= "SELECT `prava_uzivatel_menu` FROM `k_poduzivatele_prava` WHERE  `k_poduzivatele_id_obsluha` = ".$k_poduzivatele_id;
                                                                                                  if (!$select_na_pobocku) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                                                                  $vysledek_na_pobocku=$mysqli->query($select_na_pobocku);

                                                                                                      $ObsluhaText = "";
                                                                                                      while ($na_pobocku=MySQLi_Fetch_Array($vysledek_na_pobocku)):   
                                                                                                        

                                                                                                       $TextPrava = "";
                                                                                                       
                                                                                                       switch ($na_pobocku["prava_uzivatel_menu"]) {
                                                                                                           case 'Zakaznici':
                                                                                                               $TextPrava = "Zákazníci";        
                                                                                                               break;
                                                                                                           case 'Hodnoceni':
                                                                                                               $TextPrava = "Hodnocení";        
                                                                                                               break;
                                                                                                           case 'Trzby':
                                                                                                               $TextPrava = "Tržby";        
                                                                                                               break;    
                                                                                                           case 'NovyZakaznik':
                                                                                                               $TextPrava = "Nový zákazník";        
                                                                                                               break;        



                                                                                                           
                                                                                                           default:
                                                                                                               $TextPrava = $na_pobocku["prava_uzivatel_menu"];        
                                                                                                               break;
                                                                                                       }


                                                                                                        if ($ObsluhaText=="") {
                                                                                                            $ObsluhaText="druhy";
                                                                                                            $PravaOsobyTextove = $PravaOsobyTextove .$TextPrava;
                                                                                                        }else{
                                                                                                            $PravaOsobyTextove = $PravaOsobyTextove .", ". $TextPrava;
                                                                                                        }

                                                                                                        
                                                                                                      endwhile;

                                                                                                                                                                                                                                                                                                                                


                                                                                        echo "<td >".$PravaOsobyTextove."</td>";
                                                                                        echo "<td style=\"text-align: center;\" align=\"center\"><a onClick=\"if(!confirm('Opravdu chcete SMAZAT tuto osobu? Osoba bude trvale vymazána ze systému!')){return false;}\" href=\"index.php?strana=Uzivatele&Pobocka=".$VybranaPobockaID."&Obsluha_GUID=".$k_poduzivatele_hash."&SmazaniObsluhyZeSystemu=1&Obsluha_ID_GET=".$k_poduzivatele_id."\" title=\"Smazat obsluhu\"><i class=\"icon-trash\"></a></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a onClick=\"if(!confirm('Opravdu chcete obsluhu přesunout do archivu?')){return false;}\" href=\"index.php?strana=Uzivatele&Pobocka=".$VybranaPobockaID."&Obsluha_GUID=".$k_poduzivatele_hash."&PresunObsluhyDoArchivu=1&Obsluha_ID_GET=".$k_poduzivatele_id."\" title=\"Přesunout obsluhu do archivu\"><i class=\"icon-layers\"></i></a></td>";

                                                                                        
                                                                                    echo "</tr>";
                                                                        endwhile;

                                                                        echo "</tbody>";
                                                                   ?>
                                                                     </table>

                                                                 

                                </div>
                            </div>
                        </div>
                    </div>

        </div>

        <div class="row">
               <div class="col-md-12">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Seznam archivovaných obsluh </b></font></h3>
                                
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive">

                                                      <?php

                                                                     $Selectpoduzivatele = "SELECT * FROM `k_poduzivatele_obsluha` WHERE `k_poduzivatele_obsluha_sw_id` = ".$VybranaPobockaID." and `k_poduzivatele_archivace_obsluhy` = 0 order by k_poduzivatele_obsluha_id_hs ASC";
                                                                     //echo $Selectpoduzivatele;
                                                                     if (!$Selectpoduzivatele) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}

                                                                        $vysledek_Selectpoduzivatele=$mysqli->query("$Selectpoduzivatele");
                                                                        $pocet_radku_Selectpoduzivatele = $vysledek_Selectpoduzivatele->num_rows;
                                                                                                                                              
                                                                          ?>


                                                                               <table width="99.9%" class="table table-bordered table-striped" id="table_prehledUzivateluObsluhaArchiv">
                                                                                    <thead>
                                                                                        <th width="50">Detail</th>
                                                                                        <th>Jméno obsluhy</th>
                                                                                        <th>Práva obsluhy</th>
                                                                                        <th align="center">Akce</th>
                                                                                        
                                                                                    </thead>




                                                                          <?php

                                                                          /*
                                                                                <?php 
                                                                                            if ($SelectObsluha_seznam["Fotka"]!="") {
                                                                                              $FotkaObsluhy = $SelectObsluha_seznam["Fotka"];
                                                                                            }else{
                                                                                              $FotkaObsluhy = "no_image.jpg";
                                                                                            }
                                                                                        */
                                                                                      /*<div id="imgdiv"><img title="<?php echo $SelectObsluha_seznam["ObsluhaJmeno"]." - Hodnocení: ".$SelectObsluha_seznam["Hodnoceni"]; " src="../img/obsluhy/<?php echo $FotkaObsluhy;" id="imgkolac" ></div>*/
                                                                          

                                                                        $SelectpoduzivateleCNT=0;
                                                                        echo "<tbody>";
                                                                          
                                                                        


                                                                        while ($Selectpoduzivatele_seznam=MySQLi_Fetch_Array($vysledek_Selectpoduzivatele)):
                                                                            $SelectpoduzivateleCNT = $SelectpoduzivateleCNT + 1;
                                                                                $k_poduzivatele_jmeno = $Selectpoduzivatele_seznam["k_poduzivatele_obsluha_jmeno"];
                                                                                $k_poduzivatele_login = $Selectpoduzivatele_seznam["k_poduzivatele_obsluha_login"];
                                                                                 $k_poduzivatele_hash = $Selectpoduzivatele_seznam["k_poduzivatele_obsluha_hash"];
                                                                                   $k_poduzivatele_id = $Selectpoduzivatele_seznam["k_poduzivatele_obsluha_id"];

                                                                                            if ($Selectpoduzivatele_seznam["k_poduzivatele_obsluha_foto"]!="") {
                                                                                              $FotkaObsluhy = $Selectpoduzivatele_seznam["k_poduzivatele_obsluha_foto"];
                                                                                            }else{
                                                                                              $FotkaObsluhy = "no_image.jpg";
                                                                                            }
                                                                                 
                                                                                    echo "<tr>";
                                                                                        //echo "<td align=\"center\">$SelectpoduzivateleCNT</td>";
                                                                                        echo "<td><a href=\"index.php?strana=Uzivatele&Pobocka=".$VybranaPobockaID."&Obsluha_GUID=".$k_poduzivatele_hash."&Obsluha_ID_GET=".$k_poduzivatele_id."\" title=\"Editace\"><div class=\"imgdivMini\"><center><img  width=\"40\" height=\"40\" src=\"../img/obsluhy/".$FotkaObsluhy."\" class=\"imgkolacmini\"></center></div></a></td>";
                                                                                        echo "<td>".$k_poduzivatele_jmeno."</td>";
                                                                                        
                                                                                                  //Prava do programu textově
                                                                                                  $PravaOsobyTextove = "";
                                                                                                  $select_na_pobocku= "SELECT `prava_uzivatel_menu` FROM `k_poduzivatele_prava` WHERE `k_poduzivatele_id_obsluha` = ".$k_poduzivatele_id;
                                                                                                  if (!$select_na_pobocku) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                                                                  $vysledek_na_pobocku=$mysqli->query($select_na_pobocku);

                                                                                                      $ObsluhaText = "";
                                                                                                      while ($na_pobocku=MySQLi_Fetch_Array($vysledek_na_pobocku)):   
                                                                                                        

                                                                                                       $TextPrava = "";
                                                                                                       
                                                                                                       switch ($na_pobocku["prava_uzivatel_menu"]) {
                                                                                                           case 'Zakaznici':
                                                                                                               $TextPrava = "Zákazníci";        
                                                                                                               break;
                                                                                                           case 'Hodnoceni':
                                                                                                               $TextPrava = "Hodnocení";        
                                                                                                               break;
                                                                                                           case 'Trzby':
                                                                                                               $TextPrava = "Tržby";        
                                                                                                               break;    
                                                                                                           case 'NovyZakaznik':
                                                                                                               $TextPrava = "Nový zákazník";        
                                                                                                               break;        



                                                                                                           
                                                                                                           default:
                                                                                                               $TextPrava = $na_pobocku["prava_uzivatel_menu"];        
                                                                                                               break;
                                                                                                       }


                                                                                                        if ($ObsluhaText=="") {
                                                                                                            $ObsluhaText="druhy";
                                                                                                            $PravaOsobyTextove = $PravaOsobyTextove .$TextPrava;
                                                                                                        }else{
                                                                                                            $PravaOsobyTextove = $PravaOsobyTextove .", ". $TextPrava;
                                                                                                        }

                                                                                                        
                                                                                                      endwhile;

                                                                                                                                                                                                                                                                                                                                


                                                                                        echo "<td >".$PravaOsobyTextove."</td>";
                                                                                        echo "<td style=\"text-align: center;\" align=\"center\"><a onClick=\"if(!confirm('Opravdu chcete obnovit tuto osobu z archivu?')){return false;}\" href=\"index.php?strana=Uzivatele&Pobocka=".$VybranaPobockaID."&Obsluha_GUID=".$k_poduzivatele_hash."&ObnoveniObsluhyZarchivu=1&Obsluha_ID_GET=".$k_poduzivatele_id."\" title=\"Obnovit obsluhu z archivu\"><i class=\"icon-layers\"></a></i></td>";
                                                                                        
                                                                                    echo "</tr>";
                                                                        endwhile;


                                                                        if ($SelectpoduzivateleCNT=="0") {
                                                                            echo "<tr><td colspan='4' align='center'>Nic nenalezeno</td></tr>";
                                                                        }
                                                                        echo "</tbody>";
                                                                   ?>
                                                                     </table>

                                                                 

                                </div>
                            </div>
                        </div>
                    </div>

        </div>

        <div class="row">
                    <div class="col-md-12">
                            
                        <div class="panel panel-default" >
                            <div class="panel-heading">
                                <h3 class="panel-title"><font face="tahoma"><b>Akce</b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">


                                                       <?php     
                                                         

                                                             $select_na_import= "SELECT * FROM `k_obsluha_refersh` WHERE `k_obsluha_refersh_sw_id` =".$VybranaPobockaID;
                                                             if (!$select_na_import) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                             $vysledek_na_import=$mysqli->query($select_na_import);
                                                             $radku_na_import=$vysledek_na_import->num_rows;   

                                                                         
                                                             if ($radku_na_import> 0) {
                                                                $disabledAN =" disabled=\"disabled\"";
                                                             }else{
                                                                $disabledAN ="";
                                                             }
                                                       ?>
                                 
                                          <div class="rada" >
                                              <form action="index.php?strana=Uzivatele" method="POST" >
                                                <input type="hidden" class="form-control" name="ImportObsluhyZHS" value="1">
                                                <input type="hidden" class="form-control" name="k_obsluha_refersh_sw_id" value="<?php echo $VybranaPobockaID; ?>">
                                                <center> <button type="submit" <?php echo $disabledAN; ?> class="btn btn-info"><i class="fa icon-refresh"></i>Import obsluh z programu</button></center>
                                              </form>
                                          </div>

                                      

                                  <br class="clearBoth" />

                            </div>
                        </div>
                      </div>
                  </div>




   
  

    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-body">
            
          </div>
        </div>
      </div>
    </div>

  </section>        

<!-- echo random_color(); -->








