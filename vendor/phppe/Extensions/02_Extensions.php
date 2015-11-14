<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztphp/phppe3/
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
 * @file vendor/phppe/Extensions/02_Extensions.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief PHPPE Extension Manager
 */
namespace PHPPE;
use PHPPE\Core as PHPPE;

class Extensions {
	public $version="1.0.0";

	function init ($cfg)
	{
		//register module and menu
		PHPPE::lib( "Extensions", "Extension Manager", "", $this );
		PHPPE::menu( L("Extensions") ."@install", "extensions" );
		return true;
	}

	function diag()
	{
	}

	//look for error message in output
	private static function iserr($s)
	{
		$s=trim(strtolower($s));
		if( empty($s) )
			return false;
		//typical error indicators at beginning of line
		foreach( array("ssh:","curl:","tar:","find:","rm:","grep:","sed:") as $c )
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

	private static function getsiteurl()
	{
		return PHPPE::$user->data['remote']['user']."@".PHPPE::$user->data['remote']['host'].":".PHPPE::$user->data['remote']['path'];
	}


	private static function formatvalue($v) {
		if($v=="true"||$v=="false"||$v=="null"||$v=="0"||intval($v)!=0||$v[0]=="[") return $v;
		return "'".addslashes($v)."'";
	}

	private static function identity($privkey)
	{
		$fn = tempnam(".tmp", ".id_");
		file_put_contents($fn, trim($privkey)."\n");
		chmod($fn,0400);
		return $fn;
	}

	//return JSON string with version informations
	function getinstalled($skipcache=0)
	{
		if( !PHPPE::$user->has("install") ) {
			PHPPE::log('A',"Suspicious behavior ".$this->getsiteurl());
			return "PHPPE-E: ".L("Access denied");
		}
		$t="";
		//if remote not available, get from local directly
		if( !empty(PHPPE::$user->data['remote']['identity']) && !empty(PHPPE::$user->data['remote']['user']) && !empty(PHPPE::$user->data['remote']['host']) && !empty(PHPPE::$user->data['remote']['path']) ) {
			//get list from remote server
			ob_start();
			$idfile = $this->identity(PHPPE::$user->data['remote']['identity']);
			$cmd="ssh -i ".escapeshellarg($idfile)." -l ".escapeshellarg(PHPPE::$user->data['remote']['user']).
				(!empty(PHPPE::$user->data['remote']['port'])&&PHPPE::$user->data['remote']['port']>0?" -p ".intval(PHPPE::$user->data['remote']['port']):"").
				" ".escapeshellarg(PHPPE::$user->data['remote']['host']).
				" sh -c \\\"cat ".escapeshellarg(PHPPE::$user->data['remote']['path']."/composer.json")." \|grep name \| head -1 \; find ".escapeshellarg(PHPPE::$user->data['remote']['path']."/vendor/phppe")." -type f -name composer.json -exec sh -c \\'cat {} \\| grep -e name[^_] \\|head -1 \\| tr -d \\\\'\\\\n\\\\' \\; cat {} \\| grep -e version[^_] \\' \\\\\; 2>&1 \\\"";
			PHPPE::log('D',$cmd,"extensions");
			passthru($cmd);
			$r=explode("\n",trim(ob_get_clean()));
			unlink($idfile);
			//get site title
			if( !Extensions::iserr($r[0]) ) {
				$d=explode('"',$r[0]);
				$t="{ \"name\": \"".($d[3]=="No name"?L($d[3]):$d[3])."\" }";
			} else
				$t="{ \"name\": \"".PHPPE::e("","",substr(trim($r[0]),0,4)=="cat:"?L("Bootstrap first!"):str_replace("\r","",str_replace("\n"," ",trim(empty($r[1])?$r[0]:$r[1]))))."\" }";
			if( !Extensions::iserr($r[1]) && !Extensions::iserr($r[2])) {
				foreach($r as $v) {
					$d=explode("\"",$v);
					if(!empty($d[3]) && !empty($d[7])) $t.=",{ \"id\": \"".$d[3]."\", \"version\": \"".$d[7]."\" }";
				}
			}
		}
		//fallback to local, but without remote it's read only
		else {
			$t="{ \"name\": \"".PHPPE::e("","",L("configure remote access in Extensions"))."\" }";
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
	function getpkgs($skipcache=0)
	{
		$F = ".tmp/.pkgs_".PHPPE::$client->lang;
		if( !$skipcache && file_exists($F) && filemtime($F)+60*60 > time() )
			$pkgs = file_get_contents($F);

		if( empty($pkgs) )
		{
			$p=array();
			$list = [ "http://phppe.org/", "https://raw.githubusercontent.com/bztsrc/phppe3/master/" ];
			if(!empty(PHPPE::$core->repos)) $list=array_merge(PHPPE::$core->repos,$list);
			foreach($list as $r)
			{
				$d2 = file_get_contents($r.(substr($r,-1)!="/"?"/":"")."packages.json?lang=".PHPPE::$client->lang);
				$d = json_decode($d2,true);
				PHPPE::log('D','Packages from repo: '.$r.' '.(empty($d2)?"404":json_last_error_msg()),"repo");
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
							PHPPE::log('D','- [Skip] '.$pkg.' '.$v[0],"repo");
							continue;
						}
						PHPPE::log('D','- [ OK ] '.$pkg.' '.$v[0],"repo");
						$p[$pkg]['id']=$pkg;
						$p[$pkg]['desc']=!empty($ver[$v[0]]['description_'.PHPPE::$client->lang])?$ver[$v[0]]['description_'.PHPPE::$client->lang]:$ver[$v[0]]['description'];
						$p[$pkg]['name']=!empty($ver[$v[0]]['name_'.PHPPE::$client->lang])?$ver[$v[0]]['name_'.PHPPE::$client->lang]:(!empty($ver[$v[0]]['name_en'])?$ver[$v[0]]['name_en']:$ver[$v[0]]['name']);
						$p[$pkg]['config']=!empty($ver[$v[0]]['conf'])?$ver[$v[0]]['conf']:"";
						$p[$pkg]['conf']=!empty($ver[$v[0]]['conf_'.PHPPE::$client->lang])?$ver[$v[0]]['conf_'.PHPPE::$client->lang]:(!empty($ver[$v[0]]['conf_en'])?$ver[$v[0]]['conf_en']:"");
						$p[$pkg]['help']=!empty($ver[$v[0]]['help_'.PHPPE::$client->lang])?$ver[$v[0]]['help_'.PHPPE::$client->lang]:(!empty($ver[$v[0]]['help_en'])?$ver[$v[0]]['help_en']:"");
						$p[$pkg]['maintainer']=$ver[$v[0]]['maintainer']['name'];
						$p[$pkg]['url']=$ver[$v[0]]['dist']['url'];
						$p[$pkg]['depends']=array_keys($ver[$v[0]]['require']);
						foreach(array("version","license","time","size","sha1","price","preview") as $f)
							@$p[$pkg][$f]=$ver[$v[0]][$f];
						if(empty($p[$pkg]["version"])) $p[$pkg]['version']=$v[0];
						//force a minimal phppe/core configuration
						if($pkg=="phppe" && empty($p[$pkg]['config']))
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
			//fuck, this fails...
			//$pkgs=json_encode($p);
			$pkgs=""; foreach($p as $v) $pkgs.=($pkgs?",":"").json_encode($v);
			if( !empty($p) ) {
				file_put_contents($F,$pkgs);
			}
			PHPPE::log('D','Packages to cache: '.$F.' ('.count($p).' extensions)',"repo");
		} else
			PHPPE::log('D','Packages from cache: '.$F,"repo");

		return $pkgs;
	}

	//bootstrap PHPPE environament on remote server
	function bootstrap()
	{
		if( !PHPPE::$user->has("install") ) {
			PHPPE::log('A',"Suspicious behavior ".$url." ".$this->getsiteurl(),"extensions");
			return "PHPPE-E: ".L("Access denied");
		}
		//check for remote configuration
		if( empty(PHPPE::$user->data['remote']['identity']) || empty(PHPPE::$user->data['remote']['user']) || empty(PHPPE::$user->data['remote']['host']) || empty(PHPPE::$user->data['remote']['path']) )
			return "PHPPE-E: ".L("configure remote access in Extensions");
		//get live image
		$data = @file_get_contents("public/index.php");
		if( empty($data) )
			return "PHPPE-E: ".L("No PHPPE Core?");

		//we cannot install localy, that would use webserver's user, forbidden to write.
		//So we must use remote user identity even when host is localhost.
		ob_start();
		$idfile = $this->identity(PHPPE::$user->data['remote']['identity']);
		$cmd = "ssh -i ".escapeshellarg($idfile)." -l ".escapeshellarg(PHPPE::$user->data['remote']['user']).
			(!empty(PHPPE::$user->data['remote']['port'])&&PHPPE::$user->data['remote']['port']>0?" -p ".intval(PHPPE::$user->data['remote']['port']):"").
			" ".escapeshellarg(PHPPE::$user->data['remote']['host']).
			" sh -c \\\"mkdir -p ".escapeshellarg(PHPPE::$user->data['remote']['path']."/public")." \&\& cat \>".escapeshellarg(PHPPE::$user->data['remote']['path']."/public/index.php")." \\\" 2>&1";
		PHPPE::log('D',$cmd,"extensions");
		$p=popen($cmd, "w");
		if($p){
			fwrite($p,$data);
			pclose($p);
		}
		$cmd="ssh -i ".escapeshellarg($idfile)." -l ".escapeshellarg(PHPPE::$user->data['remote']['user']).
			(!empty(PHPPE::$user->data['remote']['port'])&&PHPPE::$user->data['remote']['port']>0?" -p ".intval(PHPPE::$user->data['remote']['port']):"").
			" ".escapeshellarg(PHPPE::$user->data['remote']['host']).
			" sh -c \\\"php ".escapeshellarg(PHPPE::$user->data['remote']['path']."/public/index.php")." --diag\;sudo php ".escapeshellarg(PHPPE::$user->data['remote']['path']."/public/index.php")." --diag \\\" 2>&1";
		PHPPE::log('D',$cmd,"extensions");
		passthru($cmd);
		$r=explode("\n",trim(ob_get_clean()));
		unlink($idfile);
		if( Extensions::iserr($r[0]) ) {
			PHPPE::log('E',"Failed to bootstrap PHPPE to ".$this->getsiteurl(),"extensions");
			return "PHPPE-E: ".sprintf(L("Failed to install %s"),"Core")."\nPHPPE-E: ".$this->getsiteurl()."\n\n".implode("\n",$r);
		} else {
			PHPPE::log('A',"Installed PHPPE to ".$this->getsiteurl(),"extensions");
			return "PHPPE-I: ".sprintf(L("Installed %d files from %s"),1,"public/index.php")."\nPHPPE-I: ".$this->getsiteurl()."\n\n".implode("\n",$r);
		}
	}

	//install a tarball via ssh to remote server
	function install($param,$instdep=true)
	{
		$out="";
		if(strtolower($_REQUEST['item'])==strtolower($param))
			$param=$_REQUEST['item'];

		list($url,$dir)=explode("#",$param);
		if(empty($dir)) $dir="phppe";
		if( !PHPPE::$user->has("install") ) {
			PHPPE::log('A',"Suspicious behavior ".$url." ".$this->getsiteurl(),"extensions");
			return "PHPPE-E: ".L("Access denied");
		}

		//installation check
		$inst=json_decode("[".$this->getinstalled()."]",true);
		$instidx=[];
		foreach($inst as $i) if(!empty($i['id'])) $instidx[strtolower($i['id'])]=$i['version'];
		//also install dependencies if this is the first install
		if($instdep && empty($instidx[strtolower($dir)])){
			//build dependency tree
			$pkg=[];
			$pkgs=json_decode("[".preg_replace("/,\"preview\":\"[^\"]+\"/","",$this->getpkgs())."]",true);
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
		//check for remote configuration
		if( empty(PHPPE::$user->data['remote']['identity']) || empty(PHPPE::$user->data['remote']['user']) || empty(PHPPE::$user->data['remote']['host']) || empty(PHPPE::$user->data['remote']['path']) )
			return "PHPPE-E: ".L("configure remote access in Extensions");
		//we cannot install localy, that would use webserver's user, forbidden to write.
		//So we must use remote user identity even when host is localhost.
		$d=array(0=>array("pipe","r"),1=>array("pipe","w"));
		if(substr($url,0,6)=="https:")
			$ca=@file_get_contents("vendor/phppe/Extensions/.rootca");
		$idfile = $this->identity(PHPPE::$user->data['remote']['identity']);
		$cmd = "ssh -i ".escapeshellarg($idfile)." -l ".escapeshellarg(PHPPE::$user->data['remote']['user']).
			(!empty(PHPPE::$user->data['remote']['port'])&&PHPPE::$user->data['remote']['port']>0?" -p ".intval(PHPPE::$user->data['remote']['port']):"").
			" ".escapeshellarg(PHPPE::$user->data['remote']['host']).
			" sh -c \\\"mkdir -p ".escapeshellarg(PHPPE::$user->data['remote']['path']."/vendor/".$dir)." \&\& curl ".(!empty($ca)?"--cacert '/dev/stdin'":"")." -sL ".escapeshellarg($url)." \\| tar -xzv --exclude preview -C ".escapeshellarg(PHPPE::$user->data['remote']['path']."/vendor/".$dir)." \\\" 2>&1";
		PHPPE::log('D',$cmd,"extensions");
		$pr=proc_open($cmd,
			$d,$p
		);
		if(is_array($p)) {
			if( !empty($ca) )
				fwrite($p[0],$ca);
			fclose($p[0]);
			$r=trim(stream_get_contents($p[1]));
			fclose($p[1]);
			proc_close($pr);
			//run diagnostics to check newly created file's access rights
			$cmd="ssh -i ".escapeshellarg($idfile)." -l ".escapeshellarg(PHPPE::$user->data['remote']['user']).
				(!empty(PHPPE::$user->data['remote']['port'])&&PHPPE::$user->data['remote']['port']>0?" -p ".intval(PHPPE::$user->data['remote']['port']):"").
				" ".escapeshellarg(PHPPE::$user->data['remote']['host']).
				" sh -c \\\"php ".escapeshellarg(PHPPE::$user->data['remote']['path']."/public/index.php")." --diag \\\" 2>&1";
			PHPPE::log('D',$cmd,"extensions");
			ob_start();
			passthru($cmd);
			$r=explode("\n",$r."\n\n".ob_get_clean());
		} else $r=array();
		unlink($idfile);
		if( Extensions::iserr($r[0]) || Extensions::iserr($r[1]) || Extensions::iserr($r[2]) ) {
			foreach($r as $k=>$v) if(strpos($v,".rootca")!==false) unset($r[$k]);
			PHPPE::log('E',"Failed to install ".$url." to ".$this->getsiteurl().", ".implode(" ",$r),"extensions");
			return $out."PHPPE-E: ".sprintf(L("Failed to install %s"),$url)."\n\n".implode("\n",$r);
		} else {
			$c=0;
			foreach($r as $k=>$v) {
				if($v[strlen($v)-1]=="/"||strpos($v,".rootca")!==false)
					unset($r[$k]);
				else if($v[0]=="x" && $v[1]==" ") $r[$k]="vendor/".$dir."/".substr($v,2);
				if(substr($r[$k],0,6)=="vendor"&&strpos($r[$k],"DIAG-")===false) $c++;
			}
			PHPPE::log('A',"Installed ".$url." to ".$this->getsiteurl().", ".($c+0)." files".(PHPPE::$core->runlevel>1?": ".implode(" ",$r):""),"extensions");
			return $out."PHPPE-I: ".sprintf(L("Installed %d files from %s"),$c+0,$url)."\n\n".implode("\n",$r);
		}
	}

	//remove files specified in a tarball via ssh from remote server
	function uninstall($param)
	{
		list($url,$dir)=explode("#",$_REQUEST['item']);
		if(empty($dir)) $dir="phppe";
		if( !PHPPE::$user->has("install") ) {
			PHPPE::log('A',"Suspicious behavior ".$url." ".$this->getsiteurl(),"extensions");
			return "PHPPE-E: ".L("Access denied");
		}
		//check for remote configuration
		if( empty(PHPPE::$user->data['remote']['identity']) || empty(PHPPE::$user->data['remote']['user']) || empty(PHPPE::$user->data['remote']['host']) || empty(PHPPE::$user->data['remote']['path']) )
			return "PHPPE-E: ".L("configure remote access in Extensions");
		//we cannot remove localy, that would use webserver's user, forbidden to write.
		//So we must use remote user identity even when host is localhost.
		$d=array(0=>array("pipe","r"),1=>array("pipe","w"));
		if(substr($url,0,6)=="https:")
			$ca=@file_get_contents("vendor/phppe/Extensions/.rootca");
		$idfile = $this->identity(PHPPE::$user->data['remote']['identity']);
		$cmd = "ssh -i ".escapeshellarg($idfile)." -l ".escapeshellarg(PHPPE::$user->data['remote']['user']).
			(!empty(PHPPE::$user->data['remote']['port'])&&PHPPE::$user->data['remote']['port']>0?" -p ".intval(PHPPE::$user->data['remote']['port']):"").
			" ".escapeshellarg(PHPPE::$user->data['remote']['host']).
			//don't allow removal of vendor/phppe
			(strpos($dir,"/")===false?
			" sh -c \\\"curl ".(!empty($ca)?"--cacert '/dev/stdin'":"")." -sL ".escapeshellarg($url)." \\| tar -tz \\| grep -v preview \\| grep -v core/config.php \\| grep -v \'/\$\' \\| ".
			"sed \'s#^#".escapeshellarg(PHPPE::$user->data['remote']['path']."/vendor/".$dir)."/#\' \\| xargs rm -vf \\\" 2>&1"
			:
			" rm -rvf ".escapeshellarg(PHPPE::$user->data['remote']['path']."/vendor/".$dir)." 2>&1");
		PHPPE::log('D',$cmd,"extensions");
		$pr=proc_open($cmd,$d,$p);
		if(is_array($p)) {
			if( !empty($ca) )
				fwrite($p[0],$ca);
			fclose($p[0]);
			$r=trim(stream_get_contents($p[1]));
			fclose($p[1]);
			proc_close($pr);
		} else $r="";
		//extra cleanup when removing PHPPE Pack
		if($dir=="phppe" || $dir=="phppe/core") {
			//remove empty additional directories
			$cmd = "ssh -i ".escapeshellarg($idfile)." -l ".escapeshellarg(PHPPE::$user->data['remote']['user']).
				(!empty(PHPPE::$user->data['remote']['port'])&&PHPPE::$user->data['remote']['port']>0?" -p ".intval(PHPPE::$user->data['remote']['port']):"").
				" ".escapeshellarg(PHPPE::$user->data['remote']['host']).
				" rm -rvf ".
					escapeshellarg(PHPPE::$user->data['remote']['path']."/vendor/phppe/Email")." ".
					escapeshellarg(PHPPE::$user->data['remote']['path']."/vendor/phppe/Users")." ".
					escapeshellarg(PHPPE::$user->data['remote']['path']."/vendor/phppe/DB")." ".
					escapeshellarg(PHPPE::$user->data['remote']['path']."/vendor/phppe/GPIO")." ".
					"2>&1";
			PHPPE::log('D',$cmd,"extensions");
			ob_start();
			passthru($cmd);
			$r.="\n".trim(ob_get_clean());
		}
		if($dir=="phppe/ClassMap") {
			//remove empty additional directories
			$cmd = "ssh -i ".escapeshellarg($idfile)." -l ".escapeshellarg(PHPPE::$user->data['remote']['user']).
				(!empty(PHPPE::$user->data['remote']['port'])&&PHPPE::$user->data['remote']['port']>0?" -p ".intval(PHPPE::$user->data['remote']['port']):"").
				" ".escapeshellarg(PHPPE::$user->data['remote']['host']).
				" rm -vf ".
					escapeshellarg(PHPPE::$user->data['remote']['path']."/vendor/autoload.php")." ".
					"2>&1";
			PHPPE::log('D',$cmd,"extensions");
			ob_start();
			passthru($cmd);
			$r.="\n".trim(ob_get_clean());
		}
		//call diag mode to create default files
		$cmd = "ssh -i ".escapeshellarg($idfile)." -l ".escapeshellarg(PHPPE::$user->data['remote']['user']).
			(!empty(PHPPE::$user->data['remote']['port'])&&PHPPE::$user->data['remote']['port']>0?" -p ".intval(PHPPE::$user->data['remote']['port']):"").
			" ".escapeshellarg(PHPPE::$user->data['remote']['host']).
			" sh -c \\\"php ".escapeshellarg(PHPPE::$user->data['remote']['path']."/public/index.php")." --diag \\\" 2>&1";
		PHPPE::log('D',$cmd,"extensions");
		ob_start();
		passthru($cmd);
		$r=explode("\n",$r."\n\n".trim(ob_get_clean()));
		unlink($idfile);
		if( Extensions::iserr($r[0]) || Extensions::iserr($r[1]) || Extensions::iserr($r[2]) ) {
			foreach($r as $k=>$v) if(strpos($v,".rootca")!==false) unset($r[$k]);
			PHPPE::log('E',"Failed to uninstall ".$url." ".$this->getsiteurl().", ".implode(" ",$r),"extensions");
			return "PHPPE-E: ".sprintf(L("Failed to uninstall %s"),$url)."\n\n".implode("\n",$r);
		} else {
			$c=0;
			foreach($r as $k=>$v) {
				if(strpos($v,".rootca")!==false) unset($r[$k]);
				elseif(substr($v,0,strlen(PHPPE::$user->data['remote']['path']))==PHPPE::$user->data['remote']['path']) $r[$k]=substr($v,strlen(PHPPE::$user->data['remote']['path'])+1);
				if(substr($r[$k],0,6)=="vendor"&&strpos($r[$k],"DIAG-")===false) $c++;
			}
			PHPPE::log('A',"Uninstalled ".$url." ".$this->getsiteurl().", ".($c+0)." files".(PHPPE::$core->runlevel>1?": ".implode(" ",$r):""),"extensions");
			if( explode(".",basename($url))[0] == "phppe3_extmgr" ) {
				session_destroy();
				unset($_SESSION);
			}
			return "PHPPE-I: ".sprintf(L("Uninstalled %d files of %s"),$c+0,$url)."\n\n".implode("\n",$r);
		}
	}

	//get configuration for an extension
	function getconf($dir)
	{
		if(empty($dir)) return;
		if( !PHPPE::$user->has("install") ) {
			PHPPE::log('A',"Suspicious behavior ".$url." ".$this->getsiteurl(),"extensions");
			return "PHPPE-E: ".L("Access denied");
		}

		//check for remote configuration
		if( empty(PHPPE::$user->data['remote']['identity']) || empty(PHPPE::$user->data['remote']['user']) || empty(PHPPE::$user->data['remote']['host']) || empty(PHPPE::$user->data['remote']['path']) )
			return "PHPPE-E: ".L("configure remote access in Extensions");
		$idfile = $this->identity(PHPPE::$user->data['remote']['identity']);
		//we cannot install localy, that would use webserver's user, forbidden to write.
		//So we must use remote user identity even when host is localhost.
		if($dir=="phppe") $dir.="/core";
		$d=array(0=>array("pipe","r"),1=>array("pipe","w"));
		$cmd = "ssh -i ".escapeshellarg($idfile).
			" -l ".escapeshellarg(PHPPE::$user->data['remote']['user']).
			(!empty(PHPPE::$user->data['remote']['port'])&&PHPPE::$user->data['remote']['port']>0?" -p ".intval(PHPPE::$user->data['remote']['port']):"").
			" ".escapeshellarg(PHPPE::$user->data['remote']['host']).
			" sh -c \\\"cat ".escapeshellarg(PHPPE::$user->data['remote']['path']."/vendor/".$dir."/config.php")." \\\" 2>&1";
		PHPPE::log('D',$cmd,"extensions");
		$pr=proc_open($cmd,$d,$p);
		if(is_array($p)) {
			fclose($p[0]);
			$r=trim(stream_get_contents($p[1]));
			fclose($p[1]);
			proc_close($pr);
		} else $r="";
		unlink($idfile);
		if( Extensions::iserr($r) || substr($r,0,5)!="<"."?p"."hp" ) {
			PHPPE::log('E',"Failed to get configuration for ".$dir." ".$this->getsiteurl().", ".str_replace("\n"," ",$r),"extensions");
			return "";
		} else {
			$core = new \stdClass();
			$ret = eval("?".">".$r);
			return json_encode($dir=="phppe/core"?$core:$ret);
		}
	}

	//set configuration for an extension
	function setconf($dir)
	{
		if(empty($dir) || empty($_POST)) return;
		if( !PHPPE::$user->has("install") ) {
			PHPPE::log('A',"Suspicious behavior ".$url." ".$this->getsiteurl(),"extensions");
			return "PHPPE-E: ".L("Access denied");
		}
		//check for remote configuration
		if( empty(PHPPE::$user->data['remote']['identity']) || empty(PHPPE::$user->data['remote']['user']) || empty(PHPPE::$user->data['remote']['host']) || empty(PHPPE::$user->data['remote']['path']) )
			return "PHPPE-E: ".L("configure remote access in Extensions");
		//construct new configuration file
		if($dir=="phppe") {
			$dir.="/core";
			$conf="<"."?p"."hp\n\n";
			foreach($_POST as $k=>$v) if($v!="") $conf.="\$core->".$k." = ".$this->formatvalue($v).";\n";
		} else {
			$conf="<"."?p"."hp\nreturn array(\n";
			foreach($_POST as $k=>$v) $conf.="\t\"".addslashes($k)."\" => ".$this->formatvalue($v).",\n";
			$conf.=");\n";
		}
		//we cannot install localy, that would use webserver's user, forbidden to write.
		//So we must use remote user identity even when host is localhost.
		$d=array(0=>array("pipe","r"),1=>array("pipe","w"));
		$idfile = $this->identity(PHPPE::$user->data['remote']['identity']);
		$cmd = "ssh -i ".escapeshellarg($idfile)." -l ".escapeshellarg(PHPPE::$user->data['remote']['user']).
			(!empty(PHPPE::$user->data['remote']['port'])&&PHPPE::$user->data['remote']['port']>0?" -p ".intval(PHPPE::$user->data['remote']['port']):"").
			" ".escapeshellarg(PHPPE::$user->data['remote']['host']).
			" sh -c \\\"cat \>".escapeshellarg(PHPPE::$user->data['remote']['path']."/vendor/".$dir."/config.php")." \\\" 2>&1";
		PHPPE::log('D',$cmd,"extensions");
		$pr=proc_open($cmd,$d,$p);
		if(is_array($p)) {
			fwrite($p[0],$conf);
			fclose($p[0]);
			$r=trim(stream_get_contents($p[1]));
			fclose($p[1]);
			proc_close($pr);
		} else $r="";
		unlink($idfile);
		if( Extensions::iserr($r) ) {
			PHPPE::log('E',"Failed to set configuration for ".$dir." ".$this->getsiteurl().", ".str_replace("\n"," ",$r),"extensions");
			return "PHPPE-E: ".L("Failed set configuration!")."\n\n".str_replace("\n"," ",$r);
		} else {
			die(L("Configuration saved.")."\n".$r);
		}
	}
}
?>