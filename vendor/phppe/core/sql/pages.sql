DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` varchar(128) NOT NULL default '',
  `lang` varchar(5) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `filter` varchar(255) NOT NULL default '',
  `template` varchar(64) NOT NULL default '',
  `data` mediumtext NOT NULL,
  `dds` mediumtext NOT NULL,
  `ctrl` mediumtext NOT NULL,
  `pubd` int(11) NOT NULL default 0,
  `expd` int(11) NOT NULL default 0,
  `created` int(11) NOT NULL default CURRENT_TIMESTAMP,
  `modifyd` int(11) NOT NULL default CURRENT_TIMESTAMP,
  `lockd` int(11) NOT NULL default 0,
  `ownerid` int(11) NOT NULL default 0,
  `modifyid` int(11) NOT NULL default 0,
  PRIMARY KEY  (`id`,`lang`,`created`)
);
INSERT INTO `pages` VALUES ('frame','','','','frame','','{"mainmenu":["b.*", "pages_list a left join pages b on a.page_id=b.id and b.created=(SELECT MAX(c.created) FROM pages c WHERE c.id=b.id)", "a.list_id=''@ID''", "", "ordering"]}','',0,0,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,0,0,-1);

DROP TABLE IF EXISTS `pages_list`;
CREATE TABLE `pages_list` (
  `list_id` varchar(64) NOT NULL default '',
  `page_id` varchar(128) NOT NULL default '',
  `ordering` int(11) NOT NULL default 0,
  PRIMARY KEY  (`list_id`,`page_id`)
);


INSERT INTO `pages` VALUES ('cmstest','','Test Page','','simple','{"body":"test2","meta":{}}','','',0,0,'2016-01-01 00:00:00',CURRENT_TIMESTAMP,0,0,-1);
INSERT INTO `pages` VALUES ('cmstest','','Test Page','','simple','{"body":"test","meta":{}}','','',0,0,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,0,0,-1);
INSERT INTO `pages` VALUES ('cmstest/2','hu','Test Page #2','','simple','{"body":"test2","meta":{}}','','',0,0,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,0,0,-1);
INSERT INTO `pages_list` VALUES ('mainmenu','cmstest',0);
INSERT INTO `pages_list` VALUES ('mainmenu','cmstest/2',1);
