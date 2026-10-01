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

require '_zabezpeceni.php';

  // Prava na stranku aby neslo podstrcit stranku s GET URL v oblibenych
  if ($_SESSION["JePoduzivatel"]=="1" and in_array("Sklad", $_SESSION["SeznamPravPoduzivatele"])=="0") {
    echo "<meta http-equiv=\"refresh\" content=\"0;URL=https://klient.hairsoft.cz/str/index.php?strana=PrazdnaStrana\">";
    exit;
  }
  // Prava na stranku
  

require '../fce/GeneratorBarev.php';
require '../fce/SpolecneFunkce.php';
  //require_once '../../cfg/nastaveni.php';


$jmenoStranky = "Přehled skladu ";
$jmenoStrankyPopis = "Přehled aktuálního pohybu na skladě";

if ($_SESSION["pobocka_jmeno"]=="") {
 $pobocka_jmeno =  "Nevybrána" ;
}else {
  $pobocka_jmeno =  $_SESSION["pobocka_jmeno"] ;  
}




//AKCE
if (!isset($_POST['idSkladuCombo']) || is_array($_POST['idSkladuCombo'])){$_POST['idSkladuCombo']='';}
$idSkladuCombo  =  htmlspecialchars($_POST['idSkladuCombo'], ENT_COMPAT);

if (!isset($_POST['SmazatSkladForm']) || is_array($_POST['SmazatSkladForm'])){$_POST['SmazatSkladForm']='';}
$SmazatSkladForm  =  htmlspecialchars($_POST['SmazatSkladForm'], ENT_COMPAT);

if (!isset($_POST['SmazatSkladFormId']) || is_array($_POST['SmazatSkladFormId'])){$_POST['SmazatSkladFormId']='';}
$SmazatSkladFormId  =  htmlspecialchars($_POST['SmazatSkladFormId'], ENT_COMPAT);



if (!isset($_POST['datum']) || is_array($_POST['datum'])){$_POST['datum']='';}
$datum  =  htmlspecialchars($_POST['datum'], ENT_COMPAT);

if ($datum=="") {
  $datum = date("d.m.Y"); 
}

$originalDate = $datum;
$newDate = date("d.m.Y", strtotime($originalDate));

$newYear = date("Y", strtotime($originalDate));


        // $datum ="2016-10-20";

        /*

            <div class="alert alert-success alert-dismissable">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
              Na stránce se pracuje...
            </div>
        */

        //////////////////////////////////////////////////////////////////////////////////////
        //DEMO
          if ($_SESSION["pobocka_id"]=="") {
            $sw_id = 0;
            $skupina_id = 0;  
          }else{
            $sw_id =  $_SESSION["pobocka_id"];  
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


//$sw_id =  $_SESSION["pobocka_id"];
        //echo $sw_id;
        //$sw_id =  1081;//$_SESSION["pobocka_id"];

        //Mena
$Mena_Klienta = $_SESSION["Mena_Klienta"];   



?>


<section class="main-content-wrapper">            
  <div class="pageheader">                
    <h1><?php echo $jmenoStranky; ?></h1>                
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

  
 <section id="main-content" class="animated fadeInUp">                


<?php  
//cnt
    $sql_skladcnt= "SELECT count(*) as 'COUNT', sw_sklad_id as 'PRVNI'  FROM `sklady_xml` WHERE `sw_id` = $sw_id group by `sw_id`,`sw_sklad_id` order by `sw_id` ,`sw_sklad_id` asc";
    $vysledek_sql_skladcnt=$mysqli->query($sql_skladcnt);
    $data_sql_skladcnt=MySQLi_Fetch_Array($vysledek_sql_skladcnt);
    $radku_existuje_skladu=$vysledek_sql_skladcnt->num_rows; 
    

    $sql_skladSeznam= "SELECT sw_sklad_id as 'PRVNI'  FROM `sklady_xml` WHERE `sw_id` = $sw_id Order by 1 ASC";
    $vysledek_sql_skladSeznam=$mysqli->query($sql_skladSeznam);
    $data_sql_skladSeznam=MySQLi_Fetch_Array($vysledek_sql_skladSeznam);


    $sklady_count =$radku_existuje_skladu;
    $sklady_prvni =$data_sql_skladSeznam["PRVNI"]; 

    //$sklady_count = 1; //Trvalke odblokovano tlacitko refresh protoze kdyz byla prazdna db a novy klient nemel sklady na webu a nemohl udelat refresh
  
  // if ($sklady_count==0) {
              $sklady_datum ="";
              $sklady_akt_hodn_skladu = number_format("0", 2, ',', ' ');
              $sklady_akt_prijem_sklad = number_format("0", 2, ',', ' ');
              $sklady_hodn_skladu_bezDPH = number_format("0", 2, ',', ' ');
              $sklady_prijem_sklad_bezDPH = number_format("0", 2, ',', ' ');
              $sklady_akt_stav_zasob = "";
              $sklady_seznam_chyb_zbozi = "";
              


   //}else{

                                   
        


?>
                        

<?php  



          //Pokud je sklad = 1 zobrazit jeden //vychozi sklad
          //echo $sklady_count;
          
          if ($sklady_count==1 or $sklady_count==0) {

            
          }else{
            ?>
                    <div class="col-md-12">


                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Výběr skladu </h3>
                                
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                           <form action="index.php?strana=SkladXml" method="POST" id="idSkladuForm">                                  
                                              <select class="form-control" id="idSkladuCombo" name="idSkladuCombo">
                                                  <?php 

                                                   $sqldotaz= "SELECT distinct sw_sklad_jmeno, sw_sklad_id FROM `sklady_xml` WHERE `sw_id` = $sw_id Order by 1 ASC";
                                                   

                                                   if (!$sqldotaz) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                       $vysledek=$mysqli->query("$sqldotaz");
                                                                                                                               
                                                          while ($sklady_seznam=MySQLi_Fetch_Array($vysledek)):   
                                                                $SkladyCNT = $SkladyCNT + 1;
                                                                 $sklady_seznam["ts_Datum"];
                                                                 
                                                                 if ($idSkladuCombo==$sklady_seznam["sw_sklad_id"]) {
                                                                  $ComboSelected = " selected ";
                                                                 }else{
                                                                  $ComboSelected = "";
                                                                 }

                                                                 if ($sklady_seznam["sw_sklad_jmeno"]=="") {
                                                                  $JmenoSkladuCombo = "Výchozí sklad";
                                                                 }else{
                                                                  $JmenoSkladuCombo = $sklady_seznam["sw_sklad_jmeno"];
                                                                 }



                                                                 echo "<option $ComboSelected value=\"".$sklady_seznam["sw_sklad_id"]."\">".$JmenoSkladuCombo."</option>";
                                                          endwhile; 

                                                  ?>
                                                  
                                              </select>
                                           </form>     
                                </div>
                            </div>
                        </div>
                    </div>

            <?php 
          }



              //SkladSQL
              //Pro starou funckionalitu a demo
              if ($sklady_count==1 or $sklady_count==0) {
                $sql_sklad= "SELECT * FROM `sklady_xml` WHERE `sw_id` = $sw_id ";  
                $sql_sklad_chybejici= "SELECT * FROM `sklady_xml` WHERE `IsQuantityLow` = 1 and `sw_id` = $sw_id ";  
                $idSkladuCombo=1; //Prvotni sklad je vzdy 0
              }else{
                //visesklad
                if ($idSkladuCombo=="") {
                  $idSkladuCombo = $sklady_prvni;  
                }

                $sql_sklad= "SELECT * FROM `sklady_xml` WHERE `sw_id` = $sw_id  and `sw_sklad_id` = $idSkladuCombo ";  
                $sql_sklad_chybejici= "SELECT * FROM `sklady_xml` WHERE `IsQuantityLow` = 1 and `sw_id` = $sw_id  and `sw_sklad_id` = $idSkladuCombo ";  
              }

                
                //echo $sql_sklad_chybejici;

          

              $vysledek_sql_sklad=$mysqli->query($sql_sklad);
              $data_sql_sklad=MySQLi_Fetch_Array($vysledek_sql_sklad);
              $radku_existuje_pozadavek=$vysledek_sql_sklad->num_rows; 

              $vysledek_sql_skladchabejici=$mysqli->query($sql_sklad_chybejici);
              $data_sql_skladchabejici=MySQLi_Fetch_Array($vysledek_sql_skladchabejici);
              $radku_existuje_pozadavekchabejici=$vysledek_sql_skladchabejici->num_rows; 


          //echo $radku_existuje_pozadavek;

              $sklady_datum =date("d.m.Y H:i", strtotime($data_sql_sklad["sklady_datum"])); 
              
              $sklady_akt_hodn_skladu = number_format($data_sql_sklad["sklady_akt_hodn_skladu"], 2, ',', ' ');
              $sklady_akt_prijem_sklad = number_format($data_sql_sklad["sklady_akt_prijem_sklad"], 2, ',', ' ');
              $PocatecniStavSkladu = number_format($data_sql_sklad["PocatecniStavSkladu"], 2, ',', ' ');
              
              $sklady_prijem_sklad_bezDPH = number_format($data_sql_sklad["sklady_prijem_sklad_bezDPH"], 2, ',', ' ');


    ?>

    <div class="row">
      <div class="col-md-12 col-lg-6">
        <div class="row">
          <div class="col-md-6">
            <div class="panel panel-solid-success widget-mini">
              <div class="panel-body">
                <i class="icon-bar-chart"></i>
                <span class="total text-center"><b><font size="5"><?php echo $sklady_akt_hodn_skladu."&nbsp;".$Mena_Klienta; ?></font></b></span>
                <span class="title text-center">Aktuální hodnota skladu </span>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="panel widget-mini">
              <div class="panel-body">
                <i class="icon-bar-chart"></i>
                <span class="total text-center"><font size="5"><?php echo $sklady_akt_prijem_sklad."&nbsp;".$Mena_Klienta; ?></font></span>
                <span class="title text-center">Příjem na sklad celkem</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-12 col-lg-6">
       <div class="row">
        <div class="col-md-6">
          <div class="panel panel-solid-danger widget-mini">
            <div class="panel-body">
              <i class="icon-bar-chart"></i>
              <span class="total text-center"><font size="5"><b><?php echo $PocatecniStavSkladu."&nbsp;".$Mena_Klienta ?></b></font></span>              
              <span class="title text-center"> Počáteční stav skladu  </span>
            </div>
          </div>
        </div>
        
        <div class="col-md-3">
          <div class="panel widget-mini">
            <div class="panel-body">
              <i class="icon-bar-chart"></i>
              <span class="total text-center"><font size="5"><?php echo $radku_existuje_pozadavek; ?></font></span>
              <span class="title text-center">Počet položek</span>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="panel widget-mini">
            <div class="panel-body">
              <i class="icon-bar-chart"></i>
              <span class="total text-center"><font size="5"><?php echo $radku_existuje_pozadavekchabejici; ?></font></span>
              <span class="title text-center">Chybějící položky</span>
            </div>
          </div>
        </div>

      </div>           
    </div>  
  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <div class="panel panel-default" >
                        <div class="panel-heading">
                          <h3 class="panel-title"><font face="tahoma"><b>Aktuální stav zásob</b></font></h3>
                          <div class="actions pull-right">
                            <i class="fa fa-expand"></i>
                            <i class="fa fa-chevron-down"></i>
                            <i class="fa fa-times"></i>
                          </div>
                        </div>
        
                                    <div class="panel-body">
                                      <div class="table-responsive">                                
                                      <table id="sklad1" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead>
                                          <tr>
                                            <td align="center"><b>#</b></td>
                                            <td align="center"><b>PLU</b></td>
                                            <td align="center"><b>Název</b></td>
                                            <td align="center"><b>Počáteční stav</b></td>
                                            <td align="center"><b>Příjem na sklad</b></td>
                                            <td align="center"><b>Prodej</b></td>
                                            <td align="center"><b>Spotřeba</b></td>
                                            <td align="center"><b>Konečný stav</b></td>
                                          </tr>
                                        </thead>

                                        <tbody>
                                          
                                          <?php
                                              
                                          
                                                     $sqldotaz_Skladyxml= "SELECT * FROM `sklady_xml` WHERE `sw_id` = ".$sw_id." and `sw_sklad_id` = ".$idSkladuCombo ;
                                                     //echo $sqldotaz_Skladyxml;
                                                     if (!$sqldotaz_Skladyxml) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                                                    
                                                      $vysledek_Skladyxml=$mysqli->query("$sqldotaz_Skladyxml");
                                                      $pocet_radku_Skladyxml = $vysledek_Skladyxml->num_rows;                                       
                                                                                    
                                                          if ($pocet_radku_Skladyxml==0 and $sw_id!=0) {
                                                            //echo "nic nenalezeno.";  
                                                          }else {

                                                               if ($sw_id!=0) {
                                                                

                                                                           $SkladyxmlCNT = 0;
                                                                              
                                                                              while ($Skladyxml_seznam=MySQLi_Fetch_Array($vysledek_Skladyxml)):
                                                                                    $SkladyxmlCNT = $SkladyxmlCNT + 1;
                                                                                          echo "<tr>";
                                                                                              echo "<td align='center'>$SkladyxmlCNT</td>";
                                                                                              echo "<td>".$Skladyxml_seznam["PLU"]."</td>";
                                                                                              
                                                                                              //replace zakazany znaky
                                                                                              $JmenoProduktu = "";
                                                                                              $JmenoProduktu =str_replace('__apostrofa__', "'", $Skladyxml_seznam["CommodityName"]);
                                                                                              $JmenoProduktu =str_replace('__and__', "&", $JmenoProduktu);

                                                                                              echo "<td>".$JmenoProduktu."</td>";
                                                                                              
                                                                                              echo "<td align='center'>".$Skladyxml_seznam["QuantityBegin"]."</td>";
                                                                                              echo "<td align='center'>".$Skladyxml_seznam["QuantityIncomes"]."</td>";
                                                                                              echo "<td align='center'>".$Skladyxml_seznam["QuantitySells"]."</td>";
                                                                                              echo "<td align='center'>".$Skladyxml_seznam["QuantityUsages"]."</td>";
                                                                                              echo "<td align='center'>".$Skladyxml_seznam["QuantityEnd"]."</td>";
                                                                                              
                                                                                          echo "</tr>";
                                                                              endwhile;
                                                                }

                                                          }
                                          ?>


                                       
                                         </tbody>
                                        
                                       </table>
                                      </div>
                                     </div>
                      </div>
                    </div>


                    <div class="col-md-12">
                    <div class="panel panel-default" >
                      <div class="panel-heading">
                        <h3 class="panel-title"><font face="tahoma"><b>Seznam chybějícího zboží</b></font></h3>
                        <div class="actions pull-right">
                          <i class="fa fa-expand"></i>
                          <i class="fa fa-chevron-down"></i>
                          <i class="fa fa-times"></i>
                        </div>
                      </div>
                                    <div class="panel-body">
                                      <div class="table-responsive">                                
                                      <table id="sklad2" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead>
                                          <tr>
                                            <td align="center"><b>#</b></td>
                                            <td align="center"><b>PLU</b></td>
                                            <td align="center"><b>Název</b></td>
                                            <td align="center"><b>Hlídáno od ks</b></td>
                                            <td align="center"><b>Konečný stav</b></td>
                                          </tr>
                                        </thead>

                                        <tbody>
                                          <?php
                                              
                                          
                                                     $sqldotaz_Skladyxml= "SELECT * FROM `sklady_xml` WHERE `IsQuantityLow` = 1  and `sw_id` = ".$sw_id." and `sw_sklad_id` = ".$idSkladuCombo ;
                                                     //echo $sqldotaz_Skladyxml;
                                                     if (!$sqldotaz_Skladyxml) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                                                    
                                                      $vysledek_Skladyxml=$mysqli->query("$sqldotaz_Skladyxml");
                                                      $pocet_radku_Skladyxml = $vysledek_Skladyxml->num_rows;                                       
                                                                                    
                                                          if ($pocet_radku_Skladyxml==0 and $sw_id!=0) {
                                                            //echo "nic nenalezeno.";  
                                                          }else {

                                                               if ($sw_id!=0) {
                                                                

                                                                           $SkladyxmlCNT = 0;
                                                                              
                                                                              while ($Skladyxml_seznam=MySQLi_Fetch_Array($vysledek_Skladyxml)):
                                                                                    $SkladyxmlCNT = $SkladyxmlCNT + 1;
                                                                                          echo "<tr>";
                                                                                              echo "<td align='center'>$SkladyxmlCNT</td>";
                                                                                              echo "<td>".$Skladyxml_seznam["PLU"]."</td>";
                                                                                                                                                                                            //replace zakazany znaky
                                                                                              $JmenoProduktu = "";
                                                                                              $JmenoProduktu =str_replace('__apostrofa__', "'", $Skladyxml_seznam["CommodityName"]);
                                                                                              $JmenoProduktu =str_replace('__and__', "&", $JmenoProduktu);

                                                                                              echo "<td>".$JmenoProduktu."</td>";

                                                                                              echo "<td align='center'>".$Skladyxml_seznam["CheckAmount"]."</td>";
                                                                                              echo "<td align='center'>".$Skladyxml_seznam["QuantityEnd"]."</td>";
                                                                                              //echo "<td><div align=\"right\" style=\"width: 70%;\"><font size=\"-1\">".number_format($jednotlivci_seznam["Celkem_Credit_bezDPH"], 2, '.', '&nbsp;')."</font></div></td>";
                                                                                          echo "</tr>";
                                                                              endwhile;
                                                                }

                                                          }
                                          ?>
                                         
                                         </tbody>
                                       </table>
                                      </div>
                                     </div>
                       </div>
                     </div>
                  <br>
                                              
                  </div>




<?php
    if ( $sw_id!=0) {

  ?>                      
                        
                        
                                 <div class="row">
                                      <div class="col-md-12 col-lg-12">
                                        <div class="panel panel-default">
                                          <div class="panel-body ng-binding">
                                             <?php
                                               $originalDate = $datum;
                                               $newDate = date("d.m.Y", strtotime($originalDate));
                                               $newYear = date("Y", strtotime($originalDate));
                                             ?>
                                                  <div class="rada">
                                                    <form action="../str/akce/SkladXml.php" method="POST" >
                                                      <input type="hidden" class="form-control" name="sw_id" value="<?php echo $sw_id; ?>">
                                                      <input type="hidden" class="form-control" name="DeleteSkladData" value="1">
                                                      <input type="hidden" class="form-control" name="DeleteSklad" value="1">
                                                      <button type="submit" onClick="if(!confirm('Opravdu chcete SMAZAT kompletní stav skladu?')){return false;}" class="btn btn-danger"><i class="fa icon-trash"></i>Smazat kompletní stav skladu</button>
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
                                    <div class="panel-body">
                                      <font size="-2">Synchronizováno: 
                                        <b>
                                          <?php 
                                          echo $sklady_datum;

                                          $DURATION_end=microtime(true);
                                          $DURATION = $DURATION_end - $DURATION_start;

                                          ?>

                                        </b>
                                        - Automatické načtení dat probíhá každých 15 minut.&nbsp; Strana načtena za <b><?php echo round($DURATION,3)."s"; ?></b> 
                                      </font> 
                                    </div>
                                  </div>
                                </div>
                              </div>

                            </section>        
                          </section>

                          <!-- echo random_color(); -->








