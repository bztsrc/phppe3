<?php
use PHPPE\Core as PHPPE;

//fix float point decimal separator
setlocale(LC_NUMERIC, 'en_US.utf-8');

// data converter tests

//data for tests
$_REQUEST['obj_a']="a";
$_REQUEST['obj_b']="b";
$_REQUEST['obj_c']="c";

$obj = new \stdClass();
$obj->field1 = "field1";
$obj->field2 = "field2's";
$obj->field3 = 3;
$obj->field4 = 1.2;

$tree = array(
	array("id"=>1,"name"=>"1"),
	array("id"=>2,"name"=>"2","_"=>array(
		array("id"=>21,"name"=>"21"),
		array("id"=>22,"name"=>"22","_"=>array(
			array("id"=>221,"name"=>"221"),
			array("id"=>222,"name"=>"222")
		)),
		array("id"=>23,"name"=>"23"),
		array("id"=>24,"name"=>"24"))
	)
);

//checks
echo("req2arr (no validation): ");
if( serialize(PHPPE::req2arr("obj")) != 'a:3:{s:1:"a";s:1:"a";s:1:"b";s:1:"b";s:1:"c";s:1:"c";}' ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("req2obj (no validation): ");
if( serialize(PHPPE::req2obj("obj")) != 'O:8:"stdClass":3:{s:1:"a";s:1:"a";s:1:"b";s:1:"b";s:1:"c";s:1:"c";}' || PHPPE::iserror() ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

PHPPE::validate("obj.b","phone");

echo("req2obj (failed validation): ");
if( serialize(PHPPE::req2obj("obj")) != 'O:8:"stdClass":3:{s:1:"a";s:1:"a";s:1:"b";s:1:"b";s:1:"c";s:1:"c";}' || !PHPPE::iserror() || !PHPPE::iserror("obj.b")) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("obj2str (default separator): ");
if( PHPPE::obj2str($obj) != "field1='field1' field2='field2\\'s' field3='3' field4='1.2'" ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("arr2str (default separator): ");
if( PHPPE::arr2str($obj) != "field1='field1' field2='field2\\'s' field3='3' field4='1.2'" ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("obj2str (comma separator): ");
if( PHPPE::obj2str($obj,"",",") != "field1='field1',field2='field2\\'s',field3='3',field4='1.2'" &&
    PHPPE::obj2str($obj,"",",") != "field1='field1',field2='field2''s',field3='3',field4='1.2'" ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("obj2str (string skiplist): ");
if( PHPPE::obj2str($obj,"field2,field3") != "field1='field1' field4='1.2'" ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("obj2str (array skiplist): ");
if( PHPPE::obj2str($obj,array("field2","field3")) != "field1='field1' field4='1.2'" ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("val2arr (default separator): ");
if( json_encode(PHPPE::val2arr("app.test_val2arr")) != '["a:b:c"]' ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("tre2arr (selectbox mode): ");
if( json_encode(PHPPE::tre2arr($tree)) != '[{"id":1,"name":"1"},{"id":2,"name":"2"},{"id":21,"name":"  21"},{"id":22,"name":"  22"},{"id":221,"name":"    221"},{"id":222,"name":"    222"},{"id":23,"name":"  23"},{"id":24,"name":"  24"}]' ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("tre2arr (prefix given): ");
if( json_encode(PHPPE::tre2arr($tree,"&nbsp;")) != '[{"id":1,"name":"1"},{"id":2,"name":"2"},{"id":21,"name":"&nbsp;21"},{"id":22,"name":"&nbsp;22"},{"id":221,"name":"&nbsp;&nbsp;221"},{"id":222,"name":"&nbsp;&nbsp;222"},{"id":23,"name":"&nbsp;23"},{"id":24,"name":"&nbsp;24"}]' ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

echo("tre2arr (div nesting mode): ");
if( json_encode(PHPPE::tre2arr($tree,"<div id='tree2_%d' style='padding-left:10px;'>", "</div>")) != '[{"id":1,"name":"1"},{"id":2,"name":"2\n<div id=\'tree2_1\' style=\'padding-left:10px;\'>"},{"id":21,"name":"21"},{"id":22,"name":"22\n<div id=\'tree2_3\' style=\'padding-left:10px;\'>"},{"id":221,"name":"221"},{"id":222,"name":"222\n<\/div>"},{"id":23,"name":"23"},{"id":24,"name":"24\n<\/div>"}]' ) {
	echo("Failed\n");
	return false;
} else echo("OK\n");

//everything was ok
return true;
?>