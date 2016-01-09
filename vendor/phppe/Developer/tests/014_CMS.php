<?php
use PHPPE\Core as PHPPE;

// cache test

//check if it's configured and initialized
echo("CMS installed: ");
if( !PHPPE::isInst("CMS") && !PHPPE::isInst("CMSPro") ) {
	echo("not installed!\n");
	return "SKIP";
} else echo("OK\n");


//everything was ok
return true;
?>