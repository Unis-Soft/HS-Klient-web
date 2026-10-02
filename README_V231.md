# HS Klient V231 – Programy vizuální opravy

V231 vychází přímo ze stabilní V227. Neobsahuje navigační pokusy V228–V230.

## Změny

1. Druhé bílé probliknutí
- hlavní obsah už nepoužívá legacy `fadeInUp`;
- první klasický full-page reload se tímto nemění.

2. Název Programu
- sloupec Programu v seznamu zákazníků je širší;
- krátký název jako `ZÓNY TĚLA` se zobrazí celý;
- dlouhé názvy zůstávají omezené/ellipsis;
- stejná typografie byla upravena v detailu zákazníka.

3. Grafy Programů
- SVG už nepoužívá `preserveAspectRatio="none"`, které roztahovalo text a čísla;
- šířka viewBoxu se počítá podle reálné šířky obsahu;
- čísla, datumy i text uvnitř grafu si zachovávají správné proporce;
- oprava platí pro graf zůstatku i graf frekvence docházky.

HSBridge V010 zůstává beze změny.
