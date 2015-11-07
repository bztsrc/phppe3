<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class CMS extends \PHPPE\Ctrl {
	public $id;
	public $name;
	public $_pages;
	public $_templates;
	public $_result;
	private static $_table="pages";

	function __construct()
	{
		PHPPE::$core->nocache = true;
		if(empty(PHPPE::$core->item))
			PHPPE::$core->needframe = false;
		PHPPE::$core->site = L("CMS Pages");
		PHPPE::jslib("cms.js","cms_init();");
		list($c) = x("?",@$_SERVER['REQUEST_URI']); $s=$_SERVER['SCRIPT_NAME'];
		$u = w($c,(z($c,0,u($s))==$s?u($s):u(n($s)))+1);
		if($u[0]=="/") $u=w($c,1);
		if($u[u($u)-1]=="/") $u=z($u,0,u($u)-1);
		PHPPE::$core->item=substr($u,10);
	}

	function action($item)
	{
		if(!empty($item)) {
			PHPPE::js("init()","cms_getbreakpoints();",true);
			$_SESSION['cms_page']=[];
			$frame = @jd(PHPPE::field("data","pages","id='frame'"));
			PHPPE::assign("frame",$frame);
			$page = PHPPE::fetch( "*", "pages", "id=? OR ? LIKE id||'/%'", "", "id DESC,created DESC",[$item,$item]);
			if($page->lockd) PHPPE::redirect("cms","pages");
			PHPPE::exec("UPDATE pages SET lockd=CURRENT_TIMESTAMP WHERE id=?",[$item]);
			$this->_pages = PHPPE::query("created as id,created as name,created as ago,lockd","pages", "id=?", "", "created DESC",0,0,[$page['id']]);
			foreach($this->_pages as $k=>$v) {
				if(preg_match("/^[0-9]$/",$v['name']))
					$this->_pages[$k]['name']=date((!empty(PHPPE::$l['dateformat'])?PHPPE::$l['dateformat']:"Y-m-d")." H:i:s",$v['name']);
				if(!preg_match("/^[0-9]$/",$v['ago']))
					$this->_pages[$k]['ago']=strtotime($v['ago']);
			}
			$_SESSION['cms_page']['_pages']=$this->_pages;
			if(empty($page) && $f=@glob()[0])
			if(is_string($page['dds'])) $this->dds=@json_decode($page['dds'],true);
			if(is_string($page['data'])) $page['data']=@json_decode($page['data'],true);
			if(is_array($page['data'])) foreach($page['data'] as $k=>$v) {$this->$k=$v;$_SESSION['cms_page']['data'][$k]=$v;}
			foreach(["id","name","lang","filter","template","pubd","expd","dds","ownerid","created"] as $k) $this->$k=$_SESSION['cms_page'][$k]=$page[$k];
//			$d = PHPPE::template("frame");
//			$T =
			$this->_result = /*PHPPE::_t(*/PHPPE::template($this->template)/*)*/;
//			if( preg_match( "/<!app>/ims", $d,$m,PREG_OFFSET_CAPTURE ) )
//				$this->_result = z( $d,0,$m[ 0 ][ 1 ] ) . $T . w( $d,$m[ 0 ][ 1 ] + 6 );
//			if( !$d ) $this->_result = "<div id='content'>".$T."</div>";
//			$this->_templates=array_column(PHPPE::query("id","views","","","id"),"id");
		} else {
			if(!empty($_REQUEST['unlock'])) {
				PHPPE::exec("UPDATE pages SET lockd=0 WHERE id=?",[$_REQUEST['unlock']]);
				PHPPE::redirect();
			}
			$_SESSION['cms_page']=[];
			$this->_pages=[];
			PHPPE::exec("DELETE FROM pages WHERE id='' OR template=''");
			$p = PHPPE::query("a.id,a.name,a.template,a.dds,a.ownerid,max(a.created) as created,max(a.lockd) as lockd,count(1) as versions,b.name as username,CURRENT_TIMESTAMP as ct","pages a left join users b on a.ownerid=b.id","a.id!='frame'","a.template,a.id","a.id ASC");
			foreach($p as $v)
				$this->_pages[$v['template']][]=$v;
		}
	}
}
