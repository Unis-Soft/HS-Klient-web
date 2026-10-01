# HairSoft Klient V109

Navazuje na V108. Nasazení: nahrajte složky `hs-client-ui` a `str` do kořene webu, přepište soubory a obnovte stránku pomocí Ctrl+F5.

## Opravy

- Levé menu: první načtení V109 na desktopu začíná rozbaleným menu. Dřívější uložený stav se nepřebírá; další ruční změny se ukládají pod `hairsoft.sidebar.collapsed.v109`. Ovládání menu nyní zajišťuje přímo moderní skript a zachytí kliknutí před původním ovladačem, aby nedošlo ke dvojímu přepnutí. Mobilní menu nadále používá samostatné otevření/zavření.
- Kruhové grafy: zachovány interní callbacky Chart.js 2.3.0; upravuje se pouze text tooltipu. Tooltip obsahuje název, částku či počet a procentní podíl.
- Popisky: stejné CSS legend jako u denních tržeb, včetně 11,5px fontu. Shodné velikosti tooltipů 13/12 px a výpočet velikosti částky uprostřed podle dostupného prostoru. Open Sans s bezpatkovým náhradním fontem.
- Správa dat: PHP vykresluje formuláře přímo pod grafy před synchronizační informací. Původní horní panel byl odstraněn ze zdroje; JavaScript už formuláře nepřesouvá. Zachovány formulářové cíle, POST hodnoty, potvrzení smazání a upozornění na již zadaný požadavek.

## Změněné soubory oproti V108

- `hs-client-ui/php/index.php`
- `hs-client-ui/php/strana/MesicniTrzby.php`
- `hs-client-ui/js/menu.js`
- `hs-client-ui/js/monthly-revenue.js`
- `hs-client-ui/css/monthly-revenue.css`
- Informace o verzi a nasazení.

## Ověření

- Syntaxe obou PHP souborů v PHP 8.3.33 a obou upravených JavaScriptů v Node.js.
- Chrome se skutečným Chart.js 2.3.0 z webu: šířky 1920, 1366, 768 a 375 px, kruhové proporce, nepřekrývání legend, tooltipy sloupců myší/dotykem a obsah tooltipů všech pěti testovaných typů prstenců (služby, prodej, dny týdne, účtenky, platby). Bez zachycených chyb JavaScriptu.
- Desktopové i mobilní přepínání menu; obnovení po sbalení i rozbalení; ignorování starého uloženého stavu.
- Porovnání vypočtené velikosti legend s CSS denních tržeb: obě 11,5 px.
- Běhové vykreslení PHP bloku správy dat s dostupným refreshem i s čekajícím požadavkem. Kontrola formulářů a umístění i bez spuštěného JavaScriptu.
- Opakované obnovení období, změna session, neplatné vstupy, nedostupný rok a leden/prosinec: regresní testy V108 prošly.

Testy používají lokální stránky a ukázková data, dostupnost databáze je simulována. Přihlášená produkční stránka proti skutečné databázi nebyla testována a balíček nebyl nasazen. Starší README a handoff soubory jsou historické záznamy.
