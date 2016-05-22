<?php
/**
 * Controller for creating php files using templates
 * Similar to artisan make
 */
namespace PHPPE\Ctrl;

class CreateController {

	function __construct()
	{
		//! check if executed from CLI
		if(\PHPPE\Core::$client->ip!="CLI")
			die(L("Run from command line")."\n");

		if(empty($_SERVER['argv'][2]))
			die(\Templates::getUsage());

		//! create file from template
		\Templates::create($_SERVER['argv'][2]);
		die;
	}

}
?>
