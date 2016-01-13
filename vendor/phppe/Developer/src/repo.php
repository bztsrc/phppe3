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
 * @file vendor/phppe/Developer/src/repo.php
 * @author bzt@phppe.org
 * @date 1 Jan 2016
 * @brief quick and dirty tool to generate packages.json for tarballs and manage community packages
 */
error_reporting(0);ini_set("display_errors",0);

// helpers
function readpkg($pkg,$fn){$body="";if(function_exists("bzopen"))$f=bzopen($pkg,"r");if($f){$data=bzread($f,512);if(substr($data,0,13)!="composer.json" && substr($data,110,13)!="composer.json"){bzclose($f);$f=gzopen($pkg,"rb");$data=gzread($f,512);gzclose($f);$f=gzopen($pkg,"rb");$read="gzread";$close="gzclose";} else {bzclose($f);$f=bzopen($pkg,"rb");$read="bzread";$close="bzclose";}} else {$f=gzopen($pkg,"r");if($f){$data=gzread($f,512);gzclose($f);$f=gzopen($pkg,"rb");$read="gzread";$close="gzclose";}}if(!$f||!$data) return;$ustar=substr($data,257,5)=="ustar"?1:0;if(substr($data,0,13)!="composer.json"&&substr($data,110,13)!="composer.json") {$close($f);return;}while(!feof($f)&&$data){if($ustar){$data=$read($f,512);$size=octdec(substr($data,124,12));if($size>0)$body=$read($f,floor(($size+511)/512)*512);if(substr($data,0,strlen($fn))==$fn) break;$body="";} else {$data=$read($f,110);if(substr($data,0,6)!="070701") break;$size=floor((hexdec(substr($data,54,8))+3)/4)*4;$len=hexdec(substr($data,94,8));$len+=floor((110+$len+3)/4)*4-110-$len;$name=trim($read($f,$len));$body="";if($name=="TRAILER!!!") break;$body=$read($f,$size);if($name==$fn) break;}if(strlen($data)==0) break;}$close($f);return trim($body);}
function metadecode($str){if(function_exists("json_decode")) return json_decode($str,true);$k=$v="";$ret=array();$p="";$a=0;for($i=0;$i<strlen($str);$i++){if($str[$i]=="\""){$i++;$s=$i;while($str[$i]!="\""){if($str[$i]=="\\") $i++;$i++;}$v=substr($str,$s,$i-$s);}if($str[$i]==":") { $k=$v; $v=""; }if($str[$i]==","||$str[$i]=="{"||$str[$i]=="}"||$str[$i]=="["||$str[$i]=="]") {if($k){if($p&&$a) $ret[$p][][$k]=$v; else if($a) $ret[$k][]=$v; else if($p) $ret[$p][$k]=$v; else $ret[$k]=$v;} if($str[$i]=="}")$p="";if($str[$i]=="{")$p=$k;if($str[$i]=="]"){$p="";$a=0;}if($str[$i]=="["){$p=$k;$a=1;}$k=$v=""; }}return $ret;}
function cmp($a,$b){ if($a['keywords'][0]!=$b['keywords'][0]) return $a['keywords'][0]>=$b['keywords'][0];if($a['prio']+0!=$b['prio']+0)return($a['prio']+0<$b['prio']+0?1:-1);return ($a['name']<$b['name']?-1:1);}
function dumppkg($p,$m,&$json,$l=0){$f=1;foreach($m as $k=>$v) if($k!="prio"){$json.=($f?"":",\n").str_repeat("\t",$l+3)."\"".str_replace("\\'","'",addslashes($k))."\":";$f=0;if(is_array($v)||is_object($v)) {$json.=isset($v[0])?"[\"".$v[0]."\"":"{\n";dumppkg($k,$v,$json,$l+1);$json.="\n".str_repeat("\t",$l+3).(isset($v[0])?"]":"}");} else $json.="\"".str_replace("\\'","'",addslashes($v))."\"";}}
function community($db=null){$fn="packages.db";if(!file_exists($fn)&&file_exists("../".$fn)) $fn="../".$fn;if(!file_exists($fn)&&file_exists("public/".$fn)) $fn="public/".$fn;if(is_array($db) && !empty($db)){$str=""; foreach($db as $r) if(!empty($r)) $str.=implode("|",$r)."\n";return file_put_contents($fn,$str,LOCK_EX);}$db=array();if(file_exists($fn)) {$s=explode("\n",file_get_contents($fn));foreach($s as $l){$d=explode("|",$l);	if(!empty($d[0]))$db[$d[0]."/".$d[1]]=$d;}}return $db;}

// check for tarball download with token - should work in a browser too
if( !empty($_REQUEST['install']) ) {
    if( strpos($_REQUEST['install'],"/")!==false || !file_exists($_REQUEST['install']) ) {
	header("HTTP/1.1 404 Not found");
	die("404 Not found");
    }
    $f=fileperms($_REQUEST['install']) & 0004;
    if( !$f && (empty($_REQUEST['key']) ||
	!file_exists("data/".sha1($_REQUEST['key']))||
	trim(file_get_contents("data/".sha1($_REQUEST['key'])))!=$_REQUEST['install']) ) {
	header("HTTP/1.1 403 Access Denied");
	die("403 Access Denied");
    }
    $d=file_get_contents($_REQUEST['install']);
    header("Content-type: application/octet-stream");
    header("Content-length: ".strlen($d));
    die($d);
}

/*****************************************
 * called by a real user, returning HTML *
 *****************************************/

if(!empty($_SERVER['HTTP_USER_AGENT'])&&
    strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"phppe")===false&&
    strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"curl")===false&&
    strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"wget")===false&&
    !isset($_REQUEST['force'])
    )
{
	// Community Extension add
	header("Content-type:text/html;charset=utf-8;");
	echo("<html><head><meta http-equiv='Content-Type' content='text/html;charset=utf-8'/><meta name='viewport' content='width=device-width,initial-scale=1.0'/>");
	echo("<link rel='shortcut icon' href='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAABX1BMVEUAAAABAAA4AAwRAAQAAAAAAAAAAAACAAAEAAAAAAAAAAAAAAAAAAAAAAADAAAAAAAEAAABAAAAAAAEAABqABoAAABZABZRABRJABEAAAABAAAAAAAAAAAAAAAAAAABAAACAAACAAAEAAACAAAEAAAHAAJlABlbABYAAABfABcAAABYABZjABkAAAAAAAAZAAUzAAw/AA4AAAAOAAJmABkBAAAKAAEAAAAAAAAAAAABAABlABkBAAABAAAAAABlABlVABVTABRAAA9OABNrABtRABQ0AA1tABxUABQlAAgAAABKABNmABprABtqABsrAAprABsYAAUAAAAlAAlUABUyAAxdABdeABhgABgXAAUZAAUQAANoABoWAAUkAAkNAAI9AA8qAApoABpKABNmABoBAAADAAABAABjABkGAAFwABwBAABpABqdACgAAABvABxtABtqABpzABxnABlhABcEO3+uAAAAbnRSTlMAEgQGa15UIhbiqII/+76HclFFJfz29fPo1MagkYxoYk8sKR0QDPzy8vDu7efn29nT0Le2tLKcnJR9dm9aVjg3/fny7+7t7enf3dza19TR0c/MzMvHxcLAurmzpqOJhHt4cGVlUUxIQj8zMhoYD/Q5AO0AAAF2SURBVDjL3dHlbutAEAXgY3ZshzkNQxlv4ZZ7mW+ZmbmztpP3V9dtlUptlQfo92s1Gp3Z2cX7I1UEtKRQFi2NU+HNujYTVotXIhAg8yxSAfSwqoZnxWbDR/IYEHzE9RUhk2dUx5N+MpVgN51blFbkDAXRRsFps48UPLrx+6rgRTlEJjBBci3tF7zcyaeGCAUk4AOpBhUx1dvxbz45KGBuPdE74m0t8ok5wCJfedlfwohtO47LDk++8cMvtQouS4HQWDdNa3GfcLfo/AgMfGJdPezz762dPI/wwuPxznQBF50Grn82BoKZL65Tt+s9e5Eon82X86uzZX7QLQ2nrDEsZzpYzG5PDR2Uxcc7/kHTlJ3Y3v/L2lN2fWEsFIUn3D+Bpl3HZq4bGxzuYszdLD0kSLXnN7397yS+JlMrRuho9XtyLa/jhbmYs2SM58xCtGZNZnPKq98/Zo3RfEmrVHmwrkV1CS9cbgy1WSJaEGYUDa2Ikoj36B5oQEN9Fb1CSAAAAABJRU5ErkJggg=='/>");
	echo("<style type='text/css'>body {padding-top:30px;}body,td,div{font-family:sans-serif;font-size:14px;}a{color:#000;text-decoration:none;}a:hover{color:#202020;text-decoration:underline;}#header {position:fixed;z-index:6;top:0px;left:0px;width:100%;padding: 0px 2px 0px 2px;background-color:rgba(136, 146, 191, 0.8);background:linear-gradient(rgba(136,146,191,0.4),rgba(136,146,191,0.6),rgba(136,146,191,0.8),rgba(136,146,191,0.9),rgba(136,146,191,1) 90%,rgba(0,0,0,1));height:31px;}#header DIV.title {position:fixed;top:4px;width:80%;text-align:center;font-family:sans-serif;line-height:20px;font-size:20px;font-weight:bold;text-shadow:2px 2px 2px #FFFFFF;}#header DIV.dock {position:fixed;top:0px;right:40px;font-size:24px;line-height:24px;}#header IMG {border:0px;}</style>");
	echo("<script type='text/javascript'>function ext_search(o){var i,l=document.getElementById('exts').getElementsByTagName('DIV'),r=new RegExp(o.value,'i');for(i=0;i<l.length;i++)l[i].style.display=o.value==''||l[i].textContent.match(r)?'inline-block':'none';}</script>");
	echo("</head><body style='font-family:helvetica;'><div id='header'><a href='https://github.com/bztsrc/phppe3/'><img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACoAAAAYCAMAAAC7r5/PAAABd1BMVEUAAAAAAAAXAAUAAAALAAMAAAAAAAADAABqABsAAAABAAAAAAAAAAAAAAAAAAAEAAEaAAZnABoAAAAAAAAAAAAAAAABAAAAAAACAAAAAAAAAABqABtbABdVABYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAABjABloABtfABgAAAACAAAAAAAqAAoAAAABAAADAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAABqABtYABdqABsAAAAWAAVqABsCAAACAAAAAAAAAABnABoAAABMABNbABdUABU8AA85AA5qABsXAAVIABJlABocAAYLAAIYAAUAAAAmAAkxAAxCABEDAABoABoAAABkABkAAABaABc/ABAzAA0lAAllABoXAAVjABouAAwxAAxdABgNAANeABhfABkcAAcaAAY6AA5WABVRABUmAAlnABooAAlqABtMABRqABtpABsAAAAgAAhpABs0AA1pABtZABclAAlpABprABwAAAAF8BXSAAAAe3RSTlMA9AH8BE8GEf4dCd/55yYYDvzabTgjsH5VLCr87eXj076tpY5hUj/59/Tv0c3Mqp6ThXl1cFlIRTLy39zEu7i3TTUxIxX49PDj18/Mw8G/uraWlZOMimEMCgj79vHq6ebg4NfV1c7NycXDwa+upaWSf3ZyaVxTRD0wHRaUCwYlAAACPElEQVQ4y42TZXPbQBBAT2ChBZYtU8wUx0yxA+Y41DAnZWbm9vbH92RNG7fjafK+aEf39u52dw79y3w070HXIwmKeU01Bm3pP8uig9DPmBxCvAwxh6MVuECIzli/W0N9Uq1RFkq0xqGhE5wUlTPKPNINipCTe8FLk44BZcgKkdLIDYwsR5wAfjSgIGLIFAOlyxvpCnh13nRTUObiYGgjscrAPHIzjEfXWgWIqn/UBYrxkY+qQGlkQImEKQaatBdkHqGzEhhBmv7dSqfTb2Xk4CDgJFnsh6fh8E53aaMj0ez3V+GlnTc8Z1/VxyhW1+vApP0M5Uc/b+AxM/e/aqfLMyR6khHHqpSAqInoQASiPMkaoJVl7FoLzRHl5fEixrOh0Iu6PcFRGyKVSk8BuSmVoWCiL4t4Ne/N3yZWWBBW7/USB42FsWrKYMEU0pwWAy9Pf8ziW7Fk4Q7GggsLN9e3G36PNlY9DMSrvpRHJ7NyVx0s+w67HiQa+U2iWrZrez5gl2V1fYAmOO9iV7HifnwX43WM17b2K6mAZHcrATlpUtWX8Wzx7fHSDJ57KODZzddHn0XOXmpH4miSb1k8Rpgr7oUEK3hvntm7BgPiX+qpbbrCRW/y6BFpa/bQQbadRhfjja3nux1voppe6B/u73bKn9Sp6jmZ1bN40lerpzKiLmZS9Zq7qbHT1BVSzl6yPwyqIs/RHC+qQVW7QFPVxWwo3lAljmWtWmiWtYKpsD/8JydpcuLV0FKw2VKnPMVfJ0hwIWgJ4pEAAAAASUVORK5CYII=' alt='PHPPE' style='margin:3px 32px -3px 40px;height:24px !important;' width='42' height='24'></a>");
	echo("<div id='title' class='title'>PHP Portal Engine Community Extensions</div><div class='dock'>⌕<input id='search' type='text' style='height:20px !important;line-height:24px !important;font-size:10px !important;' onkeyup='ext_search(this);'></div></div><h2>Add your Extension</h2>");
	$err[0]="<span style='color:green;'><span style='font-size:20px;line-height:16px;'>☑</span>";
	$err[1]="<span style='color:blue;'><span style='font-size:18px;line-height:16px;'>⚠</span>";
	$err[2]="<span style='color:red;'><span style='font-size:22px;line-height:16px;'>☠</span>";
	// get community packages
	$db=community();
	if(!empty($_REQUEST['id'])){
		echo("<div style='border-radius:5px;border:1px solid #808080;background:#F0F0F0;margin-bottom:5px;padding:5px;'>");
		$dir="https://raw.githubusercontent.com/".trim($_REQUEST['id']).(!empty($_REQUEST['dir'])?(trim($_REQUEST['dir'])[0]=="/"?"":"/").trim($_REQUEST['dir']):"/master");
		$ok=1; $j=""; $d=[];
		$j=file_get_contents($dir."/composer.json");
		if(empty($j)) {
			echo($err[2]." ".$dir."/composer.json not found</span></br>");$ok=0;
		} else {
			$d=metadecode($j);
			if(!is_array($d)) {
				echo($err[2]." ".$dir."/composer.json parse error: ".json_last_error()."</span></br>");$ok=0;
			} else {
				if(empty($d['name'])||substr($d['name'],0,6)!="phppe/") { echo($err[2]." composer.json parse error: <b>name</b> missing or does not start with '<tt>phppe/</tt>'</span></br>");$ok=0; }
				if(empty($d['name_en'])) {	echo($err[1]." composer.json parse warning: <b>name_en</b> missing no human readable localized name</span></br>"); }
				if(empty($d['version'])||!preg_match("/^[0-9]+\.[0-9]+\.[0-9]+/",$d['version'])) { echo($err[2]." composer.json parse error: <b>version</b> missing or not in x.x.x format</span></br>");$ok=0;	}
				if(empty($d['version_normalized'])) { echo($err[1]." composer.json parse warning: <b>version_normalized</b> missing</span></br>"); }
				if(empty($d['dist'])||$d['dist']['type']!='tar') { echo($err[2]." composer.json parse error: <b>dist</b> / <b>type</b> missing or not '<tt>tar</tt>'</span></br>");$ok=0; }
				if(empty($d['dist'])||empty($d['dist']['url'])) { echo($err[2]." composer.json parse error: <b>dist</b> / <b>url</b> missing</span></br>");$ok=0; }
				$valid=array("Connections","Content","Security","Business","Sales","Office","Games","Banners","Hardware");
				if(empty($d['keywords'])||!in_array($d['keywords'][0],$valid)) { echo($err[2]." composer.json parse error: <b>keywords</b> missing or first keyword (".(!empty($d['keywords'][0])?$d['keywords'][0]:'null').") not one of: ".implode(", ",$valid)."</span></br>");$ok=0; }
				if(empty($d['description'])) { echo($err[1]." composer.json parse warning: <b>description</b> missing</span></br>"); }
				if(empty($d['license'])) { echo($err[1]." composer.json parse error: <b>license</b> missing, defaults to LGPL-3.0+</span></br>"); }
				if(empty($d['maintainer']['name'])) { echo($err[1]." composer.json parse warning: <b>maintainer</b> / <b>name</b> missing</span></br>"); }
				if(empty($d['homepage'])) { echo($err[1]." composer.json parse warning: <b>homepage</b> missing</span></br>"); }
				if(empty($d['support'])) { echo($err[1]." composer.json parse warning: <b>support</b> missing</span></br>"); }
				if($ok) {
					$p=file_get_contents($d['dist']['url']);
					$size=strlen($p);
					$sha=sha1($p);
					if(empty($p)) {	echo($err[2]." ".$d['dist']['url']." returned 404</span></br>");$ok=0; }
				}
			}
		}
		foreach(array('preview','preview.png','preview.svg','preview.gif') as $p) {
			$pr=base64_encode(file_get_contents($dir."/".$p));
			if(!empty($pr)) break;
			echo($err[1]." ".$dir."/".$p." not found</span></br>");
		}
		//! if every validator passed
		if($ok) {
			$db[trim($_REQUEST['id'])."/".trim($_REQUEST['dir'])]=array(trim($_REQUEST['id']),trim($_REQUEST['dir']),base64_encode($j),$pr,intval($size),$sha);
			if(community($db)){
				echo($err[0]." Extension <b>".trim($d['name'])."</b> added to the repository. Thank you for your contribution!</span><br>");
				$c="packages.json"; if(!file_exists($c) && file_exists("../".$c)) $c="../".$c;
				@unlink($c);
			} else
				echo($err[2]." database write error</span><br>");
		}
		echo("</div>");
	}
	echo("<form action='".$_SERVER['PHP_SELF']."' method='get'>");
	echo("https://github.com/<input type='text' name='id' size=20 placeholder='vendor/package' value='".htmlspecialchars(trim($_REQUEST['id']))."'>/<input type='text' size=40 name='dir' placeholder='master' value='".htmlspecialchars(trim($_REQUEST['dir']))."'>&nbsp;<input type='submit'><br>");
	echo("<small style='color:#808080;display:block;margin-top:3px;'>This directory must contain at least a <a href='http://phppe.org/phppe3.html#repository' target='_new' style='color:#808080;font-family:fixed;'>composer.json</a> file with PHP Composer package meta information.</small>");
	if($db) {
		echo("<h2>Extensions</h2><div id='exts'>");
		foreach($db as $v){
				if(is_array($v)) {
					if(empty($v[0])||empty($v[2])) continue;
					$d=metadecode(base64_decode($v[2]));
					echo("<div style='display:inline-block;float:left;margin:5px;padding:3px;border:solid 1px #808080;background:#E0E0E0;min-width:360px;width:32%;overflow:auto;height:160px;box-shadow:3px 3px 6px #000;'>");
					echo("<a href='https://github.com/".($v[0]."/tree/".(empty($v[1])?'master':$v[1]))."' target='_new'><img src='data:image/png;base64,".$v[3]."' width='128' style='margin-right:5px;float:left;'>");
					echo("<b>".htmlspecialchars($d['name_en']?$d['name_en']:$d['name'])." ".$d['version']." </b></a><br>");
					echo("<small><small><nobr><i>".htmlspecialchars($d['name'])." (".round($v[4]/1024)." Kb, ".htmlspecialchars($d['license']).", ".htmlspecialchars($d['maintainer']['name']).") </i></nobr></small>");
					echo("<br><br>".htmlspecialchars($d['description'])." <br><small>");
					echo("<a href='".$d['dist']['url']."' target='_new'>".$d['dist']['url']." <br>".$v[5]."</a></small></small>");
					echo("</div>");
				}
			}
		echo("</div>");
	}
	die("</form></body></html>");
}

/************************************
 * called by agents, returning JSON *
 ************************************/
header("Content-type:application/json;charset=utf-8;");

// generate new package meta info file for PHP Composer and PHPPE Extension Manager
$c="packages.json"; if(!file_exists($c) && file_exists("../".$c)) $c="../".$c;
// failsafe if webserver's packages.json rewrite does not work
if(file_exists($c) && filesize($c)>1024 && filemtime($c)+24*60*60>time()) die(file_get_contents($c));

// get base url
$base=(!empty( $_SERVER["SERVER_NAME"])?$_SERVER["SERVER_NAME"]:"localhost");$d=explode("/",$_SERVER["PHP_SELF"]);foreach( $d as $i){if(substr($i,-4)==".php")break;if($i)$base.="/".$i;}

// get list of packages
$packages = array();

// get community packages
$db=community();
if($db) {
	foreach($db as $v){
		if(is_array($v)) {
			// failsafe
			if(empty($v[0])) continue;
			$dir="https://raw.githubusercontent.com/".$v[0].(!empty($v[1])?($v[1][0]=="/"?"":"/").$v[1]:"/master");
			//get meta info if missing
			if(empty($v[2]) || empty($v[5])) {
				$v[1]=file_get_contents($dir."/composer.json");
				foreach(array('preview','preview.png','preview.svg','preview.gif') as $p) {
					$v[3]=base64_encode(file_get_contents($dir."/".$p));
					if(!empty($v[3])) break;
				}
				//do extra checks on newly downloaded composer.json
				$d=metadecode($v[2]);
				$v[4]=0; $v[5]='';
				$v[2]=base64_encode($v[2]);
				//sanity check
				if(!is_array($d)||
					empty($d['name'])||substr($d['name'],0,6)!="phppe/"||
					empty($d['version'])||
					empty($d['dist'])||$d['dist']['type']!='tar'||empty($d['dist']['url'])||
					empty($d['license'])||
					empty($d['keywords'])||
					!in_array($d['keywords'][0],array("Connections","Content","Security","Business","Sales","Office","Games","Banners","Hardware")))
					continue;
				//get the payload
				$p=file_get_contents($d['dist']['url']);
				if(empty($p)) continue;
				$v[4]=strlen($p);
				$v[5]=sha1($p);
				$db[trim($v[0])."/".trim($v[1])]=array(trim($v[0]),trim($v[1]),$v[2],$v[3],intval($v[4]),$v[5]);
				//save to database
				community($db);
			}
			$m=metadecode(base64_decode($v[2]));
			if(!is_array($m)) continue; //die("JSON Syntax error: ".$v['id']."/composer.json\n");
			// let's copy all the fields
			$packages[$m['name']]=$m;
			// validate fields
			$packages[$m['name']]['prio']=!empty($m["prio"])&&$m["prio"]>0&&$m["prio"]<99900?$m["prio"]+0:0;
			$packages[$m['name']]['name']=!empty($m["name"])?$m["name"]:str_replace("_","/",$v[0]);
			$packages[$m['name']]['version']=!empty($m["version"])?$m["version"]:(!empty($m["version_normalized"])?$m["version_normalized"]:"?.?.?");
			$packages[$m['name']]['dist']['type']=!empty($m["dist"]["type"])?$m["dist"]["type"]:"tar";
			$packages[$m['name']]['dist']['url']=!empty($m["dist"]["url"])?$m["dist"]["url"]:$dir;
			$packages[$m['name']]['description']=!empty($m["description_en"])?$m["description_en"]:(!empty($m["description"])?$m["description"]:"");
			$packages[$m['name']]['keywords']=is_array($m["keywords"])?$m["keywords"]:[];
			$packages[$m['name']]['homepage']=$m["homepage"];
			foreach($m as $K=>$V) if(substr($K,0,5)=="name_"||substr($K,0,12)=="description_"||substr($K,0,9)=="keywords_"||substr($K,0,9)=="homepage_") $packages[$m['name']][$K]=$V;
			$packages[$m['name']]['maintainer']=!empty($m["maintainer"])?$m["maintainer"]:array("name"=>"Anonymous");
			$packages[$m['name']]['license']=!empty($m["license"])?$m["license"]:"LGPL-3.0+";
			$packages[$m['name']]['price']=!empty($m["price"])&&$m["price"]>0?intval($m["price"]): 0;
			if($packages[$m['name']]['price'] && empty($m["homepage"]))
				$packages[$m['name']]['homepage']=dirname($packages[$m['name']]['dist']['url'])."/webshop.php?product=".urlencode($v[0]);
			$packages[$m['name']]['time']=!empty($m['time'])?$m['time']:date("Y-m-d H:i:s",time());
			$packages[$m['name']]['size']=intval($v[4]);//filesize($v);
			$packages[$m['name']]['sha1']=$v[5];//sha1(file_get_contents($v));
			if(!empty($v['preview'])) $packages[$m['name']]['preview']=$v[3];
		}
	}
}

// official packages (will override community versions if any)
$d = glob("*.tgz");
if(empty($d)) $d = glob("../*.tgz");
foreach($d as $v) {
	// get meta info
	$m=metadecode(readpkg($v,"composer.json"));
	if(!is_array($m)) die("JSON Syntax error: ".$v."/composer.json\n");
	// let's copy all the fields
	$packages[$m['name']]=$m;
	// hardwired priorities for environment
	if($m['name']=="phppe") $packages[$m['name']]['prio']=99999; else
	if($m['name']=="phppe/CMS") $packages[$m['name']]['prio']=99998; else
	if($m['name']=="phppe/Extensions") $packages[$m['name']]['prio']=99997; else
	if($m['name']=="phppe/Developer") $packages[$m['name']]['prio']=99996; else
	if($m['name']=="phppe/ClassMap") $packages[$m['name']]['prio']=99995; else
	if($m['name']=="phppe/GPIO") $packages[$m['name']]['prio']=99994; else
	if($m['name']=="phppe/RPi") $packages[$m['name']]['prio']=99993; else
	// validate fields
	$packages[$m['name']]['prio']=!empty($m["prio"])&&$m["prio"]>0&&$m["prio"]<99900?$m["prio"]+0:0;
	$packages[$m['name']]['name']=!empty($m["name"])?$m["name"]:str_replace("_","/",substr($v,0,strlen($v)-4));
	$packages[$m['name']]['version']=!empty($m["version"])?$m["version"]:(!empty($m["version_normalized"])?$m["version_normalized"]:"?.?.?");
	$packages[$m['name']]['dist']['type']=!empty($m["dist"]["type"])?$m["dist"]["type"]:"tar";
	$packages[$m['name']]['dist']['url']=!empty($m["dist"]["url"])?$m["dist"]["url"]:"http://".$base."/".$v;
	$packages[$m['name']]['description']=!empty($m["description_en"])?$m["description_en"]:(!empty($m["description"])?$m["description"]:"");
	$packages[$m['name']]['keywords']=is_array($m["keywords"])?$m["keywords"]:[];
	$packages[$m['name']]['homepage']=$m["homepage"];
	foreach($m as $K=>$V) if(substr($K,0,5)=="name_"||substr($K,0,12)=="description_"||substr($K,0,9)=="keywords_"||substr($K,0,9)=="homepage_") $packages[$m['name']][$K]=$V;
	$packages[$m['name']]['maintainer']=!empty($m["maintainer"])?$m["maintainer"]:array("name"=>"Anonymous");
	$packages[$m['name']]['license']=!empty($m["license"])?$m["license"]:"LGPL-3.0+";
	$packages[$m['name']]['price']=!empty($m["price"])&&$m["price"]>0?intval($m["price"]):(fileperms($v) & 0004 ? 0 : 1);
	if($packages[$m['name']]['price'] && empty($m["homepage"]))
		$packages[$m['name']]['homepage']=dirname($packages[$v]['dist']['url'])."/webshop.php?product=".urlencode($packages[$m['name']]['name']);
	$packages[$m['name']]['time']=date("Y-m-d H:i:s",filemtime($v));
	$packages[$m['name']]['size']=filesize($v);
	$packages[$m['name']]['sha1']=sha1(file_get_contents($v));
	$p=readpkg($v,"preview");
	if(empty($p))$p=readpkg($v,"core/images/phppe.png");
	if(!empty($p)) $packages[$m['name']]['preview']=base64_encode($p);
}

usort($packages,"cmp");
// output json
$json="{\n\t\"packages\": {\n";$f=1;
foreach($packages as $p=>$m){
	//failsafe
	if(empty($m['name'])||empty($m['version'])) continue;
	//name and version
    $json.=($f?"":",\n")."\t\t\"".$m['name']."\": {\n\t\t  \"".$m['version']."\": {\n";$f=0;
    //details
    dumppkg($p,$m,$json);
    //block end
    $json.="\n\t\t  }\n\t\t}";
}
$json.="\n\t}\n}\n";
// save it for later
file_put_contents($c,$json);
die($json);
?>