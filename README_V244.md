# V244 – okamžitý odpis Programu a rychlejší focení

- Po úspěšném INSERT do HairSoft se na serveru zrcadlí pouze nový `program_visits` řádek konkrétního zákazníka. Neprovádí se okamžitý full scan kartotéky.
- Detail Programu po zařazení čerpání sleduje stav příkazu a po `done` znovu načte právě otevřený Program.
- Kontrolní plný PROGRAMS snapshot HSBridge je zkrácen z 15 na 10 minut.
- Foto modal má pouze jednu akci `Vyfotit`; duplicitní `Vybrat fotografie` je odstraněno.
- Náhled po vyfocení se vytvoří okamžitě přes lokální Object URL. Resize/JPEG komprese do max. 2560 px se provede až po stisku `Odeslat do HairSoft`, kdy už běží odesílací overlay.
- Fotografie se po samotném vyfocení do HairSoft neposílají; do fronty jdou až po potvrzení `Odeslat do HairSoft`.
- HSBridge verze: V014.