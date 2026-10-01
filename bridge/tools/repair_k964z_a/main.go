//go:build windows

package main

import (
	"encoding/binary"
	"fmt"
	"html"
	"os"
	"path/filepath"
	"regexp"
	"strings"
	"syscall"
	"time"
	"unicode/utf16"
	"unicode/utf8"
	"unsafe"
)

const (
	oldNumber      int64 = 1054931061
	targetNumber   int64 = 1054930363
	expectedBill   int64 = 130396
	expectedCentre int64 = 1
	expectedUser   int64 = 6
	expectedCode         = "K964Z-A"

	sqliteOK            = 0
	sqliteRow           = 100
	sqliteDone          = 101
	sqliteOpenReadWrite = 0x00000002
	sqliteOpenFullMutex = 0x00010000

	mbOK              = 0x00000000
	mbYesNo           = 0x00000004
	mbIconError       = 0x00000010
	mbIconQuestion    = 0x00000020
	mbIconInformation = 0x00000040
	idYes             = 6
)

var (
	user32       = syscall.NewLazyDLL("user32.dll")
	pMessageBoxW = user32.NewProc("MessageBoxW")

	sqliteDLL    = syscall.NewLazyDLL("winsqlite3.dll")
	pOpenV2      = sqliteDLL.NewProc("sqlite3_open_v2")
	pCloseV2     = sqliteDLL.NewProc("sqlite3_close_v2")
	pErrmsg      = sqliteDLL.NewProc("sqlite3_errmsg")
	pExec        = sqliteDLL.NewProc("sqlite3_exec")
	pFree        = sqliteDLL.NewProc("sqlite3_free")
	pPrepareV2   = sqliteDLL.NewProc("sqlite3_prepare_v2")
	pStep        = sqliteDLL.NewProc("sqlite3_step")
	pFinalize    = sqliteDLL.NewProc("sqlite3_finalize")
	pColumnInt64 = sqliteDLL.NewProc("sqlite3_column_int64")
	pColumnText  = sqliteDLL.NewProc("sqlite3_column_text")
	pColumnBytes = sqliteDLL.NewProc("sqlite3_column_bytes")
	pBusyTimeout = sqliteDLL.NewProc("sqlite3_busy_timeout")
)

type DB struct{ ptr uintptr }
type Stmt struct {
	ptr uintptr
	db  *DB
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

func openDB(path string) (*DB, error) {
	p, err := syscall.BytePtrFromString(path)
	if err != nil {
		return nil, err
	}
	var db uintptr
	rc, _, _ := pOpenV2.Call(uintptr(unsafe.Pointer(p)), uintptr(unsafe.Pointer(&db)), sqliteOpenReadWrite|sqliteOpenFullMutex, 0)
	if int(rc) != sqliteOK {
		if db != 0 {
			d := &DB{db}
			msg := d.errmsg()
			d.close()
			return nil, fmt.Errorf("sqlite open rc=%d: %s", rc, msg)
		}
		return nil, fmt.Errorf("sqlite open rc=%d", rc)
	}
	pBusyTimeout.Call(db, 5000)
	return &DB{db}, nil
}
func (d *DB) close() {
	if d != nil && d.ptr != 0 {
		pCloseV2.Call(d.ptr)
		d.ptr = 0
	}
}
func (d *DB) errmsg() string { p, _, _ := pErrmsg.Call(d.ptr); return cString(p) }
func (d *DB) exec(sql string) error {
	p, err := syscall.BytePtrFromString(sql)
	if err != nil {
		return err
	}
	var em uintptr
	rc, _, _ := pExec.Call(d.ptr, uintptr(unsafe.Pointer(p)), 0, 0, uintptr(unsafe.Pointer(&em)))
	if int(rc) != sqliteOK {
		msg := d.errmsg()
		if em != 0 {
			msg = cString(em)
			pFree.Call(em)
		}
		return fmt.Errorf("sqlite rc=%d: %s", rc, msg)
	}
	return nil
}
func (d *DB) prepare(sql string) (*Stmt, error) {
	p, err := syscall.BytePtrFromString(sql)
	if err != nil {
		return nil, err
	}
	var st uintptr
	rc, _, _ := pPrepareV2.Call(d.ptr, uintptr(unsafe.Pointer(p)), uintptr(len(sql)), uintptr(unsafe.Pointer(&st)), 0)
	if int(rc) != sqliteOK {
		return nil, fmt.Errorf("prepare: %s", d.errmsg())
	}
	return &Stmt{st, d}, nil
}
func (s *Stmt) finalize() {
	if s != nil && s.ptr != 0 {
		pFinalize.Call(s.ptr)
		s.ptr = 0
	}
}
func (s *Stmt) step() int       { r, _, _ := pStep.Call(s.ptr); return int(r) }
func (s *Stmt) i64(i int) int64 { r, _, _ := pColumnInt64.Call(s.ptr, uintptr(i)); return int64(r) }
func (s *Stmt) text(i int) string {
	p, _, _ := pColumnText.Call(s.ptr, uintptr(i))
	if p == 0 {
		return ""
	}
	n, _, _ := pColumnBytes.Call(s.ptr, uintptr(i))
	if n == 0 {
		return ""
	}
	b := unsafe.Slice((*byte)(unsafe.Pointer(p)), int(n))
	return string(b)
}

func message(title, text string, flags uintptr) int {
	t, _ := syscall.UTF16PtrFromString(title)
	m, _ := syscall.UTF16PtrFromString(text)
	r, _, _ := pMessageBoxW.Call(0, uintptr(unsafe.Pointer(m)), uintptr(unsafe.Pointer(t)), flags)
	return int(r)
}

var databaseFileRe = regexp.MustCompile(`(?is)<\s*DatabaseFile\s*>\s*([^<]+?)\s*<\s*/\s*DatabaseFile\s*>`)

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
	return string(b)
}
func dbPath() (string, error) {
	candidates := []string{`C:\HairSoft\Settings.xml`}
	if exe, err := os.Executable(); err == nil {
		candidates = append([]string{filepath.Join(filepath.Dir(exe), "Settings.xml")}, candidates...)
	}
	for _, settings := range candidates {
		b, err := os.ReadFile(settings)
		if err != nil {
			continue
		}
		m := databaseFileRe.FindStringSubmatch(decodeXMLText(b))
		if len(m) < 2 {
			continue
		}
		p := strings.Trim(strings.TrimSpace(html.UnescapeString(m[1])), `"`)
		if p != "" {
			if !filepath.IsAbs(p) {
				p = filepath.Join(filepath.Dir(settings), p)
			}
			if _, err := os.Stat(p); err == nil {
				return filepath.Clean(p), nil
			}
		}
	}
	for _, p := range []string{`C:\HairSoft\data\data.sdb`, `C:\HairSoft\data.sdb`} {
		if _, err := os.Stat(p); err == nil {
			return p, nil
		}
	}
	return "", fmt.Errorf("HairSoft data.sdb nebyla nalezena")
}

type row struct {
	count, id, centre, user, billnumber int64
	price                               string
}

func loadRow(db *DB) (row, error) {
	sql := `SELECT COUNT(*),COALESCE(MIN(b.id),0),COALESCE(MIN(b.id_centre),0),COALESCE(MIN(b.id_user),0),COALESCE(MIN(b.billnumber),0),COALESCE(MIN(CAST(b.price AS TEXT)),'')
FROM bill b JOIN billitem bi ON bi.id_bill=b.id JOIN BillItemCodes bic ON bic.BillItemID=bi.id
WHERE bic.Code='K964Z-A'`
	st, err := db.prepare(sql)
	if err != nil {
		return row{}, err
	}
	defer st.finalize()
	if st.step() != sqliteRow {
		return row{}, fmt.Errorf("kontrolni SELECT selhal: %s", db.errmsg())
	}
	return row{st.i64(0), st.i64(1), st.i64(2), st.i64(3), st.i64(4), st.text(5)}, nil
}
func targetCount(db *DB) (int64, error) {
	st, err := db.prepare(`SELECT COUNT(*) FROM bill WHERE id_centre=1 AND billnumber=1054930363 AND id<>130396`)
	if err != nil {
		return 0, err
	}
	defer st.finalize()
	if st.step() != sqliteRow {
		return 0, fmt.Errorf("nelze overit cilove cislo")
	}
	return st.i64(0), nil
}
func changes(db *DB) (int64, error) {
	st, err := db.prepare(`SELECT changes()`)
	if err != nil {
		return 0, err
	}
	defer st.finalize()
	if st.step() != sqliteRow {
		return 0, fmt.Errorf("nelze zjistit changes")
	}
	return st.i64(0), nil
}
func appendLog(text string) {
	base := strings.TrimSpace(os.Getenv("LOCALAPPDATA"))
	if base == "" {
		return
	}
	p := filepath.Join(base, "UnisSoft", "HSVoucherBridge")
	_ = os.MkdirAll(p, 0700)
	f, err := os.OpenFile(filepath.Join(p, "bridge.log"), os.O_CREATE|os.O_APPEND|os.O_WRONLY, 0600)
	if err != nil {
		return
	}
	defer f.Close()
	fmt.Fprintf(f, "%s %s\n", time.Now().Format("2006-01-02 15:04:05"), text)
}

func main() {
	if message("HairSoft - jednorazova oprava", "Tento nastroj opravi pouze testovaci voucher K964Z-A.\n\nPred pokracovanim zavrete HairSoft i HSVoucherBridge.\n\nPokracovat?", mbYesNo|mbIconQuestion) != idYes {
		return
	}
	path, err := dbPath()
	if err != nil {
		message("Chyba", err.Error(), mbOK|mbIconError)
		return
	}
	db, err := openDB(path)
	if err != nil {
		message("Chyba", "Databazi nelze otevrit:\n"+err.Error(), mbOK|mbIconError)
		return
	}
	defer db.close()
	r, err := loadRow(db)
	if err != nil {
		message("Chyba", err.Error(), mbOK|mbIconError)
		return
	}
	if r.count != 1 {
		message("Chyba", fmt.Sprintf("Voucher %s nebyl nalezen prave jednou (nalezeno %d). Nic nebylo zmeneno.", expectedCode, r.count), mbOK|mbIconError)
		return
	}
	if r.id != expectedBill || r.centre != expectedCentre || r.user != expectedUser || r.price != "1.0" && r.price != "1" {
		message("Chyba", fmt.Sprintf("Kontrolni udaje nesedi.\nBill ID=%d, stredisko=%d, uzivatel=%d, cena=%s, cislo=%d\n\nNic nebylo zmeneno.", r.id, r.centre, r.user, r.price, r.billnumber), mbOK|mbIconError)
		return
	}
	if r.billnumber == targetNumber {
		message("Hotovo", fmt.Sprintf("Voucher %s uz ma spravne cislo uctu %d.", expectedCode, targetNumber), mbOK|mbIconInformation)
		return
	}
	if r.billnumber != oldNumber {
		message("Chyba", fmt.Sprintf("Voucher ma neocekavane cislo uctu %d. Ocekavano %d. Nic nebylo zmeneno.", r.billnumber, oldNumber), mbOK|mbIconError)
		return
	}
	if err := db.exec("BEGIN IMMEDIATE"); err != nil {
		message("Chyba", "Nelze ziskat zapisovy zamek DB:\n"+err.Error(), mbOK|mbIconError)
		return
	}
	committed := false
	defer func() {
		if !committed {
			_ = db.exec("ROLLBACK")
		}
	}()
	r, err = loadRow(db)
	if err != nil || r.count != 1 || r.id != expectedBill || r.billnumber != oldNumber {
		message("Chyba", "Data se behem kontroly zmenila. Nic nebylo zmeneno.", mbOK|mbIconError)
		return
	}
	n, err := targetCount(db)
	if err != nil {
		message("Chyba", err.Error(), mbOK|mbIconError)
		return
	}
	if n != 0 {
		message("Chyba", fmt.Sprintf("Cilove cislo %d uz v tomto stredisku existuje. Nic nebylo zmeneno.", targetNumber), mbOK|mbIconError)
		return
	}
	if err := db.exec(`UPDATE bill SET billnumber=1054930363 WHERE id=130396 AND id_centre=1 AND id_user=6 AND billnumber=1054931061`); err != nil {
		message("Chyba", err.Error(), mbOK|mbIconError)
		return
	}
	ch, err := changes(db)
	if err != nil || ch != 1 {
		message("Chyba", fmt.Sprintf("UPDATE nezmenil prave jeden radek (changes=%d). Nic nebylo zmeneno.", ch), mbOK|mbIconError)
		return
	}
	r, err = loadRow(db)
	if err != nil || r.billnumber != targetNumber {
		message("Chyba", "Nasledna kontrola opraveneho cisla selhala. Nic nebylo zmeneno.", mbOK|mbIconError)
		return
	}
	if err := db.exec("COMMIT"); err != nil {
		message("Chyba", "COMMIT selhal:\n"+err.Error(), mbOK|mbIconError)
		return
	}
	committed = true
	appendLog(fmt.Sprintf("REPAIR bill=%d code=%s billnumber=%d -> %d", expectedBill, expectedCode, oldNumber, targetNumber))
	message("Hotovo", fmt.Sprintf("Testovaci voucher %s byl opraven.\n\nCislo uctu: %d -> %d\nBill ID: %d\n\nNyni muzete znovu spustit HSVoucherBridge V016.", expectedCode, oldNumber, targetNumber, expectedBill), mbOK|mbIconInformation)
}
