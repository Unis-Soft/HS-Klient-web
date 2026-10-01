# HairSoft Klient V122

- Roční graf počtů účtenek za střediska: opraveno čtení názvu z výsledku SQL (`nz` místo neexistujícího `Stredisko`). Legenda a tooltip mají skutečný název střediska. Počty účtenek se nemění.
- Denní tržby: datum je uprostřed, předchozí den vlevo a následující vpravo. Bez nadpisu a viditelných textů šipek. Zachován původní kalendář, formuláře a odesílání data; přístupné názvy tlačítek zůstávají.
- Stejné uspořádání na PC a mobilu v obou polohách. Navazuje na kompletní V121.

## Nahrání

Nahrajte složky hs-client-ui a str do kořene webu a proveďte Ctrl+F5.
Pokud již máte kompletní V121, stačí tyto soubory:

- hs-client-ui/php/strana/TrzbyDleObsluhy.php
- hs-client-ui/js/daily-revenue.js
- hs-client-ui/css/daily-revenue.css
- hs-client-ui/php/index.php
- hs-client-ui/VERSION.txt

## Ověření

PHP kontrola syntaxe a modelový dotaz s reálným názvem aliasu (název s apostrofem). Prohlížeč: 375, 932 a 1366 px; centrování, obě navigační tlačítka, zachované odeslání ručně zvoleného data, hranice měsíce/roku a přestupný únor. Vizuální kontrola PC i mobilu. Živý server ani databáze nebyly v tomto prostředí ověřeny.
