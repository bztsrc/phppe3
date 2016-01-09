<?php
use PHPPE\Core as PHPPE;

// cache test

//check if it's configured and initialized
echo("SalesForce installed: ");
if( !PHPPE::isInst("SF") ) {
	echo("not installed!\n");
	return "SKIP";
} else echo("OK\n");


//everything was ok
return true;
?>