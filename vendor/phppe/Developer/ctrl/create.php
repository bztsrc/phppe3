<?php
/**
 * Controller for creating php files using templates
 * Similar to artisan make
 */
namespace PHPPE\Ctrl;

class CreateController {
    static $cli="create <template> [arg1 [arg2...]]";

	function __construct()
	{
		//! check if executed from CLI
		if(\PHPPE\Core::$client->ip!="CLI")
			\PHPPE\Http::redirect("403");

		if(empty($_SERVER['argv'][2]))
			die(\PHPPE\Templates::getUsage());

		//! create file from template
		\PHPPE\Templates::create($_SERVER['argv'][2]);
		die;
	}

}
?>
