<?php
use PHPPE\Core as PHPPE;

class Page extends \PHPPE\Model {
	static $_table="pages";
}

class View extends \PHPPE\Model {
	static $_table="views";
}

class CMS_Ctrl extends \PHPPE\App {
	public $param;
	public $type;
	public $page;
	public $langs;
	public $quickhelp;
	public $layouts;

	function __construct()
	{
		PHPPE::$core->needframe = false;
		$this->langs=[""=>L("Any")];
		foreach($_SESSION['pe_ls'] as $k=>$v)
			$this->langs[$k]=$k." ".L($k);
		$this->quickhelp=!PHPPE::lib("CMS")->expert;

		PHPPE::jslib("cms.js","cms_init();");
		PHPPE::css("cms.css");
	}

	function action($item)
	{
		PHPPE::$core->template="cms_".PHPPE::$core->action;
		switch(PHPPE::$core->action){
			case "layoutdelete":
				if(PHPPE::istry()){
					PHPPE::exec("DELETE FROM views WHERE id=?",[$_SESSION['cms_layout']->id]);
					PHPPE::exec("DELETE FROM pages WHERE template=?",[$_SESSION['cms_layout']->id]);
					$_SESSION['cms_layout']=[];
					die("<html><script>\ntop.document.location.href='".url("cms","layouts")."';\n</script></html>");
				}
				break;
			case "layoutadd":
				if(empty($_SESSION['cms_page']['id'])) $_SESSION['cms_page']=['id'=>'','name'=>'','template'=>'','lang'=>PHPPE::$client->lang,'data'=>'','dds'=>'','ownerid'=>PHPPE::$user->id];
				$this->layout = $_SESSION['cms_layout'];
				if(PHPPE::istry()){
					$d=PHPPE::req2arr('layout');
					if(!PHPPE::iserror()&&!empty($d['id'])){
						$view=new View();
						foreach($_SESSION['cms_layout'] as $k=>$v)
							$view->$k=isset($d[$k])?$d[$k]:$v;
						$view->save(true);
						die("<html><script>\ntop.document.location.href='".url("cms","layouts").urlencode($view->id)."';\n</script></html>");
					}
				}
				break;
			case "pagedelete":
				if(PHPPE::istry()){
					PHPPE::exec("DELETE FROM pages WHERE id=? and created=?",[$_SESSION['cms_page']['id'],$_SESSION['cms_page']['created']]);
					$_SESSION['cms_page']=[];
					die("<html><script>\ntop.document.location.href='".url("cms","pages")."';\n</script></html>");
				}
				break;
			case "pageadd":
				if(empty($_SESSION['cms_page']['id'])) $_SESSION['cms_page']=['id'=>'','name'=>'','template'=>'','lang'=>PHPPE::$client->lang,'data'=>'','dds'=>'','ownerid'=>PHPPE::$user->id];
			case "pagemeta":
			case "pagepublish":
			case "pagefilters":
			case "pagedds":
				if(PHPPE::istry()){
					$d=PHPPE::req2arr('page');
					if(!PHPPE::iserror()&&!empty($d['id'])){
						if($d['pubd']>=$d['expd'])
								$d['pubd']=$d['expd']=0;
						$s=""; $w=[];
						foreach($d as $k=>$v) if($k!="created") {
							$s.=($s?"=?,":"").$k;
							$w[]=$v;
						}
						$w[]=$_SESSION['cms_page']['id'];
						if(!empty(PHPPE::lib("CMS")->revert)) {
							$w[]=$_SESSION['cms_page']['created'];
							PHPPE::exec("UPDATE pages set ".$s."=? WHERE id=? and created=?",$w);
							if(!empty(PHPPE::lib("CMS")->purge))
								PHPPE::exec("DELETE FROM pages WHERE id=? AND created!=?",[$_SESSION['cms_page']['id'],$_SESSION['cms_page']['created']]);
						} else {
							$page=new Page();
							foreach($_SESSION['cms_page'] as $k=>$v)
								$page->$k=isset($d[$k])?$d[$k]:$v;
							$page->save(true);
						}
						die("<html><script>\ntop.document.location.href='".url("cms","pages").urlencode($_SESSION['cms_page']['id'])."';\n</script></html>");
					}
				}
				$this->page=new Page();
				foreach($_SESSION['cms_page'] as $k=>$v)
					$this->page->$k=$v;
				if(PHPPE::$core->action=="pagemeta"||PHPPE::$core->action=="pageadd") {
					$this->layouts = PHPPE::query("id,name","views","","","id ASC");
					foreach($this->layouts as $k=>$v) {
						if(empty($v['name'])||$v['name']=="null") $v['name']=$v['id'];
						$this->layouts[$k]['name']=L($v['name']);
					}
				}
				if(empty($this->page->lang)) $this->page->lang=PHPPE::$client->lang;
				break;
			case "pagepurge":
				if(!empty($item))
					PHPPE::exec("DELETE FROM pages WHERE id=? AND created=?",[$_SESSION['cms_page']['id'],urldecode($item)]);
				foreach($_SESSION['cms_page']['_pages'] as $k=>$v)
					if($v['id']==urldecode($item))
						unset($_SESSION['cms_page']['_pages'][$k]);
				PHPPE::redirect("cms/pagerevert");
				break;
			case "pagerevert":
				$this->param=$_SESSION['cms_page']['_pages'];
				foreach($this->param as $k=>$v)
					$this->param[$k]['ago']=PHPPE::$core->now-$this->param[$k]['ago'];
				if(!empty($item)) {
					$d = PHPPE::fetch("*","pages","id=? AND created=?","","",[$this->param['id'],$item]);
					$page=new Page();
					foreach($_SESSION['cms_page'] as $k=>$v)
						if($k!="created")
							$page->$k=isset($d[$k])?$d[$k]:$v;
					$page->save(true);
					die("<html><script>\ntop.document.location.href='".url("cms","pages").urlencode($_SESSION['cms_page']['id'])."';\n</script></html>");
				}
				break;
			default:
				PHPPE::$core->needframe = true;
		}
	}
}
