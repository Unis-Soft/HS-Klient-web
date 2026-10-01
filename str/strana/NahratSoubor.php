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
require '_zabezpeceni.php';
require_once '../../cfg/nastaveni.php';
require_once '../../fce/SpolecneFunkce.php';
require_once '_chatGPT.php';
require_once dirname(__DIR__, 2) . '/hs-client-ui/php/customer-dependent-sync.php';



/*OSOBA*/
$target_dir = "galerie/";
$target_file = $target_dir . basename($_FILES["cameraFileInput"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));


/*MANAGER*/
$target_dir_manager = "galerie/manager/";
$target_file_manager = $target_dir_manager . basename($_FILES["managerFileInput"]["name"]);
$uploadOk_manager = 1;
$imageFileType_manager = strtolower(pathinfo($target_file_manager,PATHINFO_EXTENSION));




    /*ososba*/
    if (!isset($_POST['osoba_guid']) || is_array($_POST['osoba_guid'])){$_POST['osoba_guid']='';}
    $osoba_guid = htmlspecialchars($_POST['osoba_guid'], ENT_COMPAT);

    /*manager*/
    if (!isset($_POST['k_poduzivatele_hash']) || is_array($_POST['k_poduzivatele_hash'])){$_POST['k_poduzivatele_hash']='';}
    $k_poduzivatele_hash = htmlspecialchars($_POST['k_poduzivatele_hash'], ENT_COMPAT);

    if (!isset($_POST['akce']) || is_array($_POST['akce'])){$_POST['akce']='';}
    $akce = htmlspecialchars($_POST['akce'], ENT_COMPAT);

    if (!isset($_POST['otoceni']) || is_array($_POST['otoceni'])){$_POST['otoceni']='';}
    $otoceni = htmlspecialchars($_POST['otoceni'], ENT_COMPAT);

    
    if (!isset($_GET['GETosoba_guid']) || is_array($_GET['GETosoba_guid'])){$_GET['GETosoba_guid']='';}
    $GETosoba_guid = htmlspecialchars($_GET['GETosoba_guid'], ENT_COMPAT);

    if (!isset($_GET['GETOtocitSoubor']) || is_array($_GET['GETOtocitSoubor'])){$_GET['GETOtocitSoubor']='';}
    $GETOtocitSoubor = htmlspecialchars($_GET['GETOtocitSoubor'], ENT_COMPAT);

    if (!isset($_GET['GETJmenoSouboru']) || is_array($_GET['GETJmenoSouboru'])){$_GET['GETJmenoSouboru']='';}
    $GETJmenoSouboru = htmlspecialchars($_GET['GETJmenoSouboru'], ENT_COMPAT);

    
    //Otoceni Fotky z galerie
    if ($GETosoba_guid!="" and $GETOtocitSoubor!="" and $GETJmenoSouboru!="")  {
                          
                
                $SouborVelky="galerie/".$GETJmenoSouboru;
                $SouborMaly="galerie/m".$GETJmenoSouboru;

                
                rotaceObrazku($target_file,"Doprava");  


                if ($GETOtocitSoubor=="Doprava") {
                    //echo "Doprava";
                    rotaceObrazku($SouborVelky,"Doleva");
                    rotaceObrazku($SouborMaly,"Doleva");
                }

                if ($GETOtocitSoubor=="Doleva") {
                    //echo "Doleva";
                    rotaceObrazku($SouborVelky,"Doprava");
                    rotaceObrazku($SouborMaly,"Doprava");
                }
                
    
     
     
     echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=KartaOsoby&NavratGalerie=1&osoba_guid=".$GETosoba_guid."\">";       
    }


    


     /*Manager*/
    if ($akce == "managerFoto" and $k_poduzivatele_hash!="")  {

            if(isset($_POST["submit"])) {
              
              $check_manager = getimagesize($_FILES["managerFileInput"]["tmp_name"]);
              if($check_manager !== false) {
                //echo "File is an image - " . $check["mime"] . ".";
                $uploadOk_manager = 1;
              } else {
                echo "Soubor není obrázek!";
                $uploadOk_manager = 0;
              }
            }

            /*Prejmenovat soubor*/
              
              $GuidObrazku = GUID();
              $target_file_manager = $target_dir_manager.$GuidObrazku.".".$imageFileType_manager;
              $target_file_mini_manager = $target_dir_manager."m".$GuidObrazku.".".$imageFileType_manager;
              $JmenoSouboru = $GuidObrazku.".".$imageFileType_manager;


            // Check if file already exists
            if (file_exists($target_file_manager)) {
              echo "Soubor již existuje";
              $uploadOk_manager = 0;
            }

            // Check file size
            if ($_FILES["managerFileInput"]["size"] > 50000000) {
              echo "Obrázek je přiliš veliký!";
              $uploadOk_manager = 0;
            }

            // Allow certain file formats
            if($imageFileType_manager != "jpg" && $imageFileType_manager != "png" && $imageFileType_manager != "jpeg" && $imageFileType_manager != "gif" ) {
              echo "Obrázek může být pouze typu: JPG, JPEG, PNG & GIF.";
              $uploadOk_manager = 0;
            }

            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk_manager == 0) {
              //echo "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
            } else {


              //UPDATE managera
                $sql = "UPDATE `k_poduzivatele` SET `k_poduzivatele_foto`= '".$JmenoSouboru."' WHERE `k_poduzivatele_hash` = '".$k_poduzivatele_hash."'";
                                              
                  $vysledek_update_obrazku = @$mysqli->query($sql);
                  if ($vysledek_update_obrazku) {
                     
                   }else{
                     $error = $mysqli->error;
                      echo $error;
                      return;
                  }
              

    

              if (move_uploaded_file($_FILES["managerFileInput"]["tmp_name"], $target_file_manager)) {
                
                /*Miniatura*/
                copy($target_file_manager, $target_file_mini_manager);
                zmensi_obrazek($target_file_manager,250,100);      // miniatura 100%
                zmensi_obrazek($target_file_manager,1200,75);           //Zmensit puvodni 65%
                

              } else {
                //echo "Sorry, there was an error uploading your file.";
              }
            }


            echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=NastaveniPristupy\">";      
    }    
    
    


    /*OSOBA*/
    if ($akce == "fotoaparat" and $osoba_guid!="")  {
        // V202: nejprve zjistit, zda HairSoft uz vratil skutecne lokalni ID osoby.
        // Pokud je stale docasne 11111111, fotografie se ulozi na webu, ale zustane HOLD (stav 2).
        hsCustomerSyncHoldSynchronizeGuid($mysqli, $osoba_guid);
        $hsPhotoWaitForId = hsCustomerSyncHoldShouldWait($mysqli, $osoba_guid);
        $hsPhotoManualPending = hsCustomerSyncHoldIsManualCopyTarget($mysqli, $osoba_guid) && !hsCustomerSyncHoldManualConfirmed($mysqli, $osoba_guid);
        $hsPhotoWaitForRelease = ($hsPhotoWaitForId || $hsPhotoManualPending);
        $hsPhotoSyncState = $hsPhotoWaitForRelease ? (int) HS_CUSTOMER_PHOTO_HOLD : 0;
        if ($hsPhotoWaitForRelease && !hsCustomerSyncHoldQueueGuid($mysqli, $osoba_guid, 0, $hsPhotoManualPending ? 'manual' : 'waiting')) {
            echo "Fotografii se nepodařilo bezpečně zařadit do čekající synchronizace.";
            return;
        }

        // Check if image file is a actual image or fake image
            if(isset($_POST["submit"])) {
              

              $check = getimagesize($_FILES["cameraFileInput"]["tmp_name"]);
              if($check !== false) {
                //echo "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
              } else {
                echo "Soubor není obrázek!";
                $uploadOk = 0;
              }
            }

            /*Prejmenovat soubor*/
              
              $GuidObrazku = GUID();
              $target_file = $target_dir.$GuidObrazku.".".$imageFileType;
              $target_file_mini = $target_dir."m".$GuidObrazku.".".$imageFileType;
              $JmenoSouboru = $GuidObrazku.".".$imageFileType;


            // Check if file already exists
            if (file_exists($target_file)) {
              echo "Soubor již existuje";
              $uploadOk = 0;
            }

            // Check file size
            if ($_FILES["cameraFileInput"]["size"] > 50000000) {
              echo "Obrázek je přiliš veliký!";
              $uploadOk = 0;
            }

            // Allow certain file formats
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
              echo "Obrázek může být pouze typu: JPG, JPEG, PNG & GIF.";
              $uploadOk = 0;
            }

            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
              //echo "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
            } else {


              //INSERT
                $sql = "INSERT INTO `klient_lidi_obrazky` (`obrazek_id`, `obrazek_jmeno_GUID`, `obrazek_osoba_GUID`, `obrazek_vlozeno`, `obrazek_stazeno`) VALUES (NULL, '".$JmenoSouboru."', '".$osoba_guid."', CURRENT_TIMESTAMP, '".$hsPhotoSyncState."');";
                                              
                  $vysledek_insert_obrazku = @$mysqli->query($sql);
                  if ($vysledek_insert_obrazku) {
                     
                   }else{
                     $error = $mysqli->error;
                      echo $error;
                      return;
                  }
              

    

              if (move_uploaded_file($_FILES["cameraFileInput"]["tmp_name"], $target_file)) {
                
                /*Miniatura*/
                copy($target_file, $target_file_mini);
                
                
                if ($otoceni=="90" or $otoceni=="-90") {

                    if ($otoceni=="-90") {
                    rotaceObrazku($target_file,"Doprava");
                    rotaceObrazku($target_file_mini,"Doprava");
                    }

                    if ($otoceni=="90") {
                        rotaceObrazku($target_file,"Doleva");
                        rotaceObrazku($target_file_mini,"Doleva");
                    }
                }else{
                  zmensi_obrazek($target_file_mini,300,100);      // miniatura 100%
                  zmensi_obrazek($target_file,1200,65);           //Zmensit puvodni 65%  
                }

                if ($hsPhotoWaitForRelease) {
                  // Znovu zařadit až po fyzickém uložení fotografie; tím se zavře race s návratem ID během uploadu.
                  hsCustomerSyncHoldQueueGuid($mysqli, $osoba_guid);
                  hsCustomerSyncHoldSynchronizeGuid($mysqli, $osoba_guid);
                }



                //copy($target_file_manager, $target_file_mini_manager);
                //zmensi_obrazek($target_file_mini,250,100);      // miniatura 100%
                //zmensi_obrazek($target_file_mini,250,100);      // miniatura 100%
                //zmensi_obrazek($target_file,1200,65);           //Zmensit puvodni 65%
                

              } else {
                //echo "Sorry, there was an error uploading your file.";
              }
            }


            echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=KartaOsoby&NavratGalerie=1&osoba_guid=".$osoba_guid."\">";      
    }

  





 ?>