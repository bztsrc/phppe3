<?php
/**
 * Controller for tests
 */
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class Tests extends \PHPPE\Ctrl {
	public $testCases;
	public $test_val2arr="a:b:c";
	public $test_arr=["a","b","c"];
	public $test_arr2=[["A"=>"a"],["A"=>"b"],["A"=>"c"]];
	public $iscached="";
	public $dynamictemplate="dyntest";
	public $_favicon="images/phppeicon.png";

	function __construct()
	{
		PHPPE::$core->nocache = true;
		//get test cases
		$this->testCases = array();
		foreach(glob("vendor/phppe/*/tests/*.php") as $f) {
		    list($k)=explode(".",basename($f));
		    $d=explode(";",@file_get_contents("data/tests/".$k));
		    $this->testCases[$k]=[
			'name' => ucfirst(trim(str_replace("_", " ",substr($k,4)))),
			'time' => @filemtime("data/tests/".$k),
			'avg' => !empty($d[0])?$d[0]+0.0:0.0,
			'asserts' => !empty($d[1])?$d[1]+0:0,
			'ret' => L(isset($d[2])?($d[2]=='1'?"OK":(!$d[2]?"FAIL":$d[2])):"None"),
			'color' => !isset($d[2])||($d[2]&&$d[2]!='1')?"blue":($d[2]?"green":"red")
		    ];
		}
		//this is required by output cache test
		if(PHPPE::$core->item=="cachetest" && empty($_REQUEST['skipcache'])) {
		    PHPPE::$core->nocache = false;
		}
	}

	function action_run($item)
	{

		//run a specific test. If not exists, show error
		if(!empty($item) && empty($this->testCases[$item])) {
		    PHPPE::$core->template="404";
		    PHPPE::$core->app=$item;
		    return;
		}
		$start=microtime(true);
		header("Content-type:text/plain");
		@mkdir("data/tests");
		//do all tests in lack of a specified one
		$numasserts=0;
		$tests=empty($item)?array_keys($this->testCases):array($item);
		foreach($tests as $key=>$test) {
			if($key) echo(str_repeat("-",80)."\n");
			echo(L("Running test").": ".$test."\n\n");
			flush();
			$t=microtime(true); $asserts=0;
			$ret=include("vendor/phppe/Developer/tests/".$test.".php");
			if($ret===true && preg_match_all("/return/",file_get_contents("vendor/phppe/Developer/tests/".$test.".php"),$m)) {
			    $asserts=count($m[0])-1;
			    $numasserts+=$asserts;
			}
			$t=microtime(true)-$t;
			file_put_contents("data/tests/".$test,sprintf("%0.4f",round($this->testCases[$test]['avg']?($this->testCases[$test]['avg']+$t)/2:$t,4)).";".$asserts.";".$ret);
			@chmod("data/tests/".$test,0666);
			echo("\n".L("Result").": ".L($ret===true?"OK":($ret===false?"FAIL":$ret))."\n");
			flush();
		}
		die("\n--------- ".sprintf(L("Processed %d test(s) in %.5f sec."),$numasserts,microtime(true)-$start)." ---------\n");
	}

	function action($item)
	{
		if(PHPPE::$core->output!="html") {
			printf("%-20s%-9s%-7s%-21s%s\n------------------- -------- ------ -------------------- ----------\n",L("Test boundle"),L("Avg.time"),L("#Tests"),L("Last run"),L("Result"));
			foreach($this->testCases as $t)
				printf("%-20s%0.4fs  %6d %s  %s\n",$t['name'],$t['avg'],$t['asserts'],date("Y-m-d H:i:s",$t['time']),$t['ret']);
			die();
		}
		PHPPE::js("runtest(t)", "document.getElementById('testsdiv').style.display='none';document.getElementById('loadingdiv').style.display='block';document.location.href=t;");
	}

	//actions for http test cases
	function action_http($item)
	{
		switch($item) {
			//return POST array
			case "post": {
				die(json_encode($_POST));
			}
			//force time out
			case "timeout": {
				sleep(3);
				die("TimedOut");
			}
			//redirect url
			case "redirect": {
				PHPPE::redirect(url()."redirect2");
			}
			case "redirect2": {
				die("Redirected");
			}
			//cookie handler test
			case "cookie": {
				setcookie("test","1");
				PHPPE::redirect(url()."cookie2");
			}
			case "cookie2": {
				if(!isset($_COOKIE['test'])) die();
				setcookie("test","2");
				PHPPE::redirect(url()."cookie3");
			}
			case "cookie3": {
				if($_COOKIE['test']!="2") die();
				die("OK");
			}
			//language code pass over
			case "language": {
				die($_SERVER['HTTP_ACCEPT_LANGUAGE']);
			}
			//automatic carridge return removal for text
			case "cr1": {
				die("C\rR");
			}
			case "cr2": {
				header("Content-type: image/png");
				die("C\rR");
			}
			//for output cache test
			case "cachetest": {
				PHPPE::$core->template="cachetest";
				if(!empty($_REQUEST['skipcache']))
				    $this->iscached="NOCACHE";
				break;
			}
			//default test case
			default: die("OK ".$item);
		}
	}

	//route filter tests
	function action_httppost($item)
	{
		die("POST");
	}
	function action_httpget($item)
	{
		die("GET");
	}
}
?>