DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` varchar(64) NOT NULL UNIQUE default '',
  `pass` varchar(80) NOT NULL default '',
  `acl` text NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `parentid` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL default '1',
  `created` int(11) NOT NULL,
  `logind` int(11) NOT NULL,
  `logoutd` int(11) NOT NULL,
  `prefs` mediumtext NOT NULL default ''
);
INSERT INTO users VALUES (1,'bzt','$2y$10$rrDFYORgliLsPQbl5slUu.gZdhl1LN6AsdRSDUiFgnizXPYEjYoTO','{"siteadm":1}','bzt@phppe.org',0,1,CURRENT_TIMESTAMP,0,0,'{"remote":{"host":"localhost","user":"bzt"},"homepage":"http://phppe.org"}');
