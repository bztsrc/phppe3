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
 * @file vendor/phppe/core/ctrl/scripts.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief Example CLI action handlers
 */
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class Scripts extends \PHPPE\Ctrl {

	function action_daily($item="")
	{
	}

	function action_hourly($item="")
	{
	}

	function action($item="")
	{
	}

	//! call this frequently if you use file caching
	function action_cachegc($item="") {
		if(!empty(PHPPE::mc()) && method_exists(PHPPE::mc(), "cleanUp") ){
			PHPPE::mc()->cleanUp();
		}
	}
}
?>