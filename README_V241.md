# HS Klient V241 – drobnosti Programů

V241 navazuje přímo na V240.

## Změny
- Na mobilu je ikona fotoaparátu absolutně umístěná vpravo v hlavičce na úrovni „Programy zákazníka“ a už nezvětšuje výšku hlavičky. Desktopové umístění zůstává stejné.
- Docházka a Předplacené vstupy zobrazují nejvýše 10 viditelných záznamů. Při vyšším počtu dostane tabulka vlastní svislý scroll a sticky hlavičku. Funguje na mobilu i PC.
- Oba programové grafy jsou v samostatném vodorovně posuvném viewportu. Šířka grafu roste podle počtu bodů/intervalů, takže datumy mají čitelný rozestup.
- Po kliknutí na Odeslat do HairSoft se přes foto modal zobrazí informační overlay se spinnerem a textem, že fotografie probíhají odesláním.
- Ikona fotoaparátu má číselný badge s celkovým počtem fotografií, které HSBridge potvrdil stavem `done` pro daného zákazníka a konkrétní program. Po nové dávce klient toto číslo krátce polluje a aktualizuje až po skutečném potvrzení Bridge.
- Počítadlo používá existující `hsbridge_program_photo_jobs.total_files`. HS Klient fotografie dlouhodobě nearchivuje; po potvrzení Bridge se dočasné cloudové soubory smažou jako ve V240 a zůstane pouze číselný audit úspěšně předaných fotografií.
- Doplněny nové překlady CZ/SK/EN/DE.

## Bridge
Webová změna nevyžaduje novou verzi Bridge. Aktuální provozní Bridge zůstává **HSBridge V013** s desetimístnými adresáři zákazníků (`0000000012`).

## Cache
`client-detail-programs.css` a `client-detail-programs.js` používají `v=241`.