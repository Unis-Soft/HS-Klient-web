//go:build windows

package main

import (
	"crypto/sha256"
	"encoding/hex"
	"encoding/json"
	"errors"
	"net/http"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"time"
)

const (
	hsKlientBaseURL       = "https://klient.hairsoft.cz/str/api/hsbridge.php"
	hsKlientTestCustomer  = int64(492)
	hsKlientPollInterval  = 60 * time.Second
)

var statisticsDebugLogged bool

type hsKlientStatus struct {
	OK       bool `json:"ok"`
	Identity struct {
		SWID       int64  `json:"swId"`
		GroupID    int64  `json:"groupId"`
		BranchName string `json:"branchName"`
	} `json:"identity"`
	Modules struct {
		Customer   bool `json:"customer"`
		Statistics bool `json:"statistics"`
		Timeline   bool `json:"timeline"`
		Programs   bool `json:"programs"`
	} `json:"modules"`
	TestCustomerID int64 `json:"testCustomerId"`
}

type hsKlientState struct {
	LastCustomerEventID   string `json:"lastCustomerEventId,omitempty"`
	LastCustomerUpdated   string `json:"lastCustomerUpdated,omitempty"`
	LastStatisticsEventID string `json:"lastStatisticsEventId,omitempty"`
	LastProgramsEventID   string `json:"lastProgramsEventId,omitempty"`
}

func hsKlientURL(route string) string {
	return hsKlientBaseURL + "?route=" + route
}

func hsKlientHeaders(c Config) map[string]string {
	return map[string]string{
		"X-HS-Bridge-ID":    c.BridgeID,
		"X-HS-Bridge-Token": c.BridgeToken,
	}
}

func getHSKlientStatus(c Config) (hsKlientStatus, error) {
	var out hsKlientStatus
	err := jsonRequest(http.MethodGet, hsKlientURL("status"), nil, hsKlientHeaders(c), &out)
	if err != nil {
		return out, err
	}
	if !out.OK {
		return out, errors.New("HS Klient status neni OK")
	}
	return out, nil
}

func hsKlientStatePath() (string, error) {
	d, err := appDir()
	if err != nil {
		return "", err
	}
	return filepath.Join(d, "hsklient-state.json"), nil
}

func loadHSKlientState() hsKlientState {
	var st hsKlientState
	p, err := hsKlientStatePath()
	if err != nil {
		return st
	}
	b, err := os.ReadFile(p)
	if err != nil {
		return st
	}
	_ = json.Unmarshal(b, &st)
	return st
}

func saveHSKlientState(st hsKlientState) error {
	p, err := hsKlientStatePath()
	if err != nil {
		return err
	}
	b, err := json.MarshalIndent(st, "", "  ")
	if err != nil {
		return err
	}
	tmp := p + ".tmp"
	if err := os.WriteFile(tmp, b, 0600); err != nil {
		return err
	}
	return os.Rename(tmp, p)
}

func customerEventID(c Config, customer CustomerSnapshot) string {
	parts := []string{
		c.BridgeID,
		"customer-test-v001",
		strings.TrimSpace(customer.GUID),
		strings.TrimSpace(customer.Updated),
		customer.Note,
		strconv.FormatInt(customer.LoyalityPoints, 10),
	}
	sum := sha256.Sum256([]byte(strings.Join(parts, "\n")))
	return hex.EncodeToString(sum[:])
}

func statisticsEventID(c Config, customer CustomerSnapshot, visits VisitDatesSnapshot) string {
	parts := []string{
		c.BridgeID,
		"statistics-test-v002",
		strings.TrimSpace(customer.GUID),
		strconv.FormatInt(customer.ID, 10),
		strings.TrimSpace(visits.LastVisit),
		strings.TrimSpace(visits.NextVisit),
	}
	sum := sha256.Sum256([]byte(strings.Join(parts, "\n")))
	return hex.EncodeToString(sum[:])
}

func postHSKlientStatisticsTest(c Config, customer CustomerSnapshot, visits VisitDatesSnapshot, eventID string) error {
	payload := map[string]any{
		"eventId": eventID,
		"statistics": map[string]any{
			"customerId": customer.ID,
			"guid":       strings.ToUpper(strings.TrimSpace(customer.GUID)),
			"lastVisit":  visits.LastVisit,
			"nextVisit":  visits.NextVisit,
			"asOf":       visits.AsOf,
		},
	}
	var out struct {
		OK               bool `json:"ok"`
		AlreadyProcessed bool `json:"alreadyProcessed"`
	}
	if err := jsonRequest(http.MethodPost, hsKlientURL("statistics/test"), payload, hsKlientHeaders(c), &out); err != nil {
		return err
	}
	if !out.OK {
		return errors.New("HS Klient statistics sync neni OK")
	}
	return nil
}

func postHSKlientCustomerTest(c Config, customer CustomerSnapshot, eventID string) error {
	payload := map[string]any{
		"eventId": eventID,
		"customer": map[string]any{
			"id":             customer.ID,
			"guid":           strings.ToUpper(strings.TrimSpace(customer.GUID)),
			"note":           customer.Note,
			"loyalityPoints": customer.LoyalityPoints,
			"updated":        customer.Updated,
		},
	}
	var out struct {
		OK               bool `json:"ok"`
		AlreadyProcessed bool `json:"alreadyProcessed"`
	}
	if err := jsonRequest(http.MethodPost, hsKlientURL("customer/test"), payload, hsKlientHeaders(c), &out); err != nil {
		return err
	}
	if !out.OK {
		return errors.New("HS Klient customer sync neni OK")
	}
	return nil
}

func syncHSKlientCustomerTest(c Config) error {
	features := readHSSystemFeatures()
	if !features.HSKlientEnabled {
		return nil
	}
	if strings.TrimSpace(c.DBPath) == "" {
		return errors.New("HS Klient sync: chybi HairSoft DB path")
	}

	status, err := getHSKlientStatus(c)
	if err != nil {
		return err
	}
	if !status.Modules.Customer {
		return nil
	}
	if status.TestCustomerID != 0 && status.TestCustomerID != hsKlientTestCustomer {
		return errors.New("HS Klient sync: server vratil jine test customer ID")
	}

	hairSoftDBMu.Lock()
	provider, err := openHairSoftReadProvider(c.DBPath)
	if err != nil {
		hairSoftDBMu.Unlock()
		return err
	}
	customer, readErr := provider.CustomerByID(hsKlientTestCustomer)
	provider.Close()
	hairSoftDBMu.Unlock()
	if readErr != nil {
		return readErr
	}

	eventID := customerEventID(c, customer)
	st := loadHSKlientState()
	if st.LastCustomerEventID == eventID {
		return nil
	}

	if err := postHSKlientCustomerTest(c, customer, eventID); err != nil {
		return err
	}

	st.LastCustomerEventID = eventID
	st.LastCustomerUpdated = customer.Updated
	if err := saveHSKlientState(st); err != nil {
		return err
	}
	logf("[CUSTOMER] SYNC OK id=%d updated=%s points=%d", customer.ID, customer.Updated, customer.LoyalityPoints)
	return nil
}

func syncHSKlientStatisticsTest(c Config) error {
	features := readHSSystemFeatures()
	if !features.HSKlientEnabled {
		return nil
	}
	if strings.TrimSpace(c.DBPath) == "" {
		return errors.New("HS Klient statistics: chybi HairSoft DB path")
	}

	status, err := getHSKlientStatus(c)
	if err != nil {
		return err
	}
	if !status.Modules.Statistics {
		return nil
	}
	if status.TestCustomerID != 0 && status.TestCustomerID != hsKlientTestCustomer {
		return errors.New("HS Klient statistics: server vratil jine test customer ID")
	}

	asOf := time.Now().In(time.Local).Format("2006-01-02 15:04:05")

	hairSoftDBMu.Lock()
	provider, err := openHairSoftReadProvider(c.DBPath)
	if err != nil {
		hairSoftDBMu.Unlock()
		return err
	}
	customer, customerErr := provider.CustomerByID(hsKlientTestCustomer)
	if customerErr != nil {
		provider.Close()
		hairSoftDBMu.Unlock()
		return customerErr
	}
	visits, visitErr := provider.VisitDatesByCustomerID(hsKlientTestCustomer, asOf)
	var debugRows []OrderDebugRow
	if visitErr == nil && visits.NextVisit == "" && !statisticsDebugLogged {
		debugRows, _ = provider.RecentOrdersByCustomerID(hsKlientTestCustomer, 10)
	}
	provider.Close()
	hairSoftDBMu.Unlock()
	if visitErr != nil {
		return visitErr
	}

	if visits.NextVisit == "" && !statisticsDebugLogged {
		logf("[STATISTICS] DEBUG id=%d asof=%s recent_orders=%d", customer.ID, asOf, len(debugRows))
		for _, row := range debugRows {
			logf("[STATISTICS] ORDER id=%d customer=%d date=%s end=%s valid=%d canceled=%d break=%d inserted=%s changed=%s",
				row.ID, row.CustomerID, row.OrderDate, row.OrderEndDate, row.Valid, row.Canceled, row.Break, row.Inserted, row.Changed)
		}
		statisticsDebugLogged = true
	}

	eventID := statisticsEventID(c, customer, visits)
	st := loadHSKlientState()
	if st.LastStatisticsEventID == eventID {
		return nil
	}

	if err := postHSKlientStatisticsTest(c, customer, visits, eventID); err != nil {
		return err
	}

	st.LastStatisticsEventID = eventID
	if err := saveHSKlientState(st); err != nil {
		return err
	}
	logf("[STATISTICS] SYNC OK id=%d last=%s next=%s", customer.ID, visits.LastVisit, visits.NextVisit)
	return nil
}

func runHSKlientModule() {
	var customerErr string
	var statisticsErr string
	for {
		c, err := loadConfig()
		if err != nil {
			msg := err.Error()
			if msg != customerErr {
				logf("[HSKLIENT] CONFIG ERROR %v", err)
				customerErr = msg
			}
			time.Sleep(hsKlientPollInterval)
			continue
		}

		if err := syncHSKlientCustomerTest(c); err != nil {
			msg := err.Error()
			if msg != customerErr {
				logf("[CUSTOMER] SYNC ERROR %v", err)
				customerErr = msg
			}
		} else {
			customerErr = ""
		}

		if err := syncHSKlientStatisticsTest(c); err != nil {
			msg := err.Error()
			if msg != statisticsErr {
				logf("[STATISTICS] SYNC ERROR %v", err)
				statisticsErr = msg
			}
		} else {
			statisticsErr = ""
		}

		time.Sleep(hsKlientPollInterval)
	}
}
