//go:build windows

package main

import (
	"errors"
	"fmt"
	"math"
	"os"
	"regexp"
	"strings"
	"time"
)

const bridgeVersion = "V010"

var codeRe = regexp.MustCompile(`^[A-Z0-9-]{1,20}$`)
var errUserExit = errors.New("user exit")

func main() {
	initConsoleUTF8()
	release, err := acquireSingleton()
	if err != nil {
		if errors.Is(err, errorAlreadyExists) {
			return
		}
		showMessageBox("HSBridge V010", "Bridge nelze spustit: "+err.Error())
		return
	}
	defer release()

	c, err := loadConfig()
	if err != nil {
		if os.IsNotExist(err) {
			c = defaultConfig()
		} else {
			showMessageBox("HSBridge V010", "Nelze nacist config.json: "+err.Error())
			return
		}
	}
	if err := ensureBridgeIdentity(&c); err != nil {
		showMessageBox("HSBridge V010", "Nelze vytvorit identitu Bridge: "+err.Error())
		return
	}
	if err := saveConfig(c); err != nil {
		showMessageBox("HSBridge V010", "Nelze ulozit config.json: "+err.Error())
		return
	}

	logf("PROCESS START HSBridge %s pid=%d", bridgeVersion, os.Getpid())
	features := readHSSystemFeatures()
	logf("FEATURES HSKLIENT=%t REMOTE_DASHBOARD=%t", features.HSKlientEnabled, features.RemoteDashboardExists)

	if !c.StartupInstalled {
		if err := installStartup(true); err != nil {
			logf("AUTOSTART ERROR %v", err)
		} else {
			c.StartupInstalled = true
			_ = saveConfig(c)
		}
	}

	// V008: one active Bridge per HairSoft dataset.
	// SQLite installations are standalone and remain active. In a MySQL
	// network installation only the server PC (SQLHost=localhost/loopback)
	// may send or receive Bridge traffic. Client PCs are intentionally passive
	// so the same shared MySQL data are never synchronized by multiple PCs.
	traffic := currentBridgeTrafficPolicy()
	if !traffic.Allowed {
		if traffic.Err != nil {
			logf("[BRIDGE] PASSIVE role=%s settings=%s err=%v - ALL SEND/RECEIVE DISABLED",
				traffic.Role, traffic.SettingsPath, traffic.Err)
		} else {
			logf("[BRIDGE] PASSIVE role=%s sql_host=%s settings=%s - ALL SEND/RECEIVE DISABLED",
				traffic.Role, traffic.SQLHost, traffic.SettingsPath)
		}
		for {
			time.Sleep(time.Hour)
		}
	}
	if traffic.Role == bridgeRoleMySQLServer {
		logf("[BRIDGE] ACTIVE role=%s sql_host=%s settings=%s", traffic.Role, traffic.SQLHost, traffic.SettingsPath)
	} else {
		logf("[BRIDGE] ACTIVE role=%s", traffic.Role)
	}

	if backend, detail, err := quickHairSoftBackend(c); err == nil {
		logf("DB QUICK DISCOVERY OK backend=%s %s", backend, detail)
		// DBPath zustava pouze SQLite/voucher kompatibilni hodnota. MySQL
		// prihlasovaci udaje se vzdy ctou primo ze Settings.xml a do config.json
		// se nekopiruji.
		if backend == "sqlite" {
			if path, _, sqliteErr := discoverHairSoftDB(c); sqliteErr == nil && c.DBPath != path {
				c.DBPath = path
				_ = saveConfig(c)
			}
		}
	} else {
		logf("DB QUICK DISCOVERY ERROR backend=%s detail=%s err=%v", backend, detail, err)
	}

	// Register/refresh SoftRC identity before any HS Klient request.
	// This is independent from the Cooper voucher target pairing.
	if _, regErr := directoryRegister(c); regErr != nil {
		logf("DIRECTORY REGISTER WARN %v", regErr)
	}
	// V005: CUSTOMER / STATISTICS / TIMELINE zustavaji ve zdrojovem kodu,
	// ale HSBridge je neposila. Tyto oblasti obsluhuji SoftKW / SoftSYS.
	// PROGRAMS bezi nezavisle na voucherovem propojeni.
	if features.HSKlientEnabled {
		go runProgramsModule()
	}

	// Voucher pairing je samostatny modul. Na beznem HS Klient PC se nikdy
	// neotevira parovaci okno. Aktivni zustane jen na PC, kde existuje
	// konkretni Cooper/legacy voucher konfigurace. Bonfero se zapoji az v
	// budoucnu pres explicitni aktivaci modulu.
	if !voucherModuleConfigured(c) {
		logf("[VOUCHERS] DISABLED - Programs-only mode; pairing window is not used")
		for {
			time.Sleep(time.Hour)
		}
	}

	ds, err := ensurePaired(&c, false)
	if err != nil {
		if !errors.Is(err, errUserExit) {
			logf("PAIR ERROR %v", err)
			showMessageBox("HSBridge V010", "Propojeni se nepodarilo: "+err.Error())
		}
		return
	}

	var lastCatalog time.Time
	lastLoggedRevision := -1
	for {
		fresh, err := directoryGetStatus(c)
		if err != nil {
			logf("PAIR STATUS ERROR %v", err)
			sleepPoll(c.PollSeconds)
			continue
		}
		if !fresh.Paired {
			logf("PAIR RELEASED - cekam na nove propojeni")
			ds, err = ensurePaired(&c, true)
			if err != nil {
				if errors.Is(err, errUserExit) {
					return
				}
				logf("PAIR ERROR %v", err)
				sleepPoll(c.PollSeconds)
				continue
			}
			lastCatalog = time.Time{}
		} else {
			ds = fresh
		}

		if ds.SiteBaseURL != "" && c.ServerURL != ds.SiteBaseURL {
			c.ServerURL = ds.SiteBaseURL
			_ = saveConfig(c)
		}

		ts, err := getTargetStatus(c, ds)
		if err != nil {
			logf("STATUS ERROR %v", err)
			sleepPoll(c.PollSeconds)
			continue
		}
		if !ts.Paired {
			logf("STATUS target web nema aktivni pairing")
			sleepPoll(c.PollSeconds)
			continue
		}
		if !ts.Enabled {
			sleepPoll(c.PollSeconds)
			continue
		}

		if lastCatalog.IsZero() || time.Since(lastCatalog) >= time.Hour {
			logCatalogSuccess := lastCatalog.IsZero()
			if err := discoverAndStore(&c, ds, logCatalogSuccess); err != nil {
				logf("CATALOG ERROR %v", err)
			} else {
				lastCatalog = time.Now()
			}
		}

		remote, err := getRemoteConfig(c, ds)
		if err != nil {
			logf("CONFIG ERROR %v", err)
			sleepPoll(c.PollSeconds)
			continue
		}
		configChanged := applyRemote(&c, remote)
		if remote.Ready {
			if lastLoggedRevision < 0 || configChanged {
				logf("CONFIG OK revision=%d centre=%d user=%d", remote.Revision, remote.CentreID, remote.UserID)
				lastLoggedRevision = remote.Revision
			}
			job, err := getNextJob(c, ds)
			if err != nil {
				logf("JOB ERROR %v", err)
			} else if job != nil {
				ids, processErr := processJob(c, *job)
				if processErr != nil {
					logf("FAILED job=%s code=%s: %v", job.JobID, job.Code, processErr)
					if err := postJobResult(c, ds, BridgeResult{JobID: job.JobID, Status: "failed", Message: processErr.Error()}); err != nil {
						logf("RESULT ERROR job=%s: %v", job.JobID, err)
					}
				} else {
					result := BridgeResult{
						JobID: job.JobID, Status: "imported", BillID: ids.BillID, BillItemID: ids.BillItemID,
						CodeID: ids.CodeID, BillNumber: ids.BillNumber, ImportedAt: time.Now().Format(time.RFC3339),
					}
					if err := postJobResult(c, ds, result); err != nil {
						logf("RESULT ERROR job=%s: %v", job.JobID, err)
					}
				}
			}
		}
		sleepPoll(c.PollSeconds)
	}
}

func sleepPoll(seconds int) {
	if seconds < 60 || seconds > 3600 {
		seconds = 600
	}
	time.Sleep(time.Duration(seconds) * time.Second)
}

func ensurePaired(c *Config, resetCode bool) (directoryStatus, error) {
	if resetCode {
		if err := resetPairingCode(c); err != nil {
			return directoryStatus{}, err
		}
	}
	ds, err := directoryRegister(*c)
	if err == nil && ds.Paired {
		logf("PAIR RESOLVED %s", ds.SiteBaseURL)
		return ds, nil
	}
	if err != nil {
		logf("PAIR REGISTER ERROR %v", err)
	} else {
		logf("PAIR CODE REGISTERED - kod je potvrzen pairing serverem")
	}

	w, werr := newPairingWindow(c.PairingCode)
	if werr != nil {
		return directoryStatus{}, werr
	}
	w.setCode(c.PairingCode)
	w.setStatus("Cekam na propojeni...")
	lastRegister := time.Now()

	for {
		select {
		case <-w.closed:
			if !w.wasProgramClosed() {
				logf("USER EXIT - pairing window closed")
				return directoryStatus{}, errUserExit
			}
		case <-time.After(3 * time.Second):
		}

		ds, err = directoryGetStatus(*c)
		if err == nil && ds.Paired {
			logf("PAIR RESOLVED %s", ds.SiteBaseURL)
			w.setStatus("Propojeno. Bridge bezi na pozadi.")
			time.Sleep(500 * time.Millisecond)
			w.close()
			return ds, nil
		}
		if err != nil {
			w.setStatus("Cekam na pairing server...")
			logf("PAIR STATUS ERROR %v", err)
		} else {
			w.setStatus("Cekam na propojeni...")
		}
		if time.Since(lastRegister) >= 5*time.Minute {
			if _, rerr := directoryRegister(*c); rerr != nil {
				logf("PAIR REGISTER ERROR %v", rerr)
			} else {
				logf("PAIR CODE REGISTERED - platnost kodu obnovena")
			}
			lastRegister = time.Now()
		}
	}
}

func applyRemote(c *Config, remote remoteConfig) bool {
	changed := c.ConfigRevision != remote.Revision || c.CentreID != remote.CentreID || c.UserID != remote.UserID
	c.CentreID = remote.CentreID
	c.UserID = remote.UserID
	// V016 intentionally uses a fixed 10-minute web/job polling interval.
	// The legacy server pollSeconds value is ignored so an older web config
	// cannot silently return the Bridge to 10-second polling.
	c.PollSeconds = 600
	c.PaymentMappings = map[string]int64{}
	for k, v := range remote.PaymentMappings {
		c.PaymentMappings[k] = v
	}
	c.TemplateMappings = map[string]TemplateMapping{}
	for k, v := range remote.TemplateMappings {
		c.TemplateMappings[k] = v
	}
	c.ConfigRevision = remote.Revision
	if changed {
		if err := saveConfig(*c); err != nil {
			logf("CONFIG SAVE ERROR %v", err)
		}
	}
	return changed
}

func discoverAndStore(c *Config, ds directoryStatus, logSuccess bool) error {
	path, source, err := discoverHairSoftDB(*c)
	if err != nil {
		logf("DB DISCOVERY ERROR %v", err)
		catalog := HairSoftCatalog{Error: err.Error(), DBPath: c.DBPath}
		if postErr := postCatalog(*c, ds, catalog); postErr != nil {
			return fmt.Errorf("%v; catalog error post: %w", err, postErr)
		}
		return err
	}
	if c.DBPath != path {
		oldPath := c.DBPath
		c.DBPath = path
		_ = saveConfig(*c)
		if oldPath != "" {
			logf("DB PATH CHANGED source=%s path=%s", source, path)
		}
	}
	hairSoftDBMu.Lock()
	catalog, err := buildHairSoftCatalog(path, source)
	hairSoftDBMu.Unlock()
	if err != nil {
		catalog = HairSoftCatalog{Error: err.Error(), DBPath: path, DBSource: source}
		_ = postCatalog(*c, ds, catalog)
		return err
	}
	if err := postCatalog(*c, ds, catalog); err != nil {
		return err
	}
	if logSuccess {
		logf("CATALOG OK centres=%d users=%d payments=%d vouchers=%d", len(catalog.Centres), len(catalog.Users), len(catalog.Payments), len(catalog.Vouchers))
	}
	return nil
}

func logf(format string, args ...any) {
	line := time.Now().Format("2006-01-02 15:04:05") + " " + fmt.Sprintf(format, args...)
	p, err := logPath()
	if err != nil {
		return
	}
	// Keep diagnostics bounded even after years of operation. With V016's
	// event-only logging this should rotate only rarely.
	if fi, statErr := os.Stat(p); statErr == nil && fi.Size() >= 5*1024*1024 {
		backup := p + ".1"
		_ = os.Remove(backup)
		_ = os.Rename(p, backup)
	}
	f, err := os.OpenFile(p, os.O_CREATE|os.O_APPEND|os.O_WRONLY, 0600)
	if err != nil {
		return
	}
	defer f.Close()
	_, _ = fmt.Fprintln(f, line)
}

func priceNoVatForGross(gross, vat float64) float64 {
	if vat <= 0 {
		return gross
	}
	return math.Round((gross/(1.0+vat/100.0))*100.0) / 100.0
}

func processJob(c Config, job BridgeJob) (SaleIDs, error) {
	var zero SaleIDs
	job.Code = strings.ToUpper(strings.TrimSpace(job.Code))
	if job.JobID == "" || job.TemplateID == "" || !codeRe.MatchString(job.Code) || job.AmountCzk <= 0 {
		return zero, errors.New("job nema platna povinna data")
	}
	mapping, ok := c.TemplateMappings[job.TemplateID]
	if !ok {
		return zero, fmt.Errorf("web template %s neni namapovan na HS", job.TemplateID)
	}
	payID, ok := c.PaymentMappings[job.PaymentMethod]
	if !ok {
		return zero, fmt.Errorf("web platba %s neni namapovana na HS", job.PaymentMethod)
	}
	saleAt, err := parseSaleTime(job.SoldAt)
	if err != nil {
		return zero, err
	}
	saleTime := saleAt.In(time.Local).Format("2006-01-02 15:04:05")
	gids := jobGIDs(c.BridgeToken, job.JobID)

	hairSoftDBMu.Lock()
	defer hairSoftDBMu.Unlock()

	db, err := openDB(c.DBPath)
	if err != nil {
		return zero, err
	}
	defer db.Close()
	db.BusyTimeout(2500)
	if err := validateDB(db); err != nil {
		return zero, err
	}

	salePrice := float64(job.AmountCzk)
	if ids, found, err := findImportedByBillGID(db, gids[0]); err != nil {
		return zero, err
	} else if found {
		if e := verifyInserted(db, ids, job.Code, salePrice, c.CentreID, c.UserID, payID); e != nil {
			return zero, fmt.Errorf("nalezen drivejsi job, ale overeni selhalo: %w", e)
		}
		return ids, nil
	}

	vp, err := getVoucherPriceByID(db, mapping.SellPriceID)
	if err != nil {
		return zero, err
	}
	if vp.CommodityID != mapping.CommodityID {
		return zero, errors.New("mapovana HairSoft komodita se zmenila")
	}
	if vp.PriceCentreID != 0 && vp.PriceCentreID != c.CentreID {
		return zero, errors.New("cena neni platna pro nastavenou provozovnu")
	}
	if vp.PriceUserID != 0 && vp.PriceUserID != c.UserID {
		return zero, errors.New("cena neni platna pro nastaveneho prodejce")
	}
	// Cena namapovane polozky v HairSoftu je pouze vychozi informace.
	// Pro konkretni online prodej je vzdy rozhodujici castka prijata z webu.

	duration := mapping.ValidityMonthsOverride
	if duration <= 0 {
		return zero, errors.New("mapovani nema potvrzenou dobu platnosti z nastaveni HS")
	}
	localSale := saleAt.In(time.Local)
	baseValid := addMonthsClamped(localSale, int(duration))
	validityClock, clockErr := time.Parse("15:04:05", mapping.ValidityTime)
	if clockErr != nil {
		return zero, errors.New("mapovani nema platny potvrzeny cas DateValidTo")
	}
	validToTime := time.Date(baseValid.Year(), baseValid.Month(), baseValid.Day(), validityClock.Hour(), validityClock.Minute(), validityClock.Second(), 0, time.Local)
	validTo := validToTime.Format("2006-01-02 15:04:05")

	centre, err := getCentre(db, c.CentreID)
	if err != nil {
		return zero, errors.New("nastavena provozovna v HS neexistuje")
	}
	user, err := getUser(db, c.UserID, c.CentreID)
	if err != nil {
		return zero, errors.New("nastaveny online prodejce v HS neexistuje")
	}
	payment, err := getPaymentByID(db, payID)
	if err != nil {
		return zero, errors.New("mapovana HS platebni metoda neexistuje")
	}
	buy, err := getBuyPriceByID(db, vp.CommodityID, mapping.BuyPriceID)
	if err != nil {
		return zero, errors.New("mapovana nakupni cena voucheru v HS neexistuje")
	}
	store, err := getStoreByID(db, vp.CommodityID, mapping.StoreID)
	if err != nil {
		return zero, errors.New("mapovany sklad voucheru v HS neexistuje")
	}

	if err := beginImmediateRetry(db, 10*time.Second); err != nil {
		return zero, fmt.Errorf("HS prave zapisuje do DB; Bridge neziskal zapisovy slot: %w", err)
	}
	committed := false
	defer func() {
		if !committed {
			_ = db.Exec("ROLLBACK")
		}
	}()

	if ids, found, err := findImportedByBillGID(db, gids[0]); err != nil {
		return zero, err
	} else if found {
		_ = db.Exec("ROLLBACK")
		committed = true
		if e := verifyInserted(db, ids, job.Code, salePrice, c.CentreID, c.UserID, payID); e != nil {
			return zero, e
		}
		return ids, nil
	}

	current, err := getVoucherPriceByID(db, mapping.SellPriceID)
	if err != nil {
		return zero, err
	}
	if current.CommodityID != mapping.CommodityID {
		return zero, errors.New("mapovana HairSoft komodita se behem pripravy zmenila")
	}
	if current.PriceCentreID != 0 && current.PriceCentreID != c.CentreID {
		return zero, errors.New("cena jiz neplati pro nastavenou provozovnu")
	}
	if current.PriceUserID != 0 && current.PriceUserID != c.UserID {
		return zero, errors.New("cena jiz neplati pro nastaveneho prodejce")
	}
	salePriceNoVat := priceNoVatForGross(salePrice, current.Vat)

	if exists, err := voucherCodeExists(db, job.Code); err != nil {
		return zero, err
	} else if exists {
		return zero, fmt.Errorf("kod %s uz v HS existuje", job.Code)
	}
	billNumber, err := nextBillNumberForCentre(db, c.CentreID)
	if err != nil {
		return zero, err
	}

	closeNotice := showImportNotice(job.Code)
	defer closeNotice()
	ids, err := insertSale(db, centre, user, current, buy, store, payment, job.Code, duration, validTo, billNumber, saleTime, gids, salePrice, salePriceNoVat)
	if err != nil {
		return zero, err
	}
	if err := verifyInserted(db, ids, job.Code, salePrice, c.CentreID, c.UserID, payID); err != nil {
		return zero, err
	}
	if err := db.Exec("COMMIT"); err != nil {
		return zero, err
	}
	committed = true
	closeNotice()
	if err := verifyInserted(db, ids, job.Code, salePrice, c.CentreID, c.UserID, payID); err != nil {
		return zero, fmt.Errorf("zapis byl commitnut, ale nasledna kontrola selhala: %w", err)
	}
	logf("IMPORTED job=%s order=%s code=%s amount=%d bill=%d billnumber=%d", job.JobID, job.OrderID, job.Code, job.AmountCzk, ids.BillID, ids.BillNumber)
	return ids, nil
}
