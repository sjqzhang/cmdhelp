
CREATE TABLE `cmdhelp` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '���',
  `cmd` varchar(255) DEFAULT NULL COMMENT '����',
  `cmdinfo` text COMMENT '��������',
  `description` varchar(1024) DEFAULT NULL COMMENT '����',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8

