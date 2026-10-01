//go:build windows

package main

import (
	"bytes"
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"os"
	"strings"
	"time"
)

const directoryBaseURL = "https://bridge.bonfero.com"

type BridgeJob struct {
	JobID            string `json:"jobId"`
	OrderID          string `json:"orderId"`
	TemplateID       string `json:"templateId"`
	Code             string `json:"code"`
	AmountCzk        int    `json:"amountCzk"`
	PaymentMethod    string `json:"paymentMethod"`
	PaymentReference string `json:"paymentReference"`
	SoldAt           string `json:"soldAt"`
}

type BridgeResult struct {
	JobID      string `json:"jobId"`
	Status     string `json:"status"`
	Message    string `json:"message,omitempty"`
	BillID     int64  `json:"billId,omitempty"`
	BillItemID int64  `json:"billItemId,omitempty"`
	CodeID     int64  `json:"codeId,omitempty"`
	BillNumber int64  `json:"billNumber,omitempty"`
	ImportedAt string `json:"importedAt,omitempty"`
}

type directoryStatus struct {
	Registered        bool       `json:"registered"`
	Paired            bool       `json:"paired"`
	SiteBaseURL       string     `json:"siteBaseUrl"`
	SiteName          string     `json:"siteName"`
	SiteInstanceID    string     `json:"siteInstanceId"`
	SiteToken         string     `json:"siteToken,omitempty"`
	RCIdentity        RCIdentity `json:"rcIdentity"`
	DirectoryProtocol int        `json:"directoryProtocol"`
}

type targetStatus struct {
	Paired            bool   `json:"paired"`
	Enabled           bool   `json:"enabled"`
	Site              string `json:"site"`
	SiteBaseURL       string `json:"siteBaseUrl"`
	DirectoryProtocol int    `json:"directoryProtocol"`
}

type remoteConfig struct {
	Revision         int                 `json:"revision"`
	Ready            bool                `json:"ready"`
	CentreID         int64               `json:"centreId"`
	UserID           int64               `json:"userId"`
	PollSeconds      int                 `json:"pollSeconds"`
	PaymentMappings  flexibleInt64Map    `json:"paymentMappings"`
	TemplateMappings flexibleTemplateMap `json:"templateMappings"`
}

type flexibleInt64Map map[string]int64

type flexibleTemplateMap map[string]TemplateMapping

func (m *flexibleInt64Map) UnmarshalJSON(b []byte) error {
	t := strings.TrimSpace(string(b))
	if t == "" || t == "null" || t == "[]" {
		*m = flexibleInt64Map{}
		return nil
	}
	var obj map[string]int64
	if err := json.Unmarshal(b, &obj); err == nil {
		*m = obj
		return nil
	}
	return fmt.Errorf("paymentMappings ma nepodporovany JSON format")
}

func (m *flexibleTemplateMap) UnmarshalJSON(b []byte) error {
	t := strings.TrimSpace(string(b))
	if t == "" || t == "null" || t == "[]" {
		*m = flexibleTemplateMap{}
		return nil
	}
	var obj map[string]TemplateMapping
	if err := json.Unmarshal(b, &obj); err == nil {
		*m = obj
		return nil
	}
	return fmt.Errorf("templateMappings ma nepodporovany JSON format")
}

type httpError struct {
	Status int
	Body   string
}

func (e *httpError) Error() string { return fmt.Sprintf("HTTP %d: %s", e.Status, e.Body) }

func httpClient() *http.Client {
	return &http.Client{Timeout: 20 * time.Second}
}

func validateServerURL(raw string) error {
	u, err := url.Parse(strings.TrimSpace(raw))
	if err != nil {
		return err
	}
	if u.Host == "" {
		return errors.New("neplatna adresa webu")
	}
	if u.Scheme != "https" {
		if !(u.Scheme == "http" && (u.Hostname() == "localhost" || u.Hostname() == "127.0.0.1")) {
			return errors.New("Bridge vyzaduje HTTPS")
		}
	}
	return nil
}

func directoryURL(route string) string {
	return directoryBaseURL + "/api/pairing.php?route=" + url.QueryEscape(route)
}

func targetURL(base, route string) string {
	return strings.TrimRight(base, "/") + "/api/bridge.php?route=" + url.QueryEscape(route)
}

func jsonRequest(method, endpoint string, payload any, headers map[string]string, out any) error {
	var body io.Reader
	if payload != nil {
		b, err := json.Marshal(payload)
		if err != nil {
			return err
		}
		body = bytes.NewReader(b)
	}
	req, err := http.NewRequest(method, endpoint, body)
	if err != nil {
		return err
	}
	req.Header.Set("Accept", "application/json")
	if payload != nil {
		req.Header.Set("Content-Type", "application/json")
	}
	for k, v := range headers {
		req.Header.Set(k, v)
	}
	resp, err := httpClient().Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()
	b, _ := io.ReadAll(io.LimitReader(resp.Body, 1024*1024))
	if resp.StatusCode < 200 || resp.StatusCode >= 300 {
		return &httpError{Status: resp.StatusCode, Body: strings.TrimSpace(string(b))}
	}
	if out != nil && len(b) > 0 {
		if err := json.Unmarshal(b, out); err != nil {
			return fmt.Errorf("neplatna JSON odpoved: %w", err)
		}
	}
	return nil
}

func directoryRegister(c Config) (directoryStatus, error) {
	host, _ := os.Hostname()
	rc, rcErr := readRCIdentity()
	if rcErr != nil {
		logf("RC IDENTITY WARN %v", rcErr)
	}
	payload := map[string]any{
		"bridgeId":    c.BridgeID,
		"token":       c.BridgeToken,
		"pairingCode": c.PairingCode,
		"version":     bridgeVersion,
		"machineName": host,
		"rcIdentity":  rc,
	}
	var out directoryStatus
	err := jsonRequest(http.MethodPost, directoryURL("register"), payload, nil, &out)
	return out, err
}

func directoryGetStatus(c Config) (directoryStatus, error) {
	var out directoryStatus
	err := jsonRequest(http.MethodGet, directoryURL("status"), nil, map[string]string{
		"X-HS-Bridge-ID":    c.BridgeID,
		"X-HS-Bridge-Token": c.BridgeToken,
	}, &out)
	return out, err
}

func targetJSON(c Config, ds directoryStatus, method, route string, payload any, out any) error {
	base := strings.TrimSpace(ds.SiteBaseURL)
	token := strings.TrimSpace(ds.SiteToken)
	if base == "" || token == "" {
		return errors.New("Bridge nema aktivni cilovy web")
	}
	if err := validateServerURL(base); err != nil {
		return err
	}
	return jsonRequest(method, targetURL(base, route), payload, map[string]string{
		"X-HS-Bridge-ID":    c.BridgeID,
		"X-HS-Bridge-Token": token,
	}, out)
}

func getTargetStatus(c Config, ds directoryStatus) (targetStatus, error) {
	var out targetStatus
	err := targetJSON(c, ds, http.MethodGet, "status", nil, &out)
	return out, err
}

func postCatalog(c Config, ds directoryStatus, catalog HairSoftCatalog) error {
	return targetJSON(c, ds, http.MethodPost, "catalog", catalog, nil)
}

func getRemoteConfig(c Config, ds directoryStatus) (remoteConfig, error) {
	var out remoteConfig
	err := targetJSON(c, ds, http.MethodGet, "config", nil, &out)
	return out, err
}

func getNextJob(c Config, ds directoryStatus) (*BridgeJob, error) {
	var resp struct {
		Job *BridgeJob `json:"job"`
	}
	if err := targetJSON(c, ds, http.MethodGet, "jobs/next", nil, &resp); err != nil {
		return nil, err
	}
	return resp.Job, nil
}

func postJobResult(c Config, ds directoryStatus, r BridgeResult) error {
	return targetJSON(c, ds, http.MethodPost, "jobs/result", r, nil)
}

func isHTTPStatus(err error, status int) bool {
	var he *httpError
	return errors.As(err, &he) && he.Status == status
}
