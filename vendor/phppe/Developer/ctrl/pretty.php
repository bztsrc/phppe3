<?php
/**
 * Controller for creating php files using templates
 * Similar to artisan make
 */
namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\Http;
use PHPPE\Pretty;

class PrettyController {
    static $cli="pretty <extension>";

	function action()
	{
		//! check if executed from CLI
		if(Core::$client->ip!="CLI")
			Http::redirect("403");

		if(empty($_SERVER['argv'][2]))
			die(Pretty::getUsage());

		//! format files
		Pretty::parse($_SERVER['argv'][2]);
	}

}
?>
