<?php
/**
 * Controller for core deployment formatting
 */
namespace PHPPE\Ctrl;

include_once(__DIR__."/../libs/Repository.php");

class Developer {

	function __construct()
	{
		//! check if executed from CLI
		if(\PHPPE\Core::$client->ip!="CLI")
			die(L("Run from command line")."\n");

		//! convert source to deployment format
		\Repository::compress();
		die;
	}

}
?>
