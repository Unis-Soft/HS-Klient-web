# V199 – Sklad: Správa dat a KPI

Navazuje přímo na V198.

## Změny
- Ruční **Refresh skladových dat z PC** už není v horním samostatném panelu.
- Refresh, případné **Opakovat akci**, stav refreshu a mazání jsou ve spodní **Správě dat**.
- **Správa dat** je výchozí sbalená a používá stejný `details/summary` princip jako Správa dat zákazníků.
- Refresh i mazání mají stejnou typografii Tahoma, velikost 12 px a weight 400; funkční/varovné barvy zůstávají odlišené.
- Opraveno je seskupení horních KPI karet Skladu. Všechny karty se berou jako jeden vnější grid, takže už nevykukují mimo pravý okraj.
- Legacy Sklad se 4 KPI kartami má na desktopu 4 stejné sloupce, na menší šířce 2 a na mobilu 1.
- Sklad XML využívá stejnou sbalenou Správu dat pro mazání a správný společný KPI grid.

## Beze změny
- SQL a databázová struktura,
- obsah a výpočty skladových dat,
- POST akce refreshu a mazání,
- synchronizační proces HairSoft,
- multi-firma V198,
- TEST kopie zákazníka V195,
- dotaznik.net.
