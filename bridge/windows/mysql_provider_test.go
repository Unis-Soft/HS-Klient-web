//go:build windows

package main

import (
	"errors"
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


func TestBridgeTrafficPolicySQLiteIsActive(t *testing.T) {
	p := bridgeTrafficPolicyFromMySQL(mysqlHairSoftSettings{}, "", false, nil)
	if !p.Allowed || p.Role != bridgeRoleSQLite {
		t.Fatalf("unexpected SQLite policy: %#v", p)
	}
}

func TestBridgeTrafficPolicyMySQLServerIsActive(t *testing.T) {
	for _, host := range []string{"localhost", "LOCALHOST", "127.0.0.1", "::1"} {
		p := bridgeTrafficPolicyFromMySQL(mysqlHairSoftSettings{Host: host}, "Settings.xml", true, nil)
		if !p.Allowed || p.Role != bridgeRoleMySQLServer {
			t.Fatalf("host %q should be active MySQL server: %#v", host, p)
		}
	}
}

func TestBridgeTrafficPolicyMySQLClientIsPassive(t *testing.T) {
	for _, host := range []string{"192.168.1.10", "10.0.0.15", "hairsoft-server"} {
		p := bridgeTrafficPolicyFromMySQL(mysqlHairSoftSettings{Host: host}, "Settings.xml", true, nil)
		if p.Allowed || p.Role != bridgeRoleMySQLClient {
			t.Fatalf("host %q should be passive MySQL client: %#v", host, p)
		}
	}
}

func TestBridgeTrafficPolicyInvalidMySQLFailsClosed(t *testing.T) {
	p := bridgeTrafficPolicyFromMySQL(
		mysqlHairSoftSettings{Host: "192.168.1.10"},
		"Settings.xml",
		true,
		errors.New("invalid SQL config"),
	)
	if p.Allowed || p.Role != bridgeRoleMySQLInvalid {
		t.Fatalf("invalid MySQL settings must disable Bridge traffic: %#v", p)
	}
}
