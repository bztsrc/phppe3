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
 * @file vendor/phppe/core/ctrl/json.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief Example action handler that outputs to JSON format
 */
namespace PHPPE\Ctrl;

class JSON extends \PHPPE\Ctrl {
	public $_mimetype="text/json";
	public $results=[];

	function __construct($cfg){
		\PHPPE\Core::$core->noframe=true;
		\PHPPE\Core::$core->output="json";

		//! load your list here
		$this->results=[
			["title"=>"title","description"=>"description","category"=>"category","link"=>url("link")]
		];
	}

}
?>