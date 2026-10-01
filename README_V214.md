# V214 – Nastavení hodnocení: kompaktní horní bloky

V214 navazuje přímo na V213. Změna je pouze prezentační a přeuspořádává horní část Nastavení hodnocení na PC bez změny ukládání nebo databázové logiky.

## Změny
- `Výběr střediska` a `Odeslání dotazníku` jsou od šířky 992 px vedle sebe přibližně v poměru 35/65.
- Levý blok `Výběr střediska` je na desktopu skládán svisle: výběr střediska, povolení dotazníku a nastavení data omezeného hodnocení.
- Karta `Od jakého data počítat omezené hodnocení` včetně tlačítka `Změnit omezení hodnocení` byla přesunuta z bloku odesílání do levého bloku ke středisku.
- Pravý blok `Odeslání dotazníku` obsahuje v první řadě tři volby: způsob odeslání, kdy odesílat a kolikrát do roka.
- `Systém zapnut` je v pravém bloku samostatná karta přes celou šířku pod třemi volbami.
- Pokud je hodnocení vypnuté a blok odesílání se nezobrazuje, výběr střediska zůstává na plné šířce jako dříve.
- Mobilní zobrazení zůstává skládané pod sebe.
- Cache klíč `feedback.css` je zvýšen na 214.

## Beze změny
- funkce zapnutí/vypnutí hodnocení,
- ukládání způsobu zasílání a omezení počtu hodnocení,
- interní SMS a Google SMS logika,
- emailové šablony a otázky,
- databáze a SQL,
- cron hodnocení,
- všechny změny V210–V213 včetně multi-firmy, kopie zákazníka a Správy dat.
