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
			\PHPPE\Http::redirect("403");

		//! convert source to deployment format
		\PHPPE\Repository::compress();
        //! update document
        \PHPPE\Repository::updateDoc();
		die;
	}

}
?>
