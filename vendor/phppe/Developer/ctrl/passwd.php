<?php
/**
 * Controller for creating password hash
 */
namespace PHPPE\Ctrl;

class PasswdController {
    static $cli="passwd [password]";

	function __construct()
	{
		//! check if executed from CLI
		if(\PHPPE\Core::$client->ip!="CLI")
			\PHPPE\Http::redirect("403");

		if(!empty($_SERVER['argv'][2]))
			$passwd = $_SERVER['argv'][2];
        else {
            echo(chr(27)."[96mPassword? ".chr(27)."[0m");
            system('stty -echo');
            $passwd = rtrim(fgets(STDIN));
            system('stty echo');
            echo("\n");
        }

		die(password_hash($passwd, PASSWORD_BCRYPT, ['cost'=>12])."\n");
	}

}
?>
