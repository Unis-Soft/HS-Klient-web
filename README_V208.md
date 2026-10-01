# V208 – stabilní vazba TEST kopie + oprava chybějícího dokončení/Timeline

V208 navazuje přímo na V207 a opravuje chybu z reálného testu: po prvním HairSoft syncu se v cílové kartě ztratilo tlačítko „Dokončit synchronizaci do HairSoft“ a současně se nezobrazoval Timeline staging.

## Skutečná chyba V207
V207 svazovala cílovou TEST kopii, její ruční stav a staging Timeline primárně přes `target_guid`.
To je příliš křehké pro legacy synchronizaci. Pokud se identita/GUID cílového webového záznamu při prvním syncu změní nebo je cílová karta otevřena pod jiným aktuálním GUID, audit se nedohledá. Pak současně zmizí ruční tlačítko i staging Timeline.

## V208 – stabilní databázová vazba
- Do izolované auditní tabulky `k_klient_customer_copy_test_log` přibývá `target_customer_id`.
- U nové kopie se uloží přímo `klient_lidi.lidi_id` právě vytvořeného cílového řádku.
- Cílová karta dohledává TEST kopii nejprve podle stabilního `lidi_id`, potom podle GUID.
- Pro kopie vytvořené V207, které `target_customer_id` ještě nemají, existuje bezpečný recovery fallback: stejná cílová pobočka, stejné jméno/příjmení a alespoň dva shodné neprázdné údaje z e-mailu, mobilu, telefonu a data narození. Recovery se provede jen pokud existuje právě jeden kandidát.
- Po bezpečném recovery se audit, staging, fotografie, soubory a ruční fronta přepojí na aktuální GUID cílové karty.

## Timeline staging
- Historická Timeline zůstává mimo produkční `timeline` až do ručního kliknutí.
- Zobrazení stagingu už není závislé na aktuálním GUID, ale na stabilním `copy_log_id`.
- Ruční klik na Timeline také ověřuje staging přes `copy_log_id`; konkrétní nový řádek v produkční `timeline` se vytvoří až po kliknutí.

## Pojistka proti starým neočekávaným řádkům
Pokud má V207+ kopie platný staging, V208 při otevření cílové karty odstraní pouze neočekávané pending řádky z produkční `timeline`, které se přesně shodují se zdrojovou historickou Timeline (datum + text + obsluha) a nejsou řádkem už výslovně připraveným přes staging. Tím se má zastavit další opakované načítání starých testovacích klonů z webu. Již vytvořené duplicity v desktopovém HairSoft V208 automaticky nemaže.

## Ruční postup zůstává
1. Vytvořit TEST kopii A → B.
2. Nechat HairSoft založit samotného zákazníka.
3. Otevřít cílovou kartu B → Správa dat → Dokončit synchronizaci do HairSoft.
4. Potvrdit skutečné HairSoft ID.
5. Fotografie a soubory se uvolní.
6. Timeline se posílá výhradně po jednotlivých řádcích ikonou synchronizace.

## Databáze
Mění se pouze izolovaná auditní tabulka TEST kopie: nový sloupec `target_customer_id`. Produkční tabulky `klient_lidi`, `timeline`, `klient_lidi_obrazky` a `soubory` nemění strukturu.
