# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- payum_token
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `payum_token`;

CREATE TABLE `payum_token`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `hash` VARCHAR(255),
    `details` TEXT,
    `after_url` VARCHAR(255),
    `target_url` VARCHAR(255),
    `gateway_name` VARCHAR(255),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
