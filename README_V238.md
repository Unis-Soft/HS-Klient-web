# HS Klient V238 – Programy a exporty

V238 navazuje přímo na V237.

## Opraveno
- Detail zákazníka → Programy: odstraněna chyba `minY is not defined` v grafu Frekvence docházky.
- Graf zůstatku používá skutečný rozsah `minY` až `maxY`, takže umí korektně zobrazit i záporný zůstatek.
- Tlačítko **Čerpat** je uvnitř karty **Zbývá**; původní hlavička Programů zůstává beze změny.
- Seznam zákazníků → Export: Kopírovat, CSV, XLS, PDF a Tisk mají vlastní stabilní SVG ikonu v jednotném stylu; není závislé na dostupnosti jednotlivých Font Awesome glyphů.
- Endpoint pro zpětné čerpání je fyzicky přiložen také jako `/str/api/hsbridge-program-actions.php`, takže jej lze nasadit společně s HS Klientem a HSBridge V011 dostává JSON místo HTML/404.

## Beze změny
- Logika čerpání z V237: pouze množství, bez kontroly nároku a bez storna/mazání.
- HairSoft má vždy prioritu při práci s databází.
- HSBridge zůstává V011; nový EXE není pro V238 potřeba.