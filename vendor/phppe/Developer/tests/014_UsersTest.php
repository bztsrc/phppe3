<?php
use PHPPE\Core as Core;

class UsersTest extends PHPUnit_Framework_TestCase
{
	public function testUsers()
	{
		if(!\PHPPE\ClassMap::has("PHPPE\Users"))
			$this->markTestSkipped();

		$user = new \PHPPE\Users;

		$user->login("admin","changeme");
		$user->logout();
	}
}
?>
