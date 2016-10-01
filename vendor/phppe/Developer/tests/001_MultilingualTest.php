<?php
use PHPPE\Core as Core;

//L("Multilingual")

class MultilingualTest extends PHPUnit_Framework_TestCase
{
	public function testMultilingual()
	{
		$cnt = count(Core::$l);
		
		$this->assertNotEmpty(Core::$l,"Translations");
 
		//try to load fake language for Developer
		Core::$client->lang = "xx";
		Core::lang("Developer");

		//check if there's a new entry
		$this->assertNotEquals('teststr',L('teststr'),"Loading new");
	}
}
?>
