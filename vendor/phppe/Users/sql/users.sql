DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `pass` varchar(80) NOT NULL default '',
  `acl` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `parentid` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL default '1',
  `created` int(11) NOT NULL,
  `logind` int(11) NOT NULL,
  `logoutd` int(11) NOT NULL,
  `prefs` mediumtext NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `name` (`name`)
);
INSERT INTO users VALUES (0,'bzt','$2y$10$rrDFYORgliLsPQbl5slUu.gZdhl1LN6AsdRSDUiFgnizXPYEjYoTO','{"siteadm":1}','bzt@phppe.org',0,1,CURRENT_TIMESTAMP,0,0,'{"remote":{"host":"localhost","user":"bzt"},"homepage":"http://phppe.org"}');
