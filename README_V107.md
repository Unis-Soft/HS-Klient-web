# HairSoft Klient V107

V107 je kritická běhová oprava zdrojového souboru Měsíčních tržeb.

- Override načítá `_zabezpeceni.php`, `GeneratorBarev.php`, `SpolecneFunkce.php` a `DemoData.php` přes absolutní `HS_CLIENT_UI_ROOT`; umístění souboru v `hs-client-ui/php/strana` už nerozbije závislosti.
- Všech 20 polí pro typy plateb je vytvořeno před podmíněným průchodem středisek; následné `count()` proto v PHP 8.3 nedostane `null`.
- Ověřeno příkazem `php -l` nad oběma kopiemi zdroje.
- Běhový PHP test s naplněnými testovacími výsledky vygeneroval 90 kB HTML, obě tabulky, canvas prvky a JSON data služeb, prodeje i plateb.
- JavaScript měsíční stránky a PDF exportu prošel syntaktickou kontrolou Node.js.
- SQL, databázová struktura, formuláře, částky a výpočty nebyly změněny.

---

# HairSoft Klient V106

V106 opravuje přímo zdrojové vykreslení grafů Měsíčních tržeb.

- Nový override `hs-client-ui/php/strana/MesicniTrzby.php` zachovává původní SQL a výpočty.
- Grafová data se předávají přes `json_encode`, takže názvy s diakritikou nebo apostrofem nerozbijí JavaScript.
- Chart.js inicializuje pouze existující canvas prvky; chybějící pobočka nezastaví ostatní grafy.
- Všechny canvas elementy mají správný uzavírací tag.
- Po responzivním přesunutí canvasu se provede `resize()` a `update()`.
- Cache měsíčních assetů je zvýšena na V106.

---

# HairSoft Klient V105

V105 opravuje prázdné tabulky a grafy Měsíčních tržeb způsobené souběhem původní a moderní inicializace.

- DataTables a původní Chart.js se vždy dokončí před přeskládáním prvků.
- Každý existující graf se umí bezpečně inicializovat samostatně z původních dat.
- Chybějící volitelný graf už nemůže zastavit vykreslení ostatních grafů.
- Cache verze měsíčních CSS/JS assetů je zvýšena na V105.
- PHP, SQL, formuláře, částky a výpočty zůstávají beze změny.

---

# HairSoft Klient V104

V104 modernizuje obsah stránky Tržby → Měsíční tržby pod již hotovými horními KPI kartami.

- Jediné přehledné ovládání měsíce a roku nahrazuje opakované navigace v panelech.
- Oba měsíční souhrny mají moderní desktopovou tabulku a samostatné mobilní karty.
- Export je zarovnaný vpravo a PDF vytváří lokalizovaný A4 přehled bez dělení jednotlivých záznamů.
- Prstencové grafy používají schválený HairSoft styl, součet uprostřed a interaktivní tooltip s podílem.
- Sloupcový a čárový graf mají čitelné osy, Open Sans a tooltipy; na mobilu se nesmršťují, ale posouvají.
- Správa dat je přesunuta před synchronizační patičku a zachovává původní formuláře a hodnoty.
- Doplněny překlady CZ, SK, EN a DE pro nové prvky i měsíční obsah.
- Horní KPI karty, PHP, SQL, databáze a výpočty zůstávají beze změny.

---

# HairSoft Klient V103

V103 opravuje vystředění ovladače data na telefonech v režimu na výšku.

- Breakpoint do 420 px již nepřepisuje pružné boční sloupce pevnými šířkami.
- Datum zůstává přesně na středové ose celé karty.
- Obaly šipek se smrští na velikost tlačítka a skutečně se zarovnají směrem k datu.
- Režim na šířku zůstává zachovaný.
- Překlady, data, tabulky, grafy, exporty a funkce se nemění.

---

# HairSoft Klient V102

V102 opravuje přesnou polohu data v mobilním ovladači Denních tržeb.

- Datum má vlastní prostřední sloupec ukotvený přesně na středu panelu.
- Levá a pravá část mají vždy shodnou pružnou šířku.
- Šipka zpět je zarovnaná k datu zprava a šipka vpřed zleva.
- Formulář vyplňuje celý prostřední sloupec, takže datum již neposouvá jeho vnitřní šířka.
- Jazykové mutace V101, data, tabulky, grafy, exporty a funkce se nemění.

---

# HairSoft Klient V101

V101 kompletně napojuje stránku Tržby → Denní tržby na jazykové mutace.

- Doplněny překlady CZ, SK, EN a DE pro popis stránky a všechny horní ukazatele.
- Překládají se výběr data, oba denní sumáře, názvy sloupců, součty a stavy bez dat.
- Překládají se názvy grafů, podtexty, běžné typy plateb, tooltipy a text uprostřed prstence.
- Překládají se upozornění a celá sekce Správa dat včetně jejích tlačítek.
- Dynamické prvky reagují také na změnu jazyka bez opětovného načtení stránky.
- PDF export respektuje právě zvolený jazyk.
- Rozpoznání tabulek, součtů, šipek a akcí již není závislé na českém textu.
- Data, částky, formuláře, grafické styly V97–V100, PHP, SQL a databáze se nemění.

---

# HairSoft Klient V100

V100 mění pouze mobilní ovládání data v Denních tržbách.

- Šipka zpět, datum a šipka vpřed tvoří jeden kompaktní celek.
- Celý trojprvkový ovladač je přesně vystředěný v panelu.
- Ostatní opravy V99, desktop, tabulky, grafy i funkce zůstávají beze změny.

---

# HairSoft Klient V99

V99 dolaďuje čtyři konkrétní problémy mobilních Denních tržeb.

- Horní karty již nepřebírají druhou ikonu ani rozložení z obecného dashboardu.
- Jméno TOP obsluhy se zobrazuje celé a může se přirozeně zalomit na dva řádky.
- Počet účtenek je v jedné kompaktní vodorovné kartě se správně vyrovnaným popiskem a hodnotou.
- Hlavička i ovládání sekce Tržby pro datum jsou vystředěné; datum zůstává přesně mezi šipkami.
- Data obou denních sumářů jsou znovu viditelná jako mobilní karty.
- Oprava odděluje mobilní karty od skrytého desktopového posuvníku také po pozdější inicializaci DataTables.
- Hledání, stránkování a Export dále aktualizují stejné zdrojové tabulky.
- Desktop, schválené kruhové grafy V97, PDF, formuláře, PHP, SQL a databáze se nemění.

---

# HairSoft Klient V98

V98 kompletně přepracovává mobilní zobrazení stránky Tržby → Denní tržby.

- Horní ukazatele TOP obsluhy za služby, TOP obsluhy za prodej a počtu účtenek mají moderní kompaktní karty s vlastními ikonami.
- Široká desktopová tabulka se na telefonu nezobrazuje jako dlouhý seznam samostatných buněk.
- Každý záznam má vlastní kompaktní kartu s názvem střediska nebo obsluhy.
- Pět finančních kategorií tvoří přehlednou mřížku; částka a hodnota bez DPH zůstávají vždy pohromadě.
- Celková tržba je zvýrazněná přes celou šířku a Součet má vlastní barevně odlišenou kartu.
- Datum je v jedné řadě mezi dvěma kompaktními šipkami.
- Nadpisy se správně zalamují a staré ikony pro maximalizaci, sbalení a zavření jsou na telefonu skryté.
- Původní široká tabulka a její posuvník již nezpůsobují vodorovné přetékání.
- Hledání a Export jsou na telefonu pod sebou přes celou šířku; moderní stránkování dál pracuje nad původními DataTables daty.
- Kruhové grafy zachovávají schválený vzhled V97 a na mobilu jsou kompaktnější.
- Hodnoty, formuláře, tabulky na PC, exporty, PDF, PHP, SQL a databáze se nemění.

---

# HairSoft Klient V97

V97 modernizuje všechny prstencové grafy na stránce Tržby → Denní tržby.

- Grafy mají tenčí prstenec, jemnou podkladovou stopu, čisté bílé mezery mezi segmenty a decentní stín.
- Uprostřed se zobrazuje součet: částka v Kč, u grafu účtenek celkový počet.
- Nová tlumená HairSoft paleta nahrazuje původní náhodné ostré barvy.
- Stejný zaměstnanec nebo typ platby má ve všech grafech stejnou barvu.
- Původní legenda automaticky přebírá nové barvy a při najetí se jemně zvýrazní.
- Tooltip na PC i po klepnutí na mobilu ukáže název, přesnou hodnotu a procentní podíl.
- Hodnoty, výpočty, tabulky, exporty, PHP, SQL a databáze se nemění.

---

# HairSoft Klient V96

V96 dolaďuje PDF a ovládací lišty stránky Tržby → Denní tržby.

- Každý souhrnný blok PDF je nedělitelný: jeho hlavička a všech pět částek zůstávají vždy na stejné A4.
- Pokud se blok Součet do zbytku stránky nevejde, přesune se celý na následující stránku.
- Export obou denních tabulek je fyzicky vložen do vlastní lišty zarovnané vpravo.
- Tlačítko Export má bílý moderní vzhled, jednotnou ikonu, Open Sans a jemné zvýraznění po najetí.
- Copy, CSV, XLS, Tisk, tabulková data, grafy, PHP, SQL a databáze se nemění.

---

# HairSoft Klient V95

V95 opravuje a dokončuje modernizaci stránky Tržby → Denní tržby z V94.

- Výběr data je v samostatné zaoblené kartě s jednotným ovládáním předchozího a dalšího dne.
- Denní souhrnné tabulky mají moderní hlavičky, kompaktní řádky, zvýrazněné součty a zachovaný Export.
- Export je zarovnaný vpravo a PDF používá vlastní čitelný report HairSoft místo široké tabulky s 12–13 stlačenými sloupci.
- Na telefonu se stejné tabulkové řádky skládají do čitelných karet podle skutečných názvů sloupců.
- Koláčové grafy jsou v responzivních kartách s moderní legendou a tooltipy; jejich plátno má vždy skutečnou šířku a původní hodnoty i barvy se nemění.
- Akce Načíst starší data, Refresh dat z PC a Smazat zobrazená data jsou přesunuty do nové sekce Správa dat na předposlední místo stránky.
- Přesouvají se původní formuláře a tlačítka, takže jejich adresy, POST hodnoty, potvrzení a serverová logika zůstávají zachované.
- Celá stránka používá konstantní `Open Sans, Helvetica, Arial, sans-serif`, včetně tabulek, grafů, tlačítek a exportu.
- Měsíční, roční a celkové tržby ani databáze se nemění.

---

# HairSoft Klient V93

V93 vkládá administraci rezervací Bonfero přímo do HairSoft Klient.

- Rezervace → Administrace nově otevře interní stránku s responzivním iframe Bonfero.
- HairSoft a Bonfero zůstávají dva oddělené systémy bez propojení účtů nebo hesel.
- Přihlášení probíhá přímo v Bonferu a jeho zapamatování řídí relace Bonfera v prohlížeči.
- Panel obsahuje stav načítání, ruční obnovení a záložní otevření Bonfera v novém okně.
- Nové ovládací texty podporují češtinu, slovenštinu, angličtinu a němčinu.
- Přímý přístup používá stejné oprávnění Rezervace jako původní položka menu.
- Odkaz Google kalendář zůstává beze změny.
- PHP, SQL a databáze HairSoft se nemění.

---

# HairSoft Klient V92

V92 doplňuje náhled Poznámky také do mobilního zobrazení.

- Ve sbaleném mobilním panelu je mezi názvem Poznámka a šipkou vidět začátek textu.
- Náhled zůstává na jednom řádku a podle volného místa se zakončí třemi tečkami.
- Po rozbalení se náhled skryje a zobrazí se pouze původní editovatelné pole.
- Prázdná poznámka používá přeložený stav Bez poznámky.
- Desktopové pořadí Profil, navigace, Poznámka z V91 se nemění.
- Textarea, formulář, ukládání, PHP, SQL a databáze se nemění.

---

# HairSoft Klient V91

V91 upravuje pouze levý sloupec karty zákazníka na PC.

- Navigace Informace až Soubory je nově hned pod profilovou kartou.
- Poznámka je přesunuta pod navigaci a ve výchozím stavu je sbalená.
- Sbalený řádek ukazuje jednořádkový náhled poznámky nebo text Bez poznámky.
- Kliknutí na celou hlavičku Poznámky ji rozbalí; šipka zřetelně ukazuje stav.
- Změny textu se ihned projeví také v náhledu.
- Levý sloupec zůstává na PC při rolování přichycený a v případě malé výšky má vlastní jemný posuv.
- Mobilní profil, pořadí navigace a sbalená Poznámka zůstávají beze změny.
- Textarea, její název, odesílání formuláře, PHP, SQL a databáze se nemění.

---

# HairSoft Klient V90

V90 nahrazuje nespolehlivý vestavěný tooltip starší Chart.js vlastní responzivní kartou HairSoft.

- Na PC se karta zobrazí při najetí na oblast konkrétního měsíce; křížový kurzor byl odstraněn.
- Tooltip obsahuje měsíc, všechna střediska, jejich barvy a přesné částky s měnou.
- Na mobilu se tooltip otevře krátkým klepnutím a zavře klepnutím mimo graf nebo posunem stránky.
- Krátké klepnutí je odlišeno od vodorovného tažení, takže posouvání mezi 12 měsíci zůstává funkční.
- Karta se automaticky udrží uvnitř viditelné plochy obrazovky.
- Data, výpočty, osy, grafy, PHP, SQL a databáze se nemění.

---

# HairSoft Klient V89

V89 navazuje na V88 a opravuje vykreslení os i interaktivitu grafů Dashboardu.

- Graf se vytváří rovnou s konečnou responzivní velikostí, takže canvas již není následně deformovaný CSS.
- Nejvyšší hodnota osy Y má bezpečné horní odsazení a není oříznutá ani rozmazaná.
- Osa Y používá čitelné kompaktní hodnoty, například `140 tis. Kč`, a popisek Částka.
- Na ose X jsou vždy viditelné všechny měsíce od ledna do prosince.
- Hover nad libovolnou částí měsíce otevře tooltip s názvem měsíce, všemi středisky a přesnými částkami.
- Tooltip používá moderní tmavý panel, přehledné barvy středisek a konstantní Open Sans.
- Na dotykovém zařízení lze hodnoty otevřít dotykem a grafem současně vodorovně procházet.
- Opravena je také situace, kdy graf Kredit není zobrazen; ostatní grafy se vytvoří bez JavaScriptové chyby.
- Původní data, částky, střediska, barvy, PHP výpočty, SQL a databáze zůstávají beze změny.

---

# HairSoft Klient V88

V88 navazuje na V87 a modernizuje všechny měsíční grafy na Dashboardu.

- Grafy Tržby za služby, Tržby za prodej, Tržby za Vouchery a podmíněně Tržby za Kredit používají stejný moderní komponent.
- Každý graf má zaoblenou kartu, sjednocenou hlavičku, podtext a kompaktní ovládání roku i panelu.
- Původní sloupce mají jemný gradient a zaoblené vršky; barvy jednotlivých středisek zůstávají zachované.
- Osy používají konstantní Open Sans, čistou typografii a jemnou přerušovanou mřížku.
- Tooltip zobrazuje měsíc, středisko a přehledně formátovanou hodnotu.
- Součty středisek jsou převedené na čitelné štítky s původní barevnou identifikací.
- Na mobilu lze legendu i graf vodorovně posouvat, takže zůstává dobře čitelných všech 12 měsíců bez skrývání dat.
- Původní data, jejich pořadí, typ grafu, roční navigace, akce panelu, PHP, SQL a databáze zůstávají beze změny.

---

# HairSoft Klient V87

V87 navazuje na V86 a modernizuje poslední záložku Soubory v detailu zákazníka.

- Sekce má vlastní zaoblený panel, moderní hlavičku s počtem souborů a jednotný font Dashboardu.
- Pole pro nahrání je výrazné, plně klikací, ovladatelné klávesnicí a při přetažení zobrazuje aktivní stav.
- Po výběru souboru se zobrazí průběh odesílání; limit 30 MB zůstává viditelný.
- Desktopový seznam používá kompaktní hlavičku, vyvážené šířky, typové ikony souborů a čitelné stavové štítky.
- Na mobilu se každý soubor zobrazí jako samostatná karta s názvem, velikostí, datem, stavem a tlačítkem odstranění.
- Prázdný seznam nezabírá zbytečnou výšku a obsahuje jasnou výzvu k prvnímu nahrání.
- Původní nahrávání, automatické odeslání, odkazy ke stažení, stavové hodnoty, mazání, PHP, SQL a databáze zůstávají beze změny.

---

# HairSoft Klient V86

V86 navazuje na V85 a nahrazuje výchozí PDF export Hodnocení vlastní moderní šablonou ve stylu Timeline.

- PDF používá stejnou vizuální identitu jako Timeline: hlavičku HairSoft, barevnou linku, údaje zákazníka, datum vytvoření a číslování stran.
- Široká tabulka s až 15 sloupci se do PDF nezmenšuje; každé hodnocení se exportuje jako samostatný čitelný blok.
- V horní části bloku je zákazník, datum hodnocení, termín objednávky a obsluha.
- Pod nimi jsou všechny skutečně vyplněné otázky a odpovědi, včetně dlouhých textových odpovědí.
- Hvězdičkové hodnocení je zobrazeno kompaktně jako hodnota z pěti.
- Export respektuje aktuální vyhledávání a pořadí tabulky a zahrnuje všechny odpovídající záznamy, nejen aktuální stránku.
- Název souboru obsahuje zákazníka a datum vytvoření.
- Copy, CSV, XLS, Tisk, zobrazení tabulky, mobilní karty, PHP, SQL a databáze zůstávají beze změny.

---

# HairSoft Klient V85

V85 navazuje na V84 a opravuje chybnou šířku horních údajů mobilní karty Hodnocení.

- Mobilní buňky zákazníka, obou termínů a obsluhy již nepřebírají pevné desktopové šířky 170, 150, 135 a 145 px.
- Jméno zákazníka proto využívá skutečnou dostupnou šířku a neláme se po jednotlivých písmenech.
- Kontaktní údaje zůstávají kompaktně vpravo a souhrnné dlaždice využívají celou mobilní mřížku.
- Desktopová tabulka, fonty, otázky, odpovědi, náhledy, odkazy, export, stránkování, PHP, SQL a databáze zůstávají beze změny.

---

# HairSoft Klient V84

V84 navazuje na V83 a sjednocuje typografii a horní souhrn mobilních karet Hodnocení.

- Celá záložka Hodnocení používá jednu konstantní typografickou sadu: Open Sans se stejným pořadím náhradních fontů, vyhlazováním a základní velikostí jako Dashboard a seznam zákazníků.
- Desktopová tabulka používá základní velikost 13 px a sjednocené vykreslení písma v hlavičce, datech, štítcích i náhledech.
- Mobilní hlavička karty má jméno vlevo a kontaktní údaje kompaktně vpravo, takže nevzniká velký prázdný prostor.
- Datum hodnocení, termín objednávky a obsluha jsou na běžném mobilu vedle sebe v jedné vyvážené řadě.
- Na velmi úzkém telefonu zůstávají termíny vedle sebe a obsluha se bezpečně přesune pod ně.
- Otázky, odpovědi, náhledy, proklik do karty, vyhledávání, export, stránkování, PHP, SQL a databáze zůstávají funkčně beze změny.

---

# HairSoft Klient V83

V83 navazuje na V82 a dolaďuje interakce i mobilní zobrazení záložky Hodnocení.

- Starý hranatý náhled u jména zákazníka nahrazuje moderní kontaktní karta s telefonem a e-mailem; kliknutí na jméno nadále otevře kartu zákazníka.
- Označení Text používá vlastní moderní náhled s celou otázkou a skutečnou odpovědí místo systémové bubliny prohlížeče.
- Náhledy fungují při najetí myší i při ovládání klávesnicí a nejsou oříznuté vodorovným posuvem tabulky.
- Mobilní otázky již nedědí pevnou šířku 100 px, takže se text neláme po jednotlivých písmenech.
- Každé mobilní hodnocení má přehlednou plnohodnotnou kartu: zákazník a kontakty, oba termíny, obsluha a otázky přes celou šířku.
- Textová odpověď je na mobilu samostatný čitelný blok pod otázkou, číselné hodnocení zůstává kompaktně vpravo.
- PHP výpis, SQL, databáze, odkazy, hodnoty, vyhledávání, export a stránkování zůstávají beze změny.

---

# HairSoft Klient V82

V82 navazuje na V81 a modernizuje záložku Hodnocení v detailu zákazníka.

- Zachovává zvláštní dvouřádkovou hlavičku: základní údaje a skupinu až 10 otázek.
- Na PC se žádný otázkový sloupec neschová; všech 10 je dostupných vodorovným posuvem samotné tabulky.
- Vyhledávání zůstává vlevo a Export vpravo, bez textu v závorce.
- Stránkování po 25 záznamech odpovídá Timeline.
- Číselná hodnocení jsou zobrazena jako kompaktní štítky s hvězdou.
- Na mobilu se každý záznam mění na kartu se zákazníkem, termíny, obsluhou a všemi vyplněnými otázkami.
- Textová odpověď se na mobilu zobrazí přímo místo obecného označení Text.
- Prázdné koncové otázky s pomlčkou mobilní kartu zbytečně neprodlužují.
- SQL dotaz, hodnoty, řazení, odkazy zákazníka a databáze zůstávají beze změny.

---

# HairSoft Klient V81

V81 navazuje na V80 a upravuje pouze vnější panel záložky SMS Chat.

- Celý světle šedý blok SMS Chat má zaoblené rohy stejně jako Galerie.
- Na PC používá radius 18 px, na mobilu 16 px.
- Doplněné je jemné ohraničení celé sekce.
- Vnitřní tabulka, stránkování, mobilní karty, zprávy a jejich pořadí zůstávají beze změny.
- PHP, SQL a databáze se nemění.

---

# HairSoft Klient V80

V80 navazuje na V79 a doplňuje procházení zvětšených fotografií.

- Při více snímcích se ve velkém náhledu zobrazí šipka vlevo a vpravo.
- Fotografie lze přepínat také klávesami se šipkami.
- Přechod je cyklický; z posledního snímku pokračuje na první a opačně.
- Při jediné fotografii se navigační šipky nezobrazují.
- Dotyková tlačítka jsou přizpůsobená mobilnímu zobrazení.
- Celý světle šedý blok Galerie má nově zaoblené rohy a jemné ohraničení.
- Ukládání fotografií, pravé menu, jeho akce, PHP a databáze se nemění.

---

# HairSoft Klient V79

V79 navazuje na V78 a opravuje pouze ovládání zvětšené fotografie v Galerii.

- Otevření fotografie již nespouští původní opakovanou funkci automatického posuvu nahoru.
- Po otevření i zavření velkého náhledu lze stránku normálně posouvat.
- Pozice stránky zůstává pod kontrolou uživatele.
- Vzhled Galerie, pravé menu, mobilní mřížka i všechny akce z V78 zůstávají beze změny.
- Původní PHP, fotografie a databáze se nemění.

---

# HairSoft Klient V78

V78 navazuje na schválenou V77 a modernizuje pouze záložku Galerie v detailu zákazníka.

- Fotografie jsou na PC uspořádané v čisté responzivní mřížce s jednotným ořezem, zaoblením a rozestupy.
- Na mobilu používá Galerie kompaktní mřížku 2 × 2; na velmi úzkém displeji jeden sloupec.
- Kliknutí na snímek otevře moderní celoobrazovkový náhled.
- Pravý klik na PC a tlačítko se třemi tečkami na mobilu otevírají stejné menu fotografie.
- Menu zachovává funkce Nastavit jako profilovku, otočit doprava, otočit doleva a smazat obrázek.
- Původní duplicitní HTML menu jsou při vykreslení nahrazena jedním společným menu navázaným na vybraný snímek.
- Galerie bez fotografií zobrazí kompaktní prázdný stav a nevytváří zbytečně vysokou plochu.
- PHP dotaz, ukládání fotografií, adresy akcí a databáze zůstávají beze změny.

---

# HairSoft Klient V77

V77 navazuje na schválenou V76 a modernizuje pouze záložku SMS Chat v detailu zákazníka.

- Původní SMS Chat se bezpečně převádí na samostatnou DataTables tabulku.
- Desktop má zaoblený panel, hlavičku, font, řádky a barevný systém podle Timeline.
- Datum používá pevnou kompaktní šířku, oba textové sloupce využívají zbývající prostor.
- Stránkování po 25 záznamech odpovídá Timeline: Předešlá, čísla, tři tečky a Další.
- Aktivní číslo stránky má modré pozadí a bílý text.
- Na mobilu se řádky zobrazují jako kompaktní zprávové karty.
- Prázdný sloupec odesílatele nebo zákazníka na mobilu nezabírá místo.
- Při otevření skryté záložky se šířky tabulky automaticky dopočítají.
- SQL dotaz, obsah zpráv, stavové ikony a databáze zůstávají beze změny.

---

V76 je čistá oprava postavená přímo na funkční V73. Nepřebírá změny V74 ani chybnou interpretaci řazení sloupců z V75.

- Zapíná stránkování Timeline, které bylo v původní konfiguraci vypnuté hodnotou `bPaginate: false`.
- Stránkování má stejný vzhled jako seznam zákazníků: Předešlá, čísla, tři tečky a Další.
- Aktivní číslo stránky používá modré pozadí a bílý text.
- Hover, zakázané tlačítko, font, rozměry a zaoblení odpovídají seznamu zákazníků.
- Hlavička, řazení sloupců, ikony, seznam zákazníků, ostatní tabulky a mobilní karty zůstávají beze změny.

---

V73 navazuje na V72 a dolaďuje desktopovou tabulku Timeline podle tabulky zákazníků.

- Hlavička i tabulka mají zaoblené rohy a jemné ohraničení stejného typu jako seznam zákazníků.
- Datum má pevnou šířku 150 px a Obsluha 165 px.
- Detail a Akce mají kompaktní šířku 64 px.
- Poznámka využívá celý zbývající prostor tabulky.
- Pevné šířky přepisují také rozměry, které dopočítává DataTables.
- Mobilní kartové zobrazení Timeline zůstává beze změny.
- Řazení, vyhledávání, exporty, odkazy a smazání zůstávají funkčně beze změny.

---

V72 navazuje na V71 a dokončuje tabulku Timeline na PC i mobilu.

- Tabulka je pevně omezená na šířku svého panelu a nepřetéká mimo monitor.
- Pevné rozložení sloupců ponechává nejvíce místa Poznámce a dlouhý text bezpečně zalamuje.
- Hlavička má skutečnou výšku 52 px, shodný font, velikost a zarovnání jako seznam zákazníků.
- Datum, Poznámka a Obsluha zůstávají řaditelné; Detail a Akce řadicí šipky nemají.
- Pořadí sloupců je Datum, Poznámka, Obsluha, Detail a Akce.
- Detail používá vlastní moderní SVG oka, odstranění vlastní SVG koše.
- Mobilní karta je přemapovaná na nové pořadí bez změny svého rozložení.
- Moderní PDF stále obsahuje Datum, Poznámku a Obsluhu ve správném pořadí.
- Odkazy detailu, potvrzení smazání, databáze a PHP zpracování zůstávají funkčně beze změny.

V71 navazuje na V70 a nahrazuje nativní potvrzení smazání vlastním dialogem HairSoft.

- Moderní potvrzovací okno má jednotný font, ikonu koše a jasnou vizuální hierarchii.
- Primární destruktivní akce je označená tlačítkem Smazat, bezpečná volba tlačítkem Zrušit.
- Dialog funguje u smazání osoby, záznamu Timeline, souboru, fotografie a kompletního skladu.
- Na mobilu se zobrazuje jako přehledný spodní panel s velkými dotykovými tlačítky.
- Lze jej zavřít tlačítkem Zrušit, kliknutím mimo okno nebo klávesou Escape.
- Po otevření se zaměří bezpečné tlačítko Zrušit a fokus zůstává uvnitř dialogu.
- Texty dialogu podporují CZ, SK, EN a DE.
- Původní odkazy, formuláře, adresy, POST hodnoty a potvrzené mazací akce zůstávají beze změny.

V70 navazuje na V69 a dokončuje ovládání exportu na PC i mobilu.

- Pole Hledat je v ovládací liště Timeline vlevo.
- Tlačítko Export je vpravo a obsahuje jen ikonu a text Export.
- Font, svislé zarovnání a vycentrování tlačítka odpovídají modernímu rozhraní.
- Na mobilu zůstávají Hledat i Export plně dostupné v kompaktním rozložení pod sebou.
- Po volbě Copy, CSV, XLS, PDF nebo Tisk se rozbalená nabídka automaticky zavře.
- Automatické zavření platí pro všechny exportní nabídky DataTables v systému.
- Samotné exporty, moderní PDF V69, tabulky, PHP a databáze se nemění.

V69 navazuje na V68 a nahrazuje výchozí PDF z Timeline vlastní moderní šablonou HairSoft.

- PDF obsahuje pouze sloupce Datum, Poznámka a Obsluha.
- Hlavička dokumentu obsahuje značku HairSoft, název Timeline a jméno zákazníka.
- Dlouhé poznámky se čistě zalamují a řádky se nerozdělují zbytečně mezi stránky.
- Tabulka má čitelné šířky sloupců, jemné linky a střídavé pozadí řádků.
- Patička uvádí datum vytvoření a číslo aktuální stránky.
- Soubor dostává bezpečný název s klientem a datem exportu.
- Česká diakritika zůstává zachovaná přes vložený font PDF knihovny.
- Copy, CSV, XLS a Tisk i exporty ostatních tabulek zůstávají beze změny.

V68 navazuje na V67 a globálně modernizuje rozbalovací nabídku Export ve všech tabulkách DataTables.

- Na PC zůstává nabídka ukotvená pod tlačítkem Export.
- Na mobilu se zobrazí jako širší vycentrovaný panel.
- Položky mají jednotný font, zarovnané ikony a čisté barevné stavy.
- Dotykové položky mají výšku nejméně 44 px.
- Pozadí stránky se při otevření jemně ztmaví a rozostří.
- Copy, CSV, XLS, PDF a Tisk zůstávají funkčně beze změny.
- Vzhled je izolovaný v souboru `client-export-menu.css`.

- Na PC je Timeline kompaktní moderní tabulka s čistou hlavičkou.
- Export a vyhledávání tvoří jednotnou ovládací lištu.
- Editace a odstranění mají samostatná barevně odlišená tlačítka.
- Na mobilu se každý původní řádek přeskupí do čitelné karty.
- Mobilní karta obsahuje datum, poznámku, obsluhu, editaci a odstranění.
- Formulář nové i upravované poznámky má moderní responzivní vzhled.
- Původní data, odkazy, potvrzení smazání, vyhledávání, řazení a export se nemění.
- Ostatní záložky a všechny úpravy V66 zůstávají beze změny.

- Prázdná nebo jednořádková Poznámka otevře jen nezbytně vysoký blok.
- Přepisuje se také původní pevná výška obalu textového pole.
- Poznámka dál automaticky roste podle počtu řádků.
- Karta SMS má moderní vzhled i bez mobilního čísla nebo e-mailu.
- V prázdném stavu zobrazuje hodnotu Číslo nezadáno.

- Rámeček textového pole je odsazený od spodní linky hlavičky Poznámky.
- Výška textového pole se automaticky přizpůsobuje skutečnému obsahu.
- Pole se během psaní průběžně zvětšuje.
- Dlouhá poznámka používá po dosažení maximální výšky vnitřní posuvník.
- Desktop a ostatní části mobilního detailu zůstávají beze změny.

- Avatar má pevný rozměr 74 × 74 px a nemůže se deformovat do oválu.
- Fotografie je přesně kruhově oříznutá a vycentrovaná.
- Kruh má čistou bílou linku a jemný stín.
- Ostatní mobilní komponenty a celý desktop zůstávají beze změny.

- Samostatná karta Profilovka je na mobilu skrytá.
- Kruhová fotografie zákazníka je vpravo v horním modrém panelu.
- Navigace Informace až Soubory je hned pod čtyřmi kontaktními kartami.
- Poznámka je pod navigací, ve výchozím stavu sbalená a otevírá se kliknutím.
- Bloky Informací využívají na mobilu stejnou plnou šířku jako navigace, Poznámka a horní karty.
- Desktopové rozložení V62 zůstává beze změny.

- Kruh profilové fotografie je na PC posunutý o 10 px doprava pro optické vycentrování.
- Informace jsou rozdělené do čtyř bloků stejně jako u Nového zákazníka.
- Bloky používají stejné názvy, popisy, ikony, fonty, rozměry polí a mezery.
- Dvojice polí se na užším obsahu a mobilu skládají pod sebe.
- Původní tlačítko Uložit změny má stejný vzhled jako akce formuláře Nový zákazník.
- Systémové údaje zůstávají zachované v samostatném čtvrtém bloku.
- Názvy polí, jejich hodnoty a ukládání se nemění.

- Na PC je vlevo samostatná profilová karta s kruhovou fotografií a jménem zákazníka.
- Poznámka tvoří vlastní kompaktní kartu se zachovaným původním textovým polem.
- Informace, Timeline, SMS Chat, Galerie, Hodnocení a Soubory jsou v novém svislém ikonovém menu.
- Obsah aktivní sekce zůstává vpravo a jeho původní funkce se nemění.
- Na mobilu je fotografie součástí horního panelu, menu tvoří dotykovou mřížku 3 × 2 a Poznámka je sbalená pod ním.
- Nová vrstva je oddělená v souborech `client-detail-profile.css` a `client-detail-profile.js`.
- Horní čtyři karty, Možnosti, patička a seznam zákazníků se nemění.

- Sekce Možnosti je předposledním blokem stránky.
- Patička Strana načtena za zůstává vždy úplně poslední.
- Pořadí platí na PC i mobilu.
- Přesouvá se původní blok, nevytváří se jeho kopie.
- Tlačítka Refresh stránky a Smazat osobu včetně potvrzení zůstávají funkčně beze změny.
- Vzhled a šířka sekce Možnosti se nemění.
- Ostatní části detailu zákazníka se nemění.

- Volat, SMS a Bonusové body používají vzhled a přesný font Dashboardu.
- Karta fotografie má nahoře text Přidat fotografii a původní velkou modrou ikonu fotoaparátu.
- Na PC používá řada karet stejnou plnou šířku hlavního obsahu jako Dashboard.
- Boční odsazení a mezera 18 px mezi kartami odpovídají Dashboardu.
- Chybné omezení maximální šířky 1580 px bylo odstraněno.
- Na mobilu jsou čtyři karty v kompaktní mřížce 2 × 2 bez obřích původních ikon.
- Fotografická karta dál používá původní mobilní fotoaparát.
- Telefonní čísla jsou pouze při zobrazení rozdělena do trojic; původní odkazy se nemění.
- Styly a skript detailu jsou omezené pouze na horní karty.
- Desktopový profil a vzhled obsahu Timeline, SMS Chat, Galerie, Hodnocení a Soubory zůstávají beze změny.
- Seznam zákazníků na PC i mobilu zůstává beze změny.
- Mobilní rozložení horních karet z V57 zůstává beze změny.
- V63 mění pod horními kartami pouze mobilní pořadí profilu, navigace, Poznámky a šířku Informací.
- Původní PHP, názvy polí, odesílání, databáze a oprávnění se nemění.

Changelog: `https://klient.hairsoft.cz/hs-client-ui/changelog/`
