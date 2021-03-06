<?php
use PHPPE\Core as Core;

//L("DB")
class DBTest extends \PHPUnit\Framework\TestCase
{
	public function testDB()
	{
		if(!\PHPPE\ClassMap::has("PHPPE\DB"))
			$this->markTestSkipped();

		$this->assertEquals(
			"%some%thing%",
			\PHPPE\DB::like("some thing"),
			"like");

		$this->assertEquals(
			"SELECT * FROM users",
			\PHPPE\DB::select("users"),
			"Simple select");

		$this->assertEquals(
			"SELECT id,name FROM users",
			\PHPPE\DB::select("users")->fields(["id","name"]),
			"Select with fields");

		$this->assertEquals(
			"SELECT * FROM users WHERE id=?",
			\PHPPE\DB::select("users")->where("id=?"),
			"Select with where #1");

		$this->assertEquals(
			"SELECT * FROM users WHERE (id=? AND name=?)",
			\PHPPE\DB::select("users")->where(["id=?","name=?"]),
			"Select with where #2");

		$this->assertEquals(
			"SELECT * FROM users WHERE (id = 'my id')",
			\PHPPE\DB::select("users")->where([["id", "=", "my id"]]),
			"Select with where #3");

		$this->assertEquals(
			"SELECT * FROM users WHERE (id LIKE '%my%id%')",
			\PHPPE\DB::select("users")->where([["id", "like", "my id"]]),
			"Select with where #4");

		$this->assertEquals(
			"SELECT * FROM users WHERE (id LIKE '%my%id%' OR name LIKE '%my%name%') AND 1=1",
			\PHPPE\DB::select("users")->where([
				["id", "like", "my id"],
				["name", "like", "my name"],
				],"or")->where("1=1"),
			"Select with where #5");

		$this->assertEquals(
			"SELECT * FROM users u, user_posts p WHERE u.id=p.id",
			\PHPPE\DB::select("users","u")->table("user_posts","p")->where("u.id=p.id"),
			"Select with where #6");

		$wasExc=false;
		try{
			\PHPPE\DB::update("users")->where("id=id","NOT");
		}catch(\Exception $e){ $wasExc=true; }
		$this->assertTrue($wasExc,"bad where exception");

		$this->assertEquals(
			"SELECT * FROM users HAVING id=?",
			\PHPPE\DB::select("users")->having("id=?"),
			"Select with having");

		$this->assertEquals(
			"SELECT * FROM users LIMIT 10",
			\PHPPE\DB::select("users")->limit(10),
			"Select with limit #1");

		$wasExc=false;
		try{
			\PHPPE\DB::select("users")->offset(5)->sql();
		}catch(\Exception $e){ $wasExc=true; }
		$this->assertTrue($wasExc,"Select with limit #2");

		$this->assertEquals(
			"SELECT * FROM users LIMIT 10 OFFSET 5",
			\PHPPE\DB::select("users")->limit(10)->offset(5),
			"Select with limit #3");

		$this->assertEquals(
			"SELECT * FROM users GROUP BY name",
			\PHPPE\DB::select("users")->groupBy("name"),
			"Select with group by #1");

		$this->assertEquals(
			"SELECT * FROM users GROUP BY name,id",
			\PHPPE\DB::select("users")->groupBy(["name","id"]),
			"Select with group by #2");

		$this->assertEquals(
			"SELECT * FROM users ORDER BY id",
			\PHPPE\DB::select("users")->orderBy("id"),
			"Select with order by #1");

		$this->assertEquals(
			"SELECT * FROM users ORDER BY id",
			\PHPPE\DB::select("users")->orderBy(["id"]),
			"Select with order by #2");

		$wasExc=false;
		try{
			\PHPPE\DB::update("users")->sql();
		}catch(\Exception $e){ $wasExc=true; }
		$this->assertTrue($wasExc,"update no fields exception");

		$this->assertNotFalse(strpos(\PHPPE\DB::update("users"),"No fields specified"),
			"update no field string");

		$this->assertEquals(
			"UPDATE users SET id=?,name=?",
			\PHPPE\DB::update("users")->fields(['id','name']),
			"update with fields");

		$this->assertEquals(
			"DELETE FROM users",
			\PHPPE\DB::delete("users"),
			"delete table");

		$this->assertEquals(
			"DELETE a FROM users a",
			\PHPPE\DB::delete("users","a")->sql(),
			"delete alias");

		$this->assertEquals(
			"DELETE user_posts FROM user_posts LEFT JOIN users ON user_posts.userId=users.id WHERE (users.id IS NULL)",
			\PHPPE\DB::delete("user_posts")->join("LEFT","users","user_posts.userId=users.id")->where([["users.id","IS NULL"]]),
			"delete where");

		$this->assertEquals(
			"INSERT INTO users (id,name) VALUES (?,?)",
			\PHPPE\DB::insert("users")->fields('id,name'),
			"insert");

		$wasExc=false;
		try {
			\PHPPE\DB::select("users")->join("SIMPLE","user_posts","id=id");
		}catch(\Exception $e){ $wasExc=true; }
		$this->assertTrue($wasExc,"bad join exception");

		$this->assertEquals(
			"REPLACE INTO users (id,name) VALUES (?,?) WHERE id=''",
			\PHPPE\DB::replace("users")->fields(['id','name'])->where("id=''"),
			"replace");

		$this->assertEquals(
			"TRUNCATE TABLE users",
			\PHPPE\DB::truncate("users"),
			"truncate");

		$wasExc=false;
		try {
			\PHPPE\DB::select("users")->where([["1","!="]]);
		}catch(\Exception $e){ $wasExc=true; }
		$this->assertTrue($wasExc,"bad where exception");

		$wasExc=false;
		try {
			\PHPPE\DB::select("users")->where([["a", "similarto", "b"]]);
		}catch(\Exception $e){ $wasExc=true; }
		$this->assertTrue($wasExc,"bad where exception");

		$wasExc=false;
		try {
			\PHPPE\DB::update("")->sql();
		}catch(\Exception $e){ $wasExc=true; }
		$this->assertTrue($wasExc,"no table exception");

		$wasExc=false;
		try {
			\PHPPE\DB::insert("users")->sql();
		}catch(\Exception $e){ $wasExc=true; }
		$this->assertTrue($wasExc,"no where exception");

		$wasExc=false;
		try {
			\PHPPE\DB::replace("users")->fields(["id"])->sql();
		}catch(\Exception $e){ $wasExc=true; }
		$this->assertTrue($wasExc,"no where exception");

		\PHPPE\DS::close();
		$ds = new \PHPPE\DS("sqlite::memory:");

		$this->assertNotEmpty(
			\PHPPE\DB::select("users")->execute(),
			"execute");

		\PHPPE\DB::truncate("users")->execute();

		$wasExc=false;
		try {
			\PHPPE\DB::insert("users")->with('');
		}catch(\Exception $e){ $wasExc=true; }
		$this->assertTrue($wasExc,"no fields exception");

		$this->assertEquals(
			1,
			\PHPPE\DB::insert("users")->with([
				'id'=>123,
				'name'=>'newcomer',
				'email'=>'no@where.ltd'
			]),
			"insert with with");

		$wasExc=false;
		try {
			\PHPPE\DB::select("users")->where("id=?")->execute();
		}catch(\Exception $e){ $wasExc=true; }
		$this->assertTrue($wasExc,"no argument exception");

		$ds = new \PHPPE\DS("sqlite::memory:");
		$this->assertNotEmpty(
			\PHPPE\DB::select("users")->execute([],1),
			"execute on ds");

		$a=include("vendor/phppe/Core/libs/ds_mysql.php");
		$this->assertNotEmpty($a['_init'],"MySQL init");
	}
}
?>
