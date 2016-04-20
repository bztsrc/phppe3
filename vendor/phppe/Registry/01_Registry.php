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
 * @file vendor/phppe/Registry/01_Registry.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief key-value registry for Extension configuration, included in Pack
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

class Registry {
/**
 * Register registry extension
 *
 * @param cfg not used
 */
	function init($cfg) {
		PHPPE::lib("Registry","Parameter Registry");
		return true;
	}

/**
 * Read a parameter value for key from registry. Will return default if key not found
 *
 * @param key
 * @param optional default value
 * @return value
*/
	static function get($key,$default="") {
		//sanitize key
		$key=preg_replace("/[^a-zA-Z0-9_]/","",$key);
		$value=null;
		//try to read from database...
		try {
			$value = PHPPE::field("data","registry","name=?","","",[$key]);
		} catch(\Exception $e) {
		//...fallback to files
			$v = trim(@file_get_contents("data/registry/".$key));
			$value = json_decode($v);
			if(!is_array($value)&&!is_object($value))
				$value=$v;
		}
		return $value==null?$default:$value;
	}

/**
 * Store a parameter value for key into registry
 *
 * @param key
 * @param value
*/
	static function set($key,$value) {
		//sanitize key
		$key=preg_replace("/[^a-zA-Z0-9_]/","",$key);
		$value=is_array($value)||is_object($value)?json_encode($value):trim($value);
		//try to save to database...
		try {
			if(!PHPPE::exec("REPLACE INTO registry (name,data) VALUES (?,?)",[$key,$value])) throw new \Exception();
		} catch(\Exception $e) {
		//...fallback to files
			@mkdir("data/registry");
			file_put_contents("data/registry/".$key,$value);
		}
	}

/**
 * Remove a parameter from registry
 *
 * @param key
*/
	static function del($key) {
		//sanitize key
		$key=preg_replace("/[^a-zA-Z0-9_]/","",$key);
		//remove both database record as well as file
		try {
			@PHPPE::exec("DELETE FROM registry WHERE name=?",[$key]);
		} catch(\Exception $e) {
		}
		try {
			@unlink("data/registry/".$key);
		} catch(\Exception $e) {
		}
	}
}
