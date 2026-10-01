//go:build windows

package main

import "sync"

// All local HairSoft database access is serialized inside HSBridge.
// HairSoft itself always has priority; read-only modules use busy_timeout=0.
var hairSoftDBMu sync.Mutex
