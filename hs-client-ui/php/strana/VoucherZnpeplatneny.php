    
<?php
  if (!defined('HS_CLIENT_UI_ROOT')) { define('HS_CLIENT_UI_ROOT', dirname(__DIR__, 3)); }
  require HS_CLIENT_UI_ROOT . '/str/strana/_zabezpeceni.php';
  require HS_CLIENT_UI_ROOT . '/fce/SpolecneFunkce.php';
  require HS_CLIENT_UI_ROOT . '/fce/GeneratorBarev.php';
  //require_once '../../cfg/nastaveni.php';
  
  
  $jmenoStranky = "Zneplatněné Vouchery";
  $jmenoStrankyPopis = "Přehled zneplatněných voucherů";
  
  if ($_SESSION["pobocka_jmeno"]=="") {
    $pobocka_jmeno =  "Nevybrána" ;
  }else {
    $pobocka_jmeno =  $_SESSION["pobocka_jmeno"] ;  
  }

      

      /*
        if ($datum=="") {
          $datum = date("d.m.Y"); 
        }
        
               $originalDate = $datum;
               $newDate = date("d.m.Y", strtotime($originalDate));
               
               $newYear = date("Y", strtotime($originalDate));
        */       
        
        // $datum ="2016-10-20";
        
       
         
        
       
       
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////
  ////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //////////////////////////////////////DEBUG/////////////////////////////////////////////////////////////////
  
  $skupina_id = $_SESSION["skupina_id"];
  $sw_id = $_SESSION["pobocka_id"];

  if ($_SESSION["k_id"]=="10") {
    //$Uzivatel_ID =11;
   // $sw_id = 1081;
    
    //$skupina_id = 5;  // BarberCooper
    //$skupina_id = 84; // Thomas
   //$sw_id = 961;
  }
  
        //////////////////////////////////////////////////////////////////////////////////////
        //DEMO
        if ($_SESSION["pobocka_id"]=="") {
          $sw_id = 0;
          $skupina_id = 0;  
        }
        //////////////////////////////////////////////////////////////////////////////////////
  
   $SQL_ROK = $_SESSION["SQL_ROK"] ; 
  
  
      if ($rok=="1") {
          //$TestSQL_ROK = $SQL_ROK +1; 
          //$TestTabulka = "trzby_strediska_".$TestSQL_ROK;
          //$TestSql = "SHOW TABLES LIKE '$TestTabulka'";
          //$TestResult = $mysqli->query($TestSql);
         
          //if ($TestResult->num_rows > 0) {
            $SQL_ROK = $SQL_ROK+1; 
            $_SESSION["SQL_ROK"] = $SQL_ROK;
          //}     
      }elseif ($rok=="0") {
          //$TestSQL_ROK = $SQL_ROK -1;
          //$TestTabulka =  "trzby_strediska_".$TestSQL_ROK;
          //$TestSql = "SHOW TABLES LIKE '$TestTabulka'";
          //$TestResult = $mysqli->query($TestSql);
          
          //if ($TestResult->num_rows > 0) {
            $SQL_ROK = $SQL_ROK-1; 
            $_SESSION["SQL_ROK"] = $SQL_ROK;
          //}
      }
  
  
        
        //Mena
        $Mena_Klienta = $_SESSION["Mena_Klienta"];   

       
     


?>


<section class="main-content-wrapper hs-voucher-shell">            
  <div class="pageheader">                
    <h1><?php echo $jmenoStranky; ?></h1>                
    <p class="description">
      <?php echo $jmenoStrankyPopis; ?>
    </p>                
    <div class="breadcrumb-wrapper hidden-xs">                    
      <span class="label">Pobočka:</span>                    
      <ol class="breadcrumb">                        
        <li class="active"><b><?php echo $pobocka_jmeno; ?></b><?php echo JePobockaOnline($sw_id,$mysqli) ?></li> 
      </ol>
       
     
                    
    </div>            
  </div>            
  <section id="main-content" class="hs-voucher-page" data-voucher-page="VoucherZnpeplatneny">                
  
  <?php
                               
                               //WHERE NEPLATNY
                                $VoucherNeplatnyAnd = " `vou_stav` = 'Neplatný' and ";
                                $VoucherNeplatny    = " `vou_stav` = 'Neplatný' ";


     
                                //Kolik voucher celkem
                                $select_na_sumu_voucher= "SELECT COUNT(*)as 'CNT_Voucheru' FROM `vouchers` WHERE $VoucherNeplatnyAnd `vou_stav_skupinaID` = '$skupina_id'";
                                if (!$select_na_sumu_voucher) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_sumu_voucher=$mysqli->query("$select_na_sumu_voucher");
                                $data_suma_voucher=MySQLi_Fetch_Array($vysledek_na_sumu_voucher);
    
                                // Celkova hodnota voucher
                                $select_na_sumu_voucheru_hodnota= "SELECT SUM(`vou_hodnota_voucheru`) as 'SUM_Hodnota_Voucheru' FROM `vouchers` WHERE $VoucherNeplatnyAnd `vou_stav_skupinaID` = '$skupina_id'";
                                if (!$select_na_sumu_voucheru_hodnota) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_sumu_voucheru_hodnota=$mysqli->query("$select_na_sumu_voucheru_hodnota");
                                $data_suma_voucher_hodnota=MySQLi_Fetch_Array($vysledek_na_sumu_voucheru_hodnota);
                                
                                //Cerpano voucher
                                $select_na_cerpano_voucher= "SELECT SUM(`vou_cerpana_castka`) as 'SUM_Cerpano_SUM' FROM `vouchers` WHERE $VoucherNeplatnyAnd `vou_stav_skupinaID` = '$skupina_id'";
                                //echo $select_na_cerpano_voucher;
                                if (!$select_na_cerpano_voucher) { die('Chyba pripojeni do DB!');}
                                $vysledek_na_cerpano_voucher=$mysqli->query("$select_na_cerpano_voucher");
                                $suma_cerpano_voucher=MySQLi_Fetch_Array($vysledek_na_cerpano_voucher);

                                //Zustatek
                                $ZustatekSUMVoucheru = $data_suma_voucher_hodnota["SUM_Hodnota_Voucheru"] - $suma_cerpano_voucher["SUM_Cerpano_SUM"];
  ?>
  
                <div class="row">
                            <div class="col-md-3">
                                <div class="panel panel-solid-success widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $data_suma_voucher["CNT_Voucheru"] /*floor(($suma_kreditu_dobito_Cena_sms - $suma_sms_odeslano))*/ ." "; ?></font></span>
                                        <span class="title text-center">Záznamů</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $data_suma_voucher_hodnota["SUM_Hodnota_Voucheru"]."&nbsp;".$Mena_Klienta ." "; ?></font></span>
                                        <span class="title text-center">Celková&nbsp;hodnota</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel panel-solid-danger widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo $ZustatekSUMVoucheru."&nbsp;".$Mena_Klienta  ?></font></span>
                                        <span class="title text-center">Zůstatek&nbsp;</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="panel widget-mini">
                                    <div class="panel-body">
                                        <i class="icon-bar-chart"></i>
                                        <span class="total text-center"><font size="5"><?php echo  $suma_cerpano_voucher["SUM_Cerpano_SUM"]."&nbsp;".$Mena_Klienta ?></font></span>              
                                        <span class="title text-center">Čerpáno&nbsp;</span>
                                    </div>
                                </div>
                            </div>
                  </div>             
                
              
            
           
                 
                 
                 <div class="row">
                    <div class="col-md-12">
                            
                        <div class="panel panel-default hs-voucher-panel" >
                            <div class="panel-heading">
                                <h3 class="panel-title" data-hs-voucher-label="Zneplatněné vouchery"><font face="tahoma"><b>Zneplatněné vouchery</b></font></h3>
                                <div class="actions pull-right">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-chevron-down"></i>
                                    <i class="fa fa-times"></i>
                                </div>
                            </div>
                            <div class="panel-body">
                               
                                
                                <table id="example" class="table table-striped table-bordered" cellspacing="0" width="99.8%" >
                                    <thead>
                                        <tr>
                                            <td align="center" data-hs-voucher-header="#"><b>#</b></td>
                                            <td align="center" data-hs-voucher-header="Datum prodeje"><b>Datum prodeje</b></td>
                                            <td align="center" data-hs-voucher-header="Prodejce"><b>Prodejce</b></td>
                                            <td align="center" data-hs-voucher-header="Placeno"><b>Placeno</b></td>
                                            <td align="center" data-hs-voucher-header="Realizoval"><b>Realizoval</b></td>
                                            <td align="center" data-hs-voucher-header="Kód"><b>Kód</b></td>
                                            <td align="center" data-hs-voucher-header="Položka"><b>Položka</b></td>
                                            <td align="center" data-hs-voucher-header="Hodnota"><b>Hodnota</b></td>
                                            <td align="center" data-hs-voucher-header="Před.čerpání"><b>Před.čerpání</b></td>
                                            <td align="center" data-hs-voucher-header="Čerpáno"><b>Čerpáno</b></td>
                                            <td align="center" data-hs-voucher-header="Zůstatek"><b>Zůstatek</b></td>
                                            <td align="center" data-hs-voucher-header="Platnost"><b>Platnost</b></td>
                                            <td align="center" data-hs-voucher-header="Do data"><b>Do data</b></td>
                                            <td align="center" data-hs-voucher-header="Datum čerpání"><b>Datum čerpání</b></td>
                                            <td align="center" data-hs-voucher-header="Stav"><b>Stav</b></td>
                                            

                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        



                                                 $sqldotaz_voucher= "SELECT * FROM `vouchers` WHERE $VoucherNeplatnyAnd `vou_stav_skupinaID` = '$skupina_id' order by 1 DESC";
                                                  if (!$sqldotaz_voucher) { die('Chyba pripojeni do DB! - kontaktujte Administrátora systému!');}                   
                                                    $vysledek_voucher=$mysqli->query("$sqldotaz_voucher");                                       
                                                  
                                                  
                                                  $pocitadlo_radku_voucher = 0;
                                                  $PocitadloSUMvoucheru = 0;
                                                  
                                                  
                                                  while ($option=MySQLi_Fetch_Array($vysledek_voucher)):   
                                                     
                                                     
                                                    $pocitadlo_radku_voucher = $pocitadlo_radku_voucher +1;   
                                                     
                                                    $Zustatek_vypocitany = $option["vou_hodnota_voucheru"]-$option["vou_cerpana_castka"];


                                                    $UpraveneCerpani = $option["vou_posledni_cerpani"];
                                                    
                                                    if($UpraveneCerpani=="0000-00-00 00:00:00"){
                                                      $UpraveneCerpani = "";
                                                    }else{
                                                      $UpraveneCerpani = VlozNedelitelneMezery(date("d.m.Y v H:i", strtotime($UpraveneCerpani)));
                                                    }

                                                     switch (trim($option["vou_stav"])) {
                                                       case 'Neplatný':
                                                         $StavStyle = " style=\"color: #e45f60;\" ";
                                                        break;

                                                        case 'V oběhu':
                                                         $StavStyle = " style=\"color: #49bcb9;\" ";
                                                        break;

                                                        case 'Vyčerpaný':
                                                         $StavStyle = " style=\"color: #8eb5d2;\" ";
                                                        break;

                                                        case 'Částečně čerpaný':
                                                         $StavStyle = " style=\"color: #56688c;\" ";
                                                        break;
                                                      
                                                      default:
                                                        # code...
                                                        break;
                                                    }


                                                    echo "<tr>";
                                                     
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">$pocitadlo_radku_voucher</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 90%;\">".VlozNedelitelneMezery(date("d.m.Y v H:i", strtotime($option["vou_datum_vytvoreni"])))."</div></td>";
                                                          echo "<td><div align=\"left\" style=\"width: 90%;\">".$option["vou_jmeno_uzivatele_prodal"]."</div></td>";
                                                          echo "<td><div align=\"left\" style=\"width: 90%;\">".$option["vou_placeno"]."</div></td>";
                                                          echo "<td><div align=\"left\" style=\"width: 90%;\">".$option["vou_jmeno_uzivatele_cerpal"]."</div></td>";
                                                          echo "<td><div align=\"left\" style=\"width: 90%;\">".$option["vou_kod"]."</div></td>";
                                                          echo "<td><div align=\"left\" style=\"width: 90%;\">".$option["vou_nazev_voucheru"]."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">".VlozNedelitelneMezery(number_format($option["vou_hodnota_voucheru"], 2, ',', ' ')." ".$Mena_Klienta)."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">".VlozNedelitelneMezery(number_format($option["vou_castka_predchozi"], 2, ',', ' ')." ".$Mena_Klienta)."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">".VlozNedelitelneMezery(number_format($option["vou_cerpana_castka"], 2, ',', ' ')." ".$Mena_Klienta)."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 70%;\">".VlozNedelitelneMezery(number_format($Zustatek_vypocitany, 2, ',', ' ')." ".$Mena_Klienta)."</div></td>";
                                                          echo "<td><div align=\"center\" style=\"width: 90%;\">".$option["vou_doba_platnosti_mesice"]."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 90%;\">".VlozNedelitelneMezery(date("d.m.Y v H:i", strtotime($option["vou_datum_platnosti_do"])))."</div></td>";
                                                          echo "<td><div align=\"right\" style=\"width: 90%;\">".$UpraveneCerpani."</div></td>";
                                                          echo "<td ".$StavStyle."><div align=\"center\" style=\"width: 90%;\">".VlozNedelitelneMezery($option["vou_stav"])."</div></td>";
                                                          
                                                          


                                                     /*
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">$pocitadlo_radku_kredit</div></td>";
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">".date("d.m.Y v H:i", strtotime($option["sms_timestamp"]))."</div></td>";
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">".number_format($option["sms_dobito"], 2, ',', ' ')." ".$Mena_Klienta."</div></td>";
                                                     
                                                     $PocitadloSUMkreditu = $PocitadloSUMkreditu + ($option["sms_dobito"] ); 
                                                                                                          
                                                     $dobito_kredit =  number_format($option["sms_dobito"] / $suma_kreditu_dobito_Cena, 0, ',', ' ') . " SMS";
                                                     echo "<td><div align=\"right\" style=\"width: 70%;\">".$dobito_kredit."</div></td>";
                                                     */       
                                                    echo "</tr>";
                                                  endwhile;
                                        ?>
                                                                           
                                    </tbody>
                                </table>
                                                 <br>
                                                  <div class="rada">
                                                    <form action="https://klient.hairsoft.cz/str/index.php?strana=Voucher" method="POST" >
                                                      <button type="submit" class="btn btn-info btn-square" data-hs-voucher-label="Zpět na přehled Voucherů"><i class="fa fa-chevron-left"></i>Zpět na přehled Voucherů</button>
                                                    </form>
                                                  </div>

                                                  <div class="rada">
                                                    <form action="https://klient.hairsoft.cz/str/index.php?strana=Voucher" method="POST" >
                                                      
                                                      <input type="hidden" class="form-control" name="SmazatNeplatnouCeninu" value="1">

                                                      <button type="submit" onClick="if(!confirm('Opravdu chcete SMAZAT všechny tyto neplatné ceniny i jejich historii? Tato akce je nevratná!')){return false;}" class="btn btn-danger" data-hs-voucher-label="Smazat neplatné ceniny"><i class="fa icon-trash"></i>Smazat neplatné ceniny</button>
                                                    </form>
                                                  </div>
                            </div>
                        </div>
                      </div>
                  </div>

            
                 
                 
                  <?php
                        
                        //echo "Dodelat paticku po prvnim voucheru + demo data. ";
                        //posledni zaznam v tabulce
                        $sql_PosledniAktualizace= "SELECT `vou_zmena` FROM `vouchers` WHERE `vou_stav_skupinaID` = $skupina_id order by `vou_zmena` Desc LIMIT 1";
                        //echo $sql_PosledniAktualizace;
                        $vysledek_sql_PosledniAktualizace=$mysqli->query($sql_PosledniAktualizace);
                        $data_sql_PosledniAktualizace=MySQLi_Fetch_Array($vysledek_sql_PosledniAktualizace);
                        //$CelkoveTrzby = number_format($data_sql_PosledniAktualizace["TrzbyCelkem"], 2, ',', ' ');
                        $PosledniAktualizace = $data_sql_PosledniAktualizace["vou_zmena"];
                        
                        if ($PosledniAktualizace=="") {
                          $PosledniAktualizace = " nezjištěno ";
                        }
                          
                         
                        
                           if (strtotime($PosledniAktualizace)!="") {
                        ?>
                                                                  <div class="row">
                                                                  <div class="col-md-12">
                                                                      <div class="panel panel-default hs-voucher-panel">
                                                                          <div class="panel-body">
                                                                              <font size="-2">Poslední změna: 
                                                                                  <b>
                                                                                                                              <?php 
                                                                                                                                
                                                                                                                                $newDate_stav1 = date("d.m.Y v H:i", strtotime($PosledniAktualizace));
                                                                                                                                echo $newDate_stav1 ; 
                                                                                                                              
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
                          
                        <?php
                          }
                        ?>     
                 
                 
                 
                        
                
               
                  
  </section>        
</section>




 
