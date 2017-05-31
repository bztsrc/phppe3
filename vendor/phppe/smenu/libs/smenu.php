<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2017
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
 * @file vendor/phppe/smenu/libs/smenu.php
 * @author bzt
 * @date 31 May 2017
 * @brief
 */

namespace PHPPE;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\DS;

class smenu
{
	public function ctrl($app, $method)
	{
		if(Core::$core->template=="smenu") {
			$appObj = View::getval("app");
			if(empty($appObj->smenu))
				$appObj->smenu = DS::query("*", "smenu_list", "list_id=?", "", "ordering", 0, 0, [Core::$core->url]);
			View::css("smenu.css");
			View::jslib("smenu.js","pe.smenu.init(smenu_data);");
		}
	}
}
