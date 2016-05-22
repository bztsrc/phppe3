<?php
use PHPPE\Core as Core;

class Registry extends PHPUnit_Framework_TestCase
{
	public function testRegistry()
	{
		if(!class_exists("PHPPE\Registry"))
			$this->markTestSkipped();

		//registry in files
		\PHPPE\DS::close();

		\PHPPE\Registry::del("key");
		$this->assertEquals("default", \PHPPE\Registry::get("key","default"), "Registry default");
		$this->assertTrue(\PHPPE\Registry::set("key","value"),"Registry set");
		$this->assertEquals("value", \PHPPE\Registry::get("key","default"), "Registry get");

		//registry in database
		$ds = new \PHPPE\DS("sqlite::memory:");
		\PHPPE\Registry::del("key");
		$this->assertEquals("default", \PHPPE\Registry::get("key","default"), "Registry db default");
		$this->assertTrue(\PHPPE\Registry::set("key","value"),"Registry db set");
		$this->assertEquals("value", \PHPPE\Registry::get("key","default"), "Registry db get");

	}
}
?>
