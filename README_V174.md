# HS Klient V174 – číselník pojišťoven v Nastavení

Vychází z V173.

## Změny
- Samostatná položka **Systém** je z moderního levého menu odstraněna.
- **Číselník pojišťoven** je přesunut pod **Nastavení**, vedle položky Přístupy.
- Nastavení i číselník jsou dostupné pouze hlavnímu administrátorovi (`JePoduzivatel=0`, `JeObsluha=0`); přímý URL vstup obsluhy/poduživatele na číselník je vrácen na jeho běžnou výchozí stránku.
- Odstraněna je historická viditelnost Systému podle `SW ID 5242/4181`, která způsobovala, že se položka objevovala nebo mizela podle aktivní pobočky.
- Doplněny překlady CZ/SK/EN/DE pro Přístupy a Číselník pojišťoven.

## Kontrola návaznosti na zákazníky
`KartaOsoby.php`, kterou používá nový i existující zákazník, dál přímo čte `cislenik_pojistovny` a ukládá `lidi_hs_pojistovnaGUID`. Její kód nebyl ve V174 změněn. Přesun položky v menu tedy nemění načítání ani ukládání pojišťovny u zákazníka.

## Databáze
Beze změny.
