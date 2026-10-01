-- V194: posledni pracovni sekce pro kazdou zapamatovanou firmu.
ALTER TABLE `k_klient_remember_tokens`
  ADD COLUMN `last_page` VARCHAR(64) NOT NULL DEFAULT '' AFTER `last_branch_id`;
