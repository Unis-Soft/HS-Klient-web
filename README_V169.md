# HS Klient V169 – oprava načtení zákazníků pro obsluhu

Výchozí stav: **V166**. Neobsahuje změny z neúspěšných V167 ani V168.

## Zjištění
Po doplnění původního `Zakaznici.php` se ukázal důležitý rozdíl mezi session obsluhy a session vlastníka/managera. Seznam zákazníků používá při vykreslení DataTables a následném SSF načtení session hodnoty, které u obsluhy nemusí v čerstvé session existovat.

Nejdůležitější je `k_poduzivatele_skryt_citliva_data`: specializované `loginObsluha.php` ji nastavuje, ale hlavní `login.php` ji při přihlášení obsluhy nenastavoval. Zákaznický DataTable tuto hodnotu používá přímo při generování JavaScriptu. Současně nebylo u obsluhy konzistentně nastaveno `SQL_ROK` a v nové session nemusely být inicializované filtry zákaznické tabulky.

## V169
- vychází pouze z V166;
- SSF endpointy a AJAX cesty zůstávají přesně jako ve V166;
- design není měněn;
- bezpečně inicializuje `Blacklist` a `ZakazniciRazeniFiltr*`;
- opravuje již otevřenou session obsluhy bez nutnosti odhlášení;
- privacy flag obsluhy načte z DB podle jejího ID; při neúspěšném lookupu se použije bezpečný režim anonymizace;
- hlavní `login.php` nově ukládá stejný privacy flag jako `loginObsluha.php`;
- obě cesty přihlášení obsluhy nastavují `SQL_ROK` na aktuální rok.

Původní `Zakaznici.php` ani `ssf_zakaznici.php` se v této verzi nepřepisují.
