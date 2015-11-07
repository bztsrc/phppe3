<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class View extends \PHPPE\Model {
	static $_table="views";
}

class CMS extends \PHPPE\Ctrl {
	public $layouts;
	public $sitebuilds;
	public $layout;
	public $quickhelp=true;

	function __construct()
	{
		PHPPE::$core->nocache = true;
		PHPPE::$core->needframe = false;
		PHPPE::$core->site = L("CMS Layouts");
		if(PHPPE::lib("CMS")->expert)
			$this->quickhelp=false;
		PHPPE::jslib("cms.js","cms_init();");
		PHPPE::css("cms.css");
	}

	function action($item="")
	{
		$_SESSION['cms_layout']=[];
		if(!empty($item)) {
			$this->layout = (object)PHPPE::fetch("*","views","id=?","","",[$item]);
			$_SESSION['cms_layout']=$this->layout;
			if(PHPPE::istry()) {
				$view = new View();
				$layout = PHPPE::req2arr('layout');
				if(@$layout['input']['type']=="text/html") {
					$data=@file_get_contents($layout['input']['tmp_name']);
					preg_match_all("/[=\(][\'\\\"]([^\'\\\"]*?\.css[^\'\\\"]*?)[\'\\\"]/ims",$data,$css,PREG_SET_ORDER);
					$layout['css']=[]; foreach($css as $v) $layout['css'][]=$v[1];
					preg_match_all("/[=][\'\\\"]([^\'\\\"]*?\.js[^\'\\\"]*?)[\'\\\"]/ims",$data,$js,PREG_SET_ORDER);
					$layout['jslib']=[]; foreach($js as $v) $layout['jslib'][]=$v[1];
					preg_match_all("|<body[^>]*>(.*?)<\/body|ims",$data,$body,PREG_SET_ORDER);
					$layout['data']=$body[0][1];
				}
				foreach($layout as $k=>$v)
					if($k!="input")
						$view->$k=$v;
				$view->save();
				PHPPE::redirect();
			}
		} else {
			if(PHPPE::istry()) {
				$import=PHPPE::req2arr('import');
				if(@$import['file']['size']>0) {
					if($import['file']['type']=='application/zip' || $import['file']['type']=='application/x-zip') {
						try {
						  self::untar($import['file']['tmp_name'],["\CMS_Ctrl","process"]);
						  PHPPE::redirect("cms/sitebuild");

						} catch(\Exception $e) {
						  PHPPE::error($e->getMessage(),"import.file");
						}
					} else
					if(in_array($import['file']['type'],['application/gzip', 'application/x-gzip', 'application/compressed-tar', 'application/x-compressed-tar'])) {
						try {
						  self::untar($import['file']['tmp_name'],["\CMS_Ctrl","process"]);
						  PHPPE::redirect("cms/sitebuild");
						} catch(\Exception $e) {
						  PHPPE::error($e->getMessage(),"import.file");
						}
					} else
						PHPPE::error(L("Bad file format. Use zip or targz.")."\n".$import['file']['type'],"import.file");
				} else
					PHPPE::error(L("Error uploading file"),"import.file");
			}
			PHPPE::exec("DELETE FROM views WHERE id=''");
			if(!empty($_REQUEST['set'])) {
				PHPPE::exec("UPDATE views SET id=name WHERE meta='SITEBUILD' AND id='frame'");
				PHPPE::exec("UPDATE views SET id='frame' WHERE meta='SITEBUILD' AND name=?",trim($_REQUEST['set']));
				PHPPE::redirect();
			}
			$this->layouts = PHPPE::query("*,CURRENT_TIMESTAMP as ct","views","meta!='SITEBUILD'","","id ASC");
			$this->sitebuilds = PHPPE::query("*,CURRENT_TIMESTAMP as ct","views","meta='SITEBUILD'","","name ASC");
			setcookie('cms_brkpoints', null, -1, "/");
		}
	}

static function untar($file,$fn=""){
$body="";
$f=gzopen($file,"rb");if($f){$read="gzread";$close="gzclose";$close="gzclose";$open="gzopen";}
else{
$f=bzopen($file,"rb");if($f){$read="bzread";$close="bzclose";$close="bzclose";$open="bzopen";}
else
throw new \Exception(L("Unable to open ").": ".$file);
}
$data=$read($f,512);
$close($f);
if($data[0]=='P'&&$data[1]=='K') {
    $zip=zip_open($file);
    if(!$zip) throw new \Exception(L("Unable to open ").": ".$file);
    while($zip_entry=zip_read($zip)) {
       $zname=zip_entry_name($zip_entry);
       if(!zip_entry_open($zip,$zip_entry,"r")) continue;
       $zip_fs=zip_entry_filesize($zip_entry);
       if(empty($zip_fs)) continue;
	$body=zip_entry_read($zip_entry,$zip_fs);
	if(!empty($fn) && is_string($fn)) { zip_entry_close($zip_entry); zip_close($zip); return $body; }
	if(is_array($fn) && method_exists($fn[0],$fn[1])) call_user_func($fn,$zname,$body);
       zip_entry_close($zip_entry);

    }
    zip_close($zip);
    return;
}
$f=$open($file,"rb");
$ustar=substr($data,257,5)=="ustar"?1:0;
while(!feof($f)&&$data){$name="";if($ustar){$data=$read($f,512);$size=octdec(substr($data,124,12));$body=$size>0?$read($f,floor(($size+511)/512)*512):"";
$i=0;while(isset($data[$i])&&ord($data[$i])!=0&&$i<512)$i++;
$name=substr($data,0,$i);} else
{$data=$read($f,110);if(substr($data,0,6)!="070701") throw new \Exception(L("Bad format"));
$size=floor((hexdec(substr($data,54,8))+3)/4)*4;$len=hexdec(substr($data,94,8));$len+=floor((110+$len+3)/4)*4-110-$len;$name=trim($read($f,$len));$body="";if($name=="TRAILER!!!") break;
$body=$read($f,$size);}
if(empty($name)) {$close($f);return "";}
if(!empty($fn) && is_string($fn) && $name==$fn) {$close($f);return substr($body,0,$size);}
if($size>0 && is_array($fn) && method_exists($fn[0],$fn[1])) call_user_func($fn,$name,substr($body,0,$size));
}
$close($f);
}

static function mkdirr($pn) {
  if(is_dir($pn)||empty($pn)) return true;
  $next_pathname=substr($pn,0,strrpos($pn,DIRECTORY_SEPARATOR));
  if(self::mkdirr($next_pathname)) {if(!file_exists($pn)) {return mkdir($pn,0777);} }
  return false;
}


static function process($name,$body){
$dir='data/'.session_id().'/';
if(substr($name,-3)=="css") $dir.="css/"; else
if(in_array(substr($name,-3),["gif","jpg","png","mng","svg"])) $dir.="img/"; else
if(substr($name,-2)=="js") $dir.="js/"; else
if(substr($name,-4)=="html") $dir.="html/"; else $dir.="other/";
self::mkdirr($dir);
$n=basename($name);
$name=$dir.$n;
while(file_exists($name)) { $n="_".$n; $name=$dir.$n; }
file_put_contents($name,$body);
}
}
