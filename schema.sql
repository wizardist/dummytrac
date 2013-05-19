SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `history` (
  `history_ticket` int(11) NOT NULL,
  `history_date` binary(14) NOT NULL,
  `history_old` tinyint(4) NOT NULL,
  `history_new` tinyint(4) NOT NULL,
  KEY `history_ticket` (`history_ticket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE IF NOT EXISTS `ticket` (
  `ticket_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_status` tinyint(4) NOT NULL,
  `ticket_updated` binary(14) NOT NULL,
  `ticket_title` varbinary(255) NOT NULL,
  `ticket_description` blob NOT NULL,
  PRIMARY KEY (`ticket_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=5 ;
