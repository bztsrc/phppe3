DROP TABLE IF EXISTS `email_queue`;
CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL auto_increment,
  `data` mediumtext NOT NULL default '',
  `created` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
);
