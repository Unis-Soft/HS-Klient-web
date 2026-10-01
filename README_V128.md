# HairSoft Klient V128 – doladění voucherů

Navazuje na kompletní V127.

1. Odebrány exporty ze spodních sumářů v přehledu i historii. Export hlavních seznamů zůstává.
2. Nad širokou tabulkou přibyl druhý vodorovný posuvník propojený se spodním. Reaguje na změnu šířky i překreslení tabulky. Na mobilních kartách se nezobrazuje.
3. Statusy se zobrazují celé na jednom řádku bez dělení slov. Sloupec se přizpůsobí obsahu; širší tabulka se posouvá.
4. PDF skládá vouchery po dvojicích do dvou sloupců oddělených linkou. Běžně se vejdou čtyři vouchery na A4. Zachovává všechny datové údaje, poslední lichý voucher je vlevo.
5. Počáteční vykreslení čeká na připravený moderní obsah, takže při běžném refreshi neproblikne původní rozložení. Při selhání načítání skriptů se ochrana po 8 sekundách uvolní, aby stránka nezůstala skrytá.
6. V detailu je původní mazací formulář přesunut do samostatné sekce Správa dat nad patičkou. Editace zůstatku je modrá, editace platnosti zlatá. Na PC jsou ovládací tlačítka vedle sebe, na mobilu pod sebou.

## Nahrání

Nahrajte složky **hs-client-ui** a **str** do kořene webu. Poté Ctrl+F5. Původní **str/strana ponechte** na serveru.

Při kompletní V127 jsou změněny:
- hs-client-ui/js/voucher.js
- hs-client-ui/js/voucher-pdf.js
- hs-client-ui/css/voucher.css
- hs-client-ui/js/i18n.js
- hs-client-ui/php/index.php
- hs-client-ui/VERSION.txt

## Ověření

15 kombinací přehledu, prázdných dat, obou editačních režimů a zneplatněných voucherů na šířkách 390, 844 a 1440 px. Kontrola původních formulářových hodnot, umístění mazání před patičkou, obousměrného posuvu, statusů, jazyků a nepřetékajícího mobilního rozložení. Zpomalené načítání ověřuje skrytí původního vzhledu a následné zobrazení hotové stránky. PDF s pěti záznamy vizuálně ověřeno a zkontrolována přítomnost všech údajů; Excel zůstává platný. Potvrzení mazání otestováno zrušením, bez skutečného odeslání.

PHP výpočty a databázové operace jsou totožné s V127. Testy jsou lokální, bez zásahu do skutečných voucherů. Na živý server nebylo nasazeno.
