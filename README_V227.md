# V227 - Programy: frekvence dochazky

V227 navazuje primo na V226.

## Programy - druhy graf
- Pod graf vyvoje zustatku vstupu pribyl graf Frekvence dochazky.
- Kazdy sloupec ukazuje pocet dni mezi dvema po sobe jdoucimi cerpanimi vybraneho programu.
- Souhrn ukazuje prumerny, nejkratsi a nejdelsi interval.
- Pri mene nez 2 cerpanich se zobrazi korektni prazdny stav.
- Po zmene programu se oba grafy prekresli automaticky bez plneho reloadu detailu.

## Synchronizace PROGRAMS
- Klientska cast V227 nemení serverovy datovy model.
- Frekvence HSBridge PROGRAMS se meni samostatne v HSBridge V006 z 60 sekund na 15 minut.
