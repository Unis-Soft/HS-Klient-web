//go:build windows

package main

import (
	"errors"
	"fmt"
	"strings"
	"time"
)

// HairSoftDataProvider isolates Bridge modules from the physical HairSoft
// database. V001 implements SQLite. MySQL will implement the same contract.
type HairSoftDataProvider interface {
	Backend() string
	Close()
	CustomerByID(id int64) (CustomerSnapshot, error)
	VisitDatesByCustomerID(id int64, now string) (VisitDatesSnapshot, error)
	RecentOrdersByCustomerID(id int64, limit int) ([]OrderDebugRow, error)
	ProgramsSnapshot() (ProgramsSnapshot, error)
}

type CustomerSnapshot struct {
	ID             int64  `json:"id"`
	GUID           string `json:"guid,omitempty"`
	Name           string `json:"name,omitempty"`
	Surname        string `json:"surname,omitempty"`
	Phone          string `json:"phone,omitempty"`
	Cell           string `json:"cell,omitempty"`
	Email          string `json:"email,omitempty"`
	Note           string `json:"note,omitempty"`
	LoyalityPoints int64  `json:"loyalityPoints"`
	Updated        string `json:"updated,omitempty"`
	ReSync         int64  `json:"reSync,omitempty"`
}

type VisitDatesSnapshot struct {
	CustomerID int64  `json:"customerId"`
	LastVisit  string `json:"lastVisit,omitempty"`
	NextVisit  string `json:"nextVisit,omitempty"`
	AsOf       string `json:"asOf"`
}

type OrderDebugRow struct {
	ID           int64
	CustomerID   int64
	OrderDate    string
	OrderEndDate string
	Valid        int64
	Canceled     int64
	Break        int64
	Inserted     string
	Changed      string
}

type ProgramDefinition struct {
	ID          int64  `json:"id"`
	Name        string `json:"name"`
	CentreID    int64  `json:"centreId"`
	CommodityID int64  `json:"commodityId"`
	UserID      int64  `json:"userId"`
}

type ProgramPaymentSnapshot struct {
	ID         int64   `json:"id"`
	ProgramID  int64   `json:"programId"`
	CustomerID int64   `json:"customerId"`
	Price      float64 `json:"price"`
	PriceVAT   int64   `json:"priceVat"`
	Visits     int64   `json:"visits"`
	Created    string  `json:"created"`
}

type ProgramVisitSnapshot struct {
	ID         int64  `json:"id"`
	ProgramID  int64  `json:"programId"`
	CustomerID int64  `json:"customerId"`
	Visit      string `json:"visit"`
	Quantity   int64  `json:"quantity"`
}

type ProgramValueDefinition struct {
	ID        int64  `json:"id"`
	ProgramID int64  `json:"programId"`
	Name      string `json:"name"`
}

type ProgramCustomerValue struct {
	ID             int64  `json:"id"`
	ProgramValueID int64  `json:"programValueId"`
	CustomerID     int64  `json:"customerId"`
	Value          string `json:"value"`
}

type ProgramsSnapshot struct {
	AsOf             string                   `json:"asOf"`
	Programs         []ProgramDefinition      `json:"programs"`
	Payments         []ProgramPaymentSnapshot `json:"payments"`
	Visits           []ProgramVisitSnapshot   `json:"visits"`
	ValueDefinitions []ProgramValueDefinition `json:"valueDefinitions"`
	Values           []ProgramCustomerValue   `json:"values"`
}


type sqliteHairSoftProvider struct {
	db *DB
}

func openHairSoftReadProvider(path string) (HairSoftDataProvider, error) {
	db, err := openDBReadOnly(strings.TrimSpace(path))
	if err != nil {
		return nil, err
	}
	// Export modules never wait for HairSoft. If the source is busy, the cycle
	// is skipped and retried later.
	db.BusyTimeout(0)
	return &sqliteHairSoftProvider{db: db}, nil
}

func (p *sqliteHairSoftProvider) Backend() string { return "sqlite" }

func (p *sqliteHairSoftProvider) Close() {
	if p != nil && p.db != nil {
		p.db.Close()
		p.db = nil
	}
}

func (p *sqliteHairSoftProvider) CustomerByID(id int64) (CustomerSnapshot, error) {
	var out CustomerSnapshot
	if p == nil || p.db == nil {
		return out, errors.New("HairSoft provider neni otevreny")
	}
	st, err := p.db.Prepare(`
SELECT id, COALESCE(KlientGuid,''), COALESCE(name,''), COALESCE(surname,''),
       COALESCE(phone,''), COALESCE(cell,''), COALESCE(email,''),
       COALESCE(note,''), COALESCE(loyalityPoints,0),
       COALESCE(CAST(updated AS TEXT),''), COALESCE(ReSync,0)
FROM customer
WHERE id=?
LIMIT 1`)
	if err != nil {
		return out, err
	}
	defer st.Finalize()
	if err := st.Bind(1, id); err != nil {
		return out, err
	}
	rc := st.Step()
	if rc != sqliteRow {
		if rc == sqliteBusy || rc == sqliteLocked {
			return out, fmt.Errorf("HairSoft DB je prave pouzivana: rc=%d", rc)
		}
		return out, errNoRow
	}
	out.ID = st.ColInt64(0)
	out.GUID = displayText(st.ColBytes(1))
	out.Name = displayText(st.ColBytes(2))
	out.Surname = displayText(st.ColBytes(3))
	out.Phone = displayText(st.ColBytes(4))
	out.Cell = displayText(st.ColBytes(5))
	out.Email = displayText(st.ColBytes(6))
	out.Note = displayText(st.ColBytes(7))
	out.LoyalityPoints = st.ColInt64(8)
	out.Updated = displayText(st.ColBytes(9))
	out.ReSync = st.ColInt64(10)
	return out, nil
}

func (p *sqliteHairSoftProvider) VisitDatesByCustomerID(id int64, now string) (VisitDatesSnapshot, error) {
	out := VisitDatesSnapshot{CustomerID: id, AsOf: strings.TrimSpace(now)}
	if p == nil || p.db == nil {
		return out, errors.New("HairSoft provider neni otevreny")
	}
	if id <= 0 || out.AsOf == "" {
		return out, errors.New("HairSoft statistics: neplatny zakaznik nebo cas")
	}

	// "Posledni navsteva" follows the historical HS Klient meaning: an
	// actually completed/charged visit. HairSoft bills carry second-precision
	// endbill timestamps, unlike planned calendar starts.
	last, err := p.db.Prepare(`
SELECT COALESCE(CAST(MAX(endbill) AS TEXT),'')
FROM bill
WHERE id_customer=?
  AND valid=1
  AND COALESCE(Discarted,0)=0
  AND endbill IS NOT NULL
  AND endbill<=?`)
	if err != nil {
		return out, err
	}
	if err := last.Bind(1, id); err != nil {
		last.Finalize()
		return out, err
	}
	if err := last.Bind(2, out.AsOf); err != nil {
		last.Finalize()
		return out, err
	}
	rc := last.Step()
	if rc == sqliteBusy || rc == sqliteLocked {
		last.Finalize()
		return out, fmt.Errorf("HairSoft DB je prave pouzivana: rc=%d", rc)
	}
	if rc != sqliteRow {
		last.Finalize()
		return out, fmt.Errorf("HairSoft statistics last visit rc=%d", rc)
	}
	out.LastVisit = displayText(last.ColBytes(0))
	last.Finalize()

	// "Pristi navsteva" is the nearest active future booking. Canceled,
	// invalid and Break rows are intentionally excluded.
	next, err := p.db.Prepare(`
SELECT COALESCE(CAST(MIN(order_date) AS TEXT),'')
FROM orders
WHERE id_customer=?
  AND valid=1
  AND COALESCE(Break,0)=0
  AND COALESCE(Canceled,0)=0
  AND order_date IS NOT NULL
  AND order_date>?`)
	if err != nil {
		return out, err
	}
	defer next.Finalize()
	if err := next.Bind(1, id); err != nil {
		return out, err
	}
	if err := next.Bind(2, out.AsOf); err != nil {
		return out, err
	}
	rc = next.Step()
	if rc == sqliteBusy || rc == sqliteLocked {
		return out, fmt.Errorf("HairSoft DB je prave pouzivana: rc=%d", rc)
	}
	if rc != sqliteRow {
		return out, fmt.Errorf("HairSoft statistics next visit rc=%d", rc)
	}
	out.NextVisit = displayText(next.ColBytes(0))
	return out, nil
}

func (p *sqliteHairSoftProvider) RecentOrdersByCustomerID(id int64, limit int) ([]OrderDebugRow, error) {
	if p == nil || p.db == nil {
		return nil, errors.New("HairSoft provider neni otevreny")
	}
	if id <= 0 {
		return nil, errors.New("HairSoft debug orders: neplatny zakaznik")
	}
	if limit < 1 || limit > 20 {
		limit = 10
	}

	st, err := p.db.Prepare(`
SELECT id,
       COALESCE(id_customer,0),
       COALESCE(CAST(order_date AS TEXT),''),
       COALESCE(CAST(order_end_date AS TEXT),''),
       COALESCE(valid,0),
       COALESCE(Canceled,0),
       COALESCE(Break,0),
       COALESCE(CAST(inserted AS TEXT),''),
       COALESCE(CAST(changed AS TEXT),'')
FROM orders
WHERE id_customer=?
ORDER BY id DESC
LIMIT ?`)
	if err != nil {
		return nil, err
	}
	defer st.Finalize()
	if err := st.Bind(1, id); err != nil {
		return nil, err
	}
	if err := st.Bind(2, int64(limit)); err != nil {
		return nil, err
	}

	rows := make([]OrderDebugRow, 0, limit)
	for {
		rc := st.Step()
		if rc == sqliteDone {
			break
		}
		if rc == sqliteBusy || rc == sqliteLocked {
			return nil, fmt.Errorf("HairSoft DB je prave pouzivana: rc=%d", rc)
		}
		if rc != sqliteRow {
			return nil, fmt.Errorf("HairSoft debug orders rc=%d", rc)
		}
		rows = append(rows, OrderDebugRow{
			ID:           st.ColInt64(0),
			CustomerID:   st.ColInt64(1),
			OrderDate:    displayText(st.ColBytes(2)),
			OrderEndDate: displayText(st.ColBytes(3)),
			Valid:        st.ColInt64(4),
			Canceled:     st.ColInt64(5),
			Break:        st.ColInt64(6),
			Inserted:     displayText(st.ColBytes(7)),
			Changed:      displayText(st.ColBytes(8)),
		})
	}
	return rows, nil
}


func (p *sqliteHairSoftProvider) ProgramsSnapshot() (ProgramsSnapshot, error) {
	out := ProgramsSnapshot{AsOf: time.Now().In(time.Local).Format("2006-01-02 15:04:05")}
	if p == nil || p.db == nil {
		return out, errors.New("HairSoft provider neni otevreny")
	}

	programRows, err := p.db.Prepare(`
SELECT id, COALESCE(name,''), COALESCE(id_centre,0), COALESCE(id_commodity,0), COALESCE(id_user,0)
FROM programs
WHERE valid=1
ORDER BY id`)
	if err != nil { return out, err }
	active := map[int64]bool{}
	for {
		rc := programRows.Step()
		if rc == sqliteDone { break }
		if rc == sqliteBusy || rc == sqliteLocked {
			programRows.Finalize()
			return out, fmt.Errorf("HairSoft DB je prave pouzivana: rc=%d", rc)
		}
		if rc != sqliteRow {
			programRows.Finalize()
			return out, fmt.Errorf("HairSoft programs query rc=%d", rc)
		}
		id := programRows.ColInt64(0)
		active[id] = true
		out.Programs = append(out.Programs, ProgramDefinition{
			ID: id,
			Name: displayText(programRows.ColBytes(1)),
			CentreID: programRows.ColInt64(2),
			CommodityID: programRows.ColInt64(3),
			UserID: programRows.ColInt64(4),
		})
	}
	programRows.Finalize()

	payments, err := p.db.Prepare(`
SELECT pp.id, pp.id_program, pp.id_customer, CAST(pp.price AS TEXT), COALESCE(pp.price_vat,0),
       COALESCE(pp.visits,0), COALESCE(CAST(pp.created AS TEXT),'')
FROM program_payments pp
JOIN programs p ON p.id=pp.id_program
WHERE p.valid=1
ORDER BY pp.id`)
	if err != nil { return out, err }
	for {
		rc := payments.Step()
		if rc == sqliteDone { break }
		if rc == sqliteBusy || rc == sqliteLocked {
			payments.Finalize()
			return out, fmt.Errorf("HairSoft DB je prave pouzivana: rc=%d", rc)
		}
		if rc != sqliteRow {
			payments.Finalize()
			return out, fmt.Errorf("HairSoft program_payments query rc=%d", rc)
		}
		out.Payments = append(out.Payments, ProgramPaymentSnapshot{
			ID: payments.ColInt64(0), ProgramID: payments.ColInt64(1), CustomerID: payments.ColInt64(2),
			Price: payments.ColDouble(3), PriceVAT: payments.ColInt64(4), Visits: payments.ColInt64(5),
			Created: displayText(payments.ColBytes(6)),
		})
	}
	payments.Finalize()

	visits, err := p.db.Prepare(`
SELECT pv.id, pv.id_program, pv.id_customer, COALESCE(CAST(pv.visit AS TEXT),''), COALESCE(pv.quantity,1)
FROM program_visits pv
JOIN programs p ON p.id=pv.id_program
WHERE p.valid=1
ORDER BY pv.id`)
	if err != nil { return out, err }
	for {
		rc := visits.Step()
		if rc == sqliteDone { break }
		if rc == sqliteBusy || rc == sqliteLocked {
			visits.Finalize()
			return out, fmt.Errorf("HairSoft DB je prave pouzivana: rc=%d", rc)
		}
		if rc != sqliteRow {
			visits.Finalize()
			return out, fmt.Errorf("HairSoft program_visits query rc=%d", rc)
		}
		out.Visits = append(out.Visits, ProgramVisitSnapshot{
			ID: visits.ColInt64(0), ProgramID: visits.ColInt64(1), CustomerID: visits.ColInt64(2),
			Visit: displayText(visits.ColBytes(3)), Quantity: visits.ColInt64(4),
		})
	}
	visits.Finalize()

	defs, err := p.db.Prepare(`
SELECT pv.id, pv.id_program, COALESCE(pv.name,'')
FROM program_values pv
JOIN programs p ON p.id=pv.id_program
WHERE pv.valid=1 AND p.valid=1
ORDER BY pv.id`)
	if err != nil { return out, err }
	valueIDs := map[int64]bool{}
	for {
		rc := defs.Step()
		if rc == sqliteDone { break }
		if rc == sqliteBusy || rc == sqliteLocked {
			defs.Finalize()
			return out, fmt.Errorf("HairSoft DB je prave pouzivana: rc=%d", rc)
		}
		if rc != sqliteRow {
			defs.Finalize()
			return out, fmt.Errorf("HairSoft program_values query rc=%d", rc)
		}
		id := defs.ColInt64(0)
		valueIDs[id] = true
		out.ValueDefinitions = append(out.ValueDefinitions, ProgramValueDefinition{
			ID: id, ProgramID: defs.ColInt64(1), Name: displayText(defs.ColBytes(2)),
		})
	}
	defs.Finalize()

	values, err := p.db.Prepare(`
SELECT pvc.id, pvc.id_program_value, pvc.id_customer, COALESCE(pvc.value,'')
FROM program_values2customer pvc
JOIN program_values pv ON pv.id=pvc.id_program_value
JOIN programs p ON p.id=pv.id_program
WHERE pv.valid=1 AND p.valid=1
ORDER BY pvc.id`)
	if err != nil { return out, err }
	for {
		rc := values.Step()
		if rc == sqliteDone { break }
		if rc == sqliteBusy || rc == sqliteLocked {
			values.Finalize()
			return out, fmt.Errorf("HairSoft DB je prave pouzivana: rc=%d", rc)
		}
		if rc != sqliteRow {
			values.Finalize()
			return out, fmt.Errorf("HairSoft program_values2customer query rc=%d", rc)
		}
		out.Values = append(out.Values, ProgramCustomerValue{
			ID: values.ColInt64(0), ProgramValueID: values.ColInt64(1), CustomerID: values.ColInt64(2),
			Value: displayText(values.ColBytes(3)),
		})
	}
	values.Finalize()

	_ = active
	_ = valueIDs
	return out, nil
}
