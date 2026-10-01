# HairSoft Klient V149

V149 je opravná verze sekce **Uživatelé** nad V148. Opravuje regresi, kdy po přesunu pole Hledat mohla zmizet celá tabulka aktivních i archivovaných obsluh.

## Oprava seznamů obsluh
- přesun pole Hledat z DataTables zůstává zachovaný,
- V149 smí skrýt původní řádek filtru pouze tehdy, pokud tento řádek skutečně leží uvnitř příslušného DataTables wrapperu,
- rodičovský Bootstrap `.row` celé sekce Seznam obsluh / Archivované obsluhy už nemůže být omylem skryt,
- prázdný původní DataTables řádek se skryje jen pokud po přesunu filtru neobsahuje jiný ovládací prvek ani tabulku.

## Beze změny
- výběr pobočky a import zůstávají v jednom řádku,
- vzhled tabulek, práva, akce, stránkování a překlady zůstávají stejné jako ve V148,
- `hs-client-ui/php/strana/Uzivatele.php`, SQL, POST/GET parametry a databázová logika jsou beze změny.
