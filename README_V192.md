# V192 – oprava levého Odhlásit + typografie Přepnout firmu

Navazuje přímo na V191. Funkce multi-firma a dlouhodobé přihlášení se nemění.

## Oprava levého menu
Ve V191 byl původní odkaz Odhlásit nahrazen formulářovým buttonem, aby uměl bezpečně odhlásit pouze aktivní firmu. Původní červené CSS však bylo navázané jen na element `<a>`, takže vzhled tlačítka zanikl.

V192 zachovává novou bezpečnou funkci, ale button dostal stejné rozměry, gradient, stín, hover a sbalený ikonový stav jako původní V190.

## Přepínač firem – font
Panel, tlačítka a vstupy používají explicitně `Open Sans, Helvetica, Arial, sans-serif`; běžný text používá standardní HairSoft řezy 400/600/700.

## Beze změny
- přidání firmy,
- přepínání mezi firmami,
- dlouhodobé tokeny 365 dní,
- zapamatování pobočky,
- odhlášení jedné/všech firem,
- databáze,
- Google recenze V190,
- biometrii stále neřešíme.
