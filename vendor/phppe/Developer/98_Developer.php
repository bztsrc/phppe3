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
 * @file vendor/phppe/Developer/98_Developer.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief PHPPE Sources and unit tests
 */
use PHPPE\Core as PHPPE;

//register module and menu
PHPPE::lib( "Developer", "PHPPE Source and Tests");
PHPPE::menu( L("Tests"), "tests" );
//this is needed for filter tests
PHPPE::route("tests/httptest", "\Tests", "action_httppost", "post");
PHPPE::route("tests/httptest", "\Tests", "action_httpget", "get");
//register class to url that implements controller.
//Because no action given, default action rules will apply.
PHPPE::route("tests", "\Tests");
?>