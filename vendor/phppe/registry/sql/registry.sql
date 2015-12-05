DROP TABLE IF EXISTS `registry`;
CREATE TABLE `registry` (
  `name` char(128) NOT NULL default '',
  `token` char(128) NOT NULL default '',
  `data` mediumtext NOT NULL default '',
  PRIMARY KEY (`name`,`token`)
);
