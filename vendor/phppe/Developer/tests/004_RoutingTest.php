<?php
class RoutingTest extends PHPUnit_Framework_TestCase
{
	public function testRoute()
	{
		// route test
		$routes = \PHPPE\Http::route();
		$this->assertNotEmpty($routes[sha1("tests|Developer|")],"Route exists");

		//add different new routes
		\PHPPE\Http::route("test1","Tests");
		\PHPPE\Http::route("test2","Tests","action_run");
		\PHPPE\Http::route(array("url"=>"test3","name"=>"Tests","action"=>"action_run"));
		\PHPPE\Http::route(array(
			array("test4","Tests","action_run"),
			array("test5","Tests","action_run")
		));
		$r = new \stdClass();
		$r->url="test6";
		$r->name="Tests";
		\PHPPE\Http::route($r);
		\PHPPE\Http::route("test7","Tests","action_member","@loggedin,admin");
		\PHPPE\Http::route("test7","Tests","action_public");
		\PHPPE\Http::route("test9","Tests","",array("@loggedin","admin"));

		$wasExc=false;
		try {
			\PHPPE\Http::route(new \stdClass);
		}catch(\Exception $e){
			$wasExc=true;
		}
		$this->assertTrue($wasExc,"Bad route");

		$new = \PHPPE\Http::route();

		$this->assertGreaterThan(count($routes),count($new),"New routes");

		$this->assertNotEmpty($new[sha1("test1|Tests|")],"Route added");
		$this->assertEmpty($new[sha1("test1|Tests|")][2],"Default action");

		$this->assertNotEmpty($new[sha1("test2|Tests|action_run")][2],"Named action");

		$this->assertNotEmpty($new[sha1("test3|Tests|action_run")][1],"Assoc array route");

		$this->assertNotEmpty($new[sha1("test4|Tests|action_run")][2],"Multiple route #1");
		$this->assertNotEmpty($new[sha1("test5|Tests|action_run")][2],"Multiple route #2");

		$this->assertNotEmpty($new[sha1("test6|Tests|")],"Object route");

		$this->assertEquals($new[sha1("test7|Tests|action_member")][0],$new[sha1("test7|Tests|action_public")][0],"Same route with and without filter");
		
		$this->assertEquals(serialize($new[sha1("test7|Tests|action_member")][3]),serialize($new[sha1("test9|Tests|")][3]),"Filter as string and as array");

		//! for PHPUnit, as it runs from /usr/local/bin
//		\PHPPE\Core::$core->base="localhost/";
		
		$data1=file_get_contents(url("tests","httptest")."?nojs");
		$data2=file_get_contents(url("tests","httptest")."?nojs",false,stream_context_create(['http'=>[
			'method'=>'POST',
			'header'=> 'Content-type: application/x-www-form-urlencoded',
			'content'=>http_build_query([
				'var1'=>'1',
				'var2'=>'2',
			])]]));

		$this->assertEquals("GET",$data1,"Same url with GET filter");
		$this->assertEquals("POST",$data2,"Same url with POST filter");

		$this->assertEquals(
			'["test1","run",[]]',
			json_encode(\PHPPE\Http::urlMatch("test1","run")),
			"urlMatch #1");

		$this->assertEquals(
			'["Tests","action_run",[]]',
			json_encode(\PHPPE\Http::urlMatch("","","test4/")),
			"urlMatch #2");

        \PHPPE\Core::$user->id=0;
		$this->assertEquals(
			'["403","run",[]]',
			json_encode(\PHPPE\Http::urlMatch("","run","test9/")),
			"urlMatch #3");

	}

	public function testFilters()
	{
		//! default filters
		$this->assertFalse(\PHPPE\Core::cf("csrf"),"CSRF");
		$is = @$_SERVER[ 'REQUEST_METHOD' ] == "GET" ? true : false;
		$this->assertEquals($is,\PHPPE\Core::cf("get"),"GET");
		$is = @$_SERVER[ 'REQUEST_METHOD' ] == "POST" ? true : false;
		$this->assertEquals($is,\PHPPE\Core::cf("post"),"POST");
		//! Access Control Entry
		$this->assertEquals(\PHPPE\Core::$user->has("loggedin"),\PHPPE\Core::cf("@loggedin"),"ACE");
		$old = \PHPPE\Core::$user->id;
		\PHPPE\Core::$user->id = 1;
		$this->assertTrue(\PHPPE\Core::cf("loggedin"),"loggedin");
		\PHPPE\Core::$user->id = $old;

	}
}
?>
