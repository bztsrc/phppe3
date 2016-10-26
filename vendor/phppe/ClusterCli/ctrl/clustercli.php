<?php
/**
 * @file vendor/phppe/ClusterCli/ctrl/clustercli.php
 * @author bzt
 * @date 27 Sep 2016
 * @brief
 */

namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\Cache;
use PHPPE\Http;
use PHPPE\DS;

class ClusterCli extends \PHPPE\ClusterCli
{
	static $cli="cluster [client|bindcfg]";

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
			DS::exec("INSERT INTO ".self::$_table." (id,name,load,type,created,modifyd) VALUES (?,?,?,'".($node->_loadbalancer?"lb":"worker")."',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)",[$node->id,gethostbyaddr($node->id),$l]);
		};
		// execute commands
		if($cmd=="invalidate"){
			Cache::invalidate();
		} elseif ($cmd=="shutdown"){
			exec("sudo poweroff");
		} elseif ($cmd=="restart"){
			exec("sudo restart");
		} elseif ($cmd=="reload" && $node->_loadbalancer){
			// generate bind config
			$bind=Core::lib("ClusterCli")->bindcfg();
			// call the shell hook and pass the config to it's stdin
			if (!empty($bind) && file_exists(static::$_cmd)) {
				$p=popen(". ".static::$_cmd." ".$cmd,"w");
				if($p){
					pwrite($p,$bind);
					pclose($p);
				}
            }
		}
	}

/**
 * Query the bind configuration
 */
	function bindcfg($item=null)
	{
		@header("Content-type:text/plain");
		die(Core::lib("ClusterCli")->bindcfg()."\n");
	}

	function action($item=null)
	{
		if(Core::$client->ip!="CLI")                            
			Http::redirect("403");
		$node=Core::lib("ClusterCli");
		echo(chr(27)."[96mThis node is: ".chr(27)."[0m".$node->id." ".(!empty($node->loadbalancer)?"LOADBALANCER":"WORKER")."\n");
	}
}
