-- V191: vice soucasne prihlasenych firem + dlouhodobe prihlaseni na jednom zarizeni.
-- Hesla se zde NEUKLADAJI. validator_hash je SHA-256 hash nahodneho tokenu z HttpOnly cookie.
CREATE TABLE IF NOT EXISTS `k_klient_remember_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `selector` CHAR(24) NOT NULL,
  `validator_hash` CHAR(64) NOT NULL,
  `account_type` VARCHAR(16) NOT NULL,
  `principal_id` INT NOT NULL,
  `owner_k_id` INT NOT NULL,
  `soft` VARCHAR(32) NOT NULL DEFAULT 'HairSoft',
  `credential_fingerprint` CHAR(64) NOT NULL,
  `last_branch_id` INT NOT NULL DEFAULT 0,
  `last_page` VARCHAR(64) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL,
  `last_used_at` DATETIME NULL,
  `expires_at` DATETIME NOT NULL,
  `revoked_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_k_klient_remember_selector` (`selector`),
  KEY `idx_k_klient_remember_owner` (`owner_k_id`),
  KEY `idx_k_klient_remember_expiry` (`expires_at`)
) ENGINE=InnoDB;
