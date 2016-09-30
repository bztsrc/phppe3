<?php
use PHPPE\Core as Core;

class UsersTest extends PHPUnit_Framework_TestCase
{
	public function testUsers()
	{
		if(!\PHPPE\ClassMap::has("PHPPE\Users"))
			$this->markTestSkipped();

		// destroy session user
		$_SESSION['pe_u']=[];

		$user = new \PHPPE\Users;

		$this->assertNull($user->login("admin","changeme"), "Bad username or password");
		$this->assertNotNull($user->login("bzt","changeme"), "Login");
		$this->assertNull($user->login("bzt","changeme"), "Already logged in");

		$user->logout();
	}
}
?>
