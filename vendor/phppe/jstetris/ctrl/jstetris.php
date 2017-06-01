<?php
/**
 * @file vendor/phppe/jstetris/ctrl/jstetris.php
 * @author bzt
 * @date 01 Jun 2017
 * @brief JsTetris Controller
 */

namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\Http;

class jstetris
{
/**
 * Action handler
 */
	function action($item)
	{
	    View::css("jstetris.css");
	    View::jslib("jstetris.js",
		"var tetris = new Tetris();tetris.unit = 14;tetris.areaX = 12;tetris.areaY = 22;");
	}
}
