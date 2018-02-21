<?php
use PHPPE\Core as Core;

//L("AddOn")
class AddonTest extends \PHPUnit\Framework\TestCase
{
	public function testBuiltInAddons()
	{
		$value="";

		$fld = new \PHPPE\AddOn\hidden([],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"name"),"Hidden");

		$fld = new \PHPPE\AddOn\button([],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"alert"),"Button edit");
		$this->assertGreaterThan(0,strpos($fld->show(),"alert"),"Button show");

		$fld = new \PHPPE\AddOn\update([],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"submit"),"Update");

		$value="abc";
		$fld = new \PHPPE\AddOn\text([10],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"input"),"Text edit");
		$this->assertEquals("abc",$fld->show(),"Text show");
		$value="null";
		$fld = new \PHPPE\AddOn\text([10,10],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"/textarea"),"Textarea edit");
		$val = \PHPPE\AddOn\text::validate("obj.fld",$value,[2],['','','','','[a-z]+']);
		$this->assertTrue($val[0],"Text validate");

		$fld = new \PHPPE\AddOn\pass([],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"pass"),"Pass edit");
		$this->assertGreaterThan(0,strpos($fld->show(),"**",1),"Pass show");
		$value="A1b2c3D4";
		$val = \PHPPE\AddOn\pass::validate("obj.fld",$value,[],[]);
		$this->assertTrue($val[0],"Pass validate");

		$value=100;
		$fld = new \PHPPE\AddOn\num([],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"number"),"Num edit");
		$this->assertGreaterThan(0,strpos($fld->show(),"0"),"Num show");

		$value="abc";
		$val = \PHPPE\AddOn\num::validate("obj.fld",$value,[],[]);
		$this->assertNotTrue($val[0],"Num validate #1");

		$value=100;
		$val = \PHPPE\AddOn\num::validate("obj.fld",$value,[1000,2000],[]);
		$this->assertEquals(1000,$value,"Num validate #2");
		$val = \PHPPE\AddOn\num::validate("obj.fld",$value,[100,200],[]);
		$this->assertEquals(200,$value,"Num validate #3");

		$arr=[1=>"one",2=>"two"]; $value=1; $list="a,b,c";
		\PHPPE\View::assign("lst",$list);
		$obj=new \stdClass; $obj->a=1; $obj->b=2;
		$fld = new \PHPPE\AddOn\select([],"obj.fld",$value,[$arr,""]);
		$this->assertEquals(1,$fld->show(),"Select show");
		$this->assertGreaterThan(0,strpos($fld->edit(),"option"),"Select edit #1");
		$fld = new \PHPPE\AddOn\select([],"obj.fld",$value,["lst",$obj]);
		$fld = new \PHPPE\AddOn\select([1,1],"obj.fld",$value,["lst",$obj]);
		$this->assertGreaterThan(0,strpos($fld->edit(),"fld[]"),"Select edit #2");

		$obj = new \stdClass;
		$obj->txt="a,b,c";
		$obj->skip="3";
		\PHPPE\View::assign("obj",$obj);

		$arr="obj.txt"; $value=[1,2];
		$fld = new \PHPPE\AddOn\select([2,1],"obj.fld",$value,["obj.txt","obj.skip"]);
		$this->assertGreaterThan(0,strpos($fld->edit(),"option"),"Select multiple edit");
		$this->assertEquals("1, 2",$fld->show(),"Select multiple show");

		$output = \PHPPE\Core::$core->output;
		$value="Yes";
		$fld = new \PHPPE\AddOn\check([],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"checkbox"),"Check edit");
		\PHPPE\Core::$core->output="html";
		$this->assertEquals("Yes",$fld->show(),"Check show #1");
		\PHPPE\Core::$core->output="ncurses";
		$this->assertEquals("[X] Yes",$fld->show(),"Check show #2");
		\PHPPE\Core::$core->output=$output;

		$fld = new \PHPPE\AddOn\radio(['one'],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"radio"),"Radio edit");
		\PHPPE\Core::$core->output="html";
		$this->assertEquals("Yes",$fld->show(),"Radio show #1");
		\PHPPE\Core::$core->output="ncurses";
		$this->assertEquals("( ) one",$fld->show(),"Radio show #2");
		\PHPPE\Core::$core->output=$output;

		$value="+3612345678";
		$fld = new \PHPPE\AddOn\phone([],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"tel"),"Phone edit");
		$this->assertGreaterThan(0,strpos($fld->show(),"2345"),"Phone show");

		$val = \PHPPE\AddOn\phone::validate("obj.fld",$value,[],[]);
		$this->assertNotFalse($val[0],"Phone validate");

		$value="some@email.address.com";
		$fld = new \PHPPE\AddOn\email([],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"email"),"Email edit");
		\PHPPE\Core::$core->output="html";
		$this->assertGreaterThan(0,strpos($fld->show(),"&#46;com"),"Email show #1");
		\PHPPE\Core::$core->output="ncurses";
		$this->assertGreaterThan(0,strpos($fld->show(),".com"),"Email show #2");
		\PHPPE\Core::$core->output=$output;
		$val = \PHPPE\AddOn\email::validate("obj.fld",$value,[],[]);
		$this->assertNotFalse($val[0],"Email validate");

		$value="";
		$fld = new \PHPPE\AddOn\file([],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"file"),"File edit");
		$this->assertEmpty($fld->show(),"File show");

		$value="#112233";
		$fld = new \PHPPE\AddOn\color([],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"color"),"Color edit");
		$this->assertGreaterThan(0,strpos($fld->show(),"2233"),"Color show");

		$value="1970-01-02";
		$fld = new \PHPPE\AddOn\date([],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"date"),"Date edit");
		$this->assertGreaterThan(0,strpos($fld->show(),"01-0"),"Date show");
		$val = \PHPPE\AddOn\date::validate("obj.fld",$value,[],[]);
		$this->assertNotFalse($val[0],"Date validate");

		$value="1970-01-01 00:00:01";
		$fld = new \PHPPE\AddOn\time([],"obj.fld",$value);
		$this->assertGreaterThan(0,strpos($fld->edit(),"time"),"Time edit");
		$this->assertGreaterThan(0,strpos($fld->show(),"01-0"),"Time show");
		$val = \PHPPE\AddOn\time::validate("obj.fld",$value,[],[]);
		$this->assertNotFalse($val[0],"Time validate");

		$value="";
		$fld = new \PHPPE\AddOn\label([],"obj.fld",$value,["Label"]);
		$this->assertGreaterThan(0,strpos($fld->edit(),"Label"),"field label #1");
		$this->assertEquals($fld->show(),$fld->edit(),"field label #2");

	}
}
?>
