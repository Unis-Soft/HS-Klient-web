# HairSoft Klient V110 – viditelnost levého menu

Oprava proti V109 mění jediný funkční soubor: `hs-client-ui/php/strana/MesicniTrzby.php`.

Skript pro zachování období byl vložen mezi levé menu a hlavní obsah. Původní styly webu používají sousední selektor `.sidebar + .main-content-wrapper`; kvůli vloženému skriptu přestaly nastavovat odsazení obsahu. Obsah tak překryl rozbalené menu. Skript je nyní uvnitř hlavní sekce, takže původní návaznost elementů je obnovena.

Ověření na lokální testovací stránce se skutečnými veřejnými CSS soubory webu:

- Před opravou: odsazení 0 px, rozbalené menu zakryté; test skutečné viditelnosti selhal.
- Po opravě: odsazení 248 px, menu viditelné při šířkách 1920, 1366 a 768 px. Test zahrnuje bodovou kontrolu překrytí menu a ruční sbalení/rozbalení.
- Mobil 375 px: vysunuté menu viditelné.
- Kontrola syntaxe PHP 8.3.33 a regresní testy výběru období prošly.

Test není přihlášená produkční relace. Balíček nebyl nasazen na server.

Nasazení: nahrajte obsah složek `hs-client-ui` a `str` do kořene webu a přepište soubory. Máte-li již kompletní V109, stačí přepsat `hs-client-ui/php/strana/MesicniTrzby.php`. Pak stránku obnovte.

Ostatní funkční soubory zůstávají shodné s V109. Starší README jsou historické záznamy.
