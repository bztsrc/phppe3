<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016
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
 * @file vendor/phppe/DBA/libs/DBA.php
 * @author bzt
 * @date 29 Sep 2016
 * @brief DataBase Administrator
 */

namespace PHPPE;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Registry as Registry;
use PHPPE\DB as DB;

class DBA
{
    private static $self;

	public function __construct()
	{
	}

	function init($config) {
	}

	public function diag()
	{
	}

	public function route($app, $method)
	{
	}

	public function ctrl($app, $method)
	{
	}

	public function view($html)
	{
		return $html;
	}

	public function stat()
	{
	}

	public function cronMinute($item)
	{
	}

	public function cronQuoterly($item)
	{
	}

	public function cronHourly($item)
	{
	}

}
