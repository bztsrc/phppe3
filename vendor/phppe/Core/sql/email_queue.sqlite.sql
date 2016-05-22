DROP TABLE IF EXISTS `email_queue`;
CREATE TABLE `email_queue` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  `data` mediumtext NOT NULL default '',
  `created` int(11) NOT NULL
);
