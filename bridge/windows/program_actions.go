//go:build windows

package main

import (
	"encoding/json"
	"errors"
	"fmt"
	"net/http"
	"net/url"
	"os"
	"path/filepath"
	"strings"
	"time"
)

const (
	hsKlientProgramActionsBaseURL = "https://klient.hairsoft.cz/str/api/hsbridge-program-actions.php"
	programActionsPollInterval    = 10 * time.Second
)

type programActionCommand struct {
	ID         int64  `json:"id"`
	UUID       string `json:"uuid"`
	CustomerID int64  `json:"customerId"`
	ProgramID  int64  `json:"programId"`
	Quantity   int64  `json:"quantity"`
}

type programActionApplied struct {
	CommandID int64  `json:"commandId"`
	UUID      string `json:"uuid"`
	VisitID   int64  `json:"visitId"`
}

type programActionState struct {
	Applied []programActionApplied `json:"applied,omitempty"`
}

func programActionURL(route string) string {
	return hsKlientProgramActionsBaseURL + "?route=" + url.QueryEscape(route)
}

func getNextProgramAction(c Config) (*programActionCommand, error) {
	var out struct {
		OK      bool                  `json:"ok"`
		Command *programActionCommand `json:"command"`
	}
	if err := jsonRequest(http.MethodGet, programActionURL("next"), nil, hsKlientHeaders(c), &out); err != nil {
		return nil, err
	}
	if !out.OK {
		return nil, errors.New("HS Klient PROGRAMS action queue neni OK")
	}
	return out.Command, nil
}

func postProgramActionResult(c Config, commandID int64, status string, visitID int64, visitAt string, message string) error {
	payload := map[string]any{
		"commandId": commandID,
		"status":    status,
		"visitId":   visitID,
		"visitAt":   visitAt,
		"message":   message,
	}
	var out struct {
		OK bool `json:"ok"`
	}
	if err := jsonRequest(http.MethodPost, programActionURL("result"), payload, hsKlientHeaders(c), &out); err != nil {
		return err
	}
	if !out.OK {
		return errors.New("HS Klient PROGRAMS action result neni OK")
	}
	return nil
}

func programActionStatePath() (string, error) {
	d, err := appDir()
	if err != nil {
		return "", err
	}
	return filepath.Join(d, "hsklient-program-actions.json"), nil
}

func loadProgramActionState() programActionState {
	var st programActionState
	p, err := programActionStatePath()
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

func saveProgramActionState(st programActionState) error {
	if len(st.Applied) > 200 {
		st.Applied = append([]programActionApplied(nil), st.Applied[len(st.Applied)-200:]...)
	}
	p, err := programActionStatePath()
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

func appliedProgramAction(commandID int64, uuid string) (int64, bool) {
	st := loadProgramActionState()
	for i := len(st.Applied) - 1; i >= 0; i-- {
		if st.Applied[i].CommandID == commandID && st.Applied[i].UUID == uuid {
			return st.Applied[i].VisitID, true
		}
	}
	return 0, false
}

func rememberAppliedProgramAction(commandID int64, uuid string, visitID int64) error {
	st := loadProgramActionState()
	for i := range st.Applied {
		if st.Applied[i].CommandID == commandID && st.Applied[i].UUID == uuid {
			st.Applied[i].VisitID = visitID
			return saveProgramActionState(st)
		}
	}
	st.Applied = append(st.Applied, programActionApplied{CommandID: commandID, UUID: uuid, VisitID: visitID})
	return saveProgramActionState(st)
}

func insertSQLiteProgramVisit(c Config, cmd programActionCommand) (int64, error) {
	path, _, err := discoverHairSoftDB(c)
	if err != nil {
		return 0, err
	}
	db, err := openDB(path)
	if err != nil {
		return 0, err
	}
	defer db.Close()

	// HairSoft always has priority: Bridge never waits for a SQLite write lock.
	db.BusyTimeout(0)
	st, err := db.Prepare(`INSERT INTO program_visits (id_program,id_customer,visit,quantity) VALUES (?,?,?,?)`)
	if err != nil {
		return 0, err
	}
	defer st.Finalize()
	if err := st.BindAll([]any{cmd.ProgramID, cmd.CustomerID, time.Now().In(time.Local).Format("2006-01-02 15:04:05"), cmd.Quantity}); err != nil {
		return 0, err
	}
	if err := st.StepDone(); err != nil {
		return 0, err
	}
	return db.LastInsertRowID(), nil
}

func insertMySQLProgramVisit(c Config, cmd programActionCommand) (int64, error) {
	cfg, _, present, err := discoverHairSoftMySQL()
	if err != nil {
		return 0, err
	}
	if !present {
		return 0, errors.New("MySQL konfigurace HairSoft neni dostupna")
	}
	provider, err := openMySQLHairSoftProvider(cfg)
	if err != nil {
		return 0, err
	}
	defer provider.Close()
	p, ok := provider.(*mysqlHairSoftProvider)
	if !ok || p == nil {
		return 0, errors.New("MySQL provider HairSoft neni zapisovatelny")
	}
	return p.insertProgramVisit(cmd.ProgramID, cmd.CustomerID, cmd.Quantity)
}

func insertProgramVisit(c Config, cmd programActionCommand) (int64, string, error) {
	_, _, mysqlPresent, discoverErr := discoverHairSoftMySQL()
	if mysqlPresent {
		if discoverErr != nil {
			return 0, "mysql", discoverErr
		}
		id, err := insertMySQLProgramVisit(c, cmd)
		return id, "mysql", err
	}
	id, err := insertSQLiteProgramVisit(c, cmd)
	return id, "sqlite", err
}

func processNextProgramAction(c Config) error {
	cmd, err := getNextProgramAction(c)
	if err != nil {
		return err
	}
	if cmd == nil {
		return nil
	}
	if cmd.ID <= 0 || strings.TrimSpace(cmd.UUID) == "" || cmd.ProgramID <= 0 || cmd.CustomerID <= 0 || cmd.Quantity <= 0 {
		msg := "neplatny PROGRAMS prikaz"
		_ = postProgramActionResult(c, cmd.ID, "failed", 0, "", msg)
		return errors.New(msg)
	}

	// If the HairSoft INSERT succeeded but the previous HTTP acknowledgement
	// failed, do not consume the program a second time.
	if visitID, ok := appliedProgramAction(cmd.ID, cmd.UUID); ok {
		return postProgramActionResult(c, cmd.ID, "done", visitID, "", "")
	}

	// Serialize only Bridge's own access. HairSoft itself is never locked by this
	// mutex; SQLite busy_timeout=0 gives HairSoft absolute priority.
	visitAt := time.Now().In(time.Local).Format("2006-01-02 15:04:05")
	hairSoftDBMu.Lock()
	visitID, backend, writeErr := insertProgramVisit(c, *cmd)
	hairSoftDBMu.Unlock()
	if writeErr != nil {
		msg := strings.TrimSpace(writeErr.Error())
		if msg == "" {
			msg = "HairSoft DB write failed"
		}
		// Any DB problem is retryable. This avoids losing a requested consumption
		// when HairSoft is open, the DB is momentarily busy, or MySQL is restarting.
		if postErr := postProgramActionResult(c, cmd.ID, "retry", 0, "", msg); postErr != nil {
			return fmt.Errorf("PROGRAMS write %v; retry result %v", writeErr, postErr)
		}
		return fmt.Errorf("PROGRAMS write deferred: %w", writeErr)
	}

	// Persist local idempotency before acknowledging the cloud command. If the
	// local state file cannot be saved, still acknowledge the server after a
	// successful HairSoft INSERT so one local file problem cannot cause a
	// duplicate consumption.
	stateErr := rememberAppliedProgramAction(cmd.ID, cmd.UUID, visitID)
	if err := postProgramActionResult(c, cmd.ID, "done", visitID, visitAt, ""); err != nil {
		if stateErr != nil {
			return fmt.Errorf("PROGRAMS result %v; local state %v", err, stateErr)
		}
		return err
	}
	if stateErr != nil {
		logf("[PROGRAMS] ACTION STATE WARN command=%d visit=%d: %v", cmd.ID, visitID, stateErr)
	}
	logf("[PROGRAMS] CONSUME OK command=%d backend=%s program=%d customer=%d quantity=%d visit=%d",
		cmd.ID, backend, cmd.ProgramID, cmd.CustomerID, cmd.Quantity, visitID)

	// Do not perform an immediate full PROGRAMS read after a reverse write.
	// The result endpoint mirrors this one confirmed visit immediately for the
	// affected customer only. The regular full snapshot remains reconciliation.
	return nil
}

func runProgramActionsModule() {
	var lastErr string
	for {
		c, err := loadConfig()
		if err != nil {
			msg := err.Error()
			if msg != lastErr {
				logf("[PROGRAMS] ACTION CONFIG ERROR %v", err)
				lastErr = msg
			}
			time.Sleep(programActionsPollInterval)
			continue
		}

		if err := processNextProgramAction(c); err != nil {
			msg := err.Error()
			if msg != lastErr {
				logf("[PROGRAMS] ACTION DEFERRED %v", err)
				lastErr = msg
			}
		} else {
			lastErr = ""
		}
		time.Sleep(programActionsPollInterval)
	}
}