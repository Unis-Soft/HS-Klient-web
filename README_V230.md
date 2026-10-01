# HS Klient V230 – streaming shell + Bonfero multi-company security

## 1. Navigace

V228/V229 odstranily část probliknutí, ale běžná PHP navigace stále čekala na
celou odpověď serveru. V230 vrací původní princip postupného vykreslení:

- server pošle horní hlavičku a levé menu okamžitě,
- před načítáním konkrétního modulu provede flush výstupu,
- během SQL/renderování modulu zůstává shell viditelný,
- pouze hlavní pracovní plocha ukazuje loader,
- legacy `fadeInUp` zůstává vypnutý, takže nevzniká druhé probliknutí,
- PHP routování a logika modulů se nemění.

Pro nginx/FastCGI se posílá `X-Accel-Buffering: no`; PHP output buffering a
zlib komprese se pro tento dokument vypínají, aby shell mohl být skutečně
doručen dříve než obsah modulu.

## 2. Kritická oprava Bonfero / více firem

Původní Rezervace načítaly stále stejné `https://app.bonfero.com/`. Bonfero
má vlastní browser session/cookies, které nejsou svázané s aktivní HairSoft
firmou. Po přepnutí firmy proto mohl iframe dál zobrazovat Bonfero účet jiné
firmy.

V230 zavádí bezpečný fail-closed režim:

- pokud má uživatel v HairSoft Klientu uloženo více než jednu firmu,
  Bonfero iframe se vůbec nevytvoří,
- v multi-firma režimu se nezobrazuje ani tlačítko Otevřít zvlášť,
- místo toho se zobrazí bezpečnostní informace s názvem aktuální HairSoft firmy,
- pro jednu uloženou firmu zůstává dosavadní vložené Bonfero beze změny.

Trvalé řešení pro multi-firma Bonfero musí být company-bound SSO/token, který
Bonfero serverově naváže na konkrétní HairSoft firmu. Samotný iframe nemůže
bezpečně izolovat dvě Bonfero sessions stejného originu.

## Beze změny

Programy V227, HSBridge V010, databázová logika, Tržby, Vouchery, Hodnocení,
Sklad, SMS a ostatní moduly.
