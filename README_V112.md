# HairSoft Klient V112

- Odstraněn nadpis Období tržeb, podnadpis i viditelné štítky Měsíc/Rok. Zůstávají dva samostatné, vycentrované ovladače s mezerou. Přístupné názvy ovládání jsou zachovány.
- PHP vykresluje výběr období vždy za horními KPI a před tabulkovou sekcí. Odstraněna JavaScriptová tvorba a umisťování výběru podle přítomnosti tabulek. Budoucí měsíc bez tabulek tedy nemůže přesunout ovládání nad KPI.
- Zdrojové řádky tabulkových sekcí jsou označeny již v PHP a do dokončení modernizace skryty pomocí visibility. Po úpravě tabulek a grafů se zobrazí i prázdné sekce. Bez JavaScriptu se obsah zpřístupní pomocí noscript.

Změněné funkční soubory proti V111:

- hs-client-ui/php/strana/MesicniTrzby.php
- hs-client-ui/js/monthly-revenue.js
- hs-client-ui/css/monthly-revenue.css
- hs-client-ui/php/index.php (cache verze 112 pro měsíční JS/CSS)

Ověřeno: syntaxe PHP 8.3.33 a JavaScriptu; regresní testy období; lokální Chrome 1600/768/375 px pro září s ukázkovou tabulkou i říjen bez tabulky. Kontrola oddělení ovladačů, umístění za KPI, odstraněných textů a skrytí tabulek před načtením skriptu při simulovaném pomalejším načítání. Testy neprobíhaly v přihlášené produkční relaci.

Nasazení: nahrajte složky hs-client-ui a str do kořene webu a přepište soubory. Je-li nasazena kompletní V111, stačí výše uvedené čtyři soubory. Poté Ctrl+F5. Balíček nebyl nasazen na server.
