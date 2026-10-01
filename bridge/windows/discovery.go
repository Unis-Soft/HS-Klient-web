//go:build windows

package main

import (
	"encoding/binary"
	"errors"
	"fmt"
	"html"
	"os"
	"path/filepath"
	"regexp"
	"strings"
	"unicode/utf16"
	"unicode/utf8"
)

var databaseFileRe = regexp.MustCompile(`(?is)<\s*DatabaseFile\s*>\s*([^<]+?)\s*<\s*/\s*DatabaseFile\s*>`)

func executableDir() string {
	exe, err := os.Executable()
	if err != nil {
		return ""
	}
	return filepath.Dir(exe)
}

func settingsCandidates() []string {
	raw := []string{
		filepath.Join(executableDir(), "Settings.xml"),
	}
	// Standard HairSoft installation can live on any local drive.
	// Check only the conventional root; never scan whole drives.
	for drive := 'C'; drive <= 'Z'; drive++ {
		raw = append(raw, fmt.Sprintf("%c:\\HairSoft\\Settings.xml", drive))
	}
	seen := map[string]bool{}
	var out []string
	for _, p := range raw {
		p = strings.TrimSpace(p)
		if p == "" {
			continue
		}
		k := strings.ToLower(filepath.Clean(p))
		if seen[k] {
			continue
		}
		seen[k] = true
		out = append(out, p)
	}
	return out
}

func decodeXMLText(b []byte) string {
	if len(b) >= 3 && b[0] == 0xef && b[1] == 0xbb && b[2] == 0xbf {
		b = b[3:]
	}
	if len(b) >= 2 && b[0] == 0xff && b[1] == 0xfe {
		var u []uint16
		for i := 2; i+1 < len(b); i += 2 {
			u = append(u, binary.LittleEndian.Uint16(b[i:i+2]))
		}
		return string(utf16.Decode(u))
	}
	if len(b) >= 2 && b[0] == 0xfe && b[1] == 0xff {
		var u []uint16
		for i := 2; i+1 < len(b); i += 2 {
			u = append(u, binary.BigEndian.Uint16(b[i:i+2]))
		}
		return string(utf16.Decode(u))
	}
	if utf8.Valid(b) {
		return string(b)
	}
	return displayText(b)
}

func databaseFileFromSettings(path string) (string, error) {
	b, err := os.ReadFile(path)
	if err != nil {
		return "", err
	}
	text := decodeXMLText(b)
	m := databaseFileRe.FindStringSubmatch(text)
	if len(m) < 2 {
		return "", errors.New("v Settings.xml nebyl nalezen element DatabaseFile")
	}
	dbPath := strings.TrimSpace(html.UnescapeString(m[1]))
	dbPath = strings.Trim(dbPath, `"`)
	if dbPath == "" {
		return "", errors.New("DatabaseFile je prazdne")
	}
	if !filepath.IsAbs(dbPath) {
		dbPath = filepath.Join(filepath.Dir(path), dbPath)
	}
	return filepath.Clean(dbPath), nil
}

func validateDBPath(path string) error {
	path = strings.TrimSpace(path)
	if path == "" {
		return errors.New("chybi cesta k data.sdb")
	}
	st, err := os.Stat(path)
	if err != nil {
		return err
	}
	if st.IsDir() {
		return errors.New("cesta ukazuje na adresar")
	}
	db, err := openDB(path)
	if err != nil {
		return err
	}
	defer db.Close()
	db.BusyTimeout(1500)
	return validateDB(db)
}

func discoverHairSoftDB(c Config) (string, string, error) {
	if strings.TrimSpace(c.DBPath) != "" {
		if err := validateDBPath(c.DBPath); err == nil {
			return filepath.Clean(c.DBPath), "config", nil
		}
	}

	var settingsUsed string
	for _, settings := range settingsCandidates() {
		if _, err := os.Stat(settings); err != nil {
			continue
		}
		settingsUsed = settings
		dbPath, err := databaseFileFromSettings(settings)
		if err != nil {
			logf("DB SETTINGS %s: %v", settings, err)
			continue
		}
		logf("DB SETTINGS %s -> DatabaseFile=%s", settings, dbPath)
		if err := validateDBPath(dbPath); err == nil {
			return dbPath, "Settings.xml", nil
		} else {
			logf("DB SETTINGS candidate %s neni kompatibilni: %v", dbPath, err)
		}
	}

	fallbacks := []string{
		filepath.Join(executableDir(), "data", "data.sdb"),
		filepath.Join(executableDir(), "data.sdb"),
	}
	for drive := 'C'; drive <= 'Z'; drive++ {
		fallbacks = append(fallbacks,
			fmt.Sprintf("%c:\\HairSoft\\data\\data.sdb", drive),
			fmt.Sprintf("%c:\\HairSoft\\data.sdb", drive),
		)
	}
	seen := map[string]bool{}
	for _, path := range fallbacks {
		path = filepath.Clean(path)
		k := strings.ToLower(path)
		if seen[k] {
			continue
		}
		seen[k] = true
		if err := validateDBPath(path); err == nil {
			return path, "fallback", nil
		}
	}
	fallbackText := "standardni <disk>:\\HairSoft\\data\\data.sdb nebo <disk>:\\HairSoft\\data.sdb"
	return "", "", fmt.Errorf("HairSoft data.sdb nebyla nalezena; Settings.xml: %s; fallback: %s", settingsUsed, fallbackText)
}
