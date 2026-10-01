# V222 - Programy: priprava UI v seznamu a detailu zakaznika

V222 navazuje primo na V221 a realizuje dohodnutou grafickou pripravu pro Programy.

## Zmeny
- V seznamu zakazniku je novy sloupec `Programy` pred vernostnimi body.
- Stejny udaj je doplnen do mobilni karty zakaznika.
- Sloupec je viditelny pro standardni DataTables exporty Copy/Excel/CSV/PDF/Print.
- V detailu zakaznika je nova zalozka `Programy` mezi `Galerie` a `Hodnoceni`.
- Nova zalozka ma pripraveny desktopove i mobilni styly.
- Server-side `ssf_zakaznici.php` vraci novou pozici sloupce a spravne posouva Body, Blacklist a Akce.

## Zamerne zatim bez dat
- Programy se v teto verzi zobrazuji jako pomlcka.
- Nepridava se zadne neoverene mapovani `programs` / `program_visits`.
- HSBridge ani jeho serverovy endpoint se ve V222 nemeni.
- MySQL schema HS Klient se nemeni.

Dalsi krok je overit skutecnou strukturu programovych dat v HairSoft a teprve potom ji prenest pres HSBridge do pripraveneho UI.
