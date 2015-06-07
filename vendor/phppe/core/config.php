<?php
//! primary datasource
$core->db = "sqlite:data/sqlite.db";
//$core->db = "mysql:host=localhost;dbname=test@user:pass";
//$core->db = "mysql:host=localhost;dbname=test";

//! set up caching
//$core->cache="localhost:11211";
//$core->cache="unix:/tmp/memcache_sock";
//$core->cache="apc";
//$core->cache="files";
//$core->nominify = true;

//! set up master password
//$core->masterpasswd = password_hash( "changeme", PASSWORD_BCRYPT );
$core->masterpasswd = '$2y$10$rrDFYORgliLsPQbl5slUu.gZdhl1LN6AsdRSDUiFgnizXPYEjYoTO';

//! run level
//!  0 - production
//!  1 - testing
//!  2 - developer
//!  3 - debug
$core->runlevel=2;

//! set up list of allowed functions in templater
//$core->allowed = array( "number_format", "sprintf" );

//!turn on maintenance mode
//$core->maintenance = true;
?>
