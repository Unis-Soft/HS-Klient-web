//go:build windows

package main

import (
	"os"
	"path/filepath"
	"testing"
)

func writeSettingsTestFile(t *testing.T, body string) string {
	t.Helper()
	dir := t.TempDir()
	path := filepath.Join(dir, "Settings.xml")
	if err := os.WriteFile(path, []byte(body), 0600); err != nil {
		t.Fatal(err)
	}
	return path
}

func TestMySQLSettingsDetectedWithOpenSQLSettingsZero(t *testing.T) {
	path := writeSettingsTestFile(t, `<?xml version="1.0"?>
<setting><general>
<DatabaseFile>C:\HairSoft\Data\data.sdb</DatabaseFile>
<OpenSQLsettings>0</OpenSQLsettings>
<SQLHost>localhost</SQLHost>
<SQLPort>3306</SQLPort>
<SQLDatabase>data</SQLDatabase>
<SQLUser>remoteHS</SQLUser>
<SQLPasword>secret</SQLPasword>
</general></setting>`)

	cfg, present, err := mysqlSettingsFromSettings(path)
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if !present {
		t.Fatal("MySQL settings were not detected")
	}
	if cfg.Host != "localhost" || cfg.Port != 3306 || cfg.Database != "data" || cfg.User != "remoteHS" || cfg.Password != "secret" {
		t.Fatalf("unexpected config: %#v", cfg)
	}
}

func TestSQLiteSettingsDoNotActivateMySQL(t *testing.T) {
	path := writeSettingsTestFile(t, `<?xml version="1.0"?>
<setting><general>
<DatabaseFile>C:\HairSoft\Data\data.sdb</DatabaseFile>
</general></setting>`)

	_, present, err := mysqlSettingsFromSettings(path)
	if err != nil {
		t.Fatalf("unexpected error: %v", err)
	}
	if present {
		t.Fatal("SQLite-only Settings.xml incorrectly activated MySQL")
	}
}

func TestPartialMySQLSettingsFailClosed(t *testing.T) {
	path := writeSettingsTestFile(t, `<?xml version="1.0"?>
<setting><general>
<SQLHost>server01</SQLHost>
<SQLPort>3306</SQLPort>
</general></setting>`)

	_, present, err := mysqlSettingsFromSettings(path)
	if !present {
		t.Fatal("partial MySQL settings must be recognized as present")
	}
	if err == nil {
		t.Fatal("partial MySQL settings must fail instead of falling back to SQLite")
	}
}
