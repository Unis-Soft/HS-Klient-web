# HS Klient V242 – stabilita PROGRAMS action/photo endpointu

V242 navazuje přímo na V241.

## Oprava
- HSBridge V012/V013 při pravidelném pollingu akcí a fotografií volal serverový PROGRAMS endpoint každých 10 sekund ze dvou samostatných workerů. Endpoint při každém požadavku znovu ověřoval Bridge proti externímu Directory a znovu mapoval PC. Při krátké nedostupnosti / omezení Directory proto vznikaly opakované HTTP 500 i přesto, že lokální Bridge a HairSoft byly v pořádku.
- Úspěšně ověřená vazba `bridgeId + token -> sw_id + group_id` se nyní na serveru bezpečně kešuje na 60 sekund. Cache je klíčovaná SHA-256 hashem Bridge ID a tokenu; samotný token se do cache názvu ani obsahu neukládá.
- Při cache miss se ověření Directory jednou krátce zopakuje po 200 ms.
- Pokud endpoint přesto selže, odpověď obsahuje diagnostické `error_code` a `error_id`, zatímco detailní serverová chyba zůstává pouze v PHP error logu.
- Fronty, čerpání, fotografické soubory, databázové zápisy a priorita HairSoft se nemění.

## Bridge
HSBridge se nemění. Aktuální provozní verze zůstává **V013** s desetimístnou složkou zákazníka.

## Nasazení
Je nutné nahrát zejména:
- `/str/api/hsbridge-program-actions.php`

Ostatní soubory V241 zůstávají funkčně beze změny.