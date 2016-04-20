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
 * @file vendor/phppe/DataLibrary/01_DataLibrary.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief Attachment upload and managing
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

/**
 * Main class
 *
 */
class DataLibrary
{
    private static $self;
/**
 * Register Chart
 *
 * @param cfg not used
 */
	function init($cfg) {
		//register module
		PHPPE::lib("DataLibrary","Data Library");
		//save singleton instance
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
