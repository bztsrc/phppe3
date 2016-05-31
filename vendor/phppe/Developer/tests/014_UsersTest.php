<?php
use PHPPE\Core as Core;

class UsersTest extends PHPUnit_Framework_TestCase
{
	public function testUsers()
	{
		if(!\PHPPE\ClassMap::has("PHPPE\Users"))
			$this->markTestSkipped();

		$user = new \PHPPE\Users;

		$this->assertFalse($user->login("admin","changeme"), "Bad username or password");
		$this->assertTrue($user->login("bzt","changeme"), "Login");

		$user->logout();
	}
}
?>
