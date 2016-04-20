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
 * @file vendor/phppe/LDAP/01_LDAP.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief Provides LDAP or Active Directory authentication
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

/**
 * Main class
 *
 */
class LDAP
{
    private static $self;
/**
 * Register LDAP
 *
 * @param cfg ldap srvers
 */
	function init($cfg) {
		PHPPE::lib("LDAP","LDAP Authentication");
		self::$self=$this;
    		return true;
	}

/**
 * Constructor
 *
 * @param cfg not used
 */
	public function __construct($cfg=[])
	{
	}

}
