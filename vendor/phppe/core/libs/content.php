<?php
/**
 *  PHP Portal Engine v3.0.0
 *  https://github.com/bztsrc/phppe3/
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
 * @file vendor/phppe/core/libs/tools.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief PHPPE Core Extensions
 */
namespace PHPPE;

class Content {
		public $version = VERSION;
		public $name;
		//! we do not register ourself, because we are going to be registered by 00_core.php
		function __construct() {}

		//! collapse a name into a string id
		static function collapse($str) {
			return strtolower(preg_replace("/[\ ]+/","_",preg_replace("/[^a-zA-Z0-9\.\ \(\)!@%:_-]+/","",trim(strtr(str_replace(["\n","\r","\t"],[" "," "," "],$str),
				"àÀáÁâÂãÃäÄåÅÆæĀāĂăĄąèÈéÉêÊëËìÌíÍîÎïÏðÐñÑòÒôÔõÕóÓöÖőŐúÚüÜűŰ",
				"aaaaaaaaaaaaaaaaaaaaeeeeeeeeiiiiiiiiddnnoooooooooooouuuuuu"
				)))));
		}
		//! accent safe string to upper
		static function toupper($str){
			return strtoupper(strtr($str,
			"àá",
			"ÀÁ"));
		}
		//! accent safe string to lower
		static function tolower($str){
			return strtolower(strtr($str,
			"ÀÁ",
			"àá"));
		}
		//! recursive directory delete
		static function rmdir($dir) {
			if(is_dir($dir)){
				$d=glob($dir."/*");
				foreach($d as $v)
					self::rmdir($v);
				rmdir($dir);
			} else
				unlink($dir);
		}
		//! recursive directory structure creation
		static function mkdir($pn) {
			if(is_dir($pn)||empty($pn)) return true;
			$next_pathname=substr($pn,0,strrpos($pn,DIRECTORY_SEPARATOR));
			if(self::mkdir($next_pathname)) {if(!file_exists($pn)) {return mkdir($pn,0777);} }
			return false;
		}
		//! archive extractor (file can be pkzip,gz,bz2,tar,cpio,pax)
		static function untar($file,$fn=""){
			//! detect format
			$body="";$f=gzopen($file,"rb");if($f){$read="gzread";$close="gzclose";$close="gzclose";$open="gzopen";}else{$f=bzopen($file,"rb");if($f){$read="bzread";$close="bzclose";$close="bzclose";$open="bzopen";}else throw new \Exception(L("Unable to open ").": ".$file);}
			//! read archive
			$data=$read($f,512);$close($f);if($data[0]=='P'&&$data[1]=='K') {$zip=zip_open($file);if(!$zip) throw new \Exception(L("Unable to open ").": ".$file);while($zip_entry=zip_read($zip)) {$zname=zip_entry_name($zip_entry);if(!zip_entry_open($zip,$zip_entry,"r")) continue;$zip_fs=zip_entry_filesize($zip_entry);if(empty($zip_fs)) continue;$body=zip_entry_read($zip_entry,$zip_fs);if(!empty($fn) && is_string($fn)) { zip_entry_close($zip_entry); zip_close($zip); return $body; }if(is_array($fn) && method_exists($fn[0],$fn[1])) call_user_func($fn,$zname,$body);zip_entry_close($zip_entry);}zip_close($zip);return;}
			$f=$open($file,"rb");$ustar=substr($data,257,5)=="ustar"?1:0;while(!feof($f)&&$data){$name="";if($ustar){$data=$read($f,512);$size=octdec(substr($data,124,12));$body=$size>0?$read($f,floor(($size+511)/512)*512):"";$i=0;while(isset($data[$i])&&ord($data[$i])!=0&&$i<512)$i++;$name=substr($data,0,$i);} else{$data=$read($f,110);if(substr($data,0,6)!="070701") throw new \Exception(L("Bad format"));$size=floor((hexdec(substr($data,54,8))+3)/4)*4;$len=hexdec(substr($data,94,8));$len+=floor((110+$len+3)/4)*4-110-$len;$name=trim($read($f,$len));$body="";if($name=="TRAILER!!!") break;$body=$read($f,$size);}if(empty($name)) {$close($f);return "";}
			//! if argument was a filename, return it's contents
			if(!empty($fn) && is_string($fn) && $name==$fn) {$close($f);return substr($body,0,$size);}
			//! if argument was an array with class and method name, call it on every file in the archive
			if($size>0 && is_array($fn) && method_exists($fn[0],$fn[1])) call_user_func($fn,$name,substr($body,0,$size));
			}$close($f);
		}
		//! copy files to a remote server over a secure channel
		static function copy($files,$dest="")
		{
			//! check for remote configuration
			if( empty(Core::$user->data['remote']['identity']) || empty(Core::$user->data['remote']['user']) || empty(Core::$user->data['remote']['host']) || empty(Core::$user->data['remote']['path']) )
				throw new \Exception("PHPPE-E: ".L("configure remote access"));

			//! we cannot install localy, that would use webserver's user, forbidden to write.
			//! So we must use remote user identity even when host is localhost.
			ob_start();
			$idfile = tempnam(".tmp", ".id_");
			file_put_contents($idfile, trim(Core::$user->data['remote']['identity'])."\n");
			chmod($idfile,0400);
			foreach($files as $k=>$v) $files[$k]=escapeshellarg($v);
			$cmd="tar -cz ".implode(" ",$files)."|ssh -i ".escapeshellarg($idfile)." -l ".escapeshellarg(Core::$user->data['remote']['user']).
				(!empty(Core::$user->data['remote']['port'])&&Core::$user->data['remote']['port']>0?" -p ".intval(Core::$user->data['remote']['port']):"").
				" ".escapeshellarg(Core::$user->data['remote']['host']).
				" sh -c \\\" tar -xvz ".($dest?" -C ".escapeshellarg(Core::$user->data['remote']['path']."/".$dest):"")." 2>\&1 \\\" 2>&1";
			passthru($cmd);
			$r=trim(ob_get_clean());
			unlink($idfile);
			if( in_array(substr($r,0,4),["ssh:","tar:"])||substr($r,0,3)=="sh:" )
				throw new \Exception("PHPPE-E: ".sprintf(L("failed to copy %d files to %s"),count($files),Core::$user->data['remote']['user']."@".Core::$user->data['remote']['host'].":".Core::$user->data['remote']['path']."/".$dest)
					.": ".explode("\n",$r)[0]);
			return $r;
		}
		//! default action handler executes code stored in db in pages.ctrl field
                function action($a)
                {
                        //! as this could be considered as a security risk, this feature can be turned off globally
                        if(! empty(Core::$core->noctrl) || empty($a[ 'ctrl' ]) || ! Core::istry())
                                return;
                        ob_start();
                        //FIXME: sanitize php code
                        eval("namespace PHPPE\Ctrl;\nuse PHPPE\Core as PHPPE;\n" . $a[ 'ctrl' ]);
                        return o();
                }
	}
?>