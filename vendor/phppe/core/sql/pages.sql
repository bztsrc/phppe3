DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` varchar(255) NOT NULL default '',
  `lang` varchar(5) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `filter` varchar(255) NOT NULL default '',
  `template` varchar(64) NOT NULL default '',
  `data` mediumtext NOT NULL,
  `dds` mediumtext NOT NULL,
  `pubd` int(11) NOT NULL default 0,
  `expd` int(11) NOT NULL default 0,
  `created` int(11) NOT NULL default CURRENT_TIMESTAMP,
  `modifyd` int(11) NOT NULL default 0,
  `ownerid` int(11) NOT NULL default 0,
  PRIMARY KEY  (`id`,`lang`,`created`)
);
INSERT INTO `pages` VALUES ('frame','','','','frame','','{"menu":["id","pages"]}',0,0,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,-1);
INSERT INTO `pages` VALUES ('cmstest','','Test Page','','simple','{"body":"test","meta":{"generator":"PHPPECMS"}}','',0,0,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,-1);

DROP TABLE IF EXISTS `pages_list`;
CREATE TABLE `pages_list` (
  `id` varchar(64) NOT NULL default '',
  `parent_id` varchar(64) NOT NULL default '',
  `page_id` varchar(255) NOT NULL default '',
  `ordering` int(11) NOT NULL default 0,
  PRIMARY KEY  (`id`,`page_id`)
);
INSERT INTO `pages_list` VALUES ('frame','frame','frame',0);
