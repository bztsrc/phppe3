<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class Page extends \PHPPE\Model {
	public $data='';
	public $ctrl='';
	static $_table="pages";
}

class CMS extends \PHPPE\Ctrl {
	public $param;
	public $type;
	public $value;
	public $page;
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
		$this->page=PHPPE::fetch( "ownerid", "pages", "id=?", "", "id DESC,created DESC",[$_SESSION['cms_page']['id']]);
		if(PHPPE::istry() && $this->page['ownerid']==PHPPE::$user->id) {
			$param=PHPPE::req2arr("app");
			if($frame) {
					$page[$key]=$param['value'];
					// FIXME frame is not revertable
					PHPPE::exec("UPDATE pages set data=?,modifyid=?,modifyd=CURRENT_TIMESTAMP WHERE id='frame'",[json_encode($page),PHPPE::$user->id]);
			} else {
				$_SESSION['cms_page']['data'][$key]=preg_replace("/<script.*?script>/ims","",preg_replace("/<style.*?style>/ims","",preg_replace("/<head.*?head>/ims","",$param['value'])));
				if(empty(PHPPE::lib("CMS")->revert)) {
					PHPPE::exec("UPDATE pages set data=?,modifyid=?,modifyd=CURRENT_TIMESTAMP WHERE id=? AND created=?",[json_encode($_SESSION['cms_page']['data']),PHPPE::$user->id,$_SESSION['cms_page']['id'],$_SESSION['cms_page']['created']]);
					PHPPE::exec("DELETE FROM pages WHERE id=? AND created not in (SELECT created FROM pages WHERE id=? order by created desc limit 1)",[$_SESSION['cms_page']['id'],$_SESSION['cms_page']['id']]);
				} else {
					$page=new Page();
					foreach($_SESSION['cms_page'] as $k=>$v)
						if($k[0]!="_"&&$k!="created"&&$k!="modifyd"&&$k!="modifyid"&&$k!="gdds")
							$page->$k=$v;
					$page->modifyid=PHPPE::$user->id;
					$page->modifyd=$page->created=date("Y-m-d H:i:s");
					$page->save(true);
					PHPPE::exec("DELETE FROM pages WHERE id=? AND created not in (SELECT created FROM pages WHERE id=? order by created desc limit ".intval(PHPPE::lib("CMS")->purge).")",[$_SESSION['cms_page']['id'],$_SESSION['cms_page']['id']]);
				}
			}
			die("<html><script>\ntop.document.location.href='".url("cms","pages").$_SESSION['cms_page']['id']."';\n</script></html>");
		}
		PHPPE::js("init()","if(wysiwyg_toolbarhooks) wysiwyg_toolbarhooks('cms_wysiwyg');");
	}
}
