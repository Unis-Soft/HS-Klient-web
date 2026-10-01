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
require_once dirname(__DIR__, 4) . '/cfg/nastaveni.php';
require_once dirname(__DIR__, 4) . '/fce/SpolecneFunkce.php';



if (!isset($_POST['Akce']) || is_array($_POST['Akce'])){$_POST['Akce']='';}
$Akce  =  htmlspecialchars($_POST['Akce'], ENT_COMPAT);

if (!isset($_POST['CoHledat']) || is_array($_POST['CoHledat'])){$_POST['CoHledat']='';}
$CoHledat = htmlspecialchars($_POST['CoHledat'], ENT_COMPAT);
$CoHledat = trim($CoHledat);

if (!isset($_POST['Pismeno']) || is_array($_POST['Pismeno'])){$_POST['Pismeno']='';}
$Pismeno  =  htmlspecialchars($_POST['Pismeno'], ENT_COMPAT);

$sw_id = $_SESSION["pobocka_id"];
$sw_skupina = IdSkupiny($sw_id,$mysqli);

/*
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
*/
 
 //alert("I am an alert box!");



switch ($Akce) {
	case 'Hledej':
					$select_na_lidi= "SELECT * FROM `klient_lidi` WHERE (`lidi_hs_name` LIKE '%".$CoHledat."%' OR `lidi_hs_surname` LIKE '%".$CoHledat."%' OR `lidi_hs_phone` LIKE '%".$CoHledat."%' OR `lidi_hs_cell` LIKE '%".$CoHledat."%' OR `lidi_hs_email` LIKE '%".$CoHledat."%' OR `lidi_hs_title` LIKE '%".$CoHledat."%' OR `lidi_hs_street` LIKE '%".$CoHledat."%' OR `lidi_hs_city` LIKE '%".$CoHledat."%' OR `lidi_hs_zip` LIKE '%".$CoHledat."%' OR	`lidi_hs_BirthDay` LIKE '%".$CoHledat."%' OR `lidi_hs_firm_name` LIKE '%".$CoHledat."%' OR `lidi_hs_firm_register_number` LIKE '%".$CoHledat."%' OR	`lidi_hs_www` LIKE '%".$CoHledat."%' OR	`lidi_hs_skype` LIKE '%".$CoHledat."%' OR	`lidi_hs_facebook` LIKE '%".$CoHledat."%' OR `lidi_hs_note` LIKE '%".$CoHledat."%' OR `lidi_hs_card` LIKE '%".$CoHledat."%' ) and `lidi_skupinaID` =  ".$sw_skupina. " and `lidi_aktivni` = 1 order by `lidi_hs_surname` asc";
		break;

case 'HledejPismeno':
					



					if ($Pismeno=="~") {
						//hleda podle cisel a znaku
						$select_na_lidi= "SELECT * FROM `klient_lidi` Where   (`lidi_hs_surname` LIKE '1%' or `lidi_hs_surname` LIKE '2%' or `lidi_hs_surname` LIKE '3%' or `lidi_hs_surname` LIKE '4%' or `lidi_hs_surname` LIKE '5%' or `lidi_hs_surname` LIKE '6%' or `lidi_hs_surname` LIKE '7%' or `lidi_hs_surname` LIKE '8%' or `lidi_hs_surname` LIKE '9%' or `lidi_hs_surname` LIKE '0%' or `lidi_hs_surname` LIKE '-%' or `lidi_hs_surname` LIKE ' %' or `lidi_hs_surname` LIKE '+%' or `lidi_hs_surname` LIKE '*%' or `lidi_hs_surname` LIKE '/%' or `lidi_hs_surname` LIKE '.%') and `lidi_skupinaID` =  ".$sw_skupina." and `lidi_aktivni` = 1 order by `lidi_hs_surname` asc"; 					
					}else{
						//hleda podle pismena
						$select_na_lidi= "SELECT * FROM `klient_lidi` Where `lidi_hs_surname` like '".$Pismeno."%' and `lidi_skupinaID` =  ".$sw_skupina." and `lidi_aktivni` = 1 order by `lidi_hs_surname` asc";
					}
		break;		
	
	default:
					$select_na_lidi= "SELECT * FROM `klient_lidi` where  `lidi_skupinaID` =  ".$sw_skupina." and `lidi_aktivni` = 1 order by `lidi_hs_surname` asc limit 100 ;";
		break;
}



		   if (!$select_na_lidi) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   

//echo $select_na_lidi;

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
		               switch ($na_lidi["lidi_pohlavi"] ) {
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




/*
 																																	<center><div class="OrizlaKulataFotkaObal"><div class="OrizlaKulataFotka" style="background-image: url('');"></div></div></center>

                    echo "    <span class=\"OrizlaKulataFotkaObal\">";
                    echo "     <img src=\"".$avatar."\" class=\"img-circle\" alt=\"\">";
                    echo "     <i class=\"on animated bounceIn\"></i>";
                    echo "    </span>";
*/

			             /* echo "<ul>";
                    echo " <li>";
                    echo "  <div class=\"row\" onclick=\"location.href='index.php?strana=KartaOsoby&osoba_guid=".$lidi_guid."';\">";
                    echo "   <div class=\"col-md-3\">";
                    echo "    <div class=\"OrizlaKulataFotkaObal\"><div class=\"OrizlaKulataFotka\" style=\"background-image: url('".$avatar."');width:50px;height:50px;margin-right:15px\"></div></div>";
                    echo "   </div>";
                    echo "   <div class=\"col-md-9\">";
                    echo "    <div class=\"name\">".$Prijmeni." ".$Jmeno."</div>";
                    echo "     <small class=\"location text-muted\">".$Tel."</small></br>";
                    echo "     <a style=\"color:black\" href=\"tel:$Tel\"><i class=\"icon-call-out\"></i>&nbsp;&nbsp;&nbsp;&nbsp;<i class=\"icon-envelope\"></i></a>";
                    echo "    </div>";
                    echo "   </div>";
                    echo " </li>";
                    echo "</ul>";
                    */

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

		    }else{
				echo "<ul>";
                echo " <li>";
                echo "  <div class=\"row\">";
                echo "   <div class=\"name\">Nic nenalezeno...</div>";
                echo "  </div>";
                echo " </li>";
                echo "</ul>";
		    }	
 	

                                                                   


?>
