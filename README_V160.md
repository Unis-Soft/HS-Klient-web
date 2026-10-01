# V160 – Docházka – rozložení ovládání a PDF

- Refresh z PC a přepínač servisních obsluh jsou na desktopu ve stejné řadě jako Středisko, Měsíc a Rok.
- Samostatná karta Zobrazení obsluh byla odstraněna; stav servisních obsluh je uveden v metainformaci pod filtrem.
- Pole Hledat bylo z tabulky Docházky odstraněno.
- Export je vlevo nad tabulkou a používá stejný kompaktní vzhled jako ostatní moderní sekce.
- PDF export byl přepsán na robustní renderer HairSoft; vlastní PDF skript se načítá synchronně před inicializací DataTables a při případné chybě customizace neblokuje výchozí PDF export.
- PDF zůstává A4 na šířku, obsahuje období, středisko, režim servisních obsluh, adaptivní velikost písma, hlavičku HairSoft a číslování stran.
- Datová logika, refresh požadavky, SQL, názvy formulářových polí a parametr ZobrazitServisni zůstávají beze změny.
