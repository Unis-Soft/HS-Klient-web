-- V183: samostatna konfigurace Google recenzi pro SMS.
-- Puvodni tabulka HodnoceniTextSMS se NEMENI.
CREATE TABLE IF NOT EXISTS `HodnoceniGoogleSMS` (
  `HodnoceniGoogleSMSID` INT NOT NULL AUTO_INCREMENT,
  `HodnoceniPobocka` INT NOT NULL,
  `HodnoceniStredisko` INT NOT NULL,
  `HodnoceniGoogleAktivni` TINYINT(1) NOT NULL DEFAULT 0,
  `HodnoceniGoogleURL` VARCHAR(2048) NOT NULL,
  `HodnoceniGoogleTextSMS` TEXT NOT NULL,
  PRIMARY KEY (`HodnoceniGoogleSMSID`),
  UNIQUE KEY `uq_HodnoceniGoogleSMS_pobocka_stredisko` (`HodnoceniPobocka`,`HodnoceniStredisko`)
) ENGINE=InnoDB;
