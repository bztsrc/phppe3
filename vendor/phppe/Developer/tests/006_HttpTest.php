<?php
class HttpTest extends PHPUnit_Framework_TestCase
{
	public function testHelpers()
	{
		$this->assertEquals(url(),\PHPPE\Http::url(),"url wrapper");
		$this->assertEquals("a",basename(url("a")),"url app");
		$this->assertEquals("a",basename(dirname(url("a","b"))),"url action #1");
		$this->assertEquals("b",basename(url("a","b")),"url action #2");

		$_SESSION['pe_r']="";
		\PHPPE\Http::_r();
		$this->assertNotEmpty($_SESSION['pe_r'],"redirect save");

		@\PHPPE\Http::mime("text/plain",true);
		@\PHPPE\Http::mime("text/plain",false);

	}

	public function testGet()
	{
		$this->assertEquals(
			'OK',
			trim(\PHPPE\Http::get(url("tests","http"))),
			"http get");
		$this->assertEquals(
			'{"var1":"test1","var2":"test2"}',
			\PHPPE\Http::get(url("tests","http")."post",["var1"=>"test1","var2"=>"test2"]),
			"http post");
		$this->assertEquals(
			'Redirected',
			trim(\PHPPE\Http::get(url("tests","http")."redirect")),
			"http redirect");
		$this->assertEquals(
			'OK',
			trim(\PHPPE\Http::get(url("tests","http")."cookie")),
			"http cookie change");
		\PHPPE\Core::$client->lang="xx";
		$this->assertEquals(
			'xx;q=0.8',
			trim(\PHPPE\Http::get(url("tests","http")."language")),
			"http language");
		$this->assertEquals(
			'CR',
			trim(\PHPPE\Http::get(url("tests","http")."cr1")),
			"http cr #1");
		$this->assertEquals(
			"C\rR",
			trim(\PHPPE\Http::get(url("tests","http")."cr2")),
			"http cr #2");
		$this->AssertEmpty(trim(\PHPPE\Http::get("badurl")),"http bad url #1");
		$this->AssertEmpty(trim(\PHPPE\Http::get("badurl://localhost")),"http bad url #2");
		$this->AssertEmpty(trim(\PHPPE\Http::get(url("tests","http"),"",3,8)),"http recursion");
		$this->AssertEmpty(trim(\PHPPE\Http::get(url("tests","http")."timeout")),"http timeout");
	}
}
?>
