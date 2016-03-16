-- Adminer 4.2.2 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `bank_accounts`;
CREATE TABLE `bank_accounts` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `code` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `bank_accounts` (`id`, `name`, `number`, `code`, `created`) VALUES
(1,	'Air Bank a.s.',	'1009567032',	'3030',	'2016-03-11 17:01:52');

DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ic` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dic` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `clients` (`id`, `name`, `street`, `city`, `zip`, `ic`, `dic`, `created`) VALUES
(1,	'Český svaz akrobatického rokenrolu',	'Atletická 100/2',	'Praha 6 – Strahov',	'16017',	'48547239',	NULL,	'2016-03-11 16:55:58'),
(2,	'Don’t Panic s.r.o.',	'Riegrovy sady 28',	'Praha 2',	'1200',	'27572099',	'CZ27572099',	'2016-03-11 16:56:55'),
(3,	'Viktorie servis, s.r.o.',	'Úmyslovice 59',	'Úmyslovice',	'29001',	'29143551',	NULL,	'2016-03-11 16:57:37'),
(4,	'LeMi CZ s.r.o.',	'Národní 31',	'Praha 1',	'11000',	NULL,	NULL,	'2016-03-11 16:58:44');

DROP TABLE IF EXISTS `contractors`;
CREATE TABLE `contractors` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ic` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dic` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payer_vat` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `contractors` (`id`, `name`, `street`, `city`, `zip`, `ic`, `dic`, `phone`, `email`, `payer_vat`, `created`) VALUES
(1,	'Zdeněk Vítek',	'Kurzova 2221/18',	'Praha 5',	'15500',	'73817406',	NULL,	'774995414',	'zvitek@iwory.cz',	0,	'2016-03-11 17:01:20');

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `number` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `issue_date` date DEFAULT NULL,
  `date_due` date DEFAULT NULL,
  `price` float DEFAULT NULL,
  `price_vat` float DEFAULT NULL,
  `paid` tinyint(1) NOT NULL DEFAULT '0',
  `pricing` tinyint(1) NOT NULL DEFAULT '0',
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `users_id` int(10) unsigned NOT NULL,
  `bank_accounts_id` tinyint(3) unsigned DEFAULT NULL,
  `clients_id` tinyint(3) unsigned DEFAULT NULL,
  `contractors_id` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`),
  KEY `bank_accounts_id` (`bank_accounts_id`),
  KEY `clients_id` (`clients_id`),
  KEY `contractors_id` (`contractors_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`bank_accounts_id`) REFERENCES `bank_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`clients_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_ibfk_4` FOREIGN KEY (`contractors_id`) REFERENCES `contractors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `invoices` (`id`, `token`, `number`, `issue_date`, `date_due`, `price`, `price_vat`, `paid`, `pricing`, `sent`, `created`, `users_id`, `bank_accounts_id`, `clients_id`, `contractors_id`) VALUES
(1,	'am4epb0zjeauveaanz52jao28irq0wzdkkgpfyge',	'20160302',	'2016-03-11',	'2016-03-12',	54000,	NULL,	0,	0,	0,	'2016-03-11 16:41:21',	1,	1,	3,	1);

DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoices_id` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `price_per_unit` double DEFAULT NULL,
  `units` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoices_id` (`invoices_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoices_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `invoice_items` (`id`, `invoices_id`, `name`, `description`, `price_per_unit`, `units`, `total`, `created`) VALUES
(5,	1,	'Test',	'Description 2\n\n',	54000,	1,	54000,	'2016-03-11 21:46:57');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `languages_id` smallint(5) unsigned DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surname` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_token` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_expiration` datetime DEFAULT NULL,
  `token` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `active` datetime DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `languages_id` (`languages_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`languages_id`) REFERENCES `languages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `users` (`id`, `languages_id`, `email`, `name`, `surname`, `password`, `password_token`, `password_expiration`, `token`, `active`, `created`) VALUES
(1,	NULL,	'zvitek@iwory.cz',	'Zdeněk',	'Vítek',	'$2y$10$kszIpACgkD30qh/21EvvdemR.uytq/Q3Anggx5BtoRqRcEcc4wnWO',	NULL,	NULL,	'797e20f4219de5f7c32d08trw696f262d7833224',	'2016-01-13 23:33:52',	'2016-01-12 23:35:33');

DROP TABLE IF EXISTS `user_has_role`;
CREATE TABLE `user_has_role` (
  `users_id` int(10) unsigned NOT NULL,
  `user_roles_id` tinyint(3) unsigned NOT NULL,
  KEY `users_id` (`users_id`),
  KEY `user_roles_id` (`user_roles_id`),
  CONSTRAINT `user_has_role_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_has_role_ibfk_2` FOREIGN KEY (`user_roles_id`) REFERENCES `user_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `user_has_role` (`users_id`, `user_roles_id`) VALUES
(1,	1);

DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `key_name` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `user_roles` (`id`, `key_name`, `name`) VALUES
(1,	'admin',	'Admin');

-- 2016-03-16 18:32:37