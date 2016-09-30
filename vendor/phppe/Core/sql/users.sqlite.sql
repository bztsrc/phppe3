DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `name` varchar(64) NOT NULL UNIQUE default '',
  `pass` varchar(80) NOT NULL default '',
  `acl` text NOT NULL default '',
  `email` varchar(255) NOT NULL UNIQUE,
  `parentid` int(11) NOT NULL default 0,
  `active` tinyint(1) NOT NULL default 1,
  `created` int(11) NOT NULL default CURRENT_TIMESTAMP,
  `logind` int(11),
  `logoutd` int(11),
  `data` mediumtext NOT NULL default ''
);
INSERT INTO users VALUES (1,'bzt','$2y$10$rrDFYORgliLsPQbl5slUu.gZdhl1LN6AsdRSDUiFgnizXPYEjYoTO','{"panel":1,"webadm":1}','',0,1,CURRENT_TIMESTAMP,0,0,'{"remote":{"host":"localhost","user":"bzt"}}');
