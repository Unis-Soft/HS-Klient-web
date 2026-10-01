# V190 - Google recenze: kratke odkazy s priponou -G

Navazuje na KLIENT V184 a zachovava veskerou dosavadni logiku interniho hodnoceni.

## Testovaci Google SMS
Pokud je pro vybrane stredisko aktivni Google rezim, testovaci SMS uz neposila dlouhy Google URL.
Pouzije kratky alias:

`www.dotaznik.net/T<pobocka>-<stredisko>-G`

Napriklad:
`www.dotaznik.net/T4181-4-G`

Tento alias zpracuje `form.php` z DOTAZNIK.NET V190 a presmeruje ho na aktualne ulozenou Google URL pro danou pobocku a stredisko.

## Beze zmeny
- interni test: `www.dotaznik.net/T<pobocka>-<stredisko>`
- produkcni interni kody a SMS
- emailove hodnoceni
- tabulka `HodnoceniGoogleSMS`
