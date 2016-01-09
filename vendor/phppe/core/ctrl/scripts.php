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
 * @file vendor/phppe/core/ctrl/scripts.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief Example CLI action handlers
 */
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class Scripts extends \PHPPE\Ctrl {

	//! call this from cli as: php public/index.php scripts namedscript
	function action_namedscript($item="")
	{
		//! if you don't need a view (likely), call die() in action handler
		die("specific CLI handler, namedscript\n");
	}

	//! call this from cli as: php public/index.php scripts *
	function action($item="")
	{
		echo("common CLI handler, action:'".PHPPE::$core->action."' item:'".$item."'\n");
	}

}
?>