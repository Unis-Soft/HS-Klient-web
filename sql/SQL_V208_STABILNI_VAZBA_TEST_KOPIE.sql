-- HairSoft Klient V208
-- Aplikace provede migraci automaticky. Tento SQL je pouze servisní varianta.
ALTER TABLE `k_klient_customer_copy_test_log`
  ADD COLUMN `target_customer_id` BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER `target_guid`;

ALTER TABLE `k_klient_customer_copy_test_log`
  ADD KEY `idx_hs_copy_target_customer` (`target_customer_id`);
