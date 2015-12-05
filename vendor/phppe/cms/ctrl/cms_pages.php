<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class CMS extends \PHPPE\Ctrl {
	public $id;
	public $name;
	public $_favicon="images/cms/edit.png";
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
			PHPPE::$core->site = "* ".$page['name'];
			PHPPE::exec("UPDATE pages SET lockd=0,ownerid=0 WHERE ownerid=? AND id!=?",[PHPPE::$user->id,$item]);
			$this->_pages = PHPPE::query("a.created as id,a.created as name,a.created as ago,a.lockd,a.modifyid,b.name as moduser","pages a left join users b on a.modifyid=b.id", "a.id=?", "", "a.created DESC",0,0,[$page['id']]);
			foreach($this->_pages as $k=>$v) {
				if(preg_match("/^[0-9]$/",$v['name']))
					$this->_pages[$k]['name']=date((!empty(PHPPE::$l['dateformat'])?PHPPE::$l['dateformat']:"Y-m-d")." H:i:s",$v['name']);
				if(!preg_match("/^[0-9]$/",$v['ago']))
					$this->_pages[$k]['ago']=strtotime($v['ago']);
			}
			$_SESSION['cms_page']['_pages']=$this->_pages;
			if(empty($page) && $f=@glob()[0])
//			if(is_string($page['dds'])) $this->dds=@json_decode($page['dds'],true);
			if(is_string($page['data'])) $page['data']=@json_decode($page['data'],true);
			if(is_array($page['data'])) foreach($page['data'] as $k=>$v) {$this->$k=$v;$_SESSION['cms_page']['data'][$k]=$v;}
			foreach(["id","name","lang","filter","template","pubd","expd","dds","ownerid","created"] as $k) $this->$k=$_SESSION['cms_page'][$k]=$page[$k];
			$p=json_decode($_SESSION['cms_page']['dds'],true);
			if(is_array($p)) {
				foreach($p as $k => $c)
					if($k != "dds") {
						try{
						$this->$k = PHPPE::query($c[ 0 ], $c[ 1 ], @ $c[ 2 ], @ $c[ 3 ], @ $c[ 4 ], @ $c[ 5 ], @ $c[ 6 ]);
						} catch(\Exception $e) {PHPPE::log("E",$_SESSION['cms_page']['id']." ".$e->getMessage()." ".implode(" ",$c),"dds");}
					}
			}

			if($this->ownerid==0)
				PHPPE::exec("UPDATE pages SET lockd=CURRENT_TIMESTAMP,ownerid=? WHERE id=?",[PHPPE::$user->id,$item]);
			if($page['ownerid']&&$page['ownerid']!=PHPPE::$user->id)
				PHPPE::js("init()","alert('".L("Page is locked!")."');",true);
//			$d = PHPPE::template("frame");
//			$T =
			$this->_result = /*PHPPE::_t(*/PHPPE::template($this->template)/*)*/;
//			if( preg_match( "/<!app>/ims", $d,$m,PREG_OFFSET_CAPTURE ) )
//				$this->_result = z( $d,0,$m[ 0 ][ 1 ] ) . $T . w( $d,$m[ 0 ][ 1 ] + 6 );
//			if( !$d ) $this->_result = "<div id='content'>".$T."</div>";
//			$this->_templates=array_column(PHPPE::query("id","views","","","id"),"id");
		} else {
			if(!empty($_REQUEST['unlock'])) {
				PHPPE::exec("UPDATE pages SET lockd=0,ownerid=0 WHERE id=?",[$_REQUEST['unlock']]);
				PHPPE::redirect();
			}
			$_SESSION['cms_page']=[];
			$this->_pages=[];
			PHPPE::exec("DELETE FROM pages WHERE id='' OR template=''");
			$p = PHPPE::query("a.id,a.name,a.template as tid,a.dds,a.ownerid,a.modifyid,max(a.created) as created,max(a.lockd) as lockd,count(1) as versions,b.name as lockuser,c.name as moduser,v.name as template, CURRENT_TIMESTAMP as ct",
				"pages a left join users b on a.ownerid=b.id left join users c on a.modifyid=c.id left join views v on a.template=v.id",
				"a.id!='frame'","a.template,a.id","a.modifyd DESC");
			PHPPE::exec("UPDATE pages SET lockd=0,ownerid=0 WHERE ownerid=?",[PHPPE::$user->id]);
			if(!empty($_REQUEST['order'])) {
				$this->_pages[0]=$p;
			} else {
				foreach($p as $v)
					$this->_pages[$v['template']][]=$v;
			}
			PHPPE::$core->needframe=true;
		}
	}
}
