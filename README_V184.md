# V184 – Hodnocení: testovací tlačítka a funkční test dotazníku

Vychází z V183 a je určena k nasazení místo V183, pokud V183 ještě nebyla nasazena.

## 1. Testovací tlačítka
- Tlačítko `Zaslat testovací email` je stále viditelné.
- Tlačítko `Zaslat testovací SMS` je stále viditelné.
- Obě jsou výchozí `disabled` a aktivují se až po validním vstupu.
- E-mail používá nativní HTML5 validaci `type=email`; mobil zachovává dosavadní HairSoft validaci.

## 2. Oprava testovacího dotazníku
Původní test používal pevný kód `00TEST`, který v `HodnoceniHlavni` neexistoval. `form.php` proto dostal `null` místo řádku a PHP 8.3 vypisovalo warningy při čtení jeho polí.

V184 používá krátký testovací kód:
`T<pobocka>-<stredisko>`

Například jej stávající `.htaccess` automaticky předá do `form.php` stejně jako běžný kód.

`form.php` v testovacím režimu:
- načte název střediska z `HodnoceniStrediska`,
- načte aktuálních max. 20 otázek z `HodnoceniNastaveni`,
- vykreslí skutečný aktuální dotazník,
- po odeslání ukáže úspěšný test,
- testovací odpovědi **nikdy nezapisuje** do `HodnoceniHlavni`.

Současně je běžná větev bezpečně ošetřena tak, aby neexistující kód nečetl položky z `null` výsledku.

## Bezpečnost původního interního hodnocení
- Produkční interní SMS logika v klientovi se nemění.
- Produkční unikátní interní odkazy se nemění.
- `cron_hodnoceni.php` je byte-for-byte stejný jako ve V183.
- Google SMS větev z V183 se nemění.
- Změna se týká jen testovacích odkazů a zobrazení/validace testovacích tlačítek.
