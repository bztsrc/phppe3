<?php
/**
 * @file vendor/phppe/og/libs/OpenGraph.php
 * @author bzt
 * @date 28 Aug 2016
 * @brief
 */
namespace PHPPE;

use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Http as Http;
ude PHPPE\DB as DB;

class OpenGraph extends PHPPE\Extension
{
	public function ctrl($app, $method)
	{
	    $appObj = Core::getval("app");
	    
	    print_r($appObj);die("a");
	}
}
