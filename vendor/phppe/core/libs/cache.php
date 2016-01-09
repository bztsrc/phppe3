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
 * @file vendor/phppe/core/libs/cache.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief this file should return an object with Memcache compatible get() and set() methods
 */
namespace PHPPE\Cache;
use PHPPE\Core as PHPPE;

//! PHP APC support
class APC {
/**
 * constructor
 *
 * @param url (constant "apc")
 */
 function __construct($cacheurl) {
	ini_set("apc.enabled",1); /* cacheurl not used */
	if(!function_exists("apc_fetch")) PHPPE::log('C',L("no php-apc"),"cache");
 }
 function get($key) { $v=apc_exists($key)?apc_fetch($key):null;if(function_exists('gzinflate')) $d = json_decode(@gzinflate($v)); return !empty($d)?$d:$v; }
 function set($key,$value,$compress=false,$ttl=0) { return apc_store($key,$compress&&function_exists('gzdeflate')?gzdeflate(json_encode($value)):$value,$ttl); }
}

//! Plain file cache support
class Files {
/**
 * constructor
 *
 * @param url (constant "files")
 */
 function __construct($cacheurl) { @mkdir("data/cache"); /* cacheurl not used */ }
 private function fn($key) { return "data/cache/".substr($key,0,1)."/".substr($key,2,2)."/".substr($key,4); }
 function get($key) {
	$ttl=intval(@file_get_contents($this->fn($key).".ttl"));
	if(!file_exists($this->fn($key)) || ($ttl>0 && time()-filemtime($this->fn($key))>$ttl)) return null;
	$v = @file_get_contents($this->fn($key));
	if(function_exists('gzinflate')) $d = @gzinflate($v);
	return json_decode(!empty($d)?$d:$v,true);
 }
 function set($key,$value,$compress=false,$ttl=0) {
	@mkdir("data/cache/".substr($key,0,1));
	@mkdir("data/cache/".substr($key,0,1)."/".substr($key,2,2));
	if($ttl>0) @file_put_contents($this->fn($key).".ttl",$ttl);
	$v=json_encode($value);
	return @file_put_contents($this->fn($key),$compress&&function_exists('gzdeflate')?gzdeflate($v):$v);
 }
 function cron_minute($args)
 {
	$files = glob("data/cache/*/*/*.ttl");
	foreach($files as $f) {
		$ttl = intval(@file_get_contents($f));
		$cf = substr($f,0,strlen($f)-4);
		if($ttl<1 || time()-filemtime($cf) >= $ttl) {
			unlink($f);
			unlink($cf);
		}
	}
 }
}

//! class dispatcher - this violates PSR-2, but we prefer simplicity here
if(strtolower($this->cache)=="apc")
	return new \PHPPE\Cache\APC($this->cache);
elseif(strtolower($this->cache)=="files")
	return new \PHPPE\Cache\Files($this->cache);
else
	//! if configured otherwise, give other classes a chance
	return null;
