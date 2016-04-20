<?php
/**
 * Controller for CMS layouts
 */
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class View extends \PHPPE\Model {
	static $_table="views";
}

class CMSLayouts extends \PHPPE\Ctrl {
	public $layouts;
	public $sitebuilds;
	public $layout;
	public $copyout;
	public $quickhelp=true;
	public $_favicon="images/phppeicon.png";

	function __construct()
	{
		PHPPE::$core->nocache = true;
		PHPPE::$core->noframe = true;
		PHPPE::$core->site = L("CMS Layouts");
		if(PHPPE::lib("CMS")->expert)
			$this->quickhelp=false;
		PHPPE::jslib("cms.js","cms_init();".(!empty(PHPPE::$core->item)?"cms_layoutresizeinit();":"try{document.getElementById('search').focus();}catch(e){}"));
		PHPPE::css("cms.css");
		if(!empty($_SESSION['cms_copyout'])) {
			$this->copyout=$_SESSION['cms_copyout'];
			unset($_SESSION['cms_copyout']);
		}
	}

	function action($item="")
	{
		$_SESSION['cms_layout']=[];
		if(!empty($item)) {
			$this->layout = (object)PHPPE::fetch("*","views","id=?","","",[$item]);
			$_SESSION['cms_layout']=$this->layout;
			$layout = PHPPE::req2arr('layout');
			if(@$layout['input']['type']=="text/html") {
					$data=@file_get_contents($layout['input']['tmp_name']);
					preg_match_all("/[=\(][\'\\\"]([^\'\\\"]*?\.css[^\'\\\"]*?)[\'\\\"]/ims",$data,$css,PREG_SET_ORDER);
					$layout['css']=[]; foreach($css as $v) $layout['css'][]=$v[1];
					preg_match_all("/[=][\'\\\"]([^\'\\\"]*?\.js[^\'\\\"]*?)[\'\\\"]/ims",$data,$js,PREG_SET_ORDER);
					$layout['jslib']=[]; foreach($js as $v) $layout['jslib'][]=$v[1];
					preg_match_all("|<body[^>]*>(.*?)<\/body|ims",$data,$body,PREG_SET_ORDER);
					$this->choose=$body[0][1];
			}
			if(PHPPE::isTry()) {
				include_once("vendor/phppe/CMS/addons/cmsmeta.php");
				include_once("vendor/phppe/CMS/addons/cmscss.php");
				include_once("vendor/phppe/CMS/addons/cmsjs.php");
				\PHPPE\AddOn\cmsmeta::validate("layout_meta",$layout['meta']);
				\PHPPE\AddOn\cmscss::validate("layout_css",$layout['css']);
				\PHPPE\AddOn\cmsjs::validate("layout_jslib",$layout['jslib']);
				$view = new View();
				foreach($layout as $k=>$v)
					if($k!="input"&&strpos($k,":")===false)
						$view->$k=$v;
				$view->save();
				//!if memory cache enabled
				if(PHPPE::mc()) {
					//invalidate the raw template in cache
					PHPPE::ic($item);
					//invalidate all pages generated with the old template
					$pages = PHPPE::query("id","pages","template=?","id","id",0,0,[$item]);
					foreach($pages as $p)
						PHPPE::ic($p['id']);
				}
				PHPPE::redirect("cms/layouts");
			}
		} else {
			PHPPE::$core->noframe=false;
			unset($_SESSION['cms_sitebuild']);
			$import=PHPPE::req2arr('import');
			if(!empty($_FILES['import_file'])) {
				if(@$_FILES['import_file']['size']>0) {
					if(in_array($_FILES['import_file']['type'],['application/zip', 'application/x-zip','application/gzip', 'application/x-gzip', 'application/compressed-tar', 'application/x-compressed-tar'])) {
						try {
						  \PHPPE\Content::untar($_FILES['import_file']['tmp_name'],[__CLASS__,"process"]);
						  PHPPE::log('A',"Import sitebuild: ".$_FILES['import_file']['name']." by ".PHPPE::$user->id,"cms");
						  PHPPE::redirect("cms/sitebuild");
						} catch(\Exception $e) {
						  PHPPE::error($e->getMessage(),"import.file");
						}
					} else
						PHPPE::error(L("Bad file format. Use zip or targz.")."\n".$_FILES['import_file']['type'],"import.file");
				} else
					PHPPE::error(L("Error uploading file"),"import.file");
			}
			PHPPE::exec("DELETE FROM views WHERE id=''");
			if(!empty($_REQUEST['set'])) {
				//invalidate cache
				PHPPE::ic("frame");
				PHPPE::exec("UPDATE views SET id=sitebuild WHERE sitebuild!='' AND id='frame'");
				PHPPE::exec("UPDATE views SET id='frame' WHERE sitebuild=?",trim($_REQUEST['set']));
				PHPPE::redirect();
			}
			$this->layouts = PHPPE::query("*,CURRENT_TIMESTAMP as ct","views","sitebuild=''","","id ASC");
			$this->sitebuilds = PHPPE::query("*,CURRENT_TIMESTAMP as ct","views","sitebuild!=''","","name ASC");
			setcookie('cms_brkpoints', null, -1, "/");
		}
	}

	static function process($name,$body){
		$dir='.tmp/'.session_id().'/';
		if(substr($name,-3)=="css") $dir.="css/"; else
		if(in_array(substr($name,-3),["gif","jpg","png","mng","svg"])) $dir.="images/"; else
		if(substr($name,-2)=="js") $dir.="js/"; else
		if(substr($name,-4)=="html") $dir.="html/"; else return;
		\PHPPE\Content::mkdir($dir);
		$n=basename($name);
		$name=$dir.$n;
		while(file_exists($name)) { $n="_".$n; $name=$dir.$n; }
		file_put_contents($name,$body);
	}
}
