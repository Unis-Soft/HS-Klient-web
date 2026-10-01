//go:build windows

package main

import (
	"context"
	"database/sql"
	"fmt"
	"strings"
	"time"
)

var mysqlProgramsSchemaTables = []string{
	"programs",
	"program_payments",
	"program_visits",
	"program_values",
	"program_values2customer",
}

func mysqlQuoteIdentifier(name string) string {
	return "`" + strings.ReplaceAll(name, "`", "``") + "`"
}

func compactSQLForLog(s string) string {
	return strings.Join(strings.Fields(strings.TrimSpace(s)), " ")
}

func logMySQLProgramsSchema() error {
	cfg, settingsPath, present, err := discoverHairSoftMySQL()
	if err != nil {
		return err
	}
	if !present {
		return nil
	}
	if !isLocalSQLHost(cfg.Host) {
		return fmt.Errorf("schema diagnostika je povolena jen na MySQL server PC; SQLHost=%s", cfg.Host)
	}

	provider, err := openMySQLHairSoftProvider(cfg)
	if err != nil {
		return err
	}
	p, ok := provider.(*mysqlHairSoftProvider)
	if !ok || p == nil || p.db == nil {
		provider.Close()
		return fmt.Errorf("MySQL diagnostika: neocekavany provider")
	}
	defer p.Close()

	logf("[MYSQL DIAG] BEGIN database=%s settings=%s tables=%d", cfg.Database, settingsPath, len(mysqlProgramsSchemaTables))

	for _, requestedTable := range mysqlProgramsSchemaTables {
		if err := logMySQLTableSchema(p.db, cfg.Database, requestedTable); err != nil {
			logf("[MYSQL DIAG] TABLE ERROR requested=%s err=%v", requestedTable, err)
		}
	}

	logf("[MYSQL DIAG] END")
	return nil
}

func logMySQLTableSchema(db *sql.DB, databaseName, requestedTable string) error {
	ctx, cancel := context.WithTimeout(context.Background(), 15*time.Second)
	defer cancel()

	var actualTable string
	err := db.QueryRowContext(ctx, `
SELECT TABLE_NAME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA=? AND LOWER(TABLE_NAME)=LOWER(?)
LIMIT 1`, databaseName, requestedTable).Scan(&actualTable)
	if err == sql.ErrNoRows {
		logf("[MYSQL DIAG] TABLE MISSING requested=%s", requestedTable)
		return nil
	}
	if err != nil {
		return err
	}

	logf("[MYSQL DIAG] TABLE requested=%s actual=%s", requestedTable, actualTable)

	rows, err := db.QueryContext(ctx, `
SELECT ORDINAL_POSITION,
       COLUMN_NAME,
       COLUMN_TYPE,
       IS_NULLABLE,
       COALESCE(COLUMN_KEY,''),
       COALESCE(EXTRA,''),
       COLUMN_DEFAULT,
       COALESCE(COLUMN_COMMENT,'')
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA=? AND TABLE_NAME=?
ORDER BY ORDINAL_POSITION`, databaseName, actualTable)
	if err != nil {
		return err
	}

	columnCount := 0
	for rows.Next() {
		var ordinal int
		var name, columnType, nullable, key, extra, comment string
		var defaultValue sql.NullString
		if err := rows.Scan(&ordinal, &name, &columnType, &nullable, &key, &extra, &defaultValue, &comment); err != nil {
			rows.Close()
			return err
		}
		def := "<NULL>"
		if defaultValue.Valid {
			def = defaultValue.String
		}
		logf("[MYSQL DIAG] COLUMN table=%s pos=%d name=%s type=%s nullable=%s key=%s default=%q extra=%q comment=%q",
			actualTable, ordinal, name, columnType, nullable, key, def, extra, comment)
		columnCount++
	}
	if err := rows.Err(); err != nil {
		rows.Close()
		return err
	}
	rows.Close()
	logf("[MYSQL DIAG] COLUMNS table=%s count=%d", actualTable, columnCount)

	idxRows, err := db.QueryContext(ctx, `
SELECT INDEX_NAME,
       NON_UNIQUE,
       SEQ_IN_INDEX,
       COALESCE(COLUMN_NAME,''),
       COALESCE(SUB_PART,0),
       INDEX_TYPE
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA=? AND TABLE_NAME=?
ORDER BY INDEX_NAME, SEQ_IN_INDEX`, databaseName, actualTable)
	if err != nil {
		return err
	}
	indexCount := 0
	for idxRows.Next() {
		var indexName, columnName, indexType string
		var nonUnique, seq, subPart int
		if err := idxRows.Scan(&indexName, &nonUnique, &seq, &columnName, &subPart, &indexType); err != nil {
			idxRows.Close()
			return err
		}
		logf("[MYSQL DIAG] INDEX table=%s name=%s unique=%t seq=%d column=%s subpart=%d type=%s",
			actualTable, indexName, nonUnique == 0, seq, columnName, subPart, indexType)
		indexCount++
	}
	if err := idxRows.Err(); err != nil {
		idxRows.Close()
		return err
	}
	idxRows.Close()
	logf("[MYSQL DIAG] INDEXES table=%s count=%d", actualTable, indexCount)

	fkRows, err := db.QueryContext(ctx, `
SELECT CONSTRAINT_NAME,
       COLUMN_NAME,
       REFERENCED_TABLE_NAME,
       REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA=?
  AND TABLE_NAME=?
  AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY CONSTRAINT_NAME, ORDINAL_POSITION`, databaseName, actualTable)
	if err != nil {
		return err
	}
	fkCount := 0
	for fkRows.Next() {
		var constraintName, columnName, refTable, refColumn string
		if err := fkRows.Scan(&constraintName, &columnName, &refTable, &refColumn); err != nil {
			fkRows.Close()
			return err
		}
		logf("[MYSQL DIAG] FK table=%s constraint=%s column=%s ref=%s.%s",
			actualTable, constraintName, columnName, refTable, refColumn)
		fkCount++
	}
	if err := fkRows.Err(); err != nil {
		fkRows.Close()
		return err
	}
	fkRows.Close()
	logf("[MYSQL DIAG] FOREIGN_KEYS table=%s count=%d", actualTable, fkCount)

	var createTableName, createSQL string
	showSQL := "SHOW CREATE TABLE " + mysqlQuoteIdentifier(actualTable)
	if err := db.QueryRowContext(ctx, showSQL).Scan(&createTableName, &createSQL); err != nil {
		return err
	}
	logf("[MYSQL DIAG] CREATE table=%s sql=%s", createTableName, compactSQLForLog(createSQL))
	return nil
}
