//go:build windows

package main

import (
	"bufio"
	"os/exec"
	"strconv"
	"strings"
	"syscall"
)

const hsSysRegistryKey = `HKCU\SOFTWARE\VB and VBA Program Settings\TMKSW\HS SYS`

// HSSystemFeatures are read-only feature hints written by SoftSYS.
// HSBridge never changes these registry values.
type HSSystemFeatures struct {
	HSKlientEnabled       bool
	RemoteDashboardExists bool
}

func readHSSystemFeatures() HSSystemFeatures {
	var out HSSystemFeatures
	cmd := exec.Command("reg.exe", "QUERY", hsSysRegistryKey)
	cmd.SysProcAttr = &syscall.SysProcAttr{HideWindow: true}
	b, err := cmd.CombinedOutput()
	if err != nil {
		return out
	}
	sc := bufio.NewScanner(strings.NewReader(string(b)))
	for sc.Scan() {
		line := strings.TrimSpace(sc.Text())
		if line == "" {
			continue
		}
		fields := strings.Fields(line)
		if len(fields) < 3 {
			continue
		}
		name := strings.ToLower(strings.TrimSpace(fields[0]))
		value := strings.TrimSpace(strings.Join(fields[2:], " "))
		switch name {
		case "dashboard":
			n, _ := strconv.Atoi(value)
			out.HSKlientEnabled = n == 1
		case "dashboardvzdalenyhash":
			out.RemoteDashboardExists = value != ""
		}
	}
	return out
}
