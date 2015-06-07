<?php
namespace PHPPE;
use PHPPE\Core as PHPPE;

//data source tests

//for ORM test
class TestModel extends \PHPPE\Model {
	public $id;
	public $name;
	public $parentId;
	protected static $_table="test";

	function TestModel($name) {
		$this->name=$name;
		$this->parentId=0;
	}
}

//initialize
$old = PHPPE::ds();
PHPPE::dbInit("sqlite::memory:");

echo("dbInit,ds,db: ");
if( $old === PHPPE::ds() || get_class(PHPPE::db()) != "PDO" ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

//string formatters
echo("like: ");
if( PHPPE::like('a') != "%a%" || PHPPE::like('a b') != "%a%b%"  || PHPPE::like("a' \"b?") != "%a%b%" ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("field: ");
if( PHPPE::field("1+1") != "2" ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

//scheme installation
echo("query (on demand scheme installation): ");
if( serialize(PHPPE::query("*","test")) != 'a:4:{i:0;a:3:{s:2:"id";s:1:"1";s:4:"name";s:5:"first";s:8:"parentId";s:1:"0";}i:1;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}i:2;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}i:3;a:3:{s:2:"id";s:1:"4";s:4:"name";s:6:"fourth";s:8:"parentId";s:1:"0";}}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

//execute
echo("exec insert: ");
if( PHPPE::exec("insert into test values (5,'fifth',0)") != 1) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("exec update (unnamed placeholder): ");
if( PHPPE::exec("update test set parentId=1 where id=?",array(5)) != 1 || PHPPE::query("*","test","id=5")[0]["parentId"]!=1) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("exec update (named placeholder): ");
if( PHPPE::exec("update test set parentId=:newparent where id=:id",array(":id"=>5,":newparent"=>2)) != 1 || PHPPE::query("*","test","id=5")[0]["parentId"]!=2) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("exec update (no match): ");
if( PHPPE::exec("update test set parentId=:newparent where id=:id",array(":id"=>6,":newparent"=>2)) != 0) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("exec select (returns array): ");
if( !is_array(PHPPE::exec("select * from test")) ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("exec bad sql (exception): ");
$waserror="";
try {
	PHPPE::exec("select nocolumn,name,parentId from test");
} catch(\Exception $e) {
	$waserror=$e->getMessage();
}
if( empty($waserror) ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

//query records
echo("query (where): ");
if( serialize(PHPPE::query("*","test","id!=4")) != 'a:4:{i:0;a:3:{s:2:"id";s:1:"1";s:4:"name";s:5:"first";s:8:"parentId";s:1:"0";}i:1;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}i:2;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}i:3;a:3:{s:2:"id";s:1:"5";s:4:"name";s:5:"fifth";s:8:"parentId";s:1:"2";}}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("query (group by): ");
if( serialize(PHPPE::query("*","test","id!=4","parentId")) != 'a:3:{i:0;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}i:1;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}i:2;a:3:{s:2:"id";s:1:"5";s:4:"name";s:5:"fifth";s:8:"parentId";s:1:"2";}}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("query (order by): ");
if( serialize(PHPPE::query("*","test","id!=4","parentId","id desc")) != 'a:3:{i:0;a:3:{s:2:"id";s:1:"5";s:4:"name";s:5:"fifth";s:8:"parentId";s:1:"2";}i:1;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}i:2;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("query (offset): ");
if( serialize(PHPPE::query("*","test","id!=4","parentId","id desc")) != 'a:3:{i:0;a:3:{s:2:"id";s:1:"5";s:4:"name";s:5:"fifth";s:8:"parentId";s:1:"2";}i:1;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}i:2;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("query (limit): ");
if( serialize(PHPPE::query("*","test","id!=4","parentId","id desc",1,1)) != 'a:1:{i:0;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("query (unnamed placeholder): ");
if( serialize(PHPPE::query("*","test","id=?","","",0,0,array(3))) != 'a:1:{i:0;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("query (named placeholder): ");
if( serialize(PHPPE::query("*","test","id=:id","","",0,0,array(":id"=>3))) != 'a:1:{i:0;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("query (empty result): ");
if( serialize(PHPPE::query("*","test","id=11")) != 'a:0:{}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

//fetch excatly one record
echo("fetch (with result): ");
if( serialize(PHPPE::fetch("name,parentId","test","id=?","","",array(3))) != 'a:2:{s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("fetch (empty result): ");
if( serialize(PHPPE::fetch("name,parentId","test","id=11")) != 'a:0:{}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

//getting a tree from db
echo("tree (all): ");
if( serialize(PHPPE::tree("select * from test where parentId=:id")) != 'a:3:{i:0;a:4:{s:2:"id";s:1:"1";s:4:"name";s:5:"first";s:8:"parentId";s:1:"0";s:1:"_";a:1:{i:0;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}}}i:1;a:4:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";s:1:"_";a:1:{i:0;a:3:{s:2:"id";s:1:"5";s:4:"name";s:5:"fifth";s:8:"parentId";s:1:"2";}}}i:2;a:3:{s:2:"id";s:1:"4";s:4:"name";s:6:"fourth";s:8:"parentId";s:1:"0";}}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("tree (sub-tree): ");
if( serialize(PHPPE::tree("select * from test where parentId=:id",2)) != 'a:1:{i:0;a:3:{s:2:"id";s:1:"5";s:4:"name";s:5:"fifth";s:8:"parentId";s:1:"2";}}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("tree (empty): ");
if( serialize(PHPPE::tree("select * from test where parentId=:id",3)) != 'a:0:{}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

//orm
$testModel = new TestModel("something");

echo("Model->find (all): ");
if( serialize($testModel->find()) != 'a:5:{i:0;a:3:{s:2:"id";s:1:"1";s:4:"name";s:5:"first";s:8:"parentId";s:1:"0";}i:1;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}i:2;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}i:3;a:3:{s:2:"id";s:1:"4";s:4:"name";s:6:"fourth";s:8:"parentId";s:1:"0";}i:4;a:3:{s:2:"id";s:1:"5";s:4:"name";s:5:"fifth";s:8:"parentId";s:1:"2";}}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("Model->find (root tree): ");
if( serialize($testModel->find(0,"parentId=?")) != 'a:3:{i:0;a:3:{s:2:"id";s:1:"1";s:4:"name";s:5:"first";s:8:"parentId";s:1:"0";}i:1;a:3:{s:2:"id";s:1:"2";s:4:"name";s:6:"second";s:8:"parentId";s:1:"0";}i:2;a:3:{s:2:"id";s:1:"4";s:4:"name";s:6:"fourth";s:8:"parentId";s:1:"0";}}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("Model->find (sub tree): ");
if( serialize($testModel->find(1,"parentId=?")) != 'a:1:{i:0;a:3:{s:2:"id";s:1:"3";s:4:"name";s:5:"third";s:8:"parentId";s:1:"1";}}') {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("Model->save (new item, id changed): ");
if( !$testModel->save() || $testModel->id!=6 ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");


echo("Model->save (update): ");
$testModel->name="sssss";
if( $testModel->save()!=6 ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("Model->load (failure, object untouched): ");
if( $testModel->load(7)!==false ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("Model->load (success): ");
if( $testModel->load(3)!==true || $testModel->id!=3 ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("Model->load (reload): ");
$testModel->name="sssss";
if( $testModel->load()!==true || $testModel->name=="sssss" ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

PHPPE::dbClose();

//everything was ok
return true;
?>