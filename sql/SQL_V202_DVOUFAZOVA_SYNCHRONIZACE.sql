-- HairSoft Klient V202
-- Izolovana fronta pro data, ktera musi pockat na realne lokalni HairSoft ID zakaznika.
-- Existujici tabulky klient_lidi / timeline / klient_lidi_obrazky / soubory se strukturou nemeni.

CREATE TABLE IF NOT EXISTS `k_klient_customer_sync_hold` (
  `customer_guid` VARCHAR(96) NOT NULL,
  `branch_id` INT NOT NULL DEFAULT 0,
  `status` VARCHAR(16) NOT NULL DEFAULT 'waiting',
  `last_hs_id` VARCHAR(32) NOT NULL DEFAULT '',
  `last_error` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `released_at` DATETIME NULL,
  PRIMARY KEY (`customer_guid`),
  KEY `idx_hs_sync_hold_status` (`status`,`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- V201 sloupec zustava soucasti reseni pro oddeleni historickeho data Timeline
-- od technickeho casu, kdy se zaznam uvolni pro HairSoft.
SET @hs_v202_timeline_col := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'timeline'
    AND COLUMN_NAME = 'timelineDoPCVlozeno'
);
SET @hs_v202_timeline_sql := IF(
  @hs_v202_timeline_col = 0,
  'ALTER TABLE `timeline` ADD COLUMN `timelineDoPCVlozeno` DATETIME NULL AFTER `timelineDoPCSynchro`',
  'SELECT 1'
);
PREPARE hs_v202_stmt FROM @hs_v202_timeline_sql;
EXECUTE hs_v202_stmt;
DEALLOCATE PREPARE hs_v202_stmt;
