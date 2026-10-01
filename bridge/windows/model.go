//go:build windows

package main

import (
	"crypto/hmac"
	"crypto/rand"
	"crypto/sha256"
	"encoding/hex"
	"errors"
	"fmt"
	"strings"
	"time"
)

type VoucherPrice struct {
	CommodityID      int64
	PLU              []byte
	Name             []byte
	ItemAmount       int64
	CommodityType    int64
	Usage            int64
	UseQuality       int64
	PrintNote        []byte
	Duration         int64
	RateUse          int64
	DitType          int64
	RateDuration     int64
	PercentDiscount  int64
	CommodityStoreID int64
	SellPriceID      int64
	PriceCentreID    int64
	PriceUserID      int64
	Price            float64
	Vat              float64
	PriceNoVat       float64
}

type BuyPrice struct {
	ID         int64
	Price      float64
	Vat        float64
	PriceNoVat float64
}

type StoreChoice struct {
	RowID       int64
	InventoryID int64
	StoreID     int64
}

type Centre struct {
	ID   int64
	Name []byte
}

type User struct {
	ID, CentreID  int64
	Name, Surname []byte
}

type Payment struct {
	ID   int64
	Name []byte
}

type SaleIDs struct {
	BillID     int64 `json:"billId"`
	BillItemID int64 `json:"billItemId"`
	CodeID     int64 `json:"codeId"`
	BillNumber int64 `json:"billNumber"`
}

func validateDB(db *DB) error {
	uv, err := db.QueryInt64("PRAGMA user_version")
	if err != nil {
		return fmt.Errorf("nelze precist PRAGMA user_version: %w", err)
	}
	if uv != 172 && uv != 173 && uv != 174 {
		return fmt.Errorf("V004 podporuje overena schema user_version=172, 173 a 174; tato DB ma %d", uv)
	}
	if err := validateRequiredSchema(db, uv); err != nil {
		return err
	}
	mode, err := db.QueryBytes("PRAGMA journal_mode")
	if err != nil {
		return fmt.Errorf("nelze precist journal_mode: %w", err)
	}
	if !strings.EqualFold(string(mode), "wal") {
		return fmt.Errorf("online zapis za beziciho HairSoftu je ve V016 povolen pouze pro overeny WAL rezim; DB ma %s", displayText(mode))
	}
	return nil
}

func validateRequiredSchema(db *DB, uv int64) error {
	required := map[string][]string{
		"centre":              {"id", "name", "valid"},
		"user":                {"id", "id_centre", "name", "surname", "valid"},
		"commodity":           {"id", "plu", "name", "item_amout", "commodity_type", "usage", "use_quality", "print_note", "Duration", "RateUse", "DitType", "RateDuration", "PercentDiscount", "StoreID", "valid"},
		"commodity_sellprice": {"id", "id_commodity", "id_centre", "id_user", "price", "price_vat", "price_novat", "valid"},
		"commodity_buyprice":  {"id", "id_commodity", "price", "price_vat", "price_novat", "valid"},
		"commodity_store2":    {"id", "id_commodity", "id_inventory", "StoreID"},
		"billpayment":         {"id", "name", "valid", "ShowInBill"},
		"bill":                {"id", "id_centre", "id_user", "id_customer", "price", "price_novat", "startbill", "endbill", "payment", "commodity_quality", "genre", "printed", "services_price", "services_price_novat", "goods_price", "goods_price_novat", "materials_price", "materials_price_novat", "valid", "DiscountCommodity", "DiscountService", "Id_Order", "Discarted", "OutEET", "OrgBillID", "GID", "vouchers_price", "vouchers_price_novat", "PaidVoucherPrice", "payment2", "BICID", "BICWID", "VoucherAmountRemaining", "ComboPayment_price", "ComboPayment", "billnumber", "OrgBillNumber", "QRKryptoPaymentRate", "QRKryptoPaymentCurrency"},
		"billitem":            {"id", "id_bill", "id_commodity", "StoreID", "id_price", "id_buyprice", "amount", "price", "price_novat", "unitprice", "unitprice_novat", "price_vat", "id_centre", "id_user", "id_customer", "commodity_name", "commodity_item_amount", "commodity_type", "commodity_usage", "commodity_use_quality", "commodity_quality", "commodity_plu", "commodity_print_note", "inserted", "actiondate", "valid", "DiscountValue", "BaseUnitPrice", "Id_OrderActionRow", "BaseUnitPriceNoVat", "RateUse", "DitType", "Makro", "Discarted", "OrgBillItemID", "GID", "RateMinute", "RateDuration", "PercentDiscount"},
		"BillItemCodes":       {"ID", "BillItemID", "Code", "DateOfSale", "UserIDSale", "Duration", "DateValidTo", "DateOfUse", "UserIdUse", "GID", "PayBillID", "Price", "valid", "RefundBillItemID", "Export"},
		"BillItemCodesWeb":    {"Code"},
	}
	var missing []string
	for table, cols := range required {
		st, err := db.Prepare("PRAGMA table_info([" + table + "])")
		if err != nil {
			return fmt.Errorf("HairSoft schema %d nelze overit tabulku %s: %w", uv, table, err)
		}
		present := map[string]bool{}
		for {
			rc := st.Step()
			if rc == sqliteDone {
				break
			}
			if rc != sqliteRow {
				st.Finalize()
				return fmt.Errorf("HairSoft schema %d nelze precist tabulku %s: %s", uv, table, db.Errmsg())
			}
			present[strings.ToLower(strings.TrimSpace(string(st.ColBytes(1))))] = true
		}
		st.Finalize()
		for _, col := range cols {
			if !present[strings.ToLower(col)] {
				missing = append(missing, table+"."+col)
			}
		}
	}
	if len(missing) > 0 {
		return fmt.Errorf("HairSoft schema %d nema sloupce potrebne pro bezpecny Bridge zapis: %s", uv, strings.Join(missing, ", "))
	}
	return nil
}

func listCentres(db *DB) ([]Centre, error) {
	st, e := db.Prepare(`SELECT id,name FROM centre WHERE valid=1 ORDER BY id`)
	if e != nil {
		return nil, e
	}
	defer st.Finalize()
	var out []Centre
	for {
		rc := st.Step()
		if rc == sqliteDone {
			break
		}
		if rc != sqliteRow {
			return nil, fmt.Errorf("centre query: %s", db.Errmsg())
		}
		out = append(out, Centre{st.ColInt64(0), st.ColBytes(1)})
	}
	return out, nil
}

func getCentre(db *DB, id int64) (Centre, error) {
	st, e := db.Prepare(`SELECT id,name FROM centre WHERE valid=1 AND id=?`)
	if e != nil {
		return Centre{}, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{id})
	if st.Step() != sqliteRow {
		return Centre{}, errNoRow
	}
	return Centre{st.ColInt64(0), st.ColBytes(1)}, nil
}

func listUsers(db *DB, centreID int64) ([]User, error) {
	st, e := db.Prepare(`SELECT id,id_centre,name,surname FROM user WHERE valid=1 AND id_centre=? ORDER BY id`)
	if e != nil {
		return nil, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{centreID})
	var out []User
	for {
		rc := st.Step()
		if rc == sqliteDone {
			break
		}
		if rc != sqliteRow {
			return nil, fmt.Errorf("user query: %s", db.Errmsg())
		}
		out = append(out, User{st.ColInt64(0), st.ColInt64(1), st.ColBytes(2), st.ColBytes(3)})
	}
	return out, nil
}

func getUser(db *DB, id, centreID int64) (User, error) {
	st, e := db.Prepare(`SELECT id,id_centre,name,surname FROM user WHERE valid=1 AND id=? AND id_centre=?`)
	if e != nil {
		return User{}, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{id, centreID})
	if st.Step() != sqliteRow {
		return User{}, errNoRow
	}
	return User{st.ColInt64(0), st.ColInt64(1), st.ColBytes(2), st.ColBytes(3)}, nil
}

func listVoucherPrices(db *DB, centreID, userID int64) ([]VoucherPrice, error) {
	sql := `SELECT c.id,c.plu,c.name,c.item_amout,c.commodity_type,c.usage,c.use_quality,c.print_note,c.Duration,c.RateUse,c.DitType,c.RateDuration,c.PercentDiscount,c.StoreID,
      sp.id,sp.id_centre,sp.id_user,CAST(sp.price AS TEXT),CAST(sp.price_vat AS TEXT),CAST(sp.price_novat AS TEXT)
      FROM commodity c JOIN commodity_sellprice sp ON sp.id_commodity=c.id AND sp.valid=1
      WHERE c.valid=1 AND c.commodity_type=8
      AND (sp.id_centre=0 OR sp.id_centre=?) AND (sp.id_user=0 OR sp.id_user=?)
      ORDER BY c.id,sp.id`
	st, e := db.Prepare(sql)
	if e != nil {
		return nil, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{centreID, userID})
	var out []VoucherPrice
	for {
		rc := st.Step()
		if rc == sqliteDone {
			break
		}
		if rc != sqliteRow {
			return nil, fmt.Errorf("voucher price query: %s", db.Errmsg())
		}
		out = append(out, VoucherPrice{
			CommodityID: st.ColInt64(0), PLU: st.ColBytes(1), Name: st.ColBytes(2), ItemAmount: st.ColInt64(3), CommodityType: st.ColInt64(4), Usage: st.ColInt64(5), UseQuality: st.ColInt64(6), PrintNote: st.ColBytes(7), Duration: st.ColInt64(8), RateUse: st.ColInt64(9), DitType: st.ColInt64(10), RateDuration: st.ColInt64(11), PercentDiscount: st.ColInt64(12), CommodityStoreID: st.ColInt64(13), SellPriceID: st.ColInt64(14), PriceCentreID: st.ColInt64(15), PriceUserID: st.ColInt64(16), Price: st.ColDouble(17), Vat: st.ColDouble(18), PriceNoVat: st.ColDouble(19),
		})
	}
	return out, nil
}

func getVoucherPriceByID(db *DB, id int64) (VoucherPrice, error) {
	sql := `SELECT c.id,c.plu,c.name,c.item_amout,c.commodity_type,c.usage,c.use_quality,c.print_note,c.Duration,c.RateUse,c.DitType,c.RateDuration,c.PercentDiscount,c.StoreID,
      sp.id,sp.id_centre,sp.id_user,CAST(sp.price AS TEXT),CAST(sp.price_vat AS TEXT),CAST(sp.price_novat AS TEXT)
      FROM commodity c JOIN commodity_sellprice sp ON sp.id_commodity=c.id
      WHERE c.valid=1 AND c.commodity_type=8 AND sp.valid=1 AND sp.id=?`
	st, e := db.Prepare(sql)
	if e != nil {
		return VoucherPrice{}, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{id})
	if st.Step() != sqliteRow {
		return VoucherPrice{}, fmt.Errorf("prodejni cena id=%d uz neexistuje", id)
	}
	return VoucherPrice{
		CommodityID: st.ColInt64(0), PLU: st.ColBytes(1), Name: st.ColBytes(2), ItemAmount: st.ColInt64(3), CommodityType: st.ColInt64(4), Usage: st.ColInt64(5), UseQuality: st.ColInt64(6), PrintNote: st.ColBytes(7), Duration: st.ColInt64(8), RateUse: st.ColInt64(9), DitType: st.ColInt64(10), RateDuration: st.ColInt64(11), PercentDiscount: st.ColInt64(12), CommodityStoreID: st.ColInt64(13), SellPriceID: st.ColInt64(14), PriceCentreID: st.ColInt64(15), PriceUserID: st.ColInt64(16), Price: st.ColDouble(17), Vat: st.ColDouble(18), PriceNoVat: st.ColDouble(19),
	}, nil
}

func listBuyPrices(db *DB, cid int64) ([]BuyPrice, error) {
	st, e := db.Prepare(`SELECT id,CAST(price AS TEXT),CAST(price_vat AS TEXT),CAST(price_novat AS TEXT) FROM commodity_buyprice WHERE id_commodity=? AND valid=1 ORDER BY id`)
	if e != nil {
		return nil, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{cid})
	var out []BuyPrice
	for {
		rc := st.Step()
		if rc == sqliteDone {
			break
		}
		if rc != sqliteRow {
			return nil, fmt.Errorf("buyprice query: %s", db.Errmsg())
		}
		out = append(out, BuyPrice{st.ColInt64(0), st.ColDouble(1), st.ColDouble(2), st.ColDouble(3)})
	}
	return out, nil
}

func getBuyPriceByID(db *DB, cid, id int64) (BuyPrice, error) {
	st, e := db.Prepare(`SELECT id,CAST(price AS TEXT),CAST(price_vat AS TEXT),CAST(price_novat AS TEXT) FROM commodity_buyprice WHERE id_commodity=? AND id=? AND valid=1`)
	if e != nil {
		return BuyPrice{}, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{cid, id})
	if st.Step() != sqliteRow {
		return BuyPrice{}, errNoRow
	}
	return BuyPrice{st.ColInt64(0), st.ColDouble(1), st.ColDouble(2), st.ColDouble(3)}, nil
}

func listStores(db *DB, cid int64) ([]StoreChoice, error) {
	st, e := db.Prepare(`SELECT id,id_inventory,StoreID FROM commodity_store2 WHERE id_commodity=? ORDER BY id_inventory DESC,id DESC`)
	if e != nil {
		return nil, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{cid})
	var out []StoreChoice
	seen := map[int64]bool{}
	for {
		rc := st.Step()
		if rc == sqliteDone {
			break
		}
		if rc != sqliteRow {
			return nil, fmt.Errorf("store query: %s", db.Errmsg())
		}
		s := StoreChoice{st.ColInt64(0), st.ColInt64(1), st.ColInt64(2)}
		if !seen[s.StoreID] {
			out = append(out, s)
			seen[s.StoreID] = true
		}
	}
	return out, nil
}

func getStoreByID(db *DB, cid, storeID int64) (StoreChoice, error) {
	st, e := db.Prepare(`SELECT id,id_inventory,StoreID FROM commodity_store2 WHERE id_commodity=? AND StoreID=? ORDER BY id_inventory DESC,id DESC LIMIT 1`)
	if e != nil {
		return StoreChoice{}, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{cid, storeID})
	if st.Step() != sqliteRow {
		return StoreChoice{}, errNoRow
	}
	return StoreChoice{st.ColInt64(0), st.ColInt64(1), st.ColInt64(2)}, nil
}

func listPayments(db *DB) ([]Payment, error) {
	st, e := db.Prepare(`SELECT id,name FROM billpayment WHERE valid=1 AND ShowInBill=1 ORDER BY id`)
	if e != nil {
		return nil, e
	}
	defer st.Finalize()
	var out []Payment
	for {
		rc := st.Step()
		if rc == sqliteDone {
			break
		}
		if rc != sqliteRow {
			return nil, fmt.Errorf("payment query: %s", db.Errmsg())
		}
		out = append(out, Payment{st.ColInt64(0), st.ColBytes(1)})
	}
	return out, nil
}

func getPaymentByID(db *DB, id int64) (Payment, error) {
	st, e := db.Prepare(`SELECT id,name FROM billpayment WHERE valid=1 AND ShowInBill=1 AND id=?`)
	if e != nil {
		return Payment{}, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{id})
	if st.Step() != sqliteRow {
		return Payment{}, errNoRow
	}
	return Payment{st.ColInt64(0), st.ColBytes(1)}, nil
}

func voucherCodeExists(db *DB, code string) (bool, error) {
	st, e := db.Prepare(`SELECT CASE WHEN EXISTS(SELECT 1 FROM BillItemCodes WHERE Code=?) OR EXISTS(SELECT 1 FROM BillItemCodesWeb WHERE Code=?) THEN 1 ELSE 0 END`)
	if e != nil {
		return false, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{code, code})
	if st.Step() != sqliteRow {
		return false, errors.New("nelze overit kod voucheru")
	}
	return st.ColInt64(0) != 0, nil
}

func nextBillNumberForCentre(db *DB, centreID int64) (int64, error) {
	st, e := db.Prepare(`SELECT COUNT(*),COALESCE(MAX(billnumber),0) FROM bill WHERE id_centre=? AND billnumber IS NOT NULL AND billnumber>0`)
	if e != nil {
		return 0, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{centreID})
	if st.Step() != sqliteRow {
		return 0, errors.New("nelze zjistit radu cisel uctenek")
	}
	count := st.ColInt64(0)
	maxNo := st.ColInt64(1)
	if count == 0 || maxNo <= 0 {
		return 0, errors.New("pro vybranou provozovnu neni v DB predchozi billnumber; V016 jej nebude odhadovat")
	}
	if maxNo == 1<<63-1 {
		return 0, errors.New("rada billnumber dosahla maximalni hodnoty")
	}

	// HairSoft's bill.id is the globally unique database primary key.
	// billnumber is a business receipt/account number scoped to id_centre.
	// Different centres may therefore legitimately use the same billnumber.
	// V015 incorrectly checked billnumber globally and could jump over another
	// centre's sequence. V016 continues only the selected centre's own series.
	return maxNo + 1, nil
}

func findImportedByBillGID(db *DB, billGID string) (SaleIDs, bool, error) {
	st, e := db.Prepare(`SELECT b.id,bi.id,bic.ID,COALESCE(b.billnumber,0)
        FROM bill b JOIN billitem bi ON bi.id_bill=b.id JOIN BillItemCodes bic ON bic.BillItemID=bi.id
        WHERE b.GID=? LIMIT 1`)
	if e != nil {
		return SaleIDs{}, false, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{billGID})
	rc := st.Step()
	if rc == sqliteDone {
		return SaleIDs{}, false, nil
	}
	if rc != sqliteRow {
		return SaleIDs{}, false, fmt.Errorf("idempotency query: %s", db.Errmsg())
	}
	return SaleIDs{BillID: st.ColInt64(0), BillItemID: st.ColInt64(1), CodeID: st.ColInt64(2), BillNumber: st.ColInt64(3)}, true, nil
}

func insertSale(db *DB, centre Centre, user User, vp VoucherPrice, buy BuyPrice, store StoreChoice, payment Payment, code string, duration int64, validTo string, billNumber int64, saleTime string, gids [3]string, salePrice, salePriceNoVat float64) (SaleIDs, error) {
	var ids SaleIDs
	billSQL := `INSERT INTO bill (
      id_centre,id_user,id_customer,price,price_novat,startbill,endbill,payment,
      commodity_quality,genre,printed,services_price,services_price_novat,goods_price,goods_price_novat,
      materials_price,materials_price_novat,valid,DiscountCommodity,DiscountService,Id_Order,Discarted,
      OutEET,OrgBillID,GID,vouchers_price,vouchers_price_novat,PaidVoucherPrice,payment2,BICID,BICWID,
      VoucherAmountRemaining,ComboPayment_price,ComboPayment,billnumber,OrgBillNumber,QRKryptoPaymentRate,
      QRKryptoPaymentCurrency
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)`
	st, err := db.Prepare(billSQL)
	if err != nil {
		return ids, err
	}
	args := []any{centre.ID, user.ID, int64(0), salePrice, salePriceNoVat, saleTime, saleTime, payment.ID,
		int64(0), int64(0), int64(0), 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, int64(1), int64(0), int64(0), int64(0), int64(0),
		int64(0), int64(0), gids[0], salePrice, salePriceNoVat, 0.0, int64(0), int64(0), int64(0), 0.0, 0.0, int64(0), billNumber,
		int64(0), "", ""}
	if err = st.BindAll(args); err == nil {
		err = st.StepDone()
	}
	st.Finalize()
	if err != nil {
		return ids, err
	}
	ids.BillID = db.LastInsertRowID()
	ids.BillNumber = billNumber

	itemSQL := `INSERT INTO billitem (
      id_bill,id_commodity,StoreID,id_price,id_buyprice,amount,price,price_novat,unitprice,unitprice_novat,
      price_vat,id_centre,id_user,id_customer,commodity_name,commodity_item_amount,commodity_type,commodity_usage,
      commodity_use_quality,commodity_quality,commodity_plu,commodity_print_note,inserted,actiondate,valid,
      DiscountValue,BaseUnitPrice,Id_OrderActionRow,BaseUnitPriceNoVat,RateUse,DitType,Makro,Discarted,OrgBillItemID,
      GID,RateMinute,RateDuration,PercentDiscount
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)`
	st2, err := db.Prepare(itemSQL)
	if err != nil {
		return ids, err
	}
	args2 := []any{ids.BillID, vp.CommodityID, store.StoreID, vp.SellPriceID, buy.ID, int64(1), salePrice, salePriceNoVat, salePrice, salePriceNoVat,
		vp.Vat, centre.ID, user.ID, int64(0), vp.Name, vp.ItemAmount, vp.CommodityType, vp.Usage, vp.UseQuality, int64(0), vp.PLU, vp.PrintNote,
		saleTime, saleTime, int64(1), int64(0), salePrice, int64(0), salePriceNoVat, vp.RateUse, vp.DitType, int64(0), int64(0), int64(0),
		gids[1], 0.0, vp.RateDuration, vp.PercentDiscount}
	if err = st2.BindAll(args2); err == nil {
		err = st2.StepDone()
	}
	st2.Finalize()
	if err != nil {
		return ids, err
	}
	ids.BillItemID = db.LastInsertRowID()

	codeSQL := `INSERT INTO BillItemCodes (
      BillItemID,Code,DateOfSale,UserIDSale,Duration,DateValidTo,DateOfUse,UserIdUse,GID,PayBillID,Price,valid,
      RefundBillItemID,Export
    ) VALUES (?,?,?,?,?,?,NULL,?,?,?,?,?,?,?)`
	st3, err := db.Prepare(codeSQL)
	if err != nil {
		return ids, err
	}
	args3 := []any{ids.BillItemID, code, saleTime, user.ID, duration, validTo, int64(0), gids[2], int64(0), salePrice, int64(1), int64(0), int64(2)}
	if err = st3.BindAll(args3); err == nil {
		err = st3.StepDone()
	}
	st3.Finalize()
	if err != nil {
		return ids, err
	}
	ids.CodeID = db.LastInsertRowID()
	return ids, nil
}

func verifyInserted(db *DB, ids SaleIDs, code string, price float64, centreID, userID, paymentID int64) error {
	st, err := db.Prepare(`SELECT b.id,bi.id,bic.ID,bic.Code,CAST(b.price AS TEXT),CAST(b.vouchers_price AS TEXT),CAST(bi.price AS TEXT),CAST(bic.Price AS TEXT),b.id_centre,b.id_user,b.payment,b.billnumber
      FROM bill b JOIN billitem bi ON bi.id_bill=b.id JOIN BillItemCodes bic ON bic.BillItemID=bi.id
      WHERE b.id=? AND bi.id=? AND bic.ID=?`)
	if err != nil {
		return err
	}
	defer st.Finalize()
	_ = st.BindAll([]any{ids.BillID, ids.BillItemID, ids.CodeID})
	if st.Step() != sqliteRow {
		return fmt.Errorf("overovaci SELECT nevratil radek: %s", db.Errmsg())
	}
	if string(st.ColBytes(3)) != code {
		return errors.New("overeni kodu selhalo")
	}
	for _, idx := range []int{4, 5, 6, 7} {
		if abs(st.ColDouble(idx)-price) > 0.0001 {
			return fmt.Errorf("overeni ceny selhalo ve sloupci %d", idx)
		}
	}
	if st.ColInt64(8) != centreID || st.ColInt64(9) != userID || st.ColInt64(10) != paymentID || st.ColInt64(11) != ids.BillNumber {
		return errors.New("overeni vazeb prodeje selhalo")
	}
	return nil
}

func jobGIDs(secret, jobID string) [3]string {
	if strings.TrimSpace(secret) == "" || strings.TrimSpace(jobID) == "" {
		a, _ := newRandomGID()
		b, _ := newRandomGID()
		c, _ := newRandomGID()
		return [3]string{a, b, c}
	}
	return [3]string{
		hmacGID(secret, "bill:"+jobID),
		hmacGID(secret, "item:"+jobID),
		hmacGID(secret, "code:"+jobID),
	}
}

func hmacGID(secret, value string) string {
	h := hmac.New(sha256.New, []byte(secret))
	_, _ = h.Write([]byte(value))
	sum := h.Sum(nil)
	return strings.ToUpper(hex.EncodeToString(sum[:16]))
}

func newRandomGID() (string, error) {
	b := make([]byte, 16)
	if _, e := rand.Read(b); e != nil {
		return "", e
	}
	return strings.ToUpper(hex.EncodeToString(b)), nil
}

func parseSaleTime(raw string) (time.Time, error) {
	raw = strings.TrimSpace(raw)
	if raw == "" {
		return time.Time{}, errors.New("chybi datum prodeje")
	}
	if t, err := time.Parse(time.RFC3339, raw); err == nil {
		return t, nil
	}
	if t, err := time.ParseInLocation("2006-01-02 15:04:05", raw, time.Local); err == nil {
		return t, nil
	}
	return time.Time{}, fmt.Errorf("neplatne datum prodeje %q", raw)
}

func abs(x float64) float64 {
	if x < 0 {
		return -x
	}
	return x
}

func historicalVoucherDurations(db *DB, commodityID int64) ([]int64, error) {
	st, e := db.Prepare(`SELECT DISTINCT bic.Duration
        FROM BillItemCodes bic
        JOIN billitem bi ON bi.id=bic.BillItemID
        WHERE bi.id_commodity=? AND bic.valid=1 AND bic.Duration IS NOT NULL AND bic.Duration>0
        ORDER BY bic.Duration`)
	if e != nil {
		return nil, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{commodityID})
	var out []int64
	for {
		rc := st.Step()
		if rc == sqliteDone {
			break
		}
		if rc != sqliteRow {
			return nil, fmt.Errorf("duration history query: %s", db.Errmsg())
		}
		out = append(out, st.ColInt64(0))
	}
	return out, nil
}

func historicalVoucherValidityTimes(db *DB, commodityID int64) ([]string, error) {
	st, e := db.Prepare(`SELECT DISTINCT substr(bic.DateValidTo,12,8)
        FROM BillItemCodes bic
        JOIN billitem bi ON bi.id=bic.BillItemID
        WHERE bi.id_commodity=? AND bic.valid=1 AND bic.DateValidTo IS NOT NULL AND length(bic.DateValidTo)>=19
        ORDER BY 1`)
	if e != nil {
		return nil, e
	}
	defer st.Finalize()
	_ = st.BindAll([]any{commodityID})
	var out []string
	for {
		rc := st.Step()
		if rc == sqliteDone {
			break
		}
		if rc != sqliteRow {
			return nil, fmt.Errorf("validity time history query: %s", db.Errmsg())
		}
		v := strings.TrimSpace(string(st.ColBytes(0)))
		if v != "" {
			out = append(out, v)
		}
	}
	return out, nil
}

func addMonthsClamped(t time.Time, months int) time.Time {
	y, m, d := t.Date()
	targetFirst := time.Date(y, m+time.Month(months), 1, t.Hour(), t.Minute(), t.Second(), 0, t.Location())
	nextFirst := targetFirst.AddDate(0, 1, 0)
	lastDay := nextFirst.AddDate(0, 0, -1).Day()
	if d > lastDay {
		d = lastDay
	}
	return time.Date(targetFirst.Year(), targetFirst.Month(), d, t.Hour(), t.Minute(), t.Second(), 0, t.Location())
}
