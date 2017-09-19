<?php
return [
	"show columns from (.*)" => "pragma table_info(\\1)",
	"show tables" => "select name from sqlite_master where type='table' order by name",
	"truncate table" => "drop table"
];

