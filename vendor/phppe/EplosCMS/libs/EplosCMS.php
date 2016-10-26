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
 * @file vendor/phppe/EplosCMS/libs/EplosCMS.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief Compatibility layer for EplosCMS contents
 */
namespace PHPPE;

/**
 * NOTE: normally you never extend a Model from App.
 *
 * we do it to allow route() to use the same class as controller
 * this is a hack required by third party CMS
 */
class EplosCMS extends Extension
{
	public $name;
	public $pagesdir;
	static public $params = [];
	static public $page;

	function init($cfg) {

		//! defaults
		if(empty($cfg["sqldir"]))   $cfg["sqldir"] = "data/temp";
		if(empty($cfg["pagesdir"])) $cfg["pagesdir"] = "pages";
		$this->pagesdir=$cfg["pagesdir"];

		//! if it's a special sql refresh request
		if(Core::$core->url == "sqlrefresh" || isset($_REQUEST["sqlrefresh"])) {
			//look data source
			if(!DS::db())
				die("ERROR: no db");
			//read the changes
			$sqlFiles = @glob($cfg["sqldir"]."/sqlchanges-*.sql");
			foreach($sqlFiles as $sf) {
				//get sql commands from file
				$sqls = str_getcsv(@file_get_contents($sf),";");
				@unlink($sf);
				//execute one by one
				foreach($sqls as $query)
					DS::exec($query);
			}
			die("OK");
		}
		//! check if there's a CMS generated file for the url
		//! if so, add route for it
		$c = @explode("/",Core::$core->url);
		while(!empty($c)) {
			$f=implode("-SLASH-",$c);
			if( file_exists($cfg["pagesdir"]."/".$f.".php") ) {
				self::$page = $cfg["pagesdir"]."/".$f.".php";
				Http::route(Core::$core->url,"\PHPPE\EplosCMS");
				break;
			}
			//if not found, put last part in parameters array
			self::$params[] = array_pop($c);
		}
		//reverse the parameters as they were popped in reverse order
		self::$params = @array_reverse(self::$params);
		return true;
	}

	/**
	 * EplosCMS action - include the content
	 */
	function action($item)
	{
		//this gets called for view-only pages as well so
		//we must make extra checks here.
		if( !empty(self::$page) && file_exists(self::$page) ) {
			$core = &Core::$core;
			$client = &Core::$client;
			$user = &Core::$user;
			$app = &$this;
			$app->params = self::$params;
			die(require(self::$page));
		}
	}
}
