<?php
use PHPPE\Core as PHPPE;

class CMS_Ctrl extends \PHPPE\App {
	public $id;
	public $name;
	public $_pages;
	public $_templates;
	public $_result;
	private static $_table="pages";

	function __construct()
	{
		PHPPE::$core->nocache = true;
		PHPPE::$core->site = L("CMS Pages");
		PHPPE::jslib("cms.js","cms_init();");
	}

	function action($item)
	{
		if(!empty($item)) {
			PHPPE::js("init()","cms_getbreakpoints();",true);
		$_SESSION['cms_page']=[];
		$page = PHPPE::fetch( "*", "pages", "id=? OR ? LIKE id||'/%'", "", "id DESC,created DESC",[$item,$item]);
		$this->_pages = PHPPE::query("created as id,created as name,created as ago","pages", "id=?", "", "created DESC",0,0,[$page['id']]);
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
//		$d = PHPPE::template("frame");
//		$T =
		$this->_result = /*PHPPE::_t(*/PHPPE::template($this->template)/*)*/;
//		if( preg_match( "/<!app>/ims", $d,$m,PREG_OFFSET_CAPTURE ) )
//			$this->_result = z( $d,0,$m[ 0 ][ 1 ] ) . $T . w( $d,$m[ 0 ][ 1 ] + 6 );
//		if( !$d ) $this->_result = "<div id='content'>".$T."</div>";
//		$this->_templates=array_column(PHPPE::query("id","views","","","id"),"id");
		} else {
			$_SESSION['cms_page']=[];
			$this->_pages=[];
			PHPPE::exec("DELETE FROM pages WHERE id='' OR template=''");
			$p = PHPPE::query("a.id,a.name,a.template,a.dds,a.ownerid,max(a.created) as created,count(1) as versions,b.name as username","pages a left join users b on a.ownerid=b.id","a.id!='frame'","a.template,a.id","a.id ASC");
			foreach($p as $v)
				$this->_pages[$v['template']][]=$v;
		}
	}
}
