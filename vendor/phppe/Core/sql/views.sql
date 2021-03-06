DROP TABLE IF EXISTS `views`;
CREATE TABLE `views` (
  `id` char(128) NOT NULL default '',
  `name` char(128) NOT NULL default '',
  `data` mediumtext NOT NULL default '',
  `css` mediumtext NOT NULL default '',
  `jslib` mediumtext NOT NULL default '',
  `ctrl` mediumtext NOT NULL default '',
  `sitebuild` char(128) NOT NULL default '',
  `created` int(11) NOT NULL default CURRENT_TIMESTAMP,
  `modifyd` int(11) NOT NULL default 0,
  `modifyid` int(11) NOT NULL default 0,
  PRIMARY KEY  (`id`)
);
INSERT INTO `views` VALUES ('frame','Default','<div id="content"><!app></div>','','','','default',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,-1);
INSERT INTO `views` VALUES ('simple','Simple','<div class="container-fluid"><h1><!cms *text app.title></h1><!cms *wyswyg app.body></div>','','','','',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,-1);
