<?php
/**
 * Controller for creating php files using templates
 * Similar to artisan make
 */
namespace PHPPE\Ctrl;

class LangController {
    static $cli="lang <extension> [language [--write]]";

	function __construct()
	{
		//! check if executed from CLI
		if(\PHPPE\Core::$client->ip!="CLI")
			die(L("Run from command line")."\n");

		if(empty($_SERVER['argv'][2]))
			die(\PHPPE\Lang::getUsage());

		//! parse files for translateable strings
		\PHPPE\Lang::parse($_SERVER['argv'][2], @$_SERVER['argv'][3], @$_SERVER['argv'][4]);
		die;
	}

}
?>
