<?php
class TemplaterTest extends PHPUnit_Framework_TestCase
{
	public function testHelpers()
	{
		\PHPPE\Core::$core->nocache=true;

		\PHPPE\Core::$core->meta["test"]="testing";
		\PHPPE\Core::$core->link["apple-touch-icon"]="testing";
		\PHPPE\View::init([]);
		$dir=dirname(__DIR__);
		\PHPPE\View::setPath($dir);
		\PHPPE\View::assign("dir",$dir);
		\PHPPE\View::css("test.css");
		\PHPPE\View::css("test2.css");
		\PHPPE\View::css(url("css","test.css"));
		$this->assertNotEmpty(\PHPPE\View::css(),"CSS");
		\PHPPE\View::jslib("test.js","testjs();");
		\PHPPE\View::jslib("test2.js","testjs();");
		\PHPPE\View::jslib(url("js","test.js"),"testjs();");
		$this->assertNotEmpty(\PHPPE\View::jslib(),"JSLib");
		\PHPPE\View::js("some()","thing();");
		\PHPPE\View::js("some2()","thing();",true);
		\PHPPE\View::menu("a","b");
		\PHPPE\View::menu("c",["d"=>"e"]);
		$this->assertNotEmpty(\PHPPE\View::menu(),"Menu");

		$o = \PHPPE\Core::$core->output;
		\PHPPE\Core::$core->output = "ncurses";
		$this->assertEquals(
			"module-D: message",
			trim(\PHPPE\View::e('D',"module","message")),
			"error string #1");
		\PHPPE\Core::$core->output = "html";
		$this->assertEquals(
			"<span style='background:#F00000;color:#FEA0A0;padding:3px;'>E-module:&nbsp;[&quot;message&quot;]</span>",
			trim(\PHPPE\View::e('E',"module",["message"])),
			"error string #2");
		\PHPPE\Core::$core->output = $o;

		$this->assertEquals(
			"aaa\n<!include test2>\nbbb\n",
			\PHPPE\View::get("test1"),
			"Raw template file");

		\PHPPE\DS::close();
		$ds = new \PHPPE\DS("sqlite::memory:");
		\PHPPE\DS::exec("UPDATE views SET css='[\"a.css\"]' WHERE id='simple'");
		$this->assertNotEmpty(\PHPPE\View::get("simple"),"Raw template db");
		\PHPPE\DS::exec("UPDATE views SET css='' WHERE id='simple'");
	}

	public function testTemplater()
	{
		$this->assertNotFalse(strpos(\PHPPE\View::template("test1"),"TOOMNY"),"Template #1");
		$this->assertNotEmpty(\PHPPE\View::template("404"),"Template #2");
		$this->assertEmpty(\PHPPE\View::template("nosuchtemplate"),"Template #3");

		$obj=new \stdClass;
		$obj->dynamicname="dyntest";
		$obj->arr=["a","b","c"];
		$obj->arr2=[["A"=>"a"],["A"=>"b"],["A"=>"c"]];
		$obj->now=time();
		$obj->singlearr=["a"];
		$obj->arrs=unserialize('a:2:{i:0;a:2:{s:2:"id";s:5:"frame";s:4:"name";s:7:"Default";}i:1;a:2:{s:2:"id";s:6:"simple";s:4:"name";s:6:"Simple";}}');
		$obj->objs=unserialize('a:2:{i:0;O:8:"stdClass":2:{s:2:"id";s:5:"frame";s:4:"name";s:5:"Frame";}i:1;O:8:"stdClass":2:{s:2:"id";s:6:"simple";s:4:"name";s:6:"Simple";}}');
		$obj->spec[0]=new \stdClass; $obj->spec[0]->id=1; $obj->spec[0]->name=[1,2,3];
		$obj->emptyStr="";

		\PHPPE\View::assign("obj",$obj);
		\PHPPE\View::assign("app",$obj);
		\PHPPE\View::assign("core",\PHPPE\Core::$core);
		$this->assertEquals("dynamic name",\PHPPE\View::_t("<!include dynamicname>"),"Dynamic name");
		$this->assertEquals("<!-- not removed -->test",\PHPPE\View::_t("<!-- not removed -->test"),"Comment");
		$this->assertEquals("<!app>",\PHPPE\View::_t("<!app>"),"App tag");
		$this->assertEquals(\PHPPE\Core::$core->now."",\PHPPE\View::_t("<!=core.now>"),"Expression #1");
		$this->assertNotFalse(strpos(\PHPPE\View::_t("<!=core.now=1>"),"INP"),"Expression #2");
		$this->assertEquals("aa\"bb",\PHPPE\View::_t('<!="aa\"bb">'),"Expression #3");
		$this->assertFalse(strpos(\PHPPE\View::_t("<!=sprintf('aaabbb')>"),"BADFNC"),"Expression #4");
		\PHPPE\Core::$core->allowed=["number_format"];
		$this->assertNotFalse(strpos(\PHPPE\View::_t("<!=sprintf('aaabbb')>"),"BADFNC"),"Expression #5");
		\PHPPE\Core::$core->allowed=[];
		$this->assertEquals("aa bb",\PHPPE\View::_t('<!L aa_bb>'),"Language");
		$this->assertNotFalse(strpos(\PHPPE\View::_t("<!notag>"),"UNKTAG"),"Unknown tag");

		\PHPPE\Core::$l['min']="m";
		\PHPPE\Core::$l['mins']="ms";
		\PHPPE\Core::$l['hour']="h";
		\PHPPE\Core::$l['hours']="hs";
		\PHPPE\Core::$l['day']="d";
		\PHPPE\Core::$l['days']="ds";
		$this->assertEquals("0 m",\PHPPE\View::_t('<!difftime 1>'),"Difftime #1");
		$this->assertEquals("1 m",\PHPPE\View::_t('<!difftime 60>'),"Difftime #2");
		$this->assertEquals("2 ms",\PHPPE\View::_t('<!difftime 120>'),"Difftime #3");
		$this->assertEquals("1 h",\PHPPE\View::_t('<!difftime 3600>'),"Difftime #4");
		$this->assertEquals("1 h, 1 m",\PHPPE\View::_t('<!difftime 3660>'),"Difftime #5");
		$this->assertEquals("2 hs, 2 ms",\PHPPE\View::_t('<!difftime 3660*2>'),"Difftime #6");
		$this->assertEquals("1 d",\PHPPE\View::_t('<!difftime 3600*24>'),"Difftime #7");
		$this->assertEquals("2 ds",\PHPPE\View::_t('<!difftime 3600*48>'),"Difftime #8");
		$this->assertEquals("2 ds",\PHPPE\View::_t('<!difftime 3600*50>'),"Difftime #9");
		$this->assertEquals("- 2 ms",\PHPPE\View::_t('<!difftime -120>'),"Difftime #10");
		$this->assertEquals("1 m",\PHPPE\View::_t('<!difftime 3660 3600>'),"Difftime #11");
		$this->assertEquals("-",\PHPPE\View::_t('<!difftime obj.emptyStr 10>'),"Difftime #12");

		$this->assertEquals("aaa",\PHPPE\View::_t("<!template>aaa<!/template>"),"Reentrant #1");
		$this->assertEquals(\PHPPE\Core::$core->now,\PHPPE\View::_t("<!template><%=core.now><!/template>"),"Reentrant #2");

		$this->assertEquals("011a120b231c",\PHPPE\View::_t("<!foreach arr><!=KEY><!=IDX><!=ODD><!=VALUE><!/foreach>"),"Foreach #1");
		$this->assertEquals("abc",\PHPPE\View::_t("<!foreach obj.arr2><!=A><!/foreach>"),"Foreach #2");
		$this->assertNotFalse(strpos(\PHPPE\View::_t("!foreach obj.arr2><!=A><!/foreach>"),"UNCLS"),"Foreach #3");
		$this->assertEquals(
			"0a0b0c1a1b1c2a2b2c",
			\PHPPE\View::_t("<!foreach arr><!foreach arr2><!=parent.KEY><!=A><!/foreach><!/foreach>"),
			"Foreach #4");
		$this->assertEquals(
			"01101a00102b01103c11001a10002b11003c21101a20102b21103c01111a00112b01113c11011a10012b11013c21111a20112b21113c01121a00122b01123c11021a10022b11023c21121a20122b21123c",
			\PHPPE\View::_t("<!foreach arr><!foreach arr><!foreach arr2><!=parent.KEY><!=ODD><!=parent.ODD><!=parent.parent.KEY><!=IDX><!=A><!/foreach><!/foreach><!/foreach>"),
			"Foreach #5");
		$this->assertEquals("a0framea0framea1simplea1simple",\PHPPE\View::_t("<!foreach obj.arrs><!foreach VALUE>a<!=parent.KEY><!=parent.id><!/foreach><!/foreach>"),"Foreach #6");
		$this->assertEquals("frameFramesimpleSimple",\PHPPE\View::_t("<!foreach obj.objs><!=id><!=name><!=none><!/foreach>"),"Foreach #7");

		$this->assertEquals("A",\PHPPE\View::_t("<!if true>A<!else>B<!/if>"),"If true");
		$this->assertEquals("B",\PHPPE\View::_t("<!if false>A<!else>B<!/if>"),"If false");

		$this->assertEquals(1,preg_match("|<form name='a' action='[^']+' class='form-vertical' method='post' enctype='multipart/form-data'><input type='hidden' name='MAX_FILE_SIZE' value='[0-9]+'><input type='hidden' name='pe_s' value='[a-fA-F0-9]*'><input type='hidden' name='pe_f' value='a'>|ims",
			\PHPPE\View::_t("<!form a>")),
			"Form #1");

		$this->assertEquals(1,preg_match("|<form name='a' action='([^']+)' class='form-vertical' method='post' enctype='multipart/form-data'><input type='hidden' name='MAX_FILE_SIZE' value='[0-9]+'><input type='hidden' name='pe_s' value='[a-fA-F0-9]*'><input type='hidden' name='pe_f' value='a'>|ims",
			\PHPPE\View::_t("<!form a form-vertical b/c>"),$m),
			"Form #2");
		$this->assertEquals("b/c/",substr($m[1],-4),"Form #2 url");

		$this->assertEquals(1,preg_match("|<form role='form' name='a' action='([^']+)' class='form-vertical' method='post' enctype='multipart/form-data' onsubmit=\"d\(\)\"><input type='hidden' name='MAX_FILE_SIZE' value='[0-9]+'><input type='hidden' name='pe_s' value='[a-fA-F0-9]*'><input type='hidden' name='pe_f' value='a'>|ims",
			\PHPPE\View::_t("<!form a - b/c d() role>"),$m),
			"Form #3");
		$this->assertEquals("b/c/",substr($m[1],-4),"Form #3 url");

		$this->assertEquals(1,preg_match("|<form name='a' action='([^']+)' class='form-vertical' method='post' enctype='multipart/form-data' onsubmit=\"d\(\)\"><input type='hidden' name='MAX_FILE_SIZE' value='[0-9]+'><input type='hidden' name='pe_s' value='[a-fA-F0-9]*'><input type='hidden' name='pe_f' value='a'>|ims",
			\PHPPE\View::_t("<!form a - - d()>")),
			"Form #4");

		$_SESSION['pe_c']=$_SESSION['pe_e']=false;
		$this->assertEquals("show1",\PHPPE\View::_t('<!var test1 test>'),"var tag (pe_c=0,pe_e=0,show)");
		$this->assertEquals("show1",\PHPPE\View::_t('<!widget test1 test>'),"widget tag (pe_c=0,pe_e=0,show)");
		$this->assertEquals("edit1",\PHPPE\View::_t('<!field test1 test>'),"field tag (pe_c=0,pe_e=0,edit)");

		$_SESSION['pe_c']=true;
		$this->assertEquals("show1",\PHPPE\View::_t('<!var test1 test>'),"var tag (pe_c=1,pe_e=0,show)");
		$this->assertEquals("edit1",\PHPPE\View::_t('<!widget test1 test>'),"widget tag (pe_c=1,pe_e=0,edit)");
		$this->assertEquals("edit1",\PHPPE\View::_t('<!field test1 test>'),"field tag (pe_c=1,pe_e=0,edit)");

		$_SESSION['pe_c']=false;
		$_SESSION['pe_e']=true;
		$this->assertEquals("edit1",\PHPPE\View::_t('<!var test1 test>'),"var tag (pe_c=0,pe_e=1,edit)");
		$this->assertEquals("show1",\PHPPE\View::_t('<!widget test1 test>'),"widget tag (pe_c=0,pe_e=1,show)");
		$this->assertEquals("edit1",\PHPPE\View::_t('<!field test1 test>'),"field tag (pe_c=0,pe_e=1,edit)");

		$_SESSION['pe_c']=$_SESSION['pe_e']=false;
		$this->assertNotFalse(strpos(\PHPPE\View::_t('<!field *text test>'),"required"),"field tag (required)");
		\PHPPE\Core::$user->id=1;
		$this->assertEmpty(\PHPPE\View::_t('<!var @noacl test1 test>'),"var tag (noacl)");
		$this->assertEmpty(\PHPPE\View::_t('<!field @noacl test1 test>'),"field tag (noacl)");
		\PHPPE\Core::$user->id=-1;
		$this->assertEquals("show1",\PHPPE\View::_t('<!var @noacl test1 test>'),"var tag (noacl admin)");
		$this->assertEquals("edit1",\PHPPE\View::_t('<!field @noacl test1 test>'),"field tag (noacl admin)");

        $u = \PHPPE\Core::$user->id;
        \PHPPE\Core::$user->id=0;
		$this->assertEquals("",\PHPPE\View::_t('<!cms test1 test>'),"cms tag #1");
		$this->assertEquals("show1",\PHPPE\View::_t('<!cms *test1 test>'),"cms tag #2");
        \PHPPE\Core::$user->id=-1;
        $app = new \PHPPE\Content;
        \PHPPE\View::assign("app",$app);
		$this->assertNotFalse(strpos(\PHPPE\View::_t('<!cms test1 test>'),"cms_edit"),"cms tag #3");
        \PHPPE\Core::$user->id=$u;

		$this->assertEquals("show3",\PHPPE\View::_t('<!var test3 test>'), "Addon init");

		\PHPPE\Core::$l['dateformat']="Y-m-d";
		\PHPPE\Core::$l['testdate']="2001-02-03 04:05:06";
		date_default_timezone_set( "UTC" );

		$this->assertEquals("2001-02-03",\PHPPE\View::_t('<!date L("testdate")>'),"Date #1");
		$this->assertEquals("2001-02-03 04:05:06",\PHPPE\View::_t('<!time L("testdate")>'),"Time #1");
		$this->assertEquals("1970-01-01",\PHPPE\View::_t('<!date 1>'),"Date #2");
		$this->assertEquals(1,preg_match("/1970-01-01 [0-9]+:00:01/",\PHPPE\View::_t("<!time 1>")),"Time #2");

		\PHPPE\Core::$core->runlevel=0;
		$this->assertEmpty(\PHPPE\View::_t('<!dump obj>'),"Dump #1");
		\PHPPE\Core::$core->runlevel=1;
		$this->assertEquals(1,preg_match("/stdClass Object/",\PHPPE\View::_t("<!dump obj>")),"Dump #2");
		\PHPPE\Core::$core->runlevel=2;
		$d=\PHPPE\View::_t("<!dump obj>");
		$this->assertEquals(1,
			preg_match("/string\(7\) \"dyntest\"/",$d)||
			preg_match("/var\-dump/",$d),"Dump #3");
		$this->assertEquals(0,preg_match("/pe_u/",\PHPPE\View::_t("<!dump _SESSION>")),"Dump #4");

		\PHPPE\Core::$core->noframe=false;
		\PHPPE\Core::$core->output="html";
		\PHPPE\Core::$user->id=-1;
		$this->assertEquals(1,preg_match("/bbb/",
		\PHPPE\View::generate("test1","t_".sha1(\PHPPE\Core::$core->base."_test1"))),"Generate");

		\PHPPE\Cache::$mc = null;

		ob_start();
		$txt="TEST";
		@\PHPPE\View::output($txt);
		$this->assertEquals(1,preg_match("/TEST/",ob_get_clean()),"Output #1");

		\PHPPE\Cache::$mc=new \PHPPE\Cache\Files("files");
		\PHPPE\Core::$core->nocache=false;
		\PHPPE\Core::$core->noaggr=false;
		$obj->favicon="favicon.png";
		$_SESSION['pe_ls']=["en"=>"en"];

		\PHPPE\View::menu("aaa","aaa/bbb");
		\PHPPE\View::menu("ccc",["ccc"=>"ccc/ddd"]);

		\PHPPE\Core::$core->url = "aaa";

		//save it to cache
        $sha = \PHPPE\Core::$core->base . "aaa/".\PHPPE\Core::$user->id . "/". \PHPPE\Core::$client->lang;
		\PHPPE\Cache::set("c_".sha1($sha."_css"),"");
		\PHPPE\Cache::set("c_".sha1($sha."_js"),"");
		ob_start();
		@\PHPPE\View::output($txt);
		$this->assertEquals(1,preg_match("/div class='menu'/",ob_get_clean()),"Output #2");
		//read from cache
		ob_start();
		@\PHPPE\View::output($txt);
		$this->assertEquals(1,preg_match("/div class='menu'/",ob_get_clean()),"Output #3");

		\PHPPE\Core::$core->url = "aaa/ccc";
		\PHPPE\Core::$core->app = "aaa";
		ob_start();
		@\PHPPE\View::output($txt);
		$this->assertEquals(1,preg_match("/<\/span><span class='menu_a'>/",ob_get_clean()),"Active menu #1");

		\PHPPE\Core::$core->app = "ccc";
		ob_start();
		@\PHPPE\View::output($txt);
		$this->assertEquals(1,preg_match("/class='menu_a' onclick/",ob_get_clean()),"Active menu #2");

	}
}
?>
