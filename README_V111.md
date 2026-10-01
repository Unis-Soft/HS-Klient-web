# HairSoft Klient V111 – výběr období

Výběr měsíce a roku má kompaktní společný rámeček s jemným oddělením, bílé navigační šipky a vycentrovanou hlavičku. Nadpis i ovládání mají společnou středovou osu. Na mobilu se oba ovladače skládají pod sebe. Zachován Open Sans a barvy aplikace.

Oproti V110 se funkčně mění pouze `hs-client-ui/css/monthly-revenue.css` a odkaz na jeho verzi v `hs-client-ui/php/index.php` (v=111). Navigace období a ostatní funkce zůstávají stejné.

Ověřeno lokálně v Chrome se základními styly webu a skutečným skriptem měsíčních tržeb: šířky 1600, 1024, 768 a 375 px; vycentrování nadpisu a ovládání, absence vodorovného přetékání a zachování všech čtyř odkazů na období. Kontrola syntaxe změněného PHP souboru v PHP 8.3.33 prošla. Balíček nebyl nasazen na server.

Nasazení: nahrajte složky `hs-client-ui` a `str` do kořene webu a potvrďte přepsání. Při již kompletně nasazené V110 stačí přepsat uvedené dva soubory. Poté obnovte stránku Ctrl+F5.
