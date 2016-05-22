<?php
use PHPPE\Core as Core;

class Users extends PHPUnit_Framework_TestCase
{
	public function testUsers()
	{
		if(!class_exists("PHPPE\Users"))
			$this->markTestSkipped();

		$user = new \PHPPE\Users;

		$user->login("admin","changeme");
		$user->logout();
	}
}
?>
