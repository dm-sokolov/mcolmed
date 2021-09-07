---
--- Redirects
---
--- @version 1.3
--- @author Eugeny Panikarowsky - evgenii_panikaro@mail.ru
--- @copyright © 2016 Eugeny Panikarowsky
---

DROP TABLE IF EXISTS `hostdev_redirects`;
CREATE TABLE IF NOT EXISTS `hostdev_redirects` (
	`id` INT(10) NOT NULL AUTO_INCREMENT,
	`old_url` VARCHAR(255) NOT NULL,
	`type` TINYINT(4) NOT NULL,
	`new_url` VARCHAR(255) NOT NULL DEFAULT '',
	`active` TINYINT(1) NOT NULL DEFAULT '1',
	`deleted` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`append` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	`site_id` INT(11) NOT NULL,
	`informationsystem_id` INT(11) NULL DEFAULT '0',
	`informationsystem_item_id` INT(11) NULL DEFAULT '0',
	`informationsystem_group_id` INT(11) NULL DEFAULT '0',
	`referer` VARCHAR(255) NOT NULL DEFAULT '',
	`shop_id` INT(11) NULL DEFAULT '0',
	`shop_group_id` INT(11) NULL DEFAULT '0',
	`shop_item_id` INT(11) NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) COLLATE='utf8_general_ci' ENGINE=MyISAM AUTO_INCREMENT=3;
INSERT INTO `hostdev_redirects` (`old_url`, `type`, `new_url`, `active`, `deleted`, `site_id`, `informationsystem_id`, `informationsystem_item_id`, `informationsystem_group_id`, `shop_id`, `shop_group_id`, `shop_item_id`) VALUES
	('/old_url/', 0, '/vacancy/8/', 1, 0, 1, 1, 0, 0, 1, 0, 0),
	('/services/', 1, '1', 1, 0, 1, 1, 0, 0, 1, 0, 1);
