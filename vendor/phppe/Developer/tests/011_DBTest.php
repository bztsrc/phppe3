<?php
use PHPPE\Core as Core;

class DB extends PHPUnit_Framework_TestCase
{
	public function testDB()
	{
		if(!class_exists("PHPPE\DB"))
			$this->markTestSkipped();

		$this->assertEquals(
			"SELECT * FROM users",
			\PHPPE\DB::select("users"),
			"Simple select");

		$this->assertEquals(
			"SELECT id,name FROM users",
			\PHPPE\DB::select("users")->fields(["id","name"]),
			"Select with fields");
	}
}
?>
