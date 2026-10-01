# V212 – Správa dat: jednotný vnitřní prostor

V212 navazuje přímo na V211 a opravuje rozbalený vzhled společné sekce Správa dat.

## Příčina V211
Měsíční tržby používají skutečný `.panel-body`, který má správný vnitřní prostor. U `details` variant (Sklad, Sklad XML, Seznam zákazníků a Karta zákazníka) V211 označila jako společný body přímo kontejner akcí. Stavové hlášky a tlačítka proto byly příliš blízko hranám a rozbalená sekce byla opticky nízká/deformovaná.

## Oprava V212
- `details` Správa dat dostává samostatný `.hs-data-management-body` wrapper.
- Desktop padding body je 17 px stejně jako ve vzoru Měsíčních tržeb.
- Původní action kontejnery jsou uvnitř wrapperu a nemají vlastní dvojitý padding.
- Tlačítka mají sjednocenou minimální výšku 42 px a padding 9 × 14 px.
- Stavové alerty (např. požadavek na refresh skladu) mají vlastní celý řádek a korektní odstup od hran i od dalších akcí.
- Dlouhé texty u ruční synchronizace kopie zákazníka mají více prostoru a nemačkají okolní tlačítka.
- Mobilní layout zůstává sloupcový a používá 14 px body padding.

## Beze změny
- všechny Správy dat zůstávají po načtení výchozí sbalené,
- Tahoma a velikosti nadpisů/podnadpisů z V211,
- V210 ruční Timeline synchronizace,
- kopie zákazníka a ruční HairSoft ID,
- Bonfero `https://app.bonfero.com/`,
- databáze a synchronizační protokol.
