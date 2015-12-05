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
 * @file vendor/phppe/registry/01_Registry.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief key-value registry for Extension configuration
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

class Registry {
/**
 * Read a configuration value for key from registry. Will return default if key not found
 *
 * @param key
 * @param optional default value
 * @return value
*/
	static function get($key,$default="") {
		$key=preg_replace("/[^a-zA-Z0-9_]","",$key);
		$value=null;
		try {
			$value = PHPPE::fetch("data","registry","name=?","","",[$key]);
		} catch(\Exception $e) {
			$value = @file_get_contents("data/registry/".$key);
		}
		return json_decode(trim($value==null?$default:$value));
	}
/**
 * Store a configuration value for key into registry
 *
 * @param key
 * @param value
*/
	static function set($key,$value) {
		$key=preg_replace("/[^a-zA-Z0-9_]","",$key);
		$value=trim($value);
		try {
			if(!PHPPE::exec("REPLACE INTO registry SET name=?,data=?",[$key,$value])) throw new \Exception();
		} catch(\Exception $e) {
			@mkdir("data/registry");
			file_put_contents("data/registry/".$key,json_encode($value));
		}
	}
}
