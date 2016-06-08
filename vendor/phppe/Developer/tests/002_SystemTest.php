<?php
use PHPPE\Core as Core;

class SystemTest extends PHPUnit_Framework_TestCase
{
	public function testEvent()
	{
		$this->assertFalse(\PHPPE\Core::lib("Developer")->eventTestRun,"Before event");
		\PHPPE\Core::event("eventTest");
		$this->assertTrue(\PHPPE\Core::lib("Developer")->eventTestRun,"After event");
	}

	public function testCore()
	{
        $this->assertTrue(\PHPPE\ClassMap::has("NotLoaded", "oneMethod"), "ClassMap has");
		@unlink(\PHPPE\ClassMap::$file);
		$_SERVER['REQUEST_URI']="";
		$_SERVER['argv'][1]="test";
		@$core = new \PHPPE\Core(true);
		$_SERVER['REQUEST_URI']="/test/something/?arg=1";
		@$core = new \PHPPE\Core(true);
        $this->assertFileExists(\PHPPE\ClassMap::$file, "New classmap");
		$this->assertNotEmpty($core->base,"Base");
		$this->assertNotEmpty($core->url,"Url");
		$this->assertEquals(\PHPPE\Core::$core->output,$core->output, "Output");

		$this->assertGreaterThan(\PHPPE\Core::$core->now,\PHPPE\Core::started(),"Started");
	}

	public function testLib()
	{
		$libs = Core::lib();
		$this->assertNotEmpty($libs["Developer"],"Autoloading");
		$this->assertEquals($libs["Developer"],Core::lib("Developer"),"Instance");

		Core::lib( "TestLib1", new \PHPPE\Extension());
		$libs2 = Core::lib();

		$this->assertGreaterThan(count($libs),count($libs2),"Loaded manually");
		$this->assertTrue(Core::isInst("TestLib1"),"isInst");
		
		Core::lib( "TestLib2", new \PHPPE\Client(), "TestLib1");
		$this->assertNotNull(Core::lib("TestLib2"),"Dependency");
		$this->assertNull(Core::lib("TestLib3"),"No extension");

		$this->assertInstanceOf("\\PHPPE\\Extension",Core::lib("TestLib1"),"Classof 1");
		$this->assertInstanceOf("\\PHPPE\\Client",Core::lib("TestLib2"),"Classof 2");

		$this->assertEquals("PHPPE\\Extension",Core::lib("TestLib1"),"Extension toString");

	}

	public function testClient()
	{
		$lang = $_SESSION['pe_l'];
		unset($_SESSION['pe_l']);

		$client = new \PHPPE\Client;
		$client->init([]);

		$this->assertNotNull($client->ip,"Client IP");
		$this->assertNotNull($client->agent,"Client Agent");
		$this->assertNotNull($client->lang,"Client Lang");

		\PHPPE\Core::$w=true;
		$_SERVER['REMOTE_ADDR']="::1";
		$_REQUEST['nojs']=true;
		$_REQUEST['lang']="en";
		unset($_SESSION['pe_l']);
		$client->init([]);
		$this->assertEquals("en",$client->lang,"Client Lang ow");
		$_SESSION['pe_l']=$lang;
	}

	public function testUserAccess()
	{
		$user = new \PHPPE\User;
		$this->assertEquals(0,$user->id,"User Id #1");
		$user->init([]);
		$this->assertEquals(\PHPPE\Core::$user->id,$user->id,"User Id #2");
		$u = $_SESSION['pe_u'];
		unset($_SESSION['pe_u']);
		$user->init([]);
		$user->id=1;
		$this->assertEquals(1,$user->id,"User Id #3");

		$this->assertFalse($user->has("aaaa"),"User ACE #1");
		$user->grant("aaaa");
		$this->assertTrue($user->has("aaaa"),"User ACE #2");
		$user->clear("aaaa");
		$this->assertFalse($user->has("aaaa"),"User ACE #3");
		$user->grant("aaaa");
		$user->grant("bbbb");
		$user->clear();
		$this->assertFalse($user->has("bbbb"),"User ACE #4");
		$user->grant("aaaa:1");
		$user->grant("aaaa:2");
		$this->assertTrue($user->has("aaaa:1"),"User ACE #5");
		$user->clear("aaaa");
		$this->assertFalse($user->has("aaaa"),"User ACE #6");

		$_SESSION['pe_u'] = $u;
	}
	
	public function testErrors()
	{
		$this->assertEmpty(\PHPPE\Core::error(),"Errors");
		$this->assertFalse(\PHPPE\Core::isError(),"IsError");

		\PHPPE\Core::error("message","obj.field");
		
		$this->assertNotEmpty(\PHPPE\Core::error(),"Errors #2");
		$this->assertTrue(\PHPPE\Core::isError(),"IsError #2");

	}

	public function testLog()
	{
		$s = \PHPPE\Core::$core->syslog;
		$t = \PHPPE\Core::$core->trace;
		\PHPPE\Core::$core->syslog = true;
		\PHPPE\Core::$core->trace = true;
		\PHPPE\Core::log("B","Should be Audit","phpunit");
		$o = \PHPPE\Core::$core->runlevel;
		\PHPPE\Core::$core->runlevel = 0;
		\PHPPE\Core::log("D","Should be skipped","phpunit");
		\PHPPE\Core::$core->runlevel = $o;
		\PHPPE\Core::$core->syslog = $s;
		\PHPPE\Core::$core->trace = $t;
	}

	public function testContent()
	{
		\PHPPE\Core::$core->nocache=true;
		
		include_once(__DIR__."/../libs/FailFilter.php");

		\PHPPE\DS::close();

		\PHPPE\DS::db("sqlite::memory:");
		\PHPPE\DS::exec("insert into pages (id,name,template,data,dds,filter) values ('test','Test','testview','{\"body\":\"testbody\"}','{\"testdds\":[\"1\",\"\",\"\"]}','fail');");
        \PHPPE\DS::exec("insert into views (id,ctrl) values ('testview', 'echo(\"OK\");');");

		$url = \PHPPE\Core::$core->url;
		$title = \PHPPE\Core::$core->title;
		$contentApp = new \PHPPE\Content;
		//! no content
		\PHPPE\Core::$core->title = "NONE";
		$contentApp = new \PHPPE\Content("no/such/content");
		$this->assertEquals("NONE",\PHPPE\Core::$core->title,"No content");

		//! filter
		$contentApp = new \PHPPE\Content("test/");
		$this->assertEquals("403",\PHPPE\Core::$core->template,"Filtered");
		\PHPPE\DS::exec("update pages set filter='' where id='test';");

		//! is content
		$contentApp = new \PHPPE\Content("test/");
		$this->assertEquals("Test",\PHPPE\Core::$core->title,"Content");

		$contentApp->getDDS($contentApp);
		$this->assertEquals("testbody",$contentApp->body,"Body");
		$this->assertNotEmpty($contentApp->testdds,"DDS");

		$old = \PHPPE\Core::$core->noctrl;
		\PHPPE\Core::$core->noctrl = false;
		$contentApp->ctrl="echo('OK');";
		$this->assertEquals("OK",$contentApp->action(),"Content controller #1");

		$old = \PHPPE\Core::$core->noctrl;
		\PHPPE\Core::$core->noctrl = true;
		$this->assertNull($contentApp->action(),"Content controller #2");
		\PHPPE\Core::$core->noctrl = $old;

		\PHPPE\DS::exec("update pages set dds='{\"testdds2\":[\"nosuchcolumn\",\"\",\"\"]}' where id='test';");
		$contentApp = new \PHPPE\Content("test/");
		$contentApp->getDDS($contentApp);
		$this->assertEmpty(@$contentApp->testdds2,"DDS failed");

		\PHPPE\Core::$core->title = $title;
	}

	public function testTools()
	{
		$this->assertTrue(mkdir("data/a/b/c/d/e/f",0775,true),"Creating directories #1");
		$this->assertTrue(is_dir("data/a/b/c/d/e"),"Creating directories #2");
		file_put_contents("data/a/b/c/e","aaa");
		\PHPPE\Tools::rmdir("data/a");
		$this->assertFalse(is_dir("data/a"),"Removing directories");
		
	}

}
?>
