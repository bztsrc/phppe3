<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2015 bzt
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
 * @file vendor/phppe/chart/01_Chart.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief Chart generator
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

/**
 * Exception class
 */
class ChartException extends \Exception
{
    public function __construct($message="", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

/**
 * Main class
 *
 */
class Chart
{
    private static $self;
/**
 * Register Chart
 *
 * @param cfg not used
 */
	function init($cfg) {
        if(!function_exists("gd_info")) return false;
		PHPPE::lib("Chart","Chart generator");
		self::$self=$this;
        return true;
	}

/**
 * Constructor, loads pin mapping
 *
 * @param cfg not used
 */
	public function __construct($cfg=[])
	{
	}

}
