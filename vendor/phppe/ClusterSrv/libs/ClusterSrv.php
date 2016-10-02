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
 * @file vendor/phppe/ClusterSrv/libs/ClusterSrv.php
 * @author bzt
 * @date 27 Sep 2016
 * @brief
 */

namespace PHPPE;
use PHPPE\Core as Core;
use PHPPE\Tools as Tools;
use PHPPE\DS as DS;

class ClusterSrv extends \PHPPE\Model
{
	public $id;
	public $name;
	protected static $_table="cluster";
	public $_master=0;
	public $_keepalive=9;
	public $_deploy;
	public $_skeleton;
	public $_rsync=false;

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
		if(!empty($config['deploy']) && is_array($config['deploy'])) {
				$this->_deploy=$config['deploy'];
		}
		if(!empty($config['skeleton']) && is_dir($config['skeleton'])) {
				$this->_skeleton=$config['skeleton'];
		}
		if(!empty($config['skeleton']) && is_dir($config['skeleton'])) {
				$this->_rsync=true;
		}
		$master=DS::field("id",self::$_table,"type='master' AND modifyd>CURRENT_TIMESTAMP-120");
		$this->_master= strtolower(trim($this->id)) == strtolower(trim($master));

		View::jslib("cluster.js","pe.cluster.init();");
	}

	public function stat()
	{
		return
			"<div id='pe_cl' class='sub' style='display:none;' onmouseover='return pe_w();'></div>".
			"<span class='glyphicon glyphicon-cloud' style='color:#808080;' onclick='pe.cluster.getstatus();return pe_p(\"pe_cl\",null,null,-200);'></span>";
	}

	public function diag()
	{
		foreach([
			"frozen"=>"modifyd<CURRENT_TIMESTAMP-120",
			"unused"=>"viewd<CURRENT_TIMESTAMP-600",
			"overloaded"=>"load>=1.0"
		] as $k=>$v){
			$res = DS::query("id",self::$_table, $v);
			if(!empty($res))
				echo("DIAG-E: cluster: ".count($res)." ".$k." node(s): ".implode(", ",array_column($res,"id"))."\n");
		}
		foreach([
			"check"=>"#!/bin/sh\n\n# Check if this server still has all the master resources\n",
			"start"=>"#!/bin/sh\n\n# Grab all resources for master (eg. OpenVPN tunnels)\n",
			"stop"=>"#!/bin/sh\n\n# Release all resources (eg. OpenVPN tunnels)\n",
			"reload"=>"#!/bin/sh\n\n# reload all resources and clear caches\n",
			"worker"=>"#!/bin/sh\n\n# Use cURL WebAPI to start a new worker instance\n"
		] as $f=>$d){
			$fn="vendor/bin/cluster_".$f.".sh";
			if(!file_exists($fn)) {
				file_put_contents($fn,$d);
				chmod($fn,0750);
			}
		}
	}

    public function deploypush($rootdir, $dirs, $node)
    {
		static $servers;
		if($servers==null)
			//! get server configurations (ssh identity)
			$servers = json_decode(file_get_contents(".tmp/multiserver"), true);
			if(!is_array($dirs)||empty($dirs)) {
				throw new \Exception("bad input");
			}
			if(empty($servers[$node['id']]) && empty($servers[$node['name']])) {
				throw new \Exception("no remote config");
			}
			$remote=empty($servers[$node['id']])?$servers[$node['name']]:$servers[$node['id']];
			//! mark node as syncing
			DS::exec("UPDATE ".self::$_table." SET console='Syncing...',syncd=CURRENT_TIMESTAMP WHERE id=?",[$node['id']]);
			//! start a background job
			Tools::bg("\PHPPE\Ctrl\ClusterSrv", "deployworker", [
				'id'=>$node['id'],
				'rootdir'=>$rootdir,
				'dirs'=>$dirs,
				'remote'=>empty($servers[$node['id']])?$servers[$node['name']]:$servers[$node['id']],
				'rsync'=>Core::lib("ClusterSrv")->_rsync,
			], $this->_keepalive>1?$this->_keepalive:1);
	}

	function deployworker($arg)
	{
			$id=$arg['id'];
			$rootdir=$arg['rootdir'];
			$dirs=$arg['dirs'];
			$remote=$arg['remote'];
			//! call rsync
			$files=[];
			if(substr($rootdir,-1)!="/") $rootdir.="/";
			foreach($dirs as $d)
				$files[$rootdir.$d]=1;
			Core::$user->data['remote']=$remote;
			$files=array_keys($files);
			$path=!empty($remote['path'])?$remote['path']:"/var/www/";
			if(!empty($arg['rsync'])) {
				$ret=Tools::ssh("rsync",$files,$path);
			} else {
				$ret=Tools::copy($files,$path);
			}
			//! update status
			DS::exec("UPDATE ".self::$_table." SET console=?,syncd=CURRENT_TIMESTAMP WHERE id=?",[$id]);
	}

	public function resources($cmd)
	{
		$s="vendor/bin/cluster_".$cmd.".sh";
		if (in_array($cmd,["check","start","stop","reload","worker"]) && file_exists($s))
			exec(". ".$s." ".$cmd);
	}

	public function cronMinute($item)
	{
		// server supervisor
		Tools::bg("\PHPPE\Ctrl\ClusterSrv", "server", null, $this->_keepalive>1?$this->_keepalive:1);
	}

}
