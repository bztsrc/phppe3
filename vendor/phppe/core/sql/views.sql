DROP TABLE IF EXISTS `views`;
CREATE TABLE `views` (
  `id` char(128) NOT NULL default '',
  `name` char(128) NOT NULL default '',
  `data` mediumtext NOT NULL default '',
  `css` mediumtext NOT NULL default '',
  `jslib` mediumtext NOT NULL default '',
  `meta` mediumtext NOT NULL default '',
  `created` int(11) NOT NULL default CURRENT_TIMESTAMP,
  `modifyd` int(11) NOT NULL default 0,
  PRIMARY KEY  (`id`)
);
INSERT INTO `views` VALUES ('simple','','<div id="cmscontent"><!=app.body></div>','','','{"generator":"PHPPECMS"}',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP);
