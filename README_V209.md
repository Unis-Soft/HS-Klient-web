# V209 – UI kopie zákazníka + Timeline ikona + test.bonfero.com

V209 navazuje přímo na V208.

## 1. Kopie zákazníka – odstranění označení TEST
- Ve Správě dat už tlačítko Zkopírovat do jiné firmy nemá štítek TEST.
- Modal kopie už nemá žlutý štítek TEST.
- Hlavní akce je Vytvořit kopii.
- Historie používá název Kopie tohoto zákazníka a akci Odstranit kopii.
- Uživatelské potvrzovací a chybové texty kopie už nepoužívají označení testovací/Test.
- Interní názvy modulů, tříd, rout a DB tabulek se nemění, aby nevznikla regrese.

## 2. Timeline – synchronizační ikona
- Ruční synchronizační tlačítko čekající Timeline položky používá jednoduchou vlastní SVG refresh ikonu.
- Ikona má pevnou velikost 16 × 16 px a vlastní stroke, takže se nedeformuje fontem ani layoutem.
- Funkce ručního uvolnění jednoho staging řádku z V208 se nemění.

## 3. Rezervace → Administrace – dočasná Bonfero adresa
- iframe: https://test.bonfero.com
- Otevřít zvlášť: https://test.bonfero.com
- noscript fallback: https://test.bonfero.com
- URL je v RezervaceBonfero.php soustředěná do jedné proměnné pro snadný návrat na produkci.

## Databáze a synchronizace
Beze změny. V209 nemění ruční ID, staging Timeline, fotografie, soubory ani synchronizační logiku V208.
