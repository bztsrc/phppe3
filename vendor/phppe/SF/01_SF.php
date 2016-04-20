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
 * @file vendor/phppe/SF/01_SF.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief Provides SalesForce data source
 * @see https://github.com/developerforce/Force.com-Toolkit-for-PHP
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

//! if not autoloaded so far, use a fallback version
//! of the ToolKit packed with this Extension
if(!class_exists("SforceEnterpriseClient")) {
	//! those guys at SF have never heard of relative paths...
	chdir("vendor/phppe/SF/libs");
	require_once("SforceEnterpriseClient.php");
	chdir("../../../..");
}

/**
 * Main class. This will act as a data source
 *
 */
class SF extends \SforceEnterpriseClient
{
    private static $self;
    private static $ds_sel;
    //! we declare these as static to avoid accidental dump
    private static $user;
    private static $pass;
    private static $token;
    private static $wsdl;
/**
 * Register SalesForce
 *
 * @param cfg connection parameters
 */
	function init($cfg) {
		PHPPE::lib("SF","SalesForce (API ".$this->version.")");
		if(empty($cfg['user'])||empty($cfg['pass'])||empty($cfg['token']))
		    return false;
		self::$user=$cfg['user'];
		self::$pass=$cfg['pass'];
		self::$token=$cfg['token'];
		self::$wsdl=$cfg['wsdl'];
		self::$self=$this;
		if(!file_exists(self::$wsdl))
		    throw new \Exception(L("Unable to open").": ".self::$wsdl);
		//get current ds selector
		$d=PHPPE::ds();
		//initialize datasource, get our selector
		$this->ds_sel=PHPPE::db("salesforce:",$this);
		//restore ds selector
		PHPPE::ds($d);

		//connect to SalesForce
		//FIXME

    		return true;
	}

	function prepare($sql) {
	}
	
	function execute($args=[]) {
	}

	function fetchAll($opts=[]) {
	}
	
	function rowCount() {
	}

	function lastInsertId() {
	}
}
