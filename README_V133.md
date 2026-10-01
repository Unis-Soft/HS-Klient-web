# HairSoft Klient V133

Navazuje na V132.

- Karta klienta ukládá soubory opět do původního adresáře str/strana/Soubory.
- Název souboru se zapisuje pomocí parametrizovaného dotazu. Apostrofy nepoškodí dotaz ani odkaz ke stažení. Při chybě databázového zápisu se nevypisuje úspěch a odstraní se právě uložená neúplná příloha.
- Kalendář na dotykových zařízeních je vycentrovaný v obou orientacích. Pole otevírá kalendář bez softwarové klávesnice. Na PC zůstává ruční zadávání; formát data, překlady a reakce na výběr zůstávají zachovány.
- Vouchery používají přímo spinner na stejném obalu sekce jako Tržby. Samostatná vrstva a dodatečné přepočítávání polohy byly odstraněny. Ochrana před probliknutím původního vzhledu zůstává.

Nahrajte složky hs-client-ui a str. Původní str/strana i její soubory ponechte. Obnovte stránku Ctrl+F5.
Tato verze nepřesouvá již nahrané soubory ze špatného adresáře. Pokud tam na serveru existují, je třeba je samostatně dohledat a přesunout do původního úložiště.

Ověření: syntaxe PHP/JS; lokální kontrola parametrizace názvu O'Connor.pdf a úklidu při selhání přípravy/provedení dotazu; Chrome – stabilní spinner při šířkách 390/844/1366 a kalendář při 320/390/844 včetně změny měsíce, data a zachování callbacku. Softwarovou klávesnici je ještě vhodné ověřit na skutečném telefonu; automatické testy ověřují readonly nastavení dotykového pole. Živý server, databáze ani HairSoft v salonu nebyly měněny nebo testovány.
