<?php
/**
 * @file vendor/phppe/CMS/ctrl/layouts.php
 * @author bzt
 * @date 26 May 2016
 * @brief list and edit layouts
 */

namespace PHPPE\Ctrl;
use PHPPE\Core as Core;
use PHPPE\View as View;
use PHPPE\Http as Http;

class CMSLayouts
{
/**
 * default action
 */
	function action($item)
	{
		$name="layoutadd";
		$_SESSION['cms_param'][sha1("layoutadd_")] = new \PHPPE\AddOn\layoutadd([],$name,$name);
		if(empty($item)){
            if(!empty($_REQUEST['set'])) {
                \PHPPE\DS::exec("UPDATE ".\PHPPE\Views::$_table." SET id=sitebuild WHERE sitebuild!='' AND id='frame'");
                \PHPPE\DS::exec("UPDATE ".\PHPPE\Views::$_table." SET id='frame' WHERE sitebuild=?",trim($_REQUEST['set']));
                Http::redirect();
            }
			$this->layouts = \PHPPE\Views::find([],"sitebuild=''","name");
			$this->sitebuilds = \PHPPE\Views::find([],"sitebuild!=''","name");
		} else {
			$this->layout = new \PHPPE\Views($item);
			if(!empty($this->layout->jslib))
				foreach($this->layout->jslib as $j)
					View::jslib($j);
			if(!empty($this->layout->css))
				foreach($this->layout->css as $c)
					View::css($c);
			$this->numPages = \PHPPE\Page::getNum($item);
			$layout=Core::req2arr("layout");
			if(!empty($this->layout->sitebuild) && !empty($layout)) {
				Core::$core->noframe=1;
				$layout['sitebuild']=$layout['name'];
			}
			if(Core::isTry("layout")) {
				if(!empty($layout['delete'])){
					$this->layout->delete();
				} else {
					unset($layout['delete']);
					if(!empty($this->layout->sitebuild))
						$layout['sitebuild']=$layout['id'];
					$this->layout = new \PHPPE\Views($layout);
					$this->layout->save();
				}
				Http::redirect("cms/layouts");
			}
		}
	}
}
