<?php
use PHPPE\Core as PHPPE;

class Page extends \PHPPE\Model {
	static $_table="pages";
}

class CMS_Ctrl extends \PHPPE\App {
	public $param;
	public $type;
	public $value;
	public $w,$h;

	function __construct()
	{
		PHPPE::$core->nocache = true;
		PHPPE::$core->needframe = false;
		PHPPE::$core->nopanel = true;
		PHPPE::jslib("cms.js","cms_init();");
		PHPPE::css("cms.css");
	}

	function action($item="")
	{
		$this->param=$_SESSION['cms_param'][$item+0];
		$key=str_replace("app.","",$this->param[1]);
		$this->type=$this->param[0];
		$this->value=$_SESSION['cms_page']['data'][$key];
		$this->w=intval($_REQUEST['w']);
		$this->h=intval($_REQUEST['h']);
		if(PHPPE::istry()) {
			$param=PHPPE::req2arr("app");
			$_SESSION['cms_page']['data'][$key]=preg_replace("/<script.*?script>/ims","",preg_replace("/<style.*?style>/ims","",preg_replace("/<head.*?head>/ims","",$param['value'])));
			if(empty(PHPPE::lib("CMS")->revert)) {
				PHPPE::exec("UPDATE pages set data=? WHERE id=? AND created=?",[json_encode($_SESSION['cms_page']['data']),$_SESSION['cms_page']['id'],$_SESSION['cms_page']['created']]);
				if(!empty(PHPPE::lib("CMS")->purge))
					PHPPE::exec("DELETE FROM pages WHERE id=? AND created!=?",[$_SESSION['cms_page']['id'],$_SESSION['cms_page']['created']]);
			} else {
				$page=new Page();
				foreach($_SESSION['cms_page'] as $k=>$v)
					$page->$k=$v;
				$page->save(true);
			}
			die("<html><script>\ntop.document.location.href='".url("cms","pages").urlencode($_SESSION['cms_page']['id'])."';\n</script></html>");
		}
		PHPPE::js("init()","wysiwyg_toolbarhooks('cms_wysiwyg');");
	}
}
