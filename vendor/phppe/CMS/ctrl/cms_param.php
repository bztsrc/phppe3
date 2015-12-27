<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

include_once("vendor/phppe/CMS/libs/pages.php");

class CMS extends \PHPPE\Ctrl {
	public $param;
	public $type;
	public $value;
	public $page;
	public $w,$h;

	function __construct()
	{
		PHPPE::$core->nocache = true;
		PHPPE::$core->noframe = true;
		PHPPE::$core->nopanel = true;
		PHPPE::jslib("cms.js","cms_init();");
		PHPPE::css("cms.css");
	}

	function action($item="")
	{
		PHPPE::$core->noframe = true;
		PHPPE::$core->nopanel = true;
		$frame = false;
		$this->param=$_SESSION['cms_param'][$item+0];
		if(substr($this->param[1],0,6)=="frame.") $frame = true;
		$key=str_replace("frame.","",str_replace("app.","",$this->param[1]));
		$this->type=$this->param[0];
		if($frame) {
				$frpage=@jd(PHPPE::field("data","pages","id='frame'"));
				$this->value=$frpage[$key];
		} else
			$this->value=!empty($_SESSION['cms_page']['data'][$key])?$_SESSION['cms_page']['data'][$key]:'';
		$this->w=intval($_REQUEST['w']);
		$this->h=intval($_REQUEST['h']);
		$this->page=PHPPE::fetch( "ownerid", "pages", "id=?", "", "id DESC,created DESC",[$_SESSION['cms_page']['id']]);
		if(PHPPE::istry() && $this->page['ownerid']==PHPPE::$user->id) {
			$param=PHPPE::req2arr("app");
			if($this->type=="pagelist") {
				PHPPE::exec("DELETE FROM pages_list WHERE list_id=?",[$this->param[1]]);
				$d=explode(",",$param['value']);
				foreach($d as $k=>$v)
					if(!empty($v)&&trim($v)!="null")
						PHPPE::exec("INSERT INTO pages_list (list_id,page_id,ordering) values (?,?,?)",[$this->param[1],$v,intval($k)]);
			} elseif($frame) {
					$frpage[$key]=$param['value'];
					// FIXME frame is not revertable
					PHPPE::exec("UPDATE pages set data=?,modifyid=?,modifyd=CURRENT_TIMESTAMP WHERE id='frame'",[json_encode($frpage),PHPPE::$user->id]);
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
		if($this->type=="pagelist")
			$this->value=array_column(PHPPE::query("page_id","pages_list","list_id=?","page_id","ordering",0,0,[$this->param[1]]),'page_id');
		PHPPE::js("init()","if(typeof wysiwyg_toolbarhooks=='function') wysiwyg_toolbarhooks('cms_wysiwyg');setTimeout(function(){document.getElementsByTagName('FORM')[0].elements[0].focus();},100);");
	}
}
