DROP TABLE IF EXISTS `cluster`;
CREATE TABLE `cluster` (
	`id` VARCHAR(96) NOT NULL DEFAULT '127.0.0.1',
	`name` VARCHAR(64) NOT NULL DEFAULT '',
	`type` VARCHAR(16) NOT NULL DEFAULT 'worker',
	`cmd` VARCHAR(64) NOT NULL DEFAULT '',
	`load` INTEGER(3,2) NOT NULL DEFAULT 0.0,
	`created` int(11) NOT NULL default CURRENT_TIMESTAMP,
	`modifyd` int(11),
	`syncd` int(11),
	`viewd` int(11),
	`console` TEXT,
	PRIMARY KEY (`id`)
);
