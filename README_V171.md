# HairSoft Klient V171 – Změna obsluhy v iframe modalu

Výchozí stav: V170.

## Změny
- Kliknutí na `Změna obsluhy` v levém menu neotevírá samostatnou stránku, ale velký moderní modal nad aktuální sekcí.
- Modal obsahuje same-origin iframe `index.php?strana=ZmenaObsluhy&hs_embed=1`.
- Embed režim skryje horní lištu, levé menu, hlavičku stránky a stavové/patičkové prvky; v iframe zůstávají pouze moderní karty obsluh.
- Po kliknutí na kartu se v témže iframe karty schovají a zobrazí se přihlašovací panel z V170 s avatarem, jménem a heslem. Nejde o modal uvnitř modalu.
- Tlačítko Zpět vrátí výběr obsluh; Escape v přihlášení vrátí karty a Escape ve výběru zavře celý iframe modal.
- Chybné heslo znovu otevře stejný přihlašovací krok s chybovou hláškou.
- Po správném hesle iframe přes `postMessage` předá rodiči bezpečný redirect na původní `zmena_pobocky=1`, takže se načtou práva nové obsluhy.
- Samostatná stránka Změna obsluhy z V170 zůstává zachovaná jako fallback (např. otevření odkazu v nové kartě).
- Vnější iframe modal má vlastní responzivní PC/tablet/mobil layout a texty CZ/SK/EN/DE.

## Beze změny
- Databázová struktura a SQL schéma.
- V169 oprava session zákazníků.
- Ostatní sekce aplikace.
