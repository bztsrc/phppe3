<?php
use PHPPE\Core;
use PHPPE\ClassMap;
use PHPPE\DS;
use PHPPE\Client;
use PHPPE\Content;
use PHPPE\Tools;
use PHPPE\Extension;
use PHPPE\User;

//L("System")

class SystemTest extends \PHPUnit\Framework\TestCase
{
	public function testEvent()
	{
		$this->assertFalse(Core::lib("Developer")->eventTestRun,"Before event");
		Core::event("eventTest");
		$this->assertTrue(Core::lib("Developer")->eventTestRun,"After event");
	}

	public function testCore()
	{
        $this->assertTrue(ClassMap::has("NotLoaded", "oneMethod"), "ClassMap has");
		@unlink(ClassMap::$file);
		@unlink(ClassMap::$ace);
		$_SERVER['REQUEST_URI']="";
		$_SERVER['argv'][1]="test";
		@$core = new Core(true);
		$_SERVER['REQUEST_URI']="/test/something/?arg=1";
		@$core = new Core(true);
        $this->assertFileExists(ClassMap::$file, "New classmap");
		$this->assertTrue(is_array(ClassMap::map()),"Base");
		$this->assertNotEmpty($core->base,"Base");
		$this->assertNotEmpty($core->url,"Url");
		$this->assertEquals(Core::$core->output,$core->output, "Output");

		$this->assertGreaterThan(Core::$core->now,Core::started(),"Started");
        $this->assertNotEmpty(ClassMap::ace(), "ClassMap access control entries");
	}

	public function testLib()
	{
		$libs = Core::lib();
		$this->assertNotEmpty($libs["Developer"],"Autoloading");
		$this->assertEquals($libs["Developer"],Core::lib("Developer"),"Instance");

		Core::lib( "TestLib1", new Extension());
		$libs2 = Core::lib();

		$this->assertGreaterThan(count($libs),count($libs2),"Loaded manually");
		$this->assertTrue(Core::isInst("TestLib1"),"isInst");

		Core::lib( "TestLib2", new Client(), "TestLib1");
		$this->assertNotNull(Core::lib("TestLib2"),"Dependency");
		$this->assertNull(Core::lib("TestLib3"),"No extension");

		$this->assertInstanceOf("\\PHPPE\\Extension",Core::lib("TestLib1"),"Classof 1");
		$this->assertInstanceOf("\\PHPPE\\Client",Core::lib("TestLib2"),"Classof 2");

		$this->assertEquals("PHPPE\\Extension",Core::lib("TestLib1"),"Extension toString");

		$wasExc=false;
		try {
			Core::lib( "TestLib2", new Client(), "TestLib1,NoSuchLib");
		} catch(\Exception $e) {
			$wasExc=true;
		}
		$this->assertTrue($wasExc,"Failed lib dependency");
	}

	public function testClient()
	{
		$lang = $_SESSION['pe_l'];
		unset($_SESSION['pe_l']);

		$client = new Client;
		$client->init([]);

		$this->assertNotNull($client->ip,"Client IP");
		$this->assertNotNull($client->agent,"Client Agent");
		$this->assertNotNull($client->lang,"Client Lang");

		Core::$w=true;
		$_SERVER['REMOTE_ADDR']="::1";
		$_REQUEST['nojs']=true;
		$_REQUEST['lang']="en";
		unset($_SESSION['pe_l']);
		$_SESSION['pe_u']->data['lang']='hu';
		$client->init(['tz'=>'UTC']);
		$this->assertEquals("en",$client->lang,"Client Lang ow");
		unset($_SESSION['pe_u']->data['lang']);
		$_SESSION['pe_l']=$lang;
	}

	public function testUserAccess()
	{
		$user = new User;
		$this->assertEquals(0,$user->id,"User Id #1");
		$user->init([]);
		$this->assertEquals(Core::$user->id,$user->id,"User Id #2");
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
		$this->assertEmpty(Core::error(),"Errors");
		$this->assertFalse(Core::isError(),"IsError");

		Core::error("message","obj.field");

		$this->assertNotEmpty(Core::error(),"Errors #2");
		$this->assertTrue(Core::isError(),"IsError #2");

	}

	public function testLog()
	{
		$s = Core::$core->syslog;
		$t = Core::$core->trace;
		$o = Core::$w;
		Core::$core->syslog = true;
		Core::$core->trace = true;
		Core::log("B","Should be Audit","phpunit");
		$r = Core::$core->runlevel;
		Core::$core->runlevel = 0;
		Core::log("D","Should be skipped");
		Core::$w = false;
		Core::log("E","Stderr","phpunit");
		Core::$core->runlevel = 1;
		Core::$core->syslog = false;
		Core::$w = true;
		Core::log("E","Stderr","phpunit");
		$this->assertNotEmpty(file_get_contents("data/log/phpunit.log"),"Audit log");
		unlink("data/log/phpunit.log");
		Core::$w = $o;
		Core::$core->runlevel = $r;
		Core::$core->syslog = $s;
		Core::$core->trace = $t;
	}

	public function testContent()
	{
		Core::$core->nocache=true;

		include_once(__DIR__."/../libs/FailFilter.php");

		DS::close();

		DS::db("sqlite::memory:");
		DS::exec("insert into pages (id,name,template,data,dds,filter) values ('test','Test','testview','{\"body\":\"testbody\"}','{\"testdds\":[\"1\",\"\",\"\"]}','fail');");
        DS::exec("insert into views (id,ctrl) values ('testview', 'echo(\"OK\");');");

		$url = Core::$core->url;
		$title = Core::$core->title;
		$contentApp = new Content;
		//! no content
		Core::$core->title = "NONE";
		$contentApp = new Content("no/such/content");
		$this->assertEquals("NONE",Core::$core->title,"No content");

		//! filter
		$contentApp = new Content("test/");
		$this->assertEquals("403",Core::$core->template,"Filtered");
		DS::exec("update pages set filter='' where id='test';");

		//! is content
		$contentApp = new Content("test/");
		$this->assertEquals("Test",Core::$core->title,"Content");

		$contentApp->getDDS($contentApp);
		$this->assertEquals("testbody",$contentApp->body,"Body");
		$this->assertNotEmpty($contentApp->testdds,"DDS");

		$old = Core::$core->noctrl;
		Core::$core->noctrl = false;
		$contentApp->ctrl="echo('OK');";
		$this->assertEquals("OK",$contentApp->action(),"Content controller #1");

		$old = Core::$core->noctrl;
		Core::$core->noctrl = true;
		$this->assertNull($contentApp->action(),"Content controller #2");
		Core::$core->noctrl = $old;

		DS::exec("update pages set dds='{\"testdds2\":[\"nosuchcolumn\",\"\",\"\"]}' where id='test';");
		$contentApp = new Content("test/");
		$contentApp->getDDS($contentApp);
		$this->assertEmpty(@$contentApp->testdds2,"DDS failed");

		Core::$core->title = $title;
	}

	public function testTools()
	{
		$this->assertTrue(mkdir("data/a/b/c/d/e/f",0775,true),"Creating directories #1");
		$this->assertTrue(is_dir("data/a/b/c/d/e"),"Creating directories #2");
		file_put_contents("data/a/b/c/e","aaa");
		Tools::rmdir("data/a");
		$this->assertFalse(is_dir("data/a"),"Removing directories");

	}

}
?>
