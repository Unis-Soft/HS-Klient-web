# HairSoft Klient V144

V144 navazuje na V143 a dolaďuje hlavní stránku **SMS a hovory – Přehled SMS a hovorů** podle existujících referencí Ročních tržeb a Voucherů.

## Změny
- **Dle typu SMS** je na desktopu přes celou šířku hlavního obsahu. Graf tak není zbytečně stlačený do poloviny stránky.
- **Zprávy dle typu** používají přesnou typografii legendy z Ročních tržeb: 11,5 px, shodné řádkování, odsazení, barvy, zaoblení a velikost barevných bodů.
- Horizontální posuv už neobaluje celý DataTables wrapper tabulky dobití kreditu. Tím se posuvník nemůže dostat pod stránkování ani do něj zasahovat. Grafové posuvy mají navíc bezpečný spodní odstup.
- **PDF export Přehledu dobití kreditu** má novou moderní A4 šablonu podle hotových exportů Tržeb/Skladu/Voucherů: HairSoft hlavičku, sekční název, pobočku, tarif, datum vytvoření, čistou tabulku se střídáním řádků a číslovanou patičku.
- Databázové dotazy, výpočty SMS, kredit, tarif a formuláře zůstávají beze změny.

## Technicky
- přidán `hs-client-ui/js/sms-overview-pdf.js`,
- `sms-overview.css` a `sms-overview.js` zvýšeny na cache verzi V144,
- PDF callback tabulky `#example` v moderním `index.php` volá SMS customizer pouze na stránce SMS.

Nahrajte `hs-client-ui` a `str`, původní soubory ponechte. Potom Ctrl+F5.
