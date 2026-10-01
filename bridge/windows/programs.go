//go:build windows

package main

import (
	"crypto/sha256"
	"encoding/hex"
	"encoding/json"
	"errors"
	"net/http"
	"strings"
	"time"
)

func programsEventID(c Config, snapshot ProgramsSnapshot) (string, error) {
	// AsOf is observation time, not business data. Exclude it so an unchanged
	// HairSoft program snapshot is not resent every polling cycle.
	snapshot.AsOf = ""
	body, err := json.Marshal(snapshot)
	if err != nil {
		return "", err
	}
	sum := sha256.Sum256(append([]byte(c.BridgeID+"\nprograms-v004\n"), body...))
	return hex.EncodeToString(sum[:]), nil
}

func postHSKlientPrograms(c Config, snapshot ProgramsSnapshot, eventID string) error {
	payload := map[string]any{
		"eventId":  eventID,
		"programs": snapshot,
	}
	var out struct {
		OK               bool `json:"ok"`
		AlreadyProcessed bool `json:"alreadyProcessed"`
	}
	if err := jsonRequest(http.MethodPost, hsKlientURL("programs/snapshot"), payload, hsKlientHeaders(c), &out); err != nil {
		return err
	}
	if !out.OK {
		return errors.New("HS Klient programs sync neni OK")
	}
	return nil
}

func syncHSKlientPrograms(c Config) error {
	features := readHSSystemFeatures()
	if !features.HSKlientEnabled {
		return nil
	}
	if strings.TrimSpace(c.DBPath) == "" {
		return errors.New("HS Klient programs: chybi HairSoft DB path")
	}

	status, err := getHSKlientStatus(c)
	if err != nil {
		return err
	}
	if !status.Modules.Programs {
		return nil
	}

	hairSoftDBMu.Lock()
	provider, err := openHairSoftReadProvider(c.DBPath)
	if err != nil {
		hairSoftDBMu.Unlock()
		return err
	}
	snapshot, readErr := provider.ProgramsSnapshot()
	provider.Close()
	hairSoftDBMu.Unlock()
	if readErr != nil {
		return readErr
	}

	eventID, err := programsEventID(c, snapshot)
	if err != nil {
		return err
	}
	st := loadHSKlientState()
	if st.LastProgramsEventID == eventID {
		return nil
	}

	if err := postHSKlientPrograms(c, snapshot, eventID); err != nil {
		return err
	}

	st.LastProgramsEventID = eventID
	if err := saveHSKlientState(st); err != nil {
		return err
	}
	logf("[PROGRAMS] SYNC OK programs=%d payments=%d visits=%d values=%d customer_values=%d",
		len(snapshot.Programs), len(snapshot.Payments), len(snapshot.Visits), len(snapshot.ValueDefinitions), len(snapshot.Values))
	return nil
}

const programsPollInterval = 15 * time.Minute

func runProgramsModule() {
	var lastErr string
	for {
		c, err := loadConfig()
		if err != nil {
			msg := err.Error()
			if msg != lastErr {
				logf("[PROGRAMS] CONFIG ERROR %v", err)
				lastErr = msg
			}
			time.Sleep(programsPollInterval)
			continue
		}

		if err := syncHSKlientPrograms(c); err != nil {
			msg := err.Error()
			if msg != lastErr {
				logf("[PROGRAMS] SYNC ERROR %v", err)
				lastErr = msg
			}
		} else {
			lastErr = ""
		}

		time.Sleep(programsPollInterval)
	}
}
