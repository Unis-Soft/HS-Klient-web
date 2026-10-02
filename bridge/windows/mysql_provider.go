//go:build windows

package main

import (
	"context"
	"encoding/hex"
	"errors"
	"fmt"
	"html"
	"net"
	"os"
	"os/exec"
	"path/filepath"
	"regexp"
	"strconv"
	"strings"
	"syscall"
	"time"
)

type mysqlHairSoftSettings struct {
	Host     string
	Port     int
	Database string
	User     string
	Password string
}

type mysqlHairSoftProvider struct {
	cfg mysqlHairSoftSettings
}

type bridgePCRole string

const (
	bridgeRoleSQLite       bridgePCRole = "sqlite"
	bridgeRoleMySQLServer  bridgePCRole = "mysql-server"
	bridgeRoleMySQLClient  bridgePCRole = "mysql-client"
	bridgeRoleMySQLInvalid bridgePCRole = "mysql-invalid"
)

type bridgeTrafficPolicy struct {
	Allowed      bool
	Role         bridgePCRole
	SQLHost      string
	SettingsPath string
	Err          error
}

func isLocalSQLHost(host string) bool {
	host = strings.TrimSpace(strings.Trim(host, "[]"))
	if host == "" {
		return false
	}
	if strings.EqualFold(host, "localhost") || strings.EqualFold(host, "localhost.") {
		return true
	}
	ip := net.ParseIP(host)
	return ip != nil && ip.IsLoopback()
}

func bridgeTrafficPolicyFromMySQL(cfg mysqlHairSoftSettings, settingsPath string, present bool, err error) bridgeTrafficPolicy {
	if !present {
		return bridgeTrafficPolicy{Allowed: true, Role: bridgeRoleSQLite}
	}
	if err != nil {
		return bridgeTrafficPolicy{
			Allowed: false, Role: bridgeRoleMySQLInvalid,
			SettingsPath: settingsPath, Err: err,
		}
	}
	if isLocalSQLHost(cfg.Host) {
		return bridgeTrafficPolicy{
			Allowed: true, Role: bridgeRoleMySQLServer,
			SQLHost: cfg.Host, SettingsPath: settingsPath,
		}
	}
	return bridgeTrafficPolicy{
		Allowed: false, Role: bridgeRoleMySQLClient,
		SQLHost: cfg.Host, SettingsPath: settingsPath,
	}
}

func currentBridgeTrafficPolicy() bridgeTrafficPolicy {
	cfg, settingsPath, present, err := discoverHairSoftMySQL()
	return bridgeTrafficPolicyFromMySQL(cfg, settingsPath, present, err)
}

func bridgeTrafficDisabledError(policy bridgeTrafficPolicy) error {
	if policy.Allowed {
		return nil
	}
	if policy.Err != nil {
		return fmt.Errorf("HSBridge network disabled role=%s: %w", policy.Role, policy.Err)
	}
	return fmt.Errorf("HSBridge network disabled role=%s sql_host=%s", policy.Role, policy.SQLHost)
}

func settingsElement(text, name string) (string, bool) {
	re := regexp.MustCompile(`(?is)<\s*` + regexp.QuoteMeta(name) + `\s*>\s*([^<]*?)\s*<\s*/\s*` + regexp.QuoteMeta(name) + `\s*>`)
	m := re.FindStringSubmatch(text)
	if len(m) < 2 {
		return "", false
	}
	return strings.TrimSpace(html.UnescapeString(m[1])), true
}

func mysqlSettingsFromSettings(path string) (mysqlHairSoftSettings, bool, error) {
	var out mysqlHairSoftSettings
	b, err := os.ReadFile(path)
	if err != nil {
		return out, false, err
	}
	text := decodeXMLText(b)

	host, hasHost := settingsElement(text, "SQLHost")
	portRaw, hasPort := settingsElement(text, "SQLPort")
	database, hasDatabase := settingsElement(text, "SQLDatabase")
	user, hasUser := settingsElement(text, "SQLUser")
	password, hasPassword := settingsElement(text, "SQLPasword")
	if !hasPassword {
		password, hasPassword = settingsElement(text, "SQLPassword")
	}

	present := hasHost || hasPort || hasDatabase || hasUser || hasPassword
	if !present {
		return out, false, nil
	}

	if strings.TrimSpace(host) == "" {
		return out, true, errors.New("MySQL Settings.xml: chybi SQLHost")
	}
	if strings.TrimSpace(database) == "" {
		return out, true, errors.New("MySQL Settings.xml: chybi SQLDatabase")
	}
	if strings.TrimSpace(user) == "" {
		return out, true, errors.New("MySQL Settings.xml: chybi SQLUser")
	}

	port := 3306
	if strings.TrimSpace(portRaw) != "" {
		p, parseErr := strconv.Atoi(strings.TrimSpace(portRaw))
		if parseErr != nil || p < 1 || p > 65535 {
			return out, true, fmt.Errorf("MySQL Settings.xml: neplatny SQLPort %q", portRaw)
		}
		port = p
	}

	out = mysqlHairSoftSettings{
		Host: strings.TrimSpace(host), Port: port,
		Database: strings.TrimSpace(database), User: strings.TrimSpace(user), Password: password,
	}
	return out, true, nil
}

func discoverHairSoftMySQL() (mysqlHairSoftSettings, string, bool, error) {
	var lastErr error
	var lastPath string
	for _, path := range settingsCandidates() {
		if _, err := os.Stat(path); err != nil {
			continue
		}
		cfg, present, err := mysqlSettingsFromSettings(path)
		if !present {
			continue
		}
		lastPath = path
		if err != nil {
			lastErr = err
			continue
		}
		return cfg, path, true, nil
	}
	if lastErr != nil {
		return mysqlHairSoftSettings{}, lastPath, true, lastErr
	}
	return mysqlHairSoftSettings{}, "", false, nil
}

// findHairSoftMySQLClient uses the MySQL client shipped with HairSoft. This
// keeps HSBridge self-contained from a Go SQL driver while still using the
// same MySQL installation as HairSoft itself.
func findHairSoftMySQLClient() (string, error) {
	seen := map[string]bool{}
	var candidates []string
	addRoot := func(root string) {
		root = strings.TrimSpace(root)
		if root == "" {
			return
		}
		candidates = append(candidates,
			filepath.Join(root, "MySQL", "bin", "mysql.exe"),
			filepath.Join(root, "MYSQL", "bin", "mysql.exe"),
			filepath.Join(root, "mysql", "bin", "mysql.exe"),
			filepath.Join(root, "MySQL", "bin", "mariadb.exe"),
		)
	}
	addRoot(bridgeRuntimeRoot())
	for _, settings := range settingsCandidates() {
		if _, err := os.Stat(settings); err == nil {
			addRoot(filepath.Dir(settings))
		}
	}
	for _, p := range candidates {
		key := strings.ToLower(filepath.Clean(p))
		if seen[key] {
			continue
		}
		seen[key] = true
		if fi, err := os.Stat(p); err == nil && !fi.IsDir() {
			return p, nil
		}
	}
	for _, name := range []string{"mysql.exe", "mariadb.exe"} {
		if p, err := exec.LookPath(name); err == nil {
			return p, nil
		}
	}
	return "", errors.New("MySQL klient nebyl nalezen (ocekavano <HairSoft>\\MySQL\\bin\\mysql.exe)")
}

func mysqlOptionQuote(v string) string {
	v = strings.ReplaceAll(v, `\`, `\\`)
	v = strings.ReplaceAll(v, `"`, `\"`)
	v = strings.ReplaceAll(v, "\r", `\r`)
	v = strings.ReplaceAll(v, "\n", `\n`)
	return `"` + v + `"`
}

func mysqlTempDefaults(cfg mysqlHairSoftSettings) (string, func(), error) {
	d, err := appDir()
	if err != nil {
		return "", func() {}, err
	}
	f, err := os.CreateTemp(d, "mysql-client-*.cnf")
	if err != nil {
		return "", func() {}, err
	}
	p := f.Name()
	cleanup := func() { _ = os.Remove(p) }
	content := "[client]\r\n" +
		"host=" + mysqlOptionQuote(cfg.Host) + "\r\n" +
		"port=" + strconv.Itoa(cfg.Port) + "\r\n" +
		"user=" + mysqlOptionQuote(cfg.User) + "\r\n" +
		"password=" + mysqlOptionQuote(cfg.Password) + "\r\n" +
		"database=" + mysqlOptionQuote(cfg.Database) + "\r\n" +
		"protocol=tcp\r\n"
	if _, err := f.WriteString(content); err != nil {
		_ = f.Close()
		cleanup()
		return "", func() {}, err
	}
	if err := f.Close(); err != nil {
		cleanup()
		return "", func() {}, err
	}
	return p, cleanup, nil
}

func runHairSoftMySQL(cfg mysqlHairSoftSettings, query string, timeout time.Duration) (string, error) {
	exe, err := findHairSoftMySQLClient()
	if err != nil {
		return "", err
	}
	defaults, cleanup, err := mysqlTempDefaults(cfg)
	if err != nil {
		return "", err
	}
	defer cleanup()

	if timeout <= 0 {
		timeout = 20 * time.Second
	}
	ctx, cancel := context.WithTimeout(context.Background(), timeout)
	defer cancel()

	// MySQL requires --defaults-extra-file to be the first option.
	args := []string{
		"--defaults-extra-file=" + defaults,
		"--protocol=tcp",
		"--batch",
		"--skip-column-names",
		"--default-character-set=utf8mb4",
		"--connect-timeout=3",
		"--execute=" + query,
	}
	cmd := exec.CommandContext(ctx, exe, args...)
	cmd.SysProcAttr = &syscall.SysProcAttr{HideWindow: true}
	out, runErr := cmd.CombinedOutput()
	if ctx.Err() != nil {
		return "", fmt.Errorf("MySQL prikaz prekrocil limit %s", timeout)
	}
	if runErr != nil {
		msg := strings.TrimSpace(string(out))
		if msg != "" {
			return "", fmt.Errorf("MySQL klient: %w: %s", runErr, msg)
		}
		return "", fmt.Errorf("MySQL klient: %w", runErr)
	}
	return string(out), nil
}

func mysqlBatchUnescape(s string) string {
	if s == `\N` {
		return ""
	}
	var b strings.Builder
	b.Grow(len(s))
	for i := 0; i < len(s); i++ {
		if s[i] != '\\' || i+1 >= len(s) {
			b.WriteByte(s[i])
			continue
		}
		i++
		switch s[i] {
		case '0':
			b.WriteByte(0)
		case 'b':
			b.WriteByte('\b')
		case 'n':
			b.WriteByte('\n')
		case 'r':
			b.WriteByte('\r')
		case 't':
			b.WriteByte('\t')
		case 'Z':
			b.WriteByte(26)
		case '\\':
			b.WriteByte('\\')
		default:
			b.WriteByte('\\')
			b.WriteByte(s[i])
		}
	}
	return b.String()
}

func mysqlRowsFromBatch(out string) [][]string {
	out = strings.ReplaceAll(out, "\r\n", "\n")
	out = strings.TrimSuffix(out, "\n")
	if out == "" {
		return nil
	}
	lines := strings.Split(out, "\n")
	rows := make([][]string, 0, len(lines))
	for _, line := range lines {
		fields := strings.Split(line, "\t")
		for i := range fields {
			fields[i] = mysqlBatchUnescape(fields[i])
		}
		rows = append(rows, fields)
	}
	return rows
}

func mysqlSQLString(s string) string {
	if s == "" {
		return "''"
	}
	return "CONVERT(0x" + hex.EncodeToString([]byte(s)) + " USING utf8mb4)"
}

func parseI64(s string) int64 {
	v, _ := strconv.ParseInt(strings.TrimSpace(s), 10, 64)
	return v
}

func parseF64(s string) float64 {
	v, _ := strconv.ParseFloat(strings.TrimSpace(s), 64)
	return v
}

func openMySQLHairSoftProvider(cfg mysqlHairSoftSettings) (HairSoftDataProvider, error) {
	p := &mysqlHairSoftProvider{cfg: cfg}
	out, err := runHairSoftMySQL(cfg, "SELECT 1", 5*time.Second)
	if err != nil {
		return nil, fmt.Errorf("MySQL %s:%d/%s: %w", cfg.Host, cfg.Port, cfg.Database, err)
	}
	rows := mysqlRowsFromBatch(out)
	if len(rows) != 1 || len(rows[0]) < 1 || strings.TrimSpace(rows[0][0]) != "1" {
		return nil, fmt.Errorf("MySQL %s:%d/%s: neocekavana odpoved", cfg.Host, cfg.Port, cfg.Database)
	}
	return p, nil
}

func openHairSoftProgramsProvider(c Config) (HairSoftDataProvider, string, error) {
	mysqlCfg, settingsPath, mysqlPresent, err := discoverHairSoftMySQL()
	if mysqlPresent {
		if err != nil {
			return nil, "", err
		}
		p, err := openMySQLHairSoftProvider(mysqlCfg)
		if err != nil {
			return nil, "", err
		}
		return p, "Settings.xml MySQL " + settingsPath, nil
	}

	path, source, err := discoverHairSoftDB(c)
	if err != nil {
		return nil, "", err
	}
	p, err := openHairSoftReadProvider(path)
	if err != nil {
		return nil, "", err
	}
	return p, source + " SQLite " + path, nil
}

func quickHairSoftBackend(c Config) (string, string, error) {
	mysqlCfg, settingsPath, mysqlPresent, err := discoverHairSoftMySQL()
	if mysqlPresent {
		if err != nil {
			return "mysql", settingsPath, err
		}
		p, err := openMySQLHairSoftProvider(mysqlCfg)
		if err != nil {
			return "mysql", settingsPath, err
		}
		p.Close()
		return "mysql", fmt.Sprintf("%s host=%s port=%d database=%s", settingsPath, mysqlCfg.Host, mysqlCfg.Port, mysqlCfg.Database), nil
	}
	path, source, err := discoverHairSoftDB(c)
	if err != nil {
		return "sqlite", source, err
	}
	return "sqlite", source + " path=" + path, nil
}

func (p *mysqlHairSoftProvider) Backend() string { return "mysql" }
func (p *mysqlHairSoftProvider) Close()          {}

func (p *mysqlHairSoftProvider) query(query string) ([][]string, error) {
	if p == nil {
		return nil, errors.New("HairSoft MySQL provider neni otevreny")
	}
	out, err := runHairSoftMySQL(p.cfg, query, 20*time.Second)
	if err != nil {
		return nil, err
	}
	return mysqlRowsFromBatch(out), nil
}

func (p *mysqlHairSoftProvider) CustomerByID(id int64) (CustomerSnapshot, error) {
	var out CustomerSnapshot
	rows, err := p.query(fmt.Sprintf(`
SELECT id, COALESCE(KlientGuid,''), COALESCE(name,''), COALESCE(surname,''),
       COALESCE(phone,''), COALESCE(cell,''), COALESCE(email,''),
       COALESCE(note,''), COALESCE(loyalityPoints,0),
       COALESCE(CAST(updated AS CHAR),''), COALESCE(ReSync,0)
FROM customer WHERE id=%d LIMIT 1`, id))
	if err != nil {
		return out, err
	}
	if len(rows) == 0 {
		return out, errNoRow
	}
	r := rows[0]
	if len(r) < 11 {
		return out, errors.New("MySQL customer: neuplny radek")
	}
	out.ID = parseI64(r[0])
	out.GUID = r[1]
	out.Name = r[2]
	out.Surname = r[3]
	out.Phone = r[4]
	out.Cell = r[5]
	out.Email = r[6]
	out.Note = r[7]
	out.LoyalityPoints = parseI64(r[8])
	out.Updated = r[9]
	out.ReSync = parseI64(r[10])
	return out, nil
}

func (p *mysqlHairSoftProvider) VisitDatesByCustomerID(id int64, now string) (VisitDatesSnapshot, error) {
	out := VisitDatesSnapshot{CustomerID: id, AsOf: strings.TrimSpace(now)}
	if id <= 0 || out.AsOf == "" {
		return out, errors.New("HairSoft statistics: neplatny zakaznik nebo cas")
	}
	nowSQL := mysqlSQLString(out.AsOf)
	rows, err := p.query(fmt.Sprintf(`SELECT COALESCE(CAST(MAX(endbill) AS CHAR),'') FROM bill
WHERE id_customer=%d AND valid=1 AND COALESCE(Discarted,0)=0 AND endbill IS NOT NULL AND endbill<=%s`, id, nowSQL))
	if err != nil {
		return out, err
	}
	if len(rows) > 0 && len(rows[0]) > 0 {
		out.LastVisit = rows[0][0]
	}

	rows, err = p.query(fmt.Sprintf(`SELECT COALESCE(CAST(MIN(order_date) AS CHAR),'') FROM orders
WHERE id_customer=%d AND valid=1 AND COALESCE(Break,0)=0 AND COALESCE(Canceled,0)=0
  AND order_date IS NOT NULL AND order_date>%s`, id, nowSQL))
	if err != nil {
		return out, err
	}
	if len(rows) > 0 && len(rows[0]) > 0 {
		out.NextVisit = rows[0][0]
	}
	return out, nil
}

func (p *mysqlHairSoftProvider) RecentOrdersByCustomerID(id int64, limit int) ([]OrderDebugRow, error) {
	if id <= 0 {
		return nil, errors.New("HairSoft debug orders: neplatny zakaznik")
	}
	if limit < 1 || limit > 20 {
		limit = 10
	}
	rows, err := p.query(fmt.Sprintf(`SELECT id, COALESCE(id_customer,0), COALESCE(CAST(order_date AS CHAR),''),
COALESCE(CAST(order_end_date AS CHAR),''), COALESCE(valid,0), COALESCE(Canceled,0), COALESCE(Break,0),
COALESCE(CAST(inserted AS CHAR),''), COALESCE(CAST(changed AS CHAR),'')
FROM orders WHERE id_customer=%d ORDER BY id DESC LIMIT %d`, id, limit))
	if err != nil {
		return nil, err
	}
	out := make([]OrderDebugRow, 0, len(rows))
	for _, row := range rows {
		if len(row) < 9 {
			return nil, errors.New("MySQL orders: neuplny radek")
		}
		out = append(out, OrderDebugRow{ID: parseI64(row[0]), CustomerID: parseI64(row[1]), OrderDate: row[2], OrderEndDate: row[3], Valid: parseI64(row[4]), Canceled: parseI64(row[5]), Break: parseI64(row[6]), Inserted: row[7], Changed: row[8]})
	}
	return out, nil
}

func (p *mysqlHairSoftProvider) ProgramsSnapshot() (ProgramsSnapshot, error) {
	out := ProgramsSnapshot{AsOf: time.Now().In(time.Local).Format("2006-01-02 15:04:05")}

	rows, err := p.query(`SELECT id, COALESCE(name1,''), COALESCE(id_centre,0), COALESCE(id_commodity,0), COALESCE(id_user,0)
FROM programs WHERE valid=1 ORDER BY id`)
	if err != nil {
		return out, err
	}
	for _, row := range rows {
		if len(row) < 5 {
			return out, errors.New("MySQL programs: neuplny radek")
		}
		out.Programs = append(out.Programs, ProgramDefinition{ID: parseI64(row[0]), Name: row[1], CentreID: parseI64(row[2]), CommodityID: parseI64(row[3]), UserID: parseI64(row[4])})
	}

	rows, err = p.query(`SELECT pp.id, pp.id_program, pp.id_customer, pp.price, COALESCE(pp.price_vat,0), COALESCE(pp.visits,0), COALESCE(CAST(pp.created AS CHAR),'')
FROM program_payments pp JOIN programs p ON p.id=pp.id_program WHERE p.valid=1 ORDER BY pp.id`)
	if err != nil {
		return out, err
	}
	for _, row := range rows {
		if len(row) < 7 {
			return out, errors.New("MySQL program_payments: neuplny radek")
		}
		out.Payments = append(out.Payments, ProgramPaymentSnapshot{ID: parseI64(row[0]), ProgramID: parseI64(row[1]), CustomerID: parseI64(row[2]), Price: parseF64(row[3]), PriceVAT: parseI64(row[4]), Visits: parseI64(row[5]), Created: row[6]})
	}

	rows, err = p.query(`SELECT pv.id, pv.id_program, pv.id_customer, COALESCE(CAST(pv.visit AS CHAR),''), COALESCE(pv.quantity,1)
FROM program_visits pv JOIN programs p ON p.id=pv.id_program WHERE p.valid=1 ORDER BY pv.id`)
	if err != nil {
		return out, err
	}
	for _, row := range rows {
		if len(row) < 5 {
			return out, errors.New("MySQL program_visits: neuplny radek")
		}
		out.Visits = append(out.Visits, ProgramVisitSnapshot{ID: parseI64(row[0]), ProgramID: parseI64(row[1]), CustomerID: parseI64(row[2]), Visit: row[3], Quantity: parseI64(row[4])})
	}

	rows, err = p.query(`SELECT pv.id, pv.id_program, COALESCE(pv.name1,'')
FROM program_values pv JOIN programs p ON p.id=pv.id_program WHERE pv.valid=1 AND p.valid=1 ORDER BY pv.id`)
	if err != nil {
		return out, err
	}
	for _, row := range rows {
		if len(row) < 3 {
			return out, errors.New("MySQL program_values: neuplny radek")
		}
		out.ValueDefinitions = append(out.ValueDefinitions, ProgramValueDefinition{ID: parseI64(row[0]), ProgramID: parseI64(row[1]), Name: row[2]})
	}

	rows, err = p.query(`SELECT pvc.id, pvc.id_program_value, pvc.id_customer, COALESCE(pvc.value,'')
FROM program_values2customer pvc JOIN program_values pv ON pv.id=pvc.id_program_value JOIN programs p ON p.id=pv.id_program
WHERE pv.valid=1 AND p.valid=1 ORDER BY pvc.id`)
	if err != nil {
		return out, err
	}
	for _, row := range rows {
		if len(row) < 4 {
			return out, errors.New("MySQL program_values2customer: neuplny radek")
		}
		out.Values = append(out.Values, ProgramCustomerValue{ID: parseI64(row[0]), ProgramValueID: parseI64(row[1]), CustomerID: parseI64(row[2]), Value: row[3]})
	}
	return out, nil
}

func (p *mysqlHairSoftProvider) insertProgramVisit(programID, customerID, quantity int64) (int64, error) {
	if p == nil {
		return 0, errors.New("HairSoft MySQL provider neni otevreny")
	}
	// No explicit transaction. Lock wait is one second so HairSoft always has
	// priority; if its work collides with this auxiliary INSERT, the command
	// simply fails and the cloud queue retries later.
	q := fmt.Sprintf(`SET SESSION innodb_lock_wait_timeout=1;
INSERT INTO program_visits (id_program,id_customer,visit,quantity) VALUES (%d,%d,CURRENT_TIMESTAMP,%d);
SELECT LAST_INSERT_ID();`, programID, customerID, quantity)
	raw, err := runHairSoftMySQL(p.cfg, q, 6*time.Second)
	if err != nil {
		return 0, err
	}
	rows := mysqlRowsFromBatch(raw)
	for i := len(rows) - 1; i >= 0; i-- {
		if len(rows[i]) == 0 {
			continue
		}
		id, parseErr := strconv.ParseInt(strings.TrimSpace(rows[i][0]), 10, 64)
		if parseErr == nil && id > 0 {
			return id, nil
		}
	}
	return 0, errors.New("MySQL INSERT nevratil LAST_INSERT_ID")
}