# V159 – Docházka

- Nový moderní override `hs-client-ui/php/strana/Dochazka.php` nad původní datovou/refresh logikou.
- Moderní horní filtry: středisko, měsíc, rok, Refresh z PC a zobrazení servisních obsluh.
- Jasné rozlišení požadovaného období od skutečně načtených dat v tabulce.
- Moderní tabulka ve stylu Voucherů/Tržeb, hledání, exportní menu a bezpečně odsazený horizontální posuvník.
- Mobilní karty po jednotlivých dnech, včetně souhrnu Celkem.
- Exporty Copy / CSV / XLS / PDF / Tisk.
- PDF: HairSoft report, A4 landscape, pobočka/středisko/období, adaptivní velikost písma podle počtu obsluh, číslování stran.
- Překlady CZ/SK/EN/DE.
- Standardní spodní patička stránky (jen doba načtení; Docházka nemá spolehlivý timestamp synchronizace).
- Odstraněn původní debug výpis vybraného střediska (`***ID`).
- SQL dotazy pro refresh a načtení docházky jsou zachované významově; formulářové názvy a parametr `ZobrazitServisni` zůstávají kompatibilní.
