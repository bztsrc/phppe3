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
 * @file vendor/phppe/Developer/libs/phpunit.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief A very simple PHPUnit implementation
 */

namespace PHPUnit\Framework;

class TestCase
{
	public $numAsserts=0;

/**
 * Run all tests defined in testCase
 *
 * @return true, false, or 'SKIP'
 */
	public function doTests()
	{
		try {
			foreach(get_class_methods($this) as $mthd)
			{
				if(substr($mthd,0,4)=="test")
					$this->$mthd();
			}
	    }catch(\Exception $e){
			if($e->getMessage()=="SKIP")
				return "SKIP";
			elseif($e->getMessage()=="FAIL")
				return false;
			else {
				$d=$e->getTrace()[0];
				echo("\nEXCEPTION ".get_class($e)." ".$e->getFile().":".$e->getLine().": " . $e->getMessage().
					"\ncalled at ".basename($d['file']).":".$d['line']."\n");
			}
			return false;
		}
		return true;
	}

	public function markTestSkipped()
	{
		throw new \Exception("SKIP");
	}

/**
 * common assertion handler
 */
	public function assert($ret,$message="")
	{
		if(!empty($message))
			echo($message.": ");
		$this->numAsserts++;
		if(!$ret)
		{
			$d=debug_backtrace();
			echo("FAIL (".$d[1]['function'].", ".basename($d[1]['file']).":".$d[1]['line']." ".$d[2]['function'].")\n");
			throw new \Exception("FAIL");
		}
		else
			echo("OK\n");
	}

/**
 * specific assertions
 */
	public function assertEquals($expected, $actual, $message = '')
	{
		$this->assert($expected==$actual, $message);
	}

	public function assertNotEquals($expected, $actual, $message = '')
	{
		$this->assert($expected!=$actual, $message);
	}

	public function assertEmpty($actual, $message = '')
	{
		$this->assert(empty($actual), $message);
	}

	public function assertNotEmpty($actual, $message = '')
	{
		$this->assert(!empty($actual), $message);
	}

	public function assertGreaterThan($expected, $actual, $message = '')
	{
		$this->assert($expected<$actual, $message);
	}

	public function assertGreaterThanOrEqual($expected, $actual, $message = '')
	{
		$this->assert($expected<=$actual, $message);
	}

	public function assertLessThan($expected, $actual, $message = '')
	{
		$this->assert($expected>$actual, $message);
	}

	public function assertLessThanOrEqual($expected, $actual, $message = '')
	{
		$this->assert($expected>=$actual, $message);
	}

	public function assertTrue($actual, $message = '')
	{
		$this->assert($actual===true, $message);
	}

	public function assertNotTrue($actual, $message = '')
	{
		$this->assert($actual!==true, $message);
	}

	public function assertFalse($actual, $message = '')
	{
		$this->assert($actual===false, $message);
	}

	public function assertNotFalse($actual, $message = '')
	{
		$this->assert($actual!==false, $message);
	}

	public function assertNull($actual, $message = '')
	{
		$this->assert($actual===null, $message);
	}

	public function assertNotNull($actual, $message = '')
	{
		$this->assert($actual!=null, $message);
	}

	public function assertInstanceOf($expected, $actual, $message = '')
	{
		$this->assert(is_a($actual,$expected), $message);
	}

	public function assertNotInstanceOf($expected, $actual, $message = '')
	{
		$this->assert(!is_a($actual,$expected), $message);
	}

	public function assertInternalType($expected, $actual, $message = '')
	{
		$this->assert(gettype($actual)==$expected, $message);
	}

	public function assertNotInternalType($expected, $actual, $message = '')
	{
		$this->assert(gettype($actual)!=$expected, $message);
	}

	public function assertFileExists($actual, $message = '')
	{
		$this->assert(file_exists($actual), $message);
	}

	public function assertFileNotExists($actual, $message = '')
	{
		$this->assert(!file_exists($actual), $message);
	}

}
