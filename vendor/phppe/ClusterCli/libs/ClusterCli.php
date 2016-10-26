<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
 *
 *  Copyright LGPL 2016
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
 * @file vendor/phppe/ClusterCli/libs/ClusterCli.php
 * @author bzt
 * @date 27 Sep 2016
 * @brief
 */

namespace PHPPE;

class ClusterCli extends \PHPPE\Model
{
	public $id;
	public $name;
	protected static $_table="cluster";
	public $_loadbalancer;
	public $_keepalive=9;
	static $_cmd="vendor/bin/cluster_lb.sh";

	public function __construct()
	{
	}

	public function init($config)
	{
		if(!empty($config['ip'])) {
			$this->id=$config['ip'];
		} elseif(!empty($config['interface'])) {
			exec("ip addr sh ".escapeshellarg($config['interface'])."|grep inet", $ips);
			if(!empty($ips[0]) && preg_match("/inet ([0-9a-f\:\.]+)/",$ips[0],$m))
				$this->id=$m[1];
		}
		if(empty($this->id)){
			exec("ip addr sh|grep inet", $ips);
			if(!empty($ips[0]) && preg_match("/inet ([0-9a-f\:\.]+)/",$ips[0],$m))
				$this->id=$m[1];
		}
		if(!empty($config['keepalive']) && intval($config['keepalive'])>3) {
			$this->_keepalive=intval($config['keepalive']);
		}
		if(!empty($config['loadbalancer']))
			$this->_loadbalancer=is_array($config['loadbalancer'])?$config['loadbalancer']:str_getcsv($config['loadbalancer'],",");
	}

	public function route($app,$action)
	{
		// keepalive on new sessions and every minute afterwards
		if(empty($_SESSION['pe_lt']) || $_SESSION['pe_lt']+60<Core::$core->now) {
			$_SESSION['pe_lt']=Core::$core->now;
			$d=@file_get_contents("/proc/loadavg");
			$l=!empty($d)?explode(" ",$d)[0]:"1.0";
			// queried signal
			try {
				DS::exec("UPDATE ".self::$_table." SET viewd=CURRENT_TIMESTAMP,load=? WHERE id=?",[$l, $this->id]);
			} catch (\Exception $e) {
				if(Core::$client->ip=="CLI")
					echo(L("Unable to find cluster table on quorum database.")."\r\n");
			}
		}
	}

	public function diag()
	{
	    if(!file_exists(self::$_cmd)) {
            file_put_contents(self::$_cmd,"#!/bin/sh\n\n# This script get a bind configuration file on stdin.\n# Save it and reload dns server.\n# exec > /var/bind/myzones.cnf\n# systemctl reload bind\n");
            chmod(self::$_cmd,0750);
	    }
	}

	public function bindcfg()
	{
			try {
				$nodes=DS::query("*",self::$_table,"","","type, created");
			} catch (\Exception $e) {
				$nodes=[];
			}
			// generate bind config
			$sd = $this->_loadbalancer;
			if(!is_array($sd)||empty($sd)) {
				$sd=["www"];
			}
			View::assign("nodes",$nodes);
			View::assign("subdomains",$sd);
			return preg_replace("|<br[\ \/]*>[\n]?|i","\n",View::template("bind"));
	}

	public function cronMinute($item)
	{
		// client supervisor
		Tools::bg("\PHPPE\Ctrl\ClusterCli", "client", null, $this->_keepalive>1?$this->_keepalive:1);
	}

}
