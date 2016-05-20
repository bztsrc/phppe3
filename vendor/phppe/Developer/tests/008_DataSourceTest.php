<?php
class DataSource extends PHPUnit_Framework_TestCase
{
	public function testDataSource()
	{
		\PHPPE\DS::close();

		$wasExc=false;
		try {
			$val = \PHPPE\DS::field("1+1");
		} catch(\Exception $e) {
			$wasExc=true;
		}
		$this->assertTrue($wasExc,"No DS exception");

		$ds = new \PHPPE\DS("sqlite::memory:");

		$this->assertEquals("%a%",\PHPPE\DS::like("a"),"like #1");
		$this->assertEquals("%a%b%",\PHPPE\DS::like("a b"),"like #2");
		$this->assertEquals("%a%b%",\PHPPE\DS::like("a' \"b?"),"like #3");

		\PHPPE\DS::db()->s=["describe"=>".schema"];

		$this->assertInstanceOf("PDO",\PHPPE\DS::db(),"PDO object");

		$this->assertEquals(2,\PHPPE\DS::field("1+1"),"field");

		$wasExc=false;
		try {
			$val = \PHPPE\DS::query("*","nosuchtable");
		} catch(\Exception $e) {
			$wasExc=true;
		}
		$this->assertTrue($wasExc,"No scheme exception");

		$wasExc=false;
		try {
			$val = \PHPPE\DS::query("*","badtable");
		} catch(\Exception $e) {
			$wasExc=true;
		}
		$this->assertTrue($wasExc,"Bad scheme exception");

		$this->assertEquals(
			'a:4:{i:0;a:3:{s:2:"id";s:1:"1";s:4:"name";s:5:"first";s:8:"parentId";s:1:"0";}i:1;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}i:2;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}i:3;a:3:{s:2:"id";s:1:"4";s:4:"name";s:6:"fourth";s:8:"parentId";s:1:"0";}}',
			serialize(\PHPPE\DS::query("*","test")),
			"scheme install");

		$this->assertEquals(1,\PHPPE\DS::exec("-- sql comment"),"exec comment");

		$this->assertEquals(1,\PHPPE\DS::exec("insert into test values (5,'fifth',0)"),"exec insert");

		$this->assertEquals(1,\PHPPE\DS::exec("update test set parentId=1 where id=?",5),"exec update unnamed");
		$this->assertEquals(1,\PHPPE\DS::query("parentId","test","id=5")[0]["parentId"],"exec parentIdcheck");

		$this->assertEquals(1,\PHPPE\DS::exec("update test set parentId=:newparent where id=:id",[":id"=>5,":newparent"=>2]),"exec update named");
		$this->assertEquals(2,\PHPPE\DS::query("parentId","test","id=5")[0]["parentId"],"exec parentIdcheck");

		$this->assertEquals(0,\PHPPE\DS::exec("update test set parentId=:newparent where id=:id",[":id"=>6,":newparent"=>2]),"exec update no match");

		$this->assertInternalType("integer",\PHPPE\DS::exec("update test set parentId=1 where id=?",5),"insert returns");
		$this->assertInternalType("array",\PHPPE\DS::exec("select * from test"),"select returns");

		$this->assertEquals(
			'a:2:{i:0;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}i:1;a:3:{s:2:"id";s:1:"5";s:4:"name";s:5:"fifth";s:8:"parentId";s:1:"1";}}',
			serialize(\PHPPE\DS::query("*","test","id!=4","parentId")),
			"query group by");

		$this->assertEquals(
			'a:2:{i:0;a:3:{s:2:"id";s:1:"5";s:4:"name";s:5:"fifth";s:8:"parentId";s:1:"1";}i:1;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}}',
			serialize(\PHPPE\DS::query("*","test","id!=4","parentId","id desc")),
			"query order by");

		$this->assertEquals(
			'a:2:{i:0;a:3:{s:2:"id";s:1:"5";s:4:"name";s:5:"fifth";s:8:"parentId";s:1:"1";}i:1;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}}',
			serialize(\PHPPE\DS::query("*","test","id!=4","parentId","id desc",1)),
			"query offset");

		$this->assertEquals(
			'a:1:{i:0;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}}',
			serialize(\PHPPE\DS::query("*","test","id!=4","parentId","id desc",1,1)),
			"query limit");

		$this->assertEquals(
			'a:0:{}',
			serialize(\PHPPE\DS::query("*","test","id=11")),
			"query empty");

		$this->assertEquals(
			'a:2:{s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}',
			serialize(\PHPPE\DS::fetch("name,parentId","test","id=?","","",[3])),
			"fetch record");

		$this->assertEquals(
			'a:0:{}',
			serialize(\PHPPE\DS::fetch("*","test","id=11")),
			"fetch empty");

		$this->assertEquals(
			'a:3:{i:0;a:4:{s:2:"id";s:1:"1";s:4:"name";s:5:"first";s:8:"parentId";s:1:"0";s:1:"_";a:2:{i:0;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}i:1;a:3:{s:2:"id";s:1:"5";s:4:"name";s:5:"fifth";s:8:"parentId";s:1:"1";}}}i:1;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}i:2;a:3:{s:2:"id";s:1:"4";s:4:"name";s:6:"fourth";s:8:"parentId";s:1:"0";}}',
			serialize(\PHPPE\DS::tree("select * from test where parentId=:id")),
			"tree all");

		$this->assertEquals(
			'a:2:{i:0;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}i:1;a:3:{s:2:"id";s:1:"5";s:4:"name";s:5:"fifth";s:8:"parentId";s:1:"1";}}',
			serialize(\PHPPE\DS::tree("select * from test where parentId=:id",1)),
			"tree sub-tree");

		$this->assertEquals(
			'a:0:{}',
			serialize(\PHPPE\DS::tree("select * from test where parentId=:id",3)),
			"tree empty");

		$wasExc=false;
		try {
			$val = \PHPPE\DS::query("nocolumn","test");
		} catch(\Exception $e) {
			$wasExc=true;
		}
		$this->assertTrue($wasExc,"PDO exception");

		$wasExc=false;
		try {
			$val = \PHPPE\DS::db("nodrv:");
		} catch(\Exception $e) {
			$wasExc=true;
		}
		$this->assertTrue($wasExc,"No driver exception");
		$this->assertGreaterThan(0,\PHPPE\DS::bill(),"Billed secs");
	}

	public function testORM()
	{
		include_once(__DIR__."/../libs/TestModel.php");

		\PHPPE\DS::close();
		\PHPPE\DS::db("sqlite::memory:");

		$testModel = new TestModel("something");

		$this->assertEquals(
			'a:4:{i:0;a:3:{s:2:"id";s:1:"1";s:4:"name";s:5:"first";s:8:"parentId";s:1:"0";}i:1;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}i:2;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}i:3;a:3:{s:2:"id";s:1:"4";s:4:"name";s:6:"fourth";s:8:"parentId";s:1:"0";}}',
			serialize($testModel->find()),
			"ORM find #1");

		$this->assertEquals(
			'a:3:{i:0;a:3:{s:2:"id";s:1:"1";s:4:"name";s:5:"first";s:8:"parentId";s:1:"0";}i:1;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}i:2;a:3:{s:2:"id";s:1:"4";s:4:"name";s:6:"fourth";s:8:"parentId";s:1:"0";}}',
			serialize($testModel->find(0,"parentId=?")),
			"ORM find #2");

		$this->assertEquals(
			'a:1:{i:0;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}}',
			serialize($testModel->find(1,"parentId=?")),
			"ORM find #3");

		$this->assertEquals(5,$testModel->save(),"ORM save insert");
		$this->assertEquals(5,$testModel->id,"ORM save id");
		$testModel->name="sssss";
		$this->assertEquals(5,$testModel->save(),"ORM save update");

		$testModel->id=6;
		$this->assertFalse($testModel->save(),"ORM save update fail");

		$this->assertFalse($testModel->load(7),"ORM load fail");
		$this->assertEquals(3,$testModel->load(3),"ORM load success");
		$this->assertEquals(3,$testModel->id,"ORM load id");
		$testModel->name="sssss";
		$this->assertEquals(3,$testModel->load(3),"ORM load reload");
		$this->assertNotEquals("sssss",$testModel->name,"ORM load name");

		\PHPPE\DS::close();
		$wasExc=false;
		try {
			$testModel->save();
		} catch(\Exception $e) {
			$wasExc=true;
		}
		$this->assertTrue($wasExc,"save exception no ds");

		$testModel = new BadModel;

		$wasExc=false;
		try {
			$testModel->save();
		} catch(\Exception $e) {
			$wasExc=true;
		}
		$this->assertTrue($wasExc,"save exception no table");

		$wasExc=false;
		try {
			$testModel->find();
		} catch(\Exception $e) {
			$wasExc=true;
		}
		$this->assertTrue($wasExc,"find exception");

		$wasExc=false;
		try {
			$testModel->load();
		} catch(\Exception $e) {
			$wasExc=true;
		}
		$this->assertTrue($wasExc,"load exception");

	}

	public function testDiag()
	{
		\PHPPE\DS::close();
		$ds = new \PHPPE\DS;

		$this->assertNull($ds->diag(),"Diag no ds");
		
		\PHPPE\DS::db("sqlite::memory:");

		ob_start();
		$ds->diag();
		$this->assertEmpty(ob_get_clean(),"Diag no update");

		if(file_put_contents("vendor/phppe/Developer/sql/upd_test.sql","select 1;"))
		{
			ob_start();
			$ds->diag();
			$this->assertNotEmpty(ob_get_clean(),"Diag update");
			$this->assertFileNotExists("vendor/phppe/Developer/sql/upd_test.sql","Diag file");
		}
	}
}
?>
