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
 * @file vendor/phppe/bootstrap/01_bootstrap.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief Bootstrap integration in PHPPE
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

class Bootstrap {

	function init($cfg) {
		//register
		PHPPE::lib("bootstrap","Bootstrap");

		//load style sheests
		PHPPE::css("bootstrap.min.css");
		PHPPE::css("bootstrap-theme.min.css");

		//this won't work as bootstrap.js fails when loaded asynchroniously
		//PHPPE::jslib("bootstrap.min.js");

		//so we load it dynamically after page loaded
		PHPPE::js("init()","var js=document.createElement('script');js.type='text/javascript';js.src='js/bootstrap.min.js';document.body.appendChild(js);",true);

		return true;
	}

	function diag()
	{
		//patch: fix font paths for good
		$fn="vendor/phppe/bootstrap/css/bootstrap.min.css";
		file_put_contents($fn,str_replace("../fonts/","",file_get_contents($fn)));
	}
}
