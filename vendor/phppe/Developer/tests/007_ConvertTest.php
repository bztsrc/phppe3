<?php
//L("Convert")
class ConvertTest extends \PHPUnit\Framework\TestCase
{
	public function testConvert()
	{
		setlocale(LC_NUMERIC, 'en_US.utf-8');

		\PHPPE\Core::validate("obj.phone","phone",false);
		\PHPPE\Core::validate("obj.chk.me","check",true);
		\PHPPE\Core::validate("obj.file","file",false);
		$_FILES['obj_file']['error']=1;
		$_REQUEST['obj_phone']="+361234567";
		$obj = \PHPPE\Core::req2obj("obj");
		$this->assertInstanceOf("stdClass",$obj,"req2obj");
		$this->assertFalse(\PHPPE\Core::isError("obj.phone"),"validator");

		$_REQUEST['obj_phone']="abc";
		$obj2 = \PHPPE\Core::req2arr("obj");
		$this->assertInternalType("array",$obj2,"req2arr");
		$this->assertTrue(\PHPPE\Core::isError("obj.chk.me"),"validator");

		$obj = new \stdClass();
		$obj->field1 = "field1";
		$obj->field2 = "field2's";
		$obj->field3 = 3;
		$obj->field4 = 1.5;

		$this->assertEmpty(
			\PHPPE\Core::arr2str("aaa"),
			"arr2str str");

		$this->assertEquals(
			"field1='field1' field2='field2\\'s' field3='3' field4='1.5'",
			\PHPPE\Core::arr2str($obj),
			"arr2str");

		$this->assertEquals(
			"field1='field1' field2='field2\\'s' field3='3' field4='1.5'",
			\PHPPE\Core::obj2str($obj),
			"obj2str space");

		$this->assertEquals(
			"field1='field1',field2='field2\\'s',field3='3',field4='1.5'",
			str_replace("''","\\'",\PHPPE\Core::obj2str($obj,"",",")),
			"obj2str comma");

		$this->assertEquals(
			"field1='field1' field4='1.5'",
			\PHPPE\Core::obj2str($obj,"field2,field3"),
			"obj2str skip");

		$obj2 = new \stdClass();
		$obj2->test="a:b:c";
		$obj2->test2=["a","b","c"];
		\PHPPE\View::assign("obj2",$obj2);

		$this->assertEquals(
			"[\"a:b:c\"]",
			json_encode(\PHPPE\Core::val2arr("obj2.test")),
			"val2arr #1");

		$this->assertEquals(
			"[\"a\",\"b\",\"c\"]",
			json_encode(\PHPPE\Core::val2arr("obj2.test2")),
			"val2arr #2");

		$this->assertEquals(
			"[1,2]",
			json_encode(\PHPPE\Core::val2arr([1,2])),
			"val2arr #3");

		$this->assertEquals(
			"[\"a\",\"b\",\"c\"]",
			json_encode(\PHPPE\Core::val2arr("obj2.test",":")),
			"val2arr #4");

		$this->assertEquals(
			"[]",
			json_encode(\PHPPE\Core::val2arr("")),
			"val2arr #5");

		$tree = [
			["id"=>1,"name"=>"1"],
			["id"=>2,"name"=>"2","_"=>[
				["id"=>21,"name"=>"21"],
				["id"=>22,"name"=>"22","_"=>[
					["id"=>221,"name"=>"221"],
					["id"=>222,"name"=>"222"]
				]],
				["id"=>23,"name"=>"23"],
				["id"=>24,"name"=>"24"]]
			]
		];

		$this->assertEquals(
			'[{"id":1,"name":"1"},{"id":2,"name":"2"},{"id":21,"name":"  21"},{"id":22,"name":"  22"},{"id":221,"name":"    221"},{"id":222,"name":"    222"},{"id":23,"name":"  23"},{"id":24,"name":"  24"}]',
			json_encode(\PHPPE\Core::tre2arr($tree)),
			"tre2arr selectbox #1");

		$this->assertEquals(
			'[{"id":1,"name":"1"},{"id":2,"name":"2"},{"id":21,"name":"&nbsp;21"},{"id":22,"name":"&nbsp;22"},{"id":221,"name":"&nbsp;&nbsp;221"},{"id":222,"name":"&nbsp;&nbsp;222"},{"id":23,"name":"&nbsp;23"},{"id":24,"name":"&nbsp;24"}]',
			json_encode(\PHPPE\Core::tre2arr($tree,"&nbsp;")),
			"tre2arr selectbox #2");

		$this->assertEquals(
			'[{"id":1,"name":"1"},{"id":2,"name":"2\n<div id=\'tree2_1\' style=\'padding-left:10px;\'>"},{"id":21,"name":"21"},{"id":22,"name":"22\n<div id=\'tree2_3\' style=\'padding-left:10px;\'>"},{"id":221,"name":"221"},{"id":222,"name":"222\n<\/div>"},{"id":23,"name":"23"},{"id":24,"name":"24\n<\/div>"}]',
			json_encode(\PHPPE\Core::tre2arr($tree,"<div id='tree2_%d' style='padding-left:10px;'>", "</div>")),
			"tre2arr DOM");

		$tree = json_decode('[{"id":1,"name":"1"},{"id":2,"name":"2","_":[{"id":3,"name":"3"}]}]');
		$this->assertEquals(
			'[{"id":1,"name":"1"},{"id":2,"name":"2"},{"id":3,"name":"  3"}]',
			json_encode(\PHPPE\Core::tre2arr($tree)),
			"tre2arr stdClass selectbox");

		$this->assertEquals(
			'[{"id":1,"name":"1"},{"id":2,"name":"2\n<div id=\'tree2_1\' style=\'padding-left:10px;\'>"},{"id":3,"name":"3\n<\/div>"}]',
			json_encode(\PHPPE\Core::tre2arr($tree,"<div id='tree2_%d' style='padding-left:10px;'>", "</div>")),
			"tre2arr stdClass DOM");

	}

	public function testAssetMinifier()
	{
		$txt="/* valami */  megint  'masik  kell'\nrequires\textra\tspace\n//komment\nA {\n\tcolor:\t\t#112233;\n}";
		\PHPPE\Core::$core->nominify=true;
		$this->assertEquals(
			$txt,
			\PHPPE\Assets::minify($txt,"js"),
			"minify #1");
		\PHPPE\Core::$core->nominify=false;
		$this->assertEquals(
			$txt,
			\PHPPE\Assets::minify($txt,"c++"),
			"minify #2");
		$this->assertEquals(
			"megint'masik  kell'requires extra space A{color:#112233;}",
			\PHPPE\Assets::minify($txt,"js"),
			"minify #3");
		$this->assertEquals(
			"megint'masik  kell'requires extra space //komment A{color:#112233;}",
			\PHPPE\Assets::minify($txt,"css"),
			"minify #4");
		$this->assertEquals(
			"a b?".">do  not  minify<"."?",
			\PHPPE\Assets::minify("a  b?".">do  not  minify<"."?","php"),
			"minify #5");

	}
}
?>
