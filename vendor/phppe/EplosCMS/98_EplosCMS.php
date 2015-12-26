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
 * @file vendor/phppe/EplosCMS/98_EplosCMS.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief Compatibility layer for EplosCMS contents
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

/**
 * NOTE: normally you never extend a Model from App.
 *
 * we do it to allow route() to use the same class as controller
 */
class EplosCMS extends App
{
	public $name;
	public $pagesdir;
	static public $params = [];
	static public $page;

	function init($cfg) {
		//! register our name
		PHPPE::lib("EplosCMS","EplosCMS contents");

		//! defaults
		if(empty($cfg["sqldir"]))   $cfg["sqldir"] = "data/temp";
		if(empty($cfg["pagesdir"])) $cfg["pagesdir"] = "pages";
		$this->pagesdir=$cfg["pagesdir"];
		//! get page name from url
		$c = $_SERVER['SCRIPT_NAME'];
		@list($d) = explode("?",$_SERVER['REQUEST_URI']);
		foreach(array($c,n($c))as$C)
		    if(substr($d,0,strlen($C))==$C) {
			$d=substr($d,strlen($C)+1);
			break;
		    }
		//remove lead and trailer slashes
		if(!empty($d) && $d[0]=="/")
		    $d=substr($d,1);
		if(!empty($d) && $d[strlen($d)-1]=="/")
		    $d=substr($d,0,strlen($d)-1);

		//! if it's a special sql refresh request
		if($d == "sqlrefresh" || isset($_REQUEST["sqlrefresh"])) {
			//look data source
			if(!PHPPE::db())
				die("ERROR: no db");
			//read the changes
			$sqlFiles = @glob($cfg["sqldir"]."/sqlchanges-*.sql");
			foreach($sqlFiles as $sf) {
				//get sql commands from file
				$sqls = explode(";",@file_get_contents($sf));
				@unlink($sf);
				//execute one by one
				foreach($sqls as $query)
					PHPPE::exec($query);
			}
			die("OK");
		}
		//! check if there's a CMS generated file for the url
		//! if so, add route for it
		$c = @explode("/",$d);
		while(!empty($c)) {
			$f=implode("-SLASH-",$c);
			if( file_exists($cfg["pagesdir"]."/".$f.".php") ) {
				self::$page = $cfg["pagesdir"]."/".$f.".php";
				PHPPE::route($d,"\PHPPE\EplosCMS");
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
			$core = &PHPPE::$core;
			$client = &PHPPE::$client;
			$user = &PHPPE::$user;
			$app = &$this;
			$app->params = self::$params;
			die(require(self::$page));
		}
	}
}
