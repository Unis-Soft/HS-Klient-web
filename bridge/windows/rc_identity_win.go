//go:build windows

package main

import (
	"bufio"
	"errors"
	"os/exec"
	"strings"
	"syscall"
)

const rcRegistryKey = `HKCU\SOFTWARE\VB and VBA Program Settings\TMKSW\RC V1`

type RCIdentity struct {
	Setting3  string `json:"setting3,omitempty"`
	Setting10 string `json:"setting10,omitempty"`
}

func readRCIdentity() (RCIdentity, error) {
	var out RCIdentity
	cmd := exec.Command("reg.exe", "QUERY", rcRegistryKey)
	cmd.SysProcAttr = &syscall.SysProcAttr{HideWindow: true}
	b, err := cmd.CombinedOutput()
	if err != nil {
		return out, err
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
		name := strings.TrimSpace(fields[0])
		value := strings.TrimSpace(strings.Join(fields[2:], " "))
		switch strings.ToLower(name) {
		case "setting3":
			out.Setting3 = value
		case "setting10":
			out.Setting10 = value
		}
	}
	if err := sc.Err(); err != nil {
		return out, err
	}
	if out.Setting3 == "" && out.Setting10 == "" {
		return out, errors.New("RC V1 neobsahuje Setting3 ani Setting10")
	}
	return out, nil
}
