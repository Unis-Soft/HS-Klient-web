# HairSoft Klient V121 – Tržby od počátku věků

Kompletní balíček navazující na V120 s vycentrovanými šipkami ročních tržeb.

## Co je nové

- Tržby od počátku věků používají vzhled ročních tržeb: horní karty, nadpisy, zaoblené tabulky, mobilní karty a Export.
- Tři sloupcové grafy používají přímo schválené vykreslování Dashboardu, včetně barevných přechodů, zaoblení, os a tooltipů pro myš i dotyk. Šířka se přizpůsobí počtu let; delší historii lze posouvat uvnitř grafu.
- Kruhový graf účtenek zachovává kruh, legendu, celkový počet a tooltip. Prázdná historie zobrazuje nulové grafy bez fiktivních hodnot.
- Mobilní karty vyplňují stejnou šířku jako Export, také při otočení telefonu. Ovládání panelů je na mobilu skryté.
- Tabulky se zobrazí až po přípravě moderního vzhledu. Správa dat zůstává samostatně dole.
- Součty se skládají podle konkrétních roků a vybrané pobočky. Zachovány jsou tržby, služby, prodej, ceniny, kredit a částky bez DPH. Celkový přehled vychází ze středisek, přehled obsluh z jednotlivců.
- Chybějící roky uvnitř historie jsou nulové, nepoužité budoucí tabulky rozsah neprodlužují. Prodej ani ceniny se neschovají, když jsou služby nulové.
- Meziroční přehled používá označení vývoje tržeb: původní data neobsahují náklady pro výpočet zisku. U nulového předchozího roku je procentní změna pomlčka, nikoliv nekonečno. U záporné základny se počítá vůči její absolutní hodnotě.
- PDF exporty mají stejné členění jako roční tržby, včetně pobočky, rozsahu historie, měny a částek bez DPH. Meziroční přehled má vlastní pětisloupcový export. Doplněny české, slovenské, anglické a německé texty.
- Otevření stránky nemění uložený měsíc ani rok. Chyba načítání zobrazí zprávu; není vydávána za nulové tržby.

## Nahrání

Nahrajte složky `hs-client-ui` a `str` do kořene webu jako u předchozích verzí. Původní `str/strana/TrzbyOdPocatku.php` ponechte na serveru; moderní stránku načítá společné přesměrování v klientském rozhraní.

Pokud je nahraná kompletní V120, stačí přepsat/doplnit:

- hs-client-ui/php/index.php
- hs-client-ui/php/strana/TrzbyOdPocatku.php
- hs-client-ui/js/lifetime-revenue.js
- hs-client-ui/js/lifetime-revenue-pdf.js
- hs-client-ui/css/lifetime-revenue.css
- hs-client-ui/js/i18n.js
- hs-client-ui/VERSION.txt

Potom obnovte stránku pomocí Ctrl+F5. Nevyžaduje změnu databázového schématu. Stávající potvrzení a obsluha mazání všech tržeb zůstávají zachovány.

## Ověření

PHP syntaxe a lokální modelové scénáře: běžná historie, mezera mezi roky, prázdné tabulky, žádné tabulky, prodej bez služeb, odmítnutý přístup, chyba dotazu a zachování session. Kontrola součtů, řazení let a nulové základny procent.

Chrome s původním Chart.js 2.3.0 a DataTables 1.10.15: šířky 375, 932 a 1366 px, myš i dotyk, tooltipy, nulové grafy, poměr stran kruhu, plná šířka mobilních karet, skryté mobilní ovládání a souběh modulů denních/měsíčních/ročních tržeb. PDF všech tří tabulek vygenerována a vizuálně zkontrolována.

Připojení k živé databázi ani nasazení na server nebylo v tomto prostředí testováno. Databázové scénáře používají modelové výsledky dotazů. Soubory schválených denních, měsíčních, ročních tržeb a Dashboardu jsou shodné s V120; společně se mění pouze načítání nové stránky a doplnění překladů.
