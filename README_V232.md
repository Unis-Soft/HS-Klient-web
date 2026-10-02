# HS Klient V232 – Programy: názvy a grafy

V232 navazuje na V231 a opravuje konkrétní vizuální chyby Programů.

## Název programu

- v seznamu zákazníků má sloupec Programu pevnější šířku a běžnou typografii;
- krátký název jako ZÓNY TĚLA se zobrazuje celý;
- v detailu zákazníka se při jednom programu název zobrazuje přímo jako hlavní název sekce, ne jako malý text vpravo;
- dlouhé názvy zůstávají bezpečně omezené pomocí ellipsis.

## Graf zůstatku

- více událostí ve stejný kalendářní den se ve vizualizaci sloučí do jednoho bodu s konečným zůstatkem daného dne;
- tím se odstraní zhluk několika čísel vlevo nahoře;
- první a poslední hodnota jsou posunuté dovnitř grafu;
- první a poslední datum mají správné zarovnání, takže se rok neodřízne mimo oblast grafu;
- počet událostí v patičce dál odpovídá skutečnému počtu událostí, ne počtu vykreslených denních bodů.

## Graf frekvence docházky

- při 10 a méně sloupcích se datum zobrazí pod každým sloupcem;
- při větším počtu se datumy automaticky rozumně vzorkují a vždy zůstane poslední datum;
- hodnoty sloupců a datumy zůstávají uvnitř grafu.

Druhé bílé probliknutí zůstává opravené odstraněním fadeInUp z V231.
HSBridge V010 se nemění.
