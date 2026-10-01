# HS Klient V229 – stabilní shell při přechodu mezi moduly

V229 opravuje bílé probliknutí při přechodu mezi hlavními sekcemi HS Klientu.

## Co bylo skutečně špatně

V228 odstranila globální masku, ale odkazy v levém menu stále prováděly běžnou
navigaci na nový PHP dokument. Během čekání na nový dokument proto prohlížeč
mohl ukázat prázdný mezistav. Nově načtený modul navíc často obsahoval legacy
`animated fadeInUp`, takže následovalo druhé vizuální probliknutí.

## V229

- používá same-origin cross-document View Transition;
- horní hlavička a levé menu mají vlastní stabilní transition vrstvu;
- během čekání na nový PHP dokument zůstává předchozí shell na obrazovce;
- hlavní obsah se po připravení nového dokumentu pouze velmi krátce překlopí;
- legacy `fadeInUp` je vypnut pouze pro `#main-content`;
- V228 JavaScriptové zachytávání odkazu bylo odstraněno a odkazy jsou znovu
  standardní PHP navigace;
- modulové PHP, DataTables, formuláře a inicializace JavaScriptu se nemění;
- vlastní voucherový boot/spinner zůstává zachovaný.

Primárně je určeno pro aktuální Chrome/Edge/Brave, kde je cross-document View
Transitions podporováno. V prohlížeči bez této podpory proběhne běžná navigace
bez zásahu do funkcí aplikace.
