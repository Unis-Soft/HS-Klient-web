-- HairSoft Klient V201
-- Technicke datum vytvoreni odchoziho Timeline zaznamu.
-- timelineDatumCas zustava puvodni historicke datum, ktere se zobrazuje uzivateli.
-- Aplikace V201 si tento sloupec umi pri prvni TEST kopii doplnit sama.
-- Tento skript je pouze rucni/servisni varianta.

SET @hs_v201_timeline_col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'timeline'
      AND COLUMN_NAME = 'timelineDoPCVlozeno'
);

SET @hs_v201_timeline_sql := IF(
    @hs_v201_timeline_col_exists = 0,
    'ALTER TABLE `timeline` ADD COLUMN `timelineDoPCVlozeno` DATETIME NULL AFTER `timelineDoPCSynchro`',
    'SELECT 1'
);

PREPARE hs_v201_stmt FROM @hs_v201_timeline_sql;
EXECUTE hs_v201_stmt;
DEALLOCATE PREPARE hs_v201_stmt;
