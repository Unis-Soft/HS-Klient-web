# V220 - Prepinac firem: 30 firem + scroll po cca 10 polozkach

V220 navazuje primo na V219.

## Zmena
- Maximalni pocet bezpecne ulozenych firem na jednom zarizeni je zvysen z 10 na 30.
- Seznam firem v hornim prepinaci na beznem desktopu zobrazi az priblizne 10 firem bez posuvu.
- Od 11. firmy se posouva pouze vnitrni seznam; hlavicka, Pridat firmu a odhlasovaci tlacitka zustavaji na miste.
- Na nizsim displeji se vyska seznamu automaticky omezi podle vysky okna, aby panel nepretekl mimo obrazovku.
- Pri dosazeni limitu 30 firem se zobrazi konkretni informace o maximu.
- Cache klic company-switch.css je zvysen na 220.

## Bezpecnost
- Struktura remember tokenu se nemeni.
- Hesla se stale nikam neukladaji.
- Cookie pro 30 tokenu ma v realistickem testu priblizne 3.0 kB vcetne nazvu, tedy zustava pod beznym 4kB limitem jedne cookie.
- Databaze a SQL beze zmeny.

## Beze zmeny
- 365denni remember tokeny,
- CSRF ochrana,
- odhlaseni jedne / vsech firem,
- posledni pobocka a posledni sekce pro kazdou firmu,
- V219 Hodnoceni a vsechny ostatni funkce HS Klient.
