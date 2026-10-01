# V196 - Globalni typografie Tahoma

Navazuje primo na V195.

## Zmena
Cely HS Klient pouziva jako hlavni textovy font:

`Tahoma, Arial, sans-serif`

Zmena se tyka:
- loginu a loginu obsluhy,
- hlavniho menu a dashboardu,
- zakazniku a detailu zakaznika,
- formularu, tabulek, DataTables a modalnich dialogu,
- trzeb a vsech modernich grafu,
- skladu, voucheru, hodnoceni, SMS a hovoru,
- uzivatelu, dochazky, pristupu a nastaveni,
- multi-firma switch modu vcetne modalu Pridat dalsi firmu,
- changelogu,
- systemovych e-mailovych sablon vytvarenych HS Klient.

## Co zustava beze zmeny
- velikosti textu,
- font-weight / tucnost,
- line-height,
- mezery a rozmery prvku,
- barvy a layout,
- funkcni logika,
- databaze,
- synchronizace HairSoft,
- V195 TEST kopie zakaznika,
- Google hodnoceni a dotaznik.net.

## Ikony
Nebyl pouzit nebezpecny globalni selektor `* { font-family: ... !important; }`.
Font Awesome a Simple Line Icons si proto ponechavaji vlastni ikonove fonty a Tahoma je neprepisuje.

## Cache
Vsechny app-owned CSS/JS odkazy v aktualnim shellu maji cache-bust `v=196`, aby se po nasazeni nenacitaly stare fontove styly z cache.
