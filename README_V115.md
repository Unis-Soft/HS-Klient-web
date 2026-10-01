# HairSoft Klient V115 – ovládací ikony panelů na telefonu

V denních i měsíčních tržbách jsou na úzkých obrazovkách a telefonech na šířku skryty ikony maximalizace, obnovení velikosti, sbalení/rozbalení a zavření panelu. Pravidlo platí i pro původní panely bez dat a od prvního vykreslení, protože označení stránky vytváří PHP. Nadpisy se mohou zalomit. Ovládání období a export zůstávají dostupné. Na desktopu zůstávají ovládací ikony zachovány.

Rozsah: šířka do 767 px; u zařízení bez hoveru s hrubým ukazatelem do 1100 px (telefon na šířku).

Změny proti V114: hs-client-ui/css/client-ui.css a hs-client-ui/php/index.php (označení stránek a cache verze CSS 115).

Ověřeno lokálně v Chrome bez JavaScriptové úpravy panelů: denní/měsíční panely s daty i bez dat na 375/390 px, dotyková šířka 932 px a desktop 1366 px. Kontrola viditelnosti ikon, prostoru pro nadpisy a zachování exportu/navigace. Syntaxe PHP 8.3.33 prošla. Produkční relace nebyla součástí testu.

Nasazení: nahrajte složky hs-client-ui a str do kořene webu. Při kompletní V114 stačí uvedené dva soubory. Poté Ctrl+F5.
