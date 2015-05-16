
CREATE TABLE `cmdhelp` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ฑเบล',
  `cmd` varchar(255) DEFAULT NULL COMMENT 'รüม๎',
  `cmdinfo` text COMMENT 'รüม๎ฯ๊ว้',
  `description` varchar(1024) DEFAULT NULL COMMENT 'ร่ส๖',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8

