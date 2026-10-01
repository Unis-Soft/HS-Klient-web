# V223 - Programy: UI doladeni a priprava dynamickeho sloupce

V223 navazuje primo na V222.

## Zmeny
- V seznamu zakazniku je pridan horni horizontalni posuvnik synchronizovany se spodnim.
- Programovy sloupec je pripraven na metadata z budouciho HSBridge napojeni:
  - 0 aktivnich programu -> sloupec se skryje,
  - 1 aktivni program -> hlavicka nese nazev programu, vizualne zkraceny na max. 20 znaku a plny nazev zustava v tooltipu,
  - vice aktivnich programu -> v hlavicce je rovnou vyber programu.
- Vybrany program se pri budouci integraci posila jako `hs_program_id` v DataTables requestu.
- Mobilni karta pouzije stejny aktivni programovy popisek a pri skrytem sloupci metriku Programu nezobrazi.
- Programy v detailu zakaznika maji vlastni ikonu.
- Prazdny stav Programu byl graficky srovnan s Galerii/Hodnocenim/Soubory a nema nadbytecnou bilou plochu.

## Datovy kontrakt pro dalsi krok
Klient umi volitelne prijmout v DataTables JSONu objekt `hsProgramMeta` (nebo `programMeta`):
```json
{
  "activePrograms": [
    {"id": "1", "name": "Permanentka 10 navstev"}
  ],
  "selectedProgramId": "1"
}
```
Pokud metadata zatim nejsou pritomna, zachovava se V222 rezim s viditelnym sloupcem `Programy` a pomlckou.

## Beze zmeny
- HairSoft programova data se stale nemapuji.
- HSBridge ani serverovy endpoint se nemeni.
- MySQL schema HS Klient se nemeni.
