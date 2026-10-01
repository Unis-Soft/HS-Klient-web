# HairSoft Klient V126 – doladění Skladu

- Mazací tlačítko používá pevný český zdroj překladu a správně reaguje na přepnutí jazyka. Při češtině již nezůstává anglický popisek.
- Záporné skladové hodnoty jsou červené a výraznější, v tabulkách i mobilních kartách. PLU, názvy, nuly a kladné hodnoty se tímto pravidlem nemění. Funguje po hledání, řazení i stránkování; hodnoty zůstávají původní.
- Stránkování přebírá vzhled seznamu zákazníků: společný světlý zaoblený pruh, modrá aktivní stránka s bílým číslem, stejná tlačítka a mezery.

## Nahrání

Kompletní balíček navazuje na V125. Nahrajte složky hs-client-ui a str do kořene webu, potom Ctrl+F5. Původní str/strana ponechte na serveru.
Při kompletní V125 stačí přepsat:

- hs-client-ui/js/stock.js
- hs-client-ui/css/stock.css
- hs-client-ui/php/index.php
- hs-client-ui/VERSION.txt

Lokálně ověřeno v Chrome na PC a mobilu: český popisek po původně anglickém textu, přepnutí CZ → EN → CZ, 63 záznamů, přechod na další stránku, červené záporné hodnoty a barvy stránkování. PHP a JS syntaxe zkontrolována. Na živý server nebylo nasazeno.
