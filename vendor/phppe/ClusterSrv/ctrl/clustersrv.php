<?php
/**
 * @file vendor/phppe/ClusterSrv/ctrl/clustersrv.php
 * @author bzt
 * @date 27 Sep 2016
 * @brief
 */

namespace PHPPE\Ctrl;

use PHPPE\Core;
use PHPPE\View;
use PHPPE\Http;
use PHPPE\DS;

class ClusterSrv extends \PHPPE\ClusterSrv
{
	static $cli="cluster [server|status|takeover|flush|deploy|help]";

	function help($item=null)
	{
		//! check if executed from CLI
		if(Core::$client->ip!="CLI")
			Http::redirect("403");
		die("cluster help      - this help\n".
			"cluster status    - prints the current status\n".
			"cluster server    - checks management daemon (called from cron)\n".
			"cluster takeover  - force this management server to became master\n".
			"cluster flush     - flush worker cache\n".
			"cluster deploy    - push code to workers\n".
			"cluster client    - (!) checks worker daemon (called from cron)\n".
			"cluster bindcfg   - (!) generate bind configuration on lb worker\n"
		);
	}

	function takeover($item=null)
	{
		//! check if executed from CLI
		if(Core::$client->ip!="CLI")
			Http::redirect("403");
		$node=Core::lib("ClusterSrv");
		DS::exec("UPDATE ".self::$_table." SET type='slave',viewd=CURRENT_TIMESTAMP WHERE type='master'");
		DS::exec("UPDATE ".self::$_table." SET type='master',modifyd=CURRENT_TIMESTAMP WHERE id=?", [$node->id]);
		$node->resources("start");
		DS::exec("UPDATE ".self::$_table." SET cmd='reload' WHERE type='lb'");
	}

	function server($item=null)
	{
		//! check if executed from CLI
		if(Core::$client->ip!="CLI")
			Http::redirect("403");
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

			//! stop unused worker nodes
			DS::exec("UPDATE ".self::$_table." SET cmd='shutdown' WHERE type='worker' AND viewd<CURRENT_TIMESTAMP-900 AND load<0.05");

			//! if there are overloaded worker nodes, start new nodes
			$overloaded=DS::field("id",self::$_table,"type='worker' AND load>1.0 AND modifyd>CURRENT_TIMESTAMP-120");
			if(!empty($overloaded))
				$node->resources("worker");
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

	function flush($item=null)
	{
		header("Content-type:text/plain");
		if (Core::$client->ip!="CLI" && !Core::$user->has("cluadm"))
			Http::redirect("403");
		$node=Core::lib("ClusterSrv");
		DS::exec("UPDATE ".self::$_table." SET cmd='invalidate' WHERE type='worker'");
		DS::exec("UPDATE ".self::$_table." SET cmd='reload' WHERE type='lb'");
		if($node->_master)
			$node->resources("reload");
		die("OK\r\n");
	}

	function deploy($item=null)
	{
		header("Content-type:text/plain");
		if (Core::$client->ip!="CLI" && !Core::$user->has("cluadm"))
			Http::redirect("403");
		if (!file_exists(".tmp/multiserver")) {
			die("CLUSTER-E: ".L("multiserver not installed")."\n");
		}

		$node=Core::lib("ClusterSrv");
		if (!$node->_master) {
            die("CLUSTER-E: ".L("not master")."\n");
		}
		if (empty($node->_deploy)||empty($node->_skeleton)) {
			die("CLUSTER-E: ".L("deploy not configured")."\n");
		}
		// deploy['role'] => [ directories to sync ]
        
		$nodes=DS::query("*",self::$_table,"","","type,load DESC,id");
		$allOk=1;
        foreach ($nodes as $n) {
			$t=$n['type'];
			if($t=="master"||$t=="slave") $t="admin";
            echo($n['id']." ");
			if(empty($node->_deploy[$t])) {
				echo(L("no such role")." ".$t);
				$allOk=0;
			} else {
				try {
					parent::deploypush($node->_skeleton,$node->_deploy[$t],$n);
					echo(chr(27)."[92mOK".chr(27)."[0m");
				} catch(\Exception $e) {
					echo(chr(27)."[91m".$e->getMessage().chr(27)."[0m");
					$allOk=0;
				}
			}
            echo("\r\n");
        }
		die(($allOk?chr(27)."[92mOK":chr(27)."[91mERR").chr(27)."[0m\r\n");
	}
/**
 * Action handler
 */
	function action($item)
	{
		setlocale(LC_NUMERIC,"C");

		$lib=Core::lib("ClusterSrv");
		$nodes=DS::query("*",self::$_table,"","","type,load DESC,id");
		$master=DS::field("id",self::$_table,"type='master' AND modifyd>CURRENT_TIMESTAMP-120");

		//! check if executed from CLI
		if(Core::$client->ip!="CLI") {
			header("Content-type:application/json");
			$loadavg=0.0; $waspeek=0;
			$minSync="";
			if(!empty($nodes)){
				$minSync=reset($nodes)['syncd'];
				foreach($nodes as $k=>$node) {
					unset($nodes[$k]["cmd"]);
					$loadavg+=floatval($node['load']);
					if($node['load']>=0.5)
						$waspeek=1;
					if($node['load']>=0.75)
						$waspeek=2;
					if($node['syncd']<$minSync)
						$minSync=$node['syncd'];
				}
				$loadavg/=count($nodes);
			}
            date_default_timezone_set('UTC');
			$minSync=strtotime($minSync);
			$D=[]; $d=$lib->_skeleton; if(substr($d,-1)!="/") $d.="/";
			foreach (['vendor/*', 'vendor/*/*', 'vendor/*/*/*', 'vendor/*/*/*/*', 'vendor/*/*/*/*/*'] as $v) {
				$D += array_fill_keys(@glob($d.$v,GLOB_NOSORT), 0);
            }
			$newfiles=0;
			foreach ($D as $d=>$v) {
				$t=filemtime($d);
				if($t!=0 && $t>$minSync) {
					$newfiles=1;
					break;
				}
			}
			die(json_encode([
				"status"=>($loadavg<0.1?"idle":($loadavg>0.5||$waspeek?($loadavg>0.75||$waspeek==2?"error":"warn"):"ok")),
				"loadavg"=>$loadavg,
				"peek"=>$waspeek,
				"master"=>$master,
				"newfiles"=>$newfiles,
				"id"=>Core::lib("ClusterSrv")->id,
				"nodes"=>$nodes
			]));
		} else {
	        echo(chr(27)."[96mThis node is: ".chr(27)."[0m".$lib->id." ".chr(27)."[".(strtolower(trim($lib->id)) == strtolower(trim($master))?"91mMASTER":"92mSLAVE").chr(27)."[0m\n".chr(27)."[96mCluster nodes:\n");
			echo(chr(27)."[96m Id              Type        Load  Last seen            Last viewed          Name\n --------------  ---------  -----  -------------------  -------------------  -----------------------------".chr(27)."[0m\n");
			foreach($nodes as $node) {
				echo(sprintf("%s%-16s%-8s%8s",strtolower(trim($node['id'])) == strtolower(trim($master))?chr(27)."[93m*":" ",$node['id'],$node['type'],$node['load'])."  ".(!empty($node['modifyd'])?$node['modifyd']:sprintf("%-19s",L("booting")))."  ".(!empty($node['viewd'])?$node['viewd']:"????-??-?? ??:??:??")."  ".$node['name'].chr(27)."[0m\n");
			}
			echo("\n");
			if(Core::$core->action=="action")
				$this->help();
		}
	}
}
