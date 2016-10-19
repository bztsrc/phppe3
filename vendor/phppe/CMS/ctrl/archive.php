<?php
/**
 * @file vendor/phppe/CMS/ctrl/archive.php
 * @author bzt
 * @date 26 May 2016
 * @brief
 */

namespace PHPPE\Ctrl;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Http as Http;

class CMSArchive
{
    public $title;
    public $result;
/**
 * default action
 */
    function action($item)
    {
	if(!Core::$user->has("siteadm|webadm")||empty($_REQUEST['created']))
		Http::redirect($item);
	if(isset($_REQUEST['pagedel'])) {
		\PHPPE\Page::delete($item,$_REQUEST['created']);
		Http::redirect($item);
	}
	if(isset($_REQUEST['revert'])) {
		Http::redirect($item);
	}

	$frame = \PHPPE\DS::field("data,dds","pages","id='frame' AND (lang='' OR lang=?) AND created<=?","","",[Core::$client->lang,$_REQUEST['created']]);
	$frame['dss'] = @json_decode($frame['dds'],true);
	$frame['data'] = @json_decode($frame['data'],true);
	\PHPPE\View::assign("frame",$frame['data']);

	//! load archive version
	$page = \PHPPE\DS::fetch( "*", "pages", "(id=? OR ? LIKE id||'/%') AND (lang='' OR lang=?) AND created=?", "", "id DESC,created DESC",[$item,$item,Core::$client->lang,$_REQUEST['created']]);
	$this->title = L("ARCHIVE").": ".$page['name'];
	if(is_string($page['data'])) $page['data']=@json_decode($page['data'],true);
	if(is_array($page['data'])) foreach($page['data'] as $k=>$v) {$this->$k=$v;}
	foreach(["id","name","lang","filter","template","pubd","expd","dds","ownerid","created"] as $k) $this->$k=$page[$k];
	$p=@array_merge($frame['dds'],@json_decode($page['dds'],true));
	if(is_array($p)) {
		foreach($p as $k => $c)
			if($k != "dds") {
				try{
				$this->$k = \PHPPE\DS::query($c[ 0 ], $c[ 1 ], @ $c[ 2 ], @ $c[ 3 ], @ $c[ 4 ], @ $c[ 5 ], \PHPPE\View::getval(@ $c[ 6 ]));
				} catch(\Exception $e) {Core::log("E",$item." ".$e->getMessage()." ".implode(" ",$c),"dds");}
			}
	}
	$old = \PHPPE\View::template($this->template);

	if(isset($_REQUEST['diff'])) {
		include_once("vendor/phppe/CMS/libs/simplediff.php");

		//! load current version
		$page = \PHPPE\DS::fetch( "*", "pages", "id=? OR ? LIKE id||'/%'", "", "id DESC,created DESC",[$item,$item]);
		if(is_string($page['data'])) $page['data']=@json_decode($page['data'],true);
		if(is_array($page['data'])) foreach($page['data'] as $k=>$v) {$this->$k=$v;}
		foreach(["id","name","lang","filter","template","pubd","expd","dds","ownerid","created"] as $k) $this->$k=$page[$k];
		$p=@array_merge($frame['dds'],@json_decode($page['dds'],true));
		if(is_array($p)) {
			foreach($p as $k => $c)
				if($k != "dds") {
					try{
					$this->$k = \PHPPE\DS::query($c[ 0 ], $c[ 1 ], @ $c[ 2 ], @ $c[ 3 ], @ $c[ 4 ], @ $c[ 5 ], \PHPPE\View::getval(@ $c[ 6 ]));
					} catch(\Exception $e) {Core::log("E",$item." ".$e->getMessage()." ".implode(" ",$c),"dds");}
				}
		}
		$curr = \PHPPE\View::template($this->template);
		//! make sure diff splits on tag end
		$this->result=htmlDiff(preg_replace("/>([^\ \t\n])/m","> \\1",$old),preg_replace("/>([^\ \t\n])/m","> \\1",$curr));
		//! remove diff inside tags
		$this->result=preg_replace("/(<[^<>]+)<ins>.*?<\/ins>([^<>]*>)/ims","\\1\\2",$this->result);
		$this->result=preg_replace("/(<[^<>]+)<del>(.*?)<\/del>([^<>]*>)/ims","\\1\\2\\3",$this->result);
	} else
		$this->result=$old;
    }
}