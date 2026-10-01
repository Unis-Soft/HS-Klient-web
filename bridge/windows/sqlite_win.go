//go:build windows

package main

import (
	"errors"
	"fmt"
	"strconv"
	"strings"
	"syscall"
	"time"
	"unicode/utf8"
	"unsafe"
)

const (
	sqliteOK     = 0
	sqliteBusy   = 5
	sqliteLocked = 6
	sqliteRow    = 100
	sqliteDone   = 101

	sqliteOpenReadOnly  = 0x00000001
	sqliteOpenReadWrite = 0x00000002
	sqliteOpenFullMutex = 0x00010000
	sqliteNull          = 5
)

var (
	sqliteDLL        = syscall.NewLazyDLL("winsqlite3.dll")
	pOpenV2          = sqliteDLL.NewProc("sqlite3_open_v2")
	pCloseV2         = sqliteDLL.NewProc("sqlite3_close_v2")
	pErrmsg          = sqliteDLL.NewProc("sqlite3_errmsg")
	pExec            = sqliteDLL.NewProc("sqlite3_exec")
	pFree            = sqliteDLL.NewProc("sqlite3_free")
	pBusyTimeout     = sqliteDLL.NewProc("sqlite3_busy_timeout")
	pPrepareV2       = sqliteDLL.NewProc("sqlite3_prepare_v2")
	pStep            = sqliteDLL.NewProc("sqlite3_step")
	pFinalize        = sqliteDLL.NewProc("sqlite3_finalize")
	pBindInt64       = sqliteDLL.NewProc("sqlite3_bind_int64")
	pBindText        = sqliteDLL.NewProc("sqlite3_bind_text")
	pBindNull        = sqliteDLL.NewProc("sqlite3_bind_null")
	pColumnInt64     = sqliteDLL.NewProc("sqlite3_column_int64")
	pColumnText      = sqliteDLL.NewProc("sqlite3_column_text")
	pColumnBytes     = sqliteDLL.NewProc("sqlite3_column_bytes")
	pColumnType      = sqliteDLL.NewProc("sqlite3_column_type")
	pLastInsertRowID = sqliteDLL.NewProc("sqlite3_last_insert_rowid")

	kernel32             = syscall.NewLazyDLL("kernel32.dll")
	pSetConsoleOutputCP  = kernel32.NewProc("SetConsoleOutputCP")
	pSetConsoleCP        = kernel32.NewProc("SetConsoleCP")
	pMultiByteToWideChar = kernel32.NewProc("MultiByteToWideChar")
)

type DB struct{ ptr uintptr }

type Stmt struct {
	ptr uintptr
	db  *DB
}

func initConsoleUTF8() {
	pSetConsoleOutputCP.Call(65001)
	pSetConsoleCP.Call(65001)
}

func openDBReadOnly(path string) (*DB, error) {
	p, e := syscall.BytePtrFromString(path)
	if e != nil {
		return nil, e
	}
	var db uintptr
	rc, _, _ := pOpenV2.Call(uintptr(unsafe.Pointer(p)), uintptr(unsafe.Pointer(&db)), sqliteOpenReadOnly|sqliteOpenFullMutex, 0)
	if int(rc) != sqliteOK {
		if db != 0 {
			d := &DB{db}
			msg := d.Errmsg()
			d.Close()
			return nil, fmt.Errorf("sqlite readonly open rc=%d: %s", rc, msg)
		}
		return nil, fmt.Errorf("sqlite readonly open rc=%d", rc)
	}
	return &DB{db}, nil
}

func openDB(path string) (*DB, error) {
	p, e := syscall.BytePtrFromString(path)
	if e != nil {
		return nil, e
	}
	var db uintptr
	rc, _, _ := pOpenV2.Call(uintptr(unsafe.Pointer(p)), uintptr(unsafe.Pointer(&db)), sqliteOpenReadWrite|sqliteOpenFullMutex, 0)
	if int(rc) != sqliteOK {
		if db != 0 {
			d := &DB{db}
			msg := d.Errmsg()
			d.Close()
			return nil, fmt.Errorf("sqlite open rc=%d: %s", rc, msg)
		}
		return nil, fmt.Errorf("sqlite open rc=%d", rc)
	}
	return &DB{db}, nil
}

func (d *DB) Close() {
	if d != nil && d.ptr != 0 {
		pCloseV2.Call(d.ptr)
		d.ptr = 0
	}
}

func (d *DB) BusyTimeout(ms int) { pBusyTimeout.Call(d.ptr, uintptr(ms)) }

func (d *DB) Errmsg() string {
	p, _, _ := pErrmsg.Call(d.ptr)
	if p == 0 {
		return ""
	}
	return cString(p)
}

func (d *DB) Exec(sql string) error {
	p, e := syscall.BytePtrFromString(sql)
	if e != nil {
		return e
	}
	var em uintptr
	rc, _, _ := pExec.Call(d.ptr, uintptr(unsafe.Pointer(p)), 0, 0, uintptr(unsafe.Pointer(&em)))
	if int(rc) != sqliteOK {
		msg := d.Errmsg()
		if em != 0 {
			msg = cString(em)
			pFree.Call(em)
		}
		return fmt.Errorf("sqlite rc=%d: %s", rc, msg)
	}
	return nil
}

func (d *DB) Prepare(sql string) (*Stmt, error) {
	p, e := syscall.BytePtrFromString(sql)
	if e != nil {
		return nil, e
	}
	var st uintptr
	rc, _, _ := pPrepareV2.Call(d.ptr, uintptr(unsafe.Pointer(p)), uintptr(len(sql)), uintptr(unsafe.Pointer(&st)), 0)
	if int(rc) != sqliteOK {
		return nil, fmt.Errorf("prepare: %s", d.Errmsg())
	}
	return &Stmt{st, d}, nil
}

func (d *DB) LastInsertRowID() int64 {
	r, _, _ := pLastInsertRowID.Call(d.ptr)
	return int64(r)
}

func (d *DB) QueryInt64(sql string) (int64, error) {
	st, e := d.Prepare(sql)
	if e != nil {
		return 0, e
	}
	defer st.Finalize()
	if st.Step() != sqliteRow {
		return 0, fmt.Errorf("query int: %s", d.Errmsg())
	}
	return st.ColInt64(0), nil
}

func (d *DB) QueryBytes(sql string) ([]byte, error) {
	st, e := d.Prepare(sql)
	if e != nil {
		return nil, e
	}
	defer st.Finalize()
	if st.Step() != sqliteRow {
		return nil, fmt.Errorf("query text: %s", d.Errmsg())
	}
	return st.ColBytes(0), nil
}

func (s *Stmt) Finalize() {
	if s != nil && s.ptr != 0 {
		pFinalize.Call(s.ptr)
		s.ptr = 0
	}
}

func (s *Stmt) Step() int { r, _, _ := pStep.Call(s.ptr); return int(r) }

func (s *Stmt) StepDone() error {
	rc := s.Step()
	if rc != sqliteDone {
		return fmt.Errorf("sqlite step rc=%d: %s", rc, s.db.Errmsg())
	}
	return nil
}

func (s *Stmt) BindAll(vals []any) error {
	for i, v := range vals {
		if e := s.Bind(i+1, v); e != nil {
			return e
		}
	}
	return nil
}

func (s *Stmt) Bind(i int, v any) error {
	var rc uintptr
	switch x := v.(type) {
	case nil:
		rc, _, _ = pBindNull.Call(s.ptr, uintptr(i))
	case int:
		rc, _, _ = pBindInt64.Call(s.ptr, uintptr(i), uintptr(int64(x)))
	case int64:
		rc, _, _ = pBindInt64.Call(s.ptr, uintptr(i), uintptr(x))
	case float64:
		return s.bindBytes(i, []byte(strconv.FormatFloat(x, 'f', -1, 64)))
	case string:
		return s.bindBytes(i, []byte(x))
	case []byte:
		return s.bindBytes(i, x)
	default:
		return fmt.Errorf("nepodporovany bind typ %T", v)
	}
	if int(rc) != sqliteOK {
		return fmt.Errorf("bind %d: %s", i, s.db.Errmsg())
	}
	return nil
}

func (s *Stmt) bindBytes(i int, b []byte) error {
	if b == nil {
		return s.Bind(i, nil)
	}
	var ptr uintptr
	if len(b) > 0 {
		ptr = uintptr(unsafe.Pointer(&b[0]))
	}
	transient := ^uintptr(0)
	rc, _, _ := pBindText.Call(s.ptr, uintptr(i), ptr, uintptr(len(b)), transient)
	if int(rc) != sqliteOK {
		return fmt.Errorf("bind text %d: %s", i, s.db.Errmsg())
	}
	return nil
}

func (s *Stmt) ColInt64(i int) int64 {
	r, _, _ := pColumnInt64.Call(s.ptr, uintptr(i))
	return int64(r)
}

func (s *Stmt) ColDouble(i int) float64 {
	b := s.ColBytes(i)
	v, _ := strconv.ParseFloat(string(b), 64)
	return v
}

func (s *Stmt) ColBytes(i int) []byte {
	typ, _, _ := pColumnType.Call(s.ptr, uintptr(i))
	if int(typ) == sqliteNull {
		return nil
	}
	p, _, _ := pColumnText.Call(s.ptr, uintptr(i))
	if p == 0 {
		return []byte{}
	}
	n, _, _ := pColumnBytes.Call(s.ptr, uintptr(i))
	if n == 0 {
		return []byte{}
	}
	src := unsafe.Slice((*byte)(unsafe.Pointer(p)), int(n))
	out := make([]byte, len(src))
	copy(out, src)
	return out
}

func isBusyError(e error) bool {
	if e == nil {
		return false
	}
	s := strings.ToLower(e.Error())
	return strings.Contains(s, fmt.Sprintf("rc=%d", sqliteBusy)) ||
		strings.Contains(s, fmt.Sprintf("rc=%d", sqliteLocked)) ||
		strings.Contains(s, "busy") || strings.Contains(s, "locked")
}

func beginImmediateRetry(db *DB, wait time.Duration) error {
	deadline := time.Now().Add(wait)
	for {
		err := db.Exec("BEGIN IMMEDIATE")
		if err == nil {
			return nil
		}
		if !isBusyError(err) || !time.Now().Before(deadline) {
			return err
		}
		time.Sleep(200 * time.Millisecond)
	}
}

func displayText(b []byte) string {
	if len(b) == 0 {
		return ""
	}
	if utf8.Valid(b) {
		return string(b)
	}
	const cp1250 = 1250
	n, _, _ := pMultiByteToWideChar.Call(cp1250, 0, uintptr(unsafe.Pointer(&b[0])), uintptr(len(b)), 0, 0)
	if n == 0 {
		return fmt.Sprintf("% X", b)
	}
	buf := make([]uint16, int(n)+1)
	pMultiByteToWideChar.Call(cp1250, 0, uintptr(unsafe.Pointer(&b[0])), uintptr(len(b)), uintptr(unsafe.Pointer(&buf[0])), n)
	return syscall.UTF16ToString(buf)
}

func cString(p uintptr) string {
	if p == 0 {
		return ""
	}
	var b []byte
	for i := uintptr(0); ; i++ {
		v := *(*byte)(unsafe.Pointer(p + i))
		if v == 0 {
			break
		}
		b = append(b, v)
	}
	return string(b)
}

func boolFromRow(st *Stmt, col int) bool { return st.ColInt64(col) != 0 }

var errNoRow = errors.New("radek nebyl nalezen")
