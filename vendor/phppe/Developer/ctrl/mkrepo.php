<?php
/**
 * Controller for repository maker
 */
namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\Http;
use PHPPE\Testing;
use PHPPE\Repository;

class MkRepoController {
    static $cli="mkrepo [--tests]";

	function __construct()
	{
		//! check if executed from CLI
		if(Core::$client->ip!="CLI")
			Http::redirect("403");

		//! run tests
		if(in_array("--tests",$_SERVER['argv']))
		{
			echo("Running tests: ");
			ob_start();
			$tests = Testing::doTests();
			$d = ob_get_clean();
			if(!$tests)
				die("FAILED\n$d");
			echo("OK\n");
			unset($d);
		}

        //! *** MKREPO Event ***
        Core::event("mkrepo");

		//! create repository
		Repository::make();
		die;
	}

}
?>
