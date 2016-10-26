<?php
/**
 * Controller for creating php files using templates
 * Similar to artisan make
 */
namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\Http;
use PHPPE\Templates;

class CreateController {
    static $cli="create <template> [arg1 [arg2...]]";

	function __construct()
	{
		//! check if executed from CLI
		if(Core::$client->ip!="CLI")
			Http::redirect("403");

		if(empty($_SERVER['argv'][2]))
			die(Templates::getUsage());

		//! create file from template
		Templates::create($_SERVER['argv'][2]);
	}

}
?>
