<?php
use PHPPE\Core as Core;

//L("Users")
class UsersTest extends \PHPUnit\Framework\TestCase
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
		$this->assertNotNull($user->login("bzt","changeme"), "Already logged in");

		$user->logout();
	}
}
?>
