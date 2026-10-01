# V194 – Multi-firma: paměť poslední sekce

Navazuje na V193.

## Oprava
Každá zapamatovaná firma si nyní drží samostatně:
- poslední vybranou pobočku (`last_branch_id`),
- poslední pracovní sekci (`last_page`).

Příklad:
- firma A → Hodnocení,
- firma B → Sklad,
- po A → B → A se firma A vrátí zpět do Hodnocení,
- po B se vrátí do Skladu.

## Bezpečnost
Neukládají se detailní parametry URL ani GUID zákazníků. Detailní stránky se mapují na bezpečnou nadřazenou sekci (např. KartaOsoby → Zakaznici).

## DB migrace
`last_page VARCHAR(64) NOT NULL DEFAULT ''` se při prvním běhu V194 doplní automaticky do `k_klient_remember_tokens`.
SQL je přiložen i samostatně v `sql/SQL_V194_MULTI_FIRMA_LAST_PAGE.sql`.

## Beze změny
- vzhled a font přepínače V193,
- původní červené Odhlásit z V190/V193,
- Google recenze V190,
- biometrie se neřeší.

## Další zařízení
V194 stále bezpečně používá tokeny konkrétního prohlížeče. Synchronizace seznamu propojených firem mezi zařízeními vyžaduje samostatnou serverovou vazbu účtů a není v této opravě aktivována.
