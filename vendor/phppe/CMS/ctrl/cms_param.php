<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class Page extends \PHPPE\Model {
	public $data;
	static $_table="pages";
}

class CMS extends \PHPPE\Ctrl {
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
		$frame = false;
		$this->param=$_SESSION['cms_param'][$item+0];
		if(substr($this->param[1],0,6)=="frame.") $frame = true;
		$key=str_replace("frame.","",str_replace("app.","",$this->param[1]));
		$this->type=$this->param[0];
		if($frame) {
				$page=@jd(PHPPE::field("data","pages","id='frame'"));
				$this->value=$page[$key];
		} else
			$this->value=$_SESSION['cms_page']['data'][$key];
		$this->w=intval($_REQUEST['w']);
		$this->h=intval($_REQUEST['h']);
		if(PHPPE::istry()) {
			$param=PHPPE::req2arr("app");
			if($frame) {
					$page[$key]=$param['value'];
					// FIXME frame is not revertable
					PHPPE::exec("UPDATE pages set data=? WHERE id='frame'",[json_encode($page)]);
			} else {
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
			}
			die("<html><script>\ntop.document.location.href='".url("cms","pages").$_SESSION['cms_page']['id']."';\n</script></html>");
		}
		PHPPE::js("init()","wysiwyg_toolbarhooks('cms_wysiwyg');");
	}
}
