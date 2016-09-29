<?php
/**
 * Controller for repository maker
 */
namespace PHPPE\Ctrl;

class MkRepoController {
    static $cli="mkrepo [--tests]";

	function __construct()
	{
		//! check if executed from CLI
		if(\PHPPE\Core::$client->ip!="CLI")
			\PHPPE\Http::redirect("403");

		//! run tests
		if(in_array("--tests",$_SERVER['argv']))
		{
			echo("Running tests: ");
			ob_start();
			$tests = \PHPPE\Testing::doTests();
			$d = ob_get_clean();
			if(!$tests)
				die("FAILED\n$d");
			echo("OK\n");
			unset($d);
		}

        //! *** MKREPO Event ***
        \PHPPE\Core::event("mkrepo");

		//! create repository
		\PHPPE\Repository::make();
		die;
	}

}
?>
