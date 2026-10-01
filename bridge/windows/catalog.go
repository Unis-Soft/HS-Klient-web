//go:build windows

package main

import (
	"fmt"
	"sort"
	"strings"
)

type catalogCentre struct {
	ID   int64  `json:"id"`
	Name string `json:"name"`
}

type catalogUser struct {
	ID       int64  `json:"id"`
	CentreID int64  `json:"centreId"`
	Name     string `json:"name"`
}

type catalogPayment struct {
	ID   int64  `json:"id"`
	Name string `json:"name"`
}

type catalogVoucher struct {
	Key            string   `json:"key"`
	CommodityID    int64    `json:"commodityId"`
	SellPriceID    int64    `json:"sellPriceId"`
	BuyPriceID     int64    `json:"buyPriceId"`
	StoreID        int64    `json:"storeId"`
	Name           string   `json:"name"`
	PLU            string   `json:"plu"`
	Price          float64  `json:"price"`
	Vat            float64  `json:"vat"`
	PriceNoVat     float64  `json:"priceNoVat"`
	PriceCentreID  int64    `json:"priceCentreId"`
	PriceUserID    int64    `json:"priceUserId"`
	ValidityMonths []int64  `json:"validityMonths"`
	ValidityTimes  []string `json:"validityTimes"`
}

type HairSoftCatalog struct {
	Error    string           `json:"error,omitempty"`
	DBPath   string           `json:"dbPath,omitempty"`
	DBSource string           `json:"dbSource,omitempty"`
	Centres  []catalogCentre  `json:"centres,omitempty"`
	Users    []catalogUser    `json:"users,omitempty"`
	Payments []catalogPayment `json:"payments,omitempty"`
	Vouchers []catalogVoucher `json:"vouchers,omitempty"`
}

func listAllUsers(db *DB) ([]catalogUser, error) {
	// V015 intentionally includes both normal and service/virtual HairSoft users.
	st, err := db.Prepare(`SELECT id,id_centre,name,surname FROM user WHERE valid=1 ORDER BY id_centre,id`)
	if err != nil {
		return nil, err
	}
	defer st.Finalize()
	var out []catalogUser
	for {
		rc := st.Step()
		if rc == sqliteDone {
			break
		}
		if rc != sqliteRow {
			return nil, fmt.Errorf("user catalog query: %s", db.Errmsg())
		}
		name := strings.TrimSpace(strings.TrimSpace(displayText(st.ColBytes(2))) + " " + strings.TrimSpace(displayText(st.ColBytes(3))))
		if name == "" {
			name = fmt.Sprintf("ID %d", st.ColInt64(0))
		}
		out = append(out, catalogUser{ID: st.ColInt64(0), CentreID: st.ColInt64(1), Name: name})
	}
	return out, nil
}

func listAllVoucherSellPrices(db *DB) ([]VoucherPrice, error) {
	sql := `SELECT c.id,c.plu,c.name,c.item_amout,c.commodity_type,c.usage,c.use_quality,c.print_note,c.Duration,c.RateUse,c.DitType,c.RateDuration,c.PercentDiscount,c.StoreID,
      sp.id,sp.id_centre,sp.id_user,CAST(sp.price AS TEXT),CAST(sp.price_vat AS TEXT),CAST(sp.price_novat AS TEXT)
      FROM commodity c JOIN commodity_sellprice sp ON sp.id_commodity=c.id AND sp.valid=1
      WHERE c.valid=1 AND c.commodity_type=8
      ORDER BY c.id,sp.id`
	st, err := db.Prepare(sql)
	if err != nil {
		return nil, err
	}
	defer st.Finalize()
	var out []VoucherPrice
	for {
		rc := st.Step()
		if rc == sqliteDone {
			break
		}
		if rc != sqliteRow {
			return nil, fmt.Errorf("voucher catalog query: %s", db.Errmsg())
		}
		out = append(out, VoucherPrice{
			CommodityID: st.ColInt64(0), PLU: st.ColBytes(1), Name: st.ColBytes(2), ItemAmount: st.ColInt64(3), CommodityType: st.ColInt64(4), Usage: st.ColInt64(5), UseQuality: st.ColInt64(6), PrintNote: st.ColBytes(7), Duration: st.ColInt64(8), RateUse: st.ColInt64(9), DitType: st.ColInt64(10), RateDuration: st.ColInt64(11), PercentDiscount: st.ColInt64(12), CommodityStoreID: st.ColInt64(13), SellPriceID: st.ColInt64(14), PriceCentreID: st.ColInt64(15), PriceUserID: st.ColInt64(16), Price: st.ColDouble(17), Vat: st.ColDouble(18), PriceNoVat: st.ColDouble(19),
		})
	}
	return out, nil
}

func uniqueInt64(values []int64) []int64 {
	seen := map[int64]bool{}
	var out []int64
	for _, v := range values {
		if v <= 0 || seen[v] {
			continue
		}
		seen[v] = true
		out = append(out, v)
	}
	sort.Slice(out, func(i, j int) bool { return out[i] < out[j] })
	return out
}

func uniqueStrings(values []string) []string {
	seen := map[string]bool{}
	var out []string
	for _, v := range values {
		v = strings.TrimSpace(v)
		if v == "" || seen[v] {
			continue
		}
		seen[v] = true
		out = append(out, v)
	}
	sort.Strings(out)
	return out
}

func buildHairSoftCatalog(dbPath, source string) (HairSoftCatalog, error) {
	db, err := openDB(dbPath)
	if err != nil {
		return HairSoftCatalog{}, err
	}
	defer db.Close()
	db.BusyTimeout(2500)
	if err := validateDB(db); err != nil {
		return HairSoftCatalog{}, err
	}

	centres, err := listCentres(db)
	if err != nil {
		return HairSoftCatalog{}, err
	}
	users, err := listAllUsers(db)
	if err != nil {
		return HairSoftCatalog{}, err
	}
	payments, err := listPayments(db)
	if err != nil {
		return HairSoftCatalog{}, err
	}
	sellPrices, err := listAllVoucherSellPrices(db)
	if err != nil {
		return HairSoftCatalog{}, err
	}

	out := HairSoftCatalog{DBPath: dbPath, DBSource: source}
	for _, c := range centres {
		out.Centres = append(out.Centres, catalogCentre{ID: c.ID, Name: strings.TrimSpace(displayText(c.Name))})
	}
	out.Users = users
	for _, p := range payments {
		out.Payments = append(out.Payments, catalogPayment{ID: p.ID, Name: strings.TrimSpace(displayText(p.Name))})
	}

	durationCache := map[int64][]int64{}
	timeCache := map[int64][]string{}
	for _, vp := range sellPrices {
		months, ok := durationCache[vp.CommodityID]
		if !ok {
			months, err = historicalVoucherDurations(db, vp.CommodityID)
			if err != nil {
				return HairSoftCatalog{}, err
			}
			if vp.Duration > 0 {
				months = append(months, vp.Duration)
			}
			months = uniqueInt64(months)
			durationCache[vp.CommodityID] = months
		}
		times, ok := timeCache[vp.CommodityID]
		if !ok {
			times, err = historicalVoucherValidityTimes(db, vp.CommodityID)
			if err != nil {
				return HairSoftCatalog{}, err
			}
			times = uniqueStrings(times)
			timeCache[vp.CommodityID] = times
		}
		buys, err := listBuyPrices(db, vp.CommodityID)
		if err != nil {
			return HairSoftCatalog{}, err
		}
		stores, err := listStores(db, vp.CommodityID)
		if err != nil {
			return HairSoftCatalog{}, err
		}
		for _, buy := range buys {
			for _, store := range stores {
				out.Vouchers = append(out.Vouchers, catalogVoucher{
					Key:            fmt.Sprintf("%d:%d:%d:%d", vp.CommodityID, vp.SellPriceID, buy.ID, store.StoreID),
					CommodityID:    vp.CommodityID,
					SellPriceID:    vp.SellPriceID,
					BuyPriceID:     buy.ID,
					StoreID:        store.StoreID,
					Name:           strings.TrimSpace(displayText(vp.Name)),
					PLU:            strings.TrimSpace(displayText(vp.PLU)),
					Price:          vp.Price,
					Vat:            vp.Vat,
					PriceNoVat:     vp.PriceNoVat,
					PriceCentreID:  vp.PriceCentreID,
					PriceUserID:    vp.PriceUserID,
					ValidityMonths: append([]int64(nil), months...),
					ValidityTimes:  append([]string(nil), times...),
				})
			}
		}
	}
	return out, nil
}
