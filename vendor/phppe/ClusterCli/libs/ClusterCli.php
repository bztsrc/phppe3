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
use PHPPE\Core as Core;
use PHPPE\DS as DS;

class ClusterCli extends \PHPPE\Model
{
	public $id;
	public $name;
	protected static $_table="cluster";
	public $_loadbalancer;
	public $_keepalive=9;

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
			$this->_loadbalancer=$config['loadbalancer'];
	}

	public function route($app,$action)
	{
		if($app=="index"&&$action=="action") {
			$d=@file_get_contents("/proc/loadavg");
			$l=!empty($d)?explode(" ",$d)[0]:"1.0";
			// queried signal
			DS::exec("UPDATE ".self::$_table." SET viewd=CURRENT_TIMESTAMP,load=? WHERE id=?",[$l, $this->id]);
		}
	}

	public function cronMinute($item)
	{
		// server supervisor
		exec("pgrep 'cluster client'", $pids);
		if(empty($pids)) {
			// server is not running!
			if ($pid = pcntl_fork()) 
				return;     // Parent 
			ob_end_clean(); // Discard the output buffer and close 
			fclose(STDIN);  // Close all of the standard 
			fclose(STDOUT); // file descriptors as we 
			fclose(STDERR); // are running as a daemon. 
			if (posix_setsid() < 0) 
				return;
			setproctitle("php public/index.php cluster client");
			while(1){
				$ctrl = new \PHPPE\Ctrl\ClusterCli;
				$ctrl->client();
				sleep($this->_keepalive);
			}
		}
	}

}
