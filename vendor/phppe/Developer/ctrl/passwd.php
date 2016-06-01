<?php
/**
 * Controller for creating password hash
 */
namespace PHPPE\Ctrl;

class PasswdController {

	function __construct()
	{
		//! check if executed from CLI
		if(\PHPPE\Core::$client->ip!="CLI")
			die(L("Run from command line")."\n");

		if(!empty($_SERVER['argv'][2]))
			$passwd = $_SERVER['argv'][2];
        else {
            echo("Password? ");
            system('stty -echo');
            $passwd = rtrim(fgets(STDIN));
            system('stty echo');
            echo("\n");
        }

		die(password_hash($passwd, PASSWORD_BCRYPT, ['cost'=>12])."\n");
	}

}
?>
