//go:build windows

package main

import (
	"crypto/sha256"
	"encoding/hex"
	"errors"
	"fmt"
	"io"
	"net/http"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"time"
)

const programPhotoPollInterval = 10 * time.Second

type programPhotoFile struct {
	ID     int64  `json:"id"`
	UUID   string `json:"uuid"`
	Name   string `json:"name"`
	Mime   string `json:"mime"`
	Size   int64  `json:"size"`
	SHA256 string `json:"sha256"`
}

type programPhotoJob struct {
	ID          int64              `json:"id"`
	UUID        string             `json:"uuid"`
	CustomerID  int64              `json:"customerId"`
	ProgramID   int64              `json:"programId"`
	ProgramName string             `json:"programName"`
	DateFolder  string             `json:"dateFolder"`
	Subfolder   string             `json:"subfolder"`
	Files       []programPhotoFile `json:"files"`
}

func getNextProgramPhotoJob(c Config) (*programPhotoJob, error) {
	var out struct {
		OK  bool             `json:"ok"`
		Job *programPhotoJob `json:"job"`
	}
	if err := jsonRequest(http.MethodGet, programActionURL("photo-next"), nil, hsKlientHeaders(c), &out); err != nil {
		return nil, err
	}
	if !out.OK {
		return nil, errors.New("HS Klient PROGRAMS photo queue neni OK")
	}
	return out.Job, nil
}

func postProgramPhotoResult(c Config, jobID int64, status, message string) error {
	payload := map[string]any{"jobId": jobID, "status": status, "message": message}
	var out struct {
		OK bool `json:"ok"`
	}
	if err := jsonRequest(http.MethodPost, programActionURL("photo-result"), payload, hsKlientHeaders(c), &out); err != nil {
		return err
	}
	if !out.OK {
		return errors.New("HS Klient PROGRAMS photo result neni OK")
	}
	return nil
}

func safePhotoPathPart(v string) (string, error) {
	v = strings.TrimSpace(v)
	if v == "" {
		return "", nil
	}
	if v == "." || v == ".." {
		return "", errors.New("neplatny nazev slozky")
	}
	if strings.ContainsAny(v, `<>:"/\\|?*`) {
		return "", errors.New("neplatny nazev slozky")
	}
	for _, r := range v {
		if r < 32 {
			return "", errors.New("neplatny nazev slozky")
		}
	}
	return strings.TrimRight(v, " ."), nil
}

func programPhotoCustomerRoot(customerID int64) (string, error) {
	if customerID <= 0 {
		return "", errors.New("neplatne HairSoft ID zakaznika")
	}
	base := strings.TrimSpace(executableDir())
	if base == "" {
		return "", errors.New("nelze zjistit adresar HairSoft")
	}
	return filepath.Join(base, "Images", "Customers", fmt.Sprintf("%010d", customerID)), nil
}

func fileSHA256(path string) (string, error) {
	f, err := os.Open(path)
	if err != nil {
		return "", err
	}
	defer f.Close()
	h := sha256.New()
	if _, err := io.Copy(h, f); err != nil {
		return "", err
	}
	return hex.EncodeToString(h.Sum(nil)), nil
}

func downloadProgramPhotoFile(c Config, jobID int64, file programPhotoFile, target string) error {
	endpoint := programActionURL("photo-file") + "&jobId=" + strconv.FormatInt(jobID, 10) + "&fileId=" + strconv.FormatInt(file.ID, 10)
	req, err := http.NewRequest(http.MethodGet, endpoint, nil)
	if err != nil {
		return err
	}
	req.Header.Set("Accept", "application/octet-stream")
	for k, v := range hsKlientHeaders(c) {
		req.Header.Set(k, v)
	}
	resp, err := httpClient().Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()
	if resp.StatusCode < 200 || resp.StatusCode >= 300 {
		b, _ := io.ReadAll(io.LimitReader(resp.Body, 4096))
		return fmt.Errorf("HTTP %d: %s", resp.StatusCode, strings.TrimSpace(string(b)))
	}
	if err := os.MkdirAll(filepath.Dir(target), 0755); err != nil {
		return err
	}
	tmp := target + ".hsbridge-" + strings.TrimSpace(file.UUID) + ".part"
	_ = os.Remove(tmp)
	out, err := os.OpenFile(tmp, os.O_CREATE|os.O_WRONLY|os.O_TRUNC, 0644)
	if err != nil {
		return err
	}
	n, copyErr := io.Copy(out, resp.Body)
	closeErr := out.Close()
	if copyErr != nil {
		_ = os.Remove(tmp)
		return copyErr
	}
	if closeErr != nil {
		_ = os.Remove(tmp)
		return closeErr
	}
	if file.Size > 0 && n != file.Size {
		_ = os.Remove(tmp)
		return fmt.Errorf("velikost fotografie nesouhlasi: %d != %d", n, file.Size)
	}
	got, err := fileSHA256(tmp)
	if err != nil {
		_ = os.Remove(tmp)
		return err
	}
	if strings.ToLower(got) != strings.ToLower(strings.TrimSpace(file.SHA256)) {
		_ = os.Remove(tmp)
		return errors.New("SHA256 fotografie nesouhlasi")
	}
	if existing, err := fileSHA256(target); err == nil && strings.EqualFold(existing, got) {
		_ = os.Remove(tmp)
		return nil
	}
	_ = os.Remove(target)
	if err := os.Rename(tmp, target); err != nil {
		_ = os.Remove(tmp)
		return err
	}
	return nil
}

func processNextProgramPhotoJob(c Config) error {
	job, err := getNextProgramPhotoJob(c)
	if err != nil {
		return err
	}
	if job == nil {
		return nil
	}
	if job.ID <= 0 || strings.TrimSpace(job.UUID) == "" || job.CustomerID <= 0 || len(job.Files) == 0 {
		_ = postProgramPhotoResult(c, job.ID, "failed", "neplatna davka fotografii")
		return errors.New("neplatna PROGRAMS photo davka")
	}
	dateFolder, err := safePhotoPathPart(job.DateFolder)
	if err != nil || dateFolder == "" {
		_ = postProgramPhotoResult(c, job.ID, "failed", "neplatna datumova slozka")
		return errors.New("neplatna datumova slozka")
	}
	subfolder, err := safePhotoPathPart(job.Subfolder)
	if err != nil {
		_ = postProgramPhotoResult(c, job.ID, "failed", err.Error())
		return err
	}
	root, err := programPhotoCustomerRoot(job.CustomerID)
	if err != nil {
		return err
	}
	destDir := filepath.Join(root, dateFolder)
	if subfolder != "" {
		destDir = filepath.Join(destDir, subfolder)
	}
	if err := os.MkdirAll(destDir, 0755); err != nil {
		msg := err.Error()
		_ = postProgramPhotoResult(c, job.ID, "retry", msg)
		return err
	}
	for _, file := range job.Files {
		name := filepath.Base(strings.TrimSpace(file.Name))
		if name == "" || name == "." || name == ".." {
			msg := "neplatny nazev fotografie"
			_ = postProgramPhotoResult(c, job.ID, "failed", msg)
			return errors.New(msg)
		}
		target := filepath.Join(destDir, name)
		if err := downloadProgramPhotoFile(c, job.ID, file, target); err != nil {
			msg := strings.TrimSpace(err.Error())
			_ = postProgramPhotoResult(c, job.ID, "retry", msg)
			return fmt.Errorf("PROGRAMS photo deferred: %w", err)
		}
	}
	if err := postProgramPhotoResult(c, job.ID, "done", ""); err != nil {
		return err
	}
	logf("[PROGRAMS] PHOTOS OK job=%d customer=%d program=%d files=%d folder=%s", job.ID, job.CustomerID, job.ProgramID, len(job.Files), destDir)
	return nil
}

func runProgramPhotosModule() {
	var lastErr string
	for {
		c, err := loadConfig()
		if err != nil {
			msg := err.Error()
			if msg != lastErr {
				logf("[PROGRAMS] PHOTO CONFIG ERROR %v", err)
				lastErr = msg
			}
			time.Sleep(programPhotoPollInterval)
			continue
		}
		if err := processNextProgramPhotoJob(c); err != nil {
			msg := err.Error()
			if msg != lastErr {
				logf("[PROGRAMS] PHOTO DEFERRED %v", err)
				lastErr = msg
			}
		} else {
			lastErr = ""
		}
		time.Sleep(programPhotoPollInterval)
	}
}