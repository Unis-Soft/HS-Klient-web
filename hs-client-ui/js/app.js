
/*
      (               *        )           (   (     
      )\ )    *   ) (  `    ( /(     (     )\ ))\ )  
   ( (()/(  ` )  /( )\))(   )\())    )\   (()/(()/(  
   )\ /(_))  ( )(_)|(_)()\|((_)\  ((((_)(  /(_))(_)) 
  ((_|_))   (_(_())(_()((_)_ ((_)  )\ _ )\(_))(_))   
 _ | / __|  |_   _||  \/  | |/ /   (_)_\(_) _ \ _ \  
| || \__ \    | |  | |\/| | ' <     / _ \ |  _/  _/  
 \__/|___/    |_|  |_|  |_|_|\_\   /_/ \_\|_| |_|    

*/

//HLEDANI
$( "#txt_hledani_lidi" ).keyup(function() {
  var PocetZnaku = this.value.length;
  var CoHledat = $.trim($(this).val());;
  
  if (PocetZnaku>=3) {
    if(CoHledat){

         //oprava proti + v POSTU z JS do PHP
         var str = CoHledat;
         var res = str.replace("+", "_plus_");
         CoHledat = res;

        $.ajax({
            type:'POST',
            url :"/hs-client-ui/php/strana/ssf/ssf_lidi.php",                  // json datasource
            //data:'Akce='+encodeURI(CoHledat),
            //data:'Akce=Hledej&CoHledat='+CoHledat+'&Typ_Sortimentu_cross='+Typ_Sortimentu_cross,
            data:'Akce=Hledej&CoHledat='+encodeURI(CoHledat),

            beforeSend: function() {
              $("#ico_hledani_loader").show();
            },
            success:function(html){
              $('#contact-list').html(html);
              $("#ico_hledani_loader").hide();
            }
        }); 
  	}
  }
});

$( ".HledejPismeno").click(function() {
   var Pismeno = $(this).text();
	if(Pismeno){
        $.ajax({
            type:'POST',
            url :"/hs-client-ui/php/strana/ssf/ssf_lidi.php",                  // json datasource
            data:'Akce=HledejPismeno&Pismeno='+encodeURI(Pismeno),
	            beforeSend: function() {
	              $("#ico_hledani_loader").show();
	            },
	            success:function(html){
	              $('#contact-list').html(html);
	              $("#ico_hledani_loader").hide();
	            }
        }); 
  	}
});

//Hover mysi nad pismenkama a ztucneni + zmena kurzoru
 	$("#Abeceda_A").mouseenter(function() { $("#Abeceda_A").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_A").css("font-weight", "normal"); });
 	$("#Abeceda_B").mouseenter(function() { $("#Abeceda_B").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_B").css("font-weight", "normal"); });
 	$("#Abeceda_C").mouseenter(function() { $("#Abeceda_C").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_C").css("font-weight", "normal"); });
 	$("#Abeceda_D").mouseenter(function() { $("#Abeceda_D").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_D").css("font-weight", "normal"); });
 	$("#Abeceda_E").mouseenter(function() { $("#Abeceda_E").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_E").css("font-weight", "normal"); });
 	$("#Abeceda_F").mouseenter(function() { $("#Abeceda_F").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_F").css("font-weight", "normal"); });
 	$("#Abeceda_G").mouseenter(function() { $("#Abeceda_G").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_G").css("font-weight", "normal"); });
 	$("#Abeceda_H").mouseenter(function() { $("#Abeceda_H").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_H").css("font-weight", "normal"); });
 	$("#Abeceda_I").mouseenter(function() { $("#Abeceda_I").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_I").css("font-weight", "normal"); });
 	$("#Abeceda_J").mouseenter(function() { $("#Abeceda_J").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_J").css("font-weight", "normal"); });
 	$("#Abeceda_K").mouseenter(function() { $("#Abeceda_K").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_K").css("font-weight", "normal"); });
 	$("#Abeceda_L").mouseenter(function() { $("#Abeceda_L").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_L").css("font-weight", "normal"); });
 	$("#Abeceda_M").mouseenter(function() { $("#Abeceda_M").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_M").css("font-weight", "normal"); });
 	$("#Abeceda_N").mouseenter(function() { $("#Abeceda_N").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_N").css("font-weight", "normal"); });
 	$("#Abeceda_O").mouseenter(function() { $("#Abeceda_O").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_O").css("font-weight", "normal"); });
 	$("#Abeceda_P").mouseenter(function() { $("#Abeceda_P").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_P").css("font-weight", "normal"); });
 	$("#Abeceda_Q").mouseenter(function() { $("#Abeceda_Q").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_Q").css("font-weight", "normal"); });
 	$("#Abeceda_R").mouseenter(function() { $("#Abeceda_R").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_R").css("font-weight", "normal"); });
 	$("#Abeceda_S").mouseenter(function() { $("#Abeceda_S").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_S").css("font-weight", "normal"); });
 	$("#Abeceda_T").mouseenter(function() { $("#Abeceda_T").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_T").css("font-weight", "normal"); });
 	$("#Abeceda_U").mouseenter(function() { $("#Abeceda_U").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_U").css("font-weight", "normal"); });
 	$("#Abeceda_V").mouseenter(function() { $("#Abeceda_V").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_V").css("font-weight", "normal"); });
 	$("#Abeceda_W").mouseenter(function() { $("#Abeceda_W").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_W").css("font-weight", "normal"); });
 	$("#Abeceda_X").mouseenter(function() { $("#Abeceda_X").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_X").css("font-weight", "normal"); });
 	$("#Abeceda_Y").mouseenter(function() { $("#Abeceda_Y").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_Y").css("font-weight", "normal"); });
 	$("#Abeceda_Z").mouseenter(function() { $("#Abeceda_Z").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_Z").css("font-weight", "normal"); });
 	$("#Abeceda_~").mouseenter(function() { $("#Abeceda_~").css("font-weight", "bold");	$(this).css('cursor','pointer');}).mouseleave(function() { $("#Abeceda_~").css("font-weight", "normal"); });

//VALIDACE MOBILU
$( "#lidi_hs_cell").keyup(function() {
   var PocetZnaku = this.value.length;
   var CoHledat = $.trim($(this).val());;
   if (PocetZnaku>=9) {
            $.ajax({
            type:'POST',
            url :"strana/ssf/ssf_validaceMobilu.php",                  // json datasource
            data:'Akce=ValidaceMobilu&Cislo='+encodeURI(CoHledat),
            //alert("hello world");
            //beforeSend: function() {
            //},
            success:function(html){
              //alert(html);  
              if (html>0) {
                $("#MobilExistuje").show();
                $("#UlozitZmenyKartaOsobyButton").hide();
              }else{
                $("#MobilExistuje").hide();
                $("#UlozitZmenyKartaOsobyButton").show();
              }              
            }
        }); 
   }else{
    $("#MobilExistuje").hide();
   }
});







function isEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);
}

function isMobile(mobile) {
  //var regex = /(?:\+42|0042|0)\d{7}/;
  var regex = /^(\+?420)?(2[0-9]{2}|3[0-9]{2}|4[0-9]{2}|5[0-9]{2}|72[0-9]|73[0-9]|77[0-9]|60[1-8]|56[0-9]|70[2-5]|79[0-9])[0-9]{3}[0-9]{3}$/;

  var validace = mobile.replace(" ", "");
  return regex.test(validace.replace(" ", ""));
}





 //Validace emailu hodnoceni
 //$(window).on('keyup keypress', function(e) {
//$( "#TestDotaznikuEmail" ).keyup(function() {
  $( "#TestDotaznikuEmail" ).on('input keyup mouseup click change', function(e) {
    var PocetZnakuEmailHOdnoceni = $(this).val();
    var jeEmailPlatny = this.validity ? (this.validity.valid && PocetZnakuEmailHOdnoceni.trim() !== "") : isEmail(PocetZnakuEmailHOdnoceni);
    $("#btnHOdnoceniEmail").show().prop("disabled", !jeEmailPlatny).attr("aria-disabled", jeEmailPlatny ? "false" : "true");
 });

 //Validace mobilu hodnoceni
 $( "#TestDotaznikuSMS" ).on('input keyup mouseup click change', function(e) {
    var PocetZnakuSMSHOdnoceni = $(this).val();
    var jeMobilPlatny = isMobile(PocetZnakuSMSHOdnoceni);
    $("#btnHodnoceniSMS").show().prop("disabled", !jeMobilPlatny).attr("aria-disabled", jeMobilPlatny ? "false" : "true");
 });
    

 //Validace loginu na pridani poduzivatele
 $( "#k_poduzivatele_login" ).keyup(function() {
    var PocetZnakuLogin = this.value.length;

    if (PocetZnakuLogin>=3) {
     $("#btn_pridat_poduzivatele").show();      
     $("#k_poduzivatele_login_ko").hide(); 
      }else{
     $("#k_poduzivatele_login_ko").show(); 
     $("#btn_pridat_poduzivatele").hide();      
    }
 });



/*
 //Validace hesla na pridani poduzivatele
 $( "#k_poduzivatele_heslo" ).keyup(function() {
    var PocetZnakuHeslo = this.value.length;

    if (PocetZnakuHeslo>=2) {
     
     //$("#btn_pridat_poduzivatele").show();      
     //$("#k_poduzivatele_heslo_ko").hide(); 
      }else{
     //$("#k_poduzivatele_heslo_ko").show(); 
     //$("#btn_pridat_poduzivatele").hide();      
    }
 });
*/

 

 /* Pridani managera -----------------------------------------------------------------------------------*/
 //Validace jmeno na pridani poduzivatele
 $( "#k_poduzivatele_jmeno" ).keyup(function() {
   var PocetZnakujmeno = this.value.length;    
    if (PocetZnakujmeno>=2) {
     $("#btn_pridat_poduzivatele").prop("disabled",false);
      }else{
     $("#btn_pridat_poduzivatele").prop("disabled",true);
    }
 });

 //Validace emailu na pridani poduzivatele
 $("#k_poduzivatele_email").on('keyup change click keydown', function() {
   var PocetZnakuEmail = $(this).val();
    if (isEmail(PocetZnakuEmail)) {
      $("#btn_pridat_poduzivatele").prop("disabled",false);
      }else{
      $("#btn_pridat_poduzivatele").prop("disabled",true);
    }

 });

 //Validace hesla na pridani poduzivatele
 $( "#k_poduzivatele_heslo" ).keyup(function() {
    var PocetZnakuHeslo = this.value.length;
    if (PocetZnakuHeslo>=4) {
         $("#btn_pridat_poduzivatele").prop("disabled",false);
    }else{
        $("#btn_pridat_poduzivatele").prop("disabled",true);
    }
 });
/* ----------------------------------------------------------------------------------------------------*/


 //Autosubmit v combu managera a profilovky
 $('#ZmenaManageraProfilovkaCombo').on('change', function() {
    var $form = $(this).closest('form');
    $form.find('input[type=submit]').click();
 });




/*Manager detail zmena hesla*/
$( "#k_poduzivatele_heslo" ).keyup(function() {
    var PocetZnakuHeslo = this.value.length;
    if (PocetZnakuHeslo>=4) {
         $("#k_poduzivatele_zmenit_heslo_ko").prop("disabled",false);
    }else{
        $("#k_poduzivatele_zmenit_heslo_ko").prop("disabled",true);
    }
 });


 
 


















 
 //Zmena vyberu prav na provozovne
 $('#VybranaPobockaID').on('change', function() {
    
      //$("#ZmenaPobockyPravaForm").submit();
    var $form = $(this).closest('form');
    $form.find('input[type=submit]').click();
      //document.forms[ZmenaPobockyPravaForm].submit();
     //$('form').submit();
 });

 //Zmena vyberu prav na provozovne Obsluha
 $('#VybranaPobockaIDObsluha').on('change', function() {
    
      //$("#ZmenaPobockyPravaForm").submit();
    var $form = $(this).closest('form');
    $form.find('input[type=submit]').click();
      
 });

 //Zmena vyberu prav na provozovne - soupatko
 $('#chck1,#chck2,#chck3,#chck4,#chck5,#chck6,#chck7,#chck8,#chck9,#chck10,#chck11,#chck12,#chck13,#chck14,#chck15,#chck16,#chck17,#chck18,#chck19,#chck20,#chck_ZobrazitCitlivaData').on('change', function() {
    //alert("hello world");
    var $form = $(this).closest('form');
    $form.find('input[type=submit]').click();
 });


 //Prava hodnoceni
 $('#chck_Hodnoceni,#chck_HodnoceniVsech').on('change', function() {
    //alert("hello world");
    var $form = $(this).closest('form');
    $form.find('input[type=submit]').click();
 });


 $('#btnPrihlaseniObsluhy').click(function(){
   //window.location.href='the_link_to_go_to.html';
   alert("ahoj");
});


$('.DivObsluha').click(function(e) {  
    var JmenoObsluhy = $(this).text();
        
    $('#inputObsluhaJmeno').val(JmenoObsluhy);
    //$('#inputObsluhaJmeno').prop('disabled', true);
}); 

//Hodnoceni
 $('#VybraneStrediskoID').on('change', function() {
    
    var $form = $(this).closest('form');
    $form.find('input[type=submit]').click();
      
 });


 $('#PuvodniEmailSablona').click(function(){
    window.location.replace("https://klient.hairsoft.cz/str/index.php?strana=SpokojenostNastaveni&VychoziSablonaEmail=1");
 });


 $('#PuvodniSMSSablona').click(function(){
    window.location.replace("https://klient.hairsoft.cz/str/index.php?strana=SpokojenostNastaveni&VychoziSablonaSMS=1");
 });


 function UlozitButtonFce() {
    $("#UlozitDoGalerie").hide();      
    $("#LoadingUlozit").show();  
 }




         



function translateContent() {
    var selectedLanguage = document.getElementsByName("languageCombo")[0].value;
    var contentElement = document.getElementById("contentLanguage");
    
    translateTextNodes(contentElement, selectedLanguage);
}

function translateTextNodes(element, targetLanguage) {
    var childNodes = element.childNodes;

    childNodes.forEach(function(node) {
        if (node.nodeType === Node.TEXT_NODE) {
            // Překlad textového uzlu
            translateText(node.nodeValue, targetLanguage)
                .then(function(translatedText) {
                    node.nodeValue = translatedText;
                })
                .catch(function(error) {
                    console.error("Chyba překladu:", error);
                });
        } else if (node.nodeType === Node.ELEMENT_NODE) {
            // Rekurzivně překlad vnitřních uzlů (např. dalších elementů)
            translateTextNodes(node, targetLanguage);
        }
    });
}

async function translateText(text, targetLanguage) {
    var apiUrl = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl=" + targetLanguage + "&dt=t&q=" + encodeURIComponent(text);

    var response = await fetch(apiUrl);
    var data = await response.json();

    return data[0][0][0];
}




/*FOCENI*/
  /* $("#cameraFileInput").on('change', function(){
        alert('hssu');
      })

      $('#cameraFileInput').bind('click', function() {
        alert('Userss clicked on "foo."');
      });
    */  
  /*
      $('#cameraFileInput').bind('click', function() {
            alert('hu');
            //$('#cameraFileInput').trigger('click'); // will behave as if #select-5 is clicked.
      });

      $('#test').bind('click', function() {
            alert('hu');
            //$('#cameraFileInput').trigger('click'); // will behave as if #select-5 is clicked.
      });

*/
      
    
 



