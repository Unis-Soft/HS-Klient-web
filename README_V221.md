# V221 - Prepinac firem: pocitadlo vyuziti 3/30

V221 navazuje primo na V220.

## Zmena
- Do prave horni casti hlavicky panelu Prepnout firmu je doplneno nenapadne pocitadlo ve tvaru `aktualni pocet / 30`, napr. `3/30`.
- Pocitadlo se generuje z realneho poctu bezpecne ulozenych firem, takze se automaticky meni po pridani nebo odebrani firmy.
- Limit zustava 30 firem.
- Scroll seznamu zustava od vyssiho poctu firem; na beznem desktopu je videt priblizne 10 polozek.
- Cache klic `company-switch.css` je zvysen na 221.

## Beze zmeny
- bezpecnost remember tokenu a 365denni platnost,
- prihlaseni, prepinani a odebirani firem,
- posledni pobocka a posledni sekce pro kazdou firmu,
- databaze a SQL,
- ostatni funkce HS Klient.
