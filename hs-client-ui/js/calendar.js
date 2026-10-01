/* Shared calendar presentation and locale; input values and callbacks stay unchanged. */
(function(){'use strict';
 var locales={
 cs:{monthNames:['Leden','Únor','Březen','Duben','Květen','Červen','Červenec','Srpen','Září','Říjen','Listopad','Prosinec'],monthNamesShort:['Led','Úno','Bře','Dub','Kvě','Čer','Čvc','Srp','Zář','Říj','Lis','Pro'],dayNames:['Neděle','Pondělí','Úterý','Středa','Čtvrtek','Pátek','Sobota'],dayNamesShort:['Ne','Po','Út','St','Čt','Pá','So'],dayNamesMin:['Ne','Po','Út','St','Čt','Pá','So'],prevText:'Předchozí měsíc',nextText:'Následující měsíc',currentText:'Dnes',closeText:'Zavřít',weekHeader:'Týd'},
 sk:{monthNames:['Január','Február','Marec','Apríl','Máj','Jún','Júl','August','September','Október','November','December'],monthNamesShort:['Jan','Feb','Mar','Apr','Máj','Jún','Júl','Aug','Sep','Okt','Nov','Dec'],dayNames:['Nedeľa','Pondelok','Utorok','Streda','Štvrtok','Piatok','Sobota'],dayNamesShort:['Ne','Po','Ut','St','Št','Pi','So'],dayNamesMin:['Ne','Po','Ut','St','Št','Pi','So'],prevText:'Predchádzajúci mesiac',nextText:'Nasledujúci mesiac',currentText:'Dnes',closeText:'Zavrieť',weekHeader:'Týž'},
 en:{monthNames:['January','February','March','April','May','June','July','August','September','October','November','December'],monthNamesShort:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],dayNames:['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],dayNamesShort:['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],dayNamesMin:['Su','Mo','Tu','We','Th','Fr','Sa'],prevText:'Previous month',nextText:'Next month',currentText:'Today',closeText:'Close',weekHeader:'Wk'},
 de:{monthNames:['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'],monthNamesShort:['Jan','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez'],dayNames:['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],dayNamesShort:['So','Mo','Di','Mi','Do','Fr','Sa'],dayNamesMin:['So','Mo','Di','Mi','Do','Fr','Sa'],prevText:'Vorheriger Monat',nextText:'Nächster Monat',currentText:'Heute',closeText:'Schließen',weekHeader:'KW'}
 };
 function language(){var l=document.documentElement.lang;return locales[l]?l:'cs';}
 function locale(){return jQuery.extend({showAnim:''},locales[language()]);}
 // A touch date field opens the picker without summoning the software keyboard.
 var touch=window.matchMedia('(pointer:coarse)');
 function prepareInput(node){if(!node||!node.matches||!node.matches('input.hasDatepicker'))return;
  if(touch.matches){if(!node.hasAttribute('data-hs-calendar-readonly'))node.setAttribute('data-hs-calendar-readonly',node.readOnly?'1':'0');node.readOnly=true;}
  else if(node.hasAttribute('data-hs-calendar-readonly')){node.readOnly=node.getAttribute('data-hs-calendar-readonly')==='1';node.removeAttribute('data-hs-calendar-readonly');}
 }
 document.addEventListener('pointerdown',function(e){prepareInput(e.target);},true);
 document.addEventListener('focus',function(e){prepareInput(e.target);},true);
 window.addEventListener('resize',function(){document.querySelectorAll('input.hasDatepicker').forEach(prepareInput);});
 function settings(inst){if(inst){jQuery.extend(inst.settings,locale());inst.dpDiv.attr('data-hs-i18n-ignore','');}}
 function apply(){var $=window.jQuery;if(!$||!$.datepicker)return;$.datepicker.setDefaults(locale());$('.hasDatepicker').each(function(){prepareInput(this);settings($.data(this,'datepicker'));});var inst=$.datepicker._curInst;if(inst&&$.datepicker._datepickerShowing){settings(inst);$.datepicker._updateDatepicker(inst);}}
 function boot(){var $=window.jQuery;if(!$||!$.datepicker)return;if(!$.datepicker.hsCalendar){$.datepicker.hsCalendar=true;var show=$.datepicker._showDatepicker;$.datepicker._showDatepicker=function(input){var node=input&&input.target?input.target:input;prepareInput(node);settings(node&&$.data(node,'datepicker'));$.datepicker.setDefaults(locale());return show.apply(this,arguments);};}apply();}
 boot();document.addEventListener('DOMContentLoaded',function(){setTimeout(boot,0);});window.addEventListener('load',boot);document.addEventListener('hs:languagechange',apply);
}());
