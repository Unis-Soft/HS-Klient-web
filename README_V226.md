# V226 - Programy: graf vyvoje zustatku vstupu

V226 navazuje primo na V225.

## Programy - detail zakaznika
- Pod souhrn a tabulky v zalozce Programy pribyl novy graf.
- Graf ukazuje vyvoj zustatku vstupu v case.
- Data se skladaji z predplacenych vstupu a dochazky evidovane v programu.
- Po zmene vybraneho programu se graf prekresli automaticky bez reloadu cele stranky.
- Reseni zustava lazy pouze uvnitr zalozky Programy, bez bile stranky a bez plneho reloadu detailu.

## Vzhled
- Graf pouziva stejny vizualni styl jako zbytek karty.
- Nad grafem je kratky popis a souhrnne hodnoty Predplaceno / Vycerpano / Zbyva.
- Pod grafem je zobrazeno obdobi a pocet udalosti.

## Bez zmeny
- HSBridge V005 PROGRAMS se nemeni.
- `str/api/hsbridge.php` V003 PROGRAMS se nemeni.
- Datovy model `hsbridge_program_*` se nemeni.
