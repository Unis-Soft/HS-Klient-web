# HairSoft Klient V131 – spinner načítání

Navazuje na V130. Vrací viditelný tyrkysový spinner během přípravy voucherové stránky. Spinner je mimo skrytý obsah, takže ochrana proti probliknutí původního vzhledu zůstává zachovaná. Po dokončení zmizí; při chybě ustoupí chybové zprávě. Přístupný popisek má překlady CS/SK/EN/DE. Respektuje omezení animací v nastavení zařízení.

Nahrajte celé složky hs-client-ui a str a potom Ctrl+F5. Původní str/strana ponechte na serveru.
Oproti kompletní V130 se mění hs-client-ui/php/index.php, hs-client-ui/js/i18n.js a VERSION.txt.

Ověřeno v lokálním Chrome se zpomaleným načítáním: animace během čekání, zmizení po dokončení i při chybové zprávě, zachování skrytého původního obsahu. PHP syntaxe ověřena. Výpočty, filtry a formuláře beze změny. Na server nebylo nasazeno.
