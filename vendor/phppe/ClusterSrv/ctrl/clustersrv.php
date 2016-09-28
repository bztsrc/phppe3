<?php
/**
 * @file vendor/phppe/ClusterSrv/ctrl/clustersrv.php
 * @author bzt
 * @date 27 Sep 2016
 * @brief
 */

namespace PHPPE\Ctrl;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\DS as DS;

class ClusterSrv extends \PHPPE\ClusterSrv
{
	static $cli="cluster [server|status|takeover|refresh|help]";

	function help($item=null)
	{
		die("cluster status    - prints the current status\n".
			"cluster server    - checks server (called from cron)\n".
			"cluster takeover  - force this server to became master.\n".
			"cluster refresh   - flush application node's cache.\n".
			"cluster client    - checks application server.\n"
		);
	}

	function takeover($item=null)
	{
		$node=Core::lib("ClusterSrv");
		DS::exec("UPDATE ".self::$_table." SET type='slave',viewd=CURRENT_TIMESTAMP WHERE type='master'");
		DS::exec("UPDATE ".self::$_table." SET type='master',modifyd=CURRENT_TIMESTAMP WHERE id=?", [$node->id]);
		$node->resources("start");
		DS::exec("UPDATE ".self::$_table." SET cmd='reload' WHERE type='loadbalancer'");
	}

	function refresh($item=null)
	{
		$node=Core::lib("ClusterSrv");
		DS::exec("UPDATE ".self::$_table." SET cmd='invalidate' WHERE type='application'");
		DS::exec("UPDATE ".self::$_table." SET cmd='reload' WHERE type='loadbalancer'");
		if($node->_master)
			$node->resources("reload");
	}

	function server($item=null)
	{
		$node=Core::lib("ClusterSrv");
		// get command
		$cmd=DS::field( "cmd",self::$_table,"id=?","","",[$node->id]);
		if($cmd=="restart") {
			exec("sudo restart");
		}
		// keep alive signal
		$d=@file_get_contents("/proc/loadavg");
		$l=!empty($d)?explode(" ",$d)[0]:"1.0";
		if(empty(DS::exec("UPDATE ".self::$_table." SET modifyd=CURRENT_TIMESTAMP,cmd='',load=? WHERE id=?",[$l,$node->id]))){
			DS::exec("INSERT INTO ".self::$_table." (id,name,load,type,created,modifyd) VALUES (?,?,?,'".($node->_master?"master":"slave")."',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)",[$node->id,gethostbyaddr($node->id),$l]);
		};
		$master=DS::field("id",self::$_table,"type='master' AND modifyd>CURRENT_TIMESTAMP-120");
		if (strtolower(trim($node->id)) == strtolower(trim($master))) {
			/* Master */
			$node->resources("check");
			//! purge old entries
			DS::exec("DELETE FROM ".self::$_table." WHERE modifyd<CURRENT_TIMESTAMP-900");
			DS::exec("UPDATE ".self::$_table." SET cmd='restart' WHERE modifyd<CURRENT_TIMESTAMP-120");

			//! stop unused application nodes
			DS::exec("UPDATE ".self::$_table." SET cmd='shutdown' WHERE viewd<CURRENT_TIMESTAMP-900 AND load<0.05");

			//! if there are overloaded application nodes, start new nodes
			$overloaded=DS::field("id",self::$_table,"load>1.0 AND modifyd>CURRENT_TIMESTAMP-120");
			if(!empty($overloaded))
				$node->resources("application");
		} else {
			/* Slave */
			$node->resources("stop");
			// no master?
			if (empty($master)) {
				// am I the first slave?
				$slave=DS::field("id",self::$_table,"type='slave' AND modifyd>CURRENT_TIMESTAMP-120","","id");
				if (strtolower(trim($node->id)) == strtolower(trim($slave))) {
					$this->takeover();
				}
			}
		}
	}

/**
 * Action handler
 */
	function action($item)
	{
		$lib=Core::lib("ClusterSrv");
		$nodes=DS::fetch("*",self::$_table,"","","type DESC,id");
print_r($nodes);
echo("status info srv ".$this->id."\n");
	}
}
