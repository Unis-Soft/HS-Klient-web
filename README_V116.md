# HairSoft Klient V116

Mobilní datové karty denních tržeb nyní lícují s tlačítkem Export. Opraven přepis display:grid !important, který přebil mobilní display:block a nechával vpravo mezeru. Karty zároveň zabírají všechny sloupce rodičovské mřížky.

Platí pro sumáře středisek i jednotlivců a pro obě orientace. Při šířce nad 720 px zůstává podle dosavadního návrhu tabulkové zobrazení.

Lokálně ověřeny oba okraje Exportu a karet při 375/430/667/720 px a zachování tabulkové mřížky při 932/1366 px. Syntaxe PHP 8.3.33 prošla.

Proti V115 změněny hs-client-ui/css/daily-revenue.css a hs-client-ui/php/index.php (cache CSS 116). Nahrajte obě složky balíčku do kořene webu; při kompletní V115 stačí uvedené dva soubory. Poté Ctrl+F5. Nenasazeno na produkční server.
