DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `pass` varchar(80) NOT NULL default '',
  `acl` text NOT NULL default '',
  `email` varchar(255) NOT NULL,
  `parentid` int(11) NOT NULL default 0,
  `active` tinyint(1) NOT NULL default 1,
  `created` int(11) NOT NULL default CURRENT_TIMESTAMP,
  `logind` int(11),
  `logoutd` int(11),
  `data` mediumtext NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `name` (`name`)
);
INSERT INTO users VALUES (0,'bzt','$2y$10$rrDFYORgliLsPQbl5slUu.gZdhl1LN6AsdRSDUiFgnizXPYEjYoTO','{"panel":1,"webadm":1}','',0,1,CURRENT_TIMESTAMP,0,0,'{"remote":{"host":"localhost","user":"bzt"}}');
