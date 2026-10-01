-- HairSoft Klient V207 - izolovany mezisklad Timeline TEST kopie
CREATE TABLE IF NOT EXISTS `k_klient_customer_copy_timeline_stage` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `copy_log_id` BIGINT UNSIGNED NOT NULL,
  `source_timeline_id` BIGINT NOT NULL DEFAULT 0,
  `target_guid` VARCHAR(96) NOT NULL,
  `target_branch_id` INT NOT NULL DEFAULT 0,
  `timeline_datum_cas` DATETIME NULL,
  `timeline_text` TEXT NOT NULL,
  `timeline_obsluha` VARCHAR(255) NOT NULL DEFAULT '',
  `status` VARCHAR(16) NOT NULL DEFAULT 'waiting',
  `target_timeline_id` BIGINT NULL,
  `created_at` DATETIME NOT NULL,
  `prepared_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hs_copy_timeline_source` (`copy_log_id`,`source_timeline_id`),
  KEY `idx_hs_copy_timeline_target` (`target_guid`,`status`),
  KEY `idx_hs_copy_timeline_log` (`copy_log_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
