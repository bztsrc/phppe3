<?php
use PHPPE\Core as PHPPE;

// cache test

//check if it's configured and initialized
echo("Extensions installed: ");
if( !PHPPE::isInst("Extensions") && !PHPPE::isInst("ExtensionsPro") ) {
	echo("not installed!\n");
	return "SKIP";
} else echo("OK\n");


//everything was ok
return true;
?>