<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class CMS extends \PHPPE\Ctrl {
	public $choose;
	public $html;
	public $title;
	public $content;
	public $quickhelp=true;
	public $_favicon="images/phppeicon.png";

	function __construct()
	{
		$dirs=["css","images","js"];
		PHPPE::$core->nocache = true;
		PHPPE::$core->noframe = true;
		PHPPE::$core->site = L("CMS Layouts");
		if(PHPPE::lib("CMS")->expert)
			$this->quickhelp=false;
		PHPPE::jslib("cms.js","cms_init();");
		PHPPE::css("cms.css");
		if(!empty($_REQUEST['asset'])) {
			$d=explode("/",trim($_REQUEST['asset']));
			if(!is_array($d)||count($d)!=2||!in_array($d[0],$dirs)) {
				header("HTTP/1.1 403 Access forbidden");
				die("403 Access forbidden");
			}
		}
		if(empty($_SESSION['cms_sitebuild'])){
			$this->html=@glob(".tmp/".session_id()."/html/*");
			if(count($this->html)==1) {
				$_SESSION['cms_sitebuild']=$this->html[0];
				PHPPE::redirect();
			}
			if(isset($_REQUEST['cms_sitebuild'])) {
				$_SESSION['cms_sitebuild']=$this->html[intval($_REQUEST['cms_sitebuild'])];
				PHPPE::redirect();
			}
			foreach($this->html as $k=>$v)
				if(preg_match("|<title>(.*?)</title>|",file_get_contents($v),$m))
					$this->title[$k]=$m[1];
		}
		if(empty($_SESSION['cms_sitebuild'])) {
			$this->choose=true;
			PHPPE::$core->noframe = false;
		} else {
			$data=file_get_contents($_SESSION['cms_sitebuild']);
			foreach($dirs as $dir) {
				$d=glob(".tmp/".session_id()."/".$dir."/*");
				foreach($d as $v) {
					$data=preg_replace("|[a-z0-9\_\./:]*".addslashes(basename($v))."|ims","?asset=".$dir."/".basename($v),$data);
					if($dir=="css") PHPPE::css("divchoose.css?asset=".basename($v));
					if($dir=="js") PHPPE::jslib("divchoose.js?asset=".basename($v));
				}
			}
			$this->content=\PHPPE\CMS::taghtml($data);
			if(isset($_REQUEST['chooseid'])) {
				$css=[]; $jslib=[]; $meta=[];
				$id=substr(basename($_SESSION['cms_sitebuild']),0,strrpos(basename($_SESSION['cms_sitebuild']),'.'));
				$body=\PHPPE\CMS::splithtml($this->content,intval($_REQUEST['chooseid']),0)."\n<!app>\n".\PHPPE\CMS::splithtml($this->content,intval($_REQUEST['chooseid']),2);
				foreach($dirs as $dir) {
					$d=glob(".tmp/".session_id()."/".$dir."/*");
					foreach($d as $v) {
						//! store file locally on CMS server
						@copy($v,"public/".$dir."/".basename($v));
						$data=preg_replace("|[a-z0-9\_\./:]*".addslashes(basename($v))."|ims",$dir."/".basename($v),$data);
						if($dir=="css") $css[basename($v)]=basename($v);
						if($dir=="js") $jslib[basename($v)]=basename($v);
					}
				}
				PHPPE::exec("REPLACE INTO views ".
					"(id,name,data,css,jslib,meta,sitebuild,created,modifyd) values ".
					"(?,?,?,?,?,?,?,CURRENT_TIMESTAMP,CURRENT_TIMESTAMP)",[$id,$id,$body,json_encode($css),json_encode($jslib),json_encode($meta),$id]);
				//! send assets to Content servers too
				if(!empty(PHPPE::$user->data['remote']['host'])) {
					chdir(".tmp/".session_id());
					$_SESSION['cms_copyout']=\PHPPE\Tools::copy($dirs,"public/");
					chdir("../..");
				}
				\PHPPE\Tools::rmdir(".tmp/".session_id());
				unset($_SESSION['cms_sitebuild']);
				PHPPE::redirect("cms/layouts/".$id);
			}
		}
	}

	function action($item="")
	{
//		if(!empty($_SESSION['cms_sitebuild'])) {
		if(!empty($this->content)) {
//			die(htmlspecialchars($this->content));
		}
	}

}
