DROP TABLE IF EXISTS `img_list`;
CREATE TABLE `pages_list` (
  `list_id` varchar(64) NOT NULL default '',
  `img_url` varchar(128) NOT NULL default '',
  `title` varchar(128) NOT NULL default '',
  `ordering` int(11) NOT NULL default 0,
  PRIMARY KEY  (`list_id`,`img_url`)
);
