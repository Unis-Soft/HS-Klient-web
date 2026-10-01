-- HairSoft Klient V195
-- Izolovany audit TEST kopie zakaznika mezi zapamatovanymi firmami.
-- Nemeni strukturu klient_lidi, timeline ani klient_lidi_obrazky.

CREATE TABLE IF NOT EXISTS `k_klient_customer_copy_test_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_guid` VARCHAR(96) NOT NULL,
  `target_guid` VARCHAR(96) NOT NULL,
  `source_owner_k_id` INT NOT NULL,
  `target_owner_k_id` INT NOT NULL,
  `source_branch_id` INT NOT NULL,
  `target_branch_id` INT NOT NULL,
  `target_company_label` VARCHAR(255) NOT NULL DEFAULT '',
  `target_branch_name` VARCHAR(255) NOT NULL DEFAULT '',
  `customer_name` VARCHAR(255) NOT NULL DEFAULT '',
  `timeline_count` INT NOT NULL DEFAULT 0,
  `photo_count` INT NOT NULL DEFAULT 0,
  `status` VARCHAR(32) NOT NULL DEFAULT 'created',
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_hs_copy_source` (`source_owner_k_id`,`source_guid`),
  KEY `idx_hs_copy_target` (`target_owner_k_id`,`target_guid`),
  KEY `idx_hs_copy_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
