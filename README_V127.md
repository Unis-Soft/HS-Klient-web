# HairSoft Klient V127 – Vouchery

Kompletní balíček navazuje na V126 a zachovává předchozí schválené úpravy.

## Vouchery

- Moderní přehled, filtr, historie a zneplatněné vouchery; sjednocené písmo, hlavičky, tlačítka a správa dat.
- Mobilní karty obsahují všechny původní údaje. Fungují na výšku i na šířku, mají stejnou plnou vnitřní šířku jako Export. Ovládací ikony panelů nepřekrývají nadpisy.
- Vyhledávání a stránkování se vzhledem seznamu zákazníků. Detail zachovává původní identifikátor a adresu.
- Původní formuláře pro změnu zůstatkové ceny a platnosti zůstávají funkčně totožné: stejné názvy, hodnoty, adresy, skrytá metadata i potvrzení mazání. Jde o úpravu zůstatku, nikoliv změnu významu původní celkové ceny.
- Expirované vouchery zůstávají volbou ve filtru Stav. Zneplatněné vouchery jsou samostatný původní přehled.
- Exporty přehledu, historie a sumářů. PDF zachovává všechny datové sloupce v čitelných blocích po jednotlivých voucherech. Kopírování, CSV, Excel a tisk zachovávají vazbu na hledání. Opravena prázdná definice stylu starého Excel exportéru pouze pro vouchery.
- Kruhový graf má pevný kruhový poměr, sjednocené barvy s legendou a tooltipy myší i dotykem. Bez dat zobrazí neutrální kruh s nulou.
- České, slovenské, anglické a německé popisky včetně formulářů a potvrzení mazání.
- Moderní patička uchovává původní význam „Poslední změna“, datum, uvedený interval 15 minut a čas načtení. Chybějící datum nenahrazuje smyšleným údajem.

## Soubory a nahrání

Nahrajte složky **hs-client-ui** a **str** z balíčku do kořene webu. Poté Ctrl+F5.
Původní serverový adresář **str/strana ponechte na místě**: veřejný rozcestník jej nadále používá při ověřování dostupnosti stránek.

Změněné nebo nové soubory oproti V126:

- hs-client-ui/php/strana/Voucher.php
- hs-client-ui/php/strana/VoucherHistorie.php
- hs-client-ui/php/strana/VoucherZnpeplatneny.php
- hs-client-ui/js/voucher.js
- hs-client-ui/js/voucher-pdf.js
- hs-client-ui/css/voucher.css
- hs-client-ui/js/i18n.js
- hs-client-ui/js/page-status.js
- hs-client-ui/php/index.php
- hs-client-ui/VERSION.txt

**voucher1.php není součástí nové vrstvy.** V dodaných souborech ani v menu na něj nebyl nalezen odkaz; obsahuje také starší nesouvisející části. Na serveru jej nemažte. Jeho případné používání mimo dodané zdroje nebylo možné potvrdit.

## Ověření

- PHP syntaktická kontrola tří stránek a společného vstupu, JS syntaktické kontroly.
- Porovnání PHP tokenů s originály: stejné výpočty, SQL operace a rozhodování. Rozdíly pouze v cestách ke společným souborům, HTML a vykreslovacím JavaScriptu.
- Porovnání formulářů v prohlížeči: stejné adresy, metody, hodnoty, skryté údaje a potvrzovací obsluha.
- Chrome: 15 kombinací přehledu, prázdných dat, obou editačních formulářů a zneplatněných voucherů na šířkách 390, 844 a 1440 px. Přepínání CS/EN/SK/DE/CS, bez vodorovného přetečení stránky.
- Skutečné stažení PDF/CSV/XLSX; PDF vizuálně zkontrolováno, XLSX otevřen a ověřeno všech 15 sloupců.
- Hledání a stránkování nad 31 testovacími záznamy, tooltipy, potvrzení mazání zrušené bez odeslání.
- 20 regresních kontrol společné patičky včetně výjimek Bonfero a Nový zákazník.

Testy používají lokální modelová data. Nebylo nasazeno na živý server ani měněny skutečné vouchery. Původní serverový PDF export a jeho backend nebyly upravovány.
