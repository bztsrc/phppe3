<?php
/**
 * Controller for core deployment formatting
 */
namespace PHPPE\Ctrl;

class MinifyController {
    static $cli="minify";

	function __construct()
	{
		//! check if executed from CLI
		if(\PHPPE\Core::$client->ip!="CLI")
			die(L("Run from command line")."\n");

		//! convert source to deployment format
		\PHPPE\Repository::compress();
        //! update document
        \PHPPE\Repository::updateDoc();
		die;
	}

}
?>
