# HS Klient V158 - Uživatelé / pravidelný kruhový avatar

V158 je úzká vizuální oprava avataru v detailu obsluhy.

- Vnější obal avataru má pevně shodnou šířku a výšku, `aspect-ratio: 1 / 1` a kruhové ohraničení.
- Samotná fotografie vyplňuje čtvercový vnitřek, používá `background-size: cover` a je oříznuta přesně do kruhu.
- Přidány stejné minimální/maximální rozměry, aby avatar nemohl zdeformovat flex/grid ani globální legacy CSS.
- Mobilní avatar používá stejný princip v menší velikosti.
- Cache verze `users.css` zvýšena na 158.
- Žádná PHP databázová, formulářová ani oprávnění logika se nemění.
