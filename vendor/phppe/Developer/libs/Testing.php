<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016 bzt
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *   <http://www.gnu.org/licenses/>
 *
 * @file vendor/phppe/Developer/libs/Testing.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief Utilities to run unit tests
 */
namespace PHPPE;

class Testing extends \PHPPE\Extension {
	public $testCases;
    public $eventTestRun = false;
/**
 * Get all tests inside phppe
 *
 * @return array of test suites
 */
	static function getTests()
	{
		//get test cases
		$arr = array();
		foreach(glob("vendor/phppe/*/tests/*.php") as $f) {
		    list($k)=explode(".",basename($f));
			$d=file_get_contents($f);
			preg_match("/class ([^\ ]+)/",$d,$c);
			preg_match_all("/->assert/",$d,$m);
			$d=explode(";",@file_get_contents("data/tests/".$k));
			$arr[$k]=[
				'fn' => $f,
				'name' => $c[1],
				'time' => @filemtime("data/tests/".$k),
				'avg' => !empty($d[0])?floatval($d[0]):0.0,
				'executed' => !empty($d[1])?intval($d[1]):0,
				'asserts' => count($m[0]),
				'ret' => L(isset($d[2])?($d[2]=='1'?"OK":(!$d[2]?"FAIL":$d[2])):"None"),
				'color' => !isset($d[2])||($d[2]&&$d[2]!='1')?"blue":($d[2]?"green":"red")
			];
		}
		ksort($arr);
		return $arr;
	}

/**
 * Execute all or a specific test suite
 *
 * @param array returned by getTests()
 * @param name of the test suite
 * @return null if test not found, true on success, false otherwise
 */
	static function doTests($arrTests=[],$item="")
	{
        $result = true;

        //PHPUnit compatibility layer
        if(!class_exists("PHPUnit_Framework_TestCase"))
            include(__DIR__."/phpunit.php");

        if(empty($arrTests))
            $arrTests = self::getTests();

		//run a specific test. If not exists, show error
		if(!empty($item) && empty($arrTests[$item]))
            return null;

		$start=microtime(true);
		@mkdir("data/tests");
		//do all tests in lack of a specified one
		$numasserts=0;
		$tests=empty($item)?array_keys($arrTests):array($item);
		foreach($tests as $key=>$test) {
			if($key) echo(str_repeat("-",80)."\n");
			flush();
			include($arrTests[$test]['fn']);
			$cls=get_declared_classes();
			$cls=end($cls);
			echo(L("Running test").": ".$cls."\n\n");
			$t=microtime(true);
			$obj=new $cls;
			$ret=$obj->doTests();
			$asserts=$obj->numAsserts;
			$numasserts+=$asserts;
			$t=microtime(true)-$t;
			file_put_contents("data/tests/".$test,sprintf("%0.4f",round($arrTests[$test]['avg']?($arrTests[$test]['avg']+$t)/2:$t,4)).";".$asserts.";".$ret);
			@chmod("data/tests/".$test,0660);
			echo("\n".L("Result").": ".L($ret===true?"OK":($ret===false?"FAIL":$ret))."\n");
            if($ret === false)
                $result = false;
			flush();
		}
		echo("\n--------- ".sprintf(L("Processed %d test(s) in %.5f sec."),$numasserts,microtime(true)-$start)." ---------\n");
        return $result;
	}

    function eventTest()
    {
        $this->eventTestRun = true;
    }
}
?>
