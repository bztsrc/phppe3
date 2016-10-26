<?php
/**
 * Controller for core deployment formatting
 */
namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\Http;
use PHPPE\Repository;

class MinifyController {
    static $cli="minify";

	function __construct()
	{
		//! check if executed from CLI
		if(Core::$client->ip!="CLI")
			Http::redirect("403");

		//! convert source to deployment format
		Repository::compress();
        //! update document
        Repository::updateDoc();
	}

}
?>
