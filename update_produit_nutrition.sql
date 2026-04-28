-- Add nutrition columns to produit table
ALTER TABLE `produit`
  ADD COLUMN `calories`  INT(11) DEFAULT NULL AFTER `image`,
  ADD COLUMN `proteines` DECIMAL(5,1) DEFAULT NULL AFTER `calories`,
  ADD COLUMN `glucides`  DECIMAL(5,1) DEFAULT NULL AFTER `proteines`,
  ADD COLUMN `lipides`   DECIMAL(5,1) DEFAULT NULL AFTER `glucides`;
