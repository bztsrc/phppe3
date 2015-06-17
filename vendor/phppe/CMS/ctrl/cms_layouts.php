<?php
use PHPPE\Core as PHPPE;

class View extends \PHPPE\Model {
	static $_table="views";
}

class CMS_Ctrl extends \PHPPE\App {
	public $layouts;
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
			PHPPE::exec("DELETE FROM views WHERE id=''");
			$this->layouts = PHPPE::query("*","views","","","id ASC");
		}
		setcookie('cms_brkpoints', null, -1, "/");
	}
}
