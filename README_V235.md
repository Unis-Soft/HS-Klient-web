# HS Klient V235 – Programy: skutečná KPI karta, Zóny těla a i18n grafů

V235 opravuje tři konkrétní chyby V234.

## Horní karta Programy

- karta se nyní skutečně vloží do existujícího horního KPI gridu seznamu zákazníků;
- příčina V234: JavaScript nehledal skutečný KPI grid, ale odvozoval cílový sloupec od odkazu `strana=Zakaznici&AkceTab=Vsechny`; pokud tento odkaz není přímo součástí KPI DOM, cíl nevznikne a karta se vůbec nevloží;
- nový kód hledá přímo systémový KPI grid před tabulkou zákazníků a po vložení přepočítá layout na 3 karty;
- karta zobrazuje celkový kladný nevyčerpaný zůstatek všech aktivních programů; při více programech zůstává karta bez comba a rozpad je v tooltipu.

## Zóny těla / Ě

- známý HairSoft program `ZÓNY TĚLA` se před vykreslením natvrdo normalizuje na `Zóny těla`;
- normalizace probíhá ve sdílené PHP datové vrstvě i jako klientská pojistka;
- zahrnuta je i dříve pozorovaná chybná varianta `ZÓNY TÍLA`;
- databázová hodnota se nemění.

## Překlady Programů a grafů

- známý název Zóny těla se správně překládá CZ / SK / EN / DE;
- dynamické části grafů se již nespoléhají na překlad celého textového uzlu s číslem nebo datem;
- překládají se i `zbývá`, `Období`, `Událostí`, `Intervalů`, `dní`, intervalové texty, tooltipy a SVG aria-labely;
- po změně jazyka se programové grafy znovu vykreslí v novém jazyce;
- doplněny jsou i překlady prázdných/loading/chybových stavů Programů a DPH.

HSBridge zůstává V010. Databáze beze změny.