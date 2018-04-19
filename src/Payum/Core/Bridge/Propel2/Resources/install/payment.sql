# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- payum_payment
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `payum_payment`;

CREATE TABLE `payum_payment`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `number` VARCHAR(255),
    `description` VARCHAR(255),
    `client_email` VARCHAR(255),
    `client_id` VARCHAR(255),
    `total_amount` INTEGER,
    `currency_code` VARCHAR(255),
    `currency_digits_after_decimal_point` INTEGER,
    `details` TEXT,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARACTER SET='utf8';

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
