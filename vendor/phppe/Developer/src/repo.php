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
 * @file vendor/phppe/Developer/src/repo.php
 * @author bzt@phppe.org
 * @date 1 Jan 2015
 * @brief quick and dirty tool to generate packages.json for tarballs and serve requests with tokens
 */
error_reporting(0);
// helpers
function readpkg($pkg,$fn){$body="";if(function_exists("bzopen"))$f=bzopen($pkg,"r");if($f){$data=bzread($f,512);if(substr($data,0,13)!="composer.json" && substr($data,110,13)!="composer.json"){bzclose($f);$f=gzopen($pkg,"rb");$data=gzread($f,512);gzclose($f);$f=gzopen($pkg,"rb");$read="gzread";$close="gzclose";} else {bzclose($f);$f=bzopen($pkg,"rb");$read="bzread";$close="bzclose";}} else {$f=gzopen($pkg,"r");if($f){$data=gzread($f,512);gzclose($f);$f=gzopen($pkg,"rb");$read="gzread";$close="gzclose";}}if(!$f||!$data) return;$ustar=substr($data,257,5)=="ustar"?1:0;if(substr($data,0,13)!="composer.json"&&substr($data,110,13)!="composer.json") {$close($f);return;}while(!feof($f)&&$data){if($ustar){$data=$read($f,512);$size=octdec(substr($data,124,12));if($size>0)$body=$read($f,floor(($size+511)/512)*512);if(substr($data,0,strlen($fn))==$fn) break;$body="";} else {$data=$read($f,110);if(substr($data,0,6)!="070701") break;$size=floor((hexdec(substr($data,54,8))+3)/4)*4;$len=hexdec(substr($data,94,8));$len+=floor((110+$len+3)/4)*4-110-$len;$name=trim($read($f,$len));$body="";if($name=="TRAILER!!!") break;$body=$read($f,$size);if($name==$fn) break;}if(strlen($data)==0) break;}$close($f);return trim($body);}
function metadecode($str){if(function_exists("json_decode")) return json_decode($str,true);$k=$v="";$ret=array();$p="";$a=0;for($i=0;$i<strlen($str);$i++){if($str[$i]=="\""){$i++;$s=$i;while($str[$i]!="\""){if($str[$i]=="\\") $i++;$i++;}$v=substr($str,$s,$i-$s);}if($str[$i]==":") { $k=$v; $v=""; }if($str[$i]==","||$str[$i]=="{"||$str[$i]=="}"||$str[$i]=="["||$str[$i]=="]") {if($k){if($p&&$a) $ret[$p][][$k]=$v; else if($a) $ret[$k][]=$v; else if($p) $ret[$p][$k]=$v; else $ret[$k]=$v;} if($str[$i]=="}")$p="";if($str[$i]=="{")$p=$k;if($str[$i]=="]"){$p="";$a=0;}if($str[$i]=="["){$p=$k;$a=1;}$k=$v=""; }}return $ret;}
function cmp($a,$b){ if($a['keywords'][0]!=$b['keywords'][0]) return $a['keywords'][0]>=$b['keywords'][0];if($a['prio']+0!=$b['prio']+0)return($a['prio']+0<$b['prio']+0?1:-1);return ($a['name']<$b['name']?-1:1);}
function dumppkg($p,$m,&$json,$l=0){$f=1;
	foreach($m as $k=>$v) if($k!="prio"){
		$json.=($f?"":",\n").str_repeat("\t",$l+3)."\"".addslashes($k)."\":";$f=0;
		if(is_array($v)||is_object($v)) {$json.=isset($v[0])?"[\"".$v[0]."\"":"{\n";dumppkg($k,$v,$json,$l+1);$json.="\n".str_repeat("\t",$l+3).(isset($v[0])?"]":"}");} else $json.="\"".addslashes($v)."\"";}}

// check for tarball download with token
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
// redirect user to documentation
if(!empty($_SERVER['HTTP_USER_AGENT'])&&
    strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"phppe")===false&&
    strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"curl")===false&&
    strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"wget")===false&&
    !isset($_REQUEST['force'])
    )
    header("Location: phppe3.html");
// generate new package meta info file for PHP Coomposer and PHPPE Extension Manager
$c="packages.json";
// failsafe if packages.json rewrite does not exists
if(file_exists($c) && filesize($c)>16 && filemtime($c)+24*60*60>time()) die(file_get_contents($c));
// get base url
$base=(!empty( $_SERVER["SERVER_NAME"])?$_SERVER["SERVER_NAME"]:"localhost");$d=explode("/",$_SERVER["PHP_SELF"]);foreach( $d as $i){if(substr($i,-4)==".php")break;if($i)$base.="/".$i;}
// get list of packages
$packages = array();
$d = glob("*.tgz");
foreach($d as $v) {
	$m=metadecode(readpkg($v,"composer.json"));
	if(!is_array($m)) die("JSON Syntax error: ".$v."/composer.json\n");
	$packages[$v]=$m;
	if($m['name']=="phppe") $packages[$v]['prio']=99999; else
	if($m['name']=="phppe/CMS") $packages[$v]['prio']=99998; else
	if($m['name']=="phppe/Extensions") $packages[$v]['prio']=99997; else
	if($m['name']=="phppe/Developer") $packages[$v]['prio']=99996; else
	if($m['name']=="phppe/ClassMap") $packages[$v]['prio']=99995; else
	if($m['name']=="phppe/GPIO") $packages[$v]['prio']=99994; else
	if($m['name']=="phppe/RPi") $packages[$v]['prio']=99993; else
	$packages[$v]['prio']=!empty($m["prio"])&&$m["prio"]>0&&$m["prio"]<99900?$m["prio"]+0:0;
	$packages[$v]['name']=!empty($m["name"])?$m["name"]:str_replace("_","/",substr($v,0,strlen($v)-4));
	$packages[$v]['version']=!empty($m["version"])?$m["version"]:(!empty($m["version_normalized"])?$m["version_normalized"]:"?.?.?");
	$packages[$v]['dist']['type']=!empty($m["dist"]["type"])?$m["dist"]["type"]:"tar";
	$packages[$v]['dist']['url']=!empty($m["dist"]["url"])?$m["dist"]["url"]:"http://".$base."/".$v;
	$packages[$v]['description']=!empty($m["description_en"])?$m["description_en"]:(!empty($m["description"])?$m["description"]:"");
	$packages[$v]['keywords']=is_array($m["keywords"])?$m["keywords"]:[];
	$packages[$v]['homepage']=$m["homepage"];
	foreach($m as $K=>$V) if(substr($K,0,5)=="name_"||substr($K,0,12)=="description_"||substr($K,0,9)=="keywords_"||substr($K,0,9)=="homepage_") $packages[$v][$K]=$V;
	$packages[$v]['maintainer']=!empty($m["maintainer"])?$m["maintainer"]:array("name"=>"Anonymous");
	$packages[$v]['license']=!empty($m["license"])?$m["license"]:"LGPL-3.0+";
	$packages[$v]['price']=!empty($m["price"])&&$m["price"]>0?intval($m["price"]):(fileperms($v) & 0004 ? 0 : 1);
	if($packages[$v]['price'] && empty($m["homepage"]))
		$packages[$v]['homepage']=dirname($packages[$v]['dist']['url'])."/webshop.php?product=".urlencode($packages[$v]['name']);
	$packages[$v]['time']=date("Y-m-d H:i:s",filemtime($v));
	$packages[$v]['size']=filesize($v);
	$packages[$v]['sha1']=sha1(file_get_contents($v));
	$p=readpkg($v,"preview");
	if(empty($p))$p=readpkg($v,"core/images/phppe.png");
	if(!empty($p)) $packages[$v]['preview']=base64_encode($p);
}
usort($packages,"cmp");
// output json
$json="{\n\t\"packages\": {\n";$f=1;
foreach($packages as $p=>$m){
    $json.=($f?"":",\n")."\t\t\"".$m['name']."\": {\n\t\t  \"".$m['version']."\": {\n";$f=0;
    dumppkg($p,$m,$json);
    $json.="\n\t\t  }\n\t\t}";
}
$json.="\n\t}\n}\n";
// save it for later
file_put_contents($c,$json);
die($json);
?>