(function () {
  "use strict";

  var STORAGE_KEY = "hairsoft-client-language";
  var SUPPORTED = ["cs", "sk", "en", "de"];
  var FLAG_FILES = { cs: "cz", sk: "sk", en: "en", de: "de" };
  var DEFAULT_LANGUAGE = "cs";
  var applying = false;
  var textSources = new WeakMap();
  var attributeSources = new WeakMap();
  var scriptUrl = document.currentScript && document.currentScript.src ? document.currentScript.src : window.location.href;
  var flagsBase = new URL("../img/flags/", scriptUrl).href;
  var originalDocumentTitle = document.title;

  var languageNames = {
    cs: "Čeština",
    sk: "Slovenčina",
    en: "English",
    de: "Deutsch"
  };

  var changeLanguageLabels = {
    cs: "Změnit jazyk",
    sk: "Zmeniť jazyk",
    en: "Change language",
    de: "Sprache ändern"
  };

  var rows = [
    ["HairSoft Klient", "HairSoft Klient", "HairSoft Client", "HairSoft Client"],
    ["Váš salon pod kontrolou.", "Váš salón pod kontrolou.", "Your salon under control.", "Ihr Salon unter Kontrolle."],
    ["Přehledně, bezpečně a odkudkoliv.", "Prehľadne, bezpečne a odkiaľkoľvek.", "Clear, secure and accessible anywhere.", "Übersichtlich, sicher und überall erreichbar."],
    ["Vaše data jsou v bezpečí", "Vaše údaje sú v bezpečí", "Your data is safe", "Ihre Daten sind sicher"],
    ["Vítejte zpět", "Vitajte späť", "Welcome back", "Willkommen zurück"],
    ["Přihlaste se do účtu administrátora.", "Prihláste sa do účtu administrátora.", "Sign in to your administrator account.", "Melden Sie sich bei Ihrem Administratorkonto an."],
    ["Login / Email", "Login / Email", "Login / Email", "Login / E-Mail"],
    ["Zadejte login nebo email", "Zadajte login alebo email", "Enter your login or email", "Login oder E-Mail eingeben"],
    ["Statistika hodnocení", "Štatistika hodnotení", "Ratings overview", "Bewertungsübersicht"],
    ["Přehled Vašich statistik a hodnocení od zákazníků", "Prehľad vašich štatistík a hodnotení od zákazníkov", "Your statistics and customer feedback", "Ihre Statistiken und Kundenbewertungen"],
    ["Přehled hodnocení obsluh", "Prehľad hodnotení obsluhy", "Staff ratings", "Mitarbeiterbewertungen"],
    ["Bez hodnocení", "Bez hodnotení", "No ratings", "Keine Bewertungen"],
    ["Hodnotilo:", "Hodnotilo:", "Ratings:", "Bewertungen:"],
    ["K ideálu chybí", "Do ideálu chýba", "Remaining to 5", "Abstand zu 5"],
    ["K ideálu Vám chybí", "Do ideálu vám chýba", "Remaining to 5", "Abstand zu 5"],
    ["Zobrazit archivované", "Zobraziť archivované", "Show archived", "Archivierte anzeigen"],
    ["Skrýt archivované", "Skryť archivované", "Hide archived", "Archivierte ausblenden"],
    ["Nastavení hodnocení spokojenosti", "Nastavenie hodnotenia spokojnosti", "Feedback settings", "Bewertungseinstellungen"],
    ["Výběr střediska", "Výber strediska", "Select department", "Bereich auswählen"],
    ["Odeslání dotazníku", "Odoslanie dotazníka", "Questionnaire delivery", "Fragebogenversand"],
    ["Způsob odeslání dotazníku", "Spôsob odoslania dotazníka", "Delivery method", "Versandart"],
    ["VYPNUTO", "VYPNUTÉ", "OFF", "AUS"],
    ["Kdy odesílat zákazníkovi hodnocení", "Kedy odosielať zákazníkovi hodnotenie", "When to send the questionnaire", "Versandzeitpunkt"],
    ["Zítra", "Zajtra", "Tomorrow", "Morgen"],
    ["Pozítří", "Pozajtra", "In two days", "Übermorgen"],
    ["Za 3 dny", "O 3 dni", "In three days", "In drei Tagen"],
    ["Systém zapnut", "Systém zapnutý", "System enabled", "System aktiviert"],
    ["Kolikrát do roka odeslat hodnocení", "Koľkokrát ročne odoslať hodnotenie", "Requests per year", "Anfragen pro Jahr"],
    ["Neomezeně", "Neobmedzene", "Unlimited", "Unbegrenzt"],
    ["Od jakého data počítat omezení hodnocení", "Od akého dátumu počítať obmedzenie hodnotenia", "Start date for the request limit", "Startdatum für die Begrenzung"],
    ["Uložit způsob zasílání", "Uložiť spôsob zasielania", "Save delivery settings", "Versandeinstellungen speichern"],
    ["Změnit omezení hodnocení", "Zmeniť obmedzenie hodnotenia", "Change request limit", "Anfragelimit ändern"],
    ["Seznam otázek", "Zoznam otázok", "Questions", "Fragen"],
    ["Nová otázka / Editace otázky", "Nová otázka / Úprava otázky", "Add or edit a question", "Frage hinzufügen oder bearbeiten"],
    ["Otázka hodnocení", "Otázka hodnotenia", "Question", "Frage"],
    ["Typ hodnocení", "Typ hodnotenia", "Answer type", "Antworttyp"],
    ["Hvězdičky", "Hviezdičky", "Stars", "Sterne"],
    ["Typ otázky", "Typ otázky", "Category", "Kategorie"],
    ["Pořadí otázky v dotazníku", "Poradie otázky v dotazníku", "Question order", "Reihenfolge der Frage"],
    ["Přidat novou otázku", "Pridať novú otázku", "Add question", "Frage hinzufügen"],
    ["Text emailu", "Text e-mailu", "Email text", "E-Mail-Text"],
    ["Uložit text emailu", "Uložiť text e-mailu", "Save email text", "E-Mail-Text speichern"],
    ["Výchozí šablona", "Predvolená šablóna", "Default template", "Standardvorlage"],
    ["Text SMS", "Text SMS", "SMS text", "SMS-Text"],
    ["Uložit text SMS", "Uložiť text SMS", "Save SMS text", "SMS-Text speichern"],
    ["Cíl SMS hodnocení", "Cieľ SMS hodnotenia", "SMS feedback destination", "Ziel der Bewertungs-SMS"],
    ["Interní hodnocení", "Interné hodnotenie", "Internal feedback", "Interne Bewertung"],
    ["Google recenze", "Google recenzie", "Google reviews", "Google-Bewertungen"],
    ["Google recenze budou použity pouze pro SMS. Email zůstává napojený na interní dotazník.", "Google recenzie sa použijú iba pre SMS. E-mail zostáva napojený na interný dotazník.", "Google reviews will be used for SMS only. Email stays connected to the internal questionnaire.", "Google-Bewertungen werden nur für SMS verwendet. E-Mails bleiben mit dem internen Fragebogen verbunden."],
    ["Dostupné proměnné", "Dostupné premenné", "Available variables", "Verfügbare Variablen"],
    ["Odkaz na Google recenze", "Odkaz na Google recenzie", "Google review link", "Link zu Google-Bewertungen"],
    ["Sem bude ve fázi 2 uložen přímý odkaz pro napsání Google recenze.", "Sem sa vo fáze 2 uloží priamy odkaz na napísanie Google recenzie.", "In phase 2, the direct Google review link will be stored here.", "In Phase 2 wird hier der direkte Link zum Schreiben einer Google-Bewertung gespeichert."],
    ["Text SMS pro Google recenze", "Text SMS pre Google recenzie", "SMS text for Google reviews", "SMS-Text für Google-Bewertungen"],
    ["Proměnná %ODKAZ_SPOKOJENOST% zůstává stejná. Ve fázi 2 se nahradí uloženým Google odkazem.", "Premenná %ODKAZ_SPOKOJENOST% zostáva rovnaká. Vo fáze 2 sa nahradí uloženým Google odkazom.", "The %ODKAZ_SPOKOJENOST% variable stays the same. In phase 2 it will be replaced by the saved Google link.", "Die Variable %ODKAZ_SPOKOJENOST% bleibt unverändert. In Phase 2 wird sie durch den gespeicherten Google-Link ersetzt."],
    ["Uložit Google nastavení", "Uložiť Google nastavenie", "Save Google settings", "Google-Einstellungen speichern"],
    ["Fáze 1 – zatím se neukládá", "Fáza 1 – zatiaľ sa neukladá", "Phase 1 – not saved yet", "Phase 1 – wird noch nicht gespeichert"],
    ["Pouze SMS", "Iba SMS", "SMS only", "Nur SMS"],
    ["Vložte přímý odkaz, na kterém zákazník může napsat Google recenzi.", "Vložte priamy odkaz, na ktorom môže zákazník napísať Google recenziu.", "Enter the direct link where the customer can write a Google review.", "Geben Sie den direkten Link ein, über den der Kunde eine Google-Bewertung schreiben kann."],
    ["Proměnná %ODKAZ_SPOKOJENOST% se při odeslání nahradí uloženým Google odkazem.", "Premenná %ODKAZ_SPOKOJENOST% sa pri odoslaní nahradí uloženým Google odkazom.", "When sending, %ODKAZ_SPOKOJENOST% is replaced with the saved Google link.", "Beim Senden wird %ODKAZ_SPOKOJENOST% durch den gespeicherten Google-Link ersetzt."],
    ["Ukládání Google nastavení zapojíme ve fázi 2.", "Ukladanie Google nastavenia zapojíme vo fáze 2.", "Saving Google settings will be connected in phase 2.", "Das Speichern der Google-Einstellungen wird in Phase 2 angebunden."],
    ["Test dotazníku", "Test dotazníka", "Test questionnaire", "Fragebogen testen"],
    ["Zaslat testovací email", "Odoslať testovací e-mail", "Send test email", "Test-E-Mail senden"],
    ["Zaslat testovací SMS", "Odoslať testovaciu SMS", "Send test SMS", "Test-SMS senden"],
    ["Atmosféra", "Atmosféra", "Atmosphere", "Atmosphäre"],
    ["Nespecifikováno", "Nešpecifikované", "Unspecified", "Nicht angegeben"],
    ["Administrace hodnocení spokojenosti", "Správa hodnotenia spokojnosti", "Manage customer feedback", "Kundenbewertungen verwalten"],
    ["Povolení dotazníku spokojenosti", "Povolenie dotazníka spokojnosti", "Enable feedback questionnaire", "Feedback-Fragebogen aktivieren"],
    ["Hodnocení celkem", "Hodnotení celkom", "Total requests", "Anfragen insgesamt"],
    ["Odpovědělo", "Odpovedalo", "Responded", "Beantwortet"],
    ["Procentuálně", "Percentuálne", "Response rate", "Rücklaufquote"],
    ["Fronta neodeslaných", "Front neodoslaných", "Pending requests", "Ausstehende Anfragen"],
    ["Odhlášeno z hodnocení", "Odhlásené z hodnotenia", "Opted out", "Abgemeldet"],
    ["Reakce:", "Reakcie:", "Responses:", "Antworten:"],
    ["Služba", "Služba", "Service", "Dienstleistung"],
    ["Prostředí", "Prostredie", "Environment", "Umgebung"],
    ["Cena", "Cena", "Price", "Preis"],
    ["Druh", "Druh", "Kind", "Art"],
    ["Zobrazit aktivní", "Zobraziť aktívne", "Show active", "Aktive anzeigen"],
    ["Reakce", "Reakcie", "Responses", "Antworten"],
    ["Otázka", "Otázka", "Question", "Frage"],
    ["Nehodnoceno", "Nehodnotené", "Not rated", "Nicht bewertet"],
    ["Uložit", "Uložiť", "Save", "Speichern"],
    ["Heslo", "Heslo", "Password", "Passwort"],
    ["Zapomněli jste heslo?", "Zabudli ste heslo?", "Forgot your password?", "Passwort vergessen?"],
    ["Zadejte heslo", "Zadajte heslo", "Enter your password", "Passwort eingeben"],
    ["Přihlásit", "Prihlásiť", "Sign in", "Anmelden"],
    ["Další možnosti", "Ďalšie možnosti", "Other options", "Weitere Möglichkeiten"],
    ["Vyzkoušet DEMO účet", "Vyskúšať DEMO účet", "Try the DEMO account", "DEMO-Konto ausprobieren"],
    ["Přihlásit se jako obsluha", "Prihlásiť sa ako obsluha", "Sign in as staff", "Als Mitarbeiter anmelden"],
    ["Obnovení přístupu", "Obnovenie prístupu", "Restore access", "Zugang wiederherstellen"],
    ["Nové heslo vám bezpečně zašleme emailem.", "Nové heslo vám bezpečne zašleme emailom.", "We will securely send a new password to your email.", "Wir senden Ihnen sicher ein neues Passwort per E-Mail."],
    ["Bezpečné obnovení účtu", "Bezpečné obnovenie účtu", "Secure account recovery", "Sichere Kontowiederherstellung"],
    ["Zapomenuté heslo", "Zabudnuté heslo", "Forgotten password", "Passwort vergessen"],
    ["Zadejte email registrovaný ve vašem účtu.", "Zadajte email registrovaný vo vašom účte.", "Enter the email registered with your account.", "Geben Sie die in Ihrem Konto registrierte E-Mail-Adresse ein."],
    ["Email", "Email", "Email", "E-Mail"],
    ["Zadejte svůj email", "Zadajte svoj email", "Enter your email", "E-Mail-Adresse eingeben"],
    ["Zaslat nové heslo emailem", "Zaslať nové heslo emailom", "Send a new password by email", "Neues Passwort per E-Mail senden"],
    ["Zpět k přihlášení", "Späť k prihláseniu", "Back to sign in", "Zurück zur Anmeldung"],
    ["Přístup pro váš tým.", "Prístup pre váš tím.", "Access for your team.", "Zugang für Ihr Team."],
    ["Rychle, přehledně a bezpečně.", "Rýchlo, prehľadne a bezpečne.", "Fast, clear and secure.", "Schnell, übersichtlich und sicher."],
    ["Pracovní přístup obsluhy", "Pracovný prístup obsluhy", "Staff access", "Mitarbeiterzugang"],
    ["Přihlášení obsluhy", "Prihlásenie obsluhy", "Staff sign in", "Mitarbeiter-Anmeldung"],
    ["Zadejte údaje provozovny a svůj osobní přístup.", "Zadajte údaje prevádzky a svoj osobný prístup.", "Enter the company details and your personal access.", "Geben Sie die Betriebsdaten und Ihren persönlichen Zugang ein."],
    ["Login / Email provozovny", "Login / Email prevádzky", "Company login / Email", "Betriebslogin / E-Mail"],
    ["Zadejte login nebo email provozovny", "Zadajte login alebo email prevádzky", "Enter the company login or email", "Betriebslogin oder E-Mail eingeben"],
    ["Login / Jméno obsluhy", "Login / Meno obsluhy", "Staff login / Name", "Mitarbeiter-Login / Name"],
    ["Zadejte login nebo jméno obsluhy", "Zadajte login alebo meno obsluhy", "Enter the staff login or name", "Mitarbeiter-Login oder Namen eingeben"],
    ["Jiný typ přihlášení", "Iný typ prihlásenia", "Other sign-in option", "Andere Anmeldeoption"],
    ["Přihlásit se jako administrátor", "Prihlásiť sa ako administrátor", "Sign in as administrator", "Als Administrator anmelden"],
    ["Účet je blokován", "Účet je zablokovaný", "Account is blocked", "Konto ist gesperrt"],
    ["Informace", "Informácie", "Information", "Information"],
    ["Vzdálená pomoc", "Vzdialená pomoc", "Remote support", "Fernwartung"],
    ["Manuál", "Manuál", "Manual", "Handbuch"],
    ["Refresh dat pro dnešní den", "Obnoviť údaje pre dnešný deň", "Refresh today's data", "Heutige Daten aktualisieren"],
    ["Navigace", "Navigácia", "Navigation", "Navigation"],
    ["Změna obsluhy", "Zmena obsluhy", "Change staff member", "Mitarbeiter wechseln"],
    ["Změna obsluhy na jinou osobu", "Zmena obsluhy na inú osobu", "Switch to another staff member", "Zu einem anderen Mitarbeiter wechseln"],
    ["Vyberte obsluhu", "Vyberte obsluhu", "Select a staff member", "Mitarbeiter auswählen"],
    ["Kliknutím na profil otevřete přihlášení k vybrané obsluze.", "Kliknutím na profil otvoríte prihlásenie k vybranej obsluhe.", "Click a profile to sign in as the selected staff member.", "Klicken Sie auf ein Profil, um sich als ausgewählter Mitarbeiter anzumelden."],
    ["Dostupné obsluhy", "Dostupné obsluhy", "Available staff", "Verfügbare Mitarbeiter"],
    ["Přepnout obsluhu", "Prepnúť obsluhu", "Switch staff", "Mitarbeiter wechseln"],
    ["Přehlášení obsluhy", "Prehlásenie obsluhy", "Staff switch", "Mitarbeiterwechsel"],
    ["Přihlásit jako", "Prihlásiť ako", "Sign in as", "Anmelden als"],
    ["Zadejte heslo vybrané obsluhy.", "Zadajte heslo vybranej obsluhy.", "Enter the password for the selected staff member.", "Geben Sie das Passwort des ausgewählten Mitarbeiters ein."],
    ["Heslo obsluhy", "Heslo obsluhy", "Staff password", "Mitarbeiterpasswort"],
    ["Přehlásit obsluhu", "Prehlásiť obsluhu", "Switch staff", "Mitarbeiter wechseln"],
    ["Chybné přihlášení. Zkontrolujte heslo a zkuste to znovu.", "Nesprávne prihlásenie. Skontrolujte heslo a skúste to znova.", "Sign-in failed. Check the password and try again.", "Anmeldung fehlgeschlagen. Prüfen Sie das Passwort und versuchen Sie es erneut."],
    ["Povolit přepnutí na tuto obsluhu", "Povoliť prepnutie na túto obsluhu", "Allow switching to this staff member", "Wechsel zu diesem Mitarbeiter erlauben"],
    ["Obsluha se zobrazí v nabídce Změna obsluhy.", "Obsluha sa zobrazí v ponuke Zmena obsluhy.", "The staff member will appear in the Change staff menu.", "Der Mitarbeiter wird im Menü Mitarbeiter wechseln angezeigt."],
    ["Žádná další obsluha není dostupná", "Žiadna ďalšia obsluha nie je dostupná", "No other staff member is available", "Kein weiterer Mitarbeiter ist verfügbar"],
    ["Pro tuto pobočku není povoleno přehlášení na jinou aktivní obsluhu.", "Pre túto pobočku nie je povolené prehlásenie na inú aktívnu obsluhu.", "Switching to another active staff member is not enabled for this branch.", "Für diese Filiale ist der Wechsel zu einem anderen aktiven Mitarbeiter nicht aktiviert."],
    ["Dashboard", "Prehľad", "Dashboard", "Dashboard"],
    ["Dashboard s rychlými přehledy za všechny pobočky", "Prehľad s rýchlymi súhrnmi za všetky pobočky", "Dashboard with quick summaries for all branches", "Dashboard mit Schnellübersichten für alle Filialen"],
    ["Nový zákazník", "Nový zákazník", "New customer", "Neuer Kunde"],
    ["Založení nové karty zákazníka", "Vytvorenie novej karty zákazníka", "Create a new customer record", "Neue Kundenkartei anlegen"],
    ["Osobní a kontaktní údaje", "Osobné a kontaktné údaje", "Personal and contact details", "Persönliche und Kontaktdaten"],
    ["Základní informace a spojení na zákazníka", "Základné informácie a kontakt na zákazníka", "Basic information and customer contact details", "Grunddaten und Kontaktinformationen des Kunden"],
    ["Zdravotní údaje", "Zdravotné údaje", "Health information", "Gesundheitsdaten"],
    ["Pojišťovna, rodné číslo a důležité poznámky", "Poisťovňa, rodné číslo a dôležité poznámky", "Insurance provider, personal ID number and important notes", "Krankenkasse, Personenkennzahl und wichtige Hinweise"],
    ["Firma a komunikace", "Firma a komunikácia", "Company and communication", "Firma und Kommunikation"],
    ["Firemní, webové a komunikační údaje", "Firemné, webové a komunikačné údaje", "Company, web and communication details", "Firmen-, Web- und Kommunikationsdaten"],
    ["Systémové údaje", "Systémové údaje", "System information", "Systemdaten"],
    ["Historie návštěv a údaje doplňované systémem", "História návštev a údaje dopĺňané systémom", "Visit history and system-generated data", "Besuchshistorie und vom System ergänzte Daten"],
    ["Pobočka", "Pobočka", "Branch", "Filiale"],
    ["Jméno", "Meno", "First name", "Vorname"],
    ["Příjmení", "Priezvisko", "Last name", "Nachname"],
    ["Titul", "Titul", "Title", "Titel"],
    ["Datum narození", "Dátum narodenia", "Date of birth", "Geburtsdatum"],
    ["Pohlaví", "Pohlavie", "Gender", "Geschlecht"],
    ["Ulice", "Ulica", "Street", "Straße"],
    ["Město", "Mesto", "City", "Stadt"],
    ["PSČ", "PSČ", "Postal code", "PLZ"],
    ["Mobil", "Mobil", "Mobile", "Mobil"],
    ["Telefon", "Telefón", "Phone", "Telefon"],
    ["Rodné číslo", "Rodné číslo", "Personal ID number", "Personenkennzahl"],
    ["Pojišťovna", "Poisťovňa", "Health insurance provider", "Krankenkasse"],
    ["Další záznamy (léky, alergie)", "Ďalšie záznamy (lieky, alergie)", "Additional notes (medication, allergies)", "Weitere Angaben (Medikamente, Allergien)"],
    ["Jméno firmy", "Názov firmy", "Company name", "Firmenname"],
    ["IČO", "IČO", "Company ID", "Unternehmens-ID"],
    ["Blokovat SMS", "Blokovať SMS", "Block SMS", "SMS sperren"],
    ["Číslo karty", "Číslo karty", "Card number", "Kartennummer"],
    ["Počet návštěv", "Počet návštev", "Number of visits", "Anzahl Besuche"],
    ["Zrušené objednávky", "Zrušené objednávky", "Cancelled appointments", "Stornierte Termine"],
    ["Příští návštěva", "Nasledujúca návšteva", "Next visit", "Nächster Besuch"],
    ["Poslední návštěva", "Posledná návšteva", "Last visit", "Letzter Besuch"],
    ["Programy", "Programy", "Programs", "Programme"],
    ["Programy zákazníka", "Programy zákazníka", "Customer programs", "Kundenprogramme"],
    ["Zóny těla", "Zóny tela", "Body zones", "Körperzonen"],
    ["Zbývající vstupy celkem", "Zostávajúce vstupy celkom", "Total remaining entries", "Verbleibende Eintritte gesamt"],
    ["Programová data se zatím nezobrazují.", "Programové dáta sa zatiaľ nezobrazujú.", "Program data are not displayed yet.", "Programmdaten werden derzeit noch nicht angezeigt."],
    ["Sekce je připravena pro napojení dat z HairSoft.", "Sekcia je pripravená na napojenie dát z HairSoft.", "The section is ready for HairSoft data integration.", "Der Bereich ist für die Anbindung von HairSoft-Daten vorbereitet."],
    ["Poslední změna", "Posledná zmena", "Last modified", "Letzte Änderung"],
    ["Poznámka", "Poznámka", "Note", "Notiz"],
    ["Neurčeno", "Neurčené", "Not specified", "Nicht angegeben"],
    ["Muž", "Muž", "Male", "Männlich"],
    ["Žena", "Žena", "Female", "Weiblich"],
    ["Dítě", "Dieťa", "Child", "Kind"],
    ["Vyberte...", "Vyberte...", "Select...", "Auswählen..."],
    ["NE", "NIE", "NO", "NEIN"],
    ["ANO", "ÁNO", "YES", "JA"],
    ["Anonymizováno", "Anonymizované", "Anonymized", "Anonymisiert"],
    ["Toto telefonní číslo již existuje v databázi!", "Toto telefónne číslo už existuje v databáze!", "This phone number already exists in the database!", "Diese Telefonnummer ist bereits in der Datenbank vorhanden!"],
    ["Založit zákazníka", "Vytvoriť zákazníka", "Create customer", "Kunden anlegen"],
    ["Pobočky", "Pobočky", "Branches", "Filialen"],
    ["Zákazníci", "Zákazníci", "Customers", "Kunden"],
    ["Seznam zákazníků", "Zoznam zákazníkov", "Customer list", "Kundenliste"],
    ["Počet zákazníků", "Počet zákazníkov", "Customer count", "Anzahl Kunden"],
    ["Zákazník", "Zákazník", "Customer", "Kunde"],
    ["Detail", "Detail", "Details", "Detail"],
    ["Detail zákazníka", "Detail zákazníka", "Customer details", "Kundendetails"],
    ["Možnosti", "Možnosti", "Options", "Optionen"],
    ["Profilovka", "Profilová fotografia", "Profile photo", "Profilbild"],
    ["Fotografie", "Fotografia", "Photo", "Foto"],
    ["Přidat fotografii", "Pridať fotografiu", "Add photo", "Foto hinzufügen"],
    ["Volat", "Volať", "Call", "Anrufen"],
    ["Bonusové body", "Bonusové body", "Bonus points", "Bonuspunkte"],
    ["Refresh stránky", "Obnoviť stránku", "Refresh page", "Seite aktualisieren"],
    ["Smazat osobu", "Odstrániť osobu", "Delete person", "Person löschen"],
    ["Timeline", "Časová os", "Timeline", "Zeitleiste"],
    ["SMS Chat", "SMS Chat", "SMS chat", "SMS-Chat"],
    ["Galerie", "Galéria", "Gallery", "Galerie"],
    ["Fotografie zákazníka", "Fotografie zákazníka", "Customer photos", "Kundenfotos"],
    ["Kliknutím fotografii otevřete. Další možnosti najdete v nabídce u snímku.", "Kliknutím fotografiu otvoríte. Ďalšie možnosti nájdete v ponuke pri snímke.", "Click a photo to open it. More options are available in the photo menu.", "Klicken Sie auf ein Foto, um es zu öffnen. Weitere Optionen finden Sie im Fotomenü."],
    ["Počet fotografií", "Počet fotografií", "Number of photos", "Anzahl der Fotos"],
    ["Možnosti fotografie", "Možnosti fotografie", "Photo options", "Fotooptionen"],
    ["Další možnosti fotografie", "Ďalšie možnosti fotografie", "More photo options", "Weitere Fotooptionen"],
    ["Nastavit jako profilovku", "Nastaviť ako profilovú fotografiu", "Set as profile photo", "Als Profilfoto festlegen"],
    ["Otočit doprava", "Otočiť doprava", "Rotate right", "Nach rechts drehen"],
    ["Otočit doleva", "Otočiť doľava", "Rotate left", "Nach links drehen"],
    ["Smazat obrázek", "Odstrániť obrázok", "Delete photo", "Foto löschen"],
    ["Zatím zde nejsou žádné fotografie.", "Zatiaľ tu nie sú žiadne fotografie.", "There are no photos here yet.", "Hier sind noch keine Fotos vorhanden."],
    ["Předchozí fotografie", "Predchádzajúca fotografia", "Previous photo", "Vorheriges Foto"],
    ["Další fotografie", "Ďalšia fotografia", "Next photo", "Nächstes Foto"],
    ["Soubory", "Súbory", "Files", "Dateien"],
    ["Soubory zákazníka", "Súbory zákazníka", "Customer files", "Kundendateien"],
    ["Dokumenty a přílohy uložené u zákazníka", "Dokumenty a prílohy uložené pri zákazníkovi", "Documents and attachments saved for this customer", "Dokumente und Anhänge dieses Kunden"],
    ["Počet souborů", "Počet súborov", "Number of files", "Anzahl der Dateien"],
    ["Vybrat soubor k nahrání", "Vybrať súbor na nahratie", "Select a file to upload", "Datei zum Hochladen auswählen"],
    ["nebo klikněte pro výběr souboru", "alebo kliknite a vyberte súbor", "or click to select a file", "oder klicken, um eine Datei auszuwählen"],
    ["Nahrávám soubor…", "Nahrávam súbor…", "Uploading file…", "Datei wird hochgeladen…"],
    ["Počkejte prosím na dokončení nahrávání", "Počkajte, kým sa nahrávanie dokončí", "Please wait for the upload to finish", "Bitte warten Sie, bis der Upload abgeschlossen ist"],
    ["Zatím zde nejsou žádné soubory.", "Zatiaľ tu nie sú žiadne súbory.", "There are no files here yet.", "Hier sind noch keine Dateien vorhanden."],
    ["První soubor přidáte přetažením nebo kliknutím do pole výše.", "Prvý súbor pridáte presunutím alebo kliknutím do poľa vyššie.", "Add the first file by dropping it or clicking the field above.", "Fügen Sie die erste Datei per Drag-and-drop oder über das Feld oben hinzu."],
    ["Staženo", "Stiahnuté", "Downloaded", "Heruntergeladen"],
    ["Nestaženo", "Nestiahnuté", "Not downloaded", "Nicht heruntergeladen"],
    ["Smazat soubor", "Odstrániť súbor", "Delete file", "Datei löschen"],
    ["Nová poznámka", "Nová poznámka", "New note", "Neue Notiz"],
    ["Uložit poznámku", "Uložiť poznámku", "Save note", "Notiz speichern"],
    ["Uložit změny", "Uložiť zmeny", "Save changes", "Änderungen speichern"],
    ["Nahrání souboru", "Nahratie súboru", "File upload", "Datei hochladen"],
    ["Přetáhni soubor sem", "Presuňte súbor sem", "Drop a file here", "Datei hier ablegen"],
    ["Max velikost 30MB", "Maximálna veľkosť 30 MB", "Maximum size 30 MB", "Maximale Größe 30 MB"],
    ["Přehled hodnocení", "Prehľad hodnotení", "Ratings overview", "Bewertungsübersicht"],
    ["Všechny otázky jsou dostupné v přehledu hodnocení.", "Všetky otázky sú dostupné v prehľade hodnotenia.", "All questions are available in the ratings overview.", "Alle Fragen sind in der Bewertungsübersicht verfügbar."],
    ["Termín objednávky", "Termín objednávky", "Appointment date", "Termin"],
    ["Kdy bylo hodnoceno", "Kedy bolo hodnotené", "Rated on", "Bewertet am"],
    ["Jméno zákazníka", "Meno zákazníka", "Customer name", "Kundenname"],
    ["Hodnocení otázky", "Hodnotenie otázky", "Question ratings", "Fragenbewertung"],
    ["Otázka", "Otázka", "Question", "Frage"],
    ["Odpověď", "Odpoveď", "Answer", "Antwort"],
    ["Kontaktní údaje", "Kontaktné údaje", "Contact details", "Kontaktdaten"],
    ["Textová odpověď", "Textová odpoveď", "Text answer", "Textantwort"],
    ["Zobrazit textovou odpověď", "Zobraziť textovú odpoveď", "Show text answer", "Textantwort anzeigen"],
    ["Název", "Názov", "Name", "Name"],
    ["Velikost", "Veľkosť", "Size", "Größe"],
    ["Datum", "Dátum", "Date", "Datum"],
    ["Stav", "Stav", "Status", "Status"],
    ["Obsluha", "Obsluha", "Staff member", "Mitarbeiter"],
    ["Zrušené", "Zrušené", "Cancelled", "Storniert"],
    ["Body", "Body", "Points", "Punkte"],
    ["Akce", "Akcie", "Actions", "Aktionen"],
    ["Export", "Export", "Export", "Export"],
    ["Hledat", "Hľadať", "Search", "Suchen"],
    ["Hledat:", "Hľadať:", "Search:", "Suchen:"],
    ["Hledat zákazníka", "Hľadať zákazníka", "Search for a customer", "Kunden suchen"],
    ["Hledat podle jména, e-mailu nebo telefonu…", "Hľadať podľa mena, e-mailu alebo telefónu…", "Search by name, email or phone…", "Nach Name, E-Mail oder Telefon suchen…"],
    ["Nic nenalezeno", "Nič sa nenašlo", "No records found", "Keine Einträge gefunden"],
    ["První", "Prvá", "First", "Erste"],
    ["Poslední", "Posledná", "Last", "Letzte"],
    ["Další", "Ďalšia", "Next", "Weiter"],
    ["Předešlá", "Predchádzajúca", "Previous", "Zurück"],
    ["Zpět", "Späť", "Back", "Zurück"],
    ["Zobrazit stránkování", "Zobraziť stránkovanie", "Show pagination", "Seitennavigation anzeigen"],
    ["Zobrazit všechny", "Zobraziť všetkých", "Show all", "Alle anzeigen"],
    ["Správa dat zákazníků", "Správa údajov zákazníkov", "Customer data management", "Kundendaten verwalten"],
    ["Smazat zákazníka", "Odstrániť zákazníka", "Delete customer", "Kunden löschen"],
    ["Smazat seznam zákazníků", "Odstrániť zoznam zákazníkov", "Delete customer list", "Kundenliste löschen"],
    ["Znovunahrát zákazníky z PC", "Znovu nahrať zákazníkov z PC", "Reload customers from PC", "Kunden vom PC neu laden"],
    ["Čeká se na znovunahrání zákazníků z PC. Tato operace se spustí do 15 minut.", "Čaká sa na opätovné nahratie zákazníkov z PC. Táto operácia sa spustí do 15 minút.", "Waiting for customers to be reloaded from the PC. This operation will start within 15 minutes.", "Kunden werden erneut vom PC geladen. Der Vorgang startet innerhalb von 15 Minuten."],
    ["Potvrdit smazání", "Potvrdiť odstránenie", "Confirm deletion", "Löschen bestätigen"],
    ["Smazat", "Odstrániť", "Delete", "Löschen"],
    ["Zrušit", "Zrušiť", "Cancel", "Abbrechen"],
    ["Opravdu chcete smazat tuto osobu? Osoba bude smazána také v programu HairSoft.", "Naozaj chcete odstrániť túto osobu? Osoba bude odstránená aj v programe HairSoft.", "Are you sure you want to delete this person? The person will also be deleted in HairSoft.", "Möchten Sie diese Person wirklich löschen? Die Person wird auch in HairSoft gelöscht."],
    ["Opravdu chcete smazat tento záznam? Záznam bude trvale odstraněn ze systému.", "Naozaj chcete odstrániť tento záznam? Záznam bude natrvalo odstránený zo systému.", "Are you sure you want to delete this record? The record will be permanently removed from the system.", "Möchten Sie diesen Eintrag wirklich löschen? Der Eintrag wird dauerhaft aus dem System entfernt."],
    ["Opravdu chcete smazat tento soubor? Soubor bude smazán také v programu HairSoft.", "Naozaj chcete odstrániť tento súbor? Súbor bude odstránený aj v programe HairSoft.", "Are you sure you want to delete this file? The file will also be deleted in HairSoft.", "Möchten Sie diese Datei wirklich löschen? Die Datei wird auch in HairSoft gelöscht."],
    ["Opravdu chcete smazat kompletní stav skladu?", "Naozaj chcete odstrániť kompletný stav skladu?", "Are you sure you want to delete the entire inventory status?", "Möchten Sie den gesamten Lagerbestand wirklich löschen?"],
    ["Opravdu chcete smazat tento obrázek? Obrázek bude trvale odstraněn.", "Naozaj chcete odstrániť tento obrázok? Obrázok bude natrvalo odstránený.", "Are you sure you want to delete this image? The image will be permanently removed.", "Möchten Sie dieses Bild wirklich löschen? Das Bild wird dauerhaft entfernt."],
    ["Opravdu chcete SMAZAT tuto osobu? Osoba bude trvale vymazána ze systému!", "Naozaj chcete ODSTRÁNIŤ túto osobu? Osoba bude natrvalo vymazaná zo systému!", "Are you sure you want to DELETE this person? The person will be permanently removed from the system!", "Möchten Sie diese Person wirklich LÖSCHEN? Die Person wird dauerhaft aus dem System entfernt!"],
    ["Opravdu chcete SMAZAT seznam zákazníků z klientského webu? Poznámka: nemá vliv na kartotéku zákazníků v programu HairSoft.", "Naozaj chcete ODSTRÁNIŤ zoznam zákazníkov z klientského webu? Poznámka: nemá to vplyv na kartotéku zákazníkov v programe HairSoft.", "Are you sure you want to DELETE the customer list from the client website? Note: this does not affect customer records in HairSoft.", "Möchten Sie die Kundenliste wirklich von der Kundenwebsite LÖSCHEN? Hinweis: Die Kundenkartei in HairSoft bleibt davon unberührt."],
    ["Rezervace", "Rezervácie", "Bookings", "Reservierungen"],
    ["Administrace", "Administrácia", "Administration", "Administration"],
    ["Administrace Bonfero", "Administrácia Bonfero", "Bonfero administration", "Bonfero-Verwaltung"],
    ["Administrace rezervačního systému Bonfero přímo v HairSoft Klient", "Administrácia rezervačného systému Bonfero priamo v HairSoft Klient", "Manage the Bonfero booking system directly in HairSoft Client", "Bonfero-Reservierungen direkt in HairSoft Client verwalten"],
    ["Rezervace a jejich administrace", "Rezervácie a ich administrácia", "Bookings and administration", "Reservierungen und Verwaltung"],
    ["Znovu načíst Bonfero", "Znova načítať Bonfero", "Reload Bonfero", "Bonfero neu laden"],
    ["Obnovit", "Obnoviť", "Reload", "Neu laden"],
    ["Otevřít Bonfero v novém okně", "Otvoriť Bonfero v novom okne", "Open Bonfero in a new window", "Bonfero in einem neuen Fenster öffnen"],
    ["Otevřít zvlášť", "Otvoriť samostatne", "Open separately", "Separat öffnen"],
    ["Načítám Bonfero", "Načítavam Bonfero", "Loading Bonfero", "Bonfero wird geladen"],
    ["Chvíli strpení…", "Chvíľu strpenia…", "One moment…", "Einen Moment…"],
    ["Administrace rezervací Bonfero", "Administrácia rezervácií Bonfero", "Bonfero booking administration", "Bonfero-Reservierungsverwaltung"],
    ["Pro zobrazení Bonfera uvnitř HairSoft Klient je potřeba povolit JavaScript.", "Na zobrazenie Bonfera v HairSoft Klient je potrebné povoliť JavaScript.", "JavaScript must be enabled to display Bonfero inside HairSoft Client.", "JavaScript muss aktiviert sein, um Bonfero in HairSoft Client anzuzeigen."],
    ["Otevřít Bonfero samostatně", "Otvoriť Bonfero samostatne", "Open Bonfero separately", "Bonfero separat öffnen"],
    ["Google kalendář", "Google kalendár", "Google Calendar", "Google Kalender"],
    ["Volno dnes / Pořadník", "Voľno dnes / Poradovník", "Availability today / Waiting list", "Heute frei / Warteliste"],
    ["Tržby", "Tržby", "Revenue", "Umsatz"],
    ["Denní tržby", "Denné tržby", "Daily revenue", "Tagesumsatz"],
    ["Přehled Vašich denních tržeb za pobočku:", "Prehľad vašich denných tržieb za pobočku:", "Overview of your daily revenue for branch:", "Übersicht Ihrer Tagesumsätze für die Filiale:"],
    ["TOP obsluha za služby", "TOP obsluha za služby", "Top staff member for services", "Top-Mitarbeiter Dienstleistungen"],
    ["TOP obsluha za prodej", "TOP obsluha za predaj", "Top staff member for retail", "Top-Mitarbeiter Verkauf"],
    ["Tržby pro datum", "Tržby pre dátum", "Revenue by date", "Umsatz nach Datum"],
    ["Denní sumář za střediska | firmy", "Denný súhrn za strediská | firmy", "Daily summary by branches | companies", "Tagesübersicht nach Filialen | Firmen"],
    ["Denní sumář za střediska a firmy", "Denný súhrn za strediská a firmy", "Daily summary by branches and companies", "Tagesübersicht nach Filialen und Firmen"],
    ["Denní sumář za jednotlivce", "Denný súhrn za jednotlivcov", "Daily summary by staff member", "Tagesübersicht nach Mitarbeitern"],
    ["Název střediska | firmy", "Názov strediska | firmy", "Branch | company name", "Name der Filiale | Firma"],
    ["Středisko | firmy", "Stredisko | firma", "Branch | company", "Filiale | Firma"],
    ["Jméno obsluhy", "Meno obsluhy", "Staff member", "Mitarbeiter"],
    ["Tržby celkem", "Tržby celkom", "Total revenue", "Gesamtumsatz"],
    ["Za služby", "Za služby", "Services", "Dienstleistungen"],
    ["Za prodej", "Za predaj", "Retail", "Verkauf"],
    ["Ceniny", "Ceniny", "Vouchers", "Gutscheine"],
    ["Kredit", "Kredit", "Credit", "Guthaben"],
    ["Služby dnes dle uživatelů", "Služby dnes podľa používateľov", "Services today by staff member", "Dienstleistungen heute nach Mitarbeiter"],
    ["Prodej dnes dle uživatelů", "Predaj dnes podľa používateľov", "Retail today by staff member", "Verkauf heute nach Mitarbeiter"],
    ["Účtenky dle obsluhy", "Účtenky podľa obsluhy", "Receipts by staff member", "Belege nach Mitarbeiter"],
    ["Tržby dle typu platby", "Tržby podľa typu platby", "Revenue by payment type", "Umsatz nach Zahlungsart"],
    ["Firma:", "Firma:", "Company:", "Firma:"],
    ["Nejsou žádná data k zobrazení", "Nie sú žiadne údaje na zobrazenie", "No data to display", "Keine Daten zur Anzeige"],
    ["Nejsou žádná data k zobrazení.", "Nie sú žiadne údaje na zobrazenie.", "No data to display.", "Keine Daten zur Anzeige."],
    ["Pro datum:", "Pre dátum:", "For date:", "Für das Datum:"],
    ["a pobočku", "a pobočku", "and branch", "und die Filiale"],
    ["nejsou nalezeny žádné tržby za střediska.", "neboli nájdené žiadne tržby za strediská.", "no branch revenue was found.", "wurden keine Filialumsätze gefunden."],
    ["nejsou nalezeny žádné tržby za jednotlivce", "neboli nájdené žiadne tržby za jednotlivcov", "no staff revenue was found", "wurden keine Mitarbeiterumsätze gefunden"],
    ["Načíst starší data aktálního roku", "Načítať staršie údaje aktuálneho roka", "Load older data for the current year", "Ältere Daten des aktuellen Jahres laden"],
    ["Refresh dat z PC", "Obnoviť údaje z PC", "Refresh data from PC", "Daten vom PC aktualisieren"],
    ["Smazat zobrazená data", "Odstrániť zobrazené údaje", "Delete displayed data", "Angezeigte Daten löschen"],
    ["Váš požadavek na refresh dat pro tento den byl zadán", "Vaša požiadavka na obnovenie údajov pre tento deň bola zadaná", "Your request to refresh data for this day has been submitted", "Ihre Anfrage zur Aktualisierung der Daten für diesen Tag wurde übermittelt"],
    ["Váš požadavek na refresh dat z minulosti byl zadán", "Vaša požiadavka na obnovenie starších údajov bola zadaná", "Your request to refresh historical data has been submitted", "Ihre Anfrage zur Aktualisierung älterer Daten wurde übermittelt"],
    ["Právě probíhá synchronizace tržeb z minulosti", "Práve prebieha synchronizácia starších tržieb", "Historical revenue data is currently being synchronized", "Ältere Umsatzdaten werden derzeit synchronisiert"],
    ["Opravdu chcete SMAZAT data středisek a jednotlivců pro tento den?", "Naozaj chcete ODSTRÁNIŤ údaje stredísk a jednotlivcov pre tento deň?", "Are you sure you want to DELETE branch and staff data for this day?", "Möchten Sie die Filial- und Mitarbeiterdaten für diesen Tag wirklich LÖSCHEN?"],
    ["Hotově", "V hotovosti", "Cash", "Barzahlung"],
    ["Hotovost", "Hotovosť", "Cash", "Barzahlung"],
    ["QR Platba", "QR platba", "QR payment", "QR-Zahlung"],
    ["Platební karta", "Platobná karta", "Card payment", "Kartenzahlung"],
    ["Karta", "Karta", "Card", "Karte"],
    ["Bankovní převod", "Bankový prevod", "Bank transfer", "Banküberweisung"],
    ["Tisk", "Tlač", "Print", "Drucken"],
    ["Neuvedeno", "Neuvedené", "Not specified", "Nicht angegeben"],
    ["Bez názvu", "Bez názvu", "Unnamed", "Ohne Namen"],
    ["HairSoft Klient • vytvořeno", "HairSoft Klient • vytvorené", "HairSoft Client • created", "HairSoft Client • erstellt"],
    ["Strana", "Strana", "Page", "Seite"],
    ["Pro vybraný den nejsou k dispozici žádné záznamy.", "Pre vybraný deň nie sú k dispozícii žiadne záznamy.", "No records are available for the selected day.", "Für den ausgewählten Tag sind keine Einträge verfügbar."],
    ["Správa dat", "Správa údajov", "Data management", "Datenverwaltung"],
    ["Načtení, synchronizace a odstranění zobrazených dat", "Načítanie, synchronizácia a odstránenie zobrazených údajov", "Load, synchronize and delete displayed data", "Angezeigte Daten laden, synchronisieren und löschen"],
    ["Tyto akce ovlivňují data zobrazená na této stránce.", "Tieto akcie ovplyvňujú údaje zobrazené na tejto stránke.", "These actions affect the data displayed on this page.", "Diese Aktionen wirken sich auf die auf dieser Seite angezeigten Daten aus."],
    ["Vyberte den pro zobrazení tržeb", "Vyberte deň pre zobrazenie tržieb", "Select a day to display revenue", "Tag für die Umsatzanzeige auswählen"],
    ["Přehled tržeb za vybraný den", "Prehľad tržieb za vybraný deň", "Revenue overview for the selected day", "Umsatzübersicht für den ausgewählten Tag"],
    ["Podíl jednotlivých středisek a obsluh", "Podiel jednotlivých stredísk a obsluhy", "Share by branch and staff member", "Anteil nach Filiale und Mitarbeiter"],
    ["Celkem", "Celkom", "Total", "Gesamt"],
    ["Součet", "Súčet", "Total", "Summe"],
    ["Středisko | firma", "Stredisko | firma", "Branch | company", "Filiale | Firma"],
    ["bez DPH", "bez DPH", "excl. VAT", "ohne MwSt."],
    ["Účtenek", "Účteniek", "Receipts", "Belege"],
    ["Hodnota", "Hodnota", "Value", "Wert"],
    ["Na telefonu jsou jednotlivé záznamy zobrazené jako přehledné karty.", "V telefóne sú jednotlivé záznamy zobrazené ako prehľadné karty.", "On a phone, individual records are shown as clear cards.", "Auf dem Smartphone werden einzelne Einträge als übersichtliche Karten angezeigt."],
    ["Měsíční tržby", "Mesačné tržby", "Monthly revenue", "Monatsumsatz"],
    ["Přehled Vašich měsíčních tržeb za pobočku:", "Prehľad vašich mesačných tržieb za pobočku:", "Overview of your monthly revenue for branch:", "Übersicht Ihrer Monatsumsätze für die Filiale:"],
    ["Období tržeb", "Obdobie tržieb", "Revenue period", "Umsatzzeitraum"],
    ["Vyberte měsíc a rok", "Vyberte mesiac a rok", "Select month and year", "Monat und Jahr auswählen"],
    ["Měsíc", "Mesiac", "Month", "Monat"],
    ["Rok", "Rok", "Year", "Jahr"],
    ["Předchozí měsíc", "Predchádzajúci mesiac", "Previous month", "Vorheriger Monat"],
    ["Následující měsíc", "Nasledujúci mesiac", "Next month", "Nächster Monat"],
    ["Měsíční sumář za střediska | firmy", "Mesačný súhrn za strediská | firmy", "Monthly summary by branches | companies", "Monatssumme nach Filialen | Firmen"],
    ["Měsíční sumář za střediska a firmy", "Mesačný súhrn za strediská a firmy", "Monthly summary by branches and companies", "Monatssumme nach Filialen und Firmen"],
    ["Měsíční sumář za jednotlivce", "Mesačný súhrn za jednotlivcov", "Monthly summary by staff member", "Monatssumme nach Mitarbeitern"],
    ["Přehled tržeb za vybraný měsíc", "Prehľad tržieb za vybraný mesiac", "Revenue overview for the selected month", "Umsatzübersicht für den ausgewählten Monat"],
    ["Vývoj tržeb v průběhu měsíce", "Vývoj tržieb v priebehu mesiaca", "Revenue trend during the month", "Umsatzentwicklung im Monatsverlauf"],
    ["Denní vývoj služeb, prodeje, cenin a kreditu", "Denný vývoj služieb, predaja, cenín a kreditu", "Daily trend of services, retail, vouchers and credit", "Tägliche Entwicklung von Dienstleistungen, Verkauf, Gutscheinen und Guthaben"],
    ["Služby tento měsíc dle uživatelů", "Služby tento mesiac podľa používateľov", "Services this month by staff member", "Dienstleistungen diesen Monat nach Mitarbeitern"],
    ["Prodej tento měsíc dle uživatelů", "Predaj tento mesiac podľa používateľov", "Retail this month by staff member", "Verkauf diesen Monat nach Mitarbeitern"],
    ["Služby dle dnů v týdnu", "Služby podľa dní v týždni", "Services by day of week", "Dienstleistungen nach Wochentag"],
    ["Počet účtenek v jednotlivých dnech", "Počet účteniek v jednotlivých dňoch", "Receipt count by day", "Beleganzahl nach Tag"],
    ["Účtenky podle obsluhy", "Účtenky podľa obsluhy", "Receipts by staff member", "Belege nach Mitarbeiter"],
    ["Účtenky podle oblsuhy", "Účtenky podľa obsluhy", "Receipts by staff member", "Belege nach Mitarbeiter"],
    ["Rozdělení za vybraný měsíc", "Rozdelenie za vybraný mesiac", "Distribution for the selected month", "Verteilung für den ausgewählten Monat"],
    ["Refresh dat pro tento měsíc", "Obnoviť údaje pre tento mesiac", "Refresh data for this month", "Daten für diesen Monat aktualisieren"],
    ["Smazat data pro tento měsíc", "Odstrániť údaje pre tento mesiac", "Delete data for this month", "Daten dieses Monats löschen"],
    ["Váš požadavek na refresh dat pro tento měsíc a rok byl zadán", "Vaša požiadavka na obnovenie údajov pre tento mesiac a rok bola zadaná", "Your request to refresh data for this month and year has been submitted", "Ihre Anfrage zur Aktualisierung der Daten für diesen Monat und dieses Jahr wurde übermittelt"],
    ["Opravdu chcete SMAZAT data středisek a jednotlivců pro tento měsíc?", "Naozaj chcete ODSTRÁNIŤ údaje stredísk a jednotlivcov pre tento mesiac?", "Are you sure you want to DELETE branch and staff data for this month?", "Möchten Sie die Filial- und Mitarbeiterdaten für diesen Monat wirklich LÖSCHEN?"],
    ["Tyto akce ovlivňují data vybraného měsíce.", "Tieto akcie ovplyvňujú údaje vybraného mesiaca.", "These actions affect data for the selected month.", "Diese Aktionen wirken sich auf die Daten des ausgewählten Monats aus."],
    ["Pro vybraný měsíc nejsou k dispozici žádné záznamy.", "Pre vybraný mesiac nie sú k dispozícii žiadne záznamy.", "No records are available for the selected month.", "Für den ausgewählten Monat sind keine Einträge verfügbar."],
    ["Období", "Obdobie", "Period", "Zeitraum"],
    ["Synchronizováno:", "Synchronizované:", "Synchronized:", "Synchronisiert:"],
    ["Automatické načtení dat probíhá každých 15 minut.", "Automatické načítanie údajov prebieha každých 15 minút.", "Data is loaded automatically every 15 minutes.", "Die Daten werden automatisch alle 15 Minuten geladen."],
    ["Strana načtena za", "Stránka načítaná za", "Page loaded in", "Seite geladen in"],
    ["nezjištěno", "nezistené", "unknown", "unbekannt"],
    ["Pondělí", "Pondelok", "Monday", "Montag"],
    ["Úterý", "Utorok", "Tuesday", "Dienstag"],
    ["Středa", "Streda", "Wednesday", "Mittwoch"],
    ["Čtvrtek", "Štvrtok", "Thursday", "Donnerstag"],
    ["Pátek", "Piatok", "Friday", "Freitag"],
    ["Sobota", "Sobota", "Saturday", "Samstag"],
    ["Neděle", "Nedeľa", "Sunday", "Sonntag"],
    ["Roční tržby", "Ročné tržby", "Annual revenue", "Jahresumsatz"],
    ["Celkové tržby", "Celkové tržby", "Total revenue", "Gesamtumsatz"],
    ["Sklad", "Sklad", "Inventory", "Lager"],
    ["Vouchery", "Vouchery", "Vouchers", "Gutscheine"],
    ["Hodnocení", "Hodnotenie", "Ratings", "Bewertungen"],
    ["Hodnocení spokojenosti", "Hodnotenie spokojnosti", "Satisfaction ratings", "Zufriedenheitsbewertung"],
    ["Statistika", "Štatistika", "Statistics", "Statistik"],
    ["Nastavení", "Nastavenia", "Settings", "Einstellungen"],
    ["Přístupy", "Prístupy", "Access", "Zugriffe"],
    ["Číselník pojišťoven", "Číselník poisťovní", "Insurance companies", "Versicherungen"],
    ["SMS a hovory", "SMS a hovory", "SMS and calls", "SMS und Anrufe"],
    ["Přehled SMS a hovorů", "Prehľad SMS a hovorov", "SMS and call overview", "SMS- und Anrufübersicht"],
    ["Přehled odeslaných a přijatých SMS a hovorů", "Prehľad odoslaných a prijatých SMS a hovorov", "Overview of sent and received SMS and calls", "Übersicht über gesendete und empfangene SMS und Anrufe"],
    ["Výběr pobočky", "Výber pobočky", "Select branch", "Filiale auswählen"],
    ["Filtr odeslaných SMS", "Filter odoslaných SMS", "Sent SMS filter", "Filter für gesendete SMS"],
    ["Online rezervace, objednávky a blacklist", "Online rezervácie, objednávky a blacklist", "Online bookings, appointments and blacklist", "Online-Reservierungen, Termine und Blacklist"],
    ["Zjistit počet", "Zistiť počet", "Get count", "Anzahl ermitteln"],
    ["Počet", "Počet", "Count", "Anzahl"],
    ["Počet odeslaných SMS", "Počet odoslaných SMS", "Sent SMS count", "Anzahl gesendeter SMS"],
    ["Celkem SMS", "Celkom SMS", "Total SMS", "SMS gesamt"],
    ["SMS kredit", "SMS kredit", "SMS credit", "SMS-Guthaben"],
    ["SMS fronta", "SMS fronta", "SMS queue", "SMS-Warteschlange"],
    ["Celkem odeslané", "Celkom odoslané", "Total sent", "Insgesamt gesendet"],
    ["Tarif SMS", "Tarifa SMS", "SMS rate", "SMS-Tarif"],
    ["Dle typu SMS", "Podľa typu SMS", "SMS by type", "SMS nach Typ"],
    ["Zprávy dle typu", "Správy podľa typu", "Messages by type", "Nachrichtentypen"],
    ["Objednávky", "Objednávky", "Appointments", "Termine"],
    ["Rezervace", "Rezervácie", "Bookings", "Reservierungen"],
    ["Svátek", "Sviatok", "Name day", "Namenstag"],
    ["Svátky", "Sviatky", "Name days", "Namenstage"],
    ["Narozeniny", "Narodeniny", "Birthdays", "Geburtstage"],
    ["Volné", "Voľné", "Custom", "Freie SMS"],
    ["Anonymní", "Anonymné", "Anonymous", "Anonym"],
    ["Pořadník", "Poradovník", "Waiting list", "Warteliste"],
    ["Objednávky z programu", "Objednávky z programu", "Appointments from HairSoft", "Termine aus HairSoft"],
    ["Rezervační systém", "Rezervačný systém", "Booking system", "Reservierungssystem"],
    ["Pro majitele", "Pre majiteľa", "For owner", "Für Inhaber"],
    ["Přehled dobití kreditu", "Prehľad dobitia kreditu", "Credit top-ups", "Guthabenaufladungen"],
    ["Tarif", "Tarifa", "Rate", "Tarif"],
    ["Datum a čas", "Dátum a čas", "Date and time", "Datum und Uhrzeit"],
    ["Výše kreditu", "Výška kreditu", "SMS credit", "SMS-Guthaben"],
    ["Přehled odchozích zpráv", "Prehľad odchádzajúcich správ", "Outgoing messages overview", "Übersicht ausgehender Nachrichten"],
    ["Dnes odeslané", "Dnes odoslané", "Sent today", "Heute gesendet"],
    ["Odeslané za týden", "Odoslané za týždeň", "Sent in the last 7 days", "In den letzten 7 Tagen gesendet"],
    ["Odeslané za měsíc", "Odoslané za mesiac", "Sent this month", "Diesen Monat gesendet"],
    ["Celkové SMS zprávy z programu", "Celkové SMS správy z programu", "Total SMS from HairSoft", "SMS aus HairSoft insgesamt"],
    ["Objednávky z Rezervačního systému", "Objednávky z rezervačného systému", "Booking system appointments", "Termine aus dem Reservierungssystem"],
    ["SMS Hodnocení", "SMS Hodnotenie", "Rating SMS", "Bewertungs-SMS"],
    ["Celkem za SMS", "Celkom za SMS", "Total SMS cost", "SMS-Kosten gesamt"],
    ["Celková výše dobitého kreditu", "Celková výška dobitého kreditu", "Total credit topped up", "Aufgeladenes Guthaben gesamt"],
    ["Dobít kredit", "Dobiť kredit", "Top up credit", "Guthaben aufladen"],
    ["Přehled příchozích hovorů a SMS", "Prehľad prichádzajúcich hovorov a SMS", "Incoming calls and SMS overview", "Übersicht eingehender Anrufe und SMS"],
    ["Hovory dnes", "Hovory dnes", "Calls today", "Anrufe heute"],
    ["Hovory aktuální měsíc", "Hovory aktuálny mesiac", "Calls this month", "Anrufe diesen Monat"],
    ["Hovory celkem", "Hovory celkom", "Total calls", "Anrufe gesamt"],
    ["Příchozí SMS dnes", "Prichádzajúce SMS dnes", "Incoming SMS today", "Eingehende SMS heute"],
    ["Příchozí SMS aktuální měsíc", "Prichádzajúce SMS aktuálny mesiac", "Incoming SMS this month", "Eingehende SMS diesen Monat"],
    ["Příchozí SMS Celkem", "Prichádzajúce SMS celkom", "Total incoming SMS", "Eingehende SMS gesamt"],
    ["Nejsou žádná data k zobrazení", "Nie sú žiadne údaje na zobrazenie", "No data to display", "Keine Daten zum Anzeigen"],
    ["Data SMS a hovorů jsou načtena z aktuální databáze.", "Údaje SMS a hovorov sú načítané z aktuálnej databázy.", "SMS and call data is loaded from the current database.", "SMS- und Anrufdaten werden aus der aktuellen Datenbank geladen."],
    ["Strana načtena za", "Stránka načítaná za", "Page loaded in", "Seite geladen in"],
    ["Příchozí hovory", "Prichádzajúce hovory", "Incoming calls", "Eingehende Anrufe"],
    ["Příchozí SMS", "Prichádzajúce SMS", "Incoming SMS", "Eingehende SMS"],
    ["Odchozí SMS", "Odchádzajúce SMS", "Outgoing SMS", "Ausgehende SMS"],
    ["Uživatelé", "Používatelia", "Users", "Benutzer"],
    ["Přehled uživatelů", "Prehľad používateľov", "User overview", "Benutzerübersicht"],
    ["Docházka", "Dochádzka", "Attendance", "Zeiterfassung"],
    ["Můj účet", "Môj účet", "My account", "Mein Konto"],
    ["Odhlásit", "Odhlásiť", "Sign out", "Abmelden"],
    ["Pobočka:", "Pobočka:", "Branch:", "Filiale:"],
    ["Tržby dnes", "Tržby dnes", "Revenue today", "Umsatz heute"],
    ["Dnes za prodej", "Dnes za predaj", "Retail today", "Verkauf heute"],
    ["Dnes za služby", "Dnes za služby", "Services today", "Dienstleistungen heute"],
    ["Počet účtenek", "Počet účteniek", "Receipt count", "Anzahl Belege"],
    ["SMS dnes", "SMS dnes", "SMS today", "SMS heute"],
    ["Celkem za měsíc", "Celkom za mesiac", "Total this month", "Gesamt im Monat"],
    ["Prodej za měsíc", "Predaj za mesiac", "Retail this month", "Verkauf im Monat"],
    ["Služby za měsíc", "Služby za mesiac", "Services this month", "Dienstleistungen im Monat"],
    ["SMS za měsíc", "SMS za mesiac", "SMS this month", "SMS im Monat"],
    ["Tržby za služby", "Tržby za služby", "Service revenue", "Dienstleistungsumsatz"],
    ["Tržby za prodej", "Tržby za predaj", "Retail revenue", "Verkaufsumsatz"],
    ["Tržby za vouchery", "Tržby za vouchery", "Voucher revenue", "Gutscheinumsatz"],
    ["Tržby za Vouchery", "Tržby za vouchery", "Voucher revenue", "Gutscheinumsatz"],
    ["Tržby za Kredit", "Tržby za kredit", "Credit revenue", "Guthabenumsatz"],
    ["Měsíční vývoj podle středisek", "Mesačný vývoj podľa stredísk", "Monthly trend by centre", "Monatliche Entwicklung nach Bereich"],
    ["Posunutím zobrazíte další měsíce", "Posunutím zobrazíte ďalšie mesiace", "Swipe to view more months", "Wischen, um weitere Monate anzuzeigen"],
    ["Předchozí rok", "Predchádzajúci rok", "Previous year", "Vorheriges Jahr"],
    ["Následující rok", "Nasledujúci rok", "Next year", "Nächstes Jahr"],
    ["Zvětšit graf", "Zväčšiť graf", "Expand chart", "Diagramm vergrößern"],
    ["Sbalit graf", "Zbaliť graf", "Collapse chart", "Diagramm einklappen"],
    ["Skrýt graf", "Skryť graf", "Hide chart", "Diagramm ausblenden"],
    ["Ovládání grafu", "Ovládanie grafu", "Chart controls", "Diagrammsteuerung"],
    ["Částka", "Suma", "Amount", "Betrag"],
    ["Středisko", "Stredisko", "Location", "Standort"],
    ["Bez poznámky", "Bez poznámky", "No note", "Keine Notiz"],
    ["tis.", "tis.", "k", "Tsd."],
    ["mil.", "mil.", "M", "Mio."],
    ["Adresář klientů", "Adresár klientov", "Customer directory", "Kundenverzeichnis"],
    ["Rychlý kontakt", "Rýchly kontakt", "Quick contact", "Schnellkontakt"],
    ["klientů", "klientov", "clients", "Kunden"],
    ["Hledej...", "Hľadať...", "Search...", "Suchen..."],
    ["Leden", "Január", "January", "Januar"],
    ["Únor", "Február", "February", "Februar"],
    ["Březen", "Marec", "March", "März"],
    ["Duben", "Apríl", "April", "April"],
    ["Květen", "Máj", "May", "Mai"],
    ["Červen", "Jún", "June", "Juni"],
    ["Červenec", "Júl", "July", "Juli"],
    ["Srpen", "August", "August", "August"],
    ["Září", "September", "September", "September"],
    ["Říjen", "Október", "October", "Oktober"],
    ["Listopad", "November", "November", "November"],
    ["Prosinec", "December", "December", "Dezember"],
    ["Změnit jazyk", "Zmeniť jazyk", "Change language", "Sprache ändern"]
  ];

  // Annual revenue labels.
  rows = rows.concat([["Celkové roční tržby dle obsluhy","Celkové ročné tržby podľa obsluhy","Annual revenue by staff member","Jahresumsatz nach Mitarbeitern"],["Přehled Vašich celkových ročních tržeb dle obsluhy za pobočku:","Prehľad celkových ročných tržieb podľa obsluhy za pobočku:","Overview of annual staff revenue for branch:","Übersicht der jährlichen Mitarbeiterumsätze für die Filiale:"],["Roční celkové tržby za obsluhu","Ročné celkové tržby za obsluhu","Annual revenue by staff member","Jahresumsatz nach Mitarbeitern"],["Přehled tržeb za vybraný rok","Prehľad tržieb za vybraný rok","Revenue for the selected year","Umsatz im ausgewählten Jahr"],["Rozdělení za vybraný rok","Rozdelenie za vybraný rok","Breakdown for the selected year","Verteilung im ausgewählten Jahr"],["Pro vybraný rok nejsou k dispozici žádné záznamy.","Pre vybraný rok nie sú k dispozícii žiadne záznamy.","No records are available for the selected year.","Für das ausgewählte Jahr sind keine Einträge vorhanden."],["Služby a prodej po měsících","Služby a predaj po mesiacoch","Services and sales by month","Dienstleistungen und Verkäufe nach Monat"],["Historie v čase za jednotlivce","Vývoj v čase za jednotlivcov","Monthly revenue for selected staff member","Monatlicher Umsatz des ausgewählten Mitarbeiters"],["Historie tržeb za služby v čase","Vývoj tržieb za služby v čase","Service revenue by month","Dienstleistungsumsatz nach Monat"],["Historie prodeje v čase","Vývoj predaja v čase","Sales revenue by month","Verkaufsumsatz nach Monat"],["Počty účtenek za jednotlivá střediska","Počty účteniek za jednotlivé strediská","Receipts by business unit","Belege nach Betrieb"],["Celkem služby","Služby celkom","Total services","Dienstleistungen gesamt"],["Celkem prodej","Predaj celkom","Total sales","Verkäufe gesamt"],["Účtenky celkem","Účtenky celkom","Total receipts","Belege gesamt"],["Žádná obsluha","Žiadna obsluha","No staff members","Keine Mitarbeiter"],["Refresh dat pro tento rok","Obnoviť údaje pre tento rok","Refresh data for this year","Daten für dieses Jahr aktualisieren"],["Smazat data pro tento rok","Vymazať údaje pre tento rok","Delete data for this year","Daten für dieses Jahr löschen"],["Opravdu chcete SMAZAT data středisek a jednotlivců pro tento rok?","Naozaj chcete VYMAZAŤ údaje stredísk a jednotlivcov pre tento rok?","Do you really want to DELETE business unit and staff data for this year?","Möchten Sie die Betriebs- und Mitarbeiterdaten für dieses Jahr wirklich LÖSCHEN?"],["Váš požadavek na refresh dat pro tento rok byl zadán","Vaša požiadavka na obnovenie údajov pre tento rok bola zadaná","Your data refresh request for this year has been submitted","Ihre Anfrage zur Datenaktualisierung für dieses Jahr wurde übermittelt"],["TOP za služby","TOP za služby","Top month for services","Bester Monat für Dienstleistungen"],["TOP za prodej","TOP za predaj","Top month for sales","Bester Monat für Verkäufe"]]);

  // Lifetime revenue labels.
  rows = rows.concat([["Přehled tržeb za celé období","Prehľad tržieb za celé obdobie","Revenue for the entire period","Umsatz im gesamten Zeitraum"],["Rozdělení za celé období","Rozdelenie za celé obdobie","Breakdown for the entire period","Verteilung im gesamten Zeitraum"],["Celé období","Celé obdobie","Entire period","Gesamter Zeitraum"],["Pro celé období nejsou k dispozici žádné záznamy.","Pre celé obdobie nie sú k dispozícii žiadne záznamy.","No records are available for this period.","Für diesen Zeitraum sind keine Einträge vorhanden."],["Tržby v jednotlivých letech","Tržby v jednotlivých rokoch","Revenue by year","Umsatz nach Jahr"],["Služby v jednotlivých letech","Služby v jednotlivých rokoch","Service revenue by year","Dienstleistungsumsatz nach Jahr"],["Prodej v jednotlivých letech","Predaj v jednotlivých rokoch","Sales revenue by year","Verkaufsumsatz nach Jahr"],["Účtenky za jednotlivé roky","Účtenky za jednotlivé roky","Receipts by year","Belege nach Jahr"],["Meziroční vývoj tržeb","Medziročný vývoj tržieb","Year-over-year revenue","Umsatzentwicklung im Jahresvergleich"],["Změna služeb","Zmena služieb","Service revenue change","Änderung der Dienstleistungsumsätze"],["Změna prodeje","Zmena predaja","Sales revenue change","Änderung der Verkaufsumsätze"],["Změna vůči předchozímu roku; při nulovém základu se procento neuvádí.","Zmena oproti predchádzajúcemu roku; pri nulovom základe sa percento neuvádza.","Change from the previous year; percentages are omitted when the baseline is zero.","Änderung zum Vorjahr; bei einem Ausgangswert von null wird kein Prozentsatz angegeben."],["TOP rok za služby","TOP rok za služby","Top year for services","Bestes Jahr für Dienstleistungen"],["TOP rok za prodej","TOP rok za predaj","Top year for sales","Bestes Jahr für Verkäufe"],["Tržby se nepodařilo načíst. Zkuste stránku znovu načíst.","Tržby sa nepodarilo načítať. Skúste stránku znovu načítať.","Revenue could not be loaded. Please reload the page.","Die Umsätze konnten nicht geladen werden. Bitte laden Sie die Seite erneut."],["Smazat kompletní data vašich tržeb","Vymazať kompletné údaje vašich tržieb","Delete all revenue data","Alle Umsatzdaten löschen"],["Opravdu chcete SMAZAT kompletní data vašich tržeb?","Naozaj chcete VYMAZAŤ kompletné údaje vašich tržieb?","Do you really want to DELETE all your revenue data?","Möchten Sie wirklich alle Ihre Umsatzdaten LÖSCHEN?"]]);

  rows = rows.concat([["Tržby od počátku věků","Tržby od počiatku","All-time revenue","Gesamtumsatz seit Beginn"],["Celkový sumář za střediska | firmy","Celkový sumár za strediská | firmy","All-time revenue by location","Gesamtumsatz nach Betrieb"],["Celkové tržby za obsluhu","Celkové tržby za obsluhu","All-time revenue by staff member","Gesamtumsatz nach Mitarbeitern"],["Služby","Služby","Services","Dienstleistungen"],["Prodej","Predaj","Retail","Verkäufe"]]);
  rows = rows.concat([["Automatické načítání dat","Automatické načítanie údajov","Automatic data loading","Automatisches Laden der Daten"],["Každých 15 minut","Každých 15 minút","Every 15 minutes","Alle 15 Minuten"]]);
  rows = rows.concat([["Hledat podle názvu nebo PLU…","Hľadať podľa názvu alebo PLU…","Search by name or PLU…","Nach Name oder PLU suchen…"]]);
  rows = rows.concat([["Přehled skladu","Prehľad skladu","Inventory overview","Lagerübersicht"],["Přehled aktuálního pohybu na skladě","Prehľad aktuálneho pohybu na sklade","Current inventory movements","Aktuelle Lagerbewegungen"],["Výběr skladu","Výber skladu","Select warehouse","Lager auswählen"],["Výchozí sklad","Predvolený sklad","Default warehouse","Standardlager"],["Aktuální stav zásob","Aktuálny stav zásob","Current inventory","Aktueller Lagerbestand"],["Seznam chybějícího zboží","Zoznam chýbajúceho tovaru","Low stock products","Artikel mit niedrigem Bestand"],["Množství","Množstvo","Quantity","Menge"],["Počáteční stav","Počiatočný stav","Opening quantity","Anfangsbestand"],["Příjem na sklad","Príjem na sklad","Received","Wareneingang"],["Spotřeba","Spotreba","Used","Verbrauch"],["Konečný stav","Konečný stav","Closing quantity","Endbestand"],["Minimální množství","Minimálne množstvo","Minimum quantity","Mindestmenge"],["Aktuální hodnota skladu","Aktuálna hodnota skladu","Current inventory value","Aktueller Lagerwert"],["Smazat informace o skladu","Vymazať informácie o sklade","Delete inventory information","Lagerinformationen löschen"],["Smazat kompletní stav skladu","Vymazať kompletný stav skladu","Delete entire inventory status","Gesamten Lagerbestand löschen"]]);
  // V127 voucher translations
  rows = rows.concat([["Nová platnost voucheru", "Nová platnosť voucheru", "New voucher validity", "Neue Gutscheingültigkeit"], ["Kopírovat", "Kopírovať", "Copy", "Kopieren"], ["Akce", "Akcia", "Action", "Aktion"], ["Hledat voucher…", "Hľadať voucher…", "Search vouchers…", "Gutscheine suchen…"], ["Přehled Voucherů", "Prehľad voucherov", "Voucher overview", "Gutscheinübersicht"], ["Historie vybraného voucheru", "História vybraného voucheru", "Voucher history", "Gutscheinverlauf"], ["Zneplatněné vouchery", "Zneplatnené vouchery", "Invalidated vouchers", "Ungültige Gutscheine"], ["Zobrazit zneplatněné Vouchery", "Zobraziť zneplatnené vouchery", "Show invalidated vouchers", "Ungültige Gutscheine anzeigen"], ["Zpět na přehled Voucherů", "Späť na prehľad voucherov", "Back to vouchers", "Zurück zur Gutscheinübersicht"], ["Editace zůstatkové ceny", "Úprava zostatkovej hodnoty", "Edit remaining balance", "Restguthaben bearbeiten"], ["Editace platnosti voucheru", "Úprava platnosti voucheru", "Edit voucher validity", "Gutscheingültigkeit bearbeiten"], ["Editace platnosti", "Úprava platnosti", "Edit validity", "Gültigkeit bearbeiten"], ["Uložit novou zůstatkovou cenu", "Uložiť novú zostatkovú hodnotu", "Save remaining balance", "Restguthaben speichern"], ["Uložit novou platnost", "Uložiť novú platnosť", "Save validity", "Gültigkeit speichern"], ["Smazat ceninu", "Vymazať ceninu", "Delete voucher", "Gutschein löschen"], ["Smazat všechny vouchery", "Vymazať všetky vouchery", "Delete all vouchers", "Alle Gutscheine löschen"], ["Smazat neplatné ceniny", "Vymazať neplatné ceniny", "Delete invalidated vouchers", "Ungültige Gutscheine löschen"], ["Graf čerpání", "Graf čerpania", "Voucher usage", "Gutscheineinlösung"], ["Vystaveno voucherů", "Vystavených voucherov", "Vouchers issued", "Ausgestellte Gutscheine"], ["Celková hodnota", "Celková hodnota", "Total value", "Gesamtwert"], ["Zůstatek", "Zostatok", "Balance", "Restguthaben"], ["Čerpáno", "Čerpané", "Redeemed", "Eingelöst"], ["Nečerpáno", "Nečerpané", "Unredeemed", "Nicht eingelöst"], ["Částečně", "Čiastočne", "Partially", "Teilweise"], ["Vyčerpáno", "Vyčerpané", "Fully redeemed", "Vollständig eingelöst"], ["Expirováno", "Expirované", "Expired", "Abgelaufen"], ["Částečně čerpáno", "Čiastočne čerpané", "Partially redeemed", "Teilweise eingelöst"], ["Částečně čerpaný", "Čiastočne čerpaný", "Partially redeemed", "Teilweise eingelöst"], ["Vyčerpaný", "Vyčerpaný", "Fully redeemed", "Vollständig eingelöst"], ["V oběhu", "V obehu", "In circulation", "Im Umlauf"], ["Neplatný", "Neplatný", "Invalid", "Ungültig"], ["Prodáno", "Predané", "Sold", "Verkauft"], ["Kód", "Kód", "Code", "Code"], ["Hodnota", "Hodnota", "Value", "Wert"], ["Datum prodeje", "Dátum predaja", "Sale date", "Verkaufsdatum"], ["Prodejce", "Predajca", "Seller", "Verkäufer"], ["Placeno", "Platené", "Payment", "Zahlung"], ["Realizoval", "Realizoval", "Processed by", "Bearbeitet von"], ["Položka", "Položka", "Item", "Artikel"], ["Před.čerpání", "Predch. čerpanie", "Previously redeemed", "Zuvor eingelöst"], ["Platnost", "Platnosť", "Validity", "Gültigkeit"], ["Do data", "Do dátumu", "Valid until", "Gültig bis"], ["Datum čerpání", "Dátum čerpania", "Redemption date", "Einlösedatum"], ["Sumář", "Sumár", "Summary", "Zusammenfassung"], ["Sazba", "Sadzba", "Tax rate", "Steuersatz"], ["Základ", "Základ", "Tax base", "Steuerbasis"], ["Filtr", "Filter", "Filter", "Filter"], ["Aplikovat filtr", "Použiť filter", "Apply filter", "Filter anwenden"], ["Zrušit filtr", "Zrušiť filter", "Clear filter", "Filter zurücksetzen"], ["PDF Export", "PDF export", "PDF export", "PDF-Export"], ["Od", "Od", "From", "Von"], ["Do", "Do", "To", "Bis"], ["Aktuální zůstatková cena", "Aktuálna zostatková hodnota", "Current remaining balance", "Aktuelles Restguthaben"], ["Poslední změna:", "Posledná zmena:", "Last change:", "Letzte Änderung:"], ["Zůstatek v \"Depozitu\"", "Zostatok v \"Depozite\"", "Deposit balance", "Depotguthaben"], ["Cenin v oběhu", "Cenín v obehu", "Vouchers in circulation", "Gutscheine im Umlauf"], ["Přehled Vašich voucherů", "Prehľad Vašich voucherov", "Your voucher overview", "Ihre Gutscheinübersicht"], ["Přehled historie vybraného voucheru", "Prehľad histórie vybraného voucheru", "Selected voucher history", "Verlauf des ausgewählten Gutscheins"], ["Opravdu chcete SMAZAT všechny vouchery? Tato akce je nevratná!", "Naozaj chcete VYMAZAŤ všetky vouchery? Táto akcia je nevratná!", "Delete all vouchers? This action cannot be undone!", "Alle Gutscheine LÖSCHEN? Diese Aktion kann nicht rückgängig gemacht werden!"], ["Opravdu chcete SMAZAT tuto ceninu i její historii? Tato akce je nevratná!", "Naozaj chcete VYMAZAŤ túto ceninu aj jej históriu? Táto akcia je nevratná!", "Delete this voucher and its history? This action cannot be undone!", "Diesen Gutschein und seinen Verlauf LÖSCHEN? Diese Aktion kann nicht rückgängig gemacht werden!"], ["Opravdu chcete SMAZAT všechny tyto neplatné ceniny i jejich historii? Tato akce je nevratná!", "Naozaj chcete VYMAZAŤ všetky tieto neplatné ceniny aj ich históriu? Táto akcia je nevratná!", "Delete all these invalidated vouchers and their history? This action cannot be undone!", "Alle diese ungültigen Gutscheine und ihren Verlauf LÖSCHEN? Diese Aktion kann nicht rückgängig gemacht werden!"]]);
  rows = rows.concat([["Posun tabulky", "Posun tabuľky", "Scroll table", "Tabelle verschieben"]]);
  // V129 calendar and voucher labels
  rows = rows.concat([["Vše", "Všetko", "All", "Alle"], ["Všechny pobočky", "Všetky pobočky", "All branches", "Alle Filialen"], ["Aplikován filtr data:", "Použitý filter dátumu:", "Date filter applied:", "Datumsfilter angewendet:"], ["Cenina byla úspěšně smazána.", "Cenina bola úspešne vymazaná.", "The voucher was deleted successfully.", "Der Gutschein wurde erfolgreich gelöscht."], ["Neplatné ceniny byly úspěšně smazány.", "Neplatné ceniny boli úspešne vymazané.", "The invalidated vouchers were deleted successfully.", "Die ungültigen Gutscheine wurden erfolgreich gelöscht."], ["Hotově", "V hotovosti", "Cash", "Barzahlung"], ["Bankovní převod", "Bankový prevod", "Bank transfer", "Banküberweisung"], ["QR Platba", "QR platba", "QR payment", "QR-Zahlung"], ["Kartou", "Kartou", "Card", "Kartenzahlung"]]);
  // V130 voucher summary
  rows = rows.concat([["Přehled", "Prehľad", "Overview", "Übersicht"], ["Zůstatek v ''Depozitu''", "Zostatok v „Depozite“", "Deposit balance", "Depotguthaben"]]);
  // V131 loading status
  rows = rows.concat([["Načítání…", "Načítavanie…", "Loading…", "Wird geladen…"]]);
  // V175: Číselník pojišťoven.
  rows = rows.concat([
    ["Nastavení číselníku pojišťoven","Nastavenie číselníka poisťovní","Insurance company settings","Krankenkassen-Verzeichnis"],
    ["Správa zdravotních pojišťoven","Správa zdravotných poisťovní","Manage health insurance providers","Krankenkassen verwalten"],
    ["Seznam pojišťoven","Zoznam poisťovní","Insurance companies","Krankenkassen"],
    ["Přehled pojišťoven používaných v kartách zákazníků","Prehľad poisťovní používaných v kartách zákazníkov","Insurance providers used in customer records","Krankenkassen in Kundenkarteien"],
    ["Přidat pojišťovnu","Pridať poisťovňu","Add insurance provider","Krankenkasse hinzufügen"],
    ["Editace pojišťovny","Úprava poisťovne","Edit insurance provider","Krankenkasse bearbeiten"],
    ["Nová pojišťovna","Nová poisťovňa","New insurance provider","Neue Krankenkasse"],
    ["Upravte údaje vybrané pojišťovny","Upravte údaje vybranej poisťovne","Edit the selected insurance provider","Daten der ausgewählten Krankenkasse bearbeiten"],
    ["Zadejte údaje nové pojišťovny","Zadajte údaje novej poisťovne","Enter the new insurance provider details","Daten der neuen Krankenkasse eingeben"],
    ["Jméno pojišťovny","Názov poisťovne","Insurance provider name","Name der Krankenkasse"],
    ["Kód pojišťovny","Kód poisťovne","Insurance provider code","Krankenkassencode"],
    ["Zkratka","Skratka","Abbreviation","Abkürzung"],
    ["Stát","Štát","Country","Land"],
    ["Uložit pojišťovnu","Uložiť poisťovňu","Save insurance provider","Krankenkasse speichern"],
    ["Editovat pojišťovnu","Upraviť poisťovňu","Edit insurance provider","Krankenkasse bearbeiten"],
    ["Smazat pojišťovnu","Odstrániť poisťovňu","Delete insurance provider","Krankenkasse löschen"],
    ["Opravdu chcete SMAZAT tuto pojišťovnu?","Naozaj chcete ODSTRÁNIŤ túto poisťovňu?","Do you really want to DELETE this insurance provider?","Möchten Sie diese Krankenkasse wirklich LÖSCHEN?"],
    ["Pojišťovna byla smazána.","Poisťovňa bola odstránená.","The insurance provider was deleted.","Die Krankenkasse wurde gelöscht."],
    ["Pojišťovna byla editována.","Poisťovňa bola upravená.","The insurance provider was updated.","Die Krankenkasse wurde aktualisiert."],
    ["Pojišťovna byla založena.","Poisťovňa bola vytvorená.","The insurance provider was created.","Die Krankenkasse wurde angelegt."],
    ["Hledat v pojišťovnách…","Hľadať v poisťovniach…","Search insurance providers…","Krankenkassen suchen…"],
    ["Žádné pojišťovny","Žiadne poisťovne","No insurance providers","Keine Krankenkassen"],
    ["Strana _PAGE_ z _PAGES_","Strana _PAGE_ z _PAGES_","Page _PAGE_ of _PAGES_","Seite _PAGE_ von _PAGES_"],
    ["(Celkem _MAX_ záznamů)","(Celkom _MAX_ záznamov)","(_MAX_ records total)","(_MAX_ Einträge insgesamt)"]
  ]);

  // V177: Nastavení > Přístupy.
  rows = rows.concat([
    ["Přehled přístupů","Prehľad prístupov","Access overview","Zugriffsübersicht"],
    ["Přehled aktuálních přístupů do systému","Prehľad aktuálnych prístupov do systému","Current system access overview","Übersicht der aktuellen Systemzugriffe"],
    ["Seznam uživatelů","Zoznam používateľov","Users","Benutzer"],
    ["Správa administrátora a manažerů s přístupem do HairSoft","Správa administrátora a manažérov s prístupom do HairSoft","Manage administrators and managers with HairSoft access","Administratoren und Manager mit HairSoft-Zugriff verwalten"],
    ["Jméno uživatele","Meno používateľa","User name","Benutzername"],
    ["Administrátor","Administrátor","Administrator","Administrator"],
    ["Manažer","Manažér","Manager","Manager"],
    ["Hledat v přístupech…","Hľadať v prístupoch…","Search access…","Zugriffe suchen…"],
    ["Žádní uživatelé","Žiadni používatelia","No users","Keine Benutzer"],
    ["Profil manažera","Profil manažéra","Manager profile","Managerprofil"],
    ["Vyberte manažera a spravujte jeho profilovou fotografii","Vyberte manažéra a spravujte jeho profilovú fotografiu","Select a manager and manage their profile photo","Manager auswählen und Profilbild verwalten"],
    ["Výběr manažera","Výber manažéra","Select manager","Manager auswählen"],
    ["Nahrát profilovku","Nahrať profilovú fotografiu","Upload profile photo","Profilbild hochladen"],
    ["Změnit profilovku","Zmeniť profilovú fotografiu","Change profile photo","Profilbild ändern"],
    ["Smazat profilovku","Odstrániť profilovú fotografiu","Delete profile photo","Profilbild löschen"],
    ["Opravdu chcete smazat profilovou fotografii?","Naozaj chcete odstrániť profilovú fotografiu?","Do you really want to delete the profile photo?","Möchten Sie das Profilbild wirklich löschen?"],
    ["Nový manažer","Nový manažér","New manager","Neuer Manager"],
    ["Vytvořte další přístup do HairSoft","Vytvorte ďalší prístup do HairSoft","Create another HairSoft access","Weiteren HairSoft-Zugang erstellen"],
    ["Jméno manažera","Meno manažéra","Manager name","Managername"],
    ["Zadejte platný email","Zadajte platný e-mail","Enter a valid email","Gültige E-Mail eingeben"],
    ["Min 4 znaky","Min. 4 znaky","At least 4 characters","Mindestens 4 Zeichen"],
    ["Přidat uživatele","Pridať používateľa","Add user","Benutzer hinzufügen"],
    ["Nejprve vytvořte manažera. Poté zde můžete nastavit jeho profilovou fotografii.","Najprv vytvorte manažéra. Potom tu môžete nastaviť jeho profilovú fotografiu.","Create a manager first. You can then set their profile photo here.","Erstellen Sie zuerst einen Manager. Anschließend können Sie hier das Profilbild festlegen."],
    ["Potvrdit smazání uživatele","Potvrdiť odstránenie používateľa","Confirm user deletion","Benutzerlöschung bestätigen"],
    ["Smazat uživatele","Odstrániť používateľa","Delete user","Benutzer löschen"],
    ["Opravdu chcete smazat tohoto manažera? Přístup bude trvale odstraněn.","Naozaj chcete odstrániť tohto manažéra? Prístup bude trvalo odstránený.","Do you really want to delete this manager? Access will be permanently removed.","Möchten Sie diesen Manager wirklich löschen? Der Zugriff wird dauerhaft entfernt."],
    ["Detail uživatele","Detail používateľa","User details","Benutzerdetails"],
    ["Detail uživatele - podrobné nastavení.","Detail používateľa - podrobné nastavenie.","User details - advanced settings.","Benutzerdetails - erweiterte Einstellungen."],
    ["Zpět na přístupy","Späť na prístupy","Back to access","Zurück zu Zugriffe"],
    ["Výběr pobočky","Výber pobočky","Select branch","Filiale auswählen"],
    ["Práva se nastavují samostatně pro každou pobočku.","Práva sa nastavujú samostatne pre každú pobočku.","Permissions are configured separately for each branch.","Berechtigungen werden für jede Filiale separat festgelegt."],
    ["Změna hesla uživatele","Zmena hesla používateľa","Change user password","Benutzerpasswort ändern"],
    ["Nastavte nové přihlašovací heslo manažera.","Nastavte nové prihlasovacie heslo manažéra.","Set a new sign-in password for the manager.","Neues Anmeldepasswort für den Manager festlegen."],
    ["Nové heslo","Nové heslo","New password","Neues Passwort"],
    ["Změnit heslo","Zmeniť heslo","Change password","Passwort ändern"],
    ["Změna nového hesla byla úspěšně provedena.","Nové heslo bolo úspešne zmenené.","The password was changed successfully.","Das Passwort wurde erfolgreich geändert."],
    ["Práva na menu","Práva na menu","Menu permissions","Menüberechtigungen"],
    ["Povolené části systému pro vybranou pobočku","Povolené časti systému pre vybranú pobočku","Allowed system sections for the selected branch","Erlaubte Systembereiche für die ausgewählte Filiale"],
    ["Povoleno","Povolené","Allowed","Erlaubt"],
    ["Zakázáno","Zakázané","Not allowed","Nicht erlaubt"],
    ["Nastavení administrátora","Nastavenie administrátora","Administrator settings","Administratoreinstellungen"],
    ["Změna přihlašovacího hesla hlavního administrátora.","Zmena prihlasovacieho hesla hlavného administrátora.","Change the main administrator sign-in password.","Anmeldepasswort des Hauptadministrators ändern."],
    ["Zpět na seznam","Späť na zoznam","Back to list","Zurück zur Liste"],
    ["Hlavní administrátor","Hlavný administrátor","Main administrator","Hauptadministrator"],
    ["Změna hesla administrátora","Zmena hesla administrátora","Change administrator password","Administratorpasswort ändern"],
    ["Nastavte nové přihlašovací heslo administrátora.","Nastavte nové prihlasovacie heslo administrátora.","Set a new sign-in password for the administrator.","Neues Anmeldepasswort für den Administrator festlegen."],
    ["Heslo bude po změně odesláno také na e-mail administrátora.","Heslo bude po zmene odoslané aj na e-mail administrátora.","After the change, the password will also be sent to the administrator email.","Nach der Änderung wird das Passwort auch an die E-Mail-Adresse des Administrators gesendet."]
  ]);

  function key(value) {
    return String(value).replace(/\u00a0/g, " ").replace(/\s+/g, " ").trim();
  }

  // V145: Incoming calls / Incoming SMS / Outgoing SMS.
  rows = rows.concat([
    ["Přehled příchozích hovorů","Prehľad prichádzajúcich hovorov","Incoming calls overview","Übersicht eingehender Anrufe"],
    ["Přehled příchozích SMS","Prehľad prichádzajúcich SMS","Incoming SMS overview","Übersicht eingehender SMS"],
    ["Přehled ochozích SMS","Prehľad odchádzajúcich SMS","Outgoing SMS overview","Übersicht ausgehender SMS"],
    ["Přehled odchozích SMS","Prehľad odchádzajúcich SMS","Outgoing SMS overview","Übersicht ausgehender SMS"],
    ["Přehled hovorů","Prehľad hovorov","Call overview","Anrufübersicht"],
    ["Přehled SMS","Prehľad SMS","SMS overview","SMS-Übersicht"],
    ["Příchozí hovory dnes","Prichádzajúce hovory dnes","Incoming calls today","Eingehende Anrufe heute"],
    ["Příchozí hovory za týden","Prichádzajúce hovory za týždeň","Incoming calls in the last 7 days","Eingehende Anrufe in den letzten 7 Tagen"],
    ["Příchozí hovory za měsíc","Prichádzajúce hovory za mesiac","Incoming calls this month","Eingehende Anrufe diesen Monat"],
    ["Příchozí hovory celkem","Prichádzajúce hovory celkom","Total incoming calls","Eingehende Anrufe gesamt"],
    ["Příchozí SMS za týden","Prichádzajúce SMS za týždeň","Incoming SMS in the last 7 days","Eingehende SMS in den letzten 7 Tagen"],
    ["Příchozí SMS za měsíc","Prichádzajúce SMS za mesiac","Incoming SMS this month","Eingehende SMS diesen Monat"],
    ["Příchozí SMS celkem","Prichádzajúce SMS celkom","Total incoming SMS","Eingehende SMS gesamt"],
    ["Odchozí SMS dnes","Odchádzajúce SMS dnes","Outgoing SMS today","Ausgehende SMS heute"],
    ["Ochozí SMS za týden","Odchádzajúce SMS za týždeň","Outgoing SMS in the last 7 days","Ausgehende SMS in den letzten 7 Tagen"],
    ["Odchozí SMS za týden","Odchádzajúce SMS za týždeň","Outgoing SMS in the last 7 days","Ausgehende SMS in den letzten 7 Tagen"],
    ["Odchozí SMS za měsíc","Odchádzajúce SMS za mesiac","Outgoing SMS this month","Ausgehende SMS diesen Monat"],
    ["Odchozí SMS celkem","Odchádzajúce SMS celkom","Total outgoing SMS","Ausgehende SMS gesamt"],
    ["Datum hovoru","Dátum hovoru","Call date","Anrufdatum"],
    ["Datum SMS","Dátum SMS","SMS date","SMS-Datum"],
    ["Zákazník","Zákazník","Customer","Kunde"],
    ["Reakce na","Reakcia na","Reply to","Antwort auf"],
    ["Přijato do systému","Prijaté do systému","Received by system","Im System eingegangen"],
    ["Text","Text","Text","Text"],
    ["Šablona","Šablóna","Template","Vorlage"],
    ["Kdy odeslat","Kedy odoslať","Scheduled send","Sendezeit"],
    ["Odesláno","Odoslané","Sent","Gesendet"],
    ["Stav","Stav","Status","Status"],
    ["Počet SMS","Počet SMS","SMS count","SMS-Anzahl"],
    ["Hledat v hovorech…","Hľadať v hovoroch…","Search calls…","Anrufe suchen…"],
    ["Hledat v příchozích SMS…","Hľadať v prichádzajúcich SMS…","Search incoming SMS…","Eingehende SMS suchen…"],
    ["Hledat v odchozích SMS…","Hľadať v odchádzajúcich SMS…","Search outgoing SMS…","Ausgehende SMS suchen…"],
    ["záznamů na stranu","záznamov na stranu","records per page","Einträge pro Seite"]
  ]);

  // V147–V148: Users / staff access management.
  rows = rows.concat([
    ["Nastavení uživatelů", "Nastavenie používateľov", "User settings", "Benutzereinstellungen"],
    ["Správa uživatelů, přístupů a oprávnění", "Správa používateľov, prístupov a oprávnení", "Manage users, access and permissions", "Benutzer, Zugänge und Berechtigungen verwalten"],
    ["Výběr pobočky", "Výber pobočky", "Select branch", "Filiale auswählen"],
    ["Profilovka", "Profilová fotografia", "Profile photo", "Profilbild"],
    ["Přístup obsluhy", "Prístup obsluhy", "Staff access", "Mitarbeiterzugang"],
    ["Detail obsluhy", "Detail obsluhy", "Staff detail", "Mitarbeiterdetails"],
    ["Přístupové údaje", "Prístupové údaje", "Access details", "Zugangsdaten"],
    ["Nastavení hodnocení", "Nastavenie hodnotenia", "Rating settings", "Bewertungseinstellungen"],
    ["Oprávnění obsluhy", "Oprávnenia obsluhy", "Staff permissions", "Mitarbeiterberechtigungen"],
    ["Akce pro obsluhu", "Akcie pre obsluhu", "Staff actions", "Mitarbeiteraktionen"],
    ["Práva na menu pro obsluhu", "Práva menu pre obsluhu", "Staff menu permissions", "Menüberechtigungen für Mitarbeiter"],
    ["Login / Jméno uživatele", "Login / Meno používateľa", "Login / User name", "Login / Benutzername"],
    ["Min 4 znaky", "Min. 4 znaky", "Min. 4 characters", "Mind. 4 Zeichen"],
    ["Uložit údaje", "Uložiť údaje", "Save details", "Daten speichern"],
    ["Zobrazit hodnocení v programu HairSoft", "Zobraziť hodnotenie v programe HairSoft", "Show ratings in HairSoft", "Bewertungen in HairSoft anzeigen"],
    ["Zobrazit hodnocení všech obsluh", "Zobraziť hodnotenie všetkých obslúh", "Show ratings for all staff", "Bewertungen aller Mitarbeiter anzeigen"],
    ["Anonymizace citlivých dat", "Anonymizácia citlivých údajov", "Sensitive data anonymization", "Anonymisierung sensibler Daten"],
    ["Dashboard", "Dashboard", "Dashboard", "Dashboard"],
    ["Nový zákazník", "Nový zákazník", "New customer", "Neuer Kunde"],
    ["Zákazníci", "Zákazníci", "Customers", "Kunden"],
    ["Rezervace", "Rezervácie", "Bookings", "Reservierungen"],
    ["Tržby", "Tržby", "Revenue", "Umsatz"],
    ["Sklad", "Sklad", "Inventory", "Lager"],
    ["Voucher", "Voucher", "Voucher", "Gutschein"],
    ["Hodnocení", "Hodnotenie", "Ratings", "Bewertungen"],
    ["SMS a hovory", "SMS a hovory", "SMS and calls", "SMS und Anrufe"],
    ["Kamery", "Kamery", "Cameras", "Kameras"],
    ["Nastavení", "Nastavenie", "Settings", "Einstellungen"],
    ["Ceník", "Cenník", "Price list", "Preisliste"],
    ["Seznam obsluh", "Zoznam obslúh", "Staff list", "Mitarbeiterliste"],
    ["Seznam archivovaných obsluh", "Zoznam archivovaných obslúh", "Archived staff", "Archivierte Mitarbeiter"],
    ["Detail", "Detail", "Details", "Details"],
    ["Jméno obsluhy", "Meno obsluhy", "Staff member", "Mitarbeiter"],
    ["Práva obsluhy", "Práva obsluhy", "Permissions", "Berechtigungen"],
    ["Smazat obsluhu", "Odstrániť obsluhu", "Delete staff member", "Mitarbeiter löschen"],
    ["Přesunout do archivu", "Presunúť do archívu", "Move to archive", "Ins Archiv verschieben"],
    ["Archivovat", "Archivovať", "Archive", "Archivieren"],
    ["Obnovit z archivu", "Obnoviť z archívu", "Restore from archive", "Aus Archiv wiederherstellen"],
    ["Obnovit", "Obnoviť", "Restore", "Wiederherstellen"],
    ["Přesunout obsluhu do archivu", "Presunúť obsluhu do archívu", "Move staff member to archive", "Mitarbeiter archivieren"],
    ["Obnovit obsluhu z archivu", "Obnoviť obsluhu z archívu", "Restore staff member from archive", "Mitarbeiter aus Archiv wiederherstellen"],
    ["Import obsluh z HairSoft", "Import obslúh z HairSoft", "Import staff from HairSoft", "Mitarbeiter aus HairSoft importieren"],
    ["Hledat v obsluhách…", "Hľadať v obsluhe…", "Search staff…", "Mitarbeiter suchen…"],
    ["Hledat v archivovaných obsluhách…", "Hľadať v archivovaných obsluhách…", "Search archived staff…", "Archivierte Mitarbeiter suchen…"],
    ["Bez oprávnění", "Bez oprávnení", "No permissions", "Keine Berechtigungen"],
    ["Opravdu chcete SMAZAT tuto osobu? Osoba bude trvale vymazána ze systému!", "Naozaj chcete VYMAZAŤ túto osobu? Osoba bude natrvalo odstránená zo systému!", "Delete this person permanently from the system?", "Diese Person dauerhaft aus dem System löschen?"],
    ["Opravdu chcete obsluhu přesunout do archivu?", "Naozaj chcete obsluhu presunúť do archívu?", "Move this staff member to the archive?", "Diesen Mitarbeiter archivieren?"],
    ["Opravdu chcete obnovit tuto osobu z archivu?", "Naozaj chcete obnoviť túto osobu z archívu?", "Restore this person from the archive?", "Diese Person aus dem Archiv wiederherstellen?"],
    ["Toto přihlašovací jméno již existuje. Zvolte jiné.", "Toto prihlasovacie meno už existuje. Zvoľte iné.", "This login already exists. Choose another one.", "Dieser Login existiert bereits. Wählen Sie einen anderen."],
    ["Změna loginu a hesla byla úspěšně provedena.", "Zmena loginu a hesla bola úspešne vykonaná.", "Login and password were updated successfully.", "Login und Passwort wurden erfolgreich geändert."],
    ["Změna nového hesla byla úspěšně provedena.", "Zmena nového hesla bola úspešne vykonaná.", "The new password was saved successfully.", "Das neue Passwort wurde erfolgreich gespeichert."]
  ]);

  // V159: Attendance.
  rows = rows.concat([
    ["Přehled docházky","Prehľad dochádzky","Attendance overview","Zeiterfassungsübersicht"],
    ["Výběr docházky","Výber dochádzky","Attendance selection","Zeiterfassung auswählen"],
    ["Načtení dat z HairSoft","Načítanie údajov z HairSoft","Load data from HairSoft","Daten aus HairSoft laden"],
    ["Načtená data","Načítané údaje","Loaded data","Geladene Daten"],
    ["Měsíc","Mesiac","Month","Monat"],
    ["Rok","Rok","Year","Jahr"],
    ["Refresh z PC","Obnoviť z PC","Refresh from PC","Vom PC aktualisieren"],
    ["Zobrazení obsluh","Zobrazenie obslúh","Staff display","Mitarbeiteranzeige"],
    ["Filtr servisních účtů","Filter servisných účtov","Service account filter","Filter für Servicekonten"],
    ["Zobrazit i servisní obsluhy","Zobraziť aj servisné obsluhy","Show service staff too","Servicemitarbeiter ebenfalls anzeigen"],
    ["Zobrazit jen obsluhy","Zobraziť len obsluhy","Show regular staff only","Nur reguläre Mitarbeiter anzeigen"],
    ["Zobrazeny jsou běžné i servisní obsluhy.","Zobrazené sú bežné aj servisné obsluhy.","Regular and service staff are shown.","Reguläre und Servicemitarbeiter werden angezeigt."],
    ["Servisní obsluhy jsou skryté.","Servisné obsluhy sú skryté.","Service staff are hidden.","Servicemitarbeiter sind ausgeblendet."],
    ["Váš požadavek na refresh dat pro tento měsíc a rok byl zadán","Vaša požiadavka na obnovenie údajov pre tento mesiac a rok bola zadaná","Your refresh request for this month and year has been submitted","Ihre Aktualisierungsanfrage für diesen Monat und dieses Jahr wurde übermittelt"],
    ["Docházka za:","Dochádzka za:","Attendance for:","Zeiterfassung für:"],
    ["Přehled odpracovaného času podle dnů a obsluh","Prehľad odpracovaného času podľa dní a obslúh","Worked time by day and staff member","Arbeitszeit nach Tag und Mitarbeiter"],
    ["Den","Deň","Day","Tag"],
    ["Celkem","Celkom","Total","Gesamt"],
    ["Hledat v docházce…","Hľadať v dochádzke…","Search attendance…","Zeiterfassung durchsuchen…"],
    ["Pro vybrané období nejsou k dispozici žádné záznamy.","Pre vybrané obdobie nie sú k dispozícii žiadne záznamy.","No records are available for the selected period.","Für den ausgewählten Zeitraum sind keine Einträge verfügbar."],
    ["Období","Obdobie","Period","Zeitraum"],
    ["Včetně servisních obsluh","Vrátane servisných obslúh","Including service staff","Einschließlich Servicemitarbeiter"],
    ["Bez servisních obsluh","Bez servisných obslúh","Excluding service staff","Ohne Servicemitarbeiter"]
  ]);


  // V198: TEST copy of a customer between remembered companies, admin/manager permission-aware.
  rows = rows.concat([
    ["Zkopírovat do jiné firmy", "Skopírovať do inej firmy", "Copy to another company", "In ein anderes Unternehmen kopieren"],
    ["Přidejte další firmu přes Přepnout firmu. Administrátor nebo manažer musí mít oprávnění k zákazníkům.", "Pridajte ďalšiu firmu cez Prepnúť firmu. Administrátor alebo manažér musí mať oprávnenie k zákazníkom.", "Add another company using Switch company. The administrator or manager must have customer access.", "Fügen Sie über Firma wechseln ein weiteres Unternehmen hinzu. Administrator oder Manager benötigt Kundenberechtigung."],
    ["Kopie tohoto zákazníka", "Kópie tohto zákazníka", "Copies of this customer", "Kopien dieses Kunden"],
    ["Čeká na synchronizaci", "Čaká na synchronizáciu", "Waiting for synchronization", "Wartet auf Synchronisierung"],
    ["Odstraněno", "Odstránené", "Removed", "Entfernt"],
    ["Čeká na smazání v HairSoft", "Čaká na odstránenie v HairSoft", "Waiting for deletion in HairSoft", "Wartet auf Löschung in HairSoft"],
    ["Předáno HairSoft", "Odovzdané do HairSoft", "Sent to HairSoft", "An HairSoft übergeben"],
    ["Odstranit kopii", "Odstrániť kópiu", "Remove copy", "Kopie entfernen"],
    ["Zkopírovat zákazníka do jiné firmy", "Skopírovať zákazníka do inej firmy", "Copy customer to another company", "Kunden in ein anderes Unternehmen kopieren"],
    ["Zdrojová firma zůstane beze změny. V cílové firmě vznikne nový zákazník s novým GUID.", "Zdrojová firma zostane bez zmeny. V cieľovej firme vznikne nový zákazník s novým GUID.", "The source company remains unchanged. A new customer with a new GUID will be created in the target company.", "Das Quellunternehmen bleibt unverändert. Im Zielunternehmen wird ein neuer Kunde mit einer neuen GUID angelegt."],
    ["Cílová firma", "Cieľová firma", "Target company", "Zielunternehmen"],
    ["Vyberte firmu...", "Vyberte firmu...", "Select company...", "Unternehmen auswählen..."],
    ["Cílová pobočka / skupina", "Cieľová pobočka / skupina", "Target branch / group", "Zielfiliale / Gruppe"],
    ["Nejdříve vyberte firmu...", "Najprv vyberte firmu...", "Select a company first...", "Wählen Sie zuerst ein Unternehmen..."],
    ["Cílová firma nemá dostupnou pobočku.", "Cieľová firma nemá dostupnú pobočku.", "The target company has no available branch.", "Das Zielunternehmen hat keine verfügbare Filiale."],
    ["Vyberte pobočku...", "Vyberte pobočku...", "Select branch...", "Filiale auswählen..."],
    ["Zkopíruje se", "Skopíruje sa", "Will be copied", "Wird kopiert"],
    ["Základní a kontaktní údaje", "Základné a kontaktné údaje", "Basic and contact details", "Basis- und Kontaktdaten"],
    ["Poznámka a zdravotní údaje", "Poznámka a zdravotné údaje", "Note and health-related details", "Notiz und gesundheitsbezogene Angaben"],
    ["Timeline jako nové záznamy pro cílový HairSoft", "Časová os ako nové záznamy pre cieľový HairSoft", "Timeline as new records for the target HairSoft", "Zeitleiste als neue Einträge für das Ziel-HairSoft"],
    ["Fotografie včetně profilové fotografie", "Fotografie vrátane profilovej fotografie", "Photos including the profile photo", "Fotos einschließlich Profilbild"],
    ["Číslo karty, bonusové body, blacklist, tržby, rezervace, SMS, hovory a hodnocení se nekopírují.", "Číslo karty, bonusové body, blacklist, tržby, rezervácie, SMS, hovory a hodnotenia sa nekopírujú.", "Card number, loyalty points, blacklist, revenue, bookings, SMS, calls and ratings are not copied.", "Kartennummer, Bonuspunkte, Blacklist, Umsätze, Reservierungen, SMS, Anrufe und Bewertungen werden nicht kopiert."],
    ["Vytvořit kopii", "Vytvoriť kópiu", "Create copy", "Kopie erstellen"],
    ["Vytvářím kopii...", "Vytváram kópiu...", "Creating copy...", "Kopie wird erstellt..."],
    ["Opravdu chcete odstranit kopii? Pokud ji HairSoft ještě nepřevzal, bude odstraněna okamžitě. Pokud už byla synchronizována, označí se ke smazání standardní synchronizací cílové firmy. Zdrojová firma zůstane beze změny.", "Naozaj chcete odstrániť kópiu? Ak ju HairSoft ešte neprevzal, odstráni sa okamžite. Ak už bola synchronizovaná, označí sa na odstránenie štandardnou synchronizáciou cieľovej firmy. Zdrojová firma zostane bez zmeny.", "Do you really want to remove the copy? If HairSoft has not received it yet, it will be removed immediately. If it has already been synchronized, it will be marked for deletion through the target company's standard synchronization. The source company remains unchanged.", "Möchten Sie die Kopie wirklich entfernen? Wenn HairSoft sie noch nicht übernommen hat, wird sie sofort entfernt. Wurde sie bereits synchronisiert, wird sie über die Standardsynchronisierung des Zielunternehmens zur Löschung markiert. Das Quellunternehmen bleibt unverändert."],
    ["Odstranit kopii", "Odstrániť kópiu", "Remove copy", "Kopie entfernen"]
  ]);

  // V205: manual completion of a copied customer in HairSoft and row-by-row Timeline sync.
  rows = rows.concat([
    ["Dokončit synchronizaci do HairSoft", "Dokončiť synchronizáciu do HairSoft", "Complete synchronization to HairSoft", "Synchronisierung mit HairSoft abschließen"],
    ["Nejdříve založte zákazníka v HairSoft a zjistěte jeho ID.", "Najprv založte zákazníka v HairSoft a zistite jeho ID.", "First create the customer in HairSoft and find their ID.", "Legen Sie den Kunden zuerst in HairSoft an und ermitteln Sie seine ID."],
    ["Otevřít Timeline", "Otvoriť časovú os", "Open Timeline", "Zeitleiste öffnen"],
    ["HairSoft ID zákazníka", "HairSoft ID zákazníka", "Customer HairSoft ID", "HairSoft-Kunden-ID"],
    ["Uložit ID a připravit data", "Uložiť ID a pripraviť údaje", "Save ID and prepare data", "ID speichern und Daten vorbereiten"],
    ["Připravit tento záznam pro HairSoft", "Pripraviť tento záznam pre HairSoft", "Prepare this record for HairSoft", "Diesen Eintrag für HairSoft vorbereiten"],
    ["Nejdříve zadejte HairSoft ID ve Správě dat", "Najprv zadajte HairSoft ID v Správe údajov", "Enter the HairSoft ID in Data management first", "Geben Sie zuerst die HairSoft-ID in der Datenverwaltung ein"],
    ["Ukládám ID...", "Ukladám ID...", "Saving ID...", "ID wird gespeichert..."],
    ["Timeline čeká na dokončení synchronizace v HairSoft.", "Časová os čaká na dokončenie synchronizácie v HairSoft.", "Timeline is waiting for synchronization to finish in HairSoft.", "Die Timeline wartet auf den Abschluss der Synchronisierung in HairSoft."],
    ["Po ověření v HairSoft potvrďte dokončení. Teprve potom lze připravit další záznam.", "Po overení v HairSoft potvrďte dokončenie. Až potom je možné pripraviť ďalší záznam.", "After verifying it in HairSoft, confirm completion. Only then can the next record be prepared.", "Bestätigen Sie nach der Prüfung in HairSoft den Abschluss. Erst dann kann der nächste Eintrag vorbereitet werden."],
    ["Synchronizace proběhla – povolit další", "Synchronizácia prebehla – povoliť ďalší", "Synchronization completed – allow next", "Synchronisierung abgeschlossen – nächsten freigeben"],
    ["Nejdříve potvrďte dokončení předchozí synchronizace", "Najprv potvrďte dokončenie predchádzajúcej synchronizácie", "Confirm completion of the previous synchronization first", "Bestätigen Sie zuerst den Abschluss der vorherigen Synchronisierung"]
  ]);

  var dictionaries = { cs: Object.create(null), sk: Object.create(null), en: Object.create(null), de: Object.create(null) };
  rows.forEach(function (row) {
    var source = key(row[0]);
    dictionaries.cs[source] = row[0];
    dictionaries.sk[source] = row[1];
    dictionaries.en[source] = row[2];
    dictionaries.de[source] = row[3];
  });

  function getLanguage() {
    try {
      var stored = window.localStorage.getItem(STORAGE_KEY);
      if (SUPPORTED.indexOf(stored) !== -1) return stored;
    } catch (error) {}
    return DEFAULT_LANGUAGE;
  }

  function translated(source, language) {
    var normalized = key(source);
    var aliases = {
      "ZÓNY TĚLA": "Zóny těla"
    };
    var lookup = aliases[normalized] || normalized;
    var exact = dictionaries[language][lookup];
    if (exact) return exact;
    var yearKpi = normalized.match(/^(TOP rok za (?:služby|prodej)) (\d{4})$/);
    if (yearKpi) return (dictionaries[language][yearKpi[1]] || yearKpi[1]) + " " + yearKpi[2];

    // Keep branch/seller names and submitted dates untouched; translate only the fixed labels.
    var filterSummary = normalized.match(/^(\d{1,2}\.\d{1,2}\.\d{4}\s*-\s*\d{1,2}\.\d{1,2}\.\d{4}) - Stav:\s*(Vše|Prodáno|Částečně čerpáno|Vyčerpáno|Expirováno|)(?: - (Všechny pobočky|Pobočka: .*?))?(?: - Prodejce: (.*))?$/);
    if (filterSummary) {
      var dict = dictionaries[language], value = filterSummary[1] + ' - ' + (dict['Stav'] || 'Stav') + ': ' + (dict[filterSummary[2]] || filterSummary[2]);
      if (filterSummary[3]) value += ' - ' + (filterSummary[3] === 'Všechny pobočky' ? (dict['Všechny pobočky'] || filterSummary[3]) : (dict['Pobočka'] || 'Pobočka') + ': ' + filterSummary[3].slice(9));
      if (filterSummary[4]) value += ' - ' + (dict['Prodejce'] || 'Prodejce') + ': ' + filterSummary[4];
      return value;
    }
    var voucherBalance = normalized.match(/^(Aktuální zůstatková cena) (\(.*\))$/);
    if (voucherBalance) return (dictionaries[language][voucherBalance[1]] || voucherBalance[1]) + ' ' + voucherBalance[2];
    var directoryMatch = normalized.match(/^Adresář klientů\s*\((\d+)\)$/);
    if (directoryMatch) {
      var directory = dictionaries[language]["Adresář klientů"] || "Adresář klientů";
      return directory + " (" + directoryMatch[1] + ")";
    }

    var pageMatch = normalized.match(/^Strana\s+(\d+)\s+z\s+(\d+)$/);
    if (pageMatch) {
      var pageTemplates = {
        cs: "Strana {page} z {pages}",
        sk: "Strana {page} z {pages}",
        en: "Page {page} of {pages}",
        de: "Seite {page} von {pages}"
      };
      return pageTemplates[language].replace("{page}", pageMatch[1]).replace("{pages}", pageMatch[2]);
    }

    var totalMatch = normalized.match(/^\(Celkem\s+(\d+)\s+záznamů\)$/);
    if (totalMatch) {
      var totalTemplates = {
        cs: "(Celkem {count} záznamů)",
        sk: "(Spolu {count} záznamov)",
        en: "({count} records in total)",
        de: "({count} Einträge insgesamt)"
      };
      return totalTemplates[language].replace("{count}", totalMatch[1]);
    }

    var revenueTotalMatch = normalized.match(/^Celkem\s*\(([^)]+)\):$/);
    if (revenueTotalMatch) {
      var revenueTotalTemplates = {
        cs: "Celkem ({currency}):",
        sk: "Celkom ({currency}):",
        en: "Total ({currency}):",
        de: "Gesamt ({currency}):"
      };
      return revenueTotalTemplates[language].replace("{currency}", revenueTotalMatch[1]);
    }
    return source;
  }

  function preserveWhitespace(original, replacement) {
    var leading = original.match(/^\s*/)[0];
    var trailing = original.match(/\s*$/)[0];
    return leading + replacement + trailing;
  }

  function isProtectedContent(element) {
    if (element.closest("[data-hs-i18n-force]")) return false;
    return !!element.closest("script,style,noscript,textarea,code,pre,#ui-datepicker-div,.ui-datepicker-inline,.hs-client-language-switcher,[data-hs-i18n-ignore],.profile-body,#contact-list,.nadpis1,.c3-legend-item,.c3-tooltip-name");
  }

  function translateTextNode(node, language) {
    var parent = node.parentElement;
    if (!parent || isProtectedContent(parent)) return;
    var original = textSources.has(node) ? textSources.get(node) : node.nodeValue;
    if (!textSources.has(node)) textSources.set(node, original);
    var normalized = key(original);
    if (!normalized) return;
    var replacement = translated(normalized, language);
    var next = replacement === normalized ? original : preserveWhitespace(original, replacement);
    if (node.nodeValue !== next) node.nodeValue = next;
  }

  function sourceAttributes(element) {
    var sources = attributeSources.get(element);
    if (!sources) {
      sources = Object.create(null);
      attributeSources.set(element, sources);
    }
    return sources;
  }

  function translateAttribute(element, attribute, language) {
    if (!element.hasAttribute(attribute) || isProtectedContent(element)) return;
    var sources = sourceAttributes(element);
    if (!(attribute in sources)) sources[attribute] = element.getAttribute(attribute);
    var replacement = translated(sources[attribute], language);
    if (element.getAttribute(attribute) !== replacement) element.setAttribute(attribute, replacement);
  }

  function translateActionValue(element, language) {
    if (element.tagName !== "INPUT" || ["submit", "button", "reset"].indexOf((element.type || "").toLowerCase()) === -1) return;
    var sources = sourceAttributes(element);
    if (!("value" in sources)) sources.value = element.value;
    var replacement = translated(sources.value, language);
    element.dataset.hsI18nOriginalValue = sources.value;
    if (element.value !== replacement) element.value = replacement;
  }

  function translateElement(element, language) {
    ["placeholder", "title", "aria-label", "data-label"].forEach(function (attribute) {
      translateAttribute(element, attribute, language);
    });
    translateActionValue(element, language);
  }

  function translateTree(root, language) {
    if (root.nodeType === Node.TEXT_NODE) {
      translateTextNode(root, language);
      return;
    }
    if (root.nodeType !== Node.ELEMENT_NODE && root.nodeType !== Node.DOCUMENT_NODE) return;
    if (root.nodeType === Node.ELEMENT_NODE) translateElement(root, language);
    var walker = document.createTreeWalker(root, NodeFilter.SHOW_ELEMENT | NodeFilter.SHOW_TEXT);
    var node;
    while ((node = walker.nextNode())) {
      if (node.nodeType === Node.TEXT_NODE) translateTextNode(node, language);
      else translateElement(node, language);
    }
  }

  function mountSwitcher(switcher) {
    var loginShell = document.querySelector(".login-page #login-wrapper");
    if (loginShell) {
      switcher.classList.add("hs-client-language-switcher--login");
      loginShell.appendChild(switcher);
      return;
    }

    var headerNav = document.querySelector("#header .navbar-right");
    if (headerNav) {
      var slot = document.createElement("li");
      slot.className = "hs-client-language-slot hidden-xs";
      slot.appendChild(switcher);
      var rightToggle = headerNav.querySelector("li.toggle-right");
      headerNav.insertBefore(slot, rightToggle || null);
      return;
    }

    switcher.classList.add("hs-client-language-switcher--floating");
    document.body.appendChild(switcher);
  }

  function createSwitcher(language) {
    var switcher = document.createElement("div");
    switcher.className = "hs-client-language-switcher";
    switcher.setAttribute("data-hs-language", language);

    var toggle = document.createElement("button");
    toggle.type = "button";
    toggle.className = "hs-client-language-toggle";
    toggle.setAttribute("aria-haspopup", "true");
    toggle.setAttribute("aria-expanded", "false");

    var activeFlag = document.createElement("img");
    activeFlag.className = "hs-client-language-active-flag";
    activeFlag.alt = "";
    toggle.appendChild(activeFlag);
    switcher.appendChild(toggle);

    var menu = document.createElement("div");
    menu.className = "hs-client-language-menu";
    menu.setAttribute("role", "menu");
    SUPPORTED.forEach(function (code) {
      var option = document.createElement("button");
      option.type = "button";
      option.className = "hs-client-language-option";
      option.dataset.language = code;
      option.setAttribute("role", "menuitemradio");

      var flag = document.createElement("img");
      flag.src = flagsBase + FLAG_FILES[code] + ".svg";
      flag.alt = "";
      var label = document.createElement("span");
      label.textContent = languageNames[code];
      option.appendChild(flag);
      option.appendChild(label);
      menu.appendChild(option);
    });
    switcher.appendChild(menu);

    toggle.addEventListener("click", function () {
      var expanded = toggle.getAttribute("aria-expanded") === "true";
      toggle.setAttribute("aria-expanded", expanded ? "false" : "true");
      switcher.classList.toggle("hs-client-language-open", !expanded);
    });
    menu.addEventListener("click", function (event) {
      var option = event.target.closest(".hs-client-language-option");
      if (!option) return;
      setLanguage(option.dataset.language);
      toggle.setAttribute("aria-expanded", "false");
      switcher.classList.remove("hs-client-language-open");
    });
    document.addEventListener("pointerdown", function (event) {
      if (!switcher.contains(event.target)) {
        toggle.setAttribute("aria-expanded", "false");
        switcher.classList.remove("hs-client-language-open");
      }
    });
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") {
        toggle.setAttribute("aria-expanded", "false");
        switcher.classList.remove("hs-client-language-open");
      }
    });

    mountSwitcher(switcher);
    updateSwitcher(language);
  }

  function updateSwitcher(language) {
    var switcher = document.querySelector(".hs-client-language-switcher");
    if (!switcher) return;
    switcher.dataset.hsLanguage = language;
    var activeFlag = switcher.querySelector(".hs-client-language-active-flag");
    if (activeFlag) activeFlag.src = flagsBase + FLAG_FILES[language] + ".svg";
    var toggle = switcher.querySelector(".hs-client-language-toggle");
    if (toggle) toggle.setAttribute("aria-label", languageNames[language] + " – " + changeLanguageLabels[language]);
    Array.prototype.forEach.call(switcher.querySelectorAll(".hs-client-language-option"), function (option) {
      var active = option.dataset.language === language;
      option.classList.toggle("hs-client-language-option-active", active);
      option.setAttribute("aria-checked", active ? "true" : "false");
    });
  }

  function setLanguage(language) {
    if (SUPPORTED.indexOf(language) === -1) language = DEFAULT_LANGUAGE;
    try { window.localStorage.setItem(STORAGE_KEY, language); } catch (error) {}
    document.documentElement.lang = language;
    document.title = translated(originalDocumentTitle, language);
    applying = true;
    translateTree(document.body, language);
    applying = false;
    updateSwitcher(language);
    try {
      document.dispatchEvent(new CustomEvent("hs:languagechange", { detail: { language: language } }));
    } catch (error) {}
  }

  var nativeConfirm = window.confirm.bind(window);
  var nativeAlert = window.alert.bind(window);
  window.hsTranslate = function (source) { return translated(String(source), getLanguage()); };
  window.hsSetTranslatedText = function (element, source) {
    var sourceText;
    var node;
    if (!element) return;
    sourceText = String(source == null ? "" : source);
    while (element.firstChild) element.removeChild(element.firstChild);
    node = document.createTextNode(sourceText);
    textSources.set(node, sourceText);
    node.nodeValue = translated(sourceText, getLanguage());
    element.appendChild(node);
  };
  window.hsSetTranslatedAttribute = function (element, attribute, source) {
    var sources;
    if (!element || !attribute) return;
    sources = sourceAttributes(element);
    sources[attribute] = String(source == null ? "" : source);
    element.setAttribute(attribute, translated(sources[attribute], getLanguage()));
  };
  window.confirm = function (message) { return nativeConfirm(translated(String(message), getLanguage())); };
  window.alert = function (message) { return nativeAlert(translated(String(message), getLanguage())); };

  document.addEventListener("submit", function (event) {
    var submitter = event.submitter;
    if (!submitter || !submitter.dataset.hsI18nOriginalValue) return;
    var localizedValue = submitter.value;
    submitter.value = submitter.dataset.hsI18nOriginalValue;
    window.setTimeout(function () {
      if (document.documentElement.contains(submitter)) submitter.value = localizedValue;
    }, 0);
  }, true);

  function initialize() {
    var language = getLanguage();
    createSwitcher(language);
    setLanguage(language);
    var observer = new MutationObserver(function (mutations) {
      if (applying) return;
      var current = getLanguage();
      applying = true;
      mutations.forEach(function (mutation) {
        Array.prototype.forEach.call(mutation.addedNodes, function (node) {
          translateTree(node, current);
        });
      });
      applying = false;
    });
    observer.observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", initialize, { once: true });
  else initialize();
})();
