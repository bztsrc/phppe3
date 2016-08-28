<?php
/**
 * @file vendor/phppe/og/libs/og.php
 * @author bzt
 * @date 28 Aug 2016
 * @brief
 */

use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Http as Http;
ude PHPPE\DB as DB;

class og extends PHPPE\Extension
{
	public function ctrl($app, $method)
	{
	    $appObj = Core::getval("app");
	    
	    print_r($appObj);die("a");
	}
}
