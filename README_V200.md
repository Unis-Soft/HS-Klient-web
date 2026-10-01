# V200 – Sklad, Správa dat zákazníka a ikony kopie

Navazuje přímo na V199.

## Opravy
- XML varianta Skladu nyní obsahuje v rozbalené **Správě dat** také **Refresh skladových dat z PC**.
- Refresh XML skladu používá stejný existující backend `SkladyRefresh.php` a stejný stav požadavku jako klasický Sklad; při běžícím požadavku se nevytváří druhý.
- Na Kartě zákazníka je původní blok **Možnosti** nahrazen výchozí sbalenou sekcí **Správa dat**.
- Vzhled a Tahoma typografie Správy dat odpovídají Seznamu zákazníků a Skladu.
- Obsah zůstává stejný: Refresh stránky, TEST kopie do jiné firmy, Smazat osobu a historie TEST kopií.
- Tlačítko **Zkopírovat do jiné firmy** má vlastní SVG ikonu.
- Modal TEST kopie používá vlastní SVG ikony pro zavření, kopii, bezpečnost, cílovou firmu, pobočku, přenášená data, informaci a tlačítka. Tím se odstraňují chybějící/čtverečkové glyphy starého Font Awesome.

## Beze změny
- databázová struktura,
- logika V195/V198 kopie zákazníka a rollback,
- multi-firma,
- synchronizační protokol HairSoft,
- dotaznik.net.
