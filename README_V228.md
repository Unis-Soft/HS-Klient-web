# HS Klient V228 – plynulá navigace hlavního menu

V228 opravuje regresi při přechodu mezi hlavními sekcemi HS Klientu.

## Změna

- Levé menu a horní hlavička zůstávají při kliknutí na interní položku viditelné.
- Loader se zobrazuje pouze v hlavní obsahové části.
- Původní obsah se během přechodu pouze jemně ztlumí, místo celostránkové bílé plochy.
- Globální boot režim `hs-ui-preparing`, který při každém načtení schovával hlavní obsah, byl odstraněn.
- Vouchery si ponechávají vlastní bezpečný `hs-voucher-boot`.
- Odkazy s Ctrl/Cmd/Shift, nové okno, download a přepnutí obsluhy se neinterceptují.

## Beze změny

- PHP routování a oprávnění.
- DataTables, Tržby, Vouchery, Hodnocení, Sklad, SMS a ostatní moduly.
- Programy V227 a HSBridge V010.
- URL jednotlivých sekcí a historie prohlížeče.

V228 mění pouze chování navigačního shellu a cache-bust společného CSS/JS.
