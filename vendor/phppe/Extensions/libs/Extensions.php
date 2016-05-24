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
 * @file vendor/phppe/Extensions/libs/Extensions.php
 * @author bzt
 * @date 1 Jan 2016
 * @brief PHPPE Extension Manager
 */
namespace PHPPE;
use PHPPE\Core as Core;

class Extensions {

	function init($cfg)
	{
		//! global remote configuration to user object if no user specific found
		if( !empty(Core::$user->id) && empty(Core::$user->data['remote']['host']) ) {
			if( !empty($cfg['identity']) && !empty($cfg['user']) && !empty($cfg['host']) && !empty($cfg['path']) )
				Core::$user->data['remote']=$cfg;
			else
				Core::log("W","Unable to load global remote configuration from vendor/phppe/Extensions/config.php");
		}
	}

	//look for error message in output
	private static function isErr($s)
	{
		$s=trim(strtolower($s));
		if( empty($s) )
			return false;
		//typical error indicators at beginning of line
		foreach( array("sh:", "ssh:","curl:","tar:","find:","rm:","grep:","sed:") as $c )
			if( substr($s,0,strlen($c)) == $c )
				return true;
		//typical messages - must contain a space
		if(	strpos($s,"could not")!==false || strpos($s,"no such")!==false ||
			strpos($s," denied")!==false || strpos($s," failure")!==false ||
			strpos($s," error")!==false || strpos($s,"wrong ")!==false ||
			strpos($s,"try again")!==false
		)
			return true;
		return false;
	}

	private static function getSiteUrl()
	{
		return Core::$user->data['remote']['user']."@".Core::$user->data['remote']['host'].":".Core::$user->data['remote']['path'];
	}


	private static function formatvalue($v) {
		return ($v=="true"||$v=="false"||$v=="null"||
			$v=="0"||intval($v)!=0||
			@$v[0]=="["||@$v[0]=="{")? $v : "\"".addslashes($v)."\"";
	}

	private static function identity($privkey)
	{
		$fn = tempnam(".tmp", ".id_");
		file_put_contents($fn, trim($privkey)."\n");
		chmod($fn,0400);
		return $fn;
	}

	//return JSON string with version informations
	function getInstalled($skipcache=0)
	{
		if( !Core::$user->has("install") ) {
			Core::log('A',"Suspicious behavior ".$this->getsiteurl());
			return "PHPPE-E: ".L("Access denied");
		}
		$t="";
		//if remote not available, get from local directly
		if( !empty(Core::$user->data['remote']['identity']) &&
			!empty(Core::$user->data['remote']['user']) &&
			!empty(Core::$user->data['remote']['host']) &&
			!empty(Core::$user->data['remote']['path']) ) {
			//get list from remote server
			try {
				$r=explode("\n", trim(\PHPPE\Tools::ssh(
					"cat ".escapeshellarg(Core::$user->data['remote']['path']."/composer.json")." \|grep name \| head -1 \; find ".escapeshellarg(Core::$user->data['remote']['path']."/vendor/phppe")." -type f -name composer.json -exec sh -c \\'cat {} \\| grep -e name[^_] \\|head -1 \\| tr -d \\\\'\\\\n\\\\' \\; cat {} \\| grep -e version[^_] \\' \\\\\; 2>&1"
				)));
			} catch(\Exception $e) {
				$r[0]=$e->getMessage();
			}
			//get site title
			if( !self::isErr($r[0]) ) {
				$d=explode('"',$r[0]);if(empty($d[3])||$d[3]=="phppe3") $d[3]="No name";
				$t="{ \"name\": \"".($d[3]=="No name"?L($d[3]):$d[3])."\" }";
			} else
				$t="{ \"name\": \"".\PHPPE\View::e("","",substr(trim($r[0]),0,4)=="cat:"?L("Run diagnostics first!"):str_replace("\r","",str_replace("\n"," ",trim(empty($r[1])?$r[0]:$r[1]))))."\" }";
			if( !self::isErr(@$r[1]) && !self::isErr(@$r[2])) {
				foreach($r as $v) {
					$d=explode("\"",$v);
					if(!empty($d[3]) && !empty($d[7])) $t.=",{ \"id\": \"".$d[3]."\", \"version\": \"".$d[7]."\" }";
				}
			}
		}
		//fallback to local, but without remote it's read only
		else {
			$t="{ \"name\": \"".\PHPPE\View::e("","",L("configure remote access in Extensions"))."\" }";
			$d=glob("vendor/phppe/*"."/composer.json"); $d[]="vendor/phppe/composer.json";
			foreach($d as $v) {
				$j=json_decode(file_get_contents($v),true);
				if(!empty($j['name']) && !empty($j['version']))
					$t.=",{ \"id\": \"".$j['name']."\", \"version\": \"".$j['version']."\" }";
			}
		}
		return $t;
	}

	//get packages list from repositories
	function getPkgs($skipcache=0)
	{
		$F = ".tmp/.pkgs_".Core::$client->lang;
		if( !$skipcache && file_exists($F) && filemtime($F)+60*60 > time() )
			$pkgs = file_get_contents($F);

		if( empty($pkgs) )
		{
			Core::log('D',"getPkgs cache miss, reading repositories","extensions");
			$p=array();
			$list = [ "https://bztsrc.github.io/phppe3/" ];
			if(!empty(Core::$core->repos)) $list=array_merge(Core::$core->repos,$list);
//fallback to local repo for testing. REMOVE IT!!!
$list=["data/.."];
			foreach($list as $r)
			{
				$url=$r.(substr($r,-1)!="/"?"/":"")."packages.json";
				$d2=file_get_contents($url);
				if(empty($d2))
					$d2=\PHPPE\Http::get($url);
				Core::log('D',"Adding repo: ".$url." (".strlen($d2)." bytes)","extensions");
				$d = json_decode($d2,true,8);
				if(json_last_error()==4)
					$d = json_decode(str_replace("\\'","'",$d2),true,8);
				Core::log('D','Packages from: '.$url.' '.(empty($d2)?"404":json_last_error_msg()),"repo");
				if(!empty($d) && !empty($d['packages'])) {
					foreach($d['packages'] as $pkg=>$ver) {
						//get latest
						$v=array_keys($ver);
						usort($v,"version_compare"/*function($a,$b){
							$A=explode(".",$a);
							$B=explode(".",$b);
							if($A[0]!=$B[0]) return intval($B[0])-intval($A[0]);
							if($A[1]!=$B[1]) return intval($B[1])-intval($A[1]);
							if($A[2]!=$B[2]) return intval($B[2])-intval($A[2]);
							return intval($B[3])-intval($A[3]);
						}*/);
						if($ver[$v[0]]['dist']['type']!='tar' || (isset($p[$pkg]['version']) && version_compare($p[$pkg]['version'],$v[0])>=0)) {
							Core::log('D','- [Skip] '.$pkg.' '.$v[0],"repo");
							continue;
						}
						Core::log('D','- [ OK ] '.$pkg.' '.$v[0],"repo");
						$p[$pkg]['id']=$pkg;
						$p[$pkg]['desc']=!empty($ver[$v[0]]['description_'.Core::$client->lang])?$ver[$v[0]]['description_'.Core::$client->lang]:$ver[$v[0]]['description'];
						$p[$pkg]['name']=!empty($ver[$v[0]]['name_'.Core::$client->lang])?$ver[$v[0]]['name_'.Core::$client->lang]:(!empty($ver[$v[0]]['name_en'])?$ver[$v[0]]['name_en']:$ver[$v[0]]['name']);
						$p[$pkg]['config']=!empty($ver[$v[0]]['conf'])?$ver[$v[0]]['conf']:"";
						$p[$pkg]['conf']=!empty($ver[$v[0]]['conf_'.Core::$client->lang])?$ver[$v[0]]['conf_'.Core::$client->lang]:(!empty($ver[$v[0]]['conf_en'])?$ver[$v[0]]['conf_en']:"");
						$p[$pkg]['help']=!empty($ver[$v[0]]['help_'.Core::$client->lang])?$ver[$v[0]]['help_'.Core::$client->lang]:(!empty($ver[$v[0]]['help_en'])?$ver[$v[0]]['help_en']:"");
						$p[$pkg]['category']=!empty($ver[$v[0]]['keywords_'.Core::$client->lang][0])?$ver[$v[0]]['keywords_'.Core::$client->lang][0]:(!empty($ver[$v[0]]['keywords_en'][0])?$ver[$v[0]]['keywords_en']:(!empty($ver[$v[0]]['keywords'][0])?$ver[$v[0]]['keywords'][0]:""));
						$p[$pkg]['maintainer']=$ver[$v[0]]['maintainer']['name'];
						$p[$pkg]['url']=$ver[$v[0]]['dist']['url'];
						$p[$pkg]['depends']=!empty($ver[$v[0]]['require'])?array_diff(array_keys($ver[$v[0]]['require']),["php"]):[];
						foreach(array("version","license","time","size","sha1","price","preview") as $f)
							@$p[$pkg][$f]=$ver[$v[0]][$f];
						if(empty($p[$pkg]["version"])) $p[$pkg]['version']=$v[0];
						//! force a minimal phppe/Core configuration
						if($pkg=="phppe/Core" && empty($p[$pkg]['config']))
							$p[$pkg]['config']=[
							'db'=>'string(255,mysql:host=localhost;dbname=test@user:pass',
							'cache'=>'string(255,localhost:11211)',
							'masterpasswd'=>'string(80,$2y$10$rrDFYORgliLsPQbl5slUu.gZdhl1LN6AsdRSDUiFgnizXPYEjYoTO)',
							'runlevel'=>'select(0=production,1=testing,2=developer,3=debug)',
							'maintenance'=>'select(false=off,true=on)'
							];
					}
				}
			}
//			usort($p,function($a,$b){ if($a['category']==$b['category'])return 0;return $a['category']>=$b['category']; });
			//f*ck, this fails with memory error...
			//$pkgs=json_encode($p);
			$pkgs=""; foreach($p as $v) $pkgs.=($pkgs?",":"").json_encode($v);
			if( !empty($p) ) {
				file_put_contents($F,$pkgs);
			}
			Core::log('D','Packages to cache: '.$F.' ('.count($p).' extensions)',"repo");
		} else
			Core::log('D','Packages from cache: '.$F,"repo");

		return $pkgs;
	}

	//bootstrap PHPPE environament on remote server
	function bootstrap($item="")
	{
		if( !Core::$user->has("install") ) {
			Core::log('A',"Suspicious behavior ".$url." ".$this->getSiteUrl(), "extensions");
			return "PHPPE-E: ".L("Access denied");
		}
		//get live image
		$data = @file_get_contents("public/index.php");
		if( empty($data) )
			return "PHPPE-E: ".L("No PHPPE Core?");
		//! we cannot install localy, that would use webserver's user, forbidden to write.
		//! So we must use remote user identity even when host is localhost.
		try {
			\PHPPE\Tools::ssh(
				"mkdir -p ".escapeshellarg(Core::$user->data['remote']['path']."/public")." \&\& cat \>".escapeshellarg(Core::$user->data['remote']['path']."/public/index.php"),
				$data
			);
			$r=explode("\n", \PHPPE\Tools::ssh(
				"php ".escapeshellarg(Core::$user->data['remote']['path']."/public/index.php")." --diag\;sudo php ".escapeshellarg(Core::$user->data['remote']['path']."/public/index.php")." --diag"
			));
		} catch(\Exception $e) {
			return "PHPPE-E: ".$e->getMessage();
		}
		if( Extensions::isErr($r[0]) ) {
			Core::log('E', "Failed to bootstrap PHPPE to ".$this->getSiteUrl(), "extensions");
			return "PHPPE-E: ".sprintf(L("Failed to install %s"),"Core")."\nPHPPE-E: ".$this->getSiteUrl()."\n\n".implode("\n", $r);
		}
		Core::log('A', "Installed PHPPE to ".$this->getSiteUrl(), "extensions");
		return "PHPPE-I: ".sprintf(L("Installed %d files from %s"), 1, "public/index.php")."\nPHPPE-I: ".$this->getSiteUrl()."\n\n".implode("\n", $r);
	}

	//install a tarball via ssh to remote server
	function install($param,$instdep=true)
	{
		$out="";

		list($url,$dir)=explode("#",$param);
		if( !Core::$user->has("install") ) {
			Core::log('A',"Suspicious behavior ".$url." ".$this->getsiteurl(),"extensions");
			return "PHPPE-E: ".L("Access denied");
		}

		//installation check
		$inst=json_decode("[".$this->getInstalled()."]",true);
		$instidx=[];
		foreach($inst as $i) if(!empty($i['id'])) $instidx[strtolower($i['id'])]=$i['version'];
		//also install dependencies if this is the first install
		if($instdep && empty($instidx[strtolower($dir)])){
			//build dependency tree
			$pkg=[];
			$pkgs=json_decode("[".preg_replace("/,\"preview\":\"[^\"]+\"/","",$this->getPkgs())."]",true);
			foreach($pkgs as $p) {
				if(strtolower($p['id'])==strtolower($dir)) $pkg=$p;
				$pkgidx[strtolower($p['id'])]=$p;
			}
			//recursively install dependencies
			if(is_array($pkg['depends']))
			foreach($pkg['depends'] as $d){
				if(empty($pkgidx[strtolower($d)]))
					return "PHPPE-E: ".sprintf(L("Failed dependency %s for %s"),$d,$url);
				if(empty($instidx[strtolower($d)])) {
					$out.=$this->install($pkgidx[strtolower($d)]['url']."#".$pkgidx[strtolower($d)]['id'],false)."\n----------------------------------------\n";
				}
			}
		}
		if (substr($url,0,6)=="https:")
			$ca = @file_get_contents(dirname(__DIR__)."/.rootca");
		try {
			$dest = escapeshellarg(Core::$user->data['remote']['path']."/vendor/".$dir);
			$r = \PHPPE\Tools::ssh(
				"mkdir -p ".$dest." \&\& curl ".(!empty($ca)?"--cacert '/dev/stdin'":"")." -sL ".escapeshellarg($url)." \\| tar -xzv --exclude preview -C ".$dest,
				$ca
			);
			$r=explode("\n", $r."\n\n".\PHPPE\Tools::ssh(
				"php ".escapeshellarg(Core::$user->data['remote']['path']."/public/index.php")." --diag"
			));
		} catch(\Exception $e) {
			return "PHPPE-E: ".$e->getMessage();
		}
		if( self::isErr($r[0]) || self::isErr(@$r[1]) || self::isErr(@$r[2]) ) {
			foreach($r as $k=>$v) if(strpos($v,".rootca")!==false) unset($r[$k]);
			Core::log('E', "Failed to install ".$url." to ".$this->getSiteUrl().", ".implode(" ",$r), "extensions");
			return $out."PHPPE-E: ".sprintf(L("Failed to install %s"), $url)."\n\n".implode("\n", $r);
		} else {
			$c=0;
			foreach($r as $k=>$v) {
				if(empty($v)) continue;
				if($v[strlen($v)-1]=="/"||strpos($v,".rootca")!==false)
					unset($r[$k]);
				else if($v[0]=="x" && $v[1]==" ") $r[$k]=substr($v,2);
				if(!empty($r[$k]) && strpos($r[$k],"DIAG-")===false) $c++;
			}
			Core::log('A', "Installed ".$url." to ".$this->getSiteUrl().", ".($c+0)." files".(Core::$core->runlevel>2?": ".implode(" ", $r):""), "extensions");
			return $out."PHPPE-I: ".sprintf(L("Installed %d files from %s"),$c+0, $url)."\n\n".implode("\n", $r);
		}
	}

	//remove files specified in a tarball via ssh from remote server
	function uninstall($param)
	{
		list($url,$dir)=explode("#",$_REQUEST['item']);
		if(empty($dir)) $dir="phppe";

		if( !Core::$user->has("install") ) {
			Core::log('A',"Suspicious behavior ".$url." ".$this->getSiteUrl(), "extensions");
			return "PHPPE-E: ".L("Access denied");
		}

		if (substr($url,0,6)=="https:")
			$ca = @file_get_contents(dirname(__DIR__)."/.rootca");
		try {
			$r=explode("\n", \PHPPE\Tools::ssh(
				"rm -rvf ".escapeshellarg(Core::$user->data['remote']['path']."/vendor/".$dir)." \; echo \; php ".escapeshellarg(Core::$user->data['remote']['path']."/public/index.php")." --diag"
			));
		} catch(\Exception $e) {
			return "PHPPE-E: ".$e->getMessage();
		}
		if( self::isErr($r[0]) || self::isErr(@$r[1]) || self::isErr(@$r[2]) ) {
			foreach($r as $k=>$v) if(strpos($v,".rootca")!==false) unset($r[$k]);
			Core::log('E', "Failed to uninstall ".$url." ".$this->getSiteUrl().", ".implode(" ", $r), "extensions");
			return "PHPPE-E: ".sprintf(L("Failed to uninstall %s"),$url)."\n\n".implode("\n", $r);
		} else {
			$c=0;
			foreach($r as $k=>$v) {
				if($v[strlen($v)-1]=="/"||strpos($v,".rootca")!==false)
					unset($r[$k]);
				if(strpos($r[$k],"DIAG-")===false) $c++;
			}
			Core::log('A',"Uninstalled ".$url." ".$this->getsiteurl().", ".($c+0)." files".(Core::$core->runlevel>2?": ".implode(" ",$r):""), "extensions");
			if( $dir == "phppe/Extensions" ) {
				session_destroy();
				unset($_SESSION);
			}
			return "PHPPE-I: ".sprintf(L("Uninstalled %d of %s"), $c+0, $url)."\n\n".implode("\n", $r);
		}
	}

	//get configuration for an extension
	function getConf($dir)
	{
		if(empty($dir)) return;
		if( !Core::$user->has("install") ) {
			Core::log('A',"Suspicious behavior ".$url." ".$this->getsiteurl(),"extensions");
			return "PHPPE-E: ".L("Access denied");
		}

		try {
			$r=\PHPPE\Tools::ssh(
				"cat ".escapeshellarg(Core::$user->data['remote']['path']."/vendor/".$dir."/config.php")
			);
		} catch(\Exception $e) {
			return "PHPPE-E: ".$e->getMessage();
		}

		if( self::isErr($r) || substr($r,0,5)!="<"."?p"."hp" ) {
			Core::log('D',"Failed to get configuration for ".$dir." ".$this->getSiteUrl().", ".str_replace("\n"," ",$r),"extensions");
			return L("Error reading configuration");
		} else {
			$ret = eval("?".">".$r);
			return json_encode($ret);
		}
	}

	//set configuration for an extension
	function setConf($dir)
	{
		if(empty($dir) || empty($_POST)) return "PHPPE-E: ".L("Bad arguments");

		if( !Core::$user->has("install") ) {
			Core::log('A',"Suspicious behavior ".$url." ".$this->getsiteurl(),"extensions");
			return "PHPPE-E: ".L("Access denied");
		}
		//construct new configuration file
		$conf = "<"."?p"."hp\nreturn [\n";
		foreach($_POST as $k=>$v)
			$conf.="\t\"".addslashes($k)."\" => ".$this->formatvalue($v).",\n";
		$conf .= "];\n";

		try {
			$r = \PHPPE\Tools::ssh(
				"cat \>".escapeshellarg(Core::$user->data['remote']['path']."/vendor/".$dir."/config.php"),
				$conf
			);
		} catch(\Exception $e) {
			return "PHPPE-E: ".$e->getMessage();
		}
		if( self::isErr($r) ) {
			Core::log('E',"Failed to set configuration for ".$dir." ".$this->getSiteUrl().", ".str_replace("\n"," ",$r),"extensions");
			return "PHPPE-E: ".L("Failed set configuration!")."\n\n".str_replace("\n"," ",$r);
		} else {
			die(L("Configuration saved.")."\n".$r);
		}
	}
}

