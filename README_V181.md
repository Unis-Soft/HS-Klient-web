# V181 – Hodnocení: omezení v jedné kartě

- Výchozí stav: V180.
- V Nastavení hodnocení spokojenosti je akce **Změnit omezení hodnocení** přesunuta přímo do karty **Od jakého data počítat omezené hodnocení**.
- Samostatný spodní blok s tlačítkem byl odstraněn, takže sekce nyní končí tlačítkem **Uložit způsob zasílání**.
- Reset data zůstává samostatný POST (`NastavitDatumOmezeniHodnoceni=1` + `VybraneStrediskoID`), takže se při něm nespouští uložení ostatních parametrů odesílání.
- Na mobilu se akce skládá pod datum v rámci stejné karty.
- Ostatní logika hodnocení, DB a další sekce beze změny.
