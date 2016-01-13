<?php
namespace PHPPE\Ctrl;
use PHPPE\Core as PHPPE;

class CMSPageDiff extends \PHPPE\Ctrl {
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
		PHPPE::$core->nopanel = true;

		PHPPE::jslib("cms.js","cms_init();".(empty(PHPPE::$core->noanim)?"cms_initpagediff();":""));
		list($c) = x("?",@$_SERVER['REQUEST_URI']); $s=$_SERVER['SCRIPT_NAME'];
		$u = w($c,(z($c,0,u($s))==$s?u($s):u(n($s)))+1);
		if($u[0]=="/") $u=w($c,1);
		if($u[u($u)-1]=="/") $u=z($u,0,u($u)-1);
		PHPPE::$core->item=urldecode(substr($u,13));
	}

	function action($item)
	{
		if(!empty($item)) {
			PHPPE::$core->noframe = false;
			$frame = @jd(PHPPE::field("data","pages","id='frame'"));
			PHPPE::assign("frame",$frame);
			$this->_pages=$_SESSION['cms_page']['_pages'];

			//! load archive version
			$page = PHPPE::fetch( "*", "pages", "(id=? OR ? LIKE id||'/%') AND created=?", "", "id DESC,created DESC",[$_SESSION['cms_page']['id'],$_SESSION['cms_page']['id'],$item]);
			PHPPE::$core->site = L("ARCHIVE").": ".$page['name'];
			if(is_string($page['data'])) $page['data']=@json_decode($page['data'],true);
			if(is_array($page['data'])) foreach($page['data'] as $k=>$v) {$this->$k=$v;$_SESSION['cms_page']['data'][$k]=$v;}
			foreach(["id","name","lang","filter","template","pubd","expd","dds","ownerid","created"] as $k) $this->$k=$_SESSION['cms_page'][$k]=$page[$k];
			$p=json_decode($_SESSION['cms_page']['dds'],true);
			if(is_array($p)) {
				foreach($p as $k => $c)
					if($k != "dds") {
						try{
						$this->$k = PHPPE::query($c[ 0 ], $c[ 1 ], @ $c[ 2 ], @ $c[ 3 ], @ $c[ 4 ], @ $c[ 5 ], PHPPE::getval(@ $c[ 6 ]));
						} catch(\Exception $e) {PHPPE::log("E",$_SESSION['cms_page']['id']." ".$e->getMessage()." ".implode(" ",$c),"dds");}
					}
			}
			$old = /*PHPPE::_t(*/PHPPE::template($this->template)/*)*/;

			if(!isset($_REQUEST['nodiff'])) {
				include_once("vendor/phppe/CMS/libs/simplediff.php");

				//! load current version
				$page = PHPPE::fetch( "*", "pages", "id=? OR ? LIKE id||'/%'", "", "id DESC,created DESC",[$_SESSION['cms_page']['id'],$_SESSION['cms_page']['id']]);
				if(is_string($page['data'])) $page['data']=@json_decode($page['data'],true);
				if(is_array($page['data'])) foreach($page['data'] as $k=>$v) {$this->$k=$v;$_SESSION['cms_page']['data'][$k]=$v;}
				foreach(["id","name","lang","filter","template","pubd","expd","dds","ownerid","created"] as $k) $this->$k=$_SESSION['cms_page'][$k]=$page[$k];
				$p=json_decode($_SESSION['cms_page']['dds'],true);
				if(is_array($p)) {
					foreach($p as $k => $c)
						if($k != "dds") {
							try{
							$this->$k = PHPPE::query($c[ 0 ], $c[ 1 ], @ $c[ 2 ], @ $c[ 3 ], @ $c[ 4 ], @ $c[ 5 ], PHPPE::getval(@ $c[ 6 ]));
							} catch(\Exception $e) {PHPPE::log("E",$_SESSION['cms_page']['id']." ".$e->getMessage()." ".implode(" ",$c),"dds");}
						}
				}
				$curr = /*PHPPE::_t(*/PHPPE::template($this->template)/*)*/;
				//! make sure diff splits on tag end
				$this->_result=htmlDiff(preg_replace("/>([^\ \t\n])/m","> \\1",$old),preg_replace("/>([^\ \t\n])/m","> \\1",$curr));
				//! remove diff inside tags
				$this->_result=preg_replace("/(<[^<>]+)<ins>.*?<\/ins>([^<>]*>)/ims","\\1\\2",$this->_result);
				$this->_result=preg_replace("/(<[^<>]+)<del>(.*?)<\/del>([^<>]*>)/ims","\\1\\2\\3",$this->_result);
			} else
				$this->_result=$old;


		}
	}
}
