<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class CMS extends \PHPPE\Ctrl {
	public $param;
	public $type;
	public $page;
	public $langs;
	public $quickhelp;
	public $layouts;
	public $sitedata;

	function __construct()
	{
		$this->langs=[""=>L("Any")];
		if(  !empty( $_SESSION[ 'pe_ls' ] ) )$d = $_SESSION[ 'pe_ls' ];
		else {
		    //if application has translations, use that list
		    $D = @scandir( "app/lang" );
		    //if not, fallback to core's translations
		    if(!a($D)||empty($D)) $D = @scandir( P."lang" );
		    $d = [];
		    foreach( $D  as $f ) if(w($f,-4)==".php")
			$d[ z( $f,0,u( $f ) - 4 ) ] = 1;
		    $_SESSION[ 'pe_ls' ] = $d;
		}
		foreach($d as $k=>$v)
			$this->langs[$k]=$k." ".L($k);
		$this->quickhelp=!PHPPE::lib("CMS")->expert;

		PHPPE::jslib("cms.js","cms_init();");
		PHPPE::css("cms.css");
	}

	function action($item)
	{
		include_once("vendor/phppe/CMS/libs/views.php");
		include_once("vendor/phppe/CMS/libs/pages.php");
		PHPPE::$core->noframe = true;
		PHPPE::$core->nopanel = true;
		PHPPE::$core->template="cms_".PHPPE::$core->action;
		switch(PHPPE::$core->action){
			case "layoutdelete":
				if(PHPPE::istry()){
					PHPPE::log('A',"Layout delete: ".$_SESSION['cms_layout']->id." by ".PHPPE::$user->id,"cms");
					PHPPE::exec("DELETE FROM views WHERE id=?",[$_SESSION['cms_layout']->id]);
					PHPPE::exec("DELETE FROM pages WHERE template=?",[$_SESSION['cms_layout']->id]);
					$_SESSION['cms_layout']=[];
					die("<html><script>\ntop.document.location.href='".url("cms","layouts")."';\n</script></html>");
				}
				break;
			case "layoutadd":
				if(empty($_SESSION['cms_page']['id'])) $_SESSION['cms_page']=['id'=>'','name'=>'','template'=>'','lang'=>PHPPE::$client->lang,'data'=>'','dds'=>'','ownerid'=>PHPPE::$user->id,'ctrl'=>''];
				$this->layout = $_SESSION['cms_layout'];
				if(PHPPE::istry()){
					$d=PHPPE::req2arr('layout');
					if(!PHPPE::iserror()&&!empty($d['name'])){
						$view=new \PHPPE\View();
						$view->id=strtr(strtolower($d['name']),[" "=>"_","\t"=>"","/"=>"",".."=>"","\n"=>"","\r"=>""]);
						$view->name=$d['name'];
						$view->save(true);
						PHPPE::log('A',"Layout add: ".$view->id." by ".PHPPE::$user->id);
						die("<html><script>\ntop.document.location.href='".url("cms","layouts").urlencode($view->id)."';\n</script></html>");
					}
				}
				break;
			case "pagedelete":
				if(PHPPE::istry()){
					PHPPE::log('A',"Page delete: ".$_SESSION['cms_page']['id']." by ".PHPPE::$user->id,"cms");
					PHPPE::exec("DELETE FROM pages WHERE id=?",[$_SESSION['cms_page']['id']]);
					$_SESSION['cms_page']=[];
					die("<html><script>\ntop.document.location.href='".url("cms","pages")."';\n</script></html>");
				}
				break;
			case "pageadd":
				if(empty($_SESSION['cms_page']['id'])) $_SESSION['cms_page']=['id'=>'','name'=>'','template'=>'','lang'=>PHPPE::$client->lang,'data'=>'','dds'=>'','ownerid'=>PHPPE::$user->id,'ctrl'=>''];
			case "pagemeta":
			case "pagedds":
				if(PHPPE::isTry()){
					PHPPE::validate("page.pubd","time");
					PHPPE::validate("page.expd","time");
					PHPPE::validate("page.cmsmeta","cmsmeta");
					$d=PHPPE::req2arr('page');
					if(PHPPE::$core->action=="pagedds") {
						if(!$d['id']&&!empty($_SESSION['cms_page']['id'])) $d['id']=$_SESSION['cms_page']['id'];
						$dds=[];
						foreach($d as $k=>$v) {
							if(preg_match("|^(g?dds):(.*)_([0-9])$|",$k,$m)) {
								$i=empty($m[2])?preg_replace("/[^a-zA-Z0-9]/","",$d[$m[1].":_name"]):$m[2];
								if(!empty($i)) $dds[$m[1]][$i][$m[3]]=$v;
							}
						}
						foreach($dds as $t=>$D)
							foreach($D as $k=>$v)
								if(empty($v[0]) || empty($k)) {
									unset($dds[$t][$k]);
									break;
								}
						$k=json_encode($dds['dds']); $_SESSION['cms_page']['dds']=$d['dds']=!empty($k)&&$k!='null'?$k:'';
						$k=json_encode($dds['gdds']); $k=!empty($k)&&$k!='null'?$k:'';
						if($_SESSION['cms_page']['gdds']!=$k) {
							PHPPE::exec("UPDATE pages set dds=?,modifyid=?,modifyd=CURRENT_TIMESTAMP WHERE id='frame'",[$k,PHPPE::$user->id]);
							$_SESSION['cms_page']['gdds']=$k;
						}
					}
					if(!PHPPE::isError()&&!empty($d['id'])){
						if(!empty($d['pubd'])&&$d['pubd']>=$d['expd'])
								$d['pubd']=$d['expd']=0;
						PHPPE::log('A',"Page ".substr(PHPPE::$core->action,4).": ".$_SESSION['cms_page']['id']." by ".PHPPE::$user->id.(PHPPE::$core->runlevel>0?" ".json_encode($d):""),"cms");
						$s=""; $w=[];
						foreach($d as $k=>$v) if($k[0]!="_"&&$k!="created"&&$k!="modifyd"&&$k!="modifyid"&&$k!="gdds"&&$k!="id"&&strpos($k,":")===false) {
							$s.=($s?"=?,":"").$k;
							$w[]=$v;
						}
						if(!empty(PHPPE::lib("CMS")->revert)) {
							$w[]=PHPPE::$user->id;
							$w[]=$_SESSION['cms_page']['id'];
							PHPPE::exec("UPDATE pages set ".$s."=?,modifyid=?,modifyd=CURRENT_TIMESTAMP WHERE id=?",$w);
							PHPPE::exec("DELETE FROM pages WHERE id=? AND created not in (SELECT created FROM pages WHERE id=? order by created desc limit 1)",[$_SESSION['cms_page']['id'],$_SESSION['cms_page']['id']]);
						} else {
							$page=new \PHPPE\Page();
							$page->data='';
							$page->ctrl='';
							foreach($_SESSION['cms_page'] as $k=>$v)
								if($k[0]!="_"&&$k!="created"&&$k!="modifyd"&&$k!="modifyid"&&$k!="gdds"&&strpos($k,":")===false)
									$page->$k=isset($d[$k])?$d[$k]:$v;
							$page->modifyid=PHPPE::$user->id;
							$page->created=$page->modifyd=date("Y-m-d H:i:s");
							$page->save(true);
							PHPPE::exec("DELETE FROM pages WHERE id=? AND created not in (SELECT created FROM pages WHERE id=? order by created desc limit ".intval(PHPPE::lib("CMS")->purge).")",[$_SESSION['cms_page']['id'],$_SESSION['cms_page']['id']]);
						}
						die("<html><script>\ntop.document.location.href='".url("cms","pages").urlencode($_SESSION['cms_page']['id'])."';\n</script></html>");
					}
				}
				if(PHPPE::$core->action=="pagedds") {
					$frame=PHPPE::fetch("dds","pages","id='frame'","","created DESC");
					$_SESSION['cms_page']['gdds']=$frame['dds'];
				}
				$this->page=new \PHPPE\Page();
				$this->page->data='';
				$this->page->ctrl='';
				foreach($_SESSION['cms_page'] as $k=>$v)
					$this->page->$k=$v;
				if(PHPPE::$core->action=="pagemeta"||PHPPE::$core->action=="pageadd") {
					$this->layouts = PHPPE::query("id,name","views","sitebuild='' AND id!='styleguide'","","id ASC");
					$w=0;
					foreach($this->layouts as $k=>$v) {
						if(empty($v['name'])||$v['name']=="null") $v['name']=$v['id'];
						//$this->layouts[$k]['name']=L($v['name']);
						if(!empty($this->page->template) && $this->page->template==$v['id']) $w=1;
					}
					//add template to option list if not found so far
					if(!empty($this->page->template) && !$w)
						$this->layouts[]=['id'=>$this->page->template,'name'=>$this->page->template];
					//add language to languages option list if not found
					if(!empty($this->page->lang) && empty($this->langs[$this->page->lang]))
						$this->langs[$this->page->lang]=$this->page->lang." ".L($this->page->lang);
				}
				if(empty($this->page->lang)) $this->page->lang=PHPPE::$client->lang;
				break;
			case "pagepurge":
				if(!empty($item)) {
					PHPPE::log('A',"Page purge: ".$_SESSION['cms_page']['id']." to ".urldecode($item)." by ".PHPPE::$user->id,"cms");
					PHPPE::exec("DELETE FROM pages WHERE id=? AND created=?",[$_SESSION['cms_page']['id'],urldecode($item)]);
				}
				foreach($_SESSION['cms_page']['_pages'] as $k=>$v)
					if($v['id']==urldecode($item))
						unset($_SESSION['cms_page']['_pages'][$k]);
				PHPPE::redirect("cms/pagehistory");
				break;
			case "pagehistory":
				$this->param=$_SESSION['cms_page']['_pages'];
				foreach($this->param as $k=>$v)
					$this->param[$k]['ago']=PHPPE::$core->now-$this->param[$k]['ago'];
				if(!empty($item)) {
					PHPPE::log('A',"Page revert: ".$_SESSION['cms_page']['id']." to ".urldecode($item)." by ".PHPPE::$user->id,"cms");
					PHPPE::exec("UPDATE pages SET created=CURRENT_TIMESTAMP,modifyd=CURRENT_TIMESTAMP WHERE id=? AND created=?",[$_SESSION['cms_page']['id'],urldecode($item)]);
					die("<html><script>\ntop.document.location.href='".url("cms","pages").urlencode($_SESSION['cms_page']['id'])."';\n</script></html>");
				}
				break;
			default:
				PHPPE::$core->noframe = false;
				PHPPE::$core->nopanel = false;
		}
	}
}
