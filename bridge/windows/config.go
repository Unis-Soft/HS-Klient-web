//go:build windows

package main

import (
	"crypto/rand"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
)

type TemplateMapping struct {
	TemplateID             string `json:"templateId"`
	CommodityID            int64  `json:"commodityId"`
	SellPriceID            int64  `json:"sellPriceId"`
	BuyPriceID             int64  `json:"buyPriceId"`
	StoreID                int64  `json:"storeId"`
	ValidityMonthsOverride int64  `json:"validityMonthsOverride,omitempty"`
	ValidityTime           string `json:"validityTime"`
}

type Config struct {
	Version          int                        `json:"version"`
	DBPath           string                     `json:"dbPath"`
	ServerURL        string                     `json:"serverUrl"`
	BridgeID         string                     `json:"bridgeId"`
	BridgeToken      string                     `json:"bridgeToken"`
	PairingCode      string                     `json:"pairingCode"`
	CentreID         int64                      `json:"centreId"`
	UserID           int64                      `json:"userId"`
	PollSeconds      int                        `json:"pollSeconds"`
	PaymentMappings  map[string]int64           `json:"paymentMappings"`
	TemplateMappings map[string]TemplateMapping `json:"templateMappings"`
	StartupInstalled bool                       `json:"startupInstalled"`
	ConfigRevision   int                        `json:"configRevision,omitempty"`
	VoucherEnabled   bool                       `json:"voucherEnabled,omitempty"`
}

func defaultConfig() Config {
	return Config{
		Version:          1,
		PollSeconds:      600,
		PaymentMappings:  map[string]int64{},
		TemplateMappings: map[string]TemplateMapping{},
	}
}

func appDir() (string, error) {
	base := strings.TrimSpace(os.Getenv("LOCALAPPDATA"))
	if base == "" {
		d, err := os.UserConfigDir()
		if err != nil {
			return "", err
		}
		base = d
	}
	p := filepath.Join(base, "UnisSoft", "HSBridge")
	if err := os.MkdirAll(p, 0700); err != nil {
		return "", err
	}
	return p, nil
}

func legacyConfigPath() (string, error) {
	base := strings.TrimSpace(os.Getenv("LOCALAPPDATA"))
	if base == "" {
		d, err := os.UserConfigDir()
		if err != nil {
			return "", err
		}
		base = d
	}
	return filepath.Join(base, "UnisSoft", "HSVoucherBridge", "config.json"), nil
}


func configPath() (string, error) {
	d, err := appDir()
	if err != nil {
		return "", err
	}
	return filepath.Join(d, "config.json"), nil
}

func logPath() (string, error) {
	return bridgeLogPath()
}

func loadConfig() (Config, error) {
	p, err := configPath()
	if err != nil {
		return Config{}, err
	}
	b, err := os.ReadFile(p)
	migrated := false
	if err != nil && os.IsNotExist(err) {
		legacy, legacyErr := legacyConfigPath()
		if legacyErr == nil {
			if legacyBytes, readErr := os.ReadFile(legacy); readErr == nil {
				b = legacyBytes
				err = nil
				migrated = true
			}
		}
	}
	if err != nil {
		return Config{}, err
	}
	c := defaultConfig()
	if err := json.Unmarshal(b, &c); err != nil {
		return Config{}, err
	}
	normalizeConfig(&c)
	if migrated {
		// Legacy HSVoucherBridge config means this PC is already intentionally
		// used for the Cooper voucher flow. Preserve that module, but never
		// enable voucher pairing on a fresh Programs-only PC.
		c.VoucherEnabled = true
		c.StartupInstalled = false
		if saveErr := saveConfig(c); saveErr != nil {
			return Config{}, saveErr
		}
	}
	return c, nil
}

func normalizeConfig(c *Config) {
	if c.PaymentMappings == nil {
		c.PaymentMappings = map[string]int64{}
	}
	if c.TemplateMappings == nil {
		c.TemplateMappings = map[string]TemplateMapping{}
	}
	// Existing HSBridge configs created from the legacy Cooper bridge did not
	// contain VoucherEnabled yet. Infer it only from concrete voucher-target
	// state so a fresh Programs-only PC stays voucher-free.
	if !c.VoucherEnabled {
		c.VoucherEnabled =
			strings.TrimSpace(c.ServerURL) != "" ||
			c.ConfigRevision > 0 ||
			len(c.PaymentMappings) > 0 ||
			len(c.TemplateMappings) > 0
	}
	// Voucher web/config/job checks use a fixed 10-minute polling interval.
	c.PollSeconds = 600
}

func voucherModuleConfigured(c Config) bool {
	normalizeConfig(&c)
	return c.VoucherEnabled
}

func saveConfig(c Config) error {
	p, err := configPath()
	if err != nil {
		return err
	}
	c.Version = 1
	normalizeConfig(&c)
	b, err := json.MarshalIndent(c, "", "  ")
	if err != nil {
		return err
	}
	tmp := p + ".tmp"
	if err := os.WriteFile(tmp, b, 0600); err != nil {
		return err
	}
	return os.Rename(tmp, p)
}

func ensureBridgeIdentity(c *Config) error {
	if c.BridgeID == "" {
		b := make([]byte, 12)
		if _, err := rand.Read(b); err != nil {
			return err
		}
		c.BridgeID = "hsb_" + strings.ToLower(hex.EncodeToString(b))
	}
	if c.BridgeToken == "" {
		b := make([]byte, 32)
		if _, err := rand.Read(b); err != nil {
			return err
		}
		c.BridgeToken = hex.EncodeToString(b)
	}
	if c.PairingCode == "" {
		code, err := newPairingCode()
		if err != nil {
			return err
		}
		c.PairingCode = code
	}
	return nil
}

func resetPairingCode(c *Config) error {
	code, err := newPairingCode()
	if err != nil {
		return err
	}
	c.PairingCode = code
	return saveConfig(*c)
}

func newPairingCode() (string, error) {
	b := make([]byte, 4)
	if _, err := rand.Read(b); err != nil {
		return "", err
	}
	n := (uint32(b[0])<<24 | uint32(b[1])<<16 | uint32(b[2])<<8 | uint32(b[3])) % 1000000
	return fmt.Sprintf("%06d", n), nil
}

func installStartup(enable bool) error {
	exe, err := os.Executable()
	if err != nil {
		return err
	}
	name := "HSBridge"
	key := `HKCU\Software\Microsoft\Windows\CurrentVersion\Run`
	if enable {
		value := `"` + exe + `"`
		cmd := exec.Command("reg.exe", "ADD", key, "/v", name, "/t", "REG_SZ", "/d", value, "/f")
		if out, err := cmd.CombinedOutput(); err != nil {
			return fmt.Errorf("autostart: %v: %s", err, strings.TrimSpace(string(out)))
		}
		// Remove the legacy startup entry only after HSBridge was installed.
		legacy := exec.Command("reg.exe", "DELETE", key, "/v", "HSVoucherBridge", "/f")
		_, _ = legacy.CombinedOutput()
		return nil
	}
	cmd := exec.Command("reg.exe", "DELETE", key, "/v", name, "/f")
	if out, err := cmd.CombinedOutput(); err != nil {
		s := strings.ToLower(string(out))
		if !strings.Contains(s, "unable to find") && !strings.Contains(s, "nenale") {
			return fmt.Errorf("autostart remove: %v: %s", err, strings.TrimSpace(string(out)))
		}
	}
	return nil
}
