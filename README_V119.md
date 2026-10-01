# HairSoft Klient V119 – oprava bílé stránky ročních tržeb

V118 obsahovala chybnou cestu k povinnému souboru pro ověření přístupu. Roční stránka hledala /str/_zabezpeceni.php místo správného /str/strana/_zabezpeceni.php. Pokud soubor v chybné cestě neexistoval, require ukončil PHP a stránka se nezobrazila.

V119 používá stejnou cestu jako funkční měsíční stránka. Ověření přístupu zůstává zachováno.

## Nahrání opravy při nainstalované V118

Z balíčku stačí přepsat jediný soubor:

hs-client-ui/php/strana/TrzbyDleObsluhy.php

Potom znovu otevřete Roční tržby. Úprava nevyžaduje změnu databáze ani stylů. Kompletní balíček obsahuje také ostatní soubory dosavadní verze.

## Kontrola

Před opravou reprodukována chyba require s adresářovou strukturou odpovídající funkční měsíční stránce. Po opravě prošel celý běh roční stránky s testovacími databázovými výsledky pro naplněný rok, prázdný rok a demo, včetně změn roku a obsluhy. PHP 8.3 kontrola syntaxe prošla. Produkční server nebyl přístupný k ověření; serverový log nebyl k dispozici.

Oproti V118 je v aplikačním kódu změněna pouze uvedená cesta. Měsíční a denní tržby zůstávají beze změny.
