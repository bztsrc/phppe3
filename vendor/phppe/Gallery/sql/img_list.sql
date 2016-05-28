DROP TABLE IF EXISTS `img_list`;
CREATE TABLE `img_list` (
  `list_id` varchar(64) NOT NULL default '',
  `id` varchar(128) NOT NULL default '',
  `title` varchar(128) NOT NULL default '',
  `ordering` int(11) NOT NULL default 0,
  PRIMARY KEY  (`list_id`,`id`)
);
