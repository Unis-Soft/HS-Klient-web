# HairSoft Klient V132 – stejný spinner jako v Tržbách

Navazuje na V131. Spinner voucherů nyní přímo sdílí společné CSS spinneru z Tržeb: podklad 68 × 68 px se zaoblením a stínem, kruh 32 px, stejné barvy a animace 0,75 s. Zachovává mobilní variantu a omezení animací. Vlastní vzhled z V131 byl odstraněn. Ochrana proti probliknutí i zmizení spinneru po dokončení/chybě zůstávají.

Nahrajte složky hs-client-ui a str, potom Ctrl+F5. Původní str/strana ponechte.
Oproti V131 jsou změněny hs-client-ui/css/client-ui.css, hs-client-ui/js/voucher.js, hs-client-ui/php/index.php a VERSION.txt.

Ověřeno v Chrome: shodné vypočtené styly spinneru s referencí Tržeb, vizuální kontrola, animace během čekání a skrytí po dokončení nebo chybě. PHP/JS syntaxe zkontrolována. Datové operace ani formuláře se nemění. Na server nebylo nasazeno.
