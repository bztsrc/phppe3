<?php
/**
 * @file vendor/phppe/ClusterCli/ctrl/clustercli.php
 * @author bzt
 * @date 27 Sep 2016
 * @brief
 */

namespace PHPPE\Ctrl;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\DS as DS;

class ClusterCli extends \PHPPE\ClusterCli
{
	static $cli="cluster client";

/**
 * Action handler
 */
	function client($item=null)
	{
		$node=Core::lib("ClusterCli");
		// get command
		$cmd=DS::field("cmd",self::$_table,"id=?","","",[$node->id]);
		// keep alive signal
		$d=@file_get_contents("/proc/loadavg");
		$l=!empty($d)?explode(" ",$d)[0]:"1.0";
		if(empty(DS::exec("UPDATE ".self::$_table." SET modifyd=CURRENT_TIMESTAMP,cmd='',load=? WHERE id=?",[$l,$node->id]))){
			DS::exec("INSERT INTO ".self::$_table." (id,name,load,type,created,modifyd) VALUES (?,?,?,'".($node->_loadbalancer?"loadbalancer":"application")."',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)",[$node->id,gethostbyaddr($node->id),$l]);
		};
		// execute commands
		if($cmd=="invalidate"){
			Cache::invalidate();
		} elseif ($cmd=="shutdown"){
			exec("sudo poweroff");
		} elseif ($cmd=="restart"){
			exec("sudo restart");
		} elseif ($cmd=="reload" && $node->_loadbalancer){
			//FIXME: generate bind config

			$s="vendor/bin/cluster_loadbalancer.sh";
			if (file_exists($s))
				exec(". ".$s." ".$cmd);
		}
	}
}
