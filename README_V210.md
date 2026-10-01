# V210 – Timeline opravdu po jednom synchronizačním cyklu

Navazuje přímo na V209.

## 1. Tvrdý zámek Timeline
V209 dovolovala kliknout na více čekajících Timeline řádků před jednou synchronizací HairSoft. Tím vzniklo několik produkčních řádků se stavem N současně a znovu se vytvořil batch, při kterém legacy HairSoft opakoval obsah jednoho záznamu.

V210 proto dovolí mít pro jednu kopii maximálně jeden staging řádek ve stavu `prepared`:
- klik na synchronizační ikonu vytvoří právě jeden produkční Timeline řádek,
- všechny ostatní čekající ikony se okamžitě zablokují,
- nad tabulkou se zobrazí výzva k provedení synchronizace HairSoft,
- po ověření v HairSoft musí administrátor kliknout na **Synchronizace proběhla – povolit další**,
- při potvrzení HS Klient uzavře přesně tento transportní řádek (`timelineDoPCZnak` se vyprázdní a `timelineDoPCSynchro` dostane technický čas),
- staging řádek se přepne `prepared -> sent`,
- teprve potom lze připravit další historický Timeline záznam.

Kontrola je současně v UI i na serveru. U staging kopie se starý fallback na produkční N/H řádky nepoužívá, aby již potvrzený transportní řádek znovu nezablokoval další položku.

Pokud V209 už připravila více řádků současně, V210 je zobrazí jako čekající na ruční potvrzení. Po ověření, že je HairSoft zpracoval, je lze jedním potvrzením označit jako dokončené a pokračovat už striktně po jednom.

## 2. Datum Timeline
V210 nemění mapování dat. Do produkční `timeline.timelineDatumCas` se při ručním kliknutí stále zapisuje původní historické datum z HS Klient. Technické `timelineDoPCVlozeno` zůstává aktuální čas přípravy. Reálný test ukázal, že desktopový HairSoft zatím zobrazuje ve sloupci Vytvořeno dnešní datum; to je samostatné mapování na straně legacy synchronizace / HairSoft a V210 jej nemění.

## 3. Bonfero
Rezervace → Administrace je vrácena na produkční adresu `https://app.bonfero.com/` pro iframe, Otevřít zvlášť i noscript odkaz.

## Databáze
Žádná nová tabulka ani sloupec. Stávající `k_klient_customer_copy_timeline_stage.status` používá hodnoty `waiting`, `prepared` a nově `sent`.
