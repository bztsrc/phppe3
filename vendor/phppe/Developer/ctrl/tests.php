<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class Tests extends \PHPPE\Ctrl {
	public $testCases;
	public $test_val2arr="a:b:c";
	public $test_arr=["a","b","c"];
	public $test_arr2=[["A"=>"a"],["A"=>"b"],["A"=>"c"]];
	public $iscached="";
	public $dynamictemplate="dyntest";

	function __construct()
	{
		PHPPE::$core->nocache = true;
		//get test cases
		$this->testCases = array();
		foreach(glob("vendor/phppe/Developer/tests/*.php") as $f) {
		    list($k)=explode(".",basename($f));
		    $d=explode(";",@file_get_contents("data/tests/".$k));
		    $this->testCases[$k]=[
			'name' => ucfirst(trim(str_replace("_", " ",substr($k,4)))),
			'time' => @filemtime("data/tests/".$k),
			'avg' => $d[0]+0.0,
			'ret' => isset($d[1])?($d[1]?L("OK"):L("FAIL")):L("None"),
			'color' => !isset($d[1])?"blue":($d[1]?"green":"red")
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
		header("Content-type:text/plain");
		@mkdir("data/tests");
		//do all tests in lack of a specified one
		$tests=empty($item)?array_keys($this->testCases):array($item);
		foreach($tests as $key=>$test) {
			if($key) echo(str_repeat("-",80)."\n");
			echo("Running test: ".$test."\n\n");
			flush();
			$t=microtime(true);
			$ret=include("vendor/phppe/Developer/tests/".$test.".php");
			$t=microtime(true)-$t;
			file_put_contents("data/tests/".$test,sprintf("%0.4f",round($this->testCases[$test]['avg']?($this->testCases[$test]['avg']+$t)/2:$t,4)).";".intval($ret));
			@chmod("data/tests/".$test,0666);
			echo("\nResult: ".($ret?"OK":"FAIL")."\n");
			flush();
		}
		die();
	}

	function action($item)
	{
		if(PHPPE::$core->output!="html") {
			printf("%-20s%-9s%-21s%s\n",L("Test name"),L("Avg.time"),L("Last run"),L("Result"));
			foreach($this->testCases as $t)
				printf("%-20s%0.4fs  %s  %s\n",$t['name'],$t['avg'],date("Y-m-d H:i:s",$t['time']),$t['ret']);
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