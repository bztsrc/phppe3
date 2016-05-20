<?php
use PHPPE\Core as Core;

class Email extends PHPUnit_Framework_TestCase
{
	public function testEmail()
	{
		if(!class_exists("PHPPE\Email"))
			$this->markTestSkipped();
	}
}
?>
