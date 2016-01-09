<?php
use PHPPE\Core as PHPPE;

// library test
$libs = PHPPE::lib();

//check if it's empty
echo("Library autoloading: ");
if( empty($libs["Developer"]) || $libs["Developer"] != PHPPE::lib("Developer") ) {
	echo("Failed, should not happen!\n");
	return false;
} else echo("OK\n");

//add new libraries
PHPPE::lib( "TestLib1", "Test Lib");
PHPPE::lib( "TestLib2", "Test Lib 2", "TestLib1", new \PHPPE\App());

//checks
echo("Library load: ");
if( !PHPPE::isinst("TestLib1") ) {
	echo("isinst() reported failure!\n");
	return false;
} else
if( empty(PHPPE::lib("TestLib2")) ) {
	echo("Failed dependency!\n");
	return false;
} else
if( get_class(PHPPE::lib("TestLib1")) != "stdClass" ) {
	echo("Failed to load class without instance!\n");
	return false;
} else
if( get_class(PHPPE::lib("TestLib2")) != "PHPPE\App" ) {
	echo("Failed to load instanciated class!\n");
	return false;
} else echo("OK\n");

// addons test
$addons_installed = PHPPE::addon();
$addons_loaded = PHPPE::getval("core.addons");

//check if it's empty
echo("Addons installation check: ");
if( !empty($addons_loaded["test1"]) ) {
	echo("also loaded!\n");
	return false;
} else
if( empty($addons_installed["test1"]) ) {
	echo("addon() failed!\n");
	return false;
} else
if( !PHPPE::isinst("test1") ) {
	echo("isinst() failed!\n");
	return false;
} else echo("OK\n");

echo("Addons init: ");
if( !empty($addons_installed['test1']->conf) ) {
	echo("defaults failed!\n");
	return false;
} else
if( empty($addons_installed['test2']->conf) ) {
	echo("failed to call init()!\n");
	return false;
} else echo("OK\n");

PHPPE::_t("<!form test><!field test2 test.field></form>");

echo("Addons load: ");
if( !empty($addons_loaded["test2"]) || empty(PHPPE::getval("core.addons")["test2"]) ) {
	echo("templater failed!\n");
	return false;
} else echo("OK\n");

// error reporting test
echo("Error reporting: ");
//FIXME PHPPE::e(), PHPPE::error(), PHPPPE::isError()
echo("OK\n");

//! Extension test, only if installed
echo("Registry get: ");
\PHPPE\Registry::del('tests_aaa');
if(PHPPE::isInst("Registry")) {
	if( !empty(\PHPPE\Registry::get('tests_aaa'))) {
		echo("failed!\n");
		return false;
	} else echo("OK\n");

	echo("Registry set: ");
	\PHPPE\Registry::set('tests_aaa',1);
	if( \PHPPE\Registry::get('tests_aaa')!=1) {
		echo("failed!\n");
		return false;
	} else echo("OK\n");
	\PHPPE\Registry::del('tests_aaa');

} else echo("SKIP\n");

//everything was ok
return true;
?>