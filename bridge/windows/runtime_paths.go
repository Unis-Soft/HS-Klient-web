//go:build windows

package main

import (
	"os"
	"path/filepath"
	"strings"
	"time"
)

// The log file is chosen once when HSBridge starts. If the PC/application
// keeps running across midnight, the same log continues across multiple days.
// A restart on a later day naturally starts a new dated file.
var bridgeLogStartDate = time.Now().Format("2006-01-02")

// bridgeRuntimeRoot returns the HairSoft program root without scanning disks.
// It uses the same standard Settings.xml candidates as DB discovery.
func bridgeRuntimeRoot() string {
	for _, settings := range settingsCandidates() {
		if _, err := os.Stat(settings); err == nil {
			return filepath.Dir(settings)
		}
	}
	return ""
}

func bridgeLogPath() (string, error) {
	name := "HSBridge_" + bridgeLogStartDate + ".log"

	if root := strings.TrimSpace(bridgeRuntimeRoot()); root != "" {
		// Reuse HairSoft's existing common log directory.
		dir := filepath.Join(root, "log")
		if err := os.MkdirAll(dir, 0700); err != nil {
			return "", err
		}
		return filepath.Join(dir, name), nil
	}

	// Fallback is used only if HairSoft has not been discovered yet.
	d, err := appDir()
	if err != nil {
		return "", err
	}
	dir := filepath.Join(d, "log")
	if err := os.MkdirAll(dir, 0700); err != nil {
		return "", err
	}
	return filepath.Join(dir, name), nil
}
