# V245 – Export detailu zákazníka (PDF + Excel)

- V detailu zákazníka je nový `Export` se dvěma volbami: `PDF dokument` a `Excel`.
- PDF je samostatný vícestránkový zákaznický spis, nikoli screenshot aktuální obrazovky.
- Úvod PDF obsahuje jméno, pobočku, HairSoft ID, souhrn návštěv, bonusové body, poslední/příští návštěvu, poznámku a volitelně profilovou fotografii oříznutou do kruhu.
- Do PDF se exportují vyplněné osobní/kontaktní, zdravotní, firemní/komunikační a systémové údaje.
- Timeline se exportuje kompletní: datum, obsluha a záznam.
- Programy se načtou pro všechny programy zákazníka. Každý program obsahuje souhrn (předplaceno, vyčerpáno, zbývá, částka, počet fotografií), vlastní hodnoty, docházku a předplacené vstupy. Grafy ani samotné fotografie se do exportu nevkládají.
- Hodnocení obsahuje termín, datum hodnocení, obsluhu a dvojice otázka/odpověď.
- Soubory se exportují pouze jako seznam názvů. Obsah souborů se nepřikládá.
- Galerie a fotografie programů jsou z exportu vynechány; jedinou fotografií v PDF může být profilovka.
- SMS Chat se do zákaznického spisu nezahrnuje.
- Excel je skutečný `.xlsx` s listy: `Zákazník`, `Timeline`, `Programy`, `Docházka`, `Předplacené vstupy`, `Hodnocení`, `Soubory`.
- Export respektuje aktuální jazyk HS Klient (CZ/SK/EN/DE) a během přípravy zobrazuje blokující informační overlay.
- HSBridge se ve V245 funkčně nemění; zůstává V014.