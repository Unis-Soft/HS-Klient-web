# HS Klient V236 – PDF zákazníků, definitivní Zóny těla a doplnění i18n

V236 navazuje přímo na V235.

## Seznam zákazníků – PDF

- původní DataTables PDF byl nahrazen vlastní moderní šablonou HairSoft;
- PDF používá A4 naležato, hlavičku HairSoft, pobočku, datum vytvoření, číslování stran, čitelné šířky sloupců a střídavé řádky;
- z PDF jsou odstraněny technické sloupce Detail a Akce;
- Jméno + příjmení se skládá do jednoho sloupce Zákazník;
- Program je v PDF pouze tehdy, když je Programový sloupec aktivní, a jeho hlavička respektuje zvolený program i jazyk;
- Copy / Excel / CSV / Print zůstávají funkčně beze změny.

## Zóny těla

- oprava už není založená na několika ručně vyjmenovaných Unicode variantách;
- server i klient skládají známý program podle normalizovaného klíče, takže `ZÓNY TĚLA`, `ZÓNY TÌLA`, `ZÓNY TÍLA`, varianty bez diakritiky i title-case skončí jako interní `Zóny těla`;
- i18n používá stejný princip, takže výstup je CZ `Zóny těla`, SK `Zóny tela`, EN `Body zones`, DE `Körperzonen`;
- databázová hodnota se nemění.

## Překlady měsíčních tržeb

Součástí balíku jsou i dříve připravené překlady:
- TOP den za služby;
- TOP den za prodej;
- Počet účtenek za měsíc.

HSBridge zůstává V010. Databáze beze změny.