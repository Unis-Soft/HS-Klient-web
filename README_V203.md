# V203 – jemnější KPI hodnoty a čitelný loader kopie zákazníka

V203 navazuje přímo na V202 a nemění její dvoufázovou synchronizaci ani databázovou logiku. Jde pouze o dvě cílené UI úpravy.

## 1. TEST kopie zákazníka – loader
Při odeslání formuláře `Zkopírovat zákazníka do jiné firmy` už tlačítko nepoužívá Font Awesome spinner. Globální Tahoma mohla ikonový glyph přepsat a při rotaci byl nečitelný.

V203 používá vlastní CSS kruhový loader:
- není závislý na ikonovém fontu,
- zůstává ostrý při rotaci,
- zachovává text `Vytvářím kopii...`,
- nemění samotné odeslání formuláře ani ochranu proti dvojímu odeslání.

## 2. Horní KPI karty – jemnější číselné hodnoty
Číselné hodnoty horních souhrnných karet používají nově `font-weight: 500` místo masivnějšího řezu.

Zachováno je:
- `Tahoma, Arial, sans-serif`,
- původní velikost písma,
- barvy,
- line-height,
- rozměry karet,
- ikony,
- responzivita.

Úprava pokrývá Dashboard, společné horní `widget-mini` KPI karty na ostatních stránkách a samostatnou KPI vrstvu Denních tržeb.

## Databáze a funkce
Beze změny. V202 synchronizace zákazník -> skutečné HairSoft ID -> Timeline/fotografie/soubory zůstává přesně zachována.
