# HS-Klient-web

Webový HS Klient a související HSBridge.

## Aktuální verze

- HS Klient: **V245**
- HSBridge: **V014**
- PROGRAMS synchronizace: **každých 10 minut**, snapshot se odesílá pouze při změně dat; potvrzené čerpání se zrcadlí okamžitě pro konkrétního zákazníka.
- HairSoft databáze pro PROGRAMS: **SQLite i MySQL síťová verze**.

## Síťové pravidlo V008

HSBridge je aktivní pouze na jednom PC pro jednu HairSoft databázi:

- SQLite instalace → aktivní Bridge,
- MySQL server PC → `SQLHost=localhost` nebo loopback → aktivní Bridge,
- MySQL klient PC → vzdálená IP/hostname v `SQLHost` → **pasivní Bridge**.

Pasivní MySQL klientské PC neposílá ani nepřijímá žádnou HSBridge komunikaci.
Pravidlo platí globálně pro současné i budoucí Bridge moduly, tedy nejen pro
PROGRAMS.

## MySQL HairSoft

HSBridge pozná síťovou instalaci podle SQL parametrů v `HairSoft\Settings.xml`:
`SQLHost`, `SQLPort`, `SQLDatabase`, `SQLUser` a `SQLPasword`.

Pokud jsou SQL parametry přítomné, PROGRAMS používá MySQL přes TCP. Pokud chybí,
zůstává původní SQLite detekce přes `DatabaseFile` / `data.sdb`.
Hodnota `OpenSQLsettings` se jako přepínač nepoužívá.

MySQL heslo se nekopíruje do HSBridge `config.json` a neloguje se.

## Struktura

- `hs-client-ui/` – webové rozhraní HS Klientu
- `str/` – původní/serverová část HS Klientu
- `bridge/windows/` – Windows HSBridge
- `bridge/hsklient-server/` – serverový endpoint HSBridge pro HS Klient
- `bridge/directory/` – Bridge Directory / identita a párování

CUSTOMER / STATISTICS / TIMELINE zůstávají v HSBridge neaktivní; tyto oblasti
nadále řeší SoftKW / SoftSYS. Cooper voucher flow zůstává zachovaný. Na MySQL
klientském PC je však V008 centrálně pasivní, takže ani voucherová ani jiná
Bridge komunikace z takového PC neprobíhá. Bonfero bude řešeno samostatným
repozitářem.


## V010 – MySQL PROGRAMS

Podle diagnostiky reálné MySQL databáze HairSoft používají tabulky
`programs` a `program_values` pro název sloupec `name1`.
V010 proto používá:
- `programs.name1`
- `program_values.name1`

Ostatní MySQL PROGRAMS tabulky a vazby zůstávají beze změny.
Dočasná V009 schema diagnostika byla odstraněna.


## V231 – Programy vizuální opravy

V231 vychází ze stabilní V227. Odstraňuje pouze druhý `fadeInUp` flash,
opravuje šířku/typografii názvu Programu a odstraňuje deformaci SVG textů
v obou grafech Programů. HSBridge V010 zůstává beze změny.


## V232 – Programy: názvy a grafy

Opraveno zobrazení názvu jednoho Programu v seznamu i detailu. Graf zůstatku
slučuje více událostí stejného dne do jednoho vizuálního bodu a drží krajní
datumy uvnitř grafu. Graf frekvence zobrazuje datum pod každým sloupcem při
běžném počtu intervalů.


## V233 – česká diakritika v názvu Programu

Opraveno vertikální ořezávání velkých českých znaků v názvu Programu, zejména
Ě. Logika jednoho programu, zkracování dlouhého názvu a combo pro více programů
zůstává beze změny.


## V234 – Programy: KPI, diakritika a překlady

V seznamu zákazníků přibyla souhrnná karta Programy s celkovým počtem
nevyčerpaných vstupů napříč aktivními programy. Při více programech karta
zůstává bez comboboxu; konkrétní program se dál vybírá pouze v hlavičce tabulky.

Opraven byl skutečný důvod špatného vykreslení velkého Ě – globální Tahoma
`!important` přebíjela předchozí override. Programová sekce je zároveň nově
zapojena do i18n a název ZÓNY TĚLA se zobrazuje jako Zóny těla / Zóny tela /
Body zones / Körperzonen.

## V235 – Programy: skutečná KPI karta a i18n grafů

Karta Programy se skutečně vkládá do horního KPI gridu. Programové grafy mají
doplněné dynamické překlady a známý program je před vykreslením normalizován.

## V236 – PDF zákazníků a definitivní normalizace Zóny těla

Seznam zákazníků používá vlastní moderní PDF HairSoft místo původního výchozího
DataTables PDF. Normalizace známého programu je nově založená na složeném klíči,
takže zachytí i chybnou variantu `ZÓNY TÌLA`. Součástí jsou také připravené
překlady chybějících KPI Měsíčních tržeb.

## V240
- Oprava validace programu pro foto upload: hsbridge_programs.name (nikoli name1).
- Vlastni stylovany vyber fotografii + samostatne foceni z kamery.
- Bridge zustava V012 GUI.

## V243 – trvalá lokální autorizace PROGRAMS Bridge

PROGRAMS action/photo endpoint už nepoužívá 60sekundovou cache s pravidelným
opakovaným ověřováním proti externímu Directory. První úspěšné ověření uloží
lokální vazbu `bridge_id + SHA-256(token) -> sw_id`; další polling se ověřuje
výhradně v lokální MySQL HS Klient. Aktuální skupina a licence se dál kontrolují
lokálně při každém požadavku. Windows HSBridge zůstává V013.


## V244 – okamžitý odpis a rychlejší foto náhledy

Po potvrzeném čerpání se do HS Klient okamžitě zrcadlí pouze nová návštěva konkrétního zákazníka; plný kontrolní PROGRAMS snapshot běží každých 10 minut. Foto modal má jedinou akci Vyfotit a náhled se ukáže okamžitě, protože JPEG resize se přesunul až do fáze Odeslat do HairSoft. HSBridge V014.


## V245 – Export detailu zákazníka

Detail zákazníka má nový export do reprezentativního PDF a vícelistového XLSX.
PDF obsahuje profilovou fotografii pouze jako profilovku, základní/kontaktní, zdravotní,
firemní a systémové údaje, Timeline, všechny Programy včetně docházky a předplacených
vstupů, Hodnocení a seznam názvů Souborů. Galerie, fotografie Programů a SMS Chat se
do dokumentu nevkládají. Excel obsahuje samostatné listy podle typu dat. Export je
zapojen do CZ/SK/EN/DE i18n. HSBridge zůstává V014.