DROP TABLE IF EXISTS `views`;
CREATE TABLE `views` (
  `id` char(128) NOT NULL default '',
  `name` char(128) NOT NULL default '',
  `data` mediumtext NOT NULL default '',
  `css` mediumtext NOT NULL default '',
  `jslib` mediumtext NOT NULL default '',
  `meta` mediumtext NOT NULL default '',
  `sitebuild` char(128) NOT NULL default '',
  `created` int(11) NOT NULL default CURRENT_TIMESTAMP,
  `modifyd` int(11) NOT NULL default 0,
  PRIMARY KEY  (`id`)
);
INSERT INTO `views` VALUES ('frame','Default','<div id="header"><!cms wysiwyg frame.header></div><br><div id="menu"><!cms pagelist mainmenu><ul><!foreach mainmenu><li><!=name></li><!/foreach></ul></div><br><div id="content"><!app></div>','','','','default',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP);
INSERT INTO `views` VALUES ('simple','Simple','<div class="mosaicbox"><!cms wysiwyg app.body></div>','','','','',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP);
