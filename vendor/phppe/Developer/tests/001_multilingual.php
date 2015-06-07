<?php
use PHPPE\Core as PHPPE;

// Multilanguage test
$cnt = count(PHPPE::$l);

//check if it's empty
if(file_exists("vendor/phppe/core/lang/en.php")){
	echo("Translations: ");
	if( $cnt < 1 ) {
		echo("Empty dictionary?\n");
		return false;
	} else echo("OK\n");
}
//try to load fake language for Developer
PHPPE::$client->lang = "xx";
LANG_INIT();

//check if there's a new entry
echo("Loading new: ");
if( L('teststr') == 'teststr' || $cnt == count(PHPPE::$l) ) {
	echo("Failed to load extension dictionary\n");
	return false;
} else echo("OK\n");

//everything was ok
return true;
?>