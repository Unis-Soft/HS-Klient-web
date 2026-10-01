# V225 - Programy: oprava globalniho nacitani a bilych stranek

V225 navazuje primo na V224 a zachovava realne napojeni PROGRAMS dat.

## Oprava regrese z V224
- V224 nacitala programova data pri kazdem otevreni Karty osoby, i kdyz uzivatel nebyl v zalozce Programy.
- Tim se zpomalil cely detail zakaznika a pri prepinani zalozek byl videt plny reload jako bila stranka.
- V225 vraci ostatni zalozky detailu na puvodni rychlou cestu z V223.
- PROGRAMS data se nactou az ve chvili, kdy uzivatel opravdu otevre zalozku Programy.

## Programy - detail zakaznika
- Prvni otevreni Programu probiha na pozadi pres fetch.
- Behem nacitani zustava cely detail zakaznika viditelny; loader je pouze uvnitr sekce Programy.
- Prepnuti mezi vice programy uz neodesila klasicky formular a nedela plny reload stranky.
- Meni se pouze obsah zalozky Programy.

## Seznam zakazniku
- Vyber programu v hlavicce nadale pouziva DataTables AJAX.
- Pri zmene programu se uz nema zneviditelnit / zesvetlit cely panel tabulky; stara data zustanou viditelna do okamziku prekresleni noveho programoveho sloupce.
- Exporty, skryvani sloupce a mobilni data zustavaji z V224 zachovana.

## Bez zmeny
- HSBridge V005 PROGRAMS se nemeni.
- `str/api/hsbridge.php` V003 PROGRAMS se nemeni.
- Datovy model `hsbridge_program_*` se nemeni.
- Cooper voucher flow se nemeni.
