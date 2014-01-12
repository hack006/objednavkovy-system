-- phpMyAdmin SQL Dump
-- version 3.5.8.1deb1
-- http://www.phpmyadmin.net

-- DULEZITE:
-- 		Nasledujici skript vytvori strukturu DB a vytvori administratorsky ucet.
--		Uzivatele: 
--			admin: heslo "admin123"

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `objednavkovy-system`
--
-- --------------------------------------------------------
--
-- Table structure for table `actions`
--

CREATE TABLE IF NOT EXISTS `actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `description` text CHARACTER SET utf16 COLLATE utf16_czech_ci NOT NULL,
  `visible_from` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `visible_until` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `order_from` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `order_until` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `actions_products`
--

CREATE TABLE IF NOT EXISTS `actions_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `action_id_2` (`action_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `action_id` (`action_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `fetch_hours`
--

CREATE TABLE IF NOT EXISTS `fetch_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time_from` time NOT NULL,
  `time_until` time NOT NULL,
  `date` date NOT NULL,
  `action_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `action_id` (`action_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fetch_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `note` text COLLATE utf8_czech_ci NOT NULL,
  `status` enum('accepted','canceled','done','new','pickedup') COLLATE utf8_czech_ci NOT NULL DEFAULT 'new',
  `prepared_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `picked_up_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action_id` (`action_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `order_fields`
--

CREATE TABLE IF NOT EXISTS `order_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Neni zabraneno zmene nazvu produktu, na ktery se odkazujeme, proto radeji z bezpecnostniho duvodu ukladame duplicitne nazev do db. Bylo by nevhodne po zmene nevedet nazev produktu v objednavce. Vedome poruseni zasad navrhu DB!',
  `count` mediumint(9) NOT NULL,
  `price_without_tax` int(11) NOT NULL,
  `vat` decimal(5,2) NOT NULL COMMENT 'Dan jako cislo z duvodu vetsi bezpecnosti uchovani spravne hodnoty. Vedome poruseni zasady navrhu DB.',
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL,
  `price_without_tax` float NOT NULL,
  `min_order_time_hours` tinyint(3) unsigned DEFAULT NULL,
  `vat_id` int(10) unsigned NOT NULL,
  `min_order_time_days` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vat_id` (`vat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `lastname` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `username` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `phone` varchar(16) COLLATE utf8_czech_ci NOT NULL,
  `city` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `role` enum('admin','customer','','') COLLATE utf8_czech_ci NOT NULL DEFAULT 'customer',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

--
-- Insert administrator acount
--

INSERT INTO `users` (`firstname`, `lastname`, `username`, `password`, `email`, `phone`, `city`, `role`) VALUES
('Administrátor', 'xx', 'admin', '$2a$07$b40vutjrl4acwvistuanxuTXsMxCPtn/wTPv6nmsNJi0UMChCaWZC', 'admin@local.host', '123456789', 'xx', 'admin') ;

-- --------------------------------------------------------

--
-- Table structure for table `vats`
--

CREATE TABLE IF NOT EXISTS `vats` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  `value` decimal(5,2) DEFAULT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=3 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `actions_products`
--
ALTER TABLE `actions_products`
  ADD CONSTRAINT `actions_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `actions_products_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fetch_hours`
--
ALTER TABLE `fetch_hours`
  ADD CONSTRAINT `fetch_hours_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_fields`
--
ALTER TABLE `order_fields`
  ADD CONSTRAINT `order_fields_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_fields_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`vat_id`) REFERENCES `vats` (`id`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
