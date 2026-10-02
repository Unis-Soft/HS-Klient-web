# HS Klient V239 – fotografie Programů přes HSBridge

V239 navazuje přímo na V238 a přidává focení zákazníka přímo v detailu Programu.

## UI
- Vpravo v hlavičce vybraného Programu je ikona fotoaparátu.
- Modal umožní opakovaně spustit fotoaparát mobilu a nashromáždit 1 až 20 fotografií.
- Fotografie se před odesláním zobrazují jako náhledy a jednotlivě je lze odebrat.
- Automatická složka je datum ve formátu `DD.MM.YYYY`.
- Volitelná podsložka je například `Záda`.
- Bez podsložky se celá dávka uloží do jedné datumové složky. S podsložkou se celá dávka uloží do jedné společné podsložky.

## Cílová struktura HairSoft
Pro zákazníka s HairSoft ID `796`:

`C:\HairSoft\Images\Customers\796\02.10.2026\...`

nebo:

`C:\HairSoft\Images\Customers\796\02.10.2026\Záda\...`

Každá dávka obsahuje všechny pořízené fotografie; nevytváří se složka pro každou fotografii.

## Přenos
- Browser fotografie normalizuje na JPEG (max. delší strana 2560 px, kvalita 90 %), aby byl mobilní upload stabilní a velikost rozumná.
- Web je drží pouze dočasně ve frontě mimo Galerii HS Klient.
- HSBridge V012 převezme dávku určenou pouze pro své `sw_id/group_id`, stáhne soubory přes autentizovaný endpoint, ověří velikost a SHA-256 a atomicky je uloží do HairSoft.
- Po potvrzeném přenosu server fyzické dočasné soubory okamžitě smaže. HS Klient fotografie dlouhodobě nearchivuje.
- Při přerušení přenosu se job vrátí do fronty; názvy souborů jsou deterministické, takže retry nevytváří duplicity.

## Vazba zákazníka
Cílový adresář používá výhradně skutečné `klient_lidi.lidi_hs_id` / `customer.id` HairSoft. Dočasné ID `11111111` je odmítnuto.

## Priorita HairSoft
Foto modul nezapisuje do HairSoft databáze. Bridge pracuje pouze se souborovým systémem `Images\\Customers`, takže nebere databázový lock a neomezuje HairSoft při práci s DB.

## Verze
- HS Klient: V239
- HSBridge: V012