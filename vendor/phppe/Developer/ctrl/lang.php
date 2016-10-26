<?php
/**
 * Controller for creating php files using templates
 * Similar to artisan make
 */
namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\Http;
use PHPPE\Lang;

class LangController {
    static $cli="lang <extension> [language [--write]]";

	function __construct()
	{
		//! check if executed from CLI
		if(Core::$client->ip!="CLI")
			Http::redirect("403");

		if(empty($_SERVER['argv'][2]))
			die(Lang::getUsage());

		//! parse files for translateable strings
		Lang::parse($_SERVER['argv'][2], @$_SERVER['argv'][3], @$_SERVER['argv'][4]);
	}

}
?>
