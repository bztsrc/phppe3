<?php
/**
 * Controller for tests
 */
namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\Http;
use PHPPE\Cache;
use PHPPE\Testing;

class Developer {
    static $cli=["tests", "tests run"];

	public $testCases;
	public $test_val2arr="a:b:c";
	public $test_arr=["a","b","c"];
	public $test_arr2=[["A"=>"a"],["A"=>"b"],["A"=>"c"]];
	public $iscached="";
	public $dynamictemplate="dyntest";
	public $_favicon="images/phppeicon.png";

	function __construct()
	{
		Core::$core->nocache = true;
		//this is required by output cache test
		if(Core::$core->item=="cachetest" && empty($_REQUEST['skipcache'])) {
		    Core::$core->nocache = false;
			Core::$core->template="cachetest";
			if(empty(Cache::$mc))
				Cache::$mc=new \PHPPE\Cache\Files("files");
		}
        $this->testCases = Testing::getTests();
	}

	function run($item)
	{
        Http::mime("text/plain",false);

        $ret = Testing::doTests($this->testCases, $item);

		if($ret===null) {
            die(sprintf(L("Test %s not found"),$item)."\n");
		} else
            die;
	}

	function action($item)
	{
        //make lang happy: L("OK") L("FAIL") L("SKIP") L("None")
		if(Core::$core->output!="html") {
			printf(chr(27)."[96m%-20s%-9s%-9s%-21s%s\n------------------- -------- -------- -------------------- ----------".chr(27)."[0m\n",L("Test boundle"),L("Avg.time"),L("#Tests"),L("Last run"),L("Result"));
			foreach($this->testCases as $t)
				printf("%-20s".chr(27)."[90m%0.4fs  %3d /%3d %s  ".chr(27)."[%sm%s".chr(27)."[0m\n",$t['name'],$t['avg'],$t['executed'],$t['asserts'],date("Y-m-d H:i:s",$t['time']),$t['ret']=="OK"?"92":($t['ret']=="None"?"96":"91"),L($t['ret']));
			die();
		}
		View::js("runtest(t)", "document.getElementById('testsdiv').style.display='none';document.getElementById('loadingdiv').style.display='block';document.location.href='".url()."run/'+t;");
        View::css("test.css");
	}

	//actions for http test cases
	function http($item)
	{
		switch($item) {
			//return POST array
			case "post": {
				die(json_encode($_POST));
			}
			//force time out
			case "timeout": {
				sleep(1.001);
				die("");
			}
			//redirect url
			case "redirect": {
				\PHPPE\Http::redirect(url()."redirect2");
			}
			case "redirect2": {
				die("Redirected");
			}
			//cookie handler test
			case "cookie": {
				setcookie("test","1");
				\PHPPE\Http::redirect(url()."cookie2");
			}
			case "cookie2": {
				if(!isset($_COOKIE['test'])) die();
				setcookie("test","2");
				\PHPPE\Http::redirect(url()."cookie3");
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
				\PHPPE\Core::$core->template="cachetest";
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
